<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\ContentMedia;
use App\Services\ContentHtmlSanitizer;
use App\Services\ContentOrderingService;
use App\Services\VideoUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ContentController extends Controller
{
    private const TYPES = [
        'legal' => 'Aspectos Legales Importantes',
        'myth' => 'Mitos y Tabúes Desmentidos',
        'faq' => 'Preguntas Frecuentes',
        'story' => 'Historias Personales',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('buscar', ''));
        $type = array_key_exists((string) $request->query('tipo'), self::TYPES)
            ? (string) $request->query('tipo')
            : '';
        $status = in_array($request->query('estado'), ['visible', 'hidden'], true)
            ? (string) $request->query('estado')
            : '';
        $requestedPerPage = (int) $request->query('por_pagina', 10);
        $perPage = in_array($requestedPerPage, [10, 20, 30], true) ? $requestedPerPage : 10;

        $contents = Content::query()
            ->with('media')
            ->whereIn('type', array_keys(self::TYPES))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('body', 'like', "%{$search}%");
            }))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($status === 'visible', fn ($query) => $query->where('is_visible', true)->whereNotNull('published_at'))
            ->when($status === 'hidden', fn ($query) => $query->where(function ($query): void {
                $query->where('is_visible', false)->orWhereNull('published_at');
            }))
            ->orderByRaw("CASE type WHEN 'legal' THEN 1 WHEN 'myth' THEN 2 WHEN 'faq' THEN 3 WHEN 'story' THEN 4 ELSE 5 END")
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.contents.index', [
            'contents' => $contents,
            'types' => self::TYPES,
            'search' => $search,
            'type' => $type,
            'status' => $status,
            'perPage' => $perPage,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), $this->validationMessages());
        $this->validateMeaningfulTitle($data);
        $content = new Content;
        $obsoleteMediaPath = DB::transaction(function () use ($content, $data, $request): ?string {
            $this->fillContent($content, $data, $request);
            $content->created_by = $request->user()->id;
            $content->updated_by = $request->user()->id;
            app(ContentOrderingService::class)->insert($content, (int) $data['sort_order'], $request->user()->id);
            $content->save();
            return $this->syncMedia($content, $data, $request);
        });
        $this->deleteMediaFile($obsoleteMediaPath);

        return to_route('admin.contents.index')->with('status', 'Contenido creado correctamente.');
    }

    public function update(Request $request, Content $content): RedirectResponse
    {
        abort_unless(array_key_exists($content->type, self::TYPES), 404);

        $data = $request->validate($this->rules($content), $this->validationMessages());
        $this->validateMeaningfulTitle($data);
        $obsoleteMediaPath = DB::transaction(function () use ($content, $data, $request): ?string {
            $this->fillContent($content, $data, $request);
            $content->updated_by = $request->user()->id;
            app(ContentOrderingService::class)->move($content, (int) $data['sort_order'], $request->user()->id);
            $content->save();
            return $this->syncMedia($content, $data, $request);
        });
        $this->deleteMediaFile($obsoleteMediaPath);

        return to_route('admin.contents.index')->with('status', 'Contenido actualizado correctamente.');
    }

    public function destroy(Request $request, Content $content): RedirectResponse
    {
        abort_unless(array_key_exists($content->type, self::TYPES), 404);

        $obsoleteMediaPath = DB::transaction(function () use ($content, $request): ?string {
            app(ContentOrderingService::class)->remove($content, $request->user()->id);
            $content->deleted_by = $request->user()->id;
            $content->save();
            $mediaPath = null;
            if ($media = $content->media) {
                $mediaPath = $media->path;
                $media->deleted_by = $request->user()->id;
                $media->save();
                $media->delete();
            }
            $content->delete();

            return $mediaPath;
        });
        $this->deleteMediaFile($obsoleteMediaPath);

        return to_route('admin.contents.index')->with('status', 'Contenido eliminado correctamente.');
    }

    private function rules(?Content $content = null): array
    {
        $selectedType = $content?->type ?? request()->input('type');

        return [
            'type' => ['required', Rule::in($content ? [$content->type] : array_keys(self::TYPES))],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => [Rule::prohibitedIf($selectedType !== 'story'), 'nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:10000'],
            'image' => [Rule::prohibitedIf($selectedType !== 'legal'), 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=1200,min_height=675,max_width=2400,max_height=1350,ratio=16/9'],
            'image_alt' => [Rule::prohibitedIf($selectedType !== 'legal'), 'nullable', 'string', 'max:255', 'required_with:image'],
            'video' => [Rule::prohibitedIf($selectedType !== 'story'), 'nullable', 'file', 'mimes:mp4,mov', 'mimetypes:video/mp4,video/quicktime', 'max:'.config('cms.video.max_size_kb')],
            'remove_media' => [Rule::prohibitedIf(! in_array($selectedType, ['legal', 'story'], true)), 'nullable', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:1', 'max:9999'],
            'is_visible' => ['nullable', 'boolean'],
        ];
    }

    private function validationMessages(): array
    {
        return [
            'type.required' => 'Debes escoger una sección.',
            'type.in' => 'La sección seleccionada no es válida.',
            'title.required' => 'El título es obligatorio.',
            'body.required' => 'El contenido es obligatorio.',
        ];
    }

    private function validateMeaningfulTitle(array $data): void
    {
        $title = $this->titleBody($data['type'], (string) $data['title']);
        $isEmptyMythTemplate = $data['type'] === 'myth'
            && trim($title, " \t\n\r\0\x0B\"“”") === '';
        $isEmptyFaqTemplate = $data['type'] === 'faq' && $title === '';

        if ($isEmptyMythTemplate || $isEmptyFaqTemplate) {
            throw ValidationException::withMessages([
                'title' => 'Escribe un título completo para el contenido.',
            ]);
        }
    }

    private function normalizedTitle(string $type, string $title): string
    {
        $title = $this->titleBody($type, $title);

        return match ($type) {
            'myth' => 'Mito: '.$this->quotedMythTitle($title),
            'faq' => "¿{$title}?",
            default => $title,
        };
    }

    private function quotedMythTitle(string $title): string
    {
        $hasStraightQuotes = str_starts_with($title, '"') && str_ends_with($title, '"');
        $hasCurlyQuotes = str_starts_with($title, '“') && str_ends_with($title, '”');

        return $hasStraightQuotes || $hasCurlyQuotes ? $title : '"'.$title.'"';
    }

    private function titleBody(string $type, string $title): string
    {
        $title = trim($title);

        if ($type === 'myth') {
            return trim((string) preg_replace('/^Mito:\s*/iu', '', $title));
        }

        if ($type === 'faq') {
            return trim($title, " \t\n\r\0\x0B¿?");
        }

        return $title;
    }

    private function fillContent(Content $content, array $data, Request $request): void
    {
        $type = $content->exists ? $content->type : $data['type'];
        $content->type = $type;
        $content->title = $this->normalizedTitle($type, $data['title']);
        $content->subtitle = $type === 'story' ? trim((string) ($data['subtitle'] ?? '')) ?: null : null;
        $sanitizer = app(ContentHtmlSanitizer::class);
        if ($sanitizer->containsUnsafeLink($data['body'])) {
            throw ValidationException::withMessages([
                'body' => 'Los enlaces del contenido deben ser direcciones completas que comiencen con https:// o http://.',
            ]);
        }
        $sanitizedBody = $sanitizer->sanitize($data['body']);
        if (trim(strip_tags($sanitizedBody)) === '') {
            throw ValidationException::withMessages(['body' => 'El contenido debe incluir texto.']);
        }
        $content->body = $sanitizedBody;
        $content->sort_order = $data['sort_order'];
        $content->is_visible = $request->boolean('is_visible');
        $content->published_at = $content->is_visible ? ($content->published_at ?? now()) : null;

    }

    private function syncMedia(Content $content, array $data, Request $request): ?string
    {
        $media = ContentMedia::withTrashed()->where('content_id', $content->id)->first();
        $fileField = $content->type === 'legal' ? 'image' : ($content->type === 'story' ? 'video' : null);

        if (! $fileField) {
            return null;
        }

        if ($request->boolean('remove_media') && ! $request->hasFile($fileField)) {
            $obsoleteMediaPath = null;
            if ($media && ! $media->trashed()) {
                $obsoleteMediaPath = $media->path;
                $media->deleted_by = $request->user()->id;
                $media->save();
                $media->delete();
            }

            return $obsoleteMediaPath;
        }

        if (! $request->hasFile($fileField)) {
            if ($content->type === 'legal' && $media && ! $media->trashed()) {
                $altText = trim((string) ($data['image_alt'] ?? ''));
                if ($altText === '') {
                    throw ValidationException::withMessages(['image_alt' => 'La descripción de la imagen es obligatoria.']);
                }
                $media->alt_text = $altText;
                $media->updated_by = $request->user()->id;
                $media->save();
            }

            return null;
        }

        $file = $request->file($fileField);
        $preparedVideo = $content->type === 'story'
            ? app(VideoUploadService::class)->prepare($file)
            : null;
        $storedFile = $preparedVideo['file'] ?? $file;
        $videoMetadata = $preparedVideo['metadata'] ?? null;
        $storedMimeType = $preparedVideo
            ? 'video/mp4'
            : ($storedFile->getMimeType() ?: $storedFile->getClientMimeType());
        $directory = $content->type === 'legal' ? 'content/legal' : 'content/stories';
        try {
            $path = $storedFile->store($directory, 'public');
        } finally {
            if ($preparedVideo['temporary_path'] ?? null) {
                @unlink($preparedVideo['temporary_path']);
            }
        }
        $oldPath = $media && ! $media->trashed() ? $media->path : null;

        $media ??= new ContentMedia;
        if ($media->trashed()) {
            $media->restore();
        }
        $media->content_id = $content->id;
        $media->media_type = $content->type === 'legal' ? 'image' : 'video';
        $media->disk = 'public';
        $media->path = $path;
        $media->original_name = $file->getClientOriginalName();
        $media->mime_type = $storedMimeType;
        $media->size_bytes = Storage::disk('public')->size($path);
        $media->width = null;
        $media->height = null;
        if ($content->type === 'legal' && ($dimensions = @getimagesize($file->getRealPath()))) {
            [$media->width, $media->height] = $dimensions;
        }
        if ($videoMetadata) {
            $media->width = $videoMetadata['width'];
            $media->height = $videoMetadata['height'];
        }
        $media->duration_seconds = $videoMetadata['duration_seconds'] ?? null;
        $media->alt_text = $content->type === 'legal' ? trim((string) $data['image_alt']) : null;
        $media->created_by ??= $request->user()->id;
        $media->updated_by = $request->user()->id;
        $media->deleted_by = null;
        $media->save();

        return $oldPath && $oldPath !== $path ? $oldPath : null;
    }

    private function deleteMediaFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
