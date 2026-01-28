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
        if (!Schema::hasTable('date_op')) {
            Schema::create('date_op', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('operator_id')->nullable();
                $table->date('data');
                $table->decimal('suma_fara_tva', 15, 2)->default(0);
                $table->decimal('suma_cu_tva', 15, 2)->default(0);
                $table->decimal('profit', 15, 2)->default(0);
                $table->integer('nr_vanzari')->default(0);
                
                // Unique constraint on (operator_id, data) combination
                $table->unique(['operator_id', 'data'], 'unique_operator_data');
                $table->index('operator_id', 'idx_date_op_operator_id');
                $table->index('data', 'idx_date_op_data');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('date_op');
    }
};
