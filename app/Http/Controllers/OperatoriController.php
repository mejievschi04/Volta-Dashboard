<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Oferte;
use App\Models\Operator;
use App\Models\DateOp;
use App\Models\Livrare;
use App\Models\OnecKpiOperator;
use App\Models\User;
use App\Support\DbDate;
use App\Support\LunaRomana;
use Illuminate\Support\Facades\DB;

class OperatoriController extends Controller
{
    public function index(Request $request)
    {
        // Perioadă: an + lună opțională (dacă lună = null = toate lunile din an)
        $an = $request->input('an', date('Y'));
        $luna = $request->input('luna'); // 1-12 sau null
        $an = max(2023, min((int) $an, (int) date('Y')));
        $luna = $luna === '' || $luna === null ? null : (int) $luna;
        if ($luna !== null) {
            $luna = max(1, min(12, $luna));
        }

        $periodStart = $luna
            ? sprintf('%04d-%02d-01', $an, $luna)
            : sprintf('%04d-01-01', $an);
        $periodEnd = $luna
            ? date('Y-m-t', strtotime($periodStart))
            : sprintf('%04d-12-31', $an);

        $operatori1c = [];
        $chartData1c = [];
        $dezactivatedNume = Operator::where('activ', false)
            ->get()
            ->map(fn ($o) => mb_strtolower(trim((string) ($o->nume ?? ''))))
            ->filter()
            ->values()
            ->toArray();

        try {
            $query = OnecKpiOperator::query()
                ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                ->where('onec_kpi_syncs.period_start', '>=', $periodStart)
                ->where('onec_kpi_syncs.period_start', '<=', $periodEnd);

                $rows = (clone $query)
                    ->selectRaw('
                    onec_kpi_operatori.operator_nume as nume,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as total_vanzari_fara_tva,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as total_vanzari_cu_tva,
                    COALESCE(SUM(onec_kpi_operatori.profit), 0) as total_profit,
                    COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as total_comenzi
                ')
                ->groupBy('onec_kpi_operatori.operator_nume')
                ->orderByDesc('total_vanzari_fara_tva')
                ->get();

            foreach ($rows as $row) {
                $nume = trim((string) ($row->nume ?? '')) ?: 'Fără nume';
                if (in_array(mb_strtolower($nume), $dezactivatedNume, true)) {
                    continue;
                }
                $vanzari = (float) $row->total_vanzari_fara_tva;
                $nrComenzi = (int) $row->total_comenzi;
                $operatorRecord = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower($nume)])->first();
                $operatori1c[] = [
                    'nume' => $nume,
                    'vanzari_fara_tva' => $vanzari,
                    'vanzari_cu_tva' => (float) $row->total_vanzari_cu_tva,
                    'profit' => (float) $row->total_profit,
                    'nr_comenzi' => $nrComenzi,
                    'cec_mediu' => $nrComenzi > 0 ? round($vanzari / $nrComenzi, 2) : 0,
                    'operator_id' => $operatorRecord?->id,
                ];
                if ($vanzari > 0) {
                    $chartData1c[] = ['nume' => $nume, 'vanzari_fara_tva' => $vanzari, 'procent' => 0];
                }
            }
            $total1c = array_sum(array_column($chartData1c, 'vanzari_fara_tva'));
            if ($total1c > 0) {
                foreach ($chartData1c as &$d) {
                    $d['procent'] = round(($d['vanzari_fara_tva'] / $total1c) * 100, 2);
                }
                unset($d);
            }
        } catch (\Throwable $e) {
            // Tabel onec_kpi_operatori poate să nu existe
        }

        $operatoriDezactivati = Operator::where('activ', false)->orderBy('nume')->get();

        $luniNume = [
            1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
            5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
            9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie',
        ];
        $perioadaLabel = $luna
            ? $luniNume[$luna] . ' ' . $an
            : 'Anul ' . $an;

        return view('operatori.index', compact('operatori1c', 'chartData1c', 'operatoriDezactivati', 'an', 'luna', 'perioadaLabel', 'luniNume'));
    }

    /**
     * Raport detaliat 1C pentru un operator după nume (admin/vizualizare).
     * Dacă există Operator cu acest nume, redirect la show(id). Altfel afișează doar datele 1C.
     */
    public function raportByNume(string $nume)
    {
        $nume = trim($nume);
        if ($nume === '') {
            return redirect()->route('operatori')->with('error', 'Nume invalid.');
        }

        $operator = Operator::whereRaw('TRIM(nume) = ?', [$nume])->first();
        if ($operator) {
            return redirect()->route('operatori.show', $operator->id);
        }

        $date = null;
        $vanzariLunare1c = collect();
        try {
            $row = OnecKpiOperator::query()
                ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                ->whereRaw('TRIM(onec_kpi_operatori.operator_nume) = ?', [$nume])
                ->selectRaw('
                    onec_kpi_operatori.operator_nume as nume,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as total_vanzari_fara_tva,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as total_vanzari_cu_tva,
                    COALESCE(SUM(onec_kpi_operatori.profit), 0) as total_profit,
                    COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as total_comenzi
                ')
                ->groupBy('onec_kpi_operatori.operator_nume')
                ->first();
            if ($row) {
                $date = [
                    'nume' => trim((string) ($row->nume ?? '')) ?: $nume,
                    'vanzari_fara_tva' => (float) $row->total_vanzari_fara_tva,
                    'vanzari_cu_tva' => (float) $row->total_vanzari_cu_tva,
                    'profit' => (float) $row->total_profit,
                    'nr_comenzi' => (int) $row->total_comenzi,
                ];
            }
                $lunareRows = OnecKpiOperator::query()
                    ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                    ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                    ->whereRaw('TRIM(onec_kpi_operatori.operator_nume) = ?', [$nume])
                    ->selectRaw('
                        ' . DbDate::month('onec_kpi_syncs.period_start') . ' as luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as vanzari_luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as comenzi
                    ')
                    ->groupBy(DB::raw(DbDate::month('onec_kpi_syncs.period_start')))
                    ->orderBy('luna', 'desc')
                    ->get();
            foreach ($lunareRows as $r) {
                $vanzariLunare1c->push((object) [
                    'luna' => $r->luna,
                    'luna_label' => LunaRomana::labelFromYm((string) $r->luna),
                    'vanzari_luna' => (float) $r->vanzari_luna,
                    'vanzari_cu_tva' => (float) $r->vanzari_cu_tva,
                    'profit' => (float) $r->profit,
                    'comenzi' => (int) $r->comenzi,
                    'nr_vanzari' => (int) $r->comenzi,
                    'cec_mediu' => (int) $r->comenzi > 0
                        ? round((float) $r->vanzari_luna / (int) $r->comenzi, 2)
                        : 0,
                ]);
            }
        } catch (\Throwable $e) {
        }

        $operatorRecord = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower(trim($nume))])->first();
        $canEditPhotos = $operatorRecord && $this->canEditOperatorPhotos($operatorRecord);

        return view('operatori.raport', [
            'operatorNume' => $nume,
            'date' => $date,
            'vanzariLunare1c' => $vanzariLunare1c,
            'operatorRecord' => $operatorRecord,
            'canEditPhotos' => $canEditPhotos,
        ]);
    }

    /**
     * Toggle activ pentru un operator după nume (doar admin). Dezactivare = nu mai apare în listă.
     */
    public function toggleActiv(Request $request)
    {
        $operatorId = $request->input('operator_id');
        $nume = trim((string) $request->input('nume', ''));
        if (!$operatorId && $nume === '') {
            return redirect()->route('operatori')->with('error', 'Nume invalid.');
        }

        $operator = null;
        if ($operatorId) {
            $operator = Operator::find($operatorId);
        }
        if (!$operator && $nume !== '') {
            $operator = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower($nume)])->first();
        }

        if ($operator) {
            $operator->activ = !$operator->activ;
            $operator->save();
            $message = $operator->activ ? 'Operatorul a fost reactivat.' : 'Operatorul a fost dezactivat și nu mai apare în listă.';
        } else {
            Operator::create([
                'nume' => $nume,
                'data_angajare' => now(),
                'activ' => false,
            ]);
            $message = 'Operatorul a fost dezactivat și nu mai apare în listă.';
        }

        return redirect()->route('operatori')->with('success', $message);
    }

    /**
     * Pagina „Datele mele” pentru utilizatorul cu rol Operator – vede doar datele sale din 1C.
     */
    public function me(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->isOperator()) {
            return redirect()->route('dashboard');
        }

        $operatorNume = trim((string) ($user->operator_nume ?? $user->name ?? $user->username ?? ''));
        $date = null;

        $vanzariLunare1c = collect();

        if ($operatorNume !== '') {
            try {
                $row = OnecKpiOperator::query()
                    ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                    ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                    ->whereRaw('TRIM(onec_kpi_operatori.operator_nume) = ?', [$operatorNume])
                    ->selectRaw('
                        onec_kpi_operatori.operator_nume as nume,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as total_vanzari_fara_tva,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as total_vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as total_profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as total_comenzi
                    ')
                    ->groupBy('onec_kpi_operatori.operator_nume')
                    ->first();
                if ($row) {
                    $date = [
                        'nume' => trim((string) ($row->nume ?? '')) ?: $operatorNume,
                        'vanzari_fara_tva' => (float) $row->total_vanzari_fara_tva,
                        'vanzari_cu_tva' => (float) $row->total_vanzari_cu_tva,
                        'profit' => (float) $row->total_profit,
                        'nr_comenzi' => (int) $row->total_comenzi,
                    ];
                }

                // Date pe luni (din 1C, per lună) pentru grafic și tabel
                $lunareRows = OnecKpiOperator::query()
                    ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                    ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                    ->whereRaw('TRIM(onec_kpi_operatori.operator_nume) = ?', [$operatorNume])
                    ->selectRaw('
                        ' . DbDate::month('onec_kpi_syncs.period_start') . ' as luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as vanzari_luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as comenzi
                    ')
                    ->groupBy(DB::raw(DbDate::month('onec_kpi_syncs.period_start')))
                    ->orderBy('luna', 'desc')
                    ->get();

                foreach ($lunareRows as $r) {
                    $vanzariLunare1c->push((object) [
                        'luna' => $r->luna,
                        'luna_label' => LunaRomana::labelFromYm((string) $r->luna),
                        'vanzari_luna' => (float) $r->vanzari_luna,
                        'vanzari_cu_tva' => (float) $r->vanzari_cu_tva,
                        'profit' => (float) $r->profit,
                        'comenzi' => (int) $r->comenzi,
                        'nr_vanzari' => (int) $r->comenzi,
                        'cec_mediu' => (int) $r->comenzi > 0
                            ? round((float) $r->vanzari_luna / (int) $r->comenzi, 2)
                            : 0,
                    ]);
                }
            } catch (\Throwable $e) {
            }
        }

        $fullName = trim((string) ($user->full_name ?? $user->name ?? ''));
        $operatorRecord = null;
        if ($fullName !== '') {
            $operatorRecord = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower($fullName)])->first();
            if (! $operatorRecord) {
                $operatorRecord = Operator::create(['nume' => $fullName, 'activ' => true]);
            }
        }
        $canEditPhotos = $operatorRecord !== null;

        $lunaCurenta = now()->format('Y-m');
        $nrLivrariTotal = Livrare::where('user_id', $user->id)->count();
        $nrLivrariLunaCurenta = Livrare::where('user_id', $user->id)
            ->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$lunaCurenta])
            ->count();

        $comenziTotal = $date ? (int) $date['nr_comenzi'] : 0;
        $comenziLunaCurenta = 0;
        if ($vanzariLunare1c->isNotEmpty()) {
            $lunaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
            $comenziLunaCurenta = $lunaData ? (int) $lunaData->comenzi : 0;
        }
        $pickupTotal = max(0, $comenziTotal - $nrLivrariTotal);
        $pickupLunaCurenta = max(0, $comenziLunaCurenta - $nrLivrariLunaCurenta);

        return view('operatori.me', [
            'operatorNume' => $operatorNume ?: $fullName ?: $user->username,
            'date' => $date,
            'vanzariLunare1c' => $vanzariLunare1c,
            'operatorRecord' => $operatorRecord,
            'canEditPhotos' => $canEditPhotos,
            'nrLivrariTotal' => $nrLivrariTotal,
            'nrLivrariLunaCurenta' => $nrLivrariLunaCurenta,
            'pickupTotal' => $pickupTotal,
            'pickupLunaCurenta' => $pickupLunaCurenta,
        ]);
    }

    public function show($id)
    {
        // Când parametrul e numeric = ID din URL (lista operatori). Altfel = nume (legătură din raport).
        if (is_numeric($id)) {
            $operator = Operator::findOrFail((int) $id);
        } else {
            $operator = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower(trim((string) $id))])->firstOrFail();
        }

        $operatorNume = trim((string) ($operator->nume ?? ''));
        $date1c = null;
        $vanzariLunare1c = collect();
        if ($operatorNume !== '') {
            try {
                $row1c = OnecKpiOperator::query()
                    ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                    ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                    ->whereRaw('LOWER(TRIM(onec_kpi_operatori.operator_nume)) = ?', [mb_strtolower($operatorNume)])
                    ->selectRaw('
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as total_vanzari_fara_tva,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as total_vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as total_profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as total_comenzi
                    ')
                    ->first();
                if ($row1c) {
                    $date1c = [
                        'vanzari_fara_tva' => (float) $row1c->total_vanzari_fara_tva,
                        'vanzari_cu_tva' => (float) $row1c->total_vanzari_cu_tva,
                        'profit' => (float) $row1c->total_profit,
                        'nr_comenzi' => (int) $row1c->total_comenzi,
                    ];
                }
                $lunare1c = OnecKpiOperator::query()
                    ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                    ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
                    ->whereRaw('LOWER(TRIM(onec_kpi_operatori.operator_nume)) = ?', [mb_strtolower($operatorNume)])
                    ->selectRaw('
                        ' . DbDate::month('onec_kpi_syncs.period_start') . ' as luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as vanzari_luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as comenzi
                    ')
                    ->groupBy(DB::raw(DbDate::month('onec_kpi_syncs.period_start')))
                    ->orderBy('luna', 'desc')
                    ->get();
                foreach ($lunare1c as $r) {
                    $vanzariLunare1c->push((object) [
                        'luna' => $r->luna,
                        'luna_label' => LunaRomana::labelFromYm((string) $r->luna),
                        'vanzari_luna' => (float) $r->vanzari_luna,
                        'vanzari_cu_tva' => (float) $r->vanzari_cu_tva,
                        'profit' => (float) $r->profit,
                        'comenzi' => (int) $r->comenzi,
                        'nr_vanzari' => (int) $r->comenzi,
                        'cec_mediu' => (int) $r->comenzi > 0
                            ? round((float) $r->vanzari_luna / (int) $r->comenzi, 2)
                            : 0,
                    ]);
                }
            } catch (\Throwable $e) {
                \Log::warning('Operatori show: 1C KPI query failed', ['operator_id' => $operator->id, 'error' => $e->getMessage()]);
            }
        }

        // Pentru pagina identică cu „Datele mele”: $date (ca la me), livrări și pick-up
        $date = null;
        if ($date1c !== null) {
            $date = [
                'nume' => trim((string) ($operator->nume ?? '')),
                'vanzari_fara_tva' => $date1c['vanzari_fara_tva'],
                'vanzari_cu_tva' => $date1c['vanzari_cu_tva'],
                'profit' => $date1c['profit'],
                'nr_comenzi' => $date1c['nr_comenzi'],
            ];
        }
        $operatorUser = User::whereRaw('LOWER(TRIM(full_name)) = ?', [mb_strtolower($operatorNume)])->first();
        $lunaCurenta = now()->format('Y-m');
        $nrLivrariTotal = $operatorUser ? Livrare::where('user_id', $operatorUser->id)->count() : 0;
        $nrLivrariLunaCurenta = $operatorUser
            ? Livrare::where('user_id', $operatorUser->id)->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$lunaCurenta])->count()
            : 0;
        $comenziTotal = $date ? (int) $date['nr_comenzi'] : 0;
        $comenziLunaCurenta = 0;
        if ($vanzariLunare1c->isNotEmpty()) {
            $lunaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
            $comenziLunaCurenta = $lunaData ? (int) $lunaData->comenzi : 0;
        }
        $pickupTotal = max(0, $comenziTotal - $nrLivrariTotal);
        $pickupLunaCurenta = max(0, $comenziLunaCurenta - $nrLivrariLunaCurenta);

        return view('operatori.show', [
            'operator' => $operator,
            'date' => $date,
            'vanzariLunare1c' => $vanzariLunare1c,
            'nrLivrariTotal' => $nrLivrariTotal,
            'nrLivrariLunaCurenta' => $nrLivrariLunaCurenta,
            'pickupTotal' => $pickupTotal,
            'pickupLunaCurenta' => $pickupLunaCurenta,
        ]);
    }

    /**
     * Doar operatorul însuși poate edita pozele (profil și copertă), din Setări.
     * Adminul nu poate schimba pozele; operatorul = full_name al user-ului coincide cu operator.nume (ca în 1C).
     */
    private function canEditOperatorPhotos(Operator $operator): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        $fullName = trim((string) ($user->full_name ?? $user->name ?? ''));
        $operatorNume = trim((string) ($operator->nume ?? ''));
        return $fullName !== '' && $operatorNume !== '' && strcasecmp($fullName, $operatorNume) === 0;
    }

    /**
     * Încarcă poza de profil pentru operator.
     */
    public function uploadProfilePhoto(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);
        if (! $this->canEditOperatorPhotos($operator)) {
            return redirect()->route('operatori.show', $operator->id)
                ->with('error', 'Nu aveți permisiunea să modificați pozele acestui operator.');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $dir = 'operatori/' . $operator->id;
        if ($operator->photo_profil) {
            Storage::disk('public')->delete($operator->photo_profil);
        }
        $path = $request->file('photo')->store($dir, 'public');
        $operator->update(['photo_profil' => $path]);

        if (Auth::user() && Auth::user()->isOperator()) {
            return redirect()->route('setari')->with('success', 'Poza de profil a fost actualizată.');
        }
        return redirect()->route('operatori.show', $operator->id)->with('success', 'Poza de profil a fost actualizată.');
    }

    /**
     * Încarcă poza de copertă pentru operator.
     */
    public function uploadCoverPhoto(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);
        if (! $this->canEditOperatorPhotos($operator)) {
            return redirect()->route('operatori.show', $operator->id)
                ->with('error', 'Nu aveți permisiunea să modificați pozele acestui operator.');
        }

        $request->validate([
            'photo' => 'required|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        $dir = 'operatori/' . $operator->id;
        if ($operator->photo_coperta) {
            Storage::disk('public')->delete($operator->photo_coperta);
        }
        $path = $request->file('photo')->store($dir, 'public');
        $operator->update(['photo_coperta' => $path]);

        if (Auth::user() && Auth::user()->isOperator()) {
            return redirect()->route('setari')->with('success', 'Poza de copertă a fost actualizată.');
        }
        return redirect()->route('operatori.show', $operator->id)->with('success', 'Poza de copertă a fost actualizată.');
    }

    public function create()
    {
        return view('operatori.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nume' => 'required|string|max:255',
            'data_angajare' => 'required|date',
        ]);

        // Setăm valorile implicite pentru câmpurile opționale
        $validated['activ'] = true; // Implicit activ
        $validated['email'] = null;
        $validated['telefon'] = null;
        $validated['adresa'] = null;
        $validated['departament'] = null;
        $validated['functie'] = null;
        $validated['observatii'] = null;

        Operator::create($validated);

        return redirect()->route('operatori')->with('success', 'Operatorul a fost adăugat cu succes!');
    }

    public function edit($id)
    {
        $operator = Operator::findOrFail($id);
        return view('operatori.edit', compact('operator'));
    }

    public function update(Request $request, $id)
    {
        $operator = Operator::findOrFail($id);

        $validated = $request->validate([
            'nume' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'telefon' => 'nullable|string|max:50',
            'data_angajare' => 'nullable|date',
            'adresa' => 'nullable|string',
            'departament' => 'nullable|string|max:100',
            'functie' => 'nullable|string|max:100',
            'observatii' => 'nullable|string',
            'activ' => 'boolean',
        ]);

        $validated['activ'] = $request->has('activ') ? true : false;

        $operator->update($validated);

        return redirect()->route('operatori.show', $operator->id)->with('success', 'Operatorul a fost actualizat cu succes!');
    }

    public function destroy($id)
    {
        $operator = Operator::findOrFail($id);
        $operator->delete();

        return redirect()->route('operatori')->with('success', 'Operatorul a fost șters cu succes!');
    }

    private function calculateAverageSalesPerMonth($vanzari)
    {
        if ($vanzari->isEmpty()) {
            return 0;
        }

        $luni = $vanzari->groupBy(function($item) {
            return $item->data->format('Y-m');
        })->count();

        if ($luni == 0) {
            return 0;
        }

        return round($vanzari->sum('suma_cu_tva') / $luni, 2);
    }

    private function calculateWorkTime($dataAngajare)
    {
        if (!$dataAngajare) {
            return 'N/A';
        }

        $now = now();
        $start = \Carbon\Carbon::parse($dataAngajare);
        $diff = $start->diff($now);

        $ani = $diff->y;
        $luni = $diff->m;
        $zile = $diff->d;

        $result = [];
        
        if ($ani > 0) {
            $result[] = $ani . ' ' . ($ani == 1 ? 'an' : 'ani');
        }
        if ($luni > 0) {
            $result[] = $luni . ' ' . ($luni == 1 ? 'lună' : 'luni');
        }
        if ($zile > 0 && count($result) < 2) {
            $result[] = $zile . ' ' . ($zile == 1 ? 'zi' : 'zile');
        }

        return !empty($result) ? implode(', ', $result) : 'Mai puțin de o zi';
    }

    // Metode pentru gestionarea vânzărilor (pe luni)
    public function storeVanzare(Request $request, $operatorId)
    {
        $validated = $request->validate([
            'luna' => 'required|date_format:Y-m',
            'suma_fara_tva' => 'required|numeric|min:0',
            'suma_cu_tva' => 'required|numeric|min:0',
            'profit' => 'required|numeric',
            'nr_vanzari' => 'nullable|integer|min:0',
        ]);

        // Verifică dacă există deja o vânzăre pentru această lună pentru acest operator
        $lunaStart = \Carbon\Carbon::createFromFormat('Y-m', $validated['luna'])->startOfMonth();
        $lunaEnd = $lunaStart->copy()->endOfMonth();
        
        // Verifică dacă există deja o vânzăre pentru acest operator în această lună
        $existingVanzare = DateOp::where('operator_id', $operatorId)
            ->where('data', $lunaStart->format('Y-m-d'))
            ->first();

        if ($existingVanzare) {
            // Dacă există pentru același operator și aceeași dată, actualizează
            $validated['operator_id'] = $operatorId;
            $validated['data'] = $lunaStart->format('Y-m-d');
            $validated['nr_vanzari'] = $validated['nr_vanzari'] ?? 1;
            
            $existingVanzare->update($validated);
            
            return redirect()->route('operatori.show', $operatorId)
                ->with('success', 'Vânzarea a fost actualizată cu succes!');
        }

        // Salvează cu prima zi a lunii
        $validated['operator_id'] = $operatorId;
        $validated['data'] = $lunaStart->format('Y-m-d');
        $validated['nr_vanzari'] = $validated['nr_vanzari'] ?? 1;

        DateOp::create($validated);

        return redirect()->route('operatori.show', $operatorId)->with('success', 'Vânzarea a fost adăugată cu succes!');
    }

    public function updateVanzare(Request $request, $operatorId, $luna)
    {
        // Verifică dacă există deja o vânzare pentru luna specificată
        $lunaStart = \Carbon\Carbon::createFromFormat('Y-m', $luna)->startOfMonth();
        $lunaEnd = $lunaStart->copy()->endOfMonth();
        
        $vanzari = DateOp::where('operator_id', $operatorId)
            ->whereBetween('data', [$lunaStart, $lunaEnd])
            ->get();

        if ($vanzari->isEmpty()) {
            return redirect()->route('operatori.show', $operatorId)
                ->with('error', 'Nu s-a găsit vânzarea pentru această lună!');
        }

        $validated = $request->validate([
            'luna' => 'required|date_format:Y-m',
            'suma_fara_tva' => 'required|numeric|min:0',
            'suma_cu_tva' => 'required|numeric|min:0',
            'profit' => 'required|numeric',
            'nr_vanzari' => 'nullable|integer|min:0',
        ]);

        // Dacă s-a schimbat luna, verifică dacă există deja o vânzare pentru noua lună
        $newLunaStart = \Carbon\Carbon::createFromFormat('Y-m', $validated['luna'])->startOfMonth();
        if ($newLunaStart->format('Y-m') !== $luna) {
            $newLunaEnd = $newLunaStart->copy()->endOfMonth();
            $existingVanzare = DateOp::where('operator_id', $operatorId)
                ->whereBetween('data', [$newLunaStart, $newLunaEnd])
                ->whereNotIn('id', $vanzari->pluck('id'))
                ->first();

            if ($existingVanzare) {
                return redirect()->route('operatori.show', $operatorId)
                    ->with('error', 'Există deja o vânzare înregistrată pentru noua lună selectată!');
            }
        }

        // Actualizează toate vânzările din luna respectivă (sau creează una nouă dacă s-a schimbat luna)
        if ($vanzari->count() === 1) {
            // Actualizează vânzarea existentă
            $vanzare = $vanzari->first();
            $validated['data'] = $newLunaStart->format('Y-m-d');
            $validated['nr_vanzari'] = $validated['nr_vanzari'] ?? 1;
            $vanzare->update($validated);
        } else {
            // Dacă sunt mai multe vânzări, le șterge și creează una nouă
            foreach ($vanzari as $v) {
                $v->delete();
            }
            $validated['operator_id'] = $operatorId;
            $validated['data'] = $newLunaStart->format('Y-m-d');
            $validated['nr_vanzari'] = $validated['nr_vanzari'] ?? 1;
            DateOp::create($validated);
        }

        return redirect()->route('operatori.show', $operatorId)->with('success', 'Vânzarea a fost actualizată cu succes!');
    }

    public function destroyVanzare($operatorId, $luna)
    {
        $lunaStart = \Carbon\Carbon::createFromFormat('Y-m', $luna)->startOfMonth();
        $lunaEnd = $lunaStart->copy()->endOfMonth();
        
        $vanzari = DateOp::where('operator_id', $operatorId)
            ->whereBetween('data', [$lunaStart, $lunaEnd])
            ->get();

        if ($vanzari->isEmpty()) {
            return redirect()->route('operatori.show', $operatorId)
                ->with('error', 'Nu s-a găsit vânzarea pentru această lună!');
        }

        foreach ($vanzari as $vanzare) {
            $vanzare->delete();
        }

        return redirect()->route('operatori.show', $operatorId)->with('success', 'Vânzarea a fost ștearsă cu succes!');
    }
}
