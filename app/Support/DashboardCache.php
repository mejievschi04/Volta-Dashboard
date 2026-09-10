<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

class DashboardCache
{
    /**
     * @param  array{0: int, 1: int}  $ttl  [secunde fresh, secunde stale]
     */
    public static function flexible(string $key, array $ttl, Closure $callback): mixed
    {
        return Cache::flexible(self::versioned($key), $ttl, $callback);
    }

    public static function remember(string $key, int $seconds, Closure $callback): mixed
    {
        return Cache::remember(self::versioned($key), $seconds, $callback);
    }

    public static function bump(): void
    {
        if (! Cache::has('dash:v')) {
            Cache::forever('dash:v', 2);

            return;
        }

        Cache::increment('dash:v');
    }

    /** @return array{0: int, 1: int} */
    public static function ttlForMonth(?string $ym): array
    {
        $current = $ym && str_starts_with((string) $ym, date('Y-m'));

        return $current ? [45, 300] : [600, 3600];
    }

    /** @return array{0: int, 1: int} */
    public static function ttlMobile(): array
    {
        return [90, 420];
    }

    /** Cache mai lung pe intervale mari (toată perioada / 90 zile). */
    public static function ttlMobileRange(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        $days = max(1, (int) $start->diff($end)->days + 1);
        $includesToday = $end->format('Y-m-d') >= date('Y-m-d');

        if ($days >= 60) {
            return $includesToday ? [300, 1800] : [900, 7200];
        }
        if ($days >= 14) {
            return $includesToday ? [180, 900] : [600, 3600];
        }

        return $includesToday ? [90, 420] : [300, 1800];
    }

    public static function tableExists(string $table): bool
    {
        return (bool) Cache::remember('schema:has:'.$table, 3600, function () use ($table) {
            return \Illuminate\Support\Facades\Schema::hasTable($table);
        });
    }

    /** @return array{0: int, 1: int} */
    public static function ttlLive(): array
    {
        return [30, 180];
    }

    /** @return array{0: int, 1: int} */
    public static function ttlGa(string $endDate): array
    {
        $includesToday = $endDate >= date('Y-m-d');

        return $includesToday ? [120, 900] : [3600, 86400];
    }

    private static function versioned(string $key): string
    {
        $version = (int) Cache::get('dash:v', 1);

        return 'dash:'.$version.':'.$key;
    }
}
