<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            $table->index(['user_id', 'data_livrarii'], 'livrari_user_delivery_idx');
        });

        Schema::table('onec_kpi_operatori', function (Blueprint $table) {
            $table->index(['operator_nume', 'onec_kpi_sync_id'], 'onec_operator_sync_idx');
        });
    }

    public function down(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            $table->dropIndex('livrari_user_delivery_idx');
        });

        Schema::table('onec_kpi_operatori', function (Blueprint $table) {
            $table->dropIndex('onec_operator_sync_idx');
        });
    }
};
