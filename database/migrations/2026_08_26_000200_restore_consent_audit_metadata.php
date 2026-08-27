<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recupera los metadatos de trazabilidad del consentimiento.
     * Los valores históricos que ya fueron depurados permanecen nulos;
     * los nuevos consentimientos los registran desde su creación.
     */
    public function up(): void
    {
        if (! Schema::hasTable('consents')) {
            return;
        }

        if (! Schema::hasColumn('consents', 'request_id')) {
            Schema::table('consents', fn (Blueprint $table) => $table->uuid('request_id')->nullable()->after('accepted_at')->index());
        }
        if (! Schema::hasColumn('consents', 'ip_address')) {
            Schema::table('consents', fn (Blueprint $table) => $table->string('ip_address', 45)->nullable()->after('request_id'));
        }
        if (! Schema::hasColumn('consents', 'user_agent')) {
            Schema::table('consents', fn (Blueprint $table) => $table->string('user_agent', 500)->nullable()->after('ip_address'));
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('consents')) {
            return;
        }

        $columns = array_values(array_filter(
            ['request_id', 'ip_address', 'user_agent'],
            fn (string $column) => Schema::hasColumn('consents', $column),
        ));

        if ($columns !== []) {
            Schema::table('consents', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
