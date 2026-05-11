<?php

namespace App\Http\Controllers;

use App\Models\Livrare;
use App\Models\User;
use App\Support\DbDate;
use App\Support\LocalitatiMoldova;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LivrariController extends Controller
{
    public function checkComanda(Request $request)
    {
        $numarComanda = trim((string) $request->query('numar_comanda', ''));
        $ignoreId = (int) $request->query('ignore_id', 0);

        if ($numarComanda === '') {
            return response()->json(['exists' => false]);
        }

        return response()->json(['exists' => $this->numarComandaExists($numarComanda, $ignoreId)]);
    }

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
        $faraRaion = $request->boolean('fara_raion');
        $dataLivrarii = $request->input('data'); // YYYY-MM-DD - o singură zi
        $dataDeLa = $request->input('data_de_la'); // YYYY-MM-DD - perioadă
        $dataPana = $request->input('data_pana');   // YYYY-MM-DD - perioadă

        $query = $isAdmin ? Livrare::with('user') : Livrare::where('user_id', $user->id);

        if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
            $query->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
        } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
            $query->whereDate('data_livrarii', $dataLivrarii);
        } elseif ($luna !== null && $luna !== '') {
            $query->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$luna]);
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
                    ->orWhere('raion', 'like', $term)
                    ->orWhere('nr_client', 'like', $term);
            });
        }
        $this->applyMissingRaionFilter($query, $faraRaion);

        $livrari = $query->orderByDesc('data')->orderByDesc('data_livrarii')->paginate(50)->withQueryString();
        $this->applyInferredRaionForPresentation($livrari->getCollection());

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
                        ->orWhere('raion', 'like', $term)
                        ->orWhere('nr_client', 'like', $term);
                });
            }
            $this->applyMissingRaionFilter($baseCount, $faraRaion);
            if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
                $baseCount->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
            } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
                $baseCount->whereDate('data_livrarii', $dataLivrarii);
            } elseif ($luna) {
                $baseCount->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$luna]);
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
                $perOperatorQuery->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$luna]);
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
                        ->orWhere('raion', 'like', $term)
                        ->orWhere('nr_client', 'like', $term);
                });
            }
            $this->applyMissingRaionFilter($perOperatorQuery, $faraRaion);
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
                'filters' => ['luna' => $luna, 'operator_id' => $operatorId, 'locatie' => $locatie, 'cauta' => $cauta, 'fara_raion' => $faraRaion ? '1' : '', 'data' => $dataLivrarii ?? '', 'data_de_la' => $dataDeLa ?? '', 'data_pana' => $dataPana ?? ''],
                'operatorsForFilter' => $operatorsForFilter,
                'livrariLocalitati' => LocalitatiMoldova::all(),
                'livrariRaioane' => LocalitatiMoldova::raioane(),
            ]);
        }

        return view('livrari.index', [
            'livrari' => $livrari,
            'totalLivrari' => $livrari->total(),
            'perOperator' => collect(),
            'isAdmin' => false,
            'filters' => ['luna' => $luna, 'operator_id' => null, 'locatie' => $locatie, 'cauta' => $cauta, 'fara_raion' => $faraRaion ? '1' : '', 'data' => $dataLivrarii ?? '', 'data_de_la' => $dataDeLa ?? '', 'data_pana' => $dataPana ?? ''],
            'operatorsForFilter' => collect(),
            'livrariLocalitati' => LocalitatiMoldova::all(),
            'livrariRaioane' => LocalitatiMoldova::raioane(),
        ]);
    }

    /** Returneaza toate livrarile filtrate pentru exportul Excel. */
    public function exportData(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);

        $luna = $request->input('luna');
        $operatorId = $request->input('operator_id');
        $locatie = $request->input('locatie');
        $cauta = trim((string) $request->input('cauta', ''));
        $faraRaion = $request->boolean('fara_raion');
        $dataLivrarii = $request->input('data');
        $dataDeLa = $request->input('data_de_la');
        $dataPana = $request->input('data_pana');

        $query = $isAdmin ? Livrare::with('user') : Livrare::where('user_id', $user->id);

        if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
            $query->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
        } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
            $query->whereDate('data_livrarii', $dataLivrarii);
        } elseif ($luna !== null && $luna !== '') {
            $query->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$luna]);
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
                    ->orWhere('raion', 'like', $term)
                    ->orWhere('nr_client', 'like', $term);
            });
        }
        $this->applyMissingRaionFilter($query, $faraRaion);

        $headers = ['Număr comandă', 'Data', 'Localitate', 'Raion', 'Adresa', 'Nr. client', 'Data livrării', 'Locație'];
        if ($isAdmin) {
            $headers[] = 'Operator';
        }

        $rows = $query->orderByDesc('data')->orderByDesc('data_livrarii')->get()->map(function (Livrare $livrare) use ($isAdmin) {
            $effectiveRaion = $this->effectiveRaionForLivrare($livrare);
            $row = [
                $livrare->numar_comanda,
                optional($livrare->data)->format('d.m.Y'),
                $livrare->localitate ?? '—',
                $effectiveRaion !== '' ? $effectiveRaion : '—',
                $livrare->adresa_livrarii,
                $livrare->nr_client,
                optional($livrare->data_livrarii)->format('d.m.Y'),
                $effectiveRaion !== '' ? ($this->isChisinau($effectiveRaion) ? 'În Chișinău' : 'În afara') : (isset($livrare->in_chisinau) ? ($livrare->in_chisinau ? 'În Chișinău' : 'În afara') : '—'),
            ];

            if ($isAdmin) {
                $row[] = $livrare->user ? (trim((string) ($livrare->user->full_name ?? $livrare->user->name ?? '')) ?: $livrare->user->username) : '—';
            }

            return $row;
        })->values();

        return response()->json([
            'headers' => $headers,
            'rows' => $rows,
        ]);
    }

    public function map(Request $request)
    {
        return view('livrari.map', [
            'backUrl' => route('livrari', $request->query()),
        ]);
    }

    /** Returneaza agregari live pentru harta livrarilor pe raioane. */
    public function mapData(Request $request)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);
        $registryRaioane = LocalitatiMoldova::raioane();

        $rows = $this->filteredLivrariQuery($request, $isAdmin, $user)
            ->get(['raion', 'localitate', 'adresa_livrarii', 'data_livrarii'])
            ->reject(fn (Livrare $livrare) => LocalitatiMoldova::isExcludedRaion((string) ($livrare->raion ?? '')));
        $groupedByRaion = $rows->groupBy(function (Livrare $livrare) {
            $fallbackRaion = $this->sanitizeRaionValue((string) ($livrare->raion ?? ''));

            return LocalitatiMoldova::raionForLocalitateAndAddress(
                (string) ($livrare->localitate ?? ''),
                (string) ($livrare->adresa_livrarii ?? ''),
                $fallbackRaion !== '' ? $fallbackRaion : 'Fără raion'
            );
        });

        $raioane = $registryRaioane
            ->merge($groupedByRaion->keys())
            ->unique()
            ->map(function (string $raion) use ($groupedByRaion) {
                $items = $groupedByRaion->get($raion, collect());
                $localitati = $items
                    ->groupBy(function (Livrare $livrare) {
                        $rawLocalitate = trim((string) ($livrare->localitate ?? ''));

                        return $this->normalizeLocalitateKey($rawLocalitate);
                    })
                    ->map(function ($localitatiItems, string $localitateKey) {
                        $firstLocalitate = trim((string) (optional($localitatiItems->first())->localitate ?? ''));

                        return [
                            'localitate' => $this->canonicalLocalitateDisplayName($firstLocalitate, $localitateKey),
                            'total' => $localitatiItems->count(),
                        ];
                    })
                    ->sortBy([
                        ['total', 'desc'],
                        ['localitate', 'asc'],
                    ])
                    ->values()
                    ->take(8)
                    ->values();

                return [
                    'raion' => $raion,
                    'total' => $items->count(),
                    'localitati' => $localitati,
                ];
            })
            ->sortBy([
                ['total', 'desc'],
                ['raion', 'asc'],
            ])
            ->values();

        return response()->json([
            'total' => $rows->count(),
            'max_total' => $raioane->max('total') ?? 0,
            'period_label' => $this->mapPeriodLabel($request),
            'raioane' => $raioane,
            'generated_at' => now()->format('d.m.Y H:i'),
        ]);
    }

    /**
     * Salvează o livrare nouă (operator sau admin).
     * Locația (în Chișinău / în afara) se setează automat după raion.
     */
    public function store(Request $request)
    {
        $request->merge([
            'numar_comanda' => trim((string) $request->input('numar_comanda')),
        ]);

        $validated = $request->validate([
            'numar_comanda' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) {
                    if ($this->numarComandaExists((string) $value)) {
                        $fail('Această comandă există deja. Nu poate fi introdusă de două ori.');
                    }
                },
            ],
            'adresa_livrarii' => 'nullable|string|max:500',
            'localitate' => 'required|string|max:255',
            'raion' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (! $this->raionMatchesLocalitate((string) $request->input('localitate'), (string) $value)) {
                        $fail('Raionul nu corespunde localității selectate.');
                    }
                },
            ],
            'nr_client' => 'required|string|max:100',
            'data_livrarii' => 'required|date',
        ], [
            'numar_comanda.unique' => 'Această comandă există deja. Nu poate fi introdusă de două ori.',
        ]);

        $validated['localitate'] = $this->resolveLocalitateName(
            trim((string) $validated['localitate']),
            trim((string) $validated['raion'])
        );
        $validated['raion'] = trim((string) $validated['raion']);
        $validated['adresa_livrarii'] = trim((string) ($validated['adresa_livrarii'] ?? ''));
        $validated['data'] = now()->toDateString();
        $validated['in_chisinau'] = $this->isChisinau($validated['raion']);
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
                    'raion' => $livrare->raion,
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
        $request->merge([
            'numar_comanda' => trim((string) $request->input('numar_comanda')),
        ]);

        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);

        if (!$isAdmin && (int) $livrare->user_id !== (int) $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Nu aveți permisiunea de a edita această livrare.'], 403);
            }
            return redirect()->route('livrari')->with('error', 'Nu aveți permisiunea de a edita această livrare.');
        }

        $validated = $request->validate([
            'numar_comanda' => [
                'required',
                'string',
                'max:100',
                function ($attribute, $value, $fail) use ($livrare) {
                    if ($this->numarComandaExists((string) $value, (int) $livrare->id)) {
                        $fail('Această comandă există deja. Nu poate fi introdusă de două ori.');
                    }
                },
            ],
            'adresa_livrarii' => 'nullable|string|max:500',
            'localitate' => 'required|string|max:255',
            'raion' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    if (! $this->raionMatchesLocalitate((string) $request->input('localitate'), (string) $value)) {
                        $fail('Raionul nu corespunde localității selectate.');
                    }
                },
            ],
            'nr_client' => 'required|string|max:100',
            'data_livrarii' => 'required|date',
        ], [
            'numar_comanda.unique' => 'Această comandă există deja. Nu poate fi introdusă de două ori.',
        ]);

        $validated['localitate'] = $this->resolveLocalitateName(
            trim((string) $validated['localitate']),
            trim((string) $validated['raion'])
        );
        $validated['raion'] = trim((string) $validated['raion']);
        $validated['adresa_livrarii'] = trim((string) ($validated['adresa_livrarii'] ?? ''));
        $validated['in_chisinau'] = $this->isChisinau($validated['raion']);
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
                    'raion' => $livrare->raion,
                    'adresa_livrarii' => $livrare->adresa_livrarii,
                    'nr_client' => $livrare->nr_client,
                    'data_livrarii' => $livrare->data_livrarii->format('d.m.Y'),
                    'locatie' => $livrare->in_chisinau ? 'În Chișinău' : 'În afara',
                ],
            ]);
        }

        return redirect()->route('livrari')->with('success', 'Livrarea a fost actualizată.');
    }

    /**
     * Șterge o livrare.
     * Operator: doar propriile livrări. Admin: orice livrare.
     */
    public function destroy(Request $request, Livrare $livrare)
    {
        $user = Auth::user();
        $isAdmin = $user && in_array(strtolower((string) ($user->role ?? '')), ['admin', 'administrator'], true);

        if (!$isAdmin && (int) $livrare->user_id !== (int) $user->id) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Nu aveți permisiunea de a șterge această livrare.'], 403);
            }

            return redirect()->route('livrari')->with('error', 'Nu aveți permisiunea de a șterge această livrare.');
        }

        $livrare->delete();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Livrarea a fost ștearsă.',
            ]);
        }

        return redirect()->route('livrari')->with('success', 'Livrarea a fost ștearsă.');
    }

    private function filteredLivrariQuery(Request $request, bool $isAdmin, $user)
    {
        $luna = $request->input('luna');
        $operatorId = $request->input('operator_id');
        $locatie = $request->input('locatie');
        $cauta = trim((string) $request->input('cauta', ''));
        $faraRaion = $request->boolean('fara_raion');
        $dataLivrarii = $request->input('data');
        $dataDeLa = $request->input('data_de_la');
        $dataPana = $request->input('data_pana');

        $query = $isAdmin ? Livrare::query() : Livrare::where('user_id', $user->id);

        if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
            $query->whereDate('data_livrarii', '>=', $dataDeLa)->whereDate('data_livrarii', '<=', $dataPana);
        } elseif ($dataLivrarii !== null && $dataLivrarii !== '') {
            $query->whereDate('data_livrarii', $dataLivrarii);
        } elseif ($luna !== null && $luna !== '') {
            $query->whereRaw(DbDate::month('data_livrarii') . ' = ?', [$luna]);
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
                    ->orWhere('raion', 'like', $term)
                    ->orWhere('nr_client', 'like', $term);
            });
        }
        $this->applyMissingRaionFilter($query, $faraRaion);

        return $query;
    }

    private function applyMissingRaionFilter($query, bool $faraRaion): void
    {
        if (! $faraRaion) {
            return;
        }

        $query->where(function ($q) {
            $q->whereNull('raion')
                ->orWhereRaw("TRIM(COALESCE(raion, '')) IN ('', '-', '—')");
        });
    }

    private function applyInferredRaionForPresentation(\Illuminate\Support\Collection $livrari): void
    {
        $livrari->transform(function (Livrare $livrare) {
            $effectiveRaion = $this->effectiveRaionForLivrare($livrare);

            if ($effectiveRaion !== '') {
                $livrare->raion = $effectiveRaion;
                $livrare->in_chisinau = $this->isChisinau($effectiveRaion);
            }

            return $livrare;
        });
    }

    private function effectiveRaionForLivrare(Livrare $livrare): string
    {
        $fallbackRaion = $this->sanitizeRaionValue((string) ($livrare->raion ?? ''));

        return trim((string) LocalitatiMoldova::raionForLocalitateAndAddress(
            (string) ($livrare->localitate ?? ''),
            (string) ($livrare->adresa_livrarii ?? ''),
            $fallbackRaion
        ));
    }

    private function sanitizeRaionValue(string $raion): string
    {
        $trimmed = trim($raion);

        return in_array($trimmed, ['', '-', '—'], true) ? '' : $trimmed;
    }

    private function mapPeriodLabel(Request $request): string
    {
        $dataDeLa = $request->input('data_de_la');
        $dataPana = $request->input('data_pana');
        $dataLivrarii = $request->input('data');
        $luna = $request->input('luna');

        if ($dataDeLa !== null && $dataDeLa !== '' && $dataPana !== null && $dataPana !== '') {
            return optional(\Carbon\Carbon::parse($dataDeLa))->format('d.m.Y') . ' - ' . optional(\Carbon\Carbon::parse($dataPana))->format('d.m.Y');
        }

        if ($dataLivrarii !== null && $dataLivrarii !== '') {
            return optional(\Carbon\Carbon::parse($dataLivrarii))->format('d.m.Y');
        }

        if ($luna !== null && $luna !== '') {
            return \App\Support\LunaRomana::labelFromYm((string) $luna);
        }

        return 'Toată perioada';
    }

    /** Returnează true dacă raionul/localitatea este Chișinău (ignoră diacritice și majuscule). */
    private function numarComandaExists(string $numarComanda, int $ignoreId = 0): bool
    {
        $normalized = mb_strtolower(trim($numarComanda));
        if ($normalized === '') {
            return false;
        }

        $query = Livrare::whereRaw('LOWER(TRIM(numar_comanda)) = ?', [$normalized]);
        if ($ignoreId > 0) {
            $query->whereKeyNot($ignoreId);
        }

        return $query->exists();
    }

    private function isChisinau(string $localitate): bool
    {
        return $this->normalizeLocation($localitate) === 'chisinau';
    }

    private function raionMatchesLocalitate(string $localitate, string $raion): bool
    {
        $localitateNorm = $this->normalizeLocation($localitate);
        $raionNorm = $this->normalizeLocation($raion);

        if ($localitateNorm === '' || $raionNorm === '') {
            return false;
        }

        $matches = LocalitatiMoldova::localitateMatches($localitate, 12);

        if ($matches->isEmpty()) {
            return true;
        }

        return $matches->contains(function (array $match) use ($raionNorm) {
            foreach ($match['raioane'] as $matchRaion) {
                if ($this->normalizeLocation((string) $matchRaion) === $raionNorm) {
                    return true;
                }
            }

            return false;
        });
    }

    private function resolveLocalitateName(string $localitate, string $raion): string
    {
        $match = LocalitatiMoldova::bestLocalitateMatch($localitate, $raion);

        return $match['localitate'] ?? $localitate;
    }

    private function normalizeLocation(string $value): string
    {
        $norm = mb_strtolower(trim($value));

        return strtr($norm, [
            'ă' => 'a',
            'â' => 'a',
            'î' => 'i',
            'ș' => 's',
            'ş' => 's',
            'ț' => 't',
            'ţ' => 't',
        ]);
    }

    private function normalizeLocalitateKey(string $localitate): string
    {
        $trimmed = trim($localitate);
        if ($trimmed === '') {
            return '__fara_localitate__';
        }

        return $this->normalizeLocation($trimmed);
    }

    private function canonicalLocalitateDisplayName(string $sourceName, string $normalizedKey): string
    {
        if ($normalizedKey === '__fara_localitate__') {
            return 'Fără localitate';
        }

        if ($normalizedKey === 'chisinau') {
            return 'Chișinău';
        }

        $trimmed = preg_replace('/\s+/u', ' ', trim($sourceName));
        if (! is_string($trimmed) || $trimmed === '') {
            return 'Fără localitate';
        }

        return mb_convert_case(mb_strtolower($trimmed, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    }
}
