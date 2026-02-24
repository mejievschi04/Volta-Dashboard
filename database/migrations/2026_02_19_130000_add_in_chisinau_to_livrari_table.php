<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            if (! Schema::hasColumn('livrari', 'in_chisinau')) {
                $table->boolean('in_chisinau')->default(true)->after('data_livrarii');
            }
        });
    }

    public function down(): void
    {
        Schema::table('livrari', function (Blueprint $table) {
            $table->dropColumn('in_chisinau');
        });
    }
};
