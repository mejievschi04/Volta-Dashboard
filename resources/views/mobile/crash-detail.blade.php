@extends('layouts.app')

@section('title', 'Volta App - Detaliu crash - VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<style>
.crash-detail-page { display: flex; flex-direction: column; gap: 14px; }
.crash-card { background: var(--bg-elevated); border: 1px solid var(--border-primary); border-radius: var(--card-radius); box-shadow: var(--shadow-md); }
.crash-card__body { padding: 16px; }
.crash-head { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: flex-start; }
.crash-head h1 { margin: 0 0 6px; font-size: clamp(1.2rem, 2vw, 1.55rem); color: var(--text-primary); }
.crash-head p { margin: 0; color: var(--text-secondary); font-size: 0.86rem; }
.crash-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.crash-btn {
  min-height: 38px; border-radius: 10px; border: 0; padding: 0 12px;
  background: var(--brand); color: var(--text-inverse); font-weight: 800; text-decoration: none;
  display: inline-flex; align-items: center;
}
.crash-meta { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
.crash-meta-item {
  border: 1px solid var(--border-primary); border-radius: 12px; padding: 10px 12px; background: var(--bg-secondary);
}
.crash-meta-item span { display: block; color: var(--text-tertiary); font-size: 0.64rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; margin-bottom: 4px; }
.crash-meta-item strong { color: var(--text-primary); font-size: 0.9rem; word-break: break-word; }
.crash-pre {
  margin: 0; padding: 14px; border-radius: 12px; background: #0b1220; color: #e2e8f0;
  border: 1px solid rgba(148, 163, 184, 0.2); overflow: auto; max-height: 480px;
  font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.78rem; line-height: 1.45;
  white-space: pre-wrap; word-break: break-word;
}
.crash-section-title { margin: 0 0 10px; color: var(--text-primary); font-size: 0.95rem; }
@media (max-width: 900px) {
  .crash-meta { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="crash-detail-page">
  <div class="crash-card"><div class="crash-card__body">
    <div class="crash-head">
      <div>
        <h1>{{ $crash->error_type }}</h1>
        <p>{{ $crash->error_message ?: 'Fără mesaj' }}</p>
      </div>
      <div class="crash-actions">
        <a class="crash-btn" href="{{ route('mobile.crashes.list', request()->only(['start', 'end'])) }}">Înapoi la listă</a>
        <a class="crash-btn" href="{{ route('mobile.crashes', request()->only(['start', 'end'])) }}">Overview</a>
      </div>
    </div>
    <div class="crash-meta">
      <div class="crash-meta-item"><span>Ora</span><strong>{{ optional($crash->occurred_at)->format('d.m.Y H:i:s') ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>Platformă</span><strong>{{ $crash->platform ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>Versiune app</span><strong>{{ $crash->app_version ?: '—' }}@if($crash->build_number) ({{ $crash->build_number }})@endif</strong></div>
      <div class="crash-meta-item"><span>Ecran</span><strong>{{ $crash->screen ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>User</span><strong>{{ $crash->mobile_user_id ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>Device</span><strong>{{ $crash->device_id ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>OS / Model</span><strong>{{ trim(($crash->os_version ?: '').' / '.($crash->device_model ?: ''), ' /') ?: '—' }}</strong></div>
      <div class="crash-meta-item"><span>Fatal</span><strong>{{ $crash->is_fatal ? 'Da' : 'Nu' }}</strong></div>
      <div class="crash-meta-item"><span>Fingerprint</span><strong>{{ $crash->fingerprint ? \Illuminate\Support\Str::limit($crash->fingerprint, 24) : '—' }}</strong></div>
    </div>
  </div></div>

  <div class="crash-card"><div class="crash-card__body">
    <h2 class="crash-section-title">Stack trace</h2>
    <pre class="crash-pre">{{ $crash->stack_trace ?: 'Fără stack trace.' }}</pre>
  </div></div>

  <div class="crash-card"><div class="crash-card__body">
    <h2 class="crash-section-title">Metadata</h2>
    <pre class="crash-pre">{{ $crash->metadata ? json_encode($crash->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
  </div></div>
</div>
@endsection
