<?php

namespace App\Http\Controllers;

use App\Services\CallCenterRaportLunarService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RaportLunarController extends Controller
{
    public function __construct(
        private CallCenterRaportLunarService $raportLunar
    ) {}

    public function index(Request $request): View
    {
        $ym = (string) $request->query('month', date('Y-m'));
        $data = $this->raportLunar->build($ym);

        return view('rapoarte.raport-lunar', $data);
    }

    public function storeInputs(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'regex:/^\d{4}-\d{2}$/'],
            'operators' => ['required', 'array'],
            'operators.*' => ['required', 'string', 'max:255'],
            'chaturi' => ['required', 'array'],
            'chaturi.*' => ['nullable'],
            'apeluri' => ['required', 'array'],
            'apeluri.*' => ['nullable'],
            'plan' => ['nullable', 'array'],
            'plan.*' => ['nullable'],
        ]);

        $ym = $this->raportLunar->normalizeYm($validated['month']);
        $operators = $validated['operators'];
        $chaturi = array_map(static function ($v) {
            if ($v === null || $v === '') {
                return 0;
            }
            if (! is_numeric($v)) {
                return 0;
            }

            return max(0, min(99999999, (int) $v));
        }, $validated['chaturi']);
        $apeluri = array_map(static function ($v) {
            if ($v === null || $v === '') {
                return 0;
            }
            if (! is_numeric($v)) {
                return 0;
            }

            return max(0, min(99999999, (int) $v));
        }, $validated['apeluri']);

        $planuri = array_map(static function ($v) {
            if ($v === null) {
                return null;
            }
            $v = str_replace([' ', "\u{00A0}", ','], ['', '', '.'], trim((string) $v));
            if ($v === '' || ! is_numeric($v)) {
                return null;
            }

            return max(0.0, (float) $v);
        }, $validated['plan'] ?? array_fill(0, count($operators), null));

        if (count($operators) !== count($chaturi) || count($operators) !== count($apeluri) || count($operators) !== count($planuri)) {
            return back()->withInput()->withErrors(['rows' => 'Număr inconsistent de rânduri (operatori / chaturi / apeluri / plan).']);
        }

        $rows = [];
        foreach ($operators as $i => $name) {
            $rows[] = [
                'operator_nume' => $name,
                'chaturi' => (int) ($chaturi[$i] ?? 0),
                'apeluri' => (int) ($apeluri[$i] ?? 0),
                'plan_individual' => $planuri[$i] ?? null,
            ];
        }

        try {
            $this->raportLunar->saveOperatorInputs($ym, $rows);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        return redirect()
            ->route('rapoarte.raport-lunar', ['month' => $ym])
            ->with('status', 'Chaturi, apeluri și planuri individuale au fost salvate. Procentele s-au actualizat.');
    }
}
