<?php

namespace Tests\Feature;

use App\Models\OnecKpiSync;
use App\Models\Operator;
use App\Models\PlanVanzari;
use App\Models\RaportLunarCallCenterInput;
use App\Models\User;
use App\Services\CallCenterRaportLunarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RaportLunarPlanIndividualTest extends TestCase
{
    use RefreshDatabase;

    private function seedMonth(): void
    {
        Operator::create(['nume' => 'Ana Popescu', 'activ' => true]);
        Operator::create(['nume' => 'Ion Rusu', 'activ' => true]);

        $sync = OnecKpiSync::create([
            'period_start' => '2026-10-01',
            'period_end' => '2026-10-31',
            'vanzari_cu_tva' => 360000,
            'vanzari_fara_tva' => 300000,
            'profit' => 90000,
        ]);
        $sync->operatori()->create(['operator_id_1c' => 'A1', 'operator_nume' => 'Ana Popescu', 'vanzari_fara_tva' => 180000, 'vanzari_cu_tva' => 216000, 'profit' => 50000]);
        $sync->operatori()->create(['operator_id_1c' => 'I2', 'operator_nume' => 'Ion Rusu', 'vanzari_fara_tva' => 120000, 'vanzari_cu_tva' => 144000, 'profit' => 40000]);

        PlanVanzari::create(['an' => 2026, 'luna' => 'Octombrie', 'valoare' => 400000]);
    }

    public function test_admin_saves_individual_plans_and_sees_fulfilment(): void
    {
        $this->seedMonth();
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'rladmin'.uniqid()]);

        $this->actingAs($admin)->post('/rapoarte/raport-lunar/inputs', [
            'month' => '2026-10',
            'operators' => ['Ana Popescu', 'Ion Rusu'],
            'chaturi' => [10, 5],
            'apeluri' => [3, 2],
            'plan' => ['200000', ''],
        ])->assertRedirect(route('rapoarte.raport-lunar', ['month' => '2026-10']));

        $this->assertSame(200000.0, RaportLunarCallCenterInput::where('operator_nume', 'Ana Popescu')->value('plan_individual') + 0.0);
        $this->assertNull(RaportLunarCallCenterInput::where('operator_nume', 'Ion Rusu')->value('plan_individual'));

        $data = app(CallCenterRaportLunarService::class)->build('2026-10');
        $rows = collect($data['operator_rows'])->keyBy('np');
        $this->assertSame('200000', $rows['Ana Popescu']['plan_individual']);
        $this->assertSame('90,00 %', $rows['Ana Popescu']['indeplinire_plan']);
        $this->assertSame('', $rows['Ion Rusu']['indeplinire_plan']);
        $this->assertSame(200000.0, $data['sum_plan_individual']);

        $excelRow = collect($data['excel_sheets'][0]['aoa'])->first(fn ($r) => $r[0] === 'Ana Popescu');
        $this->assertSame(200000.0, $excelRow[6]);
        $this->assertSame(90.0, $excelRow[7]);

        $this->actingAs($admin)->get('/rapoarte/raport-lunar?month=2026-10')
            ->assertOk()
            ->assertSee('name="plan[]"', false)
            ->assertSee('90,00 %')
            ->assertSee('Împarte planul lunar egal');
    }

    public function test_operator_profile_shows_plan_progress(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        $this->seedMonth();
        RaportLunarCallCenterInput::create(['ym' => '2026-10', 'operator_nume' => 'Ana Popescu', 'chaturi' => 0, 'apeluri' => 0, 'plan_individual' => 240000]);
        $admin = User::factory()->create(['role' => 'admin', 'username' => 'rladmin'.uniqid()]);
        $ana = Operator::where('nume', 'Ana Popescu')->first();

        $this->actingAs($admin)->get(route('operatori.show', $ana->id))
            ->assertOk()
            ->assertSee('Planul personal · Octombrie 2026')
            ->assertSee('75,0%')
            ->assertSee('180.000,00 MDL din 240.000,00 MDL')
            ->assertSee('5.000,00 MDL')
            ->assertSee('12 zile lucrătoare rămase (L–V)')
            ->assertSee('Bonus din vânzări · Octombrie 2026')
            ->assertSee('2.600,00 MDL')
            ->assertSee('5,2% din profitul brut de 50.000,00 MDL')
            ->assertSee('Bonus (5,2%)')
            ->assertSee('<th class="tc">Îndeplinire</th>', false);

        $ion = Operator::where('nume', 'Ion Rusu')->first();
        $this->actingAs($admin)->get(route('operatori.show', $ion->id))
            ->assertOk()
            ->assertDontSee('Planul personal ·')
            ->assertSee('Planul pentru luna curentă nu a fost setat încă.')
            ->assertDontSee('<th class="tc">Îndeplinire</th>', false);
    }

    public function test_operator_sees_own_plan_on_datele_mele(): void
    {
        $this->travelTo(now()->setDate(2026, 10, 15)->setTime(12, 0));
        $this->seedMonth();
        RaportLunarCallCenterInput::create(['ym' => '2026-10', 'operator_nume' => 'Ana Popescu', 'chaturi' => 0, 'apeluri' => 0, 'plan_individual' => 150000]);
        $user = User::factory()->create(['role' => 'operator', 'username' => 'rlop'.uniqid(), 'full_name' => 'Ana Popescu', 'operator_nume' => 'Ana Popescu']);

        $this->actingAs($user)->get('/datele-mele')
            ->assertOk()
            ->assertSee('Planul personal · Octombrie 2026')
            ->assertSee('120,0%')
            ->assertSee('Plan atins')
            ->assertSee('Bonus din vânzări · Octombrie 2026')
            ->assertSee('2.600,00 MDL');
    }

    public function test_non_admin_cannot_save_plans(): void
    {
        $this->seedMonth();
        $dev = User::factory()->create(['role' => 'dev', 'username' => 'rldev'.uniqid()]);

        $this->actingAs($dev)->post('/rapoarte/raport-lunar/inputs', [
            'month' => '2026-10',
            'operators' => ['Ana Popescu'],
            'chaturi' => [1],
            'apeluri' => [1],
            'plan' => ['1000'],
        ])->assertRedirect(route('dashboard'));

        $this->assertSame(0, RaportLunarCallCenterInput::count());
    }
}
