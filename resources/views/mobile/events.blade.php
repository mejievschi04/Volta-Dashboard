@extends('layouts.app')

@section('title', 'Volta App – Folosirea aplicației – VOLTA')
@section('header-title', 'Volta App')

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $periodPresets = \App\Support\MobileRetention::presets();
  $maxEvent = max(1, (int) ($eventBreakdown->max('total') ?? 1));
@endphp

<div class="ma-page">
  @if(isset($schemaReady) && !$schemaReady)
    <div class="ma-alert">Tabela pentru datele din aplicație nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">Folosirea aplicației</h1>
        <p class="ma-hero__lead">
          Ce ecrane se deschid, cât stau oamenii, bannere și ultimele acțiuni.
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
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('mobile.analytics.events', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-bolt" aria-hidden="true"></i> Acțiuni</span>
      <div class="ma-kpi__value">{{ number_format($summary['events'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Tot ce s-a înregistrat în interval</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-file-lines" aria-hidden="true"></i> Pagini deschise</span>
      <div class="ma-kpi__value">{{ number_format($summary['page_views'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['avg_page_seconds'], 0, ',', '.') }} secunde pe pagină, în medie</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-right-to-bracket" aria-hidden="true"></i> Autentificări</span>
      <div class="ma-kpi__value">{{ number_format($summary['logins'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($summary['map_opens'], 0, ',', '.') }} deschideri ale hărții</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-rectangle-ad" aria-hidden="true"></i> Click bannere</span>
      <div class="ma-kpi__value">{{ number_format($summary['banner_clicks'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Interacțiuni promo</span>
    </div>
  </div>

  <div class="ma-grid">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-file-lines" aria-hidden="true"></i> Pagini și timp</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.pages', $q) }}">Vezi toate →</a>
      </div>
      <div class="ma-card__body ma-table-wrap">
        <table class="ma-table">
          <thead>
            <tr><th>Pagină</th><th class="num">Deschideri</th><th class="num">Timp mediu</th><th class="num">Acțiuni</th></tr>
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
        <h2><i class="fas fa-list-check" aria-hidden="true"></i> Tipuri de acțiuni</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.event-types', $q) }}">Toate tipurile →</a>
      </div>
      <div class="ma-card__body">
        @if($eventBreakdown->isEmpty())
          <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Nu există acțiuni în perioada selectată.</div>
        @else
          @foreach($eventBreakdown->take(10) as $row)
            @php
              $label = \App\Support\MobileLabels::event($row->event_name);
              $pct = round(((int) $row->total / $maxEvent) * 100);
            @endphp
            <div class="ma-bar-row">
              <div class="ma-bar-row__label" title="{{ $label }}">{{ $label }}</div>
              <div class="ma-bar-row__track"><div class="ma-bar-row__fill" style="width: {{ $pct }}%;"></div></div>
              <div class="ma-bar-row__value">{{ number_format((int) $row->total, 0, ',', '.') }}</div>
            </div>
          @endforeach
        @endif
      </div>
    </section>
  </div>

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

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Ultimele acțiuni</h2>
      <a class="ma-card__link" href="{{ route('mobile.analytics.recent-events', $q) }}">Toate acțiunile →</a>
    </div>
    <div class="ma-card__body ma-table-wrap">
      @if($recentEvents->isEmpty())
        <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i>Nu există acțiuni recente în perioada selectată.</div>
      @else
        <table class="ma-table">
          <thead>
            <tr><th>Ora</th><th>Acțiune</th><th>Pagină</th><th>Utilizator</th><th>Vizită</th><th>Detalii</th></tr>
          </thead>
          <tbody>
          @foreach($recentEvents->take(40) as $event)
            <tr>
              <td class="ma-muted">{{ optional($event->occurred_at)->format('d.m H:i') }}</td>
              <td><span class="ma-badge">{{ \App\Support\MobileLabels::event($event->event_name) }}</span></td>
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
