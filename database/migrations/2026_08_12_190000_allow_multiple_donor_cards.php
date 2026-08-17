<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donor_cards', function (Blueprint $table): void {
            $table->index('donor_id');
        });

        Schema::table('donor_cards', function (Blueprint $table): void {
            $table->dropUnique(['donor_id']);
            $table->index(['donor_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::table('donor_cards', function (Blueprint $table): void {
            $table->dropIndex(['donor_id', 'issued_at']);
            $table->unique('donor_id');
        });

        Schema::table('donor_cards', function (Blueprint $table): void {
            $table->dropIndex(['donor_id']);
        });
    }
};
