<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PlanVanzari;
use App\Models\TrafficSource;
use App\Models\OnecKpiSync;
use App\Models\Livrare;
use Illuminate\Support\Facades\Auth;

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
            
            // Date doar din 1C (onec_kpi_syncs)
            $onecSync = OnecKpiSync::whereRaw("DATE_FORMAT(period_start, '%Y-%m') = ?", [$luna])
                ->orderByDesc('created_at')
                ->first();

            $vanzariLuna = $onecSync ? floatval($onecSync->vanzari_fara_tva) : 0;
            $vanzariCuTva = $onecSync ? floatval($onecSync->vanzari_cu_tva) : 0;
            $profit = $onecSync ? floatval($onecSync->profit) : 0;
            $comenzi = $onecSync ? intval($onecSync->nr_comenzi) : 0;

            $totalZilePentruComenziZi = intval(date('t', strtotime($luna . '-01')));
            if ($onecSync && $onecSync->period_start && $onecSync->period_end) {
                $startStr = is_object($onecSync->period_start) ? $onecSync->period_start->format('Y-m-d') : (string) $onecSync->period_start;
                $endStr = is_object($onecSync->period_end) ? $onecSync->period_end->format('Y-m-d') : (string) $onecSync->period_end;
                $tsStart = strtotime($startStr);
                $tsEnd = strtotime($endStr);
                $totalZilePentruComenziZi = max(1, (int) (($tsEnd - $tsStart) / 86400) + 1);
            }
            
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
            $zileLuna = intval(date('t', strtotime($luna . '-01')));
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
            
            $comenziZi = $totalZilePentruComenziZi > 0 ? round($comenzi / $totalZilePentruComenziZi, 1) : 0;
            $totalSesiuni = intval($sesiuniData->total_sesiuni ?? 0);
            $conversie = $totalSesiuni > 0 ? round(($comenzi / $totalSesiuni) * 100, 2) : 0;
            
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
            
            // Valoare medie comandă (CEC mediu = suma fără TVA / nr comenzi)
            $cecMediu = $comenzi > 0 ? round($vanzariLuna / $comenzi, 2) : 0;
            
            // Total livrări în luna selectată (din tabelul livrari)
            $totalLivrariLuna = Livrare::whereRaw("DATE_FORMAT(data_livrarii, '%Y-%m') = ?", [$luna])->count();
            
            // Pickup = total comenzi - livrări
            $pickup = max(0, $comenzi - $totalLivrariLuna);
            
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
                'valoare_medie' => $cecMediu,
                'cec_mediu' => $cecMediu,
                'total_livrari_luna' => $totalLivrariLuna,
                'pickup' => $pickup,
                'zile_activitate' => 0,
                'progres_zilnic' => $progresZilnic,
                'kpi_source' => $onecSync ? 'onec_db' : 'onec_db',
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updatePlan(Request $request)
    {
        // Verifică dacă utilizatorul este admin
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'Nu sunteți autentificat.'
            ], 401);
        }

        $user = Auth::user();
        $role = strtolower($user->role ?? '');
        
        if ($role !== 'admin' && $role !== 'administrator') {
            return response()->json([
                'success' => false,
                'error' => 'Nu aveți permisiunea de a actualiza planul.'
            ], 403);
        }

        $request->validate([
            'month' => 'required|string|regex:/^\d{4}-\d{2}$/',
            'valoare' => 'required|numeric|min:0'
        ], [
            'month.required' => 'Luna este obligatorie.',
            'month.regex' => 'Formatul lunii trebuie să fie YYYY-MM.',
            'valoare.required' => 'Valoarea este obligatorie.',
            'valoare.numeric' => 'Valoarea trebuie să fie un număr.',
            'valoare.min' => 'Valoarea trebuie să fie pozitivă.'
        ]);

        try {
            $luna = $request->get('month');
            $valoare = floatval($request->get('valoare'));
            
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
            
            if (empty($lunaNume)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Lună invalidă.'
                ], 400);
            }
            
            // Caută sau creează planul pentru luna respectivă
            $planData = PlanVanzari::where('an', $an)
                ->where('luna', $lunaNume)
                ->first();
            
            if ($planData) {
                $planData->valoare = $valoare;
                $planData->save();
            } else {
                PlanVanzari::create([
                    'an' => $an,
                    'luna' => $lunaNume,
                    'valoare' => $valoare
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Planul a fost actualizat cu succes.',
                'plan_luna' => $valoare
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
