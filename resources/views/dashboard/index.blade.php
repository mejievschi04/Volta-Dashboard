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
  <div class="card{{ $isAdmin ? ' editable-plan' : '' }}">
    <h4>
      Plan luna curentă
      @if($isAdmin)
        <i class="fas fa-edit edit-icon" title="Click pentru a edita"></i>
      @endif
    </h4>
    <div class="value" id="plan-luna">-</div>
  </div>
  <div class="card">
    <h4>Vânzări luna curentă</h4>
    <div class="value" id="vanzari-luna">-</div>
  </div>
  <div class="card">
    <h4>Progres plan</h4>
    <div class="value" id="progres-plan">-</div>
  </div>
  <div class="card">
    <h4>Diferență față de plan</h4>
    <div class="value" id="diferenta-plan">-</div>
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
    <h4>Profit</h4>
    <div class="value" id="profit">-</div>
  </div>
  <div class="card">
    <h4>Vânzări cu TVA</h4>
    <div class="value" id="vanzari-cu-tva">-</div>
  </div>
</div>

<!-- GRAFICE -->
<div class="charts-grid">
  <div class="chart-container">
    <h2><i class="fas fa-chart-line" style="margin-right: 8px;"></i>Grafic lunar</h2>
    <div class="chart-wrapper">
      <canvas id="salesChart" style="cursor: pointer;" title="Click pentru detalii vânzări"></canvas>
    </div>
  </div>
  <div class="chart-container">
    <h2><i class="fas fa-chart-area" style="margin-right: 8px;"></i>Vânzări zilnice</h2>
    <div class="chart-wrapper">
      <canvas id="salesChart2"></canvas>
    </div>
  </div>
  <div class="chart-container">
    <h2><i class="fas fa-chart-bar" style="margin-right: 8px;"></i>Comenzi vs Conversie</h2>
    <div class="chart-wrapper">
      <canvas id="comenziConversieChart"></canvas>
    </div>
  </div>
  <div class="chart-container">
    <h2><i class="fas fa-network-wired" style="margin-right: 8px;"></i>Sesiuni zilnice</h2>
    <div class="chart-wrapper">
      <canvas id="sesiuniChart"></canvas>
    </div>
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
function initChart(chartId, label, color="#FFEE00") {
  const canvas = document.getElementById(chartId);
  if(!canvas) return;
  const ctx = canvas.getContext("2d");
  const chartInstance = new Chart(ctx, {
    type: "line",
    data: { labels: [], datasets: [{ label, data: [], borderColor: color, backgroundColor: `${color}33`, tension: 0.3, pointRadius: window.innerWidth <= 768 ? 1 : 2 }] },
    options: { responsive: true, plugins: { legend: { display: true, labels: { color: "#fff", font: { size: window.innerWidth <= 768 ? 10 : 12 } } } }, scales: { x: { ticks: { color: "#fff", font: { size: window.innerWidth <= 768 ? 9 : 11 } }, grid: { color: "rgba(255,255,0,0.05)" } }, y: { ticks: { color: "#fff", font: { size: window.innerWidth <= 768 ? 9 : 11 } }, grid: { color: "rgba(255,255,0,0.05)" }, beginAtZero: true } } }
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
      throw new Error(dataLunare.error || "Eroare la încărcarea datelor");
    }

    selectLuna.innerHTML = '';
    
    // Debug: logăm toate lunile primite
    console.log('=== DEBUG LUNI ===');
    console.log('Număr total luni primite de la API:', dataLunare.luni.length);
    console.log('Toate lunile primite:', dataLunare.luni);
    console.log('Primele 5 luni:', dataLunare.luni.slice(0, 5));
    console.log('Ultimele 5 luni:', dataLunare.luni.slice(-5));
    
    // Sortăm lunile în ordine descrescătoare (ultimele primele)
    const luniSortate = [...dataLunare.luni].sort((a, b) => {
      // Comparăm valorile YYYY-MM
      if (b.value > a.value) return 1;
      if (b.value < a.value) return -1;
      return 0;
    });
    
    console.log('Luni sortate (descrescător):', luniSortate);
    
    luniSortate.forEach((luna, index) => {
      const opt = document.createElement("option");
      opt.value = luna.value;
      opt.textContent = luna.label;
      selectLuna.appendChild(opt);
      
      // Log pentru primele și ultimele 3 opțiuni
      if (index < 3 || index >= luniSortate.length - 3) {
        console.log(`Opțiune ${index + 1}: ${luna.value} - ${luna.label}`);
      }
    });
    
    console.log('Total opțiuni adăugate în selector:', selectLuna.options.length);
    console.log('Toate opțiunile din selector:', Array.from(selectLuna.options).map(opt => opt.value + ' - ' + opt.text));
    console.log('=== END DEBUG ===');

    // Grafic lunar agregat
    const labels = dataLunare.data.map(d => d.luna);
    const vanzari = dataLunare.data.map(d => d.vanzari);
    const plan = dataLunare.data.map(d => d.plan || null);

    destroyChart("salesChart");
    const ctx = document.getElementById("salesChart").getContext("2d");
    charts["salesChart"].instance = new Chart(ctx, {
      data: {
        labels,
        datasets: [
          {
            type: "line",
            label: "Plan",
            data: plan,
            borderColor: "#EF4444",
            backgroundColor: "rgba(239, 68, 68, 0.1)",
            borderWidth: 3,
            tension: 0.3,
            pointRadius: window.innerWidth <= 768 ? 2 : 5,
            pointBackgroundColor: "#EF4444",
            pointBorderColor: "#ffffff",
            pointBorderWidth: 2,
            fill: false,
            order: 1
          },
          {
            type: "bar",
            label: "Vânzări reale",
            data: vanzari,
            backgroundColor: "rgba(255, 238, 0, 0.7)",
            borderColor: "#ffee00",
            borderWidth: 2,
            borderRadius: 6,
            order: 2
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: {
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 12 : 14 },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true
            },
            display: true,
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(31, 41, 55, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            borderColor: '#FFEE00',
            borderWidth: 1,
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 8,
            displayColors: true
          }
        },
        scales: {
          x: {
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 12 },
              maxRotation: window.innerWidth <= 768 ? 45 : 0,
              minRotation: window.innerWidth <= 768 ? 45 : 0
            },
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            }
          },
          y: {
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 11 : 12 }
            },
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            },
            beginAtZero: true
          }
        },
        onClick: (event, elements) => {
          if (elements.length > 0) {
            const element = elements[0];
            const luna = labels[element.index];
            openVanzariDetaliiModal(luna);
          }
        },
        onHover: (event, activeElements) => {
          event.native.target.style.cursor = activeElements.length > 0 ? 'pointer' : 'default';
        }
      }
    });

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
        { id: 'vanzari-cu-tva', value: formatValue(kpiData.vanzari_cu_tva || 0, 'MDL') }
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

    // Funcționalitate editare inline pentru plan (doar admini)
    @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
    function initPlanEdit() {
      const planCard = document.querySelector('.editable-plan');
      const planValue = document.getElementById('plan-luna');
      
      if (!planCard || !planValue) return;
      
      // Evită adăugarea multiplă a event listener-ului
      if (planCard.dataset.editInitialized === 'true') return;
      planCard.dataset.editInitialized = 'true';
      
      let isEditing = false;
      
      planCard.addEventListener('click', function(e) {
        // Nu permite editarea dacă se dă click pe icon sau pe header
        if (e.target.classList.contains('edit-icon') || e.target.closest('h4')) return;
        
        // Permite editarea dacă se dă click pe elementul .value sau pe card
        if (!isEditing) {
          const clickedValue = e.target.closest('.value');
          if (clickedValue === planValue || e.target === planValue) {
            startEditing();
          }
        }
      });
      
      function startEditing() {
        if (isEditing) return;
        isEditing = true;
        
        const currentValue = parseFloat(planValue.getAttribute('data-raw-value') || 0);
        const currentMonth = planValue.getAttribute('data-current-month') || selectLuna.value;
        
        // Creează input field
        const input = document.createElement('input');
        input.type = 'number';
        input.step = '0.01';
        input.min = '0';
        input.value = currentValue;
        input.style.cssText = `
          width: 100%;
          background: rgba(31, 41, 55, 0.8);
          border: 2px solid #FFEE00;
          border-radius: 8px;
          padding: 12px;
          color: #FFFFFF;
          font-size: 32px;
          font-weight: 700;
          text-align: center;
          outline: none;
          font-family: inherit;
        `;
        
        const originalHTML = planValue.innerHTML;
        planValue.innerHTML = '';
        planValue.appendChild(input);
        input.focus();
        input.select();
        
        // Salvează la Enter sau blur
        const save = async () => {
          const newValue = parseFloat(input.value) || 0;
          if (newValue < 0) {
            alert('Valoarea trebuie să fie pozitivă!');
            input.focus();
            return;
          }
          
          try {
            const response = await fetch('{{ route("api.kpi.plan.update") }}', {
              method: 'PUT',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: JSON.stringify({
                month: currentMonth,
                valoare: newValue
              })
            });
            
            const result = await response.json();
            
            if (result.success) {
              // Reîncarcă KPI-urile pentru a actualiza toate valorile
              await updateKPIandChart(currentMonth);
              planCard.style.borderColor = '#10B981';
              setTimeout(() => {
                planCard.style.borderColor = '';
              }, 2000);
            } else {
              alert('Eroare: ' + (result.error || 'Nu s-a putut actualiza planul.'));
              planValue.innerHTML = originalHTML;
            }
          } catch (error) {
            console.error('Eroare la salvare:', error);
            alert('Eroare la salvare: ' + error.message);
            planValue.innerHTML = originalHTML;
          }
          
          isEditing = false;
        };
        
        // Anulează la Escape
        const cancel = () => {
          planValue.innerHTML = originalHTML;
          isEditing = false;
        };
        
        input.addEventListener('blur', save);
        input.addEventListener('keydown', (e) => {
          if (e.key === 'Enter') {
            e.preventDefault();
            save();
          } else if (e.key === 'Escape') {
            e.preventDefault();
            cancel();
          }
        });
      }
    }
    
    // Inițializează editarea după încărcarea paginii
    document.addEventListener('DOMContentLoaded', function() {
      setTimeout(initPlanEdit, 500);
    });
    @endif

    if (!selectLunaListenerAdded) {
      selectLuna.addEventListener("change", async () => {
        const luna = selectLuna.value;
        await updateKPIandChart(luna);
        loadVanzariZilniceByLuna(luna);
        loadSesiuniZilniceByLuna(luna);
        loadRaportComenziSesiuniByLuna(luna);
      });
      selectLunaListenerAdded = true;
    }

    if(dataLunare.luni.length) {
      const ultimaLuna = dataLunare.luni[dataLunare.luni.length-1].value;
      selectLuna.value = ultimaLuna;
      selectLuna.dispatchEvent(new Event("change"));
    }

  } catch(err) {
    console.error("Eroare la încărcarea datelor:", err);
  }
}

// ---------------- LOAD ZILNIC ---------------- 
async function loadVanzariZilniceByLuna(luna) {
  try {
    const res = await fetch(`{{ route('api.vanzari.zilnice') }}?month=${luna}`);
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("salesChart2");
    const ctx = document.getElementById("salesChart2").getContext("2d");
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
            pointRadius: window.innerWidth <= 768 ? 1.5 : 3,
          pointBackgroundColor: "#ffee00" 
        }] 
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 12 : 14 },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true
            },
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(31, 41, 55, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            borderColor: '#FFEE00',
            borderWidth: 1,
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 8
          }
        }, 
        scales: { 
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 12 },
              maxRotation: window.innerWidth <= 768 ? 45 : 0,
              minRotation: window.innerWidth <= 768 ? 45 : 0
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            } 
          }, 
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 11 : 12 }
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            }, 
            beginAtZero: true 
          } 
        } 
      }
    });

  } catch(err){ console.error("Eroare la graficul zilnic:", err); }
}

// ---------------- LOAD SESIUNI ---------------- 
async function loadSesiuniZilniceByLuna(luna) {
  try {
    const res = await fetch(`{{ route('api.sesiuni') }}?month=${luna}`);
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("sesiuniChart");
    const ctx = document.getElementById("sesiuniChart").getContext("2d");
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
            pointRadius: window.innerWidth <= 768 ? 1.5 : 3,
          pointBackgroundColor: "#ffee00" 
        }] 
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 12 : 14 },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true
            },
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(31, 41, 55, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            borderColor: '#FFEE00',
            borderWidth: 1,
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 8
          }
        }, 
        scales: { 
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 12 },
              maxRotation: window.innerWidth <= 768 ? 45 : 0,
              minRotation: window.innerWidth <= 768 ? 45 : 0
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            } 
          }, 
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 11 : 12 }
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            }, 
            beginAtZero: true 
          } 
        } 
      }
    });

  } catch(err){ console.error("Eroare la graficul Sesiuni:", err); }
}

// ---------------- LOAD COMENZI VS CONVERSIE ---------------- 
async function loadRaportComenziSesiuniByLuna(luna) {
  try {
    const res = await fetch(`{{ route('api.comenzi.conversie') }}?month=${luna}`);
    const data = await res.json();
    
    if (!data.success) {
      throw new Error(data.error || "Eroare la încărcarea datelor");
    }

    destroyChart("comenziConversieChart");
    const ctx = document.getElementById("comenziConversieChart").getContext("2d");
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
            pointRadius: window.innerWidth <= 768 ? 1 : 2,
            yAxisID: 'y1'
          }
        ]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
          mode: 'index',
          intersect: false,
        },
        plugins: { 
          legend: { 
            labels: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 12 : 14 },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true
            },
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(31, 41, 55, 0.9)',
            titleColor: '#FFEE00',
            bodyColor: '#fff',
            borderColor: '#FFEE00',
            borderWidth: 1,
            padding: 12,
            titleFont: { size: 14, weight: 'bold' },
            bodyFont: { size: 13 },
            cornerRadius: 8,
            displayColors: true
          }
        }, 
        scales: { 
          x: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 9 : 12 },
              maxRotation: window.innerWidth <= 768 ? 45 : 0,
              minRotation: window.innerWidth <= 768 ? 45 : 0
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            } 
          }, 
          y: { 
            ticks: { 
              color: "#fff",
              font: { size: window.innerWidth <= 768 ? 11 : 12 }
            }, 
            grid: { 
              color: "rgba(255,255,0,0.05)",
              drawBorder: false
            }, 
            beginAtZero: true 
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            ticks: { 
              color: "#EF4444",
              font: { size: window.innerWidth <= 768 ? 9 : 12 }
            },
            grid: { drawOnChartArea: false }
          }
        } 
      }
    });

  } catch(err){ console.error("Eroare la grafic Comenzi vs Conversie:", err); }
}

// ---------------- MODAL VANZARI DETALII ---------------- 
async function openVanzariDetaliiModal(luna) {
  try {
    const res = await fetch(`{{ route('api.vanzari.detalii') }}?month=${luna}`);
    const data = await res.json();
    
    if (!data.success) {
      alert("Eroare la încărcarea datelor: " + (data.error || "Eroare necunoscută"));
      return;
    }
    
    const modal = document.createElement('div');
    modal.id = 'vanzariDetaliiModal';
    const isMobile = window.innerWidth <= 768;
    modal.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(17, 24, 39, 0.9);
      display: flex;
      justify-content: center;
      align-items: ${isMobile ? 'flex-start' : 'center'};
      z-index: 10000;
      padding: ${isMobile ? '10px' : '20px'};
      overflow-y: auto;
      -webkit-overflow-scrolling: touch;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.style.cssText = `
      background: #111827;
      border-radius: ${isMobile ? '16px' : '12px'};
      padding: ${isMobile ? '20px 16px' : '30px'};
      max-width: ${isMobile ? '100%' : '900px'};
      width: 100%;
      max-height: ${isMobile ? '95vh' : '90vh'};
      overflow-y: auto;
      color: #fff;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
      margin-top: ${isMobile ? '10px' : '0'};
    `;
    
    const monthName = new Date(luna + '-01').toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
    
    const gridCols = isMobile ? '1fr' : 'repeat(3, 1fr)';
    const cardPadding = isMobile ? '16px' : '20px';
    const titleSize = isMobile ? '18px' : '24px';
    const valueSize = isMobile ? '20px' : '24px';
    const headerFlex = isMobile ? 'column' : 'row';
    const headerGap = isMobile ? '12px' : '0';
    
    modalContent.innerHTML = `
      <div style="display: flex; flex-direction: ${headerFlex}; justify-content: space-between; align-items: ${isMobile ? 'flex-start' : 'center'}; gap: ${headerGap}; margin-bottom: ${isMobile ? '16px' : '20px'};">
        <h2 style="margin: 0; color: #ffee00; font-size: ${titleSize}; line-height: 1.3;">Detalii Vânzări - ${monthName}</h2>
        <button id="closeModal" style="
          background: #EF4444;
          color: white;
          border: none;
          padding: ${isMobile ? '10px 16px' : '10px 20px'};
          border-radius: 8px;
          cursor: pointer;
          font-size: ${isMobile ? '14px' : '16px'};
          font-weight: 600;
          min-width: ${isMobile ? 'auto' : '100px'};
          align-self: ${isMobile ? 'flex-end' : 'auto'};
        ">✕ ${isMobile ? '' : 'Închide'}</button>
      </div>
      
      <div style="display: grid; grid-template-columns: ${gridCols}; gap: ${isMobile ? '12px' : '15px'}; margin-bottom: ${isMobile ? '20px' : '25px'};">
        <div style="background: #1F2937; padding: ${cardPadding}; border-radius: 8px; text-align: center;">
          <div style="color: #9CA3AF; font-size: ${isMobile ? '12px' : '14px'}; margin-bottom: ${isMobile ? '6px' : '8px'};">Total fără TVA</div>
          <div style="color: #ffee00; font-size: ${valueSize}; font-weight: 700; word-break: break-word;">${formatNumber(data.total_fara_tva)} MDL</div>
        </div>
        <div style="background: #1F2937; padding: ${cardPadding}; border-radius: 8px; text-align: center;">
          <div style="color: #9CA3AF; font-size: ${isMobile ? '12px' : '14px'}; margin-bottom: ${isMobile ? '6px' : '8px'};">Total cu TVA</div>
          <div style="color: #ffee00; font-size: ${valueSize}; font-weight: 700; word-break: break-word;">${formatNumber(data.total_cu_tva)} MDL</div>
        </div>
        <div style="background: #1F2937; padding: ${cardPadding}; border-radius: 8px; text-align: center;">
          <div style="color: #9CA3AF; font-size: ${isMobile ? '12px' : '14px'}; margin-bottom: ${isMobile ? '6px' : '8px'};">Total Profit</div>
          <div style="color: #ffee00; font-size: ${valueSize}; font-weight: 700; word-break: break-word;">${formatNumber(data.total_profit)} MDL</div>
        </div>
      </div>
      
      <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
        <table style="width: 100%; border-collapse: collapse; min-width: ${isMobile ? '600px' : 'auto'};">
          <thead>
            <tr style="background: #1F2937;">
              <th style="padding: ${isMobile ? '10px 8px' : '12px'}; text-align: left; border-bottom: 2px solid #ffee00; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">Data</th>
              <th style="padding: ${isMobile ? '10px 8px' : '12px'}; text-align: right; border-bottom: 2px solid #ffee00; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">Fără TVA</th>
              <th style="padding: ${isMobile ? '10px 8px' : '12px'}; text-align: right; border-bottom: 2px solid #ffee00; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">Cu TVA</th>
              <th style="padding: ${isMobile ? '10px 8px' : '12px'}; text-align: right; border-bottom: 2px solid #ffee00; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">Profit</th>
            </tr>
          </thead>
          <tbody>
            ${data.data.map(row => `
              <tr style="border-bottom: 1px solid #9CA3AF;">
                <td style="padding: ${isMobile ? '10px 8px' : '10px'}; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">${row.data}</td>
                <td style="padding: ${isMobile ? '10px 8px' : '10px'}; text-align: right; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">${formatNumber(row.fara_tva)} MDL</td>
                <td style="padding: ${isMobile ? '10px 8px' : '10px'}; text-align: right; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">${formatNumber(row.cu_tva)} MDL</td>
                <td style="padding: ${isMobile ? '10px 8px' : '10px'}; text-align: right; color: #ffee00; font-size: ${isMobile ? '13px' : '14px'}; white-space: nowrap;">${formatNumber(row.profit)} MDL</td>
              </tr>
            `).join('')}
          </tbody>
        </table>
      </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    document.getElementById('closeModal').addEventListener('click', () => {
      document.body.removeChild(modal);
    });
    
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        document.body.removeChild(modal);
      }
    });
    
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
  initChart("salesChart", "Vânzări lunare", "#ffee00");
  initChart("salesChart2", "Vânzări zilnice", "#ffee00");
  initChart("sesiuniChart", "Sesiuni zilnice", "#ffee00");
  initChart("comenziConversieChart", "Comenzi vs Conversie", "#ffee00");

  loadVanzariTotale();
});
</script>
@endpush

