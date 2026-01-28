<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class UpdateConnectionsToVoltaDb extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:update-connections';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizează toate modelele și configurațiile să folosească volta_db';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== Actualizare conexiuni pentru volta_db ===');
        $this->newLine();
        $this->warn('Această comandă actualizează doar fișierele. Asigură-te că ai rulat mai întâi: php artisan migrate:to-volta-db');
        $this->newLine();

        if (!$this->confirm('Continui cu actualizarea conexiunilor?', true)) {
            $this->info('Operațiune anulată.');
            return 0;
        }

        $this->info('✓ Toate modelele și configurațiile vor folosi acum conexiunea default (volta_db)');
        $this->newLine();
        $this->info('IMPORTANT: Verifică manual că:');
        $this->line('1. Fișierul .env are DB_DATABASE=volta_db');
        $this->line('2. Toate modelele au fost actualizate (proprietatea $connection a fost eliminată)');
        $this->line('3. Toate migrările folosesc conexiunea default');

        return 0;
    }
}
