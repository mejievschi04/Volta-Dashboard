<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('onec_kpi_syncs')) {
            Schema::create('onec_kpi_syncs', function (Blueprint $table) {
                $table->id();
                $table->date('period_start');
                $table->date('period_end');
                $table->string('company')->nullable();
                $table->string('currency', 10)->nullable();
                $table->decimal('vanzari_cu_tva', 15, 2)->default(0);
                $table->decimal('vanzari_fara_tva', 15, 2)->default(0);
                $table->decimal('profit', 15, 2)->default(0);
                $table->unsignedInteger('nr_comenzi')->default(0);
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();
                $table->index(['period_start', 'period_end']);
                $table->index('created_at');
            });
        }

        if (! Schema::hasTable('onec_kpi_operatori')) {
            Schema::create('onec_kpi_operatori', function (Blueprint $table) {
                $table->id();
                $table->foreignId('onec_kpi_sync_id')->constrained('onec_kpi_syncs')->cascadeOnDelete();
                $table->string('operator_id_1c', 50);
                $table->string('operator_nume')->nullable();
                $table->decimal('vanzari_cu_tva', 15, 2)->default(0);
                $table->decimal('vanzari_fara_tva', 15, 2)->default(0);
                $table->decimal('profit', 15, 2)->default(0);
                $table->unsignedInteger('nr_comenzi')->default(0);
                $table->timestamps();
                $table->index(['onec_kpi_sync_id', 'operator_id_1c']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('onec_kpi_operatori');
        Schema::dropIfExists('onec_kpi_syncs');
    }
};
