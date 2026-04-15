<?php

namespace App\Support;

/**
 * Etichete lună + an în română (ex. „Martie 2024”), independent de APP_LOCALE / Carbon.
 */
final class LunaRomana
{
    /** @var array<int, string> */
    private const LUNI = [
        1 => 'Ianuarie',
        2 => 'Februarie',
        3 => 'Martie',
        4 => 'Aprilie',
        5 => 'Mai',
        6 => 'Iunie',
        7 => 'Iulie',
        8 => 'August',
        9 => 'Septembrie',
        10 => 'Octombrie',
        11 => 'Noiembrie',
        12 => 'Decembrie',
    ];

    public static function labelFromYm(string $ym): string
    {
        $ym = trim($ym);
        if (! preg_match('/^(\d{4})-(\d{1,2})$/', $ym, $m)) {
            return $ym;
        }
        $year = (int) $m[1];
        $month = (int) $m[2];
        $name = self::LUNI[$month] ?? ('Luna '.$month);

        return $name.' '.$year;
    }
}
