<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckDev
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        if (! Auth::user()->isDev()) {
            return redirect()->route('dashboard')->with('error', 'Doar rolul Dev poate accesa aceasta zona.');
        }

        return $next($request);
    }
}
