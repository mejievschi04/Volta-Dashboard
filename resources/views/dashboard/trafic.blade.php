@extends('layouts.app')

@section('title', 'Dashboard Trafic – VOLTA')
@section('header-title', 'Trafic')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.trafic-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 24px;
}

.trafic-header h1 {
  margin: 0;
  font-size: 28px;
  font-weight: 800;
  color: #FFEE00;
  letter-spacing: -0.5px;
  flex-shrink: 0;
}

.trafic-controls {
  display: flex;
  align-items: center;
  gap: 20px;
  flex-wrap: wrap;
}

.trafic-filters {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
}

.trafic-actions {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-wrap: wrap;
  padding-left: 20px;
  border-left: 1px solid rgba(255, 255, 255, 0.12);
}

.year-selector-wrapper,
.month-selector-wrapper {
  display: flex;
  align-items: center;
  gap: 8px;
  background: rgba(31, 41, 55, 0.4);
  padding: 10px 14px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.25s ease;
}

.trafic-filters label {
  color: rgba(255, 255, 255, 0.9);
  font-weight: 600;
  font-size: 13px;
  white-space: nowrap;
  margin: 0;
}

.trafic-filters label i {
  margin-right: 6px;
  opacity: 0.9;
}

.year-selector-wrapper:hover,
.month-selector-wrapper:hover {
  border-color: rgba(255, 238, 0, 0.2);
  background: rgba(31, 41, 55, 0.5);
}

.year-select,
.month-select {
  height: 40px;
  padding: 0 36px 0 12px;
  font-size: 14px;
  border-radius: 8px;
  border: 1px solid rgba(255, 255, 255, 0.15);
  background-color: rgba(31, 41, 55, 0.6);
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.25s ease;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%23FFEE00' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 10px;
}

.year-select {
  min-width: 100px;
}

.month-select {
  min-width: 160px;
}

.year-select:hover,
.month-select:hover {
  border-color: rgba(255, 238, 0, 0.25);
  background-color: rgba(31, 41, 55, 0.8);
}

.year-select:focus,
.month-select:focus {
  border-color: rgb(71, 85, 105);
  box-shadow: 0 0 0 2px rgba(148, 163, 184, 0.22);
}

.year-select option,
.month-select option {
  background: #1F2937;
  color: #fff;
  padding: 10px;
}

.stats-btn-link {
  text-decoration: none;
  display: inline-flex;
}

.stat-btn-main {
  height: 40px;
  padding: 0 18px;
  background: rgba(31, 41, 55, 0.6);
  color: #FFEE00;
  border: 1px solid rgba(255, 238, 0, 0.25);
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.25s ease;
  white-space: nowrap;
  box-sizing: border-box;
}

.stat-btn-main:hover {
  background: rgba(255, 238, 0, 0.12);
  border-color: rgba(255, 238, 0, 0.4);
}

.stat-btn-main:active {
  transform: scale(0.98);
}

.sync-btn {
  height: 40px;
  padding: 0 20px;
  background: #FFEE00;
  color: #000;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 13px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.25s ease;
  box-sizing: border-box;
  white-space: nowrap;
}

.sync-btn:hover {
  background: #fff333;
  box-shadow: 0 4px 14px rgba(255, 238, 0, 0.35);
}

.sync-btn:active {
  transform: scale(0.98);
}

.sync-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.sync-btn.loading #syncIcon {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

@media (max-width: 900px) {
  .trafic-actions {
    border-left: none;
    padding-left: 0;
    padding-top: 12px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    width: 100%;
  }
}

@media (max-width: 768px) {
  .trafic-header {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .trafic-header h1 {
    font-size: 24px;
  }

  .trafic-controls {
    flex-direction: column;
    align-items: stretch;
    gap: 16px;
  }

  .trafic-filters {
    flex-direction: column;
    align-items: stretch;
  }

  .year-selector-wrapper,
  .month-selector-wrapper {
    width: 100%;
  }

  .year-select,
  .month-select {
    flex: 1;
    min-width: 0;
  }

  .trafic-actions {
    flex-direction: column;
    padding-top: 12px;
    gap: 10px;
  }

  .stats-btn-link,
  .sync-btn {
    width: 100%;
  }

  .stat-btn-main {
    width: 100%;
  }
}
</style>
@endpush

@section('content')
<div class="trafic-page-shell">
  <p class="trafic-page-lead">Monitorizează sursele de trafic și sincronizează datele GA4 în același format vizual cu restul dashboard-ului.</p>

  <div class="trafic-toolbar">
    <form method="get" action="{{ route('trafic') }}" id="traficFilterForm" class="trafic-filters">
      <div class="month-selector-modern">
        <div class="month-selector-wrapper">
          <i class="fas fa-calendar" aria-hidden="true"></i>
          <label for="selectAnTrafic">An</label>
          <select id="selectAnTrafic" name="year" onchange="updateMonthOptions(); this.form.submit();" class="dashboard-month-select year-select">
          @php
            $currentYear = request('year', date('Y'));
            $currentMonth = request('month', date('m'));
            $selectedMonth = request('month', date('Y-m'));
            $selectedYear = request('year', date('Y'));
            
            // Generăm ani (ultimii 5 ani + anul curent)
            for ($year = date('Y'); $year >= date('Y') - 4; $year--) {
              $selected = $year == $selectedYear ? 'selected' : '';
              echo "<option value=\"{$year}\" {$selected}>{$year}</option>";
            }
          @endphp
          </select>
        </div>
      </div>
      
      <div class="month-selector-modern">
        <div class="month-selector-wrapper">
          <i class="fas fa-calendar-alt" aria-hidden="true"></i>
          <label for="selectLunaTrafic">Luna</label>
          <select id="selectLunaTrafic" name="month" onchange="this.form.submit()" class="dashboard-month-select month-select">
          @php
            $months = [
              '01' => 'Ianuarie', '02' => 'Februarie', '03' => 'Martie', '04' => 'Aprilie',
              '05' => 'Mai', '06' => 'Iunie', '07' => 'Iulie', '08' => 'August',
              '09' => 'Septembrie', '10' => 'Octombrie', '11' => 'Noiembrie', '12' => 'Decembrie'
            ];
            
            $selectedMonthNum = request('month', date('m'));
            $selectedYear = request('year', date('Y'));
            
            foreach ($months as $num => $name) {
              $value = $selectedYear . '-' . $num;
              $selected = ($selectedYear . '-' . $num) === $selectedMonth ? 'selected' : '';
              echo "<option value=\"{$value}\" {$selected}>{$name}</option>";
            }
          @endphp
          </select>
        </div>
      </div>
    </form>

    <div class="trafic-actions-modern">
      <a href="{{ route('trafic.stats') }}" class="trafic-action-btn" title="Vezi statistici generale">
        <i class="fas fa-chart-pie" aria-hidden="true"></i><span>Statistici</span>
      </a>
      <a href="{{ route('trafic.analiza') }}" class="trafic-action-btn" title="Analiză detaliată">
        <i class="fas fa-chart-bar" aria-hidden="true"></i><span>Analiză</span>
      </a>
      <button type="button" id="syncButton" onclick="syncGoogleAnalytics()" class="trafic-action-btn trafic-action-btn--primary" title="Sincronizează datele din Google Analytics">
        <i class="fas fa-sync-alt" id="syncIcon"></i>
        <span id="syncText">Sincronizează GA4</span>
      </button>
    </div>
  </div>
</div>

<!-- Status sincronizare -->
<div id="syncBanner" style="display: none; margin-bottom: 20px;"></div>

<!-- Grafic principal -->
<div class="card mb-4 trafic-card" style="min-height: 500px;">
  <h5 class="card-title trafic-section-title"><i class="fas fa-chart-line" aria-hidden="true"></i>Overview - toate sursele</h5>
  <div style="position: relative; height: 450px;">
    <canvas id="trafficChart" style="max-height: 450px; width: 100% !important;"></canvas>
  </div>
</div>

<!-- Carduri -->
<div class="row">
  @php
    $sources = [
      "total" => "Total",
      "google" => "Căutare Google",
      "google_cpc" => "Google CPC (plătit)",
      "direct" => "Direct",
      "yandex" => "Yandex Referral",
      "other" => "Altele"
    ];
    $month = request('month', date('Y-m'));
  @endphp
  @foreach ($sources as $key => $label)
    <div class="card trafic-source-card trafic-source-{{ $key }}" onclick="openDetails('{{ $key }}')">
      <h5>{{ $label }}</h5>
      <p id="total-{{ $key }}">-</p>
      <small>Total vizite în luna {{ $month }}</small>
    </div>
  @endforeach
</div>
<!-- MODAL -->
<div class="modal" id="detailsModal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeModal()">&times;</span>
    <h5 id="modalTitle">Detalii trafic</h5>
    <table>
      <thead>
        <tr><th>Zi</th><th>Vizite</th></tr>
      </thead>
      <tbody id="detailsTable"></tbody>
    </table>
  </div>
</div>

<!-- MODAL SINCRONIZARE -->
<div class="modal" id="syncModal">
  <div class="modal-content">
    <span class="modal-close" onclick="closeSyncModal()">&times;</span>
    <h5 id="syncModalTitle">Sincronizare Google Analytics</h5>
    <div id="syncStatusModal"></div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Variabile globale
let trafficData = {};
let currentMonth = '{{ request('month', date('Y-m')) }}';

// Funcție pentru actualizarea opțiunilor lunii când se schimbă anul
function updateMonthOptions() {
  const yearSelect = document.getElementById('selectAnTrafic');
  const monthSelect = document.getElementById('selectLunaTrafic');
  const selectedYear = yearSelect.value;
  const currentMonthValue = monthSelect.value;
  const currentMonthNum = currentMonthValue.split('-')[1];
  
  // Actualizăm valorile opțiunilor de lună cu noul an
  Array.from(monthSelect.options).forEach(option => {
    const monthNum = option.value.split('-')[1];
    option.value = selectedYear + '-' + monthNum;
    
    // Păstrăm selecția lunii curente dacă este posibil
    if (monthNum === currentMonthNum) {
      option.selected = true;
    }
  });
  
}

// Funcție pentru formatare număr
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO').format(val || 0);
}

// Încărcare date la deschiderea paginii
document.addEventListener('DOMContentLoaded', function() {
  loadTrafficData();
});

// Funcție pentru încărcarea datelor
async function loadTrafficData() {
  try {
    const response = await fetch(`{{ route('api.trafic') }}?month=${currentMonth}`);
    const result = await response.json();
    
    if (result.success) {
      trafficData = result.data;
      
      // Actualizăm totalurile în carduri
      Object.keys(result.totals || {}).forEach(source => {
        const element = document.getElementById(`total-${source}`);
        if (element) {
          element.textContent = formatNumber(result.totals[source] || 0);
        }
      });
      
      // Desenăm graficul
      drawChart();
    } else {
      console.error('Eroare la încărcarea datelor:', result.error);
    }
  } catch (error) {
    console.error('Eroare la încărcarea datelor:', error);
  }
}

// Funcție pentru desenarea graficului
function drawChart() {
  if (typeof Chart === 'undefined') {
    console.error('Chart.js nu este încărcat!');
    return;
  }

  const ctx = document.getElementById('trafficChart');
  if (!ctx) {
    console.error('Elementul trafficChart nu a fost găsit!');
    return;
  }

  const chartCtx = ctx.getContext('2d');
  const palette = (typeof VoltaChartTheme !== 'undefined' && VoltaChartTheme.getSeriesPalette)
    ? VoltaChartTheme.getSeriesPalette()
    : {
        amber: { line: "rgb(250, 204, 21)", area: "rgba(250, 204, 21, 0.18)" },
        emerald: { line: "rgb(16, 185, 129)", area: "rgba(16, 185, 129, 0.18)" },
        violet: { line: "rgb(167, 139, 250)", area: "rgba(167, 139, 250, 0.18)" },
        cyan: { line: "rgb(180, 188, 204)", area: "rgba(180, 188, 204, 0.18)" },
        rose: { line: "rgb(244, 63, 94)", area: "rgba(244, 63, 94, 0.18)" },
        slate: { line: "rgb(148, 163, 184)", area: "rgba(148, 163, 184, 0.16)" }
      };
  const colors = {
    total: palette.amber,
    google: palette.emerald,
    google_cpc: palette.violet,
    direct: palette.cyan,
    yandex: palette.rose,
    other: palette.slate
  };

  const sourceLabels = {
    total: "Total",
    google: "Google Organic",
    google_cpc: "Google CPC",
    direct: "Direct",
    yandex: "Yandex",
    other: "Altele"
  };

  // Verificăm dacă datele sunt în format nou (cu labels și datasets)
  let labels, datasets;
  
  if (trafficData && trafficData.labels && trafficData.datasets) {
    // Format nou
    labels = trafficData.labels;
    datasets = Object.keys(trafficData.datasets).map(source => ({
      label: sourceLabels[source] || source,
      data: trafficData.datasets[source] || [],
      borderColor: (colors[source] && colors[source].line) || "rgb(148, 163, 184)",
      backgroundColor: (colors[source] && colors[source].area) || "rgba(148, 163, 184, 0.16)",
      borderWidth: source === 'total' ? 3.2 : 2.4,
      tension: 0.36,
      fill: source === 'total',
      pointRadius: source === 'total' ? 2.8 : 2,
      pointHoverRadius: source === 'total' ? 5.5 : 4.2,
    }));
  } else {
    // Format vechi (compatibilitate înapoi)
    const days = [...new Set(Object.values(trafficData).flat().map(d => d.day))].sort((a, b) => a - b);
    labels = days;
    datasets = Object.keys(trafficData).map(source => ({
      label: sourceLabels[source] || source,
      data: days.map(day => {
        const found = trafficData[source].find(d => d.day == day);
        return found ? found.visits : 0;
      }),
      borderColor: (colors[source] && colors[source].line) || "rgb(148, 163, 184)",
      backgroundColor: (colors[source] && colors[source].area) || "rgba(148, 163, 184, 0.16)",
      borderWidth: source === 'total' ? 3.2 : 2.4,
      tension: 0.36,
      fill: source === 'total',
      pointRadius: source === 'total' ? 2.8 : 2,
      pointHoverRadius: source === 'total' ? 5.5 : 4.2,
    }));
  }

  // Distrugem graficul existent dacă există
  if (window.trafficChartInstance) {
    window.trafficChartInstance.destroy();
  }

  const isMobile = window.innerWidth <= 768;
  const themeBrand = (getComputedStyle(document.documentElement).getPropertyValue('--brand') || '').trim() || '#FFEE00';
  
  var chartOptions = (function () {
    if (typeof VoltaChartTheme !== 'undefined') {
      return VoltaChartTheme.cartesianDefaults({
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: VoltaChartTheme.colors.textSecondary,
              font: { family: VoltaChartTheme.font, size: isMobile ? 10 : 12, weight: '500' },
            }
          },
          tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
            titleColor: VoltaChartTheme.colors.brand,
            bodyColor: VoltaChartTheme.colors.textPrimary,
          }),
        },
        scales: {
          x: {
            ticks: Object.assign({}, VoltaChartTheme.ticks(9, 12)),
            grid: VoltaChartTheme.gridLines(),
          },
          y: {
            ticks: Object.assign({}, VoltaChartTheme.ticks(9, 12)),
            grid: VoltaChartTheme.gridLines(),
            beginAtZero: true,
          }
        }
      });
    }
    return {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: { color: '#cbd5e1' }
        },
        tooltip: {
          backgroundColor: 'rgba(30,41,59,0.96)',
          titleColor: themeBrand,
          bodyColor: '#f8fafc',
          borderColor: '#334155',
          borderWidth: 1
        }
      },
      scales: {
        y: { beginAtZero: true, ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)' } },
        x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)' } }
      }
    };
  })();

  window.trafficChartInstance = new Chart(chartCtx, {
    type: 'line',
    data: { labels: labels, datasets: datasets.map(ds => ({
      ...ds,
      pointRadius: isMobile ? Math.max(1, (ds.pointRadius || 2) - 0.5) : (ds.pointRadius || 2),
      pointBorderWidth: isMobile ? 0 : 1,
      pointHoverRadius: isMobile ? 4 : (ds.pointHoverRadius || 5)
    })) },
    options: chartOptions
  });
}

// Modal JS
function openDetails(source) {
  const names = {
    total: "Total",
    google: "Căutare Google",
    google_cpc: "Google CPC",
    direct: "Direct",
    yandex: "Yandex Referral",
    other: "Altele"
  };
  document.getElementById("modalTitle").innerText = "Detalii trafic - " + names[source];
  const tbody = document.getElementById("detailsTable");
  tbody.innerHTML = "";
  
  // Verificăm dacă datele sunt în format nou sau vechi
  if (trafficData && trafficData.labels && trafficData.datasets) {
    // Format nou
    const labels = trafficData.labels || [];
    const values = trafficData.datasets[source] || [];
    labels.forEach((label, index) => {
      tbody.innerHTML += `<tr><td>${label}</td><td>${formatNumber(values[index] || 0)}</td></tr>`;
    });
  } else {
    // Format vechi (compatibilitate înapoi)
    (trafficData[source] || []).forEach(d => {
      tbody.innerHTML += `<tr><td>${d.day}</td><td>${formatNumber(d.visits || 0)}</td></tr>`;
    });
  }
  
  document.getElementById("detailsModal").style.display = "flex";
}

function closeModal() {
  document.getElementById("detailsModal").style.display = "none";
}

// Închidere modal la click în afară
window.onclick = function(e) {
  if (e.target == document.getElementById("detailsModal")) {
    closeModal();
  }
  if (e.target == document.getElementById("syncModal")) {
    closeSyncModal();
  }
}

// Funcție sincronizare Google Analytics
async function syncGoogleAnalytics() {
  const syncButton = document.getElementById('syncButton');
  const syncIcon = document.getElementById('syncIcon');
  const syncText = document.getElementById('syncText');
  const syncModal = document.getElementById('syncModal');
  const syncStatus = document.getElementById('syncStatusModal');
  
  // Obținem luna selectată
  const urlParams = new URLSearchParams(window.location.search);
  const selectedMonth = urlParams.get('month') || currentMonth;
  
  // Calculăm prima și ultima zi a lunii selectate
  const [year, month] = selectedMonth.split('-');
  const startDate = `${year}-${month}-01`;
  const lastDay = new Date(parseInt(year), parseInt(month), 0).getDate();
  const endDate = `${year}-${month}-${String(lastDay).padStart(2, '0')}`;
  
  // Dezactivăm butonul
  syncButton.disabled = true;
  syncButton.classList.add('loading');
  syncText.textContent = 'Sincronizare...';
  
  // Deschidem modalul
  syncModal.style.display = 'flex';
  syncStatus.className = 'info';
  syncStatus.innerHTML = `<p>⏳ Se conectează la Google Analytics pentru luna ${selectedMonth}...</p>`;
  
  try {
    // Apelăm endpoint-ul de sincronizare
    const response = await fetch(`{{ route('api.ga.sync') }}?start_date=${startDate}&end_date=${endDate}`, {
      method: 'POST',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    });
    
    const text = await response.text();
    const contentType = response.headers.get('content-type');
    const isJson = contentType && contentType.includes('application/json');
    let result = null;
    if (isJson && text) {
      try { result = JSON.parse(text); } catch (_) {}
    }

    if (!response.ok) {
      const msg = (result && (result.error || result.message)) ? (result.error || result.message) : text.substring(0, 300);
      throw new Error(msg);
    }
    
    if (!result) {
      throw new Error('Răspuns invalid de la server.');
    }
    
    if (result.success) {
      syncStatus.className = 'success';
      syncStatus.innerHTML = `
        <h6>✅ Sincronizare reușită!</h6>
        <p><strong>Perioadă:</strong> ${result.start_date} - ${result.end_date}</p>
        <p><strong>Zile procesate:</strong> ${result.dates_processed}</p>
        <p><strong>Înregistrări șterse:</strong> ${result.records_deleted || 0}</p>
        <p><strong>Înregistrări inserate:</strong> ${result.records_inserted}</p>
        <p><small style="color: #666;">Datele existente au fost înlocuite cu cele noi.</small></p>
        ${result.errors && result.errors.length > 0 ? 
          '<p style="color: #856404;"><strong>Avertismente:</strong><br>' + result.errors.join('<br>') + '</p>' : ''}
        <p style="margin-top: 10px;"><em>Actualizare pagina pentru a vedea datele noi...</em></p>
      `;
      
      // Reload pagina după 2 secunde
      setTimeout(() => {
        window.location.reload();
      }, 2000);
    } else {
      throw new Error(result.error || 'Eroare necunoscută');
    }
  } catch (error) {
    syncStatus.className = 'error';
    syncStatus.innerHTML = `
      <h6>❌ Eroare la sincronizare!</h6>
      <p><strong>Eroare:</strong> ${error.message}</p>
      <p style="margin-top: 10px; font-size: 12px; color: #666;">
        Verifică că:<br>
        • Fișierul service-account-credentials.json există<br>
        • Property ID este configurat în config/google-analytics.php<br>
        • Service Account are acces la Google Analytics<br>
        • Google Analytics Data API este activat
      </p>
    `;
    
    console.error('Eroare sincronizare:', error);
  } finally {
    // Reactivăm butonul
    syncButton.disabled = false;
    syncButton.classList.remove('loading');
    syncText.textContent = 'Sincronizează GA4';
  }
}

function closeSyncModal() {
  document.getElementById('syncModal').style.display = 'none';
}
</script>
@endpush
