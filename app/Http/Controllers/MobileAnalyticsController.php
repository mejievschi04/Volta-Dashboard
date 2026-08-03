<?php

namespace App\Http\Controllers;

use App\Models\MobileAnalyticsEvent;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class MobileAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('mobile.overview', $this->buildDashboardData($request));
    }

    public function events(Request $request)
    {
        return view('mobile.events', $this->buildDashboardData($request));
    }

    public function funnels(Request $request)
    {
        return view('mobile.funnels', $this->buildDashboardData($request));
    }

    public function pagesList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $pages = null;

        if ($schemaReady) {
            $pages = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->select(
                    'page',
                    DB::raw("SUM(CASE WHEN event_name = 'page_view' THEN 1 ELSE 0 END) as views"),
                    DB::raw('COUNT(*) as events_count'),
                    DB::raw('AVG(CASE WHEN duration_ms IS NOT NULL THEN duration_ms END) as avg_duration_ms')
                )
                ->whereNotNull('page')
                ->groupBy('page')
                ->orderByDesc('views')
                ->orderByDesc('events_count')
                ->paginate(50)
                ->withQueryString();
        }

        return view('mobile.pages-list', compact('start', 'end', 'schemaReady', 'pages'));
    }

    public function eventTypesList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $eventTypes = null;

        if ($schemaReady) {
            $eventTypes = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->select('event_name', DB::raw('COUNT(*) as total'))
                ->groupBy('event_name')
                ->orderByDesc('total')
                ->paginate(50)
                ->withQueryString();
        }

        return view('mobile.event-types-list', compact('start', 'end', 'schemaReady', 'eventTypes'));
    }

    public function bannersList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $banners = null;

        if ($schemaReady) {
            $banners = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->where('event_name', 'banner_click')
                ->select('banner_id', 'banner_title', DB::raw('COUNT(*) as clicks'), DB::raw('MAX(occurred_at) as last_click_at'))
                ->groupBy('banner_id', 'banner_title')
                ->orderByDesc('clicks')
                ->paginate(50)
                ->withQueryString();
        }

        return view('mobile.banners-list', compact('start', 'end', 'schemaReady', 'banners'));
    }

    public function recentEventsList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $recentEvents = null;

        if ($schemaReady) {
            $recentEvents = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->latest('occurred_at')
                ->paginate(100)
                ->withQueryString();
        }

        return view('mobile.recent-events-list', compact('start', 'end', 'schemaReady', 'recentEvents'));
    }

    public function abandonList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $abandonRows = null;

        if ($schemaReady) {
            $abandonRows = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end])
                ->where('event_name', 'cart_abandoned')
                ->select(
                    'checkout_step',
                    DB::raw('COUNT(*) as abandons'),
                    DB::raw('AVG(cart_total) as avg_cart_total'),
                    DB::raw('AVG(items_count) as avg_items_count')
                )
                ->groupBy('checkout_step')
                ->orderBy('checkout_step')
                ->paginate(50)
                ->withQueryString();
        }

        return view('mobile.abandon-list', compact('start', 'end', 'schemaReady', 'abandonRows'));
    }

    private function buildDashboardData(Request $request): array
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');

        $summary = [
            'events' => 0,
            'sessions' => 0,
            'users' => 0,
            'page_views' => 0,
            'product_views' => 0,
            'searches' => 0,
            'add_to_cart' => 0,
            'banner_clicks' => 0,
            'cart_abandons' => 0,
            'orders' => 0,
            'logins' => 0,
            'map_opens' => 0,
            'avg_page_seconds' => 0,
            'conversion_rate' => 0,
            'events_per_session' => 0,
            'view_to_cart_rate' => 0,
        ];
        $topPages = collect();
        $bannerClicks = collect();
        $cartAbandons = collect();
        $eventBreakdown = collect();
        $recentEvents = collect();
        $topSearches = collect();
        $topProducts = collect();
        $dailyChart = ['labels' => [], 'datasets' => []];
        $funnel = [
            'visits' => 0,
            'product_views' => 0,
            'add_to_cart' => 0,
            'checkout_started' => 0,
            'checkout_completed' => 0,
            'orders_completed' => 0,
            'cart_abandoned' => 0,
            'visit_to_product_rate' => 0,
            'product_to_cart_rate' => 0,
            'cart_to_checkout_rate' => 0,
            'visit_to_checkout_rate' => 0,
            'checkout_to_order_rate' => 0,
            'dropoff_after_checkout_rate' => 0,
            'recovery_rate' => 0,
        ];

        if ($schemaReady) {
            $base = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end]);

            $eventsCount = (clone $base)->count();
            $sessionsCount = (clone $base)->whereNotNull('session_id')->distinct('session_id')->count('session_id');
            $pageViews = (clone $base)->where('event_name', 'page_view')->count();
            $ordersCount = (clone $base)->where('event_name', 'order_completed')->count();
            $addToCart = (clone $base)->where('event_name', 'add_to_cart')->count();
            $productViews = (clone $base)->where('event_name', 'product_view')->count();

            $summary = [
                'events' => $eventsCount,
                'sessions' => $sessionsCount,
                'users' => (clone $base)->whereNotNull('mobile_user_id')->distinct('mobile_user_id')->count('mobile_user_id'),
                'page_views' => $pageViews,
                'product_views' => $productViews,
                'searches' => (clone $base)->where('event_name', 'search')->count(),
                'add_to_cart' => $addToCart,
                'banner_clicks' => (clone $base)->where('event_name', 'banner_click')->count(),
                'cart_abandons' => (clone $base)->where('event_name', 'cart_abandoned')->count(),
                'orders' => $ordersCount,
                'logins' => (clone $base)->where('event_name', 'login_success')->count(),
                'map_opens' => (clone $base)->where('event_name', 'map_open')->count(),
                'avg_page_seconds' => round(((clone $base)->whereNotNull('duration_ms')->avg('duration_ms') ?? 0) / 1000),
                'conversion_rate' => $sessionsCount > 0 ? round(($ordersCount / $sessionsCount) * 100, 2) : 0,
                'events_per_session' => $sessionsCount > 0 ? round($eventsCount / $sessionsCount, 1) : 0,
                'view_to_cart_rate' => $productViews > 0 ? round(($addToCart / $productViews) * 100, 1) : 0,
            ];

            $topPages = (clone $base)
                ->select(
                    'page',
                    DB::raw("SUM(CASE WHEN event_name = 'page_view' THEN 1 ELSE 0 END) as views"),
                    DB::raw('COUNT(*) as events_count'),
                    DB::raw('AVG(CASE WHEN duration_ms IS NOT NULL THEN duration_ms END) as avg_duration_ms')
                )
                ->whereNotNull('page')
                ->groupBy('page')
                ->orderByDesc('views')
                ->orderByDesc('events_count')
                ->limit(20)
                ->get();

            $bannerClicks = (clone $base)
                ->select('banner_id', 'banner_title', DB::raw('COUNT(*) as clicks'), DB::raw('MAX(occurred_at) as last_click_at'))
                ->where('event_name', 'banner_click')
                ->groupBy('banner_id', 'banner_title')
                ->orderByDesc('clicks')
                ->limit(20)
                ->get();

            $cartAbandons = (clone $base)
                ->select(
                    'checkout_step',
                    DB::raw('COUNT(*) as abandons'),
                    DB::raw('AVG(cart_total) as avg_cart_total'),
                    DB::raw('AVG(items_count) as avg_items_count')
                )
                ->where('event_name', 'cart_abandoned')
                ->groupBy('checkout_step')
                ->orderBy('checkout_step')
                ->get();

            $eventBreakdown = (clone $base)
                ->select('event_name', DB::raw('COUNT(*) as total'))
                ->groupBy('event_name')
                ->orderByDesc('total')
                ->limit(30)
                ->get();

            $recentEvents = (clone $base)
                ->latest('occurred_at')
                ->limit(80)
                ->get();

            $topSearches = $this->topMetadataValues(clone $base, 'search', '$.query', 12);
            $topProducts = $this->topMetadataValues(clone $base, 'product_view', '$.product_name', 12);

            $dailyChart = $this->dailyChart($start, $end);
            $funnel = $this->funnelMetrics(
                clone $base,
                $summary['page_views'],
                $summary['product_views'],
                $summary['add_to_cart'],
                $summary['cart_abandons'],
                $summary['orders']
            );
        }

        return compact(
            'start',
            'end',
            'schemaReady',
            'summary',
            'topPages',
            'bannerClicks',
            'cartAbandons',
            'eventBreakdown',
            'recentEvents',
            'topSearches',
            'topProducts',
            'dailyChart',
            'funnel'
        );
    }

    public function ingest(Request $request): JsonResponse
    {
        $configuredKey = (string) config('services.mobile_analytics.key', '');
        if ($configuredKey !== '') {
            $providedKey = (string) ($request->header('X-Mobile-Analytics-Key') ?? $request->bearerToken() ?? '');
            if (! hash_equals($configuredKey, $providedKey)) {
                return response()->json(['success' => false, 'error' => 'Invalid analytics key.'], 403);
            }
        }

        $payload = $request->all();
        $events = Arr::get($payload, 'events');
        if (! is_array($events) || ! array_is_list($events)) {
            $events = [$payload];
        }

        $created = 0;
        $errors = [];

        foreach ($events as $index => $rawEvent) {
            if (! is_array($rawEvent)) {
                $errors[$index] = ['event' => ['Invalid event payload.']];
                continue;
            }

            $normalized = $this->normalizeEvent($rawEvent, $request);
            $validator = Validator::make($normalized, [
                'event_name' => ['required', 'string', 'max:80'],
                'session_id' => ['nullable', 'string', 'max:128'],
                'mobile_user_id' => ['nullable', 'string', 'max:64'],
                'device_id' => ['nullable', 'string', 'max:128'],
                'platform' => ['nullable', 'string', 'max:32'],
                'app_version' => ['nullable', 'string', 'max:32'],
                'page' => ['nullable', 'string', 'max:255'],
                'previous_page' => ['nullable', 'string', 'max:255'],
                'duration_ms' => ['nullable', 'integer', 'min:0'],
                'checkout_step' => ['nullable', 'integer', 'min:1', 'max:10'],
                'cart_total' => ['nullable', 'numeric', 'min:0'],
                'items_count' => ['nullable', 'integer', 'min:0'],
                'banner_id' => ['nullable', 'string', 'max:128'],
                'banner_title' => ['nullable', 'string', 'max:255'],
                'order_id' => ['nullable', 'string', 'max:64'],
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
            $data['occurred_at'] = $this->parseOccurredAt($data['occurred_at'] ?? null);
            MobileAnalyticsEvent::create($data);
            $created++;
        }

        return response()->json([
            'success' => empty($errors),
            'accepted' => $created,
            'errors' => $errors,
        ], empty($errors) ? 202 : 422);
    }

    private function normalizeEvent(array $event, Request $request): array
    {
        $metadata = Arr::get($event, 'metadata', []);
        if (! is_array($metadata)) {
            $metadata = ['raw_metadata' => $metadata];
        }

        return [
            'event_name' => (string) (Arr::get($event, 'event_name') ?? Arr::get($event, 'event') ?? ''),
            'session_id' => $this->nullableString(Arr::get($event, 'session_id')),
            'mobile_user_id' => $this->nullableString(Arr::get($event, 'mobile_user_id') ?? Arr::get($event, 'user_id')),
            'device_id' => $this->nullableString(Arr::get($event, 'device_id') ?? Arr::get($event, 'installation_id')),
            'platform' => $this->nullableString(Arr::get($event, 'platform')),
            'app_version' => $this->nullableString(Arr::get($event, 'app_version')),
            'page' => $this->nullableString(Arr::get($event, 'page') ?? Arr::get($metadata, 'page')),
            'previous_page' => $this->nullableString(Arr::get($event, 'previous_page') ?? Arr::get($metadata, 'previous_page')),
            'duration_ms' => $this->nullableInt(Arr::get($event, 'duration_ms') ?? Arr::get($metadata, 'duration_ms')),
            'checkout_step' => $this->nullableInt(Arr::get($event, 'checkout_step') ?? Arr::get($metadata, 'checkout_step')),
            'cart_total' => $this->nullableFloat(Arr::get($event, 'cart_total') ?? Arr::get($metadata, 'cart_total')),
            'items_count' => $this->nullableInt(Arr::get($event, 'items_count') ?? Arr::get($metadata, 'items_count')),
            'banner_id' => $this->nullableString(Arr::get($event, 'banner_id') ?? Arr::get($metadata, 'banner_id')),
            'banner_title' => $this->nullableString(Arr::get($event, 'banner_title') ?? Arr::get($metadata, 'banner_title')),
            'order_id' => $this->nullableString(Arr::get($event, 'order_id') ?? Arr::get($metadata, 'order_id')),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 2000),
            'metadata' => $metadata,
            'occurred_at' => Arr::get($event, 'occurred_at') ?? Arr::get($event, 'created_at'),
        ];
    }

    private function dailyChart(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $labels[] = $cursor->format('Y-m-d');
            $cursor->addDay();
        }

        $eventNames = ['page_view', 'product_view', 'search', 'add_to_cart', 'banner_click', 'cart_abandoned', 'order_completed'];
        $datasets = array_fill_keys($eventNames, array_fill(0, count($labels), 0));
        $labelIndex = array_flip($labels);

        $rows = MobileAnalyticsEvent::query()
            ->select(DB::raw('DATE(occurred_at) as day'), 'event_name', DB::raw('COUNT(*) as total'))
            ->whereBetween('occurred_at', [$start, $end])
            ->whereIn('event_name', $eventNames)
            ->groupBy(DB::raw('DATE(occurred_at)'), 'event_name')
            ->get();

        foreach ($rows as $row) {
            $day = (string) $row->day;
            $event = (string) $row->event_name;
            if (isset($labelIndex[$day], $datasets[$event])) {
                $datasets[$event][$labelIndex[$day]] = (int) $row->total;
            }
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function funnelMetrics($base, int $pageViews, int $productViews, int $addToCart, int $cartAbandoned, int $orders): array
    {
        $checkoutStarted = (clone $base)->where('event_name', 'checkout_started')->count();
        if ($checkoutStarted === 0) {
            $checkoutStarted = (clone $base)->where('event_name', 'checkout_step')->where('checkout_step', '>=', 2)->distinct('session_id')->count('session_id');
        }
        if ($checkoutStarted === 0) {
            $checkoutStarted = $cartAbandoned + $orders;
        }

        $checkoutCompleted = (clone $base)->where('event_name', 'checkout_completed')->count();
        if ($checkoutCompleted === 0) {
            $checkoutCompleted = $orders;
        }

        $visitToProductRate = $pageViews > 0
            ? round(($productViews / $pageViews) * 100, 1)
            : 0.0;
        $productToCartRate = $productViews > 0
            ? round(($addToCart / $productViews) * 100, 1)
            : 0.0;
        $cartToCheckoutRate = $addToCart > 0
            ? round(($checkoutStarted / $addToCart) * 100, 1)
            : 0.0;
        $visitToCheckoutRate = $pageViews > 0
            ? round(($checkoutStarted / $pageViews) * 100, 1)
            : 0.0;
        $checkoutToOrderRate = $checkoutStarted > 0
            ? round(($orders / $checkoutStarted) * 100, 1)
            : 0.0;
        $dropoffAfterCheckoutRate = $checkoutStarted > 0
            ? round((max($checkoutStarted - $orders, 0) / $checkoutStarted) * 100, 1)
            : 0.0;
        $recoveryRate = ($cartAbandoned + $orders) > 0
            ? round(($orders / ($cartAbandoned + $orders)) * 100, 1)
            : 0.0;

        return [
            'visits' => $pageViews,
            'product_views' => $productViews,
            'add_to_cart' => $addToCart,
            'checkout_started' => $checkoutStarted,
            'checkout_completed' => $checkoutCompleted,
            'orders_completed' => $orders,
            'cart_abandoned' => $cartAbandoned,
            'visit_to_product_rate' => $visitToProductRate,
            'product_to_cart_rate' => $productToCartRate,
            'cart_to_checkout_rate' => $cartToCheckoutRate,
            'visit_to_checkout_rate' => $visitToCheckoutRate,
            'checkout_to_order_rate' => $checkoutToOrderRate,
            'dropoff_after_checkout_rate' => $dropoffAfterCheckoutRate,
            'recovery_rate' => $recoveryRate,
        ];
    }

    /**
     * Top valori din metadata JSON (ex. query / product_name).
     *
     * @return \Illuminate\Support\Collection<int, object{label: string, total: int}>
     */
    private function topMetadataValues($base, string $eventName, string $jsonPath, int $limit = 12)
    {
        try {
            return (clone $base)
                ->where('event_name', $eventName)
                ->whereNotNull('metadata')
                ->select(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '{$jsonPath}')) as label"),
                    DB::raw('COUNT(*) as total')
                )
                ->groupBy('label')
                ->havingRaw("label IS NOT NULL AND label != '' AND label != 'null'")
                ->orderByDesc('total')
                ->limit($limit)
                ->get();
        } catch (\Throwable) {
            return collect();
        }
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

    private function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (int) $value : null;
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        return is_numeric($value) ? (float) $value : null;
    }
}
