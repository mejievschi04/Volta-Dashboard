<?php

namespace App\Console\Commands;

use App\Models\MobileAnalyticsEvent;
use App\Support\DashboardCache;
use App\Support\MobileDailyRollup;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RollupMobileTelemetry extends Command
{
    protected $signature = 'mobile:rollup
                            {--from= : Prima zi (Y-m-d)}
                            {--to= : Ultima zi închisă (Y-m-d, implicit ieri)}';

    protected $description = 'Calculează totaluri zilnice pentru evenimente și crash-uri, fără să șteargă datele brute.';

    public function handle(): int
    {
        $to = $this->option('to')
            ? Carbon::parse((string) $this->option('to'))->endOfDay()
            : now()->subDay()->endOfDay();

        if ($this->option('from')) {
            $from = Carbon::parse((string) $this->option('from'))->startOfDay();
        } else {
            $minEvent = DashboardCache::tableExists('mobile_analytics_events')
                ? MobileAnalyticsEvent::query()->min('occurred_at')
                : null;
            $from = $minEvent
                ? Carbon::parse($minEvent)->startOfDay()
                : $to->copy()->startOfDay();
        }

        if ($to->lt($from)) {
            $this->info('Nimic de agregat.');

            return self::SUCCESS;
        }

        $written = 0;
        $cursor = $from->copy();
        while ($cursor->lte($to)) {
            $chunkEnd = $cursor->copy()->addDays(6)->endOfDay();
            if ($chunkEnd->gt($to)) {
                $chunkEnd = $to->copy();
            }
            $written += MobileDailyRollup::upsertEventRange($cursor, $chunkEnd);
            $written += MobileDailyRollup::upsertCrashRange($cursor, $chunkEnd);
            $cursor = $chunkEnd->copy()->addDay()->startOfDay();
        }

        DashboardCache::bump();
        $this->info("Rollup {$from->toDateString()} – {$to->toDateString()}. Rânduri scrise: {$written}.");

        return self::SUCCESS;
    }
}
