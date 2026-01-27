<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Folosim conexiunea 'dashboard' pentru users, sessions și password_reset_tokens
        $connection = 'dashboard';
        
        if (!Schema::connection($connection)->hasTable('users')) {
            Schema::connection($connection)->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable()->unique();
                $table->string('username')->unique();
                $table->string('password')->nullable();
                $table->string('password_hash')->nullable();
                $table->string('role')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::connection($connection)->hasTable('password_reset_tokens')) {
            Schema::connection($connection)->create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::connection($connection)->hasTable('sessions')) {
            Schema::connection($connection)->create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = 'dashboard';
        Schema::connection($connection)->dropIfExists('sessions');
        Schema::connection($connection)->dropIfExists('password_reset_tokens');
        Schema::connection($connection)->dropIfExists('users');
    }
};
