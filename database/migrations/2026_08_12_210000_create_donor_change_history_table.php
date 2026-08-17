<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_change_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->json('changed_fields');
            $table->json('previous_values');
            $table->json('new_values');
            $table->string('source', 30)->default('donor');
            $table->uuid('request_id')->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('changed_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_change_history');
    }
};
