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
        if (!Schema::connection('vanzari')->hasTable('vanzari_1c')) {
            Schema::connection('vanzari')->create('vanzari_1c', function (Blueprint $table) {
                $table->id();
                $table->date('data');
                $table->decimal('suma_fara_tva', 15, 2)->default(0);
                $table->decimal('suma_cu_tva', 15, 2)->default(0);
                $table->decimal('profit', 15, 2)->default(0);
                $table->timestamps();
                
                $table->unique('data', 'unique_vanzare');
                $table->index('data', 'idx_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('vanzari')->dropIfExists('vanzari_1c');
    }
};
