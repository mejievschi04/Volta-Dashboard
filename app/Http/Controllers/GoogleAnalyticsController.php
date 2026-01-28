<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleAnalyticsService;
use App\Models\TrafficSource;
use Carbon\Carbon;

class GoogleAnalyticsController extends Controller
{
    protected $gaService;

    public function __construct(GoogleAnalyticsService $gaService)
    {
        // Middleware-ul 'auth' este deja aplicat pe rute în routes/web.php
        $this->gaService = $gaService;
    }

    /**
     * Sincronizează datele din Google Analytics în baza de date
     */
    public function sync(Request $request)
    {
        try {
            \Log::info('GA Sync started', [
                'request_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            // Determină perioada de sincronizare
            if ($request->has('start_date') && $request->has('end_date')) {
                $startDate = $request->input('start_date');
                $endDate = $request->input('end_date');
            } elseif ($request->has('month')) {
                $month = $request->input('month'); // Format: YYYY-MM
                $startDate = $month . '-01';
                $endDate = date('Y-m-t', strtotime($startDate)); // Ultima zi a lunii
            } else {
                // Implicit: luna curentă
                $currentMonth = date('Y-m');
                $currentYear = date('Y');

                // Dacă suntem în decembrie sau ianuarie, sincronizăm și pentru noiembrie
                if (date('m') == '12' || date('m') == '01') {
                    $startDate = $currentYear . '-11-01'; // 1 noiembrie
                    $endDate = date('Y-m-t'); // Ultima zi a lunii curente
                } else {
                    $startDate = date('Y-m-01'); // Prima zi a lunii curente
                    $endDate = date('Y-m-d'); // Ziua curentă
                }
            }

            \Log::info('GA Sync period determined', [
                'start_date' => $startDate,
                'end_date' => $endDate
            ]);

            // Extragem datele din GA4
            $gaData = $this->gaService->fetchTrafficData($startDate, $endDate);

            \Log::info('GA data fetched', [
                'data_keys' => array_keys($gaData ?? []),
                'has_rows' => isset($gaData['rows'])
            ]);

            // Procesăm datele
            $processedData = $this->gaService->processTrafficData($gaData);

            \Log::info('GA data processed', [
                'dates_count' => count($processedData)
            ]);

            // Folosim tranzacție pentru a asigura integritatea datelor
            $inserted = 0;
            $errors = [];
            $deletedRows = 0;

            \DB::beginTransaction();
            
            try {
                // Ștergem datele existente pentru perioada selectată DOAR dacă avem date noi de inserat
                if (count($processedData) > 0) {
                    $deletedRows = TrafficSource::whereBetween('date', [$startDate, $endDate])->delete();
                }

                // Inserăm datele noi în baza de date
                foreach ($processedData as $date => $sources) {
                    $totalVisits = 0;
                    $totalNewUsers = 0;
                    $totalReturningUsers = 0;

                    // Calculăm totalurile pentru această zi
                    foreach ($sources as $source => $data) {
                        // Verificăm dacă este format vechi (doar număr) sau nou (array)
                        if (is_array($data)) {
                            $visits = $data['visits'] ?? 0;
                            $newUsers = $data['new_users'] ?? 0;
                            $returningUsers = $data['returning_users'] ?? 0;
                        } else {
                            // Compatibilitate cu formatul vechi
                            $visits = $data;
                            $newUsers = 0;
                            $returningUsers = 0;
                        }

                        $totalVisits += $visits;
                        $totalNewUsers += $newUsers;
                        $totalReturningUsers += $returningUsers;

                        try {
                            // Folosim updateOrCreate pentru a evita duplicatele
                            TrafficSource::updateOrCreate(
                                [
                                    'source' => $source,
                                    'date' => $date,
                                ],
                                [
                                    'visits' => $visits,
                                    'new_users' => $newUsers,
                                    'returning_users' => $returningUsers,
                                ]
                            );

                            $inserted++;
                        } catch (\Exception $e) {
                            $errors[] = "Eroare la salvarea datelor pentru {$source} pe {$date}: " . $e->getMessage();
                            \Log::error("GA Insert error", [
                                'source' => $source,
                                'date' => $date,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }

                    // Inserăm și totalul
                    if ($totalVisits > 0 || $totalNewUsers > 0 || $totalReturningUsers > 0) {
                        try {
                            TrafficSource::updateOrCreate(
                                [
                                    'source' => 'total',
                                    'date' => $date,
                                ],
                                [
                                    'visits' => $totalVisits,
                                    'new_users' => $totalNewUsers,
                                    'returning_users' => $totalReturningUsers,
                                ]
                            );

                            $inserted++;
                        } catch (\Exception $e) {
                            $errors[] = "Eroare la salvarea totalului pentru {$date}: " . $e->getMessage();
                            \Log::error("GA Insert total error", [
                                'date' => $date,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                // Dacă avem prea multe erori, facem rollback
                if (count($errors) > 50) {
                    \DB::rollBack();
                    throw new \Exception("Prea multe erori la inserare (" . count($errors) . "). Datele nu au fost modificate.");
                }

                \DB::commit();
                
            } catch (\Exception $e) {
                \DB::connection('trafic')->rollBack();
                \Log::error("GA Sync transaction failed", [
                    'error' => $e->getMessage(),
                    'errors_count' => count($errors)
                ]);
                throw $e;
            }

            return response()->json([
                'success' => true,
                'message' => 'Datele au fost sincronizate cu succes!',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'dates_processed' => count($processedData),
                'records_deleted' => $deletedRows,
                'records_inserted' => $inserted,
                'errors' => $errors
            ], 200, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        } catch (\Exception $e) {
            \Log::error('GA Sync error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
}
