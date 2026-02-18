@extends('layouts.app')

@section('title', 'Operatori – VOLTA')

@section('content')
<div class="operatori-page">
  <header class="operatori-page-header">
    <div class="operatori-page-header-inner">
      <div class="operatori-page-icon"><i class="fas fa-users"></i></div>
      <div>
        <h1 class="operatori-page-title">Operatori</h1>
        <p class="operatori-page-subtitle">Date din 1C (ianuarie 2023 – prezent)</p>
      </div>
    </div>
  </header>

  @if(session('success'))
  <div class="operatori-alert operatori-alert-success">
    <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
  </div>
  @endif
  @if(session('error'))
  <div class="operatori-alert operatori-alert-error">
    <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
  </div>
  @endif

  @if(isset($operatori1c) && count($operatori1c) > 0)
  <div class="operatori-card operatori-card-main">
    @if(isset($chartData1c) && count($chartData1c) > 0)
    <section class="operatori-chart-section">
      <div class="operatori-pie-wrap">
        <div class="operatori-pie-canvas"><canvas id="vanzariPieChart1c"></canvas></div>
      </div>
      <div class="operatori-legend">
        @foreach($chartData1c as $d)
        <div class="operatori-legend-item">
          <span class="operatori-legend-name">{{ $d['nume'] }}</span>
          <span class="operatori-legend-value">{{ number_format($d['vanzari_fara_tva'], 2, ',', '.') }} MDL</span>
          <span class="operatori-legend-pct">{{ $d['procent'] }}%</span>
        </div>
        @endforeach
      </div>
    </section>
    @endif

    <section class="operatori-table-section">
      <h2 class="operatori-table-title"><i class="fas fa-list"></i> Lista operatori</h2>
      <div class="operatori-table-wrap">
        <table class="operatori-table">
          <thead>
            <tr>
              <th>Operator</th>
              <th class="tc">Vânzări fără TVA</th>
              <th class="tc">Profit</th>
              <th class="tc">Comenzi</th>
              <th class="tc">Acțiuni</th>
            </tr>
          </thead>
          <tbody>
            @foreach($operatori1c as $op)
            <tr>
              <td><strong>{{ $op['nume'] }}</strong></td>
              <td class="tc">{{ number_format($op['vanzari_fara_tva'], 2, ',', '.') }} MDL</td>
              <td class="tc operatori-profit">{{ number_format($op['profit'], 2, ',', '.') }} MDL</td>
              <td class="tc">{{ number_format($op['nr_comenzi'], 0, ',', '.') }}</td>
              <td class="tc operatori-actions">
                @if(!empty($op['operator_id']))
                <a href="{{ route('operatori.show', $op['operator_id']) }}" class="operatori-btn operatori-btn-report"><i class="fas fa-chart-line"></i> Raport detaliat</a>
                @else
                <a href="{{ route('operatori.raport', ['nume' => $op['nume']]) }}" class="operatori-btn operatori-btn-report"><i class="fas fa-chart-line"></i> Raport detaliat</a>
                @endif
                @if(auth()->check() && in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']))
                <form action="{{ route('operatori.toggle-activ') }}" method="post" class="operatori-form-inline" onsubmit="return confirm('Dezactivezi acest operator? Nu va mai apărea în listă.');">
                  @csrf
                  <input type="hidden" name="nume" value="{{ $op['nume'] }}">
                  <button type="submit" class="operatori-btn operatori-btn-deactivate"><i class="fas fa-user-slash"></i> Dezactivează</button>
                </form>
                @endif
              </td>
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  </div>
  @else
  <div class="operatori-card operatori-card-empty">
    <i class="fas fa-database"></i>
    <p>Nu există date din 1C pentru operatori.</p>
    <p class="operatori-empty-hint">Sincronizează din Setări → 1C.</p>
  </div>
  @endif

  @if(auth()->check() && in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']) && isset($operatoriDezactivati) && $operatoriDezactivati->count() > 0)
  <div class="operatori-card operatori-card-dezactivati">
    <h3><i class="fas fa-user-slash"></i> Operatori dezactivați</h3>
    <p class="operatori-dezactivati-desc">Nu apar în listă. Poți reactiva pentru a-i afișa din nou.</p>
    <div class="operatori-dezactivati-btns">
      @foreach($operatoriDezactivati as $od)
      <form action="{{ route('operatori.toggle-activ') }}" method="post">
        @csrf
        <input type="hidden" name="nume" value="{{ $od->nume }}">
        <button type="submit" class="operatori-btn operatori-btn-reactivate">{{ $od->nume }} <i class="fas fa-user-check"></i> Reactivează</button>
      </form>
      @endforeach
    </div>
  </div>
  @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@push('scripts')
@if(isset($chartData1c) && count($chartData1c) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const canvas1c = document.getElementById('vanzariPieChart1c');
  if (canvas1c) {
    const chartData1c = @json($chartData1c);
    const colors = ['rgba(255, 238, 0, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(74, 222, 128, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(239, 68, 68, 0.8)', 'rgba(251, 146, 60, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(99, 102, 241, 0.8)', 'rgba(236, 72, 153, 0.8)', 'rgba(20, 184, 166, 0.8)'];
    new Chart(canvas1c.getContext('2d'), {
      type: 'pie',
      data: {
        labels: chartData1c.map(i => i.nume),
        datasets: [{
          data: chartData1c.map(i => i.vanzari_fara_tva),
          backgroundColor: colors.slice(0, chartData1c.length),
          borderColor: colors.slice(0, chartData1c.length).map(c => c.replace('0.8', '1')),
          borderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            callbacks: {
              label: function(ctx) {
                const v = ctx.parsed || 0;
                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                const pct = total > 0 ? ((v / total) * 100).toFixed(2) : 0;
                return ctx.label + ': ' + new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2 }).format(v) + ' MDL (' + pct + '%)';
              }
            }
          }
        }
      }
    });
  }
});
</script>
@endif
@endpush

