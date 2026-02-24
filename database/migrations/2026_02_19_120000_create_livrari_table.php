<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Livrări: număr comandă, dată, adresă livrare, nr client, dată livrare, user care a completat.
     */
    public function up(): void
    {
        if (Schema::hasTable('livrari')) {
            return;
        }
        Schema::create('livrari', function (Blueprint $table) {
            $table->id();
            $table->string('numar_comanda', 100);
            $table->date('data');
            $table->string('adresa_livrarii', 500);
            $table->string('nr_client', 100);
            $table->date('data_livrarii');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->index('user_id');
            $table->index('data');
            $table->index('data_livrarii');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livrari');
    }
};
