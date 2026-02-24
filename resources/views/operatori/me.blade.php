@extends('layouts.operator')

@section('title', 'Datele mele – VOLTA')

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
@if(session('success'))
<div style="background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #fff; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
  <i class="fas fa-check-circle" style="font-size: 20px;"></i>
  <span style="font-weight: 600;">{{ session('success') }}</span>
</div>
@endif

@if($date)
  @php
    $lunaCurenta = now()->format('Y-m');
    $lunaCurentaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
  @endphp

  <!-- Cover + profil (ca în pagina individuală operator din admin) -->
  <div class="operator-me-cover" style="background: linear-gradient(135deg, rgba(255, 238, 0, 0.3) 0%, rgba(255, 238, 0, 0.2) 50%, rgba(255, 238, 0, 0.1) 100%); background-color: #1F2937; border-radius: 16px 16px 0 0; height: 280px; position: relative; margin-bottom: 0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); @if(isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_coperta_url) background-image: url('{{ $operatorRecord->photo_coperta_url }}'); background-size: cover; background-position: center; @endif">
    <div style="position: absolute; bottom: -70px; left: 40px; display: flex; align-items: flex-end; gap: 20px;">
      @if(isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_profil_url)
      <div class="operator-me-avatar" style="width: 140px; height: 140px; border-radius: 50%; background: url('{{ $operatorRecord->photo_profil_url }}') center/cover; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border: 5px solid #111827;"></div>
      @else
      <div class="operator-me-avatar" style="width: 140px; height: 140px; border-radius: 50%; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center; font-size: 56px; font-weight: 700; color: #000; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border: 5px solid #111827;">
        {{ strtoupper(mb_substr($date['nume'], 0, 1)) }}
      </div>
      @endif
      <div style="padding-bottom: 16px;">
        <h1 class="operator-me-title" style="color: #fff; margin: 0 0 8px 0; font-size: 32px; font-weight: 800; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);">
          {{ $date['nume'] }}
        </h1>
        <span style="background: rgba(17, 24, 39, 0.9); color: #FFEE00; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #FFEE00;">
          <i class="fas fa-database"></i> Rezultate din 1C (ian. 2023 – prezent)
        </span>
        @if(isset($operatorRecord) && $operatorRecord)
        <a href="{{ route('setari') }}#poze" style="margin-left: 12px; color: #9CA3AF; font-size: 13px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Schimbă pozele din Setări"><i class="fas fa-cog"></i> Poze în Setări</a>
        @endif
      </div>
    </div>
  </div>

  <div class="operator-me-grid" style="display: grid; grid-template-columns: 300px 1fr; gap: 20px; margin-top: 90px;">
    <!-- Coloană stânga -->
    <div>
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <h3 style="color: #fff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
          <i class="fas fa-calendar-check" style="color: #3b82f6;"></i> Luna curentă
        </h3>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.2);">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-shopping-cart" style="color: #3b82f6;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Vânzări (fără TVA)</div>
              <div style="color: #fff; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? number_format($lunaCurentaData->vanzari_luna, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(74, 222, 128, 0.1); border-radius: 10px; border: 1px solid rgba(74, 222, 128, 0.2);">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(74, 222, 128, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-trophy" style="color: #10B981;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Profit</div>
              <div style="color: #10B981; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? number_format($lunaCurentaData->profit, 2, ',', '.') : '0,00' }} MDL</div>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.03); border-radius: 10px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 238, 0, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-list" style="color: #FFEE00;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Comenzi</div>
              <div style="color: #fff; font-size: 16px; font-weight: 700;">{{ $lunaCurentaData ? (int) $lunaCurentaData->comenzi : 0 }}</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Coloană dreapta: Statistici totale + grafic + tabel -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Card statistici totale (ca în show) -->
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-line" style="color: #fff; font-size: 20px;"></i>
            </div>
            Statistici totale (1C)
          </h2>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 20px;">
          <div style="background: rgba(59, 130, 246, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.2); text-align: center;">
            <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Vânzări fără TVA</div>
            <div style="color: #fff; font-size: 22px; font-weight: 800;">{{ number_format($date['vanzari_fara_tva'], 2, ',', '.') }} <span style="font-size: 12px; color: #9CA3AF;">MDL</span></div>
          </div>
          <div style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
            <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Vânzări cu TVA</div>
            <div style="color: #fff; font-size: 22px; font-weight: 700;">{{ number_format($date['vanzari_cu_tva'], 2, ',', '.') }} <span style="font-size: 12px; color: #9CA3AF;">MDL</span></div>
          </div>
          <div style="background: rgba(74, 222, 128, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(74, 222, 128, 0.2); text-align: center;">
            <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Profit</div>
            <div style="color: #10B981; font-size: 22px; font-weight: 800;">{{ number_format($date['profit'], 2, ',', '.') }} <span style="font-size: 12px; color: #9CA3AF;">MDL</span></div>
          </div>
          <div style="background: rgba(255, 238, 0, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 238, 0, 0.2); text-align: center;">
            <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Comenzi</div>
            <div style="color: #FFEE00; font-size: 22px; font-weight: 800;">{{ number_format($date['nr_comenzi'], 0, ',', '.') }}</div>
          </div>
        </div>
      </div>

      <!-- Grafic vânzări pe luni -->
      @if($vanzariLunare1c->count() > 0)
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-line" style="color: #fff; font-size: 20px;"></i>
            </div>
            Vânzări pe luni (fără TVA)
          </h2>
        </div>
        <div style="position: relative; height: 320px;">
          <canvas id="vanzariChartMe"></canvas>
        </div>
      </div>
      @endif

      <!-- Tabel vânzări pe luni -->
      @if($vanzariLunare1c->count() > 0)
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calendar-alt" style="color: #000; font-size: 20px;"></i>
            </div>
            Vânzări pe luni
          </h2>
        </div>
        <div style="overflow-x: auto;">
          <table style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: linear-gradient(135deg, rgba(255, 238, 0, 0.2) 0%, rgba(255, 238, 0, 0.1) 100%);">
                <th style="padding: 14px 16px; text-align: left; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700; color: #fff;">Lună</th>
                <th style="padding: 14px 16px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700; color: #fff;">Comenzi</th>
                <th style="padding: 14px 16px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700; color: #fff;">Vânzări (fără TVA)</th>
                <th style="padding: 14px 16px; text-align: center; border-bottom: 2px solid rgba(255, 238, 0, 0.3); font-weight: 700; color: #fff;">Profit</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare1c as $luna)
              <tr style="border-bottom: 1px solid rgba(255, 255, 255, 0.06); transition: background 0.2s;" onmouseover="this.style.background='rgba(255, 238, 0, 0.08)'" onmouseout="this.style.background='transparent'">
                <td style="padding: 14px 16px; color: #fff; font-weight: 600;">{{ $luna->luna_label }}</td>
                <td style="padding: 14px 16px; text-align: center; color: #fff;">{{ $luna->comenzi }}</td>
                <td style="padding: 14px 16px; text-align: center; color: #fff; font-weight: 600;">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} MDL</td>
                <td style="padding: 14px 16px; text-align: center; color: #10B981; font-weight: 700;">{{ number_format($luna->profit, 2, ',', '.') }} MDL</td>
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
    new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vânzări (fără TVA) MDL',
          data: data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: 4,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: 2
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { labels: { color: '#fff', font: { size: 12 } } },
          tooltip: {
            backgroundColor: 'rgba(0,0,0,0.9)',
            titleColor: '#3b82f6',
            bodyColor: '#fff',
            callbacks: {
              label: function(ctx) {
                return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2 }).format(ctx.parsed.y) + ' MDL';
              }
            }
          }
        },
        scales: {
          x: { ticks: { color: '#9CA3AF', maxRotation: 45 }, grid: { color: 'rgba(255,255,255,0.05)' } },
          y: {
            ticks: { color: '#9CA3AF', callback: function(v) { return new Intl.NumberFormat('ro-RO', { maximumFractionDigits: 0 }).format(v) + ' MDL'; } },
            grid: { color: 'rgba(255,255,255,0.05)' },
            beginAtZero: true
          }
        }
      }
    });
  });
  </script>
  @endpush
  @endif
@else
  <!-- Fără date 1C -->
  <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 2px dashed rgba(255, 255, 255, 0.15); border-radius: 16px; padding: 48px 24px; text-align: center; margin-top: 20px;">
    <i class="fas fa-database" style="font-size: 56px; color: #9CA3AF; margin-bottom: 20px; display: block; opacity: 0.7;"></i>
    <p style="color: #E5E7EB; font-size: 18px; margin: 0; font-weight: 600;">Nu există date din 1C pentru contul tău</p>
    <p style="color: #9CA3AF; font-size: 14px; margin: 12px 0 0 0;">Nume asociat: <strong style="color: #FFEE00;">{{ $operatorNume ?: '—' }}</strong></p>
    <p style="color: #6B7280; font-size: 13px; margin: 8px 0 0 0;">Contactează administratorul pentru a seta rolul „Operator” și numele de operator (să coincidă cu 1C).</p>
  </div>
@endif
@endsection
