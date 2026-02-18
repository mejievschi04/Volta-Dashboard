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

            // Verifică dacă serviciul GA este disponibil
            try {
                $credentialsPath = config('google-analytics.credentials_path');
                $propertyId = config('google-analytics.property_id');
                
                \Log::info('GA Configuration check', [
                    'credentials_path' => $credentialsPath,
                    'property_id' => $propertyId,
                    'credentials_exists' => file_exists($credentialsPath),
                    'credentials_readable' => file_exists($credentialsPath) ? is_readable($credentialsPath) : false
                ]);

                if (empty($propertyId) || $propertyId === 'YOUR_PROPERTY_ID_HERE') {
                    throw new \Exception("Property ID nu este configurat! Verifică config/google-analytics.php sau variabila de mediu GA_PROPERTY_ID");
                }

                if (!file_exists($credentialsPath)) {
                    throw new \Exception("Fișierul de credențiale nu există pe server: {$credentialsPath}. Asigură-te că fișierul service-account-credentials.json este încărcat pe server.");
                }

                if (!is_readable($credentialsPath)) {
                    throw new \Exception("Fișierul de credențiale nu poate fi citit. Verifică permisiunile pentru: {$credentialsPath}");
                }
            } catch (\Exception $configError) {
                \Log::error('GA Configuration error', [
                    'error' => $configError->getMessage(),
                    'trace' => $configError->getTraceAsString()
                ]);
                throw $configError;
            }

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
                \DB::rollBack();
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
            ]);

            $errorMessage = $e->getMessage();
            if (strpos($errorMessage, 'Fișierul de credențiale') !== false) {
                $errorMessage .= ' Încarcă service-account-credentials.json în storage/app/google-analytics/ pe server.';
            } elseif (strpos($errorMessage, 'Property ID') !== false) {
                $errorMessage .= ' Adaugă în .env: GA_PROPERTY_ID=123456789 (ID din GA4 Admin → Property Settings).';
            }

            $isConfigError = strpos($e->getMessage(), 'Property ID') !== false
                || strpos($e->getMessage(), 'credențiale') !== false
                || strpos($e->getMessage(), 'credentials') !== false;
            $statusCode = $isConfigError ? 400 : 500;

            return response()->json([
                'success' => false,
                'message' => $isConfigError ? 'Configurare GA4 incompletă' : 'Server Error',
                'error' => $errorMessage,
            ], $statusCode, [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
    }
}
