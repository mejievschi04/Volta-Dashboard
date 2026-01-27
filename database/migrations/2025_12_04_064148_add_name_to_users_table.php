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
                if (!Schema::connection($connection)->hasColumn('users', 'name')) {
                    $table->string('name')->nullable()->after('id');
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
                if (Schema::connection($connection)->hasColumn('users', 'name')) {
                    $table->dropColumn('name');
                }
            });
        }
    }
};
