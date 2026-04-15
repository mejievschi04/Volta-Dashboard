<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Api\IstoricController;
use App\Http\Controllers\Api\KpiController;
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

            $kpiController = new KpiController();
            $resp1 = $kpiController->index(new Request(['month' => $luna1]));
            $resp2 = $kpiController->index(new Request(['month' => $luna2]));

            $data1 = json_decode($resp1->getContent(), true);
            $data2 = json_decode($resp2->getContent(), true);

            if (!$data1 || !$data2 || !($data1['success'] ?? false) || !($data2['success'] ?? false)) {
                return redirect()->back()->with('error', 'Nu am putut genera comparația PDF.');
            }

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

            $month1Label = \Carbon\Carbon::createFromFormat('Y-m', $luna1)->locale('ro')->translatedFormat('F Y');
            $month2Label = \Carbon\Carbon::createFromFormat('Y-m', $luna2)->locale('ro')->translatedFormat('F Y');

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

            return $pdf->stream('comparare_' . $luna1 . '_vs_' . $luna2 . '.pdf', ['Attachment' => true]);
        } catch (\Exception $e) {
            \Log::error('Eroare export comparare PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Eroare la generarea PDF: ' . $e->getMessage());
        }
    }
}

