@extends('layouts.app')

@section('title', 'Profil Operator – VOLTA')

@section('content')
<script>
  if (typeof window.openVanzareModal === 'undefined') {
    window.openVanzareModal = function(luna) {
      console.warn('openVanzareModal: Funcția nu este încă completă. Așteptați încărcarea scriptului.');
    };
  }
</script>
<div class="operatori-detail-page">
  @if(session('success'))
  <div class="operatori-alert operatori-alert-success">
    <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
  </div>
  @endif
  @if(session('error'))
  <div class="operatori-alert operatori-alert-error">
    <i class="fas fa-exclamation-circle"></i><span>{{ session('error') }}</span>
  </div>
  @endif

  <a href="{{ route('operatori') }}" class="operatori-back">
    <i class="fas fa-arrow-left"></i> Înapoi la Operatori
  </a>

  <div class="operatori-cover operatori-cover--show" @if($operator->photo_coperta_url) style="background-image: url('{{ $operator->photo_coperta_url }}'); background-size: cover; background-position: center;" @endif>
    @auth
    <div class="operatori-cover-photo-actions">
      <form action="{{ route('operatori.photo.coperta', $operator->id) }}" method="post" enctype="multipart/form-data" class="operatori-photo-form">
        @csrf
        <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="operatori-photo-input" id="input-cover-{{ $operator->id }}" onchange="this.form.submit()">
        <label for="input-cover-{{ $operator->id }}" class="operatori-btn operatori-btn-ghost operatori-btn-photo"><i class="fas fa-camera"></i> {{ $operator->photo_coperta_url ? 'Schimbă coperta' : 'Adaugă copertă' }}</label>
      </form>
    </div>
    @endauth
    <div class="operatori-cover-inner">
      <div class="operatori-avatar-wrap">
        @if($operator->photo_profil_url)
        <div class="operatori-avatar operatori-avatar--img" style="background-image: url('{{ $operator->photo_profil_url }}');"></div>
        @else
        <div class="operatori-avatar">{{ strtoupper(mb_substr($operator->nume, 0, 1)) }}</div>
        @endif
        @auth
        <form action="{{ route('operatori.photo.profil', $operator->id) }}" method="post" enctype="multipart/form-data" class="operatori-photo-form operatori-avatar-form">
          @csrf
          <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="operatori-photo-input" id="input-profil-{{ $operator->id }}" onchange="this.form.submit()">
          <label for="input-profil-{{ $operator->id }}" class="operatori-btn operatori-btn-avatar-upload" title="Schimbă poza de profil"><i class="fas fa-camera"></i></label>
        </form>
        @endauth
      </div>
      <div class="operatori-detail-name-wrap">
        <h1 class="operatori-detail-title">{{ $operator->nume }}</h1>
        <div class="operatori-badges">
          @if($operator->data_angajare)
          <span class="operatori-badge">
            <i class="fas fa-calendar-alt"></i> Angajat din {{ $operator->data_angajare->format('d.m.Y') }}
          </span>
          @endif
          <span class="operatori-badge">
            <i class="fas fa-{{ $operator->activ ? 'check-circle' : 'times-circle' }}"></i>
            {{ $operator->activ ? 'Activ' : 'Inactiv' }}
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="operatori-detail-grid">
    <div class="operatori-sidebar-column">
      @php
        $lunaCurenta = now()->format('Y-m');
        $vanzariLunaCurenta = $vanzari->filter(function($v) use ($lunaCurenta) {
          return $v->data->format('Y-m') == $lunaCurenta;
        });
        $vanzariLunaCurentaSuma = $vanzariLunaCurenta->sum('suma_fara_tva');
        $vanzariLunaCurentaProfit = $vanzariLunaCurenta->sum('profit');
        $vanzariLunaCurentaCount = $vanzariLunaCurenta->count();
      @endphp
      @auth
      <div class="operatori-sidebar-card operatori-sidebar-card-photos">
        <h3 class="operatori-sidebar-title"><i class="fas fa-images"></i> Poze profil și copertă</h3>
        <p class="operatori-photos-hint">Încarcă poza de profil și/sau coperta. Doar operatorul sau administratorul pot actualiza.</p>
        <div class="operatori-photo-forms">
          <form action="{{ route('operatori.photo.profil', $operator->id) }}" method="post" enctype="multipart/form-data" class="operatori-photo-form-block">
            @csrf
            <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="operatori-photo-input" id="sidebar-profil-{{ $operator->id }}" onchange="this.form.submit()">
            <label for="sidebar-profil-{{ $operator->id }}" class="operatori-btn operatori-btn-photo-block"><i class="fas fa-user-circle"></i> Poza de profil</label>
          </form>
          <form action="{{ route('operatori.photo.coperta', $operator->id) }}" method="post" enctype="multipart/form-data" class="operatori-photo-form-block">
            @csrf
            <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="operatori-photo-input" id="sidebar-coperta-{{ $operator->id }}" onchange="this.form.submit()">
            <label for="sidebar-coperta-{{ $operator->id }}" class="operatori-btn operatori-btn-photo-block"><i class="fas fa-image"></i> Poza de copertă</label>
          </form>
        </div>
      </div>
      @endauth
      <div class="operatori-sidebar-card">
        <h3 class="operatori-sidebar-title"><i class="fas fa-calendar-check"></i> Luna Curentă</h3>
        <div class="operatori-stat-list">
          <div class="operatori-stat-row">
            <div class="operatori-stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div style="flex: 1;">
              <div class="operatori-stat-label">Vânzări (fără TVA)</div>
              <div class="operatori-stat-value">{{ number_format($vanzariLunaCurentaSuma, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--profit">
            <div class="operatori-stat-icon"><i class="fas fa-trophy"></i></div>
            <div style="flex: 1;">
              <div class="operatori-stat-label">Profit</div>
              <div class="operatori-stat-value">{{ number_format($vanzariLunaCurentaProfit, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          <div class="operatori-stat-row operatori-stat-row--neutral">
            <div class="operatori-stat-icon"><i class="fas fa-list"></i></div>
            <div style="flex: 1;">
              <div class="operatori-stat-label">Comenzi</div>
              <div class="operatori-stat-value">{{ $vanzariLunaCurentaCount }}</div>
            </div>
          </div>
          @if($vanzariLunaCurentaCount > 0)
          <div class="operatori-stat-row operatori-stat-row--neutral">
            <div class="operatori-stat-icon"><i class="fas fa-calculator"></i></div>
            <div style="flex: 1;">
              <div class="operatori-stat-label">Medie/Comandă</div>
              <div class="operatori-stat-value" style="font-size: 14px; font-weight: 500;">{{ number_format($vanzariLunaCurentaSuma / $vanzariLunaCurentaCount, 2, ',', '.') }} LEI</div>
            </div>
          </div>
          @endif
        </div>
      </div>

      @if($operator->observatii)
      <div class="operatori-sidebar-card operatori-notes-card">
        <h3 class="operatori-sidebar-title"><i class="fas fa-sticky-note"></i> Observații</h3>
        <p>{{ $operator->observatii }}</p>
      </div>
      @endif
    </div>

    <div class="operatori-main-feed">
      @if(isset($vanzariStats))
      <div class="operatori-content-card">
        <div class="operatori-card-header">
          <h2 class="operatori-section-title">
            <div class="operatori-section-icon"><i class="fas fa-chart-line"></i></div>
            Statistici Vânzări
          </h2>
        </div>
        <div class="kpi-cards-grid">
          <div class="kpi-card" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2);">
            <div class="kpi-label"><i class="fas fa-shopping-cart" style="margin-right: 6px;"></i>Total Vânzări</div>
            <div class="kpi-value">{{ $vanzariStats['total_vanzari'] }}</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="kpi-label"><i class="fas fa-money-bill-wave" style="margin-right: 6px;"></i>Suma (fără TVA)</div>
            <div class="kpi-value" style="font-size: 22px;">{{ number_format($vanzariStats['total_suma_fara_tva'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="kpi-label"><i class="fas fa-receipt" style="margin-right: 6px;"></i>Suma (cu TVA)</div>
            <div class="kpi-value" style="font-size: 22px;">{{ number_format($vanzariStats['total_suma_cu_tva'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(74, 222, 128, 0.1); border: 1px solid rgba(74, 222, 128, 0.2);">
            <div class="kpi-label"><i class="fas fa-trophy" style="margin-right: 6px;"></i>Total Profit</div>
            <div class="kpi-value" style="color: #10B981;">{{ number_format($vanzariStats['total_profit'], 2, ',', '.') }} LEI</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
            <div class="kpi-label"><i class="fas fa-list" style="margin-right: 6px;"></i>Număr Vânzări</div>
            <div class="kpi-value">{{ $vanzariStats['total_nr_vanzari'] }}</div>
          </div>
          <div class="kpi-card" style="background: rgba(255, 238, 0, 0.1); border: 1px solid rgba(255, 238, 0, 0.2);">
            <div class="kpi-label"><i class="fas fa-calendar-alt" style="margin-right: 6px;"></i>Medie/Lună</div>
            <div class="kpi-value" style="color: #FFEE00; font-size: 22px;">{{ number_format($vanzariStats['medie_vanzari_luna'], 2, ',', '.') }} LEI</div>
          </div>
        </div>
      </div>
      @endif

      @if(isset($vanzariLunare) && $vanzariLunare->count() > 0)
      <div class="operatori-content-card">
        <div class="operatori-card-header">
          <h2 class="operatori-section-title">
            <div class="operatori-section-icon"><i class="fas fa-chart-line"></i></div>
            Grafic Vânzări (fără TVA)
          </h2>
        </div>
        <div class="operatori-chart-wrap">
          <canvas id="vanzariChart"></canvas>
        </div>
      </div>

      <div class="operatori-content-card">
        <div class="operatori-card-header">
          <h2 class="operatori-section-title">
            <div class="operatori-section-icon"><i class="fas fa-calendar-alt"></i></div>
            Vânzări pe Luni
          </h2>
        </div>
        <div class="operatori-detail-table-wrap">
          <table id="vanzariLunareTable" class="operatori-detail-table">
            <thead>
              <tr>
                <th>Lună</th>
                <th class="tc">Comenzi</th>
                <th class="tc">Vânzări (fără TVA)</th>
                <th class="tc">Profit</th>
                <th class="tc">Număr Vânzări</th>
              </tr>
            </thead>
            <tbody>
              @foreach($vanzariLunare as $luna)
              <tr class="table-row-hover">
                <td class="row-text"><strong>{{ $luna->luna_label }}</strong></td>
                <td class="tc row-text">{{ $luna->comenzi }}</td>
                <td class="tc row-text">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} LEI</td>
                <td class="tc td-profit">{{ number_format($luna->profit, 2, ',', '.') }} LEI</td>
                <td class="tc row-text">{{ $luna->nr_vanzari }}</td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      @endif

      @if(isset($vanzariLunare))
      <div class="operatori-content-card">
        <div class="operatori-card-header">
          <h2 class="operatori-section-title">
            <div class="operatori-section-icon operatori-section-icon--yellow"><i class="fas fa-list-alt"></i></div>
            Vânzări pe Luni
          </h2>
          @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
          <div class="operatori-card-actions">
            <button type="button" onclick="openVanzareModal()" class="operatori-btn-primary">
              <i class="fas fa-plus"></i> Adaugă Vânzare
            </button>
            <a href="{{ route('operatori.upload', $operator->id) }}" class="operatori-btn-secondary">
              <i class="fas fa-file-excel"></i> Încarcă Excel
            </a>
          </div>
          @endif
        </div>
        <div class="operatori-detail-table-wrap">
          <table id="vanzariTable" class="operatori-detail-table operatori-table-yellow">
            <thead>
              <tr>
                <th>Lună</th>
                <th class="tc">Comenzi</th>
                <th class="tc">Vânzări (fără TVA)</th>
                <th class="tc">Profit</th>
                <th class="tc">Nr. Vânzări</th>
                @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
                <th class="tc">Acțiuni</th>
                @endif
              </tr>
            </thead>
            <tbody>
              @forelse($vanzariLunare as $luna)
              <tr class="table-row-hover">
                <td class="row-text"><strong>{{ $luna->luna_label }}</strong></td>
                <td class="tc row-text">{{ $luna->comenzi }}</td>
                <td class="tc row-text">{{ number_format($luna->vanzari_luna, 2, ',', '.') }} LEI</td>
                <td class="tc td-profit">{{ number_format($luna->profit, 2, ',', '.') }} LEI</td>
                <td class="tc row-text">{{ $luna->nr_vanzari }}</td>
                @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
                <td class="tc">
                  <div class="operatori-table-actions">
                    <button type="button" onclick="openVanzareModal('{{ $luna->luna }}')" class="operatori-btn-icon"><i class="fas fa-edit"></i></button>
                    <button type="button" onclick="deleteVanzareLuna('{{ $luna->luna }}')" class="operatori-btn-icon operatori-btn-icon--danger"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
                @endif
              </tr>
              @empty
              <tr>
                <td colspan="{{ auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator') ? '6' : '5' }}" style="padding: 40px; text-align: center; color: #9CA3AF;">
                  <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 16px; opacity: 0.5; display: block;"></i>
                  Nu există vânzări înregistrate
                </td>
              </tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
      @endif
    </div>
  </div>
</div>

@if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
<div id="vanzareModal" class="operatori-modal-overlay">
  <div class="operatori-modal">
    <button type="button" onclick="closeVanzareModal()" class="operatori-modal-close" aria-label="Închide">
      <i class="fas fa-times"></i>
    </button>
    <h2 class="operatori-modal-title">
      <div class="operatori-modal-title-icon"><i class="fas fa-plus"></i></div>
      <span id="modalTitle">Adaugă Vânzare</span>
    </h2>
    <form id="vanzareForm" method="POST" action="{{ route('operatori.vanzari.store', $operator->id) }}">
      @csrf
      <input type="hidden" id="vanzareId" name="luna_id">
      <input type="hidden" id="formMethod" name="_method" value="POST">
      <div class="operatori-form-group">
        <label class="operatori-form-label" for="vanzareLuna">Lună *</label>
        <input type="month" name="luna" id="vanzareLuna" required class="operatori-form-input" placeholder="">
      </div>
      <div class="operatori-form-group">
        <label class="operatori-form-label" for="vanzareSumaFaraTva">Suma (fără TVA) *</label>
        <input type="number" name="suma_fara_tva" id="vanzareSumaFaraTva" step="0.01" min="0" required class="operatori-form-input" placeholder="0.00">
      </div>
      <div class="operatori-form-group">
        <label class="operatori-form-label" for="vanzareSumaCuTva">Suma (cu TVA) *</label>
        <input type="number" name="suma_cu_tva" id="vanzareSumaCuTva" step="0.01" min="0" required class="operatori-form-input" placeholder="0.00">
      </div>
      <div class="operatori-form-group">
        <label class="operatori-form-label" for="vanzareProfit">Profit *</label>
        <input type="number" name="profit" id="vanzareProfit" step="0.01" required class="operatori-form-input" placeholder="0.00">
      </div>
      <div class="operatori-form-group">
        <label class="operatori-form-label" for="vanzareNrVanzari">Număr Vânzări</label>
        <input type="number" name="nr_vanzari" id="vanzareNrVanzari" min="0" value="1" class="operatori-form-input" placeholder="1">
      </div>
      <div class="operatori-form-actions">
        <button type="submit" class="operatori-btn-primary"><i class="fas fa-save"></i> Salvează</button>
        <button type="button" onclick="closeVanzareModal()" class="operatori-btn-ghost">Anulează</button>
      </div>
    </form>
  </div>
</div>
@endif

@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ url('js/operatori-det.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const rows = document.querySelectorAll('.table-row-hover');
  rows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(255, 238, 0, 0.15)';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#000';
        }
      });
    });
    row.addEventListener('mouseleave', function() {
      this.style.background = 'transparent';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '';
        }
      });
    });
  });

  // Grafic Vânzări fără TVA
  @if(isset($vanzariLunare) && $vanzariLunare->count() > 0)
  const vanzariChartCanvas = document.getElementById('vanzariChart');
  if (vanzariChartCanvas) {
    const ctx = vanzariChartCanvas.getContext('2d');
    @php
      $vanzariLunareArray = [];
      if (isset($vanzariLunare) && $vanzariLunare->count() > 0) {
        $vanzariLunareArray = $vanzariLunare->reverse()->values()->all();
      }
    @endphp
    const vanzariLunareJson = @json($vanzariLunareArray);
    
    // Debug
    console.log('vanzariLunareJson:', vanzariLunareJson);
    console.log('Type:', typeof vanzariLunareJson);
    console.log('Is Array:', Array.isArray(vanzariLunareJson));
    
    // Convertim în array sigur
    let vanzariLunareArray = [];
    if (Array.isArray(vanzariLunareJson)) {
      vanzariLunareArray = vanzariLunareJson;
    } else if (vanzariLunareJson && typeof vanzariLunareJson === 'object') {
      vanzariLunareArray = Object.values(vanzariLunareJson);
    }
    
    console.log('vanzariLunareArray:', vanzariLunareArray);
    console.log('Is Array:', Array.isArray(vanzariLunareArray));
    
    if (!Array.isArray(vanzariLunareArray) || vanzariLunareArray.length === 0) {
      console.warn('Nu există date pentru grafic');
      return;
    }
    
    const labels = vanzariLunareArray.map(function(v) { return v?.luna_label || ''; });
    const data = vanzariLunareArray.map(function(v) { return parseFloat(v?.vanzari_luna) || 0; });
    
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vanzari (fara TVA)',
          data: data,
          borderColor: '#3b82f6',
          backgroundColor: 'rgba(59, 130, 246, 0.1)',
          borderWidth: 3,
          tension: 0.4,
          fill: true,
          pointRadius: window.innerWidth <= 768 ? 2 : 5,
          pointBackgroundColor: '#3b82f6',
          pointBorderColor: '#fff',
          pointBorderWidth: window.innerWidth <= 768 ? 1 : 2,
          pointHoverRadius: window.innerWidth <= 768 ? 4 : 7,
          pointHoverBackgroundColor: '#2563eb',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 3
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            labels: {
              color: '#fff',
              font: {
                size: window.innerWidth <= 768 ? 10 : 14,
                weight: 'bold'
              },
              padding: window.innerWidth <= 768 ? 8 : 15,
              usePointStyle: true,
              boxWidth: window.innerWidth <= 768 ? 8 : 12,
              boxHeight: window.innerWidth <= 768 ? 8 : 12
            },
            display: true,
            position: 'top'
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.9)',
            titleColor: '#3b82f6',
            bodyColor: '#fff',
            borderColor: '#3b82f6',
            borderWidth: 1,
            padding: 12,
            titleFont: {
              size: 14,
              weight: 'bold'
            },
            bodyFont: {
              size: 13
            },
            cornerRadius: 8,
            callbacks: {
              label: function(context) {
                return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', {
                  minimumFractionDigits: 2,
                  maximumFractionDigits: 2
                }).format(context.parsed.y) + ' LEI';
              }
            }
          }
        },
        scales: {
          x: {
            ticks: {
              color: '#fff',
              font: {
                size: window.innerWidth <= 768 ? 9 : 12
              }
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.05)'
            }
          },
          y: {
            ticks: {
              color: '#fff',
              font: {
                size: 12
              },
              callback: function(value) {
                return new Intl.NumberFormat('ro-RO', {
                  minimumFractionDigits: 0,
                  maximumFractionDigits: 0
                }).format(value) + ' LEI';
              }
            },
            grid: {
              color: 'rgba(255, 255, 255, 0.05)'
            },
            beginAtZero: true
          }
        }
      }
    });
  }
  @endif
});
</script>

<script>
  // Funcții pentru modal vânzări (pe luni)
  @php
    $isAdmin = auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator');
  @endphp
  
  // Definește variabila de date
  @if($isAdmin)
  const vanzariLunareData = @json($vanzariLunareForJs ?? []);
  @else
  const vanzariLunareData = {};
  @endif

  // Definește funcția - asigură-te că este disponibilă global
  window.openVanzareModal = function(luna = null) {
    const modal = document.getElementById('vanzareModal');
    if (!modal) {
      console.error('Modal vanzareModal nu a fost gasit');
      return;
    }
    
    const form = document.getElementById('vanzareForm');
    const title = document.getElementById('modalTitle');
    const methodInput = document.getElementById('formMethod');
    const idInput = document.getElementById('vanzareId');
    
    if (!form || !title || !methodInput || !idInput) {
      console.error('Elemente necesare pentru modal nu au fost gasite');
      return;
    }
    
    // Reset form
    form.reset();
    form.action = '{{ route("operatori.vanzari.store", $operator->id) }}';
    methodInput.value = 'POST';
    idInput.value = '';
    title.textContent = 'Adauga Vanzare';
    
    // Dacă edităm o vânzare existentă
    if (luna && vanzariLunareData && vanzariLunareData[luna]) {
      const vanzare = vanzariLunareData[luna];
      const lunaInput = document.getElementById('vanzareLuna');
      const sumaFaraTvaInput = document.getElementById('vanzareSumaFaraTva');
      const sumaCuTvaInput = document.getElementById('vanzareSumaCuTva');
      const profitInput = document.getElementById('vanzareProfit');
      const nrVanzariInput = document.getElementById('vanzareNrVanzari');
      
      if (lunaInput) lunaInput.value = vanzare.luna || '';
      if (sumaFaraTvaInput) sumaFaraTvaInput.value = vanzare.suma_fara_tva || '';
      if (sumaCuTvaInput) sumaCuTvaInput.value = vanzare.suma_cu_tva || '';
      if (profitInput) profitInput.value = vanzare.profit || '';
      if (nrVanzariInput) nrVanzariInput.value = vanzare.nr_vanzari || 1;
      
      form.action = '{{ route("operatori.vanzari.update", [$operator->id, ":luna"]) }}'.replace(':luna', luna);
      methodInput.value = 'PUT';
      idInput.value = luna;
      title.textContent = 'Editeaza Vanzare';
    } else {
      // Setează luna curentă ca default
      const today = new Date();
      const currentMonth = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0');
      const lunaInput = document.getElementById('vanzareLuna');
      if (lunaInput) {
        lunaInput.value = currentMonth;
      }
    }
    
    modal.style.display = 'flex';
  };

  window.closeVanzareModal = function() {
    const modal = document.getElementById('vanzareModal');
    if (modal) {
      modal.style.display = 'none';
    }
  };

  window.deleteVanzareLuna = function(luna) {
    if (!confirm('Esti sigur ca vrei sa stergi vanzarile pentru aceasta luna?')) {
      return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("operatori.vanzari.destroy", [$operator->id, ":luna"]) }}'.replace(':luna', luna);
    
    const methodInput = document.createElement('input');
    methodInput.type = 'hidden';
    methodInput.name = '_method';
    methodInput.value = 'DELETE';
    form.appendChild(methodInput);
    
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = '{{ csrf_token() }}';
    form.appendChild(csrfInput);
    
    document.body.appendChild(form);
    form.submit();
  };

  // Închide modal la click pe fundal și ESC
  document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('vanzareModal');
    if (modal) {
      modal.addEventListener('click', function(e) {
        if (e.target === this) {
          closeVanzareModal();
        }
      });
    }

    // Închide modal cu ESC
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        const modal = document.getElementById('vanzareModal');
        if (modal && modal.style.display === 'flex') {
          closeVanzareModal();
        }
      }
    });
  });
</script>
@endpush
