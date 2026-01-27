@extends('layouts.app')

@section('title', 'Dashboard Trafic – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.trafic-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.trafic-controls {
  display: flex;
  align-items: center;
  gap: 15px;
  flex-wrap: wrap;
}

.year-selector-wrapper,
.month-selector-wrapper {
  display: flex;
  align-items: center;
  gap: 10px;
  background: rgba(0, 0, 0, 0.3);
  padding: 8px 15px;
  border-radius: 10px;
  border: 1px solid rgba(255, 238, 0, 0.1);
  transition: all 0.3s ease;
}

.year-selector-wrapper:hover,
.month-selector-wrapper:hover {
  border-color: rgba(255, 238, 0, 0.15);
  background: rgba(0, 0, 0, 0.4);
  box-shadow: 0 0 15px rgba(255, 238, 0, 0.1);
}

.year-select {
  padding: 10px 15px;
  font-size: 14px;
  border-radius: 8px;
  border: 2px solid rgba(255, 238, 0, 0.12);
  background-color: var(--bg-soft);
  color: #FFEE00;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 120px;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23FFEE00' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px;
  padding-right: 35px;
}

.year-select:hover {
  border-color: rgba(255, 238, 0, 0.2);
  box-shadow: 0 0 15px rgba(255, 238, 0, 0.12);
  background-color: var(--bg-soft);
}

.year-select:focus {
  border-color: #FFEE00;
  box-shadow: 0 0 20px rgba(255, 238, 0, 0.15);
  background-color: var(--bg-soft);
}

.year-select option {
  background: #1a1a1a;
  color: #FFEE00;
  padding: 10px;
}

.month-select {
  padding: 10px 15px;
  font-size: 14px;
  border-radius: 8px;
  border: 2px solid rgba(255, 238, 0, 0.12);
  background-color: var(--bg-soft);
  color: #FFEE00;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 200px;
  outline: none;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23FFEE00' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px;
  padding-right: 35px;
}

.month-select:hover {
  border-color: rgba(255, 238, 0, 0.2);
  box-shadow: 0 0 15px rgba(255, 238, 0, 0.12);
  background-color: var(--bg-soft);
}

.month-select:focus {
  border-color: #FFEE00;
  box-shadow: 0 0 20px rgba(255, 238, 0, 0.15);
  background-color: var(--bg-soft);
}

.month-select option {
  background: #1a1a1a;
  color: #FFEE00;
  padding: 10px;
}

.stats-btn-link {
  text-decoration: none;
  display: inline-block;
}

.stat-btn-main {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.08) 0%, rgba(255, 238, 0, 0.05) 100%);
  color: #FFEE00;
  border: 2px solid rgba(255, 238, 0, 0.15);
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  white-space: nowrap;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.stat-btn-main:hover {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.1) 0%, rgba(255, 238, 0, 0.08) 100%);
  border-color: #FFEE00;
  box-shadow: 0 0 20px rgba(255, 238, 0, 0.15);
  transform: translateY(-2px);
}

.stat-btn-main:active {
  transform: translateY(0);
}

.sync-btn {
  background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%);
  color: #000;
  border: 2px solid #FFEE00;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 4px 12px rgba(255, 238, 0, 0.12);
  position: relative;
  overflow: hidden;
  min-width: 180px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.sync-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(0, 0, 0, 0.1), transparent);
  transition: left 0.5s;
}

.sync-btn:hover::before {
  left: 100%;
}

.sync-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(255, 238, 0, 0.15);
  background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%);
  border-color: #FFEE00;
}

.sync-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 8px rgba(255, 238, 0, 0.12);
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

@media (max-width: 768px) {
  .trafic-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .trafic-controls {
    width: 100%;
    flex-direction: column;
    align-items: stretch;
  }
  
  .month-selector-wrapper {
    width: 100%;
  }
  
  .month-select {
    width: 100%;
  }
  
  .sync-btn {
    width: 100%;
  }
}
</style>
@endpush

@section('content')
<div class="trafic-header">
  <h1 style="margin: 0; font-size: 32px; font-weight: 800; color: #FFEE00; text-shadow: 0 0 20px rgba(255, 238, 0, 0.15); letter-spacing: -0.5px;">Trafic</h1>
  
  <div class="trafic-controls">
    <form method="get" action="{{ route('trafic') }}" id="traficFilterForm" style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
      <div class="year-selector-wrapper">
        <label for="selectAnTrafic" style="color: #fff; font-weight: 600; margin-right: 10px; white-space: nowrap;">
          <i class="fas fa-calendar" style="margin-right: 5px;"></i>An:
        </label>
        <select id="selectAnTrafic" name="year" onchange="updateMonthOptions(); this.form.submit();" class="year-select">
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
      
      <div class="month-selector-wrapper">
        <label for="selectLunaTrafic" style="color: #fff; font-weight: 600; margin-right: 10px; white-space: nowrap;">
          <i class="fas fa-calendar-alt" style="margin-right: 5px;"></i>Luna:
        </label>
        <select id="selectLunaTrafic" name="month" onchange="this.form.submit()" class="month-select">
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
    </form>
    
    <a href="{{ route('trafic.stats') }}" class="stats-btn-link">
      <button class="stat-btn-main" title="Vezi statistici generale">
        <i class="fas fa-chart-pie"></i>
        <span>Statistici Generale</span>
      </button>
    </a>
    
    <a href="{{ route('trafic.analiza') }}" class="stats-btn-link">
      <button class="stat-btn-main" title="Analiză detaliată - Utilizatori, Dispozitive, Geografie, Conținut, E-commerce, Campanii">
        <i class="fas fa-chart-bar"></i>
        <span>Analiză Detaliată</span>
      </button>
    </a>
    
    <button id="syncButton" onclick="syncGoogleAnalytics()" class="sync-btn" title="Sincronizează datele din Google Analytics">
      <i class="fas fa-sync-alt" id="syncIcon"></i>
      <span id="syncText">Sincronizează GA</span>
    </button>
  </div>
</div>

<!-- Status sincronizare -->
<div id="syncStatus" style="display: none; margin-bottom: 20px;"></div>

<!-- Grafic principal -->
<div class="card mb-4" style="min-height: 500px;">
  <h5 class="card-title">Overview - toate sursele</h5>
  <canvas id="trafficChart" style="max-height: 450px; width: 100% !important;"></canvas>
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
    <div class="card" onclick="openDetails('{{ $key }}')">
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
    <div id="syncStatus"></div>
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
          element.textContent = result.totals[source] || 0;
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
  const colors = {
    total: "#F90716",
    google: "#548CFF",
    google_cpc: "#06FF00",
    direct: "#FFEE00",
    yandex: "#F0F3FF",
    other: "#888888"
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
      borderColor: colors[source] || "#888888",
      tension: 0.3,
      fill: false
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
      borderColor: colors[source] || "#888888",
      tension: 0.3,
      fill: false
    }));
  }

  // Distrugem graficul existent dacă există
  if (window.trafficChartInstance) {
    window.trafficChartInstance.destroy();
  }

  window.trafficChartInstance = new Chart(chartCtx, {
    type: 'line',
    data: { labels: labels, datasets },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'bottom',
          labels: {
            font: {
              size: 14
            },
            padding: 15
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            font: {
              size: 12
            }
          },
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          }
        },
        x: {
          ticks: {
            font: {
              size: 12
            }
          },
          grid: {
            color: 'rgba(255, 255, 255, 0.1)'
          }
        }
      }
    }
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
  const syncStatus = document.getElementById('syncStatus');
  
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
    
    if (!response.ok) {
      const text = await response.text();
      throw new Error(`HTTP ${response.status}: ${text.substring(0, 200)}`);
    }
    
    const contentType = response.headers.get('content-type');
    if (!contentType || !contentType.includes('application/json')) {
      const text = await response.text();
      throw new Error(`Răspuns invalid (nu este JSON): ${text.substring(0, 200)}`);
    }
    
    const result = await response.json();
    
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
