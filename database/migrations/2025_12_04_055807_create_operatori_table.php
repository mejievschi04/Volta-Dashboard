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
        if (!Schema::hasTable('operatori')) {
            Schema::create('operatori', function (Blueprint $table) {
                $table->id();
                $table->string('nume', 255);
                $table->string('email', 255)->nullable();
                $table->string('telefon', 50)->nullable();
                $table->date('data_angajare')->nullable();
                $table->text('adresa')->nullable();
                $table->string('departament', 100)->nullable();
                $table->string('functie', 100)->nullable();
                $table->text('observatii')->nullable();
                $table->boolean('activ')->default(true);
                $table->timestamps();
                
                $table->index('nume', 'idx_nume');
                $table->index('activ', 'idx_activ');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operatori');
    }
};
