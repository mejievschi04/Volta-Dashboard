<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckMigrationReadiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:check-readiness';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifică dacă sistemul este pregătit pentru migrarea la volta_db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Verificare Pregătire Migrare ===');
        $this->newLine();

        $allOk = true;
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');

        // Verifică conexiunea la MySQL
        $this->info('1. Verificare conexiune MySQL...');
        try {
            $connection = [
                'driver' => 'mysql',
                'host' => $dbHost,
                'port' => $dbPort,
                'database' => null,
                'username' => $dbUser,
                'password' => $dbPass,
            ];
            
            \Config::set('database.connections.temp_check', $connection);
            DB::connection('temp_check')->getPdo();
            $this->info('   ✓ Conexiune MySQL OK');
        } catch (\Exception $e) {
            $this->error('   ✗ Eroare conexiune MySQL: ' . $e->getMessage());
            $allOk = false;
        }
        $this->newLine();

        // Verifică bazele de date existente
        $this->info('2. Verificare baze de date existente...');
        $databases = [
            'dashboard' => env('DB_DATABASE_DASHBOARD', 'dashboard_db'),
            'vanzari' => env('DB_DATABASE_VANZARI', 'vanzari_1c_db'),
            'trafic' => env('DB_DATABASE_TRAFIC', 'trafic_db'),
            'produse' => env('DB_DATABASE_PRODUSE', 'produse_db'),
        ];

        $foundDatabases = [];
        foreach ($databases as $name => $dbName) {
            try {
                $connection = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $dbName,
                    'username' => $dbUser,
                    'password' => $dbPass,
                ];
                
                \Config::set("database.connections.check_{$name}", $connection);
                DB::connection("check_{$name}")->getPdo();
                
                // Verifică tabelele
                $tables = DB::connection("check_{$name}")->select("SHOW TABLES");
                $tableCount = count($tables);
                
                $foundDatabases[$name] = [
                    'name' => $dbName,
                    'tables' => $tableCount,
                ];
                
                $this->info("   ✓ {$dbName}: {$tableCount} tabele");
            } catch (\Exception $e) {
                $this->warn("   ⚠ {$dbName}: Nu există sau nu este accesibilă");
            }
        }
        $this->newLine();

        // Verifică dacă volta_db există deja
        $this->info('3. Verificare volta_db...');
        try {
            $connection = [
                'driver' => 'mysql',
                'host' => $dbHost,
                'port' => $dbPort,
                'database' => 'volta_db',
                'username' => $dbUser,
                'password' => $dbPass,
            ];
            
            \Config::set('database.connections.check_volta', $connection);
            DB::connection('check_volta')->getPdo();
            
            $tables = DB::connection('check_volta')->select("SHOW TABLES");
            $tableCount = count($tables);
            
            if ($tableCount > 0) {
                $this->warn("   ⚠ volta_db există deja cu {$tableCount} tabele");
                $this->warn("   ⚠ ATENȚIE: Migrarea va suprascrie datele existente!");
            } else {
                $this->info('   ✓ volta_db există dar este goală');
            }
        } catch (\Exception $e) {
            $this->info('   ✓ volta_db nu există (va fi creată)');
        }
        $this->newLine();

        // Rezumat
        $this->info('=== Rezumat ===');
        if (!empty($foundDatabases)) {
            $this->line('Baze de date găsite:');
            foreach ($foundDatabases as $name => $info) {
                $this->line("  - {$info['name']}: {$info['tables']} tabele");
            }
        } else {
            $this->warn('Nu s-au găsit baze de date de migrat!');
            $allOk = false;
        }
        $this->newLine();

        if ($allOk && !empty($foundDatabases)) {
            $this->info('✓ Sistemul este pregătit pentru migrare!');
            $this->newLine();
            $this->line('Următorii pași:');
            $this->line('1. Fă backup la toate bazele de date existente');
            $this->line('2. Rulează: php artisan migrate:to-volta-db');
            $this->line('3. Actualizează .env: DB_DATABASE=volta_db');
        } else {
            $this->error('✗ Sistemul NU este pregătit pentru migrare!');
            $this->line('Verifică erorile de mai sus și încearcă din nou.');
        }

        return $allOk ? 0 : 1;
    }
}
