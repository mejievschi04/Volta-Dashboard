<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('vanzari')->hasTable('vanzari_1c')) {
            Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
                // Șterge vechiul constraint unique pe 'data'
                $table->dropUnique('unique_vanzare');
            });
            
            // Adaugă noul constraint unique pe (operator_id, data)
            // Folosim DB::statement pentru că Blueprint nu suportă direct unique pe multiple coloane cu nume custom
            DB::connection('vanzari')->statement('
                ALTER TABLE vanzari_1c 
                ADD UNIQUE KEY unique_vanzare_operator_data (operator_id, data)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('vanzari')->hasTable('vanzari_1c')) {
            // Șterge noul constraint
            DB::connection('vanzari')->statement('
                ALTER TABLE vanzari_1c 
                DROP INDEX unique_vanzare_operator_data
            ');
            
            Schema::connection('vanzari')->table('vanzari_1c', function (Blueprint $table) {
                // Restaurează vechiul constraint pe 'data'
                $table->unique('data', 'unique_vanzare');
            });
        }
    }
};
