<?php

namespace App\Http\Controllers;

use App\Models\MobileAnalyticsEvent;
use App\Support\DashboardCache;
use App\Support\MobileDailyRollup;
use App\Support\MobileRetention;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MobileAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('mobile.overview', $this->cachedDashboardData($request, 'overview'));
    }

    public function events(Request $request)
    {
        return view('mobile.events', $this->cachedDashboardData($request, 'events'));
    }

    public function funnels(Request $request)
    {
        return view('mobile.funnels', $this->cachedDashboardData($request, 'funnels'));
    }

    public function pagesList(Request $request)
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
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
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
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
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
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
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
        $recentEvents = null;

        if ($schemaReady) {
            $recentEvents = MobileAnalyticsEvent::query()
                ->select(MobileAnalyticsEvent::FEED_COLUMNS)
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
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
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

    private function cachedDashboardData(Request $request, string $section): array
    {
        [$start, $end] = $this->resolvePeriod($request);

        return DashboardCache::flexible(
            'mobile:dashboard:v8:'.$section.':'.$start->timestamp.':'.$end->timestamp,
            DashboardCache::ttlMobileRange($start, $end),
            fn () => $this->buildDashboardData($request, $section)
        );
    }

    private function buildDashboardData(Request $request, string $section): array
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');

        $summary = [
            'events' => 0,
            'sessions' => 0,
            'users' => 0,
            'devices' => 0,
            'page_views' => 0,
            'product_views' => 0,
            'searches' => 0,
            'add_to_cart' => 0,
            'banner_clicks' => 0,
            'cart_abandons' => 0,
            'orders' => 0,
            'cards_generated' => 0,
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
            $detailFrom = $this->detailWindowStart($start, $end);
            $base = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$start, $end]);
            $detailBase = MobileAnalyticsEvent::query()
                ->whereBetween('occurred_at', [$detailFrom, $end]);

            // Headline metrics: rollups for closed days + live scan for the tail.
            $counts = $this->periodCounts($start, $end);
            $eventsCount = (int) $counts->events;
            $sessionsCount = (int) $counts->sessions;
            $pageViews = (int) $counts->page_views;
            $ordersCount = (int) $counts->orders;
            $addToCart = (int) $counts->add_to_cart;
            $productViews = (int) $counts->product_views;

            $summary = [
                'events' => $eventsCount,
                'sessions' => $sessionsCount,
                'users' => (int) $counts->users,
                'devices' => (int) $counts->devices,
                'page_views' => $pageViews,
                'product_views' => $productViews,
                'searches' => (int) $counts->searches,
                'add_to_cart' => $addToCart,
                'banner_clicks' => (int) $counts->banner_clicks,
                'cart_abandons' => (int) $counts->cart_abandons,
                'orders' => $ordersCount,
                'cards_generated' => (int) ($counts->cards_generated ?? 0),
                'logins' => (int) $counts->logins,
                'map_opens' => (int) $counts->map_opens,
                'avg_page_seconds' => round(((float) $counts->avg_duration_ms) / 1000),
                'conversion_rate' => $sessionsCount > 0 ? round(($ordersCount / $sessionsCount) * 100, 2) : 0,
                'events_per_session' => $sessionsCount > 0 ? round($eventsCount / $sessionsCount, 1) : 0,
                'view_to_cart_rate' => $productViews > 0 ? round(($addToCart / $productViews) * 100, 1) : 0,
            ];

            if ($section === 'events') {
                $topPages = (clone $detailBase)
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

                $bannerClicks = (clone $detailBase)
                    ->select('banner_id', 'banner_title', DB::raw('COUNT(*) as clicks'), DB::raw('MAX(occurred_at) as last_click_at'))
                    ->where('event_name', 'banner_click')
                    ->groupBy('banner_id', 'banner_title')
                    ->orderByDesc('clicks')
                    ->limit(20)
                    ->get();

                $recentEvents = MobileAnalyticsEvent::query()
                    ->select(MobileAnalyticsEvent::FEED_COLUMNS)
                    ->where('occurred_at', '<=', $end)
                    ->latest('occurred_at')
                    ->limit(80)
                    ->get();

                $eventBreakdown = $this->eventBreakdownFromCounts($counts, $base);
            }

            if ($section === 'funnels') {
                $cartAbandons = (clone $detailBase)
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

                $funnel = $this->funnelFromCounts(
                    $counts,
                    $summary['page_views'],
                    $summary['product_views'],
                    $summary['add_to_cart'],
                    $summary['cart_abandons'],
                    $summary['orders']
                );
            }

            if ($section === 'overview') {
                $eventBreakdown = $this->eventBreakdownFromCounts($counts, null);
                $topSearches = $this->topMetadataValues($detailBase, 'search', '$.query', 12);
                $topProducts = $this->topMetadataValues($detailBase, 'product_view', '$.product_name', 12);
                $dailyChart = $this->dailyChart($start, $end);
            }
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

        $rows = [];
        $now = now();

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
            $occurredAt = $this->parseOccurredAt($data['occurred_at'] ?? null);
            $metadata = $data['metadata'] ?? null;
            $rows[] = [
                'event_name' => $data['event_name'],
                'session_id' => $data['session_id'] ?? null,
                'mobile_user_id' => $data['mobile_user_id'] ?? null,
                'device_id' => $data['device_id'] ?? null,
                'platform' => $data['platform'] ?? null,
                'app_version' => $data['app_version'] ?? null,
                'page' => $data['page'] ?? null,
                'previous_page' => $data['previous_page'] ?? null,
                'duration_ms' => $data['duration_ms'] ?? null,
                'checkout_step' => $data['checkout_step'] ?? null,
                'cart_total' => $data['cart_total'] ?? null,
                'items_count' => $data['items_count'] ?? null,
                'banner_id' => $data['banner_id'] ?? null,
                'banner_title' => $data['banner_title'] ?? null,
                'order_id' => $data['order_id'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'user_agent' => $data['user_agent'] ?? null,
                'metadata' => is_array($metadata) ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : $metadata,
                'occurred_at' => $occurredAt->toDateTimeString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            MobileAnalyticsEvent::insert($chunk);
            $created += count($chunk);
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

        $lastRolled = MobileDailyRollup::lastEventDay();
        $liveFrom = $start->copy();
        if ($lastRolled && $lastRolled->gte($start)) {
            $histEnd = $lastRolled->copy()->endOfDay();
            if ($histEnd->gt($end)) {
                $histEnd = $end->copy();
            }

            if (DashboardCache::tableExists('mobile_event_daily_rollups')) {
                $rollupRows = DB::table('mobile_event_daily_rollups')
                    ->select('day', 'event_name', 'total')
                    ->whereBetween('day', [$start->toDateString(), $histEnd->toDateString()])
                    ->whereIn('event_name', $eventNames)
                    ->get();

                foreach ($rollupRows as $row) {
                    $day = (string) $row->day;
                    $event = (string) $row->event_name;
                    if (isset($labelIndex[$day], $datasets[$event])) {
                        $datasets[$event][$labelIndex[$day]] = (int) $row->total;
                    }
                }
            }

            $liveFrom = $lastRolled->copy()->addDay()->startOfDay();
        }

        if ($liveFrom->lte($end)) {
            $rows = MobileAnalyticsEvent::query()
                ->select(DB::raw('DATE(occurred_at) as day'), 'event_name', DB::raw('COUNT(*) as total'))
                ->whereBetween('occurred_at', [$liveFrom, $end])
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
        }

        return ['labels' => $labels, 'datasets' => $datasets];
    }

    private function funnelFromCounts(object $counts, int $pageViews, int $productViews, int $addToCart, int $cartAbandoned, int $orders): array
    {
        $checkoutStarted = (int) ($counts->checkout_started ?? 0);
        if ($checkoutStarted === 0) {
            $checkoutStarted = (int) ($counts->checkout_step_sessions ?? 0);
        }
        if ($checkoutStarted === 0) {
            $checkoutStarted = $cartAbandoned + $orders;
        }

        $checkoutCompleted = (int) ($counts->checkout_completed ?? 0);
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
     * @return Collection<int, object{label: string, total: int}>
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

    private function emptyCounts(): object
    {
        return (object) [
            'events' => 0,
            'sessions' => 0,
            'users' => 0,
            'devices' => 0,
            'logged_in_sessions' => 0,
            'page_views' => 0,
            'product_views' => 0,
            'searches' => 0,
            'add_to_cart' => 0,
            'banner_clicks' => 0,
            'cart_abandons' => 0,
            'orders' => 0,
            'cards_generated' => 0,
            'logins' => 0,
            'map_opens' => 0,
            'checkout_started' => 0,
            'checkout_completed' => 0,
            'checkout_step_sessions' => 0,
            'avg_duration_ms' => 0,
            'by_event' => [],
        ];
    }

    private function applyEventTotals(object $counts, array $totals): void
    {
        $map = [
            'page_view' => 'page_views',
            'product_view' => 'product_views',
            'search' => 'searches',
            'add_to_cart' => 'add_to_cart',
            'banner_click' => 'banner_clicks',
            'cart_abandoned' => 'cart_abandons',
            'order_completed' => 'orders',
            'login_success' => 'logins',
            'map_open' => 'map_opens',
            'checkout_started' => 'checkout_started',
            'checkout_completed' => 'checkout_completed',
        ];

        foreach ($totals as $name => $total) {
            $total = (int) $total;
            $counts->events = (int) $counts->events + $total;
            $counts->by_event[$name] = (int) ($counts->by_event[$name] ?? 0) + $total;
            if (isset($map[$name])) {
                $field = $map[$name];
                $counts->{$field} = (int) $counts->{$field} + $total;
            }
        }
    }

    private function addCountObject(object $into, object $add): void
    {
        foreach ([
            'events', 'sessions', 'users', 'devices', 'logged_in_sessions',
            'page_views', 'product_views', 'searches', 'add_to_cart', 'banner_clicks',
            'cart_abandons', 'orders', 'cards_generated', 'logins', 'map_opens',
            'checkout_started', 'checkout_completed', 'checkout_step_sessions',
        ] as $field) {
            $into->{$field} = (int) ($into->{$field} ?? 0) + (int) ($add->{$field} ?? 0);
        }

        $into->avg_duration_ms = (float) ($add->avg_duration_ms ?? $into->avg_duration_ms ?? 0);
    }

    private function isGeneratedCardEvent(string $name): bool
    {
        $key = strtolower($name);

        return str_contains($key, 'card') && ! str_contains($key, 'cart');
    }

    private function periodCounts(Carbon $start, Carbon $end): object
    {
        return DashboardCache::flexible(
            'mobile:counts:v5:'.$start->timestamp.':'.$end->timestamp,
            DashboardCache::ttlMobileRange($start, $end),
            function () use ($start, $end) {
                $counts = $this->emptyCounts();
                $liveFrom = $start->copy();
                $lastRolled = MobileDailyRollup::lastEventDay();

                if ($lastRolled && $lastRolled->gte($start)) {
                    $histEnd = $lastRolled->copy()->endOfDay();
                    if ($histEnd->gt($end)) {
                        $histEnd = $end->copy();
                    }
                    $hist = MobileDailyRollup::eventTotals($start, $histEnd);
                    $this->applyEventTotals($counts, $hist['totals']);
                    $counts->sessions = (int) $counts->sessions + $hist['sessions'];
                    $counts->users = (int) $counts->users + $hist['users'];
                    $liveFrom = $lastRolled->copy()->addDay()->startOfDay();
                }

                if ($liveFrom->lte($end)) {
                    $live = $this->aggregateCounts(
                        MobileAnalyticsEvent::query()->whereBetween('occurred_at', [$liveFrom, $end])
                    );
                    $this->addCountObject($counts, $live);

                    // Extra GROUP BY only for the unrolled tail (usually today).
                    // Without rollups this would scan the whole range a second time.
                    if ($lastRolled) {
                        $liveEvents = MobileAnalyticsEvent::query()
                            ->whereBetween('occurred_at', [$liveFrom, $end])
                            ->select('event_name', DB::raw('COUNT(*) as total'))
                            ->groupBy('event_name')
                            ->pluck('total', 'event_name');

                        foreach ($liveEvents as $name => $total) {
                            $counts->by_event[$name] = (int) ($counts->by_event[$name] ?? 0) + (int) $total;
                        }

                        $counts->cards_generated = 0;
                        foreach ($counts->by_event as $name => $total) {
                            if ($this->isGeneratedCardEvent((string) $name)) {
                                $counts->cards_generated += (int) $total;
                            }
                        }
                    }
                }

                return $counts;
            }
        );
    }

    /** Calculate frequently used counters with a single scan of the period. */
    private function aggregateCounts($base): object
    {
        $row = $base->selectRaw(<<<'SQL'
            COUNT(*) as events,
            COUNT(DISTINCT session_id) as sessions,
            COUNT(DISTINCT mobile_user_id) as users,
            COUNT(DISTINCT device_id) as devices,
            COUNT(DISTINCT CASE WHEN mobile_user_id IS NOT NULL THEN session_id END) as logged_in_sessions,
            SUM(CASE WHEN event_name = 'page_view' THEN 1 ELSE 0 END) as page_views,
            SUM(CASE WHEN event_name = 'product_view' THEN 1 ELSE 0 END) as product_views,
            SUM(CASE WHEN event_name = 'search' THEN 1 ELSE 0 END) as searches,
            SUM(CASE WHEN event_name = 'add_to_cart' THEN 1 ELSE 0 END) as add_to_cart,
            SUM(CASE WHEN event_name = 'banner_click' THEN 1 ELSE 0 END) as banner_clicks,
            SUM(CASE WHEN event_name = 'cart_abandoned' THEN 1 ELSE 0 END) as cart_abandons,
            SUM(CASE WHEN event_name = 'order_completed' THEN 1 ELSE 0 END) as orders,
            SUM(CASE WHEN event_name IN ('card_generated','card_created','generate_card','card_generate','cards_generated','card_generat','generare_card') THEN 1 ELSE 0 END) as cards_generated,
            SUM(CASE WHEN event_name = 'login_success' THEN 1 ELSE 0 END) as logins,
            SUM(CASE WHEN event_name = 'map_open' THEN 1 ELSE 0 END) as map_opens,
            SUM(CASE WHEN event_name = 'checkout_started' THEN 1 ELSE 0 END) as checkout_started,
            SUM(CASE WHEN event_name = 'checkout_completed' THEN 1 ELSE 0 END) as checkout_completed,
            COUNT(DISTINCT CASE WHEN event_name = 'checkout_step' AND checkout_step >= 2 THEN session_id END) as checkout_step_sessions,
            AVG(CASE WHEN duration_ms IS NOT NULL THEN duration_ms END) as avg_duration_ms
        SQL)->first();

        return $row ?: $this->emptyCounts();
    }

    /** Liste / JSON extras pe intervale lungi: ultimele 30 de zile, nu tot istoricul. */
    private function detailWindowStart(Carbon $start, Carbon $end): Carbon
    {
        if ($start->diffInDays($end) <= 45) {
            return $start->copy();
        }

        $from = $end->copy()->subDays(29)->startOfDay();

        return $from->lt($start) ? $start->copy() : $from;
    }

    private function eventBreakdownFromCounts(object $counts, $fallbackBase = null)
    {
        if (! empty($counts->by_event)) {
            return collect($counts->by_event)
                ->map(fn ($total, $name) => (object) ['event_name' => $name, 'total' => (int) $total])
                ->sortByDesc('total')
                ->take(12)
                ->values();
        }

        if ($fallbackBase === null) {
            return collect();
        }

        return (clone $fallbackBase)
            ->select('event_name', DB::raw('COUNT(*) as total'))
            ->groupBy('event_name')
            ->orderByDesc('total')
            ->limit(12)
            ->get();
    }

    private function resolvePeriod(Request $request): array
    {
        return MobileRetention::resolvePeriod($request);
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
