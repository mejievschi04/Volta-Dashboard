<?php

namespace App\Support;

/**
 * Zile lucrătoare pentru KPI plan — exclude duminicile (fără vânzări).
 */
final class ZileLucratoare
{
    /**
     * Numără zilele din intervalul [from, to], inclusiv, fără duminici.
     */
    public static function countExcludingSundays(string $fromYmd, string $toYmd): int
    {
        $from = strtotime($fromYmd);
        $to = strtotime($toYmd);
        if ($from === false || $to === false || $from > $to) {
            return 0;
        }

        $count = 0;
        for ($ts = $from; $ts <= $to; $ts += 86400) {
            if ((int) date('w', $ts) !== 0) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Zile lucrătoare trecute și rămase într-o lună (YYYY-MM).
     *
     * @return array{trecute: int, ramase: int}
     */
    public static function pentruLuna(string $lunaYm, ?string $todayYmd = null): array
    {
        $monthStart = $lunaYm . '-01';
        $monthEnd = date('Y-m-t', strtotime($monthStart));
        $today = $todayYmd ?? date('Y-m-d');
        $monthStartTs = strtotime($monthStart);
        $monthEndTs = strtotime($monthEnd);
        $todayTs = strtotime($today);

        if ($todayTs < $monthStartTs) {
            return [
                'trecute' => 0,
                'ramase' => self::countExcludingSundays($monthStart, $monthEnd),
            ];
        }

        if ($todayTs > $monthEndTs) {
            return [
                'trecute' => self::countExcludingSundays($monthStart, $monthEnd),
                'ramase' => 0,
            ];
        }

        $endTrecut = min($today, $monthEnd);
        $startRamase = date('Y-m-d', strtotime($endTrecut . ' +1 day'));
        $ramase = $startRamase > $monthEnd
            ? 0
            : self::countExcludingSundays($startRamase, $monthEnd);

        return [
            'trecute' => self::countExcludingSundays($monthStart, $endTrecut),
            'ramase' => $ramase,
        ];
    }
}
