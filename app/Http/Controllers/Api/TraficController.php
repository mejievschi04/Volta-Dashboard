<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrafficSource;
use App\Support\DbDate;
use Illuminate\Support\Facades\DB;

class TraficController extends Controller
{
    public function index(Request $request)
    {
        $luna = $request->get('month');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        try {
            $query = TrafficSource::query();
            
            // Dacă avem start_date și end_date, folosim perioada
            if ($startDate && $endDate) {
                $query->whereBetween('date', [$startDate, $endDate]);
            } elseif ($luna) {
                // Altfel folosim luna
                $query->whereRaw(DbDate::month('date') . ' = ?', [$luna]);
            } else {
                // Implicit: luna curentă
                $luna = date('Y-m');
                $query->whereRaw(DbDate::month('date') . ' = ?', [$luna]);
            }
            
            // Date pentru grafic de tendință - grupate pe perioadă și sursă
            $data = [
                'labels' => [],
                'datasets' => [
                    'total' => [],
                    'google' => [],
                    'google_cpc' => [],
                    'direct' => [],
                    'yandex' => [],
                    'other' => []
                ]
            ];
            
            if ($startDate && $endDate) {
                // Pentru perioade mai mari, grupăm pe săptămâni sau luni
                $start = new \DateTime($startDate);
                $end = new \DateTime($endDate);
                $diff = $start->diff($end);
                $daysDiff = $diff->days;
                
                if ($daysDiff <= 90) {
                    // Pentru perioade până la 3 luni, grupăm pe săptămâni
                    $rows = (clone $query)->selectRaw('
                        source,
                        ' . DbDate::yearWeek('date') . ' as week,
                        MIN(date) as week_start_date,
                        SUM(visits) as total
                    ')
                    ->groupBy('source', DB::raw(DbDate::yearWeek('date')))
                    ->orderBy('week', 'ASC')
                    ->get();
                    
                    $weeks = [];
                    $weekData = [];
                    
                    // Colectăm toate săptămânile și datele
                    foreach ($rows as $row) {
                        $weekLabel = date('d.m.Y', strtotime($row->week_start_date));
                        if (!in_array($weekLabel, $weeks)) {
                            $weeks[] = $weekLabel;
                        }
                        $source = $row->source;
                        if (!isset($weekData[$weekLabel])) {
                            $weekData[$weekLabel] = [];
                        }
                        $weekData[$weekLabel][$source] = (int)$row->total;
                    }
                    
                    // Populăm datele pentru toate sursele și toate săptămânile
                    foreach ($weeks as $weekIndex => $week) {
                        foreach (array_keys($data['datasets']) as $source) {
                            if ($source !== 'total') {
                                $data['datasets'][$source][$weekIndex] = $weekData[$week][$source] ?? 0;
                            }
                        }
                        // Calculăm totalul pentru fiecare săptămână (excludem 'total' pentru a evita dublarea)
                        $weekTotal = 0;
                        foreach ($weekData[$week] ?? [] as $source => $value) {
                            if ($source !== 'total') {
                                $weekTotal += $value;
                            }
                        }
                        $data['datasets']['total'][$weekIndex] = $weekTotal;
                    }
                    
                    $data['labels'] = $weeks;
                } else {
                    // Pentru perioade mai mari, grupăm pe luni
                    $rows = (clone $query)->selectRaw('
                        source,
                        ' . DbDate::month('date') . ' as month,
                        ' . DbDate::monthLabel('date') . ' as month_label,
                        SUM(visits) as total
                    ')
                    ->groupBy('source', DB::raw(DbDate::month('date')), DB::raw(DbDate::monthLabel('date')))
                    ->orderBy('month', 'ASC')
                    ->get();
                    
                    $months = [];
                    $monthData = [];
                    
                    // Colectăm toate lunile și datele
                    foreach ($rows as $row) {
                        if (!in_array($row->month_label, $months)) {
                            $months[] = $row->month_label;
                        }
                        $source = $row->source;
                        if (!isset($monthData[$row->month_label])) {
                            $monthData[$row->month_label] = [];
                        }
                        $monthData[$row->month_label][$source] = (int)$row->total;
                    }
                    
                    // Populăm datele pentru toate sursele și toate lunile
                    foreach ($months as $monthIndex => $month) {
                        foreach (array_keys($data['datasets']) as $source) {
                            if ($source !== 'total') {
                                $data['datasets'][$source][$monthIndex] = $monthData[$month][$source] ?? 0;
                            }
                        }
                        // Calculăm totalul pentru fiecare lună (excludem 'total' pentru a evita dublarea)
                        $monthTotal = 0;
                        foreach ($monthData[$month] ?? [] as $source => $value) {
                            if ($source !== 'total') {
                                $monthTotal += $value;
                            }
                        }
                        $data['datasets']['total'][$monthIndex] = $monthTotal;
                    }
                    
                    $data['labels'] = $months;
                }
            } elseif ($luna) {
                // Pentru o lună specifică, grupăm pe zile
                $rows = (clone $query)->selectRaw('
                    source,
                    date,
                    ' . DbDate::dayShort('date') . ' as day_label,
                    SUM(visits) as total
                ')
                ->groupBy('source', 'date', DB::raw(DbDate::dayShort('date')))
                ->orderBy('date', 'ASC')
                ->get();
                
                $days = [];
                $dayData = [];
                
                // Colectăm toate zilele și datele
                foreach ($rows as $row) {
                    if (!in_array($row->day_label, $days)) {
                        $days[] = $row->day_label;
                    }
                    $source = $row->source;
                    if (!isset($dayData[$row->day_label])) {
                        $dayData[$row->day_label] = [];
                    }
                    $dayData[$row->day_label][$source] = (int)$row->total;
                }
                
                // Populăm datele pentru toate sursele și toate zilele
                foreach ($days as $dayIndex => $day) {
                    foreach (array_keys($data['datasets']) as $source) {
                        if ($source !== 'total') {
                            $data['datasets'][$source][$dayIndex] = $dayData[$day][$source] ?? 0;
                        }
                    }
                    // Calculăm totalul pentru fiecare zi (excludem 'total' pentru a evita dublarea)
                    $dayTotal = 0;
                    foreach ($dayData[$day] ?? [] as $source => $value) {
                        if ($source !== 'total') {
                            $dayTotal += $value;
                        }
                    }
                    $data['datasets']['total'][$dayIndex] = $dayTotal;
                }
                
                $data['labels'] = $days;
            }
            
            // Calculăm totalurile agregate pe surse (inclusiv utilizatori noi și vechi)
            $totalsQuery = (clone $query)->selectRaw('
                source,
                SUM(visits) as total_visits,
                SUM(new_users) as total_new_users,
                SUM(returning_users) as total_returning_users
            ')
            ->where('source', '!=', 'total')
            ->groupBy('source')
            ->orderByDesc('total_visits')
            ->get();
            
            $totals = [];
            $sources = [];
            $totalSesiuni = 0;
            $totalNewUsers = 0;
            $totalReturningUsers = 0;
            
            foreach ($totalsQuery as $row) {
                $visits = (int)$row->total_visits;
                $newUsers = (int)$row->total_new_users;
                $returningUsers = (int)$row->total_returning_users;
                
                $totals[$row->source] = $visits;
                $totalSesiuni += $visits;
                $totalNewUsers += $newUsers;
                $totalReturningUsers += $returningUsers;
                
                $sources[] = [
                    'source' => $row->source,
                    'visits' => $visits,
                    'new_users' => $newUsers,
                    'returning_users' => $returningUsers,
                    'percentage' => 0 // Va fi calculat mai jos
                ];
            }
            
            // Adăugăm totalul general în totals pentru a fi afișat în card
            $totals['total'] = $totalSesiuni;
            
            // Calculăm procentele
            if ($totalSesiuni > 0) {
                foreach ($sources as &$source) {
                    $source['percentage'] = round(($source['visits'] / $totalSesiuni) * 100, 2);
                }
            }
            
            // Calculăm statistici pe categorii
            $organice = $totals['google'] ?? 0;
            $directe = $totals['direct'] ?? 0;
            $referral = ($totals['yandex'] ?? 0) + ($totals['other'] ?? 0);
            $googleCpc = $totals['google_cpc'] ?? 0;
            
            return response()->json([
                'success' => true,
                'month' => $luna,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'data' => $data,
                'totals' => $totals,
                'total_sesiuni' => $totalSesiuni,
                'total_new_users' => $totalNewUsers,
                'total_returning_users' => $totalReturningUsers,
                'sources' => $sources,
                'stats' => [
                    'organice' => $organice,
                    'directe' => $directe,
                    'referral' => $referral,
                    'google_cpc' => $googleCpc,
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('TraficController error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
