<?php

namespace App\Services;

use App\Models\OnecKpiSync;
use App\Models\Operator;
use App\Models\PlanVanzari;
use App\Models\RaportLunarCallCenterInput;
use App\Support\DbDate;
use App\Support\LunaRomana;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Raport lunar tip „call center”: 1C + plan + input manual chaturi/apeluri (admin).
 *
 * Procente (ca în Excel-ul analizat):
 * - Pondere chaturi: chaturi operator / SUM(chaturi toți operatorii)
 * - Pondere apeluri: apeluri operator / SUM(apeluri toți)
 * - Aport activitate (coloana „Aport in %” din TOTAL): (chaturi+apeluri operator) / (SUM chaturi + SUM apeluri)
 * - Aport vânzări: vânzări fără TVA operator / SUM(vânzări fără TVA) pe operatorii afișați (activi)
 *
 * Se afișează doar operatorii din KPI 1C care există în tabelul operatori cu activ = true (nume normalizat).
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
     * Numele exacte (din 1C) permise pentru o lună — operatori activi prezenți în KPI.
     *
     * @return list<string>
     */
    public function allowedOperatorDisplayNames(string $ym): array
    {
        $ym = $this->normalizeYm($ym);
        $sync = $this->loadSync($ym);
        $filtered = $this->filterActiveKpiOperatori($sync);

        return $filtered->map(fn ($op) => trim((string) ($op->operator_nume ?? '')))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<array{operator_nume: string, chaturi: int, apeluri: int}>  $rows
     */
    public function saveOperatorInputs(string $ym, array $rows): void
    {
        $ym = $this->normalizeYm($ym);
        $allowed = array_flip($this->allowedOperatorDisplayNames($ym));

        foreach ($rows as $i => $row) {
            $name = trim((string) ($row['operator_nume'] ?? ''));
            if ($name === '' || ! isset($allowed[$name])) {
                throw ValidationException::withMessages([
                    "rows.$i.operator_nume" => 'Operator necunoscut sau inactiv pentru această lună: '.$name,
                ]);
            }
            $ch = max(0, min(99999999, (int) ($row['chaturi'] ?? 0)));
            $ap = max(0, min(99999999, (int) ($row['apeluri'] ?? 0)));

            RaportLunarCallCenterInput::query()->updateOrCreate(
                ['ym' => $ym, 'operator_nume' => $name],
                ['chaturi' => $ch, 'apeluri' => $ap]
            );
        }
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
     *   operator_rows: list<array<string, mixed>>,
     *   footer_total: array{vanzari_fara_tva: string, plan_lunar: string, chaturi: string, apeluri: string},
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

        $sync = $this->loadSync($ym);
        $operatori = $this->filterActiveKpiOperatori($sync);

        $inputsByName = collect();
        try {
            $inputsByName = RaportLunarCallCenterInput::query()
                ->where('ym', $ym)
                ->get()
                ->keyBy(fn (RaportLunarCallCenterInput $r) => trim($r->operator_nume));
        } catch (\Throwable) {
        }

        $rows = [];
        foreach ($operatori as $op) {
            $np = trim((string) ($op->operator_nume ?? '')) ?: '—';
            $input = $inputsByName->get($np);
            $ch = $input ? (int) $input->chaturi : 0;
            $ap = $input ? (int) $input->apeluri : 0;
            $rows[] = [
                'np' => $np,
                'chaturi_int' => $ch,
                'apeluri_int' => $ap,
                'chaturi' => (string) $ch,
                'apeluri' => (string) $ap,
                'aport_activitate' => '',
                /** Raport 0–1 pentru export Excel (coloane % ca valori 0–100). */
                'aport_activitate_value' => null,
                'vanzari_fara_tva' => $this->fmtNum((float) $op->vanzari_fara_tva),
                'aport_vanzari' => '',
                'aport_vanzari_value' => null,
                'plan_individual' => '',
                'indeplinire_plan' => '',
                'pondere_chaturi' => '',
                'pondere_chaturi_value' => null,
                'pondere_apeluri' => '',
                'pondere_apeluri_value' => null,
            ];
        }

        $sumCh = (int) array_sum(array_column($rows, 'chaturi_int'));
        $sumAp = (int) array_sum(array_column($rows, 'apeluri_int'));
        $sumAct = $sumCh + $sumAp;

        $totalFtva = (float) collect($rows)->sum(function ($r) {
            return (float) str_replace(',', '', (string) $r['vanzari_fara_tva']);
        });

        foreach ($rows as $i => $r) {
            $ch = $r['chaturi_int'];
            $ap = $r['apeluri_int'];
            $v = (float) str_replace(',', '', (string) $r['vanzari_fara_tva']);

            if ($sumAct > 0) {
                $frac = ($ch + $ap) / $sumAct;
                $rows[$i]['aport_activitate_value'] = round($frac, 14);
                $rows[$i]['aport_activitate'] = $this->fmtPct($frac);
            }
            if ($totalFtva > 0) {
                $fracV = $v / $totalFtva;
                $rows[$i]['aport_vanzari_value'] = round($fracV, 14);
                $rows[$i]['aport_vanzari'] = $this->fmtPct($fracV);
            }
            if ($sumCh > 0) {
                $fracCh = $ch / $sumCh;
                $rows[$i]['pondere_chaturi_value'] = round($fracCh, 14);
                $rows[$i]['pondere_chaturi'] = $this->fmtPct($fracCh);
            }
            if ($sumAp > 0) {
                $fracAp = $ap / $sumAp;
                $rows[$i]['pondere_apeluri_value'] = round($fracAp, 14);
                $rows[$i]['pondere_apeluri'] = $this->fmtPct($fracAp);
            }
        }

        $syncFtva = $sync ? (float) $sync->vanzari_fara_tva : null;
        $syncCtva = $sync ? (float) $sync->vanzari_cu_tva : null;
        $syncProfit = $sync ? (float) $sync->profit : null;

        $sumDisplay = $totalFtva > 0 ? $this->fmtNum($totalFtva) : ($syncFtva !== null ? $this->fmtNum($syncFtva) : '');

        $footerTotal = [
            'vanzari_fara_tva' => $sumDisplay,
            'plan_lunar' => $planLunar !== null ? $this->fmtNum($planLunar) : '',
            'chaturi' => (string) $sumCh,
            'apeluri' => (string) $sumAp,
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
            $rows,
            $footerTotal,
            $vanzariRows,
            $sync !== null
        );

        foreach ($rows as $i => $_) {
            unset(
                $rows[$i]['aport_activitate_value'],
                $rows[$i]['aport_vanzari_value'],
                $rows[$i]['pondere_chaturi_value'],
                $rows[$i]['pondere_apeluri_value'],
            );
        }

        return [
            'ym' => $ym,
            'luna_label' => LunaRomana::labelFromYm($ym),
            'plan_lunar' => $planLunar,
            'has_sync' => $sync !== null,
            'sync_vanzari_fara_tva' => $syncFtva,
            'sync_vanzari_cu_tva' => $syncCtva,
            'sync_profit' => $syncProfit,
            'operator_rows' => $rows,
            'footer_total' => $footerTotal,
            'vanzari_rows' => $vanzariRows,
            'excel_sheets' => $excelSheets,
        ];
    }

    private function loadSync(?string $ym): ?OnecKpiSync
    {
        if ($ym === null) {
            return null;
        }
        try {
            return OnecKpiSync::query()
                ->whereRaw(DbDate::month('period_start').' = ?', [$ym])
                ->orderByDesc('created_at')
                ->with(['operatori' => fn ($q) => $q->orderBy('operator_nume')])
                ->first();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return Collection<int, \App\Models\OnecKpiOperator>
     */
    private function filterActiveKpiOperatori(?OnecKpiSync $sync): Collection
    {
        if (! $sync) {
            return collect();
        }

        $activeKeys = $this->activeOperatorNameKeys();

        return $sync->operatori->filter(function ($op) use ($activeKeys) {
            $key = $this->normalizeOpName((string) ($op->operator_nume ?? ''));

            return $key !== '' && isset($activeKeys[$key]);
        })->values();
    }

    /**
     * @return array<string, true> chei normalizate pentru operatori activi
     */
    private function activeOperatorNameKeys(): array
    {
        try {
            $names = Operator::query()
                ->where('activ', true)
                ->pluck('nume');
        } catch (\Throwable) {
            return [];
        }

        $keys = [];
        foreach ($names as $n) {
            $k = $this->normalizeOpName((string) $n);
            if ($k !== '') {
                $keys[$k] = true;
            }
        }

        return $keys;
    }

    private function normalizeOpName(string $name): string
    {
        $t = trim(preg_replace('/\s+/u', ' ', $name));

        return mb_strtolower($t, 'UTF-8');
    }

    private function fmtNum(float $n): string
    {
        if (abs($n - round($n)) < 0.0005) {
            return (string) (int) round($n);
        }

        return number_format($n, 2, '.', '');
    }

    /** Raport 0–1 afișat ca procent cu virgulă zecimală (ex. 12,34 %). */
    private function fmtPct(float $ratio, int $decimals = 2): string
    {
        return number_format($ratio * 100, $decimals, ',', ' ').' %';
    }

    /**
     * @param  list<array<string, mixed>>  $operatorRows
     * @param  array{vanzari_fara_tva: string, plan_lunar: string, chaturi: string, apeluri: string}  $footerTotal
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
                'NP', 'Chaturi', 'Apeluri', 'Aport activitate (%)', 'Vanzari (fara TVA, lei)', 'Aport vanzari (%)',
                'Plan individual ', 'Indeplinire plan (%)',
            ],
        ];

        foreach ($operatorRows as $r) {
            $totalAoa[] = [
                $r['np'],
                (int) $r['chaturi_int'],
                (int) $r['apeluri_int'],
                isset($r['aport_activitate_value']) && $r['aport_activitate_value'] !== null
                    ? round((float) $r['aport_activitate_value'] * 100, 4)
                    : null,
                $r['vanzari_fara_tva'] === '' ? null : (float) str_replace(',', '', (string) $r['vanzari_fara_tva']),
                isset($r['aport_vanzari_value']) && $r['aport_vanzari_value'] !== null
                    ? round((float) $r['aport_vanzari_value'] * 100, 4)
                    : null,
                $r['plan_individual'] === '' ? null : $r['plan_individual'],
                $r['indeplinire_plan'] === '' ? null : $r['indeplinire_plan'],
            ];
        }

        $totalAoa[] = ['', '', '', '', '', '', '', ''];
        $ftvaFooter = $footerTotal['vanzari_fara_tva'] !== '' ? (float) str_replace(',', '', $footerTotal['vanzari_fara_tva']) : null;
        $planFooter = $footerTotal['plan_lunar'] !== '' ? (float) str_replace(',', '', $footerTotal['plan_lunar']) : null;
        $indeplFooter = ($ftvaFooter !== null && $planFooter !== null && $planFooter > 0)
            ? round($ftvaFooter / $planFooter * 100, 4)
            : null;
        $sumCh = isset($footerTotal['chaturi']) ? (int) $footerTotal['chaturi'] : 0;
        $sumAp = isset($footerTotal['apeluri']) ? (int) $footerTotal['apeluri'] : 0;
        $totalAoa[] = [
            'TOTAL',
            $sumCh > 0 ? $sumCh : null,
            $sumAp > 0 ? $sumAp : null,
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
            $pond = isset($r['pondere_chaturi_value']) && $r['pondere_chaturi_value'] !== null
                ? round((float) $r['pondere_chaturi_value'] * 100, 4)
                : null;
            $chaturiAoa[] = [
                $r['np'],
                (int) $r['chaturi_int'],
                $pond,
            ];
        }
        $chaturiAoa[] = ['', '', ''];
        $chaturiAoa[] = ['TOTAL', $sumCh > 0 ? $sumCh : null, null];

        $apeluriAoa = [
            ['APELURI', '', ''],
            ['', '', ''],
            ['', '', ''],
        ];
        foreach ($operatorRows as $r) {
            $pondA = isset($r['pondere_apeluri_value']) && $r['pondere_apeluri_value'] !== null
                ? round((float) $r['pondere_apeluri_value'] * 100, 4)
                : null;
            $apeluriAoa[] = [
                $r['np'],
                (int) $r['apeluri_int'],
                $pondA,
            ];
        }
        $apeluriAoa[] = ['', '', ''];
        $apeluriAoa[] = ['TOTAL', $sumAp > 0 ? $sumAp : null, null];

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
