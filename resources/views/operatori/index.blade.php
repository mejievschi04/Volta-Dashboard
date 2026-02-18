@extends('layouts.app')

@section('title', 'Operatori – VOLTA')

@section('content')
<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
  <div style="margin-bottom: 30px;">
    <h1 style="color: #FFEE00; margin: 0; font-size: 32px; font-weight: 800; text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);">Operatori</h1>
    <p style="color: #9CA3AF; margin: 5px 0 0 0; font-size: 14px;">Date din 1C (ianuarie 2023 – prezent)</p>
  </div>
  
  @if(session('success'))
  <div style="background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #fff; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div style="background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); color: #fff; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>{{ session('error') }}
  </div>
  @endif
  
  @if(isset($operatori1c) && count($operatori1c) > 0)
  <div class="operator-card" style="margin-bottom: 30px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 1px solid rgba(255, 238, 0, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
    @if(isset($chartData1c) && count($chartData1c) > 0)
    <div class="pie-chart-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center; margin-bottom: 24px;">
      <div style="position: relative; height: 320px;">
        <canvas id="vanzariPieChart1c"></canvas>
      </div>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach($chartData1c as $d)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid rgba(255, 238, 0, 0.5);">
          <div style="flex: 1;">
            <div style="color: #fff; font-weight: 600; font-size: 14px;">{{ $d['nume'] }}</div>
            <div style="color: #9CA3AF; font-size: 12px;">{{ number_format($d['vanzari_fara_tva'], 2, ',', '.') }} MDL</div>
          </div>
          <div style="color: #FFEE00; font-weight: 700; font-size: 18px;">{{ $d['procent'] }}%</div>
        </div>
        @endforeach
      </div>
    </div>
    @endif
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background: rgba(255, 238, 0, 0.15); color: #FFEE00;">
            <th style="padding: 14px; text-align: left; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700;">Operator</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700;">Vânzări fără TVA</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700;">Profit</th>
            <th style="padding: 14px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700;">Comenzi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($operatori1c as $op)
          <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
            <td style="padding: 14px; color: #fff; font-weight: 600;">{{ $op['nume'] }}</td>
            <td style="padding: 14px; text-align: center; color: #fff;">{{ number_format($op['vanzari_fara_tva'], 2, ',', '.') }} MDL</td>
            <td style="padding: 14px; text-align: center; color: #10B981; font-weight: 600;">{{ number_format($op['profit'], 2, ',', '.') }} MDL</td>
            <td style="padding: 14px; text-align: center; color: #fff;">{{ number_format($op['nr_comenzi'], 0, ',', '.') }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="operator-card" style="margin-bottom: 30px; padding: 60px 20px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 2px dashed rgba(255, 255, 255, 0.1);">
    <div style="text-align: center;">
      <i class="fas fa-database" style="font-size: 48px; color: #9CA3AF; margin-bottom: 20px; display: block;"></i>
      <p style="color: #9CA3AF; font-size: 18px; margin: 0;">Nu există date din 1C pentru operatori.</p>
      <p style="color: #6B7280; font-size: 14px; margin: 12px 0 0 0;">Sincronizează din Setări → 1C.</p>
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

