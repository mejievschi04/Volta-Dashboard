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
        if (Schema::connection('vanzari')->hasTable('vanzari_1c')) {
            Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
                if (!Schema::connection('vanzari')->hasColumn('vanzari_1c', 'operator_id')) {
                    $table->unsignedBigInteger('operator_id')->nullable()->after('id');
                    $table->index('operator_id', 'idx_operator_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('vanzari')->hasTable('vanzari_1c')) {
            Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
                if (Schema::connection('vanzari')->hasColumn('vanzari_1c', 'operator_id')) {
                    $table->dropIndex('idx_operator_id');
                    $table->dropColumn('operator_id');
                }
            });
        }
    }
};
