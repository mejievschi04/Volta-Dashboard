<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ComenziConversieController extends Controller
{
    /**
     * Date doar din 1C – 1C nu oferă comenzi pe zi.
     * Returnează structură goală pentru grafic Comenzi vs Conversie.
     */
    public function index(Request $request)
    {
        $luna = $request->get('month', date('Y-m'));
        return response()->json([
            'success' => true,
            'labels' => [],
            'comenzi' => [],
            'conversie' => [],
            'month' => $luna
        ]);
    }
}
