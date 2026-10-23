<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoltaChatAnalyticsService
{
    public const TYPES = ['customer', 'internal'];

    public const GRAINS = ['day', 'week', 'month'];

    public const MAX_DAYS = 366;

    public function isConfigured(): bool
    {
        return $this->baseUrl() !== '' && $this->token() !== '';
    }

    /**
     * @return array{ok: bool, data: array|null, error: string|null, filters: array}
     */
    public function fetch(Carbon $start, Carbon $end, array $options = []): array
    {
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->startOfDay();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }
        if ($start->diffInDays($end) + 1 > self::MAX_DAYS) {
            $start = $end->copy()->subDays(self::MAX_DAYS - 1);
        }

        $days = (int) $start->diffInDays($end) + 1;
        $grain = in_array($options['grain'] ?? null, self::GRAINS, true)
            ? $options['grain']
            : ($days <= 62 ? 'day' : ($days <= 186 ? 'week' : 'month'));

        $params = array_filter([
            'from' => $start->toDateString(),
            'to' => $end->toDateString(),
            'type' => in_array($options['type'] ?? null, self::TYPES, true) ? $options['type'] : 'customer',
            'grain' => $grain,
            'channelId' => isset($options['channelId']) && ctype_digit((string) $options['channelId']) ? (int) $options['channelId'] : null,
            'operatorSort' => ($options['operatorSort'] ?? null) === 'response' ? 'response' : 'conversations',
        ], fn ($v) => $v !== null);

        $result = ['ok' => false, 'data' => null, 'error' => null, 'filters' => $params];

        if (! $this->isConfigured()) {
            $result['error'] = 'not_configured';

            return $result;
        }

        $cacheKey = 'voltachat:analytics:v1:'.md5(json_encode($params));
        $cached = Cache::get($cacheKey);
        if (is_array($cached)) {
            return array_merge($result, ['ok' => true, 'data' => $cached]);
        }

        try {
            $response = Http::withToken($this->token())
                ->acceptJson()
                ->connectTimeout(4)
                ->timeout(12)
                ->get($this->baseUrl().'/api/analytics', $params);
        } catch (\Throwable $e) {
            Log::warning('VoltaChat analytics: '.$e->getMessage());
            $result['error'] = 'unreachable';

            return $result;
        }

        if (! $response->successful() || ! is_array($response->json())) {
            Log::warning('VoltaChat analytics HTTP '.$response->status(), ['body' => mb_substr($response->body(), 0, 500)]);
            $result['error'] = match ($response->status()) {
                401 => 'unauthorized',
                422 => 'invalid',
                429 => 'rate_limited',
                503 => 'disabled',
                default => 'http_'.$response->status(),
            };

            return $result;
        }

        $data = $response->json();
        Cache::put($cacheKey, $data, 60);

        return array_merge($result, ['ok' => true, 'data' => $data]);
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.voltachat.url', ''), '/');
    }

    private function token(): string
    {
        return (string) config('services.voltachat.analytics_token', '');
    }
}
