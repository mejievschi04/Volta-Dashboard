@extends('layouts.app')

@section('title', 'Volta App – Pagini – VOLTA')
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
        <h1 class="ma-hero__title">Pagini app</h1>
        <p class="ma-hero__lead">Toate ecranele din aplicație, cu views, timp mediu și volum de evenimente.</p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.pages') }}" class="ma-filters">
        <div class="ma-field"><label for="pagesStart">De la</label><input id="pagesStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="pagesEnd">Până la</label><input id="pagesEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-file-lines" aria-hidden="true"></i> Listă pagini</h2>
      @if($schemaReady && $pages)
        <span class="ma-muted">{{ number_format($pages->total(), 0, ',', '.') }} rezultate</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead><tr><th>Pagină</th><th class="num">Views</th><th class="num">Timp mediu</th><th class="num">Evenimente</th></tr></thead>
        <tbody>
        @if($schemaReady && $pages && $pages->count())
          @foreach($pages as $page)
            <tr>
              <td>{{ $page->page }}</td>
              <td class="num">{{ number_format((int) $page->views, 0, ',', '.') }}</td>
              <td class="num">{{ $page->avg_duration_ms ? number_format(round($page->avg_duration_ms / 1000), 0, ',', '.') . 's' : '—' }}</td>
              <td class="num">{{ number_format((int) $page->events_count, 0, ',', '.') }}</td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="4" class="ma-muted">Nu există date în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $pages)
        <div style="margin-top:14px;">{{ $pages->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
