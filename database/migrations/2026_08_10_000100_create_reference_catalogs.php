<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['genders', 'relationships', 'donation_scopes', 'health_answer_options'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 100);
                $table->unsignedSmallInteger('sort_order')->default(0);
                $table->boolean('is_active')->default(true)->index();
                $table->timestamps();
            });
        }

        Schema::create('provinces', function (Blueprint $table) {
            $table->id();
            $table->string('official_code', 30)->nullable()->unique();
            $table->string('name', 120);
            $table->string('type', 30)->default('province');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained()->restrictOnDelete();
            $table->string('official_code', 30)->nullable()->unique();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['province_id', 'name']);
        });

        Schema::create('corregimientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('district_id')->constrained()->restrictOnDelete();
            $table->string('official_code', 30)->nullable()->unique();
            $table->string('name', 120);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->unique(['district_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corregimientos');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('provinces');
        Schema::dropIfExists('health_answer_options');
        Schema::dropIfExists('donation_scopes');
        Schema::dropIfExists('relationships');
        Schema::dropIfExists('genders');
    }
};
