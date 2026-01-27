<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Oferte;
use App\Models\Operator;
use App\Models\DateOp;
use Illuminate\Support\Facades\DB;

class OperatoriController extends Controller
{
    public function index()
    {
        // Obține toți operatorii activi
        $operatori = Operator::where('activ', true)
            ->orderBy('nume')
            ->get();

        // Obține statistici pentru fiecare operator
        $operatoriStats = [];
        
        foreach ($operatori as $operator) {
            // Statistici oferte
            $oferteStats = Oferte::where(function($query) use ($operator) {
                $query->where('operator_id', $operator->id)
                      ->orWhere('operator', $operator->nume);
            })
            ->selectRaw('
                COUNT(*) as total_oferte,
                SUM(CASE WHEN status = "trimise" THEN 1 ELSE 0 END) as oferte_trimise,
                SUM(CASE WHEN status = "finalizate" THEN 1 ELSE 0 END) as oferte_finalizate,
                SUM(CASE WHEN status = "refuzate" THEN 1 ELSE 0 END) as oferte_refuzate
            ')
            ->first();

            // Statistici vânzări
            $vanzariStats = DateOp::where('operator_id', $operator->id)
                ->selectRaw('
                    COUNT(*) as total_vanzari,
                    COALESCE(SUM(suma_fara_tva), 0) as total_suma_fara_tva,
                    COALESCE(SUM(suma_cu_tva), 0) as total_suma_cu_tva,
                    COALESCE(SUM(profit), 0) as total_profit,
                    COALESCE(SUM(nr_vanzari), 0) as total_nr_vanzari
                ')
                ->first();

            // Calculează timpul lucrat
            $timpLucrat = $this->calculateWorkTime($operator->data_angajare);

            $operatoriStats[] = [
                'operator' => $operator,
                'oferte' => $oferteStats,
                'vanzari' => $vanzariStats,
                'timp_lucrat' => $timpLucrat,
            ];
        }

        // Calculează procentajele pentru fiecare operator
        $chartData = [];
        $totalOperatoriVanzariFaraTva = 0;
        
        // Mai întâi colectăm toate vânzările operatorilor activi și calculăm totalul
        foreach ($operatoriStats as $stat) {
            // Obține valoarea și asigură-te că este un număr
            $vanzariFaraTva = $stat['vanzari']->total_suma_fara_tva ?? 0;
            $vanzariFaraTva = (float) $vanzariFaraTva;
            
            // Adaugă la total doar dacă există vânzări
            if ($vanzariFaraTva > 0) {
                $totalOperatoriVanzariFaraTva += $vanzariFaraTva;
            }
        }
        
        // Acum calculăm procentajele bazate pe totalul operatorilor activi
        foreach ($operatoriStats as $stat) {
            // Obține valoarea și asigură-te că este un număr
            $vanzariFaraTva = $stat['vanzari']->total_suma_fara_tva ?? 0;
            $vanzariFaraTva = (float) $vanzariFaraTva;
            
            // Calculează procentajul doar dacă există vânzări și totalul este mai mare decât 0
            $procent = 0;
            if ($totalOperatoriVanzariFaraTva > 0 && $vanzariFaraTva > 0) {
                $procent = ($vanzariFaraTva / $totalOperatoriVanzariFaraTva) * 100;
                // Rotunjim la 2 zecimale
                $procent = round($procent, 2);
            }
            
            // Adaugă în grafic doar dacă operatorul are vânzări
            // Folosim >= 0 pentru a include și operatorii cu 0 (dacă e nevoie)
            if ($vanzariFaraTva > 0) {
                $chartData[] = [
                    'nume' => $stat['operator']->nume,
                    'vanzari_fara_tva' => $vanzariFaraTva,
                    'procent' => $procent,
                ];
            }
        }
        
        // Sortează după vânzări descrescător pentru o afișare mai clară
        usort($chartData, function($a, $b) {
            return $b['vanzari_fara_tva'] <=> $a['vanzari_fara_tva'];
        });

        return view('operatori.index', compact('operatoriStats', 'chartData'));
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
        ]);
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
