<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('simulated_identities', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 30)->default('cedula');
            $table->string('document_number', 40)->unique();
            $table->string('document_code_hash');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('simulated_identities');
    }
};
