@extends('layouts.app')

@section('title', 'Profil Operator – VOLTA')

@section('header-title', $operator->nume ?? 'Profil operator')

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@section('content')
<div class="operatori-detail-page operatori-show-page">
@if(session('success'))
<div class="operatori-alert operatori-alert-success">
  <i class="fas fa-check-circle" aria-hidden="true"></i><span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="operatori-alert operatori-alert-error">
  <i class="fas fa-exclamation-circle" aria-hidden="true"></i><span>{{ session('error') }}</span>
</div>
@endif

<a href="{{ route('operatori') }}" class="operatori-back">
  <i class="fas fa-arrow-left" aria-hidden="true"></i> Înapoi la Operatori
</a>

@if(isset($date) && $date)
  @php
    $lunaCurenta = now()->format('Y-m');
    $lunaCurentaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
  @endphp

  <div class="operatori-cover operatori-cover--show" @if($operator->photo_coperta_url) style="background-image: url('{{ $operator->photo_coperta_url }}'); background-size: cover; background-position: center;" @endif>
    <div class="operatori-cover-inner">
      @if($operator->photo_profil_url)
      <div class="operatori-avatar operatori-avatar--img" style="background-image: url('{{ $operator->photo_profil_url }}');"></div>
      @else
      <div class="operatori-avatar" aria-hidden="true">{{ strtoupper(mb_substr($date['nume'], 0, 1)) }}</div>
      @endif
      <div class="operatori-detail-name-wrap">
        <h1 class="operatori-detail-title">{{ $date['nume'] }}</h1>
        <span class="kpi-source-badge">
          <i class="fas fa-database" aria-hidden="true"></i> Rezultate pentru perioada selectată
        </span>
      </div>
    </div>
  </div>

  <div class="operatori-detail-grid">
    <div>
      <div class="operatori-sidebar-card">
        <h3 class="operatori-sidebar-title"><i class="fas fa-calendar-check" aria-hidden="true"></i> Luna curentă</h3>
        <div class="operatori-stat-list">
          <div class="operatori-stat-row">
            <div class="operatori-stat-icon"><i class="fas fa-shopping-cart" aria-hidden="true"></i></div>
            <div class="operatori-stat-row-body">
              <div class="operatori-stat-label">Vânzări (fără TVA)</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? number_format($lunaCurentaData->vanzari_luna, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--profit">
            <div class="operatori-stat-icon"><i class="fas fa-trophy" aria-hidden="true"></i></div>
            <div class="operatori-stat-row-body">
              <div class="operatori-stat-label">Profit</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? number_format($lunaCurentaData->profit, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--neutral">
            <div class="operatori-stat-icon"><i class="fas fa-list" aria-hidden="true"></i></div>
            <div class="operatori-stat-row-body">
              <div class="operatori-stat-label">Comenzi</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? (int) $lunaCurentaData->comenzi : 0 }}</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--delivery">
            <div class="operatori-stat-icon"><i class="fas fa-truck" aria-hidden="true"></i></div>
            <div class="operatori-stat-row-body">
              <div class="operatori-stat-label">Livrări (luna curentă)</div>
              <div class="operatori-stat-value operatori-stat-value--delivery">{{ $nrLivrariLunaCurenta ?? 0 }}</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--pickup">
            <div class="operatori-stat-icon"><i class="fas fa-store" aria-hidden="true"></i></div>
            <div class="operatori-stat-row-body">
              <div class="operatori-stat-label">Pick-up (luna curentă)</div>
              <div class="operatori-stat-value operatori-stat-value--pickup">{{ $pickupLunaCurenta ?? 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="operatori-main-feed">
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Statistici totale</h2>
        <div class="operatori-stats-grid">
          <div class="operatori-stat-box">
            <div class="operatori-stat-label">Vânzări fără TVA</div>
            <div class="operatori-stat-value">{{ number_format($date['vanzari_fara_tva'], 2, ',', '.') }} MDL</div>
          </div>
          <div class="operatori-stat-box operatori-stat-box--neutral">
            <div class="operatori-stat-label">Vânzări cu TVA</div>
            <div class="operatori-stat-value">{{ number_format($date['vanzari_cu_tva'], 2, ',', '.') }} MDL</div>
          </div>
          <div class="operatori-stat-box operatori-stat-box--profit">
            <div class="operatori-stat-label">Profit</div>
            <div class="operatori-stat-value">{{ number_format($date['profit'], 2, ',', '.') }} MDL</div>
          </div>
          <div class="operatori-stat-box operatori-stat-box--neutral">
            <div class="operatori-stat-label">Comenzi</div>
            <div class="operatori-stat-value operatori-stat-value--primary">{{ number_format($date['nr_comenzi'], 0, ',', '.') }}</div>
          </div>
          <div class="operatori-stat-box operatori-stat-box--amber">
            <div class="operatori-stat-label">Livrări (total)</div>
            <div class="operatori-stat-value operatori-stat-value--delivery">{{ number_format($nrLivrariTotal ?? 0, 0, ',', '.') }}</div>
          </div>
          <div class="operatori-stat-box operatori-stat-box--violet">
            <div class="operatori-stat-label">Pick-up (total)</div>
            <div class="operatori-stat-value operatori-stat-value--pickup">{{ number_format($pickupTotal ?? 0, 0, ',', '.') }}</div>
          </div>
        </div>
      </div>

      @if($vanzariLunare1c->count() > 0)
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Vânzări pe luni (fără TVA)</h2>
        <div class="operatori-chart-wrap"><canvas id="vanzariChartMe"></canvas></div>
      </div>
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Vânzări pe luni</h2>
        <div class="operatori-table-wrap">
          <table class="operatori-table">
            <thead>
              <tr>
                <th>Lună</th>
                <th class="tc">Comenzi</th>
                <th class="tc">Vânzări (fără TVA)</th>
                <th class="tc">Vânzări (cu TVA)</th>
                <th class="tc">Profit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare1c as $luna)
              <tr>
                <td class="operatori-table-strong">{{ $luna->luna_label }}</td>
                <td class="tc">{{ $luna->comenzi }}</td>
                <td class="tc operatori-table-strong">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} MDL</td>
                <td class="tc operatori-table-strong">{{ number_format($luna->vanzari_cu_tva, 2, ',', '.') }} MDL</td>
                <td class="tc operatori-profit">{{ number_format($luna->profit, 2, ',', '.') }} MDL</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif
    </div>
  </div>

  @if($vanzariLunare1c->count() > 0)
  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('vanzariChartMe');
    if (!canvas) return;
    const vanzariLunare = @json($vanzariLunare1c->sortBy('luna')->values());
    const labels = vanzariLunare.map(function(v) { return v.luna_label || v.luna; });
    const data = vanzariLunare.map(function(v) { return parseFloat(v.vanzari_luna) || 0; });
    var chartOptions = (function() {
      if (typeof VoltaChartTheme !== 'undefined') {
        return VoltaChartTheme.cartesianDefaults({
          plugins: {
            tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
              titleColor: VoltaChartTheme.colors.brand,
              bodyColor: VoltaChartTheme.colors.textPrimary,
              callbacks: {
                label: function(ctx) {
                  return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2 }).format(ctx.parsed.y) + ' MDL';
                }
              }
            }),
          },
          scales: {
            x: { ticks: { maxRotation: 45, minRotation: 0 } },
            y: {
              ticks: {
                callback: function(v) {
                  return new Intl.NumberFormat('ro-RO', { maximumFractionDigits: 0 }).format(v) + ' MDL';
                }
              }
            }
          }
        });
      }
      return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { labels: { color: '#e2e8f0', font: { size: 12 } } },
          tooltip: {
            backgroundColor: 'rgba(30,41,59,0.96)',
            titleColor: '#FFEE00',
            bodyColor: '#f8fafc',
            borderColor: '#334155',
            borderWidth: 1,
            padding: 12,
            cornerRadius: 10,
            callbacks: {
              label: function(ctx) {
                return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2 }).format(ctx.parsed.y) + ' MDL';
              }
            }
          }
        },
        scales: {
          x: { ticks: { color: '#cbd5e1', maxRotation: 45 }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false } },
          y: {
            ticks: {
              color: '#cbd5e1',
              callback: function(v) { return new Intl.NumberFormat('ro-RO', { maximumFractionDigits: 0 }).format(v) + ' MDL'; }
            },
            grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false },
            beginAtZero: true
          }
        }
      };
    })();
    new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vânzări (fără TVA) MDL',
          data: data,
          borderColor: '#FFEE00',
          backgroundColor: 'rgba(255, 238, 0, 0.12)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 4,
          pointBackgroundColor: '#FFEE00',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: chartOptions
    });
  });
  </script>
  @endpush
  @endif
@else
  <div class="operatori-card operatori-card-empty" style="margin-top: 20px;">
    <i class="fas fa-database" aria-hidden="true"></i>
    <p>Nu există date pentru acest operator.</p>
    <p class="operatori-empty-hint">Nume: <strong>{{ $operator->nume ?? '—' }}</strong></p>
  </div>
@endif
</div>
@endsection
