<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoMetadataService
{
    /**
     * @return array{duration_seconds: int, width: int, height: int, video_codec: string, audio_codec: ?string}
     */
    public function inspect(UploadedFile $file): array
    {
        $metadata = $this->probe($file);
        $errors = [];

        if ($metadata['video_codec'] !== config('cms.video.video_codec')) {
            $errors[] = 'El video debe utilizar el códec H.264.';
        }
        if ($metadata['audio_codec'] && $metadata['audio_codec'] !== config('cms.video.audio_codec')) {
            $errors[] = 'La pista de audio debe utilizar el códec AAC.';
        }
        if ($metadata['duration_seconds'] <= 0 || $metadata['duration_seconds'] > config('cms.video.max_duration_seconds')) {
            $errors[] = 'El video debe durar como máximo 90 segundos.';
        }
        if ($metadata['width'] < config('cms.video.min_width') || $metadata['height'] < config('cms.video.min_height') ||
            $metadata['width'] > config('cms.video.max_width') || $metadata['height'] > config('cms.video.max_height')) {
            $errors[] = 'La resolución del video debe estar entre 1280×720 y 1920×1080 píxeles.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages(['video' => $errors]);
        }

        return $metadata;
    }

    /**
     * Reads source metadata without enforcing the normalized output dimensions.
     *
     * @return array{duration_seconds: int, width: int, height: int, video_codec: string, audio_codec: ?string}
     */
    public function probe(UploadedFile $file): array
    {
        $process = new Process([
            (string) config('cms.video.ffprobe_binary'),
            '-v', 'error',
            '-show_entries', 'format=duration,format_name:stream=codec_type,codec_name,width,height',
            '-of', 'json',
            $file->getRealPath(),
        ]);
        $process->setTimeout(15);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            throw ValidationException::withMessages([
                'video' => 'No fue posible analizar el video. Verifica que el archivo sea válido y no esté dañado.',
            ]);
        }

        $probe = json_decode($process->getOutput(), true);
        if (! is_array($probe)) {
            throw ValidationException::withMessages(['video' => 'El video no contiene metadatos válidos.']);
        }

        $streams = collect($probe['streams'] ?? []);
        $video = $streams->firstWhere('codec_type', 'video');
        $audio = $streams->firstWhere('codec_type', 'audio');
        $duration = (float) ($probe['format']['duration'] ?? 0);
        $width = (int) ($video['width'] ?? 0);
        $height = (int) ($video['height'] ?? 0);
        if (! $video) {
            throw ValidationException::withMessages([
                'video' => 'El archivo debe contener una pista de video válida.',
            ]);
        }

        return [
            'duration_seconds' => (int) ceil($duration),
            'width' => $width,
            'height' => $height,
            'video_codec' => (string) $video['codec_name'],
            'audio_codec' => $audio['codec_name'] ?? null,
        ];
    }
}
