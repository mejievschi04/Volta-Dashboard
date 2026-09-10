@extends('layouts.app')

@section('title', 'Volta App – Detaliu eroare – VOLTA')
@section('header-title', 'Volta App')

@section('content')
@php $q = request()->only(['start', 'end']); @endphp

<div class="ma-page">
  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">{{ $crash->error_type }}</h1>
        <p class="ma-hero__lead">{{ $crash->error_message ?: 'Fără mesaj' }}</p>
      </div>
      <div class="ma-filters">
        <a class="ma-btn ma-btn--ghost" href="{{ route('mobile.crashes.list', $q) }}">Înapoi la listă</a>
        <a class="ma-btn" href="{{ route('mobile.problems', $q) }}">Înapoi la probleme</a>
      </div>
    </div>
  </section>

  <section class="ma-card ma-card--danger">
    <div class="ma-card__head">
      <h2><i class="fas fa-circle-info" aria-hidden="true"></i> Context</h2>
      <span class="ma-badge {{ $crash->is_fatal ? 'ma-badge--danger' : 'ma-badge--ok' }}">
        {{ $crash->is_fatal ? 'Critică' : 'Ușoară' }}
      </span>
    </div>
    <div class="ma-card__body">
      <div class="ma-meta">
        <div class="ma-meta__item"><span>Ora</span><strong>{{ optional($crash->occurred_at)->format('d.m.Y H:i:s') ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Platformă</span><strong>{{ $crash->platform ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Versiune app</span><strong>{{ $crash->app_version ?: '—' }}@if($crash->build_number) ({{ $crash->build_number }})@endif</strong></div>
        <div class="ma-meta__item"><span>Ecran</span><strong>{{ $crash->screen ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Utilizator</span><strong>{{ $crash->mobile_user_id ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Dispozitiv</span><strong>{{ $crash->device_id ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>OS / Model</span><strong>{{ trim(($crash->os_version ?: '').' / '.($crash->device_model ?: ''), ' /') ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Semnătură</span><strong>{{ $crash->fingerprint ? \Illuminate\Support\Str::limit($crash->fingerprint, 28) : '—' }}</strong></div>
      </div>
    </div>
  </section>

  <section class="ma-card ma-card--danger">
    <div class="ma-card__head">
      <h2><i class="fas fa-code" aria-hidden="true"></i> Detalii tehnice</h2>
    </div>
    <div class="ma-card__body">
      <pre class="ma-pre">{{ $crash->stack_trace ?: 'Fără detalii tehnice.' }}</pre>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-brackets-curly" aria-hidden="true"></i> Date suplimentare</h2>
    </div>
    <div class="ma-card__body">
      <pre class="ma-pre">{{ $crash->metadata ? json_encode($crash->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
    </div>
  </section>
</div>
@endsection
