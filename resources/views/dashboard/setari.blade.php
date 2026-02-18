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
    @php $isAdmin = auth()->check() && in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']); @endphp
    @if($isAdmin)
    <div class="tab" data-tab="onec-refresh">Date 1C</div>
    @endif
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

  @if($isAdmin)
  <!-- Hard refresh date 1C (doar admin) -->
  <div class="tab-content" id="onec-refresh">
    <div class="form">
      <div class="field">
        <label>🔄 Hard refresh date 1C (lunile trecute)</label>
        <p style="color: #9CA3AF; font-size: 13px; margin: 8px 0 12px 0;">Reîncarcă din 1C și rescrie în baza de date toate lunile trecute (ultimele 12 luni). Folosește doar dacă vrei să suprascrii datele existente cu cele de la 1C.</p>
      </div>
      <div class="field">
        <button type="button" id="onecHardRefreshBtn" class="btn" style="width:100%; background: #EF4444; color: #fff; font-weight: 600;">
          Rescrie toate datele 1C (lunile trecute)
        </button>
      </div>
      <div id="onecHardRefreshStatus" style="margin-top: 12px; padding: 12px; border-radius: 8px; display: none;"></div>
    </div>
  </div>
  @endif
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

// Hard refresh 1C (admin)
@if(isset($isAdmin) && $isAdmin)
(function() {
  const btn = document.getElementById('onecHardRefreshBtn');
  const statusEl = document.getElementById('onecHardRefreshStatus');
  if (!btn || !statusEl) return;
  btn.addEventListener('click', function() {
    if (btn.disabled) return;
    btn.disabled = true;
    statusEl.style.display = 'block';
    statusEl.textContent = 'Se reîncarcă datele din 1C... (poate dura câteva minute)';
    statusEl.style.background = '#1F2937';
    statusEl.style.color = '#fff';
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    fetch("{{ route('api.1c.hard.refresh') }}", {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
      body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
      statusEl.textContent = data.message || (data.success ? 'Gata.' : 'Eroare.');
      statusEl.style.background = data.success ? '#10B981' : '#EF4444';
      statusEl.style.color = '#fff';
      if (data.onec_calls !== undefined) {
        statusEl.textContent += ' Apeluri 1C: ' + data.onec_calls + '/' + (data.total_months || '?') + '.';
      }
    })
    .catch(err => {
      statusEl.textContent = 'Eroare: ' + (err.message || 'rețea');
      statusEl.style.background = '#EF4444';
    })
    .finally(() => { btn.disabled = false; });
  });
})();
@endif
</script>
@endpush
