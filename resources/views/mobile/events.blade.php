@extends('layouts.app')

@section('title', 'Volta App - Evenimente - VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<style>
.mobile-events-page { display: flex; flex-direction: column; gap: 14px; }
.mobile-events-card {
  background: linear-gradient(160deg, rgba(26, 34, 48, 0.96) 0%, rgba(14, 19, 29, 0.98) 100%);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 16px;
  box-shadow: 0 12px 28px rgba(0, 0, 0, 0.24);
  position: relative;
  overflow: hidden;
}
.mobile-events-card::before {
  content: '';
  position: absolute;
  inset: 0 0 auto 0;
  height: 2px;
  background: linear-gradient(90deg, rgba(255, 238, 0, 0), rgba(255, 238, 0, 0.75), rgba(255, 238, 0, 0));
  pointer-events: none;
}
.mobile-events-card__head {
  padding: 14px 16px;
  border-bottom: 1px solid rgba(148, 163, 184, 0.14);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}
.mobile-events-card__head h2 {
  margin: 0;
  color: #fff;
  font-size: 0.97rem;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}
.mobile-events-card__head i { color: var(--brand, #FFEE00); }
.mobile-events-card__body { padding: 16px; }
.mobile-events-header { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; }
.mobile-events-title h1 {
  margin: 0 0 5px;
  color: #fff;
  font-size: clamp(1.3rem, 2.1vw, 1.8rem);
  letter-spacing: -0.03em;
}
.mobile-events-title p { margin: 0; color: #94a3b8; font-size: 0.84rem; }
.mobile-events-filters { display: flex; flex-wrap: wrap; gap: 8px; align-items: flex-end; }
.mobile-events-field { display: flex; flex-direction: column; gap: 5px; }
.mobile-events-field label { color: #94a3b8; font-size: 0.64rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; }
.mobile-events-field input {
  min-height: 40px; border-radius: 10px; border: 1px solid rgba(148, 163, 184, 0.28);
  background: rgba(15, 23, 42, 0.74); color: #e2e8f0; padding: 9px 11px; font: inherit;
}
.mobile-events-apply {
  min-height: 40px; border: 0; border-radius: 10px; padding: 0 14px;
  background: var(--brand, #FFEE00); color: #0f172a; font-weight: 800; cursor: pointer;
}
.mobile-events-alert {
  border: 1px solid rgba(255, 238, 0, 0.34); border-radius: 12px; padding: 12px 14px;
  background: rgba(255, 238, 0, 0.09); color: #fef08a; font-size: 0.84rem;
}
.mobile-events-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.mobile-events-metrics { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
.mobile-events-metric {
  border: 1px solid rgba(148, 163, 184, 0.18);
  border-radius: 12px;
  padding: 10px 12px;
  background: rgba(15, 23, 42, 0.5);
}
.mobile-events-metric strong {
  display: block;
  color: #fff;
  font-size: 1.2rem;
  letter-spacing: -0.02em;
}
.mobile-events-metric span {
  color: #94a3b8;
  font-size: 0.76rem;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}
.mobile-events-metric i { color: rgba(255, 238, 0, 0.9); }
.mobile-events-table-wrap { overflow-x: auto; }
.mobile-events-table { width: 100%; border-collapse: collapse; font-size: 0.8rem; }
.mobile-events-table th {
  text-align: left; color: #94a3b8; background: rgba(15, 23, 42, 0.62); font-size: 0.63rem;
  text-transform: uppercase; letter-spacing: 0.08em; padding: 9px 10px; white-space: nowrap;
}
.mobile-events-table td { color: #e2e8f0; padding: 10px; border-top: 1px solid rgba(148, 163, 184, 0.13); vertical-align: top; }
.mobile-events-table tbody tr:hover td { background: rgba(148, 163, 184, 0.06); }
.mobile-events-muted { color: #94a3b8; }
.mobile-events-badge {
  display: inline-flex; align-items: center; border-radius: 999px;
  border: 1px solid rgba(255, 238, 0, 0.28); background: rgba(255, 238, 0, 0.09);
  color: #fde047; padding: 3px 8px; font-size: 0.67rem; font-weight: 800;
}
.mobile-events-empty {
  border: 1px dashed rgba(148, 163, 184, 0.26); border-radius: 12px; padding: 14px;
  color: #94a3b8; background: rgba(15, 23, 42, 0.34); font-size: 0.82rem;
}
@media (max-width: 1100px) { .mobile-events-grid, .mobile-events-metrics { grid-template-columns: 1fr; } }
@media (max-width: 640px) {
  .mobile-events-header { align-items: stretch; }
  .mobile-events-filters { width: 100%; }
  .mobile-events-field, .mobile-events-field input, .mobile-events-apply { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="mobile-events-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="mobile-events-alert">
      Tabela pentru evenimente mobile nu este încă creată în această bază locală. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <div class="mobile-events-card">
    <div class="mobile-events-card__body">
      <div class="mobile-events-header">
        <div class="mobile-events-title">
          <h1>Evenimente mobile</h1>
          <p>Analiză detaliată pe pagini, bannere și feed de evenimente recente.</p>
        </div>
        <form method="get" action="{{ route('mobile.analytics.events') }}" class="mobile-events-filters">
          <div class="mobile-events-field">
            <label for="mobileEventsStart">De la</label>
            <input id="mobileEventsStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
          </div>
          <div class="mobile-events-field">
            <label for="mobileEventsEnd">Până la</label>
            <input id="mobileEventsEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
          </div>
          <button class="mobile-events-apply" type="submit">Aplică</button>
        </form>
      </div>
    </div>
  </div>

  <div class="mobile-events-metrics">
    <div class="mobile-events-metric">
      <strong>{{ number_format($summary['events'], 0, ',', '.') }}</strong>
      <span><i class="fas fa-bolt"></i> Evenimente totale</span>
    </div>
    <div class="mobile-events-metric">
      <strong>{{ number_format($summary['page_views'], 0, ',', '.') }}</strong>
      <span><i class="fas fa-eye"></i> Vizualizări pagini</span>
    </div>
    <div class="mobile-events-metric">
      <strong>{{ number_format($summary['banner_clicks'], 0, ',', '.') }}</strong>
      <span><i class="fas fa-rectangle-ad"></i> Click-uri bannere</span>
    </div>
  </div>

  <div class="mobile-events-grid">
    <section class="mobile-events-card">
      <div class="mobile-events-card__head">
        <h2><i class="fas fa-file-lines"></i> Pagini și timp petrecut</h2>
      </div>
      <div class="mobile-events-card__body mobile-events-table-wrap">
        <table class="mobile-events-table">
          <thead>
            <tr><th>Pagina</th><th>View-uri</th><th>Timp mediu</th><th>Evenimente</th></tr>
          </thead>
          <tbody>
          @forelse($topPages as $page)
            <tr>
              <td>{{ $page->page }}</td>
              <td>{{ number_format((int) $page->views, 0, ',', '.') }}</td>
              <td>{{ $page->avg_duration_ms ? number_format(round($page->avg_duration_ms / 1000), 0, ',', '.') . 's' : '-' }}</td>
              <td>{{ number_format((int) $page->events_count, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="mobile-events-muted">Nu există pagini înregistrate.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="mobile-events-card">
      <div class="mobile-events-card__head">
        <h2><i class="fas fa-rectangle-ad"></i> Click-uri pe bannere</h2>
      </div>
      <div class="mobile-events-card__body mobile-events-table-wrap">
        <table class="mobile-events-table">
          <thead>
            <tr><th>Banner</th><th>Click-uri</th><th>Ultimul click</th></tr>
          </thead>
          <tbody>
          @forelse($bannerClicks as $banner)
            <tr>
              <td>{{ $banner->banner_title ?: ($banner->banner_id ?: '-') }}</td>
              <td>{{ number_format((int) $banner->clicks, 0, ',', '.') }}</td>
              <td class="mobile-events-muted">{{ $banner->last_click_at ? \Carbon\Carbon::parse($banner->last_click_at)->format('d.m.Y H:i') : '-' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="mobile-events-muted">Nu există click-uri pe bannere.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="mobile-events-card">
    <div class="mobile-events-card__head">
      <h2><i class="fas fa-clock-rotate-left"></i> Flux evenimente recente</h2>
    </div>
    <div class="mobile-events-card__body mobile-events-table-wrap">
      @if($recentEvents->isEmpty())
        <div class="mobile-events-empty">Nu există evenimente recente în perioada selectată.</div>
      @else
        <table class="mobile-events-table">
          <thead>
            <tr><th>Ora</th><th>Eveniment</th><th>Pagina</th><th>User</th><th>Sesiune</th><th>Detalii</th></tr>
          </thead>
          <tbody>
          @foreach($recentEvents as $event)
            <tr>
              <td class="mobile-events-muted">{{ optional($event->occurred_at)->format('d.m H:i') }}</td>
              <td><span class="mobile-events-badge">{{ $event->event_name }}</span></td>
              <td>{{ $event->page ?: '-' }}</td>
              <td>{{ $event->mobile_user_id ?: '-' }}</td>
              <td class="mobile-events-muted">{{ $event->session_id ? \Illuminate\Support\Str::limit($event->session_id, 14) : '-' }}</td>
              <td class="mobile-events-muted">
                @if($event->duration_ms)
                  {{ round($event->duration_ms / 1000) }}s
                @elseif($event->cart_total)
                  {{ number_format((float) $event->cart_total, 2, ',', '.') }} MDL
                @elseif($event->banner_title)
                  {{ $event->banner_title }}
                @else
                  -
                @endif
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      @endif
    </div>
  </section>
</div>
@endsection
