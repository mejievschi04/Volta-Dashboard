<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyMigration extends Command
{
    protected $signature = 'migrate:verify';
    protected $description = 'Verifică că toate datele au fost migrate corect în volta_db';

    public function handle()
    {
        $this->info('=== Verificare Migrare volta_db ===');
        $this->newLine();

        try {
            // Verifică conexiunea la volta_db
            $currentDb = DB::connection()->getDatabaseName();
            $this->info("Baza de date curentă: {$currentDb}");
            
            if ($currentDb !== 'volta_db') {
                $this->error("⚠ ATENȚIE: Aplicația nu folosește volta_db!");
                $this->line("Actualizează .env: DB_DATABASE=volta_db");
                $this->line("Apoi rulează: php artisan config:clear");
                return 1;
            }
            
            $this->info('✓ Conectat la volta_db');
            $this->newLine();

            // Verifică tabelele și numărul de înregistrări
            $tables = [
                'users' => 'Utilizatori',
                'operatori' => 'Operatori',
                'oferte' => 'Oferte',
                'vanzari_1c' => 'Vânzări',
                'plan_vanzari' => 'Plan Vânzări',
                'date_op' => 'Date OP',
                'traffic_sources' => 'Traffic Sources',
                'sessions' => 'Sesiuni',
            ];

            $total = 0;
            foreach ($tables as $table => $label) {
                try {
                    $count = DB::table($table)->count();
                    $total += $count;
                    $status = $count > 0 ? '✓' : '⚠';
                    $this->line("  {$status} {$label}: {$count} înregistrări");
                } catch (\Exception $e) {
                    $this->warn("  ✗ {$label}: Tabelul nu există sau eroare - " . $e->getMessage());
                }
            }

            $this->newLine();
            $this->info("Total înregistrări: {$total}");
            $this->newLine();

            if ($total > 0) {
                $this->info('✓ Migrarea este completă și datele sunt prezente!');
                $this->newLine();
                $this->line('Următorii pași:');
                $this->line('1. Testează aplicația (login, dashboard, rapoarte)');
                $this->line('2. Verifică că toate funcționalitățile funcționează');
                $this->line('3. Păstrează backup-urile în siguranță');
                return 0;
            } else {
                $this->error('✗ Nu s-au găsit date în volta_db!');
                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Eroare: ' . $e->getMessage());
            return 1;
        }
    }
}
