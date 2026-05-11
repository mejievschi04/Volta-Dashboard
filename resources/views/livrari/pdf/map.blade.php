<!doctype html>
<html lang="ro">
<head>
  <meta charset="utf-8">
  <title>Hartă livrări - PDF</title>
  <style>
    @page { margin: 22px 20px; }
    body { font-family: DejaVu Sans, Arial, sans-serif; color: #0f172a; font-size: 12px; }
    .head { margin-bottom: 14px; }
    .title { font-size: 18px; font-weight: 700; margin: 0 0 6px; }
    .meta { color: #334155; margin: 2px 0; }
    .kpi { margin: 12px 0 16px; }
    .kpi strong { font-size: 16px; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #cbd5e1; padding: 7px 8px; vertical-align: top; }
    th { background: #f8fafc; text-align: left; font-weight: 700; }
    .num { text-align: right; white-space: nowrap; }
    .localitati { color: #334155; font-size: 11px; line-height: 1.4; }
  </style>
</head>
<body>
  <div class="head">
    <p class="title">Hartă livrări - distribuție pe raioane</p>
    <p class="meta">Perioada: {{ $payload['period_label'] ?? '-' }}</p>
    <p class="meta">Generat: {{ $generatedAt ?? '-' }}</p>
  </div>

  <div class="kpi">
    <strong>Total livrări: {{ number_format((int) ($payload['total'] ?? 0), 0, ',', '.') }}</strong>
  </div>

  <table>
    <thead>
      <tr>
        <th style="width: 28px;">#</th>
        <th>Denumire raion</th>
        <th class="num" style="width: 110px;">Nr. comenzi</th>
        <th>Top localități</th>
      </tr>
    </thead>
    <tbody>
      @forelse(($payload['raioane'] ?? []) as $idx => $row)
        <tr>
          <td class="num">{{ $idx + 1 }}</td>
          <td>{{ $row['raion_label'] ?? ($row['raion'] ?? '—') }}</td>
          <td class="num">{{ number_format((int) ($row['total'] ?? 0), 0, ',', '.') }}</td>
          <td class="localitati">
            @php
              $locals = collect($row['localitati'] ?? [])->map(function ($l) {
                $name = $l['localitate'] ?? '—';
                $count = number_format((int) ($l['total'] ?? 0), 0, ',', '.');
                return $name . ' (' . $count . ')';
              });
            @endphp
            {{ $locals->isEmpty() ? '—' : $locals->implode(', ') }}
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="text-align:center; color:#64748b;">Nu există date pentru filtrele curente.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>
</html>
