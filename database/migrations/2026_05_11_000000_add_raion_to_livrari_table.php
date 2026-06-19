<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('livrari') || Schema::hasColumn('livrari', 'raion')) {
            return;
        }

        Schema::table('livrari', function (Blueprint $table) {
            $table->string('raion', 255)->nullable()->after('localitate');
        });

        // Raioanele pentru datele vechi se completeaza separat cu:
        // php artisan livrari:backfill-raioane
        // Nu copiem localitatea in raion, pentru ca ar strica localitatile vechi.
    }

    public function down(): void
    {
        if (! Schema::hasTable('livrari') || ! Schema::hasColumn('livrari', 'raion')) {
            return;
        }

        Schema::table('livrari', function (Blueprint $table) {
            $table->dropColumn('raion');
        });
    }
};
