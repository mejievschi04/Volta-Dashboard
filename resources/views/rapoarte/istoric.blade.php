@extends('layouts.app')

@section('title', 'Istoric – VOLTA')

@section('content')
<style>
.istoric-container {
  padding: 30px;
  max-width: 1600px;
  margin: 0 auto;
}

.istoric-header {
  background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%);
  border: 2px solid #FFEE00;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 4px 20px rgba(255, 238, 0, 0.1);
}

.istoric-header h1 {
  color: #FFEE00;
  margin: 0 0 10px 0;
  font-size: 28px;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.istoric-header p {
  color: #999;
  margin: 0;
  font-size: 14px;
}

.istoric-header .source-note {
  color: #10B981;
  font-size: 12px;
  margin-top: 8px;
}

.istoric-filters {
  display: flex;
  gap: 15px;
  margin-bottom: 25px;
  flex-wrap: wrap;
  align-items: center;
}

.filter-group {
  display: flex;
  align-items: center;
  gap: 10px;
}

.filter-group label {
  color: #FFEE00;
  font-weight: 600;
  font-size: 14px;
  white-space: nowrap;
}

.filter-group select,
.filter-group input {
  padding: 10px 14px;
  border-radius: 8px;
  background: #1F2937;
  color: #FFEE00;
  border: 2px solid #FFEE00;
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
}

.filter-group select:focus,
.filter-group input:focus {
  outline: none;
  box-shadow: 0 0 10px rgba(255, 238, 0, 0.12);
}

.istoric-stats {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.stat-card {
  background: #1F2937;
  border: 1px solid #9CA3AF;
  border-radius: 10px;
  padding: 20px;
  text-align: center;
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #FFEE00;
}

.stat-card h4 {
  color: #888;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 10px 0;
  font-weight: 600;
}

.stat-card .value {
  color: #FFEE00;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
}

.istoric-charts {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.istoric-chart-container {
  background: #1F2937;
  border: 1px solid #9CA3AF;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.istoric-chart-container h3 {
  color: #FFEE00;
  font-size: 18px;
  margin: 0 0 20px 0;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.istoric-table-container {
  background: #1F2937;
  border: 1px solid #9CA3AF;
  border-radius: 12px;
  padding: 25px;
  overflow-x: auto;
  width: 100%;
  box-sizing: border-box;
}

.istoric-table-container h3 {
  color: #FFEE00;
  font-size: 18px;
  margin: 0 0 20px 0;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.istoric-table {
  width: 100%;
  border-collapse: collapse;
  table-layout: fixed;
}

.istoric-table th {
  background: var(--bg-soft);
  color: #FFEE00;
  padding: 12px 4px;
  text-align: left;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 0.5px;
  border-bottom: 2px solid #FFEE00;
  position: sticky;
  top: 0;
  z-index: 10;
  word-wrap: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
}

.istoric-table td {
  padding: 10px 4px;
  color: #fff;
  border-bottom: 1px solid #9CA3AF;
  font-size: 13px;
  word-wrap: break-word;
  overflow: hidden;
  text-overflow: ellipsis;
  background-color: var(--bg-soft);
}

.istoric-table tr:hover {
  background: var(--bg-soft);
}

.istoric-table .positive {
  color: #0f0;
  font-weight: 600;
}

.istoric-table .negative {
  color: #f00;
  font-weight: 600;
}

.istoric-table .text-center {
  text-align: center;
}

.istoric-table .text-right {
  text-align: right;
}

.loading {
  text-align: center;
  padding: 40px;
  color: #FFEE00;
  font-size: 18px;
}

.no-data {
  text-align: center;
  padding: 40px;
  color: #999;
  font-size: 16px;
}

/* === Optimizări Mobile === */
@media (max-width: 768px) {
  .istoric-container {
    padding: 15px;
  }
  
  .istoric-header {
    padding: 15px;
    margin-bottom: 15px;
  }
  
  .istoric-header h1 {
    font-size: 20px !important;
    margin-bottom: 8px !important;
  }
  
  .istoric-header p {
    font-size: 12px !important;
  }
  
  .istoric-filters {
    flex-direction: column;
    gap: 10px;
    margin-bottom: 15px;
  }
  
  .filter-group {
    width: 100%;
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
  
  .filter-group label {
    font-size: 11px;
  }
  
  .filter-group select,
  .filter-group input {
    width: 100%;
    padding: 14px 16px;
    font-size: 16px;
    min-height: 48px;
  }
  
  .istoric-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-bottom: 15px;
  }
  
  .stat-card:last-child:nth-child(odd) {
    grid-column: span 2;
  }
  
  .stat-card {
    padding: 12px;
  }
  
  .stat-card h4 {
    font-size: 10px;
    margin-bottom: 6px;
  }
  
  .stat-card .value {
    font-size: 18px;
  }
  
  .istoric-charts {
    grid-template-columns: 1fr;
    gap: 12px;
    margin-bottom: 15px;
  }
  
  .istoric-chart-container {
    padding: 12px;
  }
  
  .istoric-chart-container h3 {
    font-size: 13px;
    margin-bottom: 10px;
  }
  
  .istoric-chart-container canvas {
    max-height: 250px;
  }
  
  .istoric-table-container {
    padding: 12px;
    overflow-x: auto;
  }
  
  .istoric-table-container h3 {
    font-size: 13px;
    margin-bottom: 10px;
  }
  
  .istoric-table {
    font-size: 11px;
    min-width: 800px;
  }
  
  .istoric-table th {
    padding: 8px 4px;
    font-size: 9px;
  }
  
  .istoric-table td {
    padding: 8px 4px;
    font-size: 10px;
  }
  
  .loading,
  .no-data {
    padding: 20px;
    font-size: 14px;
  }
}

@media (max-width: 480px) {
  .istoric-container {
    padding: 10px;
  }
  
  .istoric-header {
    padding: 12px;
    margin-bottom: 12px;
  }
  
  .istoric-header h1 {
    font-size: 18px !important;
    margin-bottom: 6px !important;
  }
  
  .istoric-header p {
    font-size: 11px !important;
  }
  
  .istoric-filters {
    gap: 8px;
    margin-bottom: 12px;
  }
  
  .filter-group label {
    font-size: 10px;
  }
  
  .filter-group select,
  .filter-group input {
    padding: 12px 14px;
    font-size: 16px;
  }
  
  .istoric-stats {
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 12px;
  }
  
  .stat-card {
    padding: 10px;
  }
  
  .stat-card h4 {
    font-size: 9px;
    margin-bottom: 4px;
  }
  
  .stat-card .value {
    font-size: 16px;
  }
  
  .istoric-charts {
    gap: 10px;
    margin-bottom: 12px;
  }
  
  .istoric-chart-container {
    padding: 10px;
  }
  
  .istoric-chart-container h3 {
    font-size: 12px;
    margin-bottom: 8px;
  }
  
  .istoric-chart-container canvas {
    max-height: 220px;
  }
  
  .istoric-table-container {
    padding: 10px;
  }
  
  .istoric-table-container h3 {
    font-size: 12px;
    margin-bottom: 8px;
  }
  
  .istoric-table {
    font-size: 10px;
    min-width: 700px;
  }
  
  .istoric-table th {
    padding: 6px 3px;
    font-size: 8px;
  }
  
  .istoric-table td {
    padding: 6px 3px;
    font-size: 9px;
  }
  
  .loading,
  .no-data {
    padding: 15px;
    font-size: 12px;
  }
}
</style>

<div class="istoric-container">
  <div class="istoric-header">
    <h1>Istoric Rapoarte</h1>
    <p>Vizualizează și analizează toate datele istorice pentru fiecare lună</p>
    <p class="source-note"><i class="fas fa-database"></i> Iconul de lângă lună = date vânzări/profit/comenzi din 1C</p>
  </div>

  <!-- Filtre -->
  <div class="istoric-filters">
    <div class="filter-group">
      <label for="filterAn">An:</label>
      <select id="filterAn">
        <option value="">Toți anii</option>
      </select>
    </div>
    <div class="filter-group">
      <label for="filterLuna">Lună:</label>
      <select id="filterLuna">
        <option value="">Toate lunile</option>
        <option value="1">Ianuarie</option>
        <option value="2">Februarie</option>
        <option value="3">Martie</option>
        <option value="4">Aprilie</option>
        <option value="5">Mai</option>
        <option value="6">Iunie</option>
        <option value="7">Iulie</option>
        <option value="8">August</option>
        <option value="9">Septembrie</option>
        <option value="10">Octombrie</option>
        <option value="11">Noiembrie</option>
        <option value="12">Decembrie</option>
      </select>
    </div>
    <div class="filter-group">
      <label for="searchInput">Caută:</label>
      <input type="text" id="searchInput" placeholder="Caută în istoric...">
    </div>
  </div>

  <!-- Statistici Agregat -->
  <div class="istoric-stats" id="statsContainer">
    <!-- Statisticile vor fi populate dinamic -->
  </div>

  <!-- Grafice -->
  <div class="istoric-charts">
    <div class="istoric-chart-container">
      <h3>Evoluție Vânzări</h3>
      <canvas id="vanzariChart"></canvas>
    </div>
    <div class="istoric-chart-container">
      <h3>Evoluție Comenzi</h3>
      <canvas id="comenziChart"></canvas>
    </div>
    <div class="istoric-chart-container">
      <h3>Evoluție Profit</h3>
      <canvas id="profitChart"></canvas>
    </div>
    <div class="istoric-chart-container">
      <h3>Evoluție Conversie</h3>
      <canvas id="conversieChart"></canvas>
    </div>
  </div>

  <!-- Tabel Istoric -->
  <div class="istoric-table-container">
    <h3>Tabel Istoric Complet</h3>
    <div id="tableLoading" class="loading">Se încarcă datele...</div>
    <table class="istoric-table" id="istoricTable" style="display: none;">
      <thead>
        <tr>
          <th style="width: 10%;">Lună</th>
          <th class="text-right" style="width: 8%;">Plan</th>
          <th class="text-right" style="width: 8%;">Vânzări</th>
          <th class="text-right" style="width: 8%;">Vânzări TVA</th>
          <th class="text-right" style="width: 7%;">Profit</th>
          <th class="text-center" style="width: 7%;">Progres %</th>
          <th class="text-right" style="width: 8%;">Dif. Plan</th>
          <th class="text-right" style="width: 6%;">Comenzi</th>
          <th class="text-right" style="width: 6%;">Comenzi/Zi</th>
          <th class="text-right" style="width: 6%;">CEC mediu</th>
          <th class="text-right" style="width: 6%;">Livrări</th>
          <th class="text-right" style="width: 6%;">Pickup</th>
          <th class="text-right" style="width: 7%;">Sesiuni</th>
          <th class="text-center" style="width: 6%;">Conversie</th>
          <th class="text-right" style="width: 9%;">vs Anterioară</th>
        </tr>
      </thead>
      <tbody id="istoricTableBody">
        <!-- Rândurile vor fi populate dinamic -->
      </tbody>
    </table>
    <div id="noData" class="no-data" style="display: none;">
      Nu există date pentru filtrele selectate.
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ---------------- UTILITARE ---------------- 
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(val || 0);
}

let allData = [];
let filteredData = [];
const charts = {};

// ---------------- LOAD DATA ---------------- 
async function loadIstoric() {
  try {
    const res = await fetch("{{ route('api.istoric') }}");
    const result = await res.json();
    
    if (!result.success) {
      throw new Error(result.error || "Eroare la încărcarea datelor");
    }
    
    allData = result.data;
    filteredData = [...allData];
    
    // Populează filtrele
    populateFilters();
    
    // Actualizează afișarea
    updateDisplay();
    
  } catch(err) {
    console.error("Eroare la încărcarea istoricului:", err);
    document.getElementById("tableLoading").textContent = "Eroare la încărcarea datelor: " + err.message;
  }
}

// ---------------- POPULATE FILTERS ---------------- 
function populateFilters() {
  const anSelect = document.getElementById("filterAn");
  const ani = [...new Set(allData.map(d => d.an))].sort((a, b) => b - a);
  
  anSelect.innerHTML = '<option value="">Toți anii</option>';
  ani.forEach(an => {
    const opt = document.createElement("option");
    opt.value = an;
    opt.textContent = an;
    anSelect.appendChild(opt);
  });
}

// ---------------- FILTER DATA ---------------- 
function filterData() {
  const an = document.getElementById("filterAn").value;
  const luna = document.getElementById("filterLuna").value;
  const search = document.getElementById("searchInput").value.toLowerCase();
  
  filteredData = allData.filter(item => {
    const matchAn = !an || item.an == an;
    const matchLuna = !luna || item.luna_num == luna;
    const matchSearch = !search || item.luna_label.toLowerCase().includes(search);
    
    return matchAn && matchLuna && matchSearch;
  });
  
  updateDisplay();
}

// ---------------- UPDATE DISPLAY ---------------- 
function updateDisplay() {
  updateStats();
  updateTable();
  updateCharts();
}

// ---------------- UPDATE STATS ---------------- 
function updateStats() {
  if (filteredData.length === 0) {
    document.getElementById("statsContainer").innerHTML = '';
    return;
  }
  
  const totalVanzari = filteredData.reduce((sum, d) => sum + (d.vanzari_luna || 0), 0);
  const totalProfit = filteredData.reduce((sum, d) => sum + (d.profit || 0), 0);
  const totalComenzi = filteredData.reduce((sum, d) => sum + (d.comenzi || 0), 0);
  const totalLivrari = filteredData.reduce((sum, d) => sum + (d.total_livrari_luna || 0), 0);
  const totalPickup = filteredData.reduce((sum, d) => sum + (d.pickup || 0), 0);
  const avgConversie = filteredData.length > 0 
    ? (filteredData.reduce((sum, d) => sum + (d.conversie || 0), 0) / filteredData.length).toFixed(2)
    : 0;
  const avgCecMediu = filteredData.length > 0 && totalComenzi > 0
    ? (totalVanzari / totalComenzi).toFixed(2)
    : (filteredData.length > 0 ? (filteredData.reduce((sum, d) => sum + (d.cec_mediu || 0), 0) / filteredData.length).toFixed(2) : 0);
  
  document.getElementById("statsContainer").innerHTML = `
    <div class="stat-card">
      <h4>Total Vânzări</h4>
      <p class="value">${formatNumber(totalVanzari)} MDL</p>
    </div>
    <div class="stat-card">
      <h4>Total Profit</h4>
      <p class="value">${formatNumber(totalProfit)} MDL</p>
    </div>
    <div class="stat-card">
      <h4>Total Comenzi</h4>
      <p class="value">${formatNumber(totalComenzi)}</p>
    </div>
    <div class="stat-card">
      <h4>CEC mediu</h4>
      <p class="value">${formatNumber(avgCecMediu)} MDL</p>
    </div>
    <div class="stat-card">
      <h4>Total livrări</h4>
      <p class="value">${formatNumber(totalLivrari)}</p>
    </div>
    <div class="stat-card">
      <h4>Pickup</h4>
      <p class="value">${formatNumber(totalPickup)}</p>
    </div>
    <div class="stat-card">
      <h4>Conversie Medie</h4>
      <p class="value">${avgConversie}%</p>
    </div>
    <div class="stat-card">
      <h4>Luni Analizate</h4>
      <p class="value">${filteredData.length}</p>
    </div>
  `;
}

// ---------------- UPDATE TABLE ---------------- 
function updateTable() {
  const tbody = document.getElementById("istoricTableBody");
  const table = document.getElementById("istoricTable");
  const loading = document.getElementById("tableLoading");
  const noData = document.getElementById("noData");
  
  if (filteredData.length === 0) {
    table.style.display = 'none';
    loading.style.display = 'none';
    noData.style.display = 'block';
    return;
  }
  
  loading.style.display = 'none';
  noData.style.display = 'none';
  table.style.display = 'table';
  
  tbody.innerHTML = '';
  
  filteredData.forEach(item => {
    const tr = document.createElement('tr');
    
    const vanzariVsAnterioara = item.vanzari_vs_anterioara || 0;
    const vanzariVsAnterioaraPercent = item.vanzari_vs_anterioara_percent || 0;
    const diffClass = vanzariVsAnterioara >= 0 ? 'positive' : 'negative';
    const sign = vanzariVsAnterioara >= 0 ? '+' : '';
    
    const sourceIcon = (item.kpi_source === 'onec_db') ? ' <i class="fas fa-database" style="color: #10B981; font-size: 11px;" title="1C"></i>' : '';
    tr.innerHTML = `
      <td><strong>${item.luna_label}</strong>${sourceIcon}</td>
      <td class="text-right">${formatNumber(item.plan_luna)}</td>
      <td class="text-right">${formatNumber(item.vanzari_luna)}</td>
      <td class="text-right">${formatNumber(item.vanzari_cu_tva)}</td>
      <td class="text-right">${formatNumber(item.profit)}</td>
      <td class="text-center">${formatNumber(item.progres_plan)}%</td>
      <td class="text-right ${item.diferenta_plan >= 0 ? 'positive' : 'negative'}">${formatNumber(item.diferenta_plan)}</td>
      <td class="text-right">${formatNumber(item.comenzi)}</td>
      <td class="text-right">${formatNumber(item.comenzi_zi)}</td>
      <td class="text-right">${formatNumber(item.cec_mediu)}</td>
      <td class="text-right">${formatNumber(item.total_livrari_luna)}</td>
      <td class="text-right">${formatNumber(item.pickup)}</td>
      <td class="text-right">${formatNumber(item.sesiuni)}</td>
      <td class="text-center">${formatNumber(item.conversie)}%</td>
      <td class="text-right ${diffClass}" style="font-size: 11px; line-height: 1.3;">${sign}${formatNumber(Math.abs(vanzariVsAnterioara))}<br><small style="font-size: 10px;">(${sign}${vanzariVsAnterioaraPercent}%)</small></td>
    `;
    tbody.appendChild(tr);
  });
}

// ---------------- UPDATE CHARTS ---------------- 
function updateCharts() {
  if (filteredData.length === 0) return;
  
  const labels = filteredData.map(d => d.luna_label).reverse();
  const vanzari = filteredData.map(d => d.vanzari_luna || 0).reverse();
  const comenzi = filteredData.map(d => d.comenzi || 0).reverse();
  const profit = filteredData.map(d => d.profit || 0).reverse();
  const conversie = filteredData.map(d => d.conversie || 0).reverse();
  
  // Vânzări Chart
  destroyChart("vanzariChart");
  const ctx1 = document.getElementById("vanzariChart").getContext("2d");
  charts["vanzariChart"] = {
    instance: new Chart(ctx1, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Vânzări (MDL)",
          data: vanzari,
          borderColor: "#FFEE00",
          backgroundColor: "rgba(255, 238, 0, 0.2)",
          fill: true,
          tension: 0.3,
          pointRadius: window.innerWidth <= 768 ? 2 : 4
        }]
      },
      options: {
        responsive: true,
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 10 : 12 }
            } 
          } 
        },
        scales: {
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" } 
          },
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" }, 
            beginAtZero: true 
          }
        }
      }
    })
  };
  
  // Comenzi Chart
  destroyChart("comenziChart");
  const ctx2 = document.getElementById("comenziChart").getContext("2d");
  charts["comenziChart"] = {
    instance: new Chart(ctx2, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [{
          label: "Comenzi",
          data: comenzi,
          backgroundColor: "rgba(0, 255, 0, 0.7)",
          borderColor: "#0f0",
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 10 : 12 }
            } 
          } 
        },
        scales: {
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" } 
          },
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" }, 
            beginAtZero: true 
          }
        }
      }
    })
  };
  
  // Profit Chart
  destroyChart("profitChart");
  const ctx3 = document.getElementById("profitChart").getContext("2d");
  charts["profitChart"] = {
    instance: new Chart(ctx3, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Profit (MDL)",
          data: profit,
          borderColor: "#ff0000",
          backgroundColor: "rgba(255, 0, 0, 0.2)",
          fill: true,
          tension: 0.3,
          pointRadius: window.innerWidth <= 768 ? 2 : 4
        }]
      },
      options: {
        responsive: true,
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 10 : 12 }
            } 
          } 
        },
        scales: {
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" } 
          },
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" }, 
            beginAtZero: true 
          }
        }
      }
    })
  };
  
  // Conversie Chart
  destroyChart("conversieChart");
  const ctx4 = document.getElementById("conversieChart").getContext("2d");
  charts["conversieChart"] = {
    instance: new Chart(ctx4, {
      type: "line",
      data: {
        labels: labels,
        datasets: [{
          label: "Conversie (%)",
          data: conversie,
          borderColor: "#00ffff",
          backgroundColor: "rgba(0, 255, 255, 0.2)",
          fill: true,
          tension: 0.3,
          pointRadius: window.innerWidth <= 768 ? 2 : 4
        }]
      },
      options: {
        responsive: true,
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 10 : 12 }
            } 
          } 
        },
        scales: {
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" } 
          },
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            }, 
            grid: { color: "rgba(255,255,0,0.05)" }, 
            beginAtZero: true 
          }
        }
      }
    })
  };
}

// ---------------- DESTROY CHART ---------------- 
function destroyChart(chartId) {
  if (charts[chartId] && charts[chartId].instance) {
    charts[chartId].instance.destroy();
    charts[chartId].instance = null;
  }
}


// ---------------- EVENT LISTENERS ---------------- 
document.getElementById("filterAn").addEventListener("change", filterData);
document.getElementById("filterLuna").addEventListener("change", filterData);
document.getElementById("searchInput").addEventListener("input", filterData);

// ---------------- DOCUMENT READY ---------------- 
document.addEventListener("DOMContentLoaded", () => {
  loadIstoric();
});
</script>
@endpush
