<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanVanzari;
use App\Models\OnecKpiSync;

class VanzariLunareController extends Controller
{
    public function index(Request $request)
    {
        try {
            $firstDate = date('Y-m-01', strtotime('-24 months'));
            $lastDate = date('Y-m-t');
            $onecByMonth = collect();

            try {
                $range = OnecKpiSync::selectRaw('MIN(period_start) as min_date, MAX(period_end) as max_date')->first();
                if ($range && $range->min_date) {
                    $firstDate = $range->min_date;
                }
                if ($range && $range->max_date) {
                    $lastDate = $range->max_date;
                }
                $onecByMonth = OnecKpiSync::selectRaw("DATE_FORMAT(period_start, '%Y-%m') as month, vanzari_fara_tva")
                    ->orderByDesc('created_at')
                    ->get()
                    ->unique('month')
                    ->keyBy('month');
            } catch (\Throwable $e) {
                // Tabel onec_kpi_syncs poate să nu existe încă (migrare nerulată)
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
            
            $startDate = new \DateTime($firstDate);
            $startDate->modify('first day of this month');
            $endDate = new \DateTime($lastDate);
            $endDate->modify('last day of this month');
            $currentYear = (int) $endDate->format('Y');
            $currentMonth = (int) $endDate->format('m');
            $startYear = (int) $startDate->format('Y');
            $startMonth = (int) $startDate->format('m');
            $totalMonths = ($currentYear - $startYear) * 12 + ($currentMonth - $startMonth) + 1;
            if ($totalMonths < 1) {
                $totalMonths = 1;
            }

            $currentDate = clone $startDate;
            for ($i = 0; $i < $totalMonths; $i++) {
                $year = (int) $currentDate->format('Y');
                $month = (int) $currentDate->format('m');
                $monthKey = $currentDate->format('Y-m');
                $onec = $onecByMonth->get($monthKey);
                $vanzariVal = $onec ? floatval($onec->vanzari_fara_tva) : 0;
                $planKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $planValoare = $planMap[$planKey] ?? null;
                $lunaNume = $luniRomana[$month] ?? 'Luna ' . $month;
                $label = $lunaNume . ' ' . $year;
                $luni[] = ['value' => $monthKey, 'label' => $label];
                $data[] = [
                    'luna' => $monthKey,
                    'vanzari' => $vanzariVal,
                    'plan' => $planValoare,
                    'zile' => 0
                ];
                $currentDate->modify('+1 month');
            }

            return response()->json([
                'success' => true,
                'luni' => $luni,
                'data' => $data,
                'debug' => [
                    'firstDate' => $firstDate,
                    'lastDate' => $lastDate,
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
