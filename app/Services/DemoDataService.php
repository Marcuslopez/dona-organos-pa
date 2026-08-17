<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DemoDataService
{
    private const RELATED_TABLES = [
        'donor_contacts', 'donation_preferences', 'consents', 'donor_health_answers',
        'donor_cards', 'donor_status_history', 'donor_change_history',
    ];

    public function status(): array
    {
        if (! Schema::hasColumn('donors', 'is_demo')) {
            throw new RuntimeException('La migración de datos demostrativos no está aplicada.');
        }

        $ids = DB::table('donors')->where('is_demo', true)->pluck('id');
        $counts = ['donors' => $ids->count()];

        foreach (self::RELATED_TABLES as $table) {
            $counts[$table] = Schema::hasTable($table) && $ids->isNotEmpty()
                ? DB::table($table)->whereIn('donor_id', $ids)->count()
                : 0;
        }

        $dates = DB::table('donors')->where('is_demo', true)
            ->selectRaw('MIN(registered_at) as first_date, MAX(registered_at) as last_date')->first();

        return [
            'environment' => app()->environment(),
            'counts' => $counts,
            'first_date' => $dates?->first_date,
            'last_date' => $dates?->last_date,
        ];
    }

    public function purge(): array
    {
        $before = $this->status();

        if ($before['counts']['donors'] === 0) {
            return ['deleted' => false, 'before' => $before, 'after' => $before];
        }

        $normalBefore = DB::table('donors')->where('is_demo', false)->count();

        $purgedIds = [];

        DB::transaction(function () use (&$purgedIds): void {
            $ids = DB::table('donors')->where('is_demo', true)->lockForUpdate()->pluck('id');

            if ($ids->isEmpty()) {
                throw new RuntimeException('Los datos demostrativos cambiaron durante la validación.');
            }

            if (DB::table('donors')->whereIn('id', $ids)->where('is_demo', false)->exists()) {
                throw new RuntimeException('La selección contiene donantes no demostrativos.');
            }

            $purgedIds = $ids->all();
            DB::table('donors')->whereIn('id', $ids)->where('is_demo', true)->delete();
        }, 3);

        $after = $this->status();
        $normalAfter = DB::table('donors')->where('is_demo', false)->count();

        if ($after['counts']['donors'] !== 0 || array_sum(array_diff_key($after['counts'], ['donors' => true])) !== 0) {
            throw new RuntimeException('La limpieza dejó relaciones demostrativas pendientes.');
        }

        if ($normalBefore !== $normalAfter) {
            throw new RuntimeException('Cambió la cantidad de donantes no demostrativos.');
        }

        foreach (self::RELATED_TABLES as $table) {
            if (Schema::hasTable($table) && DB::table($table)->whereIn('donor_id', $purgedIds)->exists()) {
                throw new RuntimeException("La tabla {$table} conserva relaciones demostrativas.");
            }
        }

        return ['deleted' => true, 'before' => $before, 'after' => $after];
    }
}
