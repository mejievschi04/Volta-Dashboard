@extends('layouts.app')

@section('title', 'Volta App – Listă crash-uri – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php $q = request()->only(['start', 'end']); @endphp

<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">Tabela pentru crash-uri mobile nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Listă crash-uri</h1>
        <p class="ma-hero__lead">Toate crash-urile din perioada selectată, cu link rapid la detaliu.</p>
      </div>
      <form method="get" action="{{ route('mobile.crashes.list') }}" class="ma-filters">
        <div class="ma-field"><label for="listStart">De la</label><input id="listStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="listEnd">Până la</label><input id="listEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
        <a class="ma-btn ma-btn--ghost" href="{{ route('mobile.crashes', $q) }}">Overview</a>
      </form>
    </div>
  </section>

  <section class="ma-card ma-card--danger">
    <div class="ma-card__head">
      <h2><i class="fas fa-bug" aria-hidden="true"></i> Crash-uri</h2>
      @if($schemaReady && $crashes)
        <span class="ma-muted">{{ number_format($crashes->total(), 0, ',', '.') }} rezultate</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
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
              <td class="ma-muted">{{ optional($crash->occurred_at)->format('d.m.Y H:i') }}</td>
              <td><span class="ma-badge ma-badge--danger">{{ $crash->error_type }}</span></td>
              <td>{{ \Illuminate\Support\Str::limit($crash->error_message ?: '—', 90) }}</td>
              <td>{{ $crash->platform ?: '—' }}</td>
              <td>{{ $crash->app_version ?: '—' }}</td>
              <td>{{ $crash->screen ?: '—' }}</td>
              <td>{{ $crash->mobile_user_id ?: '—' }}</td>
              <td class="ma-muted">{{ $crash->device_id ? \Illuminate\Support\Str::limit($crash->device_id, 18) : '—' }}</td>
              <td><a class="ma-card__link" href="{{ route('mobile.crashes.show', array_merge(['crash' => $crash], $q)) }}">Detaliu →</a></td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="9" class="ma-muted">Nu există crash-uri în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $crashes)
        <div style="margin-top:14px;">{{ $crashes->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
