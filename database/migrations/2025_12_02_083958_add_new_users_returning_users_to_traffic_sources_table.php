<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('traffic_sources')) {
            return;
        }

        Schema::table('traffic_sources', function (Blueprint $table): void {
            if (!Schema::hasColumn('traffic_sources', 'new_users')) {
                $table->integer('new_users')->default(0);
            }

            if (!Schema::hasColumn('traffic_sources', 'returning_users')) {
                $table->integer('returning_users')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('traffic_sources')) {
            return;
        }

        Schema::table('traffic_sources', function (Blueprint $table): void {
            if (Schema::hasColumn('traffic_sources', 'returning_users')) {
                $table->dropColumn('returning_users');
            }

            if (Schema::hasColumn('traffic_sources', 'new_users')) {
                $table->dropColumn('new_users');
            }
        });
    }
};
