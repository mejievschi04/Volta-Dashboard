<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RapoarteController extends Controller
{
    public function index()
    {
        return view('rapoarte.comparare');
    }

    public function istoric()
    {
        return view('rapoarte.istoric');
    }

    /**
     * URL vechi: redirecționare către /rapoarte (conținutul comparării este pagina principală Rapoarte).
     */
    public function comparare()
    {
        return redirect()->route('rapoarte');
    }
}
