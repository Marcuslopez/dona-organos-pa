<?php

return [
    'video' => [
        'ffmpeg_binary' => env('FFMPEG_BINARY', 'ffmpeg'),
        'ffprobe_binary' => env('FFPROBE_BINARY', 'ffprobe'),
        'max_size_kb' => 25 * 1024,
        'max_duration_seconds' => 90,
        'min_width' => 1280,
        'min_height' => 720,
        'max_width' => 1920,
        'max_height' => 1080,
        'video_codec' => 'h264',
        'audio_codec' => 'aac',
    ],
];
