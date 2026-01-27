<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrafficSource;
use Illuminate\Support\Facades\DB;

class SesiuniController extends Controller
{
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        
        try {
            $rows = TrafficSource::selectRaw('
                date,
                DATE_FORMAT(date, "%d.%m.%Y") as data_formatata,
                SUM(visits) as total_sesiuni
            ')
            ->where('source', 'total')
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$luna])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
            $labels = [];
            $sesiuni = [];
            
            foreach ($rows as $row) {
                $labels[] = $row->data_formatata;
                $sesiuni[] = intval($row->total_sesiuni);
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'sesiuni' => $sesiuni,
                'month' => $luna,
                'total' => array_sum($sesiuni)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
