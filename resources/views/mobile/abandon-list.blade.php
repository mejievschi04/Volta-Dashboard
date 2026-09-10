@extends('layouts.app')

@section('title', 'Volta App – Coșuri părăsite – VOLTA')
@section('header-title', 'Volta App')

@section('content')
<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">Tabela pentru datele din aplicație nu este încă creată. Rulează <code>php artisan migrate</code>.</div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <p class="ma-kicker">Aplicația Volta</p>
        <h1 class="ma-hero__title">Coșuri părăsite</h1>
        <p class="ma-hero__lead">La ce pas din plată renunță clienții, cu valoarea medie a coșului.</p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.abandon') }}" class="ma-filters">
        <div class="ma-field"><label for="abandonStart">De la</label><input id="abandonStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}"></div>
        <div class="ma-field"><label for="abandonEnd">Până la</label><input id="abandonEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}"></div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
  </section>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-cart-arrow-down" aria-hidden="true"></i> Pași din plată</h2>
      @if($schemaReady && $abandonRows)
        <span class="ma-muted">{{ number_format($abandonRows->total(), 0, ',', '.') }} pași</span>
      @endif
    </div>
    <div class="ma-card__body ma-table-wrap">
      <table class="ma-table">
        <thead><tr><th>Pas din plată</th><th class="num">Coșuri părăsite</th><th class="num">Valoare medie coș</th><th class="num">Produse medii</th></tr></thead>
        <tbody>
        @if($schemaReady && $abandonRows && $abandonRows->count())
          @foreach($abandonRows as $row)
            <tr>
              <td>Pas {{ $row->checkout_step ?: '?' }}</td>
              <td class="num">{{ number_format((int) $row->abandons, 0, ',', '.') }}</td>
              <td class="num">{{ $row->avg_cart_total !== null ? number_format((float) $row->avg_cart_total, 2, ',', '.') . ' MDL' : '—' }}</td>
              <td class="num">{{ $row->avg_items_count !== null ? number_format((float) $row->avg_items_count, 1, ',', '.') : '—' }}</td>
            </tr>
          @endforeach
        @else
          <tr><td colspan="4" class="ma-muted">Nu există date în perioada selectată.</td></tr>
        @endif
        </tbody>
      </table>
      @if($schemaReady && $abandonRows)
        <div style="margin-top:14px;">{{ $abandonRows->links('vendor.pagination.livrari') }}</div>
      @endif
    </div>
  </section>
</div>
@endsection
