<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoogleAnalyticsService;
use App\Support\DashboardCache;

class GAAnalyticsController extends Controller
{
    protected $gaService;

    public function __construct(GoogleAnalyticsService $gaService)
    {
        $this->gaService = $gaService;
    }

    private function cachedReport(Request $request, string $name, callable $fetcher)
    {
        $startDate = $request->get('start_date', date('Y-m-01'));
        $endDate = $request->get('end_date', date('Y-m-d'));

        try {
            $data = DashboardCache::flexible(
                'ga-api:'.$name.':'.$startDate.':'.$endDate,
                DashboardCache::ttlGa($endDate),
                fn () => $fetcher($startDate, $endDate)
            );

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function users(Request $request)
    {
        return $this->cachedReport($request, 'users', fn ($s, $e) => $this->gaService->fetchUsersData($s, $e));
    }

    public function devices(Request $request)
    {
        return $this->cachedReport($request, 'devices', fn ($s, $e) => $this->gaService->fetchDevicesData($s, $e));
    }

    public function geo(Request $request)
    {
        return $this->cachedReport($request, 'geo', fn ($s, $e) => $this->gaService->fetchGeoData($s, $e));
    }

    public function content(Request $request)
    {
        return $this->cachedReport($request, 'content', fn ($s, $e) => $this->gaService->fetchContentData($s, $e));
    }

    public function ecommerce(Request $request)
    {
        return $this->cachedReport($request, 'ecommerce', fn ($s, $e) => $this->gaService->fetchEcommerceData($s, $e));
    }

    public function campaigns(Request $request)
    {
        return $this->cachedReport($request, 'campaigns', fn ($s, $e) => $this->gaService->fetchCampaignsData($s, $e));
    }
}
