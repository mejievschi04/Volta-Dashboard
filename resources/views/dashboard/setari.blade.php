@extends('layouts.app')

@section('title', 'Setări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/setari.css') }}">
<style>
  .setari-1c-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
  .btn-sync-1c {
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 12px 24px; background: #FFEE00; color: #000; border: none; border-radius: 8px;
    font-size: 14px; font-weight: 700; cursor: pointer; transition: all 0.25s ease;
  }
  .btn-sync-1c:hover { background: #fff333; box-shadow: 0 4px 14px rgba(255, 238, 0, 0.35); }
  .btn-sync-1c:disabled { opacity: 0.7; cursor: not-allowed; }
  .btn-sync-1c.syncing .fa-sync-alt { animation: setari-spin 0.8s linear infinite; }
  @keyframes setari-spin { to { transform: rotate(360deg); } }
  .setari-sync-status {
    display: none; margin-top: 8px; padding: 10px 14px; font-size: 13px; border-radius: 8px;
    background: rgba(31, 41, 55, 0.8); color: #9CA3AF;
  }
  .setari-sync-status-error { color: #F87171; background: rgba(239, 68, 68, 0.15) !important; }
  .btn-hard-refresh { width: 100%; background: #EF4444; color: #fff; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="panel">
  <div class="tabs">
    <div class="tab active" data-tab="general">Generale</div>
    <div class="tab" data-tab="security">Securitate</div>
    @php $isAdmin = auth()->check() && in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']); @endphp
    @if($isAdmin)
    <div class="tab" data-tab="onec-refresh">1C</div>
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
  <!-- Secțiune 1C – doar admin -->
  <div class="tab-content" id="onec-refresh">
    <div class="form">
      <div class="field">
        <label>Sync 1C (lună curentă / lunile lipsă)</label>
        <p style="color: #9CA3AF; font-size: 13px; margin: 8px 0 12px 0;">Sincronizează cu 1C doar perioadele care lipsesc (luna curentă, luna trecută). Nu rescrie datele existente.</p>
      </div>
      <div class="field setari-1c-actions">
        <button type="button" id="sync1cBtn" class="btn btn-sync-1c" title="Sincronizare 1C">
          <i class="fas fa-sync-alt"></i> Sync 1C
        </button>
        <div id="sync1cStatus" class="setari-sync-status" aria-live="polite"></div>
      </div>

      <div class="field" style="margin-top: 28px;">
        <label>Hard refresh 1C (lunile trecute)</label>
        <p style="color: #9CA3AF; font-size: 13px; margin: 8px 0 12px 0;">Reîncarcă din 1C și rescrie în baza de date toate lunile trecute (ultimele 12 luni). Folosește doar dacă vrei să suprascrii datele existente cu cele de la 1C.</p>
      </div>
      <div class="field">
        <button type="button" id="onecHardRefreshBtn" class="btn btn-hard-refresh">
          Rescrie 1C (lunile trecute)
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

// Sync 1C (admin) – în Setări > Date 1C
@if(isset($isAdmin) && $isAdmin)
(function() {
  const syncBtn = document.getElementById('sync1cBtn');
  const statusEl = document.getElementById('sync1cStatus');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const syncUrl = "{{ route('api.1c.sync.kpi') }}";

  function setSyncStatus(text, isError) {
    if (!statusEl) return;
    statusEl.textContent = text;
    statusEl.style.display = text ? 'block' : 'none';
    statusEl.className = 'setari-sync-status' + (isError ? ' setari-sync-status-error' : '');
  }

  if (syncBtn) {
    syncBtn.addEventListener('click', function() {
      if (syncBtn.disabled) return;
      setSyncStatus('Se sincronizează...', false);
      syncBtn.disabled = true;
      syncBtn.classList.add('syncing');
      fetch(syncUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ smart: true })
      })
      .then(async function(response) {
        const contentType = response.headers.get('content-type') || '';
        let data = {};
        if (contentType.includes('application/json')) {
          data = await response.json().catch(function() { return {}; });
        } else {
          if (response.status === 419) {
            setSyncStatus('Eroare 419: Token CSRF invalid. Reîncarcă pagina.', true);
            return;
          }
          if (response.status === 500) {
            setSyncStatus('Eroare 500 de la server.', true);
            return;
          }
          setSyncStatus('Eroare ' + response.status, true);
          return;
        }
        if (!response.ok || !data.success) {
          var msg = data.message || 'Eroare la sincronizarea cu 1C.';
          if (data.error) {
            if (String(data.error).indexOf('Connection refused') !== -1) {
              msg = 'Serverul 1C nu răspunde. Verifică accesul de pe server.';
            } else {
              msg = msg + ' ' + data.error;
            }
          }
          setSyncStatus(msg, true);
        } else {
          setSyncStatus(data.message || (data.date_start && data.date_end ? 'OK: ' + data.date_start + ' – ' + data.date_end : 'Sincronizare reușită.'), false);
        }
      })
      .catch(function(error) {
        setSyncStatus('Eroare rețea: ' + (error.message || 'Verifică consola (F12).'), true);
      })
      .finally(function() {
        syncBtn.disabled = false;
        syncBtn.classList.remove('syncing');
      });
    });
  }
})();

// Hard refresh 1C (admin)
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
