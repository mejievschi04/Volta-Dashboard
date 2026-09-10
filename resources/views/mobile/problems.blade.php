@extends('layouts.app')

@section('title', 'Volta App – Probleme – VOLTA')
@section('header-title', 'Volta App')

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = \App\Support\MobileRetention::presets();
  $fatalRate = ($summary['crashes'] ?? 0) > 0
    ? round((($summary['fatal'] ?? 0) / $summary['crashes']) * 100, 1)
    : 0;
  $shotRate = ($feedbackSummary['total'] ?? 0) > 0
    ? round((($feedbackSummary['with_screenshot'] ?? 0) / $feedbackSummary['total']) * 100, 1)
    : 0;
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">Tabela pentru erorile din aplicație nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif
  @if(isset($feedbackReady) && !$feedbackReady)
    <div class="ma-alert">Tabela pentru mesajele din aplicație nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">Probleme</h1>
        <p class="ma-hero__lead">
          Erori din aplicație și mesajele trimise de clienți —
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} ({{ $days }} zile).
        </p>
      </div>
      <form method="get" action="{{ route('mobile.problems') }}" class="ma-filters">
        <div class="ma-field">
          <label for="crashesStart">De la</label>
          <input id="crashesStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="crashesEnd">Până la</label>
          <input id="crashesEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
        <a class="ma-btn ma-btn--ghost" href="{{ route('mobile.crashes.list', $q) }}">Listă completă</a>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('mobile.problems', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi ma-kpi--warn">
      <span class="ma-kpi__label"><i class="fas fa-bug" aria-hidden="true"></i> Erori</span>
      <div class="ma-kpi__value">{{ number_format($summary['crashes'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['fatal'], 0, ',', '.') }} grave ({{ number_format($fatalRate, 1, ',', '.') }}%)</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-mobile-screen" aria-hidden="true"></i> Dispozitive</span>
      <div class="ma-kpi__value">{{ number_format($summary['devices'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['users'], 0, ',', '.') }} utilizatori afectați</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-fingerprint" aria-hidden="true"></i> Grupuri unice</span>
      <div class="ma-kpi__value">{{ number_format($summary['fingerprints'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Tipuri de eroare distincte</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-comment-dots" aria-hidden="true"></i> Mesaje din app</span>
      <div class="ma-kpi__value">{{ number_format($feedbackSummary['total'] ?? 0, 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($feedbackSummary['with_screenshot'] ?? 0, 0, ',', '.') }} cu captură ({{ number_format($shotRate, 1, ',', '.') }}%)</span>
    </div>
  </div>

  <div class="ma-grid">
    <section class="ma-card ma-card--danger">
      <div class="ma-card__head">
        <h2><i class="fas fa-chart-line" aria-hidden="true"></i> Erori pe zile</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="mobileCrashesChart"></canvas></div>
      </div>
    </section>

    <section class="ma-card ma-card--danger">
      <div class="ma-card__head">
        <h2><i class="fas fa-list" aria-hidden="true"></i> Top erori</h2>
        <a class="ma-card__link" href="{{ route('mobile.crashes.list', $q) }}">Vezi toate →</a>
      </div>
      <div class="ma-card__body">
        @if($topFingerprints->isEmpty())
          <div class="ma-empty"><i class="fas fa-check-circle" aria-hidden="true"></i>Nu există erori în perioada selectată.</div>
        @else
          @php $maxFp = max(1, (int) $topFingerprints->max('total')); @endphp
          @foreach($topFingerprints->take(8) as $row)
            @php $pct = round(((int) $row->total / $maxFp) * 100); @endphp
            <div class="ma-bar-row">
              <div class="ma-bar-row__label" title="{{ $row->error_type }} — {{ $row->error_message }}">
                {{ $row->error_type }}
              </div>
              <div class="ma-bar-row__track"><div class="ma-bar-row__fill" style="width: {{ $pct }}%; background: rgba(244, 63, 94, 0.75);"></div></div>
              <div class="ma-bar-row__value">{{ number_format((int) $row->total, 0, ',', '.') }}</div>
            </div>
          @endforeach
        @endif
      </div>
    </section>
  </div>

  <section class="ma-card ma-card--danger">
    <div class="ma-card__head">
      <h2><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Ultimele erori</h2>
      <a class="ma-card__link" href="{{ route('mobile.crashes.list', $q) }}">Listă completă →</a>
    </div>
    <div class="ma-card__body ma-table-wrap">
      @if($recentCrashes->isEmpty())
        <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Nicio eroare recentă.</div>
      @else
        <table class="ma-table">
          <thead>
            <tr>
              <th>Ora</th>
              <th>Tip</th>
              <th>Mesaj</th>
              <th>Platformă</th>
              <th>Versiune</th>
              <th>Ecran</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
          @foreach($recentCrashes as $crash)
            <tr>
              <td class="ma-muted">{{ optional($crash->occurred_at)->format('d.m.Y H:i') }}</td>
              <td><span class="ma-badge ma-badge--danger">{{ $crash->error_type }}</span></td>
              <td>{{ \Illuminate\Support\Str::limit($crash->error_message ?: '—', 70) }}</td>
              <td>{{ $crash->platform ?: '—' }}</td>
              <td>{{ $crash->app_version ?: '—' }}</td>
              <td>{{ $crash->screen ?: '—' }}</td>
              <td><a class="ma-card__link" href="{{ route('mobile.crashes.show', array_merge(['crash' => $crash], $q)) }}">Detaliu →</a></td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </section>

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Mesaje din aplicație</h2>
      <p>Rapoarte trimise de clienți</p>
    </div>
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-inbox" aria-hidden="true"></i> Ultimele mesaje</h2>
        <a class="ma-card__link" href="{{ route('mobile.feedback', $q) }}">Toate mesajele →</a>
      </div>
      <div class="ma-card__body ma-table-wrap">
        @if(($recentReports ?? collect())->isEmpty())
          <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Niciun mesaj în perioada selectată.</div>
        @else
          <table class="ma-table">
            <thead>
              <tr>
                <th>Ora</th>
                <th>Mesaj</th>
                <th>Nume</th>
                <th>Platformă</th>
                <th>Captură</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            @foreach($recentReports as $report)
              <tr>
                <td class="ma-muted">{{ optional($report->occurred_at)->format('d.m.Y H:i') }}</td>
                <td>{{ \Illuminate\Support\Str::limit($report->message ?: '—', 80) }}</td>
                <td>{{ $report->reporter_name ?: '—' }}</td>
                <td>{{ $report->platform ?: '—' }}</td>
                <td>
                  <span class="ma-badge {{ $report->has_screenshot ? 'ma-badge--ok' : '' }}">
                    {{ $report->has_screenshot ? 'Da' : 'Nu' }}
                  </span>
                </td>
                <td><a class="ma-card__link" href="{{ route('mobile.feedback.show', array_merge(['report' => $report], $q)) }}">Detaliu →</a></td>
              </tr>
            @endforeach
            </tbody>
          </table>
        @endif
      </div>
    </section>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var chartEl = document.getElementById('mobileCrashesChart');
  if (!chartEl || typeof Chart === 'undefined') return;

  var chartData = @json($dailyChart);
  var lineColor = 'rgb(244, 63, 94)';
  var areaColor = 'rgba(244, 63, 94, 0.14)';
  if (typeof VoltaChartTheme !== 'undefined' && VoltaChartTheme.getSeriesPalette) {
    var palette = VoltaChartTheme.getSeriesPalette();
    if (palette && palette.rose) {
      lineColor = palette.rose.line;
      areaColor = palette.rose.area;
    }
  }

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        plugins: { legend: { display: false }, tooltip: VoltaChartTheme.tooltip() },
        scales: {
          x: { ticks: VoltaChartTheme.ticks(9, 11), grid: VoltaChartTheme.gridLines() },
          y: { beginAtZero: true, ticks: VoltaChartTheme.ticks(9, 11), grid: VoltaChartTheme.gridLines() }
        }
      })
    : { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } };

  new Chart(chartEl.getContext('2d'), {
    type: 'line',
    data: {
      labels: chartData.labels || [],
      datasets: [{
        label: 'Erori',
        data: chartData.totals || [],
        borderColor: lineColor,
        backgroundColor: areaColor,
        borderWidth: 2.2,
        tension: 0.32,
        fill: true,
        pointRadius: 0,
        pointHoverRadius: 4
      }]
    },
    options: options
  });
});
</script>
@endpush
