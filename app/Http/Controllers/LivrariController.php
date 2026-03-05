<?php

namespace App\Http\Controllers;

use App\Models\Livrare;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivrariController extends Controller
{
    /**
     * Operator: listă livrările proprii + formular adăugare.
     * Admin: listă toate livrările + coloană operator + KPI. Filtre: lună, operator, Chișinău/în afara.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);

        $luna = $request->input('luna'); // YYYY-MM
        $operatorId = $request->input('operator_id');
        $locatie = $request->input('locatie'); // 'chisinau' | 'afara' | ''
        $cauta = trim((string) $request->input('cauta', ''));
        $dataLivrarii = $request->input('data'); // YYYY-MM-DD - o singură zi
        $dataDeLa = $request->input('data_de_la'); // YYYY-MM-DD - perioadă
        $dataPana = $request->input('data_pana');   // YYYY-MM-DD - perioadă

        $query = $isAdmin ? Livrare::with('user') : Livrare::where('user_id', $user->id);

        if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
            $query->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
        } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
            $query->whereDate('data_livrarii', $dataLivrarii);
        } elseif ($luna !== null && $luna !== '') {
            $query->whereRaw('DATE_FORMAT(data_livrarii, "%Y-%m") = ?', [$luna]);
        }
        if ($isAdmin && $operatorId !== null && $operatorId !== '') {
            $query->where('user_id', $operatorId);
        }
        if ($locatie === 'chisinau') {
            $query->where('in_chisinau', true);
        } elseif ($locatie === 'afara') {
            $query->where('in_chisinau', false);
        }
        if ($cauta !== '') {
            $term = '%' . $cauta . '%';
            $query->where(function ($q) use ($term) {
                $q->where('numar_comanda', 'like', $term)
                    ->orWhere('adresa_livrarii', 'like', $term)
                    ->orWhere('localitate', 'like', $term)
                    ->orWhere('nr_client', 'like', $term);
            });
        }

        $livrari = $query->orderByDesc('data')->orderByDesc('data_livrarii')->paginate(50)->withQueryString();

        if ($isAdmin) {
            $baseCount = Livrare::query();
            if ($operatorId) {
                $baseCount->where('user_id', $operatorId);
            }
            if ($locatie === 'chisinau') {
                $baseCount->where('in_chisinau', true);
            } elseif ($locatie === 'afara') {
                $baseCount->where('in_chisinau', false);
            }
            if ($cauta !== '') {
                $term = '%' . $cauta . '%';
                $baseCount->where(function ($q) use ($term) {
                    $q->where('numar_comanda', 'like', $term)
                        ->orWhere('adresa_livrarii', 'like', $term)
                        ->orWhere('localitate', 'like', $term)
                        ->orWhere('nr_client', 'like', $term);
                });
            }
            if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
                $baseCount->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
            } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
                $baseCount->whereDate('data_livrarii', $dataLivrarii);
            } elseif ($luna) {
                $baseCount->whereRaw('DATE_FORMAT(data_livrarii, "%Y-%m") = ?', [$luna]);
            }
            $totalLivrari = $baseCount->count();

            $perOperatorQuery = Livrare::query()
                ->selectRaw('user_id, COUNT(*) as total')
                ->groupBy('user_id');
            if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
                $perOperatorQuery->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
            } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
                $perOperatorQuery->whereDate('data_livrarii', $dataLivrarii);
            } elseif ($luna) {
                $perOperatorQuery->whereRaw('DATE_FORMAT(data_livrarii, "%Y-%m") = ?', [$luna]);
            }
            if ($locatie === 'chisinau') {
                $perOperatorQuery->where('in_chisinau', true);
            } elseif ($locatie === 'afara') {
                $perOperatorQuery->where('in_chisinau', false);
            }
            if ($cauta !== '') {
                $term = '%' . $cauta . '%';
                $perOperatorQuery->where(function ($q) use ($term) {
                    $q->where('numar_comanda', 'like', $term)
                        ->orWhere('adresa_livrarii', 'like', $term)
                        ->orWhere('localitate', 'like', $term)
                        ->orWhere('nr_client', 'like', $term);
                });
            }
            $perOperatorRows = $perOperatorQuery->get();
            $users = User::whereIn('id', $perOperatorRows->pluck('user_id'))->get()->keyBy('id');
            $perOperator = $perOperatorRows->map(function ($row) use ($users) {
                $u = $users->get($row->user_id);
                $nume = $u ? (trim((string) ($u->full_name ?? $u->name ?? '')) ?: $u->username) : '—';
                return (object) ['user_id' => $row->user_id, 'nume' => $nume, 'total' => (int) $row->total];
            });
            $operatorIds = Livrare::select('user_id')->distinct()->pluck('user_id');
            $operatorsForFilter = User::whereIn('id', $operatorIds)
                ->orWhere(function ($q) {
                    $q->whereRaw('LOWER(TRIM(COALESCE(role, ""))) IN (?, ?)', ['operator', 'operatori']);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'full_name', 'username']);
            return view('livrari.index', [
                'livrari' => $livrari,
                'totalLivrari' => $totalLivrari,
                'perOperator' => $perOperator,
                'isAdmin' => true,
                'filters' => ['luna' => $luna, 'operator_id' => $operatorId, 'locatie' => $locatie, 'cauta' => $cauta, 'data' => $dataLivrarii ?? '', 'data_de_la' => $dataDeLa ?? '', 'data_pana' => $dataPana ?? ''],
                'operatorsForFilter' => $operatorsForFilter,
            ]);
        }

        return view('livrari.index', [
            'livrari' => $livrari,
            'totalLivrari' => $livrari->total(),
            'perOperator' => collect(),
            'isAdmin' => false,
            'filters' => ['luna' => $luna, 'operator_id' => null, 'locatie' => $locatie, 'cauta' => $cauta, 'data' => $dataLivrarii ?? '', 'data_de_la' => $dataDeLa ?? '', 'data_pana' => $dataPana ?? ''],
            'operatorsForFilter' => collect(),
        ]);
    }

    /**
     * Salvează o livrare nouă (operator sau admin).
     * Locația (în Chișinău / în afara) se setează automat după oraș: dacă orașul = Chișinău → în Chișinău, altfel → în afara.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'numar_comanda' => 'required|string|max:100',
            'data' => 'required|date',
            'adresa_livrarii' => 'required|string|max:500',
            'localitate' => 'required|string|max:255',
            'nr_client' => 'required|string|max:100',
            'data_livrarii' => 'required|date',
        ]);

        $localitate = trim((string) $validated['localitate']);
        $validated['in_chisinau'] = $this->isChisinau($localitate);
        $validated['user_id'] = Auth::id();
        $livrare = Livrare::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Livrarea a fost adăugată.',
                'livrare' => [
                    'id' => $livrare->id,
                    'numar_comanda' => $livrare->numar_comanda,
                    'data' => $livrare->data->format('d.m.Y'),
                    'localitate' => $livrare->localitate,
                    'adresa_livrarii' => $livrare->adresa_livrarii,
                    'nr_client' => $livrare->nr_client,
                    'data_livrarii' => $livrare->data_livrarii->format('d.m.Y'),
                    'locatie' => $livrare->in_chisinau ? 'În Chișinău' : 'În afara',
                ],
            ]);
        }

        return redirect()->route('livrari')->with('success', 'Livrarea a fost adăugată.');
    }

    /**
     * Actualizează o livrare existentă.
     * Operator: doar propriile livrări. Admin: orice livrare.
     */
    public function update(Request $request, Livrare $livrare)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);

        if (!$isAdmin && (int) $livrare->user_id !== (int) $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Nu aveți permisiunea de a edita această livrare.'], 403);
            }
            return redirect()->route('livrari')->with('error', 'Nu aveți permisiunea de a edita această livrare.');
        }

        $validated = $request->validate([
            'numar_comanda' => 'required|string|max:100',
            'data' => 'required|date',
            'adresa_livrarii' => 'required|string|max:500',
            'localitate' => 'required|string|max:255',
            'nr_client' => 'required|string|max:100',
            'data_livrarii' => 'required|date',
        ]);

        $localitate = trim((string) $validated['localitate']);
        $validated['in_chisinau'] = $this->isChisinau($localitate);
        $livrare->update($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Livrarea a fost actualizată.',
                'livrare' => [
                    'id' => $livrare->id,
                    'numar_comanda' => $livrare->numar_comanda,
                    'data' => $livrare->data->format('d.m.Y'),
                    'localitate' => $livrare->localitate,
                    'adresa_livrarii' => $livrare->adresa_livrarii,
                    'nr_client' => $livrare->nr_client,
                    'data_livrarii' => $livrare->data_livrarii->format('d.m.Y'),
                    'locatie' => $livrare->in_chisinau ? 'În Chișinău' : 'În afara',
                ],
            ]);
        }

        return redirect()->route('livrari')->with('success', 'Livrarea a fost actualizată.');
    }

    /** Returnează true dacă localitatea este Chișinău (ignoră diacritice și majuscule). */
    private function isChisinau(string $localitate): bool
    {
        $norm = mb_strtolower(trim($localitate));
        return in_array($norm, ['chisinau', 'chișinău', 'chișinau', 'chisinău'], true);
    }
}
