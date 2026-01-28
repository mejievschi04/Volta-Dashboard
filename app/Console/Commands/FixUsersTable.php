<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixUsersTable extends Command
{
    protected $signature = 'migrate:fix-users-table';
    protected $description = 'Adaugă coloanele lipsă în tabelul users';

    public function handle()
    {
        $this->info('Adăugare coloane lipsă în tabelul users...');
        
        try {
            // Verifică și adaugă coloanele
            $columns = DB::select("SHOW COLUMNS FROM `users`");
            $existingColumns = array_map(function($col) {
                return $col->Field;
            }, $columns);
            
            if (!in_array('country', $existingColumns)) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `country` varchar(255) DEFAULT NULL AFTER `role`");
                $this->info('✓ Coloana "country" adăugată');
            } else {
                $this->line('- Coloana "country" există deja');
            }
            
            if (!in_array('currency', $existingColumns)) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `currency` varchar(10) DEFAULT NULL AFTER `country`");
                $this->info('✓ Coloana "currency" adăugată');
            } else {
                $this->line('- Coloana "currency" există deja');
            }
            
            if (!in_array('language', $existingColumns)) {
                DB::statement("ALTER TABLE `users` ADD COLUMN `language` varchar(50) DEFAULT NULL AFTER `currency`");
                $this->info('✓ Coloana "language" adăugată');
            } else {
                $this->line('- Coloana "language" există deja');
            }
            
            $this->info('✓ Tabelul users a fost actualizat!');
            return 0;
        } catch (\Exception $e) {
            $this->error('Eroare: ' . $e->getMessage());
            return 1;
        }
    }
}
