<?php

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** Date locale idempotente pentru previzualizarea tuturor rapoartelor. */
class LocalDemoSeeder extends Seeder
{
    public function run(): void
    {
        $now = CarbonImmutable::now();
        $password = Hash::make('demo1234');

        foreach ([
            ['username' => 'demo-admin', 'name' => 'Administrator demo', 'full_name' => 'Administrator demo', 'email' => 'admin.demo@volta.local', 'role' => 'admin'],
            ['username' => 'demo-dev', 'name' => 'Developer demo', 'full_name' => 'Developer demo', 'email' => 'dev.demo@volta.local', 'role' => 'dev'],
            ['username' => 'maria.popescu', 'name' => 'Maria Popescu', 'full_name' => 'Maria Popescu', 'email' => 'maria.popescu@volta.local', 'role' => 'operator', 'operator_nume' => 'Maria Popescu'],
        ] as $user) {
            DB::table('users')->updateOrInsert(['username' => $user['username']], array_merge($user, [
                'password' => $password, 'password_hash' => $password, 'currency' => 'MDL', 'language' => 'Română', 'country' => 'Republica Moldova', 'theme' => 'dark', 'created_at' => $now, 'updated_at' => $now,
            ]));
        }

        $operators = [
            ['nume' => 'Maria Popescu', 'email' => 'maria.popescu@volta.local', 'telefon' => '+373 68 100 101', 'functie' => 'Operator vânzări'],
            ['nume' => 'Andrei Rusu', 'email' => 'andrei.rusu@volta.local', 'telefon' => '+373 68 100 102', 'functie' => 'Operator vânzări'],
            ['nume' => 'Elena Ceban', 'email' => 'elena.ceban@volta.local', 'telefon' => '+373 68 100 103', 'functie' => 'Operator senior'],
            ['nume' => 'Victor Munteanu', 'email' => 'victor.munteanu@volta.local', 'telefon' => '+373 68 100 104', 'functie' => 'Operator vânzări'],
            ['nume' => 'Ana Ciobanu', 'email' => 'ana.ciobanu@volta.local', 'telefon' => '+373 68 100 105', 'functie' => 'Operator vânzări'],
            ['nume' => 'Radu Lungu', 'email' => 'radu.lungu@volta.local', 'telefon' => '+373 68 100 106', 'functie' => 'Operator vânzări'],
        ];
        foreach ($operators as $index => $operator) {
            DB::table('operatori')->updateOrInsert(['nume' => $operator['nume']], array_merge($operator, [
                'data_angajare' => $now->subYears(1)->subMonths($index * 3)->toDateString(), 'adresa' => 'Chișinău, Republica Moldova', 'departament' => 'Call Center', 'observatii' => 'Profil demo pentru rapoarte.', 'activ' => true, 'created_at' => $now, 'updated_at' => $now,
            ]));
        }
        $operatorIds = DB::table('operatori')->whereIn('nume', array_column($operators, 'nume'))->pluck('id', 'nume');
        $mariaUserId = DB::table('users')->where('username', 'maria.popescu')->value('id');

        // 12 sincronizări 1C pentru dashboard, istoric și detalii operator.
        DB::table('onec_kpi_syncs')->where('company', 'Demo Volta')->delete();
        $months = collect(range(0, 11))->map(fn (int $i) => $now->startOfMonth()->subMonths($i))->reverse()->values();
        foreach ($months as $monthIndex => $month) {
            $rows = [];
            foreach ($operators as $index => $operator) {
                $faraTva = 92000 + $index * 11750 + $monthIndex * 3900 + (($monthIndex * 137 + $index * 89) % 6800);
                $rows[] = ['operator_id_1c' => sprintf('DEMO-OP-%03d', $index + 1), 'operator_nume' => $operator['nume'], 'vanzari_fara_tva' => $faraTva, 'vanzari_cu_tva' => round($faraTva * 1.2, 2), 'profit' => round($faraTva * (0.17 + ($index % 3) * 0.012), 2), 'nr_comenzi' => 46 + $index * 6 + (($monthIndex + $index * 2) % 12)];
            }
            $syncId = DB::table('onec_kpi_syncs')->insertGetId([
                'period_start' => $month->toDateString(), 'period_end' => $month->endOfMonth()->toDateString(), 'company' => 'Demo Volta', 'currency' => 'MDL', 'vanzari_fara_tva' => array_sum(array_column($rows, 'vanzari_fara_tva')), 'vanzari_cu_tva' => array_sum(array_column($rows, 'vanzari_cu_tva')), 'profit' => array_sum(array_column($rows, 'profit')), 'nr_comenzi' => array_sum(array_column($rows, 'nr_comenzi')), 'generated_at' => $month->endOfMonth()->setTime(18, 0), 'created_at' => $now, 'updated_at' => $now,
            ]);
            foreach ($rows as $row) DB::table('onec_kpi_operatori')->insert(array_merge($row, ['onec_kpi_sync_id' => $syncId, 'created_at' => $now, 'updated_at' => $now]));
        }

        $monthNames = ['Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie', 'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'];
        foreach ($months as $index => $month) DB::table('plan_vanzari')->updateOrInsert(['an' => $month->year, 'luna' => $monthNames[$month->month - 1]], ['valoare' => 760000 + $index * 22000]);

        // Trafic zilnic și totalul folosit de KPI.
        DB::table('traffic_sources')->where('source', 'like', 'demo:%')->delete();
        foreach (range(0, 89) as $daysAgo) {
            $date = $now->subDays($daysAgo)->toDateString(); $total = 0;
            foreach (['Organic', 'Google Ads', 'Facebook', 'Direct'] as $index => $source) {
                $visits = 75 + $index * 19 + (($daysAgo * 7 + $index * 11) % 42); $total += $visits;
                DB::table('traffic_sources')->insert(['source' => 'demo:' . $source, 'date' => $date, 'visits' => $visits, 'new_users' => (int) round($visits * .63), 'returning_users' => (int) round($visits * .37), 'created_at' => $now, 'updated_at' => $now]);
            }
            DB::table('traffic_sources')->updateOrInsert(['source' => 'total', 'date' => $date], ['visits' => $total, 'new_users' => (int) round($total * .63), 'returning_users' => (int) round($total * .37), 'created_at' => $now, 'updated_at' => $now]);
        }

        foreach ($operators as $index => $operator) foreach (range(0, 5) as $monthsAgo) {
            $date = $now->startOfMonth()->subMonths($monthsAgo)->addDays(4)->toDateString();
            DB::table('date_op')->updateOrInsert(['operator_id' => $operatorIds[$operator['nume']], 'data' => $date], ['suma_fara_tva' => 90000 + $index * 10000 + $monthsAgo * 3200, 'suma_cu_tva' => 108000 + $index * 12000 + $monthsAgo * 3840, 'profit' => 16000 + $index * 1700, 'nr_vanzari' => 50 + $index * 5]);
            DB::table('raport_lunar_call_center_inputs')->updateOrInsert(['ym' => substr($date, 0, 7), 'operator_nume' => $operator['nume']], ['chaturi' => 115 + $index * 17 + $monthsAgo * 3, 'apeluri' => 86 + $index * 12 + $monthsAgo * 2, 'created_at' => $now, 'updated_at' => $now]);
        }

        DB::table('oferte')->where('observatii', 'like', 'Demo Volta:%')->delete();
        foreach (range(1, 18) as $index) {
            $operator = $operators[$index % count($operators)]; $sentAt = $now->subDays($index * 3); $status = ['trimise', 'finalizate', 'finalizate', 'refuzate'][$index % 4];
            DB::table('oferte')->insert(['operator_id' => $operatorIds[$operator['nume']], 'operator' => $operator['nume'], 'status' => $status, 'data_trimisa' => $sentAt->toDateString(), 'data_finalizata' => $status === 'trimise' ? null : $sentAt->addDays(2)->toDateString(), 'valoare' => 4200 + $index * 875, 'observatii' => 'Demo Volta: ofertă comercială.', 'created_at' => $now, 'updated_at' => $now]);
        }
        foreach (range(1, 16) as $index) {
            $deliveryDate = $now->subDays($index * 2);
            DB::table('livrari')->updateOrInsert(['numar_comanda' => 'DEMO-' . str_pad((string) $index, 4, '0', STR_PAD_LEFT)], ['data' => $deliveryDate->subDay()->toDateString(), 'adresa_livrarii' => 'Str. Demo ' . $index . ', ap. ' . ($index + 10), 'localitate' => $index % 3 === 0 ? 'Orhei' : 'Chișinău', 'raion' => $index % 3 === 0 ? 'Orhei' : 'Municipiul Chișinău', 'nr_client' => '+373 60 200 ' . str_pad((string) $index, 3, '0', STR_PAD_LEFT), 'data_livrarii' => $deliveryDate->toDateString(), 'in_chisinau' => $index % 3 !== 0, 'user_id' => $mariaUserId, 'created_at' => $now, 'updated_at' => $now]);
        }

        $this->seedMobileData($now);
    }

    private function seedMobileData(CarbonImmutable $now): void
    {
        DB::table('mobile_analytics_events')->where('session_id', 'like', 'demo-%')->delete();
        DB::table('mobile_crashes')->where('session_id', 'like', 'demo-%')->delete();
        DB::table('mobile_feedback_reports')->where('session_id', 'like', 'demo-%')->delete();
        $pages = ['/acasa', '/catalog', '/produs/invertor-volta', '/cos', '/checkout'];
        foreach (range(1, 45) as $session) {
            $at = $now->subHours($session * 5); $base = ['session_id' => 'demo-session-' . $session, 'mobile_user_id' => 'demo-user-' . (($session % 18) + 1), 'device_id' => 'demo-device-' . (($session % 22) + 1), 'platform' => $session % 3 === 0 ? 'iOS' : 'Android', 'app_version' => '2.4.0', 'ip_address' => '127.0.0.1', 'user_agent' => 'Volta Demo App', 'created_at' => $now, 'updated_at' => $now];
            foreach ($pages as $pageIndex => $page) DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'page_view', 'page' => $page, 'previous_page' => $pageIndex ? $pages[$pageIndex - 1] : null, 'duration_ms' => 18000 + $pageIndex * 7000, 'metadata' => json_encode(['demo' => true]), 'occurred_at' => $at->addMinutes($pageIndex * 2)]));
            DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'product_view', 'page' => '/produs/invertor-volta', 'items_count' => 1, 'metadata' => json_encode(['product_name' => 'Invertor Volta 5kW']), 'occurred_at' => $at->addMinutes(4)]));
            if ($session % 2 === 0) DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'add_to_cart', 'page' => '/cos', 'cart_total' => 12499, 'items_count' => 1, 'occurred_at' => $at->addMinutes(7)]));
            if ($session % 3 === 0) DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'banner_click', 'page' => '/acasa', 'banner_id' => 'demo-autumn', 'banner_title' => 'Oferta sezonului', 'occurred_at' => $at->addMinute()]));
            if ($session % 5 === 0) DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'order_completed', 'page' => '/checkout', 'order_id' => 'MDEMO-' . $session, 'cart_total' => 12499, 'items_count' => 1, 'occurred_at' => $at->addMinutes(10)]));
            if ($session % 7 === 0) DB::table('mobile_analytics_events')->insert(array_merge($base, ['event_name' => 'cart_abandoned', 'page' => '/cos', 'checkout_step' => 2, 'cart_total' => 8990, 'items_count' => 2, 'occurred_at' => $at->addMinutes(9)]));
        }
        foreach (range(1, 9) as $index) DB::table('mobile_crashes')->insert(['fingerprint' => 'demo-crash-' . ($index % 3), 'error_type' => $index % 2 ? 'NetworkError' : 'TypeError', 'error_message' => $index % 2 ? 'Conexiunea a expirat.' : 'Ecranul produsului nu a putut fi încărcat.', 'stack_trace' => 'Demo stack trace #' . $index, 'is_fatal' => $index % 4 === 0, 'screen' => $index % 2 ? '/checkout' : '/catalog', 'session_id' => 'demo-crash-session-' . $index, 'mobile_user_id' => 'demo-user-' . $index, 'device_id' => 'demo-device-' . $index, 'platform' => $index % 3 === 0 ? 'iOS' : 'Android', 'app_version' => '2.4.0', 'os_version' => '17.5', 'device_model' => 'Demo Phone', 'metadata' => json_encode(['demo' => true]), 'occurred_at' => $now->subDays($index), 'created_at' => $now, 'updated_at' => $now]);
        foreach (range(1, 5) as $index) DB::table('mobile_feedback_reports')->insert(['message' => 'Demo: ar fi util să pot filtra mai rapid produsele după putere.', 'reporter_name' => 'Client demo ' . $index, 'reporter_email' => 'client' . $index . '@example.test', 'has_screenshot' => $index % 2 === 0, 'status' => ['new', 'in_review', 'resolved'][$index % 3], 'session_id' => 'demo-feedback-' . $index, 'mobile_user_id' => 'demo-user-' . $index, 'device_id' => 'demo-device-' . $index, 'platform' => 'Android', 'app_version' => '2.4.0', 'metadata' => json_encode(['demo' => true]), 'occurred_at' => $now->subDays($index * 2), 'created_at' => $now, 'updated_at' => $now]);
    }
}
