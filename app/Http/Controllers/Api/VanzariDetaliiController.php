<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VanzariDetaliiController extends Controller
{
    /**
     * Date doar din 1C – 1C nu oferă date pe zi.
     * Returnează totaluri 0 și listă goală (modalul cu tabel zilnic a fost eliminat).
     */
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        return response()->json([
            'success' => true,
            'month' => $luna,
            'data' => [],
            'total_fara_tva' => 0,
            'total_cu_tva' => 0,
            'total_profit' => 0
        ]);
    }
}
