@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', 'Livrări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/operatori.css') }}">
<style>
  .livrari-page { padding: 24px 20px; max-width: 1200px; margin: 0 auto; font-family: 'Montserrat', system-ui, sans-serif; color: #E5E7EB; }
  .livrari-page h1 { color: #FFEE00; margin: 0 0 8px 0; font-size: 28px; font-weight: 800; }
  .livrari-page .subtitle { color: #9CA3AF; margin: 0 0 24px 0; font-size: 14px; }
  .livrari-alert { display: flex; align-items: center; gap: 10px; padding: 14px 18px; border-radius: 12px; margin-bottom: 20px; font-weight: 500; }
  .livrari-alert-success { background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #fff; }
  .livrari-card { background: linear-gradient(160deg, #1F2937 0%, #111827 100%); border-radius: 16px; padding: 24px; margin-bottom: 24px; border: 1px solid rgba(255,255,255,0.06); }
  .livrari-card h2 { color: #fff; margin: 0 0 20px 0; font-size: 20px; font-weight: 700; display: flex; align-items: center; gap: 10px; }
  .livrari-form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
  .livrari-form-grid label { display: block; color: #FFEE00; margin-bottom: 6px; font-size: 13px; font-weight: 600; }
  .livrari-form-grid input { width: 100%; padding: 12px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; background: rgba(255,255,255,0.05); color: #fff; font-size: 14px; }
  .livrari-form-grid input:focus { outline: none; border-color: #FFEE00; background: rgba(255,238,0,0.08); }
  .livrari-btn { padding: 12px 24px; border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; border: none; display: inline-flex; align-items: center; gap: 8px; }
  .livrari-btn-primary { background: linear-gradient(135deg, #FFEE00 0%, #FACC15 100%); color: #000; }
  .livrari-btn-primary:hover { opacity: 0.95; }
  .livrari-table-wrap { overflow-x: auto; }
  .livrari-table { width: 100%; border-collapse: collapse; }
  .livrari-table th, .livrari-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid rgba(255,255,255,0.08); color: #E5E7EB; }
  .livrari-table th { background: rgba(255,238,0,0.15); color: #FFEE00; font-weight: 700; font-size: 13px; }
  .livrari-table tbody tr:hover { background: rgba(255,255,255,0.03); }
  .livrari-kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
  .livrari-kpi-box { background: rgba(255,238,0,0.1); border: 1px solid rgba(255,238,0,0.3); border-radius: 12px; padding: 20px; text-align: center; }
  .livrari-kpi-box .label { color: #9CA3AF; font-size: 12px; margin-bottom: 6px; }
  .livrari-kpi-box .value { color: #FFEE00; font-size: 24px; font-weight: 800; }
  .livrari-per-operator { margin-top: 16px; }
  .livrari-per-operator h3 { color: #fff; font-size: 16px; margin: 0 0 12px 0; }
  .livrari-per-operator table { width: 100%; max-width: 400px; }
  .livrari-per-operator th { text-align: left; color: #9CA3AF; font-size: 12px; }
  .livrari-pagination { margin-top: 20px; display: flex; justify-content: center; gap: 8px; }
  .livrari-pagination a, .livrari-pagination span { padding: 8px 14px; border-radius: 8px; background: rgba(255,255,255,0.08); color: #fff; text-decoration: none; font-size: 14px; }
  .livrari-pagination a:hover { background: rgba(255,238,0,0.2); color: #FFEE00; }
  .livrari-pagination .disabled span { color: #6B7280; }
  .livrari-filters { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 16px; margin-bottom: 20px; }
  .livrari-filters label { display: block; color: #9CA3AF; font-size: 12px; margin-bottom: 4px; }
  .livrari-filters select { padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: #1F2937; color: #E5E7EB; min-width: 160px; }
  .livrari-filters select option { background: #1F2937; color: #E5E7EB; }
  .livrari-form-grid select { background: #1F2937 !important; color: #E5E7EB !important; }
  .livrari-form-grid select option { background: #1F2937; color: #E5E7EB; }
  .livrari-filters .livrari-btn { padding: 10px 18px; font-size: 13px; }
  .livrari-search-wrap { min-width: 220px; }
  /* Formular adăugare livrare – mai simplu pentru operatori */
  .livrari-add-card { margin-bottom: 24px; }
  .livrari-add-hint { color: #9CA3AF; font-size: 14px; margin: -8px 0 20px 0; }
  .livrari-add-form { display: flex; flex-direction: column; gap: 20px; }
  .livrari-add-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
  .livrari-add-row-full { grid-template-columns: 1fr; }
  .livrari-add-field label { display: block; color: #FFEE00; margin-bottom: 8px; font-size: 14px; font-weight: 600; }
  .livrari-add-field input,
  .livrari-add-field select { width: 100%; padding: 14px 16px; border: 1px solid rgba(255,255,255,0.2); border-radius: 10px; background: #1F2937; color: #E5E7EB; font-size: 15px; }
  .livrari-add-field input:focus,
  .livrari-add-field select:focus { outline: none; border-color: #FFEE00; }
  .livrari-add-field input::placeholder { color: #6B7280; }
  .livrari-add-actions { margin-top: 8px; }
  .livrari-btn-add { padding: 14px 28px; font-size: 16px; }
  @media (max-width: 600px) {
    .livrari-add-row { grid-template-columns: 1fr; }
  }
  .livrari-search-input { padding: 10px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 8px; background: #1F2937; color: #E5E7EB; min-width: 220px; font-size: 14px; }
  .livrari-search-input::placeholder { color: #6B7280; }
  .livrari-search-input:focus { outline: none; border-color: #FFEE00; background: #111827; }
</style>
@endpush

@section('content')
<div class="livrari-page">
  <h1><i class="fas fa-truck" style="margin-right: 10px;"></i> Livrări</h1>
  <p class="subtitle">{{ $isAdmin ? 'Toate livrările și KPI per operator' : 'Adaugă și vizualizează livrările tale' }}</p>

  @php
    $filters = $filters ?? ['luna' => '', 'operator_id' => '', 'locatie' => '', 'cauta' => ''];
    $operatorsForFilter = $operatorsForFilter ?? collect();
  @endphp

  <form method="get" action="{{ route('livrari') }}" class="livrari-card" style="margin-bottom: 20px;">
    <h2 style="margin-bottom: 16px;"><i class="fas fa-filter"></i> Filtre și căutare</h2>
    <div class="livrari-filters">
      <div class="livrari-search-wrap">
        <label for="cauta">Căutare</label>
        <input type="text" id="cauta" name="cauta" value="{{ $filters['cauta'] ?? '' }}" placeholder="Nr. comandă, adresă, nr. client..." class="livrari-search-input" maxlength="200">
      </div>
      <div>
        <label>Lună</label>
        <select name="luna">
          <option value="">Toate lunile</option>
          @foreach(range(now()->year, now()->year - 2, -1) as $y)
            @foreach(range(1, 12) as $m)
              @php $ym = sprintf('%04d-%02d', $y, $m); @endphp
              <option value="{{ $ym }}" {{ ($filters['luna'] ?? '') == $ym ? 'selected' : '' }}>{{ \Carbon\Carbon::createFromFormat('Y-m', $ym)->translatedFormat('F Y') }}</option>
            @endforeach
          @endforeach
        </select>
      </div>
      @if($isAdmin)
      <div>
        <label>Operator</label>
        <select name="operator_id">
          <option value="">Toți operatorii</option>
          @foreach($operatorsForFilter as $u)
            <option value="{{ $u->id }}" {{ ($filters['operator_id'] ?? '') == $u->id ? 'selected' : '' }}>{{ trim($u->full_name ?? $u->name ?? '') ?: $u->username }}</option>
          @endforeach
        </select>
      </div>
      @endif
      <div>
        <label>Locație</label>
        <select name="locatie">
          <option value="">Toate</option>
          <option value="chisinau" {{ ($filters['locatie'] ?? '') === 'chisinau' ? 'selected' : '' }}>În Chișinău</option>
          <option value="afara" {{ ($filters['locatie'] ?? '') === 'afara' ? 'selected' : '' }}>În afara</option>
        </select>
      </div>
      <button type="submit" class="livrari-btn livrari-btn-primary"><i class="fas fa-search"></i> Filtrează</button>
    </div>
  </form>

  @if(session('success'))
  <div class="livrari-alert livrari-alert-success">
    <i class="fas fa-check-circle"></i><span>{{ session('success') }}</span>
  </div>
  @endif
  @if($errors->any())
  <div class="livrari-alert" style="background: rgba(239,68,68,0.2); color: #F87171; border: 1px solid rgba(239,68,68,0.4);">
    <i class="fas fa-exclamation-circle"></i>
    <span>{{ $errors->first() }}</span>
  </div>
  @endif

  @if($isAdmin && ($perOperator->isNotEmpty() || $totalLivrari > 0))
  <div class="livrari-card">
    <h2><i class="fas fa-chart-bar"></i> KPI Livrări</h2>
    <div class="livrari-kpi-grid">
      <div class="livrari-kpi-box">
        <div class="label">Total livrări</div>
        <div class="value">{{ number_format($totalLivrari, 0, ',', '.') }}</div>
      </div>
    </div>
    <div class="livrari-per-operator">
      <h3>Livrări per operator</h3>
      <table class="livrari-table">
        <thead>
          <tr>
            <th>Operator</th>
            <th class="tc">Nr. livrări</th>
          </tr>
        </thead>
        <tbody>
          @foreach($perOperator as $row)
          <tr>
            <td>{{ $row->nume }}</td>
            <td class="tc" style="text-align: center;">{{ $row->total }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @endif

  @if(!$isAdmin)
  <div class="livrari-card livrari-add-card">
    <h2><i class="fas fa-plus-circle"></i> Adaugă livrare nouă</h2>
    <p class="livrari-add-hint">Completează câmpurile și apasă Salvează. Locația (În Chișinău / În afara) se stabilește automat după localitate.</p>
    <form action="{{ route('livrari.store') }}" method="post" class="livrari-add-form">
      @csrf
      <input type="hidden" name="data" value="{{ old('data', date('Y-m-d')) }}" id="livrari-data-comanda">
      <div class="livrari-add-row">
        <div class="livrari-add-field">
          <label for="data_livrarii">Data livrării *</label>
          <input type="date" id="data_livrarii" name="data_livrarii" value="{{ old('data_livrarii', date('Y-m-d')) }}" required>
        </div>
        <div class="livrari-add-field">
          <label for="numar_comanda">Număr comandă *</label>
          <input type="text" id="numar_comanda" name="numar_comanda" value="{{ old('numar_comanda') }}" required maxlength="100" placeholder="Ex: CMD-001">
        </div>
      </div>
      <div class="livrari-add-row">
        <div class="livrari-add-field">
          <label for="localitate">Localitate *</label>
          <input type="text" id="localitate" name="localitate" value="{{ old('localitate') }}" required maxlength="255" placeholder="Ex: Chișinău, Bălți, Orhei...">
        </div>
        <div class="livrari-add-field">
          <label for="nr_client">Nr. client *</label>
          <input type="text" id="nr_client" name="nr_client" value="{{ old('nr_client') }}" required maxlength="100" placeholder="Ex: CL-123">
        </div>
      </div>
      <div class="livrari-add-row livrari-add-row-full">
        <div class="livrari-add-field">
          <label for="adresa_livrarii">Adresă *</label>
          <input type="text" id="adresa_livrarii" name="adresa_livrarii" value="{{ old('adresa_livrarii') }}" required maxlength="500" placeholder="Strada, nr., bloc, scara, apartament, cod poștal">
        </div>
      </div>
      <div class="livrari-add-actions">
        <button type="submit" class="livrari-btn livrari-btn-primary livrari-btn-add"><i class="fas fa-check"></i> Salvează livrarea</button>
      </div>
    </form>
  </div>
  @endif

  <div class="livrari-card">
    <h2><i class="fas fa-list"></i> {{ $isAdmin ? 'Toate livrările' : 'Livrările mele' }}</h2>
    <div class="livrari-table-wrap">
      <table class="livrari-table">
        <thead>
          <tr>
            <th>Număr comandă</th>
            <th>Data</th>
            <th>Localitate</th>
            <th>Adresa</th>
            <th>Nr. client</th>
            <th>Data livrării</th>
            <th>Locație</th>
            @if($isAdmin)<th>Operator</th>@endif
          </tr>
        </thead>
        <tbody>
          @forelse($livrari as $l)
          <tr>
            <td>{{ $l->numar_comanda }}</td>
            <td>{{ $l->data->format('d.m.Y') }}</td>
            <td>{{ $l->localitate ?? '—' }}</td>
            <td>{{ $l->adresa_livrarii }}</td>
            <td>{{ $l->nr_client }}</td>
            <td>{{ $l->data_livrarii->format('d.m.Y') }}</td>
            <td>{{ isset($l->in_chisinau) ? ($l->in_chisinau ? 'În Chișinău' : 'În afara') : '—' }}</td>
            @if($isAdmin)
            <td>{{ $l->user ? (trim($l->user->full_name ?? $l->user->name ?? '') ?: $l->user->username) : '—' }}</td>
            @endif
          </tr>
          @empty
          <tr>
            <td colspan="{{ $isAdmin ? 9 : 8 }}" style="text-align: center; color: #9CA3AF; padding: 32px;">Nicio livrare înregistrată.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($livrari->hasPages())
    <div class="livrari-pagination">
      {{ $livrari->links() }}
    </div>
    @endif
  </div>
</div>
@if(!$isAdmin)
@push('scripts')
<script>
(function() {
  var dataComanda = document.getElementById('livrari-data-comanda');
  var dataLivrarii = document.getElementById('data_livrarii');
  if (dataComanda && dataLivrarii) {
    dataLivrarii.addEventListener('change', function() {
      dataComanda.value = dataLivrarii.value;
    });
  }
})();
</script>
@endpush
@endif
@endsection
