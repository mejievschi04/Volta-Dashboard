@extends('layouts.app')

@section('title', 'Analiza trafic - VOLTA')
@section('header-title', '')

@push('styles')
<link rel="stylesheet" href="{{ url('css/trafic.css') }}">
<style>
.ta-page {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 0 28px;
}

.ta-layout {
  display: grid;
  grid-template-columns: 320px 1fr;
  gap: 18px;
}

.ta-sidebar {
  padding: 16px;
  position: sticky;
  top: 16px;
  align-self: start;
}

.ta-sidebar-head {
  margin-bottom: 14px;
  padding-bottom: 12px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.ta-title {
  margin: 0 0 6px;
  color: var(--text-primary, #f8fafc);
  font-size: 16px;
  font-weight: 700;
}

.ta-group-title {
  margin: 0 0 8px;
  color: var(--text-tertiary, #94a3b8);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .08em;
  font-weight: 700;
}

.ta-filters {
  display: grid;
  gap: 10px;
  margin-bottom: 14px;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 23, 42, 0.35);
}

.ta-filters .month-selector-modern {
  width: 100%;
  min-width: 0;
}

.ta-filters .month-selector-wrapper {
  width: 100%;
  min-width: 0;
}

.ta-filters .dashboard-month-select {
  width: 100%;
  min-width: 0;
  max-width: 100%;
}

.ta-tabs {
  display: grid;
  gap: 8px;
  padding: 12px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 23, 42, 0.35);
}


.ta-tab {
  width: 100%;
  display: inline-flex;
  align-items: center;
  gap: 10px;
  border-radius: 10px;
  border: 1px solid rgba(255, 238, 0, 0.2);
  background: rgba(255, 238, 0, 0.06);
  color: var(--brand, #ffee00);
  font-size: 13px;
  line-height: 1.3;
  font-weight: 700;
  text-align: left;
  padding: 11px 12px;
  cursor: pointer;
  transition: background .2s ease, border-color .2s ease, color .2s ease;
}

.ta-tab:hover {
  background: rgba(255, 238, 0, 0.14);
  border-color: rgba(255, 238, 0, 0.42);
  color: #fff;
}

.ta-tab.is-active {
  background: linear-gradient(135deg, rgba(255, 238, 0, 0.22) 0%, rgba(255, 238, 0, 0.12) 100%);
  border-color: rgba(255, 238, 0, 0.5);
  color: #fff;
  box-shadow: inset 3px 0 0 rgba(255, 238, 0, 0.8);
}

.ta-content {
  min-width: 0;
  display: grid;
  gap: 14px;
}

.ta-panel {
  padding: 16px;
}

.ta-panel.is-hidden {
  display: none;
}

.ta-panel-head {
  margin-bottom: 12px;
}

.ta-panel-title {
  margin: 0;
  color: var(--text-primary, #f8fafc);
  font-size: 18px;
  font-weight: 700;
  display: inline-flex;
  align-items: center;
  gap: 8px;
}

.ta-panel-title i {
  color: var(--brand, #ffee00);
}

.ta-placeholder {
  text-align: center;
  padding: 52px 18px;
}

.ta-placeholder i {
  display: block;
  color: var(--brand, #ffee00);
  font-size: 40px;
  opacity: .85;
  margin-bottom: 12px;
}

.ta-placeholder p {
  margin: 0;
  color: var(--text-secondary, #94a3b8);
}

.ta-loading {
  text-align: center;
  padding: 34px 16px;
  color: var(--text-secondary, #94a3b8);
}

.ta-loading i {
  color: var(--brand, #ffee00);
  margin-bottom: 10px;
}

.ta-error {
  border-radius: 10px;
  background: rgba(239, 68, 68, 0.14);
  border: 1px solid rgba(239, 68, 68, 0.36);
  color: #fca5a5;
  padding: 12px 14px;
}

.ta-kpi-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(170px, 1fr));
  gap: 10px;
}

.ta-kpi {
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  background: rgba(15, 23, 42, 0.55);
  padding: 12px;
}

.ta-kpi-label {
  margin: 0 0 6px;
  color: var(--text-tertiary, #94a3b8);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .05em;
  font-weight: 700;
}

.ta-kpi-value {
  margin: 0;
  color: #f8fafc;
  font-size: 24px;
  font-weight: 800;
  line-height: 1.1;
}

.ta-table-wrap {
  border: 1px solid var(--border-primary, #334155);
  border-radius: 12px;
  overflow-x: auto;
}

.ta-table {
  width: 100%;
  border-collapse: collapse;
}

.ta-table th {
  background: var(--bg-secondary, #1e293b);
  color: var(--text-secondary, #cbd5e1);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: .05em;
  text-align: left;
  padding: 12px 14px;
  border-bottom: 1px solid var(--border-primary, #334155);
}

.ta-table td {
  color: var(--text-primary, #e2e8f0);
  padding: 12px 14px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.ta-table tbody tr:hover td {
  background: rgba(255, 255, 255, 0.03);
}

@media (max-width: 1000px) {
  .ta-layout {
    grid-template-columns: 1fr;
  }
  .ta-sidebar {
    position: static;
  }
}
</style>
@endpush

@section('content')
<div class="ta-page">
  <a href="{{ route('trafic') }}" class="trafic-back-link">
    <i class="fas fa-arrow-left" aria-hidden="true"></i>
    <span>Inapoi la Trafic</span>
  </a>

  <div class="ta-layout">
    <aside class="trafic-card ta-sidebar">
      <div class="ta-sidebar-head">
        <h2 class="ta-title">Meniu analiza</h2>
      </div>

      <p class="ta-group-title">Perioada</p>
      <div class="ta-filters">
        <div class="month-selector-modern">
          <div class="month-selector-wrapper">
            <i class="fas fa-calendar" aria-hidden="true"></i>
            <label for="taYear">An</label>
            <select id="taYear" class="dashboard-month-select">
              @for($year = date('Y'); $year >= 2020; $year--)
              <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
              @endfor
            </select>
          </div>
        </div>
        <div class="month-selector-modern">
          <div class="month-selector-wrapper">
            <i class="fas fa-calendar-alt" aria-hidden="true"></i>
            <label for="taMonth">Luna</label>
            <select id="taMonth" class="dashboard-month-select">
              <option value="all" selected>An intreg</option>
              @for($month = 1; $month <= 12; $month++)
              <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                {{ \Carbon\Carbon::create()->month($month)->locale('ro')->monthName }}
              </option>
              @endfor
            </select>
          </div>
        </div>
      </div>

      <p class="ta-group-title">Sectiuni</p>
      <div class="ta-tabs">
        <button type="button" class="ta-tab" data-section="users"><i class="fas fa-users" aria-hidden="true"></i>Utilizatori</button>
        <button type="button" class="ta-tab" data-section="devices"><i class="fas fa-mobile-alt" aria-hidden="true"></i>Dispozitive</button>
        <button type="button" class="ta-tab" data-section="geo"><i class="fas fa-globe" aria-hidden="true"></i>Geografie</button>
        <button type="button" class="ta-tab" data-section="content"><i class="fas fa-file-alt" aria-hidden="true"></i>Continut</button>
        <button type="button" class="ta-tab" data-section="ecommerce"><i class="fas fa-shopping-cart" aria-hidden="true"></i>E-commerce</button>
        <button type="button" class="ta-tab" data-section="campaigns"><i class="fas fa-bullhorn" aria-hidden="true"></i>Campanii</button>
      </div>
    </aside>

    <section class="ta-content">
      <div id="taEmpty" class="trafic-card ta-placeholder">
        <i class="fas fa-mouse-pointer" aria-hidden="true"></i>
        <p>Selecteaza o sectiune din stanga pentru a incarca analiza.</p>
      </div>

      <article id="ta-users" class="trafic-card ta-panel is-hidden" data-section="users">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-users" aria-hidden="true"></i>Utilizatori</h3>
        </header>
        <div class="ta-body" id="ta-users-body"></div>
      </article>

      <article id="ta-devices" class="trafic-card ta-panel is-hidden" data-section="devices">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-mobile-alt" aria-hidden="true"></i>Dispozitive</h3>
        </header>
        <div class="ta-body" id="ta-devices-body"></div>
      </article>

      <article id="ta-geo" class="trafic-card ta-panel is-hidden" data-section="geo">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-globe" aria-hidden="true"></i>Geografie</h3>
        </header>
        <div class="ta-body" id="ta-geo-body"></div>
      </article>

      <article id="ta-content" class="trafic-card ta-panel is-hidden" data-section="content">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-file-alt" aria-hidden="true"></i>Continut</h3>
        </header>
        <div class="ta-body" id="ta-content-body"></div>
      </article>

      <article id="ta-ecommerce" class="trafic-card ta-panel is-hidden" data-section="ecommerce">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-shopping-cart" aria-hidden="true"></i>E-commerce</h3>
        </header>
        <div class="ta-body" id="ta-ecommerce-body"></div>
      </article>

      <article id="ta-campaigns" class="trafic-card ta-panel is-hidden" data-section="campaigns">
        <header class="ta-panel-head">
          <h3 class="ta-panel-title"><i class="fas fa-bullhorn" aria-hidden="true"></i>Campanii</h3>
        </header>
        <div class="ta-body" id="ta-campaigns-body"></div>
      </article>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const routes = {
    users: @json(route('api.ga.users')),
    devices: @json(route('api.ga.devices')),
    geo: @json(route('api.ga.geo')),
    content: @json(route('api.ga.content')),
    ecommerce: @json(route('api.ga.ecommerce')),
    campaigns: @json(route('api.ga.campaigns')),
  };

  const tabs = Array.from(document.querySelectorAll('.ta-tab'));
  const sections = {
    users: document.getElementById('ta-users'),
    devices: document.getElementById('ta-devices'),
    geo: document.getElementById('ta-geo'),
    content: document.getElementById('ta-content'),
    ecommerce: document.getElementById('ta-ecommerce'),
    campaigns: document.getElementById('ta-campaigns'),
  };
  const bodies = {
    users: document.getElementById('ta-users-body'),
    devices: document.getElementById('ta-devices-body'),
    geo: document.getElementById('ta-geo-body'),
    content: document.getElementById('ta-content-body'),
    ecommerce: document.getElementById('ta-ecommerce-body'),
    campaigns: document.getElementById('ta-campaigns-body'),
  };

  const emptyState = document.getElementById('taEmpty');
  const yearSelect = document.getElementById('taYear');
  const monthSelect = document.getElementById('taMonth');

  let active = null;
  let abortController = null;
  const cache = new Map();

  function fmt(value) {
    return new Intl.NumberFormat('ro-RO').format(Number(value) || 0);
  }

  function fmtDuration(seconds) {
    const s = Number(seconds) || 0;
    if (!s) return '0s';
    const m = Math.floor(s / 60);
    const r = Math.floor(s % 60);
    return m > 0 ? m + 'm ' + r + 's' : r + 's';
  }

  function fmtPct(value) {
    return (Number(value) || 0).toFixed(2) + '%';
  }

  function range() {
    const year = yearSelect.value;
    const month = monthSelect.value;
    if (month === 'all') {
      return { start: year + '-01-01', end: year + '-12-31' };
    }
    const lastDay = new Date(Number(year), Number(month), 0).getDate();
    return {
      start: year + '-' + month + '-01',
      end: year + '-' + month + '-' + String(lastDay).padStart(2, '0'),
    };
  }

  function showLoading(section) {
    bodies[section].innerHTML =
      '<div class="ta-loading"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i><p>Se incarca datele...</p></div>';
  }

  function showError(section, message) {
    bodies[section].innerHTML = '<div class="ta-error">' + message + '</div>';
  }

  function renderTable(headers, rows) {
    if (!rows.length) {
      return '<div class="ta-error">Nu exista date pentru perioada selectata.</div>';
    }
    const thead = headers.map(function (h) { return '<th>' + h + '</th>'; }).join('');
    const tbody = rows.map(function (row) {
      return '<tr>' + row.map(function (cell) { return '<td>' + cell + '</td>'; }).join('') + '</tr>';
    }).join('');
    return '<div class="ta-table-wrap"><table class="ta-table"><thead><tr>' + thead + '</tr></thead><tbody>' + tbody + '</tbody></table></div>';
  }

  function renderKpi(items) {
    return '<div class="ta-kpi-grid">' + items.map(function (item) {
      return '<div class="ta-kpi"><p class="ta-kpi-label">' + item.label + '</p><p class="ta-kpi-value">' + item.value + '</p></div>';
    }).join('') + '</div>';
  }

  const renderers = {
    users: function (result) {
      const rows = (result.data && result.data.rows) ? result.data.rows : [];
      if (!rows.length) return '<div class="ta-error">Nu exista date pentru utilizatori.</div>';
      const totals = rows.reduce(function (acc, row) {
        acc.active += Number(row.metricValues[0] && row.metricValues[0].value || 0);
        acc.newUsers += Number(row.metricValues[1] && row.metricValues[1].value || 0);
        acc.sessions += Number(row.metricValues[2] && row.metricValues[2].value || 0);
        acc.duration += Number(row.metricValues[3] && row.metricValues[3].value || 0);
        acc.bounce += Number(row.metricValues[4] && row.metricValues[4].value || 0);
        acc.engagement += Number(row.metricValues[5] && row.metricValues[5].value || 0);
        return acc;
      }, { active: 0, newUsers: 0, sessions: 0, duration: 0, bounce: 0, engagement: 0 });
      const cnt = rows.length || 1;
      const returning = Math.max(0, totals.active - totals.newUsers);
      return renderKpi([
        { label: 'Utilizatori activi', value: fmt(totals.active) },
        { label: 'Utilizatori noi', value: fmt(totals.newUsers) },
        { label: 'Utilizatori fideli', value: fmt(returning) },
        { label: 'Sesiuni', value: fmt(totals.sessions) },
        { label: 'Durata medie', value: fmtDuration(totals.duration / cnt) },
        { label: 'Rata respingere', value: fmtPct(totals.bounce / cnt) },
      ]);
    },
    devices: function (result) {
      const rows = ((result.data && result.data.rows) ? result.data.rows : []).slice(0, 25).map(function (row) {
        return [
          row.dimensionValues[0] ? row.dimensionValues[0].value : '-',
          row.dimensionValues[1] ? row.dimensionValues[1].value : '-',
          row.dimensionValues[2] ? row.dimensionValues[2].value : '-',
          fmt(row.metricValues[0] ? row.metricValues[0].value : 0),
          fmt(row.metricValues[1] ? row.metricValues[1].value : 0),
        ];
      });
      return renderTable(['Dispozitiv', 'OS', 'Browser', 'Sesiuni', 'Utilizatori'], rows);
    },
    geo: function (result) {
      const rows = ((result.data && result.data.rows) ? result.data.rows : []).slice(0, 25).map(function (row) {
        return [
          row.dimensionValues[0] ? row.dimensionValues[0].value : '-',
          row.dimensionValues[1] ? row.dimensionValues[1].value : '-',
          fmt(row.metricValues[0] ? row.metricValues[0].value : 0),
          fmt(row.metricValues[1] ? row.metricValues[1].value : 0),
        ];
      });
      return renderTable(['Tara', 'Oras', 'Sesiuni', 'Utilizatori'], rows);
    },
    content: function (result) {
      const rows = ((result.data && result.data.rows) ? result.data.rows : []).slice(0, 25).map(function (row) {
        const path = row.dimensionValues[0] ? row.dimensionValues[0].value : '-';
        const title = row.dimensionValues[1] ? row.dimensionValues[1].value : '-';
        return [
          path.length > 65 ? path.slice(0, 62) + '...' : path,
          title.length > 65 ? title.slice(0, 62) + '...' : title,
          fmt(row.metricValues[0] ? row.metricValues[0].value : 0),
          fmtDuration(row.metricValues[1] ? row.metricValues[1].value : 0),
        ];
      });
      return renderTable(['Pagina', 'Titlu', 'Vizualizari', 'Durata medie'], rows);
    },
    ecommerce: function (result) {
      const rows = (result.data && result.data.rows) ? result.data.rows : [];
      if (!rows.length) return '<div class="ta-error">Nu exista date de e-commerce pentru perioada selectata.</div>';
      const totals = rows.reduce(function (acc, row) {
        acc.revenue += Number(row.metricValues[0] && row.metricValues[0].value || 0);
        acc.buyers += Number(row.metricValues[1] && row.metricValues[1].value || 0);
        acc.transactions += Number(row.metricValues[2] && row.metricValues[2].value || 0);
        acc.items += Number(row.metricValues[3] && row.metricValues[3].value || 0);
        return acc;
      }, { revenue: 0, buyers: 0, transactions: 0, items: 0 });
      return renderKpi([
        { label: 'Venit total', value: fmt(totals.revenue.toFixed(2)) },
        { label: 'Cumparatori', value: fmt(totals.buyers) },
        { label: 'Tranzactii', value: fmt(totals.transactions) },
        { label: 'Articole', value: fmt(totals.items) },
      ]);
    },
    campaigns: function (result) {
      const rows = ((result.data && result.data.rows) ? result.data.rows : []).slice(0, 25).map(function (row) {
        return [
          row.dimensionValues[0] ? row.dimensionValues[0].value : '-',
          row.dimensionValues[1] ? row.dimensionValues[1].value : '-',
          fmt(row.metricValues[0] ? row.metricValues[0].value : 0),
          fmt(row.metricValues[1] ? row.metricValues[1].value : 0),
        ];
      });
      return renderTable(['Campanie', 'Sursa / Mediu', 'Sesiuni', 'Utilizatori'], rows);
    }
  };

  function setActive(section) {
    active = section;
    tabs.forEach(function (tab) {
      tab.classList.toggle('is-active', tab.dataset.section === section);
    });
    Object.keys(sections).forEach(function (key) {
      sections[key].classList.toggle('is-hidden', key !== section);
    });
    emptyState.style.display = 'none';
  }

  async function fetchSection(section) {
    setActive(section);
    showLoading(section);

    const selectedRange = range();
    const key = section + ':' + selectedRange.start + ':' + selectedRange.end;
    if (cache.has(key)) {
      bodies[section].innerHTML = renderers[section](cache.get(key));
      return;
    }

    if (abortController) {
      abortController.abort();
    }
    abortController = new AbortController();

    const url = routes[section] + '?start_date=' + encodeURIComponent(selectedRange.start) + '&end_date=' + encodeURIComponent(selectedRange.end);
    try {
      const response = await fetch(url, {
        signal: abortController.signal,
        headers: { 'Accept': 'application/json' }
      });
      const result = await response.json();
      if (!response.ok || !result.success) {
        throw new Error((result && (result.error || result.message)) ? (result.error || result.message) : 'Eroare la incarcare.');
      }
      cache.set(key, result);
      bodies[section].innerHTML = renderers[section](result);
    } catch (error) {
      if (error.name === 'AbortError') return;
      showError(section, 'Eroare: ' + error.message);
    }
  }

  tabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      fetchSection(tab.dataset.section);
    });
  });

  function refetchActive() {
    if (!active) return;
    fetchSection(active);
  }


  yearSelect.addEventListener('change', refetchActive);
  monthSelect.addEventListener('change', refetchActive);
})();
</script>
@endpush
