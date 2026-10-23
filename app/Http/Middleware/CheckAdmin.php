<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * @param  string  ...$alsoAllowed  roluri acceptate în plus față de admin (ex. "dev")
     */
    public function handle(Request $request, Closure $next, string ...$alsoAllowed): Response
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $allowed = $user->isAdmin() || (in_array('dev', $alsoAllowed, true) && $user->isDev());

        if (! $allowed) {
            return redirect()->route('dashboard')->with('error', 'Nu aveti permisiunea de a accesa aceasta pagina.');
        }

        return $next($request);
    }
}
