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
        // Facem coloanele problematice nullable sau setăm default-uri
        $alterations = [
            // Coloane care pot fi nullable
            "ALTER TABLE `users` MODIFY COLUMN `prenume` varchar(100) DEFAULT NULL",
            "ALTER TABLE `users` MODIFY COLUMN `telefon` varchar(20) DEFAULT NULL",
            "ALTER TABLE `users` MODIFY COLUMN `parola` varchar(255) DEFAULT NULL",
            
            // Coloane care trebuie să aibă default-uri dacă nu pot fi nullable
            // username și password_hash trebuie să rămână NOT NULL dar cu default-uri
        ];

        foreach ($alterations as $sql) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // Ignorăm erorile dacă coloana nu există sau deja are valoarea dorită
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nu facem rollback pentru a nu afecta datele existente
    }
};
