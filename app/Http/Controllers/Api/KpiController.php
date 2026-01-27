<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use App\Models\PlanVanzari;
use App\Models\TrafficSource;
use Illuminate\Support\Facades\DB;

class KpiController extends Controller
{
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        
        try {
            // Parsează luna
            $parts = explode('-', $luna);
            $an = intval($parts[0]);
            $lunaNum = intval($parts[1]);
            
            // Convertim numărul lunii în numele lunii în română
            $luniRomana = [
                1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
                5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
                9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'
            ];
            $lunaNume = $luniRomana[$lunaNum] ?? '';
            
            // Total vânzări pentru luna selectată
            $vanzariData = Vanzari::selectRaw('
                SUM(suma_fara_tva) as total_vanzari,
                SUM(suma_cu_tva) as total_vanzari_cu_tva,
                SUM(profit) as total_profit,
                SUM(nr_vanzari) as total_comenzi,
                COUNT(DISTINCT data) as zile_activitate
            ')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$luna])
            ->first();
            
            // Plan vânzări pentru luna selectată
            $planData = PlanVanzari::where('an', $an)
                ->where('luna', $lunaNume)
                ->first();
            $planLuna = $planData ? floatval($planData->valoare) : 0;
            
            // Total sesiuni pentru luna selectată
            $sesiuniData = TrafficSource::selectRaw('SUM(visits) as total_sesiuni')
                ->where('source', 'total')
                ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$luna])
                ->first();
            
            // Calculează KPI-urile
            $comenzi = intval($vanzariData->total_comenzi ?? 0);
            $zileLuna = intval(date('t', strtotime($luna . '-01')));
            
            // Calculează zilele trecute
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
            
            $comenziZi = $zileLuna > 0 ? round($comenzi / $zileLuna, 1) : 0;
            
            // Conversie
            $totalSesiuni = intval($sesiuniData->total_sesiuni ?? 0);
            $conversie = $totalSesiuni > 0 ? round(($comenzi / $totalSesiuni) * 100, 2) : 0;
            
            // Calculează KPI-urile pentru plan
            $vanzariLuna = floatval($vanzariData->total_vanzari ?? 0);
            $vanzariCuTva = floatval($vanzariData->total_vanzari_cu_tva ?? 0);
            $profit = floatval($vanzariData->total_profit ?? 0);
            
            // Progres plan (%)
            $progresPlan = $planLuna > 0 ? round(($vanzariLuna / $planLuna) * 100, 2) : 0;
            
            // Diferență față de plan
            $diferentaPlan = $vanzariLuna - $planLuna;
            
            // Prognoză plan
            $vanzariZilniceMedii = $zileTrecute > 0 ? ($vanzariLuna / $zileTrecute) : 0;
            $zileRamase = max(0, $zileLuna - $zileTrecute);
            $prognozaPlan = $vanzariLuna + ($vanzariZilniceMedii * $zileRamase);
            
            // Prognoză plan %
            $prognozaPlanProcent = $planLuna > 0 ? round(($prognozaPlan / $planLuna) * 100, 2) : 0;
            
            // Valoare medie comandă
            $valoareMedie = $comenzi > 0 ? round($vanzariLuna / $comenzi, 2) : 0;
            
            // Progres zilnic
            $progresZilnic = $zileLuna > 0 ? round(($zileTrecute / $zileLuna) * 100, 2) : 0;
            
            return response()->json([
                'success' => true,
                'month' => $luna,
                'plan_luna' => $planLuna,
                'vanzari_luna' => $vanzariLuna,
                'vanzari_cu_tva' => $vanzariCuTva,
                'profit' => $profit,
                'progres_plan' => $progresPlan,
                'diferenta_plan' => $diferentaPlan,
                'prognoza_plan' => $prognozaPlan,
                'prognoza_plan_procent' => $prognozaPlanProcent,
                'sesiuni' => $totalSesiuni,
                'comenzi' => $comenzi,
                'comenzi_zi' => $comenziZi,
                'conversie' => $conversie,
                'valoare_medie' => $valoareMedie,
                'zile_activitate' => intval($vanzariData->zile_activitate ?? 0),
                'progres_zilnic' => $progresZilnic,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
