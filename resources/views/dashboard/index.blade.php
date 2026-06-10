@extends('layouts.app')

@section('title', 'Dashboard – VOLTA')

@section('header-title')
Bun venit, {{ Auth::check() ? Auth::user()->username : 'User' }}!
@endsection

@section('content')
<!-- Selector lună modern pentru mobile -->
<div class="month-selector-modern">
  <div class="month-selector-wrapper">
    <i class="fas fa-calendar-alt"></i>
    <label for="selectLuna">Selectează luna</label>
    <select id="selectLuna" class="dashboard-month-select"></select>
  </div>
</div>

<!-- KPI CARDS -->
@php
  $isAdmin = auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator');
@endphp
<div class="kpi-grid">
  <div class="card">
    <h4>Vânzări fără TVA</h4>
    <div class="value" id="vanzari-luna">-</div>
  </div>
  <div class="card">
    <h4>Vânzări cu TVA</h4>
    <div class="value" id="vanzari-cu-tva">-</div>
  </div>
  <div class="card">
    <h4>Profit</h4>
    <div class="value" id="profit">-</div>
  </div>
  <div class="card">
    <h4>CEC mediu</h4>
    <div class="value" id="cec-mediu">-</div>
  </div>
  <div class="card{{ $isAdmin ? ' editable-plan' : '' }}"@if($isAdmin) title="Click pentru a seta planul lunar" role="button" tabindex="0" aria-label="Plan luna curentă — click pentru editare"@endif>
    <h4>
      Plan luna curentă
      @if($isAdmin)
        <i class="fas fa-edit edit-icon" title="Setează planul pentru orice lună"></i>
      @endif
    </h4>
    <div class="value" id="plan-luna">-</div>
  </div>
  <div class="card">
    <h4>Progres plan</h4>
    <div class="value" id="progres-plan">-</div>
  </div>
  <div class="card">
    <h4>Prognoză plan</h4>
    <div class="value" id="prognoza-plan">-</div>
  </div>
  <div class="card">
    <h4>Prognoză plan %</h4>
    <div class="value" id="prognoza-plan-procent">-</div>
  </div>
  <div class="card">
    <h4>Diferență față de plan</h4>
    <div class="value" id="diferenta-plan">-</div>
  </div>
  <div class="card">
    <h4>Comenzi</h4>
    <div class="value" id="comenzi">-</div>
  </div>
  <div class="card">
    <h4>Comenzi/zi</h4>
    <div class="value" id="comenzi-zi">-</div>
  </div>
  <div class="card">
    <h4>Sesiuni</h4>
    <div class="value" id="sesiuni">-</div>
  </div>
  <div class="card">
    <h4>Conversie</h4>
    <div class="value" id="conversie">-</div>
  </div>
  <div class="card">
    <h4>Total livrări lună</h4>
    <div class="value" id="total-livrari-luna">-</div>
  </div>
  <div class="card">
    <h4>Pickup</h4>
    <div class="value" id="pickup">-</div>
  </div>
</div>

<!-- GRAFICE -->
<div class="charts-grid">
  <div class="chart-container chart-panel">
    <div class="chart-panel__head">
      <span class="chart-panel__icon" aria-hidden="true"><i class="fas fa-chart-line"></i></span>
      <div class="chart-panel__titles">
        <h2 class="chart-panel__title">Grafic lunar</h2>
        <p class="chart-panel__subtitle">Plan vs. vânzări reale</p>
      </div>
    </div>
    <div class="chart-wrapper" title="Click pentru vizualizare mare">
      <canvas id="salesChart"></canvas>
    </div>
  </div>
  <div class="chart-container chart-panel">
    <div class="chart-panel__head">
      <span class="chart-panel__icon" aria-hidden="true"><i class="fas fa-shopping-cart"></i></span>
      <div class="chart-panel__titles">
        <h2 class="chart-panel__title">Comenzi per lună</h2>
        <p class="chart-panel__subtitle">Volum comenzi</p>
      </div>
    </div>
    <div class="chart-wrapper" title="Click pentru vizualizare mare">
      <canvas id="comenziLunarChart"></canvas>
    </div>
  </div>
  <div class="chart-container chart-panel">
    <div class="chart-panel__head">
      <span class="chart-panel__icon chart-panel__icon--coral" aria-hidden="true"><i class="fas fa-percentage"></i></span>
      <div class="chart-panel__titles">
        <h2 class="chart-panel__title">Conversie per lună</h2>
        <p class="chart-panel__subtitle">Comenzi / sesiuni</p>
      </div>
    </div>
    <div class="chart-wrapper" title="Click pentru vizualizare mare">
      <canvas id="conversieLunarChart"></canvas>
    </div>
  </div>
  <div class="chart-container chart-panel">
    <div class="chart-panel__head">
      <span class="chart-panel__icon chart-panel__icon--sky" aria-hidden="true"><i class="fas fa-network-wired"></i></span>
      <div class="chart-panel__titles">
        <h2 class="chart-panel__title">Sesiuni per lună</h2>
        <p class="chart-panel__subtitle">Trafic site</p>
      </div>
    </div>
    <div class="chart-wrapper" title="Click pentru vizualizare mare">
      <canvas id="sesiuniChart"></canvas>
    </div>
  </div>
</div>

<div id="dashboard-chart-modal" class="dashboard-chart-modal" aria-hidden="true">
  <div class="dashboard-chart-modal__backdrop" data-close-chart-modal tabindex="-1"></div>
  <div class="dashboard-chart-modal__panel" role="dialog" aria-modal="true" aria-labelledby="dashboard-chart-modal-title">
    <div class="dashboard-chart-modal__head">
      <h3 id="dashboard-chart-modal-title"></h3>
      <button type="button" class="dashboard-chart-modal__close" data-close-chart-modal aria-label="Închide"><i class="fas fa-times"></i></button>
    </div>
    <div class="dashboard-chart-modal__body">
      <div class="dashboard-chart-modal__chart-wrap">
        <canvas id="dashboard-chart-modal-canvas"></canvas>
      </div>
    </div>
  </div>
</div>

@if($isAdmin)
<div id="dashboard-plan-modal" class="dashboard-chart-modal dashboard-plan-modal" aria-hidden="true">
  <div class="dashboard-chart-modal__backdrop" data-close-plan-modal tabindex="-1"></div>
  <div class="dashboard-chart-modal__panel dashboard-plan-modal__panel" role="dialog" aria-modal="true" aria-labelledby="dashboard-plan-modal-title">
    <div class="dashboard-chart-modal__head">
      <h3 id="dashboard-plan-modal-title"><i class="fas fa-bullseye" aria-hidden="true"></i> Plan lunar</h3>
      <button type="button" class="dashboard-chart-modal__close" data-close-plan-modal aria-label="Închide"><i class="fas fa-times"></i></button>
    </div>
    <div class="dashboard-chart-modal__body dashboard-plan-modal__body">
      <p class="dashboard-plan-modal__lead">Alege anul și luna, apoi introdu sau modifică planul de vânzări (MDL).</p>
      <form id="dashboardPlanForm" class="dashboard-plan-form" novalidate>
        <div class="dashboard-plan-form__grid">
          <div class="dashboard-plan-form__field">
            <label for="planEditYear">An</label>
            <select id="planEditYear" class="dashboard-month-select" required></select>
          </div>
          <div class="dashboard-plan-form__field">
            <label for="planEditMonth">Luna</label>
            <select id="planEditMonth" class="dashboard-month-select" required></select>
          </div>
          <div class="dashboard-plan-form__field dashboard-plan-form__field--wide">
            <label for="planEditValoare">Plan (MDL)</label>
            <input type="number" id="planEditValoare" class="dashboard-plan-form__input" min="0" step="0.01" required placeholder="0">
          </div>
        </div>
        <p class="dashboard-plan-form__hint" id="planEditHint" aria-live="polite"></p>
        <div class="dashboard-plan-form__actions">
          <button type="button" class="btn secondary" data-close-plan-modal>Anulează</button>
          <button type="submit" class="btn primary" id="planEditSaveBtn">
            <i class="fas fa-save" aria-hidden="true"></i> Salvează planul
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ---------------- UTILITARE ---------------- 
function formatNumber(val) {
  return new Intl.NumberFormat('ro-RO').format(val || 0);
}

function getThemeBrand() {
  try {
    const v = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim();
    return v || '#FFEE00';
  } catch (e) {
    return '#FFEE00';
  }
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

// ---------------- MODAL GRAFIC MARE (click pe canvas) ---------------- 
let dashboardModalChart = null;

function cloneDashboardChartOptions(base) {
  try {
    return JSON.parse(JSON.stringify(base));
  } catch (e) {
    return {};
  }
}

function optionsWithExpandToModal(title, baseOpts) {
  const o = cloneDashboardChartOptions(baseOpts);
  o.onClick = function (evt, elements, chart) {
    openChartExpandModal(title, chart);
  };
  return o;
}

function openChartExpandModal(title, sourceChart) {
  const modal = document.getElementById("dashboard-chart-modal");
  const titleEl = document.getElementById("dashboard-chart-modal-title");
  const canvas = document.getElementById("dashboard-chart-modal-canvas");
  if (!modal || !canvas || !sourceChart) return;

  if (titleEl) titleEl.textContent = title || "Grafic";

  if (dashboardModalChart) {
    dashboardModalChart.destroy();
    dashboardModalChart = null;
  }

  let dataCopy;
  let optionsCopy;
  try {
    dataCopy = JSON.parse(JSON.stringify(sourceChart.data));
    optionsCopy = JSON.parse(JSON.stringify(sourceChart.options));
  } catch (err) {
    console.error("Modal grafic: nu s-au putut clona datele", err);
    return;
  }

  optionsCopy.maintainAspectRatio = false;
  optionsCopy.onClick = null;

  modal.classList.add("is-open");
  modal.setAttribute("aria-hidden", "false");
  document.body.style.overflow = "hidden";

  const ctx = canvas.getContext("2d");
  const spec = { data: dataCopy, options: optionsCopy };
  const rootType = sourceChart.config && sourceChart.config.type;
  if (rootType) spec.type = rootType;
  dashboardModalChart = new Chart(ctx, spec);

  requestAnimationFrame(function () {
    if (dashboardModalChart) dashboardModalChart.resize();
  });
}

function closeDashboardChartModal() {
  const modal = document.getElementById("dashboard-chart-modal");
  if (dashboardModalChart) {
    dashboardModalChart.destroy();
    dashboardModalChart = null;
  }
  if (modal) {
    modal.classList.remove("is-open");
    modal.setAttribute("aria-hidden", "true");
  }
  document.body.style.overflow = "";
}

// ---------------- INIT CHART ---------------- 
function initChart(chartId, label, color="#FFEE00") {
  const canvas = document.getElementById(chartId);
  if(!canvas) return;
  const ctx = canvas.getContext("2d");
  const isM = window.innerWidth <= 768;
  const fill = color.length === 7 ? color + "1A" : color;
  const baseOptsRaw = typeof VoltaChartTheme !== "undefined"
    ? VoltaChartTheme.cartesianDefaults({
        plugins: {
          legend: { display: true, position: "top" },
          tooltip: Object.assign({}, VoltaChartTheme.tooltip(), { titleColor: VoltaChartTheme.colors.brand }),
        },
      })
    : {
        responsive: true,
        plugins: {
          legend: { display: true, labels: { color: "#fff", font: { size: isM ? 10 : 12 } } },
          tooltip: { backgroundColor: "rgba(31,41,55,0.95)", titleColor: getThemeBrand(), bodyColor: "#fff", borderColor: "#475569", borderWidth: 1, padding: 12, cornerRadius: 10 },
        },
        scales: {
          x: { ticks: { color: "#e2e8f0", font: { size: isM ? 9 : 11 } }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false } },
          y: { ticks: { color: "#e2e8f0", font: { size: isM ? 9 : 11 } }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false }, beginAtZero: true },
        },
      };
  const baseOpts = optionsWithExpandToModal(label + " – vizualizare mare", baseOptsRaw);
  const chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [{
        label,
        data: [],
        borderColor: color,
        backgroundColor: fill,
        tension: 0.15,
        borderWidth: 2,
        pointRadius: isM ? 2 : 3,
        pointHoverRadius: isM ? 4 : 5,
        pointBackgroundColor: color,
        pointBorderColor: "#E2E8F0",
        pointBorderWidth: 1,
        fill: true,
      }],
    },
    options: baseOpts,
  });
  charts[chartId] = { instance: chartInstance };
}

// ---------------- LOAD KPI + CHART LUNAR ---------------- 
let selectLunaListenerAdded = false;

async function loadVanzariTotale() {
  const selectLuna = document.getElementById("selectLuna");

  try {
    const resLunare = await fetch("{{ route('api.vanzari.lunare') }}");
    const dataLunare = await resLunare.json();
    
    if (!dataLunare.success) {
      const lunaCurenta = new Date().toISOString().slice(0, 7);
      const opt = document.createElement("option");
      opt.value = lunaCurenta;
      opt.textContent = new Date(lunaCurenta + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
      selectLuna.appendChild(opt);
      selectLuna.value = lunaCurenta;
      await updateKPIandChart(lunaCurenta);
      loadComenziSiConversieLunare();
      return;
    }

    selectLuna.innerHTML = '';

    const luniSortate = [...dataLunare.luni].sort((a, b) => {
      if (b.value > a.value) return 1;
      if (b.value < a.value) return -1;
      return 0;
    });

    luniSortate.forEach((luna) => {
      const opt = document.createElement("option");
      opt.value = luna.value;
      opt.textContent = luna.label;
      selectLuna.appendChild(opt);
    });

    // Grafic lunar agregat + Comenzi + Conversie (aceeași sursă, același stil)
    const labels = dataLunare.data.map(d => d.luna_label || d.luna);
    const vanzari = dataLunare.data.map(d => d.vanzari);
    const plan = dataLunare.data.map(d => d.plan || null);
    const comenziData = dataLunare.data.map(d => d.comenzi || 0);
    const sesiuniData = dataLunare.data.map(d => d.sesiuni || 0);
    const conversieData = dataLunare.data.map(d => d.conversie || 0);

    const isDashMobile = window.innerWidth <= 768;
    const barChartOptions = typeof VoltaChartTheme !== "undefined"
      ? VoltaChartTheme.cartesianDefaults({
          animation: false,
          datasets: {
            bar: {
              categoryPercentage: 0.72,
              barPercentage: 0.86,
              maxBarThickness: isDashMobile ? 40 : 52,
            },
          },
          plugins: {
            legend: {
              display: true,
              position: "top",
              labels: {
                color: VoltaChartTheme.colors.textSecondary,
                font: { family: VoltaChartTheme.font, size: isDashMobile ? 11 : 12, weight: "600" },
                padding: isDashMobile ? 12 : 16,
                usePointStyle: true,
                pointStyle: "rectRounded",
              },
            },
            tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
              titleColor: VoltaChartTheme.colors.brand,
              bodyColor: VoltaChartTheme.colors.textPrimary,
            }),
          },
          scales: {
            x: {
              ticks: Object.assign(VoltaChartTheme.ticks(9, 12), {
                maxRotation: isDashMobile ? 45 : 0,
                minRotation: isDashMobile ? 45 : 0,
              }),
              grid: VoltaChartTheme.gridLines({ borderDash: [] }),
            },
            y: {
              beginAtZero: true,
              ticks: VoltaChartTheme.ticks(10, 12),
              grid: VoltaChartTheme.gridLines({ borderDash: [] }),
            },
          },
        })
      : {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          interaction: { mode: "index", intersect: false },
          datasets: {
            bar: {
              categoryPercentage: 0.72,
              barPercentage: 0.86,
              maxBarThickness: isDashMobile ? 40 : 52,
              borderRadius: isDashMobile ? 9 : 12,
            },
          },
          plugins: {
            legend: { display: true, position: "top", labels: { color: "#e2e8f0", font: { size: 13 }, usePointStyle: true } },
            tooltip: { backgroundColor: "rgba(30,41,59,0.96)", titleColor: getThemeBrand(), bodyColor: "#f8fafc", borderColor: "#334155", borderWidth: 1, padding: 12, cornerRadius: 10 },
          },
          scales: {
            x: { ticks: { color: "#cbd5e1", maxRotation: isDashMobile ? 45 : 0 }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false } },
            y: { ticks: { color: "#cbd5e1" }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false }, beginAtZero: true },
          },
        };

    const optSales = optionsWithExpandToModal("Grafic lunar – vizualizare mare", barChartOptions);
    const optComenzi = optionsWithExpandToModal("Comenzi per lună – vizualizare mare", barChartOptions);
    const optConversie = optionsWithExpandToModal("Conversie per lună – vizualizare mare", barChartOptions);
    const optSesiuni = optionsWithExpandToModal("Sesiuni per lună – vizualizare mare", barChartOptions);

    destroyChart("salesChart");
    const ctxSales = document.getElementById("salesChart");
    const SOL = (typeof VoltaChartTheme !== "undefined" && VoltaChartTheme.barSolid) ? VoltaChartTheme.barSolid : {};
    const yellow = SOL.brand || getThemeBrand();
    const yellowHi = SOL.brandHover || "#FFF59A";
    const rose = SOL.coral || "#FB7185";
    const roseHi = SOL.coralHover || "#FDA4AF";
    const sky = SOL.sky || "#B4BCCC";
    const skyHi = SOL.skyHover || "#D4DAE4";
    const planFillStatic = "rgba(251, 113, 133, 0.12)";
    if (ctxSales) {
      charts["salesChart"] = { instance: new Chart(ctxSales.getContext("2d"), {
        data: {
          labels,
          datasets: [
            {
              type: "line",
              label: "Plan",
              data: plan,
              borderColor: rose,
              backgroundColor: planFillStatic,
              borderWidth: 2.5,
              tension: 0.2,
              cubicInterpolationMode: "default",
              pointRadius: window.innerWidth <= 768 ? 0 : 3,
              pointHoverRadius: 5,
              pointBackgroundColor: rose,
              pointBorderColor: "#F8FAFC",
              pointBorderWidth: 1,
              fill: true,
              order: 1,
            },
            {
              type: "bar",
              label: "Vânzări reale",
              data: vanzari,
              backgroundColor: yellow,
              hoverBackgroundColor: yellowHi,
              borderColor: "rgba(15, 23, 42, 0.35)",
              borderWidth: 1,
              borderRadius: 6,
              borderSkipped: false,
              order: 2,
            },
          ],
        },
        options: optSales,
      }) };
    }

    destroyChart("comenziLunarChart");
    const ctxComenzi = document.getElementById("comenziLunarChart");
    if (ctxComenzi) {
      charts["comenziLunarChart"] = { instance: new Chart(ctxComenzi.getContext("2d"), {
        type: "bar",
        data: {
          labels,
          datasets: [{
            label: "Comenzi",
            data: comenziData,
            backgroundColor: yellow,
            hoverBackgroundColor: yellowHi,
            borderColor: "rgba(15, 23, 42, 0.35)",
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
          }],
        },
        options: optComenzi,
      }) };
    }

    destroyChart("conversieLunarChart");
    const ctxConversie = document.getElementById("conversieLunarChart");
    if (ctxConversie) {
      charts["conversieLunarChart"] = { instance: new Chart(ctxConversie.getContext("2d"), {
        type: "bar",
        data: {
          labels,
          datasets: [{
            label: "Conversie (%)",
            data: conversieData,
            backgroundColor: rose,
            hoverBackgroundColor: roseHi,
            borderColor: "rgba(15, 23, 42, 0.35)",
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
          }],
        },
        options: optConversie,
      }) };
    }

    destroyChart("sesiuniChart");
    const ctxSesiuni = document.getElementById("sesiuniChart");
    if (ctxSesiuni) {
      charts["sesiuniChart"] = { instance: new Chart(ctxSesiuni.getContext("2d"), {
        type: "bar",
        data: {
          labels,
          datasets: [{
            label: "Total sesiuni",
            data: sesiuniData,
            backgroundColor: sky,
            hoverBackgroundColor: skyHi,
            borderColor: "rgba(15, 23, 42, 0.35)",
            borderWidth: 1,
            borderRadius: 6,
            borderSkipped: false,
          }],
        },
        options: optSesiuni,
      }) };
    }

    async function updateKPIandChart(luna) {
      const resKPI = await fetch(`{{ route('api.kpi') }}?month=${luna}`);
      const kpiData = await resKPI.json();
      
      if (!kpiData.success) {
        console.error("Eroare la încărcarea KPI:", kpiData.error);
        return;
      }

      const planLuna = kpiData.plan_luna || 0;
      const diferentaPlan = kpiData.diferenta_plan || 0;
      const diferentaColor = diferentaPlan >= 0 ? '#0f0' : '#f00';
      
      function formatValue(value, suffix = '') {
        const suffixHtml = suffix ? `<span style="font-size:18px;color:var(--muted); font-weight:600; margin-left:4px;">${suffix}</span>` : '';
        return `<span style="display:inline-flex; align-items:baseline;">${formatNumber(value)}${suffixHtml}</span>`;
      }
      
      const kpiValues = [
        { id: 'plan-luna', value: formatValue(planLuna, 'MDL'), rawValue: planLuna },
        { id: 'vanzari-luna', value: formatValue(kpiData.vanzari_luna || 0, 'MDL') },
        { id: 'progres-plan', value: formatValue(kpiData.progres_plan || 0, '%') },
        { id: 'diferenta-plan', value: `<span style="color: ${diferentaColor}">${formatNumber(diferentaPlan)}</span> <span style="font-size:16px;color:var(--muted); font-weight:600;">MDL</span>` },
        { id: 'prognoza-plan', value: formatValue(kpiData.prognoza_plan || 0, 'MDL') },
        { id: 'prognoza-plan-procent', value: formatValue(kpiData.prognoza_plan_procent || 0, '%') },
        { id: 'comenzi', value: formatValue(kpiData.comenzi || 0) },
        { id: 'comenzi-zi', value: formatValue(kpiData.comenzi_zi || 0) },
        { id: 'sesiuni', value: formatValue(kpiData.sesiuni || 0) },
        { id: 'conversie', value: formatValue(kpiData.conversie || 0, '%') },
        { id: 'profit', value: formatValue(kpiData.profit || 0, 'MDL') },
        { id: 'vanzari-cu-tva', value: formatValue(kpiData.vanzari_cu_tva || 0, 'MDL') },
        { id: 'cec-mediu', value: formatValue(kpiData.cec_mediu ?? kpiData.valoare_medie ?? 0, 'MDL') },
        { id: 'total-livrari-luna', value: formatValue(kpiData.total_livrari_luna ?? 0) },
        { id: 'pickup', value: formatValue(kpiData.pickup ?? 0) }
      ];

      kpiValues.forEach(kpi => {
        const element = document.getElementById(kpi.id);
        if (element) {
          element.innerHTML = kpi.value;
          // Salvează valoarea brută pentru plan-luna pentru editare
          if (kpi.id === 'plan-luna' && kpi.rawValue !== undefined) {
            element.setAttribute('data-raw-value', kpi.rawValue);
            element.setAttribute('data-current-month', luna);
          }
        }
      });

      // Reinițializează editarea după actualizarea KPI-urilor
      @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
      initPlanEdit();
      @endif
    }

    // Formular plan lunar (toate lunile anului) — doar admini
    @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
    const PLAN_LUNI_RO = [
      'Ianuarie', 'Februarie', 'Martie', 'Aprilie', 'Mai', 'Iunie',
      'Iulie', 'August', 'Septembrie', 'Octombrie', 'Noiembrie', 'Decembrie'
    ];
    const PLAN_YEAR_MIN = 2023;

    function buildPlanMonthKey(year, monthNum) {
      return String(year) + '-' + String(monthNum).padStart(2, '0');
    }

    function parseYm(ym) {
      if (!ym || !/^\d{4}-\d{2}$/.test(ym)) {
        const now = new Date();
        return { year: now.getFullYear(), month: now.getMonth() + 1 };
      }
      const parts = ym.split('-');
      return { year: parseInt(parts[0], 10), month: parseInt(parts[1], 10) };
    }

    function populatePlanYearMonthSelects(defaultYear, defaultMonth) {
      const yearSelect = document.getElementById('planEditYear');
      const monthSelect = document.getElementById('planEditMonth');
      if (!yearSelect || !monthSelect) return;

      const currentYear = new Date().getFullYear();
      yearSelect.innerHTML = '';
      for (let y = currentYear + 1; y >= PLAN_YEAR_MIN; y--) {
        const opt = document.createElement('option');
        opt.value = String(y);
        opt.textContent = String(y);
        yearSelect.appendChild(opt);
      }
      yearSelect.value = String(defaultYear);

      monthSelect.innerHTML = '';
      PLAN_LUNI_RO.forEach(function (name, index) {
        const opt = document.createElement('option');
        opt.value = String(index + 1);
        opt.textContent = name;
        monthSelect.appendChild(opt);
      });
      monthSelect.value = String(defaultMonth);
    }

    async function fetchPlanForForm() {
      const yearSelect = document.getElementById('planEditYear');
      const monthSelect = document.getElementById('planEditMonth');
      const valoareInput = document.getElementById('planEditValoare');
      const hint = document.getElementById('planEditHint');
      if (!yearSelect || !monthSelect || !valoareInput) return;

      const monthKey = buildPlanMonthKey(parseInt(yearSelect.value, 10), parseInt(monthSelect.value, 10));
      if (hint) hint.textContent = 'Se încarcă planul pentru ' + PLAN_LUNI_RO[parseInt(monthSelect.value, 10) - 1] + ' ' + yearSelect.value + '…';

      try {
        const res = await fetch(@json(route('api.kpi.plan.show')) + '?month=' + encodeURIComponent(monthKey));
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Nu s-a putut încărca planul.');
        valoareInput.value = data.plan_luna != null ? data.plan_luna : 0;
        if (hint) {
          hint.textContent = data.plan_luna > 0
            ? 'Plan existent: ' + formatNumber(data.plan_luna) + ' MDL'
            : 'Nu există plan setat pentru această lună — poți introduce o valoare nouă.';
        }
      } catch (err) {
        if (hint) hint.textContent = '';
        console.error(err);
        alert('Eroare la încărcarea planului: ' + err.message);
      }
    }

    function openPlanModal() {
      const modal = document.getElementById('dashboard-plan-modal');
      const planValue = document.getElementById('plan-luna');
      const selectLunaEl = document.getElementById('selectLuna');
      if (!modal) return;

      const refYm = (planValue && planValue.getAttribute('data-current-month')) || (selectLunaEl && selectLunaEl.value) || new Date().toISOString().slice(0, 7);
      const parsed = parseYm(refYm);
      const defaultYear = new Date().getFullYear();
      populatePlanYearMonthSelects(defaultYear, parsed.month);

      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      fetchPlanForForm();
      const valoareInput = document.getElementById('planEditValoare');
      if (valoareInput) valoareInput.focus();
    }

    function closePlanModal() {
      const modal = document.getElementById('dashboard-plan-modal');
      if (!modal) return;
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }

    function initPlanEdit() {
      const planCard = document.querySelector('.editable-plan');
      const modal = document.getElementById('dashboard-plan-modal');
      const form = document.getElementById('dashboardPlanForm');
      const yearSelect = document.getElementById('planEditYear');
      const monthSelect = document.getElementById('planEditMonth');
      if (!planCard || !modal || !form) return;
      if (planCard.dataset.editInitialized === 'true') return;
      planCard.dataset.editInitialized = 'true';

      planCard.addEventListener('click', function () { openPlanModal(); });
      planCard.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openPlanModal();
        }
      });

      modal.addEventListener('click', function (e) {
        if (e.target.closest('[data-close-plan-modal]')) closePlanModal();
      });

      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closePlanModal();
      });

      if (yearSelect) yearSelect.addEventListener('change', fetchPlanForForm);
      if (monthSelect) monthSelect.addEventListener('change', fetchPlanForForm);

      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const saveBtn = document.getElementById('planEditSaveBtn');
        const valoareInput = document.getElementById('planEditValoare');
        const monthKey = buildPlanMonthKey(parseInt(yearSelect.value, 10), parseInt(monthSelect.value, 10));
        const valoare = parseFloat(valoareInput.value);
        if (Number.isNaN(valoare) || valoare < 0) {
          alert('Introdu o valoare validă (≥ 0).');
          valoareInput.focus();
          return;
        }

        if (saveBtn) saveBtn.disabled = true;
        try {
          const response = await fetch(@json(route('api.kpi.plan.update')), {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': @json(csrf_token())
            },
            body: JSON.stringify({ month: monthKey, valoare: valoare })
          });
          const result = await response.json();
          if (!result.success) throw new Error(result.error || 'Nu s-a putut salva planul.');

          const selectLunaEl = document.getElementById('selectLuna');
          const dashboardMonth = selectLunaEl ? selectLunaEl.value : monthKey;
          if (dashboardMonth === monthKey) {
            await updateKPIandChart(dashboardMonth);
          } else {
            loadVanzariTotale();
          }

          planCard.style.borderColor = '#10B981';
          setTimeout(function () { planCard.style.borderColor = ''; }, 2000);
          closePlanModal();
        } catch (error) {
          alert('Eroare la salvare: ' + error.message);
        } finally {
          if (saveBtn) saveBtn.disabled = false;
        }
      });
    }

    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(initPlanEdit, 500);
    });
    @endif

    if (!selectLunaListenerAdded) {
      selectLuna.addEventListener("change", async () => {
        const luna = selectLuna.value;
        await updateKPIandChart(luna);
      });
      selectLunaListenerAdded = true;
    }

    if (dataLunare.luni.length) {
      const ultimaLuna = dataLunare.luni[dataLunare.luni.length - 1].value;
      selectLuna.value = ultimaLuna;
      selectLuna.dispatchEvent(new Event("change"));
    } else {
      const lunaCurenta = new Date().toISOString().slice(0, 7);
      const opt = document.createElement("option");
      opt.value = lunaCurenta;
      opt.textContent = new Date(lunaCurenta + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
      selectLuna.appendChild(opt);
      selectLuna.value = lunaCurenta;
      await updateKPIandChart(lunaCurenta);
      loadComenziSiConversieLunare();
    }

  } catch(err) {
    console.error("Eroare la încărcarea datelor:", err);
    const lunaCurenta = new Date().toISOString().slice(0, 7);
    if (selectLuna && !selectLuna.options.length) {
      const opt = document.createElement("option");
      opt.value = lunaCurenta;
      opt.textContent = new Date(lunaCurenta + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
      selectLuna.appendChild(opt);
      selectLuna.value = lunaCurenta;
      await updateKPIandChart(lunaCurenta);
      loadComenziSiConversieLunare();
    }
  }
}

// ---------------- COMENZI ȘI CONVERSIE PER LUNĂ (fallback: grafice goale) ----------------
function loadComenziSiConversieLunare() {
  destroyChart("comenziLunarChart");
  destroyChart("conversieLunarChart");
  destroyChart("sesiuniChart");
  initChart("comenziLunarChart", "Comenzi", getThemeBrand());
  initChart("conversieLunarChart", "Conversie %", "#FB7185");
  initChart("sesiuniChart", "Total sesiuni", "#B4BCCC");
}

// ---------------- DOCUMENT READY ---------------- 
document.addEventListener("DOMContentLoaded", () => {
  const chartModalEl = document.getElementById("dashboard-chart-modal");
  if (chartModalEl) {
    chartModalEl.addEventListener("click", function (e) {
      if (e.target.closest("[data-close-chart-modal]")) closeDashboardChartModal();
    });
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && chartModalEl.classList.contains("is-open")) closeDashboardChartModal();
    });
  }
  window.addEventListener("resize", function () {
    if (dashboardModalChart) dashboardModalChart.resize();
  });

  initChart("salesChart", "Vânzări lunare", getThemeBrand());
  initChart("comenziLunarChart", "Comenzi", getThemeBrand());
  initChart("conversieLunarChart", "Conversie %", "#FB7185");
  initChart("sesiuniChart", "Total sesiuni", "#B4BCCC");

  loadVanzariTotale();
});
</script>
@endpush

