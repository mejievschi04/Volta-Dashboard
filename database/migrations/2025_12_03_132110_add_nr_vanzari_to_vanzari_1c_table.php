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
        if (!Schema::hasTable('vanzari_1c')) {
            return;
        }

        if (!Schema::hasColumn('vanzari_1c', 'nr_vanzari')) {
            Schema::table('vanzari_1c', function (Blueprint $table): void {
                $table->integer('nr_vanzari')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('vanzari_1c')) {
            return;
        }

        if (Schema::hasColumn('vanzari_1c', 'nr_vanzari')) {
            Schema::table('vanzari_1c', function (Blueprint $table): void {
                $table->dropColumn('nr_vanzari');
            });
        }
    }
};
