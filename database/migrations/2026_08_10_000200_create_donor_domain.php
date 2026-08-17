<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30)->default('cedula');
            $table->string('document_number', 40)->unique();
            $table->string('full_name', 180);
            $table->date('birth_date');
            $table->foreignId('gender_id')->constrained()->restrictOnDelete();
            $table->string('email', 180)->index();
            $table->string('phone', 20);
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->foreignId('corregimiento_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('registered_at')->useCurrent()->index();
            $table->timestamp('withdrawn_at')->nullable();
            $table->timestamps();
            $table->index(['province_id', 'registered_at']);
            $table->index(['status', 'registered_at']);
            $table->index('birth_date');
        });

        Schema::create('donor_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('relationship_id')->constrained()->restrictOnDelete();
            $table->string('full_name', 180);
            $table->string('email', 180)->nullable();
            $table->string('phone', 20);
            $table->boolean('is_informed')->default(false);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->index(['donor_id', 'is_primary']);
        });

        Schema::create('donation_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('donation_scope_id')->constrained()->restrictOnDelete();
            $table->boolean('research_authorized');
            $table->timestamps();
        });

        Schema::create('consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->string('version', 50);
            $table->string('signed_name', 180);
            $table->boolean('voluntary_accepted');
            $table->boolean('electronically_accepted');
            $table->boolean('sensitive_data_authorized');
            $table->boolean('institutional_query_authorized');
            $table->boolean('cornea_information_acknowledged')->default(false);
            $table->timestamp('accepted_at');
            $table->string('request_id', 100)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
            $table->unique(['donor_id', 'version']);
        });

        Schema::create('health_questions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('questionnaire_version', 50);
            $table->text('text');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('donor_health_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('health_question_id')->constrained()->restrictOnDelete();
            $table->foreignId('health_answer_option_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->unique(['donor_id', 'health_question_id']);
        });

        Schema::create('donor_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donor_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('folio', 20)->unique();
            $table->char('public_token_hash', 64)->unique();
            $table->timestamp('issued_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_cards');
        Schema::dropIfExists('donor_health_answers');
        Schema::dropIfExists('health_questions');
        Schema::dropIfExists('consents');
        Schema::dropIfExists('donation_preferences');
        Schema::dropIfExists('donor_contacts');
        Schema::dropIfExists('donors');
    }
};
