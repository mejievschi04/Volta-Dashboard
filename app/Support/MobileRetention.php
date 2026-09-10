<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MobileRetention
{
    public static function days(): int
    {
        return max(1, (int) config('mobile.retention_days', 21));
    }

    public static function cutoff(): Carbon
    {
        return now()->subDays(self::days())->startOfDay();
    }

    /** Implicit ca înainte: ultimele 30 de zile. */
    public static function defaultStart(): Carbon
    {
        return now()->subDays(29)->startOfDay();
    }

    /** Prima zi pentru care există evenimente, rollup-uri, erori sau mesaje. */
    public static function earliestStart(): Carbon
    {
        return DashboardCache::remember('mobile:earliest-start:v1', 600, function () {
            $dates = [];

            foreach ([
                ['mobile_analytics_events', 'occurred_at'],
                ['mobile_crashes', 'occurred_at'],
                ['mobile_feedback_reports', 'occurred_at'],
                ['mobile_event_daily_rollups', 'day'],
                ['mobile_crash_daily_rollups', 'day'],
            ] as [$table, $column]) {
                if (! DashboardCache::tableExists($table)) {
                    continue;
                }

                $value = DB::table($table)->min($column);
                if ($value) {
                    $dates[] = $value;
                }
            }

            if ($dates === []) {
                return self::defaultStart();
            }

            return Carbon::parse(min($dates))->startOfDay();
        });
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public static function resolvePeriod(Request $request): array
    {
        if ($request->boolean('all') || $request->query('period') === 'all') {
            return [self::earliestStart(), now()->endOfDay()];
        }

        try {
            $start = $request->filled('start')
                ? Carbon::parse((string) $request->query('start'))->startOfDay()
                : self::defaultStart();
        } catch (\Throwable) {
            $start = self::defaultStart();
        }

        try {
            $end = $request->filled('end')
                ? Carbon::parse((string) $request->query('end'))->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable) {
            $end = now()->endOfDay();
        }

        if ($end->lt($start)) {
            $end = $start->copy()->endOfDay();
        }

        return [$start, $end];
    }

    /** @return list<array{label: string, start: string, end: string}> */
    public static function presets(): array
    {
        $today = now()->format('Y-m-d');
        $allStart = self::earliestStart()->format('Y-m-d');

        return [
            ['label' => '7 zile', 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => $today],
            ['label' => '30 zile', 'start' => now()->subDays(29)->format('Y-m-d'), 'end' => $today],
            ['label' => 'Luna curentă', 'start' => now()->startOfMonth()->format('Y-m-d'), 'end' => $today],
            ['label' => '90 zile', 'start' => now()->subDays(89)->format('Y-m-d'), 'end' => $today],
            ['label' => 'Toată perioada', 'start' => $allStart, 'end' => $today],
        ];
    }
}
