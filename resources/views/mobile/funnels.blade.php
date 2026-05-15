@extends('layouts.app')

@section('title', 'Volta App - Pâlnie conversie - VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<style>
.mobile-funnels-page { display: flex; flex-direction: column; gap: 14px; }
.mobile-funnels-card {
  background: linear-gradient(160deg, rgba(26, 34, 48, 0.96) 0%, rgba(14, 19, 29, 0.98) 100%);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.24);
  position: relative;
  overflow: hidden;
}
.mobile-funnels-card::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 2px;
  background: linear-gradient(90deg, rgba(255, 238, 0, 0), rgba(255, 238, 0, 0.75), rgba(255, 238, 0, 0));
  pointer-events: none;
}
.mobile-funnels-card__head {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.14);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.mobile-funnels-card__head h2 {
  margin: 0;
  color: #fff;
  font-size: 0.97rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.mobile-funnels-card__head i { color: var(--brand, #FFEE00); }
.mobile-funnels-card__body { padding: 16px; }
.mobile-funnels-header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; }
.mobile-funnels-title h1 {
  margin: 0 0 5px;
  color: #fff;
  font-size: clamp(1.3rem, 2.1vw, 1.8rem);
  letter-spacing: -0.03em;
}
.mobile-funnels-title p { margin: 0; color: #94a3b8; font-size: 0.84rem; }
.mobile-funnels-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.mobile-funnels-field { display: flex; flex-direction: column; gap: 5px; }
.mobile-funnels-field label { color: #94a3b8; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
.mobile-funnels-field input {
  min-height: 40px; border-radius: 10px; border: 1px solid rgba(148, 163, 184, 0.28);
  background: rgba(15, 23, 42, 0.74); color: #e2e8f0; padding: 9px 11px; font: inherit;
}
.mobile-funnels-apply {
  min-height: 40px; border: 0; border-radius: 10px; padding: 0 14px;
  background: var(--brand, #FFEE00); color: #0f172a; font-weight: 800; cursor: pointer;
}
.mobile-funnels-alert {
  border: 1px solid rgba(255, 238, 0, 0.34); border-radius: 12px; padding: 12px 14px;
  background: rgba(255, 238, 0, 0.09); color: #fef08a; font-size: 0.84rem;
}
.mobile-funnels-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.mobile-funnels-kpi {
  border: 1px solid rgba(148, 163, 184, 0.17);
  border-radius: 14px;
  background: rgba(15, 23, 42, 0.52);
  padding: 12px;
  transition: transform 0.18s ease, border-color 0.2s ease;
}
.mobile-funnels-kpi:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 238, 0, 0.42);
}
.mobile-funnels-kpi .kpi-label { color: #94a3b8; font-size: 0.73rem; font-weight: 700; margin-bottom: 7px; display: block; }
.mobile-funnels-kpi .kpi-label i { color: rgba(255, 238, 0, 0.9); margin-right: 6px; }
.mobile-funnels-kpi .kpi-value { color: #fff; font-size: clamp(1.2rem, 2.4vw, 1.55rem); font-weight: 800; letter-spacing: -0.02em; }
.mobile-funnels-kpi .kpi-help { color: #94a3b8; font-size: 0.75rem; }
.mobile-funnels-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(320px, 0.85fr); gap: 14px; }
.mobile-funnels-chart-wrap { position: relative; min-height: 320px; }
.mobile-funnels-table-wrap { overflow-x: auto; }
.mobile-funnels-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.mobile-funnels-table th {
  text-align: left; color: #94a3b8; background: rgba(15, 23, 42, 0.62); font-size: 0.63rem;
  text-transform: uppercase; letter-spacing: 0.08em; padding: 9px 10px;
}
.mobile-funnels-table td { color: #e2e8f0; padding: 10px; border-top: 1px solid rgba(148, 163, 184, 0.13); }
.mobile-funnels-muted { color: #94a3b8; }
@media (max-width: 1100px) {
  .mobile-funnels-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .mobile-funnels-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .mobile-funnels-header { align-items: stretch; }
  .mobile-funnels-filters { width: 100%; }
  .mobile-funnels-field, .mobile-funnels-field input, .mobile-funnels-apply { width: 100%; }
  .mobile-funnels-kpis { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="mobile-funnels-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="mobile-funnels-alert">
      Tabela pentru evenimente mobile nu este încă creată în această bază locală. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <div class="mobile-funnels-card">
    <div class="mobile-funnels-card__body">
      <div class="mobile-funnels-header">
        <div class="mobile-funnels-title">
          <h1>Pâlnie de conversie</h1>
          <p>Parcursul de la vizită la comandă și punctele principale de drop-off.</p>
        </div>
        <form method="get" action="{{ route('mobile.analytics.funnels') }}" class="mobile-funnels-filters">
          <div class="mobile-funnels-field">
            <label for="mobileFunnelsStart">De la</label>
            <input id="mobileFunnelsStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
          </div>
          <div class="mobile-funnels-field">
            <label for="mobileFunnelsEnd">Până la</label>
            <input id="mobileFunnelsEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
          </div>
          <button class="mobile-funnels-apply" type="submit">Aplică</button>
        </form>
      </div>
    </div>
  </div>

  <div class="mobile-funnels-kpis">
    <div class="mobile-funnels-kpi">
      <span class="kpi-label"><i class="fas fa-arrow-right"></i> Vizite → Checkout</span>
      <div class="kpi-value">{{ number_format((float) $funnel['visit_to_checkout_rate'], 1, ',', '.') }}%</div>
      <span class="kpi-help">{{ number_format($funnel['checkout_started'], 0, ',', '.') }} checkout-uri din {{ number_format($funnel['visits'], 0, ',', '.') }} vizite</span>
    </div>
    <div class="mobile-funnels-kpi">
      <span class="kpi-label"><i class="fas fa-bag-shopping"></i> Checkout → Comandă</span>
      <div class="kpi-value">{{ number_format((float) $funnel['checkout_to_order_rate'], 1, ',', '.') }}%</div>
      <span class="kpi-help">{{ number_format($funnel['orders_completed'], 0, ',', '.') }} comenzi finalizate</span>
    </div>
    <div class="mobile-funnels-kpi">
      <span class="kpi-label"><i class="fas fa-person-falling"></i> Drop-off după checkout</span>
      <div class="kpi-value">{{ number_format((float) $funnel['dropoff_after_checkout_rate'], 1, ',', '.') }}%</div>
      <span class="kpi-help">{{ number_format($funnel['cart_abandoned'], 0, ',', '.') }} abandonuri în funnel</span>
    </div>
    <div class="mobile-funnels-kpi">
      <span class="kpi-label"><i class="fas fa-rotate-right"></i> Rată de recuperare</span>
      <div class="kpi-value">{{ number_format((float) $funnel['recovery_rate'], 1, ',', '.') }}%</div>
      <span class="kpi-help">Raport comenzi finalizate vs abandon + comenzi</span>
    </div>
  </div>

  <div class="mobile-funnels-grid">
    <section class="mobile-funnels-card">
      <div class="mobile-funnels-card__head">
        <h2><i class="fas fa-chart-column"></i> Funnel principal</h2>
      </div>
      <div class="mobile-funnels-card__body">
        <div class="mobile-funnels-chart-wrap">
          <canvas id="mobileFunnelChart"></canvas>
        </div>
      </div>
    </section>

    <section class="mobile-funnels-card">
      <div class="mobile-funnels-card__head">
        <h2><i class="fas fa-list-ol"></i> Volum pe etapă</h2>
      </div>
      <div class="mobile-funnels-card__body mobile-funnels-table-wrap">
        <table class="mobile-funnels-table">
          <thead>
            <tr><th>Etapă</th><th>Total</th></tr>
          </thead>
          <tbody>
            <tr><td>Vizite pagini</td><td>{{ number_format($funnel['visits'], 0, ',', '.') }}</td></tr>
            <tr><td>Checkout început</td><td>{{ number_format($funnel['checkout_started'], 0, ',', '.') }}</td></tr>
            <tr><td>Checkout completat</td><td>{{ number_format($funnel['checkout_completed'], 0, ',', '.') }}</td></tr>
            <tr><td>Comenzi finalizate</td><td>{{ number_format($funnel['orders_completed'], 0, ',', '.') }}</td></tr>
            <tr><td>Abandonuri coș</td><td>{{ number_format($funnel['cart_abandoned'], 0, ',', '.') }}</td></tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="mobile-funnels-card">
    <div class="mobile-funnels-card__head">
      <h2><i class="fas fa-cart-arrow-down"></i> Unde se părăsește coșul</h2>
    </div>
    <div class="mobile-funnels-card__body mobile-funnels-table-wrap">
      <table class="mobile-funnels-table">
        <thead>
          <tr><th>Pas checkout</th><th>Abandonuri</th><th>Total mediu coș</th><th>Produse medii</th></tr>
        </thead>
        <tbody>
        @forelse($cartAbandons as $row)
          <tr>
            <td>Pas {{ $row->checkout_step ?: '?' }}</td>
            <td>{{ number_format((int) $row->abandons, 0, ',', '.') }}</td>
            <td>{{ $row->avg_cart_total !== null ? number_format((float) $row->avg_cart_total, 2, ',', '.') . ' MDL' : '-' }}</td>
            <td>{{ $row->avg_items_count !== null ? number_format((float) $row->avg_items_count, 1, ',', '.') : '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="mobile-funnels-muted">Nu există abandonuri pentru perioada selectată.</td></tr>
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
  var labels = ['Vizite', 'Checkout început', 'Checkout completat', 'Comenzi'];
  var values = [
    funnel.visits || 0,
    funnel.checkout_started || 0,
    funnel.checkout_completed || 0,
    funnel.orders_completed || 0
  ];

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        indexAxis: 'y',
        plugins: {
          legend: { display: false },
          tooltip: VoltaChartTheme.tooltip()
        },
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
        borderWidth: 1.4,
        borderColor: 'rgba(255, 238, 0, 0.72)',
        backgroundColor: [
          'rgba(255, 238, 0, 0.85)',
          'rgba(250, 204, 21, 0.75)',
          'rgba(203, 213, 225, 0.6)',
          'rgba(16, 185, 129, 0.72)'
        ]
      }]
    },
    options: options
  });
});
</script>
@endpush
