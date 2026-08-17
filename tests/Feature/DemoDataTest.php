<?php

namespace Tests\Feature;

use App\Services\AdminMetricsService;
use App\Services\DemoDataService;
use Carbon\CarbonImmutable;
use Database\Seeders\GeographyCatalogSeeder;
use Database\Seeders\MetricsDemoSeeder;
use Database\Seeders\ReferenceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class DemoDataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-14 10:00:00', 'America/Panama'));
        $this->seed(ReferenceCatalogSeeder::class);
        $this->seed(GeographyCatalogSeeder::class);
        config()->set('demo-data.records', 300);
        config()->set('demo-data.maximum_records', 400);
        config()->set('demo-data.months', 12);
        config()->set('demo-data.seed', 20260813);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_demo_seeder_requires_explicit_enablement(): void
    {
        config()->set('demo-data.enabled', false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('La carga demo está deshabilitada');
        $this->seed(MetricsDemoSeeder::class);
    }

    public function test_demo_data_can_be_seeded_inspected_and_purged_without_touching_normal_donors(): void
    {
        $normalId = $this->normalDonor();
        config()->set('demo-data.enabled', true);
        $this->seed(MetricsDemoSeeder::class);

        $service = app(DemoDataService::class);
        $status = $service->status();
        $this->assertSame(300, $status['counts']['donors']);
        $this->assertSame(300, $status['counts']['donation_preferences']);
        $this->assertSame(300, $status['counts']['consents']);
        $this->assertGreaterThanOrEqual(300, $status['counts']['donor_contacts']);
        $this->assertSame(0, DB::table('donors')->where('is_demo', true)->where('withdrawn_at', '>', now())->count());
        $withdrawalsByMonth = DB::table('donors')->where('is_demo', true)->where('status', 'withdrawn')
            ->selectRaw("strftime('%Y-%m', registered_at) as period, COUNT(*) as total")
            ->groupBy('period')->pluck('total', 'period');
        $this->assertSame([
            '2025-09' => 4, '2025-11' => 2, '2025-12' => 6, '2026-01' => 3,
            '2026-03' => 5, '2026-04' => 1, '2026-06' => 4, '2026-08' => 2,
        ], $withdrawalsByMonth->map(fn ($total): int => (int) $total)->all());
        $activeByMonth = DB::table('donors')->where('is_demo', true)->where('status', 'active')
            ->selectRaw("strftime('%Y-%m', registered_at) as period, COUNT(*) as total")
            ->groupBy('period')->pluck('total', 'period');
        $this->assertSame([
            '2025-09' => 38, '2025-11' => 19, '2025-12' => 52, '2026-01' => 26,
            '2026-03' => 48, '2026-04' => 13, '2026-06' => 44, '2026-08' => 33,
        ], $activeByMonth->map(fn ($total): int => (int) $total)->all());
        $registrationsByMonth = DB::table('donors')->where('is_demo', true)
            ->selectRaw("strftime('%Y-%m', registered_at) as period, COUNT(*) as total")
            ->groupBy('period')->pluck('total', 'period');
        $this->assertSame([
            '2025-09' => 42, '2025-11' => 21, '2025-12' => 58, '2026-01' => 29,
            '2026-03' => 53, '2026-04' => 14, '2026-06' => 48, '2026-08' => 35,
        ], $registrationsByMonth->map(fn ($total): int => (int) $total)->all());

        $metrics = app(AdminMetricsService::class);
        $this->assertSame(301, $metrics->summary()['total']);
        $this->assertNotEmpty($metrics->registrationsByMonth());
        $this->assertCount(6, $metrics->ageDistribution());

        $result = $service->purge();
        $this->assertTrue($result['deleted']);
        $this->assertDatabaseHas('donors', ['id' => $normalId, 'is_demo' => false]);
        $this->assertDatabaseMissing('donors', ['is_demo' => true]);
        $this->assertSame(0, DB::table('donor_contacts')->whereNotIn('donor_id', [$normalId])->count());

        $secondRun = $service->purge();
        $this->assertFalse($secondRun['deleted']);
    }

    public function test_commands_report_status_and_purge_demo_data(): void
    {
        config()->set('demo-data.enabled', true);
        config()->set('demo-data.records', 300);
        $this->seed(MetricsDemoSeeder::class);

        $this->artisan('demo:status')->assertSuccessful()->expectsOutputToContain('Ambiente: testing');
        $this->artisan('demo:purge', ['--force' => true])->assertSuccessful()
            ->expectsOutput('Datos demostrativos eliminados satisfactoriamente.');
        $this->artisan('demo:purge', ['--force' => true])->assertSuccessful()
            ->expectsOutput('No existen datos demostrativos para eliminar.');
    }

    private function normalDonor(): int
    {
        $place = DB::table('corregimientos')->join('districts', 'districts.id', '=', 'corregimientos.district_id')
            ->select('corregimientos.id as corregimiento_id', 'districts.id as district_id', 'districts.province_id')->first();

        return DB::table('donors')->insertGetId([
            'document_number' => '8-111-1111', 'full_name' => 'Donante Normal',
            'first_name' => 'Donante', 'first_last_name' => 'Normal', 'birth_date' => '1990-01-01',
            'gender_id' => DB::table('genders')->value('id'), 'email' => 'normal@example.test', 'phone' => '6123-4567',
            'province_id' => $place->province_id, 'district_id' => $place->district_id,
            'corregimiento_id' => $place->corregimiento_id, 'status' => 'active', 'is_demo' => false,
            'registered_at' => now(), 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
