<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('contents')
            ->where('type', '!=', 'story')
            ->whereNotNull('related_url')
            ->orderBy('id')
            ->each(function (object $content): void {
                $url = htmlspecialchars($content->related_url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                DB::table('contents')->where('id', $content->id)->update([
                    'body' => $content->body.'<div><a href="'.$url.'" target="_blank" rel="noopener noreferrer">Consultar información relacionada</a></div>',
                    'related_url' => null,
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // La migración no intenta separar enlaces que luego hayan sido editados dentro de Trix.
    }
};
