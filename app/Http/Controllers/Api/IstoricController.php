<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanVanzari;
use App\Models\TrafficSource;
use App\Models\OnecKpiSync;
use Illuminate\Support\Facades\DB;

class IstoricController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Lunile doar din 1C (onec_kpi_syncs)
            $allMonths = OnecKpiSync::selectRaw("DATE_FORMAT(period_start, '%Y-%m') as month")
                ->distinct()
                ->pluck('month')
                ->filter()
                ->sort()
                ->values()
                ->reverse()
                ->values();

            // Obține planurile
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
            
            $luniRomana = [
                1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
                5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
                9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
            ];
            
            $istoric = [];

            foreach ($allMonths as $luna) {
                $parts = explode('-', $luna);
                $an = (int) ($parts[0] ?? date('Y'));
                $lunaNum = (int) ($parts[1] ?? 1);
                $planKey = $an . '-' . str_pad($lunaNum, 2, '0', STR_PAD_LEFT);

                $onecSync = OnecKpiSync::whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$luna])
                    ->orderByDesc('created_at')
                    ->first();

                $vanzariLuna = $onecSync ? floatval($onecSync->vanzari_fara_tva) : 0;
                $vanzariCuTva = $onecSync ? floatval($onecSync->vanzari_cu_tva) : 0;
                $profit = $onecSync ? floatval($onecSync->profit) : 0;
                $comenzi = $onecSync ? intval($onecSync->nr_comenzi) : 0;

                $planLuna = $planMap[$planKey] ?? 0;
                $sesiuniData = TrafficSource::selectRaw('SUM(visits) as total_sesiuni')
                    ->where('source', 'total')
                    ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$luna])
                    ->first();
                $totalSesiuni = intval($sesiuniData->total_sesiuni ?? 0);
                
                $zileLuna = intval(date('t', strtotime($luna . '-01')));
                $totalZileComenziZi = $zileLuna;
                if ($onecSync && $onecSync->period_start && $onecSync->period_end) {
                    $startStr = is_object($onecSync->period_start) ? $onecSync->period_start->format('Y-m-d') : (string) $onecSync->period_start;
                    $endStr = is_object($onecSync->period_end) ? $onecSync->period_end->format('Y-m-d') : (string) $onecSync->period_end;
                    $totalZileComenziZi = max(1, (int) ((strtotime($endStr) - strtotime($startStr)) / 86400) + 1);
                }
                $comenziZi = $totalZileComenziZi > 0 ? round($comenzi / $totalZileComenziZi, 1) : 0;
                $conversie = $totalSesiuni > 0 ? round(($comenzi / $totalSesiuni) * 100, 2) : 0;
                $progresPlan = $planLuna > 0 ? round(($vanzariLuna / $planLuna) * 100, 2) : 0;
                $diferentaPlan = $vanzariLuna - $planLuna;
                
                // Calculează zilele trecute pentru prognoză
                $lunaSelectata = strtotime($luna . '-01');
                $lunaCurenta = strtotime(date('Y-m-01'));
                $ziCurenta = intval(date('d'));
                
                if ($lunaSelectata < $lunaCurenta) {
                    $zileTrecute = $zileLuna;
                } elseif ($lunaSelectata == $lunaCurenta) {
                    $zileTrecute = min($ziCurenta, $zileLuna);
                } else {
                    $zileTrecute = 0;
                }
                
                $vanzariZilniceMedii = $zileTrecute > 0 ? ($vanzariLuna / $zileTrecute) : 0;
                $zileRamase = max(0, $zileLuna - $zileTrecute);
                $prognozaPlan = $vanzariLuna + ($vanzariZilniceMedii * $zileRamase);
                $prognozaPlanProcent = $planLuna > 0 ? round(($prognozaPlan / $planLuna) * 100, 2) : 0;
                
                $lunaNume = $luniRomana[$lunaNum] ?? 'Luna ' . $lunaNum;
                $label = $lunaNume . ' ' . $an;

                $istoric[] = [
                    'luna' => $luna,
                    'luna_label' => $label,
                    'an' => $an,
                    'luna_num' => $lunaNum,
                    'plan_luna' => $planLuna,
                    'vanzari_luna' => $vanzariLuna,
                    'vanzari_cu_tva' => $vanzariCuTva,
                    'profit' => $profit,
                    'progres_plan' => $progresPlan,
                    'diferenta_plan' => $diferentaPlan,
                    'prognoza_plan' => $prognozaPlan,
                    'prognoza_plan_procent' => $prognozaPlanProcent,
                    'comenzi' => $comenzi,
                    'comenzi_zi' => $comenziZi,
                    'sesiuni' => $totalSesiuni,
                    'conversie' => $conversie,
                    'zile_activitate' => 0,
                    'kpi_source' => 'onec_db',
                ];
            }
            
            // Calculează diferențele față de luna anterioară
            for ($i = 0; $i < count($istoric); $i++) {
                if ($i < count($istoric) - 1) {
                    $prev = $istoric[$i + 1];
                    $current = $istoric[$i];
                    
                    $diffVanzari = $current['vanzari_luna'] - $prev['vanzari_luna'];
                    $diffPercent = $prev['vanzari_luna'] > 0 ? round(($diffVanzari / $prev['vanzari_luna']) * 100, 2) : 0;
                    
                    $istoric[$i]['vanzari_vs_anterioara'] = $diffVanzari;
                    $istoric[$i]['vanzari_vs_anterioara_percent'] = $diffPercent;
                } else {
                    $istoric[$i]['vanzari_vs_anterioara'] = 0;
                    $istoric[$i]['vanzari_vs_anterioara_percent'] = 0;
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => $istoric
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

