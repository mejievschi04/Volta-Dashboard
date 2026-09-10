<?php

namespace App\Support;

use App\Models\MobileAnalyticsEvent;
use App\Models\MobileCrash;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MobileDailyRollup
{
    public static function upsertEventRange(Carbon $from, Carbon $to): int
    {
        if (! DashboardCache::tableExists('mobile_analytics_events')
            || ! DashboardCache::tableExists('mobile_event_daily_rollups')) {
            return 0;
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        if ($to->lt($from)) {
            return 0;
        }

        $rows = MobileAnalyticsEvent::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('DATE(occurred_at) as day, event_name, COUNT(*) as total, COUNT(DISTINCT session_id) as sessions, COUNT(DISTINCT mobile_user_id) as users')
            ->groupBy(DB::raw('DATE(occurred_at)'), 'event_name')
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        $now = now();
        $payload = $rows->map(fn ($row) => [
            'day' => $row->day,
            'event_name' => (string) $row->event_name,
            'total' => (int) $row->total,
            'sessions' => (int) $row->sessions,
            'users' => (int) $row->users,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($payload, 200) as $chunk) {
            DB::table('mobile_event_daily_rollups')->upsert(
                $chunk,
                ['day', 'event_name'],
                ['total', 'sessions', 'users', 'updated_at']
            );
        }

        return count($payload);
    }

    public static function upsertCrashRange(Carbon $from, Carbon $to): int
    {
        if (! DashboardCache::tableExists('mobile_crashes')
            || ! DashboardCache::tableExists('mobile_crash_daily_rollups')) {
            return 0;
        }

        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();
        if ($to->lt($from)) {
            return 0;
        }

        $rows = MobileCrash::query()
            ->whereBetween('occurred_at', [$from, $to])
            ->selectRaw('DATE(occurred_at) as day, COUNT(*) as total, SUM(CASE WHEN is_fatal = 1 THEN 1 ELSE 0 END) as fatal, COUNT(DISTINCT fingerprint) as fingerprints')
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->get();

        if ($rows->isEmpty()) {
            return 0;
        }

        $now = now();
        $payload = $rows->map(fn ($row) => [
            'day' => $row->day,
            'total' => (int) $row->total,
            'fatal' => (int) $row->fatal,
            'fingerprints' => (int) $row->fingerprints,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        foreach (array_chunk($payload, 200) as $chunk) {
            DB::table('mobile_crash_daily_rollups')->upsert(
                $chunk,
                ['day'],
                ['total', 'fatal', 'fingerprints', 'updated_at']
            );
        }

        return count($payload);
    }

    /** Ultima zi închisă (ieri) pentru care există rollup de evenimente. */
    public static function lastEventDay(): ?Carbon
    {
        if (! DashboardCache::tableExists('mobile_event_daily_rollups')) {
            return null;
        }

        $day = DB::table('mobile_event_daily_rollups')->max('day');

        return $day ? Carbon::parse($day)->startOfDay() : null;
    }

    public static function lastCrashDay(): ?Carbon
    {
        if (! DashboardCache::tableExists('mobile_crash_daily_rollups')) {
            return null;
        }

        $day = DB::table('mobile_crash_daily_rollups')->max('day');

        return $day ? Carbon::parse($day)->startOfDay() : null;
    }

    /**
     * @return array{total: int, fatal: int, fingerprints: int}
     */
    public static function crashTotals(Carbon $from, Carbon $to): array
    {
        $empty = ['total' => 0, 'fatal' => 0, 'fingerprints' => 0];
        if (! DashboardCache::tableExists('mobile_crash_daily_rollups') || $to->lt($from)) {
            return $empty;
        }

        $row = DB::table('mobile_crash_daily_rollups')
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('SUM(total) as total, SUM(fatal) as fatal, SUM(fingerprints) as fingerprints')
            ->first();

        return [
            'total' => (int) ($row->total ?? 0),
            'fatal' => (int) ($row->fatal ?? 0),
            'fingerprints' => (int) ($row->fingerprints ?? 0),
        ];
    }

    /**
     * @return array{totals: array<string, int>, sessions: int, users: int}
     */
    public static function eventTotals(Carbon $from, Carbon $to): array
    {
        $empty = ['totals' => [], 'sessions' => 0, 'users' => 0];
        if (! DashboardCache::tableExists('mobile_event_daily_rollups') || $to->lt($from)) {
            return $empty;
        }

        $rows = DB::table('mobile_event_daily_rollups')
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->select('event_name', DB::raw('SUM(total) as total'))
            ->groupBy('event_name')
            ->get();

        $totals = [];
        foreach ($rows as $row) {
            $totals[(string) $row->event_name] = (int) $row->total;
        }

        $uniques = DB::query()
            ->fromSub(
                DB::table('mobile_event_daily_rollups')
                    ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
                    ->selectRaw('day, MAX(sessions) as day_sessions, MAX(users) as day_users')
                    ->groupBy('day'),
                'per_day'
            )
            ->selectRaw('SUM(day_sessions) as sessions, SUM(day_users) as users')
            ->first();

        return [
            'totals' => $totals,
            'sessions' => (int) ($uniques->sessions ?? 0),
            'users' => (int) ($uniques->users ?? 0),
        ];
    }
}
