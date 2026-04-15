<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $role = strtolower(trim((string) ($user->role ?? '')));

        // Verifică dacă utilizatorul este admin
        if ($role !== 'admin' && $role !== 'administrator') {
            return redirect()->route('dashboard')->with('error', 'Nu aveți permisiunea de a accesa această pagină.');
        }

        return $next($request);
    }
}
