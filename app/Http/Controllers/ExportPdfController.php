<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\IstoricController;
use App\Support\LunaRomana;
use Dompdf\Dompdf;
use Dompdf\Options;

class ExportPdfController extends Controller
{
    public function exportIstoric(Request $request)
    {
        // Curăță orice output buffer înainte
        while (ob_get_level()) {
            ob_end_clean();
        }
        
        try {
            // Obține datele filtrate
            $istoricController = new IstoricController();
            $response = $istoricController->index($request);
            $data = json_decode($response->getContent(), true);
            
            if (!$data['success']) {
                return redirect()->back()->with('error', 'Eroare la încărcarea datelor');
            }
            
            $istoricData = $data['data'];
            
            // Aplică filtrele manual dacă sunt trimise
            $an = $request->get('an', '');
            $luna = $request->get('luna', '');
            $search = $request->get('search', '');
            
            if ($an || $luna || $search) {
                $istoricData = array_filter($istoricData, function($item) use ($an, $luna, $search) {
                    $matchAn = !$an || $item['an'] == $an;
                    $matchLuna = !$luna || $item['luna_num'] == $luna;
                    $matchSearch = !$search || stripos($item['luna_label'], $search) !== false;
                    return $matchAn && $matchLuna && $matchSearch;
                });
                $istoricData = array_values($istoricData); // Reindex array
            }
            
            // Calculează statistici
            $totalVanzari = array_sum(array_column($istoricData, 'vanzari_luna'));
            $totalProfit = array_sum(array_column($istoricData, 'profit'));
            $totalComenzi = array_sum(array_column($istoricData, 'comenzi'));
            $avgConversie = count($istoricData) > 0 
                ? round(array_sum(array_column($istoricData, 'conversie')) / count($istoricData), 2)
                : 0;
            
            // Configurează DomPDF
            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false, // Dezactivăm remote pentru siguranță
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), base_path()],
                'tempDir' => sys_get_temp_dir()
            ]);
            
            $pdf = new Dompdf($options);
            
            // Generează HTML pentru PDF
            try {
                $html = view('rapoarte.pdf.istoric', [
                    'istoricData' => $istoricData,
                    'totalVanzari' => $totalVanzari,
                    'totalProfit' => $totalProfit,
                    'totalComenzi' => $totalComenzi,
                    'avgConversie' => $avgConversie,
                    'an' => $an,
                    'luna' => $luna,
                    'search' => $search,
                    'logoPath' => public_path('images/volta-logo.png')
                ])->render();
            } catch (\Exception $viewError) {
                \Log::error('Eroare generare view: ' . $viewError->getMessage());
                throw new \Exception('Eroare la generarea view-ului: ' . $viewError->getMessage());
            }
            
            // Curăță HTML-ul de caractere problematice
            $html = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $html);
            
            // Încarcă HTML în PDF
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'landscape');
            
            // Render PDF
            $pdf->render();
            
            $filename = 'istoric_' . date('Y-m-d_His') . '.pdf';
            
            // Returnează PDF-ul folosind stream() - aceasta gestionează totul corect
            return $pdf->stream($filename, ['Attachment' => true]);
            
        } catch (\Exception $e) {
            \Log::error('Eroare export PDF: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Eroare la generarea PDF: ' . $e->getMessage());
        }
    }

    public function exportComparare(Request $request)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        try {
            $luna1 = (string) $request->get('luna1', date('Y-m'));
            $luna2 = (string) $request->get('luna2', date('Y-m', strtotime('-1 month')));
            [$range1Start, $range1End] = $this->normalizeComparareRange(
                (string) $request->get('luna1_start', $luna1),
                (string) $request->get('luna1_end', $luna1)
            );
            [$range2Start, $range2End] = $this->normalizeComparareRange(
                (string) $request->get('luna2_start', $luna2),
                (string) $request->get('luna2_end', $luna2)
            );

            $istoricController = new IstoricController();
            $istoricResponse = $istoricController->index(new Request());
            $istoricPayload = json_decode($istoricResponse->getContent(), true);

            if (!$istoricPayload || !($istoricPayload['success'] ?? false)) {
                return redirect()->back()->with('error', 'Nu am putut genera comparația PDF.');
            }

            $istoricRows = $istoricPayload['data'] ?? [];
            $data1 = $this->aggregateComparareRange($istoricRows, $range1Start, $range1End);
            $data2 = $this->aggregateComparareRange($istoricRows, $range2Start, $range2End);

            $rows = [
                ['key' => 'plan_luna', 'label' => 'Plan', 'suffix' => 'MDL'],
                ['key' => 'vanzari_luna', 'label' => 'Vânzări fără TVA', 'suffix' => 'MDL'],
                ['key' => 'vanzari_cu_tva', 'label' => 'Vânzări cu TVA', 'suffix' => 'MDL'],
                ['key' => 'profit', 'label' => 'Profit', 'suffix' => 'MDL'],
                ['key' => 'progres_plan', 'label' => 'Progres Plan', 'suffix' => '%'],
                ['key' => 'prognoza_plan', 'label' => 'Prognoză Plan', 'suffix' => 'MDL'],
                ['key' => 'prognoza_plan_procent', 'label' => 'Prognoză Plan %', 'suffix' => '%'],
                ['key' => 'comenzi', 'label' => 'Comenzi', 'suffix' => ''],
                ['key' => 'comenzi_zi', 'label' => 'Comenzi/Zi', 'suffix' => ''],
                ['key' => 'cec_mediu', 'label' => 'CEC mediu', 'suffix' => 'MDL'],
                ['key' => 'total_livrari_luna', 'label' => 'Total livrări lună', 'suffix' => ''],
                ['key' => 'pickup', 'label' => 'Pickup', 'suffix' => ''],
                ['key' => 'sesiuni', 'label' => 'Sesiuni', 'suffix' => ''],
                ['key' => 'conversie', 'label' => 'Conversie', 'suffix' => '%'],
            ];

            $month1Label = $this->comparareRangeLabel($range1Start, $range1End);
            $month2Label = $this->comparareRangeLabel($range2Start, $range2End);

            $options = new Options();
            $options->set([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => false,
                'defaultFont' => 'DejaVu Sans',
                'chroot' => [public_path(), base_path()],
                'tempDir' => sys_get_temp_dir()
            ]);

            $pdf = new Dompdf($options);
            $html = view('rapoarte.pdf.comparare', [
                'rows' => $rows,
                'data1' => $data1,
                'data2' => $data2,
                'month1Label' => $month1Label,
                'month2Label' => $month2Label,
                'logoPath' => public_path('images/volta-logo.png')
            ])->render();

            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'landscape');
            $pdf->render();

            $filename = 'comparare_' . $range1Start . '_' . $range1End . '_vs_' . $range2Start . '_' . $range2End . '.pdf';

            return $pdf->stream($filename, ['Attachment' => true]);
        } catch (\Exception $e) {
            \Log::error('Eroare export comparare PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Eroare la generarea PDF: ' . $e->getMessage());
        }
    }

    private function normalizeComparareRange(string $start, string $end): array
    {
        $start = preg_match('/^\d{4}-\d{2}$/', $start) ? $start : date('Y-m');
        $end = preg_match('/^\d{4}-\d{2}$/', $end) ? $end : $start;

        return $start <= $end ? [$start, $end] : [$end, $start];
    }

    private function comparareRangeLabel(string $start, string $end): string
    {
        if ($start === $end) {
            return LunaRomana::labelFromYm($start);
        }

        return LunaRomana::labelFromYm($start) . ' - ' . LunaRomana::labelFromYm($end);
    }

    private function aggregateComparareRange(array $istoricRows, string $start, string $end): array
    {
        $selectedRows = array_values(array_filter($istoricRows, function ($row) use ($start, $end) {
            $month = (string) ($row['luna'] ?? '');
            return $month >= $start && $month <= $end;
        }));

        $sum = function (string $key) use ($selectedRows): float {
            return array_reduce($selectedRows, function (float $carry, array $row) use ($key): float {
                return $carry + (float) ($row[$key] ?? 0);
            }, 0.0);
        };

        $plan = $sum('plan_luna');
        $vanzari = $sum('vanzari_luna');
        $prognozaPlan = $sum('prognoza_plan');
        $comenzi = $sum('comenzi');
        $sesiuni = $sum('sesiuni');
        $zileInterval = max(1, $this->daysInMonthRange($start, $end));

        return [
            'success' => true,
            'plan_luna' => $plan,
            'vanzari_luna' => $vanzari,
            'vanzari_cu_tva' => $sum('vanzari_cu_tva'),
            'profit' => $sum('profit'),
            'progres_plan' => $plan > 0 ? round(($vanzari / $plan) * 100, 2) : 0,
            'diferenta_plan' => $vanzari - $plan,
            'prognoza_plan' => $prognozaPlan,
            'prognoza_plan_procent' => $plan > 0 ? round(($prognozaPlan / $plan) * 100, 2) : 0,
            'comenzi' => $comenzi,
            'comenzi_zi' => round($comenzi / $zileInterval, 1),
            'cec_mediu' => $comenzi > 0 ? round($vanzari / $comenzi, 2) : 0,
            'total_livrari_luna' => $sum('total_livrari_luna'),
            'pickup' => $sum('pickup'),
            'sesiuni' => $sesiuni,
            'conversie' => $sesiuni > 0 ? round(($comenzi / $sesiuni) * 100, 2) : 0,
        ];
    }

    private function daysInMonthRange(string $start, string $end): int
    {
        $days = 0;
        $current = \DateTime::createFromFormat('Y-m-d', $start . '-01');
        $last = \DateTime::createFromFormat('Y-m-d', $end . '-01');

        while ($current && $last && $current <= $last) {
            $days += (int) $current->format('t');
            $current->modify('+1 month');
        }

        return $days;
    }
}
