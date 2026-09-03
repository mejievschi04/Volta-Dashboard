<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\OneCController;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FetchOneCKpi extends Command
{
    protected $signature = '1c:fetch-kpi
                            {--date-start= : Început perioadă (YYYY-MM-DD), implicit: prima zi a lunii curente}
                            {--date-end= : Sfârșit perioadă (YYYY-MM-DD), implicit: azi}
                            {--save : Salvează răspunsul în storage/app/last_onec_kpi_response.json}
                            {--sync : Sync smart în DB (doar perioade lipsă), fără apel 1C dacă există deja}
                            {--force : La --sync, forțează re-fetch din 1C chiar dacă perioada există}';

    protected $description = 'Apelează API-ul 1C GetKPIData; cu --sync salvează în DB doar perioadele care lipsesc';

    public function handle(): int
    {
        if ($this->option('sync')) {
            return $this->handleSync();
        }

        $dateStart = $this->option('date-start') ?: date('Y-m-01');
        $dateEnd = $this->option('date-end') ?: date('Y-m-d');
        $oneCDateEnd = CarbonImmutable::createFromFormat('!Y-m-d', $dateEnd)
            ->addDay()
            ->format('Y-m-d');
        $save = $this->option('save');

        $config = config('services.onec', []);
        $baseUrl = $config['base_url'] ?? 'http://212.56.193.250/VOLTA_SQL/hs/CallCenterKPI';
        $username = $config['username'] ?? 'HTTPService';
        $password = $config['password'] ?? '';

        $url = rtrim($baseUrl, '/') . '/GetKPIData/';

        $this->info('Apel 1C GetKPIData');
        $this->line("URL: {$url}");
        $this->line("Perioadă inclusivă: {$dateStart} → {$dateEnd}");
        $this->newLine();

        try {
            $response = Http::withBasicAuth($username, $password)
                ->acceptJson()
                ->timeout(30)
                ->get($url, [
                    'date_start' => $dateStart,
                    // 1C tratează date_end ca limită exclusivă.
                    'date_end' => $oneCDateEnd,
                ]);

            if (! $response->successful()) {
                $this->error('Eroare HTTP: ' . $response->status());
                $this->line($response->body());
                return self::FAILURE;
            }

            $data = $response->json();
            $jsonPretty = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            if ($save) {
                Storage::put('last_onec_kpi_response.json', $jsonPretty);
                $this->info('Răspuns salvat în: storage/app/last_onec_kpi_response.json');
                $this->newLine();
            }

            $this->info('--- Răspuns brut 1C (primele 3000 caractere) ---');
            $this->line(mb_substr($jsonPretty, 0, 3000));
            if (mb_strlen($jsonPretty) > 3000) {
                $this->line('... [trunchiat, folosește --save pentru fișier complet]');
            }
            $this->newLine();
            $this->info('Tip răspuns: ' . gettype($data));
            if (is_array($data)) {
                $this->line('Chei top-level: ' . implode(', ', array_keys($data)));
                if (! empty($data) && is_array(reset($data))) {
                    $this->line('Exemplu prim element: ' . json_encode(array_shift($data), JSON_UNESCAPED_UNICODE));
                }
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Excepție: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    private function handleSync(): int
    {
        $request = new Request();
        $request->merge([
            'smart' => true,
            'force' => $this->option('force'),
        ]);
        $controller = app(OneCController::class);
        $response = $controller->syncKpi($request);
        $data = $response->getData(true);
        $this->line($data['message'] ?? 'Done.');
        if (! empty($data['onec_calls'])) {
            $this->info('Apeluri 1C: ' . $data['onec_calls']);
        }
        if (! empty($data['skipped'])) {
            $this->comment('Perioade deja existente: ' . count($data['skipped']));
        }
        return ($data['success'] ?? false) ? self::SUCCESS : self::FAILURE;
    }
}
