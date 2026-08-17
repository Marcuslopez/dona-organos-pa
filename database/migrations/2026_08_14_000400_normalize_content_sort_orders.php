<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('contents')
                ->whereNull('deleted_at')
                ->select('type')
                ->distinct()
                ->pluck('type')
                ->each(function (string $type): void {
                    DB::table('contents')
                        ->where('type', $type)
                        ->whereNull('deleted_at')
                        ->orderBy('sort_order')
                        ->orderBy('id')
                        ->pluck('id')
                        ->each(fn (int $id, int $index) => DB::table('contents')->where('id', $id)->update([
                            'sort_order' => $index + 1,
                        ]));
                });
        });
    }

    public function down(): void
    {
        // La normalización no puede revertirse sin reintroducir órdenes ambiguos.
    }
};
