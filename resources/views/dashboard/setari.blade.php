@extends('layouts.app')

@section('title', 'Setări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/setari.css') }}">
@endpush

@section('content')
<div class="panel">
  <div class="tabs">
    <div class="tab active" data-tab="general">Generale</div>
    <div class="tab" data-tab="security">Securitate</div>
    <div class="tab" data-tab="import-vanzari">Import Vânzări</div>
  </div>

  <!-- GENERAL -->
  <form method="POST" action="{{ route('setari.update') }}" class="tab-content active" id="general">
    @csrf
    <div class="form">
      <div class="field">
        <label>Nume utilizator</label>
        <input type="text" name="username" value="{{ Auth::user()->username }}">
      </div>
      <div class="field">
        <label>Email</label>
        <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}">
      </div>
      <div class="field">
        <label>Monedă</label>
        <select name="currency">
          <option value="USD" {{ (Auth::user()->currency ?? 'MDL') == 'USD' ? 'selected' : '' }}>USD</option>
          <option value="EUR" {{ (Auth::user()->currency ?? 'MDL') == 'EUR' ? 'selected' : '' }}>EUR</option>
          <option value="MDL" {{ (Auth::user()->currency ?? 'MDL') == 'MDL' ? 'selected' : '' }}>MDL</option>
        </select>
      </div>
      <div class="field">
        <label>Limba interfață</label>
        <select name="language">
          <option value="Română" {{ (Auth::user()->language ?? 'Română') == 'Română' ? 'selected' : '' }}>Română</option>
          <option value="English" {{ (Auth::user()->language ?? 'Română') == 'English' ? 'selected' : '' }}>English</option>
          <option value="Русский" {{ (Auth::user()->language ?? 'Română') == 'Русский' ? 'selected' : '' }}>Русский</option>
        </select>
      </div>
      <div class="field">
        <label>Rol</label>
        <select name="role">
          <option value="Administrator" {{ (Auth::user()->role ?? 'Administrator') == 'Administrator' ? 'selected' : '' }}>Administrator</option>
          <option value="Manager" {{ (Auth::user()->role ?? 'Administrator') == 'Manager' ? 'selected' : '' }}>Manager</option>
          <option value="Vizualizare" {{ (Auth::user()->role ?? 'Administrator') == 'Vizualizare' ? 'selected' : '' }}>Vizualizare</option>
        </select>
      </div>
      <div class="field">
        <label>Țară</label>
        <select name="country">
          <option value="Republica Moldova" {{ (Auth::user()->country ?? 'Republica Moldova') == 'Republica Moldova' ? 'selected' : '' }}>Republica Moldova</option>
          <option value="România" {{ (Auth::user()->country ?? 'Republica Moldova') == 'România' ? 'selected' : '' }}>România</option>
          <option value="Ucraina" {{ (Auth::user()->country ?? 'Republica Moldova') == 'Ucraina' ? 'selected' : '' }}>Ucraina</option>
        </select>
      </div>
      <div class="field">
        <button type="submit" name="save_general" class="btn" style="width:100%">Salvează setări</button>
      </div>
    </div>
  </form>

  <!-- SECURITATE -->
  <form method="POST" action="{{ route('setari.password') }}" class="tab-content" id="security">
    @csrf
    <div class="form">
      <div class="field">
        <label>Parola curentă</label>
        <input type="password" name="current_password" placeholder="Introdu parola curentă">
      </div>
      <div class="field">
        <label>Parola nouă</label>
        <input type="password" name="new_password" placeholder="Introdu parola nouă">
      </div>
      <div class="field">
        <label>Confirmă parola nouă</label>
        <input type="password" name="confirm_password" placeholder="Confirmă parola nouă">
      </div>
      <div class="field">
        <button type="submit" name="change_password" class="btn" style="width:100%">Schimbă parola</button>
      </div>
      @if(session('passMessage'))
        <div style="margin-top:10px; color: {{ session('passMessage') === 'Parola a fost schimbată cu succes!' ? 'green' : 'red' }};">
          {{ session('passMessage') }}
        </div>
      @endif
    </div>
  </form>

  <!-- IMPORT DATE VANZARI -->
  <form method="POST" action="{{ route('upload.vanzari') }}" enctype="multipart/form-data" class="tab-content" id="import-vanzari">
    @csrf
    <div class="form">
      <div class="field">
        <label>📊 Importare Date Vânzări din 1C (Excel)</label>
      </div>
      <div class="field">
        <input type="file" name="excel_file" accept=".xlsx,.xls" required style="padding: 10px; border: 2px dashed #333; border-radius: 8px; background: #111; color: #fff; width: 100%; cursor: pointer;">
      </div>
      <div class="field">
        <label style="display: flex; align-items: center; cursor: pointer;">
          <input type="checkbox" name="replace_existing" style="margin-right: 8px;">
          Înlocuiește datele existente (șterge datele vechi pentru perioada importată)
        </label>
      </div>
      <div class="field">
        <button type="submit" name="upload_excel" class="btn" style="width:100%; background: #FFEE00; color: #000; font-weight: 600;">
          📤 Încarcă și Importă Date Vânzări
        </button>
      </div>
      @if(session('import_status'))
        <div style="margin-top:10px; padding: 12px; border-radius: 8px; background: {{ session('import_status') === 'success' ? '#4caf50' : '#f44336' }}; color: white;">
          {!! session('import_message') !!}
        </div>
      @endif
    </div>
  </form>
</div>

<div class="toast" id="toast">
  @if(session('toastMessage'))
    {{ session('toastMessage') }}
  @endif
</div>
@endsection

@push('scripts')
<script>
// TAB-uri
const tabs = document.querySelectorAll('.tab');
const tabContents = document.querySelectorAll('.tab-content');
tabs.forEach(tab => {
  tab.addEventListener('click', () => {
    const id = tab.getAttribute('data-tab');
    tabs.forEach(t => t.classList.remove('active'));
    tabContents.forEach(c => c.classList.remove('active'));
    tab.classList.add('active');
    document.getElementById(id).classList.add('active');
  });
});

// TOAST
@if(session('toastMessage'))
document.getElementById('toast').classList.add('show');
setTimeout(() => {
  document.getElementById('toast').classList.remove('show');
}, 3000);
@endif
</script>
@endpush
