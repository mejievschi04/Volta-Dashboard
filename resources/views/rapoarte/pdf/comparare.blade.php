<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comparare KPI - VOLTA STATS</title>
    <style>
        @page { margin: 18mm; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; color: #111827; }
        .header { border-bottom: 3px solid #ffee00; padding-bottom: 12px; margin-bottom: 14px; }
        .title { font-size: 20px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; }
        .subtitle { margin-top: 4px; color: #475569; font-size: 11px; }
        .meta { margin-top: 6px; color: #64748b; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #ffee00; color: #000; padding: 8px 6px; text-align: left; border: 1px solid #ddd; font-size: 9px; }
        td { padding: 7px 6px; border: 1px solid #ddd; font-size: 9px; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        .num { text-align: right; }
        .pos { color: #15803d; font-weight: 700; }
        .neg { color: #b91c1c; font-weight: 700; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">VOLTA STATS - Comparare KPI</div>
        <div class="subtitle">{{ $month1Label }} vs {{ $month2Label }}</div>
        <div class="meta">Generat la {{ date('d.m.Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Indicator</th>
                <th class="num">{{ $month1Label }}</th>
                <th class="num">{{ $month2Label }}</th>
                <th class="num">Diferență</th>
                <th class="num">Diferență %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $row)
                @php
                    $v1 = (float)($data1[$row['key']] ?? 0);
                    $v2 = (float)($data2[$row['key']] ?? 0);
                    $diff = $v1 - $v2;
                    $diffPercent = $v2 != 0 ? ($diff / $v2) * 100 : 0;
                    $sign = $diff >= 0 ? '+' : '';
                    $cls = $diff >= 0 ? 'pos' : 'neg';
                    $suffix = $row['suffix'] ? ' ' . $row['suffix'] : '';
                @endphp
                <tr>
                    <td><strong>{{ $row['label'] }}</strong></td>
                    <td class="num">{{ number_format($v1, 2, ',', '.') }}{{ $suffix }}</td>
                    <td class="num">{{ number_format($v2, 2, ',', '.') }}{{ $suffix }}</td>
                    <td class="num {{ $cls }}">{{ $sign }}{{ number_format(abs($diff), 2, ',', '.') }}{{ $suffix }}</td>
                    <td class="num {{ $cls }}">{{ $sign }}{{ number_format($diffPercent, 2, ',', '.') }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
