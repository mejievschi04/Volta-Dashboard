<?php

namespace App\Http\Controllers;

use App\Models\MobileAnalyticsEvent;
use App\Support\DashboardCache;
use App\Support\MobileDailyRollup;
use App\Support\MobileEventNames;
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

    public function summary(Request $request)
    {
        return view('mobile.summary', $this->cachedDashboardData($request, 'summary'));
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
            'mobile:dashboard:v12:'.$section.':'.$start->timestamp.':'.$end->timestamp,
            DashboardCache::ttlMobileRange($start, $end),
            fn () => $this->buildDashboardData($request, $section)
        );
    }

    private function buildDashboardData(Request $request, string $section): array
    {
        [$start, $end] = $this->resolvePeriod($request);
        $schemaReady = DashboardCache::tableExists('mobile_analytics_events');
        $listsCapped = false;

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
            $listsCapped = $start->diffInDays($end) > 90;
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
                $eventBreakdown = $this->eventBreakdownFromCounts($counts, $base);
                $topSearches = $this->topMetadataValues($detailBase, 'search', '$.query', 12);
                $topProducts = $this->topMetadataValues($detailBase, 'product_view', '$.product_name', 12);
                $dailyChart = $this->dailyChart($start, $end);
            }

            if ($section === 'summary') {
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
            'funnel',
            'listsCapped'
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
            'event_name' => MobileEventNames::canonical((string) (Arr::get($event, 'event_name') ?? Arr::get($event, 'event') ?? '')),
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

        $eventNames = MobileEventNames::chartEvents();
        $datasets = array_fill_keys(MobileEventNames::chartSeries(), array_fill(0, count($labels), 0));
        $labelIndex = array_flip($labels);

        $rolledDays = $this->rolledEventDays($start, $end);
        if ($rolledDays !== []) {
            $histEnd = now()->subDay()->endOfDay();
            if ($histEnd->gt($end)) {
                $histEnd = $end->copy();
            }

            $rollupRows = DB::table('mobile_event_daily_rollups')
                ->select('day', 'event_name', 'total')
                ->whereBetween('day', [$start->toDateString(), $histEnd->toDateString()])
                ->whereIn('event_name', $eventNames)
                ->get();

            foreach ($rollupRows as $row) {
                $day = (string) $row->day;
                $event = MobileEventNames::canonical((string) $row->event_name);
                if (isset($labelIndex[$day], $datasets[$event])) {
                    $datasets[$event][$labelIndex[$day]] += (int) $row->total;
                }
            }
        }

        $live = $this->liveEventsQuery($start, $end, $rolledDays);
        $rows = $live
            ->select(DB::raw('DATE(occurred_at) as day'), 'event_name', DB::raw('COUNT(*) as total'))
            ->whereIn('event_name', $eventNames)
            ->groupBy(DB::raw('DATE(occurred_at)'), 'event_name')
            ->get();

        foreach ($rows as $row) {
            $day = (string) $row->day;
            $event = MobileEventNames::canonical((string) $row->event_name);
            if (isset($labelIndex[$day], $datasets[$event])) {
                $datasets[$event][$labelIndex[$day]] += (int) $row->total;
            }
        }

        return ['labels' => self::europeanLabels($labels), 'datasets' => $datasets];
    }

    public static function europeanLabels(array $ymdLabels): array
    {
        return array_map(static fn (string $day): string => Carbon::parse($day)->format('d.m.Y'), $ymdLabels);
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
                ->whereIn('event_name', MobileEventNames::aliases($eventName))
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
            'discount_card_generate_success' => 'cards_generated',
        ];

        foreach ($totals as $name => $total) {
            $total = (int) $total;
            $counts->events = (int) $counts->events + $total;
            $counts->by_event[$name] = (int) ($counts->by_event[$name] ?? 0) + $total;
            $canonical = MobileEventNames::canonical((string) $name);
            if (isset($map[$canonical])) {
                $field = $map[$canonical];
                $counts->{$field} = (int) $counts->{$field} + $total;
            }
        }
    }

    private function addNamedCounts(object $into, object $add): void
    {
        foreach ([
            'events', 'logged_in_sessions',
            'page_views', 'product_views', 'searches', 'add_to_cart', 'banner_clicks',
            'cart_abandons', 'orders', 'cards_generated', 'logins', 'map_opens',
            'checkout_started', 'checkout_completed', 'checkout_step_sessions',
        ] as $field) {
            $into->{$field} = (int) ($into->{$field} ?? 0) + (int) ($add->{$field} ?? 0);
        }
    }

    private function isGeneratedCardEvent(string $name): bool
    {
        return MobileEventNames::isCardGenerated($name);
    }

    private function periodCounts(Carbon $start, Carbon $end): object
    {
        return DashboardCache::flexible(
            'mobile:counts:v8:'.$start->timestamp.':'.$end->timestamp,
            DashboardCache::ttlMobileRange($start, $end),
            function () use ($start, $end) {
                $counts = $this->emptyCounts();
                $rolledDays = $this->rolledEventDays($start, $end);

                if ($rolledDays !== []) {
                    $histEnd = now()->subDay()->endOfDay();
                    if ($histEnd->gt($end)) {
                        $histEnd = $end->copy();
                    }
                    $hist = MobileDailyRollup::eventTotals($start, $histEnd);
                    $this->applyEventTotals($counts, $hist['totals']);
                }

                $named = $this->aggregateCounts($this->liveEventsQuery($start, $end, $rolledDays));
                $this->addNamedCounts($counts, $named);

                $liveEvents = $this->liveEventsQuery($start, $end, $rolledDays)
                    ->select('event_name', DB::raw('COUNT(*) as total'))
                    ->groupBy('event_name')
                    ->pluck('total', 'event_name');

                foreach ($liveEvents as $name => $total) {
                    $counts->by_event[$name] = (int) ($counts->by_event[$name] ?? 0) + (int) $total;
                }

                $this->fillUniquesFromRaw($counts, $start, $end);

                if ($counts->by_event !== []) {
                    $counts->cards_generated = 0;
                    foreach ($counts->by_event as $name => $total) {
                        if ($this->isGeneratedCardEvent((string) $name)) {
                            $counts->cards_generated += (int) $total;
                        }
                    }
                }

                return $counts;
            }
        );
    }

    /** @param list<string> $rolledDays */
    private function liveEventsQuery(Carbon $start, Carbon $end, array $rolledDays)
    {
        $query = MobileAnalyticsEvent::query()->whereBetween('occurred_at', [$start, $end]);
        if ($rolledDays === []) {
            return $query;
        }

        $placeholders = implode(',', array_fill(0, count($rolledDays), '?'));

        return $query->whereRaw(
            '(occurred_at >= ? OR DATE(occurred_at) NOT IN ('.$placeholders.'))',
            array_merge([now()->startOfDay()->toDateTimeString()], $rolledDays)
        );
    }

    /** @return list<string> */
    private function rolledEventDays(Carbon $start, Carbon $end): array
    {
        if (! DashboardCache::tableExists('mobile_event_daily_rollups')) {
            return [];
        }

        $until = now()->subDay()->startOfDay();
        if ($end->copy()->startOfDay()->lt($until)) {
            $until = $end->copy()->startOfDay();
        }
        if ($until->lt($start->copy()->startOfDay())) {
            return [];
        }

        return DB::table('mobile_event_daily_rollups')
            ->whereBetween('day', [$start->toDateString(), $until->toDateString()])
            ->distinct()
            ->pluck('day')
            ->map(fn ($day) => Carbon::parse((string) $day)->toDateString())
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Unici exacți pe interval: zilele închise din mobile_daily_actives + coada live din evenimente,
     * reunite cu UNION ca aceeași persoană să nu fie numărată de două ori.
     */
    private function fillUniquesFromRaw(object $counts, Carbon $start, Carbon $end): void
    {
        $counts->sessions = 0;
        $counts->users = 0;
        $counts->devices = 0;

        $activesFrom = null;
        $activesTo = null;
        $rawFrom = $start->copy();

        $lastActive = MobileDailyRollup::lastActiveDay();
        if ($lastActive) {
            $closed = now()->subDay()->startOfDay();
            $activesTo = $lastActive->lt($closed) ? $lastActive : $closed;
            if ($activesTo->gt($end)) {
                $activesTo = $end->copy()->startOfDay();
            }
            if ($activesTo->gte($start->copy()->startOfDay())) {
                $activesFrom = $start->copy()->startOfDay();
                $rawFrom = $activesTo->copy()->addDay()->startOfDay();
            } else {
                $activesTo = null;
            }
        }

        foreach (MobileDailyRollup::ACTIVE_KINDS as $kind => $column) {
            $parts = [];
            if ($activesFrom && $activesTo) {
                $parts[] = DB::table('mobile_daily_actives')
                    ->distinct()
                    ->select('identity')
                    ->where('kind', $kind)
                    ->whereBetween('day', [$activesFrom->toDateString(), $activesTo->toDateString()]);
            }
            if ($rawFrom->lte($end)) {
                $parts[] = DB::table('mobile_analytics_events')
                    ->distinct()
                    ->selectRaw("{$column} as identity")
                    ->whereBetween('occurred_at', [$rawFrom, $end])
                    ->whereNotNull($column)
                    ->where($column, '!=', '');
            }
            if ($parts === []) {
                continue;
            }

            $union = array_shift($parts);
            foreach ($parts as $part) {
                $union->union($part);
            }

            $total = DB::query()->fromSub($union, 'u')->count();
            $field = ['user' => 'users', 'device' => 'devices', 'session' => 'sessions'][$kind];
            $counts->{$field} = (int) $total;
        }

        $legacy = $this->legacyUniqueTotals($start, $end);
        $counts->sessions += $legacy['sessions'];
        $counts->users += $legacy['users'];

        $avg = MobileAnalyticsEvent::query()
            ->whereBetween('occurred_at', [$start, $end])
            ->whereNotNull('duration_ms')
            ->avg('duration_ms');
        $counts->avg_duration_ms = (float) ($avg ?? 0);
    }

    /**
     * Zile vechi care au doar totaluri zilnice (fără listă de activi și fără date brute).
     * Acolo unicii sunt aproximați ca sumă pe zile.
     *
     * @return array{sessions: int, users: int}
     */
    private function legacyUniqueTotals(Carbon $start, Carbon $end): array
    {
        $bounds = array_filter([
            MobileDailyRollup::firstActiveDay(),
            ($minRaw = MobileAnalyticsEvent::query()->min('occurred_at')) ? Carbon::parse($minRaw)->startOfDay() : null,
        ]);
        if ($bounds === []) {
            return ['sessions' => 0, 'users' => 0];
        }

        $firstExact = min($bounds);
        $legacyEnd = $firstExact->copy()->subDay()->endOfDay();
        if ($legacyEnd->gt($end)) {
            $legacyEnd = $end->copy();
        }
        if ($legacyEnd->lt($start)) {
            return ['sessions' => 0, 'users' => 0];
        }

        $hist = MobileDailyRollup::eventTotals($start, $legacyEnd);

        return ['sessions' => $hist['sessions'], 'users' => $hist['users']];
    }

    /** Calculate frequently used counters with a single scan of the period. */
    private function aggregateCounts($base): object
    {
        $sql = implode(",\n            ", [
            'COUNT(*) as events',
            'COUNT(DISTINCT session_id) as sessions',
            'COUNT(DISTINCT mobile_user_id) as users',
            'COUNT(DISTINCT device_id) as devices',
            'COUNT(DISTINCT CASE WHEN mobile_user_id IS NOT NULL THEN session_id END) as logged_in_sessions',
            MobileEventNames::sqlSum('page_view', 'page_views'),
            MobileEventNames::sqlSum('product_view', 'product_views'),
            MobileEventNames::sqlSum('search', 'searches'),
            MobileEventNames::sqlSum('add_to_cart', 'add_to_cart'),
            MobileEventNames::sqlSum('banner_click', 'banner_clicks'),
            MobileEventNames::sqlSum('cart_abandoned', 'cart_abandons'),
            MobileEventNames::sqlSum('order_completed', 'orders'),
            MobileEventNames::sqlSum('discount_card_generate_success', 'cards_generated'),
            MobileEventNames::sqlSum('login_success', 'logins'),
            MobileEventNames::sqlSum('map_open', 'map_opens'),
            MobileEventNames::sqlSum('checkout_started', 'checkout_started'),
            MobileEventNames::sqlSum('checkout_completed', 'checkout_completed'),
            'COUNT(DISTINCT CASE WHEN event_name = \'checkout_step\' AND checkout_step >= 2 THEN session_id END) as checkout_step_sessions',
            'AVG(CASE WHEN duration_ms IS NOT NULL THEN duration_ms END) as avg_duration_ms',
        ]);

        $row = $base->selectRaw($sql)->first();

        return $row ?: $this->emptyCounts();
    }

    /** Liste detaliate pe intervale foarte lungi: ultimele 90 de zile. */
    private function detailWindowStart(Carbon $start, Carbon $end): Carbon
    {
        if ($start->diffInDays($end) <= 90) {
            return $start->copy();
        }

        $from = $end->copy()->subDays(89)->startOfDay();

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
