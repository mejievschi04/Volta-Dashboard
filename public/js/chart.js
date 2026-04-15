// ---------------- UTILITARE ----------------
function clean(val) {
  return val ? val.replace(/^"+|"+$/g, '').replace(/\r/g, '').replace(/"/g, '').trim() : "";
}

function toNumber(val) {
  if (!val) return 0;
  val = val.replace(/\./g, '').replace(',', '.'); 
  const n = parseFloat(val);
  return isNaN(n) ? 0 : n;
}

function formatNumber(val) {
  return val.toLocaleString('ro-RO');
}

function parseCSV(str) {
  const rows = [];
  let cur = '', row = [], insideQuotes = false;
  for (let i = 0; i < str.length; i++) {
    const c = str[i];
    if (c === '"') {
      if (insideQuotes && str[i + 1] === '"') { cur += '"'; i++; }
      else { insideQuotes = !insideQuotes; }
    } else if (c === ',' && !insideQuotes) {
      row.push(cur); cur = '';
    } else if ((c === '\n' || c === '\r') && !insideQuotes) {
      if (cur || row.length) { row.push(cur); rows.push(row); }
      cur = ''; row = [];
      if (c === '\r' && str[i + 1] === '\n') i++;
    } else { cur += c; }
  }
  if (cur || row.length) { row.push(cur); rows.push(row); }
  return rows;
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

// ---------------- INIT CHART ----------------
function initChart(chartId, label, color="#FFD700") {
  const canvas = document.getElementById(chartId);
  if(!canvas) return;
  const ctx = canvas.getContext("2d");
  const isMobile = window.innerWidth <= 768;
  const fill = color.length === 7 ? color + "22" : color;
  const opts = typeof VoltaChartTheme !== "undefined"
    ? VoltaChartTheme.cartesianDefaults({
        plugins: {
          tooltip: Object.assign({}, VoltaChartTheme.tooltip(), { titleColor: VoltaChartTheme.colors.brand }),
        },
      })
    : {
        responsive: true,
        plugins: { legend: { display: true, labels: { color: "#e2e8f0", font: { size: isMobile ? 10 : 12 } } } },
        scales: {
          x: { ticks: { color: "#cbd5e1", font: { size: isMobile ? 9 : 11 } }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false } },
          y: { ticks: { color: "#cbd5e1", font: { size: isMobile ? 9 : 11 } }, grid: { color: "rgba(148,163,184,0.12)", drawBorder: false }, beginAtZero: true },
        },
      };
  const chartInstance = new Chart(ctx, {
    type: "line",
    data: {
      labels: [],
      datasets: [{
        label,
        data: [],
        borderColor: color,
        backgroundColor: fill,
        tension: 0.35,
        borderWidth: 2,
        pointRadius: isMobile ? 2 : 3,
        pointHoverRadius: isMobile ? 4 : 6,
        pointBackgroundColor: color,
        pointBorderColor: "rgba(15,23,42,0.9)",
        pointBorderWidth: 1,
        fill: true,
      }],
    },
    options: opts,
  });
  charts[chartId] = { instance: chartInstance };
}

// ---------------- LOAD KPI + CHART LUNAR ----------------
let selectLunaListenerAdded = false; // Flag pentru a preveni adăugarea multiplă a listener-ului

async function loadVanzariTotale() {
  const selectLuna = document.getElementById("selectLuna");
  if (!selectLuna) return; // Dacă nu există elementul, ieșim

  try {
    // Generează luni dinamic (ultimele 24 de luni)
    const luni = [];
    const now = new Date();
    
    for (let i = 0; i < 24; i++) {
      const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const value = `${year}-${month}`;
      const label = date.toLocaleDateString('ro-RO', { year: 'numeric', month: 'long' });
      luni.push({ value, label });
    }

    // Populare dropdown selectLuna și selectLunaCompare
    const selectLunaCompare = document.getElementById("selectLunaCompare");
    const enableCompare = document.getElementById("enableCompare");
    
    if (selectLuna) {
      selectLuna.innerHTML = '';
      luni.forEach(luna => {
        const opt = document.createElement("option");
        opt.value = luna.value;
        opt.textContent = luna.label;
        selectLuna.appendChild(opt);
      });
    }
    
    if (selectLunaCompare) {
      selectLunaCompare.innerHTML = '';
      luni.forEach(luna => {
        const optCompare = document.createElement("option");
        optCompare.value = luna.value;
        optCompare.textContent = luna.label;
        selectLunaCompare.appendChild(optCompare);
      });
    }
    
    // Toggle pentru activare/dezactivare comparare
    enableCompare.addEventListener("change", () => {
      selectLunaCompare.disabled = !enableCompare.checked;
      selectLunaCompare.style.opacity = enableCompare.checked ? '1' : '0.5';
      if (enableCompare.checked) {
        updateKPIandChart(selectLuna.value, selectLunaCompare.value);
      } else {
        updateKPIandChart(selectLuna.value);
      }
    });
    
    // Event listener pentru schimbarea lunii de comparat
    selectLunaCompare.addEventListener("change", () => {
      if (enableCompare.checked) {
        updateKPIandChart(selectLuna.value, selectLunaCompare.value);
      }
    });

    // Grafic lunar agregat
    const labels = dataLunare.data.map(d => d.luna);
    const vanzari = dataLunare.data.map(d => d.vanzari);
    const plan = dataLunare.data.map(d => d.plan || null);

    destroyChart("salesChart");
    const salesCanvas = document.getElementById("salesChart");
    if (salesCanvas) {
    const ctx = salesCanvas.getContext("2d");
    charts["salesChart"] = { instance: new Chart(ctx, {
      data: {
        labels,
        datasets: [
          {
            type: "line",
            label: "Plan",
            data: plan,
            borderColor: "#EF4444",
            backgroundColor: "rgba(255, 0, 0, 0.1)",
            borderWidth: 3,
            tension: 0.3,
            pointRadius: window.innerWidth <= 768 ? 2 : 5,
            pointBackgroundColor: "#EF4444",
            pointBorderColor: "#ffffff",
            pointBorderWidth: window.innerWidth <= 768 ? 1 : 2,
            fill: false,
            order: 1
          },
          {
            type: "bar",
            label: "Vânzări reale",
            data: vanzari,
            backgroundColor: "rgba(255, 238, 0, 0.7)", // Galben transparent
            borderColor: "#ffee00",
            borderWidth: 2,
            borderRadius: 6,
            order: 2
          }
        ]
      },
      options: typeof VoltaChartTheme !== "undefined"
        ? VoltaChartTheme.cartesianDefaults({
            plugins: {
              legend: { display: true, position: "top" },
              tooltip: Object.assign({}, VoltaChartTheme.tooltip(), { titleColor: VoltaChartTheme.colors.brand }),
            },
            onClick: (event, elements) => {
              if (elements.length > 0) {
                const element = elements[0];
                const luna = labels[element.index];
                openVanzariDetaliiModal(luna);
              }
            },
          })
        : {
        responsive: true,
        plugins: {
          legend: { 
            labels: { 
              color: "#e2e8f0",
              font: { size: window.innerWidth <= 768 ? 10 : 12 }
            },
            display: true
          },
          tooltip: { backgroundColor: "rgba(30,41,59,0.96)", titleColor: "#FFEE00", bodyColor: "#f8fafc", borderColor: "#334155", borderWidth: 1, cornerRadius: 10, padding: 12 },
        },
        scales: {
          x: {
            ticks: { 
              color: "#cbd5e1",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            },
            grid: { color: "rgba(148,163,184,0.12)", drawBorder: false }
          },
          y: {
            ticks: { 
              color: "#cbd5e1",
              font: { size: window.innerWidth <= 768 ? 9 : 11 }
            },
            grid: { color: "rgba(148,163,184,0.12)", drawBorder: false },
            beginAtZero: true
          }
        },
        onClick: (event, elements) => {
          if (elements.length > 0) {
            const element = elements[0];
            const luna = labels[element.index];
            openVanzariDetaliiModal(luna);
          }
        }
      }
    }) };
    }

    // Funcție pentru actualizare KPI
    async function updateKPIandChart(luna, lunaCompare = null) {
      // Încarcă KPI pentru luna selectată - folosim ruta Laravel
      const resKPI = await fetch(`/api/kpi?month=${luna}`, {
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
      });
      
      if (!resKPI.ok) {
        console.error("Eroare la încărcarea KPI:", resKPI.statusText);
        return;
      }
      
      const kpiData = await resKPI.json();
      
      if (!kpiData.success) {
        console.error("Eroare la încărcarea KPI:", kpiData.error);
        return;
      }

      let kpiDataCompare = null;
      if (lunaCompare) {
        const resKPICompare = await fetch(`/api/kpi?month=${lunaCompare}`, {
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
          },
          credentials: 'same-origin'
        });
        
        if (resKPICompare.ok) {
          kpiDataCompare = await resKPICompare.json();
          if (!kpiDataCompare.success) {
            kpiDataCompare = null;
          }
        }
      }

      const planLuna = kpiData.plan_luna || 0;
      const diferentaPlan = kpiData.diferenta_plan || 0;
      const diferentaColor = diferentaPlan >= 0 ? '#0f0' : '#f00';
      
      // Funcție helper pentru formatare cu comparare
      function formatWithCompare(current, compare, suffix = '') {
        const suffixHtml = suffix ? `<span style="font-size:18px;color:var(--muted); font-weight:600; margin-left:4px;">${suffix}</span>` : '';
        const mainValueHtml = `<span style="display:inline-flex; align-items:baseline;">${formatNumber(current)}${suffixHtml}</span>`;
        if (!compare) {
          return mainValueHtml;
        }
        const diff = current - compare;
        const diffPercent = compare > 0 ? ((diff / compare) * 100).toFixed(1) : 0;
        const diffColor = diff >= 0 ? '#0f0' : '#f00';
        const diffSign = diff >= 0 ? '+' : '';
        const compareValue = `<small style="font-size:12px; color:${diffColor}; display:block; margin-top:5px; line-height:1.3; font-weight:600;">${diffSign}${formatNumber(Math.abs(diff))} (${diffSign}${diffPercent}%)</small>`;
        return mainValueHtml + compareValue;
      }
      
      // Mapare KPI-uri folosind ID-urile din view
      const kpiMap = {
        'plan-luna': formatWithCompare(planLuna, kpiDataCompare?.plan_luna || 0, 'MDL'),
        'vanzari-luna': formatWithCompare(kpiData.vanzari_luna || 0, kpiDataCompare?.vanzari_luna || 0, 'MDL'),
        'progres-plan': formatWithCompare(kpiData.progres_plan || 0, kpiDataCompare?.progres_plan || 0, '%'),
        'diferenta-plan': `<span style="color: ${diferentaColor}">${formatNumber(diferentaPlan)}</span> <span style="font-size:16px;color:var(--muted); font-weight:600;">MDL</span>`,
        'profit-luna': formatWithCompare(kpiData.profit || 0, kpiDataCompare?.profit || 0, 'MDL'),
        'vanzari-tva': formatWithCompare(kpiData.vanzari_cu_tva || 0, kpiDataCompare?.vanzari_cu_tva || 0, 'MDL'),
        'sesiuni': formatWithCompare(kpiData.sesiuni || 0, kpiDataCompare?.sesiuni || 0),
        'comenzi': formatWithCompare(kpiData.comenzi || 0, kpiDataCompare?.comenzi || 0),
        'rata-conversie': formatWithCompare(kpiData.conversie || 0, kpiDataCompare?.conversie || 0, '%'),
        'valoare-medie': formatWithCompare(kpiData.valoare_medie || 0, kpiDataCompare?.valoare_medie || 0, 'MDL'),
        'zile-activitate': formatWithCompare(kpiData.zile_activitate || 0, kpiDataCompare?.zile_activitate || 0),
        'progres-zilnic': formatWithCompare(kpiData.progres_zilnic || 0, kpiDataCompare?.progres_zilnic || 0, '%')
      };

      // Actualizează valorile KPI
      Object.keys(kpiMap).forEach(id => {
        const element = document.getElementById(id);
        if (element) {
          element.innerHTML = kpiMap[id];
        }
      });
    }


    // --- Eveniment schimbare luna pentru celelalte grafice ---
    // Adaugă listener-ul doar o dată pentru a evita duplicarea
    if (!selectLunaListenerAdded) {
      selectLuna.addEventListener("change", async () => {
        const luna = selectLuna.value;
        const lunaCompare = enableCompare.checked ? selectLunaCompare.value : null;
        await updateKPIandChart(luna, lunaCompare);

        // Reîncarcă graficele filtrate după luna selectată
        loadVanzariZilniceByLuna(luna);
        loadSesiuniZilniceByLuna(luna);
        loadRaportComenziSesiuniByLuna(luna);
      });
      selectLunaListenerAdded = true;
    }

    // --- Inițializare cu ultima lună (luna curentă) ---
    if (luni.length > 0 && selectLuna) {
      const ultimaLuna = luni[0].value; // Prima lună este cea mai recentă
      selectLuna.value = ultimaLuna;
      // Apelăm updateKPIandChart pentru a încărca datele
      if (typeof updateKPIandChart === 'function') {
        updateKPIandChart(ultimaLuna);
      }
    }

  } catch(err) {
    console.error("Eroare la încărcarea datelor:", err);
  }
}

// ---------------- MAP CSV BY LUNA ----------------
const CSV_BY_LUNA = {
  "Ianuarie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1494409538&single=true&output=csv",
  "Februarie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1306671217&single=true&output=csv",
  "Martie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1516455377&single=true&output=csv",
  "Aprilie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=677593108&single=true&output=csv",
  "Mai": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1492087469&single=true&output=csv",
  "Iunie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1027778141&single=true&output=csv",
  "Iulie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1759203601&single=true&output=csv",
  "August": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=0&single=true&output=csv",
  "Septembrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=308022222&single=true&output=csv",
  "Octombrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1776646642&single=true&output=csv",
  "Noiembrie": "https://docs.google.com/spreadsheets/d/e/2PACX-1vQ0hwNgk-C-AbPHL2ahWcl_uZfDyRJufOXMmDsVaX-_QmI_50W13mb5wNyDS-k7YhpMQ8IoqJzi2yCT/pub?gid=1348206179&single=true&output=csv",
 
  // adaugă restul lunilor aici
};

    
    
// ---------------- LOAD ZILNIC ----------------  
async function loadVanzariZilniceByLuna(luna) {
  try {
    // TODO: Implementează API endpoint pentru vânzări zilnice
    // Pentru moment, nu facem nimic pentru a evita erorile
    const chartElement = document.getElementById("salesChart2");
    if (!chartElement) return;
    
    // Dezactivează temporar până când API-ul este implementat
    console.log("loadVanzariZilniceByLuna: API endpoint nu este încă implementat");
    return;
    
    /* Cod vechi - de activat când API-ul este gata
    const res = await fetch(`/api/vanzari/zilnice?month=${luna}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("salesChart2");
    const ctx = chartElement.getContext("2d");
    charts["salesChart2"].instance = new Chart(ctx, {
      type: "line",
      data: { 
        labels: data.labels, 
        datasets: [{ 
          label: "Vânzări zilnice", 
          data: data.vanzari, 
          borderColor: "#ffee00", 
          backgroundColor: "rgba(255,238,0,0.2)", 
          fill: true, 
          tension: 0.35, 
          pointRadius: 3, 
          pointBackgroundColor: "#ffee00" 
        }] 
      },
      options: { 
        responsive: true, 
        plugins: { legend: { labels: { color: "#fff" } } }, 
        scales: { 
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } }, 
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true } 
        } 
      }
    });
    */

  } catch(err){ console.error("Eroare la graficul zilnic:", err); }
}

// ---------------- LOAD SESIUNI ----------------  
async function loadSesiuniZilniceByLuna(luna) {
  try {
    // TODO: Implementează API endpoint pentru sesiuni zilnice
    const chartElement = document.getElementById("sesiuniChart");
    if (!chartElement) return;
    
    console.log("loadSesiuniZilniceByLuna: API endpoint nu este încă implementat");
    return;
    
    /* Cod vechi - de activat când API-ul este gata
    const res = await fetch(`/api/sesiuni/zilnice?month=${luna}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("sesiuniChart");
    const ctx = chartElement.getContext("2d");
    charts["sesiuniChart"].instance = new Chart(ctx, {
      type: "line",
      data: { 
        labels: data.labels, 
        datasets: [{ 
          label: "Sesiuni zilnice", 
          data: data.sesiuni, 
          borderColor: "#ffee00", 
          backgroundColor: "rgba(255,238,0,0.2)", 
          fill: true, 
          tension: 0.35, 
          pointRadius: 3, 
          pointBackgroundColor: "#ffee00" 
        }] 
      },
      options: { 
        responsive: true, 
        plugins: { legend: { labels: { color: "#fff" } } }, 
        scales: { 
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } }, 
          y: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true } 
        } 
      }
    });
    */

  } catch(err){ console.error("Eroare la graficul Sesiuni:", err); }
}

// ---------------- LOAD COMENZI VS CONVERSIE ----------------
async function loadRaportComenziSesiuniByLuna(luna) {
  try {
    // TODO: Implementează API endpoint pentru raport comenzi/sesiuni
    const chartElement = document.getElementById("comenziConversieChart");
    if (!chartElement) return;
    
    console.log("loadRaportComenziSesiuniByLuna: API endpoint nu este încă implementat");
    return;
    
    /* Cod vechi - de activat când API-ul este gata
    const res = await fetch(`/api/raport/comenzi-sesiuni?month=${luna}`, {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    });
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("comenziConversieChart");
    const ctx = chartElement.getContext("2d");
    charts["comenziConversieChart"].instance = new Chart(ctx, {
      type: "line",
      data: {
        labels: data.labels,
        datasets: [
          { 
            label: "Comenzi", 
            data: data.comenzi, 
            borderColor: "#ffee00", 
            backgroundColor: "rgba(255,238,0,0.2)", 
            fill: true, 
            tension: 0.35, 
            pointRadius: 3 
          },
          { 
            label: "Conversie (%)", 
            data: data.conversie, 
            borderColor: "#EF4444", 
            backgroundColor: "rgba(255,0,0,0.1)", 
            fill: true, 
            tension: 0.35, 
            pointRadius: 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: { 
        responsive: true, 
        plugins: { legend: { labels: { color: "#fff" } } }, 
        scales: { 
          x: { ticks: { color: "#fff" }, grid: { color: "rgba(255,255,0,0.05)" } }, 
          y: { 
            ticks: { color: "#fff" }, 
            grid: { color: "rgba(255,255,0,0.05)" }, 
            beginAtZero: true 
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            ticks: { color: "#EF4444" },
            grid: { drawOnChartArea: false }
          }
        } 
      }
    });
    */

  } catch(err){ console.error("Eroare la grafic Comenzi vs Conversie:", err); }
}

// ---------------- MODAL VANZARI DETALII ----------------
async function openVanzariDetaliiModal(luna) {
  try {
    const res = await fetch(`../api/api-vanzari-detalii.php?month=${luna}`);
    const data = await res.json();
    
    if (!data.success) {
      alert("Eroare la încărcarea datelor: " + (data.error || "Eroare necunoscută"));
      return;
    }
    
    // Creează modal
    const modal = document.createElement('div');
    modal.id = 'vanzariDetaliiModal';
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.8);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10000;
      padding: 20px;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
      background: #1F2937;
      border-radius: 12px;
      padding: 30px;
      max-width: 900px;
      width: 100%;
      max-height: 90vh;
      overflow-y: auto;
      color: #fff;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
    `;
    
    const monthName = new Date(luna + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
    
    modalContent.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; color: #ffee00;">Detalii Vânzări - ${monthName}</h2>
        <button id="closeModal" style="
          background: #EF4444;
          color: white;
          border: none;
          padding: 10px 20px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 16px;
          font-weight: 600;
        ">✕ Închide</button>
      </div>
      
      <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 25px;">
        <div style="background: #1F2937; padding: 20px; border-radius: 8px; text-align: center;">
          <div style="color: #888; font-size: 14px; margin-bottom: 8px;">Total fără TVA</div>
          <div style="color: #ffee00; font-size: 24px; font-weight: 700;">${formatNumber(data.total_fara_tva)} MDL</div>
        </div>
        <div style="background: #1F2937; padding: 20px; border-radius: 8px; text-align: center;">
          <div style="color: #888; font-size: 14px; margin-bottom: 8px;">Total cu TVA</div>
          <div style="color: #ffee00; font-size: 24px; font-weight: 700;">${formatNumber(data.total_cu_tva)} MDL</div>
        </div>
        <div style="background: #1F2937; padding: 20px; border-radius: 8px; text-align: center;">
          <div style="color: #888; font-size: 14px; margin-bottom: 8px;">Total Profit</div>
          <div style="color: #ffee00; font-size: 24px; font-weight: 700;">${formatNumber(data.total_profit)} MDL</div>
        </div>
      </div>
      
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr style="background: #1F2937;">
              <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ffee00;">Data</th>
              <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ffee00;">Fără TVA</th>
              <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ffee00;">Cu TVA</th>
              <th style="padding: 12px; text-align: right; border-bottom: 2px solid #ffee00;">Profit</th>
            </tr>
          </thead>
          <tbody>
            ${data.data.map(row => `
              <tr style="border-bottom: 1px solid #9CA3AF;">
                <td style="padding: 10px;">${row.data}</td>
                <td style="padding: 10px; text-align: right;">${formatNumber(row.fara_tva)} MDL</td>
                <td style="padding: 10px; text-align: right;">${formatNumber(row.cu_tva)} MDL</td>
                <td style="padding: 10px; text-align: right; color: #ffee00;">${formatNumber(row.profit)} MDL</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Event listener pentru închidere
    document.getElementById('closeModal').addEventListener('click', () => {
      document.body.removeChild(modal);
    });
    
    // Închide la click pe fundal
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        document.body.removeChild(modal);
      }
    });
    
    // Închide cu ESC
    const escHandler = (e) => {
      if (e.key === 'Escape') {
        document.body.removeChild(modal);
        document.removeEventListener('keydown', escHandler);
      }
    };
    document.addEventListener('keydown', escHandler);
    
  } catch(err) {
    console.error("Eroare la deschiderea modalului:", err);
    alert("Eroare la încărcarea datelor detaliate");
  }
}

// ---------------- DOCUMENT READY ----------------
document.addEventListener("DOMContentLoaded", () => {
  initChart("salesChart", "Vânzări lunare", "#ffee00"); // lunar agregat
  initChart("salesChart2", "Vânzări zilnice", "#ffee00");
  initChart("sesiuniChart", "Sesiuni zilnice", "#ffee00");
  initChart("comenziConversieChart", "Comenzi vs Conversie", "#ffee00");

  loadVanzariTotale(); // grafic lunar agregat

  // Event listener-ul pentru selectLuna este deja adăugat în loadVanzariTotale()
  // Nu mai adăugăm unul duplicat aici
});