<?php

namespace Tests\Feature;

use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_displays_published_cms_content(): void
    {
        $this->seed(ContentSeeder::class);

        $this->get('/')
            ->assertOk()
            ->assertSee('DONA ÓRGANOS PANAMÁ')
            ->assertSee('Aspectos Legales Importantes')
            ->assertSee('Acto altruista y gratuito')
            ->assertSee('Mitos y Tabúes Desmentidos')
            ->assertSee('Preguntas Frecuentes')
            ->assertSee('Historias Personales')
            ->assertSee('M. P.')
            ->assertSee('Laura Gómez')
            ->assertSee('Madre de paciente trasplantada');
    }

    public function test_home_page_does_not_display_hidden_or_deleted_content(): void
    {
        $this->seed(ContentSeeder::class);

        DB::table('contents')->where('seed_key', 'legal_001')->update(['is_visible' => false]);
        DB::table('contents')->where('seed_key', 'myth_001')->update(['deleted_at' => now()]);
        DB::table('contents')->where('seed_key', 'story_001')->update(['is_visible' => false]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee('Acto altruista y gratuito')
            ->assertDontSee('Si soy donante, el equipo médico no me va a salvar la vida')
            ->assertDontSee('Gracias a un donante hoy puedo volver a ver');
    }
}
