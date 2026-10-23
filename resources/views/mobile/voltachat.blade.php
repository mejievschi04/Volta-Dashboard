@extends('layouts.app')

@section('title', 'VoltaChat – VOLTA')

@section('content')
@php
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = \App\Support\MobileRetention::presets();
@endphp

<div class="ma-page">
  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Dialoguri cu clienții</p>
        <h1 class="ma-hero__title">VoltaChat</h1>
        <p class="ma-hero__lead">
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} · {{ $days }} {{ $days === 1 ? 'zi' : 'zile' }}
        </p>
      </div>
      <form method="get" action="{{ route('voltachat') }}" class="ma-filters">
        <div class="ma-field">
          <label for="vcStart">De la</label>
          <input id="vcStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="vcEnd">Până la</label>
          <input id="vcEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('voltachat', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
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
    return { labels: set.labels, data: set.data.map(function (v) { return v === null ? null : Math.round(Number(v) * 10) / 10; }) };
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
