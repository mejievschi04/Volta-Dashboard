<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleAnalyticsController;
use Illuminate\Console\Command;
use Illuminate\Http\Request;

class SyncGa4Traffic extends Command
{
    protected $signature = 'ga4:sync
                            {--month= : Luna (YYYY-MM); implicit: luna curentă}';

    protected $description = 'Sincronizează datele din Google Analytics 4 (trafic) în baza de date. Folosit și de scheduler (zilnic 07:00).';

    public function handle(): int
    {
        $month = $this->option('month');
        $request = new Request();
        if ($month !== null && $month !== '') {
            $request->merge(['month' => $month]);
            $this->info("Sync GA4 pentru luna: {$month}");
        } else {
            $this->info('Sync GA4 pentru perioada implicită (luna curentă / noiembrie–prezent în dec–ian).');
        }

        try {
            $controller = app(GoogleAnalyticsController::class);
            $response = $controller->sync($request);
            $data = $response->getData(true);

            if (! empty($data['success']) && $data['success']) {
                $this->info($data['message'] ?? 'GA4 sincronizat cu succes.');
                if (isset($data['records_inserted'])) {
                    $this->line('Înregistrări inserate: ' . $data['records_inserted']);
                }
                return self::SUCCESS;
            }

            $this->error($data['message'] ?? $data['error'] ?? 'Eroare la sync GA4.');
            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error('Excepție: ' . $e->getMessage());
            \Log::error('GA4 sync command exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return self::FAILURE;
        }
    }
}
