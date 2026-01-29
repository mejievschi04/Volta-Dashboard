@extends('layouts.app')

@section('title', 'Profil Operator – VOLTA')

@section('content')
<script>
  // Safety: Define funcția imediat pentru a evita erorile de referință
  if (typeof window.openVanzareModal === 'undefined') {
    window.openVanzareModal = function(luna) {
      console.warn('openVanzareModal: Funcția nu este încă completă. Așteptați încărcarea scriptului.');
    };
  }
</script>
<div style="padding: 20px; max-width: 1200px; margin: 0 auto;">
  @if(session('success'))
  <div style="background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #fff; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);">
    <i class="fas fa-check-circle" style="font-size: 20px;"></i>
    <span style="font-weight: 600;">{{ session('success') }}</span>
  </div>
  @endif
  
  @if(session('error'))
  <div style="background: linear-gradient(135deg, #F87171 0%, #EF4444 100%); color: #fff; padding: 16px 20px; border-radius: 12px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">
    <i class="fas fa-exclamation-circle" style="font-size: 20px;"></i>
    <span style="font-weight: 600;">{{ session('error') }}</span>
  </div>
  @endif
  
  <div style="margin-bottom: 20px;">
    <a href="{{ route('operatori') }}" style="color: #fff; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; background: rgba(107, 114, 128, 0.15); border-radius: 8px; border: 1px solid rgba(107, 114, 128, 0.3); transition: all 0.2s;" onmouseover="this.style.background='rgba(107, 114, 128, 0.25)'" onmouseout="this.style.background='rgba(107, 114, 128, 0.15)'">
      <i class="fas fa-arrow-left"></i> Înapoi la Operatori
    </a>
  </div>

  <!-- Cover Photo & Profile Header -->
  <div style="background: linear-gradient(135deg, rgba(255, 238, 0, 0.3) 0%, rgba(255, 238, 0, 0.2) 50%, rgba(255, 238, 0, 0.1) 100%); background-color: var(--bg-soft); border-radius: 16px 16px 0 0; height: 320px; position: relative; margin-bottom: 0; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
    <div style="position: absolute; bottom: -80px; left: 40px; display: flex; align-items: flex-end; gap: 20px;">
      <!-- Avatar -->
      <div style="width: 160px; height: 160px; border-radius: 50%; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); background-color: var(--bg-soft); display: flex; align-items: center; justify-content: center; font-size: 64px; font-weight: 700; color: #000; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); border: 6px solid #111827; position: relative;">
        {{ strtoupper(substr($operator->nume, 0, 1)) }}
      </div>
      <!-- Name & Status -->
      <div style="padding-bottom: 20px; color: var(--ink);">
        <h1 style="color: var(--ink); margin: 0 0 10px 0; font-size: 36px; font-weight: 800; text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);">
          {{ $operator->nume }}
        </h1>
        <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
          @if($operator->data_angajare)
          <span style="background-color: var(--bg-soft); color: #FFEE00; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(10px); border: 1px solid #FFEE00;">
            <i class="fas fa-calendar-alt"></i> Angajat din {{ $operator->data_angajare->format('d.m.Y') }}
          </span>
          @endif
          <span style="background-color: var(--bg-soft); color: #FFEE00; padding: 8px 16px; border-radius: 20px; font-size: 14px; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; backdrop-filter: blur(10px); border: 1px solid #FFEE00;">
            <i class="fas fa-{{ $operator->activ ? 'check-circle' : 'times-circle' }}"></i>
            {{ $operator->activ ? 'Activ' : 'Inactiv' }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content Area -->
  <div style="display: grid; grid-template-columns: 300px 1fr; gap: 20px; margin-top: 100px;">
    
    <!-- Left Sidebar - Profile Info -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      <!-- Current Month Performance Card -->
      @php
        $lunaCurenta = now()->format('Y-m');
        $vanzariLunaCurenta = $vanzari->filter(function($v) use ($lunaCurenta) {
          return $v->data->format('Y-m') == $lunaCurenta;
        });
        $vanzariLunaCurentaSuma = $vanzariLunaCurenta->sum('suma_fara_tva');
        $vanzariLunaCurentaProfit = $vanzariLunaCurenta->sum('profit');
        $vanzariLunaCurentaCount = $vanzariLunaCurenta->count();
      @endphp
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <h3 style="color: #fff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
          <i class="fas fa-calendar-check" style="color: #3b82f6;"></i>Luna Curentă
        </h3>
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(59, 130, 246, 0.1); border-radius: 10px; border: 1px solid rgba(59, 130, 246, 0.2);">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(59, 130, 246, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-shopping-cart" style="color: #3b82f6;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Vânzări (fără TVA)</div>
              <div style="color: #fff; font-size: 16px; font-weight: 700;">{{ number_format($vanzariLunaCurentaSuma, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(74, 222, 128, 0.1); border-radius: 10px; border: 1px solid rgba(74, 222, 128, 0.2);">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(74, 222, 128, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-trophy" style="color: #10B981;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Profit</div>
              <div style="color: #10B981; font-size: 16px; font-weight: 700;">{{ number_format($vanzariLunaCurentaProfit, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.03); border-radius: 10px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(255, 238, 0, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-list" style="color: #FFEE00;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Comenzi</div>
              <div style="color: #fff; font-size: 16px; font-weight: 700;">{{ $vanzariLunaCurentaCount }}</div>
            </div>
          </div>
          
          @if($vanzariLunaCurentaCount > 0)
          <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: rgba(255, 255, 255, 0.03); border-radius: 10px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background: rgba(168, 85, 247, 0.2); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calculator" style="color: #8B5CF6;"></i>
            </div>
            <div style="flex: 1;">
              <div style="color: #9CA3AF; font-size: 12px; margin-bottom: 4px;">Medie/Comandă</div>
              <div style="color: #fff; font-size: 14px; font-weight: 500;">{{ number_format($vanzariLunaCurentaSuma / $vanzariLunaCurentaCount, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          @endif
        </div>
      </div>

      @if($operator->observatii)
      <!-- Notes Card -->
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <h3 style="color: #fff; margin: 0 0 16px 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px;">
          <i class="fas fa-sticky-note" style="color: #FFEE00;"></i>Observații
        </h3>
        <p style="color: #9CA3AF; font-size: 14px; line-height: 1.6; margin: 0;">{{ $operator->observatii }}</p>
      </div>
      @endif
    </div>

    <!-- Main Feed -->
    <div style="display: flex; flex-direction: column; gap: 20px;">
      
      <!-- Sales Stats Card -->
      @if(isset($vanzariStats))
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-line" style="color: #fff; font-size: 20px;"></i>
            </div>
            Statistici Vânzări
          </h2>
        </div>
        <div class="kpi-cards-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
          <div class="kpi-card" style="background: rgba(59, 130, 246, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(59, 130, 246, 0.2); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-shopping-cart" style="margin-right: 6px;"></i>Total Vânzări
            </div>
            <div class="kpi-value" style="color: #fff; font-size: 28px; font-weight: 800;">{{ $vanzariStats['total_vanzari'] }}</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-money-bill-wave" style="margin-right: 6px;"></i>Suma (fără TVA)
            </div>
            <div class="kpi-value" style="color: #fff; font-size: 22px; font-weight: 700;">{{ number_format($vanzariStats['total_suma_fara_tva'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-receipt" style="margin-right: 6px;"></i>Suma (cu TVA)
            </div>
            <div class="kpi-value" style="color: #fff; font-size: 22px; font-weight: 700;">{{ number_format($vanzariStats['total_suma_cu_tva'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(74, 222, 128, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(74, 222, 128, 0.2); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-trophy" style="margin-right: 6px;"></i>Total Profit
            </div>
            <div class="kpi-value" style="color: #10B981; font-size: 28px; font-weight: 800;">{{ number_format($vanzariStats['total_profit'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-list" style="margin-right: 6px;"></i>Număr Vânzări
            </div>
            <div class="kpi-value" style="color: #fff; font-size: 28px; font-weight: 800;">{{ $vanzariStats['total_nr_vanzari'] }}</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 238, 0, 0.1); padding: 20px; border-radius: 12px; border: 1px solid rgba(255, 238, 0, 0.2); text-align: center;">
            <div class="kpi-label" style="color: #9CA3AF; font-size: 12px; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
              <i class="fas fa-calendar-alt" style="margin-right: 6px;"></i>Medie/Lună
            </div>
            <div class="kpi-value" style="color: #FFEE00; font-size: 22px; font-weight: 700;">{{ number_format($vanzariStats['medie_vanzari_luna'], 2, ',', '.') }} LEI</div>
          </div>
        </div>
      </div>
      @endif

      <!-- Sales Chart (fără TVA) -->
      @if(isset($vanzariLunare) && $vanzariLunare->count() > 0)
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-chart-line" style="color: #fff; font-size: 20px;"></i>
            </div>
            Grafic Vânzări (fără TVA)
          </h2>
        </div>
        <div style="position: relative; height: 400px;">
          <canvas id="vanzariChart"></canvas>
        </div>
      </div>
      @endif

      <!-- Monthly Sales Table -->
      @if(isset($vanzariLunare) && $vanzariLunare->count() > 0)
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-calendar-alt" style="color: #fff; font-size: 20px;"></i>
            </div>
            Vânzări pe Luni
          </h2>
        </div>
        <div style="overflow-x: auto;">
          <table id="vanzariLunareTable" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: rgba(255, 255, 255, 0.05);">
                <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(255, 255, 255, 0.1); font-weight: 700; color: #fff;">Lună</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(255, 255, 255, 0.1); font-weight: 700; color: #fff;">Comenzi</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(255, 255, 255, 0.1); font-weight: 700; color: #fff;">Vânzări (fără TVA)</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(255, 255, 255, 0.1); font-weight: 700; color: #fff;">Profit</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(255, 255, 255, 0.1); font-weight: 700; color: #fff;">Număr Vânzări</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare as $luna)
              <tr class="table-row-hover" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.2s;">
                <td class="row-text" style="padding: 16px; color: #fff; font-weight: 600;">{{ $luna->luna_label }}</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff;">{{ $luna->comenzi }}</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff; font-weight: 600;">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} LEI</td>
                <td style="padding: 16px; text-align: center; color: #10B981; font-weight: 700; font-size: 16px;">{{ number_format($luna->profit, 2, ',', '.') }} LEI</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff;">{{ $luna->nr_vanzari }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      <!-- Tabel Vânzări pe Luni (cu posibilitate de editare) -->
      @if(isset($vanzariLunare))
      <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 24px; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
          <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center;">
              <i class="fas fa-list-alt" style="color: #000; font-size: 20px;"></i>
            </div>
            Vânzări pe Luni
          </h2>
          @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
          <div style="display: flex; gap: 8px;">
            <button onclick="openVanzareModal()" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(255, 238, 0, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(255, 238, 0, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(255, 238, 0, 0.3)'">
              <i class="fas fa-plus"></i> Adaugă Vânzare
            </button>
            <a href="{{ route('operatori.upload', $operator->id) }}" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: #fff; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 14px; text-decoration: none; display: flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(59, 130, 246, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(59, 130, 246, 0.3)'">
              <i class="fas fa-file-excel"></i> Încarcă Excel
            </a>
          </div>
          @endif
        </div>
        <div style="overflow-x: auto;">
          <table id="vanzariTable" style="width: 100%; border-collapse: collapse;">
            <thead>
              <tr style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000;">
                <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Lună</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Comenzi</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Vânzări (fără TVA)</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Profit</th>
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Nr. Vânzări</th>
                @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
                <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Acțiuni</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($vanzariLunare as $luna)
              <tr class="table-row-hover" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.2s;">
                <td class="row-text" style="padding: 16px; color: #fff; font-weight: 600;">{{ $luna->luna_label }}</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff;">{{ $luna->comenzi }}</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff; font-weight: 600;">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} LEI</td>
                <td style="padding: 16px; text-align: center; color: #10B981; font-weight: 700; font-size: 16px;">{{ number_format($luna->profit, 2, ',', '.') }} LEI</td>
                <td class="row-text" style="padding: 16px; text-align: center; color: #fff;">{{ $luna->nr_vanzari }}</td>
                @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
                <td style="padding: 16px; text-align: center;">
                  <div style="display: flex; gap: 8px; justify-content: center;">
                    <button onclick="openVanzareModal('{{ $luna->luna }}')" style="background: rgba(59, 130, 246, 0.2); color: #3b82f6; border: 1px solid rgba(59, 130, 246, 0.3); padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(59, 130, 246, 0.3)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.2)'">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="deleteVanzareLuna('{{ $luna->luna }}')" style="background: rgba(239, 68, 68, 0.2); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3); padding: 8px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;" onmouseover="this.style.background='rgba(239, 68, 68, 0.3)'" onmouseout="this.style.background='rgba(239, 68, 68, 0.2)'">
                      <i class="fas fa-trash"></i>
                    </button>
                  </div>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator') ? '6' : '5' }}" style="padding: 40px; text-align: center; color: #9CA3AF;">
                  <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                  Nu există vânzări înregistrate
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif

    </div>
  </div>
</div>

<!-- Modal pentru Adăugare/Editare Vânzare -->
@if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
<div id="vanzareModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 10000; align-items: center; justify-content: center;">
  <div style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border-radius: 16px; padding: 32px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.5); position: relative;">
    <button onclick="closeVanzareModal()" style="position: absolute; top: 16px; right: 16px; background: rgba(255, 255, 255, 0.1); color: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.2)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
      <i class="fas fa-times"></i>
    </button>
    <h2 style="color: #fff; margin: 0 0 24px 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
      <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center;">
        <i class="fas fa-plus" style="color: #000; font-size: 18px;"></i>
      </div>
      <span id="modalTitle">Adaugă Vânzare</span>
    </h2>
    <form id="vanzareForm" method="POST" action="{{ route('operatori.vanzari.store', $operator->id) }}">
      @csrf
      <input type="hidden" id="vanzareId" name="luna_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">
      
      <div style="display: flex; flex-direction: column; gap: 20px;">
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Lună *</label>
          <input type="month" name="luna" id="vanzareLuna" required 
                 style="width: 100%; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;" 
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 255, 255, 0.1)'" 
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'">
        </div>
        
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Suma (fără TVA) *</label>
          <input type="number" name="suma_fara_tva" id="vanzareSumaFaraTva" step="0.01" min="0" required 
                 style="width: 100%; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;" 
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 255, 255, 0.1)'" 
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="0.00">
        </div>
        
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Suma (cu TVA) *</label>
          <input type="number" name="suma_cu_tva" id="vanzareSumaCuTva" step="0.01" min="0" required 
                 style="width: 100%; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;" 
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 255, 255, 0.1)'" 
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="0.00">
        </div>
        
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Profit *</label>
          <input type="number" name="profit" id="vanzareProfit" step="0.01" required 
                 style="width: 100%; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;" 
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 255, 255, 0.1)'" 
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="0.00">
        </div>
        
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Număr Vânzări</label>
          <input type="number" name="nr_vanzari" id="vanzareNrVanzari" min="0" value="1" 
                 style="width: 100%; padding: 12px; border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;" 
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 255, 255, 0.1)'" 
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.2)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="1">
        </div>
        
        <div style="display: flex; gap: 12px; margin-top: 8px;">
          <button type="submit" style="flex: 1; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; border: none; padding: 14px; border-radius: 10px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(255, 238, 0, 0.3);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(255, 238, 0, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(255, 238, 0, 0.3)'">
            <i class="fas fa-save"></i> Salvează
          </button>
          <button type="button" onclick="closeVanzareModal()" style="flex: 1; background: rgba(255, 255, 255, 0.1); color: #fff; border: 1px solid rgba(255, 255, 255, 0.2); padding: 14px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.15)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.1)'">
            Anulează
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
@endif

@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ url('js/operatori-det.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const rows = document.querySelectorAll('.table-row-hover');
  rows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(255, 238, 0, 0.15)';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#000';
        }
      });
    });
    row.addEventListener('mouseleave', function() {
      this.style.background = 'transparent';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '';
        }
      });
    });
  });

  // Grafic Vânzări fără TVA
  @if(isset($vanzariLunare) && $vanzariLunare->count() > 0)
  const vanzariChartCanvas = document.getElementById('vanzariChart');
  if (vanzariChartCanvas) {
    const ctx = vanzariChartCanvas.getContext('2d');
    @php
      $vanzariLunareArray = [];
      if (isset($vanzariLunare) && $vanzariLunare->count() > 0) {
        $vanzariLunareArray = $vanzariLunare->reverse()->values()->all();
      }
    @endphp
    const vanzariLunareJson = @json($vanzariLunareArray);
    
    // Debug
    console.log('vanzariLunareJson:', vanzariLunareJson);
    console.log('Type:', typeof vanzariLunareJson);
    console.log('Is Array:', Array.isArray(vanzariLunareJson));
    
    // Convertim în array sigur
    let vanzariLunareArray = [];
    if (Array.isArray(vanzariLunareJson)) {
      vanzariLunareArray = vanzariLunareJson;
    } else if (vanzariLunareJson && typeof vanzariLunareJson === 'object') {
      vanzariLunareArray = Object.values(vanzariLunareJson);
    }
    
    console.log('vanzariLunareArray:', vanzariLunareArray);
    console.log('Is Array:', Array.isArray(vanzariLunareArray));
    
    if (!Array.isArray(vanzariLunareArray) || vanzariLunareArray.length === 0) {
      console.warn('Nu există date pentru grafic');
      return;
    }
    
    const labels = vanzariLunareArray.map(function(v) { return v?.luna_label || ''; });
    const data = vanzariLunareArray.map(function(v) { return parseFloat(v?.vanzari_luna) || 0; });
    
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vanzari (fara TVA)',
          data: data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: window.innerWidth <= 768 ? 2 : 5,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: window.innerWidth <= 768 ? 1 : 2,
          pointHoverRadius: window.innerWidth <= 768 ? 4 : 7,
          pointHoverBackgroundColor: '#2563eb',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: '#fff',
              font: {
                size: window.innerWidth <= 768 ? 10 : 14,
                weight: 'bold'
              },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true,
              boxWidth: window.innerWidth <= 768 ? 8 : 12,
              boxHeight: window.innerWidth <= 768 ? 8 : 12
            },
            display: true,
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#3b82f6',
            bodyColor: '#fff',
            borderColor: '#3b82f6',
            borderWidth: 1,
            padding: 12,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(context.parsed.y) + ' LEI';
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#fff',
              font: {
                size: window.innerWidth <= 768 ? 9 : 12
              }
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.05)'
            }
          },
          y: {
            ticks: {
              color: '#fff',
              font: {
                size: 12
              },
              callback: function(value) {
                return new Intl.NumberFormat('ro-RO', {
                  minimumFractionDigits: 0,
                  maximumFractionDigits: 0
                }).format(value) + ' LEI';
              }
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.05)'
            },
            beginAtZero: true
          }
        }
      }
    });
  }
  @endif
});
</script>

<script>
  // Funcții pentru modal vânzări (pe luni)
  @php
    $isAdmin = auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator');
  @endphp
  
  // Definește variabila de date
  @if($isAdmin)
  const vanzariLunareData = @json($vanzariLunareForJs ?? []);
  @else
  const vanzariLunareData = {};
  @endif

  // Definește funcția - asigură-te că este disponibilă global
  window.openVanzareModal = function(luna = null) {
    const modal = document.getElementById('vanzareModal');
    if (!modal) {
      console.error('Modal vanzareModal nu a fost gasit');
      return;
    }
    
    const form = document.getElementById('vanzareForm');
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');
    const idInput = document.getElementById('vanzareId');
    
    if (!form || !title || !methodInput || !idInput) {
      console.error('Elemente necesare pentru modal nu au fost gasite');
      return;
    }
    
    // Reset form
    form.reset();
    form.action = '{{ route("operatori.vanzari.store", $operator->id) }}';
    methodInput.value = 'POST';
    idInput.value = '';
    title.textContent = 'Adauga Vanzare';
    
    // Dacă edităm o vânzare existentă
    if (luna && vanzariLunareData && vanzariLunareData[luna]) {
      const vanzare = vanzariLunareData[luna];
      const lunaInput = document.getElementById('vanzareLuna');
      const sumaFaraTvaInput = document.getElementById('vanzareSumaFaraTva');
      const sumaCuTvaInput = document.getElementById('vanzareSumaCuTva');
      const profitInput = document.getElementById('vanzareProfit');
      const nrVanzariInput = document.getElementById('vanzareNrVanzari');
      
      if (lunaInput) lunaInput.value = vanzare.luna || '';
      if (sumaFaraTvaInput) sumaFaraTvaInput.value = vanzare.suma_fara_tva || '';
      if (sumaCuTvaInput) sumaCuTvaInput.value = vanzare.suma_cu_tva || '';
      if (profitInput) profitInput.value = vanzare.profit || '';
      if (nrVanzariInput) nrVanzariInput.value = vanzare.nr_vanzari || 1;
      
      form.action = '{{ route("operatori.vanzari.update", [$operator->id, ":luna"]) }}'.replace(':luna', luna);
      methodInput.value = 'PUT';
      idInput.value = luna;
      title.textContent = 'Editeaza Vanzare';
    } else {
      // Setează luna curentă ca default
      const today = new Date();
      const currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
      const lunaInput = document.getElementById('vanzareLuna');
      if (lunaInput) {
        lunaInput.value = currentMonth;
      }
    }
    
    modal.style.display = 'flex';
  };

  window.closeVanzareModal = function() {
    const modal = document.getElementById('vanzareModal');
    if (modal) {
      modal.style.display = 'none';
    }
  };

  window.deleteVanzareLuna = function(luna) {
    if (!confirm('Esti sigur ca vrei sa stergi vanzarile pentru aceasta luna?')) {
      return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("operatori.vanzari.destroy", [$operator->id, ":luna"]) }}'.replace(':luna', luna);
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
  };

  // Închide modal la click pe fundal și ESC
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('vanzareModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === this) {
          closeVanzareModal();
        }
      });
    }

    // Închide modal cu ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('vanzareModal');
        if (modal && modal.style.display === 'flex') {
          closeVanzareModal();
        }
      }
    });
  });
</script>
@endpush
