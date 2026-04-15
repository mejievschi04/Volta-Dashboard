<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vanzari_1c', function (Blueprint $table): void {
            if (!Schema::hasColumn('vanzari_1c', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()->index();
            }
        });

        Schema::table('oferte', function (Blueprint $table): void {
            if (!Schema::hasColumn('oferte', 'operator_id')) {
                $table->foreignId('operator_id')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('oferte', function (Blueprint $table): void {
            if (Schema::hasColumn('oferte', 'operator_id')) {
                $table->dropColumn('operator_id');
            }
        });

        Schema::table('vanzari_1c', function (Blueprint $table): void {
            if (Schema::hasColumn('vanzari_1c', 'operator_id')) {
                $table->dropColumn('operator_id');
            }
        });
    }
};
