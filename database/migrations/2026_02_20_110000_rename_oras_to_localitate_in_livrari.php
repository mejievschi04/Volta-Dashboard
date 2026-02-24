<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('livrari', 'oras') && ! Schema::hasColumn('livrari', 'localitate')) {
            DB::statement('ALTER TABLE livrari CHANGE oras localitate VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('livrari', 'localitate') && ! Schema::hasColumn('livrari', 'oras')) {
            DB::statement('ALTER TABLE livrari CHANGE localitate oras VARCHAR(255) NULL');
        }
    }
};
