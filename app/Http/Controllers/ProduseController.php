<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProduseController extends Controller
{
    public function index()
    {
        return view('produse.index');
    }
}
