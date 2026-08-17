<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('title');
        });

        DB::table('contents')->where('seed_key', 'story_001')->update([
            'title' => 'M. P.',
            'subtitle' => null,
            'body' => 'Gracias a un donante hoy puedo volver a ver. No tengo palabras para agradecer.',
            'sort_order' => 1,
            'updated_at' => now(),
        ]);

        DB::table('contents')->where('seed_key', 'story_002')->update([
            'title' => 'Laura Gómez',
            'subtitle' => 'Madre de paciente trasplantada',
            'body' => "Mi hija tiene una segunda oportunidad gracias a un donante anónimo.\n\nCuando nos dijeron que mi hija necesitaba un trasplante de corazón, sentí que el mundo se detenía. Fueron semanas de mucha incertidumbre, rezando por un milagro. Y ese milagro llegó: una familia, en medio de su propio dolor, decidió decir sí a la donación.\n\nHoy, mi hija corre, ríe y va al colegio como cualquier niña de su edad. Siempre estaremos agradecidos con esa persona y su familia. Donar salva vidas. No hay gesto más generoso y humano.",
            'sort_order' => 2,
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('contents', function (Blueprint $table) {
            $table->dropColumn('subtitle');
        });
    }
};
