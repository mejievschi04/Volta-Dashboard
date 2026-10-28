@php
  $money = fn ($v) => number_format((float) $v, 2, ',', '.') . ' MDL';
  $lunaLabel = $bonus['luna_label'] ?? ($plan['luna_label'] ?? '');
@endphp
<div class="op-hero-kpis" aria-label="Plan personal și bonus · {{ $lunaLabel }}">
  @if(!empty($plan))
    @php
      $procent = (float) $plan['procent'];
      $reper = $plan['prognoza_procent'] ?? $procent;
      $tone = $procent >= 100 || $reper >= 100 ? 'done' : ($reper >= 85 ? 'warn' : 'bad');
    @endphp
    <div class="op-hero-kpi op-plan op-plan--{{ $tone }}">
      <span class="op-hero-kpi__title"><i class="fas fa-bullseye" aria-hidden="true"></i> Planul personal · {{ $plan['luna_label'] }}</span>
      <strong class="op-plan__pct">{{ number_format($procent, 1, ',', '.') }}%</strong>
      <div class="op-plan__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, round($procent)) }}" aria-label="Îndeplinire plan">
        <span style="width: {{ min(100, max(0, $procent)) }}%"></span>
      </div>
      <span class="op-hero-kpi__hint">{{ $money($plan['realizat']) }} din {{ $money($plan['plan']) }}</span>
      @if($plan['rest'] <= 0)
        <span class="op-hero-kpi__line op-hero-kpi__line--done"><i class="fas fa-check" aria-hidden="true"></i> Plan atins</span>
      @elseif($plan['pe_zi'] !== null)
        <span class="op-hero-kpi__line">De vândut pe zi: <strong>{{ $money($plan['pe_zi']) }}</strong></span>
        <span class="op-hero-kpi__hint">{{ $plan['zile_ramase'] }} {{ $plan['zile_ramase'] === 1 ? 'zi lucrătoare rămasă' : 'zile lucrătoare rămase' }} (L–V)</span>
      @else
        <span class="op-hero-kpi__line">Rămas: <strong>{{ $money($plan['rest']) }}</strong></span>
      @endif
    </div>
  @else
    <div class="op-hero-kpi op-plan op-plan--empty">
      <span class="op-hero-kpi__title"><i class="fas fa-bullseye" aria-hidden="true"></i> Planul personal</span>
      <span class="op-hero-kpi__line">Planul pentru luna curentă nu a fost setat încă.</span>
    </div>
  @endif

  @if(!empty($bonus))
    <div class="op-hero-kpi op-bonus">
      <span class="op-hero-kpi__title"><i class="fas fa-gift" aria-hidden="true"></i> Bonus din vânzări · {{ $bonus['luna_label'] }}</span>
      <strong class="op-bonus__value">{{ $money($bonus['bonus']) }}</strong>
      <span class="op-hero-kpi__hint">{{ \App\Support\BonusVanzari::procentLabel() }} din profitul brut de {{ $money($bonus['profit']) }}</span>
    </div>
  @endif
</div>
