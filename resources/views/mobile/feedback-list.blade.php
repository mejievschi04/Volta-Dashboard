@extends('layouts.app')

@section('title', 'Volta App – Feedback – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $days = max(1, (int) $start->diffInDays($end) + 1);
  $periodPresets = [
    ['label' => '7 zile', 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '30 zile', 'start' => now()->subDays(29)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => 'Luna curentă', 'start' => now()->startOfMonth()->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '90 zile', 'start' => now()->subDays(89)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
  ];
  $shotRate = ($summary['total'] ?? 0) > 0
    ? round((($summary['with_screenshot'] ?? 0) / $summary['total']) * 100, 1)
    : 0;
@endphp

<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">Tabela pentru feedback nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Feedback</h1>
        <p class="ma-hero__lead">
          Mesaje din „Raportează o problemă” —
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} ({{ $days }} zile).
        </p>
      </div>
      <form method="get" action="{{ route('mobile.feedback') }}" class="ma-filters">
        <div class="ma-field"><label for="feedbackStart">De la</label><input id="feedbackStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="feedbackEnd">Până la</label><input id="feedbackEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('mobile.feedback', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-comment-dots" aria-hidden="true"></i> Total rapoarte</span>
      <div class="ma-kpi__value">{{ number_format($summary['total'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Pe intervalul selectat</span>
    </div>
    <div class="ma-kpi ma-kpi--accent">
      <span class="ma-kpi__label"><i class="fas fa-image" aria-hidden="true"></i> Cu screenshot</span>
      <div class="ma-kpi__value">{{ number_format($summary['with_screenshot'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($shotRate, 1, ',', '.') }}% din total</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-mobile-screen" aria-hidden="true"></i> Device-uri</span>
      <div class="ma-kpi__value">{{ number_format($summary['devices'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Dispozitive distincte</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-users" aria-hidden="true"></i> Utilizatori</span>
      <div class="ma-kpi__value">{{ number_format($summary['users'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">Useri care au raportat</span>
    </div>
  </div>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-inbox" aria-hidden="true"></i> Inbox</h2>
      @if($schemaReady && $reports)
        <span class="ma-muted">{{ number_format($reports->total(), 0, ',', '.') }} rezultate</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead>
          <tr>
            <th>Ora</th>
            <th>Mesaj</th>
            <th>Nume</th>
            <th>Email</th>
            <th>Platformă</th>
            <th>Versiune</th>
            <th>Screenshot</th>
            <th>User</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        @if($schemaReady && $reports && $reports->count())
          @foreach($reports as $report)
            <tr>
              <td class="ma-muted">{{ optional($report->occurred_at)->format('d.m.Y H:i') }}</td>
              <td>{{ \Illuminate\Support\Str::limit($report->message ?: '—', 90) }}</td>
              <td>{{ $report->reporter_name ?: '—' }}</td>
              <td>{{ $report->reporter_email ?: '—' }}</td>
              <td>{{ $report->platform ?: '—' }}</td>
              <td>{{ $report->app_version ?: '—' }}</td>
              <td>
                <span class="ma-badge {{ $report->has_screenshot ? 'ma-badge--ok' : '' }}">
                  {{ $report->has_screenshot ? 'Da' : 'Nu' }}
                </span>
              </td>
              <td>{{ $report->mobile_user_id ?: '—' }}</td>
              <td><a class="ma-card__link" href="{{ route('mobile.feedback.show', array_merge(['report' => $report], $q)) }}">Detaliu →</a></td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="9" class="ma-muted">Nu există rapoarte în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $reports)
        <div style="margin-top:14px;">{{ $reports->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
