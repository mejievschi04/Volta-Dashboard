<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\OneCController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OneCInclusivePeriodTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_requests_the_day_after_the_inclusive_period_end_from_one_c(): void
    {
        Http::fake([
            '*' => Http::response([
                'meta' => ['company' => 'Volta', 'currency' => 'MDL'],
                'kpiTotal' => ['vanzariFaraTVA' => 100, 'nrComenzi' => 1],
                'kpiPeOperator' => [],
            ]),
        ]);

        $request = Request::create('/api/1c/sync-kpi', 'POST', [
            'date_start' => '2026-08-01',
            'date_end' => '2026-08-31',
            'force' => true,
        ]);

        $response = app(OneCController::class)->syncKpi($request);

        $this->assertTrue($response->getData(true)['success']);
        Http::assertSent(fn (HttpRequest $request) => $request['date_start'] === '2026-08-01'
            && $request['date_end'] === '2026-09-01'
        );
        $this->assertDatabaseHas('onec_kpi_syncs', [
            'period_start' => '2026-08-01 00:00:00',
            'period_end' => '2026-08-31 00:00:00',
        ]);
    }
}
