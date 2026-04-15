<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanVanzari;
use App\Models\OnecKpiSync;
use App\Models\TrafficSource;
use App\Support\DbDate;

class VanzariLunareController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Perioadă fixă: din ianuarie 2023 până la sfârșitul lunii curente
            $firstDate = '2023-01-01';
            $lastDate = date('Y-m-t');
            $onecByMonth = collect();

            try {
                $onecByMonth = OnecKpiSync::selectRaw(DbDate::month('period_start') . ' as month, vanzari_fara_tva, nr_comenzi')
                    ->orderByDesc('created_at')
                    ->get()
                    ->unique('month')
                    ->keyBy('month');
            } catch (\Throwable $e) {
                // Tabel onec_kpi_syncs poate să nu existe încă (migrare nerulată)
            }

            $sesiuniByMonth = collect();
            try {
                $sesiuniRows = TrafficSource::selectRaw(DbDate::month('date') . ' as month, SUM(visits) as total_sesiuni')
                    ->where('source', 'total')
                    ->groupBy('month')
                    ->pluck('total_sesiuni', 'month');
                $sesiuniByMonth = $sesiuniRows;
            } catch (\Throwable $e) {
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
                $comenziVal = $onec ? intval($onec->nr_comenzi) : 0;
                $totalSesiuni = (int) ($sesiuniByMonth->get($monthKey) ?? 0);
                $conversieVal = $totalSesiuni > 0 ? round(($comenziVal / $totalSesiuni) * 100, 2) : 0;
                $planKey = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
                $planValoare = $planMap[$planKey] ?? null;
                $lunaNume = $luniRomana[$month] ?? 'Luna ' . $month;
                $label = $lunaNume . ' ' . $year;
                $luni[] = ['value' => $monthKey, 'label' => $label];
                $data[] = [
                    'luna' => $monthKey,
                    'luna_label' => $label,
                    'vanzari' => $vanzariVal,
                    'plan' => $planValoare,
                    'comenzi' => $comenziVal,
                    'sesiuni' => $totalSesiuni,
                    'conversie' => $conversieVal,
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
