<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class DbDate
{
    public static function format(string $column, string $format): string
    {
        return self::driver() === 'sqlite'
            ? "strftime('{$format}', {$column})"
            : "DATE_FORMAT({$column}, '{$format}')";
    }

    public static function month(string $column): string
    {
        return self::format($column, '%Y-%m');
    }

    public static function day(string $column): string
    {
        return self::format($column, '%d.%m.%Y');
    }

    public static function dayShort(string $column): string
    {
        return self::format($column, '%d.%m');
    }

    public static function monthLabel(string $column): string
    {
        return self::format($column, '%m.%Y');
    }

    public static function yearWeek(string $column): string
    {
        return self::driver() === 'sqlite'
            ? "strftime('%Y-%W', {$column})"
            : "YEARWEEK({$column}, 1)";
    }

    private static function driver(): string
    {
        return DB::connection()->getDriverName();
    }
}
