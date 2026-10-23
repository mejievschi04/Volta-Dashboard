<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VoltaChatAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public static function sample(): array
    {
        $labels = [];
        $conv = [];
        for ($i = 1; $i <= 23; $i++) {
            $labels[] = sprintf('2026-10-%02d', $i);
            $conv[] = 20 + ($i * 7) % 31;
        }
        $points = [];
        for ($d = 0; $d < 7; $d++) {
            for ($h = 7; $h < 22; $h++) {
                $points[] = ['dow' => $d, 'h' => $h, 'v' => max(0, (int) round(30 - abs(13 - $h) * 3 - ($d >= 5 ? 12 : 0)))];
            }
        }

        return [
            'generated_at' => '2026-10-23T12:00:00+03:00',
            'filters' => ['type' => 'customer', 'grain' => 'day'],
            'channels' => [['id' => 1, 'name' => 'Web'], ['id' => 2, 'name' => 'Telegram'], ['id' => 3, 'name' => 'Viber']],
            'kpis' => ['total_conversations' => 812, 'unique_customers' => 640, 'total_messages' => 6930, 'avg_messages_per_conversation' => 8.53, 'median_messages_per_conversation' => 6, 'new_customers' => 410, 'returning_customers' => 230, 'status_new' => 12, 'status_open' => 25, 'status_active' => 8, 'status_closed' => 767, 'status_closed_rate' => 94.5, 'read_rate_inbound' => 97.2, 'read_rate_outbound' => 81.4],
            'datasets' => [
                'conversations' => ['labels' => $labels, 'data' => $conv],
                'unique_customers' => ['labels' => $labels, 'data' => array_map(fn ($v) => (int) ($v * 0.8), $conv)],
                'messages' => ['labels' => $labels, 'data' => array_map(fn ($v) => $v * 8, $conv)],
                'channels' => ['labels' => ['Web', 'Telegram', 'Viber'], 'data' => [480, 220, 112]],
                'heatmap' => ['points' => $points, 'max' => 30],
                'status' => ['labels' => ['new', 'open', 'active', 'closed'], 'data' => [12, 25, 8, 767]],
                'read_rate_inbound' => ['labels' => $labels, 'data' => array_map(fn ($v) => 90 + $v % 10, $conv)],
                'read_rate_outbound' => ['labels' => $labels, 'data' => array_map(fn ($v) => 70 + $v % 20, $conv)],
                'operators' => ['rows' => [
                    ['id' => 8, 'name' => 'Marina', 'conversations' => 342, 'art_seconds' => 669, 'replies' => 690, 'median_seconds' => 25, 'buckets' => ['lt_1m' => 410, 'm1_5' => 90, 'm5_15' => 60, 'm15_60' => 110, 'gt_1h' => 20], 'slowest' => [['conversation_id' => 4202, 'from' => '2026-10-12T09:10:00+03:00', 'to' => '2026-10-12T12:22:00+03:00', 'seconds' => 11542, 'url' => 'https://chat.example/admin/conversations?search=%234202'], ['conversation_id' => 4310, 'from' => '2026-10-15T10:00:00+03:00', 'to' => '2026-10-15T11:30:00+03:00', 'seconds' => 5400, 'url' => 'https://chat.example/admin/conversations?search=%234310']]],
                    ['id' => 9, 'name' => 'Ion', 'conversations' => 280, 'art_seconds' => 95, 'replies' => 510, 'median_seconds' => 18, 'buckets' => ['lt_1m' => 400, 'm1_5' => 80, 'm5_15' => 20, 'm15_60' => 10, 'gt_1h' => 0], 'slowest' => []],
                    ['id' => 10, 'name' => 'Ana', 'conversations' => 190, 'art_seconds' => 1320, 'replies' => 300, 'median_seconds' => 240, 'buckets' => ['lt_1m' => 80, 'm1_5' => 70, 'm5_15' => 60, 'm15_60' => 60, 'gt_1h' => 30], 'slowest' => [['conversation_id' => 4400, 'from' => '2026-10-20T15:00:00+03:00', 'seconds' => 7200, 'url' => 'javascript:alert(1)']]],
                ]],
            ],
        ];
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'username' => 'vcadmin'.uniqid()]);
    }

    public function test_renders_with_data(): void
    {
        config(['services.voltachat.url' => 'https://chat.example/', 'services.voltachat.analytics_token' => 'secret']);
        Http::fake(['chat.example/api/analytics*' => Http::response(self::sample())]);

        $this->actingAs($this->admin())
            ->get('/aplicatia-volta/voltachat?start=2026-10-01&end=2026-10-23&chat_channel=2')
            ->assertOk()
            ->assertSee('812')
            ->assertSee('Marina')
            ->assertSee('11 min 9 s')
            ->assertSee('3 h 12 min')
            ->assertSee('94,5%')
            ->assertDontSee("javascript:alert", false);

        Http::assertSent(fn ($r) => $r->hasHeader('Authorization', 'Bearer secret')
            && str_starts_with($r->url(), 'https://chat.example/api/analytics?')
            && $r['from'] === '2026-10-01' && $r['to'] === '2026-10-23' && (int) $r['channelId'] === 2 && $r['grain'] === 'day');

        $this->get('/aplicatia-volta?start=2026-10-01&end=2026-10-23')->assertOk()->assertSee('voltaChatSection');
    }

    public function test_errors(): void
    {
        config(['services.voltachat.url' => '', 'services.voltachat.analytics_token' => '']);
        $this->actingAs($this->admin())->get('/aplicatia-volta/voltachat')->assertOk()->assertSee('VOLTACHAT_ANALYTICS_TOKEN');

        config(['services.voltachat.url' => 'https://chat.example', 'services.voltachat.analytics_token' => 'bad']);
        Http::fake(['*' => Http::response(['message' => 'x'], 401)]);
        $this->get('/aplicatia-volta/voltachat')->assertOk()->assertSee('greșit (401)', false);
    }
}
