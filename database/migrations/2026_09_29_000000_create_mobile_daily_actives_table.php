<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mobile_daily_actives')) {
            return;
        }

        Schema::create('mobile_daily_actives', function (Blueprint $table) {
            $table->date('day');
            $table->string('kind', 10);
            $table->string('identity', 128);

            $table->primary(['day', 'kind', 'identity']);
            $table->index(['kind', 'day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mobile_daily_actives');
    }
};
