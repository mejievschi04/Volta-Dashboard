@php
  $fmt = fn ($value) => number_format((float) $value, 0, ',', '.');
  $dec = fn ($value, $d = 1) => number_format((float) $value, $d, ',', '.');
  $pct = function ($value) {
      $value = (float) $value;
      if ($value > 0 && $value <= 1) {
          $value *= 100;
      }

      return number_format($value, 1, ',', '.').'%';
  };
  $dur = function ($seconds) {
      if ($seconds === null || $seconds === '') {
          return '—';
      }
      $s = (int) round((float) $seconds);
      if ($s < 60) {
          return $s.' s';
      }
      if ($s < 3600) {
          return intdiv($s, 60).' min'.($s % 60 ? ' '.($s % 60).' s' : '');
      }

      return intdiv($s, 3600).' h'.(intdiv($s % 3600, 60) ? ' '.intdiv($s % 3600, 60).' min' : '');
  };
  $chatErrorTexts = [
      'not_configured' => 'Conexiunea cu VoltaChat nu este configurată. Setați <code>VOLTACHAT_URL</code> și <code>VOLTACHAT_ANALYTICS_TOKEN</code> în <code>.env</code>, apoi <code>php artisan config:clear</code>.',
      'unreachable' => 'Serverul VoltaChat nu răspunde. Încercați din nou peste câteva momente.',
      'unauthorized' => 'Tokenul pentru VoltaChat este greșit (401). Verificați <code>VOLTACHAT_ANALYTICS_TOKEN</code>.',
      'disabled' => 'API-ul de statistici este oprit pe serverul VoltaChat (503): lipsește <code>ANALYTICS_API_TOKEN</code> acolo.',
      'rate_limited' => 'Prea multe cereri către VoltaChat. Reîncercați peste un minut.',
      'invalid' => 'VoltaChat a respins perioada sau filtrele alese.',
  ];
  $kpis = $chat['kpis'] ?? [];
  $ds = $chat['datasets'] ?? [];
  $channels = $chat['channels'] ?? [];
  $operators = $ds['operators']['rows'] ?? [];
  $statusNames = ['new' => 'Noi', 'open' => 'Deschise', 'active' => 'Active', 'closed' => 'Închise'];
  $bucketNames = ['lt_1m' => '< 1 min', 'm1_5' => '1–5 min', 'm5_15' => '5–15 min', 'm15_60' => '15–60 min', 'gt_1h' => '> 1 h'];
  $bucketColors = ['lt_1m' => '#34d399', 'm1_5' => '#22d3ee', 'm5_15' => '#a78bfa', 'm15_60' => '#fb923c', 'gt_1h' => '#fb7185'];
  $pairs = function ($set) {
      $labels = $set['labels'] ?? [];
      $data = $set['data'] ?? [];
      $rows = [];
      foreach ($labels as $i => $label) {
          $rows[] = ['label' => (string) $label, 'value' => (float) ($data[$i] ?? 0)];
      }

      return $rows;
  };
  $channelRows = $pairs($ds['channels'] ?? []);
  $statusRows = $pairs($ds['status'] ?? []);
  if (! $statusRows && $kpis) {
      foreach ($statusNames as $key => $label) {
          $statusRows[] = ['label' => $key, 'value' => (float) ($kpis['status_'.$key] ?? 0)];
      }
  }
  $channelMax = max(1, ...(array_column($channelRows, "value") ?: [0]));
  $statusMax = max(1, ...(array_column($statusRows, "value") ?: [0]));
  $heat = [];
  foreach ($ds['heatmap']['points'] ?? [] as $p) {
      $heat[(int) ($p['dow'] ?? 0)][(int) ($p['h'] ?? 0)] = (int) ($p['v'] ?? 0);
  }
  $heatMax = max(1, (int) ($ds['heatmap']['max'] ?? 0), ...array_map(fn ($row) => max($row ?: [0]), $heat ?: [[0]]));
  $dowNames = ['Lu', 'Ma', 'Mi', 'Jo', 'Vi', 'Sâ', 'Du'];
  $f = $chatFilters;
@endphp

<div class="ma-section__head vc-head">
  <div>
    <h2><i class="fas fa-comments" aria-hidden="true"></i> VoltaChat</h2>
    <p>Dialogurile din chat, pentru aceeași perioadă: {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }}</p>
  </div>
  @if($chat)
    <form class="vc-filters" data-vc-filters>
      <div class="ma-field">
        <label for="vcType">Dialoguri</label>
        <select id="vcType" name="chat_type">
          <option value="customer" @selected(($f['type'] ?? '') === 'customer')>Cu clienții</option>
          <option value="internal" @selected(($f['type'] ?? '') === 'internal')>Interne</option>
        </select>
      </div>
      <div class="ma-field">
        <label for="vcChannel">Canal</label>
        <select id="vcChannel" name="chat_channel">
          <option value="">Toate canalele</option>
          @foreach($channels as $channel)
            <option value="{{ $channel['id'] ?? '' }}" @selected((string) ($f['channelId'] ?? '') === (string) ($channel['id'] ?? ''))>{{ $channel['name'] ?? ('#'.($channel['id'] ?? '')) }}</option>
          @endforeach
        </select>
      </div>
      <div class="ma-field">
        <label for="vcGrain">Grupare</label>
        <select id="vcGrain" name="chat_grain">
          <option value="day" @selected(($f['grain'] ?? '') === 'day')>Pe zile</option>
          <option value="week" @selected(($f['grain'] ?? '') === 'week')>Pe săptămâni</option>
          <option value="month" @selected(($f['grain'] ?? '') === 'month')>Pe luni</option>
        </select>
      </div>
    </form>
  @endif
</div>

@if(! $chat)
  <div class="ma-alert ma-alert--soft">
    <strong>Statistica VoltaChat nu este disponibilă.</strong>
    {!! $chatErrorTexts[$chatError] ?? 'Răspuns neașteptat de la VoltaChat ('.e($chatError).').' !!}
  </div>
@else
  <div class="ma-kpis">
    <div class="ma-kpi ma-kpi--accent">
      <span class="ma-kpi__label"><i class="fas fa-comments" aria-hidden="true"></i> Dialoguri</span>
      <div class="ma-kpi__value">{{ $fmt($kpis['total_conversations'] ?? 0) }}</div>
      <span class="ma-kpi__help">{{ $fmt($kpis['status_closed'] ?? 0) }} închise · {{ $pct($kpis['status_closed_rate'] ?? 0) }}</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-users" aria-hidden="true"></i> Clienți unici</span>
      <div class="ma-kpi__value">{{ $fmt($kpis['unique_customers'] ?? 0) }}</div>
      <span class="ma-kpi__help">{{ $fmt($kpis['new_customers'] ?? 0) }} noi · {{ $fmt($kpis['returning_customers'] ?? 0) }} reveniți</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-message" aria-hidden="true"></i> Mesaje</span>
      <div class="ma-kpi__value">{{ $fmt($kpis['total_messages'] ?? 0) }}</div>
      <span class="ma-kpi__help">{{ $dec($kpis['avg_messages_per_conversation'] ?? 0) }} pe dialog în medie · mediana {{ $dec($kpis['median_messages_per_conversation'] ?? 0, 0) }}</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-inbox" aria-hidden="true"></i> În lucru</span>
      <div class="ma-kpi__value">{{ $fmt(($kpis['status_new'] ?? 0) + ($kpis['status_open'] ?? 0) + ($kpis['status_active'] ?? 0)) }}</div>
      <span class="ma-kpi__help">{{ $fmt($kpis['status_new'] ?? 0) }} noi · {{ $fmt($kpis['status_open'] ?? 0) }} deschise · {{ $fmt($kpis['status_active'] ?? 0) }} active</span>
    </div>
    <div class="ma-kpi ma-kpi--good">
      <span class="ma-kpi__label"><i class="fas fa-check-double" aria-hidden="true"></i> Citite de operatori</span>
      <div class="ma-kpi__value">{{ $pct($kpis['read_rate_inbound'] ?? 0) }}</div>
      <span class="ma-kpi__help">Mesajele clienților care au fost citite</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-eye" aria-hidden="true"></i> Citite de clienți</span>
      <div class="ma-kpi__value">{{ $pct($kpis['read_rate_outbound'] ?? 0) }}</div>
      <span class="ma-kpi__help">Răspunsurile operatorilor văzute de clienți</span>
    </div>
  </div>

  <div class="ma-grid vc-grid">
    <section class="ma-card">
      <div class="ma-card__head"><h2><i class="fas fa-chart-line" aria-hidden="true"></i> Evoluție dialoguri</h2></div>
      <div class="ma-card__body"><div class="ma-chart"><canvas id="vcTrendChart"></canvas></div></div>
    </section>
    <section class="ma-card">
      <div class="ma-card__head"><h2><i class="fas fa-tower-broadcast" aria-hidden="true"></i> Canale și status</h2></div>
      <div class="ma-card__body">
        @forelse($channelRows as $row)
          <div class="ma-rank">
            <div class="ma-rank__top"><strong>{{ $row['label'] }}</strong><span>{{ $fmt($row['value']) }}</span></div>
            <div class="ma-rank__bar"><span style="width: {{ round($row['value'] / $channelMax * 100, 1) }}%"></span></div>
          </div>
        @empty
          <div class="ma-empty">Niciun dialog pe canale în perioada aleasă.</div>
        @endforelse
        <div class="vc-divider"></div>
        @foreach($statusRows as $row)
          <div class="ma-rank">
            <div class="ma-rank__top"><strong>{{ $statusNames[strtolower($row['label'])] ?? $row['label'] }}</strong><span>{{ $fmt($row['value']) }}</span></div>
            <div class="ma-rank__bar vc-rank--status"><span style="width: {{ round($row['value'] / $statusMax * 100, 1) }}%"></span></div>
          </div>
        @endforeach
      </div>
    </section>
  </div>

  <div class="ma-grid vc-grid">
    <section class="ma-card">
      <div class="ma-card__head"><h2><i class="fas fa-calendar-week" aria-hidden="true"></i> Când scriu clienții</h2><span class="ma-muted vc-small">zi × oră</span></div>
      <div class="ma-card__body">
        <div class="vc-heat">
          <span></span>
          @for($h = 0; $h < 24; $h++)
            <span class="vc-heat__hour">{{ $h % 3 === 0 ? $h : '' }}</span>
          @endfor
          @foreach($dowNames as $dow => $dowName)
            <span class="vc-heat__dow">{{ $dowName }}</span>
            @for($h = 0; $h < 24; $h++)
              @php $v = $heat[$dow][$h] ?? 0; @endphp
              <span class="vc-heat__cell" style="--vc-a: {{ $v ? round(0.12 + 0.88 * $v / $heatMax, 3) : 0 }}" title="{{ $dowName }} {{ str_pad($h, 2, '0', STR_PAD_LEFT) }}:00 · {{ $fmt($v) }} {{ $v === 1 ? 'dialog' : 'dialoguri' }}"></span>
            @endfor
          @endforeach
        </div>
      </div>
    </section>
    <section class="ma-card">
      <div class="ma-card__head"><h2><i class="fas fa-check-double" aria-hidden="true"></i> Rată de citire</h2></div>
      <div class="ma-card__body"><div class="ma-chart vc-chart--small"><canvas id="vcReadChart"></canvas></div></div>
    </section>
  </div>

  <section class="ma-card">
    <div class="ma-card__head">
      <h2><i class="fas fa-headset" aria-hidden="true"></i> Operatori</h2>
      <div class="vc-sort">
        <span class="ma-muted vc-small">Ordonează după</span>
        <button type="button" class="ma-period__chip {{ ($f['operatorSort'] ?? '') !== 'response' ? 'is-active' : '' }}" data-vc-sort="conversations">dialoguri</button>
        <button type="button" class="ma-period__chip {{ ($f['operatorSort'] ?? '') === 'response' ? 'is-active' : '' }}" data-vc-sort="response">timp de răspuns</button>
      </div>
    </div>
    <div class="ma-card__body">
      @if(! $operators)
        <div class="ma-empty"><i class="fas fa-headset" aria-hidden="true"></i>Niciun operator nu a răspuns în perioada aleasă.</div>
      @else
        <div class="ma-table-wrap">
          <table class="ma-table vc-ops">
            <thead>
              <tr>
                <th>Operator</th>
                <th class="num">Dialoguri</th>
                <th class="num">Răspunsuri</th>
                <th class="num">Timp mediu</th>
                <th class="num">Mediana</th>
                <th>Viteza răspunsurilor</th>
                <th>Cele mai lente</th>
              </tr>
            </thead>
            <tbody>
              @foreach($operators as $op)
                @php
                  $buckets = $op['buckets'] ?? [];
                  $bucketTotal = max(1, array_sum(array_map('intval', $buckets)));
                  $art = $op['art_seconds'] ?? null;
                @endphp
                <tr>
                  <td><strong>{{ $op['name'] ?? ('#'.($op['id'] ?? '')) }}</strong></td>
                  <td class="num">{{ $fmt($op['conversations'] ?? 0) }}</td>
                  <td class="num">{{ $fmt($op['replies'] ?? 0) }}</td>
                  <td class="num {{ $art !== null && $art > 900 ? 'vc-bad' : ($art !== null && $art <= 120 ? 'vc-good' : '') }}">{{ $dur($art) }}</td>
                  <td class="num">{{ $dur($op['median_seconds'] ?? null) }}</td>
                  <td>
                    <div class="vc-buckets" title="@foreach($bucketNames as $k => $n){{ $n }}: {{ (int) ($buckets[$k] ?? 0) }}{{ $loop->last ? '' : ' · ' }}@endforeach">
                      @foreach($bucketNames as $k => $n)
                        @if(($buckets[$k] ?? 0) > 0)
                          <span style="width: {{ round($buckets[$k] / $bucketTotal * 100, 2) }}%; background: {{ $bucketColors[$k] }}"></span>
                        @endif
                      @endforeach
                    </div>
                    <div class="vc-buckets__meta">{{ round(($buckets['lt_1m'] ?? 0) / $bucketTotal * 100) }}% sub 1 min · {{ round(($buckets['gt_1h'] ?? 0) / $bucketTotal * 100) }}% peste 1 h</div>
                  </td>
                  <td>
                    @php $slowest = $op['slowest'] ?? []; @endphp
                    @if($slowest)
                      <details class="vc-slow">
                        <summary>{{ $dur($slowest[0]['seconds'] ?? null) }} @if(count($slowest) > 1)<span class="ma-muted">+{{ count($slowest) - 1 }}</span>@endif</summary>
                        <ul>
                          @foreach($slowest as $s)
                            <li>
                              @if(! empty($s['url']) && str_starts_with($s['url'], 'http'))
                                <a href="{{ $s['url'] }}" target="_blank" rel="noopener">#{{ $s['conversation_id'] ?? '' }}</a>
                              @else
                                #{{ $s['conversation_id'] ?? '' }}
                              @endif
                              <span>{{ $dur($s['seconds'] ?? null) }}</span>
                              @if(! empty($s['from']))
                                <em>{{ \Illuminate\Support\Carbon::parse($s['from'])->format('d.m H:i') }}</em>
                              @endif
                            </li>
                          @endforeach
                        </ul>
                      </details>
                    @else
                      <span class="ma-muted">—</span>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        <div class="vc-legend">
          @foreach($bucketNames as $k => $n)
            <span><i style="background: {{ $bucketColors[$k] }}"></i>{{ $n }}</span>
          @endforeach
          <span class="ma-muted">Timp lucrător: L–V 08–18, S 08–16. Răspunsurile automate nu intră în calcul.</span>
        </div>
      @endif
    </div>
  </section>

  @php
    $chartPayload = [
        'conversations' => $ds['conversations'] ?? null,
        'unique_customers' => $ds['unique_customers'] ?? null,
        'messages' => $ds['messages'] ?? null,
        'read_inbound' => $ds['read_rate_inbound'] ?? null,
        'read_outbound' => $ds['read_rate_outbound'] ?? null,
    ];
  @endphp
  <script type="application/json" data-vc-charts>@json($chartPayload)</script>
@endif
