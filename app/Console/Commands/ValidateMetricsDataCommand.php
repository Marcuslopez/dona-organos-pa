<?php

namespace App\Console\Commands;

use App\Services\AdminMetricsService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ValidateMetricsDataCommand extends Command
{
    protected $signature = 'metrics:validate';

    protected $description = 'Valida y resume los datos utilizados por las métricas de donantes, sin modificar la base de datos';

    public function handle(AdminMetricsService $metrics): int
    {
        try {
            $timezone = config('app.timezone');
            $now = CarbonImmutable::now($timezone);
            $firstMonth = $now->startOfMonth()->subMonths(11);
            $summary = $metrics->summary();
            $growth = $metrics->cumulativeGrowthLast12Months()->keyBy('period');
            $activity = $metrics->registrationsByCurrentStatusLast12Months();

            $this->info('Validación de datos para métricas');
            $this->line('Fecha de ejecución: '.$now->format('d/m/Y h:i:s a'));
            $this->line('Período analizado: '.$firstMonth->format('d/m/Y').' a '.$now->format('d/m/Y'));
            $this->newLine();

            $this->table(['Total', 'Activos', 'Bajas'], [[
                $summary['total'],
                $summary['active'],
                $summary['withdrawn'],
            ]]);

            $this->newLine();
            $this->info('Últimos 12 meses (misma regla de las gráficas)');
            $this->table(
                ['Mes', 'Altas (estado activo)', 'Bajas (estado baja)', 'Activos acumulados'],
                $activity->map(function (array $month) use ($growth): array {
                    return [
                        $month['period'],
                        $month['highs'],
                        $month['lows'],
                        $growth[$month['period']]['total'] ?? 0,
                    ];
                })->all(),
            );

            $this->newLine();
            $this->info('Distribución por edad');
            $this->table(
                ['Rango', 'Donantes'],
                $metrics->ageDistribution()->map(fn (array $row): array => [$row['label'], $row['total']])->all(),
            );

            $this->newLine();
            $this->info('Distribución por provincia');
            $this->table(
                ['Provincia', 'Donantes'],
                $metrics->provinceDistribution()->map(fn (object $row): array => [$row->label, $row->total])->all(),
            );

            $this->newLine();
            $this->info('Control de calidad (sin datos personales)');
            $checks = [
                ['Donantes con estado no reconocido', DB::table('donors')->whereNotIn('status', ['active', 'withdrawn'])->count()],
                ['Donantes sin fecha de registro', DB::table('donors')->whereNull('registered_at')->count()],
                ['Donantes sin fecha de nacimiento', DB::table('donors')->whereNull('birth_date')->count()],
                ['Donantes sin provincia', DB::table('donors')->whereNull('province_id')->count()],
                ['Contactos sin donante asociado', DB::table('donor_contacts')->leftJoin('donors', 'donors.id', '=', 'donor_contacts.donor_id')->whereNull('donors.id')->count()],
            ];
            $this->table(['Validación', 'Cantidad'], $checks);

            $issues = collect($checks)->take(5)->filter(fn (array $check): bool => (int) $check[1] > 0);
            if ($issues->isNotEmpty()) {
                $this->warn('Se encontraron alertas de calidad. Revise las cantidades anteriores antes de certificar las métricas.');
            } else {
                $this->info('Sin alertas de calidad en los campos revisados.');
            }

            $this->line('Nota: “Altas” y “Bajas” se agrupan por fecha de registro y estado actual del donante, exactamente como el panel de métricas.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('No fue posible validar las métricas: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
