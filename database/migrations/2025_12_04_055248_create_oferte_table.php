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
        if (!Schema::hasTable('oferte')) {
            Schema::create('oferte', function (Blueprint $table) {
                $table->id();
                $table->string('operator', 255);
                $table->enum('status', ['trimise', 'finalizate', 'refuzate'])->default('trimise');
                $table->date('data_trimisa');
                $table->date('data_finalizata')->nullable();
                $table->decimal('valoare', 15, 2)->default(0);
                $table->text('observatii')->nullable();
                $table->timestamps();
                
                $table->index('operator', 'idx_operator');
                $table->index('status', 'idx_status');
                $table->index('data_trimisa', 'idx_data_trimisa');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferte');
    }
};
