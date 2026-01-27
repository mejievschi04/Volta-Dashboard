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
        Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
            if (!Schema::connection('vanzari')->hasColumn('vanzari_1c', 'nr_vanzari')) {
                $table->integer('nr_vanzari')->default(0)->after('profit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
            if (Schema::connection('vanzari')->hasColumn('vanzari_1c', 'nr_vanzari')) {
                $table->dropColumn('nr_vanzari');
            }
        });
    }
};
