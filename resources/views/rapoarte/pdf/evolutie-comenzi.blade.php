@php
  $fmt = fn ($n, $d = 0) => number_format((float) $n, $d, ',', '.');
  $pct = fn ($p) => $p === null ? '—' : (($p > 0 ? '+' : '').number_format($p, 1, ',', '.').'%');
  $pctClass = fn ($p) => $p === null ? 'muted' : ($p > 0 ? 'up' : ($p < 0 ? 'down' : 'flat'));
  $max = max(1, (int) $summary['max']);
  $count = count($rows);
  $barArea = 150;
  $labelSize = $count > 24 ? 6 : ($count > 14 ? 7 : 8.5);
@endphp
<!DOCTYPE html>
<html lang="ro">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
  <title>Evoluția comenzilor – VOLTA</title>
  <style>
    @page { margin: 12mm 12mm 14mm 12mm; }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 9px; color: #0f172a; line-height: 1.35; }

    .header { width: 100%; background: #0b1030; color: #fff; border-radius: 10px; padding: 12px 16px; margin-bottom: 12px; }
    .header td { vertical-align: middle; }
    .logo { height: 34px; }
    .title { font-size: 17px; font-weight: bold; letter-spacing: 0.2px; }
    .subtitle { font-size: 9.5px; color: #9fe9f5; margin-top: 3px; }
    .meta { text-align: right; font-size: 8.5px; color: #c7d2fe; }

    .cards { width: 100%; border-collapse: separate; border-spacing: 0; margin-bottom: 12px; }
    .cards td.gap { width: 6px; padding: 0; background: none; border: 0; }
    .card { background: #f1f5f9; border: 1px solid #dbe3ee; border-radius: 8px; padding: 8px 10px; vertical-align: top; }
    .card-label { font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; font-weight: bold; }
    .card-value { font-size: 16px; font-weight: bold; margin-top: 3px; color: #0b1030; }
    .card-help { font-size: 7.5px; color: #64748b; margin-top: 2px; }

    .section-title { font-size: 11px; font-weight: bold; color: #0b1030; margin: 4px 0 6px; padding-left: 7px; border-left: 3px solid #00b8d4; }

    .chart-box { border: 1px solid #dbe3ee; border-radius: 8px; padding: 10px 8px 6px; margin-bottom: 12px; }
    .chart { width: 100%; table-layout: fixed; border-collapse: collapse; }
    .chart td { text-align: center; padding: 0 2px; }
    .chart .bars td { height: {{ $barArea + 18 }}px; vertical-align: bottom; border-bottom: 1px solid #94a3b8; }
    .bar-value { font-size: {{ $labelSize }}px; font-weight: bold; color: #0b1030; margin-bottom: 2px; }
    .bar { width: 70%; margin: 0 auto; background: #00b8d4; border-radius: 3px 3px 0 0; }
    .bar.current { background: #7dd3fc; }
    .bar.best { background: #0891b2; }
    .chart .labels td { font-size: {{ $labelSize }}px; color: #334155; padding-top: 3px; }
    .chart .growth td { font-size: {{ $labelSize }}px; font-weight: bold; padding-top: 2px; }
    .legend { font-size: 7.5px; color: #64748b; margin-top: 6px; }
    .legend span { display: inline-block; width: 8px; height: 8px; border-radius: 2px; margin: 0 3px 0 10px; vertical-align: middle; }

    .up { color: #059669; }
    .down { color: #dc2626; }
    .flat { color: #475569; }
    .muted { color: #94a3b8; }

    table.data { width: 100%; border-collapse: collapse; }
    table.data th { background: #0b1030; color: #e0f7fb; font-size: 7.5px; text-transform: uppercase; letter-spacing: 0.4px; padding: 6px 6px; text-align: right; }
    table.data th:first-child, table.data td:first-child { text-align: left; }
    table.data td { padding: 5px 6px; border-bottom: 1px solid #e2e8f0; text-align: right; }
    table.data tr:nth-child(even) td { background: #f8fafc; }
    table.data tfoot td { font-weight: bold; background: #e0f7fb; border-top: 2px solid #00b8d4; }
    .tag { font-size: 7px; color: #0369a1; font-weight: bold; }

    .footer { margin-top: 10px; font-size: 7.5px; color: #94a3b8; }
  </style>
</head>
<body>
  <table class="header">
    <tr>
      <td style="width: 52px;">
        @if(is_file($logoPath))<img src="{{ $logoPath }}" class="logo" alt="">@endif
      </td>
      <td>
        <div class="title">Evoluția comenzilor</div>
        <div class="subtitle">{{ $periodLabel }} · {{ $count }} {{ $count === 1 ? 'lună' : 'luni' }} · date lunare din 1C</div>
      </td>
      <td class="meta">VOLTA STATS<br>Generat la {{ now()->timezone('Europe/Bucharest')->format('d.m.Y H:i') }}</td>
    </tr>
  </table>

  <table class="cards">
    <tr>
      <td class="card">
        <div class="card-label">Total comenzi</div>
        <div class="card-value">{{ $fmt($summary['total']) }}</div>
        <div class="card-help">în perioada aleasă</div>
      </td>
      <td class="gap"></td>
      <td class="card">
        <div class="card-label">Medie lunară</div>
        <div class="card-value">{{ $fmt($summary['medie']) }}</div>
        <div class="card-help">comenzi / lună{{ $summary['exclude_curent'] ? ', fără luna în curs' : '' }}</div>
      </td>
      <td class="gap"></td>
      <td class="card">
        <div class="card-label">Creștere totală</div>
        <div class="card-value {{ $pctClass($summary['crestere_totala']) }}">{{ $pct($summary['crestere_totala']) }}</div>
        <div class="card-help">
          @if($summary['prima'] && $summary['ultima'] && $summary['prima'] !== $summary['ultima'])
            {{ $summary['ultima']['label'] }} vs {{ $summary['prima']['label'] }}
          @else
            necesită cel puțin 2 luni cu comenzi
          @endif
        </div>
      </td>
      <td class="gap"></td>
      <td class="card">
        <div class="card-label">Creștere medie lunară</div>
        <div class="card-value {{ $pctClass($summary['crestere_medie']) }}">{{ $pct($summary['crestere_medie']) }}</div>
        <div class="card-help">media variațiilor lună la lună</div>
      </td>
      <td class="gap"></td>
      <td class="card">
        <div class="card-label">Cea mai bună lună</div>
        <div class="card-value">{{ $summary['best'] ? $fmt($summary['best']['comenzi']) : '—' }}</div>
        <div class="card-help">{{ $summary['best']['label'] ?? 'fără comenzi' }}</div>
      </td>
      <td class="gap"></td>
      <td class="card">
        <div class="card-label">Cea mai slabă lună</div>
        <div class="card-value">{{ $summary['worst'] ? $fmt($summary['worst']['comenzi']) : '—' }}</div>
        <div class="card-help">{{ $summary['worst']['label'] ?? 'fără comenzi' }}</div>
      </td>
    </tr>
  </table>

  <div class="section-title">Comenzi pe luni și creștere față de luna anterioară</div>
  <div class="chart-box">
    <table class="chart">
      <tr class="bars">
        @foreach($rows as $row)
          @php
            $h = $row['comenzi'] > 0 ? max(2, (int) round($row['comenzi'] / $max * $barArea)) : 0;
            $isBest = $summary['best'] && $summary['best']['luna'] === $row['luna'];
          @endphp
          <td>
            <div class="bar-value">{{ $fmt($row['comenzi']) }}</div>
            @if($h > 0)<div class="bar {{ $row['in_curs'] ? 'current' : ($isBest ? 'best' : '') }}" style="height: {{ $h }}px;"></div>@endif
          </td>
        @endforeach
      </tr>
      <tr class="labels">
        @foreach($rows as $row)<td>{{ $row['short'] }}</td>@endforeach
      </tr>
      <tr class="growth">
        @foreach($rows as $row)<td class="{{ $pctClass($row['crestere']) }}">{{ $pct($row['crestere']) }}</td>@endforeach
      </tr>
    </table>
    <div class="legend">
      <span style="background:#00b8d4;"></span>Comenzi
      <span style="background:#0891b2;"></span>Cea mai bună lună
      <span style="background:#7dd3fc;"></span>Luna în curs (parțială)
      &nbsp;&nbsp;Procentele de sub bare: creștere față de luna anterioară.
    </div>
  </div>

  <div class="section-title">Detaliu lunar</div>
  <table class="data">
    <thead>
      <tr>
        <th>Lună</th>
        <th>Comenzi</th>
        <th>Diferență vs luna anterioară</th>
        <th>Creștere lunară</th>
        <th>vs aceeași lună an trecut</th>
        <th>Comenzi / zi</th>
        <th>Vânzări fără TVA</th>
        <th>CEC mediu</th>
        <th>Conversie</th>
      </tr>
    </thead>
    <tbody>
      @foreach($rows as $row)
        <tr>
          <td>{{ $row['label'] }} @if($row['in_curs'])<span class="tag">în curs</span>@endif</td>
          <td><strong>{{ $fmt($row['comenzi']) }}</strong></td>
          <td class="{{ $pctClass($row['diferenta']) }}">{{ $row['diferenta'] === null ? '—' : (($row['diferenta'] > 0 ? '+' : '').$fmt($row['diferenta'])) }}</td>
          <td class="{{ $pctClass($row['crestere']) }}">{{ $pct($row['crestere']) }}</td>
          <td class="{{ $pctClass($row['crestere_an']) }}">{{ $pct($row['crestere_an']) }}</td>
          <td>{{ $fmt($row['comenzi_zi'], 1) }}</td>
          <td>{{ $fmt($row['vanzari']) }} MDL</td>
          <td>{{ $fmt($row['cec_mediu']) }} MDL</td>
          <td>{{ $fmt($row['conversie'], 2) }}%</td>
        </tr>
      @endforeach
    </tbody>
    <tfoot>
      <tr>
        <td>Total / medie</td>
        <td>{{ $fmt($summary['total']) }}</td>
        <td></td>
        <td class="{{ $pctClass($summary['crestere_medie']) }}">{{ $pct($summary['crestere_medie']) }}</td>
        <td></td>
        <td></td>
        <td>{{ $fmt(array_sum(array_column($rows, 'vanzari'))) }} MDL</td>
        <td>{{ $summary['total'] > 0 ? $fmt(array_sum(array_column($rows, 'vanzari')) / $summary['total']) : '0' }} MDL</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <div class="footer">
    Creșterea lunară compară fiecare lună cu luna anterioară; „vs aceeași lună an trecut” compară cu aceeași lună din anul precedent.
    Luna în curs conține doar zilele sincronizate până acum, deci creșterea ei e provizorie{{ $summary['exclude_curent'] ? ' și nu intră în indicatorii din antet (creșteri, medie, cea mai bună / slabă lună)' : '' }}.
  </div>
</body>
</html>
