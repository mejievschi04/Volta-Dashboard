<?php

namespace App\Console\Commands;

use App\Models\MobileAnalyticsEvent;
use App\Models\MobileCrash;
use App\Models\MobileFeedbackReport;
use App\Support\DashboardCache;
use App\Support\MobileDailyRollup;
use App\Support\MobileRetention;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class PruneMobileTelemetry extends Command
{
    protected $signature = 'mobile:prune
                            {--days= : Păstrează ultimele N zile (implicit config mobile.retention_days)}
                            {--dry-run : Doar numără, fără ștergere}';

    protected $description = 'Numără evenimentele și crash-urile mai vechi de 3 săptămâni, le salvează ca totaluri zilnice, apoi le șterge.';

    public function handle(): int
    {
        $days = max(1, (int) ($this->option('days') ?: MobileRetention::days()));
        $cutoff = now()->subDays($days)->startOfDay();
        $dry = (bool) $this->option('dry-run');

        $this->info(($dry ? '[dry-run] ' : '')."Retention {$days} zile. Șterg înregistrări cu occurred_at < {$cutoff->toDateTimeString()}");

        $eventCount = $this->pruneEvents($cutoff, $dry);
        $crashCount = $this->pruneCrashes($cutoff, $dry);
        $screenshotCount = $this->clearOldScreenshots($cutoff, $dry);
        $logCount = $this->pruneLogFiles($days, $dry);

        $this->table(
            ['Sursă', 'Găsite / procesate'],
            [
                ['Evenimente mobile', (string) $eventCount],
                ['Crash-uri', (string) $crashCount],
                ['Screenshot-uri feedback golite', (string) $screenshotCount],
                ['Fișiere log Laravel', (string) $logCount],
            ]
        );

        if (! $dry && ($eventCount + $crashCount + $screenshotCount) > 0) {
            DashboardCache::bump();
        }

        return self::SUCCESS;
    }

    private function pruneEvents(\DateTimeInterface $cutoff, bool $dry): int
    {
        if (! Schema::hasTable('mobile_analytics_events')) {
            return 0;
        }

        $count = MobileAnalyticsEvent::query()->where('occurred_at', '<', $cutoff)->count();
        if ($count === 0) {
            return 0;
        }

        if ($dry) {
            return $count;
        }

        $min = MobileAnalyticsEvent::query()->where('occurred_at', '<', $cutoff)->min('occurred_at');
        if ($min) {
            MobileDailyRollup::upsertEventRange(
                Carbon::parse($min)->startOfDay(),
                Carbon::parse($cutoff)->subSecond()
            );
        }

        $this->deleteInChunks('mobile_analytics_events', $cutoff);

        return $count;
    }

    private function pruneCrashes(\DateTimeInterface $cutoff, bool $dry): int
    {
        if (! Schema::hasTable('mobile_crashes')) {
            return 0;
        }

        $count = MobileCrash::query()->where('occurred_at', '<', $cutoff)->count();
        if ($count === 0) {
            return 0;
        }

        if ($dry) {
            return $count;
        }

        $min = MobileCrash::query()->where('occurred_at', '<', $cutoff)->min('occurred_at');
        if ($min) {
            MobileDailyRollup::upsertCrashRange(
                Carbon::parse($min)->startOfDay(),
                Carbon::parse($cutoff)->subSecond()
            );
        }

        $this->deleteInChunks('mobile_crashes', $cutoff);

        return $count;
    }

    private function clearOldScreenshots(\DateTimeInterface $cutoff, bool $dry): int
    {
        if (! Schema::hasTable('mobile_feedback_reports')) {
            return 0;
        }

        $query = MobileFeedbackReport::query()
            ->where('occurred_at', '<', $cutoff)
            ->whereNotNull('screenshot_base64');
        $count = $query->count();
        if ($count === 0 || $dry) {
            return $count;
        }

        MobileFeedbackReport::query()
            ->where('occurred_at', '<', $cutoff)
            ->whereNotNull('screenshot_base64')
            ->update([
                'screenshot_base64' => null,
                'has_screenshot' => false,
            ]);

        return $count;
    }

    private function deleteInChunks(string $table, \DateTimeInterface $cutoff): void
    {
        do {
            $deleted = DB::table($table)
                ->where('occurred_at', '<', $cutoff)
                ->limit(2000)
                ->delete();
        } while ($deleted > 0);
    }

    private function pruneLogFiles(int $days, bool $dry): int
    {
        $dir = storage_path('logs');
        if (! is_dir($dir)) {
            return 0;
        }

        $cutoffTs = now()->subDays($days)->startOfDay()->timestamp;
        $removed = 0;

        foreach (File::files($dir) as $file) {
            $name = $file->getFilename();
            if ($name === 'laravel.log' || $name === '.gitignore') {
                continue;
            }
            if ($file->getMTime() >= $cutoffTs) {
                continue;
            }
            if ($dry) {
                $removed++;
                continue;
            }
            if (@unlink($file->getPathname())) {
                $removed++;
            }
        }

        return $removed;
    }
}
