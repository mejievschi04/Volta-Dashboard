<?php

namespace App\Services;

use App\Models\OnecKpiSync;
use App\Models\PlanVanzari;
use App\Support\DbDate;
use App\Support\LunaRomana;
/**
 * Agregă date disponibile în dashboard pentru un raport lunar tip „call center”
 * (structură similară cu exportul Excel manual: TOTAL, Chaturi, Apeluri, Vânzări).
 * Coloane fără sursă în DB (chat, apeluri, plan per operator) rămân goale.
 */
final class CallCenterRaportLunarService
{
    /** @var array<int, string> */
    private const LUNI = [
        1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
        5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
        9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie',
    ];

    public function normalizeYm(string $ym): string
    {
        $ym = trim($ym);
        if (preg_match('/^(\d{4})-(\d{1,2})$/', $ym, $m)) {
            return sprintf('%04d-%02d', (int) $m[1], (int) $m[2]);
        }

        return date('Y-m');
    }

    /**
     * @return array{
     *   ym: string,
     *   luna_label: string,
     *   plan_lunar: ?float,
     *   has_sync: bool,
     *   sync_vanzari_fara_tva: ?float,
     *   sync_vanzari_cu_tva: ?float,
     *   sync_profit: ?float,
     *   operator_rows: list<array{
     *     np: string,
     *     chaturi: string,
     *     apeluri: string,
     *     aport_activitate: string,
     *     vanzari_fara_tva: string,
     *     aport_vanzari: string,
     *     plan_individual: string,
     *     indeplinire_plan: string,
     *   }>,
     *   footer_total: array{
     *     vanzari_fara_tva: string,
     *     plan_lunar: string,
     *   },
     *   vanzari_rows: list<array{manager: string, fara_tva: string, cu_tva: string, profit: string}>,
     *   excel_sheets: list<array{name: string, aoa: list<list<mixed>>}>
     * }
     */
    public function build(string $ym): array
    {
        $ym = $this->normalizeYm($ym);
        $parts = explode('-', $ym);
        $an = (int) ($parts[0] ?? date('Y'));
        $lunaNum = (int) ($parts[1] ?? (int) date('n'));
        $lunaNume = self::LUNI[$lunaNum] ?? 'Ianuarie';

        $planRow = null;
        try {
            $planRow = PlanVanzari::where('an', $an)->where('luna', $lunaNume)->first();
        } catch (\Throwable) {
        }
        $planLunar = $planRow ? (float) $planRow->valoare : null;

        $sync = null;
        try {
            $sync = OnecKpiSync::query()
                ->whereRaw(DbDate::month('period_start').' = ?', [$ym])
                ->orderByDesc('created_at')
                ->with(['operatori' => fn ($q) => $q->orderBy('operator_nume')])
                ->first();
        } catch (\Throwable) {
        }

        $operatori = $sync ? $sync->operatori : collect();
        $totalFtva = (float) $operatori->sum(fn ($o) => (float) $o->vanzari_fara_tva);

        $operatorRows = [];
        foreach ($operatori as $op) {
            $v = (float) $op->vanzari_fara_tva;
            $aportV = $totalFtva > 0 ? round($v / $totalFtva, 6) : null;

            $operatorRows[] = [
                'np' => trim((string) ($op->operator_nume ?? '')) ?: '—',
                'chaturi' => '',
                'apeluri' => '',
                'aport_activitate' => '',
                'vanzari_fara_tva' => $this->fmtNum($v),
                'aport_vanzari' => $aportV !== null ? (string) $aportV : '',
                'plan_individual' => '',
                'indeplinire_plan' => '',
            ];
        }

        $syncFtva = $sync ? (float) $sync->vanzari_fara_tva : null;
        $syncCtva = $sync ? (float) $sync->vanzari_cu_tva : null;
        $syncProfit = $sync ? (float) $sync->profit : null;

        $sumDisplay = $totalFtva > 0 ? $this->fmtNum($totalFtva) : ($syncFtva !== null ? $this->fmtNum($syncFtva) : '');

        $footerTotal = [
            'vanzari_fara_tva' => $sumDisplay,
            'plan_lunar' => $planLunar !== null ? $this->fmtNum($planLunar) : '',
        ];

        $vanzariRows = [];
        if ($sync) {
            $vanzariRows[] = [
                'manager' => 'CALL-CENTER',
                'fara_tva' => $this->fmtNum((float) $sync->vanzari_fara_tva),
                'cu_tva' => $this->fmtNum((float) $sync->vanzari_cu_tva),
                'profit' => $this->fmtNum((float) $sync->profit),
            ];
        }
        foreach ($operatori as $op) {
            $vanzariRows[] = [
                'manager' => trim((string) ($op->operator_nume ?? '')) ?: '—',
                'fara_tva' => $this->fmtNum((float) $op->vanzari_fara_tva),
                'cu_tva' => $this->fmtNum((float) $op->vanzari_cu_tva),
                'profit' => $this->fmtNum((float) $op->profit),
            ];
        }

        $excelSheets = $this->buildExcelSheets(
            LunaRomana::labelFromYm($ym),
            $planLunar,
            $operatorRows,
            $footerTotal,
            $vanzariRows,
            $sync !== null
        );

        return [
            'ym' => $ym,
            'luna_label' => LunaRomana::labelFromYm($ym),
            'plan_lunar' => $planLunar,
            'has_sync' => $sync !== null,
            'sync_vanzari_fara_tva' => $syncFtva,
            'sync_vanzari_cu_tva' => $syncCtva,
            'sync_profit' => $syncProfit,
            'operator_rows' => $operatorRows,
            'footer_total' => $footerTotal,
            'vanzari_rows' => $vanzariRows,
            'excel_sheets' => $excelSheets,
        ];
    }

    private function fmtNum(float $n): string
    {
        if (abs($n - round($n)) < 0.0005) {
            return (string) (int) round($n);
        }

        return number_format($n, 2, '.', '');
    }

    /**
     * @param  list<array<string, string>>  $operatorRows
     * @param  array{vanzari_fara_tva: string, plan_lunar: string}  $footerTotal
     * @param  list<array{manager: string, fara_tva: string, cu_tva: string, profit: string}>  $vanzariRows
     * @return list<array{name: string, aoa: list<list<mixed>>}>
     */
    private function buildExcelSheets(
        string $lunaLabel,
        ?float $planLunar,
        array $operatorRows,
        array $footerTotal,
        array $vanzariRows,
        bool $hasSync
    ): array {
        $planCell = $planLunar !== null ? $planLunar : '';

        $totalAoa = [
            ['CALL CENTRU', '', '', '', '', '', '', ''],
            [$lunaLabel, '', 'Plan lunar', $planCell, '', '', '', ''],
            ['', '', '', '', '', '', '', ''],
            [
                'NP', 'Chaturi', 'Apeluri', 'Aport in %', 'Vanzari (fara TVA, lei)', 'Aport in %',
                'Plan individual ', 'Indeplinirea plan',
            ],
        ];

        foreach ($operatorRows as $r) {
            $totalAoa[] = [
                $r['np'],
                $r['chaturi'] === '' ? null : $r['chaturi'],
                $r['apeluri'] === '' ? null : $r['apeluri'],
                $r['aport_activitate'] === '' ? null : $r['aport_activitate'],
                $r['vanzari_fara_tva'] === '' ? null : (float) str_replace(',', '', $r['vanzari_fara_tva']),
                $r['aport_vanzari'] === '' ? null : (float) $r['aport_vanzari'],
                $r['plan_individual'] === '' ? null : $r['plan_individual'],
                $r['indeplinire_plan'] === '' ? null : $r['indeplinire_plan'],
            ];
        }

        $totalAoa[] = ['', '', '', '', '', '', '', ''];
        $ftvaFooter = $footerTotal['vanzari_fara_tva'] !== '' ? (float) str_replace(',', '', $footerTotal['vanzari_fara_tva']) : null;
        $planFooter = $footerTotal['plan_lunar'] !== '' ? (float) str_replace(',', '', $footerTotal['plan_lunar']) : null;
        $indeplFooter = ($ftvaFooter !== null && $planFooter !== null && $planFooter > 0)
            ? round($ftvaFooter / $planFooter, 8)
            : null;
        $totalAoa[] = [
            'TOTAL',
            null,
            null,
            null,
            $ftvaFooter,
            null,
            $planFooter,
            $indeplFooter,
        ];

        $chaturiAoa = [
            ['CHATURI', '', ''],
            ['', '', ''],
        ];
        foreach ($operatorRows as $r) {
            $chaturiAoa[] = [$r['np'], '', ''];
        }
        $chaturiAoa[] = ['', '', ''];
        $chaturiAoa[] = ['TOTAL', '', ''];

        $apeluriAoa = [
            ['APELURI', '', ''],
            ['', '', ''],
            ['', '', ''],
        ];
        foreach ($operatorRows as $r) {
            $apeluriAoa[] = [$r['np'], '', ''];
        }
        $apeluriAoa[] = ['', '', ''];
        $apeluriAoa[] = ['TOTAL', '', ''];

        $vanzariAoa = [
            ['', '', '', '', ''],
            ['Vânzări', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['', '', '', '', ''],
            ['Manager', 'Vinzari F/TVA', 'Vinzari CU TVA', 'Profit Brut', ''],
        ];
        foreach ($vanzariRows as $vr) {
            $fa = $vr['fara_tva'] !== '' ? (float) str_replace(',', '', $vr['fara_tva']) : null;
            $cu = $vr['cu_tva'] !== '' ? (float) str_replace(',', '', $vr['cu_tva']) : null;
            $pr = $vr['profit'] !== '' ? (float) str_replace(',', '', $vr['profit']) : null;
            $vanzariAoa[] = [$vr['manager'], $fa, $cu, $pr, ''];
        }

        if (! $hasSync && $operatorRows === []) {
            $vanzariAoa[] = ['(fără date 1C pentru luna selectată)', '', '', '', ''];
        }

        return [
            ['name' => 'TOTAL', 'aoa' => $totalAoa],
            ['name' => 'Chaturi', 'aoa' => $chaturiAoa],
            ['name' => 'Apeluri', 'aoa' => $apeluriAoa],
            ['name' => 'Vanzari', 'aoa' => $vanzariAoa],
        ];
    }
}
