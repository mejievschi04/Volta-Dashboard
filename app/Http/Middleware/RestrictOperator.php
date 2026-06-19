<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RestrictOperator
{
    /** Rute pe care un utilizator cu rol Operator le poate accesa. */
    private const ALLOWED_ROUTES = [
        'datele-mele',
        'api.kpi',
        'livrari',
        'livrari.export-data',
        'livrari.check-comanda',
        'livrari.store',
        'livrari.update',
        'livrari.destroy',
        'setari',
        'setari.update',
        'setari.password',
        'operatori.photo.profil',
        'operatori.photo.coperta',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();
        if (!method_exists($user, 'isOperator') || !$user->isOperator()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        return redirect()->route('datele-mele');
    }
}
