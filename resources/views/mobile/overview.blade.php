@extends('layouts.app')

@section('title', 'Volta App – Magazin – VOLTA')
@section('header-title', 'Volta App')

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = \App\Support\MobileRetention::presets();
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">
      Tabela pentru evenimente din aplicație nu este încă creată. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">Magazin</h1>
        <p class="ma-hero__lead">
          Cine a venit în magazinul din telefon și ce s-a întâmplat cu coșul:
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} · {{ $days }} {{ $days === 1 ? 'zi' : 'zile' }}.
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

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Cine a venit</h2>
      <p>Vizitatori și sesiuni</p>
    </div>
    <div class="ma-kpis">
      <div class="ma-kpi ma-kpi--accent">
        <span class="ma-kpi__label"><i class="fas fa-user" aria-hidden="true"></i> Vizitatori</span>
        <div class="ma-kpi__value">{{ number_format($summary['users'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help">Persoane distincte cu cont în app</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-arrow-right-to-bracket" aria-hidden="true"></i> Sesiuni</span>
        <div class="ma-kpi__value">{{ number_format($summary['sessions'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help">{{ number_format($summary['events_per_session'] ?? 0, 1, ',', '.') }} acțiuni pe sesiune</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-mobile-screen" aria-hidden="true"></i> Dispozitive</span>
        <div class="ma-kpi__value">{{ number_format($summary['devices'] ?? 0, 0, ',', '.') }}</div>
        <span class="ma-kpi__help">Telefoane distincte</span>
      </div>
      <div class="ma-kpi ma-kpi--good">
        <span class="ma-kpi__label"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Comenzi din sesiuni</span>
        <div class="ma-kpi__value">{{ number_format($summary['conversion_rate'] ?? 0, 2, ',', '.') }}%</div>
        <span class="ma-kpi__help">{{ number_format($summary['orders'], 0, ',', '.') }} comenzi finalizate</span>
      </div>
    </div>
  </section>

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Coș</h2>
      <p>Ce s-a pus în coș, ce s-a părăsit, ce s-a comandat</p>
    </div>
    <div class="ma-kpis">
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-box-open" aria-hidden="true"></i> Produse privite</span>
        <div class="ma-kpi__value">{{ number_format($summary['product_views'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help">{{ number_format($summary['searches'], 0, ',', '.') }} căutări</span>
      </div>
      <div class="ma-kpi">
        <span class="ma-kpi__label"><i class="fas fa-cart-plus" aria-hidden="true"></i> Adăugat în coș</span>
        <div class="ma-kpi__value">{{ number_format($summary['add_to_cart'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help">{{ number_format($summary['view_to_cart_rate'] ?? 0, 1, ',', '.') }}% din produsele privite</span>
      </div>
      <div class="ma-kpi ma-kpi--warn">
        <span class="ma-kpi__label"><i class="fas fa-cart-arrow-down" aria-hidden="true"></i> Coșuri părăsite</span>
        <div class="ma-kpi__value">{{ number_format($summary['cart_abandons'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help"><a class="ma-card__link" href="{{ route('mobile.analytics.funnels', $q) }}">Vezi drumul spre comandă →</a></span>
      </div>
      <div class="ma-kpi ma-kpi--good">
        <span class="ma-kpi__label"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Comenzi</span>
        <div class="ma-kpi__value">{{ number_format($summary['orders'], 0, ',', '.') }}</div>
        <span class="ma-kpi__help">Finalizate în aplicație</span>
      </div>
    </div>
  </section>

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Evoluție</h2>
      <p>Pagini, coș și comenzi pe zile</p>
    </div>
    <section class="ma-card">
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="mobileOverviewChart"></canvas></div>
      </div>
    </section>
  </section>

  <section class="ma-section">
    <div class="ma-section__head">
      <h2>Ce caută oamenii</h2>
      <p>Căutări și produse vizitate</p>
    </div>
    <div class="ma-grid ma-grid--2">
      <section class="ma-card">
        <div class="ma-card__head">
          <h2><i class="fas fa-magnifying-glass" aria-hidden="true"></i> Căutări</h2>
        </div>
        <div class="ma-card__body">
          @if(($topSearches ?? collect())->isEmpty())
            <div class="ma-empty"><i class="fas fa-magnifying-glass" aria-hidden="true"></i>Nu există căutări în interval.</div>
          @else
            <div class="ma-table-wrap">
              <table class="ma-table">
                <thead><tr><th>Căutare</th><th class="num">De câte ori</th></tr></thead>
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
          <h2><i class="fas fa-box-open" aria-hidden="true"></i> Produse privite</h2>
        </div>
        <div class="ma-card__body">
          @if(($topProducts ?? collect())->isEmpty())
            <div class="ma-empty"><i class="fas fa-box-open" aria-hidden="true"></i>Nu există vizualizări de produs.</div>
          @else
            <div class="ma-table-wrap">
              <table class="ma-table">
                <thead><tr><th>Produs</th><th class="num">De câte ori</th></tr></thead>
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
  </section>
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
    add_to_cart: palette ? palette.amber : { line: 'rgb(255, 238, 0)', area: 'rgba(255, 238, 0, 0.12)' },
    cart_abandoned: palette ? palette.rose : { line: 'rgb(244, 63, 94)', area: 'rgba(244, 63, 94, 0.12)' },
    order_completed: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' }
  };
  var names = {
    page_view: 'Pagini deschise',
    add_to_cart: 'Adăugat în coș',
    cart_abandoned: 'Coș părăsit',
    order_completed: 'Comenzi'
  };
  var keep = { page_view: 1, add_to_cart: 1, cart_abandoned: 1, order_completed: 1 };

  var datasets = Object.keys(chartData.datasets || {}).filter(function (key) {
    return keep[key];
  }).map(function (key) {
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
