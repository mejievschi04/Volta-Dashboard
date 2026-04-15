<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('date_op')) {
            return;
        }

        Schema::create('date_op', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('operator_id')->nullable()->index();
            $table->date('data');
            $table->decimal('suma_fara_tva', 15, 2)->default(0);
            $table->decimal('suma_cu_tva', 15, 2)->default(0);
            $table->decimal('profit', 15, 2)->default(0);
            $table->unsignedInteger('nr_vanzari')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('date_op');
    }
};
