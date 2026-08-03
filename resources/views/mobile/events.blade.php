@extends('layouts.app')

@section('title', 'Volta App – Evenimente – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php $q = request()->only(['start', 'end']); @endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Evenimente</h1>
        <p class="ma-hero__lead">
          Pagini vizitate, performanța bannerelor și feed-ul recent de acțiuni din app.
        </p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.events') }}" class="ma-filters">
        <div class="ma-field">
          <label for="eventsStart">De la</label>
          <input id="eventsStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="eventsEnd">Până la</label>
          <input id="eventsEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-bolt" aria-hidden="true"></i> Evenimente</span>
      <div class="ma-kpi__value">{{ number_format($summary['events'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Total pe interval</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-file-lines" aria-hidden="true"></i> Page views</span>
      <div class="ma-kpi__value">{{ number_format($summary['page_views'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['avg_page_seconds'], 0, ',', '.') }}s timp mediu</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-rectangle-ad" aria-hidden="true"></i> Click bannere</span>
      <div class="ma-kpi__value">{{ number_format($summary['banner_clicks'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Interacțiuni promo</span>
    </div>
    <div class="ma-kpi ma-kpi--accent">
      <span class="ma-kpi__label"><i class="fas fa-users" aria-hidden="true"></i> Sesiuni</span>
      <div class="ma-kpi__value">{{ number_format($summary['sessions'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['events_per_session'] ?? 0, 1, ',', '.') }} evenimente/sesiune</span>
    </div>
  </div>

  <div class="ma-shortcuts">
    <a class="ma-shortcut" href="{{ route('mobile.analytics.pages', $q) }}">
      <i class="fas fa-file-lines" aria-hidden="true"></i>
      <span><strong>Toate paginile</strong><span>Listă completă</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.analytics.banners', $q) }}">
      <i class="fas fa-rectangle-ad" aria-hidden="true"></i>
      <span><strong>Bannere</strong><span>Click-uri detaliate</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.analytics.event-types', $q) }}">
      <i class="fas fa-list-check" aria-hidden="true"></i>
      <span><strong>Tipuri</strong><span>Breakdown evenimente</span></span>
    </a>
    <a class="ma-shortcut" href="{{ route('mobile.analytics.recent-events', $q) }}">
      <i class="fas fa-clock-rotate-left" aria-hidden="true"></i>
      <span><strong>Recente</strong><span>Feed live</span></span>
    </a>
  </div>

  <div class="ma-grid ma-grid--2">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-file-lines" aria-hidden="true"></i> Pagini și timp petrecut</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.pages', $q) }}">Vezi toate →</a>
      </div>
      <div class="ma-card__body ma-table-wrap">
        <table class="ma-table">
          <thead>
            <tr><th>Pagină</th><th class="num">Views</th><th class="num">Timp mediu</th><th class="num">Evenimente</th></tr>
          </thead>
          <tbody>
          @forelse($topPages as $page)
            <tr>
              <td>{{ $page->page }}</td>
              <td class="num">{{ number_format((int) $page->views, 0, ',', '.') }}</td>
              <td class="num">{{ $page->avg_duration_ms ? number_format(round($page->avg_duration_ms / 1000), 0, ',', '.') . 's' : '—' }}</td>
              <td class="num">{{ number_format((int) $page->events_count, 0, ',', '.') }}</td>
            </tr>
          @empty
            <tr><td colspan="4" class="ma-muted">Nu există pagini înregistrate.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-rectangle-ad" aria-hidden="true"></i> Click-uri pe bannere</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.banners', $q) }}">Vezi toate →</a>
      </div>
      <div class="ma-card__body ma-table-wrap">
        <table class="ma-table">
          <thead>
            <tr><th>Banner</th><th class="num">Click-uri</th><th>Ultimul click</th></tr>
          </thead>
          <tbody>
          @forelse($bannerClicks as $banner)
            <tr>
              <td>{{ $banner->banner_title ?: ($banner->banner_id ?: '—') }}</td>
              <td class="num">{{ number_format((int) $banner->clicks, 0, ',', '.') }}</td>
              <td class="ma-muted">{{ $banner->last_click_at ? \Carbon\Carbon::parse($banner->last_click_at)->format('d.m.Y H:i') : '—' }}</td>
            </tr>
          @empty
            <tr><td colspan="3" class="ma-muted">Nu există click-uri pe bannere.</td></tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </section>
  </div>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Flux evenimente recente</h2>
      <a class="ma-card__link" href="{{ route('mobile.analytics.recent-events', $q) }}">Feed complet →</a>
    </div>
    <div class="ma-card__body ma-table-wrap">
      @if($recentEvents->isEmpty())
        <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Nu există evenimente recente în perioada selectată.</div>
      @else
        <table class="ma-table">
          <thead>
            <tr><th>Ora</th><th>Eveniment</th><th>Pagină</th><th>User</th><th>Sesiune</th><th>Detalii</th></tr>
          </thead>
          <tbody>
          @foreach($recentEvents as $event)
            <tr>
              <td class="ma-muted">{{ optional($event->occurred_at)->format('d.m H:i') }}</td>
              <td><span class="ma-badge">{{ $event->event_name }}</span></td>
              <td>{{ $event->page ?: '—' }}</td>
              <td>{{ $event->mobile_user_id ?: '—' }}</td>
              <td class="ma-muted">{{ $event->session_id ? \Illuminate\Support\Str::limit($event->session_id, 14) : '—' }}</td>
              <td class="ma-muted">
                @if($event->duration_ms)
                  {{ round($event->duration_ms / 1000) }}s
                @elseif($event->cart_total)
                  {{ number_format((float) $event->cart_total, 2, ',', '.') }} MDL
                @elseif($event->banner_title)
                  {{ $event->banner_title }}
                @else
                  —
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
