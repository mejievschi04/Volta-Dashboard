<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            if (! Schema::hasColumn('livrari', 'oras')) {
                $table->string('oras', 255)->nullable()->after('adresa_livrarii');
            }
        });
    }

    public function down(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            $table->dropColumn('oras');
        });
    }
};
