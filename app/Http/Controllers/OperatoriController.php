<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Oferte;
use App\Models\Operator;
use App\Models\DateOp;
use App\Models\OnecKpiOperator;
use Illuminate\Support\Facades\DB;

class OperatoriController extends Controller
{
    public function index()
    {
        // Date din 1C (ianuarie 2023); excludem operatorii dezactivați (Operator.activ = 0)
        $operatori1c = [];
        $chartData1c = [];
        $dezactivatedNume = Operator::where('activ', false)
            ->get()
            ->map(fn ($o) => trim((string) ($o->nume ?? '')))
            ->filter()
            ->values()
            ->toArray();

        try {
            $rows = OnecKpiOperator::query()
                ->join('onec_kpi_syncs', 'onec_kpi_operatori.onec_kpi_sync_id', '=', 'onec_kpi_syncs.id')
                ->where('onec_kpi_syncs.period_start', '>=', '2023-01-01')
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
                if (in_array($nume, $dezactivatedNume, true)) {
                    continue;
                }
                $vanzari = (float) $row->total_vanzari_fara_tva;
                $operatorRecord = Operator::whereRaw('TRIM(nume) = ?', [$nume])->first();
                $operatori1c[] = [
                    'nume' => $nume,
                    'vanzari_fara_tva' => $vanzari,
                    'vanzari_cu_tva' => (float) $row->total_vanzari_cu_tva,
                    'profit' => (float) $row->total_profit,
                    'nr_comenzi' => (int) $row->total_comenzi,
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

        return view('operatori.index', compact('operatori1c', 'chartData1c', 'operatoriDezactivati'));
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
                    DATE_FORMAT(onec_kpi_syncs.period_start, "%Y-%m") as luna,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as vanzari_luna,
                    COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as vanzari_cu_tva,
                    COALESCE(SUM(onec_kpi_operatori.profit), 0) as profit,
                    COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as comenzi
                ')
                ->groupBy(DB::raw('DATE_FORMAT(onec_kpi_syncs.period_start, "%Y-%m")'))
                ->orderBy('luna', 'desc')
                ->get();
            foreach ($lunareRows as $r) {
                $vanzariLunare1c->push((object) [
                    'luna' => $r->luna,
                    'luna_label' => \Carbon\Carbon::createFromFormat('Y-m', $r->luna)->translatedFormat('F Y'),
                    'vanzari_luna' => (float) $r->vanzari_luna,
                    'vanzari_cu_tva' => (float) $r->vanzari_cu_tva,
                    'profit' => (float) $r->profit,
                    'comenzi' => (int) $r->comenzi,
                    'nr_vanzari' => (int) $r->comenzi,
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
        $nume = trim((string) $request->input('nume', ''));
        if ($nume === '') {
            return redirect()->route('operatori')->with('error', 'Nume invalid.');
        }

        $operator = Operator::whereRaw('TRIM(nume) = ?', [$nume])->first();
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
                        DATE_FORMAT(onec_kpi_syncs.period_start, "%Y-%m") as luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_fara_tva), 0) as vanzari_luna,
                        COALESCE(SUM(onec_kpi_operatori.vanzari_cu_tva), 0) as vanzari_cu_tva,
                        COALESCE(SUM(onec_kpi_operatori.profit), 0) as profit,
                        COALESCE(SUM(onec_kpi_operatori.nr_comenzi), 0) as comenzi
                    ')
                    ->groupBy(DB::raw('DATE_FORMAT(onec_kpi_syncs.period_start, "%Y-%m")'))
                    ->orderBy('luna', 'desc')
                    ->get();

                foreach ($lunareRows as $r) {
                    $vanzariLunare1c->push((object) [
                        'luna' => $r->luna,
                        'luna_label' => \Carbon\Carbon::createFromFormat('Y-m', $r->luna)->translatedFormat('F Y'),
                        'vanzari_luna' => (float) $r->vanzari_luna,
                        'vanzari_cu_tva' => (float) $r->vanzari_cu_tva,
                        'profit' => (float) $r->profit,
                        'comenzi' => (int) $r->comenzi,
                        'nr_vanzari' => (int) $r->comenzi,
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

        return view('operatori.me', [
            'operatorNume' => $operatorNume ?: $fullName ?: $user->username,
            'date' => $date,
            'vanzariLunare1c' => $vanzariLunare1c,
            'operatorRecord' => $operatorRecord,
            'canEditPhotos' => $canEditPhotos,
        ]);
    }

    public function show($id)
    {
        // Găsește operatorul după ID sau nume
        $operator = Operator::where('id', $id)
            ->orWhere('nume', $id)
            ->firstOrFail();

        // Obține toate ofertele pentru operator
        $oferte = Oferte::where(function($query) use ($operator) {
            $query->where('operator_id', $operator->id)
                  ->orWhere('operator', $operator->nume);
        })
        ->orderBy('data_trimisa', 'desc')
        ->get();

        // Statistici oferte
        $oferteStats = [
            'total_oferte' => $oferte->count(),
            'oferte_trimise' => $oferte->where('status', 'trimise')->count(),
            'oferte_finalizate' => $oferte->where('status', 'finalizate')->count(),
            'oferte_refuzate' => $oferte->where('status', 'refuzate')->count(),
            'valoare_totala' => $oferte->sum('valoare'),
            'valoare_finalizate' => $oferte->where('status', 'finalizate')->sum('valoare'),
        ];

        // Statistici vânzări
        $vanzari = DateOp::where('operator_id', $operator->id)
            ->orderBy('data', 'desc')
            ->get();

        $vanzariStats = [
            'total_vanzari' => $vanzari->sum('nr_vanzari'),
            'total_suma_fara_tva' => $vanzari->sum('suma_fara_tva'),
            'total_suma_cu_tva' => $vanzari->sum('suma_cu_tva'),
            'total_profit' => $vanzari->sum('profit'),
            'total_nr_vanzari' => $vanzari->sum('nr_vanzari'),
            'medie_vanzari_luna' => $this->calculateAverageSalesPerMonth($vanzari),
        ];

        // Statistici vânzări pe luni
        $vanzariLunare = DateOp::where('operator_id', $operator->id)
            ->selectRaw('
                DATE_FORMAT(data, "%Y-%m") as luna,
                DATE_FORMAT(data, "%Y") as an,
                DATE_FORMAT(data, "%m") as luna_num,
                DATE_FORMAT(data, "%M %Y") as luna_label,
                COUNT(*) as comenzi,
                SUM(suma_fara_tva) as vanzari_luna,
                SUM(profit) as profit,
                SUM(nr_vanzari) as nr_vanzari
            ')
            ->groupBy('luna', 'an', 'luna_num', 'luna_label')
            ->orderBy('luna', 'desc')
            ->get();

        // Calculează suma cu TVA pentru fiecare lună (pentru JavaScript)
        $vanzariLunareForJs = collect();
        foreach ($vanzariLunare as $v) {
            $vanzariLuna = DateOp::where('operator_id', $operator->id)
                ->whereRaw('DATE_FORMAT(data, "%Y-%m") = ?', [$v->luna])
                ->get();
            $sumaCuTva = $vanzariLuna->sum('suma_cu_tva');
            
            $vanzariLunareForJs->put($v->luna, [
                'luna' => $v->luna,
                'suma_fara_tva' => $v->vanzari_luna,
                'suma_cu_tva' => $sumaCuTva > 0 ? $sumaCuTva : ($v->vanzari_luna * 1.19),
                'profit' => $v->profit,
                'nr_vanzari' => $v->nr_vanzari ?? 1,
            ]);
        }

        return view('operatori.show', [
            'operator' => $operator,
            'oferte' => $oferte,
            'oferteStats' => $oferteStats,
            'vanzari' => $vanzari,
            'vanzariStats' => $vanzariStats,
            'vanzariLunare' => $vanzariLunare,
            'vanzariLunareForJs' => $vanzariLunareForJs,
            'canEditPhotos' => $this->canEditOperatorPhotos($operator),
        ]);
    }

    /**
     * Doar operatorul însuși sau adminul poate edita pozele (profil și copertă).
     * Operatorul = full_name al user-ului coincide cu operator.nume (ca în 1C).
     */
    private function canEditOperatorPhotos(Operator $operator): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        $role = strtolower((string) ($user->role ?? ''));
        if (in_array($role, ['admin', 'administrator'], true)) {
            return true;
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
