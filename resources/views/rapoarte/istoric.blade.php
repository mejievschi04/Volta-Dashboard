@extends('layouts.app')

@section('title', 'Istoric – VOLTA')

@section('header-title', 'Istoric')

@section('content')
<div class="istoric-page">
  <p class="rapoarte-lead">
    Evoluție pe luni: filtrează după an sau lună, caută în etichete, apoi explorează KPI-urile agregate, graficele și tabelul detaliat.
  </p>

  <p class="kpi-source-badge istoric-source-hint">
    <i class="fas fa-database" aria-hidden="true"></i>
    Iconița verde lângă lună marchează o lună cu date complete.
  </p>

  <div class="istoric-filters-grid">
    <div class="month-selector-modern">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
        <label for="filterAn">An</label>
        <select id="filterAn" class="dashboard-month-select">
          <option value="">Toți anii</option>
        </select>
      </div>
    </div>
    <div class="month-selector-modern">
      <div class="month-selector-wrapper">
        <i class="fas fa-calendar-day" aria-hidden="true"></i>
        <label for="filterLuna">Lună</label>
        <select id="filterLuna" class="dashboard-month-select">
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
    </div>
    <div class="month-selector-modern istoric-search-wrap">
      <div class="month-selector-wrapper">
        <i class="fas fa-search" aria-hidden="true"></i>
        <label for="searchInput">Căutare</label>
        <input type="text" id="searchInput" placeholder="Caută după lună…" autocomplete="off">
      </div>
    </div>
  </div>

  <div class="kpi-grid istoric-stat-grid" id="statsContainer">
    <div class="card istoric-kpi-placeholder">
      <h4>Sumar KPI</h4>
      <p class="istoric-kpi-placeholder-text">Se încarcă indicatorii…</p>
    </div>
  </div>

  <div class="rapoarte-charts-grid">
    <div class="chart-container">
      <h3><i class="fas fa-coins" aria-hidden="true"></i> Vânzări fără TVA</h3>
      <div class="chart-wrapper">
        <canvas id="vanzariChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-shopping-cart" aria-hidden="true"></i> Comenzi</h3>
      <div class="chart-wrapper">
        <canvas id="comenziChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-chart-line" aria-hidden="true"></i> Profit</h3>
      <div class="chart-wrapper">
        <canvas id="profitChart"></canvas>
      </div>
    </div>
    <div class="chart-container">
      <h3><i class="fas fa-percentage" aria-hidden="true"></i> Conversie</h3>
      <div class="chart-wrapper">
        <canvas id="conversieChart"></canvas>
      </div>
    </div>
  </div>

  <section class="comparare-table-section istoric-table-section" aria-labelledby="istoric-table-heading">
    <h3 id="istoric-table-heading" style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <span><i class="fas fa-table" aria-hidden="true"></i> Istoric complet</span>
      <span style="display:inline-flex;gap:8px;align-items:center;">
        <button type="button" id="istoricExportExcelBtn" class="btn secondary">
          <i class="fas fa-file-excel" aria-hidden="true"></i> Excel
        </button>
        <button type="button" id="istoricExportPdfBtn" class="btn secondary">
          <i class="fas fa-file-pdf" aria-hidden="true"></i> PDF
        </button>
      </span>
    </h3>
    <div class="comparare-table-wrap">
      <div id="tableLoading" class="istoric-loading">Se încarcă datele…</div>
      <table class="comparare-table istoric-data-table" id="istoricTable" style="display: none;">
        <thead>
          <tr>
            <th scope="col">Lună</th>
            <th scope="col" class="text-right">Plan</th>
            <th scope="col" class="text-right">Vânzări</th>
            <th scope="col" class="text-right">Vânzări TVA</th>
            <th scope="col" class="text-right">Profit</th>
            <th scope="col" class="text-center">Progres %</th>
            <th scope="col" class="text-right">Dif. plan</th>
            <th scope="col" class="text-right">Comenzi</th>
            <th scope="col" class="text-right">Comenzi/zi</th>
            <th scope="col" class="text-right">CEC mediu</th>
            <th scope="col" class="text-right">Livrări</th>
            <th scope="col" class="text-right">Pickup</th>
            <th scope="col" class="text-right">Sesiuni</th>
            <th scope="col" class="text-center">Conversie</th>
            <th scope="col" class="text-right">vs anterioară</th>
          </tr>
        </thead>
        <tbody id="istoricTableBody"></tbody>
      </table>
      <div id="noData" class="istoric-empty" style="display: none;">Nu există date pentru filtrele selectate.</div>
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(val || 0);
}

let allData = [];
let filteredData = [];
const charts = {};

function istoricChartOptions() {
  if (typeof VoltaChartTheme !== 'undefined') {
    return VoltaChartTheme.cartesianDefaults({
      plugins: {
        legend: { display: true, position: 'top' },
        tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
          titleColor: VoltaChartTheme.colors.brand,
          bodyColor: VoltaChartTheme.colors.textPrimary,
        }),
      },
      scales: {
        x: {
          ticks: Object.assign(VoltaChartTheme.ticks(8, 11), {
            maxRotation: window.innerWidth <= 768 ? 45 : 35,
            minRotation: window.innerWidth <= 768 ? 45 : 0,
          }),
        },
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
      x: { ticks: { color: '#cbd5e1', maxRotation: 45 }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false } },
      y: { ticks: { color: '#cbd5e1' }, grid: { color: 'rgba(148,163,184,0.12)', drawBorder: false }, beginAtZero: true },
    },
  };
}

async function loadIstoric() {
  try {
    const res = await fetch("{{ route('api.istoric') }}");
    const result = await res.json();

    if (!result.success) {
      throw new Error(result.error || 'Eroare la încărcarea datelor');
    }

    allData = Array.isArray(result.data) ? result.data : [];
    filteredData = [...allData];

    populateFilters();
    updateDisplay();
  } catch (err) {
    console.error('Eroare la încărcarea istoricului:', err);
    const el = document.getElementById('tableLoading');
    if (el) el.textContent = 'Eroare la încărcarea datelor: ' + err.message;
  }
}

function populateFilters() {
  const anSelect = document.getElementById('filterAn');
  const ani = [...new Set(allData.map(d => d.an))].sort((a, b) => b - a);

  anSelect.innerHTML = '<option value="">Toți anii</option>';
  ani.forEach(an => {
    const opt = document.createElement('option');
    opt.value = an;
    opt.textContent = an;
    anSelect.appendChild(opt);
  });
}

function filterData() {
  const an = document.getElementById('filterAn').value;
  const luna = document.getElementById('filterLuna').value;
  const search = document.getElementById('searchInput').value.toLowerCase();

  filteredData = allData.filter(item => {
    const matchAn = !an || item.an == an;
    const matchLuna = !luna || item.luna_num == luna;
    const matchSearch = !search || item.luna_label.toLowerCase().includes(search);
    return matchAn && matchLuna && matchSearch;
  });

  updateDisplay();
}

function updateDisplay() {
  updateStats();
  updateTable();
  updateCharts();
}

function updateStats() {
  const container = document.getElementById('statsContainer');
  if (!container) return;

  if (filteredData.length === 0) {
    const hasAnyData = allData.length > 0;
    container.innerHTML = `
      <div class="card istoric-kpi-placeholder">
        <h4>Sumar KPI</h4>
        <p class="istoric-kpi-placeholder-text">${hasAnyData
          ? 'Nicio lună nu se potrivește cu filtrele curente. Resetează anul, luna sau căutarea.'
          : 'Nu există încă rânduri de istoric în baza de date pentru perioada afișată.'}</p>
      </div>
    `;
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

  const blocks = [
    { label: 'Total vânzări', val: formatNumber(totalVanzari), unit: 'MDL' },
    { label: 'Total profit', val: formatNumber(totalProfit), unit: 'MDL' },
    { label: 'Total comenzi', val: formatNumber(totalComenzi), unit: '' },
    { label: 'CEC mediu', val: formatNumber(avgCecMediu), unit: 'MDL' },
    { label: 'Total livrări', val: formatNumber(totalLivrari), unit: '' },
    { label: 'Pickup', val: formatNumber(totalPickup), unit: '' },
    { label: 'Conversie medie', val: avgConversie, unit: '%' },
    { label: 'Luni analizate', val: String(filteredData.length), unit: '' },
  ];

  container.innerHTML = blocks.map(b => `
    <div class="card">
      <h4>${b.label}</h4>
      <div class="value">${b.val}${b.unit ? `<span class="unit">${b.unit}</span>` : ''}</div>
    </div>
  `).join('');
}

function updateTable() {
  const tbody = document.getElementById('istoricTableBody');
  const table = document.getElementById('istoricTable');
  const loading = document.getElementById('tableLoading');
  const noData = document.getElementById('noData');

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
    const vanzariVsAnterioara = item.vanzari_vs_anterioara || 0;
    const vanzariVsAnterioaraPercent = item.vanzari_vs_anterioara_percent || 0;
    const diffClass = vanzariVsAnterioara >= 0 ? 'diff-positive' : 'diff-negative';
    const sign = vanzariVsAnterioara >= 0 ? '+' : '';
    const planDiffClass = (item.diferenta_plan || 0) >= 0 ? 'diff-positive' : 'diff-negative';
    const sourceIcon = '';

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><strong>${item.luna_label}</strong>${sourceIcon}</td>
      <td class="text-right">${formatNumber(item.plan_luna)}</td>
      <td class="text-right">${formatNumber(item.vanzari_luna)}</td>
      <td class="text-right">${formatNumber(item.vanzari_cu_tva)}</td>
      <td class="text-right">${formatNumber(item.profit)}</td>
      <td class="text-center">${formatNumber(item.progres_plan)}%</td>
      <td class="text-right ${planDiffClass}">${formatNumber(item.diferenta_plan)}</td>
      <td class="text-right">${formatNumber(item.comenzi)}</td>
      <td class="text-right">${formatNumber(item.comenzi_zi)}</td>
      <td class="text-right">${formatNumber(item.cec_mediu)}</td>
      <td class="text-right">${formatNumber(item.total_livrari_luna)}</td>
      <td class="text-right">${formatNumber(item.pickup)}</td>
      <td class="text-right">${formatNumber(item.sesiuni)}</td>
      <td class="text-center">${formatNumber(item.conversie)}%</td>
      <td class="text-right istoric-vs-col ${diffClass}">${sign}${formatNumber(Math.abs(vanzariVsAnterioara))}<small>(${sign}${vanzariVsAnterioaraPercent}%)</small></td>
    `;
    tbody.appendChild(tr);
  });
}

function destroyChart(chartId) {
  if (charts[chartId] && charts[chartId].instance) {
    charts[chartId].instance.destroy();
    charts[chartId].instance = null;
  }
}

function updateCharts() {
  if (filteredData.length === 0) {
    ['vanzariChart', 'comenziChart', 'profitChart', 'conversieChart'].forEach(destroyChart);
    return;
  }

  const labels = filteredData.map(d => d.luna_label).reverse();
  const vanzari = filteredData.map(d => d.vanzari_luna || 0).reverse();
  const comenzi = filteredData.map(d => d.comenzi || 0).reverse();
  const profit = filteredData.map(d => d.profit || 0).reverse();
  const conversie = filteredData.map(d => d.conversie || 0).reverse();
  const opts = istoricChartOptions();

  destroyChart('vanzariChart');
  const ctx1 = document.getElementById('vanzariChart').getContext('2d');
  charts['vanzariChart'] = {
    instance: new Chart(ctx1, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Vânzări (MDL)',
          data: vanzari,
          borderColor: 'rgba(255, 238, 0, 0.85)',
          backgroundColor: 'rgba(255, 238, 0, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: window.innerWidth <= 768 ? 2 : 4,
          pointHoverRadius: window.innerWidth <= 768 ? 4 : 6,
          pointBackgroundColor: 'rgba(255, 238, 0, 0.9)',
          pointBorderColor: 'rgb(15, 23, 42)',
          pointBorderWidth: 1,
        }],
      },
      options: opts,
    }),
  };

  destroyChart('comenziChart');
  const ctx2 = document.getElementById('comenziChart').getContext('2d');
  charts['comenziChart'] = {
    instance: new Chart(ctx2, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Comenzi',
          data: comenzi,
          backgroundColor: 'rgba(96, 165, 250, 0.45)',
          hoverBackgroundColor: 'rgba(96, 165, 250, 0.65)',
          borderColor: 'rgba(59, 130, 246, 0.4)',
          borderWidth: 1,
          borderRadius: 6,
          borderSkipped: false,
        }],
      },
      options: opts,
    }),
  };

  destroyChart('profitChart');
  const ctx3 = document.getElementById('profitChart').getContext('2d');
  charts['profitChart'] = {
    instance: new Chart(ctx3, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Profit (MDL)',
          data: profit,
          borderColor: 'rgb(248, 113, 113)',
          backgroundColor: 'rgba(248, 113, 113, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: window.innerWidth <= 768 ? 2 : 4,
          pointHoverRadius: window.innerWidth <= 768 ? 4 : 6,
          pointBackgroundColor: 'rgb(248, 113, 113)',
          pointBorderColor: 'rgb(15, 23, 42)',
          pointBorderWidth: 1,
        }],
      },
      options: opts,
    }),
  };

  destroyChart('conversieChart');
  const ctx4 = document.getElementById('conversieChart').getContext('2d');
  charts['conversieChart'] = {
    instance: new Chart(ctx4, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Conversie (%)',
          data: conversie,
          borderColor: 'rgb(52, 211, 153)',
          backgroundColor: 'rgba(52, 211, 153, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.35,
          pointRadius: window.innerWidth <= 768 ? 2 : 4,
          pointHoverRadius: window.innerWidth <= 768 ? 4 : 6,
          pointBackgroundColor: 'rgb(45, 212, 191)',
          pointBorderColor: 'rgb(15, 23, 42)',
          pointBorderWidth: 1,
        }],
      },
      options: opts,
    }),
  };
}

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('filterAn').addEventListener('change', filterData);
  document.getElementById('filterLuna').addEventListener('change', filterData);
  document.getElementById('searchInput').addEventListener('input', filterData);
  document.getElementById('istoricExportExcelBtn').addEventListener('click', function () {
    const table = document.getElementById('istoricTable');
    if (!table || table.style.display === 'none') {
      alert('Nu există date în tabel pentru export.');
      return;
    }
    try {
      window.VoltaExcelExport.exportTable(table, {
        fileName: 'istoric_tabel_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Istoric'
      });
    } catch (error) {
      alert('Nu am putut exporta Excel: ' + error.message);
    }
  });
  document.getElementById('istoricExportPdfBtn').addEventListener('click', function () {
    const an = document.getElementById('filterAn').value || '';
    const luna = document.getElementById('filterLuna').value || '';
    const search = document.getElementById('searchInput').value || '';
    const params = new URLSearchParams({ an: an, luna: luna, search: search });
    window.location.href = @json(route('export.istoric.pdf')) + '?' + params.toString();
  });
  loadIstoric();
});
</script>
@endpush
