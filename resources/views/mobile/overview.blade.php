@extends('layouts.app')

@section('title', 'Volta App - Prezentare - VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<style>
.mobile-page { display: flex; flex-direction: column; gap: 16px; }
.mobile-card {
  background: linear-gradient(160deg, rgba(26, 34, 48, 0.96) 0%, rgba(14, 19, 29, 0.98) 100%);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.24);
  position: relative;
  overflow: hidden;
}
.mobile-card::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 2px;
  background: linear-gradient(90deg, rgba(255, 238, 0, 0), rgba(255, 238, 0, 0.75), rgba(255, 238, 0, 0));
  pointer-events: none;
}
.mobile-card__head {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.14);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.mobile-card__head h2 {
  margin: 0;
  font-size: 0.98rem;
  color: #f8fafc;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.mobile-card__head i { color: var(--brand, #FFEE00); }
.mobile-card__body { padding: 16px; }
.mobile-header {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 14px;
}
.mobile-title h1 {
  margin: 0 0 5px;
  color: #fff;
  font-size: clamp(1.35rem, 2.2vw, 1.9rem);
  letter-spacing: -0.03em;
}
.mobile-title p { margin: 0; color: #94a3b8; font-size: 0.86rem; }
.mobile-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.mobile-field { display: flex; flex-direction: column; gap: 5px; }
.mobile-field label {
  color: #94a3b8; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase;
}
.mobile-field input {
  min-height: 40px; border-radius: 10px; border: 1px solid rgba(148, 163, 184, 0.28);
  background: rgba(15, 23, 42, 0.74); color: #e2e8f0; padding: 9px 11px; font: inherit;
}
.mobile-apply {
  min-height: 40px; border: 0; border-radius: 10px; padding: 0 14px;
  background: var(--brand, #FFEE00); color: #0f172a; font-weight: 800; cursor: pointer;
}
.mobile-alert {
  border: 1px solid rgba(255, 238, 0, 0.34); border-radius: 12px; padding: 12px 14px;
  background: rgba(255, 238, 0, 0.09); color: #fef08a; font-size: 0.84rem;
}
.mobile-alert code {
  background: rgba(15, 23, 42, 0.55); border: 1px solid rgba(148, 163, 184, 0.24);
  color: #f8fafc; padding: 2px 6px; border-radius: 6px;
}
.mobile-kpis { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.mobile-kpi {
  border: 1px solid rgba(148, 163, 184, 0.17);
  border-radius: 14px;
  background: rgba(15, 23, 42, 0.52);
  padding: 12px;
  transition: transform 0.18s ease, border-color 0.2s ease;
}
.mobile-kpi:hover {
  transform: translateY(-2px);
  border-color: rgba(255, 238, 0, 0.42);
}
.mobile-kpi .kpi-label { color: #94a3b8; font-size: 0.73rem; font-weight: 700; margin-bottom: 7px; display: block; }
.mobile-kpi .kpi-label i { color: rgba(255, 238, 0, 0.9); margin-right: 6px; }
.mobile-kpi .kpi-value { color: #fff; font-size: clamp(1.2rem, 2.4vw, 1.55rem); font-weight: 800; letter-spacing: -0.02em; }
.mobile-kpi .kpi-help { color: #94a3b8; font-size: 0.75rem; }
.mobile-grid { display: grid; grid-template-columns: minmax(0, 1.45fr) minmax(320px, 0.85fr); gap: 14px; }
.mobile-chart-wrap { position: relative; min-height: 320px; }
.mobile-table-wrap { overflow-x: auto; }
.mobile-table { width: 100%; border-collapse: collapse; font-size: 0.81rem; }
.mobile-table th {
  text-align: left; color: #94a3b8; background: rgba(15, 23, 42, 0.62); font-size: 0.63rem;
  text-transform: uppercase; letter-spacing: 0.08em; padding: 9px 10px;
}
.mobile-table td { color: #e2e8f0; padding: 10px; border-top: 1px solid rgba(148, 163, 184, 0.13); }
.mobile-badge {
  display: inline-flex; align-items: center; border-radius: 999px;
  border: 1px solid rgba(255, 238, 0, 0.28); background: rgba(255, 238, 0, 0.09);
  color: #fde047; padding: 3px 8px; font-size: 0.67rem; font-weight: 800;
}
.mobile-empty {
  border: 1px dashed rgba(148, 163, 184, 0.26); border-radius: 12px; padding: 14px;
  color: #94a3b8; background: rgba(15, 23, 42, 0.34); font-size: 0.82rem;
}
@media (max-width: 1100px) {
  .mobile-kpis { grid-template-columns: repeat(2, minmax(0, 1fr)); }
  .mobile-grid { grid-template-columns: 1fr; }
}
@media (max-width: 640px) {
  .mobile-header { align-items: stretch; }
  .mobile-filters { width: 100%; }
  .mobile-field, .mobile-field input, .mobile-apply { width: 100%; }
  .mobile-kpis { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="mobile-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="mobile-alert">
      Tabela pentru evenimente mobile nu este încă creată în această bază locală. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <div class="mobile-card">
    <div class="mobile-card__body">
      <div class="mobile-header">
        <div class="mobile-title">
          <h1>Prezentare statistici mobile</h1>
          <p>KPI principal, trend zilnic și distribuția tipurilor de evenimente.</p>
        </div>
        <form method="get" action="{{ route('mobile.analytics') }}" class="mobile-filters">
          <div class="mobile-field">
            <label for="mobileOverviewStart">De la</label>
            <input id="mobileOverviewStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
          </div>
          <div class="mobile-field">
            <label for="mobileOverviewEnd">Până la</label>
            <input id="mobileOverviewEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
          </div>
          <button class="mobile-apply" type="submit">Aplică</button>
        </form>
      </div>
    </div>
  </div>

  <div class="mobile-kpis">
    <div class="mobile-kpi">
      <span class="kpi-label"><i class="fas fa-bolt"></i> Evenimente</span>
      <div class="kpi-value">{{ number_format($summary['events'], 0, ',', '.') }}</div>
      <span class="kpi-help">Total evenimente pe interval</span>
    </div>
    <div class="mobile-kpi">
      <span class="kpi-label"><i class="fas fa-users"></i> Sesiuni active</span>
      <div class="kpi-value">{{ number_format($summary['sessions'], 0, ',', '.') }}</div>
      <span class="kpi-help">{{ number_format($summary['users'], 0, ',', '.') }} utilizatori identificați</span>
    </div>
    <div class="mobile-kpi">
      <span class="kpi-label"><i class="fas fa-clock"></i> Timp mediu / pagină</span>
      <div class="kpi-value">{{ number_format($summary['avg_page_seconds'], 0, ',', '.') }}s</div>
      <span class="kpi-help">Pe baza evenimentelor cu durată</span>
    </div>
    <div class="mobile-kpi">
      <span class="kpi-label"><i class="fas fa-cart-shopping"></i> Comenzi completate</span>
      <div class="kpi-value">{{ number_format($summary['orders'], 0, ',', '.') }}</div>
      <span class="kpi-help">{{ number_format($summary['cart_abandons'], 0, ',', '.') }} abandonuri de coș</span>
    </div>
  </div>

  <div class="mobile-grid">
    <section class="mobile-card">
      <div class="mobile-card__head">
        <h2><i class="fas fa-chart-line"></i> Evoluție evenimente pe zile</h2>
      </div>
      <div class="mobile-card__body">
        <div class="mobile-chart-wrap">
          <canvas id="mobileOverviewChart"></canvas>
        </div>
      </div>
    </section>

    <section class="mobile-card">
      <div class="mobile-card__head">
        <h2><i class="fas fa-list-check"></i> Top tipuri de evenimente</h2>
      </div>
      <div class="mobile-card__body">
        @if($eventBreakdown->isEmpty())
          <div class="mobile-empty">Nu există încă evenimente pentru perioada selectată.</div>
        @else
          <div class="mobile-table-wrap">
            <table class="mobile-table">
              <thead><tr><th>Eveniment</th><th>Total</th></tr></thead>
              <tbody>
              @foreach($eventBreakdown->take(12) as $row)
                <tr>
                  <td><span class="mobile-badge">{{ $row->event_name }}</span></td>
                  <td>{{ number_format($row->total, 0, ',', '.') }}</td>
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
    banner_click: palette ? palette.cyan : { line: 'rgb(203, 213, 225)', area: 'rgba(203, 213, 225, 0.12)' },
    cart_abandoned: palette ? palette.rose : { line: 'rgb(244, 63, 94)', area: 'rgba(244, 63, 94, 0.12)' },
    order_completed: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' }
  };
  var names = {
    page_view: 'Pagini',
    banner_click: 'Click bannere',
    cart_abandoned: 'Abandon coș',
    order_completed: 'Comenzi'
  };

  var datasets = Object.keys(chartData.datasets || {}).map(function (key) {
    return {
      label: names[key] || key,
      data: chartData.datasets[key] || [],
      borderColor: colors[key].line,
      backgroundColor: colors[key].area,
      borderWidth: 2.4,
      tension: 0.35,
      fill: key === 'page_view',
      pointRadius: 2,
      pointHoverRadius: 4
    };
  });

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        plugins: {
          legend: { position: 'bottom', labels: { color: VoltaChartTheme.colors.textSecondary } },
          tooltip: VoltaChartTheme.tooltip()
        },
        scales: {
          x: { ticks: VoltaChartTheme.ticks(9, 12), grid: VoltaChartTheme.gridLines() },
          y: { beginAtZero: true, ticks: VoltaChartTheme.ticks(9, 12), grid: VoltaChartTheme.gridLines() }
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
