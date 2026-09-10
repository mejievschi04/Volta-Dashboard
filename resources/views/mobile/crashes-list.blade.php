@extends('layouts.app')

@section('title', 'Volta App – Listă erori – VOLTA')
@section('header-title', 'Volta App')

@section('content')
@php $q = request()->only(['start', 'end']); @endphp

<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">Tabela pentru erorile din aplicație nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">Listă erori</h1>
        <p class="ma-hero__lead">Toate erorile din perioada selectată, cu detaliu rapid.</p>
      </div>
      <form method="get" action="{{ route('mobile.crashes.list') }}" class="ma-filters">
        <div class="ma-field"><label for="listStart">De la</label><input id="listStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="listEnd">Până la</label><input id="listEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
        <a class="ma-btn ma-btn--ghost" href="{{ route('mobile.problems', $q) }}">Înapoi la probleme</a>
      </form>
    </div>
  </section>

  <section class="ma-card ma-card--danger">
    <div class="ma-card__head">
      <h2><i class="fas fa-bug" aria-hidden="true"></i> Erori</h2>
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
            <th>Utilizator</th>
            <th>Dispozitiv</th>
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
          <tr><td colspan="9" class="ma-muted">Nu există erori în perioada selectată.</td></tr>
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
