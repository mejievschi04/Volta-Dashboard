@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', 'Livrări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/operatori.css') }}">
<style>
  .livrari-page { padding: 24px 20px; max-width: 1200px; margin: 0 auto; font-family: 'Montserrat', system-ui, sans-serif; color: #E5E7EB; }
  .livrari-page h1 { color: #FFEE00; margin: 0 0 8px 0; font-size: 28px; font-weight: 800; }
  .livrari-page .subtitle { color: #9CA3AF; margin: 0 0 24px 0; font-size: 14px; }
  .livrari-alert { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
  .livrari-alert-success { background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #fff; }
  .livrari-card { background: linear-gradient(160deg, #1F2937 0%, #111827 100%); border-radius: 16px; padding: 24px; margin-bottom: 24px; border: 1px solid rgba(255,255,255,0.06); }
  .livrari-card h2 { color: #fff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
  .livrari-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
  .livrari-form-grid label { display: block; color: #FFEE00; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
  .livrari-form-grid input { width: 100%; padding: 12px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; font-size: 14px; }
  .livrari-form-grid input:focus { outline: none; border-color: #FFEE00; background: rgba(255,238,0,0.08); }
  .livrari-btn { padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; }
  .livrari-btn-primary { background: linear-gradient(135deg, #FFEE00 0%, #FACC15 100%); color: #000; }
  .livrari-btn-primary:hover { opacity: 0.95; }
  .livrari-table-wrap { overflow-x: auto; }
  .livrari-table { width: 100%; border-collapse: collapse; }
  .livrari-table th, .livrari-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); color: #E5E7EB; }
  .livrari-table th { background: rgba(255,238,0,0.15); color: #FFEE00; font-weight: 700; font-size: 13px; }
  .livrari-table tbody tr:hover { background: rgba(255,255,255,0.03); }
  .livrari-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .livrari-kpi-box { background: rgba(255,238,0,0.1); border: 1px solid rgba(255,238,0,0.3); border-radius: 12px; padding: 20px; text-align: center; }
  .livrari-kpi-box .label { color: #9CA3AF; font-size: 12px; margin-bottom: 6px; }
  .livrari-kpi-box .value { color: #FFEE00; font-size: 24px; font-weight: 800; }
  .livrari-per-operator { margin-top: 16px; }
  .livrari-per-operator h3 { color: #fff; font-size: 16px; margin: 0 0 12px 0; }
  .livrari-per-operator table { width: 100%; max-width: 400px; }
  .livrari-per-operator th { text-align: left; color: #9CA3AF; font-size: 12px; }
  .livrari-pagination { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
  .livrari-pagination a, .livrari-pagination span { padding: 8px 14px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #fff; text-decoration: none; font-size: 14px; }
  .livrari-pagination a:hover { background: rgba(255,238,0,0.2); color: #FFEE00; }
  .livrari-pagination .disabled span { color: #6B7280; }
  .livrari-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; margin-bottom: 20px; }
  .livrari-filters label { display: block; color: #9CA3AF; font-size: 12px; margin-bottom: 4px; }
  .livrari-filters select { padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: #1F2937; color: #E5E7EB; min-width: 160px; }
  .livrari-filters select option { background: #1F2937; color: #E5E7EB; }
  .livrari-form-grid select { background: #1F2937 !important; color: #E5E7EB !important; }
  .livrari-form-grid select option { background: #1F2937; color: #E5E7EB; }
  .livrari-filters .livrari-btn { padding: 10px 18px; font-size: 13px; }
  .livrari-search-wrap { min-width: 220px; }
  /* Formular adăugare livrare – mai simplu pentru operatori */
  .livrari-add-card { margin-bottom: 24px; }
  .livrari-add-hint { color: #9CA3AF; font-size: 14px; margin: -8px 0 20px 0; }
  .livrari-add-form { display: flex; flex-direction: column; gap: 20px; }
  .livrari-add-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .livrari-add-row-full { grid-template-columns: 1fr; }
  .livrari-add-field label { display: block; color: #FFEE00; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
  .livrari-add-field input,
  .livrari-add-field select { width: 100%; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; background: #1F2937; color: #E5E7EB; font-size: 15px; }
  .livrari-add-field input:focus,
  .livrari-add-field select:focus { outline: none; border-color: #FFEE00; }
  .livrari-add-field input::placeholder { color: #6B7280; }
  .livrari-add-actions { margin-top: 8px; }
  .livrari-btn-add { padding: 14px 28px; font-size: 16px; }
  @media (max-width: 600px) {
    .livrari-add-row { grid-template-columns: 1fr; }
  }
  .livrari-search-input { padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: #1F2937; color: #E5E7EB; min-width: 220px; font-size: 14px; }
  .livrari-search-input::placeholder { color: #6B7280; }
  .livrari-search-input:focus { outline: none; border-color: #FFEE00; background: #111827; }
  /* Modal adaugare livrare */
  .livrari-modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.7); z-index: 1000; align-items: center; justify-content: center; padding: 20px; }
  .livrari-modal-overlay.is-open { display: flex; }
  .livrari-modal { background: linear-gradient(160deg, #1F2937 0%, #111827 100%); border-radius: 16px; padding: 28px; max-width: 560px; width: 100%; max-height: 90vh; overflow-y: auto; border: 1px solid rgba(255,238,0,0.2); box-shadow: 0 20px 60px rgba(0,0,0,0.5); }
  .livrari-modal-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
  .livrari-modal-title { color: #FFEE00; font-size: 20px; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px; }
  .livrari-modal-close { background: none; border: none; color: #9CA3AF; font-size: 24px; cursor: pointer; padding: 4px 8px; line-height: 1; border-radius: 8px; }
  .livrari-modal-close:hover { color: #fff; background: rgba(255,255,255,0.1); }
  .livrari-modal-success { display: none; padding: 12px 16px; background: rgba(16, 185, 129, 0.2); color: #34D399; border-radius: 10px; margin-bottom: 16px; font-weight: 500; }
  .livrari-modal-success.is-visible { display: block; }
  .livrari-modal-error { display: none; padding: 12px 16px; background: rgba(239, 68, 68, 0.2); color: #F87171; border-radius: 10px; margin-bottom: 16px; }
  .livrari-modal-error.is-visible { display: block; }
  .livrari-btn-open-modal { padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 15px; cursor: pointer; border: none; background: linear-gradient(135deg, #FFEE00 0%, #FACC15 100%); color: #000; display: inline-flex; align-items: center; gap: 8px; }
  .livrari-btn-open-modal:hover { opacity: 0.95; }
</style>
@endpush

@section('content')
<div class="livrari-page">
  <h1><i class="fas fa-truck" style="margin-right: 10px;"></i> Livrări</h1>
  <p class="subtitle">{{ $isAdmin ? 'Toate livrările și KPI per operator' : 'Adaugă și vizualizează livrările tale' }}</p>

  @php
    $filters = $filters ?? ['luna' => '', 'operator_id' => '', 'locatie' => '', 'cauta' => ''];
    $operatorsForFilter = $operatorsForFilter ?? collect();
  @endphp

  <form method="get" action="{{ route('livrari') }}" class="livrari-card" style="margin-bottom: 20px;">
    <h2 style="margin-bottom: 16px;"><i class="fas fa-filter"></i> Filtre și căutare</h2>
    <div class="livrari-filters">
      <div class="livrari-search-wrap">
        <label for="cauta">Căutare</label>
        <input type="text" id="cauta" name="cauta" value="{{ $filters['cauta'] ?? '' }}" placeholder="Nr. comandă, adresă, nr. client..." class="livrari-search-input" maxlength="200">
      </div>
      <div>
        <label>Lună</label>
        <select name="luna">
          <option value="">Toate lunile</option>
          @foreach(range(now()->year, now()->year - 2, -1) as $y)
            @foreach(range(1, 12) as $m)
              @php $ym = sprintf('%04d-%02d', $y, $m); @endphp
              <option value="{{ $ym }}" {{ ($filters['luna'] ?? '') == $ym ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}</option>
            @endforeach
          @endforeach
        </select>
      </div>
      @if($isAdmin)
      <div>
        <label>Operator</label>
        <select name="operator_id">
          <option value="">Toți operatorii</option>
          @foreach($operatorsForFilter as $u)
            <option value="{{ $u->id }}" {{ ($filters['operator_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ trim($u->full_name ?? $u->name ?? '') ?: $u->username }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div>
        <label>Locație</label>
        <select name="locatie">
          <option value="">Toate</option>
          <option value="chisinau" {{ ($filters['locatie'] ?? '') === 'chisinau' ? 'selected' : '' }}>În Chișinău</option>
          <option value="afara" {{ ($filters['locatie'] ?? '') === 'afara' ? 'selected' : '' }}>În afara</option>
        </select>
      </div>
      <button type="submit" class="livrari-btn livrari-btn-primary"><i class="fas fa-search"></i> Filtrează</button>
    </div>
  </form>

  @if(session('success'))
  <div class="livrari-alert livrari-alert-success">
    <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
  </div>
  @endif
  @if($errors->any())
  <div class="livrari-alert" style="background: rgba(239,68,68,0.2); color: #F87171; border: 1px solid rgba(239,68,68,0.4);">
    <i class="fas fa-exclamation-circle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
  @endif

  @if($isAdmin && ($perOperator->isNotEmpty() || $totalLivrari > 0))
  <div class="livrari-card">
    <h2><i class="fas fa-chart-bar"></i> KPI Livrări</h2>
    <div class="livrari-kpi-grid">
      <div class="livrari-kpi-box">
        <div class="label">Total livrări</div>
        <div class="value">{{ number_format($totalLivrari, 0, ',', '.') }}</div>
      </div>
    </div>
    <div class="livrari-per-operator">
      <h3>Livrări per operator</h3>
      <table class="livrari-table">
        <thead>
          <tr>
            <th>Operator</th>
            <th class="tc">Nr. livrări</th>
          </tr>
        </thead>
        <tbody>
          @foreach($perOperator as $row)
          <tr>
            <td>{{ $row->nume }}</td>
            <td class="tc" style="text-align: center;">{{ $row->total }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  @if(!$isAdmin)
  <div style="margin-bottom: 24px;">
    <button type="button" class="livrari-btn-open-modal" id="livrariOpenModalBtn" aria-label="Adaugă livrare">
      <i class="fas fa-plus-circle"></i> Adaugă livrare
    </button>
  </div>

  <!-- Modal Adaugă livrare -->
  <div class="livrari-modal-overlay" id="livrariAddModal" aria-hidden="true">
    <div class="livrari-modal" role="dialog" aria-labelledby="livrariModalTitle">
      <div class="livrari-modal-header">
        <h2 class="livrari-modal-title" id="livrariModalTitle"><i class="fas fa-truck"></i> Adaugă livrare nouă</h2>
        <button type="button" class="livrari-modal-close" id="livrariModalClose" aria-label="Închide">&times;</button>
      </div>
      <p class="livrari-add-hint" style="margin-bottom: 16px;">Locația (În Chișinău / În afara) se stabilește automat după localitate. După salvare poți introduce altă livrare sau închide.</p>
      <div class="livrari-modal-success" id="livrariModalSuccess"></div>
      <div class="livrari-modal-error" id="livrariModalError"></div>
      <form id="livrariAddForm" action="{{ route('livrari.store') }}" method="post" class="livrari-add-form">
        @csrf
        <input type="hidden" name="data" value="{{ date('Y-m-d') }}" id="livrari-data-comanda">
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="modal_data_livrarii">Data livrării *</label>
            <input type="date" id="modal_data_livrarii" name="data_livrarii" value="{{ date('Y-m-d') }}" required>
          </div>
          <div class="livrari-add-field">
            <label for="modal_numar_comanda">Număr comandă *</label>
            <input type="text" id="modal_numar_comanda" name="numar_comanda" required maxlength="100" placeholder="Ex: CMD-001">
          </div>
        </div>
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="modal_localitate">Localitate *</label>
            <input type="text" id="modal_localitate" name="localitate" required maxlength="255" placeholder="Ex: Chișinău, Bălți, Orhei...">
          </div>
          <div class="livrari-add-field">
            <label for="modal_nr_client">Nr. client *</label>
            <input type="text" id="modal_nr_client" name="nr_client" required maxlength="100" placeholder="Ex: CL-123">
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="modal_adresa_livrarii">Adresa *</label>
            <input type="text" id="modal_adresa_livrarii" name="adresa_livrarii" required maxlength="500" placeholder="Strada, nr., bloc, scara, apartament, cod poștal">
          </div>
        </div>
        <div class="livrari-add-actions" style="display: flex; gap: 12px; align-items: center;">
          <button type="submit" class="livrari-btn livrari-btn-primary livrari-btn-add" id="livrariModalSubmitBtn"><i class="fas fa-check"></i> Salvează livrarea</button>
          <button type="button" class="livrari-modal-close" id="livrariModalCloseBottom" style="padding: 10px 20px; font-size: 14px;">Închide</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  <div class="livrari-card">
    <h2><i class="fas fa-list"></i> {{ $isAdmin ? 'Toate livrările' : 'Livrările mele' }}</h2>
    <div class="livrari-table-wrap">
      <table class="livrari-table">
        <thead>
          <tr>
            <th>Număr comandă</th>
            <th>Data</th>
            <th>Localitate</th>
            <th>Adresa</th>
            <th>Nr. client</th>
            <th>Data livrării</th>
            <th>Locație</th>
            @if($isAdmin)<th>Operator</th>@endif
          </tr>
        </thead>
        <tbody id="livrariTableBody">
          @forelse($livrari as $l)
          <tr>
            <td>{{ $l->numar_comanda }}</td>
            <td>{{ $l->data->format('d.m.Y') }}</td>
            <td>{{ $l->localitate ?? '—' }}</td>
            <td>{{ $l->adresa_livrarii }}</td>
            <td>{{ $l->nr_client }}</td>
            <td>{{ $l->data_livrarii->format('d.m.Y') }}</td>
            <td>{{ isset($l->in_chisinau) ? ($l->in_chisinau ? 'În Chișinău' : 'În afara') : '—' }}</td>
            @if($isAdmin)
            <td>{{ $l->user ? (trim($l->user->full_name ?? $l->user->name ?? '') ?: $l->user->username) : '—' }}</td>
            @endif
          </tr>
          @empty
          <tr id="livrariEmptyRow">
            <td colspan="{{ $isAdmin ? 9 : 8 }}" style="text-align: center; color: #9CA3AF; padding: 32px;">Nicio livrare înregistrată.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($livrari->hasPages())
    <div class="livrari-pagination">
      {{ $livrari->links() }}
    </div>
    @endif
  </div>
</div>
@if(!$isAdmin)
@push('scripts')
<script>
(function() {
  var modal = document.getElementById('livrariAddModal');
  var openBtn = document.getElementById('livrariOpenModalBtn');
  var closeBtn = document.getElementById('livrariModalClose');
  var closeBtnBottom = document.getElementById('livrariModalCloseBottom');
  var form = document.getElementById('livrariAddForm');
  var successEl = document.getElementById('livrariModalSuccess');
  var errorEl = document.getElementById('livrariModalError');
  var submitBtn = document.getElementById('livrariModalSubmitBtn');
  var dataComanda = document.getElementById('livrari-data-comanda');
  var dataLivrarii = document.getElementById('modal_data_livrarii');
  var tbody = document.getElementById('livrariTableBody');
  var emptyRow = document.getElementById('livrariEmptyRow');
  var isAdmin = {{ $isAdmin ? 'true' : 'false' }};

  function openModal() {
    if (modal) {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
  }
  function closeModal() {
    if (modal) {
      modal.classList.remove('is-open');
      modal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
    }
  }
  function showSuccess(msg) {
    if (successEl) { successEl.textContent = msg; successEl.classList.add('is-visible'); }
    if (errorEl) errorEl.classList.remove('is-visible');
  }
  function showError(msg) {
    if (errorEl) { errorEl.textContent = msg; errorEl.classList.add('is-visible'); }
    if (successEl) successEl.classList.remove('is-visible');
  }
  function hideMessages() {
    if (successEl) successEl.classList.remove('is-visible');
    if (errorEl) errorEl.classList.remove('is-visible');
  }
  function resetForm() {
    if (form) form.reset();
    if (dataComanda) dataComanda.value = new Date().toISOString().slice(0, 10);
    if (dataLivrarii) dataLivrarii.value = new Date().toISOString().slice(0, 10);
  }
  function addRowToTable(livrare) {
    if (!tbody) return;
    if (emptyRow) emptyRow.remove();
    var colCount = isAdmin ? 9 : 8;
    var tr = document.createElement('tr');
    tr.innerHTML =
      '<td>' + (livrare.numar_comanda || '') + '</td>' +
      '<td>' + (livrare.data || '') + '</td>' +
      '<td>' + (livrare.localitate || '—') + '</td>' +
      '<td>' + (livrare.adresa_livrarii || '') + '</td>' +
      '<td>' + (livrare.nr_client || '') + '</td>' +
      '<td>' + (livrare.data_livrarii || '') + '</td>' +
      '<td>' + (livrare.locatie || '—') + '</td>' +
      (isAdmin ? '<td>—</td>' : '');
    tbody.insertBefore(tr, tbody.firstChild);
  }

  if (openBtn) openBtn.addEventListener('click', function() { hideMessages(); resetForm(); openModal(); });
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (closeBtnBottom) closeBtnBottom.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) closeModal();
    });
  }

  if (dataLivrarii && dataComanda) {
    dataLivrarii.addEventListener('change', function() { dataComanda.value = dataLivrarii.value; });
  }

  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      if (submitBtn) submitBtn.disabled = true;
      errorEl.textContent = '';
      hideMessages();
      var formData = new FormData(form);
      var csrf = document.querySelector('meta[name="csrf-token"]');
      if (csrf) formData.append('_token', csrf.getAttribute('content'));

      fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, status: r.status, data: d }; }).catch(function() { return { ok: false, status: r.status, data: {} }; }); })
      .then(function(result) {
        if (result.ok && result.data.success) {
          showSuccess(result.data.message || 'Livrarea a fost adăugată.');
          resetForm();
          if (result.data.livrare) addRowToTable(result.data.livrare);
        } else {
          var msg = 'Eroare la salvare.';
          if (result.data) {
            if (result.data.message) msg = result.data.message;
            else if (result.data.errors) msg = Object.values(result.data.errors).flat().join(' ');
          }
          showError(msg);
        }
      })
      .catch(function(err) {
        showError('Eroare de rețea. Încearcă din nou.');
      })
      .finally(function() {
        if (submitBtn) submitBtn.disabled = false;
      });
    });
  }
})();
</script>
@endpush
@endif
@endsection
