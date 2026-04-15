@extends('layouts.app')

@section('title', 'Rapoarte – VOLTA')

@section('header-title', 'Rapoarte')

@section('content')
<div class="rapoarte-page">
  <p class="rapoarte-lead">
    Alege două luni pentru a compara KPI-uri, grafice și diferențe — același limbaj vizual ca în restul dashboardului.
  </p>

  <div class="rapoarte-periods-grid">
    <div class="month-selector-modern">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-day" aria-hidden="true"></i>
        <label for="selectLuna1">Perioada 1</label>
        <select id="selectLuna1" class="dashboard-month-select"></select>
      </div>
    </div>
    <div class="month-selector-modern">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <label for="selectLuna2">Perioada 2</label>
        <select id="selectLuna2" class="dashboard-month-select"></select>
      </div>
    </div>
  </div>

  <div class="kpi-grid comparare-kpi-grid" id="kpiGrid"></div>

  <div class="rapoarte-charts-grid">
    <div class="chart-container">
      <h3><i class="fas fa-coins" aria-hidden="true"></i> Vânzări fără TVA</h3>
      <div class="chart-wrapper">
        <canvas id="vanzariCompareChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-shopping-cart" aria-hidden="true"></i> Comenzi</h3>
      <div class="chart-wrapper">
        <canvas id="comenziCompareChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-chart-line" aria-hidden="true"></i> Profit</h3>
      <div class="chart-wrapper">
        <canvas id="profitCompareChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-percentage" aria-hidden="true"></i> Conversie</h3>
      <div class="chart-wrapper">
        <canvas id="conversieCompareChart"></canvas>
      </div>
    </div>
  </div>

  <section class="comparare-table-section" aria-labelledby="comparare-table-heading">
    <h3 id="comparare-table-heading" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <span><i class="fas fa-table" aria-hidden="true"></i> Detalii comparare</span>
      <span style="display:inline-flex;gap:8px;align-items:center;">
        <button type="button" id="comparareExportExcelBtn" class="btn secondary">
          <i class="fas fa-file-excel" aria-hidden="true"></i> Excel
        </button>
        <button type="button" id="comparareExportPdfBtn" class="btn secondary">
          <i class="fas fa-file-pdf" aria-hidden="true"></i> PDF
        </button>
      </span>
    </h3>
    <div class="comparare-table-wrap">
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
        <tbody id="comparareTableBody"></tbody>
      </table>
    </div>
  </section>
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
  const cls = diff >= 0 ? 'comparare-diff-badge--up' : 'comparare-diff-badge--down';
  return `<span class="comparare-diff-badge ${cls}">${sign}${formatNumber(Math.abs(diff))} (${sign}${diffPercent}%)</span>`;
}

function compareCartesianOptions() {
  if (typeof VoltaChartTheme !== 'undefined') {
    return VoltaChartTheme.cartesianDefaults({
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
          titleColor: VoltaChartTheme.colors.brand,
          bodyColor: VoltaChartTheme.colors.textPrimary,
        }),
      },
    });
  }
  return {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index', intersect: false },
    plugins: {
      legend: { display: true, labels: { color: '#e2e8f0', font: { size: 12 } } },
      tooltip: { backgroundColor: 'rgba(30,41,59,0.96)', titleColor: '#FFEE00', bodyColor: '#f8fafc', borderColor: '#334155', borderWidth: 1, padding: 12, cornerRadius: 10 },
    },
    scales: {
      x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false } },
      y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false }, beginAtZero: true },
    },
  };
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
    { key: 'cec_mediu', label: 'CEC mediu', suffix: 'MDL' },
    { key: 'total_livrari_luna', label: 'Total livrări lună', suffix: '' },
    { key: 'pickup', label: 'Pickup', suffix: '' },
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
    card.className = 'card comparare-kpi-card';
    const suf = kpi.suffix ? ' ' + kpi.suffix : '';
    card.innerHTML = `
      <h4>${kpi.label}</h4>
      <div class="value">${formatNumber(val1)}${suf}</div>
      <div class="comparare-vs">vs ${formatNumber(val2)}${suf}</div>
      ${formatDiff(diff, diffPercent)}
    `;
    grid.appendChild(card);
  });
}

// ---------------- UPDATE CHARTS ---------------- 
function updateCharts(data1, data2, label1, label2) {
  const barOpts = compareCartesianOptions();
  const labels = [label1, label2];

  destroyChart("vanzariCompareChart");
  const ctx1 = document.getElementById("vanzariCompareChart").getContext("2d");
  charts["vanzariCompareChart"] = {
    instance: new Chart(ctx1, {
      type: "bar",
      data: {
        labels,
        datasets: [{
          label: "Vânzări fără TVA",
          data: [data1.vanzari_luna || 0, data2.vanzari_luna || 0],
          backgroundColor: ["rgba(255, 238, 0, 0.52)", "rgba(255, 238, 0, 0.28)"],
          hoverBackgroundColor: ["rgba(255, 238, 0, 0.72)", "rgba(255, 238, 0, 0.45)"],
          borderColor: ["rgba(255, 238, 0, 0.35)", "rgba(255, 238, 0, 0.22)"],
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false,
        }],
      },
      options: barOpts,
    }),
  };

  destroyChart("comenziCompareChart");
  const ctx2 = document.getElementById("comenziCompareChart").getContext("2d");
  charts["comenziCompareChart"] = {
    instance: new Chart(ctx2, {
      type: "bar",
      data: {
        labels,
        datasets: [{
          label: "Comenzi",
          data: [data1.comenzi || 0, data2.comenzi || 0],
          backgroundColor: ["rgba(96, 165, 250, 0.5)", "rgba(96, 165, 250, 0.28)"],
          hoverBackgroundColor: ["rgba(96, 165, 250, 0.68)", "rgba(96, 165, 250, 0.42)"],
          borderColor: ["rgba(59, 130, 246, 0.45)", "rgba(59, 130, 246, 0.28)"],
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false,
        }],
      },
      options: barOpts,
    }),
  };

  destroyChart("profitCompareChart");
  const ctx3 = document.getElementById("profitCompareChart").getContext("2d");
  charts["profitCompareChart"] = {
    instance: new Chart(ctx3, {
      type: "bar",
      data: {
        labels,
        datasets: [{
          label: "Profit",
          data: [data1.profit || 0, data2.profit || 0],
          backgroundColor: ["rgba(248, 113, 113, 0.48)", "rgba(248, 113, 113, 0.26)"],
          hoverBackgroundColor: ["rgba(248, 113, 113, 0.68)", "rgba(248, 113, 113, 0.4)"],
          borderColor: ["rgba(239, 68, 68, 0.4)", "rgba(239, 68, 68, 0.25)"],
          borderWidth: 1,
          borderRadius: 8,
          borderSkipped: false,
        }],
      },
      options: barOpts,
    }),
  };

  destroyChart("conversieCompareChart");
  const ctx4 = document.getElementById("conversieCompareChart").getContext("2d");
  charts["conversieCompareChart"] = {
    instance: new Chart(ctx4, {
      type: "line",
      data: {
        labels,
        datasets: [{
          label: "Conversie (%)",
          data: [data1.conversie || 0, data2.conversie || 0],
          borderColor: "rgb(248, 113, 113)",
          backgroundColor: "rgba(248, 113, 113, 0.12)",
          borderWidth: 2.5,
          fill: true,
          tension: 0.35,
          pointRadius: window.innerWidth <= 768 ? 3 : 5,
          pointHoverRadius: window.innerWidth <= 768 ? 5 : 7,
          pointBackgroundColor: "rgb(248, 113, 113)",
          pointBorderColor: "rgb(15, 23, 42)",
          pointBorderWidth: 1,
        }],
      },
      options: barOpts,
    }),
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
    { key: 'cec_mediu', label: 'CEC mediu', suffix: 'MDL' },
    { key: 'total_livrari_luna', label: 'Total livrări lună', suffix: '' },
    { key: 'pickup', label: 'Pickup', suffix: '' },
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
  const excelBtn = document.getElementById('comparareExportExcelBtn');
  const pdfBtn = document.getElementById('comparareExportPdfBtn');
  if (excelBtn) {
    excelBtn.addEventListener('click', function () {
      const table = document.getElementById('comparareTable');
      if (!table) {
        alert('Nu există date pentru export.');
        return;
      }
      Promise.resolve(window.VoltaExcelExport.exportTable(table, {
        fileName: 'raport_comparare_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Comparare'
      })).catch(function (error) {
        alert('Nu am putut exporta Excel: ' + error.message);
      });
    });
  }
  if (pdfBtn) {
    pdfBtn.addEventListener('click', function () {
      const luna1 = document.getElementById("selectLuna1").value;
      const luna2 = document.getElementById("selectLuna2").value;
      if (!luna1 || !luna2) {
        alert('Selectează ambele perioade.');
        return;
      }
      const params = new URLSearchParams({ luna1: luna1, luna2: luna2 });
      window.location.href = @json(route('export.comparare.pdf')) + '?' + params.toString();
    });
  }
  loadLuni();
});
</script>
@endpush
