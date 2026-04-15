<?php

namespace App\Http\Controllers;

use App\Services\CallCenterRaportLunarService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaportLunarController extends Controller
{
    public function __construct(
        private CallCenterRaportLunarService $raportLunar
    ) {}

    public function index(Request $request): View
    {
        $ym = (string) $request->query('month', date('Y-m'));
        $data = $this->raportLunar->build($ym);

        return view('rapoarte.raport-lunar', $data);
    }
}
