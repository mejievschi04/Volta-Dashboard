<?php

namespace Tests\Feature;

use App\Models\OnecKpiSync;
use App\Models\Operator;
use App\Models\RaportLunarCallCenterInput;
use App\Models\User;
use App\Services\CallCenterRaportLunarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RaportLunarVoltaChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        config(['services.voltachat.url' => 'https://chat.example', 'services.voltachat.analytics_token' => 'secret']);
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));

        Operator::create(['nume' => 'Ana Popescu', 'activ' => true]);
        Operator::create(['nume' => 'Ion Rusu', 'activ' => true]);
        $sync = OnecKpiSync::create(['period_start' => '2026-10-01', 'period_end' => '2026-10-31', 'vanzari_cu_tva' => 1, 'vanzari_fara_tva' => 1, 'profit' => 1]);
        $sync->operatori()->create(['operator_id_1c' => 'A1', 'operator_nume' => 'Ana Popescu', 'vanzari_fara_tva' => 1000, 'vanzari_cu_tva' => 1200, 'profit' => 300]);
        $sync->operatori()->create(['operator_id_1c' => 'I2', 'operator_nume' => 'Ion Rusu', 'vanzari_fara_tva' => 1000, 'vanzari_cu_tva' => 1200, 'profit' => 300]);
        RaportLunarCallCenterInput::create(['ym' => '2026-10', 'operator_nume' => 'Ion Rusu', 'chaturi' => 7, 'apeluri' => 4]);
    }

    private function fakeChat(): void
    {
        Http::fake(['chat.example/*' => Http::response(['datasets' => ['operators' => ['rows' => [
            ['id' => 8, 'name' => 'Popescu Ană', 'conversations' => 120],
            ['id' => 9, 'name' => 'Ion', 'conversations' => 80],
            ['id' => 11, 'name' => 'Maria Stan', 'conversations' => 30],
        ]]]])]);
    }

    private function rows(): array
    {
        return collect(app(CallCenterRaportLunarService::class)->build('2026-10')['operator_rows'])->keyBy('np')->all();
    }

    public function test_chats_are_filled_from_voltachat_by_name(): void
    {
        $this->fakeChat();
        $data = app(CallCenterRaportLunarService::class)->build('2026-10');
        $rows = collect($data['operator_rows'])->keyBy('np');

        $this->assertSame(120, $rows['Ana Popescu']['chaturi_int']);
        $this->assertSame('voltachat', $rows['Ana Popescu']['chat_source']);
        $this->assertSame(80, $rows['Ion Rusu']['chaturi_int']);
        $this->assertSame(['Maria Stan'], array_column($data['voltachat']['unmatched'], 'name'));
        $this->assertSame('200', $data['footer_total']['chaturi']);

        $this->assertSame(120, RaportLunarCallCenterInput::where('operator_nume', 'Ana Popescu')->value('chaturi'));
        $this->assertSame(80, RaportLunarCallCenterInput::where('operator_nume', 'Ion Rusu')->value('chaturi'));
        $this->assertSame(4, RaportLunarCallCenterInput::where('operator_nume', 'Ion Rusu')->value('apeluri'));

        Http::assertSent(fn ($r) => $r['from'] === '2026-10-01' && $r['to'] === '2026-10-15' && $r['type'] === 'customer');
    }

    public function test_saved_link_and_manual_override(): void
    {
        $this->fakeChat();
        Operator::where('nume', 'Ion Rusu')->update(['voltachat_operator_id' => 11]);
        $this->assertSame(30, $this->rows()['Ion Rusu']['chaturi_int']);

        Operator::where('nume', 'Ion Rusu')->update(['voltachat_operator_id' => 0]);
        RaportLunarCallCenterInput::where('operator_nume', 'Ion Rusu')->update(['chaturi' => 7]);
        $rows = $this->rows();
        $this->assertSame(7, $rows['Ion Rusu']['chaturi_int']);
        $this->assertSame('manual', $rows['Ion Rusu']['chat_source']);
        $this->assertSame('manual', $rows['Ion Rusu']['voltachat_mode']);
    }

    public function test_page_shows_selects_and_saving_stores_links(): void
    {
        $this->fakeChat();
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'vcrl'.uniqid()]);

        $this->actingAs($admin)->get('/rapoarte/raport-lunar?month=2026-10')
            ->assertOk()
            ->assertSee('Automat din VoltaChat')
            ->assertSee('name="voltachat_id[]"', false)
            ->assertSee('Conturi VoltaChat fără operator potrivit:')
            ->assertSee('Maria Stan (30)')
            ->assertSee('js/excel-export-exceljs.js', false);

        $this->actingAs($admin)->post('/rapoarte/raport-lunar/inputs', [
            'month' => '2026-10',
            'operators' => ['Ana Popescu', 'Ion Rusu'],
            'chaturi' => [120, 30],
            'apeluri' => [1, 2],
            'plan' => ['', ''],
            'voltachat_id' => ['', '11'],
        ])->assertRedirect();

        $this->assertNull(Operator::where('nume', 'Ana Popescu')->value('voltachat_operator_id'));
        $this->assertSame(11, Operator::where('nume', 'Ion Rusu')->value('voltachat_operator_id'));
    }

    public function test_falls_back_to_saved_values_when_voltachat_is_down(): void
    {
        Http::fake(['chat.example/*' => Http::response('down', 500)]);
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'vcrl'.uniqid()]);

        $rows = $this->rows();
        $this->assertSame(7, $rows['Ion Rusu']['chaturi_int']);
        $this->assertSame('manual', $rows['Ion Rusu']['chat_source']);

        $this->actingAs($admin)->get('/rapoarte/raport-lunar?month=2026-10')
            ->assertOk()
            ->assertSee('VoltaChat a răspuns cu o eroare')
            ->assertDontSee('name="voltachat_id[]"', false);
    }
}
