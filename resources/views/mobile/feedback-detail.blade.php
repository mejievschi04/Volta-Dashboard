@extends('layouts.app')

@section('title', 'Volta App – Detaliu feedback – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php $q = request()->only(['start', 'end']); @endphp

<div class="ma-page">
  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Raport #{{ $report->id }}</h1>
        <p class="ma-hero__lead" style="white-space:pre-wrap;">{{ $report->message }}</p>
      </div>
      <div class="ma-filters">
        <a class="ma-btn ma-btn--ghost" href="{{ route('mobile.feedback', $q) }}">Înapoi la listă</a>
      </div>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-circle-info" aria-hidden="true"></i> Context</h2>
      <span class="ma-badge">{{ $report->status ?: 'new' }}</span>
    </div>
    <div class="ma-card__body">
      <div class="ma-meta">
        <div class="ma-meta__item"><span>Ora</span><strong>{{ optional($report->occurred_at)->format('d.m.Y H:i:s') ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Platformă</span><strong>{{ $report->platform ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Versiune app</span><strong>{{ $report->app_version ?: '—' }}@if($report->build_number) ({{ $report->build_number }})@endif</strong></div>
        <div class="ma-meta__item"><span>Nume</span><strong>{{ $report->reporter_name ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Email</span><strong>{{ $report->reporter_email ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>User</span><strong>{{ $report->mobile_user_id ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>Device</span><strong>{{ $report->device_id ?: '—' }}</strong></div>
        <div class="ma-meta__item"><span>OS / Model</span><strong>{{ trim(($report->os_version ?: '').' / '.($report->device_model ?: ''), ' /') ?: '—' }}</strong></div>
      </div>
    </div>
  </section>

  @if($report->has_screenshot && $report->screenshot_base64)
  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-image" aria-hidden="true"></i> Screenshot{{ $report->screenshot_filename ? ' — '.$report->screenshot_filename : '' }}</h2>
    </div>
    <div class="ma-card__body">
      <img
        class="ma-shot"
        alt="Screenshot raport"
        src="data:{{ $report->screenshot_mime ?: 'image/jpeg' }};base64,{{ $report->screenshot_base64 }}"
      >
    </div>
  </section>
  @endif

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-brackets-curly" aria-hidden="true"></i> Metadata</h2>
    </div>
    <div class="ma-card__body">
      <pre class="ma-pre">{{ $report->metadata ? json_encode($report->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '{}' }}</pre>
    </div>
  </section>
</div>
@endsection
