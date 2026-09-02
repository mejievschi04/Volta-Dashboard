@extends('layouts.app')

@section('title', 'Operatori – VOLTA')

@section('header-title', 'Operatori')

@section('content')
<div class="operatori-page">
  @php
    $totalVanzari = array_sum(array_column($operatori1c ?? [], 'vanzari_fara_tva'));
    $totalProfit = array_sum(array_column($operatori1c ?? [], 'profit'));
    $totalComenzi = array_sum(array_column($operatori1c ?? [], 'nr_comenzi'));
    $topOperator = $operatori1c[0] ?? null;
  @endphp
  <section class="operatori-directory-hero">
    <div>
      <h1>Operatori</h1>
      <p>Urmărește rezultatele echipei pentru <strong>{{ $perioadaLabel ?? 'perioada selectată' }}</strong> și deschide rapid profilul fiecărui operator.</p>
    </div>
  </section>

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

  <section class="operatori-overview-grid" aria-label="Rezumat performanță">
    <div class="operatori-overview-stat"><span>Vânzări fără TVA</span><strong>{{ number_format($totalVanzari, 0, ',', '.') }} <small>MDL</small></strong></div>
    <div class="operatori-overview-stat"><span>Profit total</span><strong class="is-profit">{{ number_format($totalProfit, 0, ',', '.') }} <small>MDL</small></strong></div>
    <div class="operatori-overview-stat"><span>Comenzi procesate</span><strong>{{ number_format($totalComenzi, 0, ',', '.') }}</strong></div>
    <div class="operatori-overview-stat"><span>Cel mai bun rezultat</span><strong class="is-name">{{ $topOperator['nume'] ?? '—' }}</strong></div>
  </section>

  <form method="get" action="{{ route('operatori') }}" class="operatori-filter-form operatori-filter-panel">
    <div class="operatori-filter-panel__heading">Filtrează perioada</div>
    <div class="rapoarte-periods-grid operatori-filter-periods">
      <div class="month-selector-modern">
        <div class="month-selector-wrapper">
          <i class="fas fa-calendar-alt" aria-hidden="true"></i>
          <label for="an">An</label>
          <select name="an" id="an" class="dashboard-month-select">
            @foreach(range((int)date('Y'), 2023, -1) as $y)
            <option value="{{ $y }}" {{ (isset($an) && (int)$an === $y) ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="month-selector-modern">
        <div class="month-selector-wrapper">
          <i class="fas fa-calendar-day" aria-hidden="true"></i>
          <label for="luna">Lună</label>
          <select name="luna" id="luna" class="dashboard-month-select">
            <option value="">Toate lunile</option>
            @foreach($luniNume ?? [] as $nr => $numeLuna)
            <option value="{{ $nr }}" {{ (isset($luna) && (int)$luna === $nr) ? 'selected' : '' }}>{{ $numeLuna }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </form>

  @if(isset($operatori1c) && count($operatori1c) > 0)
  <div class="operatori-card operatori-card-main operatori-directory-card">
    @if(isset($chartData1c) && count($chartData1c) > 0)
    @php $maxVanzari = max(array_column($chartData1c, 'vanzari_fara_tva')) ?: 1; @endphp
    <section class="operatori-ranking-section" aria-labelledby="operatori-ranking-title">
      <div class="operatori-ranking-heading">
        <div>
          <h2 id="operatori-ranking-title">Clasament după vânzări</h2>
          <p>Compară direct contribuția fiecărui operator la rezultatul echipei.</p>
        </div>
        <span>{{ $perioadaLabel }}</span>
      </div>
      <ol class="operatori-ranking-list">
        @foreach($chartData1c as $index => $d)
        @php $width = max(4, round(($d['vanzari_fara_tva'] / $maxVanzari) * 100, 1)); @endphp
        <li class="operatori-ranking-item">
          <span class="operatori-ranking-position">{{ $index + 1 }}</span>
          <div class="operatori-ranking-main">
            <div class="operatori-ranking-label"><strong>{{ $d['nume'] }}</strong><span>{{ $d['procent'] }}% din vânzările echipei</span></div>
            <div class="operatori-ranking-track" aria-hidden="true"><span style="width: {{ $width }}%"></span></div>
          </div>
          <strong class="operatori-ranking-value">{{ number_format($d['vanzari_fara_tva'], 0, ',', '.') }} <small>MDL</small></strong>
        </li>
        @endforeach
      </ol>
    </section>
    @endif

    <section class="operatori-table-section">
      <h2 class="operatori-table-title operatori-table-title--directory">
        <span>Rezultate individuale <small>{{ count($operatori1c) }} operatori activi</small></span>
        <button type="button" id="operatoriExportExcelBtn" class="operatori-btn operatori-btn-export volta-export-btn">
          <i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel
        </button>
      </h2>
      <div class="operatori-table-wrap">
        <table class="operatori-table operatori-directory-table">
          <thead>
            <tr>
              <th>Operator</th>
              <th class="tc">Vânzări fără TVA</th>
              <th class="tc">Vânzări cu TVA</th>
              <th class="tc">Profit</th>
              <th class="tc">Comenzi</th>
              <th class="tc">CEC mediu</th>
              @if(auth()->check() && in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'administrator']))
              <th class="tc">Acțiuni</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($operatori1c as $op)
            @php
              $detailUrl = !empty($op['operator_id'])
                ? route('operatori.show', $op['operator_id'])
                : route('operatori.raport', ['nume' => $op['nume']]);
            @endphp
            <tr class="operatori-row-click" data-href="{{ $detailUrl }}" tabindex="0" role="link" aria-label="Detalii operator {{ $op['nume'] }}">
              <td>
                <a href="{{ $detailUrl }}" class="operatori-name-link operatori-person-link">{{ $op['nume'] }}</a>
              </td>
              <td class="tc">{{ number_format($op['vanzari_fara_tva'], 2, ',', '.') }} MDL</td>
              <td class="tc">{{ number_format($op['vanzari_cu_tva'], 2, ',', '.') }} MDL</td>
              <td class="tc operatori-profit">{{ number_format($op['profit'], 2, ',', '.') }} MDL</td>
              <td class="tc">{{ number_format($op['nr_comenzi'], 0, ',', '.') }}</td>
              <td class="tc">{{ number_format($op['cec_mediu'] ?? 0, 2, ',', '.') }} MDL</td>
              @if(auth()->check() && in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'administrator']))
              <td class="tc operatori-actions">
                <form action="{{ route('operatori.toggle-activ') }}" method="post" class="operatori-form-inline" onsubmit="return confirm('Dezactivezi acest operator? Nu va mai apărea în listă.');">
                  @csrf
                  <input type="hidden" name="operator_id" value="{{ $op['operator_id'] ?? '' }}">
                  <input type="hidden" name="nume" value="{{ $op['nume'] }}">
                  <button type="submit" class="operatori-btn operatori-btn-deactivate"><i class="fas fa-user-slash"></i> Dezactivează</button>
                </form>
              </td>
              @endif
            </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </section>
  </div>
  @else
  <div class="operatori-card operatori-card-empty">
    <i class="fas fa-database"></i>
    <p>Nu există date pentru operatori.</p>
    <p class="operatori-empty-hint">Adaugă sau încarcă date pentru a vedea graficele.</p>
  </div>
  @endif

  @if(auth()->check() && in_array(strtolower(trim(auth()->user()->role ?? '')), ['admin', 'administrator']) && isset($operatoriDezactivati) && $operatoriDezactivati->count() > 0)
  <div class="operatori-card operatori-card-dezactivati">
    <h3><i class="fas fa-user-slash"></i> Operatori dezactivați</h3>
    <p class="operatori-dezactivati-desc">Nu apar în listă. Poți reactiva pentru a-i afișa din nou.</p>
    <div class="operatori-dezactivati-btns">
      @foreach($operatoriDezactivati as $od)
      <form action="{{ route('operatori.toggle-activ') }}" method="post">
        @csrf
        <input type="hidden" name="nume" value="{{ $od->nume }}">
        <button type="submit" class="operatori-btn operatori-btn-reactivate">{{ $od->nume }} <i class="fas fa-user-check"></i> Reactivează</button>
      </form>
      @endforeach
    </div>
  </div>
  @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}?v={{ @filemtime(public_path('css/operatori.css')) ?: 0 }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const filtersForm = document.querySelector('.operatori-filter-form');
  if (filtersForm) {
    filtersForm.querySelectorAll('select').forEach(function (select) {
      select.addEventListener('change', function () {
        if (filtersForm.dataset.submitting === 'true') return;
        filtersForm.dataset.submitting = 'true';
        filtersForm.requestSubmit ? filtersForm.requestSubmit() : filtersForm.submit();
      });
    });
  }

  const exportBtn = document.getElementById('operatoriExportExcelBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      const table = document.querySelector('.operatori-table');
      if (!table) {
        alert('Nu există tabel pentru export.');
        return;
      }
      Promise.resolve(window.VoltaExcelExport.exportTable(table, {
        fileName: 'operatori_tabel_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Operatori'
      })).catch(function (error) {
        alert('Nu am putut exporta Excel: ' + error.message);
      });
    });
  }

  document.querySelectorAll('.operatori-row-click').forEach(function (row) {
    row.addEventListener('click', function (event) {
      if (event.target.closest('a, button, form, input, select, textarea, label')) {
        return;
      }
      const href = row.getAttribute('data-href');
      if (href) {
        window.location.href = href;
      }
    });
    row.addEventListener('keydown', function (event) {
      if (event.key !== 'Enter' && event.key !== ' ') {
        return;
      }
      if (event.target.closest('a, button, form, input, select, textarea, label')) {
        return;
      }
      event.preventDefault();
      const href = row.getAttribute('data-href');
      if (href) {
        window.location.href = href;
      }
    });
  });
});
</script>
@endpush
