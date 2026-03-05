@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', 'Livrări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/operatori.css') }}">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
  /* ---------- Page base ---------- */
  .livrari-page {
    padding: 32px 24px 48px;
    max-width: 1280px;
    margin: 0 auto;
    font-family: 'DM Sans', 'Montserrat', system-ui, sans-serif;
    color: var(--ink-secondary, #E5E7EB);
    min-height: 60vh;
  }
  .livrari-page h1 {
    color: var(--brand, #FFEE00);
    margin: 0 0 6px 0;
    font-size: clamp(1.75rem, 4vw, 2rem);
    font-weight: 700;
    letter-spacing: -0.02em;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .livrari-page h1 i { opacity: 0.95; }
  .livrari-page .subtitle {
    color: var(--muted, #9CA3AF);
    margin: 0 0 28px 0;
    font-size: 0.9375rem;
    font-weight: 500;
  }

  /* ---------- Cards ---------- */
  .livrari-card {
    background: linear-gradient(165deg, rgba(31, 41, 55, 0.95) 0%, rgba(17, 24, 39, 0.98) 100%);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 24px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 238, 0, 0.04);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .livrari-card:hover { border-color: rgba(255, 238, 0, 0.12); }
  .livrari-card h2 {
    color: #fff;
    margin: 0 0 20px 0;
    font-size: 1.125rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: -0.01em;
  }
  .livrari-card h2 i { color: var(--brand); opacity: 0.9; }

  /* ---------- Alerts ---------- */
  .livrari-alert {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px 20px;
    border-radius: 12px;
    margin-bottom: 24px;
    font-weight: 500;
    font-size: 0.9375rem;
    border: 1px solid transparent;
  }
  .livrari-alert-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(52, 211, 153, 0.15) 100%);
    color: #34D399;
    border-color: rgba(16, 185, 129, 0.35);
  }
  .livrari-alert i { font-size: 1.25rem; flex-shrink: 0; }

  /* ---------- Filters card ---------- */
  .livrari-filters-card { padding: 24px 28px; }
  .livrari-filters-card h2 { margin-bottom: 20px; }
  .livrari-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 20px;
  }
  .livrari-filters label {
    display: block;
    color: var(--muted);
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 6px;
  }
  .livrari-search-wrap { min-width: 240px; }
  .livrari-search-input {
    padding: 12px 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.6);
    color: #fff;
    min-width: 100%;
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-search-input::placeholder { color: #6B7280; }
  .livrari-search-input:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(255, 238, 0, 0.12);
  }
  .livrari-filters select {
    padding: 12px 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.6);
    color: #E5E7EB;
    min-width: 180px;
    font-size: 0.9375rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-filters select:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(255, 238, 0, 0.12);
  }
  .livrari-filters .livrari-btn { padding: 12px 22px; font-size: 0.9375rem; }
  .livrari-btn {
    padding: 12px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9375rem;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: transform 0.15s ease, box-shadow 0.2s ease;
  }
  .livrari-btn:hover { transform: translateY(-1px); }
  .livrari-btn-primary {
    background: linear-gradient(135deg, #FFEE00 0%, #E6D600 100%);
    color: #0a0a0a;
    box-shadow: 0 4px 14px rgba(255, 238, 0, 0.35);
  }
  .livrari-btn-primary:hover { box-shadow: 0 6px 20px rgba(255, 238, 0, 0.45); }
  .livrari-btn-edit {
    padding: 8px 14px;
    font-size: 0.8125rem;
    background: rgba(255, 255, 255, 0.1);
    color: var(--brand);
    border: 1px solid rgba(255, 238, 0, 0.3);
  }
  .livrari-btn-edit:hover { background: rgba(255, 238, 0, 0.15); }

  /* ---------- Data table ---------- */
  .livrari-table-card { padding: 24px 28px; }
  .livrari-table-card h2 { margin-bottom: 20px; }
  .livrari-table-wrap {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(17, 24, 39, 0.4);
  }
  .livrari-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
  }
  .livrari-table th {
    background: rgba(255, 238, 0, 0.08);
    color: var(--brand);
    padding: 14px 18px;
    text-align: left;
    font-weight: 700;
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 1px solid rgba(255, 238, 0, 0.2);
  }
  .livrari-table td {
    padding: 14px 18px;
    color: #E5E7EB;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  .livrari-table tbody tr {
    transition: background 0.15s ease;
  }
  .livrari-table tbody tr:hover { background: rgba(255, 238, 0, 0.04); }
  .livrari-table tbody tr:last-child td { border-bottom: none; }

  /* ---------- Pagination ---------- */
  .livrari-pagination {
    margin-top: 24px;
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
  }
  .livrari-pagination a,
  .livrari-pagination span {
    padding: 10px 16px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.06);
    color: #fff;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 600;
    transition: background 0.2s, color 0.2s;
  }
  .livrari-pagination a:hover {
    background: rgba(255, 238, 0, 0.2);
    color: var(--brand);
  }
  .livrari-pagination .disabled span { color: #6B7280; }

  /* ---------- Add livrare button (operator) ---------- */
  .livrari-operator-actions { margin-bottom: 28px; }
  .livrari-btn-open-modal {
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 1rem;
    cursor: pointer;
    border: none;
    background: linear-gradient(135deg, #FFEE00 0%, #E6D600 100%);
    color: #0a0a0a;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 4px 16px rgba(255, 238, 0, 0.4);
    transition: transform 0.15s ease, box-shadow 0.2s ease;
  }
  .livrari-btn-open-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(255, 238, 0, 0.5);
  }

  /* ---------- Modal ---------- */
  .livrari-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.75);
    z-index: 1000;
    align-items: center;
    justify-content: center;
    padding: 24px;
    backdrop-filter: blur(8px);
  }
  .livrari-modal-overlay.is-open { display: flex; }
  .livrari-modal {
    background: linear-gradient(165deg, #1F2937 0%, #111827 100%);
    border-radius: 20px;
    padding: 32px;
    max-width: 540px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid rgba(255, 238, 0, 0.15);
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
  }
  .livrari-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }
  .livrari-modal-title {
    color: var(--brand);
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .livrari-modal-close {
    background: rgba(255, 255, 255, 0.08);
    border: none;
    color: #9CA3AF;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    font-size: 1.5rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s;
  }
  .livrari-modal-close:hover { background: rgba(255, 255, 255, 0.15); color: #fff; }
  .livrari-modal-close.livrari-btn-secondary {
    width: auto;
    height: auto;
    padding: 12px 22px;
    font-size: 0.9375rem;
    font-weight: 600;
  }
  .livrari-add-hint {
    color: var(--muted);
    font-size: 0.875rem;
    margin: -4px 0 20px 0;
    line-height: 1.5;
  }
  .livrari-add-form { display: flex; flex-direction: column; gap: 22px; }
  .livrari-add-row { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
  .livrari-add-row-full { grid-template-columns: 1fr; }
  .livrari-add-field label {
    display: block;
    color: var(--brand);
    margin-bottom: 8px;
    font-size: 0.8125rem;
    font-weight: 600;
  }
  .livrari-add-field input,
  .livrari-add-field select {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.6);
    color: #E5E7EB;
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-add-field input:focus,
  .livrari-add-field select:focus {
    outline: none;
    border-color: var(--brand);
    box-shadow: 0 0 0 3px rgba(255, 238, 0, 0.12);
  }
  .livrari-add-field input::placeholder { color: #6B7280; }
  .livrari-add-actions { margin-top: 8px; display: flex; gap: 12px; align-items: center; flex-wrap: wrap; }
  .livrari-btn-add { padding: 14px 26px; font-size: 1rem; }
  .livrari-modal-success,
  .livrari-modal-error {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 18px;
    font-weight: 500;
    font-size: 0.9375rem;
  }
  .livrari-modal-success { display: none; background: rgba(16, 185, 129, 0.2); color: #34D399; border: 1px solid rgba(16, 185, 129, 0.3); }
  .livrari-modal-success.is-visible { display: block; }
  .livrari-modal-error { display: none; background: rgba(239, 68, 68, 0.15); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3); }
  .livrari-modal-error.is-visible { display: block; }

  @media (max-width: 600px) {
    .livrari-add-row { grid-template-columns: 1fr; }
    .livrari-page { padding: 20px 16px 32px; }
    .livrari-card { padding: 20px; }
    .livrari-filters { gap: 16px; }
    .livrari-filters select { min-width: 100%; }
    .livrari-search-wrap { min-width: 100%; }
  }

  /* ---------- Admin: KPI & extra polish ---------- */
  .livrari-page.livrari-page--admin { max-width: 1320px; padding: 32px 24px 48px; }
  .livrari-page--admin .livrari-card {
    border-radius: 18px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3), 0 0 0 1px rgba(255, 255, 255, 0.04);
  }
  .livrari-page--admin .livrari-admin-kpi { padding: 0; overflow: hidden; }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-header {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 28px 32px 26px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    background: linear-gradient(90deg, rgba(255, 238, 0, 0.06) 0%, transparent 100%);
  }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(255, 238, 0, 0.2) 0%, rgba(250, 204, 21, 0.08) 100%);
    border: 1px solid rgba(255, 238, 0, 0.25);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--brand);
    flex-shrink: 0;
  }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-title {
    margin: 0 0 4px 0;
    font-size: 1.375rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: -0.02em;
  }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-subtitle {
    margin: 0;
    font-size: 0.875rem;
    color: var(--muted);
    font-weight: 400;
  }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-body {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 28px;
    padding: 28px 32px 32px;
  }
  .livrari-page--admin .livrari-kpi-total-card {
    background: linear-gradient(145deg, rgba(255, 238, 0, 0.1) 0%, rgba(250, 204, 21, 0.05) 100%);
    border: 1px solid rgba(255, 238, 0, 0.2);
    border-radius: 16px;
    padding: 28px;
    display: flex;
    align-items: center;
    gap: 20px;
    height: fit-content;
  }
  .livrari-page--admin .livrari-kpi-total-icon {
    width: 52px;
    height: 52px;
    border-radius: 12px;
    background: rgba(255, 238, 0, 0.18);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.375rem;
    color: var(--brand);
    flex-shrink: 0;
  }
  .livrari-page--admin .livrari-kpi-total-content { display: flex; flex-direction: column; gap: 6px; min-width: 0; }
  .livrari-page--admin .livrari-kpi-total-label {
    font-size: 0.6875rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--muted);
  }
  .livrari-page--admin .livrari-kpi-total-value {
    font-size: 2rem;
    font-weight: 800;
    color: var(--brand);
    letter-spacing: -0.03em;
    line-height: 1.1;
  }
  .livrari-page--admin .livrari-per-operator {
    background: rgba(31, 41, 55, 0.6);
    border-radius: 14px;
    padding: 24px;
    border: 1px solid rgba(255, 255, 255, 0.06);
  }
  .livrari-page--admin .livrari-per-operator-title {
    margin: 0 0 18px 0;
    font-size: 1rem;
    font-weight: 700;
    color: #E5E7EB;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .livrari-page--admin .livrari-per-operator-title i { color: var(--brand); }
  .livrari-page--admin .livrari-per-operator-table-wrap {
    overflow: hidden;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
  }
  .livrari-page--admin .livrari-per-operator-table { margin: 0; }
  .livrari-page--admin .livrari-per-operator-table thead tr { background: rgba(55, 65, 81, 0.5); }
  .livrari-page--admin .livrari-per-operator-table th {
    padding: 14px 20px;
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--muted);
    font-weight: 700;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    text-align: left;
  }
  .livrari-page--admin .livrari-per-operator-table th.livrari-th-num { text-align: right; width: 120px; }
  .livrari-page--admin .livrari-per-operator-table td {
    padding: 14px 20px;
    font-size: 0.9375rem;
    color: #E5E7EB;
    border-bottom: 1px solid rgba(255, 255, 255, 0.04);
    background: rgba(17, 24, 39, 0.35);
  }
  .livrari-page--admin .livrari-per-operator-table td.livrari-td-num {
    text-align: right;
    color: var(--brand);
    font-size: 1rem;
    font-weight: 700;
  }
  .livrari-page--admin .livrari-per-operator-table tbody tr:hover td { background: rgba(255, 238, 0, 0.06); }
  .livrari-page--admin .livrari-per-operator-table tbody tr:last-child td { border-bottom: none; }
  .livrari-page--admin .livrari-operator-name { font-weight: 600; color: #fff; }
  .livrari-page--admin .livrari-table-wrap { border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.06); }
  .livrari-page--admin .livrari-table th { padding: 14px 18px; font-size: 0.6875rem; }
  .livrari-page--admin .livrari-table td { padding: 14px 18px; font-size: 0.875rem; }
  .livrari-page--admin .livrari-table tbody tr:hover { background: rgba(255, 238, 0, 0.04); }

  @media (max-width: 768px) {
    .livrari-page--admin .livrari-admin-kpi .livrari-kpi-body { grid-template-columns: 1fr; padding: 20px 20px 24px; }
    .livrari-page--admin .livrari-admin-kpi .livrari-kpi-header { padding: 20px 20px 18px; flex-wrap: wrap; }
    .livrari-page--admin .livrari-kpi-total-card { flex-direction: column; align-items: flex-start; }
    .livrari-page--admin .livrari-kpi-total-value { font-size: 1.75rem; }
  }
</style>
@endpush

@section('content')
<div class="livrari-page {{ $isAdmin ? 'livrari-page--admin' : '' }}">
  <h1><i class="fas fa-truck" style="margin-right: 10px;"></i> Livrări</h1>
  <p class="subtitle">{{ $isAdmin ? 'Toate livrările și KPI per operator' : 'Adaugă și vizualizează livrările tale' }}</p>

  @php
    $filters = $filters ?? ['luna' => '', 'operator_id' => '', 'locatie' => '', 'cauta' => '', 'data' => ''];
    $operatorsForFilter = $operatorsForFilter ?? collect();
  @endphp

  <form method="get" action="{{ route('livrari') }}" class="livrari-card livrari-filters-card" style="margin-bottom: 20px;">
    <h2 style="margin-bottom: 16px;"><i class="fas fa-filter"></i> Filtre și căutare</h2>
    <div class="livrari-filters">
      <div class="livrari-search-wrap">
        <label for="cauta">Căutare</label>
        <input type="text" id="cauta" name="cauta" value="{{ $filters['cauta'] ?? '' }}" placeholder="Nr. comandă, adresă, raion, nr. telefon..." class="livrari-search-input" maxlength="200">
      </div>
      <div>
        <label for="data_livrarii">Data livrării</label>
        <input type="date" id="data_livrarii" name="data" value="{{ $filters['data'] ?? '' }}" class="livrari-search-input" style="min-width: 160px;">
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
  <div class="livrari-card livrari-admin-kpi">
    <div class="livrari-kpi-header">
      <div class="livrari-kpi-header-icon"><i class="fas fa-truck"></i></div>
      <div>
        <h2 class="livrari-kpi-title">KPI Livrări</h2>
        <p class="livrari-kpi-subtitle">Rezumat livrări și distribuție per operator</p>
      </div>
    </div>
    <div class="livrari-kpi-body">
      <div class="livrari-kpi-total-card">
        <div class="livrari-kpi-total-icon"><i class="fas fa-boxes-stacked"></i></div>
        <div class="livrari-kpi-total-content">
          <span class="livrari-kpi-total-label">Total livrări</span>
          <span class="livrari-kpi-total-value">{{ number_format($totalLivrari, 0, ',', '.') }}</span>
        </div>
      </div>
      <div class="livrari-per-operator">
        <h3 class="livrari-per-operator-title"><i class="fas fa-users"></i> Livrări per operator</h3>
        <div class="livrari-per-operator-table-wrap">
          <table class="livrari-table livrari-per-operator-table">
            <thead>
              <tr>
                <th>Operator</th>
                <th class="livrari-th-num">Nr. livrări</th>
              </tr>
            </thead>
            <tbody>
              @foreach($perOperator as $row)
              <tr>
                <td><span class="livrari-operator-name">{{ $row->nume }}</span></td>
                <td class="livrari-td-num"><strong>{{ $row->total }}</strong></td>
              </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  @endif

  @if(!$isAdmin)
  <div class="livrari-operator-actions">
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
      <p class="livrari-add-hint" style="margin-bottom: 16px;">Locația (În Chișinău / În afara) se stabilește automat după raion. După salvare poți introduce altă livrare sau închide.</p>
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
            <label for="modal_localitate">Raion *</label>
            <input type="text" id="modal_localitate" name="localitate" required maxlength="255" placeholder="Ex: Chișinău, Bălți, Orhei...">
          </div>
          <div class="livrari-add-field">
            <label for="modal_nr_client">Nr. de telefon *</label>
            <input type="text" id="modal_nr_client" name="nr_client" required maxlength="100" placeholder="Ex: 069123456 sau 37378123456">
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
          <button type="button" class="livrari-modal-close livrari-btn-secondary" id="livrariModalCloseBottom">Închide</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  <div class="livrari-card livrari-table-card">
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
            <th>Acțiuni</th>
          </tr>
        </thead>
        <tbody id="livrariTableBody">
          @forelse($livrari as $l)
          <tr data-id="{{ $l->id }}"
              data-numar-comanda="{{ e($l->numar_comanda) }}"
              data-data="{{ $l->data->format('Y-m-d') }}"
              data-localitate="{{ e($l->localitate) }}"
              data-adresa="{{ e($l->adresa_livrarii) }}"
              data-nr-client="{{ e($l->nr_client) }}"
              data-data-livrarii="{{ $l->data_livrarii->format('Y-m-d') }}">
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
            <td>
              <button type="button" class="livrari-btn livrari-btn-edit" aria-label="Editează" title="Editează">
                <i class="fas fa-edit"></i> Editează
              </button>
            </td>
          </tr>
          @empty
          <tr id="livrariEmptyRow">
            <td colspan="{{ $isAdmin ? 10 : 9 }}" style="text-align: center; color: #9CA3AF; padding: 32px;">Nicio livrare înregistrată.</td>
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

  <!-- Modal Editează livrare (disponibil pentru operator și admin) -->
  <div class="livrari-modal-overlay" id="livrariEditModal" aria-hidden="true" data-update-url="{{ route('livrari.update', ['livrare' => '__ID__']) }}">
    <div class="livrari-modal" role="dialog" aria-labelledby="livrariEditModalTitle">
      <div class="livrari-modal-header">
        <h2 class="livrari-modal-title" id="livrariEditModalTitle"><i class="fas fa-edit"></i> Editează livrarea</h2>
        <button type="button" class="livrari-modal-close" id="livrariEditModalClose" aria-label="Închide">&times;</button>
      </div>
      <div class="livrari-modal-success" id="livrariEditModalSuccess"></div>
      <div class="livrari-modal-error" id="livrariEditModalError"></div>
      <form id="livrariEditForm" method="post" class="livrari-add-form" action="">
        @csrf
        @method('PUT')
        <input type="hidden" name="data" id="edit-data-comanda">
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="edit_data_livrarii">Data livrării *</label>
            <input type="date" id="edit_data_livrarii" name="data_livrarii" required>
          </div>
          <div class="livrari-add-field">
            <label for="edit_numar_comanda">Număr comandă *</label>
            <input type="text" id="edit_numar_comanda" name="numar_comanda" required maxlength="100" placeholder="Ex: CMD-001">
          </div>
        </div>
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="edit_localitate">Raion *</label>
            <input type="text" id="edit_localitate" name="localitate" required maxlength="255" placeholder="Ex: Chișinău, Bălți...">
          </div>
          <div class="livrari-add-field">
            <label for="edit_nr_client">Nr. de telefon *</label>
            <input type="text" id="edit_nr_client" name="nr_client" required maxlength="100" placeholder="Ex: 069123456">
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="edit_adresa_livrarii">Adresa *</label>
            <input type="text" id="edit_adresa_livrarii" name="adresa_livrarii" required maxlength="500" placeholder="Strada, nr., bloc...">
          </div>
        </div>
        <div class="livrari-add-actions">
          <button type="submit" class="livrari-btn livrari-btn-primary livrari-btn-add" id="livrariEditModalSubmitBtn"><i class="fas fa-check"></i> Salvează modificările</button>
          <button type="button" class="livrari-modal-close livrari-btn-secondary" id="livrariEditModalCloseBottom">Închide</button>
        </div>
      </form>
    </div>
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
    var dataYmd = livrare.data ? livrare.data.split('.').reverse().join('-') : '';
    var dataLivrariiYmd = livrare.data_livrarii ? livrare.data_livrarii.split('.').reverse().join('-') : '';
    var tr = document.createElement('tr');
    tr.setAttribute('data-id', livrare.id || '');
    tr.setAttribute('data-numar-comanda', livrare.numar_comanda || '');
    tr.setAttribute('data-data', dataYmd);
    tr.setAttribute('data-localitate', livrare.localitate || '');
    tr.setAttribute('data-adresa', livrare.adresa_livrarii || '');
    tr.setAttribute('data-nr-client', livrare.nr_client || '');
    tr.setAttribute('data-data-livrarii', dataLivrariiYmd);
    tr.innerHTML =
      '<td>' + (livrare.numar_comanda || '') + '</td>' +
      '<td>' + (livrare.data || '') + '</td>' +
      '<td>' + (livrare.localitate || '—') + '</td>' +
      '<td>' + (livrare.adresa_livrarii || '') + '</td>' +
      '<td>' + (livrare.nr_client || '') + '</td>' +
      '<td>' + (livrare.data_livrarii || '') + '</td>' +
      '<td>' + (livrare.locatie || '—') + '</td>' +
      (isAdmin ? '<td>—</td>' : '') +
      '<td><button type="button" class="livrari-btn livrari-btn-edit" aria-label="Editează" title="Editează"><i class="fas fa-edit"></i> Editează</button></td>';
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

@push('scripts')
<script>
(function() {
  var editModal = document.getElementById('livrariEditModal');
  var editForm = document.getElementById('livrariEditForm');
  var editSuccessEl = document.getElementById('livrariEditModalSuccess');
  var editErrorEl = document.getElementById('livrariEditModalError');
  var editSubmitBtn = document.getElementById('livrariEditModalSubmitBtn');
  var editDataComanda = document.getElementById('edit-data-comanda');
  var editDataLivrarii = document.getElementById('edit_data_livrarii');
  var updateUrlTemplate = editModal ? editModal.getAttribute('data-update-url') : '';
  var currentEditRow = null;

  function openEditModal() {
    if (editModal) {
      editModal.classList.add('is-open');
      editModal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
    }
  }
  function closeEditModal() {
    if (editModal) {
      editModal.classList.remove('is-open');
      editModal.setAttribute('aria-hidden', 'true');
      document.body.style.overflow = '';
      currentEditRow = null;
    }
  }
  function showEditSuccess(msg) {
    if (editSuccessEl) { editSuccessEl.textContent = msg; editSuccessEl.classList.add('is-visible'); }
    if (editErrorEl) editErrorEl.classList.remove('is-visible');
  }
  function showEditError(msg) {
    if (editErrorEl) { editErrorEl.textContent = msg; editErrorEl.classList.add('is-visible'); }
    if (editSuccessEl) editSuccessEl.classList.remove('is-visible');
  }
  function hideEditMessages() {
    if (editSuccessEl) editSuccessEl.classList.remove('is-visible');
    if (editErrorEl) editErrorEl.classList.remove('is-visible');
  }

  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.livrari-btn-edit');
    if (!btn) return;
    var row = btn.closest('tr');
    if (!row || !row.dataset.id) return;
    currentEditRow = row;
    var id = row.dataset.id;
    document.getElementById('edit_numar_comanda').value = row.dataset.numarComanda || '';
    editDataComanda.value = row.dataset.data || '';
    editDataLivrarii.value = row.dataset.dataLivrarii || '';
    document.getElementById('edit_localitate').value = row.dataset.localitate || '';
    document.getElementById('edit_nr_client').value = row.dataset.nrClient || '';
    document.getElementById('edit_adresa_livrarii').value = row.dataset.adresa || '';
    editForm.action = updateUrlTemplate.replace('__ID__', id);
    hideEditMessages();
    openEditModal();
  });

  if (editDataLivrarii && editDataComanda) {
    editDataLivrarii.addEventListener('change', function() { editDataComanda.value = editDataLivrarii.value; });
  }

  [document.getElementById('livrariEditModalClose'), document.getElementById('livrariEditModalCloseBottom')].forEach(function(el) {
    if (el) el.addEventListener('click', closeEditModal);
  });
  if (editModal) {
    editModal.addEventListener('click', function(e) {
      if (e.target === editModal) closeEditModal();
    });
  }

  if (editForm) {
    editForm.addEventListener('submit', function(e) {
      e.preventDefault();
      if (!currentEditRow || !editForm.action) return;
      if (editSubmitBtn) editSubmitBtn.disabled = true;
      hideEditMessages();
      var formData = new FormData(editForm);
      var csrf = document.querySelector('meta[name="csrf-token"]');
      if (csrf) formData.append('_token', csrf.getAttribute('content'));

      fetch(editForm.action, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      })
      .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, status: r.status, data: d }; }).catch(function() { return { ok: false, status: r.status, data: {} }; }); })
      .then(function(result) {
        if (result.ok && result.data.success && result.data.livrare) {
          showEditSuccess(result.data.message || 'Livrarea a fost actualizată.');
          var L = result.data.livrare;
          var cells = currentEditRow.querySelectorAll('td');
          cells[0].textContent = L.numar_comanda || '';
          cells[1].textContent = L.data || '';
          cells[2].textContent = L.localitate || '—';
          cells[3].textContent = L.adresa_livrarii || '';
          cells[4].textContent = L.nr_client || '';
          cells[5].textContent = L.data_livrarii || '';
          cells[6].textContent = L.locatie || '—';
          currentEditRow.dataset.numarComanda = L.numar_comanda || '';
          currentEditRow.dataset.data = L.data ? L.data.split('.').reverse().join('-') : '';
          currentEditRow.dataset.dataLivrarii = L.data_livrarii ? L.data_livrarii.split('.').reverse().join('-') : '';
          currentEditRow.dataset.localitate = L.localitate || '';
          currentEditRow.dataset.nrClient = L.nr_client || '';
          currentEditRow.dataset.adresa = L.adresa_livrarii || '';
        } else {
          var msg = result.data && result.data.message ? result.data.message : 'Eroare la actualizare.';
          if (result.data && result.data.errors) msg = Object.values(result.data.errors).flat().join(' ');
          showEditError(msg);
        }
      })
      .catch(function() { showEditError('Eroare de rețea. Încearcă din nou.'); })
      .finally(function() { if (editSubmitBtn) editSubmitBtn.disabled = false; });
    });
  }
})();
</script>
@endpush
@endsection
