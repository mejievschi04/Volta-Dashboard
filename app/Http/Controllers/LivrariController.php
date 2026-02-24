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

        $query = $isAdmin ? Livrare::with('user') : Livrare::where('user_id', $user->id);

        if ($luna !== null && $luna !== '') {
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
                    ->orWhere('oras', 'like', $term)
                    ->orWhere('nr_client', 'like', $term);
            });
        }

        $livrari = $query->orderByDesc('data')->orderByDesc('data_livrarii')->paginate(50)->withQueryString();

        if ($isAdmin) {
            $baseCount = Livrare::query();
            if ($luna) {
                $baseCount->whereRaw('DATE_FORMAT(data_livrarii, "%Y-%m") = ?', [$luna]);
            }
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
                        ->orWhere('oras', 'like', $term)
                        ->orWhere('nr_client', 'like', $term);
                });
            }
            $totalLivrari = $baseCount->count();

            $perOperatorQuery = Livrare::query()
                ->selectRaw('user_id, COUNT(*) as total')
                ->groupBy('user_id');
            if ($luna) {
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
                        ->orWhere('oras', 'like', $term)
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
                'filters' => ['luna' => $luna, 'operator_id' => $operatorId, 'locatie' => $locatie, 'cauta' => $cauta],
                'operatorsForFilter' => $operatorsForFilter,
            ]);
        }

        return view('livrari.index', [
            'livrari' => $livrari,
            'totalLivrari' => $livrari->total(),
            'perOperator' => collect(),
            'isAdmin' => false,
            'filters' => ['luna' => $luna, 'operator_id' => null, 'locatie' => $locatie, 'cauta' => $cauta],
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
            'oras' => 'required|string|max:255',
            'nr_client' => 'required|string|max:100',
            'data_livrarii' => 'required|date',
        ]);

        $oras = trim((string) $validated['oras']);
        $validated['in_chisinau'] = $this->isChisinau($oras);
        $validated['user_id'] = Auth::id();
        Livrare::create($validated);

        return redirect()->route('livrari')->with('success', 'Livrarea a fost adăugată.');
    }

    /** Returnează true dacă orașul este Chișinău (ignoră diacritice și majuscule). */
    private function isChisinau(string $oras): bool
    {
        $norm = mb_strtolower(trim($oras));
        return in_array($norm, ['chisinau', 'chișinău', 'chișinau', 'chisinău'], true);
    }
}
