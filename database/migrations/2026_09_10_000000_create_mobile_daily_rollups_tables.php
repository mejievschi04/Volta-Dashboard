<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('mobile_event_daily_rollups')) {
            Schema::create('mobile_event_daily_rollups', function (Blueprint $table) {
                $table->id();
                $table->date('day');
                $table->string('event_name', 80);
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('sessions')->default(0);
                $table->unsignedInteger('users')->default(0);
                $table->timestamps();

                $table->unique(['day', 'event_name']);
                $table->index('day');
            });
        }

        if (! Schema::hasTable('mobile_crash_daily_rollups')) {
            Schema::create('mobile_crash_daily_rollups', function (Blueprint $table) {
                $table->id();
                $table->date('day');
                $table->unsignedInteger('total')->default(0);
                $table->unsignedInteger('fatal')->default(0);
                $table->unsignedInteger('fingerprints')->default(0);
                $table->timestamps();

                $table->unique('day');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_event_daily_rollups');
        Schema::dropIfExists('mobile_crash_daily_rollups');
    }
};
