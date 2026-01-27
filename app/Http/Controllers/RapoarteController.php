<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RapoarteController extends Controller
{
    public function index()
    {
        return view('rapoarte.index');
    }

    public function istoric()
    {
        return view('rapoarte.istoric');
    }

    public function comparare()
    {
        return view('rapoarte.comparare');
    }
}
