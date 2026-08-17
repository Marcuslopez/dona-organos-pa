<?php

namespace App\Console\Commands;

use App\Services\DemoDataService;
use Illuminate\Console\Command;
use Throwable;

class DemoStatusCommand extends Command
{
    protected $signature = 'demo:status';

    protected $description = 'Inspecciona los datos demostrativos sin modificar la base de datos';

    public function handle(DemoDataService $service): int
    {
        try {
            $status = $service->status();
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Ambiente: '.$status['environment']);
        $this->table(['Tabla', 'Registros demo'], collect($status['counts'])->map(fn ($count, $table) => [$table, $count])->values()->all());
        $this->line('Período: '.($status['first_date'] ?: '—').' a '.($status['last_date'] ?: '—'));

        return self::SUCCESS;
    }
}
