<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Sincronizări zilnice la 07:00 (ora României) */
Schedule::command('1c:fetch-kpi --sync')->dailyAt('07:00')->timezone('Europe/Bucharest');
Schedule::command('ga4:sync')->dailyAt('07:00')->timezone('Europe/Bucharest');
