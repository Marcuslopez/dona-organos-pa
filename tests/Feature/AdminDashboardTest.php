<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\DonorCardService;
use Database\Seeders\GeographyCatalogSeeder;
use Database\Seeders\ReferenceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ReferenceCatalogSeeder::class);
        $this->seed(GeographyCatalogSeeder::class);
    }

    public function test_dashboard_shows_statistics_and_registered_donors(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $donorId = $this->createDonor();

        DB::table('donor_cards')->insert([
            'donor_id' => $donorId,
            'folio' => 'CD-0000001',
            'public_token_hash' => hash('sha256', 'token'),
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Registro de donantes')
            ->assertSee('data-csv-download=', false)
            ->assertSee('Donante Administrativo')
            ->assertSee('donante.administrativo@example.com');
    }

    public function test_dashboard_shows_donor_once_when_multiple_card_versions_exist(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $donorId = $this->createDonor();

        foreach (['CD-0000001', 'CD-0000002'] as $index => $folio) {
            DB::table('donor_cards')->insert([
                'donor_id' => $donorId,
                'folio' => $folio,
                'public_token_hash' => hash('sha256', 'token-'.$index),
                'issued_at' => now()->addMinutes($index),
                'revoked_at' => $index === 0 ? now() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->actingAs($user)->get(route('admin.dashboard'))->assertOk();

        $this->assertSame(1, substr_count($response->getContent(), 'Donante Administrativo'));
        $response->assertSee('1–1 de 1');
    }

    public function test_dashboard_can_filter_donors_by_status(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->createDonor('active', 'Donante Activo');
        $this->createDonor('withdrawn', 'Donante Retirado', '8-222-2222');

        $this->actingAs($user)->get(route('admin.dashboard', ['estado' => 'active']))
            ->assertOk()
            ->assertSee('Donante Activo')
            ->assertDontSee('Donante Retirado');
    }

    public function test_dashboard_filters_active_donors_by_default(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->createDonor('active', 'Donante Activo');
        $this->createDonor('withdrawn', 'Donante Retirado', '8-222-2222');

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Donante Activo')
            ->assertDontSee('Donante Retirado')
            ->assertSee('<option value="active" selected>Activos</option>', false);
    }

    public function test_dashboard_can_explicitly_show_all_statuses(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->createDonor('active', 'Donante Activo');
        $this->createDonor('withdrawn', 'Donante Retirado', '8-222-2222');

        $this->actingAs($user)->get(route('admin.dashboard').'?estado=')
            ->assertOk()
            ->assertSee('Donante Activo')
            ->assertSee('Donante Retirado')
            ->assertSee('<option value="" selected>Todos</option>', false);
    }

    public function test_csv_export_uses_filters_and_exports_all_matching_results(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->createDonor('active', 'Donante Activo Uno', '8-111-1111');
        $this->createDonor('active', 'Donante Activo Dos', '8-222-2222');
        $this->createDonor('withdrawn', 'Donante Retirado', '8-333-3333');

        $response = $this->actingAs($user)->get(route('admin.donors.export.csv', [
            'estado' => 'active',
            'nombre' => 'Donante Activo',
        ]))->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8')
            ->assertDownload('donantes-'.now()->timezone('America/Panama')->format('Y-m-d').'.csv');

        ob_start();
        $response->sendContent();
        $csv = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Nombre,Cédula,Correo,Provincia,Contacto,"Correo contacto","Fecha de registro",Estado', $csv);
        $this->assertStringContainsString('Donante Activo Uno', $csv);
        $this->assertStringContainsString('Donante Activo Dos', $csv);
        $this->assertStringNotContainsString('Donante Retirado', $csv);
    }

    public function test_authenticated_administrator_can_open_donor_detail(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $donorId = $this->createDonor();

        $response = $this->actingAs($user)->get(route('admin.donors.show', $donorId))
            ->assertOk()
            ->assertSee('Detalle del donante')
            ->assertSee('<title>donante-sin-folio-donante-administrativo</title>', false)
            ->assertSee('Donante Administrativo')
            ->assertSee('Consentimiento informado')
            ->assertSee('Evidencia técnica de auditoría')
            ->assertSee('print-when-expanded', false)
            ->assertSee('Imprimir / Guardar PDF')
            ->assertDontSee('Preferencia de donación')
            ->assertDontSee('Fecha exacta del servidor')
            ->assertDontSee('Emisión del carné');

        $this->assertSame(1, substr_count($response->getContent(), 'Donante Administrativo'));
    }

    public function test_guest_cannot_open_donor_detail(): void
    {
        $this->get('/administracion/donantes/1')->assertRedirect(route('login'));
    }

    public function test_dashboard_includes_demo_donors_in_the_same_grid(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $donorId = $this->createDonor('active', 'Donante Semilla Visible', 'D-100-0001');
        DB::table('donors')->where('id', $donorId)->update(['is_demo' => true]);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Donante Semilla Visible');
    }

    public function test_administrator_can_download_donor_card_as_pdf(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $donorId = $this->createDonor();
        $folio = 'CD-0000001';
        $token = app(DonorCardService::class)->publicToken($donorId, $folio);

        DB::table('donor_cards')->insert([
            'donor_id' => $donorId,
            'folio' => $folio,
            'public_token_hash' => hash('sha256', $token),
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pdfResponse = $this->actingAs($user)->get(route('admin.donors.card.pdf', $donorId))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf')
            ->assertHeader('content-disposition', 'inline; filename=carnet-CD-0000001-donante-administrativo.pdf');

        $this->assertGreaterThan(5000, strlen($pdfResponse->getContent()));

        $this->actingAs($user)->get(route('admin.donors.card.print', $donorId))
            ->assertOk()
            ->assertSee('Selecciona una impresora o “Guardar como PDF”')
            ->assertSee(route('admin.donors.card.pdf', $donorId), false);

        $this->get(route('cards.verify', $token))
            ->assertOk()
            ->assertSee('Registro activo verificado')
            ->assertDontSee('Verificación pública')
            ->assertDontSee('Carné vigente')
            ->assertSee('Hola, gracias por tomar la decisión de donar vida.')
            ->assertSee('Voluntad registrada:')
            ->assertSee('Carné:')
            ->assertSee('Tu decisión puede transformar vidas')
            ->assertDontSee('Folio')
            ->assertSee($folio);
    }

    private function createDonor(string $status = 'active', string $name = 'Donante Administrativo', string $document = '8-111-1111'): int
    {
        $province = DB::table('provinces')->first();
        $district = DB::table('districts')->where('province_id', $province->id)->first();
        $corregimiento = DB::table('corregimientos')->where('district_id', $district->id)->first();

        return DB::table('donors')->insertGetId([
            'document_type' => 'cedula',
            'document_number' => $document,
            'document_code_hash' => password_hash('ABC123456', PASSWORD_BCRYPT),
            'document_code_fingerprint' => hash_hmac('sha256', $document, (string) config('app.key')),
            'full_name' => $name,
            'birth_date' => '1990-01-01',
            'gender_id' => DB::table('genders')->value('id'),
            'email' => str_replace(' ', '.', strtolower($name)).'@example.com',
            'phone' => '6123-4567',
            'province_id' => $province->id,
            'district_id' => $district->id,
            'corregimiento_id' => $corregimiento->id,
            'status' => $status,
            'registered_at' => now(),
            'withdrawn_at' => $status === 'withdrawn' ? now() : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
