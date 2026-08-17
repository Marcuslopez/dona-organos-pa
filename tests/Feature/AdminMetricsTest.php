<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AdminMetricsService;
use Carbon\CarbonImmutable;
use Database\Seeders\GeographyCatalogSeeder;
use Database\Seeders\ReferenceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-13 10:00:00', 'America/Panama'));
        $this->seed(ReferenceCatalogSeeder::class);
        $this->seed(GeographyCatalogSeeder::class);
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_cumulative_growth_contains_only_active_normal_and_demo_donors(): void
    {
        $this->donorAt('2025-07-15 09:00:00', false, '8-100-1001');
        $this->donorAt('2025-08-15 09:00:00', true, 'D-100-1010', 'withdrawn');
        $this->donorAt('2025-09-10 09:00:00', true, 'D-100-1002');
        $this->donorAt('2025-09-12 09:00:00', false, '8-100-1011', 'withdrawn');
        $this->donorAt('2026-08-01 09:00:00', false, '8-100-1003');
        $this->donorAt('2026-08-02 09:00:00', true, 'D-100-1012', 'withdrawn');

        $growth = app(AdminMetricsService::class)->cumulativeGrowthLast12Months();

        $this->assertCount(12, $growth);
        $this->assertSame('2025-09', $growth->first()['period']);
        $this->assertSame(1, $growth->first()['registrations']);
        $this->assertSame(2, $growth->first()['total']);
        $this->assertSame('2026-08', $growth->last()['period']);
        $this->assertSame(1, $growth->last()['registrations']);
        $this->assertSame(3, $growth->last()['total']);
        $this->assertSame(0, $growth->firstWhere('period', '2026-01')['registrations']);
    }

    public function test_administrator_can_open_first_metrics_chart(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->donorAt('2026-08-01 09:00:00', true, 'D-100-1001');

        $this->actingAs($user)->get(route('admin.metrics.index'))
            ->assertOk()
            ->assertSee('Métricas de donantes')
            ->assertSee('Crecimiento acumulado de donantes')
            ->assertSee('Hasta el mes actual')
            ->assertSee('growth-line', false)
            ->assertSee('+1');
    }

    public function test_monthly_activity_uses_only_donor_registration_date_and_current_status(): void
    {
        $this->donorAt('2026-07-03 09:00:00', true, 'D-100-1004');
        $this->donorAt('2026-07-05 09:00:00', false, '8-100-1005');
        $this->donorAt('2026-07-08 09:00:00', true, 'D-100-1007', 'withdrawn');
        $this->donorAt('2026-08-02 09:00:00', false, '8-100-1008', 'withdrawn');
        $this->donorAt('2026-08-20 09:00:00', false, '8-100-1009', 'withdrawn');

        $activity = app(AdminMetricsService::class)->registrationsByCurrentStatusLast12Months();
        $july = $activity->firstWhere('period', '2026-07');
        $august = $activity->firstWhere('period', '2026-08');

        $this->assertCount(12, $activity);
        $this->assertSame(2, $july['highs']);
        $this->assertSame(1, $july['lows']);
        $this->assertSame(3, $july['total']);
        $this->assertSame(0, $august['highs']);
        $this->assertSame(1, $august['lows']);
        $this->assertSame(1, $august['total']);
    }

    public function test_metrics_page_renders_monthly_highs_and_lows_chart(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->donorAt('2026-08-01 09:00:00', true, 'D-100-1006', 'withdrawn');

        $this->actingAs($user)->get(route('admin.metrics.index'))
            ->assertOk()
            ->assertSee('Altas y bajas de los últimos 12 meses')
            ->assertSee('Donantes registrados cada mes, clasificados según su estado actual.')
            ->assertSee('month-chart', false)
            ->assertSee('Altas')
            ->assertSee('Bajas');
    }

    public function test_status_distribution_uses_current_donor_statuses(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $this->donorAt('2026-08-01 09:00:00', true, 'D-100-1020');
        $this->donorAt('2026-08-02 09:00:00', false, '8-100-1021');
        $this->donorAt('2026-08-03 09:00:00', true, 'D-100-1022', 'withdrawn');

        $summary = app(AdminMetricsService::class)->summary();
        $this->assertSame(['total' => 3, 'active' => 2, 'withdrawn' => 1], $summary);

        $this->actingAs($user)->get(route('admin.metrics.index'))
            ->assertOk()
            ->assertSee('Altas y bajas de donantes')
            ->assertSee('Estado actual')
            ->assertSee('status-donut', false)
            ->assertSee('Activos')
            ->assertSee('66.7%')
            ->assertSee('Bajas')
            ->assertSee('33.3%');
    }

    public function test_age_and_province_charts_use_all_donors(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $youngId = $this->donorAt('2026-08-01 09:00:00', true, 'D-100-1030');
        $olderId = $this->donorAt('2026-08-02 09:00:00', false, '8-100-1031', 'withdrawn');
        $provinces = DB::table('provinces')->orderBy('id')->take(2)->get();
        DB::table('donors')->where('id', $youngId)->update([
            'birth_date' => '2000-01-01', 'province_id' => $provinces[0]->id,
        ]);
        DB::table('donors')->where('id', $olderId)->update([
            'birth_date' => '1960-01-01', 'province_id' => $provinces[1]->id,
        ]);

        $ages = app(AdminMetricsService::class)->ageDistribution()->pluck('total', 'label');
        $this->assertSame(1, $ages['18-29']);
        $this->assertSame(1, $ages['60-69']);

        $provinceTotals = app(AdminMetricsService::class)->provinceDistribution()->pluck('total', 'label');
        $this->assertSame(1, (int) $provinceTotals[$provinces[0]->name]);
        $this->assertSame(1, (int) $provinceTotals[$provinces[1]->name]);

        $this->actingAs($user)->get(route('admin.metrics.index'))
            ->assertOk()
            ->assertSee('Donantes por edad')
            ->assertSee('Rangos de edad')
            ->assertSee('vertical-chart', false)
            ->assertSee('Donantes por provincia')
            ->assertSee('Todas las provincias')
            ->assertSee('horizontal-chart', false)
            ->assertSee($provinces[0]->name)
            ->assertSee($provinces[1]->name);
    }

    public function test_guest_cannot_open_metrics(): void
    {
        $this->get(route('admin.metrics.index'))->assertRedirect(route('login'));
    }

    private function donorAt(string $registeredAt, bool $isDemo, string $document, string $status = 'active'): int
    {
        $place = DB::table('corregimientos')->join('districts', 'districts.id', '=', 'corregimientos.district_id')
            ->select('corregimientos.id as corregimiento_id', 'districts.id as district_id', 'districts.province_id')->first();

        return DB::table('donors')->insertGetId([
            'document_number' => $document, 'full_name' => 'Donante de Métricas',
            'first_name' => 'Donante', 'first_last_name' => 'Métricas', 'birth_date' => '1990-01-01',
            'gender_id' => DB::table('genders')->value('id'), 'email' => strtolower(str_replace('-', '', $document)).'@example.test',
            'phone' => '6123-4567', 'province_id' => $place->province_id, 'district_id' => $place->district_id,
            'corregimiento_id' => $place->corregimiento_id, 'status' => $status, 'is_demo' => $isDemo,
            'registered_at' => $registeredAt, 'created_at' => $registeredAt, 'updated_at' => $registeredAt,
        ]);
    }
}
