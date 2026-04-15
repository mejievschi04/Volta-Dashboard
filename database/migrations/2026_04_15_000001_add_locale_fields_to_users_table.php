<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country', 100)->nullable();
            }

            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 10)->nullable();
            }

            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 50)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'language')) {
                $table->dropColumn('language');
            }

            if (Schema::hasColumn('users', 'currency')) {
                $table->dropColumn('currency');
            }

            if (Schema::hasColumn('users', 'country')) {
                $table->dropColumn('country');
            }
        });
    }
};
