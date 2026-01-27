<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use App\Models\PlanVanzari;
use Illuminate\Support\Facades\DB;

class VanzariLunareController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Obține prima și ultima dată din baza de date folosind query-uri directe
            // pentru a evita probleme cu conexiunea sau formatul
            $firstDateRow = DB::connection('vanzari')
                ->table('vanzari_1c')
                ->selectRaw('MIN(data) as min_date')
                ->first();
            
            $lastDateRow = DB::connection('vanzari')
                ->table('vanzari_1c')
                ->selectRaw('MAX(data) as max_date')
                ->first();
            
            $firstDate = $firstDateRow->min_date ?? null;
            $lastDateInDb = $lastDateRow->max_date ?? null;
            
            // Verifică dacă există date înainte de prima dată găsită
            // Poate există date cu NULL sau cu format diferit
            $allDates = DB::connection('vanzari')
                ->table('vanzari_1c')
                ->selectRaw('MIN(data) as min_date, MAX(data) as max_date, COUNT(DISTINCT DATE_FORMAT(data, "%Y-%m")) as total_months')
                ->first();
            
            // Verifică și toate datele distincte pentru debugging
            $allDistinctDates = DB::connection('vanzari')
                ->table('vanzari_1c')
                ->selectRaw('DATE_FORMAT(data, "%Y-%m") as month, MIN(data) as first_date_in_month')
                ->groupBy(DB::raw('DATE_FORMAT(data, "%Y-%m")'))
                ->orderBy('month', 'ASC')
                ->limit(5)
                ->get();
            
            // Log pentru debugging
            \Log::info('VanzariLunareController - Date din DB', [
                'firstDate' => $firstDate,
                'lastDateInDb' => $lastDateInDb,
                'currentDate' => date('Y-m-d'),
                'currentMonth' => date('Y-m'),
                'allDatesQuery' => [
                    'min_date' => $allDates->min_date ?? null,
                    'max_date' => $allDates->max_date ?? null,
                    'total_months' => $allDates->total_months ?? 0
                ],
                'primele5Luni' => $allDistinctDates->map(function($item) {
                    return $item->month . ' (' . $item->first_date_in_month . ')';
                })->toArray()
            ]);
            
            // Setăm manual prima dată la ianuarie 2024 pentru a include toate lunile disponibile
            // Există date din ianuarie 2024 și chiar ianuarie 2023 în baza de date
            // Dacă query-ul nu găsește corect prima dată, folosim ianuarie 2024 ca fallback
            if (!$firstDate || $firstDate > '2024-01-01') {
                $firstDate = '2024-01-01'; // Prima dată fixă - ianuarie 2024
            }
            
            // Dacă există date mai vechi decât ianuarie 2024, folosim acea dată
            // Verificăm din nou cu query direct
            $actualMinDate = $allDates->min_date ?? null;
            if ($actualMinDate && $actualMinDate < $firstDate) {
                $firstDate = $actualMinDate;
            }
            
            // Folosim ultima dată din DB sau luna curentă, care este mai recentă
            // Astfel utilizatorul poate selecta toate lunile până la ultima dată disponibilă
            $currentMonthLastDay = date('Y-m-t');
            $lastDateInDbFormatted = $lastDateInDb ? date('Y-m-t', strtotime($lastDateInDb)) : null;
            
            // Folosim data mai recentă între ultima dată din DB și luna curentă
            if ($lastDateInDbFormatted && $lastDateInDbFormatted > $currentMonthLastDay) {
                $lastDate = $lastDateInDbFormatted;
            } else {
                $lastDate = $currentMonthLastDay;
            }
            
            // Obține datele lunare (totaluri pe lună) - fără TVA
            $rows = Vanzari::selectRaw('
                DATE_FORMAT(data, "%Y-%m") as month,
                DATE_FORMAT(data, "%M") as month_name,
                YEAR(data) as an,
                MONTH(data) as luna_num,
                SUM(suma_fara_tva) as total_vanzari,
                COUNT(DISTINCT data) as zile_activitate
            ')
            ->groupBy(DB::raw('DATE_FORMAT(data, "%Y-%m")'), DB::raw('DATE_FORMAT(data, "%M")'), DB::raw('YEAR(data)'), DB::raw('MONTH(data)'))
            ->orderBy('month', 'ASC')
            ->get();
            
            // Creează un map pentru datele existente
            $dataMap = [];
            foreach ($rows as $row) {
                $dataMap[$row->month] = $row;
            }
            
            // Obține planurile pentru toate lunile
            $planMap = [];
            $lunaToNum = [
                'Ianuarie' => 1, 'Februarie' => 2, 'Martie' => 3, 'Aprilie' => 4,
                'Mai' => 5, 'Iunie' => 6, 'Iulie' => 7, 'August' => 8,
                'Septembrie' => 9, 'Octombrie' => 10, 'Noiembrie' => 11, 'Decembrie' => 12
            ];
            
            try {
                $planRows = PlanVanzari::all();
                
                foreach ($planRows as $plan) {
                    $lunaNum = $lunaToNum[$plan->luna] ?? null;
                    if ($lunaNum) {
                        $key = intval($plan->an) . '-' . str_pad($lunaNum, 2, '0', STR_PAD_LEFT);
                        $planMap[$key] = floatval($plan->valoare);
                    }
                }
            } catch (\Exception $e) {
                // Tabelul plan_vanzari poate să nu existe
            }
            
            // Formatare pentru dropdown și grafice
            $luni = [];
            $data = [];
            
            $luniRomana = [
                1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
                5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
                9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
            ];
            
            // Generează toate lunile între prima și ultima dată
            $startDate = new \DateTime($firstDate);
            $startDate->modify('first day of this month');
            
            // Folosim luna curentă ca ultimă dată
            $currentYear = (int)date('Y');
            $currentMonth = (int)date('m');
            
            // Calculăm numărul de luni între startDate și luna curentă
            $startYear = (int)$startDate->format('Y');
            $startMonth = (int)$startDate->format('m');
            
            // Calculăm corect numărul de luni (inclusiv luna curentă)
            $totalMonths = ($currentYear - $startYear) * 12 + ($currentMonth - $startMonth) + 1;
            
            // Asigurăm că avem cel puțin 1 lună
            if ($totalMonths < 1) {
                $totalMonths = 1;
            }
            
            // Log pentru debugging
            \Log::info('VanzariLunareController - Calcul luni', [
                'startYear' => $startYear,
                'startMonth' => $startMonth,
                'currentYear' => $currentYear,
                'currentMonth' => $currentMonth,
                'totalMonths' => $totalMonths,
                'startDate' => $startDate->format('Y-m-d')
            ]);
            
            $currentDate = clone $startDate;
            
            // Generăm toate lunile până la luna curentă inclusiv
            for ($i = 0; $i < $totalMonths; $i++) {
                $year = (int)$currentDate->format('Y');
                $month = (int)$currentDate->format('m');
                $monthKey = $currentDate->format('Y-m');
                
                // Verifică dacă există date pentru această lună
                $row = $dataMap[$monthKey] ?? null;
                
                $planKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $planValoare = $planMap[$planKey] ?? null;
                $lunaNume = $luniRomana[$month] ?? 'Luna ' . $month;
                $label = $lunaNume . ' ' . $year;
                
                $luni[] = [
                    'value' => $monthKey,
                    'label' => $label
                ];
                
                $data[] = [
                    'luna' => $monthKey,
                    'vanzari' => $row ? floatval($row->total_vanzari) : 0,
                    'plan' => $planValoare,
                    'zile' => $row ? intval($row->zile_activitate) : 0
                ];
                
                // Trecem la luna următoare
                $currentDate->modify('+1 month');
            }
            
            // Log pentru debugging - ultimele 5 luni generate
            $lastMonths = array_slice($luni, -5);
            \Log::info('VanzariLunareController - Ultimele luni generate', [
                'totalLuni' => count($luni),
                'ultimele5' => $lastMonths
            ]);
            
            return response()->json([
                'success' => true,
                'luni' => $luni,
                'data' => $data,
                'debug' => [
                    'firstDate' => $firstDate,
                    'lastDateInDb' => $lastDateInDb ?? null,
                    'totalMonths' => $totalMonths,
                    'currentMonth' => date('Y-m')
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
