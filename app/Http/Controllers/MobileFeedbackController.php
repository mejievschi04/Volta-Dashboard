<?php

namespace App\Http\Controllers;

use App\Models\MobileFeedbackReport;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileFeedbackController extends Controller
{
    public function index(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_feedback_reports');
        $reports = null;
        $summary = [
            'total' => 0,
            'with_screenshot' => 0,
            'devices' => 0,
            'users' => 0,
        ];

        if ($schemaReady) {
            $base = MobileFeedbackReport::query()->whereBetween('occurred_at', [$start, $end]);
            $summary['total'] = (clone $base)->count();
            $summary['with_screenshot'] = (clone $base)->where('has_screenshot', true)->count();
            $summary['devices'] = (int) (clone $base)->whereNotNull('device_id')->selectRaw('COUNT(DISTINCT device_id) as aggregate')->value('aggregate');
            $summary['users'] = (int) (clone $base)->whereNotNull('mobile_user_id')->selectRaw('COUNT(DISTINCT mobile_user_id) as aggregate')->value('aggregate');

            $reports = MobileFeedbackReport::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->latest('occurred_at')
                ->paginate(50)
                ->withQueryString();
        }

        return view('mobile.feedback-list', compact('start', 'end', 'schemaReady', 'reports', 'summary'));
    }

    public function show(Request $request, MobileFeedbackReport $report)
    {
        $schemaReady = Schema::hasTable('mobile_feedback_reports');
        [$start, $end] = $this->resolvePeriod($request);

        return view('mobile.feedback-detail', compact('report', 'schemaReady', 'start', 'end'));
    }

    public function ingest(Request $request): JsonResponse
    {
        $configuredKey = (string) (
            config('services.mobile_feedback.key', '')
            ?: config('services.mobile_analytics.key', '')
            ?: config('services.mobile_crashes.key', '')
        );
        if ($configuredKey !== '') {
            $providedKey = (string) (
                $request->header('X-Mobile-Feedback-Key')
                ?? $request->header('X-Mobile-Analytics-Key')
                ?? $request->header('X-Mobile-Crashes-Key')
                ?? $request->bearerToken()
                ?? ''
            );
            if (! hash_equals($configuredKey, $providedKey)) {
                return response()->json(['success' => false, 'error' => 'Invalid feedback key.'], 403);
            }
        }

        if (! Schema::hasTable('mobile_feedback_reports')) {
            return response()->json(['success' => false, 'error' => 'Feedback table missing.'], 503);
        }

        $payload = $request->all();
        $items = Arr::get($payload, 'reports');
        if (! is_array($items) || ! array_is_list($items)) {
            $items = [$payload];
        }

        $created = 0;
        $errors = [];

        foreach ($items as $index => $raw) {
            if (! is_array($raw)) {
                $errors[$index] = ['report' => ['Invalid feedback payload.']];
                continue;
            }

            $normalized = $this->normalizeReport($raw, $request);
            $validator = Validator::make($normalized, [
                'message' => ['required', 'string', 'min:10', 'max:5000'],
                'reporter_name' => ['nullable', 'string', 'max:128'],
                'reporter_email' => ['nullable', 'string', 'max:255'],
                'screenshot_filename' => ['nullable', 'string', 'max:255'],
                'screenshot_mime' => ['nullable', 'string', 'max:64'],
                'screenshot_base64' => ['nullable', 'string', 'max:2500000'],
                'has_screenshot' => ['nullable', 'boolean'],
                'status' => ['nullable', 'string', 'max:32'],
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
            $data['has_screenshot'] = ! empty($data['screenshot_base64']) || ! empty($data['has_screenshot']);
            $data['status'] = $data['status'] ?? 'new';
            $data['occurred_at'] = $this->parseOccurredAt($data['occurred_at'] ?? null);

            MobileFeedbackReport::create($data);
            $created++;
        }

        return response()->json([
            'success' => empty($errors),
            'accepted' => $created,
            'errors' => $errors,
        ], empty($errors) ? 202 : 422);
    }

    private function normalizeReport(array $report, Request $request): array
    {
        $metadata = Arr::get($report, 'metadata', []);
        if (! is_array($metadata)) {
            $metadata = ['raw_metadata' => $metadata];
        }

        $message = $this->nullableString(
            Arr::get($report, 'message')
            ?? Arr::get($report, 'error_message')
            ?? Arr::get($metadata, 'message')
        );

        $screenshotBase64 = Arr::get($report, 'screenshot_base64')
            ?? Arr::get($report, 'screenshot')
            ?? Arr::get($metadata, 'screenshot_base64');
        if (is_string($screenshotBase64)) {
            // Strip data-url prefix if present.
            if (str_contains($screenshotBase64, ',')) {
                $screenshotBase64 = substr($screenshotBase64, strpos($screenshotBase64, ',') + 1);
            }
            $screenshotBase64 = preg_replace('/\s+/', '', $screenshotBase64) ?: null;
        } else {
            $screenshotBase64 = null;
        }

        return [
            'message' => $message ?? '',
            'reporter_name' => $this->nullableString(Arr::get($report, 'reporter_name') ?? Arr::get($report, 'name') ?? Arr::get($metadata, 'name')),
            'reporter_email' => $this->nullableString(Arr::get($report, 'reporter_email') ?? Arr::get($report, 'email') ?? Arr::get($metadata, 'email')),
            'screenshot_filename' => $this->nullableString(Arr::get($report, 'screenshot_filename') ?? Arr::get($metadata, 'screenshot_filename')),
            'screenshot_mime' => $this->nullableString(Arr::get($report, 'screenshot_mime') ?? Arr::get($metadata, 'screenshot_mime') ?? 'image/jpeg'),
            'screenshot_base64' => is_string($screenshotBase64) ? $screenshotBase64 : null,
            'has_screenshot' => (bool) (Arr::get($report, 'has_screenshot') ?? Arr::get($metadata, 'has_screenshot') ?? false),
            'status' => $this->nullableString(Arr::get($report, 'status')) ?? 'new',
            'session_id' => $this->nullableString(Arr::get($report, 'session_id')),
            'mobile_user_id' => $this->nullableString(Arr::get($report, 'mobile_user_id') ?? Arr::get($report, 'user_id')),
            'device_id' => $this->nullableString(Arr::get($report, 'device_id') ?? Arr::get($report, 'installation_id')),
            'platform' => $this->nullableString(Arr::get($report, 'platform')),
            'app_version' => $this->nullableString(Arr::get($report, 'app_version')),
            'os_version' => $this->nullableString(Arr::get($report, 'os_version') ?? Arr::get($metadata, 'os_version')),
            'device_model' => $this->nullableString(Arr::get($report, 'device_model') ?? Arr::get($metadata, 'device_model')),
            'build_number' => $this->nullableString(Arr::get($report, 'build_number') ?? Arr::get($metadata, 'build_number')),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'metadata' => $metadata,
            'occurred_at' => Arr::get($report, 'occurred_at') ?? Arr::get($report, 'created_at'),
        ];
    }

    private function resolvePeriod(Request $request): array
    {
        $end = Carbon::today()->endOfDay();
        $start = Carbon::today()->subDays(29)->startOfDay();

        try {
            if ($request->filled('start')) {
                $start = Carbon::parse($request->input('start'))->startOfDay();
            }
            if ($request->filled('end')) {
                $end = Carbon::parse($request->input('end'))->endOfDay();
            }
        } catch (\Throwable) {
            // keep defaults
        }

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    private function parseOccurredAt(mixed $value): Carbon
    {
        try {
            if ($value) {
                return Carbon::parse($value);
            }
        } catch (\Throwable) {
            // fall through
        }

        return now();
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_bool($value) || is_numeric($value)) {
            $value = (string) $value;
        }
        if (! is_string($value)) {
            return null;
        }
        $trimmed = trim($value);

        return $trimmed === '' ? null : Str::limit($trimmed, 2000, '');
    }
}
