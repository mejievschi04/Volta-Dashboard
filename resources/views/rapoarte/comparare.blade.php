@extends('layouts.app')

@section('title', 'Rapoarte – VOLTA')

@section('header-title', 'Rapoarte')

@section('content')
<div class="rapoarte-page">
  <p class="rapoarte-lead">
    Compară două luni direct. Pentru intervale, activează opțiunea Interval dintr-un singur click.
  </p>

  <button type="button" class="rapoarte-range-button" id="toggleRangesBtn" aria-pressed="false">
    <i class="fas fa-calendar-week" aria-hidden="true"></i>
    Interval
  </button>

  <div class="rapoarte-periods-grid">
    <div class="rapoarte-period-card">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-day" aria-hidden="true"></i>
        <label for="selectLuna1">Luna 1</label>
        <select id="selectLuna1" class="dashboard-month-select"></select>
      </div>

      <div class="month-selector-wrapper rapoarte-range-end" id="range1EndControl" hidden>
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <label for="selectLuna1End">Luna 1 până la</label>
        <select id="selectLuna1End" class="dashboard-month-select"></select>
      </div>
    </div>

    <div class="rapoarte-period-card">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-day" aria-hidden="true"></i>
        <label for="selectLuna2">Luna 2</label>
        <select id="selectLuna2" class="dashboard-month-select"></select>
      </div>

      <div class="month-selector-wrapper rapoarte-range-end" id="range2EndControl" hidden>
        <i class="fas fa-calendar-check" aria-hidden="true"></i>
        <label for="selectLuna2End">Luna 2 până la</label>
        <select id="selectLuna2End" class="dashboard-month-select"></select>
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
            <th id="period1Header">Luna 1</th>
            <th id="period2Header">Luna 2</th>
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
      tooltip: { backgroundColor: 'rgba(30,41,59,0.96)', titleColor: ((getComputedStyle(document.documentElement).getPropertyValue('--brand') || '').trim() || '#FFEE00'), bodyColor: '#f8fafc', borderColor: '#334155', borderWidth: 1, padding: 12, cornerRadius: 10 },
    },
    scales: {
      x: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false } },
      y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false }, beginAtZero: true },
    },
  };
}

// ---------------- CHARTS OBJECT ---------------- 
const charts = {};
let monthsOrder = [];
let istoricRows = [];

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
    const [luniRes, istoricRes] = await Promise.all([
      fetch("{{ route('api.vanzari.lunare') }}"),
      fetch("{{ route('api.istoric') }}")
    ]);

    const luniPayload = await luniRes.json();
    const istoricPayload = await istoricRes.json();

    if (!luniPayload.success) {
      throw new Error(luniPayload.error || "Eroare la încărcarea datelor");
    }
    if (!istoricPayload.success) {
      throw new Error(istoricPayload.error || "Eroare la încărcarea istoricului");
    }

    istoricRows = Array.isArray(istoricPayload.data) ? istoricPayload.data : [];
    monthsOrder = Array.isArray(luniPayload.luni)
      ? luniPayload.luni.map(function (l) { return l.value; }).sort()
      : [];

    const selects = [
      document.getElementById("selectLuna1"),
      document.getElementById("selectLuna1End"),
      document.getElementById("selectLuna2"),
      document.getElementById("selectLuna2End")
    ].filter(Boolean);

    selects.forEach(function (select) { select.innerHTML = ''; });
    (luniPayload.luni || []).forEach(function (luna) {
      selects.forEach(function (select) {
        const opt = document.createElement("option");
        opt.value = luna.value;
        opt.textContent = luna.label;
        select.appendChild(opt);
      });
    });

    if (monthsOrder.length >= 2) {
      const latest = monthsOrder[monthsOrder.length - 1];
      const previous = monthsOrder[monthsOrder.length - 2];
      document.getElementById("selectLuna1").value = latest;
      document.getElementById("selectLuna1End").value = latest;
      document.getElementById("selectLuna2").value = previous;
      document.getElementById("selectLuna2End").value = previous;
    } else if (monthsOrder.length === 1) {
      document.getElementById("selectLuna1").value = monthsOrder[0];
      document.getElementById("selectLuna1End").value = monthsOrder[0];
      document.getElementById("selectLuna2").value = monthsOrder[0];
      document.getElementById("selectLuna2End").value = monthsOrder[0];
    }

    selects.forEach(function (select) {
      select.addEventListener("change", updateComparare);
    });

    await updateComparare();
  } catch(err) {
    console.error("Eroare la încărcarea lunilor:", err);
  }
}

function parseRangeInputs(startId, endId) {
  const start = document.getElementById(startId).value;
  const end = document.getElementById(endId).value;
  if (!start || !end) return null;
  if (start <= end) return { start: start, end: end };
  return { start: end, end: start };
}

function parseOptionalRange(monthId, endId, toggleId) {
  const month = document.getElementById(monthId).value;
  if (!month) return null;

  if (document.getElementById(toggleId).getAttribute('aria-pressed') === 'true') {
    return parseRangeInputs(monthId, endId);
  }

  return { start: month, end: month };
}

function getComparisonRanges() {
  return {
    range1: parseOptionalRange("selectLuna1", "selectLuna1End", "toggleRangesBtn"),
    range2: parseOptionalRange("selectLuna2", "selectLuna2End", "toggleRangesBtn")
  };
}

function syncOptionalRanges() {
  const toggle = document.getElementById('toggleRangesBtn');
  const useRanges = toggle && toggle.getAttribute('aria-pressed') === 'true';
  ['range1EndControl', 'range2EndControl'].forEach(function (controlId) {
    const control = document.getElementById(controlId);
    if (control) control.hidden = !useRanges;
  });
  if (toggle) toggle.classList.toggle('is-active', useRanges);
}

function monthLabelFromYm(ym) {
  if (!ym || !/^\d{4}-\d{2}$/.test(ym)) return ym || '';
  const [year, month] = ym.split('-');
  const date = new Date(Number(year), Number(month) - 1, 1);
  return date.toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
}

function rangeLabel(range) {
  if (!range) return 'Interval invalid';
  if (range.start === range.end) return monthLabelFromYm(range.start);
  return monthLabelFromYm(range.start) + ' - ' + monthLabelFromYm(range.end);
}

function monthsInRange(startYm, endYm) {
  if (!startYm || !endYm) return [];
  return monthsOrder.filter(function (ym) {
    return ym >= startYm && ym <= endYm;
  });
}

function aggregateRangeKpi(range) {
  const months = monthsInRange(range.start, range.end);
  const selectedRows = istoricRows.filter(function (row) {
    return months.includes(row.luna);
  });

  const sum = function (key) {
    return selectedRows.reduce(function (acc, row) { return acc + (Number(row[key]) || 0); }, 0);
  };

  const plan = sum('plan_luna');
  const vanzari = sum('vanzari_luna');
  const vanzariCuTva = sum('vanzari_cu_tva');
  const profit = sum('profit');
  const comenzi = sum('comenzi');
  const sesiuni = sum('sesiuni');
  const totalLivrari = sum('total_livrari_luna');
  const pickup = sum('pickup');
  const prognozaPlan = sum('prognoza_plan');
  const zileInterval = Math.max(1, months.reduce(function (acc, ym) {
    const date = new Date(ym + '-01T00:00:00');
    const year = date.getFullYear();
    const month = date.getMonth();
    return acc + new Date(year, month + 1, 0).getDate();
  }, 0));
  const cecMediu = comenzi > 0 ? (vanzari / comenzi) : 0;
  const progresPlan = plan > 0 ? (vanzari / plan) * 100 : 0;
  const diferentaPlan = vanzari - plan;
  const prognozaPlanProcent = plan > 0 ? (prognozaPlan / plan) * 100 : 0;
  const conversie = sesiuni > 0 ? (comenzi / sesiuni) * 100 : 0;
  const comenziZi = comenzi / zileInterval;

  return {
    plan_luna: plan,
    vanzari_luna: vanzari,
    vanzari_cu_tva: vanzariCuTva,
    profit: profit,
    progres_plan: Number(progresPlan.toFixed(2)),
    diferenta_plan: diferentaPlan,
    prognoza_plan: prognozaPlan,
    prognoza_plan_procent: Number(prognozaPlanProcent.toFixed(2)),
    comenzi: comenzi,
    comenzi_zi: Number(comenziZi.toFixed(1)),
    cec_mediu: Number(cecMediu.toFixed(2)),
    total_livrari_luna: totalLivrari,
    pickup: pickup,
    sesiuni: sesiuni,
    conversie: Number(conversie.toFixed(2))
  };
}

// ---------------- UPDATE COMPARARE ---------------- 
async function updateComparare() {
  const { range1, range2 } = getComparisonRanges();
  if (!range1 || !range2) return;

  try {
    const kpiData1 = aggregateRangeKpi(range1);
    const kpiData2 = aggregateRangeKpi(range2);

    const label1 = rangeLabel(range1);
    const label2 = rangeLabel(range2);
    document.getElementById("period1Header").textContent = label1;
    document.getElementById("period2Header").textContent = label2;
    
    // Update KPI Cards
    updateKPICards(kpiData1, kpiData2);

    // Update Charts
    updateCharts(kpiData1, kpiData2, label1, label2);
    
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
  const themeStyles = getComputedStyle(document.documentElement);
  const brandRgb = (themeStyles.getPropertyValue('--brand-rgb') || '').trim() || '255, 238, 0';

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
          backgroundColor: [`rgba(${brandRgb}, 0.52)`, `rgba(${brandRgb}, 0.28)`],
          hoverBackgroundColor: [`rgba(${brandRgb}, 0.72)`, `rgba(${brandRgb}, 0.45)`],
          borderColor: [`rgba(${brandRgb}, 0.35)`, `rgba(${brandRgb}, 0.22)`],
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
          backgroundColor: [`rgba(${brandRgb}, 0.5)`, `rgba(${brandRgb}, 0.28)`],
          hoverBackgroundColor: [`rgba(${brandRgb}, 0.68)`, `rgba(${brandRgb}, 0.42)`],
          borderColor: [`rgba(${brandRgb}, 0.45)`, `rgba(${brandRgb}, 0.28)`],
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
  const rangeToggleBtn = document.getElementById('toggleRangesBtn');
  if (rangeToggleBtn) {
    rangeToggleBtn.addEventListener('click', function () {
      const nextValue = rangeToggleBtn.getAttribute('aria-pressed') !== 'true';
      rangeToggleBtn.setAttribute('aria-pressed', nextValue ? 'true' : 'false');
      syncOptionalRanges();
      updateComparare();
    });
    syncOptionalRanges();
  }

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
      const { range1, range2 } = getComparisonRanges();
      if (!range1 || !range2) {
        alert('Selectează ambele luni.');
        return;
      }
      const params = new URLSearchParams({
        luna1: range1.start,
        luna2: range2.start,
        luna1_start: range1.start,
        luna1_end: range1.end,
        luna2_start: range2.start,
        luna2_end: range2.end
      });
      window.location.href = @json(route('export.comparare.pdf')) + '?' + params.toString();
    });
  }
  loadLuni();
});
</script>
@endpush
