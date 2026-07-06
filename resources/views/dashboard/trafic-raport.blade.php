@extends('layouts.app')

@section('title', 'Raport Trafic – VOLTA')
@section('header-title', '')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.trafic-raport-filters {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 16px;
  margin-bottom: 24px;
  padding: 20px;
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: linear-gradient(165deg, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.96) 100%);
}

.trafic-raport-mode {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
}

.trafic-raport-mode button {
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(15, 23, 42, 0.5);
  color: #e2e8f0;
  border-radius: 10px;
  padding: 10px 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.trafic-raport-mode button.active {
  border-color: rgba(255, 238, 0, 0.5);
  background: rgba(255, 238, 0, 0.12);
  color: #ffee00;
}

.trafic-raport-months {
  display: flex;
  flex-wrap: wrap;
  gap: 16px;
  align-items: flex-end;
  flex: 1 1 280px;
}

.trafic-raport-months label {
  display: block;
  font-size: 12px;
  font-weight: 600;
  color: #94a3b8;
  margin-bottom: 6px;
}

.trafic-raport-months select {
  min-width: 180px;
  height: 42px;
  padding: 0 12px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.12);
  background: rgba(15, 23, 42, 0.7);
  color: #fff;
  font-weight: 600;
}

.trafic-raport-generate {
  border: none;
  border-radius: 10px;
  padding: 12px 20px;
  font-weight: 700;
  cursor: pointer;
  background: #ffee00;
  color: #0f172a;
  transition: transform 0.15s ease;
}

.trafic-raport-generate:hover { transform: translateY(-1px); }

.trafic-raport-card {
  border-radius: 16px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: linear-gradient(165deg, rgba(30, 41, 59, 0.92) 0%, rgba(15, 23, 42, 0.96) 100%);
  padding: 24px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.28);
}

.trafic-raport-card h2 {
  margin: 0 0 8px;
  font-size: 1.25rem;
  color: #f8fafc;
  display: flex;
  align-items: center;
  gap: 10px;
}

.trafic-raport-card h2 i { color: #ffee00; }

.trafic-raport-period {
  margin: 0 0 20px;
  color: #94a3b8;
  font-size: 14px;
}

.trafic-raport-warning {
  margin-bottom: 16px;
  padding: 12px 14px;
  border-radius: 10px;
  border: 1px solid rgba(245, 158, 11, 0.35);
  background: rgba(245, 158, 11, 0.1);
  color: #fcd34d;
  font-size: 13px;
}

.trafic-raport-table-wrap { overflow-x: auto; }

.trafic-raport-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 560px;
}

.trafic-raport-table th,
.trafic-raport-table td {
  padding: 14px 16px;
  text-align: left;
  border-bottom: 1px solid rgba(255, 255, 255, 0.06);
}

.trafic-raport-table th {
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #e2e8f0;
  background: rgba(255, 238, 0, 0.12);
}

.trafic-raport-table td { color: #f8fafc; }
.trafic-raport-table td.num { text-align: right; font-variant-numeric: tabular-nums; font-weight: 600; }
.trafic-raport-table tbody tr:hover td { background: rgba(255, 238, 0, 0.05); }
.trafic-raport-table tfoot td {
  font-weight: 800;
  color: #ffee00;
  border-top: 2px solid rgba(255, 238, 0, 0.25);
}

.trafic-raport-definitions {
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 13px;
  color: #94a3b8;
  line-height: 1.55;
}

.trafic-raport-definitions h3 {
  margin: 0 0 10px;
  font-size: 14px;
  color: #e2e8f0;
}

.trafic-raport-definitions ol {
  margin: 0;
  padding-left: 20px;
}

.trafic-raport-definitions li { margin-bottom: 6px; }

.trafic-raport-source {
  font-size: 12px;
  color: #64748b;
  margin: 0 0 12px;
}
  text-align: center;
  padding: 40px 16px;
  color: #94a3b8;
}

.trafic-raport-loading {
  text-align: center;
  padding: 32px;
  color: #94a3b8;
}

.trafic-raport-loading i { color: #ffee00; margin-right: 8px; }

@media (max-width: 640px) {
  .trafic-raport-filters { flex-direction: column; align-items: stretch; }
  .trafic-raport-months select { width: 100%; min-width: 0; }
  .trafic-raport-generate { width: 100%; }
}
</style>
@endpush

@section('content')
<div class="trafic-subpage stats-page raport-page">
  <a href="{{ route('trafic') }}" class="back-btn trafic-back-link">
    <i class="fas fa-arrow-left" aria-hidden="true"></i>
    <span>Înapoi la Trafic</span>
  </a>

  <h1><i class="fas fa-file-alt" aria-hidden="true"></i>Raport Trafic</h1>
  <p style="color:#94a3b8;margin:-8px 0 20px;font-size:14px;">
    Vizitatori site, bounce rate și rata de conversie — o lună sau interval de luni.
  </p>

  <div class="trafic-raport-filters">
    <div class="trafic-raport-mode" role="group" aria-label="Tip perioadă">
      <button type="button" class="active" id="modeSingle" data-mode="single">O lună</button>
      <button type="button" id="modeRange" data-mode="range">Mai multe luni</button>
    </div>

    <div class="trafic-raport-months">
      <div id="singleMonthWrap">
        <label for="raportLuna">Luna</label>
        <select id="raportLuna" class="dashboard-month-select" aria-label="Selectează luna"></select>
      </div>
      <div id="rangeMonthWrap" style="display:none;">
        <label for="raportStart">De la</label>
        <select id="raportStart" class="dashboard-month-select" aria-label="Luna de început"></select>
      </div>
      <div id="rangeMonthEndWrap" style="display:none;">
        <label for="raportEnd">Până la</label>
        <select id="raportEnd" class="dashboard-month-select" aria-label="Luna de sfârșit"></select>
      </div>
    </div>

    <button type="button" class="trafic-raport-generate" id="raportGenerate">
      <i class="fas fa-table" aria-hidden="true"></i> Generează raport
    </button>
  </div>

  <div class="trafic-raport-card" id="raportCard" style="display:none;">
    <h2><i class="fas fa-table" aria-hidden="true"></i>eCommerce Stats</h2>
    <p class="trafic-raport-period" id="raportPeriodLabel"></p>
    <p class="trafic-raport-source" id="raportSourceLabel"></p>
    <div id="raportWarning" class="trafic-raport-warning" style="display:none;"></div>
    <div class="trafic-raport-table-wrap">
      <table class="trafic-raport-table">
        <thead>
          <tr>
            <th>Perioadă</th>
            <th class="num">Vizitatori site</th>
            <th class="num">Bounce rate</th>
            <th class="num">Conversion rate</th>
          </tr>
        </thead>
        <tbody id="raportTableBody"></tbody>
        <tfoot id="raportTableFoot"></tfoot>
      </table>
    </div>
    <div class="trafic-raport-definitions" id="raportDefinitions">
      <h3>Definiții (GA4)</h3>
      <ol>
        <li><strong>Vizitatori site</strong> — numărul de accesări în e-shop (<code>sessions</code>).</li>
        <li><strong>Bounce rate</strong> — procentul sesiunilor neangajate: utilizatori care nu au interacționat semnificativ (&lt;10s, fără conversie, sub 2 pagini) (<code>bounceRate</code>).</li>
        <li><strong>Conversion rate</strong> — procentul sesiunilor în care s-a realizat o acțiune dorită (ex. achiziție, eveniment marcat ca conversie) (<code>sessionConversionRate</code>).</li>
      </ol>
    </div>
  </div>

  <div class="trafic-raport-loading" id="raportLoading" style="display:none;">
    <i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Se încarcă raportul...
  </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  const monthSelects = ['raportLuna', 'raportStart', 'raportEnd'];
  const now = new Date();
  const options = [];

  for (let i = 0; i < 36; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const ym = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0');
    const label = d.toLocaleDateString('ro-RO', { month: 'long', year: 'numeric' });
    options.push({ value: ym, label: label.charAt(0).toUpperCase() + label.slice(1) });
  }

  monthSelects.forEach(function(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = options.map(function(o) {
      return '<option value="' + o.value + '">' + o.label + '</option>';
    }).join('');
  });

  let mode = 'single';
  const modeSingle = document.getElementById('modeSingle');
  const modeRange = document.getElementById('modeRange');
  const singleWrap = document.getElementById('singleMonthWrap');
  const rangeWrap = document.getElementById('rangeMonthWrap');
  const rangeEndWrap = document.getElementById('rangeMonthEndWrap');

  function setMode(next) {
    mode = next;
    modeSingle.classList.toggle('active', mode === 'single');
    modeRange.classList.toggle('active', mode === 'range');
    singleWrap.style.display = mode === 'single' ? '' : 'none';
    rangeWrap.style.display = mode === 'range' ? '' : 'none';
    rangeEndWrap.style.display = mode === 'range' ? '' : 'none';
  }

  modeSingle.addEventListener('click', function() { setMode('single'); });
  modeRange.addEventListener('click', function() { setMode('range'); });

  function formatNumber(val) {
    return new Intl.NumberFormat('ro-RO').format(val || 0);
  }

  function formatPct(val) {
    if (val === null || val === undefined || val === '') return '—';
    return formatNumber(val) + ' %';
  }

  async function loadRaport() {
    const startMonth = mode === 'single'
      ? document.getElementById('raportLuna').value
      : document.getElementById('raportStart').value;
    const endMonth = mode === 'single'
      ? startMonth
      : document.getElementById('raportEnd').value;

    document.getElementById('raportCard').style.display = 'none';
    document.getElementById('raportLoading').style.display = 'block';

    try {
      const url = @json(route('api.trafic.raport'))
        + '?start_month=' + encodeURIComponent(startMonth)
        + '&end_month=' + encodeURIComponent(endMonth);
      const res = await fetch(url);
      const data = await res.json();

      document.getElementById('raportLoading').style.display = 'none';

      if (!data.success) {
        alert(data.error || 'Eroare la încărcarea raportului.');
        return;
      }

      const rows = data.rows || [];
      const tbody = document.getElementById('raportTableBody');
      const tfoot = document.getElementById('raportTableFoot');
      const warning = document.getElementById('raportWarning');

      if (data.ga_warning) {
        warning.textContent = data.ga_warning;
        warning.style.display = 'block';
      } else {
        warning.style.display = 'none';
      }

      if (!rows.length) {
        tbody.innerHTML = '<tr><td colspan="4" class="trafic-raport-empty">Nu există date pentru perioada selectată.</td></tr>';
        tfoot.innerHTML = '';
      } else {
        tbody.innerHTML = rows.map(function(row) {
          return '<tr>'
            + '<td>' + row.luna_label + '</td>'
            + '<td class="num">' + formatNumber(row.vizite_site) + '</td>'
            + '<td class="num">' + formatPct(row.bounce_rate) + '</td>'
            + '<td class="num">' + formatPct(row.conversion_rate) + '</td>'
            + '</tr>';
        }).join('');

        const t = data.totals || {};
        const totalLabel = rows.length > 1 ? 'Total / medie' : 'Total';
        tfoot.innerHTML = '<tr>'
          + '<td>' + totalLabel + '</td>'
          + '<td class="num">' + formatNumber(t.vizite_site) + '</td>'
          + '<td class="num">' + formatPct(t.bounce_rate) + '</td>'
          + '<td class="num">' + formatPct(t.conversion_rate) + '</td>'
          + '</tr>';
      }

      const periodLabel = startMonth === endMonth
        ? (options.find(function(o) { return o.value === startMonth; }) || {}).label || startMonth
        : ((options.find(function(o) { return o.value === startMonth; }) || {}).label || startMonth)
          + ' — '
          + ((options.find(function(o) { return o.value === endMonth; }) || {}).label || endMonth);

      document.getElementById('raportPeriodLabel').textContent = 'Perioadă: ' + periodLabel;

      const sourceLabel = document.getElementById('raportSourceLabel');
      if (data.source === 'ga4') {
        sourceLabel.textContent = 'Sursă date: Google Analytics 4';
      } else {
        sourceLabel.textContent = 'Sursă date: local (sync trafic + comenzi 1C) — configurează GA4 pentru metrici complete';
      }

      document.getElementById('raportCard').style.display = 'block';
    } catch (e) {
      document.getElementById('raportLoading').style.display = 'none';
      console.error(e);
      alert('Eroare la încărcarea raportului.');
    }
  }

  document.getElementById('raportGenerate').addEventListener('click', loadRaport);
  loadRaport();
});
</script>
@endpush
