@extends('layouts.app')

@section('title', 'Volta App – Tipuri evenimente – VOLTA')
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
        <h1 class="ma-hero__title">Tipuri evenimente</h1>
        <p class="ma-hero__lead">Distribuția completă pe tipuri de evenimente din aplicație.</p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.event-types') }}" class="ma-filters">
        <div class="ma-field"><label for="typesStart">De la</label><input id="typesStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="typesEnd">Până la</label><input id="typesEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-list-check" aria-hidden="true"></i> Breakdown</h2>
      @if($schemaReady && $eventTypes)
        <span class="ma-muted">{{ number_format($eventTypes->total(), 0, ',', '.') }} tipuri</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead><tr><th>Eveniment</th><th class="num">Total</th></tr></thead>
        <tbody>
        @if($schemaReady && $eventTypes && $eventTypes->count())
          @foreach($eventTypes as $row)
            <tr>
              <td><span class="ma-badge">{{ $row->event_name }}</span></td>
              <td class="num">{{ number_format((int) $row->total, 0, ',', '.') }}</td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="2" class="ma-muted">Nu există date în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $eventTypes)
        <div style="margin-top:14px;">{{ $eventTypes->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
