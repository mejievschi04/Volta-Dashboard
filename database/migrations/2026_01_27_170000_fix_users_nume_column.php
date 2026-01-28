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
        if (Schema::hasTable('users')) {
            // Verifică dacă coloana 'nume' există
            $columns = DB::select("SHOW COLUMNS FROM `users` LIKE 'nume'");
            
            if (!empty($columns)) {
                // Coloana 'nume' există - o facem nullable sau o ștergem
                // Mai întâi, copiem datele din 'nume' în 'name' dacă 'name' este NULL
                DB::statement("
                    UPDATE `users` 
                    SET `name` = `nume` 
                    WHERE `name` IS NULL AND `nume` IS NOT NULL AND `nume` != ''
                ");
                
                // Acum facem coloana 'nume' nullable
                try {
                    DB::statement("ALTER TABLE `users` MODIFY COLUMN `nume` varchar(255) DEFAULT NULL");
                } catch (\Exception $e) {
                    // Dacă nu funcționează, încercăm să ștergem coloana
                    try {
                        DB::statement("ALTER TABLE `users` DROP COLUMN `nume`");
                    } catch (\Exception $e2) {
                        // Ignorăm eroarea dacă coloana nu poate fi ștearsă
                    }
                }
            }
            
            // Asigurăm că coloana 'name' există și este nullable
            $nameColumns = DB::select("SHOW COLUMNS FROM `users` LIKE 'name'");
            if (empty($nameColumns)) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('name')->nullable()->after('id');
                });
            } else {
                // Facem sigur că 'name' este nullable
                try {
                    DB::statement("ALTER TABLE `users` MODIFY COLUMN `name` varchar(255) DEFAULT NULL");
                } catch (\Exception $e) {
                    // Ignorăm dacă deja este nullable
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nu facem nimic la rollback pentru a nu pierde date
    }
};
