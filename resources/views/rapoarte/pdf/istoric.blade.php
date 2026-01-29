<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Istoric Rapoarte - VOLTA</title>
    <style>
        @page {
            margin: 15mm;
        }
    <style>
        @page {
            margin: 20mm;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #111827;
            line-height: 1.4;
        }
        
        .header {
            border-bottom: 3px solid #ffee00;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 20%;
        }
        
        .header-center {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            width: 60%;
        }
        
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 20%;
        }
        
        .logo {
            max-width: 80px;
            max-height: 80px;
        }
        
        .header-title {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header-subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .date-info {
            font-size: 10px;
            color: #666;
        }
        
        .stats-section {
            background: #f5f5f5;
            border: 2px solid #ffee00;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            display: table;
            width: 100%;
        }
        
        .stat-item {
            display: table-cell;
            text-align: center;
            padding: 0 10px;
            border-right: 1px solid #ddd;
        }
        
        .stat-item:last-child {
            border-right: none;
        }
        
        .stat-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .stat-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }
        
        .table-container {
            margin-bottom: 20px;
        }
        
        .table-title {
            font-size: 14px;
            font-weight: bold;
            color: #000;
            margin-bottom: 10px;
            text-transform: uppercase;
            border-bottom: 2px solid #ffee00;
            padding-bottom: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 8px;
        }
        
        thead {
            background: #ffee00;
            color: #000;
        }
        
        th {
            padding: 8px 5px;
            text-align: left;
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }
        
        th.text-center {
            text-align: center;
        }
        
        th.text-right {
            text-align: right;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #ddd;
            font-size: 8px;
        }
        
        td.text-center {
            text-align: center;
        }
        
        td.text-right {
            text-align: right;
        }
        
        tbody tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        tbody tr:hover {
            background: #f0f0f0;
        }
        
        .positive {
            color: #0a0;
            font-weight: bold;
        }
        
        .negative {
            color: #a00;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #ffee00;
            text-align: center;
            font-size: 9px;
            color: #666;
        }
        
        .filters-info {
            font-size: 9px;
            color: #666;
            margin-bottom: 15px;
            padding: 10px;
            background: #f9f9f9;
            border-left: 3px solid #ffee00;
        }
        
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            @php
                $logoExists = file_exists($logoPath);
                $logoBase64 = '';
                if ($logoExists) {
                    $logoData = file_get_contents($logoPath);
                    $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
                }
            @endphp
            @if($logoExists && $logoBase64)
            <img src="{{ $logoBase64 }}" alt="VOLTA Logo" class="logo">
            @else
            <div style="width: 80px; height: 80px; background: #FFEE00; border-radius: 5px; text-align: center; line-height: 80px; font-weight: bold; font-size: 24px; color: #000;">V</div>
            @endif
        </div>
        <div class="header-center">
            <div class="header-title">VOLTA Dashboard</div>
            <div class="header-subtitle">Raport Istoric Complet</div>
        </div>
        <div class="header-right">
            <div class="date-info">
                Generat: {{ date('d.m.Y H:i') }}<br>
                Utilizator: {{ Auth::check() ? Auth::user()->username : 'Guest' }}
            </div>
        </div>
    </div>
    
    <!-- Filtre Aplicate -->
    @if($an || $luna || $search)
    <div class="filters-info">
        <strong>Filtre aplicate:</strong>
        @if($an) An: {{ $an }} @endif
        @if($luna) 
            @php
                $luniNume = [1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie', 5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August', 9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie'];
            @endphp
            Lună: {{ $luniNume[$luna] ?? $luna }}
        @endif
        @if($search) Căutare: {{ $search }} @endif
        @if(!$an && !$luna && !$search) Toate datele @endif
    </div>
    @endif
    
    <!-- Statistici Agregat -->
    <div class="stats-section">
        <div class="stat-item">
            <div class="stat-label">Total Vânzări</div>
            <div class="stat-value">{{ number_format($totalVanzari, 0, ',', '.') }} MDL</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total Profit</div>
            <div class="stat-value">{{ number_format($totalProfit, 0, ',', '.') }} MDL</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total Comenzi</div>
            <div class="stat-value">{{ number_format($totalComenzi, 0, ',', '.') }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Conversie Medie</div>
            <div class="stat-value">{{ number_format($avgConversie, 2, ',', '.') }}%</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Luni Analizate</div>
            <div class="stat-value">{{ count($istoricData) }}</div>
        </div>
    </div>
    
    <!-- Tabel Istoric -->
    <div class="table-container">
        <div class="table-title">Istoric Complet - Toate Lunile</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Lună</th>
                    <th class="text-right" style="width: 8%;">Plan (MDL)</th>
                    <th class="text-right" style="width: 8%;">Vânzări (MDL)</th>
                    <th class="text-right" style="width: 8%;">Vânzări TVA (MDL)</th>
                    <th class="text-right" style="width: 8%;">Profit (MDL)</th>
                    <th class="text-center" style="width: 7%;">Progres (%)</th>
                    <th class="text-right" style="width: 8%;">Dif. Plan (MDL)</th>
                    <th class="text-right" style="width: 6%;">Comenzi</th>
                    <th class="text-right" style="width: 6%;">Comenzi/Zi</th>
                    <th class="text-right" style="width: 7%;">Sesiuni</th>
                    <th class="text-center" style="width: 6%;">Conversie (%)</th>
                    <th class="text-right" style="width: 8%;">vs Anterioară</th>
                </tr>
            </thead>
            <tbody>
                @forelse($istoricData as $item)
                <tr>
                    <td><strong>{{ $item['luna_label'] }}</strong></td>
                    <td class="text-right">{{ number_format($item['plan_luna'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['vanzari_luna'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['vanzari_cu_tva'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['profit'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item['progres_plan'], 2, ',', '.') }}%</td>
                    <td class="text-right {{ $item['diferenta_plan'] >= 0 ? 'positive' : 'negative' }}">
                        {{ number_format($item['diferenta_plan'], 0, ',', '.') }}
                    </td>
                    <td class="text-right">{{ number_format($item['comenzi'], 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['comenzi_zi'], 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item['sesiuni'], 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item['conversie'], 2, ',', '.') }}%</td>
                    <td class="text-right {{ ($item['vanzari_vs_anterioara'] ?? 0) >= 0 ? 'positive' : 'negative' }}">
                        @php
                            $vsAnterioara = $item['vanzari_vs_anterioara'] ?? 0;
                            $vsAnterioaraPercent = $item['vanzari_vs_anterioara_percent'] ?? 0;
                            $sign = $vsAnterioara >= 0 ? '+' : '';
                        @endphp
                        {{ $sign }}{{ number_format(abs($vsAnterioara), 0, ',', '.') }} MDL<br>
                        <small>({{ $sign }}{{ number_format($vsAnterioaraPercent, 2, ',', '.') }}%)</small>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px; color: #999;">
                        Nu există date pentru filtrele selectate.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <p><strong>VOLTA Dashboard</strong> - Sistem de Management și Analiză</p>
        <p>Acest raport a fost generat automat pe {{ date('d.m.Y') }} la {{ date('H:i') }}</p>
        <p>© {{ date('Y') }} VOLTA. Toate drepturile rezervate.</p>
    </div>
</body>
</html>

