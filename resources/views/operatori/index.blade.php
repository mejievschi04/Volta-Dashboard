@extends('layouts.app')

@section('title', 'Operatori – VOLTA')

@section('content')
<div style="padding: 20px; max-width: 1400px; margin: 0 auto;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
      <h1 style="color: #FFEE00; margin: 0; font-size: 32px; font-weight: 800; text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);">Operatori</h1>
      <p style="color: #9CA3AF; margin: 5px 0 0 0; font-size: 14px;">Gestionare și statistici operatori</p>
    </div>
    @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
    <a href="{{ route('operatori.create') }}" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 238, 0, 0.3); transition: transform 0.2s;">
      <i class="fas fa-plus"></i> Adaugă Operator
    </a>
    @endif
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
  
  @if(isset($chartData) && count($chartData) > 0)
  <!-- Grafic Circular - Distribuția Vânzărilor -->
  <div class="operator-card" style="margin-bottom: 30px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 1px solid rgba(255, 238, 0, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 2px solid rgba(255, 255, 255, 0.1);">
      <h2 style="color: #fff; margin: 0; font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 12px;">
        <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center;">
          <i class="fas fa-chart-pie" style="color: #000; font-size: 20px;"></i>
        </div>
        Distribuția Vânzărilor (fără TVA)
      </h2>
    </div>
    <div class="pie-chart-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: center;">
      <div style="position: relative; height: 400px;">
        <canvas id="vanzariPieChart"></canvas>
      </div>
      <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach($chartData as $data)
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; background: rgba(255, 255, 255, 0.05); border-radius: 8px; border-left: 4px solid rgba(255, 238, 0, 0.5);">
          <div style="flex: 1;">
            <div style="color: #fff; font-weight: 600; font-size: 14px; margin-bottom: 4px;">{{ $data['nume'] }}</div>
            <div style="color: #9CA3AF; font-size: 12px;">{{ number_format($data['vanzari_fara_tva'], 2, ',', '.') }} LEI</div>
          </div>
          <div style="color: #FFEE00; font-weight: 700; font-size: 18px; margin-left: 16px;">{{ $data['procent'] }}%</div>
        </div>
        @endforeach
      </div>
    </div>
  </div>
  @endif
  
  @if(isset($operatoriStats) && count($operatoriStats) > 0)
  <div class="operator-card" style="margin-bottom: 30px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 1px solid rgba(255, 238, 0, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
    <div style="overflow-x: auto;">
      <table id="operatoriTable" style="width: 100%; border-collapse: collapse;">
        <thead>
          <tr style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000;">
            <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Operator</th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Timp Lucrat</th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Total Vânzări (fără TVA)</th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Total Profit</th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">Acțiuni</th>
          </tr>
        </thead>
        <tbody>
          @foreach($operatoriStats as $stat)
          <tr class="table-row-hover" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.2s;">
            <td style="padding: 16px;">
              <a href="{{ route('operatori.show', $stat['operator']->id) }}" class="row-link" style="color: #fff; text-decoration: none; font-weight: 700; font-size: 16px; display: block;">
                {{ $stat['operator']->nume }}
              </a>
              @if($stat['operator']->departament)
                <div style="color: #9CA3AF; font-size: 12px; margin-top: 4px;">
                  <i class="fas fa-building" style="margin-right: 4px;"></i>{{ $stat['operator']->departament }}
                </div>
              @endif
            </td>
            <td class="row-text" style="padding: 16px; text-align: center; color: #fff; font-weight: 600; font-size: 14px;">
              <span style="display: inline-flex; align-items: center; gap: 6px;">
                <i class="fas fa-clock" style="color: #3b82f6;"></i>
                {{ $stat['timp_lucrat'] ?? 'N/A' }}
              </span>
            </td>
            <td class="row-text" style="padding: 16px; text-align: center; color: #fff; font-weight: 600; font-size: 15px;">
              {{ number_format($stat['vanzari']->total_suma_fara_tva ?? 0, 2, ',', '.') }} LEI
            </td>
            <td class="row-text" style="padding: 16px; text-align: center;">
              <span style="color: #10B981; font-weight: 700; font-size: 16px;">
                {{ number_format($stat['vanzari']->total_profit ?? 0, 2, ',', '.') }} LEI
              </span>
            </td>
            <td style="padding: 16px; text-align: center;">
              <div style="display: flex; gap: 8px; justify-content: center;">
                <a href="{{ route('operatori.show', $stat['operator']->id) }}" style="background: rgba(59, 130, 246, 0.15); color: #3b82f6; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.2s; border: 1px solid rgba(59, 130, 246, 0.3);" onmouseover="this.style.background='rgba(59, 130, 246, 0.25)'" onmouseout="this.style.background='rgba(59, 130, 246, 0.15)'">
                  <i class="fas fa-eye" style="margin-right: 4px;"></i>Profil
                </a>
                @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
                <a href="{{ route('operatori.edit', $stat['operator']->id) }}" style="background: rgba(156, 163, 175, 0.15); color: #9CA3AF; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.2s; border: 1px solid rgba(156, 163, 175, 0.3);" onmouseover="this.style.background='rgba(156, 163, 175, 0.25)'" onmouseout="this.style.background='rgba(156, 163, 175, 0.15)'">
                  <i class="fas fa-edit" style="margin-right: 4px;"></i>Edit
                </a>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="operator-card" style="margin-bottom: 30px; padding: 60px 20px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 2px dashed rgba(255, 255, 255, 0.1);">
    <div style="text-align: left;">
      <i class="fas fa-users" style="font-size: 48px; color: #9CA3AF; margin-bottom: 20px; display: block;"></i>
      <p style="color: #9CA3AF; font-size: 18px; margin: 0;">Nu există operatori în sistem momentan.</p>
    </div>
  </div>
  @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@push('scripts')
@if(isset($chartData) && count($chartData) > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Grafic Circular - Distribuția Vânzărilor
  const pieChartCanvas = document.getElementById('vanzariPieChart');
  if (pieChartCanvas) {
    const ctx = pieChartCanvas.getContext('2d');
    const chartData = @json($chartData);
    
    // Culori pentru grafic
    const colors = [
      'rgba(255, 238, 0, 0.8)',   // Galben
      'rgba(59, 130, 246, 0.8)',  // Albastru
      'rgba(74, 222, 128, 0.8)',  // Verde
      'rgba(168, 85, 247, 0.8)',  // Violet
      'rgba(239, 68, 68, 0.8)',   // Roșu
      'rgba(251, 146, 60, 0.8)',  // Portocaliu
      'rgba(34, 197, 94, 0.8)',   // Verde deschis
      'rgba(99, 102, 241, 0.8)',  // Indigo
      'rgba(236, 72, 153, 0.8)',  // Roz
      'rgba(20, 184, 166, 0.8)',  // Teal
    ];
    
    const labels = chartData.map(item => item.nume);
    const data = chartData.map(item => item.vanzari_fara_tva);
    const backgroundColors = colors.slice(0, chartData.length);
    const borderColors = backgroundColors.map(color => color.replace('0.8', '1'));
    
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vânzări (fără TVA)',
          data: data,
          backgroundColor: backgroundColors,
          borderColor: borderColors,
          borderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false, // Ascundem legenda pentru că o avem în sidebar
            labels: {
              font: {
                size: window.innerWidth <= 768 ? 10 : 12
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            borderColor: '#FFEE00',
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
                const label = context.label || '';
                const value = context.parsed || 0;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = total > 0 ? ((value / total) * 100).toFixed(2) : 0;
                return label + ': ' + new Intl.NumberFormat('ro-RO', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(value) + ' LEI (' + percentage + '%)';
              }
            }
          }
        }
      }
    });
  }
  
  // Hover effects pentru tabel
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
      const links = this.querySelectorAll('.row-link');
      links.forEach(link => {
        link.style.color = '#000';
      });
    });
    row.addEventListener('mouseleave', function() {
      this.style.background = 'transparent';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#fff';
        }
      });
      const links = this.querySelectorAll('.row-link');
      links.forEach(link => {
        link.style.color = '#fff';
      });
    });
  });
});
</script>
@else
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
      const links = this.querySelectorAll('.row-link');
      links.forEach(link => {
        link.style.color = '#000';
      });
    });
    row.addEventListener('mouseleave', function() {
      this.style.background = 'transparent';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#fff';
        }
      });
      const links = this.querySelectorAll('.row-link');
      links.forEach(link => {
        link.style.color = '#fff';
      });
    });
  });
});
</script>
@endif
@endpush

