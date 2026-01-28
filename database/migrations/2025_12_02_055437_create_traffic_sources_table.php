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
        if (!Schema::hasTable('traffic_sources')) {
            Schema::create('traffic_sources', function (Blueprint $table) {
                $table->id();
                $table->string('source', 50);
                $table->date('date');
                $table->integer('visits')->default(0);
                $table->timestamps();
                
                $table->unique(['source', 'date'], 'unique_source_date');
                $table->index('date', 'idx_date');
                $table->index('source', 'idx_source');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('traffic_sources');
    }
};
