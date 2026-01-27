<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DateOp;
use App\Models\Operator;
use SimpleXLSX;

class UploadOperatorVanzariController extends Controller
{
    public function uploadForm($operatorId)
    {
        $operator = Operator::findOrFail($operatorId);
        return view('operatori.upload', compact('operator'));
    }

    public function upload(Request $request, $operatorId)
    {
        \Log::info('Upload started', ['operatorId' => $operatorId, 'method' => $request->method()]);
        
        $operator = Operator::findOrFail($operatorId);
        \Log::info('Operator found', ['id' => $operatorId, 'name' => $operator->nume]);

        \Log::info('File in request', ['has_file' => $request->hasFile('file'), 'files' => array_keys($request->files->all())]);
        
        try {
            $validated = $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);
            \Log::info('File validation passed', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            \Log::error('Validation failed', ['errors' => $ve->errors()]);
            return redirect()->route('operatori.show', $operatorId)
                ->with('error', 'Validare eșuată: ' . implode(', ', array_reduce($ve->errors(), 'array_merge', [])));
        }

        try {
            $file = $request->file('file');
            \Log::info('File received', ['name' => $file->getClientOriginalName(), 'size' => $file->getSize()]);
            
            $filePath = $file->getPathname();
            $fileExtension = strtolower($file->getClientOriginalExtension());
            \Log::info('File info', ['path' => $filePath, 'extension' => $fileExtension]);
            
            $rowCount = 0;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];
            $totalSumaFara = 0.0;
            $totalSumaCu = 0.0;
            $totalProfit = 0.0;
            $totalNrVanzari = 0;

            // Parse file based on extension
            $rows = [];
            if ($fileExtension === 'csv') {
                \Log::info('Parsing as CSV');
                $rows = array_map('str_getcsv', file($filePath));
            } elseif ($fileExtension === 'xlsx' || $fileExtension === 'xls') {
                // Use SimpleXLSX for Excel files
                try {
                    \Log::info('Parsing as Excel with SimpleXLSX');
                    // Load SimpleXLSX library
                    require_once app_path('Libraries/SimpleXLSX.php');
                    
                    $xlsx = SimpleXLSX::parse($filePath);
                    if ($xlsx !== false) {
                        $rows = $xlsx->rows();
                        \Log::info('Excel parsed successfully', ['rows_count' => count($rows)]);
                    } else {
                        throw new \Exception('Nu se poate citi fișierul Excel');
                    }
                } catch (\Exception $e) {
                    \Log::warning('Excel parse failed, trying CSV fallback', ['error' => $e->getMessage()]);
                    // Fallback: try to read as CSV
                    try {
                        $rows = array_map('str_getcsv', file($filePath));
                        \Log::info('CSV fallback succeeded', ['rows_count' => count($rows)]);
                    } catch (\Exception $csvError) {
                        throw new \Exception('Eroare la citire: ' . $e->getMessage());
                    }
                }
            } else {
                throw new \Exception('Format fișier nesuportat: ' . $fileExtension);
            }

            if (empty($rows)) {
                throw new \Exception('Fișierul este gol sau nu poate fi citit');
            }
            
            \Log::info('Processing rows', ['total_rows' => count($rows)]);

            // Iterate through rows starting from row 2 (skip header)
            for ($i = 1; $i < count($rows); $i++) {
                $rowCount++;
                $rowData = $rows[$i];

                // Skip empty rows (check if date is empty OR all numeric columns are empty)
                $col0Empty = empty($rowData[0]);
                $col2Empty = empty(trim($rowData[2] ?? ''));
                $col3Empty = empty(trim($rowData[3] ?? ''));
                $col4Empty = empty(trim($rowData[4] ?? ''));
                
                if ($col0Empty || ($col2Empty && $col3Empty && $col4Empty)) {
                    \Log::debug("Row $rowCount skipped (empty)");
                    continue;
                }

                // Log raw row data for debugging
                \Log::debug("Row $rowCount raw data", [
                    'col0' => $rowData[0] ?? 'MISSING',
                    'col1' => $rowData[1] ?? 'MISSING',
                    'col2' => $rowData[2] ?? 'MISSING',
                    'col3' => $rowData[3] ?? 'MISSING',
                    'col4' => $rowData[4] ?? 'MISSING',
                ]);

                try {
                    $data = $this->parseRowData($rowData, $operatorId, $rowCount);
                    \Log::debug("Row $rowCount parsed", ['data' => $data]);
                    
                    if ($data) {
                        // Check if record exists
                        $exists = DateOp::where('operator_id', $operatorId)
                            ->where('data', $data['data'])
                            ->first();

                        if ($exists) {
                            // Accumulate into existing record (sum columns)
                            $exists->suma_fara_tva = floatval($exists->suma_fara_tva) + floatval($data['suma_fara_tva'] ?? 0);
                            $exists->suma_cu_tva = floatval($exists->suma_cu_tva) + floatval($data['suma_cu_tva'] ?? 0);
                            $exists->profit = floatval($exists->profit) + floatval($data['profit'] ?? 0);
                            $exists->nr_vanzari = intval($exists->nr_vanzari) + intval($data['nr_vanzari'] ?? 0);
                            $exists->save();
                            \Log::debug("Row $rowCount accumulated into existing record", ['new_totals' => [
                                'suma_fara_tva' => $exists->suma_fara_tva,
                                'suma_cu_tva' => $exists->suma_cu_tva,
                                'profit' => $exists->profit,
                                'nr_vanzari' => $exists->nr_vanzari,
                            ]]);
                        } else {
                            // Create new record
                            DateOp::create($data);
                            \Log::debug("Row $rowCount created new record");
                        }
                        // Accumulate totals
                        $totalSumaFara += floatval($data['suma_fara_tva'] ?? 0);
                        $totalSumaCu += floatval($data['suma_cu_tva'] ?? 0);
                        $totalProfit += floatval($data['profit'] ?? 0);
                        $totalNrVanzari += intval($data['nr_vanzari'] ?? 0);

                        $successCount++;
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $errorMsg = "Row {$rowCount}: " . $e->getMessage();
                    $errors[] = $errorMsg;
                    \Log::warning($errorMsg);
                }
            }

            \Log::info('Upload completed', ['success' => $successCount, 'errors' => $errorCount]);

            // Format totals in Romanian-style number format
            $fmt = function ($v) {
                return number_format((float)$v, 2, ',', ' ');
            };

            \Log::info('Upload totals', ['total_suma_fara' => $totalSumaFara, 'total_suma_cu' => $totalSumaCu, 'total_profit' => $totalProfit, 'total_nr_vanzari' => $totalNrVanzari]);

            $message = "Procesare completă! Rânduri citite: {$rowCount}, Înregistrări adăugate/actualizate: {$successCount}";
            $message .= ", Total Suma fără TVA: {$fmt($totalSumaFara)}";
            $message .= ", Total Suma cu TVA: {$fmt($totalSumaCu)}";
            $message .= ", Total Profit: {$fmt($totalProfit)}";
            $message .= ", Total Nr. Vânzări: {$totalNrVanzari}";
            if ($errorCount > 0) {
                $message .= ", Erori: {$errorCount}";
                if (count($errors) > 0) {
                    $message .= " - " . implode("; ", array_slice($errors, 0, 3));
                }
            }

            if ($successCount == 0 && $errorCount > 0) {
                return redirect()->route('operatori.show', $operatorId)
                    ->with('error', $message);
            }

            return redirect()->route('operatori.show', $operatorId)
                ->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Upload error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'operator_id' => $operatorId
            ]);
            
            return redirect()->route('operatori.show', $operatorId)
                ->with('error', 'Eroare la procesarea fișierului: ' . $e->getMessage());
        }
    }

    private function parseRowData($rowData, $operatorId, $rowNum)
    {
        // Flexible parser that supports both 4-column and 5-column layouts
        // Common layouts:
        // 4 cols: [data, suma_fara_tva, suma_cu_tva, profit]
        // 5 cols: [data, comenzi, suma_fara_tva, profit, nr_vanzari] OR
        //         [data, suma_fara_tva, comenzi, profit, nr_vanzari]

        // Normalize row length
        while (count($rowData) < 5) {
            $rowData[] = null;
        }

        // Trim all columns
        $cols = array_map(function ($v) {
            return is_null($v) ? '' : trim($v);
        }, $rowData);

        // ===== COLUMN 0: DATA =====
        $dataRaw = $cols[0] ?? '';
        if (empty($dataRaw)) {
            throw new \Exception('Data este obligatorie (coloana 1)');
        }
        $data = $this->parseDate($dataRaw);
        if (!$data) {
            throw new \Exception('Format dată invalid în coloana 1: ' . $dataRaw);
        }

        // Determine column types more robustly (amount | int | empty)
        $determineType = function ($s) {
            $s = trim((string)$s);
            if ($s === '') return 'empty';
            // pure integer (no separators, only digits)
            if (preg_match('/^-?\d+$/', $s)) return 'int';
            // if looks like amount via previous heuristic
            if ($this->looksLikeAmount($s)) return 'amount';
            // if after removing letters and spaces we have decimals or >=3 digits -> amount
            $tmp = $s;
            // remove currency letters and common words
            $tmp = preg_replace('/[A-Za-z\p{L}]+/u', '', $tmp);
            // remove unicode spaces
            $tmp = preg_replace('/\p{Z}/u', '', $tmp);
            $digits = preg_replace('/[^0-9]/', '', $tmp);
            if (strlen($digits) >= 3 || preg_match('/[.,]\d{1,2}$/', $tmp)) return 'amount';
            return 'unknown';
        };

        $colTypes = [
            1 => $determineType($cols[1] ?? ''),
            2 => $determineType($cols[2] ?? ''),
            3 => $determineType($cols[3] ?? ''),
            4 => $determineType($cols[4] ?? ''),
        ];

        // Default values
        $sumaFaraRaw = '';
        $sumaCuRaw = '';
        $profitRaw = '';
        $nrVanzariRaw = '';
        // Mapping rules (ordered):
        // 1) If col1..col3 are amounts -> [suma_fara, suma_cu, profit]
        if ($colTypes[1] === 'amount' && $colTypes[2] === 'amount' && $colTypes[3] === 'amount') {
            $sumaFaraRaw = $cols[1];
            $sumaCuRaw = $cols[2];
            $profitRaw = $cols[3];
            $nrVanzariRaw = $cols[4] ?? '';
            $chosenMapping = 'amount-amount-amount';
        }
        // 2) If pattern int, amount, amount -> [nr_vanzari, suma_fara, suma_cu]
        elseif ($colTypes[1] === 'int' && $colTypes[2] === 'amount' && $colTypes[3] === 'amount') {
            $nrVanzariRaw = $cols[1];
            $sumaFaraRaw = $cols[2];
            $sumaCuRaw = $cols[3];
            $profitRaw = '';
            $chosenMapping = 'int-amount-amount';
        }
        // 3) If pattern int, amount, amount, int (duplicate counts) -> prefer first int as nr_vanzari
        elseif ($colTypes[1] === 'int' && $colTypes[2] === 'amount' && $colTypes[3] === 'amount' && $colTypes[4] === 'int') {
            $nrVanzariRaw = $cols[1];
            $sumaFaraRaw = $cols[2];
            $sumaCuRaw = $cols[3];
            $profitRaw = '';
            $chosenMapping = 'int-amount-amount-int';
        }
        // 4) If col1 not amount and col2..col3 are amounts and col4 is int -> col4 = nr_vanzari
        elseif ($colTypes[1] !== 'amount' && $colTypes[2] === 'amount' && $colTypes[3] === 'amount' && $colTypes[4] === 'int') {
            $nrVanzariRaw = $cols[4];
            $sumaFaraRaw = $cols[2];
            $sumaCuRaw = $cols[3];
            $profitRaw = '';
            $chosenMapping = 'col4-int-amount-amount';
        }
        // 5) If col1 int and col2 amount and col3 not amount -> [nr_vanzari, suma_fara, profit]
        elseif ($colTypes[1] === 'int' && $colTypes[2] === 'amount' && $colTypes[3] !== 'amount') {
            $nrVanzariRaw = $cols[1];
            $sumaFaraRaw = $cols[2];
            $profitRaw = $cols[3];
            $sumaCuRaw = $cols[4] ?? '';
            $chosenMapping = 'int-amount-profit';
        }
        // Fallback: try conventional mapping [suma_fara, suma_cu, profit]
        else {
            $sumaFaraRaw = $cols[1];
            $sumaCuRaw = $cols[2];
            $profitRaw = $cols[3];
            $nrVanzariRaw = $cols[4] ?? '';
            $chosenMapping = 'fallback';
        }

        \Log::debug("Row {$rowNum} mapping decision", ['types' => $colTypes, 'chosen' => $chosenMapping, 'raw' => [$cols[1], $cols[2], $cols[3], $cols[4]]]);

        // Normalize and parse numbers
        $sumaFara = 0.0;
        $sumaCu = 0.0;
        $profit = 0.0;
        $nrVanzari = 1;

        if ($sumaFaraRaw !== '') {
            $sumaFara = floatval($this->normalizeNumber($sumaFaraRaw));
        }

        if ($sumaCuRaw !== '') {
            $sumaCu = floatval($this->normalizeNumber($sumaCuRaw));
        }

        if ($profitRaw !== '') {
            $profit = floatval($this->normalizeNumber($profitRaw));
        }

        if ($nrVanzariRaw !== '') {
            // remove decimals if present, keep integer part
            $nrVanzari = intval(floatval($this->normalizeNumber($nrVanzariRaw)));
            if ($nrVanzari <= 0) $nrVanzari = 1;
        }

        // If suma_cu not provided or zero, calculate as suma_fara + profit
        if (empty($sumaCu) && ($sumaFara !== 0 || $profit !== 0)) {
            $sumaCu = $sumaFara + $profit;
        }

        \Log::debug("Row {$rowNum} parsed successfully", [
            'data' => $data,
            'suma_fara_tva' => $sumaFara,
            'suma_cu_tva' => $sumaCu,
            'profit' => $profit,
            'nr_vanzari' => $nrVanzari,
        ]);

        return [
            'operator_id' => $operatorId,
            'data' => $data,
            'suma_fara_tva' => $sumaFara,
            'suma_cu_tva' => $sumaCu,
            'profit' => $profit,
            'nr_vanzari' => $nrVanzari,
        ];
    }

    private function normalizeNumber($numStr)
    {
        // Robust normalization for various formats seen in uploads
        // Steps:
        // - Trim and detect negative
        // - Remove all unicode space separators (NBSP, thin space, etc.)
        // - Remove all letters/currency words (LEI, RON, etc.)
        // - Decide decimal separator when both '.' and ',' appear
        // - Convert to dot decimal and return numeric string

        if (!is_string($numStr) && !is_numeric($numStr)) return 0;
        $s = trim((string)$numStr);
        if ($s === '') return 0;

        // Detect and keep sign
        $isNegative = false;
        if (strpos($s, '-') !== false) {
            $isNegative = true;
        }
        // Remove parentheses-style negatives
        $s = preg_replace('/^[\(]+|[\)]+$/', '', $s);

        // Remove letters (currency words) and common non-numeric symbols except dot/comma/minus
        // Keep ., - and digits and spaces for now
        $s = preg_replace('/[^0-9\.,\-\p{Z}]/u', '', $s);

        // Remove all unicode spaces (including NBSP, thin space, etc.)
        $s = preg_replace('/\p{Z}/u', '', $s);

        if ($s === '' || $s === '-' || $s === ',') return 0;

        // If both dot and comma exist, try to infer which is decimal by position
        $hasDot = strpos($s, '.') !== false;
        $hasComma = strpos($s, ',') !== false;

        if ($hasDot && $hasComma) {
            $lastDot = strrpos($s, '.');
            $lastComma = strrpos($s, ',');
            if ($lastDot > $lastComma) {
                // dot is decimal separator -> remove all commas
                $s = str_replace(',', '', $s);
            } else {
                // comma is decimal separator -> remove dots and replace comma with dot
                $s = str_replace('.', '', $s);
                $s = str_replace(',', '.', $s);
            }
        } elseif ($hasComma) {
            // Common European format: comma decimal
            $s = str_replace(',', '.', $s);
        } else {
            // dot only or no separator -> leave as-is (dot decimal or integer)
        }

        // Remove any remaining stray characters except digits, dot and minus
        $s = preg_replace('/[^0-9\.-]/', '', $s);

        if ($isNegative && strpos($s, '-') === false) {
            $s = '-' . $s;
        }

        return $s;
    }

    private function looksLikeAmount($str)
    {
        if (empty($str)) return false;
        // If contains currency text it's an amount
        if (preg_match('/\b(LEI|RON|€|EUR|lei)\b/i', $str)) return true;
        // If contains comma or dot followed by 1-2 decimals -> amount
        if (preg_match('/[.,]\d{1,2}\s*$/', $str)) return true;
        // If contains space or NBSP (or other separator) as thousands separator -> amount
        // Use \p{Zs} (any separator space) with /u to be Unicode-safe
        if (preg_match('/\d[\s\p{Zs}]\d{1,3}/u', $str)) return true;
        // If long number (>=4 digits) it's probably an amount
        $digits = preg_replace('/[^0-9]/', '', $str);
        if (strlen($digits) >= 4) return true;
        return false;
    }

    private function parseDate($dateRaw)
    {
        // Normalize whitespace
        $dateNorm = preg_replace('/\s+/', ' ', trim($dateRaw));

        // 1) Excel serial date (numeric) - SimpleXLSX may return numeric dates
        $maybeNumeric = str_replace([',', ' '], ['.', ''], $dateNorm);
        if (is_numeric($maybeNumeric)) {
            $serial = floatval($maybeNumeric);
            // Excel dates are > 30 typically; <30 are plain numbers
            if ($serial > 30) {
                // Excel 1900: offset 25569 days from Unix epoch
                $unix = ($serial - 25569) * 86400;
                try {
                    return \Carbon\Carbon::createFromTimestampUTC((int)round($unix))->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            }
        }
        // 2) Standard textual formats: YYYY-MM-DD or YYYY-MM
        if (strlen($dateNorm) == 10 && strpos($dateNorm, '-') > 0) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $dateNorm)->format('Y-m-d');
            } catch (\Exception $e) {
                // continue
            }
        }
        if (strlen($dateNorm) == 7 && strpos($dateNorm, '-') === 4) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m', $dateNorm)->startOfMonth()->format('Y-m-d');
            } catch (\Exception $e) {
                // continue
            }
        }

        // 3) Day + month name + year (e.g., "15 ianuarie 2025" or "15 January 2025")
        if (preg_match('/^\s*(\d{1,2})\s+([a-zăâîșțA-ZĂÂÎȘȚ]+)\s+(\d{4})\s*$/u', $dateNorm, $m)) {
            $day = intval($m[1]);
            $monthName = strtolower($m[2]);
            $year = intval($m[3]);
            $tmp = $this->parseMonthName($monthName . ' ' . $year);
            if ($tmp) {
                try {
                    $dt = \Carbon\Carbon::createFromFormat('Y-m-d', $tmp)->setDay(min($day, 28));
                    return $dt->format('Y-m-d');
                } catch (\Exception $e) {
                    return null;
                }
            }
        }

        // 4) Month name + year (e.g., "ianuarie 2025" or "January 2025")
        return $this->parseMonthName($dateNorm);
    }

    private function parseMonthName($dateStr)
    {
        // Map of Romanian month names to numbers
        $romanianMonths = [
            'ianuarie' => 1, 'ian' => 1,
            'februarie' => 2, 'feb' => 2,
            'martie' => 3, 'mar' => 3,
            'aprilie' => 4, 'apr' => 4,
            'mai' => 5,
            'iunie' => 6, 'iun' => 6,
            'iulie' => 7, 'iul' => 7,
            'august' => 8, 'aug' => 8,
            'septembrie' => 9, 'sep' => 9,
            'octombrie' => 10, 'oct' => 10,
            'noiembrie' => 11, 'nov' => 11,
            'decembrie' => 12, 'dec' => 12,
        ];

        // Map of English month names to numbers
        $englishMonths = [
            'january' => 1, 'jan' => 1,
            'february' => 2, 'feb' => 2,
            'march' => 3, 'mar' => 3,
            'april' => 4, 'apr' => 4,
            'may' => 5,
            'june' => 6, 'jun' => 6,
            'july' => 7, 'jul' => 7,
            'august' => 8, 'aug' => 8,
            'september' => 9, 'sep' => 9,
            'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'december' => 12, 'dec' => 12,
        ];

        // Combine both maps
        $allMonths = array_merge($romanianMonths, $englishMonths);

        // Try different separators and patterns
        $patterns = [
            '/^\s*([a-zăâîșțA-Z]+)\s+(\d{4})\s*$/u',  // "ianuarie 2025" or "January 2025"
            '/^\s*(\d{4})\s+([a-zăâîșțA-Z]+)\s*$/u',  // "2025 ianuarie"
            '/^\s*([a-zăâîșțA-Z]+)\s*-\s*(\d{4})\s*$/u', // "ianuarie - 2025"
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $dateStr, $matches)) {
                // Determine which is month and which is year
                $monthStr = strtolower(trim($matches[1]));
                $yearStr = $matches[2];
                
                // Check if first match is a number (year)
                if (is_numeric($monthStr)) {
                    $yearStr = $monthStr;
                    $monthStr = strtolower(trim($matches[2]));
                }
                
                // Get month number
                if (isset($allMonths[$monthStr])) {
                    $monthNum = $allMonths[$monthStr];
                    $year = intval($yearStr);
                    
                    // Convert to first day of month format YYYY-MM-DD
                    return \Carbon\Carbon::createFromDate($year, $monthNum, 1)->format('Y-m-d');
                }
            }
        }

        return null;
    }

    public function downloadTemplate()
    {
        $fileName = 'Template_Vanzari_Operator_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Create CSV content with correct structure: data, suma_fara_tva, suma_cu_tva, profit
        $csvContent = "data,suma_fara_tva,suma_cu_tva,profit\n";
        $csvContent .= "Ianuarie 2025,1000.50,1190.60,300.25\n";
        $csvContent .= "Ianuarie 2025,1500.00,1785.00,450.75\n";
        $csvContent .= "Februarie 2025,2000.00,2380.00,600.50\n";
        
        return response($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}

