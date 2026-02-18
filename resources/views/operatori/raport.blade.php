@extends('layouts.app')

@section('title', 'Raport operator – ' . ($date['nume'] ?? $operatorNume) . ' – VOLTA')

@push('styles')
<style>
  @media (max-width: 900px) {
    .operator-me-grid { grid-template-columns: 1fr !important; margin-top: 80px !important; }
    .operator-me-cover { height: 220px !important; }
    .operator-me-avatar { width: 100px !important; height: 100px !important; font-size: 40px !important; bottom: -50px !important; left: 20px !important; }
    .operator-me-title { font-size: 24px !important; }
  }
</style>
@endpush

@section('content')
<div style="padding: 20px; max-width: 1200px; margin: 0 auto;">
  <div style="margin-bottom: 20px;">
    <a href="{{ route('operatori') }}" style="color: #fff; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(107, 114, 128, 0.15); border-radius: 8px; border: 1px solid rgba(107, 114, 128, 0.3); transition: all 0.2s;" onmouseover="this.style.background='rgba(107, 114, 128, 0.25)'" onmouseout="this.style.background='rgba(107, 114, 128, 0.15)'">
      <i class="fas fa-arrow-left"></i> Înapoi la Operatori
    </a>
  </div>

@if($date)
  @php
    $lunaCurenta = now()->format('Y-m');
    $lunaCurentaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
  @endphp

  <div class="operator-me-cover" style="background: linear-gradient(135deg, rgba(255, 238, 0, 0.3) 0%, rgba(255, 238, 0, 0.2) 50%, rgba(255, 238, 0, 0.1) 100%); background-color: #1F2937; border-radius: 16px 16px 0 0; height: 280px; position: relative; margin-bottom: 0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
    <div style="position: absolute; bottom: -70px; left: 40px; display: flex; align-items: flex-end; gap: 20px;">
      <div class="operator-me-avatar" style="width: 140px; height: 140px; border-radius: 50%; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center; font-size: 56px; font-weight: 700; color: #000; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border: 5px solid #111827;">
        {{ strtoupper(mb_substr($date['nume'], 0, 1)) }}
      </div>
      <div style="padding-bottom: 16px;">
        <h1 class="operator-me-title" style="color: #fff; margin: 0 0 8px 0; font-size: 32px; font-weight: 800;">{{ $date['nume'] }}</h1>
        <span style="background: rgba(17, 24, 39, 0.9); color: #FFEE00; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #FFEE00;">
          <i class="fas fa-database"></i> Raport 1C (ian. 2023 – prezent)
        </span>
      </div>
    </div>
  </div>

  <div class="operator-me-grid" style="display: grid; grid-template-columns: 300px 1fr; gap: 20px; margin-top: 90px;">
    <div>
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <h3 style="color: #fff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700;"><i class="fas fa-calendar-check" style="color: #3b82f6; margin-right: 10px;"></i> Luna curentă</h3>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 10px;">
            <div style="flex: 1;"><div style="color: #9CA3AF; font-size: 12px;">Vânzări (fără TVA)</div><div style="color: #fff; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? number_format($lunaCurentaData->vanzari_luna, 2, ',', '.') : '0,00' }} MDL</div></div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(74, 222, 128, 0.1); border-radius: 10px;">
            <div style="flex: 1;"><div style="color: #9CA3AF; font-size: 12px;">Profit</div><div style="color: #10B981; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? number_format($lunaCurentaData->profit, 2, ',', '.') : '0,00' }} MDL</div></div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.03); border-radius: 10px;">
            <div style="flex: 1;"><div style="color: #9CA3AF; font-size: 12px;">Comenzi</div><div style="color: #fff; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? (int) $lunaCurentaData->comenzi : 0 }}</div></div>
          </div>
        </div>
      </div>
    </div>
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1);">
        <h2 style="color: #fff; margin: 0 0 20px 0; font-size: 24px; font-weight: 700;">Statistici totale (1C)</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px;">
          <div style="background: rgba(59, 130, 246, 0.1); padding: 20px; border-radius: 12px; text-align: center;"><div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px;">Vânzări fără TVA</div><div style="color: #fff; font-size: 22px; font-weight: 800;">{{ number_format($date['vanzari_fara_tva'], 2, ',', '.') }} MDL</div></div>
          <div style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 12px; text-align: center;"><div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px;">Vânzări cu TVA</div><div style="color: #fff; font-size: 22px; font-weight: 700;">{{ number_format($date['vanzari_cu_tva'], 2, ',', '.') }} MDL</div></div>
          <div style="background: rgba(74, 222, 128, 0.1); padding: 20px; border-radius: 12px; text-align: center;"><div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px;">Profit</div><div style="color: #10B981; font-size: 22px; font-weight: 800;">{{ number_format($date['profit'], 2, ',', '.') }} MDL</div></div>
          <div style="background: rgba(255, 238, 0, 0.1); padding: 20px; border-radius: 12px; text-align: center;"><div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px;">Comenzi</div><div style="color: #FFEE00; font-size: 22px; font-weight: 800;">{{ number_format($date['nr_comenzi'], 0, ',', '.') }}</div></div>
        </div>
      </div>
      @if($vanzariLunare1c->count() > 0)
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1);">
        <h2 style="color: #fff; margin: 0 0 20px 0; font-size: 24px; font-weight: 700;">Vânzări pe luni (fără TVA)</h2>
        <div style="position: relative; height: 320px;"><canvas id="vanzariChartRaport"></canvas></div>
      </div>
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1);">
        <h2 style="color: #fff; margin: 0 0 20px 0; font-size: 24px; font-weight: 700;">Tabel pe luni</h2>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: rgba(255, 238, 0, 0.15);"><th style="padding: 14px; text-align: left; font-weight: 700; color: #fff;">Lună</th><th style="padding: 14px; text-align: center; font-weight: 700; color: #fff;">Comenzi</th><th style="padding: 14px; text-align: center; font-weight: 700; color: #fff;">Vânzări (fără TVA)</th><th style="padding: 14px; text-align: center; font-weight: 700; color: #fff;">Profit</th></tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare1c as $luna)
              <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);"><td style="padding: 14px; color: #fff; font-weight: 600;">{{ $luna->luna_label }}</td><td style="padding: 14px; text-align: center; color: #fff;">{{ $luna->comenzi }}</td><td style="padding: 14px; text-align: center; color: #fff;">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} MDL</td><td style="padding: 14px; text-align: center; color: #10B981; font-weight: 700;">{{ number_format($luna->profit, 2, ',', '.') }} MDL</td></tr>
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
  <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 2px dashed rgba(255, 255, 255, 0.15); border-radius: 16px; padding: 48px 24px; text-align: center;">
    <i class="fas fa-database" style="font-size: 56px; color: #9CA3AF; margin-bottom: 20px; display: block;"></i>
    <p style="color: #E5E7EB; font-size: 18px; margin: 0;">Nu există date din 1C pentru <strong>{{ $operatorNume ?: '—' }}</strong>.</p>
  </div>
@endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush
