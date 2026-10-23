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

  <section class="ma-section vc-section" id="voltaChatSection"
           data-url="{{ route('aplicatia-volta.voltachat', ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}">
    <div class="ma-section__head">
      <h2><i class="fas fa-comments" aria-hidden="true"></i> VoltaChat</h2>
      <p>Se încarcă statistica dialogurilor…</p>
    </div>
    <div class="vc-loading"><i class="fas fa-circle-notch fa-spin" aria-hidden="true"></i></div>
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

document.addEventListener('DOMContentLoaded', function () {
  var section = document.getElementById('voltaChatSection');
  if (!section) return;

  var baseUrl = section.getAttribute('data-url');
  var charts = [];
  var chatParams = {};
  var hasTheme = typeof VoltaChartTheme !== 'undefined';
  var palette = hasTheme && VoltaChartTheme.getSeriesPalette ? VoltaChartTheme.getSeriesPalette() : null;
  var colors = {
    a: palette ? palette.amber : { line: 'rgb(250, 204, 21)', area: 'rgba(250, 204, 21, 0.14)' },
    b: palette ? palette.cyan : { line: 'rgb(34, 211, 238)', area: 'rgba(34, 211, 238, 0.14)' },
    c: palette ? palette.violet : { line: 'rgb(167, 139, 250)', area: 'rgba(167, 139, 250, 0.14)' },
    d: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' }
  };

  function label(value) {
    var m = /^(\d{4})-(\d{2})(?:-(\d{2}))?$/.exec(String(value));
    if (!m) return value;
    return m[3] ? m[3] + '.' + m[2] : m[2] + '.' + m[1];
  }

  function line(set, name, color, extra) {
    return Object.assign({
      label: name,
      data: (set && set.data) || [],
      borderColor: color.line,
      backgroundColor: color.area,
      borderWidth: 2.2,
      tension: 0.32,
      pointRadius: 0,
      pointHoverRadius: 4
    }, extra || {});
  }

  function options(extraScales) {
    var scales = Object.assign({
      x: { ticks: hasTheme ? VoltaChartTheme.ticks(9, 11) : {}, grid: hasTheme ? VoltaChartTheme.gridLines() : {} },
      y: { beginAtZero: true, ticks: hasTheme ? VoltaChartTheme.ticks(9, 11) : {}, grid: hasTheme ? VoltaChartTheme.gridLines() : {} }
    }, extraScales || {});
    var base = {
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { position: 'bottom', labels: { color: hasTheme ? VoltaChartTheme.colors.textSecondary : undefined, boxWidth: 10, padding: 14 } },
        tooltip: hasTheme ? VoltaChartTheme.tooltip() : {}
      },
      scales: scales
    };
    return hasTheme ? VoltaChartTheme.cartesianDefaults(base) : Object.assign({ responsive: true, maintainAspectRatio: false }, base);
  }

  function toPercent(set) {
    if (!set || !Array.isArray(set.data)) return set;
    var fraction = set.data.every(function (v) { return v === null || Number(v) <= 1; });
    return { labels: set.labels, data: set.data.map(function (v) { return v === null ? null : Math.round(Number(v) * (fraction ? 100 : 1) * 10) / 10; }) };
  }

  function renderCharts() {
    charts.forEach(function (c) { c.destroy(); });
    charts = [];
    var node = section.querySelector('[data-vc-charts]');
    if (!node || typeof Chart === 'undefined') return;
    var data = JSON.parse(node.textContent || '{}');

    var trend = document.getElementById('vcTrendChart');
    if (trend && data.conversations) {
      charts.push(new Chart(trend.getContext('2d'), {
        type: 'line',
        data: {
          labels: (data.conversations.labels || []).map(label),
          datasets: [
            line(data.conversations, 'Dialoguri', colors.a, { fill: true }),
            line(data.unique_customers, 'Clienți unici', colors.b),
            line(data.messages, 'Mesaje', colors.c, { yAxisID: 'y1', borderDash: [5, 4] })
          ]
        },
        options: options({
          y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: hasTheme ? VoltaChartTheme.ticks(9, 11) : {} }
        })
      }));
    }

    var read = document.getElementById('vcReadChart');
    var inbound = toPercent(data.read_inbound);
    var outbound = toPercent(data.read_outbound);
    if (read && (inbound || outbound)) {
      var readOptions = options();
      readOptions.scales.y.max = 100;
      readOptions.scales.y.ticks = Object.assign({}, readOptions.scales.y.ticks, { callback: function (v) { return v + '%'; } });
      charts.push(new Chart(read.getContext('2d'), {
        type: 'line',
        data: {
          labels: ((inbound || outbound).labels || []).map(label),
          datasets: [
            line(inbound, 'Citite de operatori', colors.d),
            line(outbound, 'Citite de clienți', colors.b)
          ]
        },
        options: readOptions
      }));
    }
  }

  function load() {
    var url = new URL(baseUrl, window.location.origin);
    Object.keys(chatParams).forEach(function (k) {
      if (chatParams[k] !== '' && chatParams[k] != null) url.searchParams.set(k, chatParams[k]);
    });
    section.classList.add('is-loading');
    fetch(url.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' })
      .then(function (r) { if (!r.ok) throw new Error(r.status); return r.text(); })
      .then(function (html) {
        section.innerHTML = html;
        renderCharts();
      })
      .catch(function () {
        section.innerHTML = '<div class="ma-section__head"><h2><i class="fas fa-comments" aria-hidden="true"></i> VoltaChat</h2></div>'
          + '<div class="ma-alert ma-alert--soft"><strong>Statistica VoltaChat nu s-a putut încărca.</strong> Reîncărcați pagina.</div>';
      })
      .finally(function () { section.classList.remove('is-loading'); });
  }

  section.addEventListener('change', function (e) {
    var form = e.target.closest('[data-vc-filters]');
    if (!form) return;
    new FormData(form).forEach(function (v, k) { chatParams[k] = v; });
    load();
  });

  section.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-vc-sort]');
    if (!btn) return;
    chatParams.chat_sort = btn.getAttribute('data-vc-sort');
    load();
  });

  load();
});
</script>
@endpush
