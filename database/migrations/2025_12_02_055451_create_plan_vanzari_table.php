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
        if (!Schema::hasTable('plan_vanzari')) {
            Schema::create('plan_vanzari', function (Blueprint $table) {
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
        Schema::dropIfExists('plan_vanzari');
    }
};
