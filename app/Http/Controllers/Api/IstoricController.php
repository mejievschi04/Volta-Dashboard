<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use App\Models\PlanVanzari;
use App\Models\TrafficSource;
use Illuminate\Support\Facades\DB;

class IstoricController extends Controller
{
    public function index(Request $request)
    {
        try {
            // Obține toate lunile cu date
            $rows = Vanzari::selectRaw('
                DATE_FORMAT(data, "%Y-%m") as month,
                DATE_FORMAT(data, "%M") as month_name,
                YEAR(data) as an,
                MONTH(data) as luna_num
            ')
            ->groupBy(DB::raw('DATE_FORMAT(data, "%Y-%m")'), DB::raw('DATE_FORMAT(data, "%M")'), DB::raw('YEAR(data)'), DB::raw('MONTH(data)'))
            ->orderBy('month', 'DESC')
            ->get();
            
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
            
            foreach ($rows as $row) {
                $luna = $row->month;
                $planKey = $row->an . '-' . str_pad($row->luna_num, 2, '0', STR_PAD_LEFT);
                
                // Vânzări pentru luna
                $vanzariData = Vanzari::selectRaw('
                    SUM(suma_fara_tva) as total_vanzari,
                    SUM(suma_cu_tva) as total_vanzari_cu_tva,
                    SUM(profit) as total_profit,
                    SUM(nr_vanzari) as total_comenzi,
                    COUNT(DISTINCT data) as zile_activitate
                ')
                ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$luna])
                ->first();
                
                // Plan
                $planLuna = $planMap[$planKey] ?? 0;
                
                // Sesiuni
                $sesiuniData = TrafficSource::selectRaw('SUM(visits) as total_sesiuni')
                    ->where('source', 'total')
                    ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$luna])
                    ->first();
                
                // Calcule
                $vanzariLuna = floatval($vanzariData->total_vanzari ?? 0);
                $vanzariCuTva = floatval($vanzariData->total_vanzari_cu_tva ?? 0);
                $profit = floatval($vanzariData->total_profit ?? 0);
                $comenzi = intval($vanzariData->total_comenzi ?? 0);
                $totalSesiuni = intval($sesiuniData->total_sesiuni ?? 0);
                
                $zileLuna = intval(date('t', strtotime($luna . '-01')));
                $comenziZi = $zileLuna > 0 ? round($comenzi / $zileLuna, 1) : 0;
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
                
                $lunaNume = $luniRomana[$row->luna_num] ?? 'Luna ' . $row->luna_num;
                $label = $lunaNume . ' ' . $row->an;
                
                $istoric[] = [
                    'luna' => $luna,
                    'luna_label' => $label,
                    'an' => $row->an,
                    'luna_num' => $row->luna_num,
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
                    'zile_activitate' => intval($vanzariData->zile_activitate ?? 0)
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

