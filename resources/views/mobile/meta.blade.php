@extends('layouts.app')

@section('title', 'Volta App – Meta – VOLTA')
@section('header-title', 'Volta App')

@push('styles')
<link rel="stylesheet" href="{{ url('css/mobile-analytics.css') }}">
@endpush

@section('content')
@php
  $q = request()->only(['start', 'end']);
  $periodPresets = [
    ['label' => '7 zile', 'start' => now()->subDays(6)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '30 zile', 'start' => now()->subDays(29)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => 'Luna curentă', 'start' => now()->startOfMonth()->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
    ['label' => '90 zile', 'start' => now()->subDays(89)->format('Y-m-d'), 'end' => now()->format('Y-m-d')],
  ];
  $maxHour = max(1, max($hourly) ?: 1);
  $maxWeekday = max(1, max($weekday) ?: 1);
  $maxPlatformSessions = max(1, (int) ($platforms->max('sessions') ?? 1));
  $funnelSteps = [
    ['label' => 'Vizite', 'value' => (int) ($funnel['visits'] ?? 0)],
    ['label' => 'Produs', 'value' => (int) ($funnel['product_views'] ?? 0), 'rate' => $funnel['visit_to_product_rate'] ?? 0],
    ['label' => 'Coș', 'value' => (int) ($funnel['add_to_cart'] ?? 0), 'rate' => $funnel['product_to_cart_rate'] ?? 0],
    ['label' => 'Checkout', 'value' => (int) ($funnel['checkout_started'] ?? 0), 'rate' => $funnel['cart_to_checkout_rate'] ?? 0],
    ['label' => 'Comandă', 'value' => (int) ($funnel['orders_completed'] ?? 0), 'rate' => $funnel['checkout_to_order_rate'] ?? 0],
  ];
  $maxFunnel = max(1, max(array_column($funnelSteps, 'value')) ?: 1);
@endphp

<div class="ma-page">
  @if(!$schemaReady)
    <div class="ma-alert">
      Tabela pentru evenimente mobile nu este încă creată. Rulează <code>php artisan migrate</code> și reîncarcă pagina.
    </div>
  @endif

  <section class="ma-hero">
    <div class="ma-hero__row">
      <div>
        <h1 class="ma-hero__title">Meta</h1>
        <p class="ma-hero__lead">
          Indicatori executivi din tot ce trimite app-ul: engagement, conversie, retenție, platforme și sănătate —
          {{ $start->format('d.m.Y') }} – {{ $end->format('d.m.Y') }} ({{ $days }} zile).
        </p>
      </div>
      <form method="get" action="{{ route('mobile.analytics.meta') }}" class="ma-filters">
        <div class="ma-field">
          <label for="metaStart">De la</label>
          <input id="metaStart" type="date" name="start" value="{{ $start->format('Y-m-d') }}">
        </div>
        <div class="ma-field">
          <label for="metaEnd">Până la</label>
          <input id="metaEnd" type="date" name="end" value="{{ $end->format('Y-m-d') }}">
        </div>
        <button class="ma-btn" type="submit"><i class="fas fa-filter" aria-hidden="true"></i> Aplică</button>
      </form>
    </div>
    <div class="ma-period">
      @foreach($periodPresets as $preset)
        @php $isActive = $start->format('Y-m-d') === $preset['start'] && $end->format('Y-m-d') === $preset['end']; @endphp
        <a class="ma-period__chip {{ $isActive ? 'is-active' : '' }}"
           href="{{ route('mobile.analytics.meta', ['start' => $preset['start'], 'end' => $preset['end']]) }}">
          {{ $preset['label'] }}
        </a>
      @endforeach
    </div>
  </section>

  @if(!empty($qualityNotes))
    <div class="ma-alert ma-alert--soft">
      <strong>Semnale:</strong>
      {{ implode(' ', $qualityNotes) }}
    </div>
  @endif

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-users" aria-hidden="true"></i> Utilizatori</span>
      <div class="ma-kpi__value">{{ number_format($meta['users'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">DAU mediu {{ number_format($meta['dau_avg'], 1, ',', '.') }} · {{ number_format($meta['devices'], 0, ',', '.') }} device-uri</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-fingerprint" aria-hidden="true"></i> Sesiuni</span>
      <div class="ma-kpi__value">{{ number_format($meta['sessions'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($meta['events_per_session'], 1, ',', '.') }} evt/sesiune · {{ number_format($meta['pages_per_session'], 1, ',', '.') }} pagini</span>
    </div>
    <div class="ma-kpi ma-kpi--accent">
      <span class="ma-kpi__label"><i class="fas fa-percent" aria-hidden="true"></i> Conversie</span>
      <div class="ma-kpi__value">{{ number_format($meta['conversion_rate'], 2, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['orders'], 0, ',', '.') }} comenzi din sesiuni</span>
    </div>
    <div class="ma-kpi ma-kpi--good">
      <span class="ma-kpi__label"><i class="fas fa-coins" aria-hidden="true"></i> Venit (proxy)</span>
      <div class="ma-kpi__value">{{ number_format($meta['revenue'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">AOV {{ number_format($meta['aov'], 0, ',', '.') }} · din cart_total pe order_completed</span>
    </div>
  </div>

  <div class="ma-kpis">
    <div class="ma-kpi {{ $meta['bounce_rate'] >= 55 ? 'ma-kpi--warn' : '' }}">
      <span class="ma-kpi__label"><i class="fas fa-door-open" aria-hidden="true"></i> Bounce</span>
      <div class="ma-kpi__value">{{ number_format($meta['bounce_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">Sesiuni cu ≤1 eveniment</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-clock" aria-hidden="true"></i> Durată sesiune</span>
      <div class="ma-kpi__value">{{ number_format($meta['avg_session_seconds'], 0, ',', '.') }}s</div>
      <span class="ma-kpi__help">Pagina medie {{ number_format($meta['avg_page_seconds'], 0, ',', '.') }}s</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-rotate" aria-hidden="true"></i> Returning</span>
      <div class="ma-kpi__value">{{ number_format($meta['returning_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['returning_users'], 0, ',', '.') }} returning · {{ number_format($meta['new_users'], 0, ',', '.') }} noi</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-calendar-check" aria-hidden="true"></i> Retenție proxy</span>
      <div class="ma-kpi__value">{{ number_format($meta['retention_proxy'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['multi_day_users'], 0, ',', '.') }} useri activi ≥2 zile în interval</span>
    </div>
  </div>

  <div class="ma-kpis">
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-cart-plus" aria-hidden="true"></i> Produs → Coș</span>
      <div class="ma-kpi__value">{{ number_format($meta['view_to_cart_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['add_to_cart'], 0, ',', '.') }} ATC / {{ number_format($meta['product_views'], 0, ',', '.') }} views</span>
    </div>
    <div class="ma-kpi {{ $meta['abandon_rate'] >= 70 ? 'ma-kpi--warn' : '' }}">
      <span class="ma-kpi__label"><i class="fas fa-cart-arrow-down" aria-hidden="true"></i> Abandon</span>
      <div class="ma-kpi__value">{{ number_format($meta['abandon_rate'], 1, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['cart_abandons'], 0, ',', '.') }} abandonuri · checkout→order {{ number_format($meta['checkout_to_order_rate'], 1, ',', '.') }}%</span>
    </div>
    <div class="ma-kpi {{ $meta['crash_rate'] >= 2 ? 'ma-kpi--warn' : '' }}">
      <span class="ma-kpi__label"><i class="fas fa-bug" aria-hidden="true"></i> Crash / sesiune</span>
      <div class="ma-kpi__value">{{ number_format($meta['crash_rate'], 2, ',', '.') }}%</div>
      <span class="ma-kpi__help">{{ number_format($meta['crashes'], 0, ',', '.') }} crash · {{ number_format($meta['fatal_rate'], 1, ',', '.') }}% fatale</span>
    </div>
    <div class="ma-kpi">
      <span class="ma-kpi__label"><i class="fas fa-comment-dots" aria-hidden="true"></i> Feedback</span>
      <div class="ma-kpi__value">{{ number_format($meta['feedback'], 0, ',', '.') }}</div>
      <span class="ma-kpi__help">{{ number_format($meta['feedback_with_screenshot'], 0, ',', '.') }} cu screenshot · login rate {{ number_format($meta['logged_in_session_rate'], 1, ',', '.') }}%</span>
    </div>
  </div>

  <div class="ma-grid">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-chart-area" aria-hidden="true"></i> Activitate zilnică</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-chart"><canvas id="metaDailyChart"></canvas></div>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-filter" aria-hidden="true"></i> Pâlnie condensată</h2>
        <a class="ma-card__link" href="{{ route('mobile.analytics.funnels', $q) }}">Detaliu →</a>
      </div>
      <div class="ma-card__body">
        <div class="ma-meta-funnel">
          @foreach($funnelSteps as $step)
            <div class="ma-meta-funnel__row">
              <div class="ma-meta-funnel__label">
                <span>{{ $step['label'] }}</span>
                @if(isset($step['rate']))
                  <em>{{ number_format($step['rate'], 1, ',', '.') }}%</em>
                @endif
              </div>
              <div class="ma-meta-funnel__track">
                <span style="width: {{ max(4, round(($step['value'] / $maxFunnel) * 100)) }}%"></span>
              </div>
              <strong>{{ number_format($step['value'], 0, ',', '.') }}</strong>
            </div>
          @endforeach
        </div>
        <p class="ma-muted" style="margin:12px 0 0;font-size:0.78rem;">
          Peak: {{ str_pad((string) $peakHour, 2, '0', STR_PAD_LEFT) }}:00 · {{ $weekdayLabels[$peakWeekdayIdx] ?? '—' }}
          · {{ number_format($meta['searches'], 0, ',', '.') }} căutări · {{ number_format($meta['banner_clicks'], 0, ',', '.') }} banner clicks · {{ number_format($meta['map_opens'], 0, ',', '.') }} map opens
        </p>
      </div>
    </section>
  </div>

  <div class="ma-grid ma-grid--2">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-clock" aria-hidden="true"></i> Oră din zi</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-heat">
          @foreach($hourly as $hour => $total)
            <div class="ma-heat__cell" title="{{ str_pad((string) $hour, 2, '0', STR_PAD_LEFT) }}:00 — {{ number_format($total, 0, ',', '.') }} evenimente">
              <span class="ma-heat__bar" style="height: {{ max(6, round(($total / $maxHour) * 100)) }}%"></span>
              <em>{{ $hour % 3 === 0 ? str_pad((string) $hour, 2, '0', STR_PAD_LEFT) : '' }}</em>
            </div>
          @endforeach
        </div>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-calendar-week" aria-hidden="true"></i> Zi din săptămână</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-heat ma-heat--week">
          @foreach($weekday as $idx => $total)
            <div class="ma-heat__cell" title="{{ $weekdayLabels[$idx] }} — {{ number_format($total, 0, ',', '.') }} evenimente">
              <span class="ma-heat__bar" style="height: {{ max(6, round(($total / $maxWeekday) * 100)) }}%"></span>
              <em>{{ $weekdayLabels[$idx] }}</em>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  </div>

  <div class="ma-grid ma-grid--3">
    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-mobile-screen" aria-hidden="true"></i> Platforme</h2>
      </div>
      <div class="ma-card__body">
        @forelse($platforms as $row)
          <div class="ma-rank">
            <div class="ma-rank__top">
              <strong>{{ $row->label }}</strong>
              <span>{{ number_format((int) $row->sessions, 0, ',', '.') }} sesiuni</span>
            </div>
            <div class="ma-rank__bar"><span style="width: {{ max(4, round(((int) $row->sessions / $maxPlatformSessions) * 100)) }}%"></span></div>
            <div class="ma-rank__meta">{{ number_format((int) $row->events, 0, ',', '.') }} evt · {{ number_format((int) $row->orders, 0, ',', '.') }} comenzi</div>
          </div>
        @empty
          <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i> Nicio dată pe platformă.</div>
        @endforelse
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-code-branch" aria-hidden="true"></i> Versiuni app</h2>
      </div>
      <div class="ma-card__body">
        <div class="ma-table-wrap">
          <table class="ma-table">
            <thead>
              <tr>
                <th>Versiune</th>
                <th class="num">Sesiuni</th>
                <th class="num">Useri</th>
                <th class="num">Comenzi</th>
              </tr>
            </thead>
            <tbody>
              @forelse($versions as $row)
                <tr>
                  <td><span class="ma-badge">{{ $row->label }}</span></td>
                  <td class="num">{{ number_format((int) $row->sessions, 0, ',', '.') }}</td>
                  <td class="num">{{ number_format((int) $row->users, 0, ',', '.') }}</td>
                  <td class="num">{{ number_format((int) $row->orders, 0, ',', '.') }}</td>
                </tr>
              @empty
                <tr><td colspan="4" class="ma-muted">Nicio versiune raportată.</td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="ma-card">
      <div class="ma-card__head">
        <h2><i class="fas fa-door-open" aria-hidden="true"></i> Pagini de intrare</h2>
      </div>
      <div class="ma-card__body">
        @forelse($topEntryPages as $row)
          <div class="ma-rank">
            <div class="ma-rank__top">
              <strong title="{{ $row->page }}">{{ \Illuminate\Support\Str::limit($row->page, 28) }}</strong>
              <span>{{ number_format((int) $row->total, 0, ',', '.') }}</span>
            </div>
          </div>
        @empty
          <div class="ma-empty"><i class="fas fa-inbox" aria-hidden="true"></i> Trimite <code>previous_page</code> gol la primul screen ca să vedem entry points.</div>
        @endforelse
      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  const el = document.getElementById('metaDailyChart');
  if (!el || typeof Chart === 'undefined') return;
  const data = @json($dailyActive);
  new Chart(el, {
    type: 'line',
    data: {
      labels: data.labels || [],
      datasets: [
        {
          label: 'Useri',
          data: data.users || [],
          borderColor: '#ffee00',
          backgroundColor: 'rgba(255,238,0,0.12)',
          tension: 0.35,
          fill: true,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: 'Sesiuni',
          data: data.sessions || [],
          borderColor: '#38bdf8',
          backgroundColor: 'transparent',
          tension: 0.35,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: 'Comenzi',
          data: data.orders || [],
          borderColor: '#34d399',
          backgroundColor: 'transparent',
          tension: 0.35,
          pointRadius: 0,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          labels: { color: '#94a3b8', boxWidth: 10, font: { size: 11 } },
        },
      },
      scales: {
        x: {
          ticks: { color: '#64748b', maxTicksLimit: 8, font: { size: 10 } },
          grid: { color: 'rgba(148,163,184,0.08)' },
        },
        y: {
          beginAtZero: true,
          ticks: { color: '#64748b', font: { size: 10 } },
          grid: { color: 'rgba(148,163,184,0.08)' },
        },
      },
    },
  });
})();
</script>
@endpush
