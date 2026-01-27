<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vanzari;
use App\Models\TrafficSource;
use Illuminate\Support\Facades\DB;

class ComenziConversieController extends Controller
{
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        
        try {
            // Comenzi zilnice (folosim nr_vanzari din tabel)
            $comenziRows = Vanzari::selectRaw('
                data,
                DATE_FORMAT(data, "%d.%m.%Y") as data_formatata,
                SUM(nr_vanzari) as comenzi_zi
            ')
            ->whereRaw("DATE_FORMAT(data, '%Y-%m') = ?", [$luna])
            ->groupBy('data')
            ->orderBy('data', 'ASC')
            ->get();
            
            // Sesiuni zilnice
            $sesiuniRows = TrafficSource::selectRaw('
                date,
                DATE_FORMAT(date, "%d.%m.%Y") as data_formatata,
                SUM(visits) as total_sesiuni
            ')
            ->where('source', 'total')
            ->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$luna])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->get();
            
            // Combină datele pe zile
            $labels = [];
            $comenzi = [];
            $conversie = [];
            
            // Creăm un map pentru sesiuni
            $sesiuniMap = [];
            foreach ($sesiuniRows as $row) {
                $sesiuniMap[$row->data_formatata] = intval($row->total_sesiuni);
            }
            
            foreach ($comenziRows as $row) {
                $dataFormatata = $row->data_formatata;
                $labels[] = $dataFormatata;
                $comenziZi = intval($row->comenzi_zi);
                $comenzi[] = $comenziZi;
                
                // Conversie = (comenzi / sesiuni) * 100
                $sesiuniZi = $sesiuniMap[$dataFormatata] ?? 0;
                $conv = $sesiuniZi > 0 ? round(($comenziZi / $sesiuniZi) * 100, 2) : 0;
                $conversie[] = $conv;
            }
            
            return response()->json([
                'success' => true,
                'labels' => $labels,
                'comenzi' => $comenzi,
                'conversie' => $conversie,
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
