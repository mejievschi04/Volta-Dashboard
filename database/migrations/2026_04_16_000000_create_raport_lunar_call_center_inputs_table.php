<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('raport_lunar_call_center_inputs')) {
            return;
        }

        Schema::create('raport_lunar_call_center_inputs', function (Blueprint $table) {
            $table->id();
            $table->string('ym', 7)->comment('YYYY-MM');
            $table->string('operator_nume', 255);
            $table->unsignedInteger('chaturi')->default(0);
            $table->unsignedInteger('apeluri')->default(0);
            $table->timestamps();

            $table->unique(['ym', 'operator_nume']);
            $table->index('ym');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raport_lunar_call_center_inputs');
    }
};
