<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use Illuminate\Support\Facades\DB;

class VanzariDetaliiController extends Controller
{
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        
        try {
            $rows = Vanzari::selectRaw('
                data,
                DATE_FORMAT(data, "%d.%m.%Y") as data_formatata,
                suma_fara_tva,
                suma_cu_tva,
                profit
            ')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$luna])
            ->orderBy('data', 'ASC')
            ->get();
            
            // Calculează totalurile
            $totalFaraTva = 0;
            $totalCuTva = 0;
            $totalProfit = 0;
            
            $datele = [];
            foreach ($rows as $row) {
                $faraTva = floatval($row->suma_fara_tva);
                $cuTva = floatval($row->suma_cu_tva);
                $profit = floatval($row->profit);
                
                $totalFaraTva += $faraTva;
                $totalCuTva += $cuTva;
                $totalProfit += $profit;
                
                $datele[] = [
                    'data' => $row->data_formatata,
                    'fara_tva' => $faraTva,
                    'cu_tva' => $cuTva,
                    'profit' => $profit
                ];
            }
            
            return response()->json([
                'success' => true,
                'month' => $luna,
                'data' => $datele,
                'total_fara_tva' => $totalFaraTva,
                'total_cu_tva' => $totalCuTva,
                'total_profit' => $totalProfit
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
