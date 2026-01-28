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
        Schema::table('traffic_sources', function (Blueprint $table) {
            if (!Schema::hasColumn('traffic_sources', 'new_users')) {
                $table->integer('new_users')->default(0)->after('visits');
            }
            if (!Schema::hasColumn('traffic_sources', 'returning_users')) {
                $table->integer('returning_users')->default(0)->after('new_users');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('traffic_sources', function (Blueprint $table) {
            if (Schema::hasColumn('traffic_sources', 'new_users')) {
                $table->dropColumn('new_users');
            }
            if (Schema::hasColumn('traffic_sources', 'returning_users')) {
                $table->dropColumn('returning_users');
            }
        });
    }
};
