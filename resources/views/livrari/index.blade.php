@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', auth()->check() && auth()->user()->isOperator() ? 'Livrări – VOLTA STATS' : 'Livrări – VOLTA')
@section('header-title', 'Livrări')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/operatori.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<style>
  /* ---------- Page base ---------- */
  .livrari-page {
    padding: 0 0 var(--space-8, 32px);
    max-width: 1400px;
    margin: 0 auto;
    font-family: 'Noto Sans', system-ui, sans-serif;
    color: var(--text-primary, #E5E7EB);
    min-height: 60vh;
  }
  .livrari-page--modern .livrari-filters-block .livrari-filters-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px;
    align-items: end;
    width: 100%;
  }
  .livrari-page--modern .livrari-filters-block .livrari-filters-row .livrari-btn-primary {
    width: 100%;
    justify-content: center;
    min-height: 46px;
    border-radius: 12px;
  }
  .livrari-page h1 {
    color: var(--text-primary, #f8fafc);
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
  .livrari-page-lead {
    margin: 0 0 var(--space-6, 24px);
  }
  /* ---------- Overview ---------- */
  .livrari-overview {
    display: grid;
    grid-template-columns: minmax(0, 0.9fr) minmax(0, 1.55fr);
    gap: 14px;
    margin: 0 0 18px;
  }
  .livrari-overview-stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 14px;
  }
  .livrari-overview-card {
    min-height: 132px;
    padding: 18px 20px;
    margin: 0;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    position: relative;
  }
  .livrari-overview-label,
  .livrari-overview-period {
    color: #94a3b8;
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
  }
  .livrari-overview-label i { color: var(--brand, #ffee00); margin-right: 7px; }
  .livrari-overview-value {
    color: #fff;
    font-family: 'Space Grotesk', 'Noto Sans', sans-serif;
    font-size: clamp(1.6rem, 2.5vw, 2.05rem);
    font-weight: 700;
    letter-spacing: -0.045em;
    line-height: 1;
    position: relative;
    z-index: 1;
  }
  .livrari-overview-localitate {
    font-size: clamp(1.1rem, 1.8vw, 1.35rem);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
  .livrari-overview-subvalue {
    position: relative;
    z-index: 1;
    color: #cbd5e1;
    font-size: 0.8125rem;
    line-height: 1.4;
  }
  .livrari-overview-chart {
    min-height: 132px;
    padding: 16px 20px 12px;
    margin: 0;
  }
  .livrari-overview-chart-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 4px;
  }
  .livrari-overview-chart-title {
    margin: 0;
    color: #fff;
    font-size: 1rem;
    font-weight: 700;
  }
  .livrari-overview-chart-title i { color: var(--brand, #ffee00); margin-right: 8px; }
  .livrari-overview-chart-body { height: 104px; position: relative; }
  .livrari-overview-empty {
    height: 100%;
    display: grid;
    place-items: center;
    color: #94a3b8;
    font-size: 0.875rem;
    text-align: center;
  }
  @media (max-width: 1060px) {
    .livrari-overview { grid-template-columns: 1fr; }
  }
  @media (max-width: 600px) {
    .livrari-overview-stats { grid-template-columns: 1fr; gap: 10px; }
    .livrari-overview-card { min-height: 112px; padding: 16px; }
    .livrari-overview-chart { min-height: 150px; padding: 16px; }
    .livrari-overview-chart-body { height: 100px; }
    .livrari-overview-chart-head { display: block; }
    .livrari-overview-period { display: block; margin-top: 5px; font-size: 0.6875rem; }
  }
  .livrari-section-switch {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin: 0 0 16px;
  }
  .livrari-section-btn {
    appearance: none;
    border: 1px solid rgba(148, 163, 184, 0.28);
    background: rgba(15, 23, 42, 0.75);
    color: #cbd5e1;
    font-size: 0.8125rem;
    font-weight: 700;
    letter-spacing: 0.01em;
    border-radius: 999px;
    padding: 9px 16px;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
  }
  .livrari-section-btn:hover {
    border-color: rgba(255, 238, 0, 0.42);
    color: #f8fafc;
  }
  .livrari-section-btn.is-active {
    border-color: rgba(255, 238, 0, 0.72);
    color: #fff;
    background: linear-gradient(180deg, rgba(36, 46, 67, 0.95) 0%, rgba(22, 31, 49, 0.98) 100%);
    box-shadow: 0 10px 24px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(255, 238, 0, 0.15) inset;
  }
  .livrari-section-panel {
    margin-bottom: 0;
  }
  .livrari-section-panel.is-collapsed {
    display: none;
  }

  /* ---------- Cards ---------- */
  .livrari-card {
    background: linear-gradient(165deg, rgba(20, 31, 55, 0.94) 0%, rgba(9, 17, 35, 0.97) 100%);
    border-radius: 18px;
    padding: 28px;
    margin-bottom: 24px;
    border: 1px solid rgba(255, 255, 255, 0.07);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.28);
    transition: box-shadow 0.2s ease, border-color 0.2s ease;
  }
  .livrari-card:hover {
    border-color: rgba(255, 238, 0, 0.14);
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.32);
  }
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
  .livrari-card h2 i { color: var(--brand, #ffee00); opacity: 0.92; }

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
  .livrari-filters-card {
    padding: 18px 20px;
    margin-bottom: 20px;
    background: var(--bg-elevated, #16233d);
    border: 1px solid var(--border-primary, #334155);
    box-shadow: var(--shadow-md, 0 4px 8px rgba(0, 0, 0, 0.4));
  }
  .livrari-filters-card h2 { margin-bottom: 14px; font-size: 1rem; }
  .livrari-filters-row {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 14px;
  }
  .livrari-filters-block {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 14px;
  }
  .livrari-filters-block + .livrari-filters-block {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid rgba(255, 255, 255, 0.06);
  }
  .livrari-filters-block-title {
    width: 100%;
    margin: 0 0 8px 0;
    font-size: 0.6875rem;
    font-weight: 600;
    color: var(--text-tertiary, #94a3b8);
    text-transform: uppercase;
    letter-spacing: 0.06em;
  }
  .livrari-filters {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 14px;
  }
  .livrari-filters label {
    display: block;
    color: var(--muted);
    font-size: 0.6875rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 4px;
  }
  .livrari-search-wrap { min-width: 180px; }
  .livrari-search-input {
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    background: rgba(9, 17, 35, 0.78);
    color: #fff;
    min-width: 100%;
    font-size: 0.8125rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-search-input::placeholder { color: #6B7280; }
  .livrari-search-input:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.18);
  }
  .livrari-filters select {
    padding: 10px 14px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 10px;
    background: linear-gradient(180deg, rgba(20, 31, 55, 0.98) 0%, rgba(9, 17, 35, 0.98) 100%);
    color: #F8FAFC;
    min-width: 140px;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    appearance: none;
    background-image:
      linear-gradient(45deg, transparent 50%, var(--brand, #FFEE00) 50%),
      linear-gradient(135deg, var(--brand, #FFEE00) 50%, transparent 50%);
    background-position:
      calc(100% - 15px) calc(50% - 2px),
      calc(100% - 10px) calc(50% - 2px);
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
    padding-right: 32px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
  }
  .livrari-filters select:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.5);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.35) inset;
  }
  .livrari-filters select:hover {
    border-color: rgba(255, 238, 0, 0.32);
    background: linear-gradient(180deg, rgba(20, 31, 55, 1) 0%, rgba(9, 17, 35, 1) 100%);
  }
  .livrari-filters .livrari-btn { padding: 8px 16px; font-size: 0.8125rem; }
  .livrari-perioada-wrap { min-width: 200px; flex: 1 1 220px; }
  .livrari-perioada-wrap label { display: block; margin-bottom: 4px; font-size: 0.6875rem; }
  .livrari-perioada-field {
    display: flex;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    background: rgba(9, 17, 35, 0.78);
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-perioada-field:hover { border-color: rgba(255, 238, 0, 0.28); }
  .livrari-perioada-field:focus-within {
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.18);
  }
  .livrari-perioada-field input {
    flex: 1;
    padding: 8px 12px;
    border: none;
    background: transparent;
    color: #E5E7EB;
    font-size: 0.8125rem;
    cursor: pointer;
    min-width: 0;
  }
  .livrari-perioada-field input::placeholder { color: #6B7280; }
  .livrari-perioada-field input:focus { outline: none; }
  .livrari-perioada-icon {
    padding: 8px 10px;
    color: var(--brand, #ffee00);
    opacity: 0.95;
    font-size: 0.875rem;
  }
  .livrari-perioada-presets {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
    align-items: center;
  }
  .livrari-preset-btn {
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.05);
    color: #9CA3AF;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
    white-space: nowrap;
  }
  .livrari-preset-btn:hover {
    background: rgba(255, 238, 0, 0.12);
    color: var(--brand, #ffee00);
    border-color: rgba(255, 238, 0, 0.35);
  }
  .livrari-filter-item { display: flex; flex-direction: column; gap: 4px; }
  .livrari-filter-item label { margin-bottom: 0; font-size: 0.6875rem; }
  .livrari-filter-item select {
    padding: 10px 14px;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 10px;
    background: linear-gradient(180deg, rgba(20, 31, 55, 0.98) 0%, rgba(9, 17, 35, 0.98) 100%);
    color: #F8FAFC;
    min-width: 140px;
    font-size: 0.875rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%239CA3AF' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 32px;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
  }
  .livrari-filters select option,
  .livrari-filter-item select option {
    background: #0b1630;
    color: #F8FAFC;
  }
  .livrari-filters select option:checked,
  .livrari-filter-item select option:checked {
    background: #182743;
    color: var(--brand, #FFEE00);
    font-weight: 700;
  }
  .livrari-filters select:focus-visible,
  .livrari-filter-item select:focus-visible {
    outline: 2px solid rgba(255, 238, 0, 0.35);
    outline-offset: 1px;
  }
  .livrari-filter-item select:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.5);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.2), 0 0 0 1px rgba(0, 0, 0, 0.35) inset;
  }
  .livrari-filter-item select:hover {
    border-color: rgba(255, 238, 0, 0.32);
    background: linear-gradient(180deg, rgba(20, 31, 55, 1) 0%, rgba(9, 17, 35, 1) 100%);
  }
  .livrari-search-wrap.livrari-filter-item { min-width: 160px; flex: 1 1 180px; }
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
    text-decoration: none;
    transition: transform 0.15s ease, box-shadow 0.2s ease;
  }
  .livrari-btn:hover { transform: translateY(-1px); }
  .livrari-btn-primary {
    background: linear-gradient(135deg, #facc15 0%, #ffee00 55%, #e6d600 100%);
    color: #0a0a0a;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 238, 0, 0.15);
  }
  .livrari-btn-primary:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 238, 0, 0.28);
  }
  .livrari-btn-muted {
    background: rgba(9, 17, 35, 0.8);
    color: #cbd5e1;
    border: 1px solid rgba(148, 163, 184, 0.28);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.04);
  }
  .livrari-btn-muted:hover {
    color: #f8fafc;
    border-color: rgba(255, 238, 0, 0.35);
  }
  .livrari-btn-muted.is-active {
    color: #111827;
    border-color: rgba(255, 238, 0, 0.72);
    background: linear-gradient(135deg, rgba(255, 238, 0, 0.95) 0%, rgba(250, 204, 21, 0.95) 100%);
    box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 238, 0, 0.18);
  }
  .livrari-btn-edit {
    width: 36px;
    height: 36px;
    padding: 0;
    font-size: 0.9rem;
    background: rgba(255, 238, 0, 0.08);
    color: var(--brand, #ffee00);
    border: 1px solid rgba(255, 238, 0, 0.28);
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
  }
  .livrari-btn-edit:hover {
    background: rgba(255, 238, 0, 0.16);
    color: #fff;
    border-color: rgba(255, 238, 0, 0.45);
  }
  .livrari-actions-cell {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 8px;
  }
  .livrari-btn-delete {
    width: 36px;
    height: 36px;
    padding: 0;
    font-size: 0.9rem;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    border: 1px solid rgba(248, 113, 113, 0.45);
    background: rgba(239, 68, 68, 0.12);
    color: #fca5a5;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s, color 0.2s, border-color 0.2s;
  }
  .livrari-btn-delete:hover {
    background: rgba(239, 68, 68, 0.22);
    color: #fecaca;
    border-color: rgba(248, 113, 113, 0.65);
  }
  .livrari-btn-delete:disabled {
    opacity: 0.55;
    cursor: not-allowed;
  }

  /* ---------- Data table ---------- */
  .livrari-table-card { padding: 18px 20px; }
  .livrari-table-card h2 { margin-bottom: 14px; font-size: 1rem; }
  .livrari-table-wrap {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid rgba(255, 255, 255, 0.06);
    background: rgba(6, 14, 30, 0.72);
  }
  .livrari-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
  }
  .livrari-table th {
    background: var(--bg-secondary, #16233d);
    color: var(--text-secondary, #cbd5e1);
    padding: 10px 14px;
    text-align: left;
    font-weight: 700;
    font-size: 0.625rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    border-bottom: 1px solid var(--border-primary, #334155);
  }
  .livrari-table td {
    padding: 10px 14px;
    font-size: 0.8125rem;
    color: #E5E7EB;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  .livrari-table tbody tr {
    transition: background 0.15s ease;
  }
  .livrari-table tbody tr:hover { background: rgba(255, 255, 255, 0.035); }
  .livrari-table tbody tr:last-child td { border-bottom: none; }

  /* ---------- Pagination (RO + stil Volta) ---------- */
  .livrari-pagination {
    margin-top: 28px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.08);
  }
  .livrari-pag { display: flex; flex-direction: column; align-items: center; gap: 14px; }
  .livrari-pag__row {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 10px 12px;
    width: 100%;
  }
  .livrari-pag__edge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 18px;
    border-radius: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    line-height: 1.2;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(20, 31, 55, 0.9);
    color: #f1f5f9;
    text-decoration: none;
    transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease;
    white-space: nowrap;
  }
  .livrari-pag__edge i { font-size: 0.75rem; opacity: 0.9; }
  .livrari-pag__edge--link:hover {
    background: rgba(255, 238, 0, 0.12);
    border-color: rgba(255, 238, 0, 0.35);
    color: var(--brand, #ffee00);
    transform: translateY(-1px);
  }
  .livrari-pag__edge--link:active { transform: translateY(0); }
  .livrari-pag__edge--disabled {
    opacity: 0.42;
    cursor: not-allowed;
    color: #94a3b8;
    border-color: rgba(255, 255, 255, 0.06);
    background: rgba(9, 17, 35, 0.7);
  }
  .livrari-pag__pages {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 4px 8px;
    border-radius: 14px;
    background: rgba(9, 17, 35, 0.76);
    border: 1px solid rgba(255, 255, 255, 0.08);
  }
  .livrari-pag__pages > li { margin: 0; padding: 0; }
  .livrari-pag__gap span {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 28px;
    color: #64748b;
    font-weight: 700;
    font-size: 0.875rem;
    user-select: none;
  }
  .livrari-pag__num {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 40px;
    height: 40px;
    padding: 0 8px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 700;
    text-decoration: none;
    border: 1px solid transparent;
    transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
  }
  .livrari-pag__num--link {
    color: #e2e8f0;
    background: rgba(255, 255, 255, 0.04);
    border-color: rgba(255, 255, 255, 0.08);
  }
  .livrari-pag__num--link:hover {
    background: rgba(255, 238, 0, 0.12);
    border-color: rgba(255, 238, 0, 0.25);
    color: var(--brand, #ffee00);
  }
  .livrari-pag__num--current {
    color: #0f172a;
    background: linear-gradient(135deg, #facc15 0%, #ffee00 55%, #e6d600 100%);
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 1px rgba(0, 0, 0, 0.2), 0 4px 14px rgba(255, 238, 0, 0.2);
  }
  .livrari-pag__meta {
    margin: 0;
    font-size: 0.8125rem;
    color: #94a3b8;
    text-align: center;
    max-width: 42rem;
    line-height: 1.45;
  }
  .livrari-pag__meta strong { color: #e2e8f0; font-weight: 600; }
  @media (max-width: 640px) {
    .livrari-pag__edge span { display: none; }
    .livrari-pag__edge { padding: 10px 14px; }
    .livrari-pag__edge i { font-size: 0.875rem; }
    .livrari-pag__meta { font-size: 0.75rem; }
  }

  /* ---------- Add livrare button (operator) ---------- */
  .livrari-operator-actions { margin-bottom: 28px; }
  .livrari-btn-open-modal {
    padding: 14px 28px;
    border-radius: 14px;
    font-weight: 700;
    font-size: 1rem;
    cursor: pointer;
    border: none;
    background: linear-gradient(135deg, #facc15 0%, #ffee00 50%, #e6d600 100%);
    color: #0a0a0a;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 6px 22px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 238, 0, 0.2);
    transition: transform 0.15s ease, box-shadow 0.2s ease;
  }
  .livrari-btn-open-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.48), 0 0 0 1px rgba(255, 238, 0, 0.35);
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
    background: linear-gradient(165deg, #1e293b 0%, #0f172a 100%);
    border-radius: 20px;
    padding: 32px;
    max-width: 540px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    border: 1px solid rgba(255, 238, 0, 0.12);
    box-shadow: 0 24px 64px rgba(0, 0, 0, 0.55), 0 0 0 1px rgba(255, 238, 0, 0.06);
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
    color: var(--text-primary, #f8fafc);
    font-size: 1.25rem;
    font-weight: 700;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .livrari-modal-title i { color: var(--brand, #ffee00); }
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
    color: var(--text-secondary, #cbd5e1);
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
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.16);
  }
  .livrari-add-field input::placeholder { color: #6B7280; }
  .livrari-raion-field { position: relative; }
  .livrari-raion-menu {
    position: absolute;
    left: 0;
    right: 0;
    top: calc(100% + 6px);
    z-index: 1200;
    max-height: 220px;
    overflow-y: auto;
    padding: 6px;
    border: 1px solid rgba(255, 238, 0, 0.2);
    border-radius: 10px;
    background: rgba(15, 23, 42, 0.98);
    box-shadow: 0 18px 36px rgba(0, 0, 0, 0.38);
  }
  .livrari-raion-menu[hidden] { display: none; }
  .livrari-raion-option {
    display: flex;
    width: 100%;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    min-height: 34px;
    padding: 8px 10px;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: #E5E7EB;
    cursor: pointer;
    font: inherit;
    font-size: 0.875rem;
    text-align: left;
  }
  .livrari-raion-option span,
  .livrari-raion-option small {
    min-width: 0;
    overflow-wrap: anywhere;
  }
  .livrari-raion-option small {
    color: #94A3B8;
    font-size: 0.75rem;
  }
  .livrari-raion-option:hover,
  .livrari-raion-option.is-active {
    background: rgba(255, 238, 0, 0.14);
    color: #FFEE00;
  }
  .livrari-raion-option:hover small,
  .livrari-raion-option.is-active small { color: #FDE68A; }
  .livrari-raion-empty {
    padding: 10px;
    color: #9CA3AF;
    font-size: 0.875rem;
  }
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
  .livrari-export-modal { max-width: 760px; }
  .livrari-export-form { display: flex; flex-direction: column; gap: 18px; }
  .livrari-export-section {
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    padding: 16px;
    background: rgba(15, 23, 42, 0.35);
  }
  .livrari-export-section legend,
  .livrari-export-section-title {
    margin: 0 0 12px;
    color: #f8fafc;
    font-size: 0.875rem;
    font-weight: 700;
  }
  .livrari-export-choices {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 10px;
  }
  .livrari-export-choice,
  .livrari-export-check {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #e5e7eb;
    font-size: 0.875rem;
    cursor: pointer;
  }
  .livrari-export-choice {
    padding: 12px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.04);
  }
  .livrari-export-choice input,
  .livrari-export-check input { accent-color: #ffee00; }
  .livrari-export-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 14px;
  }
  .livrari-export-field { display: flex; flex-direction: column; gap: 6px; }
  .livrari-export-field label {
    color: #9CA3AF;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
  }
  .livrari-export-field input,
  .livrari-export-field select {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.7);
    color: #fff;
  }
  .livrari-export-field select option { color: #111827; }
  .livrari-export-columns {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 10px;
  }
  .livrari-export-tools { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
  .livrari-export-tool {
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.05);
    color: #e5e7eb;
    padding: 7px 10px;
    font-size: 0.8125rem;
    font-weight: 700;
    cursor: pointer;
  }
  .livrari-export-tool:hover { color: #ffee00; border-color: rgba(255, 238, 0, 0.28); }
  .livrari-map-modal { max-width: 1120px; }
  .livrari-map-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    color: #94A3B8;
    font-size: 0.875rem;
  }
  .livrari-map-toolbar-actions {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
  }
  .livrari-map-select {
    min-width: 210px;
    padding: 10px 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.75);
    color: #fff;
    font-weight: 700;
  }
  .livrari-map-select option { color: #111827; }
  .livrari-map-layout {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 300px;
    gap: 16px;
  }
  .livrari-live-map {
    min-height: 540px;
    border-radius: 14px;
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: #0f172a;
  }
  .livrari-map-side {
    min-height: 540px;
    max-height: 540px;
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    background: rgba(15, 23, 42, 0.48);
    padding: 14px;
  }
  .livrari-map-total {
    display: grid;
    gap: 4px;
    padding-bottom: 12px;
    margin-bottom: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  }
  .livrari-map-total strong { color: #fff; font-size: 1.75rem; line-height: 1; }
  .livrari-map-total span { color: #94A3B8; font-size: 0.8125rem; }
  .livrari-map-detail {
    display: grid;
    gap: 8px;
    padding: 12px;
    margin-bottom: 12px;
    border: 1px solid rgba(255, 238, 0, 0.16);
    border-radius: 12px;
    background: rgba(255, 238, 0, 0.06);
  }
  .livrari-map-detail-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #F8FAFC;
    font-weight: 900;
  }
  .livrari-map-detail-count {
    color: #0F172A;
    background: #FFEE00;
    border-radius: 999px;
    padding: 3px 9px;
    font-size: 0.75rem;
    flex-shrink: 0;
  }
  .livrari-map-detail-period {
    color: #CBD5E1;
    font-size: 0.8125rem;
  }
  .livrari-map-detail-localitati {
    margin: 0;
    padding-left: 18px;
    color: #CBD5E1;
    font-size: 0.8125rem;
    line-height: 1.45;
  }
  .livrari-map-list { display: grid; gap: 8px; }
  .livrari-map-row {
    width: 100%;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.04);
    color: #E5E7EB;
    padding: 10px;
    display: grid;
    gap: 4px;
    text-align: left;
    cursor: pointer;
  }
  .livrari-map-row:hover {
    border-color: rgba(255, 238, 0, 0.28);
    background: rgba(255, 238, 0, 0.08);
  }
  .livrari-map-row-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    font-weight: 800;
  }
  .livrari-map-row-count {
    color: #0f172a;
    background: #ffee00;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 0.75rem;
    flex-shrink: 0;
  }
  .livrari-map-row-sub {
    color: #94A3B8;
    font-size: 0.75rem;
    line-height: 1.35;
  }
  .livrari-map-empty {
    padding: 18px 10px;
    color: #94A3B8;
    text-align: center;
  }
  .livrari-map-popup strong { color: #111827; }
  .livrari-map-popup ul { margin: 6px 0 0; padding-left: 18px; }
  .livrari-map-popup li { margin: 2px 0; }
  .livrari-map-refresh { white-space: nowrap; }
  .livrari-map-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 12px;
    margin-top: 12px;
    color: #CBD5E1;
    font-size: 0.75rem;
  }
  .livrari-map-legend span {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .livrari-map-legend i {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
  }
  .leaflet-container { font-family: 'Noto Sans', system-ui, sans-serif; }

  @media (max-width: 600px) {
    .livrari-add-row { grid-template-columns: 1fr; }
    .livrari-page { padding: 20px 16px 32px; }
    .livrari-card { padding: 20px; }
    .livrari-filters { gap: 16px; }
    .livrari-filters-block + .livrari-filters-block { margin-top: 20px; padding-top: 20px; }
    .livrari-filters select { min-width: 100%; }
    .livrari-filter-item select { min-width: 100%; }
    .livrari-search-wrap { min-width: 100%; }
    .livrari-perioada-wrap { min-width: 100%; flex: 1 1 100%; }
    .livrari-map-layout { grid-template-columns: 1fr; }
    .livrari-live-map { min-height: 420px; }
    .livrari-map-side { min-height: auto; max-height: 320px; }
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
    background: linear-gradient(90deg, rgba(255, 238, 0, 0.08) 0%, transparent 55%);
  }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-copy { min-width: 0; }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-export { margin-left: auto; white-space: nowrap; }
  .livrari-page--admin .livrari-admin-kpi .livrari-kpi-header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: linear-gradient(135deg, rgba(255, 238, 0, 0.2) 0%, rgba(250, 204, 21, 0.08) 100%);
    border: 1px solid rgba(255, 238, 0, 0.28);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: var(--brand, #ffee00);
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
    background: linear-gradient(145deg, rgba(255, 238, 0, 0.1) 0%, rgba(250, 204, 21, 0.04) 100%);
    border: 1px solid rgba(255, 238, 0, 0.22);
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
    background: rgba(255, 238, 0, 0.16);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.375rem;
    color: var(--brand, #ffee00);
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
    color: var(--brand, #ffee00);
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
  .livrari-page--admin .livrari-per-operator-title i { color: var(--brand, #ffee00); }
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
    color: var(--brand, #ffee00);
    font-size: 1rem;
    font-weight: 700;
  }
  .livrari-page--admin .livrari-per-operator-table tbody tr:hover td { background: rgba(255, 238, 0, 0.06); }
  .livrari-page--admin .livrari-per-operator-table tbody tr:last-child td { border-bottom: none; }
  .livrari-page--admin .livrari-operator-name { font-weight: 600; color: #fff; }
  .livrari-page--admin .livrari-table-wrap { border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.06); }
  .livrari-page--admin .livrari-table th { padding: 14px 18px; font-size: 0.6875rem; }
  .livrari-page--admin .livrari-table td { padding: 14px 18px; font-size: 0.875rem; }
  .livrari-page--admin .livrari-table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
  .livrari-alert-error {
    background: rgba(239, 68, 68, 0.15);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.35);
  }
  .livrari-page .livrari-card h2 {
    color: var(--text-primary, #f8fafc);
  }
  .livrari-page .livrari-card h2 i {
    color: var(--brand, #ffee00);
    opacity: 0.9;
  }
  .livrari-page .livrari-table-wrap {
    border: 1px solid var(--border-primary, #334155);
    background: var(--bg-secondary, #0f172a);
  }
  .livrari-page .livrari-table th {
    background: var(--bg-secondary, #1e293b);
    color: var(--text-secondary, #cbd5e1);
    border-bottom: 1px solid var(--border-primary, #334155);
  }
  .livrari-page .livrari-table td {
    color: var(--text-primary, #e5e7eb);
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
  }
  .livrari-page .livrari-table tbody tr:hover {
    background: rgba(255, 255, 255, 0.03);
  }
  .livrari-page .livrari-btn-primary {
    background: linear-gradient(135deg, var(--brand-dark, #facc15) 0%, var(--brand, #ffee00) 100%);
    color: #0a0a0a;
    box-shadow: var(--shadow-md, 0 4px 10px rgba(0, 0, 0, 0.35));
  }
  .livrari-page .livrari-btn-primary:hover {
    box-shadow: var(--shadow-lg, 0 8px 24px rgba(0, 0, 0, 0.45));
  }
  .livrari-page .livrari-filters-row {
    gap: 12px;
  }
  .livrari-page .livrari-filter-item select,
  .livrari-page .livrari-search-input,
  .livrari-page .livrari-perioada-field {
    background: var(--bg-secondary, #1e293b);
    border: 1px solid var(--border-primary, #334155);
    color: var(--text-primary, #e5e7eb);
  }
  .livrari-page .livrari-filter-item select:focus,
  .livrari-page .livrari-search-input:focus,
  .livrari-page .livrari-perioada-field:focus-within {
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.16);
  }

  @media (max-width: 768px) {
    .livrari-page--admin .livrari-admin-kpi .livrari-kpi-body { grid-template-columns: 1fr; padding: 20px 20px 24px; }
    .livrari-page--admin .livrari-admin-kpi .livrari-kpi-header { padding: 20px 20px 18px; flex-wrap: wrap; }
    .livrari-page--admin .livrari-admin-kpi .livrari-kpi-export { margin-left: 0; width: 100%; justify-content: center; }
    .livrari-page--admin .livrari-kpi-total-card { flex-direction: column; align-items: flex-start; }
    .livrari-page--admin .livrari-kpi-total-value { font-size: 1.75rem; }
  }
  /* Flatpickr – calendar compact și stilizat */
  .livrari-page .flatpickr-calendar {
    background: #1F2937;
    border: 1px solid rgba(255, 255, 255, 0.12);
    box-shadow: 0 8px 32px rgba(0,0,0,0.4);
    border-radius: 12px;
  }
  .livrari-page .flatpickr-calendar.open { z-index: 1100; }
  .livrari-page .flatpickr-months { background: #1F2937; padding: 8px 4px 4px; }
  .livrari-page .flatpickr-current-month {
    color: #fff;
    font-size: 0.875rem;
    font-weight: 600;
    padding: 0 0 6px 0;
  }
  .livrari-page .flatpickr-prev-month,
  .livrari-page .flatpickr-next-month {
    padding: 6px 10px;
    top: 8px;
  }
  .livrari-page .flatpickr-prev-month svg,
  .livrari-page .flatpickr-next-month svg { fill: #FFEE00; width: 12px; height: 12px; }
  .livrari-page .flatpickr-prev-month:hover svg,
  .livrari-page .flatpickr-next-month:hover svg { fill: #fff; }
  .livrari-page .flatpickr-weekdays { background: #1F2937; }
  .livrari-page span.flatpickr-weekday {
    color: #9CA3AF;
    font-size: 0.6875rem;
    font-weight: 600;
    letter-spacing: 0.02em;
  }
  .livrari-page .flatpickr-days {
    background: #1F2937;
    border-color: rgba(255,255,255,0.06);
    padding: 4px;
  }
  .livrari-page .dayContainer { padding: 0; }
  .livrari-page .flatpickr-day {
    color: #E5E7EB;
    font-size: 0.8125rem;
    max-width: 32px;
    height: 32px;
    line-height: 32px;
    border-radius: 8px;
  }
  .livrari-page .flatpickr-day:hover {
    background: rgba(255, 238, 0, 0.2);
    border-color: rgba(255, 238, 0, 0.3);
    color: #fff;
  }
  .livrari-page .flatpickr-day.selected,
  .livrari-page .flatpickr-day.startRange,
  .livrari-page .flatpickr-day.endRange {
    background: #FFEE00;
    border-color: #FFEE00;
    color: #0a0a0a;
    font-weight: 600;
  }
  .livrari-page .flatpickr-day.inRange {
    background: rgba(255, 238, 0, 0.22);
    border-color: rgba(255, 238, 0, 0.25);
    box-shadow: none;
    color: #fff;
  }
  .livrari-page .flatpickr-day.flatpickr-disabled { color: #6B7280; }
  .livrari-page .flatpickr-monthDropdown-months {
    background: #111827;
    color: #fff;
    font-size: 0.8125rem;
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.1);
  }
  .livrari-page .numInputWrapper { width: 36px; }
  .livrari-page .numInputWrapper input {
    font-size: 0.8125rem;
    padding: 4px 6px;
    background: rgba(17, 24, 39, 0.8);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 6px;
    color: #fff;
  }
  .livrari-page .numInputWrapper span.arrowUp:after { border-bottom-color: #FFEE00; }
  .livrari-page .numInputWrapper span.arrowDown:after { border-top-color: #FFEE00; }
  .livrari-date-display {
    width: 100%;
    padding: 14px 16px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 10px;
    background: rgba(17, 24, 39, 0.6);
    color: #E5E7EB;
    font-size: 0.9375rem;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-date-display:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.16);
  }
  .livrari-flatpickr.flatpickr-calendar {
    background: linear-gradient(165deg, #1e293b 0%, #0f172a 100%);
    border: 1px solid rgba(255, 238, 0, 0.18);
    box-shadow: 0 20px 46px rgba(0, 0, 0, 0.45);
    border-radius: 12px;
  }
  .livrari-flatpickr .flatpickr-months,
  .livrari-flatpickr .flatpickr-weekdays { background: transparent; }
  .livrari-flatpickr .flatpickr-current-month,
  .livrari-flatpickr .flatpickr-current-month .flatpickr-monthDropdown-months,
  .livrari-flatpickr .flatpickr-current-month input.cur-year { color: #fff; }
  .livrari-flatpickr .flatpickr-prev-month svg,
  .livrari-flatpickr .flatpickr-next-month svg { fill: var(--brand, #FFEE00); }
  .livrari-flatpickr .flatpickr-day { color: #E5E7EB; border-radius: 8px; }
  .livrari-flatpickr .flatpickr-day:hover {
    background: rgba(255, 238, 0, 0.2);
    border-color: rgba(255, 238, 0, 0.32);
    color: #fff;
  }
  .livrari-flatpickr .flatpickr-day.selected,
  .livrari-flatpickr .flatpickr-day.startRange,
  .livrari-flatpickr .flatpickr-day.endRange {
    background: var(--brand, #FFEE00);
    border-color: var(--brand, #FFEE00);
    color: #0a0a0a;
  }
  .livrari-flatpickr .flatpickr-day.inRange {
    background: rgba(255, 238, 0, 0.2);
    border-color: rgba(255, 238, 0, 0.25);
    color: #fff;
  }
  .livrari-flatpickr .flatpickr-day.today {
    border-color: rgba(255, 238, 0, 0.45);
    color: var(--brand, #FFEE00);
  }
  .livrari-flatpickr .flatpickr-day.today:hover {
    background: rgba(255, 238, 0, 0.2);
    color: #fff;
  }
  .livrari-flatpickr .flatpickr-day.flatpickr-disabled,
  .livrari-flatpickr .flatpickr-day.flatpickr-disabled:hover {
    color: #64748b;
    background: transparent;
    border-color: transparent;
  }
  .livrari-flatpickr .flatpickr-weekday {
    color: #9CA3AF;
    font-weight: 600;
    font-size: 0.6875rem;
  }
  .livrari-flatpickr .flatpickr-monthDropdown-months,
  .livrari-flatpickr .numInputWrapper input {
    background: linear-gradient(180deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.95) 100%);
    color: #F8FAFC;
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 6px;
  }
  .livrari-flatpickr .flatpickr-monthDropdown-months {
    padding-right: 24px;
    appearance: none;
    background-image: linear-gradient(45deg, transparent 50%, var(--brand, #FFEE00) 50%), linear-gradient(135deg, var(--brand, #FFEE00) 50%, transparent 50%);
    background-position: calc(100% - 14px) calc(50% - 2px), calc(100% - 9px) calc(50% - 2px);
    background-size: 5px 5px, 5px 5px;
    background-repeat: no-repeat;
  }
  .livrari-flatpickr .flatpickr-monthDropdown-months option {
    background: #0f172a;
    color: #f8fafc;
  }
  .livrari-flatpickr .flatpickr-monthDropdown-months option:checked,
  .livrari-flatpickr .flatpickr-monthDropdown-months option:hover {
    background: #1e293b;
    color: var(--brand, #FFEE00);
  }
  .livrari-flatpickr .numInputWrapper span.arrowUp:after { border-bottom-color: var(--brand, #FFEE00); }
  .livrari-flatpickr .numInputWrapper span.arrowDown:after { border-top-color: var(--brand, #FFEE00); }

  /* ---------- Neutral dark element palette (global consistency) ---------- */
  .livrari-card {
    background: linear-gradient(165deg, rgba(28, 32, 42, 0.95) 0%, rgba(16, 18, 24, 0.98) 100%);
  }
  .livrari-filters-card { background: #1a1d26; }
  .livrari-search-input,
  .livrari-perioada-field {
    background: rgba(20, 22, 30, 0.82);
  }
  .livrari-filters select,
  .livrari-filter-item select {
    background: linear-gradient(180deg, rgba(34, 38, 49, 0.98) 0%, rgba(20, 22, 30, 0.98) 100%);
  }
  .livrari-filters select:hover,
  .livrari-filter-item select:hover {
    background: linear-gradient(180deg, rgba(40, 45, 58, 1) 0%, rgba(24, 27, 36, 1) 100%);
  }
  .livrari-btn-muted { background: rgba(24, 27, 36, 0.86); }
  .livrari-table-wrap { background: rgba(14, 16, 22, 0.72); }
  .livrari-table th { background: #232836; }
  .livrari-table tbody tr:hover { background: rgba(255, 255, 255, 0.04); }
  .livrari-pag__edge { background: rgba(28, 32, 42, 0.92); }
  .livrari-pag__edge--disabled { background: rgba(20, 22, 30, 0.72); }
  .livrari-pag__pages { background: rgba(20, 22, 30, 0.8); }
  .livrari-modal { background: linear-gradient(165deg, #232836 0%, #171b24 100%); }
</style>
@endpush

@section('content')
<div class="livrari-page livrari-page--modern {{ $isAdmin ? 'livrari-page--admin' : '' }} {{ !$isAdmin ? 'livrari-page--operator' : '' }}">
  <p class="rapoarte-lead livrari-page-lead">
    {{ $isAdmin ? 'Toate livrările și KPI per operator, în același format vizual ca Dashboard/Rapoarte.' : 'Adaugă și vizualizează livrările tale, cu filtre rapide și tabel unificat.' }}
  </p>

  @php
    $filters = $filters ?? ['luna' => '', 'operator_id' => '', 'locatie' => '', 'cauta' => '', 'fara_raion' => '', 'data' => '', 'data_de_la' => '', 'data_pana' => ''];
    $operatorsForFilter = $operatorsForFilter ?? collect();
    $livrariLocalitati = collect($livrariLocalitati ?? []);
    $livrariRaioane = collect($livrariRaioane ?? []);
    $overview = $overview ?? ['total' => (int) ($totalLivrari ?? 0), 'top_localitate' => null, 'chart' => ['labels' => [], 'values' => [], 'granularity' => 'day', 'period_label' => '']];
    $overviewTopLocalitate = $overview['top_localitate'] ?? null;
    $overviewChart = $overview['chart'] ?? ['labels' => [], 'values' => [], 'period_label' => ''];
  @endphp

  @if($isAdmin)
  <div class="livrari-section-switch" id="livrariSectionSwitch" data-default-section="operare">
    <button type="button" class="livrari-section-btn is-active" data-section-target="operare" aria-pressed="true">
      <i class="fas fa-list-check" aria-hidden="true"></i> Operare livrări
    </button>
    <button type="button" class="livrari-section-btn" data-section-target="analiza" aria-pressed="false">
      <i class="fas fa-chart-line" aria-hidden="true"></i> KPI și analiză
    </button>
  </div>
  @endif

  <section class="livrari-section-panel" data-section-panel="operare">
  <form method="get" action="{{ route('livrari') }}" class="livrari-card livrari-filters-card">
    <h2><i class="fas fa-filter"></i> Filtre și căutare</h2>

    <div class="livrari-filters-block">
      <span class="livrari-filters-block-title">Perioadă</span>
      <div class="livrari-filters-row">
        <div class="livrari-perioada-wrap">
          <label for="livrari_perioada_input">Interval date</label>
          <div class="livrari-perioada-field" id="livrariPerioadaTrigger">
            <input type="text" id="livrari_perioada_input" readonly placeholder="Click pentru a alege perioada..." value="{{ isset($filters['data_de_la']) && isset($filters['data_pana']) && $filters['data_de_la'] !== '' && $filters['data_pana'] !== '' ? \Carbon\Carbon::parse($filters['data_de_la'])->format('d.m.Y') . ' – ' . \Carbon\Carbon::parse($filters['data_pana'])->format('d.m.Y') : '' }}">
            <span class="livrari-perioada-icon"><i class="fas fa-calendar-alt"></i></span>
          </div>
          <input type="hidden" name="data_de_la" id="data_de_la" value="{{ $filters['data_de_la'] ?? '' }}">
          <input type="hidden" name="data_pana" id="data_pana" value="{{ $filters['data_pana'] ?? '' }}">
          <div class="livrari-perioada-presets">
            <button type="button" class="livrari-preset-btn" data-days="7">7 zile</button>
            <button type="button" class="livrari-preset-btn" data-days="30">30 zile</button>
            <button type="button" class="livrari-preset-btn" data-range="month">Luna aceasta</button>
            <button type="button" class="livrari-preset-btn" data-range="prev-month">Luna trecută</button>
          </div>
        </div>
      </div>
    </div>

    <div class="livrari-filters-block">
      <span class="livrari-filters-block-title">Alte filtre</span>
      <div class="livrari-filters-row">
        <div class="livrari-search-wrap livrari-filter-item">
          <label for="cauta">Căutare</label>
          <input type="text" id="cauta" name="cauta" value="{{ $filters['cauta'] ?? '' }}" placeholder="Nr. comandă, localitate, raion..." class="livrari-search-input" maxlength="200">
        </div>
        <div class="livrari-filter-item">
          <label for="luna">Lună</label>
          <select name="luna" id="luna">
            <option value="">Toate lunile</option>
            @foreach(range(now()->year, now()->year - 2, -1) as $y)
              @foreach(range(1, 12) as $m)
                @php $ym = sprintf('%04d-%02d', $y, $m); @endphp
                <option value="{{ $ym }}" {{ ($filters['luna'] ?? '') == $ym ? 'selected' : '' }}>{{ \App\Support\LunaRomana::labelFromYm($ym) }}</option>
              @endforeach
            @endforeach
          </select>
        </div>
        @if($isAdmin)
        <div class="livrari-filter-item">
          <label for="operator_id">Operator</label>
          <select name="operator_id" id="operator_id">
            <option value="">Toți operatorii</option>
            @foreach($operatorsForFilter as $u)
              <option value="{{ $u->id }}" {{ ($filters['operator_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ trim($u->full_name ?? $u->name ?? '') ?: $u->username }}</option>
            @endforeach
          </select>
        </div>
        @endif
        <div class="livrari-filter-item">
          <label for="locatie">Locație</label>
          <select name="locatie" id="locatie">
            <option value="">Toate</option>
            <option value="chisinau" {{ ($filters['locatie'] ?? '') === 'chisinau' ? 'selected' : '' }}>În Chișinău</option>
            <option value="afara" {{ ($filters['locatie'] ?? '') === 'afara' ? 'selected' : '' }}>În afara</option>
          </select>
        </div>
      </div>
    </div>
  </form>
  </section>

  <section class="livrari-overview" aria-label="Rezumat livrări">
    <div class="livrari-overview-stats">
      <article class="livrari-card livrari-overview-card">
        <span class="livrari-overview-label"><i class="fas fa-truck" aria-hidden="true"></i> Nr. livrări</span>
        <strong class="livrari-overview-value">{{ number_format((int) ($overview['total'] ?? 0), 0, ',', '.') }}</strong>
        <span class="livrari-overview-subvalue">în selecția curentă</span>
      </article>
      <article class="livrari-card livrari-overview-card">
        <span class="livrari-overview-label"><i class="fas fa-location-dot" aria-hidden="true"></i> Top localitate</span>
        <strong class="livrari-overview-value livrari-overview-localitate" title="{{ $overviewTopLocalitate['nume'] ?? '' }}">{{ $overviewTopLocalitate['nume'] ?? '—' }}</strong>
        <span class="livrari-overview-subvalue">
          @if($overviewTopLocalitate)
            {{ number_format((int) $overviewTopLocalitate['total'], 0, ',', '.') }} livrări · {{ number_format((float) $overviewTopLocalitate['share'], 1, ',', '.') }}% din total
          @else
            Nu există localități în selecția curentă
          @endif
        </span>
      </article>
    </div>
    <article class="livrari-card livrari-overview-chart">
      <div class="livrari-overview-chart-head">
        <h2 class="livrari-overview-chart-title"><i class="fas fa-chart-line" aria-hidden="true"></i> Evoluție livrări</h2>
        <span class="livrari-overview-period">{{ $overviewChart['period_label'] ?? '' }}</span>
      </div>
      <div class="livrari-overview-chart-body">
        @if(!empty($overviewChart['labels']))
          <canvas id="livrariOverviewChart" role="img" aria-label="Evoluția numărului de livrări în perioada selectată"></canvas>
        @else
          <div class="livrari-overview-empty">Nu există date pentru perioada selectată.</div>
        @endif
      </div>
    </article>
  </section>

  @if(session('success'))
  <div class="livrari-alert livrari-alert-success">
    <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
  </div>
  @endif
  @if($errors->any())
  <div class="livrari-alert livrari-alert-error">
    <i class="fas fa-exclamation-circle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
  @endif

  <section class="livrari-section-panel {{ $isAdmin ? 'is-collapsed' : '' }}" data-section-panel="analiza">
  @if($isAdmin && ($perOperator->isNotEmpty() || $totalLivrari > 0))
  <div class="livrari-card livrari-admin-kpi">
    <div class="livrari-kpi-header">
      <div class="livrari-kpi-header-icon"><i class="fas fa-truck"></i></div>
      <div class="livrari-kpi-copy">
        <h2 class="livrari-kpi-title">KPI Livrări</h2>
        <p class="livrari-kpi-subtitle">Rezumat livrări și distribuție per operator</p>
      </div>
      <button type="button" id="livrariExportTotalsExcelBtn" class="livrari-btn livrari-btn-primary livrari-kpi-export volta-export-btn">
        <i class="fas fa-file-excel" aria-hidden="true"></i> Export totaluri
      </button>
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
          <table id="livrariTotalsTable" class="livrari-table livrari-per-operator-table">
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
  </section>

  <section class="livrari-section-panel" data-section-panel="operare">
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
      <p class="livrari-add-hint">Scrie localitatea, iar raionul se completează automat. Dacă aceeași localitate există în mai multe raioane, alege raionul corect.</p>
      <div class="livrari-modal-success" id="livrariModalSuccess"></div>
      <div class="livrari-modal-error" id="livrariModalError"></div>
      <form id="livrariAddForm" action="{{ route('livrari.store') }}" method="post" class="livrari-add-form">
        @csrf
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="modal_data_livrarii">Data livrării *</label>
            <input type="date" id="modal_data_livrarii" name="data_livrarii" value="{{ date('Y-m-d') }}" lang="ro-RO" required>
          </div>
          <div class="livrari-add-field">
            <label for="modal_numar_comanda">Număr comandă *</label>
            <input type="text" id="modal_numar_comanda" name="numar_comanda" required maxlength="100" placeholder="Ex: CMD-001">
          </div>
        </div>
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="modal_localitate">Localitate *</label>
            <input type="text" id="modal_localitate" name="localitate" required maxlength="255" autocomplete="off" placeholder="Ex: Sipoteni">
          </div>
          <div class="livrari-add-field">
            <label for="modal_raion">Raion *</label>
            <select id="modal_raion" name="raion" required data-placeholder="Scrie localitatea mai întâi">
              <option value="">Scrie localitatea mai întâi</option>
            </select>
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="modal_adresa_livrarii">Adresa</label>
            <input type="text" id="modal_adresa_livrarii" name="adresa_livrarii" maxlength="500" placeholder="Strada, nr., bloc, scara, apartament, cod poștal">
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="modal_nr_client">Nr. de telefon *</label>
            <input type="text" id="modal_nr_client" name="nr_client" required maxlength="100" placeholder="Ex: 069123456 sau 37378123456">
          </div>
        </div>
        <div class="livrari-add-actions">
          <button type="submit" class="livrari-btn livrari-btn-primary livrari-btn-add" id="livrariModalSubmitBtn"><i class="fas fa-check"></i> Salvează livrarea</button>
          <button type="button" class="livrari-modal-close livrari-btn-secondary" id="livrariModalCloseBottom">Închide</button>
        </div>
      </form>
    </div>
  </div>
  @endif

  <div class="livrari-card livrari-table-card">
    <h2 style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
      <span><i class="fas fa-list"></i> {{ $isAdmin ? 'Toate livrările' : 'Livrările mele' }}</span>
      <span style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
        @if($isAdmin)
        <a href="{{ route('livrari.map', request()->query()) }}" class="livrari-btn livrari-btn-primary">
          <i class="fas fa-map-location-dot" aria-hidden="true"></i> Hartă live
        </a>
        @endif
        <button type="button" id="livrariExportExcelBtn" class="livrari-btn livrari-btn-primary volta-export-btn">
          <i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel
        </button>
      </span>
    </h2>
    <div class="livrari-table-wrap">
      <table id="livrariDataTable" class="livrari-table">
        <thead>
          <tr>
            <th>Număr comandă</th>
            <th>Data</th>
            <th>Localitate</th>
            <th>Raion</th>
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
              data-raion="{{ e($l->raion ?? '') }}"
              data-adresa="{{ e($l->adresa_livrarii) }}"
              data-nr-client="{{ e($l->nr_client) }}"
              data-data-livrarii="{{ $l->data_livrarii->format('Y-m-d') }}">
            <td>{{ $l->numar_comanda }}</td>
            <td>{{ $l->data->format('d.m.Y') }}</td>
            <td>{{ $l->localitate ?? '—' }}</td>
            <td>{{ ($l->raion ?? '') !== '' ? \App\Support\LocalitatiMoldova::administrativeUnitLabel((string) $l->raion) : '—' }}</td>
            <td>{{ $l->adresa_livrarii }}</td>
            <td>{{ $l->nr_client }}</td>
            <td>{{ $l->data_livrarii->format('d.m.Y') }}</td>
            <td>{{ isset($l->in_chisinau) ? ($l->in_chisinau ? 'În Chișinău' : 'În afara') : '—' }}</td>
            @if($isAdmin)
            <td>{{ $l->user ? (trim($l->user->full_name ?? $l->user->name ?? '') ?: $l->user->username) : '—' }}</td>
            @endif
            <td class="livrari-actions-cell">
              <button type="button" class="livrari-btn livrari-btn-edit" aria-label="Editează" title="Editează">
                <i class="fas fa-edit" aria-hidden="true"></i>
              </button>
              <button type="button" class="livrari-btn-delete" data-delete-url="{{ route('livrari.destroy', $l) }}" aria-label="Șterge livrarea" title="Șterge">
                <i class="fas fa-trash-alt" aria-hidden="true"></i>
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
    {{ $livrari->links('vendor.pagination.livrari') }}
  </div>
  </section>

  @php
    $totalLivrariExportValue = number_format((int) $totalLivrari, 0, ',', '.');
    $livrariExportColumns = [
      'Număr comandă',
      'Data',
      'Localitate',
      'Raion',
      'Adresa',
      'Nr. client',
      'Data livrării',
      'Locație',
    ];
    if ($isAdmin) {
      $livrariExportColumns[] = 'Operator';
    }
  @endphp
  <div class="livrari-modal-overlay" id="livrariExportModal" aria-hidden="true">
    <div class="livrari-modal livrari-export-modal" role="dialog" aria-labelledby="livrariExportModalTitle">
      <div class="livrari-modal-header">
        <h2 class="livrari-modal-title" id="livrariExportModalTitle"><i class="fas fa-file-excel"></i> Setări export</h2>
        <button type="button" class="livrari-modal-close" id="livrariExportModalClose" aria-label="Închide">&times;</button>
      </div>
      <form id="livrariExportForm" class="livrari-export-form">
        <fieldset class="livrari-export-section">
          <legend>Rânduri</legend>
          <div class="livrari-export-choices">
            <label class="livrari-export-choice">
              <input type="radio" name="export_scope" value="all" checked>
              <span>Toate rezultatele filtrate</span>
            </label>
            <label class="livrari-export-choice">
              <input type="radio" name="export_scope" value="page">
              <span>Doar pagina curentă</span>
            </label>
          </div>
        </fieldset>

        @if($isAdmin)
        <div class="livrari-export-section">
          <div class="livrari-export-field">
            <label for="livrariExportOperator">Operator</label>
            <select id="livrariExportOperator">
              <option value="">Toți operatorii</option>
              @foreach($operatorsForFilter as $u)
                @php
                  $operatorExportName = trim($u->full_name ?? $u->name ?? '') ?: $u->username;
                @endphp
                <option value="{{ $u->id }}" data-operator-name="{{ e($operatorExportName) }}" {{ ($filters['operator_id'] ?? '') == $u->id ? 'selected' : '' }}>
                  {{ $operatorExportName }}
                </option>
              @endforeach
            </select>
          </div>
        </div>
        @endif

        <div class="livrari-export-section">
          <p class="livrari-export-section-title">Fișier</p>
          <div class="livrari-export-grid">
            <div class="livrari-export-field">
              <label for="livrariExportFileName">Nume fișier</label>
              <input type="text" id="livrariExportFileName" value="livrari_tabel" maxlength="80">
            </div>
            <div class="livrari-export-field">
              <label for="livrariExportSheetName">Nume foaie</label>
              <input type="text" id="livrariExportSheetName" value="Livrari" maxlength="31">
            </div>
          </div>
        </div>

        <fieldset class="livrari-export-section">
          <legend>Coloane</legend>
          <div class="livrari-export-tools">
            <button type="button" class="livrari-export-tool" id="livrariExportSelectAll">Selectează toate</button>
            <button type="button" class="livrari-export-tool" id="livrariExportClearColumns">Debifează toate</button>
          </div>
          <div class="livrari-export-columns">
            @foreach($livrariExportColumns as $index => $label)
            <label class="livrari-export-check">
              <input type="checkbox" class="livrari-export-column" value="{{ $index }}" checked>
              <span>{{ $label }}</span>
            </label>
            @endforeach
          </div>
        </fieldset>

        @if($isAdmin)
        <label class="livrari-export-check livrari-export-section">
          <input type="checkbox" id="livrariExportIncludeTotals">
          <span>Include totalurile într-o foaie separată</span>
        </label>
        @endif

        <div class="livrari-add-actions">
          <button type="submit" class="livrari-btn livrari-btn-primary" id="livrariExportSubmitBtn">
            <i class="fas fa-file-export"></i> Exportă
          </button>
          <button type="button" class="livrari-modal-close livrari-btn-secondary" id="livrariExportModalCloseBottom">Închide</button>
        </div>
      </form>
    </div>
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
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="edit_data_livrarii">Data livrării *</label>
            <input type="date" id="edit_data_livrarii" name="data_livrarii" lang="ro-RO" required>
          </div>
          <div class="livrari-add-field">
            <label for="edit_numar_comanda">Număr comandă *</label>
            <input type="text" id="edit_numar_comanda" name="numar_comanda" required maxlength="100" placeholder="Ex: CMD-001">
          </div>
        </div>
        <div class="livrari-add-row">
          <div class="livrari-add-field">
            <label for="edit_localitate">Localitate *</label>
            <input type="text" id="edit_localitate" name="localitate" required maxlength="255" autocomplete="off" placeholder="Ex: Sipoteni">
          </div>
          <div class="livrari-add-field">
            <label for="edit_raion">Raion *</label>
            <select id="edit_raion" name="raion" required data-placeholder="Scrie localitatea mai întâi">
              <option value="">Scrie localitatea mai întâi</option>
            </select>
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="edit_adresa_livrarii">Adresa</label>
            <input type="text" id="edit_adresa_livrarii" name="adresa_livrarii" maxlength="500" placeholder="Strada, nr., bloc...">
          </div>
        </div>
        <div class="livrari-add-row livrari-add-row-full">
          <div class="livrari-add-field">
            <label for="edit_nr_client">Nr. de telefon *</label>
            <input type="text" id="edit_nr_client" name="nr_client" required maxlength="100" placeholder="Ex: 069123456">
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
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  var canvas = document.getElementById('livrariOverviewChart');
  if (!canvas || !window.Chart) return;

  var chartData = @json($overviewChart);
  var labels = Array.isArray(chartData.labels) ? chartData.labels : [];
  var values = Array.isArray(chartData.values) ? chartData.values : [];
  if (!labels.length || !values.length) return;

  var theme = window.VoltaChartTheme;
  var options = theme ? theme.cartesianDefaults({
    plugins: {
      legend: { display: false },
      tooltip: Object.assign({}, theme.tooltip(), {
        callbacks: {
          label: function (context) {
            return context.parsed.y + (context.parsed.y === 1 ? ' livrare' : ' livrări');
          }
        }
      })
    },
    scales: {
      x: {
        ticks: Object.assign({}, theme.ticks(9, 11), { maxTicksLimit: 8 }),
        grid: { display: false },
        border: { display: false }
      },
      y: {
        beginAtZero: true,
        ticks: Object.assign({}, theme.ticks(9, 11), { precision: 0, maxTicksLimit: 5 }),
        grid: theme.gridLines(),
        border: { display: false }
      }
    }
  }) : {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
  };

  new Chart(canvas, {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Livrări',
        data: values,
        borderColor: '#FFEE00',
        backgroundColor: 'rgba(255, 238, 0, 0.14)',
        fill: true,
        tension: 0.35,
        pointRadius: labels.length > 20 ? 0 : 3,
        pointHoverRadius: 5,
        pointBackgroundColor: '#FFEE00',
        pointBorderColor: '#16181f',
        pointBorderWidth: 2
      }]
    },
    options: options
  });
})();
</script>
<script>
(function() {
  var localitati = @json($livrariLocalitati);
  var controls = [
    { input: document.getElementById('modal_localitate'), raion: document.getElementById('modal_raion') },
    { input: document.getElementById('edit_localitate'), raion: document.getElementById('edit_raion') }
  ].filter(function(control) { return control.input && control.raion; });
  var activeIndex = -1;
  var checkComandaUrl = @json(route('livrari.check-comanda'));
  var duplicateComandaMessage = 'Această comandă există deja. Nu poate fi introdusă de două ori.';

  window.LivrariComandaDuplicate = {
    message: duplicateComandaMessage,
    check: function(numarComanda, ignoreId) {
      var value = String(numarComanda || '').trim();
      if (!value) {
        return Promise.resolve(false);
      }

      var url = new URL(checkComandaUrl, window.location.origin);
      url.searchParams.set('numar_comanda', value);
      if (ignoreId) {
        url.searchParams.set('ignore_id', ignoreId);
      }

      return fetch(url.toString(), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function(response) {
        if (!response.ok) return false;
        return response.json();
      })
      .then(function(data) {
        return !!(data && data.exists);
      })
      .catch(function() {
        return false;
      });
    }
  };

  function normalizeText(value) {
    return String(value || '')
      .toLowerCase()
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '');
  }

  function normalizeSearch(value) {
    return normalizeText(value).replace(/[^a-z0-9]+/g, '');
  }

  function isChisinauLocalitate(value) {
    return normalizeSearch(value) === 'chisinau';
  }

  function editDistance(a, b) {
    if (a === b) return 0;
    if (!a.length) return b.length;
    if (!b.length) return a.length;

    var previous = Array.from({ length: b.length + 1 }, function(_, index) { return index; });
    for (var i = 0; i < a.length; i++) {
      var current = [i + 1];
      for (var j = 0; j < b.length; j++) {
        var insert = current[j] + 1;
        var remove = previous[j + 1] + 1;
        var replace = previous[j] + (a[i] === b[j] ? 0 : 1);
        current.push(Math.min(insert, remove, replace));
      }
      previous = current;
    }

    return previous[b.length];
  }

  function localitateScore(needle, candidate) {
    if (!candidate) return null;
    if (candidate === needle) return 0;
    if (candidate.indexOf(needle) === 0) return 10 + Math.abs(candidate.length - needle.length);
    var position = candidate.indexOf(needle);
    if (position !== -1) return 25 + position + Math.abs(candidate.length - needle.length);
    if (needle.length < 3) return null;

    var distance = editDistance(needle, candidate);
    var threshold = needle.length <= 5 ? 1 : (needle.length <= 9 ? 2 : 3);
    if (distance > threshold) return null;

    return 50 + distance * 8 + Math.abs(candidate.length - needle.length);
  }

  function uniqueRaioane(items) {
    var seen = {};
    return items.map(function(item) { return item.raion; }).filter(function(raion) {
      var key = normalizeSearch(raion);
      if (seen[key]) return false;
      seen[key] = true;
      return true;
    }).sort(function(a, b) { return a.localeCompare(b, 'ro'); });
  }

  function exactLocalitateMatches(value) {
    var key = normalizeSearch(value);
    if (!key) return [];
    return localitati.filter(function(item) {
      return normalizeSearch(item.localitate) === key;
    });
  }

  function matchesLocalitati(query) {
    var needle = normalizeSearch(query);
    var grouped = {};
    localitati.forEach(function(item) {
      var key = normalizeSearch(item.localitate);
      var score = needle === '' ? 100 : localitateScore(needle, key);
      if (score === null) return;
      if (!grouped[key]) grouped[key] = { localitate: item.localitate, raioane: [], score: score };
      grouped[key].score = Math.min(grouped[key].score, score);
      if (grouped[key].raioane.indexOf(item.raion) === -1) grouped[key].raioane.push(item.raion);
    });

    return Object.keys(grouped)
      .map(function(key) { return grouped[key]; })
      .sort(function(a, b) {
        if (isChisinauLocalitate(a.localitate) !== isChisinauLocalitate(b.localitate)) {
          return isChisinauLocalitate(a.localitate) ? -1 : 1;
        }
        if (a.score !== b.score) return a.score - b.score;
        return a.localitate.localeCompare(b.localitate, 'ro');
      })
      .slice(0, 24);
  }

  function closeRaionMenus() {
    document.querySelectorAll('.livrari-raion-menu').forEach(function(menu) {
      menu.hidden = true;
      menu.innerHTML = '';
    });
    activeIndex = -1;
  }

  function setActive(menu, index) {
    var options = Array.from(menu.querySelectorAll('.livrari-raion-option'));
    activeIndex = options.length ? Math.max(0, Math.min(index, options.length - 1)) : -1;
    options.forEach(function(option, optionIndex) {
      option.classList.toggle('is-active', optionIndex === activeIndex);
    });
    if (options[activeIndex]) {
      options[activeIndex].scrollIntoView({ block: 'nearest' });
    }
  }

  function setRaionOptions(select, raioane, preferredRaion) {
    var placeholder = select.getAttribute('data-placeholder') || 'Scrie localitatea mai întâi';
    select.innerHTML = '';

    if (!raioane.length) {
      var empty = document.createElement('option');
      empty.value = '';
      empty.textContent = placeholder;
      select.appendChild(empty);
      select.value = '';
      return;
    }

    if (raioane.length > 1) {
      var prompt = document.createElement('option');
      prompt.value = '';
      prompt.textContent = 'Alege raionul';
      select.appendChild(prompt);
    }

    raioane.forEach(function(raion) {
      var option = document.createElement('option');
      option.value = raion;
      option.textContent = raion;
      select.appendChild(option);
    });

    if (preferredRaion && raioane.indexOf(preferredRaion) !== -1) {
      select.value = preferredRaion;
    } else if (raioane.length === 1) {
      select.value = raioane[0];
    } else {
      select.value = '';
    }
  }

  function syncRaion(control, preferredRaion) {
    var matches = exactLocalitateMatches(control.input.value);
    var raioane = uniqueRaioane(matches);

    if (!raioane.length) {
      var fuzzyMatch = matchesLocalitati(control.input.value)[0];
      if (fuzzyMatch && fuzzyMatch.score < 80) {
        raioane = fuzzyMatch.raioane;
        preferredRaion = fuzzyMatch.raioane.length === 1 ? fuzzyMatch.raioane[0] : preferredRaion;
      }
    }

    if (!raioane.length && preferredRaion) {
      raioane = [preferredRaion];
    }

    setRaionOptions(control.raion, raioane, preferredRaion);
  }

  function selectLocalitate(control, item) {
    control.input.value = item.localitate;
    setRaionOptions(control.raion, item.raioane, item.raioane.length === 1 ? item.raioane[0] : '');
    closeRaionMenus();
    control.input.focus();
  }

  function localitateMetaText(item) {
    var sameSingleRaion = item.raioane.length === 1 && normalizeSearch(item.raioane[0]) === normalizeSearch(item.localitate);
    var parts = [];

    if (!sameSingleRaion) {
      parts.push(item.raioane.join(', '));
    }

    if (item.score >= 50) {
      parts.push('potrivire inteligentă');
    }

    return parts.join(' · ');
  }

  function renderRaionMenu(control) {
    var input = control.input;
    var menu = input._raionMenu;
    if (!menu) return;
    var matches = matchesLocalitati(input.value);

    menu.innerHTML = '';
    if (!matches.length) {
      var empty = document.createElement('div');
      empty.className = 'livrari-raion-empty';
      empty.textContent = 'Nicio localitate găsită';
      menu.appendChild(empty);
      menu.hidden = false;
      activeIndex = -1;
      return;
    }

    matches.forEach(function(item) {
      var option = document.createElement('button');
      option.type = 'button';
      option.className = 'livrari-raion-option';
      var name = document.createElement('span');
      name.textContent = item.localitate;
      option.appendChild(name);
      var metaText = localitateMetaText(item);
      if (metaText) {
        var meta = document.createElement('small');
        meta.textContent = metaText;
        option.appendChild(meta);
      }
      option.addEventListener('mousedown', function(event) {
        event.preventDefault();
        selectLocalitate(control, item);
      });
      menu.appendChild(option);
    });

    menu.hidden = false;
    setActive(menu, -1);
  }

  controls.forEach(function(control) {
    var input = control.input;
    input.parentElement.classList.add('livrari-raion-field');
    syncRaion(control, control.raion.value);

    var menu = document.createElement('div');
    menu.className = 'livrari-raion-menu';
    menu.hidden = true;
    menu.setAttribute('role', 'listbox');
    input.parentElement.appendChild(menu);
    input._raionMenu = menu;

    input.addEventListener('input', function() {
      syncRaion(control, '');
      renderRaionMenu(control);
    });
    input.addEventListener('focus', function() {
      renderRaionMenu(control);
    });
    input.addEventListener('keydown', function(event) {
      if (menu.hidden) return;
      var options = Array.from(menu.querySelectorAll('.livrari-raion-option'));
      if (!options.length) return;

      if (event.key === 'ArrowDown') {
        event.preventDefault();
        setActive(menu, activeIndex + 1);
      } else if (event.key === 'ArrowUp') {
        event.preventDefault();
        setActive(menu, activeIndex - 1);
      } else if (event.key === 'Enter' && activeIndex >= 0 && options[activeIndex]) {
        event.preventDefault();
        options[activeIndex].dispatchEvent(new MouseEvent('mousedown', { bubbles: true, cancelable: true }));
      } else if (event.key === 'Escape') {
        closeRaionMenus();
      }
    });
  });

  document.addEventListener('mousedown', function(event) {
    if (!event.target.closest('.livrari-raion-field')) {
      closeRaionMenus();
    }
  });

  window.LivrariLocationLookup = {
    set: function(inputId, raionId, localitate, raion) {
      var control = {
        input: document.getElementById(inputId),
        raion: document.getElementById(raionId)
      };
      if (!control.input || !control.raion) return;
      control.input.value = localitate || '';
      syncRaion(control, raion || '');
    },
    reset: function(inputId, raionId) {
      this.set(inputId, raionId, '', '');
    }
  };
})();
</script>
@endpush
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
  var dataLivrarii = document.getElementById('modal_data_livrarii');
  var numarComanda = document.getElementById('modal_numar_comanda');
  var tbody = document.getElementById('livrariTableBody');
  var emptyRow = document.getElementById('livrariEmptyRow');
  var isAdmin = {{ $isAdmin ? 'true' : 'false' }};
  var duplicateComanda = false;
  var duplicateComandaTimer = null;
  var saveInProgress = false;

  function isTypingTarget(target) {
    if (!target) return false;
    var tag = (target.tagName || '').toLowerCase();
    return target.isContentEditable || tag === 'input' || tag === 'textarea' || tag === 'select';
  }

  function isAnyLivrariModalOpen() {
    return !!document.querySelector('.livrari-modal-overlay.is-open');
  }

  function openModal() {
    if (modal) {
      modal.classList.add('is-open');
      modal.setAttribute('aria-hidden', 'false');
      document.body.style.overflow = 'hidden';
      window.setTimeout(function() {
        if (numarComanda) numarComanda.focus();
      }, 40);
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
  function setDuplicateComanda(exists) {
    duplicateComanda = !!exists;
    updateSubmitState();
    if (duplicateComanda) {
      showError(window.LivrariComandaDuplicate.message);
    } else if (errorEl && errorEl.textContent === window.LivrariComandaDuplicate.message) {
      hideMessages();
    }
  }
  function updateSubmitState() {
    if (submitBtn) submitBtn.disabled = duplicateComanda || saveInProgress;
  }
  function scheduleDuplicateComandaCheck() {
    if (!numarComanda || !window.LivrariComandaDuplicate) return;
    window.clearTimeout(duplicateComandaTimer);
    duplicateComandaTimer = window.setTimeout(function() {
      var value = numarComanda.value.trim();
      if (!value) {
        setDuplicateComanda(false);
        return;
      }

      window.LivrariComandaDuplicate.check(value).then(function(exists) {
        if (numarComanda.value.trim() === value) {
          setDuplicateComanda(exists);
        }
      });
    }, 250);
  }
  function resetForm() {
    if (form) form.reset();
    if (dataLivrarii) dataLivrarii.value = new Date().toISOString().slice(0, 10);
    if (window.LivrariLocationLookup) window.LivrariLocationLookup.reset('modal_localitate', 'modal_raion');
    setDuplicateComanda(false);
  }
  function firstMissingRequired(root) {
    if (!root) return null;
    var fields = Array.from(root.querySelectorAll('[required]'));
    for (var i = 0; i < fields.length; i++) {
      var field = fields[i];
      if (!String(field.value || '').trim()) {
        return field;
      }
    }
    return null;
  }
  function livrariDestroyUrl(id) {
    return @json(url('livrari')) + '/' + id;
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
    tr.setAttribute('data-raion', livrare.raion || '');
    tr.setAttribute('data-adresa', livrare.adresa_livrarii || '');
    tr.setAttribute('data-nr-client', livrare.nr_client || '');
    tr.setAttribute('data-data-livrarii', dataLivrariiYmd);
    var delUrl = livrare.id ? livrariDestroyUrl(livrare.id) : '';
    tr.innerHTML =
      '<td>' + (livrare.numar_comanda || '') + '</td>' +
      '<td>' + (livrare.data || '') + '</td>' +
      '<td>' + (livrare.localitate || '—') + '</td>' +
      '<td>' + (livrare.raion || '—') + '</td>' +
      '<td>' + (livrare.adresa_livrarii || '') + '</td>' +
      '<td>' + (livrare.nr_client || '') + '</td>' +
      '<td>' + (livrare.data_livrarii || '') + '</td>' +
      '<td>' + (livrare.locatie || '—') + '</td>' +
      (isAdmin ? '<td>—</td>' : '') +
      '<td class="livrari-actions-cell">' +
      '<button type="button" class="livrari-btn livrari-btn-edit" aria-label="Editează" title="Editează"><i class="fas fa-edit" aria-hidden="true"></i></button>' +
      '<button type="button" class="livrari-btn-delete" data-delete-url="' + delUrl + '" aria-label="Șterge livrarea" title="Șterge"><i class="fas fa-trash-alt" aria-hidden="true"></i></button>' +
      '</td>';
    tbody.insertBefore(tr, tbody.firstChild);
  }

  if (openBtn) openBtn.addEventListener('click', function() { hideMessages(); resetForm(); openModal(); });
  if (numarComanda) numarComanda.addEventListener('input', scheduleDuplicateComandaCheck);
  document.addEventListener('keydown', function(e) {
    if (e.key && e.key.toLowerCase() === 'q' && !e.ctrlKey && !e.metaKey && !e.altKey && !e.repeat) {
      if (isTypingTarget(e.target) || isAnyLivrariModalOpen()) return;
      e.preventDefault();
      hideMessages();
      resetForm();
      openModal();
    }
    if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (closeBtnBottom) closeBtnBottom.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function(e) {
      if (e.target === modal) closeModal();
    });
  }

  if (form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var missing = firstMissingRequired(form);
      if (missing) {
        showError('Completează toate câmpurile obligatorii, inclusiv data.');
        missing.focus();
        return;
      }
      if (duplicateComanda) {
        showError(window.LivrariComandaDuplicate.message);
        return;
      }
      saveInProgress = true;
      updateSubmitState();
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
        saveInProgress = false;
        updateSubmitState();
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
  var editNumarComanda = document.getElementById('edit_numar_comanda');
  var editDataLivrarii = document.getElementById('edit_data_livrarii');
  var updateUrlTemplate = editModal ? editModal.getAttribute('data-update-url') : '';
  var currentEditRow = null;
  var editDuplicateComanda = false;
  var editDuplicateComandaTimer = null;
  var editSaveInProgress = false;

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
  function setEditDuplicateComanda(exists) {
    editDuplicateComanda = !!exists;
    updateEditSubmitState();
    if (editDuplicateComanda) {
      showEditError(window.LivrariComandaDuplicate.message);
    } else if (editErrorEl && editErrorEl.textContent === window.LivrariComandaDuplicate.message) {
      hideEditMessages();
    }
  }
  function updateEditSubmitState() {
    if (editSubmitBtn) editSubmitBtn.disabled = editDuplicateComanda || editSaveInProgress;
  }
  function firstMissingRequired(root) {
    if (!root) return null;
    var fields = Array.from(root.querySelectorAll('[required]'));
    for (var i = 0; i < fields.length; i++) {
      var field = fields[i];
      if (!String(field.value || '').trim()) {
        return field;
      }
    }
    return null;
  }
  function scheduleEditDuplicateComandaCheck() {
    if (!editNumarComanda || !window.LivrariComandaDuplicate) return;
    window.clearTimeout(editDuplicateComandaTimer);
    editDuplicateComandaTimer = window.setTimeout(function() {
      var value = editNumarComanda.value.trim();
      var ignoreId = currentEditRow ? currentEditRow.dataset.id : '';
      if (!value) {
        setEditDuplicateComanda(false);
        return;
      }

      window.LivrariComandaDuplicate.check(value, ignoreId).then(function(exists) {
        if (editNumarComanda.value.trim() === value) {
          setEditDuplicateComanda(exists);
        }
      });
    }, 250);
  }

  document.addEventListener('click', function(e) {
    var btn = e.target.closest('.livrari-btn-edit');
    if (!btn) return;
    var row = btn.closest('tr');
    if (!row || !row.dataset.id) return;
    currentEditRow = row;
    var id = row.dataset.id;
    if (editNumarComanda) editNumarComanda.value = row.dataset.numarComanda || '';
    editDataLivrarii.value = row.dataset.dataLivrarii || '';
    if (window.LivrariLocationLookup) {
      window.LivrariLocationLookup.set('edit_localitate', 'edit_raion', row.dataset.localitate || '', row.dataset.raion || '');
    } else {
      document.getElementById('edit_localitate').value = row.dataset.localitate || '';
      document.getElementById('edit_raion').value = row.dataset.raion || '';
    }
    document.getElementById('edit_nr_client').value = row.dataset.nrClient || '';
    document.getElementById('edit_adresa_livrarii').value = row.dataset.adresa || '';
    editForm.action = updateUrlTemplate.replace('__ID__', id);
    setEditDuplicateComanda(false);
    hideEditMessages();
    openEditModal();
  });

  if (editNumarComanda) editNumarComanda.addEventListener('input', scheduleEditDuplicateComandaCheck);

  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && editModal && editModal.classList.contains('is-open')) {
      closeEditModal();
    }
  });

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
      var missing = firstMissingRequired(editForm);
      if (missing) {
        showEditError('Completează toate câmpurile obligatorii, inclusiv data.');
        missing.focus();
        return;
      }
      if (!currentEditRow || !editForm.action) return;
      if (editDuplicateComanda) {
        showEditError(window.LivrariComandaDuplicate.message);
        return;
      }
      editSaveInProgress = true;
      updateEditSubmitState();
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
          cells[3].textContent = L.raion || '—';
          cells[4].textContent = L.adresa_livrarii || '';
          cells[5].textContent = L.nr_client || '';
          cells[6].textContent = L.data_livrarii || '';
          cells[7].textContent = L.locatie || '—';
          currentEditRow.dataset.numarComanda = L.numar_comanda || '';
          currentEditRow.dataset.data = L.data ? L.data.split('.').reverse().join('-') : '';
          currentEditRow.dataset.dataLivrarii = L.data_livrarii ? L.data_livrarii.split('.').reverse().join('-') : '';
          currentEditRow.dataset.localitate = L.localitate || '';
          currentEditRow.dataset.raion = L.raion || '';
          currentEditRow.dataset.nrClient = L.nr_client || '';
          currentEditRow.dataset.adresa = L.adresa_livrarii || '';
        } else {
          var msg = result.data && result.data.message ? result.data.message : 'Eroare la actualizare.';
          if (result.data && result.data.errors) msg = Object.values(result.data.errors).flat().join(' ');
          showEditError(msg);
        }
      })
      .catch(function() { showEditError('Eroare de rețea. Încearcă din nou.'); })
      .finally(function() {
        editSaveInProgress = false;
        updateEditSubmitState();
      });
    });
  }

  var livrariEmptyColspan = {{ $isAdmin ? 10 : 9 }};
  var livrariTableBodyEl = document.getElementById('livrariTableBody');
  document.addEventListener('click', function(e) {
    var delBtn = e.target.closest('.livrari-btn-delete');
    if (!delBtn) return;
    e.preventDefault();
    var url = delBtn.getAttribute('data-delete-url');
    if (!url) return;
    if (!window.confirm('Ștergi definitiv această livrare? Acțiunea nu poate fi anulată.')) return;
    var row = delBtn.closest('tr');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var token = csrfMeta ? csrfMeta.getAttribute('content') : '';
    delBtn.disabled = true;
    fetch(url, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': token,
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
      .then(function(r) {
        return r.json().then(function(d) { return { ok: r.ok, status: r.status, data: d }; }).catch(function() { return { ok: false, status: r.status, data: {} }; });
      })
      .then(function(result) {
        if (result.ok && result.data && result.data.success) {
          if (row && row === currentEditRow) {
            closeEditModal();
          }
          if (row) row.remove();
          if (livrariTableBodyEl) {
            var rows = livrariTableBodyEl.querySelectorAll('tr');
            if (rows.length === 0) {
              var trEmpty = document.createElement('tr');
              trEmpty.id = 'livrariEmptyRow';
              trEmpty.innerHTML = '<td colspan="' + livrariEmptyColspan + '" style="text-align:center;color:#9CA3AF;padding:32px;">Nicio livrare înregistrată.</td>';
              livrariTableBodyEl.appendChild(trEmpty);
            }
          }
        } else {
          var dmsg = (result.data && result.data.message) ? result.data.message : 'Nu s-a putut șterge livrarea.';
          window.alert(dmsg);
        }
      })
      .catch(function() { window.alert('Eroare de rețea. Încearcă din nou.'); })
      .finally(function() { delBtn.disabled = false; });
  });
})();
</script>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ro.js"></script>
<script>
(function() {
  var trigger = document.getElementById('livrariPerioadaTrigger');
  var displayInput = document.getElementById('livrari_perioada_input');
  var hiddenDeLa = document.getElementById('data_de_la');
  var hiddenPana = document.getElementById('data_pana');
  var filtersForm = document.querySelector('.livrari-filters-card');
  if (!trigger || !displayInput || !hiddenDeLa || !hiddenPana) return;

  function submitFilters() {
    if (!filtersForm || filtersForm.dataset.submitting === 'true') return;
    filtersForm.dataset.submitting = 'true';
    filtersForm.requestSubmit ? filtersForm.requestSubmit() : filtersForm.submit();
  }

  function formatRO(d) {
    var day = ('0' + d.getDate()).slice(-2);
    var month = ('0' + (d.getMonth() + 1)).slice(-2);
    var year = d.getFullYear();
    return day + '.' + month + '.' + year;
  }
  function setHidden(from, to) {
    if (!from || !to) return;
    hiddenDeLa.value = from.getFullYear() + '-' + ('0' + (from.getMonth() + 1)).slice(-2) + '-' + ('0' + from.getDate()).slice(-2);
    hiddenPana.value = to.getFullYear() + '-' + ('0' + (to.getMonth() + 1)).slice(-2) + '-' + ('0' + to.getDate()).slice(-2);
    displayInput.value = formatRO(from) + ' – ' + formatRO(to);
  }
  function parseRoDate(value) {
    var raw = String(value || '').trim();
    if (!raw) return null;
    var ro = raw.match(/^(\d{1,2})\.(\d{1,2})\.(\d{4})$/);
    if (ro) return new Date(parseInt(ro[3], 10), parseInt(ro[2], 10) - 1, parseInt(ro[1], 10));
    var iso = raw.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
    if (iso) return new Date(parseInt(iso[1], 10), parseInt(iso[2], 10) - 1, parseInt(iso[3], 10));
    return null;
  }

  var defaultDate = [];
  if (hiddenDeLa.value && hiddenPana.value) {
    defaultDate = [hiddenDeLa.value, hiddenPana.value];
  }

  var fp = flatpickr(displayInput, {
    mode: 'range',
    dateFormat: 'Y-m-d',
    locale: 'ro',
    monthSelectorType: 'static',
    defaultDate: defaultDate,
    disableMobile: true,
    allowInput: false,
    onChange: function(selectedDates, dateStr, instance) {
      if (selectedDates.length === 2) {
        setHidden(selectedDates[0], selectedDates[1]);
        submitFilters();
      } else if (selectedDates.length === 1) {
        hiddenDeLa.value = dateStr;
        hiddenPana.value = '';
        displayInput.value = formatRO(selectedDates[0]) + ' – ...';
      }
    },
    onClose: function(selectedDates, dateStr, instance) {
      if (selectedDates.length === 1) {
        setHidden(selectedDates[0], selectedDates[0]);
        displayInput.value = formatRO(selectedDates[0]);
        submitFilters();
      }
    }
  });

  var addDateInput = document.getElementById('modal_data_livrarii');
  if (addDateInput) {
    flatpickr(addDateInput, {
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd.m.Y',
      altInputClass: 'livrari-date-display',
      locale: 'ro',
      monthSelectorType: 'static',
      disableMobile: true,
      allowInput: true,
      defaultDate: addDateInput.value || 'today',
      parseDate: parseRoDate,
      onReady: function(selectedDates, dateStr, instance) {
        if (instance && instance.calendarContainer) {
          instance.calendarContainer.classList.add('livrari-flatpickr');
        }
      }
    });
  }

  var editDateInput = document.getElementById('edit_data_livrarii');
  if (editDateInput) {
    flatpickr(editDateInput, {
      dateFormat: 'Y-m-d',
      altInput: true,
      altFormat: 'd.m.Y',
      altInputClass: 'livrari-date-display',
      locale: 'ro',
      monthSelectorType: 'static',
      disableMobile: true,
      allowInput: true,
      parseDate: parseRoDate,
      onReady: function(selectedDates, dateStr, instance) {
        if (instance && instance.calendarContainer) {
          instance.calendarContainer.classList.add('livrari-flatpickr');
        }
      }
    });
  }

  trigger.addEventListener('click', function(e) {
    if (e.target.closest('.livrari-preset-btn')) return;
    if (e.target.closest('#livrariPerioadaTrigger')) fp.open();
  });

  document.querySelectorAll('.livrari-preset-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
      var now = new Date();
      var start, end;
      var days = btn.getAttribute('data-days');
      var range = btn.getAttribute('data-range');
      if (days) {
        var n = parseInt(days, 10);
        end = new Date(now);
        start = new Date(now);
        start.setDate(start.getDate() - n + 1);
      } else if (range === 'month') {
        start = new Date(now.getFullYear(), now.getMonth(), 1);
        end = new Date(now);
      } else if (range === 'prev-month') {
        start = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        end = new Date(now.getFullYear(), now.getMonth(), 0);
      } else {
        return;
      }
      fp.setDate([start, end], true);
      setHidden(start, end);
    });
  });

  ['luna', 'operator_id', 'locatie'].forEach(function(id) {
    var select = document.getElementById(id);
    if (select) select.addEventListener('change', submitFilters);
  });

  var searchInput = document.getElementById('cauta');
  var searchTimer;
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      window.clearTimeout(searchTimer);
      searchTimer = window.setTimeout(submitFilters, 450);
    });
  }
})();
</script>
@endpush

{{-- Harta nu mai este modal pe pagina de livrări; este disponibilă pe /livrari/harta.
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const openBtn = document.getElementById('livrariOpenMapBtn');
  const modal = document.getElementById('livrariMapModal');
  const closeBtn = document.getElementById('livrariMapModalClose');
  const refreshBtn = document.getElementById('livrariMapRefreshBtn');
  const totalEl = document.getElementById('livrariMapTotal');
  const listEl = document.getElementById('livrariMapList');
  const updatedEl = document.getElementById('livrariMapUpdated');
  const mapEl = document.getElementById('livrariLiveMap');
  const raionSelect = document.getElementById('livrariMapRaionSelect');
  const detailEl = document.getElementById('livrariMapDetail');
  const mapDataUrl = @json(route('livrari.map-data'));
  const mapGeoJsonUrl = @json(asset('data/moldova-adm1.geojson'));
  let map = null;
  let regionLayer = null;
  let layersByRaion = {};
  let geoJsonData = null;
  let lastPayload = null;
  let selectedRaion = '';
  let loading = false;
  const featureRaionAliases = {
    balti: 'Bălți',
    calarasi: 'Călărași',
    causeni: 'Căușeni',
    chisinau: 'Chișinău',
    cimislia: 'Cimișlia',
    donduseni: 'Dondușeni',
    dubasari: 'Dubăsari',
    edinet: 'Edineț',
    falesti: 'Fălești',
    floresti: 'Florești',
    gagauzia: 'UTA Găgăuzia',
    hincesti: 'Hîncești',
    ocnita: 'Ocnița',
    riscani: 'Rîșcani',
    singerei: 'Sîngerei',
    soldanesti: 'Șoldănești',
    stefanvoda: 'Ștefan Vodă',
    straseni: 'Strășeni',
    telenesti: 'Telenești'
  };
  const hiddenFeatureRaions = {
    transnistria: true
  };

  function escapeHtml(value) {
    return String(value || '').replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }

  function filteredMapUrl() {
    const url = new URL(mapDataUrl, window.location.origin);
    const params = new URLSearchParams(window.location.search);
    params.delete('page');
    params.forEach(function (value, key) {
      url.searchParams.append(key, value);
    });
    return url.toString();
  }

  function initMap() {
    if (map || !mapEl || !window.L) return;
    map = L.map(mapEl, {
      scrollWheelZoom: true,
      zoomControl: true
    }).setView([47.05, 28.55], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 18,
      attribution: '&copy; OpenStreetMap | Boundaries: geoBoundaries'
    }).addTo(map);

  }

  function normalizeRaion(value) {
    return String(value || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '');
  }

  function canonicalFeatureRaion(value) {
    const key = normalizeRaion(value);
    return featureRaionAliases[key] || String(value || '').trim();
  }

  function featureName(feature) {
    const props = feature && feature.properties ? feature.properties : {};
    return props.shapeName || props.name || props.NAME_1 || '';
  }

  function ratioForTotal(total, maxTotal) {
    const safeTotal = Math.max(0, Number(total) || 0);
    const safeMax = Math.max(1, Number(maxTotal) || 0);
    return Math.min(1, safeTotal / safeMax);
  }

  function colorForTotal(total, maxTotal) {
    const ratio = ratioForTotal(total, maxTotal);
    const yellow = { r: 255, g: 238, b: 0 };
    const r = Math.round(yellow.r * ratio);
    const g = Math.round(yellow.g * ratio);
    const b = Math.round(yellow.b * ratio);
    return 'rgb(' + r + ', ' + g + ', ' + b + ')';
  }

  function popupHtml(item, periodLabel) {
    const locals = (item.localitati || []).slice(0, 5).map(function (row) {
      return '<li>' + escapeHtml(row.localitate) + ': <strong>' + row.total + '</strong></li>';
    }).join('');

    return '<div class="livrari-map-popup">' +
      '<strong>' + escapeHtml(item.raion) + '</strong><br>' +
      item.total + ' livrări<br>' +
      'Perioada: ' + escapeHtml(periodLabel || '-') +
      (locals ? '<ul>' + locals + '</ul>' : '') +
      '</div>';
  }

  function itemsByRaion() {
    const items = lastPayload && lastPayload.raioane ? lastPayload.raioane : [];
    return items.reduce(function (acc, item) {
      acc[item.raion] = item;
      return acc;
    }, {});
  }

  function itemsByNormalizedRaion(items) {
    return (items || []).reduce(function (acc, item) {
      acc[normalizeRaion(item.raion)] = item;
      return acc;
    }, {});
  }

  function itemForFeature(feature, indexedItems) {
    const rawName = featureName(feature);
    const canonicalName = canonicalFeatureRaion(rawName);
    const item = indexedItems[normalizeRaion(canonicalName)] || indexedItems[normalizeRaion(rawName)];

    return item || {
      raion: canonicalName,
      total: 0,
      localitati: []
    };
  }

  function renderRaionSelect(items) {
    if (!raionSelect) return;
    const previous = selectedRaion || raionSelect.value || '';
    raionSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Selectează raion';
    raionSelect.appendChild(placeholder);

    items.slice().sort(function (a, b) {
      return a.raion.localeCompare(b.raion, 'ro');
    }).forEach(function (item) {
      const option = document.createElement('option');
      option.value = item.raion;
      option.textContent = item.raion + ' (' + item.total + ')';
      raionSelect.appendChild(option);
    });

    if (previous && items.some(function (item) { return item.raion === previous; })) {
      raionSelect.value = previous;
      selectedRaion = previous;
    } else {
      raionSelect.value = '';
      selectedRaion = '';
    }
  }

  function renderDetail(item) {
    if (!detailEl) return;
    const title = detailEl.querySelector('.livrari-map-detail-title span:first-child');
    const count = detailEl.querySelector('.livrari-map-detail-count');
    const period = detailEl.querySelector('.livrari-map-detail-period');
    const localitati = detailEl.querySelector('.livrari-map-detail-localitati');
    const periodLabel = lastPayload && lastPayload.period_label ? lastPayload.period_label : '-';

    if (!item) {
      if (title) title.textContent = 'Selectează un raion';
      if (count) count.textContent = '0';
      if (period) period.textContent = 'Perioada: ' + periodLabel;
      if (localitati) localitati.innerHTML = '<li>Alege un raion de pe hartă sau din listă.</li>';
      return;
    }

    if (title) title.textContent = item.raion;
    if (count) {
      count.textContent = item.total;
      const maxTotal = lastPayload ? lastPayload.max_total : 0;
      const ratio = ratioForTotal(item.total, maxTotal);
      count.style.background = colorForTotal(item.total, maxTotal);
      count.style.color = ratio >= 0.62 ? '#0f172a' : '#f8fafc';
    }
    if (period) period.textContent = 'Perioada: ' + periodLabel;
    if (localitati) {
      localitati.innerHTML = '';
      const rows = item.localitati || [];
      if (!rows.length) {
        const li = document.createElement('li');
        li.textContent = 'Nu sunt livrări în acest raion pentru perioada selectată.';
        localitati.appendChild(li);
      } else {
        rows.slice(0, 8).forEach(function (row) {
          const li = document.createElement('li');
          li.textContent = row.localitate + ': ' + row.total;
          localitati.appendChild(li);
        });
      }
    }
  }

  function regionStyle(item, isSelected) {
    const total = item ? item.total || 0 : 0;
    const maxTotal = lastPayload ? lastPayload.max_total || 0 : 0;

    return {
      color: isSelected ? '#2563eb' : '#111827',
      weight: isSelected ? 3 : 1.2,
      opacity: isSelected ? 1 : 0.8,
      fillColor: colorForTotal(total, maxTotal),
      fillOpacity: total ? 0.72 : 0.2
    };
  }

  function refreshRegionStyles() {
    if (!regionLayer) return;
    regionLayer.eachLayer(function (layer) {
      layer.setStyle(regionStyle(layer._livrariItem, layer._livrariRaion === selectedRaion));
    });
  }

  function selectRaion(raion, focusMap) {
    selectedRaion = raion || '';
    if (raionSelect) raionSelect.value = selectedRaion;
    const item = itemsByRaion()[selectedRaion] || null;
    renderDetail(item);
    refreshRegionStyles();

    const layer = layersByRaion[selectedRaion];
    if (focusMap && layer && map) {
      map.fitBounds(layer.getBounds(), { padding: [44, 44], maxZoom: 10 });
      layer.openPopup();
    }
  }

  function renderList(items) {
    if (!listEl) return;
    listEl.innerHTML = '';

    if (!items.length) {
      const empty = document.createElement('div');
      empty.className = 'livrari-map-empty';
      empty.textContent = 'Nu există livrări pentru filtrele curente.';
      listEl.appendChild(empty);
      return;
    }

    items.forEach(function (item) {
      const row = document.createElement('button');
      row.type = 'button';
      row.className = 'livrari-map-row';

      const main = document.createElement('span');
      main.className = 'livrari-map-row-main';
      const name = document.createElement('span');
      name.textContent = item.raion;
      const count = document.createElement('span');
      count.className = 'livrari-map-row-count';
      count.textContent = item.total;
      const maxTotal = lastPayload ? lastPayload.max_total : 0;
      const ratio = ratioForTotal(item.total, maxTotal);
      count.style.background = colorForTotal(item.total, maxTotal);
      count.style.color = ratio >= 0.62 ? '#0f172a' : '#f8fafc';
      main.appendChild(name);
      main.appendChild(count);

      const sub = document.createElement('span');
      sub.className = 'livrari-map-row-sub';
      sub.textContent = (item.localitati || []).slice(0, 3).map(function (localitate) {
        return localitate.localitate + ' ' + localitate.total;
      }).join(' | ') || 'Fără localități';

      row.appendChild(main);
      row.appendChild(sub);
      row.addEventListener('click', function () {
        selectRaion(item.raion, true);
      });

      listEl.appendChild(row);
    });
  }

  function renderMap(payload, geoJson) {
    initMap();
    lastPayload = payload || {};
    if (totalEl) totalEl.textContent = payload.total || 0;
    if (updatedEl) updatedEl.textContent = 'Perioada: ' + (payload.period_label || '-') + ' | Actualizat: ' + (payload.generated_at || '');

    const items = (payload.raioane || []);
    const indexedItems = itemsByNormalizedRaion(items);
    renderRaionSelect(items);
    renderList(items);
    layersByRaion = {};

    if (!map || !geoJson) return;
    if (regionLayer) {
      map.removeLayer(regionLayer);
      regionLayer = null;
    }

    regionLayer = L.geoJSON(geoJson, {
      filter: function (feature) {
        return !hiddenFeatureRaions[normalizeRaion(featureName(feature))];
      },
      style: function (feature) {
        return regionStyle(itemForFeature(feature, indexedItems), false);
      },
      onEachFeature: function (feature, layer) {
        const item = itemForFeature(feature, indexedItems);
        layer._livrariItem = item;
        layer._livrariRaion = item.raion;
        layersByRaion[item.raion] = layer;

        layer.bindPopup(popupHtml(item, payload.period_label));
        layer.on({
          click: function () {
            selectRaion(item.raion, false);
          },
          mouseover: function () {
            layer.setStyle({
              weight: 3,
              opacity: 1,
              fillOpacity: item.total ? 0.86 : 0.32
            });
            if (layer.bringToFront) layer.bringToFront();
          },
          mouseout: function () {
            refreshRegionStyles();
          }
        });
      }
    }).addTo(map);

    regionLayer.eachLayer(function (layer) {
      const element = layer.getElement && layer.getElement();
      if (element) element.style.cursor = 'pointer';
    });

    const bounds = regionLayer.getBounds();
    if (bounds.isValid && bounds.isValid()) {
      map.fitBounds(bounds, { padding: [24, 24], maxZoom: 8 });
    } else {
      map.setView([47.05, 28.55], 7);
    }

    window.setTimeout(function () {
      map.invalidateSize();
    }, 80);

    if (selectedRaion && itemsByRaion()[selectedRaion]) {
      selectRaion(selectedRaion, false);
    } else {
      renderDetail(null);
      refreshRegionStyles();
    }
  }

  function loadMapData() {
    if (loading) return;
    loading = true;
    if (refreshBtn) refreshBtn.disabled = true;
    if (updatedEl) updatedEl.textContent = 'Se încarcă datele live...';

    const dataRequest = fetch(filteredMapUrl(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (response) {
        if (!response.ok) throw new Error('Nu am putut citi datele pentru hartă.');
        return response.json();
      });

    const geoJsonRequest = geoJsonData
      ? Promise.resolve(geoJsonData)
      : fetch(mapGeoJsonUrl, { headers: { 'Accept': 'application/geo+json, application/json' } })
        .then(function (response) {
          if (!response.ok) throw new Error('Nu am putut încărca hotarele raioanelor.');
          return response.json();
        })
        .then(function (data) {
          geoJsonData = data;
          return data;
        });

    Promise.all([dataRequest, geoJsonRequest])
      .then(function (results) {
        renderMap(results[0], results[1]);
      })
      .catch(function (error) {
        if (updatedEl) updatedEl.textContent = error.message;
        if (listEl) {
          listEl.innerHTML = '<div class="livrari-map-empty">Nu am putut încărca harta.</div>';
        }
      })
      .finally(function () {
        loading = false;
        if (refreshBtn) refreshBtn.disabled = false;
      });
  }

  function openModal() {
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    initMap();
    window.setTimeout(function () {
      if (map) map.invalidateSize();
      loadMapData();
    }, 80);
  }

  function closeModal() {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  if (openBtn) openBtn.addEventListener('click', openModal);
  if (refreshBtn) refreshBtn.addEventListener('click', loadMapData);
  if (raionSelect) {
    raionSelect.addEventListener('change', function () {
      selectRaion(raionSelect.value, true);
    });
  }
  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  if (modal) {
    modal.addEventListener('click', function (event) {
      if (event.target === modal) closeModal();
    });
  }
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && modal && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
});
</script>
@endpush
--}}

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const sectionButtons = Array.from(document.querySelectorAll('.livrari-section-btn[data-section-target]'));
  const sectionPanels = Array.from(document.querySelectorAll('.livrari-section-panel[data-section-panel]'));

  function toggleLivrariSection(target) {
    if (!target) return;
    sectionButtons.forEach(function (button) {
      const isActive = button.getAttribute('data-section-target') === target;
      button.classList.toggle('is-active', isActive);
      button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
    });
    sectionPanels.forEach(function (panel) {
      const matches = panel.getAttribute('data-section-panel') === target;
      panel.classList.toggle('is-collapsed', !matches);
      panel.setAttribute('aria-hidden', matches ? 'false' : 'true');
    });
    try {
      window.localStorage.setItem('livrari.active.section', target);
    } catch (error) {
      // localStorage poate fi indisponibil în unele contexte private.
    }
  }

  if (sectionButtons.length && sectionPanels.length) {
    let initialSection = 'operare';
    try {
      const storedSection = window.localStorage.getItem('livrari.active.section');
      if (storedSection) initialSection = storedSection;
    } catch (error) {}

    if (!sectionButtons.some(function (button) { return button.getAttribute('data-section-target') === initialSection; })) {
      initialSection = 'operare';
    }

    toggleLivrariSection(initialSection);

    sectionButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        toggleLivrariSection(button.getAttribute('data-section-target'));
      });
    });
  }

  const exportBtn = document.getElementById('livrariExportExcelBtn');
  const exportModal = document.getElementById('livrariExportModal');
  const exportForm = document.getElementById('livrariExportForm');
  const exportSubmitBtn = document.getElementById('livrariExportSubmitBtn');
  const exportFileName = document.getElementById('livrariExportFileName');
  const exportSheetName = document.getElementById('livrariExportSheetName');
  const exportOperator = document.getElementById('livrariExportOperator');
  const selectAllBtn = document.getElementById('livrariExportSelectAll');
  const clearColumnsBtn = document.getElementById('livrariExportClearColumns');
  const includeTotals = document.getElementById('livrariExportIncludeTotals');
  const exportColumnInputs = Array.from(document.querySelectorAll('.livrari-export-column'));

  function openExportModal() {
    if (!exportModal) return;
    exportModal.classList.add('is-open');
    exportModal.setAttribute('aria-hidden', 'false');
  }

  function closeExportModal() {
    if (!exportModal) return;
    exportModal.classList.remove('is-open');
    exportModal.setAttribute('aria-hidden', 'true');
  }

  function selectedColumnIndexes() {
    return exportColumnInputs
      .filter(function (input) { return input.checked; })
      .map(function (input) { return parseInt(input.value, 10); })
      .filter(function (value) { return !Number.isNaN(value); });
  }

  function pickColumns(headers, rows, indexes) {
    return {
      headers: indexes.map(function (index) { return headers[index] || ''; }),
      rows: (rows || []).map(function (row) {
        return indexes.map(function (index) { return row[index] || ''; });
      })
    };
  }

  function selectedOperator() {
    if (!exportOperator || !exportOperator.value) return { id: '', name: '' };
    const option = exportOperator.options[exportOperator.selectedIndex];
    return {
      id: exportOperator.value,
      name: option ? (option.getAttribute('data-operator-name') || option.textContent || '').trim() : ''
    };
  }

  function currentPagePayload(operatorName) {
    const table = document.getElementById('livrariDataTable');
    if (!table) return { headers: [], rows: [] };
    const headers = Array.from(table.querySelectorAll('thead th'))
      .slice(0, -1)
      .map(function (cell) { return (cell.innerText || cell.textContent || '').trim(); });
    const operatorIndex = headers.indexOf('Operator');
    let rows = Array.from(table.querySelectorAll('tbody tr'))
      .filter(function (row) { return row.id !== 'livrariEmptyRow'; })
      .map(function (row) {
        return Array.from(row.querySelectorAll('td'))
          .slice(0, headers.length)
          .map(function (cell) { return (cell.innerText || cell.textContent || '').trim(); });
      });
    if (operatorName && operatorIndex >= 0) {
      rows = rows.filter(function (row) { return row[operatorIndex] === operatorName; });
    }
    return { headers: headers, rows: rows };
  }

  function filteredExportUrl(operatorId) {
    const exportUrl = new URL(@json(url('livrari/export-data')), window.location.origin);
    const params = new URLSearchParams(window.location.search);
    params.delete('page');
    if (operatorId) params.delete('operator_id');
    params.forEach(function (value, key) {
      exportUrl.searchParams.append(key, value);
    });
    if (operatorId) exportUrl.searchParams.set('operator_id', operatorId);
    return exportUrl.toString();
  }

  function allFilteredPayload(operatorId) {
    return fetch(filteredExportUrl(operatorId), {
      headers: { 'Accept': 'application/json' }
    }).then(function (response) {
      if (!response.ok) throw new Error('Nu am putut citi datele pentru export.');
      return response.json();
    }).then(function (payload) {
      return {
        headers: payload.headers || [],
        rows: payload.rows || []
      };
    });
  }

  function totalsRows() {
    const totalsTable = document.getElementById('livrariTotalsTable');
    const rows = [
      ['Total livrari', @json($totalLivrariExportValue)],
      ['', '']
    ];

    if (totalsTable) {
      Array.from(totalsTable.querySelectorAll('tbody tr')).forEach(function (row) {
        const cells = Array.from(row.querySelectorAll('td'));
        if (cells.length < 2) return;
        rows.push([
          (cells[0].innerText || cells[0].textContent || '').trim(),
          (cells[1].innerText || cells[1].textContent || '').trim()
        ]);
      });
    }

    return rows;
  }

  function exportTotalsRows(payload, operator) {
    if (operator && operator.id) {
      return [
        ['Total livrari', String((payload.rows || []).length)],
        ['', ''],
        [operator.name || 'Operator selectat', String((payload.rows || []).length)]
      ];
    }

    return totalsRows();
  }

  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      openExportModal();
    });
  }

  if (exportModal) {
    exportModal.addEventListener('click', function (event) {
      if (event.target === exportModal || event.target.closest('.livrari-modal-close')) {
        closeExportModal();
      }
    });
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && exportModal && exportModal.classList.contains('is-open')) {
      closeExportModal();
    }
  });

  if (selectAllBtn) {
    selectAllBtn.addEventListener('click', function () {
      exportColumnInputs.forEach(function (input) { input.checked = true; });
    });
  }

  if (clearColumnsBtn) {
    clearColumnsBtn.addEventListener('click', function () {
      exportColumnInputs.forEach(function (input) { input.checked = false; });
    });
  }

  if (exportForm) {
    exportForm.addEventListener('submit', function (event) {
      event.preventDefault();

      const indexes = selectedColumnIndexes();
      if (!indexes.length) {
        alert('Alege cel putin o coloana pentru export.');
        return;
      }

      const scopeInput = exportForm.querySelector('input[name="export_scope"]:checked');
      const scope = scopeInput ? scopeInput.value : 'all';
      const operator = selectedOperator();
      const fileName = (exportFileName && exportFileName.value.trim() ? exportFileName.value.trim() : 'livrari_tabel')
        + '_' + window.VoltaExcelExport.nowStamp();
      const sheetName = exportSheetName && exportSheetName.value.trim() ? exportSheetName.value.trim() : 'Livrari';
      const payloadPromise = scope === 'page'
        ? Promise.resolve(currentPagePayload(operator.name))
        : allFilteredPayload(operator.id);

      if (exportSubmitBtn) exportSubmitBtn.disabled = true;
      payloadPromise
        .then(function (payload) {
          const filteredPayload = pickColumns(payload.headers || [], payload.rows || [], indexes);
          if (includeTotals && includeTotals.checked) {
            return window.VoltaExcelExport.exportSheets([
              { name: sheetName, aoa: [filteredPayload.headers].concat(filteredPayload.rows), coerceNumbers: false },
              { name: 'Totaluri livrari', aoa: [['Indicator', 'Valoare']].concat(exportTotalsRows(payload, operator)), coerceNumbers: false }
            ], fileName);
          }

          return window.VoltaExcelExport.exportRows(filteredPayload.headers, filteredPayload.rows, {
            fileName: fileName,
            sheetName: sheetName,
            coerceNumbers: false
          });
        })
        .then(function () {
          closeExportModal();
        })
        .catch(function (error) {
          alert('Nu am putut exporta Excel: ' + error.message);
        })
        .finally(function () {
          if (exportSubmitBtn) exportSubmitBtn.disabled = false;
        });
    });
  }

  const totalsExportBtn = document.getElementById('livrariExportTotalsExcelBtn');
  if (totalsExportBtn) {
    totalsExportBtn.addEventListener('click', function () {
      Promise.resolve(window.VoltaExcelExport.exportRows(['Indicator', 'Valoare'], totalsRows(), {
        fileName: 'livrari_totaluri_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Totaluri livrari',
        coerceNumbers: false
      })).catch(function (error) {
        alert('Nu am putut exporta Excel: ' + error.message);
      });
    });
  }
});
</script>
@endpush
@endsection
