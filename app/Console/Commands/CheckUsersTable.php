<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckUsersTable extends Command
{
    protected $signature = 'check:users-table';
    protected $description = 'Verifică structura tabelului users';

    public function handle()
    {
        $this->info('Verificare structură tabel users...');
        $this->newLine();

        try {
            $columns = DB::select("SHOW COLUMNS FROM `users`");
            
            $this->table(
                ['Coloană', 'Tip', 'Null', 'Default', 'Extra'],
                array_map(function($col) {
                    return [
                        $col->Field,
                        $col->Type,
                        $col->Null,
                        $col->Default ?? 'NULL',
                        $col->Extra ?? ''
                    ];
                }, $columns)
            );
            
            // Verifică specific coloanele problematice
            $numeCol = array_filter($columns, fn($c) => $c->Field === 'nume');
            $nameCol = array_filter($columns, fn($c) => $c->Field === 'name');
            
            $this->newLine();
            if (!empty($numeCol)) {
                $nume = reset($numeCol);
                $this->warn("Coloana 'nume' există:");
                $this->line("  - Null: {$nume->Null}");
                $this->line("  - Default: " . ($nume->Default ?? 'NULL'));
            } else {
                $this->info("✓ Coloana 'nume' nu există (OK)");
            }
            
            if (!empty($nameCol)) {
                $name = reset($nameCol);
                $this->info("Coloana 'name' există:");
                $this->line("  - Null: {$name->Null}");
                $this->line("  - Default: " . ($name->Default ?? 'NULL'));
            } else {
                $this->error("✗ Coloana 'name' nu există!");
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Eroare: ' . $e->getMessage());
            return 1;
        }
    }
}
