<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VanzariZilniceController extends Controller
{
    /**
     * Date doar din 1C – 1C nu oferă breakdown zilnic, doar per perioadă.
     * Returnează structură goală pentru grafic.
     */
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        return response()->json([
            'success' => true,
            'labels' => [],
            'vanzari' => [],
            'month' => $luna
        ]);
    }
}
