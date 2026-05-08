<?php

namespace App\Http\Middleware;

use App\Support\DevMode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DevModeLock
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! DevMode::enabled() || $this->isAllowedPath($request)) {
            return $next($request);
        }

        return response()
            ->view('dev-mode.locked', ['state' => DevMode::state()], 503)
            ->header('Retry-After', '300');
    }

    private function isAllowedPath(Request $request): bool
    {
        if ($request->is('dev-mode') || $request->is('dev-mode/*')) {
            return true;
        }

        if ($request->is('login') || $request->is('logout') || $request->is('up')) {
            return true;
        }

        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*') || $request->is('favicon.ico')) {
            return true;
        }

        return false;
    }
}
