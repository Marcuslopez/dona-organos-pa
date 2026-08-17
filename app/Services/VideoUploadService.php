<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class VideoUploadService
{
    /**
     * @return array{file: UploadedFile, metadata: array, temporary_path: ?string}
     */
    public function prepare(UploadedFile $file): array
    {
        $metadataService = app(VideoMetadataService::class);
        $sourceMetadata = $metadataService->probe($file);

        if ($sourceMetadata['duration_seconds'] <= 0 || $sourceMetadata['duration_seconds'] > config('cms.video.max_duration_seconds')) {
            throw ValidationException::withMessages([
                'video' => 'El video debe durar como máximo 90 segundos.',
            ]);
        }

        if (! $this->requiresNormalization($file, $sourceMetadata)) {
            return [
                'file' => $file,
                'metadata' => $sourceMetadata,
                'temporary_path' => null,
            ];
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'dona-organos-video-');
        if ($temporaryPath === false) {
            throw ValidationException::withMessages([
                'video' => 'No fue posible preparar temporalmente el video.',
            ]);
        }

        $mp4Path = $temporaryPath.'.mp4';
        @unlink($temporaryPath);

        $process = new Process([
            (string) config('cms.video.ffmpeg_binary'),
            '-y',
            '-i', $file->getRealPath(),
            '-map', '0:v:0',
            '-map', '0:a:0?',
            '-vf', 'scale=1280:720:force_original_aspect_ratio=decrease,pad=1280:720:(ow-iw)/2:(oh-ih)/2',
            '-c:v', 'libx264',
            '-preset', 'medium',
            '-crf', '23',
            '-pix_fmt', 'yuv420p',
            '-c:a', 'aac',
            '-b:a', '128k',
            '-movflags', '+faststart',
            $mp4Path,
        ]);
        $process->setTimeout(180);

        try {
            $process->mustRun();
        } catch (ProcessFailedException $exception) {
            @unlink($mp4Path);
            throw ValidationException::withMessages([
                'video' => 'No fue posible preparar el video. Verifica que el archivo sea válido y no esté dañado.',
            ]);
        }

        if (! is_file($mp4Path) || filesize($mp4Path) === 0) {
            @unlink($mp4Path);
            throw ValidationException::withMessages([
                'video' => 'La conversión no produjo un video válido.',
            ]);
        }

        if (filesize($mp4Path) > ((int) config('cms.video.max_size_kb') * 1024)) {
            @unlink($mp4Path);
            throw ValidationException::withMessages([
                'video' => 'El video convertido supera el máximo permitido de 25 MB.',
            ]);
        }

        $normalized = new UploadedFile(
            $mp4Path,
            pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME).'.mp4',
            'video/mp4',
            null,
            true,
        );

        try {
            $metadata = $metadataService->inspect($normalized);
        } catch (ValidationException $exception) {
            @unlink($mp4Path);
            throw $exception;
        }

        return [
            'file' => $normalized,
            'metadata' => $metadata,
            'temporary_path' => $mp4Path,
        ];
    }

    /**
     * @param  array{duration_seconds: int, width: int, height: int, video_codec: string, audio_codec: ?string}  $metadata
     */
    private function requiresNormalization(UploadedFile $file, array $metadata): bool
    {
        return strtolower($file->getClientOriginalExtension()) === 'mov'
            || $metadata['video_codec'] !== config('cms.video.video_codec')
            || ($metadata['audio_codec'] && $metadata['audio_codec'] !== config('cms.video.audio_codec'))
            || $metadata['width'] < config('cms.video.min_width')
            || $metadata['height'] < config('cms.video.min_height')
            || $metadata['width'] > config('cms.video.max_width')
            || $metadata['height'] > config('cms.video.max_height');
    }
}
