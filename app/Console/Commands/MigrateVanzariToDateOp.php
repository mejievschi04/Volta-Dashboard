<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\DateOp;
use App\Models\Vanzari;
use Illuminate\Support\Facades\DB;

class MigrateVanzariToDateOp extends Command
{
    protected $signature = 'migrate:vanzari-to-dateop';
    protected $description = 'Migrate data from vanzari_1c to date_op table';

    public function handle()
    {
        $this->info('Starting migration from vanzari_1c to date_op...');

        try {
            // Get all data from vanzari_1c
            $vanzari = Vanzari::all();

            if ($vanzari->isEmpty()) {
                $this->warn('No data found in vanzari_1c table.');
                return;
            }

            $count = 0;
            foreach ($vanzari as $v) {
                // Check if the record already exists in date_op
                $exists = DateOp::where('operator_id', $v->operator_id)
                    ->where('data', $v->data)
                    ->exists();

                if (!$exists) {
                    DateOp::create([
                        'operator_id' => $v->operator_id,
                        'data' => $v->data,
                        'suma_fara_tva' => $v->suma_fara_tva,
                        'suma_cu_tva' => $v->suma_cu_tva,
                        'profit' => $v->profit,
                        'nr_vanzari' => $v->nr_vanzari ?? 0,
                    ]);
                    $count++;
                }
            }

            $this->info("Migration completed! {$count} records were migrated.");
        } catch (\Exception $e) {
            $this->error('Error during migration: ' . $e->getMessage());
        }
    }
}
