@extends('layouts.app')

@section('title', 'Hartă livrări - VOLTA')
@section('header-title', 'Hartă livrări')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css">
<style>
  .livrari-map-page {
    display: grid;
    gap: 16px;
    padding: 20px;
  }

  .livrari-map-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .livrari-map-title {
    margin: 0;
    color: #F8FAFC;
    font-family: 'Space Grotesk', sans-serif;
    font-size: 1.6rem;
  }

  .livrari-map-subtitle {
    color: #94A3B8;
    font-size: 0.9rem;
  }

  .livrari-map-actions {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
  }

  .livrari-map-btn,
  .livrari-map-select {
    min-height: 42px;
    border-radius: 10px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(9, 17, 35, 0.84);
    color: #F8FAFC;
    font-weight: 800;
  }

  .livrari-map-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 0 14px;
    text-decoration: none;
    cursor: pointer;
  }

  .livrari-map-btn-primary {
    background: #FFEE00;
    color: #111827;
    border-color: #FFEE00;
  }
  .livrari-map-btn-primary:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255, 238, 0, 0.4);
  }

  .livrari-map-select {
    min-width: 220px;
    padding: 0 12px;
  }

  .livrari-map-select option {
    color: #F8FAFC;
    background: #1a1d26;
  }

  .livrari-map-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 320px;
    gap: 16px;
    min-height: calc(100vh - 190px);
  }

  .livrari-map-canvas {
    min-height: calc(100vh - 190px);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    overflow: hidden;
    background:
      radial-gradient(circle at 22% 24%, rgba(255, 238, 0, 0.06), transparent 24%),
      linear-gradient(135deg, #060d1f 0%, #0f1a33 54%, #14213d 100%);
  }

  .livrari-map-canvas .leaflet-container,
  .livrari-map-canvas.leaflet-container {
    background: transparent;
    font-family: 'Noto Sans', system-ui, sans-serif;
  }

  .leaflet-tooltip.livrari-map-raion-label {
    background: rgba(2, 6, 23, 0.9);
    border: 1px solid rgba(255, 238, 0, 0.72);
    border-radius: 999px;
    color: #FFEE00;
    font-size: 0.76rem;
    font-weight: 900;
    letter-spacing: 0.01em;
    box-shadow: 0 8px 22px rgba(0, 0, 0, 0.42), 0 0 0 1px rgba(255, 238, 0, 0.16);
    padding: 4px 10px;
  }

  .leaflet-tooltip.livrari-map-raion-label:before {
    display: none;
  }

  .livrari-map-side {
    display: grid;
    align-content: start;
    gap: 12px;
    min-height: calc(100vh - 190px);
    max-height: calc(100vh - 190px);
    overflow-y: auto;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 14px;
    background: rgba(9, 17, 35, 0.82);
    padding: 14px;
  }

  .livrari-map-total,
  .livrari-map-detail {
    border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    padding-bottom: 12px;
  }

  .livrari-map-total strong {
    display: block;
    color: #fff;
    font-size: 2rem;
    line-height: 1;
  }

  .livrari-map-total span,
  .livrari-map-detail-period,
  .livrari-map-row-sub {
    color: #94A3B8;
    font-size: 0.82rem;
  }

  .livrari-map-detail-title,
  .livrari-map-row-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    color: #F8FAFC;
    font-weight: 900;
  }

  .livrari-map-count {
    flex-shrink: 0;
    min-width: 30px;
    border-radius: 999px;
    padding: 3px 9px;
    background: #FFEE00;
    color: #111827;
    text-align: center;
    font-size: 0.75rem;
  }

  .livrari-map-detail-localitati {
    margin: 10px 0 0;
    padding-left: 18px;
    color: #CBD5E1;
    font-size: 0.84rem;
    line-height: 1.45;
  }

  .livrari-map-list {
    display: grid;
    gap: 8px;
  }

  .livrari-map-list-title {
    color: #F8FAFC;
    font-weight: 900;
    font-size: 0.9rem;
    padding: 2px 2px 6px;
  }

  .livrari-map-loc-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 9px 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(20, 31, 55, 0.72);
    color: #E5E7EB;
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.05);
  }

  .livrari-map-loc-name {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
    font-weight: 700;
  }

  .livrari-map-loc-rank {
    color: #94A3B8;
    font-size: 0.78rem;
    min-width: 20px;
  }

  .livrari-map-loc-text {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }

  .livrari-map-row {
    display: grid;
    gap: 4px;
    width: 100%;
    padding: 10px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 10px;
    background: rgba(20, 31, 55, 0.72);
    color: #E5E7EB;
    text-align: left;
    cursor: pointer;
  }

  .livrari-map-row:hover,
  .livrari-map-row.is-active {
    border-color: rgba(255, 238, 0, 0.34);
    background: rgba(255, 238, 0, 0.09);
  }

  .livrari-map-empty {
    color: #94A3B8;
    padding: 12px 4px;
  }

  .livrari-map-legend {
    display: grid;
    gap: 8px;
    color: #CBD5E1;
    font-size: 0.82rem;
  }

  .livrari-map-legend-scale {
    height: 12px;
    width: min(460px, 100%);
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    background: linear-gradient(90deg, #000000 0%, #FFEE00 100%);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
  }

  .livrari-map-legend-labels {
    width: min(460px, 100%);
    display: flex;
    justify-content: space-between;
    gap: 10px;
    font-weight: 700;
  }

  @media (max-width: 1020px) {
    .livrari-map-shell { grid-template-columns: 1fr; }
    .livrari-map-canvas { min-height: 560px; }
    .livrari-map-side {
      min-height: auto;
      max-height: none;
    }
  }

  @media (max-width: 640px) {
    .livrari-map-page { padding: 14px; }
    .livrari-map-actions,
    .livrari-map-btn,
    .livrari-map-select { width: 100%; }
    .livrari-map-btn { justify-content: center; }
    .livrari-map-canvas { min-height: 460px; }
  }

  /* Keep map controls/panels on neutral dark palette */
  .livrari-map-btn { background: rgba(20, 22, 30, 0.88); color: #F8FAFC; }
  .livrari-map-select { background: rgba(20, 22, 30, 0.88); }
  .livrari-map-btn-primary { background: #FFEE00 !important; color: #111827 !important; border-color: #FFEE00 !important; }
  .livrari-map-side { background: rgba(18, 20, 28, 0.84); }
  .livrari-map-loc-row,
  .livrari-map-row { background: rgba(30, 34, 44, 0.78); }
</style>
@endpush

@section('content')
<div class="livrari-map-page">
  <div class="livrari-map-head">
    <div>
      <h1 class="livrari-map-title"><i class="fas fa-map-location-dot"></i> Hartă live livrări</h1>
      <div class="livrari-map-subtitle" id="livrariMapUpdated">Se încarcă datele live...</div>
    </div>
    <div class="livrari-map-actions">
      <select id="livrariMapRaionSelect" class="livrari-map-select" aria-label="Selectează raion">
        <option value="">Selectează raion</option>
      </select>
      <button type="button" class="livrari-map-btn livrari-map-btn-primary" id="livrariMapRefreshBtn">
        <i class="fas fa-rotate" aria-hidden="true"></i> Actualizează
      </button>
      <a href="{{ $backUrl }}" class="livrari-map-btn">
        <i class="fas fa-arrow-left" aria-hidden="true"></i> Înapoi
      </a>
    </div>
  </div>

  <div class="livrari-map-legend" aria-label="Legendă volum comenzi">
    <div>Intensitate comenzi (negru → galben aprins)</div>
    <div class="livrari-map-legend-scale" aria-hidden="true"></div>
    <div class="livrari-map-legend-labels">
      <span>0 comenzi</span>
      <span id="livrariMapLegendMax">max comenzi</span>
    </div>
  </div>

  <div class="livrari-map-shell">
    <div class="livrari-map-canvas" id="livrariLiveMap"></div>
    <aside class="livrari-map-side">
      <div class="livrari-map-total">
        <strong id="livrariMapTotal">0</strong>
        <span>livrări în selecția curentă</span>
      </div>

      <div class="livrari-map-detail" id="livrariMapDetail">
        <div class="livrari-map-detail-title">
          <span>Selectează un raion</span>
          <span class="livrari-map-count">0</span>
        </div>
        <div class="livrari-map-detail-period">Perioada: -</div>
        <ol class="livrari-map-detail-localitati">
          <li>Localitățile apar mai jos, în ordine descrescătoare.</li>
        </ol>
      </div>

      <div class="livrari-map-list" id="livrariMapList">
        <div class="livrari-map-empty">Se încarcă lista raioanelor...</div>
      </div>
    </aside>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const refreshBtn = document.getElementById('livrariMapRefreshBtn');
  const totalEl = document.getElementById('livrariMapTotal');
  const listEl = document.getElementById('livrariMapList');
  const updatedEl = document.getElementById('livrariMapUpdated');
  const mapEl = document.getElementById('livrariLiveMap');
  const raionSelect = document.getElementById('livrariMapRaionSelect');
  const detailEl = document.getElementById('livrariMapDetail');
  const legendMaxEl = document.getElementById('livrariMapLegendMax');
  const mapDataUrl = @json(route('livrari.map-data'));
  const mapGeoJsonUrl = @json(asset('data/moldova-adm1.geojson'));
  let map = null;
  let regionLayer = null;
  let layersByRaion = {};
  let geoJsonData = null;
  let lastPayload = null;
  let selectedRaion = '';
  let selectedRaionLabelLayer = null;
  let loading = false;
  const featureRaionAliases = {
    balti: 'Bălți',
    calarasi: 'Călărași',
    causeni: 'Căușeni',
    chisinau: 'Chișinău',
    cimislia: 'Cimișlia',
    donduseni: 'Dondușeni',
    dubasari: 'Dubăsari',
    edinet: 'Edineț',
    falesti: 'Fălești',
    floresti: 'Florești',
    gagauzia: 'UTA Găgăuzia',
    hincesti: 'Hîncești',
    ocnita: 'Ocnița',
    riscani: 'Rîșcani',
    singerei: 'Sîngerei',
    soldanesti: 'Șoldănești',
    stefanvoda: 'Ștefan Vodă',
    straseni: 'Strășeni',
    telenesti: 'Telenești'
  };
  const hiddenFeatureRaions = { transnistria: true };

  function normalizeRaion(value) {
    return String(value || '')
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .replace(/[^a-z0-9]+/g, '');
  }

  function filteredMapUrl() {
    const url = new URL(mapDataUrl, window.location.origin);
    const params = new URLSearchParams(window.location.search);
    params.delete('page');
    params.forEach(function (value, key) {
      url.searchParams.append(key, value);
    });
    return url.toString();
  }

  function initMap() {
    if (map || !mapEl || !window.L) return;
    map = L.map(mapEl, {
      scrollWheelZoom: true,
      zoomControl: true,
      attributionControl: false,
      maxBoundsViscosity: 1
    }).setView([47.05, 28.55], 7);
  }

  function canonicalFeatureRaion(value) {
    const key = normalizeRaion(value);
    return featureRaionAliases[key] || String(value || '').trim();
  }

  function featureName(feature) {
    const props = feature && feature.properties ? feature.properties : {};
    return props.shapeName || props.name || props.NAME_1 || '';
  }

  function ratioForTotal(total, maxTotal) {
    const safeTotal = Math.max(0, Number(total) || 0);
    const safeMax = Math.max(1, Number(maxTotal) || 0);
    if (safeTotal <= 0) return 0;
    const logRatio = Math.log1p(safeTotal) / Math.log1p(safeMax);
    const boosted = Math.pow(Math.min(1, logRatio), 0.72);
    return Math.max(0.18, boosted);
  }

  function colorForTotal(total, maxTotal) {
    const ratio = ratioForTotal(total, maxTotal);
    const yellow = { r: 255, g: 238, b: 0 };
    const r = Math.round(yellow.r * ratio);
    const g = Math.round(yellow.g * ratio);
    const b = Math.round(yellow.b * ratio);
    return 'rgb(' + r + ', ' + g + ', ' + b + ')';
  }

  function itemsByRaion() {
    const items = lastPayload && lastPayload.raioane ? lastPayload.raioane : [];
    return items.reduce(function (acc, item) {
      acc[item.raion] = item;
      return acc;
    }, {});
  }

  function itemsByNormalizedRaion(items) {
    return (items || []).reduce(function (acc, item) {
      acc[normalizeRaion(item.raion)] = item;
      return acc;
    }, {});
  }

  function itemForFeature(feature, indexedItems) {
    const rawName = featureName(feature);
    const canonicalName = canonicalFeatureRaion(rawName);
    const item = indexedItems[normalizeRaion(canonicalName)] || indexedItems[normalizeRaion(rawName)];

    return item || {
      raion: canonicalName,
      total: 0,
      localitati: []
    };
  }

  function sortedLocalitati(localitati) {
    const grouped = {};
    (localitati || []).forEach(function (row) {
      const rawName = String(row && row.localitate ? row.localitate : '').trim() || 'Fără nume';
      const key = String(rawName)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '');
      const total = Number(row && row.total ? row.total : 0) || 0;
      if (!grouped[key]) {
        grouped[key] = { localitate: rawName, total: 0 };
      }
      grouped[key].total += total;
      if (key === 'chisinau') {
        grouped[key].localitate = 'Chișinău';
      }
    });

    return Object.keys(grouped).map(function (key) { return grouped[key]; }).sort(function (a, b) {
      const diff = (Number(b.total) || 0) - (Number(a.total) || 0);
      if (diff !== 0) return diff;
      return String(a.localitate || '').localeCompare(String(b.localitate || ''), 'ro');
    });
  }

  function renderRaionSelect(items) {
    const previous = selectedRaion || raionSelect.value || '';
    raionSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Selectează raion';
    raionSelect.appendChild(placeholder);

    items.slice().sort(function (a, b) {
      return a.raion.localeCompare(b.raion, 'ro');
    }).forEach(function (item) {
      const option = document.createElement('option');
      option.value = item.raion;
      option.textContent = item.raion + ' (' + item.total + ')';
      raionSelect.appendChild(option);
    });

    if (previous && items.some(function (item) { return item.raion === previous; })) {
      raionSelect.value = previous;
      selectedRaion = previous;
    } else {
      raionSelect.value = '';
      selectedRaion = '';
    }
  }

  function renderDetail(item) {
    const title = detailEl.querySelector('.livrari-map-detail-title span:first-child');
    const count = detailEl.querySelector('.livrari-map-count');
    const period = detailEl.querySelector('.livrari-map-detail-period');
    const localitati = detailEl.querySelector('.livrari-map-detail-localitati');
    const periodLabel = lastPayload && lastPayload.period_label ? lastPayload.period_label : '-';

    if (!item) {
      title.textContent = 'Selectează un raion';
      count.textContent = '0';
      period.textContent = 'Perioada: ' + periodLabel;
      localitati.innerHTML = '<li>Alege un raion din hartă sau din selector.</li>';
      return;
    }

    title.textContent = item.raion;
    count.textContent = item.total;
    const maxTotal = lastPayload ? lastPayload.max_total : 0;
    const ratio = ratioForTotal(item.total, maxTotal);
    count.style.background = colorForTotal(item.total, maxTotal);
    count.style.color = ratio >= 0.62 ? '#0f172a' : '#f8fafc';
    period.textContent = 'Perioada: ' + periodLabel;
    const rows = sortedLocalitati(item.localitati);
    localitati.innerHTML = rows.length
      ? '<li>Localități active: <strong>' + rows.length + '</strong></li>'
      : '<li>Nu sunt localități active în perioada selectată.</li>';
  }

  function regionStyle(item, isSelected) {
    const total = item ? item.total || 0 : 0;
    const maxTotal = lastPayload ? lastPayload.max_total || 0 : 0;

    return {
      color: isSelected ? '#F8FAFC' : '#CBD5E1',
      weight: isSelected ? 3.6 : 1.2,
      opacity: isSelected ? 1 : 0.9,
      fillColor: colorForTotal(total, maxTotal),
      fillOpacity: isSelected ? (total ? 0.9 : 0.34) : (total ? 0.76 : 0.24)
    };
  }

  function applySelectionVisualEffect(layer, isSelected) {
    const element = layer && layer.getElement ? layer.getElement() : null;
    if (!element) return;
    element.style.cursor = 'pointer';
    element.style.transition = 'filter 180ms ease, transform 180ms ease, fill-opacity 180ms ease, stroke-width 180ms ease';
    element.style.transformOrigin = 'center';
    if (isSelected) {
      element.style.filter = 'drop-shadow(0 8px 10px rgba(0, 0, 0, 0.35)) drop-shadow(0 0 0.5px rgba(255, 255, 255, 0.6))';
      element.style.transform = 'translateY(-1px) scale(1.012)';
    } else {
      element.style.filter = 'none';
      element.style.transform = 'none';
    }
  }

  function refreshSelectedRaionLabel() {
    if (selectedRaionLabelLayer) {
      selectedRaionLabelLayer.closeTooltip();
      selectedRaionLabelLayer.unbindTooltip();
      selectedRaionLabelLayer = null;
    }

    if (!selectedRaion) return;

    const layer = layersByRaion[selectedRaion];
    if (!layer || !layer._livrariItem) return;

    const total = Number(layer._livrariItem.total) || 0;
    layer.bindTooltip(total + ' livrări', {
      permanent: true,
      direction: 'center',
      className: 'livrari-map-raion-label',
      interactive: false,
      opacity: 1
    });
    layer.openTooltip();
    if (layer.bringToFront) layer.bringToFront();
    selectedRaionLabelLayer = layer;
  }

  function refreshRegionStyles() {
    if (!regionLayer) return;
    regionLayer.eachLayer(function (layer) {
      const isSelected = layer._livrariRaion === selectedRaion;
      layer.setStyle(regionStyle(layer._livrariItem, isSelected));
      applySelectionVisualEffect(layer, isSelected);
    });
    refreshSelectedRaionLabel();
  }

  function selectRaion(raion, focusMap) {
    selectedRaion = raion || '';
    raionSelect.value = selectedRaion;
    renderDetail(itemsByRaion()[selectedRaion] || null);
    refreshRegionStyles();
    renderList(lastPayload && lastPayload.raioane ? lastPayload.raioane : []);

    const layer = layersByRaion[selectedRaion];
    if (focusMap && layer && map) {
      map.fitBounds(layer.getBounds(), { padding: [48, 48], maxZoom: 10 });
    }
  }

  function renderList(items) {
    listEl.innerHTML = '';

    if (!selectedRaion) {
      const emptySelection = document.createElement('div');
      emptySelection.className = 'livrari-map-empty';
      emptySelection.textContent = 'Selectează un raion din hartă sau din listă.';
      listEl.appendChild(emptySelection);
      return;
    }

    const selectedItems = (items || []).filter(function (item) {
      return item.raion === selectedRaion;
    });

    if (!selectedItems.length) {
      const empty = document.createElement('div');
      empty.className = 'livrari-map-empty';
      empty.textContent = 'Nu există date pentru raionul selectat.';
      listEl.appendChild(empty);
      return;
    }

    const item = selectedItems[0];
    const rows = sortedLocalitati(item.localitati);
    const title = document.createElement('div');
    title.className = 'livrari-map-list-title';
    title.textContent = 'Localități (descrescător după livrări)';
    listEl.appendChild(title);

    if (!rows.length) {
      const emptyLocalitati = document.createElement('div');
      emptyLocalitati.className = 'livrari-map-empty';
      emptyLocalitati.textContent = 'Nu sunt localități pentru raionul selectat.';
      listEl.appendChild(emptyLocalitati);
      return;
    }

    rows.forEach(function (row, index) {
      const entry = document.createElement('div');
      entry.className = 'livrari-map-loc-row';

      const name = document.createElement('span');
      name.className = 'livrari-map-loc-name';
      name.innerHTML = '<span class="livrari-map-loc-rank">#' + (index + 1) + '</span><span class="livrari-map-loc-text"></span>';
      name.querySelector('.livrari-map-loc-text').textContent = row.localitate || 'Fără nume';

      const count = document.createElement('span');
      count.className = 'livrari-map-count';
      count.textContent = row.total || 0;
      const maxTotal = lastPayload ? lastPayload.max_total : 0;
      const ratio = ratioForTotal(row.total, maxTotal);
      count.style.background = colorForTotal(row.total, maxTotal);
      count.style.color = ratio >= 0.62 ? '#0f172a' : '#f8fafc';

      entry.appendChild(name);
      entry.appendChild(count);
      listEl.appendChild(entry);
    });
  }

  function renderMap(payload, geoJson) {
    initMap();
    lastPayload = payload || {};
    totalEl.textContent = payload.total || 0;
    updatedEl.textContent = 'Perioada: ' + (payload.period_label || '-') + ' | Actualizat: ' + (payload.generated_at || '');
    if (legendMaxEl) {
      const maxTotal = Math.max(0, Number(payload.max_total) || 0);
      legendMaxEl.textContent = maxTotal + ' comenzi';
    }

    const items = payload.raioane || [];
    const indexedItems = itemsByNormalizedRaion(items);
    renderRaionSelect(items);
    renderList(items);
    layersByRaion = {};

    if (regionLayer) {
      map.removeLayer(regionLayer);
      regionLayer = null;
    }

    regionLayer = L.geoJSON(geoJson, {
      filter: function (feature) {
        return !hiddenFeatureRaions[normalizeRaion(featureName(feature))];
      },
      style: function (feature) {
        return regionStyle(itemForFeature(feature, indexedItems), false);
      },
      onEachFeature: function (feature, layer) {
        const item = itemForFeature(feature, indexedItems);
        layer._livrariItem = item;
        layer._livrariRaion = item.raion;
        layersByRaion[item.raion] = layer;

        layer.on({
          click: function () {
            selectRaion(item.raion, false);
          },
          mouseover: function () {
            layer.setStyle({ weight: 3, opacity: 1, fillOpacity: item.total ? 0.88 : 0.34 });
            if (layer.bringToFront) layer.bringToFront();
          },
          mouseout: function () {
            refreshRegionStyles();
          }
        });
      }
    }).addTo(map);

    regionLayer.eachLayer(function (layer) {
      applySelectionVisualEffect(layer, layer._livrariRaion === selectedRaion);
    });

    const bounds = regionLayer.getBounds();
    if (bounds.isValid && bounds.isValid()) {
      map.fitBounds(bounds, { padding: [28, 28], maxZoom: 8 });
      map.setMaxBounds(bounds.pad(0.06));
    }

    window.setTimeout(function () {
      map.invalidateSize();
    }, 80);

    if (selectedRaion && itemsByRaion()[selectedRaion]) {
      selectRaion(selectedRaion, false);
    } else {
      renderDetail(null);
      refreshRegionStyles();
      renderList(items);
    }
  }

  function loadMapData() {
    if (loading) return;
    loading = true;
    refreshBtn.disabled = true;
    updatedEl.textContent = 'Se încarcă datele live...';

    const dataRequest = fetch(filteredMapUrl(), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (response) {
      if (!response.ok) throw new Error('Nu am putut citi datele pentru hartă.');
      return response.json();
    });

    const geoJsonRequest = geoJsonData
      ? Promise.resolve(geoJsonData)
      : fetch(mapGeoJsonUrl, { headers: { 'Accept': 'application/geo+json, application/json' } })
        .then(function (response) {
          if (!response.ok) throw new Error('Nu am putut încărca hotarele raioanelor.');
          return response.json();
        })
        .then(function (data) {
          geoJsonData = data;
          return data;
        });

    Promise.all([dataRequest, geoJsonRequest])
      .then(function (results) {
        renderMap(results[0], results[1]);
      })
      .catch(function (error) {
        updatedEl.textContent = error.message;
        listEl.innerHTML = '<div class="livrari-map-empty">Nu am putut încărca harta.</div>';
      })
      .finally(function () {
        loading = false;
        refreshBtn.disabled = false;
      });
  }

  refreshBtn.addEventListener('click', loadMapData);
  raionSelect.addEventListener('change', function () {
    selectRaion(raionSelect.value, true);
  });
  loadMapData();
});
</script>
@endpush
