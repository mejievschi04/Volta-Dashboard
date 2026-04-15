<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('livrari', 'oras') && ! Schema::hasColumn('livrari', 'localitate')) {
            Schema::table('livrari', function (Blueprint $table): void {
                $table->renameColumn('oras', 'localitate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('livrari', 'localitate') && ! Schema::hasColumn('livrari', 'oras')) {
            Schema::table('livrari', function (Blueprint $table): void {
                $table->renameColumn('localitate', 'oras');
            });
        }
    }
};
