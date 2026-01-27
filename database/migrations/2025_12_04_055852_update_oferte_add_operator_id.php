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
        if (Schema::hasTable('oferte')) {
            Schema::table('oferte', function (Blueprint $table) {
                if (!Schema::hasColumn('oferte', 'operator_id')) {
                    $table->unsignedBigInteger('operator_id')->nullable()->after('id');
                    $table->index('operator_id', 'idx_operator_id_oferte');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('oferte')) {
            Schema::table('oferte', function (Blueprint $table) {
                if (Schema::hasColumn('oferte', 'operator_id')) {
                    $table->dropIndex('idx_operator_id_oferte');
                    $table->dropColumn('operator_id');
                }
            });
        }
    }
};
