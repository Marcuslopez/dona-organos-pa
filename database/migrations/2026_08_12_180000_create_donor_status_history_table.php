<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donor_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->string('previous_status', 20)->nullable();
            $table->string('new_status', 20);
            $table->string('reason', 255)->nullable();
            $table->string('source', 30)->default('donor');
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuid('request_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->index(['donor_id', 'changed_at']);
            $table->index(['new_status', 'changed_at']);
        });

        DB::table('donors')->orderBy('id')->chunkById(100, function ($donors): void {
            $now = now();
            DB::table('donor_status_history')->insert($donors->map(fn (object $donor): array => [
                'donor_id' => $donor->id,
                'previous_status' => null,
                'new_status' => $donor->status,
                'reason' => 'Estado inicial incorporado al crear el historial.',
                'source' => 'system',
                'changed_by_user_id' => null,
                'request_id' => (string) Str::uuid(),
                'ip_address' => null,
                'user_agent' => null,
                'changed_at' => $donor->withdrawn_at ?? $donor->registered_at ?? $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_status_history');
    }
};
