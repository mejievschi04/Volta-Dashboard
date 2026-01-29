@extends('layouts.app')

@section('title', 'Statistici Trafic – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.stats-page {
  padding: 30px;
}

.stats-page h1 {
  margin: 0 0 30px;
  font-size: 32px;
  font-weight: 800;
  color: #FFEE00;
  text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);
  letter-spacing: -0.5px;
}

.stats-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 40px;
}

.stat-btn {
  background: rgba(31, 41, 55, 0.4);
  color: #FFEE00;
  border: 2px solid rgba(255, 238, 0, 0.3);
  padding: 25px 20px;
  border-radius: 12px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 700;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-btn i {
  font-size: 32px;
  margin-bottom: 5px;
}

.stat-btn:hover {
  background: rgba(255, 238, 0, 0.1);
  border-color: rgba(255, 238, 0, 0.6);
  box-shadow: 0 0 25px rgba(255, 238, 0, 0.4);
  transform: translateY(-5px);
}

.stat-btn:active {
  transform: translateY(-2px);
}

.stat-btn.active {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.2) 0%, rgba(255, 238, 0, 0.1) 100%);
  border-color: #FFEE00;
  box-shadow: 0 0 30px rgba(255, 238, 0, 0.6);
  color: #FFEE00;
}

.stat-btn-total {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.15) 0%, rgba(255, 238, 0, 0.05) 100%);
  border-color: rgba(255, 238, 0, 0.5);
}

.stat-btn-total:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.25) 0%, rgba(255, 238, 0, 0.15) 100%);
}

.stats-general-card {
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.8) 0%, rgba(31, 41, 55, 0.9) 100%);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4), 0 0 20px rgba(255, 238, 0, 0.1);
  border: 1px solid rgba(255, 238, 0, 0.15);
  backdrop-filter: blur(10px);
  margin-top: 30px;
}

.stats-general-card h3 {
  color: #FFEE00;
  margin-bottom: 25px;
  font-size: 28px;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 15px;
}

/* === Chart Wrapper Modern === */
.chart-wrapper-modern {
  position: relative;
  height: 550px;
  margin-top: 20px;
  background: linear-gradient(135deg, rgba(31, 41, 55, 0.4) 0%, rgba(31, 41, 55, 0.6) 100%);
  border-radius: 12px;
  padding: 20px;
  border: 1px solid rgba(255, 238, 0, 0.1);
  box-shadow: 
    inset 0 2px 10px rgba(0, 0, 0, 0.3),
    0 0 20px rgba(255, 238, 0, 0.05);
  overflow: hidden;
}

.chart-wrapper-modern::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 2px;
  background: linear-gradient(90deg, 
    transparent, 
    rgba(255, 238, 0, 0.5), 
    rgba(255, 238, 0, 0.8), 
    rgba(255, 238, 0, 0.5), 
    transparent
  );
  animation: shimmer 3s ease-in-out infinite;
  z-index: 2;
}

.chart-wrapper-modern::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 1px;
  background: linear-gradient(90deg, 
    transparent, 
    rgba(255, 238, 0, 0.3), 
    transparent
  );
  z-index: 2;
}

@keyframes shimmer {
  0%, 100% { 
    opacity: 0.5;
    transform: translateX(-100%);
  }
  50% { 
    opacity: 1;
    transform: translateX(100%);
  }
}

.chart-wrapper-modern canvas {
  position: relative;
  z-index: 1;
  filter: drop-shadow(0 0 10px rgba(255, 238, 0, 0.1));
}

.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 20px;
}

.stat-item {
  background: rgba(31, 41, 55, 0.4);
  padding: 25px;
  border-radius: 12px;
  border-left: 4px solid #FFEE00;
  transition: all 0.3s ease;
}

.stat-item:hover {
  background: rgba(31, 41, 55, 0.6);
  transform: translateY(-3px);
  box-shadow: 0 5px 15px rgba(255, 238, 0, 0.2);
}

.stat-item h4 {
  color: #9CA3AF;
  font-size: 13px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin: 0 0 12px 0;
}

.stat-item .value {
  color: #FFEE00;
  font-size: 32px;
  font-weight: 800;
  margin: 0;
  text-shadow: 0 0 10px rgba(255, 238, 0, 0.5);
  line-height: 1.2;
}

.stat-item .change {
  color: #9CA3AF;
  font-size: 12px;
  margin-top: 8px;
}

.back-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: #FFEE00;
  text-decoration: none;
  font-weight: 600;
  margin-bottom: 20px;
  padding: 8px 15px;
  border-radius: 8px;
  background: rgba(31, 41, 55, 0.3);
  border: 1px solid rgba(255, 238, 0, 0.2);
  transition: all 0.3s ease;
}

.back-btn:hover {
  background: rgba(255, 238, 0, 0.1);
  border-color: rgba(255, 238, 0, 0.4);
  transform: translateX(-3px);
}

.loading {
  text-align: center;
  padding: 40px;
  color: #9CA3AF;
}

.loading i {
  font-size: 48px;
  color: #FFEE00;
  animation: spin 1s linear infinite;
  margin-bottom: 15px;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

/* === Mobile Responsive === */
@media (max-width: 768px) {
  .stats-page {
    padding: 15px;
    padding-top: 75px;
  }
  
  .stats-page h1 {
    font-size: 24px;
    margin-bottom: 20px;
  }
  
  .stats-buttons {
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
  }
  
  .stat-btn {
    padding: 18px 15px;
    font-size: 14px;
  }
  
  .stat-btn i {
    font-size: 24px;
  }
  
  .stats-general-card {
    padding: 20px;
    margin-top: 20px;
  }
  
  .stats-general-card h3 {
    font-size: 22px;
    margin-bottom: 20px;
    flex-wrap: wrap;
  }
  
  .stats-general-card h3 span {
    font-size: 16px;
    margin-left: 0;
    margin-top: 5px;
    width: 100%;
  }
  
  .chart-wrapper-modern {
    height: 400px !important;
    padding: 15px;
  }
  
  .back-btn {
    font-size: 14px;
    padding: 6px 12px;
    margin-bottom: 15px;
  }
  
  .loading {
    padding: 30px 20px;
  }
  
  .loading i {
    font-size: 36px;
  }
}

@media (max-width: 480px) {
  .stats-page {
    padding: 12px;
    padding-top: 70px;
  }
  
  .stats-page h1 {
    font-size: 20px;
    margin-bottom: 15px;
  }
  
  .stats-buttons {
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 20px;
  }
  
  .stat-btn {
    padding: 15px 12px;
    font-size: 13px;
  }
  
  .stat-btn i {
    font-size: 20px;
  }
  
  .stats-general-card {
    padding: 15px;
    margin-top: 15px;
  }
  
  .stats-general-card h3 {
    font-size: 18px;
    margin-bottom: 15px;
  }
  
  .stats-general-card h3 i {
    font-size: 20px;
  }
  
  .chart-wrapper-modern {
    height: 350px !important;
    padding: 12px;
  }
  
  .back-btn {
    font-size: 13px;
    padding: 5px 10px;
  }
}
</style>
@endpush

@section('content')
<div class="stats-page">
  <a href="{{ route('trafic') }}" class="back-btn">
    <i class="fas fa-arrow-left"></i>
    <span>Înapoi la Trafic</span>
  </a>
  
  <h1><i class="fas fa-chart-pie" style="margin-right: 15px;"></i>Statistici Generale Trafic</h1>
  
  <div class="stats-buttons">
    <button class="stat-btn" onclick="loadStatsPeriod(3)" title="Ultimele 3 luni">
      <i class="fas fa-chart-line"></i>
      <span>3 Luni</span>
    </button>
    <button class="stat-btn" onclick="loadStatsPeriod(6)" title="Ultimele 6 luni">
      <i class="fas fa-chart-bar"></i>
      <span>6 Luni</span>
    </button>
    <button class="stat-btn" onclick="loadStatsPeriod(12)" title="Ultimele 12 luni">
      <i class="fas fa-chart-area"></i>
      <span>12 Luni</span>
    </button>
    <button class="stat-btn stat-btn-total" onclick="loadStatsPeriod('total')" title="Total (toate datele)">
      <i class="fas fa-infinity"></i>
      <span>Total</span>
    </button>
  </div>
  
  <div id="statsGeneralContainer" style="display: none;">
    <div class="stats-general-card">
      <h3>
        <i class="fas fa-chart-pie"></i>
        Statistici Generale
        <span id="statsPeriodLabel" style="font-size: 20px; color: #9CA3AF; font-weight: 600; margin-left: 10px;"></span>
      </h3>
      <div class="chart-wrapper-modern">
        <canvas id="statsChart"></canvas>
      </div>
    </div>
  </div>
  
  <div id="loadingContainer" class="loading" style="display: none;">
    <i class="fas fa-spinner"></i>
    <p>Se încarcă statisticile...</p>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variabilă globală pentru grafic
let statsChart = null;

// Funcție pentru încărcarea statisticilor pentru o perioadă
async function loadStatsPeriod(months) {
  try {
    // Dezactivăm toate butoanele
    document.querySelectorAll('.stat-btn').forEach(btn => btn.classList.remove('active'));
    
    // Activăm butonul selectat
    event.target.closest('.stat-btn').classList.add('active');
    
    // Ascundem containerul de statistici și afișăm loading
    document.getElementById('statsGeneralContainer').style.display = 'none';
    document.getElementById('loadingContainer').style.display = 'block';
    
    const periodLabel = months === 'total' ? 'Total' : `Ultimele ${months} luni`;
    document.getElementById('statsPeriodLabel').textContent = `- ${periodLabel}`;
    
    // Calculăm perioada
    let startDate, endDate;
    const today = new Date();
    endDate = today.toISOString().split('T')[0];
    
    if (months === 'total') {
      startDate = '2020-01-01'; // Data de început arbitrară
    } else {
      const start = new Date(today);
      start.setMonth(today.getMonth() - months);
      startDate = start.toISOString().split('T')[0];
    }
    
    // Apelăm API-ul pentru statistici
    const response = await fetch(`{{ route('api.trafic') }}?start_date=${startDate}&end_date=${endDate}`);
    const result = await response.json();
    
    // Ascundem loading
    document.getElementById('loadingContainer').style.display = 'none';
    
    if (result.success) {
      displayStats(result);
      document.getElementById('statsGeneralContainer').style.display = 'block';
    } else {
      if (statsChart) {
        statsChart.destroy();
        statsChart = null;
      }
      const canvas = document.getElementById('statsChart');
      const ctx = canvas.getContext('2d');
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      ctx.fillStyle = '#f00';
      ctx.font = '16px Arial';
      ctx.textAlign = 'center';
      ctx.fillText(`Eroare: ${result.error || 'Eroare necunoscută'}`, canvas.width / 2, canvas.height / 2);
      document.getElementById('statsGeneralContainer').style.display = 'block';
    }
  } catch (error) {
    document.getElementById('loadingContainer').style.display = 'none';
    console.error('Eroare la încărcarea statisticilor:', error);
    if (statsChart) {
      statsChart.destroy();
      statsChart = null;
    }
    const canvas = document.getElementById('statsChart');
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#f00';
    ctx.font = '16px Arial';
    ctx.textAlign = 'center';
    ctx.fillText(`Eroare: ${error.message}`, canvas.width / 2, canvas.height / 2);
    document.getElementById('statsGeneralContainer').style.display = 'block';
  }
}

// Funcție pentru afișarea statisticilor
function displayStats(data) {
  const chartData = data.data || {};
  const labels = chartData.labels || [];
  const datasets = chartData.datasets || {};
  
  // Pregătim datele pentru grafic - FĂRĂ utilizatorii noi și vechi
  const chartDatasets = [];
  
  // Culori moderne și vibrante pentru fiecare linie
  const colors = {
    'total': { 
      border: '#FFEE00', 
      background: 'linear-gradient(180deg, rgba(255, 238, 0, 0.25) 0%, rgba(255, 238, 0, 0.05) 100%)',
      glow: 'rgba(255, 238, 0, 0.6)',
      shadow: '0 0 15px rgba(255, 238, 0, 0.4)'
    },
    'google': { 
      border: '#10B981', 
      background: 'linear-gradient(180deg, rgba(16, 185, 129, 0.25) 0%, rgba(16, 185, 129, 0.05) 100%)',
      glow: 'rgba(16, 185, 129, 0.5)',
      shadow: '0 0 12px rgba(16, 185, 129, 0.3)'
    },
    'google_cpc': { 
      border: '#8B5CF6', 
      background: 'linear-gradient(180deg, rgba(139, 92, 246, 0.25) 0%, rgba(139, 92, 246, 0.05) 100%)',
      glow: 'rgba(139, 92, 246, 0.5)',
      shadow: '0 0 12px rgba(139, 92, 246, 0.3)'
    },
    'direct': { 
      border: '#3B82F6', 
      background: 'linear-gradient(180deg, rgba(59, 130, 246, 0.25) 0%, rgba(59, 130, 246, 0.05) 100%)',
      glow: 'rgba(59, 130, 246, 0.5)',
      shadow: '0 0 12px rgba(59, 130, 246, 0.3)'
    },
    'yandex': { 
      border: '#EF4444', 
      background: 'linear-gradient(180deg, rgba(239, 68, 68, 0.25) 0%, rgba(239, 68, 68, 0.05) 100%)',
      glow: 'rgba(239, 68, 68, 0.5)',
      shadow: '0 0 12px rgba(239, 68, 68, 0.3)'
    },
    'other': { 
      border: '#06B6D4', 
      background: 'linear-gradient(180deg, rgba(6, 182, 212, 0.25) 0%, rgba(6, 182, 212, 0.05) 100%)',
      glow: 'rgba(6, 182, 212, 0.5)',
      shadow: '0 0 12px rgba(6, 182, 212, 0.3)'
    }
  };
  
  // Nume pentru fiecare sursă
  const sourceNames = {
    'total': 'Total Sesiuni',
    'google': 'Sesiuni Organice',
    'google_cpc': 'Sesiuni Google CPC',
    'direct': 'Sesiuni Directe',
    'yandex': 'Sesiuni Yandex',
    'other': 'Sesiuni Altele'
  };
  
  // Ordinea surselor pentru afișare
  const sourceOrder = ['total', 'google', 'direct', 'google_cpc', 'yandex', 'other'];
  
  sourceOrder.forEach(source => {
    if (datasets[source] && datasets[source].length > 0) {
      // Completăm cu 0 dacă lipsește vreun punct
      const values = [];
      for (let i = 0; i < labels.length; i++) {
        values.push(datasets[source][i] || 0);
      }
      
      // Configurare dataset cu efecte speciale
      const datasetConfig = {
        label: sourceNames[source],
        data: values,
        borderColor: colors[source].border,
        backgroundColor: colors[source].border + '25', // 25% opacity pentru fill
        borderWidth: source === 'total' ? 4 : 3,
        fill: true,
        tension: 0.5,
        pointRadius: 0, // Ascundem punctele normale pentru un look mai clean
        pointHoverRadius: 8,
        pointBackgroundColor: colors[source].border,
        pointBorderColor: '#000',
        pointBorderWidth: 3,
        pointHoverBorderWidth: 4,
        pointHoverBackgroundColor: colors[source].border,
        pointHoverBorderColor: '#fff',
        pointHoverShadowBlur: 10,
        pointHoverShadowColor: colors[source].glow
      };
      
      // Linie punctată pentru Google (distingere vizuală)
      if (source === 'google') {
        datasetConfig.borderDash = [8, 4];
      }
      
      chartDatasets.push(datasetConfig);
    }
  });
  
  const ctx = document.getElementById('statsChart').getContext('2d');
  
  // Distrugem graficul anterior dacă există
  if (statsChart) {
    statsChart.destroy();
  }
  
  // Creăm noul grafic de tendință
  statsChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: labels,
      datasets: chartDatasets
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: {
        mode: 'index',
        intersect: false,
        axis: 'x'
      },
      onHover: (event, activeElements) => {
        event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
      },
      plugins: {
        legend: {
          display: true,
          position: 'top',
          align: 'center',
          labels: {
            color: '#fff',
            font: {
              size: 13,
              weight: '600',
              family: "'Montserrat', sans-serif"
            },
            usePointStyle: true,
            pointStyle: 'circle',
            padding: 18,
            boxWidth: 12,
            boxHeight: 12,
            generateLabels: function(chart) {
              const original = Chart.defaults.plugins.legend.labels.generateLabels;
              const labels = original.call(this, chart);
              labels.forEach((label, index) => {
                const dataset = chart.data.datasets[label.datasetIndex];
                label.fillStyle = dataset.borderColor;
                label.strokeStyle = dataset.borderColor;
                label.lineWidth = 3;
              });
              return labels;
            }
          },
          onClick: function(e, legendItem) {
            const index = legendItem.datasetIndex;
            const chart = this.chart;
            const meta = chart.getDatasetMeta(index);
            meta.hidden = meta.hidden === null ? !chart.data.datasets[index].hidden : null;
            chart.update();
          }
        },
        tooltip: {
          enabled: true,
          backgroundColor: 'rgba(31, 41, 55, 0.95)',
          titleColor: '#FFEE00',
          titleFont: {
            size: 15,
            weight: 'bold',
            family: "'Montserrat', sans-serif"
          },
          bodyColor: '#fff',
          bodyFont: {
            size: 14,
            weight: '600',
            family: "'Montserrat', sans-serif"
          },
          borderColor: '#FFEE00',
          borderWidth: 2,
          padding: 16,
          cornerRadius: 10,
          displayColors: true,
          boxPadding: 8,
          usePointStyle: true,
          callbacks: {
            title: function(context) {
              return context[0].label;
            },
            label: function(context) {
              const label = context.dataset.label || '';
              const value = formatNumber(context.parsed.y);
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = total > 0 ? ((context.parsed.y / total) * 100).toFixed(1) : 0;
              return `${label}: ${value} sesiuni (${percentage}%)`;
            },
            labelColor: function(context) {
              return {
                borderColor: context.dataset.borderColor,
                backgroundColor: context.dataset.borderColor,
                borderWidth: 3,
                borderRadius: 2
              };
            }
          },
          animation: {
            duration: 200
          }
        }
      },
      animation: {
        duration: 2000,
        easing: 'easeInOutQuart',
        delay: function(context) {
          return context.dataIndex * 50; // Animație cascadă
        }
      },
      transitions: {
        show: {
          animation: {
            duration: 1000,
            easing: 'easeInOutQuart'
          }
        },
        hide: {
          animation: {
            duration: 500
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            color: '#fff',
            font: {
              size: 13,
              weight: '600',
              family: "'Montserrat', sans-serif"
            },
            padding: 12,
            callback: function(value) {
              return formatNumber(value);
            },
            backdropColor: 'rgba(31, 41, 55, 0.8)',
            backdropPadding: 4
          },
          grid: {
            color: 'rgba(255, 238, 0, 0.15)',
            drawBorder: true,
            borderColor: 'rgba(255, 238, 0, 0.3)',
            borderWidth: 1,
            lineWidth: 1,
            drawOnChartArea: true,
            drawTicks: true
          },
          title: {
            display: true,
            text: 'Număr de sesiuni',
            color: '#FFEE00',
            font: {
              size: 14,
              weight: '700',
              family: "'Montserrat', sans-serif"
            },
            padding: {
              top: 10,
              bottom: 10
            }
          }
        },
        x: {
          ticks: {
            color: '#fff',
            font: {
              size: 12,
              weight: '600',
              family: "'Montserrat', sans-serif"
            },
            padding: 10,
            maxRotation: window.innerWidth <= 768 ? 45 : 30,
            minRotation: window.innerWidth <= 768 ? 45 : 0,
            backdropColor: 'rgba(31, 41, 55, 0.8)',
            backdropPadding: 4
          },
          grid: {
            color: 'rgba(255, 238, 0, 0.08)',
            drawBorder: true,
            borderColor: 'rgba(255, 238, 0, 0.2)',
            borderWidth: 1,
            lineWidth: 1,
            drawOnChartArea: true,
            drawTicks: true
          },
          title: {
            display: true,
            text: 'Perioadă',
            color: '#FFEE00',
            font: {
              size: 14,
              weight: '700',
              family: "'Montserrat', sans-serif"
            },
            padding: {
              top: 10,
              bottom: 10
            }
          }
        }
      }
    }
  });
}

// Funcție pentru formatare număr
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO').format(val || 0);
}
</script>
@endpush

