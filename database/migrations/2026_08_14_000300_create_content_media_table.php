<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_id')->unique()->constrained('contents')->cascadeOnDelete();
            $table->string('media_type', 20);
            $table->string('disk', 50)->default('public');
            $table->string('path', 2048);
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('alt_text')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['media_type', 'deleted_at']);
        });

        DB::table('contents')->where('type', 'story')->whereNotNull('related_url')->orderBy('id')->each(function (object $content): void {
            $path = str_starts_with($content->related_url, '/storage/')
                ? substr($content->related_url, strlen('/storage/'))
                : $content->related_url;
            DB::table('content_media')->insert([
                'content_id' => $content->id,
                'media_type' => 'video',
                'disk' => 'public',
                'path' => $path,
                'original_name' => basename($path),
                'mime_type' => 'application/octet-stream',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('related_url');
        });
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('related_url', 2048)->nullable()->after('body');
        });

        DB::table('content_media')->where('media_type', 'video')->orderBy('id')->each(function (object $media): void {
            DB::table('contents')->where('id', $media->content_id)->update([
                'related_url' => '/storage/'.$media->path,
            ]);
        });

        Schema::dropIfExists('content_media');
    }
};
