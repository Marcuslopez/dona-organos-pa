<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\VideoMetadataService;
use App\Services\VideoUploadService;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_list_only_the_four_cms_sections(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $storyId = DB::table('contents')->insertGetId([
            'seed_key' => 'story_test', 'type' => 'story', 'title' => 'Historia audiovisual',
            'body' => 'Testimonio autorizado.',
            'is_visible' => true, 'sort_order' => 1, 'published_at' => now(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('content_media')->insert([
            'content_id' => $storyId, 'media_type' => 'video', 'disk' => 'public',
            'path' => 'content/stories/video-test.mp4', 'original_name' => 'video-test.mp4',
            'mime_type' => 'video/mp4', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('contents')->insert([
            'seed_key' => 'hero_outside_cms', 'type' => 'hero', 'title' => 'Texto fuera del CMS',
            'body' => 'No debe aparecer.', 'is_visible' => true, 'sort_order' => 1,
            'published_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($user)->get(route('admin.contents.index', ['por_pagina' => 30]))
            ->assertOk()
            ->assertSee('Contenidos del portal')
            ->assertSee('Aspectos Legales Importantes')
            ->assertSee('Mitos y Tabúes Desmentidos')
            ->assertSee('Preguntas Frecuentes')
            ->assertSee('Historias Personales')
            ->assertSee('Historia audiovisual')
            ->assertSee('Video asociado')
            ->assertDontSee('Texto fuera del CMS')
            ->assertSee('1–27 de 27');
    }

    public function test_content_list_can_filter_by_section_status_and_text(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        DB::table('contents')->where('seed_key', 'faq_001')->update(['is_visible' => false]);

        $this->actingAs($user)->get(route('admin.contents.index', [
            'tipo' => 'faq', 'estado' => 'hidden', 'buscar' => 'órganos',
        ]))
            ->assertOk()
            ->assertSee('¿Qué órganos y tejidos se pueden donar?')
            ->assertSee('Oculto')
            ->assertDontSee('Si soy donante, el equipo médico no me va a salvar la vida');
    }

    public function test_content_list_rejects_unsupported_filters_and_paginates_safely(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->get(route('admin.contents.index', [
            'tipo' => 'hero', 'estado' => 'deleted', 'por_pagina' => 999,
        ]))
            ->assertOk()
            ->assertSee('1–10 de 26');
    }

    public function test_guest_cannot_open_content_management(): void
    {
        $this->get(route('admin.contents.index'))->assertRedirect(route('login'));
    }

    public function test_administrator_can_create_text_story_with_optional_video(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);
        $this->mock(VideoMetadataService::class)
            ->shouldReceive('probe')
            ->once()
            ->andReturn([
                'duration_seconds' => 45,
                'width' => 1920,
                'height' => 1080,
                'video_codec' => 'h264',
                'audio_codec' => 'aac',
            ]);

        $response = $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'story',
            'title' => 'Testimonio de prueba',
            'subtitle' => 'Persona receptora',
            'body' => 'Esta historia puede publicarse con un video opcional.',
            'video' => UploadedFile::fake()->create('historia.mp4', 500, 'video/mp4'),
            'sort_order' => 3,
            'is_visible' => '1',
        ]);

        $response->assertRedirect(route('admin.contents.index'));
        $story = DB::table('contents')->where('title', 'Testimonio de prueba')->first();
        $this->assertNotNull($story);
        $this->assertSame($user->id, $story->created_by);
        $media = DB::table('content_media')->where('content_id', $story->id)->first();
        $this->assertNotNull($media);
        $this->assertSame('video', $media->media_type);
        $this->assertSame(45, $media->duration_seconds);
        $this->assertSame(1920, $media->width);
        $this->assertSame(1080, $media->height);
        $this->assertStringStartsWith('content/stories/', $media->path);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_administrator_can_upload_mov_and_it_is_stored_as_normalized_mp4(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);
        $source = UploadedFile::fake()->create('historia-iphone.mov', 500, 'video/quicktime');
        $normalized = UploadedFile::fake()->create('historia-iphone.mp4', 450, 'video/mp4');
        $temporaryPath = $normalized->getRealPath();

        $this->mock(VideoUploadService::class)
            ->shouldReceive('prepare')
            ->once()
            ->andReturn([
                'file' => $normalized,
                'metadata' => [
                    'duration_seconds' => 30,
                    'width' => 1280,
                    'height' => 720,
                    'video_codec' => 'h264',
                    'audio_codec' => 'aac',
                ],
                'temporary_path' => $temporaryPath,
            ]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'story',
            'title' => 'Historia desde iPhone',
            'body' => 'Historia acompañada por un archivo MOV convertido.',
            'video' => $source,
            'sort_order' => 4,
            'is_visible' => '1',
        ])->assertRedirect(route('admin.contents.index'));

        $content = DB::table('contents')->where('title', 'Historia desde iPhone')->first();
        $media = DB::table('content_media')->where('content_id', $content->id)->first();
        $this->assertSame('historia-iphone.mov', $media->original_name);
        $this->assertSame('video/mp4', $media->mime_type);
        $this->assertStringEndsWith('.mp4', $media->path);
        $this->assertSame(1280, $media->width);
        $this->assertSame(720, $media->height);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_removing_story_video_deletes_the_physical_file(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);
        $contentId = DB::table('contents')->insertGetId([
            'type' => 'story',
            'title' => 'Historia con video removible',
            'body' => 'Testimonio autorizado.',
            'is_visible' => true,
            'sort_order' => 1,
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $path = 'content/stories/video-removible.mp4';
        Storage::disk('public')->put($path, 'video');
        DB::table('content_media')->insert([
            'content_id' => $contentId,
            'media_type' => 'video',
            'disk' => 'public',
            'path' => $path,
            'original_name' => 'video-removible.mp4',
            'mime_type' => 'video/mp4',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->put(route('admin.contents.update', $contentId), [
            'type' => 'story',
            'title' => 'Historia con video removible',
            'body' => 'Testimonio autorizado.',
            'sort_order' => 1,
            'is_visible' => '1',
            'remove_media' => '1',
        ])->assertRedirect(route('admin.contents.index'));

        Storage::disk('public')->assertMissing($path);
        $this->assertSoftDeleted('content_media', ['content_id' => $contentId]);
    }

    public function test_cms_rejects_video_formats_other_than_mp4_or_mov(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'story',
            'title' => 'Video incompatible',
            'body' => 'La historia conserva texto aunque el archivo sea rechazado.',
            'video' => UploadedFile::fake()->create('historia.webm', 500, 'video/webm'),
            'sort_order' => 4,
        ])->assertSessionHasErrors('video');

        $this->assertDatabaseMissing('contents', ['title' => 'Video incompatible']);
    }

    public function test_administrator_can_attach_a_valid_legal_image_with_metadata(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'legal',
            'title' => 'Marco jurídico actualizado',
            'body' => 'Contenido legal obligatorio.',
            'image' => UploadedFile::fake()->image('marco-legal.jpg', 1600, 900),
            'image_alt' => 'Persona consultando el marco jurídico de donación.',
            'sort_order' => 7,
            'is_visible' => '1',
        ])->assertRedirect(route('admin.contents.index'));

        $content = DB::table('contents')->where('title', 'Marco jurídico actualizado')->first();
        $media = DB::table('content_media')->where('content_id', $content->id)->first();
        $this->assertSame('image', $media->media_type);
        $this->assertSame(1600, $media->width);
        $this->assertSame(900, $media->height);
        $this->assertSame('Persona consultando el marco jurídico de donación.', $media->alt_text);
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_legal_image_must_respect_dimensions_and_accessible_description(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'legal', 'title' => 'Imagen inválida', 'body' => 'Texto válido.',
            'image' => UploadedFile::fake()->image('cuadrada.jpg', 800, 800),
            'sort_order' => 8,
        ])->assertSessionHasErrors(['image', 'image_alt']);

        $this->assertDatabaseMissing('contents', ['title' => 'Imagen inválida']);
    }

    public function test_administrator_can_update_and_soft_delete_content(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $content = DB::table('contents')->where('seed_key', 'faq_001')->first();

        $this->actingAs($user)->put(route('admin.contents.update', $content->id), [
            'type' => 'faq',
            'title' => 'Pregunta actualizada',
            'body' => 'Respuesta actualizada.',
            'sort_order' => 4,
        ])->assertRedirect(route('admin.contents.index'));

        $this->assertDatabaseHas('contents', [
            'id' => $content->id,
            'title' => '¿Pregunta actualizada?',
            'is_visible' => false,
            'updated_by' => $user->id,
        ]);

        $this->actingAs($user)->delete(route('admin.contents.destroy', $content->id))
            ->assertRedirect(route('admin.contents.index'));
        $this->assertNotNull(DB::table('contents')->where('id', $content->id)->value('deleted_at'));
        $this->assertSame($user->id, DB::table('contents')->where('id', $content->id)->value('deleted_by'));
    }

    public function test_moving_content_reorders_the_section_without_duplicates(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $firstMyth = DB::table('contents')->where('seed_key', 'myth_001')->first();

        $this->actingAs($user)->put(route('admin.contents.update', $firstMyth->id), [
            'type' => 'myth',
            'title' => $firstMyth->title,
            'body' => $firstMyth->body,
            'sort_order' => 3,
            'is_visible' => '1',
        ])->assertRedirect(route('admin.contents.index'));

        $this->assertSame([
            'myth_002' => 1,
            'myth_003' => 2,
            'myth_001' => 3,
        ], DB::table('contents')->whereIn('seed_key', ['myth_001', 'myth_002', 'myth_003'])
            ->orderBy('sort_order')->pluck('sort_order', 'seed_key')->all());
        $this->assertSame(10, DB::table('contents')->where('type', 'myth')->distinct()->count('sort_order'));
    }

    public function test_creating_content_in_an_occupied_position_shifts_following_items(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'faq',
            'title' => 'Nueva pregunta intermedia',
            'body' => 'Respuesta de prueba.',
            'sort_order' => 2,
            'is_visible' => '1',
        ])->assertRedirect(route('admin.contents.index'));

        $this->assertDatabaseHas('contents', ['title' => '¿Nueva pregunta intermedia?', 'sort_order' => 2]);
        $this->assertDatabaseHas('contents', ['seed_key' => 'faq_002', 'sort_order' => 3]);
        $this->assertSame(9, DB::table('contents')->where('type', 'faq')->distinct()->count('sort_order'));
    }

    public function test_deleting_content_closes_the_order_gap(): void
    {
        $this->seed(ContentSeeder::class);
        $user = User::factory()->create(['is_active' => true]);
        $secondStory = DB::table('contents')->where('seed_key', 'story_002')->first();

        $this->actingAs($user)->delete(route('admin.contents.destroy', $secondStory->id))
            ->assertRedirect(route('admin.contents.index'));

        $this->assertSame([1], DB::table('contents')->where('type', 'story')->whereNull('deleted_at')->pluck('sort_order')->all());
    }

    public function test_cms_preserves_safe_links_and_rejects_unsafe_links(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $payload = [
            'type' => 'faq',
            'title' => 'Consulta oficial',
            'body' => '<div>Consulta el <a href="https://www.minsa.gob.pa">sitio oficial</a>.</div>',
            'sort_order' => 1,
            'is_visible' => '1',
        ];

        $this->actingAs($user)->post(route('admin.contents.store'), $payload)
            ->assertRedirect(route('admin.contents.index'));
        $this->assertDatabaseHas('contents', [
            'title' => '¿Consulta oficial?',
            'body' => '<div>Consulta el <a href="https://www.minsa.gob.pa" target="_blank" rel="noopener noreferrer">sitio oficial</a>.</div>',
        ]);

        $payload['title'] = 'Enlace inseguro';
        $payload['body'] = '<div><a href="javascript:alert(1)">No permitido</a></div>';
        $this->actingAs($user)->post(route('admin.contents.store'), $payload)
            ->assertSessionHasErrors('body');
        $this->assertDatabaseMissing('contents', ['title' => 'Enlace inseguro']);
    }

    public function test_cms_rejects_empty_title_templates_and_required_fields(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        foreach ([
            ['type' => 'myth', 'title' => 'Mito:'],
            ['type' => 'faq', 'title' => '¿?'],
        ] as $template) {
            $this->actingAs($user)->post(route('admin.contents.store'), [
                ...$template,
                'body' => 'Este contenido sí contiene texto.',
                'sort_order' => 1,
            ])->assertSessionHasErrors([
                'title' => 'Escribe un título completo para el contenido.',
            ]);

            $this->assertDatabaseMissing('contents', $template);
        }

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => '',
            'title' => '',
            'body' => '',
            'sort_order' => 1,
        ])->assertSessionHasErrors([
            'type' => 'Debes escoger una sección.',
            'title' => 'El título es obligatorio.',
            'body' => 'El contenido es obligatorio.',
        ]);
    }

    public function test_cms_preserves_fixed_myth_prefix_and_normalizes_question_marks(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'myth',
            'title' => 'La donación tiene un costo',
            'body' => 'Aclaración del mito.',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.contents.index'));

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'myth',
            'title' => '"El personal médico no salvará mi vida"',
            'body' => 'Segunda aclaración del mito.',
            'sort_order' => 2,
        ])->assertRedirect(route('admin.contents.index'));

        $this->actingAs($user)->post(route('admin.contents.store'), [
            'type' => 'faq',
            'title' => 'Cómo puedo registrarme',
            'body' => 'Respuesta de la pregunta.',
            'sort_order' => 1,
        ])->assertRedirect(route('admin.contents.index'));

        $this->assertDatabaseHas('contents', ['title' => 'Mito: "La donación tiene un costo"']);
        $this->assertDatabaseHas('contents', ['title' => 'Mito: "El personal médico no salvará mi vida"']);
        $this->assertDatabaseHas('contents', ['title' => '¿Cómo puedo registrarme?']);
    }
}
