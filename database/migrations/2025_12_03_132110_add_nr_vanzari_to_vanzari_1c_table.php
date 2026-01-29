<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists using DB query
        $tables = DB::select("SHOW TABLES LIKE 'vanzari_1c'");
        
        if (!empty($tables)) {
            // Check if column exists using DB query
            $columns = DB::select("SHOW COLUMNS FROM `vanzari_1c` LIKE 'nr_vanzari'");
            
            if (empty($columns)) {
                // Add column using raw SQL
                try {
                    DB::statement("ALTER TABLE `vanzari_1c` ADD COLUMN `nr_vanzari` INT DEFAULT 0 AFTER `profit`");
                } catch (\Exception $e) {
                    // Ignore if column already exists or other error
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Check if table exists using DB query
        $tables = DB::select("SHOW TABLES LIKE 'vanzari_1c'");
        
        if (!empty($tables)) {
            // Check if column exists using DB query
            $columns = DB::select("SHOW COLUMNS FROM `vanzari_1c` LIKE 'nr_vanzari'");
            
            if (!empty($columns)) {
                // Drop column using raw SQL
                try {
                    DB::statement("ALTER TABLE `vanzari_1c` DROP COLUMN `nr_vanzari`");
                } catch (\Exception $e) {
                    // Ignore if column doesn't exist or other error
                }
            }
        }
    }
};
