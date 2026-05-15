@extends('layouts.app')

@section('title', 'Mobile Analytics - VOLTA')
@section('header-title', 'Mobile Analytics')

@push('styles')
<style>
.mobile-page {
  display: flex;
  flex-direction: column;
  gap: 18px;
  padding-bottom: 32px;
}

.mobile-head {
  display: flex;
  justify-content: space-between;
  gap: 16px;
  align-items: flex-end;
  flex-wrap: wrap;
}

.mobile-title h1 {
  margin: 0 0 6px;
  color: var(--brand);
  font-size: clamp(1.5rem, 3vw, 2rem);
  letter-spacing: -0.04em;
}

.mobile-title p {
  margin: 0;
  color: var(--text-secondary);
  font-size: 14px;
}

.mobile-filters {
  display: flex;
  align-items: flex-end;
  gap: 10px;
  flex-wrap: wrap;
}

.mobile-field {
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.mobile-field label {
  color: var(--text-tertiary);
  font-size: 11px;
  font-weight: 800;
  text-transform: uppercase;
  letter-spacing: 0.08em;
}

.mobile-field input {
  min-height: 42px;
  border-radius: 10px;
  border: 1px solid var(--border-primary);
  background: var(--bg-secondary);
  color: var(--text-primary);
  padding: 10px 12px;
  font: inherit;
}

.mobile-apply {
  min-height: 42px;
  border: 0;
  border-radius: 10px;
  padding: 0 16px;
  background: var(--brand);
  color: var(--text-inverse);
  font-weight: 800;
  cursor: pointer;
}

.mobile-kpis {
  display: grid;
  grid-template-columns: repeat(4, minmax(0, 1fr));
  gap: 14px;
}

.mobile-kpi {
  min-height: 0;
}

.mobile-kpi h4 {
  margin-bottom: 8px;
}

.mobile-kpi .value {
  color: var(--text-primary);
  font-size: clamp(1.2rem, 2.4vw, 1.65rem);
}

.mobile-kpi small {
  color: var(--text-tertiary);
  font-size: 12px;
}

.mobile-grid {
  display: grid;
  grid-template-columns: minmax(0, 1.45fr) minmax(300px, 0.85fr);
  gap: 18px;
}

.mobile-panel {
  background: var(--bg-elevated);
  border: 1px solid var(--border-primary);
  border-radius: var(--card-radius);
  box-shadow: var(--shadow-md);
  overflow: hidden;
}

.mobile-panel__head {
  padding: 14px 16px;
  border-bottom: 1px solid var(--border-primary);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}

.mobile-panel__head h2 {
  margin: 0;
  font-size: 16px;
  color: var(--text-primary);
  display: flex;
  align-items: center;
  gap: 8px;
}

.mobile-panel__head i {
  color: var(--brand);
}

.mobile-panel__body {
  padding: 16px;
}

.mobile-chart-wrap {
  position: relative;
  min-height: 330px;
}

.mobile-table-wrap {
  overflow-x: auto;
}

.mobile-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13px;
}

.mobile-table th {
  text-align: left;
  color: var(--text-tertiary);
  background: var(--bg-secondary);
  font-size: 10px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  padding: 10px 12px;
  white-space: nowrap;
}

.mobile-table td {
  color: var(--text-primary);
  padding: 11px 12px;
  border-top: 1px solid rgba(148, 163, 184, 0.12);
  vertical-align: top;
}

.mobile-table tbody tr:hover td {
  background: rgba(51, 65, 85, 0.32);
}

.mobile-muted {
  color: var(--text-tertiary);
}

.mobile-badge {
  display: inline-flex;
  align-items: center;
  border-radius: 999px;
  border: 1px solid rgba(255, 238, 0, 0.24);
  background: rgba(255, 238, 0, 0.08);
  color: var(--brand);
  padding: 4px 8px;
  font-size: 11px;
  font-weight: 800;
  white-space: nowrap;
}

.mobile-empty {
  color: var(--text-secondary);
  padding: 16px;
  border: 1px dashed var(--border-primary);
  border-radius: var(--card-radius);
  background: rgba(15, 23, 42, 0.25);
  font-size: 14px;
}

@media (max-width: 1100px) {
  .mobile-kpis {
    grid-template-columns: repeat(2, minmax(0, 1fr));
  }

  .mobile-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .mobile-head,
  .mobile-filters {
    align-items: stretch;
    flex-direction: column;
  }

  .mobile-kpis {
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 10px;
  }

  .mobile-field input,
  .mobile-apply {
    width: 100%;
  }
}
</style>
@endpush

@section('content')
<div class="mobile-page">
  <div class="mobile-head">
    <div class="mobile-title">
      <h1>Mobile Analytics</h1>
      <p>Evenimente din aplicatia mobila: timp pe pagina, click-uri pe bannere si abandon cos.</p>
    </div>
    <form method="get" action="{{ route('mobile.analytics') }}" class="mobile-filters">
      <div class="mobile-field">
        <label for="mobileStart">Start</label>
        <input id="mobileStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
      </div>
      <div class="mobile-field">
        <label for="mobileEnd">End</label>
        <input id="mobileEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
      </div>
      <button class="mobile-apply" type="submit">Aplica</button>
    </form>
  </div>

  <div class="mobile-kpis">
    <div class="card mobile-kpi">
      <h4><i class="fas fa-bolt"></i> Evenimente</h4>
      <div class="value">{{ number_format($summary['events'], 0, ',', '.') }}</div>
      <small>Total primit in perioada</small>
    </div>
    <div class="card mobile-kpi">
      <h4><i class="fas fa-users"></i> Sesiuni</h4>
      <div class="value">{{ number_format($summary['sessions'], 0, ',', '.') }}</div>
      <small>{{ number_format($summary['users'], 0, ',', '.') }} utilizatori identificati</small>
    </div>
    <div class="card mobile-kpi">
      <h4><i class="fas fa-clock"></i> Timp mediu</h4>
      <div class="value">{{ number_format($summary['avg_page_seconds'], 0, ',', '.') }}s</div>
      <small>Din evenimente page_leave</small>
    </div>
    <div class="card mobile-kpi">
      <h4><i class="fas fa-cart-shopping"></i> Abandon cos</h4>
      <div class="value">{{ number_format($summary['cart_abandons'], 0, ',', '.') }}</div>
      <small>{{ number_format($summary['orders'], 0, ',', '.') }} comenzi finalizate</small>
    </div>
  </div>

  <div class="mobile-grid">
    <section class="mobile-panel">
      <div class="mobile-panel__head">
        <h2><i class="fas fa-chart-line"></i> Evolutie evenimente</h2>
      </div>
      <div class="mobile-panel__body">
        <div class="mobile-chart-wrap">
          <canvas id="mobileEventsChart"></canvas>
        </div>
      </div>
    </section>

    <section class="mobile-panel">
      <div class="mobile-panel__head">
        <h2><i class="fas fa-list-check"></i> Tipuri evenimente</h2>
      </div>
      <div class="mobile-panel__body">
        @if($eventBreakdown->isEmpty())
          <div class="mobile-empty">Nu exista inca evenimente mobile pentru perioada aleasa.</div>
        @else
          <div class="mobile-table-wrap">
            <table class="mobile-table">
              <thead><tr><th>Eveniment</th><th>Total</th></tr></thead>
              <tbody>
              @foreach($eventBreakdown as $row)
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

  <div class="mobile-grid">
    <section class="mobile-panel">
      <div class="mobile-panel__head">
        <h2><i class="fas fa-file-lines"></i> Pagini si timp petrecut</h2>
      </div>
      <div class="mobile-panel__body mobile-table-wrap">
        <table class="mobile-table">
          <thead>
            <tr><th>Pagina</th><th>View-uri</th><th>Timp mediu</th><th>Evenimente</th></tr>
          </thead>
          <tbody>
          @forelse($topPages as $page)
            <tr>
              <td>{{ $page->page }}</td>
              <td>{{ number_format((int) $page->views, 0, ',', '.') }}</td>
              <td>{{ $page->avg_duration_ms ? number_format(round($page->avg_duration_ms / 1000), 0, ',', '.') . 's' : '-' }}</td>
              <td>{{ number_format((int) $page->events_count, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="mobile-muted">Nu exista pagini inregistrate.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="mobile-panel">
      <div class="mobile-panel__head">
        <h2><i class="fas fa-rectangle-ad"></i> Click-uri bannere</h2>
      </div>
      <div class="mobile-panel__body mobile-table-wrap">
        <table class="mobile-table">
          <thead>
            <tr><th>Banner</th><th>Click-uri</th><th>Ultimul click</th></tr>
          </thead>
          <tbody>
          @forelse($bannerClicks as $banner)
            <tr>
              <td>{{ $banner->banner_title ?: ($banner->banner_id ?: '-') }}</td>
              <td>{{ number_format((int) $banner->clicks, 0, ',', '.') }}</td>
              <td class="mobile-muted">{{ $banner->last_click_at ? \Carbon\Carbon::parse($banner->last_click_at)->format('d.m.Y H:i') : '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="mobile-muted">Nu exista click-uri pe bannere.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="mobile-panel">
    <div class="mobile-panel__head">
      <h2><i class="fas fa-cart-arrow-down"></i> Unde se paraseste cosul</h2>
    </div>
    <div class="mobile-panel__body mobile-table-wrap">
      <table class="mobile-table">
        <thead>
          <tr><th>Pas checkout</th><th>Abandonuri</th><th>Total mediu cos</th><th>Produse medii</th></tr>
        </thead>
        <tbody>
        @forelse($cartAbandons as $row)
          <tr>
            <td><span class="mobile-badge">Pas {{ $row->checkout_step ?: '?' }}</span></td>
            <td>{{ number_format((int) $row->abandons, 0, ',', '.') }}</td>
            <td>{{ $row->avg_cart_total !== null ? number_format((float) $row->avg_cart_total, 2, ',', '.') . ' MDL' : '-' }}</td>
            <td>{{ $row->avg_items_count !== null ? number_format((float) $row->avg_items_count, 1, ',', '.') : '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="4" class="mobile-muted">Nu exista abandonuri de cos in perioada aleasa.</td></tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </section>

  <section class="mobile-panel">
    <div class="mobile-panel__head">
      <h2><i class="fas fa-clock-rotate-left"></i> Evenimente recente</h2>
    </div>
    <div class="mobile-panel__body mobile-table-wrap">
      <table class="mobile-table">
        <thead>
          <tr><th>Ora</th><th>Eveniment</th><th>Pagina</th><th>User</th><th>Sesiune</th><th>Detalii</th></tr>
        </thead>
        <tbody>
        @forelse($recentEvents as $event)
          <tr>
            <td class="mobile-muted">{{ optional($event->occurred_at)->format('d.m H:i') }}</td>
            <td><span class="mobile-badge">{{ $event->event_name }}</span></td>
            <td>{{ $event->page ?: '-' }}</td>
            <td>{{ $event->mobile_user_id ?: '-' }}</td>
            <td class="mobile-muted">{{ $event->session_id ? \Illuminate\Support\Str::limit($event->session_id, 12) : '-' }}</td>
            <td class="mobile-muted">
              @if($event->duration_ms)
                {{ round($event->duration_ms / 1000) }}s
              @elseif($event->cart_total)
                {{ number_format((float) $event->cart_total, 2, ',', '.') }} MDL
              @elseif($event->banner_title)
                {{ $event->banner_title }}
              @else
                -
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="mobile-muted">Nu exista evenimente recente.</td></tr>
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
  var chartEl = document.getElementById('mobileEventsChart');
  if (!chartEl || typeof Chart === 'undefined') return;

  var chartData = @json($dailyChart);
  var palette = (typeof VoltaChartTheme !== 'undefined' && VoltaChartTheme.getSeriesPalette)
    ? VoltaChartTheme.getSeriesPalette()
    : null;
  var colors = {
    page_view: palette ? palette.amber : { line: 'rgb(250, 204, 21)', area: 'rgba(250, 204, 21, 0.14)' },
    banner_click: palette ? palette.cyan : { line: 'rgb(34, 211, 238)', area: 'rgba(34, 211, 238, 0.12)' },
    cart_abandoned: palette ? palette.rose : { line: 'rgb(244, 63, 94)', area: 'rgba(244, 63, 94, 0.12)' },
    order_completed: palette ? palette.emerald : { line: 'rgb(16, 185, 129)', area: 'rgba(16, 185, 129, 0.12)' }
  };
  var names = {
    page_view: 'Pagini',
    banner_click: 'Click bannere',
    cart_abandoned: 'Abandon cos',
    order_completed: 'Comenzi'
  };

  var datasets = Object.keys(chartData.datasets || {}).map(function (key) {
    return {
      label: names[key] || key,
      data: chartData.datasets[key] || [],
      borderColor: colors[key].line,
      backgroundColor: colors[key].area,
      borderWidth: 2.6,
      tension: 0.35,
      fill: key === 'page_view',
      pointRadius: 2,
      pointHoverRadius: 5
    };
  });

  var options = (typeof VoltaChartTheme !== 'undefined')
    ? VoltaChartTheme.cartesianDefaults({
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color: VoltaChartTheme.colors.textSecondary, font: { family: VoltaChartTheme.font, size: 12 } }
          },
          tooltip: VoltaChartTheme.tooltip()
        },
        scales: {
          x: { ticks: VoltaChartTheme.ticks(9, 12), grid: VoltaChartTheme.gridLines() },
          y: { beginAtZero: true, ticks: VoltaChartTheme.ticks(9, 12), grid: VoltaChartTheme.gridLines() }
        }
      })
    : {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom' } },
        scales: { y: { beginAtZero: true } }
      };

  new Chart(chartEl.getContext('2d'), {
    type: 'line',
    data: { labels: chartData.labels || [], datasets: datasets },
    options: options
  });
});
</script>
@endpush
