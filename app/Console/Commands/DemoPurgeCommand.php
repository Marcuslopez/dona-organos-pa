<?php

namespace App\Console\Commands;

use App\Services\DemoDataService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class DemoPurgeCommand extends Command
{
    protected $signature = 'demo:purge {--force : Omite la confirmación interactiva, no las validaciones}';

    protected $description = 'Elimina exclusivamente los donantes demostrativos y sus relaciones';

    public function handle(DemoDataService $service): int
    {
        try {
            $status = $service->status();
            $count = $status['counts']['donors'];
            if ($count === 0) {
                $this->info('No existen datos demostrativos para eliminar.');

                return self::SUCCESS;
            }

            $this->table(['Tabla', 'Registros por eliminar'], collect($status['counts'])->map(fn ($value, $table) => [$table, $value])->values()->all());

            if (! $this->option('force')) {
                $expected = "ELIMINAR {$count} DATOS DEMO";
                if ($this->ask("Escriba {$expected} para continuar") !== $expected) {
                    $this->warn('Limpieza cancelada. No se modificaron datos.');

                    return self::FAILURE;
                }
            }

            $result = $service->purge();
            Log::notice('Limpieza de datos demostrativos completada.', [
                'environment' => app()->environment(), 'counts' => $result['before']['counts'],
            ]);
            $this->info('Datos demostrativos eliminados satisfactoriamente.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Falló la limpieza de datos demostrativos.', ['environment' => app()->environment(), 'error' => $exception->getMessage()]);
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
