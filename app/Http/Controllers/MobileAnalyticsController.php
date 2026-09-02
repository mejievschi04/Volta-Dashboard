<?php

namespace App\Http\Controllers;

use App\Models\MobileAnalyticsEvent;
use App\Models\MobileCrash;
use App\Models\MobileFeedbackReport;
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

    public function meta(Request $request)
    {
        return view('mobile.meta', $this->buildMetaData($request));
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

    /**
     * Meta-level executive metrics across events, crashes and feedback.
     */
    private function buildMetaData(Request $request): array
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = Schema::hasTable('mobile_analytics_events');
        $days = max(1, (int) $start->diffInDays($end) + 1);

        $meta = [
            'sessions' => 0,
            'users' => 0,
            'devices' => 0,
            'events' => 0,
            'dau_avg' => 0,
            'new_users' => 0,
            'returning_users' => 0,
            'returning_rate' => 0,
            'multi_day_users' => 0,
            'retention_proxy' => 0,
            'bounce_rate' => 0,
            'events_per_session' => 0,
            'pages_per_session' => 0,
            'avg_session_seconds' => 0,
            'avg_page_seconds' => 0,
            'logged_in_session_rate' => 0,
            'orders' => 0,
            'conversion_rate' => 0,
            'revenue' => 0,
            'aov' => 0,
            'product_views' => 0,
            'add_to_cart' => 0,
            'view_to_cart_rate' => 0,
            'cart_abandons' => 0,
            'abandon_rate' => 0,
            'checkout_to_order_rate' => 0,
            'searches' => 0,
            'search_to_product_rate' => 0,
            'banner_clicks' => 0,
            'logins' => 0,
            'map_opens' => 0,
            'crashes' => 0,
            'fatal_crashes' => 0,
            'crash_rate' => 0,
            'fatal_rate' => 0,
            'feedback' => 0,
            'feedback_with_screenshot' => 0,
        ];

        $platforms = collect();
        $versions = collect();
        $hourly = array_fill(0, 24, 0);
        $weekday = array_fill(0, 7, 0);
        $dailyActive = ['labels' => [], 'users' => [], 'sessions' => [], 'orders' => []];
        $funnel = [
            'visits' => 0,
            'product_views' => 0,
            'add_to_cart' => 0,
            'checkout_started' => 0,
            'orders_completed' => 0,
            'visit_to_product_rate' => 0,
            'product_to_cart_rate' => 0,
            'cart_to_checkout_rate' => 0,
            'checkout_to_order_rate' => 0,
        ];
        $topEntryPages = collect();
        $qualityNotes = [];

        if ($schemaReady) {
            $base = MobileAnalyticsEvent::query()->whereBetween('occurred_at', [$start, $end]);

            $events = (clone $base)->count();
            $sessions = (int) (clone $base)->whereNotNull('session_id')->distinct('session_id')->count('session_id');
            $users = (int) (clone $base)->whereNotNull('mobile_user_id')->distinct('mobile_user_id')->count('mobile_user_id');
            $devices = (int) (clone $base)->whereNotNull('device_id')->distinct('device_id')->count('device_id');
            $pageViews = (clone $base)->where('event_name', 'page_view')->count();
            $productViews = (clone $base)->where('event_name', 'product_view')->count();
            $addToCart = (clone $base)->where('event_name', 'add_to_cart')->count();
            $orders = (clone $base)->where('event_name', 'order_completed')->count();
            $cartAbandons = (clone $base)->where('event_name', 'cart_abandoned')->count();
            $searches = (clone $base)->where('event_name', 'search')->count();
            $bannerClicks = (clone $base)->where('event_name', 'banner_click')->count();
            $logins = (clone $base)->where('event_name', 'login_success')->count();
            $mapOpens = (clone $base)->where('event_name', 'map_open')->count();

            $orderAgg = (clone $base)
                ->where('event_name', 'order_completed')
                ->selectRaw('COALESCE(SUM(cart_total), 0) as revenue, COALESCE(AVG(cart_total), 0) as aov')
                ->first();
            $revenue = (float) ($orderAgg->revenue ?? 0);
            $aov = (float) ($orderAgg->aov ?? 0);

            $avgPageMs = (float) ((clone $base)->whereNotNull('duration_ms')->avg('duration_ms') ?? 0);

            $avgSessionSeconds = 0.0;
            try {
                $avgMs = DB::query()
                    ->fromSub(function ($q) use ($start, $end) {
                        $q->from('mobile_analytics_events')
                            ->select('session_id', DB::raw('SUM(COALESCE(duration_ms, 0)) as total_ms'))
                            ->whereBetween('occurred_at', [$start, $end])
                            ->whereNotNull('session_id')
                            ->groupBy('session_id');
                    }, 'session_durations')
                    ->avg('total_ms');
                $avgSessionSeconds = ((float) ($avgMs ?? 0)) / 1000;
            } catch (\Throwable) {
                $avgSessionSeconds = $sessions > 0 ? ($avgPageMs / 1000) : 0;
            }

            $bounceRate = 0.0;
            if ($sessions > 0) {
                try {
                    $bounced = (int) DB::query()
                        ->fromSub(function ($q) use ($start, $end) {
                            $q->from('mobile_analytics_events')
                                ->select('session_id', DB::raw('COUNT(*) as cnt'))
                                ->whereBetween('occurred_at', [$start, $end])
                                ->whereNotNull('session_id')
                                ->groupBy('session_id')
                                ->havingRaw('COUNT(*) <= 1');
                        }, 'bounced_sessions')
                        ->count();
                    $bounceRate = round(($bounced / $sessions) * 100, 1);
                } catch (\Throwable) {
                    $bounceRate = 0.0;
                }
            }

            $loggedInSessions = 0;
            if ($sessions > 0) {
                $loggedInSessions = (int) (clone $base)
                    ->whereNotNull('session_id')
                    ->whereNotNull('mobile_user_id')
                    ->distinct('session_id')
                    ->count('session_id');
            }

            // New users: first-ever event falls inside period
            $newUsers = 0;
            $returningUsers = 0;
            if ($users > 0) {
                try {
                    $newUsers = (int) DB::query()
                        ->fromSub(function ($q) use ($start, $end) {
                            $q->from('mobile_analytics_events')
                                ->select('mobile_user_id', DB::raw('MIN(occurred_at) as first_seen'))
                                ->whereNotNull('mobile_user_id')
                                ->groupBy('mobile_user_id')
                                ->havingRaw('MIN(occurred_at) BETWEEN ? AND ?', [$start, $end]);
                        }, 'first_touch')
                        ->count();
                    $returningUsers = max(0, $users - $newUsers);
                } catch (\Throwable) {
                    $newUsers = 0;
                    $returningUsers = 0;
                }
            }

            $multiDayUsers = 0;
            if ($users > 0) {
                try {
                    $multiDayUsers = (int) DB::query()
                        ->fromSub(function ($q) use ($start, $end) {
                            $q->from('mobile_analytics_events')
                                ->select('mobile_user_id')
                                ->whereBetween('occurred_at', [$start, $end])
                                ->whereNotNull('mobile_user_id')
                                ->groupBy('mobile_user_id')
                                ->havingRaw('COUNT(DISTINCT DATE(occurred_at)) >= 2');
                        }, 'sticky')
                        ->count();
                } catch (\Throwable) {
                    $multiDayUsers = 0;
                }
            }

            $dauRows = (clone $base)
                ->whereNotNull('mobile_user_id')
                ->select(DB::raw('DATE(occurred_at) as day'), DB::raw('COUNT(DISTINCT mobile_user_id) as users'))
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->get();
            $dauAvg = $dauRows->count() > 0
                ? round($dauRows->avg('users'), 1)
                : 0.0;

            $checkoutStarted = (clone $base)->where('event_name', 'checkout_started')->count();
            if ($checkoutStarted === 0) {
                $checkoutStarted = (clone $base)->where('event_name', 'checkout_step')->where('checkout_step', '>=', 2)->distinct('session_id')->count('session_id');
            }
            if ($checkoutStarted === 0) {
                $checkoutStarted = $cartAbandons + $orders;
            }

            $meta = [
                'sessions' => $sessions,
                'users' => $users,
                'devices' => $devices,
                'events' => $events,
                'dau_avg' => $dauAvg,
                'new_users' => $newUsers,
                'returning_users' => $returningUsers,
                'returning_rate' => $users > 0 ? round(($returningUsers / $users) * 100, 1) : 0,
                'multi_day_users' => $multiDayUsers,
                'retention_proxy' => $users > 0 ? round(($multiDayUsers / $users) * 100, 1) : 0,
                'bounce_rate' => $bounceRate,
                'events_per_session' => $sessions > 0 ? round($events / $sessions, 1) : 0,
                'pages_per_session' => $sessions > 0 ? round($pageViews / $sessions, 1) : 0,
                'avg_session_seconds' => round($avgSessionSeconds),
                'avg_page_seconds' => round($avgPageMs / 1000),
                'logged_in_session_rate' => $sessions > 0 ? round(($loggedInSessions / $sessions) * 100, 1) : 0,
                'orders' => $orders,
                'conversion_rate' => $sessions > 0 ? round(($orders / $sessions) * 100, 2) : 0,
                'revenue' => round($revenue, 2),
                'aov' => round($aov, 2),
                'product_views' => $productViews,
                'add_to_cart' => $addToCart,
                'view_to_cart_rate' => $productViews > 0 ? round(($addToCart / $productViews) * 100, 1) : 0,
                'cart_abandons' => $cartAbandons,
                'abandon_rate' => ($cartAbandons + $orders) > 0
                    ? round(($cartAbandons / ($cartAbandons + $orders)) * 100, 1)
                    : 0,
                'checkout_to_order_rate' => $checkoutStarted > 0
                    ? round(($orders / $checkoutStarted) * 100, 1)
                    : 0,
                'searches' => $searches,
                'search_to_product_rate' => $searches > 0 ? round(($productViews / $searches) * 100, 1) : 0,
                'banner_clicks' => $bannerClicks,
                'logins' => $logins,
                'map_opens' => $mapOpens,
                'crashes' => 0,
                'fatal_crashes' => 0,
                'crash_rate' => 0,
                'fatal_rate' => 0,
                'feedback' => 0,
                'feedback_with_screenshot' => 0,
            ];

            $funnel = [
                'visits' => $pageViews,
                'product_views' => $productViews,
                'add_to_cart' => $addToCart,
                'checkout_started' => $checkoutStarted,
                'orders_completed' => $orders,
                'visit_to_product_rate' => $pageViews > 0 ? round(($productViews / $pageViews) * 100, 1) : 0,
                'product_to_cart_rate' => $productViews > 0 ? round(($addToCart / $productViews) * 100, 1) : 0,
                'cart_to_checkout_rate' => $addToCart > 0 ? round(($checkoutStarted / $addToCart) * 100, 1) : 0,
                'checkout_to_order_rate' => $checkoutStarted > 0 ? round(($orders / $checkoutStarted) * 100, 1) : 0,
            ];

            $platforms = (clone $base)
                ->select(
                    DB::raw("COALESCE(NULLIF(platform, ''), 'necunoscut') as label"),
                    DB::raw('COUNT(*) as events'),
                    DB::raw('COUNT(DISTINCT session_id) as sessions'),
                    DB::raw("SUM(CASE WHEN event_name = 'order_completed' THEN 1 ELSE 0 END) as orders")
                )
                ->groupBy(DB::raw("COALESCE(NULLIF(platform, ''), 'necunoscut')"))
                ->orderByDesc('sessions')
                ->limit(8)
                ->get();

            $versions = (clone $base)
                ->select(
                    DB::raw("COALESCE(NULLIF(app_version, ''), 'necunoscut') as label"),
                    DB::raw('COUNT(DISTINCT session_id) as sessions'),
                    DB::raw('COUNT(DISTINCT mobile_user_id) as users'),
                    DB::raw("SUM(CASE WHEN event_name = 'order_completed' THEN 1 ELSE 0 END) as orders")
                )
                ->groupBy(DB::raw("COALESCE(NULLIF(app_version, ''), 'necunoscut')"))
                ->orderByDesc('sessions')
                ->limit(10)
                ->get();

            $hourRows = (clone $base)
                ->select(DB::raw('HOUR(occurred_at) as hour'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('HOUR(occurred_at)'))
                ->get();
            foreach ($hourRows as $row) {
                $h = (int) $row->hour;
                if ($h >= 0 && $h <= 23) {
                    $hourly[$h] = (int) $row->total;
                }
            }

            // MySQL DAYOFWEEK: 1=Sunday … 7=Saturday → map to Mon=0 … Sun=6
            $weekdayRows = (clone $base)
                ->select(DB::raw('DAYOFWEEK(occurred_at) as dow'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('DAYOFWEEK(occurred_at)'))
                ->get();
            foreach ($weekdayRows as $row) {
                $mysqlDow = (int) $row->dow; // 1 Sun … 7 Sat
                $idx = $mysqlDow === 1 ? 6 : $mysqlDow - 2;
                if ($idx >= 0 && $idx <= 6) {
                    $weekday[$idx] = (int) $row->total;
                }
            }

            $labels = [];
            $cursor = $start->copy()->startOfDay();
            while ($cursor->lte($end)) {
                $labels[] = $cursor->format('Y-m-d');
                $cursor->addDay();
            }
            $labelIndex = array_flip($labels);
            $usersSeries = array_fill(0, count($labels), 0);
            $sessionsSeries = array_fill(0, count($labels), 0);
            $ordersSeries = array_fill(0, count($labels), 0);

            $dailyUserRows = (clone $base)
                ->whereNotNull('mobile_user_id')
                ->select(DB::raw('DATE(occurred_at) as day'), DB::raw('COUNT(DISTINCT mobile_user_id) as total'))
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->get();
            foreach ($dailyUserRows as $row) {
                $day = (string) $row->day;
                if (isset($labelIndex[$day])) {
                    $usersSeries[$labelIndex[$day]] = (int) $row->total;
                }
            }

            $dailySessionRows = (clone $base)
                ->whereNotNull('session_id')
                ->select(DB::raw('DATE(occurred_at) as day'), DB::raw('COUNT(DISTINCT session_id) as total'))
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->get();
            foreach ($dailySessionRows as $row) {
                $day = (string) $row->day;
                if (isset($labelIndex[$day])) {
                    $sessionsSeries[$labelIndex[$day]] = (int) $row->total;
                }
            }

            $dailyOrderRows = (clone $base)
                ->where('event_name', 'order_completed')
                ->select(DB::raw('DATE(occurred_at) as day'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('DATE(occurred_at)'))
                ->get();
            foreach ($dailyOrderRows as $row) {
                $day = (string) $row->day;
                if (isset($labelIndex[$day])) {
                    $ordersSeries[$labelIndex[$day]] = (int) $row->total;
                }
            }

            $dailyActive = [
                'labels' => $labels,
                'users' => $usersSeries,
                'sessions' => $sessionsSeries,
                'orders' => $ordersSeries,
            ];

            $topEntryPages = (clone $base)
                ->where('event_name', 'page_view')
                ->where(function ($q) {
                    $q->whereNull('previous_page')->orWhere('previous_page', '');
                })
                ->whereNotNull('page')
                ->select('page', DB::raw('COUNT(*) as total'))
                ->groupBy('page')
                ->orderByDesc('total')
                ->limit(8)
                ->get();

            if ($meta['bounce_rate'] >= 55) {
                $qualityNotes[] = 'Bounce rate ridicat — mulți utilizatori pleacă după un singur eveniment.';
            }
            if ($meta['conversion_rate'] < 1 && $sessions > 50) {
                $qualityNotes[] = 'Conversie sub 1% pe sesiuni — verifică pâlnie și checkout.';
            }
            if ($meta['view_to_cart_rate'] < 5 && $productViews > 50) {
                $qualityNotes[] = 'Rata produs → coș e scăzută; merită revizuită pagina de produs.';
            }
            if ($meta['retention_proxy'] < 15 && $users > 30) {
                $qualityNotes[] = 'Puțini utilizatori revin în aceeași perioadă (retenție proxy mică).';
            }
        }

        if (Schema::hasTable('mobile_crashes')) {
            $crashBase = MobileCrash::query()->whereBetween('occurred_at', [$start, $end]);
            $crashes = (clone $crashBase)->count();
            $fatal = (clone $crashBase)->where('is_fatal', true)->count();
            $meta['crashes'] = $crashes;
            $meta['fatal_crashes'] = $fatal;
            $meta['crash_rate'] = $meta['sessions'] > 0
                ? round(($crashes / $meta['sessions']) * 100, 2)
                : 0;
            $meta['fatal_rate'] = $crashes > 0 ? round(($fatal / $crashes) * 100, 1) : 0;
            if ($meta['crash_rate'] >= 2) {
                $qualityNotes[] = 'Rata de crash pe sesiune e ridicată — prioritizează stabilitatea.';
            }
        }

        if (Schema::hasTable('mobile_feedback_reports')) {
            $fbBase = MobileFeedbackReport::query()->whereBetween('occurred_at', [$start, $end]);
            $meta['feedback'] = (clone $fbBase)->count();
            $meta['feedback_with_screenshot'] = (clone $fbBase)->where('has_screenshot', true)->count();
        }

        $peakHour = array_keys($hourly, max($hourly) ?: 0)[0] ?? 0;
        $weekdayLabels = ['Lun', 'Mar', 'Mie', 'Joi', 'Vin', 'Sâm', 'Dum'];
        $peakWeekdayIdx = array_keys($weekday, max($weekday) ?: 0)[0] ?? 0;

        return compact(
            'start',
            'end',
            'days',
            'schemaReady',
            'meta',
            'platforms',
            'versions',
            'hourly',
            'weekday',
            'weekdayLabels',
            'peakHour',
            'peakWeekdayIdx',
            'dailyActive',
            'funnel',
            'topEntryPages',
            'qualityNotes'
        );
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
