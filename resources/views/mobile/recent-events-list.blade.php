@extends('layouts.app')

@section('title', 'Volta App – Evenimente recente – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Evenimente recente</h1>
        <p class="ma-hero__lead">Feed cronologic cu toate acțiunile înregistrate din aplicație.</p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.recent-events') }}" class="ma-filters">
        <div class="ma-field"><label for="recentStart">De la</label><input id="recentStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="recentEnd">Până la</label><input id="recentEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-clock-rotate-left" aria-hidden="true"></i> Feed</h2>
      @if($schemaReady && $recentEvents)
        <span class="ma-muted">{{ number_format($recentEvents->total(), 0, ',', '.') }} evenimente</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead><tr><th>Ora</th><th>Eveniment</th><th>Pagină</th><th>User</th><th>Sesiune</th><th>Detalii</th></tr></thead>
        <tbody>
        @if($schemaReady && $recentEvents && $recentEvents->count())
          @foreach($recentEvents as $event)
            <tr>
              <td class="ma-muted">{{ optional($event->occurred_at)->format('d.m.Y H:i') }}</td>
              <td><span class="ma-badge">{{ $event->event_name }}</span></td>
              <td>{{ $event->page ?: '—' }}</td>
              <td>{{ $event->mobile_user_id ?: '—' }}</td>
              <td class="ma-muted">{{ $event->session_id ? \Illuminate\Support\Str::limit($event->session_id, 20) : '—' }}</td>
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
        @else
          <tr><td colspan="6" class="ma-muted">Nu există date în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $recentEvents)
        <div style="margin-top:14px;">{{ $recentEvents->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
