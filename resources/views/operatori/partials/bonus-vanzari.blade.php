@if(!empty($bonus))
  @php
    $titleTag = $titleTag ?? 'h3';
    $money = fn ($v) => number_format((float) $v, 2, ',', '.') . ' MDL';
  @endphp
  <div class="{{ $cardClass }} op-bonus">
    <{{ $titleTag }} class="{{ $titleClass }}"><i class="fas fa-gift" aria-hidden="true"></i> Bonus din vânzări · {{ $bonus['luna_label'] }}</{{ $titleTag }}>
    <strong class="op-bonus__value">{{ $money($bonus['bonus']) }}</strong>
    <p class="op-bonus__hint">{{ \App\Support\BonusVanzari::procentLabel() }} din profitul brut de {{ $money($bonus['profit']) }}</p>
  </div>
@endif
