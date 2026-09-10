<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Http\Request;

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

    public static function defaultStart(): Carbon
    {
        return now()->subDays(self::days() - 1)->startOfDay();
    }

    /** @return array{0: Carbon, 1: Carbon} */
    public static function resolvePeriod(Request $request): array
    {
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

        $minStart = self::cutoff();
        if ($start->lt($minStart)) {
            $start = $minStart->copy();
        }

        return [$start, $end];
    }

    /** @return list<array{label: string, start: string, end: string}> */
    public static function presets(): array
    {
        $days = self::days();
        $today = now()->format('Y-m-d');
        $monthStart = now()->startOfMonth();
        $minStart = self::cutoff();
        if ($monthStart->lt($minStart)) {
            $monthStart = $minStart->copy();
        }

        return [
            ['label' => '7 zile', 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => $today],
            ['label' => $days.' zile', 'start' => self::defaultStart()->format('Y-m-d'), 'end' => $today],
            ['label' => 'Luna curentă', 'start' => $monthStart->format('Y-m-d'), 'end' => $today],
        ];
    }
}
