<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrafficSource;
use App\Models\OnecKpiSync;
use App\Support\DbDate;
use App\Support\LunaRomana;
use App\Services\GoogleAnalyticsService;
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

    /**
     * Raport trafic: vizitatori site, bounce rate, conversion rate pe lună/luni.
     */
    public function raport(Request $request, GoogleAnalyticsService $gaService)
    {
        $startMonth = $this->normalizeMonthKey($request->get('start_month', date('Y-m')));
        $endMonth = $this->normalizeMonthKey($request->get('end_month', $startMonth));

        if ($startMonth === null || $endMonth === null) {
            return response()->json([
                'success' => false,
                'error' => 'Perioadă invalidă. Folosește formatul YYYY-MM.',
            ], 400);
        }

        if ($startMonth > $endMonth) {
            [$startMonth, $endMonth] = [$endMonth, $startMonth];
        }

        $months = $this->monthKeysBetween($startMonth, $endMonth);
        $gaByMonth = [];
        $gaWarning = null;

        try {
            $rangeStart = $startMonth . '-01';
            $rangeEnd = date('Y-m-t', strtotime($endMonth . '-01'));
            $gaByMonth = $gaService->fetchMonthlyRaportMetrics($rangeStart, $rangeEnd);
        } catch (\Throwable $e) {
            $gaWarning = 'Date GA4 indisponibile: ' . $e->getMessage();
        }

        $rows = [];
        $totals = [
            'sessions' => 0,
            'bounce_weighted' => 0.0,
            'conversion_weighted' => 0.0,
            'rate_months' => 0,
        ];

        foreach ($months as $ym) {
            $ga = $gaByMonth[$ym] ?? null;

            if ($ga) {
                $vizite = (int) $ga['sessions'];
                $bounceRate = $ga['bounce_rate'];
                $conversie = $ga['conversion_rate'];
            } else {
                // Fallback: vizite din sync local, conversie din comenzi 1C
                $vizite = (int) TrafficSource::query()
                    ->where('source', 'total')
                    ->whereRaw(DbDate::month('date') . ' = ?', [$ym])
                    ->sum('visits');

                $onecSync = OnecKpiSync::whereRaw(DbDate::month('period_start') . ' = ?', [$ym])
                    ->orderByDesc('created_at')
                    ->first();
                $comenzi = $onecSync ? (int) $onecSync->nr_comenzi : 0;
                $conversie = $vizite > 0 ? round(($comenzi / $vizite) * 100, 2) : 0;
                $bounceRate = null;
            }

            $rows[] = [
                'luna' => $ym,
                'luna_label' => LunaRomana::labelFromYm($ym),
                'vizite_site' => $vizite,
                'bounce_rate' => $bounceRate,
                'conversion_rate' => $conversie,
                'sursa' => $ga ? 'ga4' : 'local',
            ];

            $totals['sessions'] += $vizite;
            if ($bounceRate !== null && $conversie !== null) {
                $totals['bounce_weighted'] += $bounceRate * $vizite;
                $totals['conversion_weighted'] += $conversie * $vizite;
                $totals['rate_months'] += $vizite;
            }
        }

        $avgBounce = $totals['rate_months'] > 0
            ? round($totals['bounce_weighted'] / $totals['rate_months'], 2)
            : null;
        $avgConversie = $totals['rate_months'] > 0
            ? round($totals['conversion_weighted'] / $totals['rate_months'], 2)
            : null;

        return response()->json([
            'success' => true,
            'start_month' => $startMonth,
            'end_month' => $endMonth,
            'source' => ! empty($gaByMonth) ? 'ga4' : 'local',
            'metric_definitions' => [
                'vizite_site' => 'GA4 sessions — numărul de accesări (sesiuni) în e-shop',
                'bounce_rate' => 'GA4 bounceRate — % sesiuni neangajate (fără interacțiune: sub 10s, fără conversie, sub 2 pagini)',
                'conversion_rate' => 'GA4 sessionConversionRate — % sesiuni în care s-a declanșat un eveniment de conversie (ex. achiziție)',
            ],
            'rows' => $rows,
            'totals' => [
                'vizite_site' => $totals['sessions'],
                'bounce_rate' => $avgBounce,
                'conversion_rate' => $avgConversie,
            ],
            'ga_warning' => $gaWarning,
        ]);
    }

    private function normalizeMonthKey(?string $value): ?string
    {
        $value = trim((string) $value);
        if (! preg_match('/^\d{4}-\d{2}$/', $value)) {
            return null;
        }
        [$year, $month] = array_map('intval', explode('-', $value));
        if ($year < 2000 || $year > 2100 || $month < 1 || $month > 12) {
            return null;
        }

        return sprintf('%04d-%02d', $year, $month);
    }

    /** @return list<string> */
    private function monthKeysBetween(string $startYm, string $endYm): array
    {
        $months = [];
        $cursor = strtotime($startYm . '-01');
        $endTs = strtotime($endYm . '-01');

        while ($cursor <= $endTs) {
            $months[] = date('Y-m', $cursor);
            $cursor = strtotime('+1 month', $cursor);
        }

        return $months;
    }
}
