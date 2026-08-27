<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->simplifyConsents();

        foreach (['donor_health_answers', 'donation_preferences', 'health_questions', 'health_answer_options', 'donation_scopes'] as $table) {
            Schema::dropIfExists($table);
        }

        if (Schema::hasTable('donors') && Schema::hasColumn('donors', 'is_demo')) {
            if ($this->indexExists('donors', 'donors_is_demo_index')) {
                Schema::table('donors', fn (Blueprint $table) => $table->dropIndex('donors_is_demo_index'));
            }
            Schema::table('donors', fn (Blueprint $table) => $table->dropColumn('is_demo'));
        }
    }

    /**
     * Conserva el historial de decisiones y elimina campos que ya no forman
     * parte del consentimiento único del portal. La evidencia técnica
     * (solicitud, IP y navegador) se conserva para trazabilidad.
     */
    private function simplifyConsents(): void
    {
        if (! Schema::hasTable('consents')) {
            return;
        }

        if (! Schema::hasColumn('consents', 'consent_sequence')) {
            Schema::table('consents', fn (Blueprint $table) => $table->unsignedInteger('consent_sequence')->default(1)->after('donor_id'));
        }
        if (! Schema::hasColumn('consents', 'accepted')) {
            Schema::table('consents', fn (Blueprint $table) => $table->boolean('accepted')->default(false)->after('consent_sequence'));
        }
        if (! Schema::hasColumn('consents', 'revocation_reason')) {
            Schema::table('consents', fn (Blueprint $table) => $table->string('revocation_reason', 80)->nullable()->after('revoked_at'));
        }

        $legacyAcceptedColumns = ['voluntary_accepted', 'electronically_accepted', 'sensitive_data_authorized', 'institutional_query_authorized'];
        if (collect($legacyAcceptedColumns)->every(fn (string $column) => Schema::hasColumn('consents', $column))) {
            DB::table('consents')->update([
                'accepted' => DB::raw('CASE WHEN voluntary_accepted = 1 AND electronically_accepted = 1 AND sensitive_data_authorized = 1 AND institutional_query_authorized = 1 THEN 1 ELSE 0 END'),
            ]);
        }

        DB::table('consents')->select('id', 'donor_id')->orderBy('donor_id')->orderBy('accepted_at')->orderBy('id')->get()
            ->groupBy('donor_id')
            ->each(function ($consents): void {
                foreach ($consents->values() as $index => $consent) {
                    DB::table('consents')->where('id', $consent->id)->update(['consent_sequence' => $index + 1]);
                }
            });

        if ($this->indexExists('consents', 'consents_donor_id_version_unique')) {
            Schema::table('consents', fn (Blueprint $table) => $table->dropUnique('consents_donor_id_version_unique'));
        }
        $obsoleteColumns = ['signed_name', 'voluntary_accepted', 'electronically_accepted', 'sensitive_data_authorized', 'institutional_query_authorized', 'cornea_information_acknowledged'];
        $obsoleteColumns = array_values(array_filter($obsoleteColumns, fn (string $column) => Schema::hasColumn('consents', $column)));
        if ($obsoleteColumns !== []) {
            Schema::table('consents', fn (Blueprint $table) => $table->dropColumn($obsoleteColumns));
        }

        if (! $this->indexExists('consents', 'consents_donor_sequence_unique')) {
            Schema::table('consents', fn (Blueprint $table) => $table->unique(['donor_id', 'consent_sequence'], 'consents_donor_sequence_unique'));
        }
    }

    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))->contains(fn (object $row) => $row->name === $index);
        }

        return DB::table('information_schema.statistics')
            ->where('table_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('index_name', $index)
            ->exists();
    }

    public function down(): void
    {
        // La eliminación de datos médicos y campos de consentimiento es deliberadamente irreversible.
    }
};
