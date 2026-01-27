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
        if (!Schema::connection('vanzari')->hasTable('plan_vanzari')) {
            Schema::connection('vanzari')->create('plan_vanzari', function (Blueprint $table) {
                $table->id();
                $table->integer('an');
                $table->string('luna', 20);
                $table->decimal('valoare', 15, 2)->default(0);
                
                $table->index(['an', 'luna']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('vanzari')->dropIfExists('plan_vanzari');
    }
};
