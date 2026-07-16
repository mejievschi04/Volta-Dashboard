<?php

namespace App\Http\Controllers;

use App\Models\MobileCrash;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileCrashesController extends Controller
{
    public function index(Request $request)
    {
        return view('mobile.crashes-overview', $this->buildDashboardData($request));
    }

    public function list(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_crashes');
        $crashes = null;

        if ($schemaReady) {
            $crashes = MobileCrash::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->latest('occurred_at')
                ->paginate(100)
                ->withQueryString();
        }

        return view('mobile.crashes-list', compact('start', 'end', 'schemaReady', 'crashes'));
    }

    public function show(Request $request, MobileCrash $crash)
    {
        $schemaReady = Schema::hasTable('mobile_crashes');
        [$start, $end] = $this->resolvePeriod($request);

        return view('mobile.crash-detail', compact('crash', 'schemaReady', 'start', 'end'));
    }

    public function ingest(Request $request): JsonResponse
    {
        $configuredKey = (string) (
            config('services.mobile_crashes.key', '')
            ?: config('services.mobile_analytics.key', '')
        );
        if ($configuredKey !== '') {
            $providedKey = (string) (
                $request->header('X-Mobile-Crashes-Key')
                ?? $request->header('X-Mobile-Analytics-Key')
                ?? $request->bearerToken()
                ?? ''
            );
            if (! hash_equals($configuredKey, $providedKey)) {
                return response()->json(['success' => false, 'error' => 'Invalid crashes key.'], 403);
            }
        }

        $payload = $request->all();
        $crashes = Arr::get($payload, 'crashes');
        if (! is_array($crashes) || ! array_is_list($crashes)) {
            $crashes = [$payload];
        }

        $created = 0;
        $errors = [];

        foreach ($crashes as $index => $rawCrash) {
            if (! is_array($rawCrash)) {
                $errors[$index] = ['crash' => ['Invalid crash payload.']];
                continue;
            }

            $normalized = $this->normalizeCrash($rawCrash, $request);
            $validator = Validator::make($normalized, [
                'fingerprint' => ['nullable', 'string', 'max:64'],
                'error_type' => ['required', 'string', 'max:255'],
                'error_message' => ['nullable', 'string', 'max:1024'],
                'stack_trace' => ['nullable', 'string'],
                'is_fatal' => ['nullable', 'boolean'],
                'screen' => ['nullable', 'string', 'max:255'],
                'session_id' => ['nullable', 'string', 'max:128'],
                'mobile_user_id' => ['nullable', 'string', 'max:64'],
                'device_id' => ['nullable', 'string', 'max:128'],
                'platform' => ['nullable', 'string', 'max:32'],
                'app_version' => ['nullable', 'string', 'max:32'],
                'os_version' => ['nullable', 'string', 'max:64'],
                'device_model' => ['nullable', 'string', 'max:128'],
                'build_number' => ['nullable', 'string', 'max:32'],
                'ip_address' => ['nullable', 'string', 'max:45'],
                'user_agent' => ['nullable', 'string', 'max:2000'],
                'metadata' => ['nullable', 'array'],
                'occurred_at' => ['nullable', 'date'],
            ]);

            if ($validator->fails()) {
                $errors[$index] = $validator->errors()->toArray();
                continue;
            }

            $data = $validator->validated();
            $data['is_fatal'] = array_key_exists('is_fatal', $data) ? (bool) $data['is_fatal'] : true;
            $data['occurred_at'] = $this->parseOccurredAt($data['occurred_at'] ?? null);
            if (empty($data['fingerprint'])) {
                $data['fingerprint'] = $this->buildFingerprint(
                    (string) $data['error_type'],
                    $data['error_message'] ?? null,
                    $data['stack_trace'] ?? null
                );
            }

            MobileCrash::create($data);
            $created++;
        }

        return response()->json([
            'success' => empty($errors),
            'accepted' => $created,
            'errors' => $errors,
        ], empty($errors) ? 202 : 422);
    }

    private function buildDashboardData(Request $request): array
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_crashes');

        $summary = [
            'crashes' => 0,
            'devices' => 0,
            'users' => 0,
            'fatal' => 0,
            'fingerprints' => 0,
        ];
        $topFingerprints = collect();
        $platformBreakdown = collect();
        $recentCrashes = collect();
        $dailyChart = ['labels' => [], 'totals' => []];

        if ($schemaReady) {
            $base = MobileCrash::query()->whereBetween('occurred_at', [$start, $end]);

            $summary['crashes'] = (clone $base)->count();
            $summary['devices'] = (int) (clone $base)->whereNotNull('device_id')->selectRaw('COUNT(DISTINCT device_id) as aggregate')->value('aggregate');
            $summary['users'] = (int) (clone $base)->whereNotNull('mobile_user_id')->selectRaw('COUNT(DISTINCT mobile_user_id) as aggregate')->value('aggregate');
            $summary['fatal'] = (clone $base)->where('is_fatal', true)->count();
            $summary['fingerprints'] = (int) (clone $base)->whereNotNull('fingerprint')->selectRaw('COUNT(DISTINCT fingerprint) as aggregate')->value('aggregate');

            $topFingerprints = (clone $base)
                ->select(
                    'fingerprint',
                    'error_type',
                    DB::raw('MAX(error_message) as error_message'),
                    DB::raw('COUNT(*) as total'),
                    DB::raw('MAX(occurred_at) as last_seen_at')
                )
                ->whereNotNull('fingerprint')
                ->groupBy('fingerprint', 'error_type')
                ->orderByDesc('total')
                ->limit(15)
                ->get();

            $platformBreakdown = (clone $base)
                ->select('platform', DB::raw('COUNT(*) as total'))
                ->groupBy('platform')
                ->orderByDesc('total')
                ->get();

            $recentCrashes = (clone $base)
                ->latest('occurred_at')
                ->limit(40)
                ->get();

            $dailyChart = $this->dailyChart($start, $end);
        }

        return compact(
            'start',
            'end',
            'schemaReady',
            'summary',
            'topFingerprints',
            'platformBreakdown',
            'recentCrashes',
            'dailyChart'
        );
    }

    private function normalizeCrash(array $crash, Request $request): array
    {
        $metadata = Arr::get($crash, 'metadata', []);
        if (! is_array($metadata)) {
            $metadata = ['raw_metadata' => $metadata];
        }

        $errorType = $this->nullableString(
            Arr::get($crash, 'error_type')
            ?? Arr::get($crash, 'type')
            ?? Arr::get($metadata, 'error_type')
            ?? 'Error'
        ) ?? 'Error';

        $errorMessage = $this->nullableString(
            Arr::get($crash, 'error_message')
            ?? Arr::get($crash, 'message')
            ?? Arr::get($metadata, 'error_message')
        );
        if ($errorMessage !== null) {
            $errorMessage = Str::limit($errorMessage, 1024, '');
        }

        $stackTrace = Arr::get($crash, 'stack_trace') ?? Arr::get($crash, 'stack') ?? Arr::get($metadata, 'stack_trace');
        if ($stackTrace !== null && ! is_string($stackTrace)) {
            $stackTrace = json_encode($stackTrace, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        $fingerprint = $this->nullableString(Arr::get($crash, 'fingerprint'));
        if ($fingerprint === null) {
            $fingerprint = $this->buildFingerprint($errorType, $errorMessage, is_string($stackTrace) ? $stackTrace : null);
        }

        $isFatal = Arr::get($crash, 'is_fatal', Arr::get($metadata, 'is_fatal', true));
        if (is_string($isFatal)) {
            $isFatal = filter_var($isFatal, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        return [
            'fingerprint' => $fingerprint,
            'error_type' => $errorType,
            'error_message' => $errorMessage,
            'stack_trace' => is_string($stackTrace) ? $stackTrace : null,
            'is_fatal' => $isFatal === null ? true : (bool) $isFatal,
            'screen' => $this->nullableString(Arr::get($crash, 'screen') ?? Arr::get($crash, 'page') ?? Arr::get($metadata, 'screen')),
            'session_id' => $this->nullableString(Arr::get($crash, 'session_id')),
            'mobile_user_id' => $this->nullableString(Arr::get($crash, 'mobile_user_id') ?? Arr::get($crash, 'user_id')),
            'device_id' => $this->nullableString(Arr::get($crash, 'device_id') ?? Arr::get($crash, 'installation_id')),
            'platform' => $this->nullableString(Arr::get($crash, 'platform')),
            'app_version' => $this->nullableString(Arr::get($crash, 'app_version')),
            'os_version' => $this->nullableString(Arr::get($crash, 'os_version') ?? Arr::get($metadata, 'os_version')),
            'device_model' => $this->nullableString(Arr::get($crash, 'device_model') ?? Arr::get($metadata, 'device_model')),
            'build_number' => $this->nullableString(Arr::get($crash, 'build_number') ?? Arr::get($metadata, 'build_number')),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'metadata' => $metadata,
            'occurred_at' => Arr::get($crash, 'occurred_at') ?? Arr::get($crash, 'created_at'),
        ];
    }

    private function buildFingerprint(string $errorType, ?string $errorMessage, ?string $stackTrace): string
    {
        $stackTop = '';
        if (is_string($stackTrace) && $stackTrace !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $stackTrace) ?: [];
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line !== '') {
                    $stackTop = $line;
                    break;
                }
            }
        }

        $messageFirst = '';
        if (is_string($errorMessage) && $errorMessage !== '') {
            $messageFirst = strtok($errorMessage, "\n") ?: $errorMessage;
        }

        return hash('sha256', strtolower(trim($errorType)).'|'.strtolower(trim((string) $messageFirst)).'|'.strtolower(trim($stackTop)));
    }

    private function dailyChart(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $totals = array_fill(0, count($labels), 0);
        $labelIndex = array_flip($labels);

        $rows = MobileCrash::query()
            ->select(DB::raw('DATE(occurred_at) as day'), DB::raw('COUNT(*) as total'))
            ->whereBetween('occurred_at', [$start, $end])
            ->groupBy(DB::raw('DATE(occurred_at)'))
            ->get();

        foreach ($rows as $row) {
            $day = (string) $row->day;
            if (isset($labelIndex[$day])) {
                $totals[$labelIndex[$day]] = (int) $row->total;
            }
        }

        return ['labels' => $labels, 'totals' => $totals];
    }

    private function resolvePeriod(Request $request): array
    {
        try {
            $start = $request->filled('start')
                ? Carbon::parse((string) $request->query('start'))->startOfDay()
                : now()->subDays(29)->startOfDay();
        } catch (\Throwable) {
            $start = now()->subDays(29)->startOfDay();
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

        return [$start, $end];
    }

    private function parseOccurredAt(mixed $value): Carbon
    {
        if ($value) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable) {
                return now();
            }
        }

        return now();
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
