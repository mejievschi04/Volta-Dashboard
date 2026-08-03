@extends('layouts.app')

@section('title', 'Volta App – Bannere – VOLTA')
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
        <h1 class="ma-hero__title">Bannere</h1>
        <p class="ma-hero__lead">Performanța bannerelor din app: click-uri și ultima interacțiune.</p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.banners') }}" class="ma-filters">
        <div class="ma-field"><label for="bannersStart">De la</label><input id="bannersStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="bannersEnd">Până la</label><input id="bannersEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-rectangle-ad" aria-hidden="true"></i> Click-uri pe bannere</h2>
      @if($schemaReady && $banners)
        <span class="ma-muted">{{ number_format($banners->total(), 0, ',', '.') }} bannere</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead><tr><th>Banner</th><th class="num">Click-uri</th><th>Ultimul click</th></tr></thead>
        <tbody>
        @if($schemaReady && $banners && $banners->count())
          @foreach($banners as $banner)
            <tr>
              <td>{{ $banner->banner_title ?: ($banner->banner_id ?: '—') }}</td>
              <td class="num">{{ number_format((int) $banner->clicks, 0, ',', '.') }}</td>
              <td class="ma-muted">{{ $banner->last_click_at ? \Carbon\Carbon::parse($banner->last_click_at)->format('d.m.Y H:i') : '—' }}</td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="3" class="ma-muted">Nu există date în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $banners)
        <div style="margin-top:14px;">{{ $banners->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
