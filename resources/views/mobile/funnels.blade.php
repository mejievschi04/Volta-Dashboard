@extends('layouts.app')

@section('title', 'Volta App – Pâlnie conversie – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $steps = [
    ['key' => 'visits', 'label' => '1. Vizite pagini', 'value' => (int) ($funnel['visits'] ?? 0)],
    ['key' => 'product_views', 'label' => '2. Vizualizări produs', 'value' => (int) ($funnel['product_views'] ?? 0), 'rate' => $funnel['visit_to_product_rate'] ?? 0, 'rateLabel' => 'din vizite'],
    ['key' => 'add_to_cart', 'label' => '3. Adăugat în coș', 'value' => (int) ($funnel['add_to_cart'] ?? 0), 'rate' => $funnel['product_to_cart_rate'] ?? 0, 'rateLabel' => 'din produse'],
    ['key' => 'checkout_started', 'label' => '4. Checkout început', 'value' => (int) ($funnel['checkout_started'] ?? 0), 'rate' => $funnel['cart_to_checkout_rate'] ?? 0, 'rateLabel' => 'din coș'],
    ['key' => 'orders_completed', 'label' => '5. Comenzi finalizate', 'value' => (int) ($funnel['orders_completed'] ?? 0), 'rate' => $funnel['checkout_to_order_rate'] ?? 0, 'rateLabel' => 'din checkout'],
  ];
  $maxStep = max(1, ...array_column($steps, 'value'));
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Pâlnie de conversie</h1>
        <p class="ma-hero__lead">
          Parcursul de la vizită la comandă și punctele unde utilizatorii abandonează.
        </p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.funnels') }}" class="ma-filters">
        <div class="ma-field">
          <label for="funnelStart">De la</label>
          <input id="funnelStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="funnelEnd">Până la</label>
          <input id="funnelEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-eye" aria-hidden="true"></i> Vizite → Produs</span>
      <div class="ma-kpi__value">{{ number_format((float) ($funnel['visit_to_product_rate'] ?? 0), 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($funnel['product_views'] ?? 0, 0, ',', '.') }} / {{ number_format($funnel['visits'], 0, ',', '.') }}</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-cart-plus" aria-hidden="true"></i> Produs → Coș</span>
      <div class="ma-kpi__value">{{ number_format((float) ($funnel['product_to_cart_rate'] ?? 0), 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($funnel['add_to_cart'] ?? 0, 0, ',', '.') }} add-to-cart</span>
    </div>
    <div class="ma-kpi ma-kpi--good">
      <span class="ma-kpi__label"><i class="fas fa-bag-shopping" aria-hidden="true"></i> Checkout → Comandă</span>
      <div class="ma-kpi__value">{{ number_format((float) $funnel['checkout_to_order_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($funnel['orders_completed'], 0, ',', '.') }} comenzi</span>
    </div>
    <div class="ma-kpi ma-kpi--warn">
      <span class="ma-kpi__label"><i class="fas fa-person-falling" aria-hidden="true"></i> Drop-off după checkout</span>
      <div class="ma-kpi__value">{{ number_format((float) $funnel['dropoff_after_checkout_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($funnel['cart_abandoned'], 0, ',', '.') }} abandonuri · recuperare {{ number_format((float) $funnel['recovery_rate'], 1, ',', '.') }}%</span>
    </div>
  </div>

  <div class="ma-grid">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-stairs" aria-hidden="true"></i> Etape funnel</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-funnel">
          @foreach($steps as $step)
            @php $width = round(($step['value'] / $maxStep) * 100); @endphp
            <div class="ma-funnel__step">
              <div class="ma-funnel__meta">
                <span class="ma-funnel__name">{{ $step['label'] }}</span>
                <span class="ma-funnel__count">{{ number_format($step['value'], 0, ',', '.') }}</span>
              </div>
              <div class="ma-funnel__bar"><div class="ma-funnel__fill" style="width: {{ $width }}%;"></div></div>
              @if(isset($step['rate']))
                <div class="ma-funnel__rate">Conversie {{ number_format((float) $step['rate'], 1, ',', '.') }}% {{ $step['rateLabel'] }}</div>
              @endif
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-chart-column" aria-hidden="true"></i> Comparație vizuală</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="mobileFunnelChart"></canvas></div>
      </div>
    </section>
  </div>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-cart-arrow-down" aria-hidden="true"></i> Unde se părăsește coșul</h2>
      <a class="ma-card__link" href="{{ route('mobile.analytics.abandon', $q) }}">Listă completă →</a>
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead>
          <tr>
            <th>Pas checkout</th>
            <th class="num">Abandonuri</th>
            <th class="num">Total mediu coș</th>
            <th class="num">Produse medii</th>
          </tr>
        </thead>
        <tbody>
        @forelse($cartAbandons as $row)
          <tr>
            <td>Pas {{ $row->checkout_step ?: '?' }}</td>
            <td class="num">{{ number_format((int) $row->abandons, 0, ',', '.') }}</td>
            <td class="num">{{ $row->avg_cart_total !== null ? number_format((float) $row->avg_cart_total, 2, ',', '.') . ' MDL' : '—' }}</td>
            <td class="num">{{ $row->avg_items_count !== null ? number_format((float) $row->avg_items_count, 1, ',', '.') : '—' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="ma-muted">Nu există abandonuri pentru perioada selectată.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var chartEl = document.getElementById('mobileFunnelChart');
  if (!chartEl || typeof Chart === 'undefined') return;

  var funnel = @json($funnel);
  var labels = ['Vizite', 'Produse', 'În coș', 'Checkout', 'Comenzi'];
  var values = [
    funnel.visits || 0,
    funnel.product_views || 0,
    funnel.add_to_cart || 0,
    funnel.checkout_started || 0,
    funnel.orders_completed || 0
  ];

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        indexAxis: 'y',
        plugins: { legend: { display: false }, tooltip: VoltaChartTheme.tooltip() },
        scales: {
          x: { beginAtZero: true, ticks: VoltaChartTheme.ticks(9, 12), grid: VoltaChartTheme.gridLines() },
          y: { ticks: VoltaChartTheme.ticks(9, 12), grid: { display: false } }
        }
      })
    : { responsive: true, maintainAspectRatio: false, indexAxis: 'y', scales: { x: { beginAtZero: true } } };

  new Chart(chartEl.getContext('2d'), {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        data: values,
        borderRadius: 8,
        borderSkipped: false,
        borderWidth: 1.2,
        borderColor: 'rgba(255, 238, 0, 0.55)',
        backgroundColor: [
          'rgba(255, 238, 0, 0.85)',
          'rgba(167, 139, 250, 0.7)',
          'rgba(56, 189, 248, 0.7)',
          'rgba(250, 204, 21, 0.7)',
          'rgba(16, 185, 129, 0.72)'
        ]
      }]
    },
    options: options
  });
});
</script>
@endpush
