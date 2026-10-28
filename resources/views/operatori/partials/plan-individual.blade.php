@php $titleTag = $titleTag ?? 'h3'; @endphp
@if(!empty($plan))
  @php
    $money = fn ($v) => number_format((float) $v, 2, ',', '.') . ' MDL';
    $procent = (float) $plan['procent'];
    $reper = $plan['prognoza_procent'] ?? $procent;
    $tone = $procent >= 100 || $reper >= 100 ? 'done' : ($reper >= 85 ? 'warn' : 'bad');
  @endphp
  <div class="{{ $cardClass }} op-plan op-plan--{{ $tone }}">
    <{{ $titleTag }} class="{{ $titleClass }}"><i class="fas fa-bullseye" aria-hidden="true"></i> Planul personal · {{ $plan['luna_label'] }}</{{ $titleTag }}>

    <div class="op-plan__big">
      <div class="op-plan__big-item">
        <span class="op-plan__big-label">Îndeplinit</span>
        <strong class="op-plan__pct">{{ number_format($procent, 1, ',', '.') }}%</strong>
      </div>
      <div class="op-plan__big-item">
        <span class="op-plan__big-label">De vândut pe zi</span>
        @if($plan['rest'] <= 0)
          <strong class="op-plan__daily op-plan__daily--done">Plan atins</strong>
        @elseif($plan['pe_zi'] !== null)
          <strong class="op-plan__daily">{{ $money($plan['pe_zi']) }}</strong>
        @else
          <strong class="op-plan__daily">—</strong>
        @endif
      </div>
    </div>

    <div class="op-plan__bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ min(100, round($procent)) }}" aria-label="Îndeplinire plan">
      <span style="width: {{ min(100, max(0, $procent)) }}%"></span>
    </div>
    <p class="op-plan__of">{{ $money($plan['realizat']) }} din {{ $money($plan['plan']) }}</p>

    <dl class="op-plan__facts">
      <div><dt>Rămas din plan</dt><dd>{{ $plan['rest'] > 0 ? $money($plan['rest']) : '0,00 MDL' }}</dd></div>
      <div><dt>Zile lucrătoare rămase (L–V)</dt><dd>{{ $plan['zile_ramase'] }}</dd></div>
      @if($plan['prognoza_procent'] !== null)
        <div><dt>Prognoză la final de lună</dt><dd>{{ number_format($plan['prognoza_procent'], 1, ',', '.') }}%</dd></div>
      @endif
    </dl>
  </div>
@elseif(!empty($showEmpty))
  <div class="{{ $cardClass }} op-plan op-plan--empty">
    <{{ $titleTag }} class="{{ $titleClass }}"><i class="fas fa-bullseye" aria-hidden="true"></i> Planul personal</{{ $titleTag }}>
    <p class="op-plan__of">Planul pentru luna curentă nu a fost setat încă.</p>
  </div>
@endif
