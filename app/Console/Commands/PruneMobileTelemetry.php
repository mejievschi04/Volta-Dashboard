<?php

namespace App\Console\Commands;

use App\Models\MobileAnalyticsEvent;
use App\Models\MobileCrash;
use App\Models\MobileFeedbackReport;
use App\Support\DashboardCache;
use App\Support\MobileRetention;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

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

        if (Schema::hasTable('mobile_event_daily_rollups')) {
            $rows = MobileAnalyticsEvent::query()
                ->where('occurred_at', '<', $cutoff)
                ->selectRaw('DATE(occurred_at) as day, event_name, COUNT(*) as total, COUNT(DISTINCT session_id) as sessions, COUNT(DISTINCT mobile_user_id) as users')
                ->groupBy(DB::raw('DATE(occurred_at)'), 'event_name')
                ->get();

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

        if (Schema::hasTable('mobile_crash_daily_rollups')) {
            $rows = MobileCrash::query()
                ->where('occurred_at', '<', $cutoff)
                ->selectRaw('DATE(occurred_at) as day, COUNT(*) as total, SUM(CASE WHEN is_fatal = 1 THEN 1 ELSE 0 END) as fatal, COUNT(DISTINCT fingerprint) as fingerprints')
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->get();

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
