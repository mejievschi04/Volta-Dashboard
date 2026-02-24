@extends('layouts.app')

@section('title', 'Raport operator – ' . ($date['nume'] ?? $operatorNume) . ' – VOLTA')

@push('styles')
<style>
  @media (max-width: 900px) {
    .operatori-detail-grid { grid-template-columns: 1fr !important; margin-top: 80px !important; }
    .operatori-cover { height: 220px !important; }
    .operatori-cover-inner { bottom: -50px !important; left: 20px !important; }
    .operatori-avatar { width: 100px !important; height: 100px !important; font-size: 40px !important; }
    .operatori-detail-title { font-size: 24px !important; }
  }
</style>
@endpush

@section('content')
<div class="operatori-detail-page">
  <a href="{{ route('operatori') }}" class="operatori-back">
    <i class="fas fa-arrow-left"></i> Înapoi la Operatori
  </a>

@if($date)
  @php
    $lunaCurenta = now()->format('Y-m');
    $lunaCurentaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
  @endphp

  <div class="operatori-cover" @if(isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_coperta_url) style="background-image: url('{{ $operatorRecord->photo_coperta_url }}'); background-size: cover; background-position: center;" @endif>
    <div class="operatori-cover-inner">
      @if(isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_profil_url)
      <div class="operatori-avatar operatori-avatar--img" style="background-image: url('{{ $operatorRecord->photo_profil_url }}');"></div>
      @else
      <div class="operatori-avatar">{{ strtoupper(mb_substr($date['nume'], 0, 1)) }}</div>
      @endif
      <div class="operatori-detail-name-wrap">
        <h1 class="operatori-detail-title">{{ $date['nume'] }}</h1>
        <span class="operatori-badge">
          <i class="fas fa-database"></i> Raport 1C (ian. 2023 – prezent)
        </span>
      </div>
    </div>
  </div>

  <div class="operatori-detail-grid">
    <div>
      <div class="operatori-sidebar-card">
        <h3 class="operatori-sidebar-title"><i class="fas fa-calendar-check"></i> Luna curentă</h3>
        <div class="operatori-stat-list">
          <div class="operatori-stat-row">
            <div style="flex: 1;">
              <div class="operatori-stat-label">Vânzări (fără TVA)</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? number_format($lunaCurentaData->vanzari_luna, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--profit">
            <div style="flex: 1;">
              <div class="operatori-stat-label">Profit</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? number_format($lunaCurentaData->profit, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--neutral">
            <div style="flex: 1;">
              <div class="operatori-stat-label">Comenzi</div>
              <div class="operatori-stat-value">{{ $lunaCurentaData ? (int) $lunaCurentaData->comenzi : 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="operatori-main-feed">
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Statistici totale (1C)</h2>
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
          <div class="operatori-stat-box operatori-stat-box--yellow">
            <div class="operatori-stat-label">Comenzi</div>
            <div class="operatori-stat-value">{{ number_format($date['nr_comenzi'], 0, ',', '.') }}</div>
          </div>
        </div>
      </div>
      @if($vanzariLunare1c->count() > 0)
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Vânzări pe luni (fără TVA)</h2>
        <div class="operatori-chart-wrap"><canvas id="vanzariChartRaport"></canvas></div>
      </div>
      <div class="operatori-content-card">
        <h2 class="operatori-section-title">Tabel pe luni</h2>
        <div class="operatori-detail-table-wrap">
          <table class="operatori-detail-table">
            <thead>
              <tr>
                <th>Lună</th>
                <th class="tc">Comenzi</th>
                <th class="tc">Vânzări (fără TVA)</th>
                <th class="tc">Profit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare1c as $luna)
              <tr>
                <td><strong>{{ $luna->luna_label }}</strong></td>
                <td class="tc">{{ $luna->comenzi }}</td>
                <td class="tc">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} MDL</td>
                <td class="tc td-profit">{{ number_format($luna->profit, 2, ',', '.') }} MDL</td>
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
    const canvas = document.getElementById('vanzariChartRaport');
    if (!canvas) return;
    const vanzariLunare = @json($vanzariLunare1c->sortBy('luna')->values());
    const labels = vanzariLunare.map(function(v) { return v.luna_label || v.luna; });
    const data = vanzariLunare.map(function(v) { return parseFloat(v.vanzari_luna) || 0; });
    new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: { labels: labels, datasets: [{ label: 'Vânzări (fără TVA) MDL', data: data, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.1)', borderWidth: 3, tension: 0.4, fill: true, pointRadius: 4 }] },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { labels: { color: '#fff' } }, tooltip: { backgroundColor: 'rgba(0,0,0,0.9)', bodyColor: '#fff' } },
        scales: { x: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.05)' } }, y: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.05)' }, beginAtZero: true } }
      }
    });
  });
  </script>
  @endpush
  @endif
@else
  <div class="operatori-detail-empty">
    <i class="fas fa-database"></i>
    <p>Nu există date din 1C pentru <strong>{{ $operatorNume ?: '—' }}</strong>.</p>
  </div>
@endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush
