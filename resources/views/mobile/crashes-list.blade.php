@extends('layouts.app')

@section('title', 'Volta App - Listă crash-uri - VOLTA')
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
.mobile-list-btn { min-height: 38px; border-radius: 10px; border: 0; padding: 0 12px; background: var(--brand); color: var(--text-inverse); font-weight: 800; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; }
.mobile-list-alert { border: 1px solid rgba(255, 238, 0, 0.34); border-radius: 12px; padding: 12px 14px; background: rgba(255, 238, 0, 0.09); color: #fef08a; font-size: 0.84rem; }
.mobile-list-table-wrap { overflow-x: auto; }
.mobile-list-table { width: 100%; border-collapse: collapse; font-size: 0.82rem; }
.mobile-list-table th { text-align: left; color: var(--text-tertiary); background: var(--bg-secondary); font-size: 0.64rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 9px 10px; white-space: nowrap; }
.mobile-list-table td { color: var(--text-primary); padding: 10px; border-top: 1px solid rgba(148, 163, 184, 0.13); vertical-align: top; }
.mobile-list-link { color: #fde047; font-weight: 700; text-decoration: none; }
.mobile-list-link:hover { text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="mobile-list-page">
  @if(!$schemaReady)
    <div class="mobile-list-alert">Tabela pentru crash-uri mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif
  <div class="mobile-list-card"><div class="mobile-list-card__body"><div class="mobile-list-head">
    <div class="mobile-list-title">
      <h1>Listă crash-uri</h1>
      <p>Toate crash-urile din perioada selectată.</p>
    </div>
    <form method="get" action="{{ route('mobile.crashes.list') }}" class="mobile-list-filters">
      <div class="mobile-list-field"><label for="crashesStart">De la</label><input id="crashesStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
      <div class="mobile-list-field"><label for="crashesEnd">Până la</label><input id="crashesEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
      <button class="mobile-list-btn" type="submit">Aplică</button>
      <a class="mobile-list-btn" href="{{ route('mobile.crashes', request()->only(['start', 'end'])) }}">Overview</a>
    </form>
  </div></div></div>
  <div class="mobile-list-card"><div class="mobile-list-card__body mobile-list-table-wrap">
    <table class="mobile-list-table">
      <thead>
        <tr>
          <th>Ora</th>
          <th>Tip</th>
          <th>Mesaj</th>
          <th>Platformă</th>
          <th>Versiune</th>
          <th>Ecran</th>
          <th>User</th>
          <th>Device</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
      @if($schemaReady && $crashes && $crashes->count())
        @foreach($crashes as $crash)
          <tr>
            <td>{{ optional($crash->occurred_at)->format('d.m.Y H:i') }}</td>
            <td>{{ $crash->error_type }}</td>
            <td>{{ \Illuminate\Support\Str::limit($crash->error_message ?: '—', 90) }}</td>
            <td>{{ $crash->platform ?: '—' }}</td>
            <td>{{ $crash->app_version ?: '—' }}</td>
            <td>{{ $crash->screen ?: '—' }}</td>
            <td>{{ $crash->mobile_user_id ?: '—' }}</td>
            <td>{{ $crash->device_id ? \Illuminate\Support\Str::limit($crash->device_id, 18) : '—' }}</td>
            <td><a class="mobile-list-link" href="{{ route('mobile.crashes.show', array_merge(['crash' => $crash], request()->only(['start', 'end']))) }}">Detaliu</a></td>
          </tr>
        @endforeach
      @else
        <tr><td colspan="9" style="color:var(--text-secondary);">Nu există crash-uri în perioada selectată.</td></tr>
      @endif
      </tbody>
    </table>
    @if($schemaReady && $crashes)
      <div style="margin-top:12px;">{{ $crashes->links('vendor.pagination.livrari') }}</div>
    @endif
  </div></div>
</div>
@endsection
