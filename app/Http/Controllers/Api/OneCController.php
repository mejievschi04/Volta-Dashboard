<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OneCController extends Controller
{
    /**
     * Apelează API-ul 1C pentru KPI Call Center și întoarce răspunsul brut
     * pentru verificare rapidă.
     */
    public function syncKpi(Request $request)
    {
        try {
            // Perioada implicită: de la începutul lunii curente până azi
            $dateStart = $request->input('date_start', date('Y-m-01'));
            $dateEnd = $request->input('date_end', date('Y-m-d'));

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
                    'message' => 'Eroare la apelarea API-ului 1C',
                    'status' => $response->status(),
                    'body' => $response->body(),
                ], $response->status() ?: 500);
            }

            $data = $response->json();

            Log::info('OneC KPI API success', [
                'status' => $response->status(),
                'has_data' => ! empty($data),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'API 1C a răspuns cu succes',
                'date_start' => $dateStart,
                'date_end' => $dateEnd,
                'raw_response' => $data ?? $response->body(),
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        } catch (\Throwable $e) {
            Log::error('OneC KPI sync exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Eroare internă la sincronizarea cu 1C',
                'error' => $e->getMessage(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
}

