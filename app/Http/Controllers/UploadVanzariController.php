<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vanzari;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

// Funcție helper pentru încărcarea clasei SimpleXLSX (în namespace global)
if (!function_exists('loadSimpleXLSX')) {
    function loadSimpleXLSX() {
        if (!class_exists('SimpleXLSX', false)) {
            $paths = [
                app_path('Libraries/SimpleXLSX.php'),
                base_path('app/Libraries/SimpleXLSX.php'),
                __DIR__ . '/../Libraries/SimpleXLSX.php'
            ];
            
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    if (class_exists('SimpleXLSX', false)) {
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }
}

class UploadVanzariController extends Controller
{
    public function upload(Request $request)
    {
        // Încarcă clasa SimpleXLSX dacă nu este deja încărcată
        if (!class_exists('SimpleXLSX', false)) {
            loadSimpleXLSX();
        }
        
        Log::info('UploadVanzariController: Upload început', [
            'has_file' => $request->hasFile('excel_file'),
            'replace_existing' => $request->has('replace_existing'),
            'simpleXLSX_loaded' => class_exists('SimpleXLSX', false)
        ]);

        // Verifică conexiunea la baza de date
        try {
            DB::connection('vanzari')->getPdo();
            Log::info('UploadVanzariController: Conexiune la baza de date OK');
        } catch (\Exception $e) {
            Log::error('UploadVanzariController: Eroare conexiune DB', [
                'error' => $e->getMessage()
            ]);
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Eroare la conexiunea la baza de date: ' . $e->getMessage());
        }

        // Verifică dacă extensia zip este activată
        if (!class_exists('ZipArchive')) {
            Log::error('UploadVanzariController: ZipArchive nu este disponibil');
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Extensia PHP ZipArchive nu este activată. Te rugăm să activezi extensia zip în php.ini.');
        }

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
        ]);

        $file = $request->file('excel_file');
        $replaceExisting = $request->has('replace_existing');
        
        Log::info('UploadVanzariController: Fișier validat', [
            'filename' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime' => $file->getMimeType()
        ]);

        // Verifică eroarea upload-ului
        if (!$file->isValid()) {
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Eroare la încărcarea fișierului: ' . $file->getError());
        }

        // Verifică tipul fișierului
        $fileExtension = strtolower($file->getClientOriginalExtension());
        if (!in_array($fileExtension, ['xlsx', 'xls'])) {
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Format fișier invalid. Se acceptă doar .xlsx sau .xls');
        }

        // Verifică dacă este fișier .xls (format vechi binar)
        $fileContent = file_get_contents($file->getRealPath(), false, null, 0, 8);
        $isOldXLS = ($fileExtension === 'xls' || substr($fileContent, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1");

        if ($isOldXLS) {
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Fișierele .xls (Excel 97-2003) nu sunt suportate. Te rugăm să convertești fișierul în .xlsx (Excel 2007+).');
        }

        try {
            // Asigură-te că directorul temp există
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                if (!mkdir($tempDir, 0755, true)) {
                    Log::error('UploadVanzariController: Nu s-a putut crea directorul temp', [
                        'path' => $tempDir
                    ]);
                    return redirect()->route('setari')
                        ->with('import_status', 'error')
                        ->with('import_message', 'Eroare: Nu s-a putut crea directorul temporar. Verifică permisiunile pentru storage/app.');
                }
            }
            
            // Salvează fișierul temporar direct
            $tempFileName = 'temp_vanzari_' . time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file->getClientOriginalName());
            $tempPath = $tempDir . '/' . $tempFileName;
            
            // Mută fișierul încărcat în locația temporară
            if (!$file->move($tempDir, $tempFileName)) {
                Log::error('UploadVanzariController: Nu s-a putut muta fișierul', [
                    'from' => $file->getRealPath(),
                    'to' => $tempPath
                ]);
                return redirect()->route('setari')
                    ->with('import_status', 'error')
                    ->with('import_message', 'Eroare: Nu s-a putut salva fișierul temporar. Verifică permisiunile pentru storage/app/temp.');
            }
            
            Log::info('UploadVanzariController: Fișier salvat temporar', [
                'temp_path' => $tempPath,
                'file_exists' => file_exists($tempPath),
                'file_size' => file_exists($tempPath) ? filesize($tempPath) : 0
            ]);

            // Verifică dacă fișierul există
            if (!file_exists($tempPath)) {
                Log::error('UploadVanzariController: Fișierul temporar nu există', [
                    'temp_path' => $tempPath
                ]);
                return redirect()->route('setari')
                    ->with('import_status', 'error')
                    ->with('import_message', 'Eroare: Fișierul temporar nu a putut fi creat. Verifică permisiunile pentru storage/app/temp.');
            }
            
            // Verifică din nou formatul înainte de parsare
            $fileHeader = @file_get_contents($tempPath, false, null, 0, 8);
            if ($fileHeader === false) {
                Log::error('UploadVanzariController: Nu s-a putut citi header-ul fișierului', [
                    'temp_path' => $tempPath
                ]);
                @unlink($tempPath);
                return redirect()->route('setari')
                    ->with('import_status', 'error')
                    ->with('import_message', 'Eroare: Nu s-a putut citi fișierul. Verifică că fișierul este valid.');
            }
            
            if (substr($fileHeader, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
                @unlink($tempPath);
                return redirect()->route('setari')
                    ->with('import_status', 'error')
                    ->with('import_message', 'Fișierul este în format .xls (Excel 97-2003) care nu este suportat.');
            } elseif (substr($fileHeader, 0, 4) === "PK\x03\x04") {
                // Este un fișier ZIP (format .xlsx)
                // Încarcă clasa SimpleXLSX dacă nu este deja încărcată
                if (!loadSimpleXLSX()) {
                    Log::error('UploadVanzariController: Clasa SimpleXLSX nu poate fi încărcată');
                    @unlink($tempPath);
                    return redirect()->route('setari')
                        ->with('import_status', 'error')
                        ->with('import_message', 'Eroare: Clasa SimpleXLSX nu a putut fi încărcată. Verifică că fișierul app/Libraries/SimpleXLSX.php există.');
                }
                
                // Folosește clasa globală SimpleXLSX (cu backslash pentru namespace global)
                $xlsx = \SimpleXLSX::parse($tempPath, false, true);
                
                Log::info('UploadVanzariController: Fișier Excel parsat', [
                    'success' => $xlsx !== false,
                    'temp_path' => $tempPath
                ]);
                
                if ($xlsx) {
                    // Verifică câte sheet-uri sunt disponibile
                    $sheetsCount = method_exists($xlsx, 'sheetsCount') ? $xlsx->sheetsCount() : 1;
                    $sheetNames = method_exists($xlsx, 'sheetNames') ? $xlsx->sheetNames() : ['Sheet1'];
                    
                    Log::info('UploadVanzariController: Sheet-uri găsite', [
                        'sheets_count' => $sheetsCount,
                        'sheet_names' => $sheetNames
                    ]);
                    
                    // Încearcă să citească din fiecare sheet până găsește date
                    $rows = [];
                    for ($sheetIndex = 0; $sheetIndex < $sheetsCount; $sheetIndex++) {
                        $sheetRows = $xlsx->rows($sheetIndex);
                        Log::info('UploadVanzariController: Sheet citit', [
                            'sheet_index' => $sheetIndex,
                            'rows_count' => count($sheetRows)
                        ]);
                        
                        if (!empty($sheetRows) && count($sheetRows) > 0) {
                            // Verifică dacă sheet-ul are date reale
                            $hasData = false;
                            foreach ($sheetRows as $testRow) {
                                if (!empty($testRow) && count(array_filter($testRow, function($cell) { 
                                    return !empty(trim((string)$cell)); 
                                })) > 0) {
                                    $hasData = true;
                                    break;
                                }
                            }
                            if ($hasData) {
                                $rows = $sheetRows;
                                Log::info('UploadVanzariController: Date găsite în sheet', [
                                    'sheet_index' => $sheetIndex,
                                    'rows_count' => count($rows)
                                ]);
                                break;
                            }
                        }
                    }
                    
                    // Dacă nu s-au găsit date în niciun sheet, folosește primul sheet
                    if (empty($rows)) {
                        $rows = $xlsx->rows(0);
                        Log::info('UploadVanzariController: Folosind primul sheet', [
                            'rows_count' => count($rows)
                        ]);
                    }

                    Log::info('UploadVanzariController: Procesare date', [
                        'total_rows' => count($rows),
                        'first_row_preview' => count($rows) > 0 ? array_slice($rows[0], 0, 4) : []
                    ]);

                    if (count($rows) > 1) {
                        $imported = 0;
                        $updated = 0;
                        $debugMessages = [];

                        // Determină dacă prima linie este header
                        $startRow = 0;
                        if (count($rows) > 0) {
                            $firstRow = $rows[0];
                            $firstCell = trim((string)($firstRow[0] ?? ''));
                            $firstCellLower = strtolower($firstCell);
                            
                            if (stripos($firstCellLower, 'dată') !== false || 
                                stripos($firstCellLower, 'date') !== false ||
                                stripos($firstCellLower, 'дата') !== false) {
                                $startRow = 1; // Skip header
                            }
                        }

                        // Dacă trebuie să înlocuim, colectăm toate datele din Excel înainte de procesare
                        $datesToDelete = [];
                        if ($replaceExisting) {
                            for ($i = $startRow; $i < count($rows); $i++) {
                                $row = $rows[$i];
                                $dateRaw = $row[0] ?? '';
                                
                                // Dacă este număr Excel (timestamp), convertim direct
                                if (is_numeric($dateRaw) && $dateRaw > 25569) {
                                    $dateRaw = date('Y-m-d', ($dateRaw - 25569) * 86400);
                                }
                                $dateStr = is_string($dateRaw) ? trim($dateRaw) : (is_numeric($dateRaw) ? (string)$dateRaw : '');
                                
                                if (empty($dateStr) || 
                                    stripos($dateStr, 'итого') !== false || 
                                    stripos($dateStr, 'total') !== false) {
                                    continue;
                                }
                                
                                $date = $this->parseDate($dateStr);
                                if ($date && strtotime($date) && !in_array($date, $datesToDelete)) {
                                    $datesToDelete[] = $date;
                                }
                            }
                            
                            // Șterge datele existente ÎNAINTE de a insera noile date
                            if (!empty($datesToDelete)) {
                                Vanzari::on('vanzari')->whereIn('data', $datesToDelete)->delete();
                            }
                        }

                        // Procesăm datele și le inserăm
                        for ($i = $startRow; $i < count($rows); $i++) {
                            $row = $rows[$i];
                            
                            // Extrage datele din coloane
                            // Structură așteptată: Data, Suma fără TVA, Suma cu TVA, Profit, Număr comenzi
                            $dateRaw = $row[0] ?? '';
                            $sumaFaraTvaRaw = $row[1] ?? '';
                            $sumaCuTvaRaw = $row[2] ?? '';
                            $profitRaw = $row[3] ?? '';
                            $nrVanzariRaw = $row[4] ?? ''; // Numărul de comenzi
                            
                            // Dacă este număr Excel (timestamp), convertim direct
                            if (is_numeric($dateRaw) && $dateRaw > 25569) {
                                $dateRaw = date('Y-m-d', ($dateRaw - 25569) * 86400);
                            }
                            $dateStr = is_string($dateRaw) ? trim($dateRaw) : (is_numeric($dateRaw) ? (string)$dateRaw : '');
                            
                            // Verifică dacă data este goală sau este linia "Итого" (Total)
                            if (empty($dateStr)) {
                                continue;
                            }
                            
                            if (stripos($dateStr, 'итого') !== false || stripos($dateStr, 'total') !== false) {
                                continue;
                            }
                            
                            // Convertim data
                            $date = $this->parseDate($dateStr);
                            if (!$date || !strtotime($date)) {
                                if ($imported + $updated < 3) {
                                    $debugMessages[] = "Linia " . ($i + 1) . ": Data invalidă (raw: '" . $dateStr . "')";
                                }
                                Log::warning('UploadVanzariController: Data invalidă', [
                                    'row' => $i + 1,
                                    'date_raw' => $dateStr,
                                    'date_parsed' => $date
                                ]);
                                continue;
                            }
                            
                            // Convertim numerele
                            $sumaFaraTva = $this->parseNumber($sumaFaraTvaRaw);
                            $sumaCuTva = $this->parseNumber($sumaCuTvaRaw);
                            $profit = $this->parseNumber($profitRaw);
                            $nrVanzari = $this->parseInteger($nrVanzariRaw); // Numărul de comenzi
                            
                            Log::debug('UploadVanzariController: Date procesate', [
                                'row' => $i + 1,
                                'date' => $date,
                                'suma_fara_tva' => $sumaFaraTva,
                                'suma_cu_tva' => $sumaCuTva,
                                'profit' => $profit,
                                'nr_vanzari' => $nrVanzari
                            ]);
                            
                            // Inserează sau actualizează în baza de date
                            try {
                                // Verifică dacă există deja o înregistrare pentru această dată
                                $existing = Vanzari::on('vanzari')->where('data', $date)->first();
                                
                                if ($existing) {
                                    // Actualizează înregistrarea existentă
                                    $existing->suma_fara_tva = $sumaFaraTva;
                                    $existing->suma_cu_tva = $sumaCuTva;
                                    $existing->profit = $profit;
                                    $existing->nr_vanzari = $nrVanzari;
                                    $saved = $existing->save();
                                    $updated++;
                                    
                                    Log::info('UploadVanzariController: Actualizare în DB', [
                                        'date' => $date,
                                        'id' => $existing->id,
                                        'saved' => $saved
                                    ]);
                                } else {
                                    // Creează o nouă înregistrare
                                    $vanzare = new Vanzari();
                                    $vanzare->setConnection('vanzari');
                                    $vanzare->data = $date;
                                    $vanzare->suma_fara_tva = $sumaFaraTva;
                                    $vanzare->suma_cu_tva = $sumaCuTva;
                                    $vanzare->profit = $profit;
                                    $vanzare->nr_vanzari = $nrVanzari;
                                    $saved = $vanzare->save();
                                    $imported++;
                                    
                                    Log::info('UploadVanzariController: Inserare în DB', [
                                        'date' => $date,
                                        'id' => $vanzare->id ?? null,
                                        'saved' => $saved
                                    ]);
                                }
                            } catch (\Exception $e) {
                                Log::error('Eroare la import vânzări: ' . $e->getMessage(), [
                                    'date' => $date,
                                    'row' => $i + 1,
                                    'file' => $e->getFile(),
                                    'line' => $e->getLine(),
                                    'trace' => $e->getTraceAsString()
                                ]);
                                $debugMessages[] = "Linia " . ($i + 1) . ": Eroare DB: " . $e->getMessage();
                                continue;
                            }
                        }
                        
                        // Șterge fișierul temporar
                        @unlink($tempPath);
                        
                        Log::info('UploadVanzariController: Procesare finalizată', [
                            'imported' => $imported,
                            'updated' => $updated,
                            'debug_messages_count' => count($debugMessages)
                        ]);
                        
                        if ($imported == 0 && $updated == 0) {
                            $errorMsg = 'Nu s-au putut importa date de vânzări.';
                            if (!empty($debugMessages)) {
                                $errorMsg .= '<br><br>Probleme identificate:<br>' . implode('<br>', array_slice($debugMessages, 0, 20));
                            } else {
                                $errorMsg .= '<br><br>Nu s-au găsit date valide în fișierul Excel. Verifică formatul fișierului.';
                            }
                            Log::warning('UploadVanzariController: Niciun record importat', [
                                'debug_messages' => $debugMessages
                            ]);
                            return redirect()->route('setari')
                                ->with('import_status', 'error')
                                ->with('import_message', $errorMsg);
                        } else {
                            $success = "Import reușit! $imported înregistrări inserate, $updated actualizate.";
                            Log::info('UploadVanzariController: Import reușit', [
                                'imported' => $imported,
                                'updated' => $updated
                            ]);
                            return redirect()->route('setari')
                                ->with('import_status', 'success')
                                ->with('import_message', $success);
                        }
                    } else {
                        @unlink($tempPath);
                        return redirect()->route('setari')
                            ->with('import_status', 'error')
                            ->with('import_message', 'Fișierul Excel este gol sau nu a putut fi citit.');
                    }
                } else {
                    @unlink($tempPath);
                    return redirect()->route('setari')
                        ->with('import_status', 'error')
                        ->with('import_message', 'Nu s-a putut citi fișierul Excel. Verifică că fișierul este un .xlsx valid.');
                }
            } else {
                @unlink($tempPath);
                return redirect()->route('setari')
                    ->with('import_status', 'error')
                    ->with('import_message', 'Format fișier necunoscut. Fișierul trebuie să fie în format .xlsx (Excel 2007+).');
            }
        } catch (\Exception $e) {
            Log::error('Eroare la procesarea Excel: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            if (isset($tempPath) && file_exists($tempPath)) {
                @unlink($tempPath);
            }
            return redirect()->route('setari')
                ->with('import_status', 'error')
                ->with('import_message', 'Eroare la procesarea fișierului: ' . $e->getMessage() . ' (Verifică log-urile pentru detalii)');
        }
    }

    private function parseDate($dateStr)
    {
        // Dacă este număr (timestamp Excel), convertește-l
        if (is_numeric($dateStr) && $dateStr > 25569) {
            return date('Y-m-d', ($dateStr - 25569) * 86400);
        }
        
        // Elimină spațiile și convertim la string
        $dateStr = trim((string)$dateStr);
        if (empty($dateStr)) {
            return null;
        }
        
        // Format: DD.MM.YYYY HH:MM:SS sau DD.MM.YYYY (format românesc)
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?$/', $dateStr, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }
        
        // Format: YYYY-MM-DD
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $dateStr, $matches)) {
            return $dateStr;
        }
        
        // Format: DD-MM-YYYY
        if (preg_match('/^(\d{1,2})-(\d{1,2})-(\d{4})$/', $dateStr, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = $matches[3];
            return "$year-$month-$day";
        }
        
        // Încearcă strtotime ca ultimă opțiune
        $timestamp = strtotime($dateStr);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }
        
        return null;
    }

    private function parseNumber($value)
    {
        // Convertim întâi la string
        $value = trim((string)$value);
        
        // Dacă este gol sau NULL, returnează 0
        if (empty($value) && $value !== '0') {
            return 0;
        }
        
        // Verifică dacă este deja un număr valid (fără spații și virgule)
        if (is_numeric($value) && strpos($value, ',') === false && strpos($value, ' ') === false) {
            return floatval($value);
        }
        
        // Format românesc/european: spațiu pentru mii, virgulă pentru zecimale
        // Elimină TOATE spațiile (separator de mii)
        $value = str_replace(' ', '', $value);
        
        // Înlocuiește virgula cu punct (separator zecimal)
        $value = str_replace(',', '.', $value);
        
        // Elimină caracterele care nu sunt numere, punct sau minus
        $value = preg_replace('/[^0-9\.\-]/', '', $value);
        
        // Convertim la float
        return floatval($value);
    }

    private function parseInteger($value)
    {
        // Convertim întâi la string
        $value = trim((string)$value);
        
        // Dacă este gol sau NULL, returnează 0
        if (empty($value) && $value !== '0') {
            return 0;
        }
        
        // Dacă este deja un număr întreg, returnează-l
        if (is_numeric($value) && strpos($value, '.') === false && strpos($value, ',') === false) {
            return intval($value);
        }
        
        // Elimină spațiile (separator de mii)
        $value = str_replace(' ', '', $value);
        
        // Elimină virgula sau punctul dacă există (pentru numere zecimale, luăm partea întreagă)
        $value = preg_replace('/[^0-9\-]/', '', $value);
        
        // Convertim la întreg
        return intval($value);
    }
}
