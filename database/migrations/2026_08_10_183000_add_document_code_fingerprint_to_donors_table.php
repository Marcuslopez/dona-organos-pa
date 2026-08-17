<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->char('document_code_fingerprint', 64)->nullable()->unique()->after('document_code_hash');
        });
    }

    public function down(): void
    {
        Schema::table('donors', function (Blueprint $table) {
            $table->dropUnique(['document_code_fingerprint']);
            $table->dropColumn('document_code_fingerprint');
        });
    }
};
