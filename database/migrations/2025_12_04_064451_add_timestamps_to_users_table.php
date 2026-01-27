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
        $connection = 'dashboard';
        if (Schema::connection($connection)->hasTable('users')) {
            Schema::connection($connection)->table('users', function (Blueprint $table) use ($connection) {
                if (!Schema::connection($connection)->hasColumn('users', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::connection($connection)->hasColumn('users', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = 'dashboard';
        if (Schema::connection($connection)->hasTable('users')) {
            Schema::connection($connection)->table('users', function (Blueprint $table) use ($connection) {
                if (Schema::connection($connection)->hasColumn('users', 'created_at')) {
                    $table->dropColumn('created_at');
                }
                if (Schema::connection($connection)->hasColumn('users', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }
};
