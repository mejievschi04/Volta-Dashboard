@extends('layouts.app')

@section('title', 'Aplicația Volta – VOLTA')

@section('content')
@php
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = \App\Support\MobileRetention::presets();
  $fmt = fn ($value) => number_format((int) $value, 0, ',', '.');
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">Datele din aplicație nu sunt încă disponibile.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Statistica principală</p>
        <h1 class="ma-hero__title">Aplicația Volta</h1>
        <p class="ma-hero__lead">
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} · {{ $days }} {{ $days === 1 ? 'zi' : 'zile' }}
        </p>
      </div>
      <form method="get" action="{{ route('aplicatia-volta') }}" class="ma-filters">
        <div class="ma-field">
          <label for="avStart">De la</label>
          <input id="avStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="avEnd">Până la</label>
          <input id="avEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('aplicatia-volta', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  <section class="ma-section">
    <div class="ma-kpis">
      <div class="ma-kpi ma-kpi--accent">
        <span class="ma-kpi__label"><i class="fas fa-user" aria-hidden="true"></i> Utilizatori</span>
        <div class="ma-kpi__value">{{ $fmt($summary['devices']) }}</div>
        <span class="ma-kpi__help">{{ $fmt($summary['users']) }} cu cont · restul fără autentificare</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Sesiuni</span>
        <div class="ma-kpi__value">{{ $fmt($summary['sessions']) }}</div>
        <span class="ma-kpi__help">De câte ori a fost deschisă aplicația</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-file-lines" aria-hidden="true"></i> Pagini deschise</span>
        <div class="ma-kpi__value">{{ $fmt($summary['page_views']) }}</div>
        <span class="ma-kpi__help">{{ $fmt($summary['avg_page_seconds']) }} secunde pe pagină, în medie</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-id-card" aria-hidden="true"></i> Carduri generate</span>
        <div class="ma-kpi__value">{{ $fmt($summary['cards_generated'] ?? 0) }}</div>
        <span class="ma-kpi__help">Carduri de reducere create din profil</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-box-open" aria-hidden="true"></i> Produse privite</span>
        <div class="ma-kpi__value">{{ $fmt($summary['product_views']) }}</div>
        <span class="ma-kpi__help">{{ $fmt($summary['searches']) }} căutări</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-cart-plus" aria-hidden="true"></i> Adăugat în coș</span>
        <div class="ma-kpi__value">{{ $fmt($summary['add_to_cart']) }}</div>
        <span class="ma-kpi__help">{{ $fmt($summary['cart_abandons']) }} coșuri părăsite</span>
      </div>
      <div class="ma-kpi ma-kpi--good">
        <span class="ma-kpi__label"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Comenzi</span>
        <div class="ma-kpi__value">{{ $fmt($summary['orders']) }}</div>
        <span class="ma-kpi__help">{{ number_format($summary['conversion_rate'] ?? 0, 2, ',', '.') }}% din sesiuni</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-rectangle-ad" aria-hidden="true"></i> Click bannere</span>
        <div class="ma-kpi__value">{{ $fmt($summary['banner_clicks']) }}</div>
        <span class="ma-kpi__help">Total click-uri pe bannerele din aplicație</span>
      </div>
    </div>
  </section>

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Evoluție pe zile</h2>
      <p>Pagini deschise, coș, comenzi și carduri generate</p>
    </div>
    <section class="ma-card">
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="appSummaryChart"></canvas></div>
      </div>
    </section>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var chartEl = document.getElementById('appSummaryChart');
  if (!chartEl || typeof Chart === 'undefined') return;

  var chartData = @json($dailyChart);
  var palette = (typeof VoltaChartTheme !== 'undefined' && VoltaChartTheme.getSeriesPalette)
    ? VoltaChartTheme.getSeriesPalette()
    : null;
  var series = [
    { key: 'page_view', label: 'Pagini deschise', color: palette ? palette.amber : { line: 'rgb(250, 204, 21)', area: 'rgba(250, 204, 21, 0.14)' }, fill: true },
    { key: 'add_to_cart', label: 'Adăugat în coș', color: palette ? palette.cyan : { line: 'rgb(180, 188, 204)', area: 'rgba(180, 188, 204, 0.16)' } },
    { key: 'order_completed', label: 'Comenzi', color: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' } },
    { key: 'discount_card_generate_success', label: 'Carduri generate', color: palette ? palette.violet : { line: 'rgb(167, 139, 250)', area: 'rgba(167, 139, 250, 0.16)' } }
  ];

  var datasets = series.map(function (s) {
    return {
      label: s.label,
      data: (chartData.datasets || {})[s.key] || [],
      borderColor: s.color.line,
      backgroundColor: s.color.area,
      borderWidth: 2.2,
      tension: 0.32,
      fill: !!s.fill,
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
