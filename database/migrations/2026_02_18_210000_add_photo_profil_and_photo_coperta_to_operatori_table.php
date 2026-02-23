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
        Schema::table('operatori', function (Blueprint $table) {
            $table->string('photo_profil', 500)->nullable()->after('observatii');
            $table->string('photo_coperta', 500)->nullable()->after('photo_profil');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('operatori', function (Blueprint $table) {
            $table->dropColumn(['photo_profil', 'photo_coperta']);
        });
    }
};
