@extends('layouts.app')

@section('title', 'Volta App - Abandon coș - VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<style>
.mobile-list-page { display: flex; flex-direction: column; gap: 14px; }
.mobile-list-card { background: var(--bg-elevated); border: 1px solid var(--border-primary); border-radius: var(--card-radius); box-shadow: var(--shadow-md); }
.mobile-list-card__body { padding: 16px; }
.mobile-list-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; }
.mobile-list-title h1 { margin: 0 0 5px; font-size: clamp(1.2rem, 2vw, 1.6rem); color: var(--text-primary); }
.mobile-list-title p { margin: 0; color: var(--text-secondary); font-size: 0.84rem; }
.mobile-list-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.mobile-list-field { display: flex; flex-direction: column; gap: 4px; }
.mobile-list-field label { color: var(--text-tertiary); font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; }
.mobile-list-field input { min-height: 38px; padding: 8px 10px; border-radius: 10px; border: 1px solid var(--border-primary); background: var(--bg-secondary); color: var(--text-primary); }
.mobile-list-btn { min-height: 38px; border-radius: 10px; border: 0; padding: 0 12px; background: var(--brand); color: var(--text-inverse); font-weight: 800; cursor: pointer; }
.mobile-list-alert { border: 1px solid rgba(255, 238, 0, 0.34); border-radius: 12px; padding: 12px 14px; background: rgba(255, 238, 0, 0.09); color: #fef08a; font-size: 0.84rem; }
.mobile-list-table-wrap { overflow-x: auto; }
.mobile-list-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.mobile-list-table th { text-align: left; color: var(--text-tertiary); background: var(--bg-secondary); font-size: 0.64rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 9px 10px; white-space: nowrap; }
.mobile-list-table td { color: var(--text-primary); padding: 10px; border-top: 1px solid rgba(148, 163, 184, 0.13); vertical-align: top; }
</style>
@endpush

@section('content')
<div class="mobile-list-page">
  @if(!$schemaReady)
    <div class="mobile-list-alert">Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif
  <div class="mobile-list-card"><div class="mobile-list-card__body"><div class="mobile-list-head">
    <div class="mobile-list-title"><h1>Abandon coș (listă completă)</h1><p>Toate etapele cu abandon sunt listate explicit, fără limitări.</p></div>
    <form method="get" action="{{ route('mobile.analytics.abandon') }}" class="mobile-list-filters">
      <div class="mobile-list-field"><label for="abandonStart">De la</label><input id="abandonStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
      <div class="mobile-list-field"><label for="abandonEnd">Până la</label><input id="abandonEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
      <button class="mobile-list-btn" type="submit">Aplică</button>
    </form>
  </div></div></div>
  <div class="mobile-list-card"><div class="mobile-list-card__body mobile-list-table-wrap">
    <table class="mobile-list-table">
      <thead><tr><th>Pas checkout</th><th>Abandonuri</th><th>Total mediu coș</th><th>Produse medii</th></tr></thead>
      <tbody>
      @if($schemaReady && $abandonRows && $abandonRows->count())
        @foreach($abandonRows as $row)
          <tr>
            <td>Pas {{ $row->checkout_step ?: '?' }}</td>
            <td>{{ number_format((int) $row->abandons, 0, ',', '.') }}</td>
            <td>{{ $row->avg_cart_total !== null ? number_format((float) $row->avg_cart_total, 2, ',', '.') . ' MDL' : '-' }}</td>
            <td>{{ $row->avg_items_count !== null ? number_format((float) $row->avg_items_count, 1, ',', '.') : '-' }}</td>
          </tr>
        @endforeach
      @else
        <tr><td colspan="4" style="color:var(--text-secondary);">Nu există date în perioada selectată.</td></tr>
      @endif
      </tbody>
    </table>
    @if($schemaReady && $abandonRows)
      <div style="margin-top:12px;">{{ $abandonRows->links('vendor.pagination.livrari') }}</div>
    @endif
  </div></div>
</div>
@endsection
