<?php

// Polyfill mb_split() dacă extensia mbstring lipsește (opțional; pe producție recomandat mbstring)
if (! function_exists('mb_split')) {
    function mb_split($pattern, $string, $limit = -1) {
        $result = @preg_split('/' . $pattern . '/u', $string, $limit === -1 ? -1 : $limit);
        return $result !== false ? $result : [$string];
    }
}

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
