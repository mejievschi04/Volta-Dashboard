<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OnecKpiSync;
use App\Models\OnecKpiOperator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneCController extends Controller
{
    /**
     * Actualizare KPI din sursa externă.
     * - Fără force: nu se reapelează datele dacă perioada există deja în DB.
     * - Cu smart=1: se verifică lunile din 1 ianuarie 2023 până în prezent; pentru fiecare lună care lipsește se actualizează datele.
     */
    public function syncKpi(Request $request)
    {
        try {
            $force = $request->boolean('force');
            $smart = $request->boolean('smart');

            if ($smart) {
                return $this->syncKpiSmart($request, $force);
            }

            $dateStart = $request->input('date_start', date('Y-m-01'));
            $dateEnd = $request->input('date_end', date('Y-m-d'));

            // Nu reîncărcăm dacă avem deja această perioadă (decât cu force)
            if (! $force) {
                $existing = OnecKpiSync::where('period_start', $dateStart)
                    ->where('period_end', $dateEnd)
                    ->first();
                if ($existing) {
                    Log::info('OneC KPI skip: perioada există deja', [
                        'period_start' => $dateStart,
                        'period_end' => $dateEnd,
                    ]);
                    return response()->json([
                        'success' => true,
                        'message' => 'Datele pentru această perioadă există deja. Nu s-a efectuat nicio actualizare.',
                        'date_start' => $dateStart,
                        'date_end' => $dateEnd,
                        'sync_id' => $existing->id,
                        'from_cache' => true,
                    ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
            }

            $result = $this->fetchFromOneCAndSave($dateStart, $dateEnd);
            if ($result instanceof JsonResponse) {
                return $result;
            }
            $sync = $result;

            return response()->json([
                'success' => true,
                'message' => 'Sursa externă a răspuns cu succes. Datele au fost salvate în baza de date.',
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'sync_id' => $sync->id,
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            Log::error('OneC KPI sync exception', [
                'message' => $message,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            $userMessage = 'Eroare la actualizarea datelor.';
            if (str_contains($message, 'Connection refused')) {
                $userMessage = 'Serverul nu răspunde (conexiune refuzată). Verificați că serverul este pornit și accesibil.';
            } elseif (str_contains($message, 'timed out') || str_contains($message, 'timeout')) {
                $userMessage = 'Serverul nu răspunde în timp util (timeout). Încercați din nou sau verificați rețeaua.';
            }

            return response()->json([
                'success' => false,
                'message' => $userMessage,
                'error' => $message,
            ], 503, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }

    /**
     * Hard refresh: rescrie toate datele pentru lunile trecute (doar admin).
     * Pentru fiecare lună în trecut (ultimele N luni, implicit 12) se reîncarcă și se suprascrie în DB.
     */
    public function hardRefresh(Request $request): JsonResponse
    {
        $monthsBack = max(1, min(24, (int) $request->input('months', 12)));
        $periods = [];
        $cursor = strtotime('first day of last month');
        for ($i = 0; $i < $monthsBack; $i++) {
            $periods[] = [
                'start' => date('Y-m-01', $cursor),
                'end' => date('Y-m-t', $cursor),
                'label' => date('Y-m', $cursor),
            ];
            $cursor = strtotime('-1 month', $cursor);
        }

        $synced = [];
        $errors = [];
        foreach ($periods as $p) {
            $result = $this->fetchFromOneCAndSave($p['start'], $p['end']);
            if ($result instanceof JsonResponse) {
                $errors[] = ['period' => $p['label'], 'message' => 'Eroare de actualizare'];
                continue;
            }
            $synced[] = ['period' => $p['label'], 'sync_id' => $result->id];
        }

        $total = count($periods);
        $ok = count($synced);
        $message = "Reîmprospătare finalizată: {$ok}/{$total} luni reîncărcate.";
        if (count($errors) > 0) {
            $message .= ' Erori: ' . count($errors) . ' lun(i).';
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => $message,
            'synced' => $synced,
            'errors' => $errors,
            'total_months' => $total,
            'onec_calls' => $ok,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Sync smart: perioade din 1 ianuarie 2023 până în prezent care lipsesc.
     * Pentru fiecare lună: dacă există deja în DB și nu e force, nu se reapelează.
     */
    private function syncKpiSmart(Request $request, bool $force): JsonResponse
    {
        $today = date('Y-m-d');
        $currentMonthStart = date('Y-m-01');
        $periods = [];

        // De la ianuarie 2023 până la luna trecută (luni complete)
        $cursor = strtotime('2023-01-01');
        $endOfLastMonth = strtotime('last day of last month');
        while ($cursor <= $endOfLastMonth) {
            $periods[] = [
                'start' => date('Y-m-01', $cursor),
                'end' => date('Y-m-t', $cursor),
                'label' => date('Y-m', $cursor),
            ];
            $cursor = strtotime('+1 month', $cursor);
        }

        // Luna curentă (de la 1 până azi)
        $periods[] = [
            'start' => $currentMonthStart,
            'end' => $today,
            'label' => date('Y-m') . ' (curentă)',
        ];

        $synced = [];
        $skipped = [];
        $errors = [];

        foreach ($periods as $p) {
            $dateStart = $p['start'];
            $dateEnd = $p['end'];

            // Un singur sync per lună: skip doar dacă avem deja date până la sfârșitul perioadei cerute
            // (astfel în cursul lunii actualizăm zilnic, iar în luna următoare reîmprospătăm luna completă)
            if (! $force) {
                $monthStart = substr($dateStart, 0, 7);
                $existing = OnecKpiSync::where('period_start', '>=', $dateStart)
                    ->where('period_start', '<', date('Y-m-d', strtotime($monthStart . '-01 +1 month')))
                    ->first();
                if ($existing && $existing->period_end >= $dateEnd) {
                    $skipped[] = ['period' => "{$dateStart} – {$dateEnd}", 'label' => $p['label'], 'sync_id' => $existing->id];
                    continue;
                }
            }

            $result = $this->fetchFromOneCAndSave($dateStart, $dateEnd);
            if ($result instanceof JsonResponse) {
                $errors[] = ['period' => "{$dateStart} – {$dateEnd}", 'label' => $p['label']];
                continue;
            }
            $synced[] = ['period' => "{$dateStart} – {$dateEnd}", 'label' => $p['label'], 'sync_id' => $result->id];
        }

        $oneCCalls = count($synced);
        $message = $oneCCalls === 0 && count($skipped) > 0
            ? 'Toate perioadele existau deja. Nu s-a efectuat nicio actualizare.'
            : "Sincronizare finalizată: {$oneCCalls} actualizare(i), " . count($skipped) . ' perioade deja existente.';
        if (count($errors) > 0) {
            $message .= ' Erori: ' . count($errors) . ' perioad(e).';
        }

        return response()->json([
            'success' => count($errors) === 0,
            'message' => $message,
            'smart' => true,
            'onec_calls' => $oneCCalls,
            'synced' => $synced,
            'skipped' => $skipped,
            'errors' => $errors,
        ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Reîncarcă datele pentru perioada dată și salvează în DB.
     * Returnează OnecKpiSync la succes sau JsonResponse la eroare.
     */
    private function fetchFromOneCAndSave(string $dateStart, string $dateEnd): OnecKpiSync|JsonResponse
    {
        $config = config('services.onec', []);
        $baseUrl = $config['base_url'] ?? 'http://212.56.193.250/VOLTA_SQL/hs/CallCenterKPI';
        $username = $config['username'] ?? 'HTTPService';
        $password = $config['password'] ?? '';
        $url = rtrim($baseUrl, '/') . '/GetKPIData/';

        Log::info('OneC KPI sync started', [
            'url' => $url,
            'date_start' => $dateStart,
            'date_end' => $dateEnd,
        ]);

        $response = Http::withBasicAuth($username, $password)
            ->acceptJson()
            ->timeout(30)
            ->get($url, [
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
            ]);

        if (! $response->successful()) {
            Log::error('OneC KPI API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Eroare la apelarea sursei externe',
                'status' => $response->status(),
                'body' => $response->body(),
            ], $response->status() ?: 500, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $data = $response->json();
        Log::info('OneC KPI API success', ['status' => $response->status(), 'has_data' => ! empty($data)]);

        $sync = $this->saveOneCResponseToDb($dateStart, $dateEnd, $data ?? []);
        return $sync;
    }

    /**
     * Citește o valoare numerică din array încercând mai multe variante de chei.
     */
    private function getNumeric(array $arr, array $keyVariants, float $default = 0): float
    {
        foreach ($keyVariants as $key) {
            if (array_key_exists($key, $arr) && $arr[$key] !== null && $arr[$key] !== '') {
                return (float) $arr[$key];
            }
        }
        return $default;
    }

    /**
     * Salvează răspunsul în tabelele de KPI.
     * O singură înregistrare per lună: dacă există deja un sync pentru aceeași lună (period_start în aceeași lună),
     * îl actualizează ca să nu dublăm vânzările la raportare (ex. februarie parțial + februarie completă).
     */
    private function saveOneCResponseToDb(string $dateStart, string $dateEnd, array $data): ?OnecKpiSync
    {
        $meta = $data['meta'] ?? [];
        $kpiTotal = $data['kpiTotal'] ?? $data['KpiTotal'] ?? [];
        $kpiPeOperator = $data['kpiPeOperator'] ?? $data['KpiPeOperator'] ?? $data['kpiPeOperator'] ?? [];
        if (! is_array($kpiPeOperator)) {
            $kpiPeOperator = [];
        }

        $generatedAt = isset($meta['generatedAt'])
            ? (is_numeric($meta['generatedAt']) ? date('Y-m-d H:i:s', (int) $meta['generatedAt']) : $meta['generatedAt'])
            : null;

        // Căutăm orice sync pentru aceeași lună (YYYY-MM) ca dateStart, ca să actualizăm în loc să duplicăm
        $nextMonth = date('Y-m-d', strtotime(substr($dateStart, 0, 7) . '-01 +1 month'));
        $sync = OnecKpiSync::where('period_start', '>=', $dateStart)
            ->where('period_start', '<', $nextMonth)
            ->first();

        DB::transaction(function () use (
            &$sync,
            $dateStart,
            $dateEnd,
            $nextMonth,
            $meta,
            $kpiTotal,
            $kpiPeOperator,
            $generatedAt
        ) {
            $payload = [
                'period_start' => $dateStart,
                'period_end' => $dateEnd,
                'company' => $meta['company'] ?? null,
                'currency' => $meta['currency'] ?? null,
                'vanzari_cu_tva' => $this->getNumeric($kpiTotal, ['vanzariCuTVA', 'vanzari_cu_tva', 'VanzariCuTVA']),
                'vanzari_fara_tva' => $this->getNumeric($kpiTotal, ['vanzariFaraTVA', 'vanzari_fara_tva', 'VanzariFaraTVA']),
                'profit' => $this->getNumeric($kpiTotal, ['profit', 'Profit']),
                'nr_comenzi' => (int) $this->getNumeric($kpiTotal, ['nrComenzi', 'nr_comenzi', 'NrComenzi']),
                'generated_at' => $generatedAt,
            ];

            if ($sync) {
                $sync->update($payload);
                $sync->operatori()->delete();
            } else {
                $sync = OnecKpiSync::create($payload);
            }

            foreach ($kpiPeOperator as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $nume = $row['operatorNume'] ?? $row['operator_nume'] ?? $row['OperatorNume'] ?? null;
                OnecKpiOperator::create([
                    'onec_kpi_sync_id' => $sync->id,
                    'operator_id_1c' => (string) ($row['operatorId1c'] ?? $row['operator_id_1c'] ?? ''),
                    'operator_nume' => $nume,
                    'vanzari_cu_tva' => $this->getNumeric($row, ['vanzariCuTVA', 'vanzari_cu_tva', 'VanzariCuTVA']),
                    'vanzari_fara_tva' => $this->getNumeric($row, ['vanzariFaraTVA', 'vanzari_fara_tva', 'VanzariFaraTVA']),
                    'profit' => $this->getNumeric($row, ['profit', 'Profit']),
                    'nr_comenzi' => (int) $this->getNumeric($row, ['nrComenzi', 'nr_comenzi', 'NrComenzi']),
                ]);
            }

            // Șterge eventuale duplicate pentru aceeași lună (un singur sync per YYYY-MM)
            $sameMonthIds = OnecKpiSync::where('period_start', '>=', $dateStart)
                ->where('period_start', '<', $nextMonth)
                ->where('id', '!=', $sync->id)
                ->pluck('id');
            if ($sameMonthIds->isNotEmpty()) {
                OnecKpiOperator::whereIn('onec_kpi_sync_id', $sameMonthIds)->delete();
                OnecKpiSync::whereIn('id', $sameMonthIds)->delete();
            }
        });

        return $sync;
    }
}
