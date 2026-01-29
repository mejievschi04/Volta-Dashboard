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
        $tables = DB::select("SHOW TABLES LIKE 'traffic_sources'");
        
        if (!empty($tables)) {
            // Check if columns exist using DB query
            $newUsersColumns = DB::select("SHOW COLUMNS FROM `traffic_sources` LIKE 'new_users'");
            $returningUsersColumns = DB::select("SHOW COLUMNS FROM `traffic_sources` LIKE 'returning_users'");
            
            if (empty($newUsersColumns)) {
                try {
                    DB::statement("ALTER TABLE `traffic_sources` ADD COLUMN `new_users` INT DEFAULT 0 AFTER `visits`");
                } catch (\Exception $e) {
                    // Ignore if column already exists or other error
                }
            }
            
            if (empty($returningUsersColumns)) {
                try {
                    DB::statement("ALTER TABLE `traffic_sources` ADD COLUMN `returning_users` INT DEFAULT 0 AFTER `new_users`");
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
        $tables = DB::select("SHOW TABLES LIKE 'traffic_sources'");
        
        if (!empty($tables)) {
            // Check if columns exist using DB query
            $newUsersColumns = DB::select("SHOW COLUMNS FROM `traffic_sources` LIKE 'new_users'");
            $returningUsersColumns = DB::select("SHOW COLUMNS FROM `traffic_sources` LIKE 'returning_users'");
            
            if (!empty($newUsersColumns)) {
                try {
                    DB::statement("ALTER TABLE `traffic_sources` DROP COLUMN `new_users`");
                } catch (\Exception $e) {
                    // Ignore if column doesn't exist or other error
                }
            }
            
            if (!empty($returningUsersColumns)) {
                try {
                    DB::statement("ALTER TABLE `traffic_sources` DROP COLUMN `returning_users`");
                } catch (\Exception $e) {
                    // Ignore if column doesn't exist or other error
                }
            }
        }
    }
};
