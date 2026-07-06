@extends('layouts.operator')

@section('title', 'Datele mele – VOLTA STATS')

@section('content')
@if(session('success'))
<div class="operator-me-flash operator-me-flash--success">
  <i class="fas fa-check-circle" aria-hidden="true"></i>
  <span>{{ session('success') }}</span>
</div>
@endif

@php
  $luniNumeKpi = [
    1 => 'Ianuarie', 2 => 'Februarie', 3 => 'Martie', 4 => 'Aprilie',
    5 => 'Mai', 6 => 'Iunie', 7 => 'Iulie', 8 => 'August',
    9 => 'Septembrie', 10 => 'Octombrie', 11 => 'Noiembrie', 12 => 'Decembrie',
  ];
  $lunaCurentaKpi = now()->format('Y-m');
  $lunaCurentaLabel = ($luniNumeKpi[(int) now()->format('n')] ?? '') . ' ' . now()->format('Y');
@endphp

<section class="operator-team-kpi" aria-label="KPI echipă">
  <div class="operator-team-kpi-header">
    <div class="operator-team-kpi-heading">
      <h2><i class="fas fa-users" aria-hidden="true"></i> KPI echipă</h2>
      <p class="operator-team-kpi-subtitle">Performanța întregii echipe — luna curentă</p>
    </div>
    <span class="operator-team-kpi-badge" id="operator-kpi-month-label">{{ $lunaCurentaLabel }}</span>
  </div>
  <div class="kpi-grid operator-team-kpi-grid">
    <div class="card">
      <h4>Vânzări fără TVA</h4>
      <div class="value" id="operator-vanzari-luna">-</div>
    </div>
    <div class="card">
      <h4>Plan luna curentă</h4>
      <div class="value" id="operator-plan-luna">-</div>
    </div>
    <div class="card">
      <h4>Progres plan</h4>
      <div class="value" id="operator-progres-plan">-</div>
    </div>
    <div class="card">
      <h4>Prognoză plan</h4>
      <div class="value" id="operator-prognoza-plan">-</div>
    </div>
    <div class="card">
      <h4>Vânzări/zi pentru plan</h4>
      <div class="value" id="operator-vanzari-zi-plan">-</div>
    </div>
  </div>
</section>

@if($date)
  @php
    $lunaCurenta = now()->format('Y-m');
    $lunaCurentaData = $vanzariLunare1c->firstWhere('luna', $lunaCurenta);
    $heroCover = (isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_coperta_url)
      ? $operatorRecord->photo_coperta_url
      : null;
    $heroAvatar = (isset($operatorRecord) && $operatorRecord && $operatorRecord->photo_profil_url)
      ? $operatorRecord->photo_profil_url
      : null;

    $monthStats = [
      ['icon' => 'fa-shopping-cart', 'accent' => 'blue', 'label' => 'Vânzări (fără TVA)', 'value' => ($lunaCurentaData ? number_format($lunaCurentaData->vanzari_luna, 2, ',', '.') : '0,00') . ' MDL'],
      ['icon' => 'fa-trophy', 'accent' => 'green', 'label' => 'Profit', 'value' => ($lunaCurentaData ? number_format($lunaCurentaData->profit, 2, ',', '.') : '0,00') . ' MDL'],
      ['icon' => 'fa-list', 'accent' => 'brand', 'label' => 'Comenzi', 'value' => (string) ($lunaCurentaData ? (int) $lunaCurentaData->comenzi : 0)],
      ['icon' => 'fa-truck', 'accent' => 'orange', 'label' => 'Livrări (luna curentă)', 'value' => (string) ($nrLivrariLunaCurenta ?? 0)],
      ['icon' => 'fa-store', 'accent' => 'violet', 'label' => 'Pick-up (luna curentă)', 'value' => (string) ($pickupLunaCurenta ?? 0)],
    ];

    $totalStats = [
      ['accent' => 'blue', 'label' => 'Vânzări fără TVA', 'value' => number_format($date['vanzari_fara_tva'], 2, ',', '.') . ' MDL'],
      ['accent' => 'slate', 'label' => 'Vânzări cu TVA', 'value' => number_format($date['vanzari_cu_tva'], 2, ',', '.') . ' MDL'],
      ['accent' => 'green', 'label' => 'Profit', 'value' => number_format($date['profit'], 2, ',', '.') . ' MDL'],
      ['accent' => 'brand', 'label' => 'Comenzi', 'value' => number_format($date['nr_comenzi'], 0, ',', '.')],
      ['accent' => 'orange', 'label' => 'Livrări (total)', 'value' => number_format($nrLivrariTotal ?? 0, 0, ',', '.')],
      ['accent' => 'violet', 'label' => 'Pick-up (total)', 'value' => number_format($pickupTotal ?? 0, 0, ',', '.')],
    ];
  @endphp

  <section class="operator-me-page">
    <header class="operator-me-hero @if($heroCover) operator-me-hero--with-cover @endif" @if($heroCover) style="background-image: url('{{ $heroCover }}');" @endif>
      <div class="operator-me-hero__overlay"></div>
      <div class="operator-me-hero__identity">
        @if($heroAvatar)
          <div class="operator-me-avatar" style="background-image: url('{{ $heroAvatar }}');"></div>
        @else
          <div class="operator-me-avatar operator-me-avatar--initial">{{ strtoupper(mb_substr($date['nume'], 0, 1)) }}</div>
        @endif
        <div class="operator-me-hero__copy">
          <h1 class="operator-me-title">{{ $date['nume'] }}</h1>
          <div class="operator-me-hero__badges">
            <span class="operator-me-badge"><i class="fas fa-database" aria-hidden="true"></i> Rezultate pentru perioada selectată</span>
            @if(isset($operatorRecord) && $operatorRecord)
            <a href="{{ route('setari') }}#poze" class="operator-me-link"><i class="fas fa-cog" aria-hidden="true"></i> Poze în Setări</a>
            @endif
          </div>
        </div>
      </div>
    </header>

    <div class="operator-me-grid">
      <aside class="operator-me-sidebar">
        <article class="operator-me-card">
          <h2 class="operator-me-card__title"><i class="fas fa-calendar-check" aria-hidden="true"></i> Luna curentă</h2>
          <div class="operator-me-stat-list">
            @foreach($monthStats as $s)
              <div class="operator-me-stat operator-me-stat--{{ $s['accent'] }}">
                <span class="operator-me-stat__icon"><i class="fas {{ $s['icon'] }}" aria-hidden="true"></i></span>
                <div class="operator-me-stat__content">
                  <span class="operator-me-stat__label">{{ $s['label'] }}</span>
                  <strong class="operator-me-stat__value">{{ $s['value'] }}</strong>
                </div>
              </div>
            @endforeach
          </div>
        </article>
      </aside>

      <div class="operator-me-main">
        <article class="operator-me-card">
          <h2 class="operator-me-card__title"><i class="fas fa-chart-line" aria-hidden="true"></i> Statistici totale</h2>
          <div class="operator-me-totals-grid">
            @foreach($totalStats as $s)
              <div class="operator-me-total operator-me-total--{{ $s['accent'] }}">
                <span class="operator-me-total__label">{{ $s['label'] }}</span>
                <strong class="operator-me-total__value">{{ $s['value'] }}</strong>
              </div>
            @endforeach
          </div>
        </article>

        @if($vanzariLunare1c->count() > 0)
        <article class="operator-me-card">
          <h2 class="operator-me-card__title"><i class="fas fa-wave-square" aria-hidden="true"></i> Vânzări pe luni (fără TVA)</h2>
          <div class="operator-me-chart-wrap">
            <canvas id="vanzariChartMe"></canvas>
          </div>
        </article>

        <article class="operator-me-card">
          <h2 class="operator-me-card__title"><i class="fas fa-calendar-alt" aria-hidden="true"></i> Istoric lunar</h2>
          <div class="operator-me-table-wrap">
            <table class="operator-me-table">
              <thead>
                <tr>
                  <th>Lună</th>
                  <th>Comenzi</th>
                  <th>Vânzări (fără TVA)</th>
                  <th>Profit</th>
                </tr>
              </thead>
              <tbody>
                @foreach($vanzariLunare1c as $luna)
                <tr>
                  <td>{{ $luna->luna_label }}</td>
                  <td>{{ $luna->comenzi }}</td>
                  <td>{{ number_format($luna->vanzari_luna, 2, ',', '.') }} MDL</td>
                  <td class="operator-me-table__profit">{{ number_format($luna->profit, 2, ',', '.') }} MDL</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </article>
        @endif
      </div>
    </div>
  </section>

  @if($vanzariLunare1c->count() > 0)
  @push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    const canvas = document.getElementById('vanzariChartMe');
    if (!canvas) return;
    const vanzariLunare = @json($vanzariLunare1c->sortBy('luna')->values());
    const labels = vanzariLunare.map(function(v) { return v.luna_label || v.luna; });
    const data = vanzariLunare.map(function(v) { return parseFloat(v.vanzari_luna) || 0; });

    const options = (typeof VoltaChartTheme !== 'undefined')
      ? VoltaChartTheme.cartesianDefaults({
          animation: false,
          plugins: {
            legend: { display: true, labels: { color: VoltaChartTheme.colors.textSecondary } },
            tooltip: Object.assign({}, VoltaChartTheme.tooltip(), {
              callbacks: {
                label: function(ctx) {
                  return 'Vânzări: ' + new Intl.NumberFormat('ro-RO', { minimumFractionDigits: 2 }).format(ctx.parsed.y) + ' MDL';
                }
              }
            }),
          },
          scales: {
            x: { grid: VoltaChartTheme.gridLines({ borderDash: [] }) },
            y: {
              grid: VoltaChartTheme.gridLines({ borderDash: [] }),
              ticks: Object.assign({}, VoltaChartTheme.ticks(10, 12), {
                callback: function(v) { return new Intl.NumberFormat('ro-RO', { maximumFractionDigits: 0 }).format(v) + ' MDL'; }
              }),
              beginAtZero: true,
            }
          }
        })
      : {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          plugins: { legend: { labels: { color: '#e2e8f0' } } },
          scales: {
            x: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.06)' } },
            y: { ticks: { color: '#9CA3AF' }, grid: { color: 'rgba(255,255,255,0.06)' }, beginAtZero: true }
          }
        };

    new Chart(canvas.getContext('2d'), {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'Vânzări (fără TVA) MDL',
          data: data,
          borderColor: '#FFEE00',
          backgroundColor: 'rgba(255, 238, 0, 0.12)',
          borderWidth: 2.25,
          tension: 0.2,
          fill: true,
          pointRadius: 3,
          pointHoverRadius: 5,
          pointBackgroundColor: '#FFEE00',
          pointBorderColor: '#F8FAFC',
          pointBorderWidth: 1
        }]
      },
      options: options
    });
  });
  </script>
  @endpush
  @endif
@else
  <div class="operator-me-empty">
    <i class="fas fa-database" aria-hidden="true"></i>
    <p class="operator-me-empty__title">Nu există date pentru contul tău</p>
    <p class="operator-me-empty__text">Nume asociat: <strong>{{ $operatorNume ?: '—' }}</strong></p>
    <p class="operator-me-empty__hint">Contactează administratorul pentru a seta rolul „Operator” și numele asociat.</p>
  </div>
@endif

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const lunaCurenta = @json($lunaCurentaKpi);

  function formatNumber(val) {
    return new Intl.NumberFormat('ro-RO').format(val || 0);
  }

  function formatValue(value, suffix) {
    const suffixHtml = suffix
      ? '<span style="font-size:18px;color:var(--muted);font-weight:600;margin-left:4px;">' + suffix + '</span>'
      : '';
    return '<span style="display:inline-flex;align-items:baseline;flex-wrap:wrap;">' + formatNumber(value) + suffixHtml + '</span>';
  }

  async function loadTeamKpi() {
    try {
      const res = await fetch(@json(route('api.kpi')) + '?month=' + encodeURIComponent(lunaCurenta));
      const kpiData = await res.json();
      if (!kpiData.success) return;

      const map = {
        'operator-vanzari-luna': formatValue(kpiData.vanzari_luna || 0, 'MDL'),
        'operator-plan-luna': formatValue(kpiData.plan_luna || 0, 'MDL'),
        'operator-progres-plan': formatValue(kpiData.progres_plan || 0, '%'),
        'operator-prognoza-plan': formatValue(kpiData.prognoza_plan_procent || 0, '%'),
        'operator-vanzari-zi-plan': formatValue(kpiData.vanzari_zi_pentru_plan || 0, 'MDL'),
      };

      Object.keys(map).forEach(function(id) {
        const el = document.getElementById(id);
        if (el) el.innerHTML = map[id];
      });
    } catch (e) {
      console.error('Eroare la încărcarea KPI echipă:', e);
    }
  }

  loadTeamKpi();
});
</script>
@endpush
@endsection
