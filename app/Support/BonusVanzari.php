<?php

namespace App\Support;

/**
 * Bonusul operatorului din vânzări: procent fix din profitul brut (1C).
 */
final class BonusVanzari
{
    public const RATE = 0.052;

    public static function dinProfit(float $profitBrut): float
    {
        return round(max(0.0, $profitBrut) * self::RATE, 2);
    }

    public static function procentLabel(): string
    {
        return rtrim(rtrim(number_format(self::RATE * 100, 2, ',', ''), '0'), ',').'%';
    }
}
