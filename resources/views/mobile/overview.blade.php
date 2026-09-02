@extends('layouts.app')

@section('title', 'Volta App – Prezentare – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = [
    ['label' => '7 zile', 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '30 zile', 'start' => now()->subDays(29)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => 'Luna curentă', 'start' => now()->startOfMonth()->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '90 zile', 'start' => now()->subDays(89)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
  ];
  $maxEvent = max(1, (int) ($eventBreakdown->max('total') ?? 1));
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">
      Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Prezentare Volta App</h1>
        <p class="ma-hero__lead">
          Activitate în aplicația mobilă: sesiuni, conversie, produse și comenzi —
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} ({{ $days }} zile).
        </p>
      </div>
      <form method="get" action="{{ route('mobile.analytics') }}" class="ma-filters">
        <div class="ma-field">
          <label for="maStart">De la</label>
          <input id="maStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="maEnd">Până la</label>
          <input id="maEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php
          $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end'];
        @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('mobile.analytics', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-users" aria-hidden="true"></i> Sesiuni</span>
      <div class="ma-kpi__value">{{ number_format($summary['sessions'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['users'], 0, ',', '.') }} utilizatori · {{ number_format($summary['events_per_session'] ?? 0, 1, ',', '.') }} evenimente/sesiune</span>
    </div>
    <div class="ma-kpi ma-kpi--accent">
      <span class="ma-kpi__label"><i class="fas fa-percent" aria-hidden="true"></i> Rată conversie</span>
      <div class="ma-kpi__value">{{ number_format($summary['conversion_rate'] ?? 0, 2, ',', '.') }}%</div>
      <span class="ma-kpi__help">Comenzi / sesiuni în interval</span>
    </div>
    <div class="ma-kpi ma-kpi--good">
      <span class="ma-kpi__label"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Comenzi</span>
      <div class="ma-kpi__value">{{ number_format($summary['orders'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['cart_abandons'], 0, ',', '.') }} abandonuri coș</span>
    </div>
    <div class="ma-kpi {{ ($summary['view_to_cart_rate'] ?? 0) < 5 ? 'ma-kpi--warn' : '' }}">
      <span class="ma-kpi__label"><i class="fas fa-cart-plus" aria-hidden="true"></i> Produs → Coș</span>
      <div class="ma-kpi__value">{{ number_format($summary['view_to_cart_rate'] ?? 0, 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($summary['add_to_cart'], 0, ',', '.') }} add-to-cart din {{ number_format($summary['product_views'], 0, ',', '.') }} views</span>
    </div>
  </div>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-bolt" aria-hidden="true"></i> Evenimente</span>
      <div class="ma-kpi__value">{{ number_format($summary['events'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['page_views'], 0, ',', '.') }} page views</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-eye" aria-hidden="true"></i> Vizualizări produs</span>
      <div class="ma-kpi__value">{{ number_format($summary['product_views'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['searches'], 0, ',', '.') }} căutări</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-image" aria-hidden="true"></i> Click bannere</span>
      <div class="ma-kpi__value">{{ number_format($summary['banner_clicks'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['logins'], 0, ',', '.') }} login-uri · {{ number_format($summary['map_opens'], 0, ',', '.') }} hartă</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-clock" aria-hidden="true"></i> Timp mediu pagină</span>
      <div class="ma-kpi__value">{{ number_format($summary['avg_page_seconds'], 0, ',', '.') }}s</div>
      <span class="ma-kpi__help">Pe baza evenimentelor cu duration</span>
    </div>
  </div>

  <div class="ma-shortcuts">
    <a class="ma-shortcut" href="{{ route('mobile.analytics.meta', $q) }}">
      <i class="fas fa-layer-group" aria-hidden="true"></i>
      <span><strong>Meta</strong><span>KPI executive + sănătate</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.analytics.funnels', $q) }}">
      <i class="fas fa-filter-circle-dollar" aria-hidden="true"></i>
      <span><strong>Pâlnie conversie</strong><span>Drop-off pe etape</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.analytics.events', $q) }}">
      <i class="fas fa-bolt" aria-hidden="true"></i>
      <span><strong>Evenimente</strong><span>Pagini, bannere, feed</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.crashes', $q) }}">
      <i class="fas fa-bug" aria-hidden="true"></i>
      <span><strong>Crash-uri</strong><span>Stabilitate app</span></span>
    </a>
  </div>

  <div class="ma-grid">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-chart-line" aria-hidden="true"></i> Evoluție pe zile</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="mobileOverviewChart"></canvas></div>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-list-check" aria-hidden="true"></i> Top tipuri evenimente</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.event-types', $q) }}">Vezi toate →</a>
      </div>
      <div class="ma-card__body">
        @if($eventBreakdown->isEmpty())
          <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Nu există evenimente în perioada selectată.</div>
        @else
          @foreach($eventBreakdown->take(10) as $row)
            @php $pct = round(((int) $row->total / $maxEvent) * 100); @endphp
            <div class="ma-bar-row">
              <div class="ma-bar-row__label" title="{{ $row->event_name }}">{{ $row->event_name }}</div>
              <div class="ma-bar-row__track"><div class="ma-bar-row__fill" style="width: {{ $pct }}%;"></div></div>
              <div class="ma-bar-row__value">{{ number_format((int) $row->total, 0, ',', '.') }}</div>
            </div>
          @endforeach
        @endif
      </div>
    </section>
  </div>

  <div class="ma-grid ma-grid--2">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Top căutări</h2>
      </div>
      <div class="ma-card__body">
        @if(($topSearches ?? collect())->isEmpty())
          <div class="ma-empty"><i class="fas fa-magnifying-glass" aria-hidden="true"></i>Nu există căutări în interval.</div>
        @else
          <div class="ma-table-wrap">
            <table class="ma-table">
              <thead><tr><th>Query</th><th class="num">Total</th></tr></thead>
              <tbody>
              @foreach($topSearches as $row)
                <tr>
                  <td>{{ $row->label }}</td>
                  <td class="num">{{ number_format((int) $row->total, 0, ',', '.') }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-box-open" aria-hidden="true"></i> Top produse vizualizate</h2>
      </div>
      <div class="ma-card__body">
        @if(($topProducts ?? collect())->isEmpty())
          <div class="ma-empty"><i class="fas fa-box-open" aria-hidden="true"></i>Nu există vizualizări de produs.</div>
        @else
          <div class="ma-table-wrap">
            <table class="ma-table">
              <thead><tr><th>Produs</th><th class="num">Total</th></tr></thead>
              <tbody>
              @foreach($topProducts as $row)
                <tr>
                  <td>{{ $row->label }}</td>
                  <td class="num">{{ number_format((int) $row->total, 0, ',', '.') }}</td>
                </tr>
              @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var chartEl = document.getElementById('mobileOverviewChart');
  if (!chartEl || typeof Chart === 'undefined') return;

  var chartData = @json($dailyChart);
  var palette = (typeof VoltaChartTheme !== 'undefined' && VoltaChartTheme.getSeriesPalette)
    ? VoltaChartTheme.getSeriesPalette()
    : null;
  var colors = {
    page_view: palette ? palette.amber : { line: 'rgb(250, 204, 21)', area: 'rgba(250, 204, 21, 0.14)' },
    product_view: palette ? palette.violet : { line: 'rgb(167, 139, 250)', area: 'rgba(167, 139, 250, 0.12)' },
    search: palette ? palette.cyan : { line: 'rgb(34, 211, 238)', area: 'rgba(34, 211, 238, 0.12)' },
    add_to_cart: palette ? palette.amber : { line: 'rgb(255, 238, 0)', area: 'rgba(255, 238, 0, 0.12)' },
    banner_click: palette ? palette.slate : { line: 'rgb(203, 213, 225)', area: 'rgba(203, 213, 225, 0.12)' },
    cart_abandoned: palette ? palette.rose : { line: 'rgb(244, 63, 94)', area: 'rgba(244, 63, 94, 0.12)' },
    order_completed: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' }
  };
  var names = {
    page_view: 'Pagini',
    product_view: 'Produse',
    search: 'Căutări',
    add_to_cart: 'În coș',
    banner_click: 'Bannere',
    cart_abandoned: 'Abandon',
    order_completed: 'Comenzi'
  };

  var datasets = Object.keys(chartData.datasets || {}).map(function (key) {
    return {
      label: names[key] || key,
      data: chartData.datasets[key] || [],
      borderColor: (colors[key] || colors.page_view).line,
      backgroundColor: (colors[key] || colors.page_view).area,
      borderWidth: 2.2,
      tension: 0.32,
      fill: key === 'page_view',
      pointRadius: 0,
      pointHoverRadius: 4
    };
  });

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        plugins: {
          legend: { position: 'bottom', labels: { color: VoltaChartTheme.colors.textSecondary, boxWidth: 10, padding: 14 } },
          tooltip: VoltaChartTheme.tooltip()
        },
        scales: {
          x: { ticks: VoltaChartTheme.ticks(9, 11), grid: VoltaChartTheme.gridLines() },
          y: { beginAtZero: true, ticks: VoltaChartTheme.ticks(9, 11), grid: VoltaChartTheme.gridLines() }
        }
      })
    : { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } };

  new Chart(chartEl.getContext('2d'), {
    type: 'line',
    data: { labels: chartData.labels || [], datasets: datasets },
    options: options
  });
});
</script>
@endpush
