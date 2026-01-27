@extends('layouts.app')

@section('title', 'Comparare Rapoarte – VOLTA')

@section('content')
<style>
.comparare-container {
  padding: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.comparare-header {
  background: linear-gradient(135deg, #2B2B2B 0%, #2B2B2B 100%);
  border: 2px solid #ffee00;
  border-radius: 12px;
  padding: 25px;
  margin-bottom: 30px;
  box-shadow: 0 4px 20px rgba(255, 238, 0, 0.1);
}

.comparare-controls {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
  margin-bottom: 20px;
}

.control-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.control-group label {
  color: #ffee00;
  font-weight: 600;
  font-size: 14px;
  text-transform: uppercase;
  letter-spacing: 1px;
}

.control-group select {
  padding: 12px 16px;
  border-radius: 8px;
  background: #2B2B2B;
  color: #ffee00;
  border: 2px solid #ffee00;
  font-size: 16px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.control-group select:hover {
  background: #111;
  box-shadow: 0 0 10px rgba(255, 238, 0, 0.3);
}

.control-group select:focus {
  outline: none;
  box-shadow: 0 0 15px rgba(255, 238, 0, 0.5);
}

.comparare-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.comparare-kpi-card {
  background: #2B2B2B;
  border: 1px solid #333;
  border-radius: 10px;
  padding: 20px;
  position: relative;
  overflow: hidden;
}

.comparare-kpi-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  width: 4px;
  height: 100%;
  background: #ffee00;
}

.comparare-kpi-card h4 {
  color: #888;
  font-size: 13px;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin: 0 0 12px 0;
  font-weight: 600;
}

.comparare-kpi-card .value-current {
  color: #ffee00;
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 8px;
}

.comparare-kpi-card .value-compare {
  color: #999;
  font-size: 18px;
  font-weight: 500;
  margin-bottom: 10px;
}

.comparare-kpi-card .value-diff {
  font-size: 14px;
  font-weight: 600;
  padding: 6px 12px;
  border-radius: 6px;
  display: inline-block;
}

.value-diff.positive {
  background: rgba(0, 255, 0, 0.1);
  color: #0f0;
  border: 1px solid #0f0;
}

.value-diff.negative {
  background: rgba(255, 0, 0, 0.1);
  color: #f00;
  border: 1px solid #f00;
}

.comparare-charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  gap: 25px;
  margin-bottom: 30px;
}

.comparare-chart-container {
  background: #2B2B2B;
  border: 1px solid #333;
  border-radius: 12px;
  padding: 25px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.comparare-chart-container h3 {
  color: #ffee00;
  font-size: 18px;
  margin: 0 0 20px 0;
  text-align: center;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.comparare-chart-container canvas {
  max-height: 350px;
}

.comparare-table-container {
  background: #2B2B2B;
  border: 1px solid #333;
  border-radius: 12px;
  padding: 25px;
  overflow-x: auto;
}

.comparare-table-container h3 {
  color: #ffee00;
  font-size: 18px;
  margin: 0 0 20px 0;
  text-transform: uppercase;
  letter-spacing: 1px;
  font-weight: 600;
}

.comparare-table {
  width: 100%;
  border-collapse: collapse;
}

.comparare-table th {
  background: var(--bg-soft);
  color: #ffee00;
  padding: 15px;
  text-align: left;
  font-weight: 600;
  text-transform: uppercase;
  font-size: 12px;
  letter-spacing: 1px;
  border-bottom: 2px solid #ffee00;
}

.comparare-table td {
  padding: 12px 15px;
  color: #fff;
  border-bottom: 1px solid #333;
  background-color: var(--bg-soft);
}

.comparare-table tr:hover {
  background: var(--bg-soft);
}

.comparare-table .diff-positive {
  color: #0f0;
  font-weight: 600;
  background-color: var(--bg-soft);
}

.comparare-table .diff-negative {
  color: #f00;
  font-weight: 600;
  background-color: var(--bg-soft);
}
</style>

<div class="comparare-container">
  <div class="comparare-header">
    <h1 style="color: #ffee00; margin: 0 0 10px 0; font-size: 28px; text-transform: uppercase; letter-spacing: 2px;">
      Comparare Rapoarte
    </h1>
    <p style="color: #999; margin: 0; font-size: 14px;">
      Compară performanța între două perioade pentru a analiza evoluția indicatorilor cheie
    </p>
  </div>

  <div class="comparare-controls">
    <div class="control-group">
      <label for="selectLuna1">Perioada 1 (Curentă)</label>
      <select id="selectLuna1"></select>
    </div>
    <div class="control-group">
      <label for="selectLuna2">Perioada 2 (Comparare)</label>
      <select id="selectLuna2"></select>
    </div>
  </div>

  <!-- KPI Cards cu Comparare -->
  <div class="comparare-kpi-grid" id="kpiGrid">
    <!-- KPI cards vor fi populate dinamic -->
  </div>

  <!-- Grafice Comparare -->
  <div class="comparare-charts-grid">
    <div class="comparare-chart-container">
      <h3>Vânzări Comparare</h3>
      <canvas id="vanzariCompareChart"></canvas>
    </div>
    <div class="comparare-chart-container">
      <h3>Comenzi Comparare</h3>
      <canvas id="comenziCompareChart"></canvas>
    </div>
    <div class="comparare-chart-container">
      <h3>Profit Comparare</h3>
      <canvas id="profitCompareChart"></canvas>
    </div>
    <div class="comparare-chart-container">
      <h3>Conversie Comparare</h3>
      <canvas id="conversieCompareChart"></canvas>
    </div>
  </div>

  <!-- Tabel Comparare -->
  <div class="comparare-table-container">
    <h3>Tabel Comparare Detaliat</h3>
    <table class="comparare-table" id="comparareTable">
      <thead>
        <tr>
          <th>Indicator</th>
          <th id="period1Header">Perioada 1</th>
          <th id="period2Header">Perioada 2</th>
          <th>Diferență</th>
          <th>Diferență %</th>
        </tr>
      </thead>
      <tbody id="comparareTableBody">
        <!-- Rândurile vor fi populate dinamic -->
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ---------------- UTILITARE ---------------- 
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO').format(val || 0);
}

function calculateDiff(current, compare) {
  const diff = current - compare;
  const diffPercent = compare > 0 ? ((diff / compare) * 100).toFixed(2) : 0;
  return { diff, diffPercent };
}

function formatDiff(diff, diffPercent) {
  const sign = diff >= 0 ? '+' : '';
  const color = diff >= 0 ? 'positive' : 'negative';
  return `<span class="value-diff ${color}">${sign}${formatNumber(Math.abs(diff))} (${sign}${diffPercent}%)</span>`;
}

// ---------------- CHARTS OBJECT ---------------- 
const charts = {};

// ---------------- DESTROY CHART ---------------- 
function destroyChart(chartId) {
  if (charts[chartId] && charts[chartId].instance) {
    charts[chartId].instance.destroy();
    charts[chartId].instance = null;
  }
}

// ---------------- LOAD LUNI ---------------- 
async function loadLuni() {
  try {
    const res = await fetch("{{ route('api.vanzari.lunare') }}");
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    const selectLuna1 = document.getElementById("selectLuna1");
    const selectLuna2 = document.getElementById("selectLuna2");
    
    selectLuna1.innerHTML = '';
    selectLuna2.innerHTML = '';
    
    data.luni.forEach(luna => {
      const opt1 = document.createElement("option");
      opt1.value = luna.value;
      opt1.textContent = luna.label;
      selectLuna1.appendChild(opt1);
      
      const opt2 = document.createElement("option");
      opt2.value = luna.value;
      opt2.textContent = luna.label;
      selectLuna2.appendChild(opt2);
    });
    
    // Setează ultimele 2 luni ca default
    if (data.luni.length >= 2) {
      selectLuna1.value = data.luni[data.luni.length - 1].value;
      selectLuna2.value = data.luni[data.luni.length - 2].value;
      await updateComparare();
    } else if (data.luni.length === 1) {
      selectLuna1.value = data.luni[0].value;
      selectLuna2.value = data.luni[0].value;
      await updateComparare();
    }
    
    selectLuna1.addEventListener("change", updateComparare);
    selectLuna2.addEventListener("change", updateComparare);
    
  } catch(err) {
    console.error("Eroare la încărcarea lunilor:", err);
  }
}

// ---------------- UPDATE COMPARARE ---------------- 
async function updateComparare() {
  const luna1 = document.getElementById("selectLuna1").value;
  const luna2 = document.getElementById("selectLuna2").value;
  
  if (!luna1 || !luna2) return;
  
  try {
    const [res1, res2] = await Promise.all([
      fetch(`{{ route('api.kpi') }}?month=${luna1}`),
      fetch(`{{ route('api.kpi') }}?month=${luna2}`)
    ]);
    
    const [kpiData1, kpiData2] = await Promise.all([
      res1.json(),
      res2.json()
    ]);
    
    if (!kpiData1.success || !kpiData2.success) {
      throw new Error("Eroare la încărcarea datelor KPI");
    }
    
    // Update headers
    const month1Name = new Date(luna1 + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
    const month2Name = new Date(luna2 + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
    document.getElementById("period1Header").textContent = month1Name;
    document.getElementById("period2Header").textContent = month2Name;
    
    // Update KPI Cards
    updateKPICards(kpiData1, kpiData2);
    
    // Update Charts
    updateCharts(kpiData1, kpiData2, month1Name, month2Name);
    
    // Update Table
    updateTable(kpiData1, kpiData2);
    
  } catch(err) {
    console.error("Eroare la actualizarea comparației:", err);
  }
}

// ---------------- UPDATE KPI CARDS ---------------- 
function updateKPICards(data1, data2) {
  const kpis = [
    { key: 'plan_luna', label: 'Plan', suffix: 'MDL' },
    { key: 'vanzari_luna', label: 'Vânzări', suffix: 'MDL' },
    { key: 'progres_plan', label: 'Progres Plan', suffix: '%' },
    { key: 'comenzi', label: 'Comenzi', suffix: '' },
    { key: 'comenzi_zi', label: 'Comenzi/Zi', suffix: '' },
    { key: 'sesiuni', label: 'Sesiuni', suffix: '' },
    { key: 'conversie', label: 'Conversie', suffix: '%' },
    { key: 'profit', label: 'Profit', suffix: 'MDL' },
    { key: 'vanzari_cu_tva', label: 'Vânzări cu TVA', suffix: 'MDL' }
  ];
  
  const grid = document.getElementById("kpiGrid");
  grid.innerHTML = '';
  
  kpis.forEach(kpi => {
    const val1 = data1[kpi.key] || 0;
    const val2 = data2[kpi.key] || 0;
    const { diff, diffPercent } = calculateDiff(val1, val2);
    
    const card = document.createElement('div');
    card.className = 'comparare-kpi-card';
    card.innerHTML = `
      <h4>${kpi.label}</h4>
      <div class="value-current">${formatNumber(val1)} ${kpi.suffix}</div>
      <div class="value-compare">vs ${formatNumber(val2)} ${kpi.suffix}</div>
      ${formatDiff(diff, diffPercent)}
    `;
    grid.appendChild(card);
  });
}

// ---------------- UPDATE CHARTS ---------------- 
function updateCharts(data1, data2, label1, label2) {
  // Vânzări Chart
  destroyChart("vanzariCompareChart");
  const ctx1 = document.getElementById("vanzariCompareChart").getContext("2d");
  charts["vanzariCompareChart"] = {
    instance: new Chart(ctx1, {
      type: "bar",
      data: {
        labels: [label1, label2],
        datasets: [{
          label: "Vânzări fără TVA",
          data: [data1.vanzari_luna || 0, data2.vanzari_luna || 0],
          backgroundColor: ["rgba(255, 238, 0, 0.7)", "rgba(255, 238, 0, 0.4)"],
          borderColor: "#ffee00",
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#fff" } }
        },
        scales: {
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } },
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true }
        }
      }
    })
  };
  
  // Comenzi Chart
  destroyChart("comenziCompareChart");
  const ctx2 = document.getElementById("comenziCompareChart").getContext("2d");
  charts["comenziCompareChart"] = {
    instance: new Chart(ctx2, {
      type: "bar",
      data: {
        labels: [label1, label2],
        datasets: [{
          label: "Comenzi",
          data: [data1.comenzi || 0, data2.comenzi || 0],
          backgroundColor: ["rgba(0, 255, 0, 0.7)", "rgba(0, 255, 0, 0.4)"],
          borderColor: "#0f0",
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#fff" } }
        },
        scales: {
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } },
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true }
        }
      }
    })
  };
  
  // Profit Chart
  destroyChart("profitCompareChart");
  const ctx3 = document.getElementById("profitCompareChart").getContext("2d");
  charts["profitCompareChart"] = {
    instance: new Chart(ctx3, {
      type: "bar",
      data: {
        labels: [label1, label2],
        datasets: [{
          label: "Profit",
          data: [data1.profit || 0, data2.profit || 0],
          backgroundColor: ["rgba(255, 0, 0, 0.7)", "rgba(255, 0, 0, 0.4)"],
          borderColor: "#f00",
          borderWidth: 2
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#fff" } }
        },
        scales: {
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } },
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true }
        }
      }
    })
  };
  
  // Conversie Chart
  destroyChart("conversieCompareChart");
  const ctx4 = document.getElementById("conversieCompareChart").getContext("2d");
  charts["conversieCompareChart"] = {
    instance: new Chart(ctx4, {
      type: "line",
      data: {
        labels: [label1, label2],
        datasets: [{
          label: "Conversie (%)",
          data: [data1.conversie || 0, data2.conversie || 0],
          borderColor: "#ffee00",
          backgroundColor: "rgba(255, 238, 0, 0.2)",
          borderWidth: 3,
          fill: true,
          tension: 0.3,
          pointRadius: 6,
          pointBackgroundColor: "#ffee00"
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: "#fff" } }
        },
        scales: {
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } },
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true }
        }
      }
    })
  };
}

// ---------------- UPDATE TABLE ---------------- 
function updateTable(data1, data2) {
  const tbody = document.getElementById("comparareTableBody");
  tbody.innerHTML = '';
  
  const rows = [
    { key: 'plan_luna', label: 'Plan', suffix: 'MDL' },
    { key: 'vanzari_luna', label: 'Vânzări fără TVA', suffix: 'MDL' },
    { key: 'vanzari_cu_tva', label: 'Vânzări cu TVA', suffix: 'MDL' },
    { key: 'profit', label: 'Profit', suffix: 'MDL' },
    { key: 'progres_plan', label: 'Progres Plan', suffix: '%' },
    { key: 'prognoza_plan', label: 'Prognoză Plan', suffix: 'MDL' },
    { key: 'prognoza_plan_procent', label: 'Prognoză Plan %', suffix: '%' },
    { key: 'comenzi', label: 'Comenzi', suffix: '' },
    { key: 'comenzi_zi', label: 'Comenzi/Zi', suffix: '' },
    { key: 'sesiuni', label: 'Sesiuni', suffix: '' },
    { key: 'conversie', label: 'Conversie', suffix: '%' }
  ];
  
  rows.forEach(row => {
    const val1 = data1[row.key] || 0;
    const val2 = data2[row.key] || 0;
    const { diff, diffPercent } = calculateDiff(val1, val2);
    const diffClass = diff >= 0 ? 'diff-positive' : 'diff-negative';
    const sign = diff >= 0 ? '+' : '';
    
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><strong>${row.label}</strong></td>
      <td>${formatNumber(val1)} ${row.suffix}</td>
      <td>${formatNumber(val2)} ${row.suffix}</td>
      <td class="${diffClass}">${sign}${formatNumber(Math.abs(diff))} ${row.suffix}</td>
      <td class="${diffClass}">${sign}${diffPercent}%</td>
    `;
    tbody.appendChild(tr);
  });
}

// ---------------- DOCUMENT READY ---------------- 
document.addEventListener("DOMContentLoaded", () => {
  loadLuni();
});
</script>
@endpush

