<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use Illuminate\Support\Facades\DB;

class VanzariZilniceController extends Controller
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
            
            $labels = [];
            $vanzari = [];
            
            foreach ($rows as $row) {
                $labels[] = $row->data_formatata;
                $vanzari[] = floatval($row->suma_cu_tva);
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'vanzari' => $vanzari,
                'month' => $luna
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
