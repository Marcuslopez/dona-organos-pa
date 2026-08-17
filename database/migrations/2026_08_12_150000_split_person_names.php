<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['donors', 'donor_contacts'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->string('first_name', 80)->nullable()->after('full_name');
                $table->string('middle_name', 80)->nullable()->after('first_name');
                $table->string('first_last_name', 80)->nullable()->after('middle_name');
                $table->string('second_last_name', 80)->nullable()->after('first_last_name');
            });

            DB::table($tableName)->select('id', 'full_name')->orderBy('id')->each(function (object $row) use ($tableName): void {
                $parts = preg_split('/\s+/u', trim($row->full_name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $count = count($parts);

                DB::table($tableName)->where('id', $row->id)->update([
                    'first_name' => $parts[0] ?? $row->full_name,
                    'middle_name' => $count >= 3 ? $parts[1] : null,
                    'first_last_name' => $count >= 4 ? $parts[$count - 2] : ($parts[$count - 1] ?? $row->full_name),
                    'second_last_name' => $count >= 4 ? $parts[$count - 1] : null,
                ]);
            });
        }
    }

    public function down(): void
    {
        foreach (['donor_contacts', 'donors'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table): void {
                $table->dropColumn(['first_name', 'middle_name', 'first_last_name', 'second_last_name']);
            });
        }
    }
};
