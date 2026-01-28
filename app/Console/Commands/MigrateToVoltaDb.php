<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class MigrateToVoltaDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:to-volta-db {--force : Force migration without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrează toate datele din bazele multiple într-o singură bază de date volta_db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Migrare date în volta_db ===');
        $this->newLine();

        if (!$this->option('force')) {
            if (!$this->confirm('Această operațiune va migra toate datele în volta_db. Continui?', true)) {
                $this->info('Operațiune anulată.');
                return 0;
            }
        }

        try {
            // Obținem configurațiile pentru bazele existente din variabilele de mediu
            // Dacă conexiunile nu mai există în config, le construim din env
            $dbHost = env('DB_HOST', '127.0.0.1');
            $dbPort = env('DB_PORT', '3306');
            $dbUser = env('DB_USERNAME', 'root');
            $dbPass = env('DB_PASSWORD', '');
            
            $connections = [];
            
            // Dashboard
            $dashboardDb = env('DB_DATABASE_DASHBOARD', 'dashboard_db');
            if ($dashboardDb) {
                $connections['dashboard'] = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $dashboardDb,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ];
            }
            
            // Vanzari
            $vanzariDb = env('DB_DATABASE_VANZARI', 'vanzari_1c_db');
            if ($vanzariDb) {
                $connections['vanzari'] = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $vanzariDb,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ];
            }
            
            // Trafic
            $traficDb = env('DB_DATABASE_TRAFIC', 'trafic_db');
            if ($traficDb) {
                $connections['trafic'] = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $traficDb,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ];
            }
            
            // Produse
            $produseDb = env('DB_DATABASE_PRODUSE', 'produse_db');
            if ($produseDb) {
                $connections['produse'] = [
                    'driver' => 'mysql',
                    'host' => $dbHost,
                    'port' => $dbPort,
                    'database' => $produseDb,
                    'username' => $dbUser,
                    'password' => $dbPass,
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                ];
            }
            
            if (empty($connections)) {
                $this->warn('Nu s-au găsit baze de date de migrat.');
                $this->line('Verifică că variabilele de mediu DB_DATABASE_* sunt setate în .env');
                $this->line('Sau că bazele de date există cu numele default:');
                $this->line('  - dashboard_db');
                $this->line('  - vanzari_1c_db');
                $this->line('  - trafic_db');
                $this->line('  - produse_db');
                return 1;
            }

            // Configurăm conexiunea temporară pentru volta_db
            $voltaConfig = [
                'driver' => 'mysql',
                'host' => $dbHost,
                'port' => $dbPort,
                'database' => 'volta_db',
                'username' => $dbUser,
                'password' => $dbPass,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ];

            Config::set('database.connections.volta_temp', $voltaConfig);
            
            // Configurăm temporar conexiunile pentru bazele sursă
            foreach ($connections as $name => $config) {
                Config::set("database.connections.{$name}", $config);
            }

            // Pasul 1: Verificăm dacă volta_db există deja
            $this->info('Pasul 1: Verificare baza de date volta_db...');
            try {
                DB::connection('volta_temp')->getPdo();
                $existingTables = DB::connection('volta_temp')->select("SHOW TABLES");
                $tableCount = count($existingTables);
                
                if ($tableCount > 0) {
                    $this->warn("⚠ volta_db există deja cu {$tableCount} tabele!");
                    if (!$this->option('force')) {
                        if (!$this->confirm('Continui cu migrarea? Datele existente vor fi păstrate și completate cu datele din bazele sursă.', false)) {
                            $this->info('Operațiune anulată.');
                            return 0;
                        }
                    }
                    $this->info('✓ Folosind volta_db existentă.');
                } else {
                    $this->info('✓ volta_db există dar este goală.');
                }
            } catch (\Exception $e) {
                // Baza de date nu există, o creăm
                $this->info('Creare baza de date volta_db...');
                $this->createDatabase($voltaConfig);
                $this->info('✓ Baza de date volta_db a fost creată.');
            }

            // Pasul 2: Creăm tabelele în volta_db
            $this->newLine();
            $this->info('Pasul 2: Creare tabele în volta_db...');
            $this->createTables($voltaConfig);
            $this->info('✓ Toate tabelele au fost create.');

            // Pasul 3: Migrăm datele
            $this->newLine();
            $this->info('Pasul 3: Migrare date...');

            // Migrăm din dashboard
            if (isset($connections['dashboard'])) {
                $this->migrateData('dashboard', $connections['dashboard'], $voltaConfig, [
                    'users',
                    'password_reset_tokens',
                    'sessions',
                    'operatori',
                    'oferte',
                ]);
            }

            // Migrăm din vanzari
            if (isset($connections['vanzari'])) {
                $this->migrateData('vanzari', $connections['vanzari'], $voltaConfig, [
                    'vanzari_1c',
                    'plan_vanzari',
                    'date_op',
                ]);
            }

            // Migrăm din trafic
            if (isset($connections['trafic'])) {
                $this->migrateData('trafic', $connections['trafic'], $voltaConfig, [
                    'traffic_sources',
                ]);
            }

            // Migrăm din produse
            if (isset($connections['produse'])) {
                $this->migrateData('produse', $connections['produse'], $voltaConfig, [
                    'produse',
                ]);
            }

            $this->info('✓ Toate datele au fost migrate cu succes!');
            $this->newLine();
            $this->info('=== Migrare completă! ===');
            $this->newLine();
            $this->warn('IMPORTANT: Acum trebuie să:');
            $this->line('1. Actualizezi fișierul .env să folosească DB_DATABASE=volta_db');
            $this->line('2. Rulezi: php artisan migrate:update-connections');
            $this->line('3. Verifici că toate datele sunt prezente în volta_db');

            return 0;
        } catch (\Exception $e) {
            $this->error('Eroare la migrare: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }

    /**
     * Creează baza de date
     */
    private function createDatabase($config)
    {
        $connection = [
            'driver' => 'mysql',
            'host' => $config['host'],
            'port' => $config['port'],
            'database' => null, // Conectăm fără bază de date
            'username' => $config['username'],
            'password' => $config['password'],
        ];

        Config::set('database.connections.temp_create', $connection);
        
        DB::connection('temp_create')->statement("CREATE DATABASE IF NOT EXISTS `{$config['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    }

    /**
     * Creează toate tabelele în volta_db
     */
    private function createTables($voltaConfig)
    {
        DB::connection('volta_temp')->statement("USE `{$voltaConfig['database']}`");

        // Users table
        if (!Schema::connection('volta_temp')->hasTable('users')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `users` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `name` varchar(255) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `username` varchar(255) NOT NULL,
                    `password` varchar(255) DEFAULT NULL,
                    `password_hash` varchar(255) DEFAULT NULL,
                    `role` varchar(255) DEFAULT NULL,
                    `country` varchar(255) DEFAULT NULL,
                    `currency` varchar(10) DEFAULT NULL,
                    `language` varchar(50) DEFAULT NULL,
                    `email_verified_at` timestamp NULL DEFAULT NULL,
                    `remember_token` varchar(100) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `users_username_unique` (`username`),
                    UNIQUE KEY `users_email_unique` (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } else {
            // Adaugă coloanele lipsă dacă tabelul există deja
            try {
                DB::connection('volta_temp')->statement("
                    ALTER TABLE `users` 
                    ADD COLUMN IF NOT EXISTS `country` varchar(255) DEFAULT NULL AFTER `role`,
                    ADD COLUMN IF NOT EXISTS `currency` varchar(10) DEFAULT NULL AFTER `country`,
                    ADD COLUMN IF NOT EXISTS `language` varchar(50) DEFAULT NULL AFTER `currency`
                ");
            } catch (\Exception $e) {
                // Coloanele pot exista deja sau pot fi erori de sintaxă MySQL
                // Încercăm cu sintaxă compatibilă
                try {
                    $columns = DB::connection('volta_temp')->select("SHOW COLUMNS FROM `users` LIKE 'country'");
                    if (empty($columns)) {
                        DB::connection('volta_temp')->statement("ALTER TABLE `users` ADD COLUMN `country` varchar(255) DEFAULT NULL AFTER `role`");
                    }
                } catch (\Exception $e2) {}
                
                try {
                    $columns = DB::connection('volta_temp')->select("SHOW COLUMNS FROM `users` LIKE 'currency'");
                    if (empty($columns)) {
                        DB::connection('volta_temp')->statement("ALTER TABLE `users` ADD COLUMN `currency` varchar(10) DEFAULT NULL AFTER `country`");
                    }
                } catch (\Exception $e2) {}
                
                try {
                    $columns = DB::connection('volta_temp')->select("SHOW COLUMNS FROM `users` LIKE 'language'");
                    if (empty($columns)) {
                        DB::connection('volta_temp')->statement("ALTER TABLE `users` ADD COLUMN `language` varchar(50) DEFAULT NULL AFTER `currency`");
                    }
                } catch (\Exception $e2) {}
            }
        }

        // Password reset tokens
        if (!Schema::connection('volta_temp')->hasTable('password_reset_tokens')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
                    `email` varchar(255) NOT NULL,
                    `token` varchar(255) NOT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`email`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Sessions
        if (!Schema::connection('volta_temp')->hasTable('sessions')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `sessions` (
                    `id` varchar(255) NOT NULL,
                    `user_id` bigint unsigned DEFAULT NULL,
                    `ip_address` varchar(45) DEFAULT NULL,
                    `user_agent` text,
                    `payload` longtext NOT NULL,
                    `last_activity` int NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `sessions_user_id_index` (`user_id`),
                    KEY `sessions_last_activity_index` (`last_activity`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Operatori
        if (!Schema::connection('volta_temp')->hasTable('operatori')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `operatori` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `nume` varchar(255) NOT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `telefon` varchar(50) DEFAULT NULL,
                    `data_angajare` date DEFAULT NULL,
                    `adresa` text,
                    `departament` varchar(100) DEFAULT NULL,
                    `functie` varchar(100) DEFAULT NULL,
                    `observatii` text,
                    `activ` tinyint(1) NOT NULL DEFAULT '1',
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_nume` (`nume`),
                    KEY `idx_activ` (`activ`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Oferte
        if (!Schema::connection('volta_temp')->hasTable('oferte')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `oferte` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `operator` varchar(255) NOT NULL,
                    `operator_id` bigint unsigned DEFAULT NULL,
                    `status` enum('trimise','finalizate','refuzate') NOT NULL DEFAULT 'trimise',
                    `data_trimisa` date NOT NULL,
                    `data_finalizata` date DEFAULT NULL,
                    `valoare` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `observatii` text,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_operator` (`operator`),
                    KEY `idx_status` (`status`),
                    KEY `idx_data_trimisa` (`data_trimisa`),
                    KEY `oferte_operator_id_foreign` (`operator_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Vanzari 1C
        if (!Schema::connection('volta_temp')->hasTable('vanzari_1c')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `vanzari_1c` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `operator_id` bigint unsigned DEFAULT NULL,
                    `data` date NOT NULL,
                    `suma_fara_tva` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `suma_cu_tva` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `profit` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `nr_vanzari` int DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_vanzare` (`data`),
                    KEY `idx_data` (`data`),
                    KEY `vanzari_1c_operator_id_foreign` (`operator_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Plan Vanzari
        if (!Schema::connection('volta_temp')->hasTable('plan_vanzari')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `plan_vanzari` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `an` int NOT NULL,
                    `luna` varchar(20) NOT NULL,
                    `valoare` decimal(15,2) NOT NULL DEFAULT '0.00',
                    PRIMARY KEY (`id`),
                    KEY `plan_vanzari_an_luna_index` (`an`,`luna`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Date OP
        if (!Schema::connection('volta_temp')->hasTable('date_op')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `date_op` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `operator_id` bigint unsigned DEFAULT NULL,
                    `data` date NOT NULL,
                    `suma_fara_tva` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `suma_cu_tva` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `profit` decimal(15,2) NOT NULL DEFAULT '0.00',
                    `nr_vanzari` int DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `date_op_operator_id_foreign` (`operator_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Traffic Sources
        if (!Schema::connection('volta_temp')->hasTable('traffic_sources')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `traffic_sources` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `source` varchar(50) NOT NULL,
                    `date` date NOT NULL,
                    `visits` int NOT NULL DEFAULT '0',
                    `new_users` int DEFAULT NULL,
                    `returning_users` int DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `unique_source_date` (`source`,`date`),
                    KEY `idx_date` (`date`),
                    KEY `idx_source` (`source`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Produse
        if (!Schema::connection('volta_temp')->hasTable('produse')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `produse` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `nume` varchar(255) NOT NULL,
                    `cod` varchar(255) DEFAULT NULL,
                    `pret` decimal(10,2) DEFAULT NULL,
                    `categorie` varchar(255) DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }

        // Migrations table
        if (!Schema::connection('volta_temp')->hasTable('migrations')) {
            DB::connection('volta_temp')->statement("
                CREATE TABLE IF NOT EXISTS `migrations` (
                    `id` int unsigned NOT NULL AUTO_INCREMENT,
                    `migration` varchar(255) NOT NULL,
                    `batch` int NOT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        }
    }

    /**
     * Migrează datele dintr-o conexiune în volta_db
     */
    private function migrateData($connectionName, $sourceConfig, $targetConfig, $tables)
    {
        $this->line("  Migrare din conexiunea '{$connectionName}'...");

        foreach ($tables as $table) {
            try {
                // Verificăm dacă tabelul există în sursă
                if (!Schema::connection($connectionName)->hasTable($table)) {
                    $this->warn("    ⚠ Tabelul '{$table}' nu există în '{$connectionName}', se sare peste.");
                    continue;
                }

                // Obținem coloanele din sursă și target
                $sourceColumns = DB::connection($connectionName)->select("SHOW COLUMNS FROM `{$table}`");
                $sourceColumnNames = array_map(function($col) {
                    return $col->Field;
                }, $sourceColumns);
                
                $targetColumns = DB::connection('volta_temp')->select("SHOW COLUMNS FROM `{$table}`");
                $targetColumnNames = array_map(function($col) {
                    return $col->Field;
                }, $targetColumns);
                
                // Adaugă coloanele lipsă în target
                foreach ($sourceColumns as $col) {
                    if (!in_array($col->Field, $targetColumnNames)) {
                        try {
                            $null = $col->Null === 'YES' ? 'NULL' : 'NOT NULL';
                            $default = $col->Default !== null ? "DEFAULT '{$col->Default}'" : ($col->Null === 'YES' ? 'DEFAULT NULL' : '');
                            DB::connection('volta_temp')->statement("ALTER TABLE `{$table}` ADD COLUMN `{$col->Field}` {$col->Type} {$null} {$default}");
                            $this->line("    + Coloana '{$col->Field}' adăugată în target");
                        } catch (\Exception $e) {
                            $this->warn("    ⚠ Nu s-a putut adăuga coloana '{$col->Field}': " . $e->getMessage());
                        }
                    }
                }
                
                // Folosim doar coloanele comune care există în ambele tabele
                $columnNames = array_intersect($sourceColumnNames, $targetColumnNames);
                
                $data = DB::connection($connectionName)->table($table)->get();
                $count = $data->count();

                if ($count > 0) {
                    // Verificăm dacă tabelul din target are date
                    $existingCount = DB::connection('volta_temp')->table($table)->count();
                    
                    if ($existingCount > 0) {
                        $this->warn("    ⚠ Tabelul '{$table}' are deja {$existingCount} înregistrări în volta_db.");
                        if (!$this->option('force')) {
                            if (!$this->confirm("    Suprascri datele existente pentru '{$table}'?", false)) {
                                $this->line("    - Tabelul '{$table}': Sărit (păstrând datele existente).");
                                continue;
                            }
                        }
                        // Pentru tabele cu foreign keys, folosim DELETE în loc de TRUNCATE
                        DB::connection('volta_temp')->statement('SET FOREIGN_KEY_CHECKS=0');
                        try {
                            DB::connection('volta_temp')->table($table)->truncate();
                        } catch (\Exception $e) {
                            // Dacă truncate eșuează (din cauza foreign keys), folosim DELETE
                            DB::connection('volta_temp')->table($table)->delete();
                        }
                        DB::connection('volta_temp')->statement('SET FOREIGN_KEY_CHECKS=1');
                    }

                    // Convertim datele în array-uri asociative cu numele coloanelor corecte
                    // Folosim DOAR coloanele comune (care există în ambele tabele)
                    $insertData = [];
                    foreach ($data as $row) {
                        $rowArray = [];
                        foreach ($columnNames as $colName) {
                            $rowArray[$colName] = $row->$colName ?? null;
                        }
                        $insertData[] = $rowArray;
                    }

                    // Inserăm datele în target
                    // Dezactivăm temporar strict mode și foreign key checks
                    DB::connection('volta_temp')->statement('SET FOREIGN_KEY_CHECKS=0');
                    DB::connection('volta_temp')->statement('SET sql_mode=""');
                    
                    $chunks = array_chunk($insertData, 100);
                    $inserted = 0;
                    $skipped = 0;
                    foreach ($chunks as $chunk) {
                        try {
                            // Folosim INSERT IGNORE pentru a ignora duplicatele
                            $values = [];
                            $columns = array_keys($chunk[0]);
                            $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                            
                            foreach ($chunk as $row) {
                                $values = array_merge($values, array_values($row));
                            }
                            
                            $sql = 'INSERT IGNORE INTO `' . $table . '` (`' . implode('`,`', $columns) . '`) VALUES ';
                            $sql .= implode(',', array_fill(0, count($chunk), $placeholders));
                            
                            DB::connection('volta_temp')->statement($sql, $values);
                            $inserted += count($chunk);
                        } catch (\Exception $e) {
                            // Dacă INSERT IGNORE eșuează, încercăm cu insert normal
                            try {
                                DB::connection('volta_temp')->table($table)->insert($chunk);
                                $inserted += count($chunk);
                            } catch (\Exception $e2) {
                                $skipped += count($chunk);
                                $this->warn("    ⚠ Nu s-au putut insera " . count($chunk) . " înregistrări: " . $e2->getMessage());
                            }
                        }
                    }
                    
                    // Reactivăm strict mode și foreign key checks
                    DB::connection('volta_temp')->statement('SET FOREIGN_KEY_CHECKS=1');
                    DB::connection('volta_temp')->statement('SET sql_mode="STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION"');
                    
                    if ($skipped > 0) {
                        $this->warn("    ⚠ {$skipped} înregistrări omise (duplicate sau erori)");
                    }
                    $this->info("    ✓ Tabelul '{$table}': {$inserted} înregistrări migrate.");
                } else {
                    $this->line("    - Tabelul '{$table}': 0 înregistrări (gol).");
                }
            } catch (\Exception $e) {
                $this->error("    ✗ Eroare la migrarea tabelului '{$table}': " . $e->getMessage());
            }
        }
    }
}
