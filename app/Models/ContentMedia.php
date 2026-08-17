<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable(['media_type', 'disk', 'path', 'original_name', 'mime_type', 'size_bytes', 'width', 'height', 'duration_seconds', 'alt_text'])]
class ContentMedia extends Model
{
    use SoftDeletes;

    protected $table = 'content_media';

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(function (): string {
            // Public CMS files live on the same host as the application. A relative
            // URL avoids coupling playback to APP_URL's host/port during local and
            // LAN testing. Remote disks (for example S3) still provide their URL.
            if ($this->disk === 'public') {
                return '/storage/'.ltrim($this->path, '/');
            }

            return Storage::disk($this->disk)->url($this->path);
        });
    }
}
