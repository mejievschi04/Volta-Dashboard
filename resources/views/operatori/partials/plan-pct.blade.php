@if($pct === null)
  —
@else
  <span class="op-plan-pct op-plan-pct--{{ $pct >= 100 ? 'done' : ($pct >= 85 ? 'warn' : 'bad') }}">{{ number_format($pct, 1, ',', '.') }}%</span>
@endif
