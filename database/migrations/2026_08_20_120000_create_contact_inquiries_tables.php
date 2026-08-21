<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->nullable();
            $table->string('email');
            $table->text('message');
            $table->string('status', 20)->default('nueva');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->timestamp('privacy_accepted_at');
            $table->string('privacy_policy_version', 30);
            $table->string('requester_ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['status', 'assigned_to']);
            $table->index('email');
        });

        Schema::create('contact_inquiry_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamp('sent_at')->nullable();
            $table->string('delivery_status', 20)->default('sent');
            $table->timestamps();
        });

        Schema::create('contact_inquiry_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contact_inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 50);
            $table->string('previous_status', 20)->nullable();
            $table->string('current_status', 20)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_inquiry_histories');
        Schema::dropIfExists('contact_inquiry_replies');
        Schema::dropIfExists('contact_inquiries');
    }
};
