@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', auth()->check() && auth()->user()->isOperator() ? 'Livrări – VOLTA STATS' : 'Livrări – VOLTA')
@section('header-title', 'Livrări')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/operatori.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
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

  /* ---------- Cards ---------- */
  .livrari-card {
    background: linear-gradient(165deg, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.96) 100%);
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
    background: var(--bg-elevated, #1e293b);
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
    background: rgba(17, 24, 39, 0.6);
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
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    background: rgba(17, 24, 39, 0.6);
    color: #E5E7EB;
    min-width: 140px;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
  }
  .livrari-filters select:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.18);
  }
  .livrari-filters .livrari-btn { padding: 8px 16px; font-size: 0.8125rem; }
  .livrari-perioada-wrap { min-width: 200px; flex: 1 1 220px; }
  .livrari-perioada-wrap label { display: block; margin-bottom: 4px; font-size: 0.6875rem; }
  .livrari-perioada-field {
    display: flex;
    align-items: center;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    background: rgba(17, 24, 39, 0.6);
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
    padding: 8px 12px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    border-radius: 8px;
    background-color: rgba(17, 24, 39, 0.6);
    color: #E5E7EB;
    min-width: 140px;
    font-size: 0.8125rem;
    font-weight: 500;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 12 12'%3E%3Cpath fill='%239CA3AF' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
    padding-right: 28px;
  }
  .livrari-filter-item select:focus {
    outline: none;
    border-color: rgba(255, 238, 0, 0.45);
    box-shadow: 0 0 0 2px rgba(255, 238, 0, 0.18);
  }
  .livrari-filter-item select:hover { border-color: rgba(255, 255, 255, 0.2); }
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
    background: rgba(17, 24, 39, 0.4);
  }
  .livrari-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
  }
  .livrari-table th {
    background: var(--bg-secondary, #1e293b);
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
  .livrari-table tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
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
    background: rgba(30, 41, 59, 0.85);
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
    background: rgba(15, 23, 42, 0.5);
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
    background: rgba(15, 23, 42, 0.45);
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
    .livrari-filters-block + .livrari-filters-block { margin-top: 20px; padding-top: 20px; }
    .livrari-filters select { min-width: 100%; }
    .livrari-filter-item select { min-width: 100%; }
    .livrari-search-wrap { min-width: 100%; }
    .livrari-perioada-wrap { min-width: 100%; flex: 1 1 100%; }
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
</style>
@endpush

@section('content')
<div class="livrari-page livrari-page--modern {{ $isAdmin ? 'livrari-page--admin' : '' }}">
  <p class="rapoarte-lead livrari-page-lead">
    {{ $isAdmin ? 'Toate livrările și KPI per operator, în același format vizual ca Dashboard/Rapoarte.' : 'Adaugă și vizualizează livrările tale, cu filtre rapide și tabel unificat.' }}
  </p>

  @php
    $filters = $filters ?? ['luna' => '', 'operator_id' => '', 'locatie' => '', 'cauta' => '', 'data' => '', 'data_de_la' => '', 'data_pana' => ''];
    $operatorsForFilter = $operatorsForFilter ?? collect();
  @endphp

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
          <input type="text" id="cauta" name="cauta" value="{{ $filters['cauta'] ?? '' }}" placeholder="Nr. comandă, adresă, raion..." class="livrari-search-input" maxlength="200">
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
        <button type="submit" class="livrari-btn livrari-btn-primary"><i class="fas fa-search"></i> Filtrează</button>
      </div>
    </div>
  </form>

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

  @if($isAdmin && ($perOperator->isNotEmpty() || $totalLivrari > 0))
  <div class="livrari-card livrari-admin-kpi">
    <div class="livrari-kpi-header">
      <div class="livrari-kpi-header-icon"><i class="fas fa-truck"></i></div>
      <div class="livrari-kpi-copy">
        <h2 class="livrari-kpi-title">KPI Livrări</h2>
        <p class="livrari-kpi-subtitle">Rezumat livrări și distribuție per operator</p>
      </div>
      <button type="button" id="livrariExportTotalsExcelBtn" class="livrari-btn livrari-btn-primary livrari-kpi-export">
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
      <p class="livrari-add-hint">Locația (În Chișinău / În afara) se stabilește automat după raion. După salvare poți introduce altă livrare sau închide.</p>
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
      <button type="button" id="livrariExportExcelBtn" class="livrari-btn livrari-btn-primary">
        <i class="fas fa-file-excel" aria-hidden="true"></i> Export Excel
      </button>
    </h2>
    <div class="livrari-table-wrap">
      <table id="livrariDataTable" class="livrari-table">
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
            <td colspan="{{ $isAdmin ? 9 : 8 }}" style="text-align: center; color: #9CA3AF; padding: 32px;">Nicio livrare înregistrată.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $livrari->links('vendor.pagination.livrari') }}
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
    tr.setAttribute('data-adresa', livrare.adresa_livrarii || '');
    tr.setAttribute('data-nr-client', livrare.nr_client || '');
    tr.setAttribute('data-data-livrarii', dataLivrariiYmd);
    var delUrl = livrare.id ? livrariDestroyUrl(livrare.id) : '';
    tr.innerHTML =
      '<td>' + (livrare.numar_comanda || '') + '</td>' +
      '<td>' + (livrare.data || '') + '</td>' +
      '<td>' + (livrare.localitate || '—') + '</td>' +
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

  var livrariEmptyColspan = {{ $isAdmin ? 9 : 8 }};
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
  if (!trigger || !displayInput || !hiddenDeLa || !hiddenPana) return;

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

  var defaultDate = [];
  if (hiddenDeLa.value && hiddenPana.value) {
    defaultDate = [hiddenDeLa.value, hiddenPana.value];
  }

  var fp = flatpickr(displayInput, {
    mode: 'range',
    dateFormat: 'Y-m-d',
    locale: 'ro',
    defaultDate: defaultDate,
    allowInput: false,
    onChange: function(selectedDates, dateStr, instance) {
      if (selectedDates.length === 2) {
        setHidden(selectedDates[0], selectedDates[1]);
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
      }
    }
  });

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
})();
</script>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const exportBtn = document.getElementById('livrariExportExcelBtn');
  if (exportBtn) {
    exportBtn.addEventListener('click', function () {
      const table = document.getElementById('livrariDataTable');
      if (!table) {
        alert('Nu exista tabel pentru export.');
        return;
      }
      const headers = Array.from(table.querySelectorAll('thead th'))
        .slice(0, -1)
        .map(function (cell) { return (cell.innerText || cell.textContent || '').trim(); });
      const rows = Array.from(table.querySelectorAll('tbody tr'))
        .filter(function (row) { return row.id !== 'livrariEmptyRow'; })
        .map(function (row) {
          return Array.from(row.querySelectorAll('td'))
            .slice(0, headers.length)
            .map(function (cell) { return (cell.innerText || cell.textContent || '').trim(); });
        });
      Promise.resolve(window.VoltaExcelExport.exportRows(headers, rows, {
        fileName: 'livrari_tabel_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Livrari'
      })).catch(function (error) {
        alert('Nu am putut exporta Excel: ' + error.message);
      });
    });
  }

  const totalsExportBtn = document.getElementById('livrariExportTotalsExcelBtn');
  if (totalsExportBtn) {
    totalsExportBtn.addEventListener('click', function () {
      const totalsTable = document.getElementById('livrariTotalsTable');
      const rows = [
        ['Total livrari', @json((int) $totalLivrari)],
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

      Promise.resolve(window.VoltaExcelExport.exportRows(['Indicator', 'Valoare'], rows, {
        fileName: 'livrari_totaluri_' + window.VoltaExcelExport.nowStamp(),
        sheetName: 'Totaluri livrari'
      })).catch(function (error) {
        alert('Nu am putut exporta Excel: ' + error.message);
      });
    });
  }
});
</script>
@endpush
@endsection
