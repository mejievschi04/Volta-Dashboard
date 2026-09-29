<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/* Sincronizări zilnice la 05:00, 12:00 și 16:00 (ora României) */
Schedule::command('1c:fetch-kpi --sync')->cron('0 5,12,16 * * *')->timezone('Europe/Bucharest');
Schedule::command('ga4:sync')->cron('0 5,12,16 * * *')->timezone('Europe/Bucharest');
Schedule::command('mobile:rollup')->hourly()->timezone('Europe/Bucharest');
Schedule::command('mobile:prune')->dailyAt('03:15')->timezone('Europe/Bucharest');
