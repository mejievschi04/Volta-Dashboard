<?php

// Suprimăm erorile minore din ServeCommand care apar la parsing-ul output-ului PHP server
// Această eroare apare când Laravel încearcă să parseze output-ul PHP built-in server
// și formatul nu se potrivește cu regex-ul așteptat (de ex. pentru favicon.ico requests)
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    if (str_contains($errfile, 'ServeCommand.php') && 
        str_contains($errstr, 'Undefined array key')) {
        return true; // Suprimă eroarea
    }
    return false; // Continuă cu handler-ul default
}, E_WARNING | E_NOTICE);

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\FetchOneCKpi::class,
        \App\Console\Commands\SyncGa4Traffic::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Adăugăm logging pentru middleware auth
        $middleware->redirectGuestsTo(fn (Request $request) => route('login'));
        
        // Configurează TrustProxies pentru ngrok și alte proxy-uri
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Suprimăm erorile minore din ServeCommand care apar la parsing-ul output-ului PHP server
        // Această eroare apare când Laravel încearcă să parseze output-ul PHP built-in server
        // și formatul nu se potrivește cu regex-ul așteptat (de ex. pentru favicon.ico requests)
        $exceptions->report(function (\ErrorException $exception) {
            if (str_contains($exception->getFile(), 'ServeCommand.php') && 
                str_contains($exception->getMessage(), 'Undefined array key')) {
                return false; // Nu raporta eroarea
            }
        });
        
        // Suprimăm afișarea erorii în consolă
        $exceptions->render(function (\ErrorException $exception, $request) {
            if (str_contains($exception->getFile(), 'ServeCommand.php') && 
                str_contains($exception->getMessage(), 'Undefined array key')) {
                // Pentru console commands, returnăm null pentru a suprima output-ul
                if (app()->runningInConsole()) {
                    return null;
                }
            }
        });
    })->create();
