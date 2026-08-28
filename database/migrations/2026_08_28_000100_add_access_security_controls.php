<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedTinyInteger('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('login_locked_until')->nullable()->after('failed_login_attempts')->index();
            $table->string('active_session_id', 120)->nullable()->after('login_locked_until')->index();
        });

        Schema::table('donors', function (Blueprint $table) {
            $table->string('active_access_token', 120)->nullable()->after('status')->index();
        });

        Schema::create('admin_login_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('donor_access_codes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('donor_id')->unique();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('consumed_at')->nullable();
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();

            $table->foreign('donor_id')->references('id')->on('donors')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donor_access_codes');
        Schema::dropIfExists('admin_login_codes');

        Schema::table('donors', function (Blueprint $table) {
            $table->dropIndex(['active_access_token']);
            $table->dropColumn('active_access_token');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['login_locked_until']);
            $table->dropIndex(['active_session_id']);
            $table->dropColumn(['failed_login_attempts', 'login_locked_until', 'active_session_id']);
        });
    }
};
