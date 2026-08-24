<?php

namespace Tests\Feature;

use Database\Seeders\GeographyCatalogSeeder;
use Database\Seeders\ReferenceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsValidationCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_metrics_validation_command_reports_data_without_modifying_it(): void
    {
        $this->seed(ReferenceCatalogSeeder::class);
        $this->seed(GeographyCatalogSeeder::class);

        $this->artisan('metrics:validate')
            ->expectsOutputToContain('Validación de datos para métricas')
            ->expectsOutputToContain('Últimos 12 meses')
            ->expectsOutputToContain('Control de calidad')
            ->assertSuccessful();
    }
}
