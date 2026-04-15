@extends(auth()->check() && auth()->user()->isOperator() ? 'layouts.operator' : 'layouts.app')

@section('title', auth()->check() && auth()->user()->isOperator() ? 'Setări – VOLTA STATS' : 'Setări – VOLTA')

@push('styles')
<link rel="stylesheet" href="{{ url('css/setari.css') }}">
<style>
  .setari-photos-card { margin-bottom: 24px; }
  .setari-photos-hint { color: #9CA3AF; font-size: 14px; margin: 0 0 16px 0; }
  .setari-photo-forms { display: flex; flex-wrap: wrap; gap: 12px; }
  .setari-photo-form { margin: 0; }
  .setari-photo-input { position: absolute; width: 0; height: 0; opacity: 0; }
  .setari-photo-btn {
    display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px;
    background: rgba(255, 238, 0, 0.15); color: #FFEE00; border: 1px solid rgba(255, 238, 0, 0.4);
    border-radius: 10px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.2s;
  }
  .setari-photo-btn:hover { background: rgba(255, 238, 0, 0.25); }
</style>
@endpush

@section('content')
@php $isAdmin = auth()->check() && in_array(strtolower(auth()->user()->role ?? ''), ['admin', 'administrator']); @endphp
@php $isOperator = auth()->check() && auth()->user()->isOperator(); @endphp

<div class="setari-wrap">
@if($isOperator)
  {{-- Operator: Poze profil și copertă --}}
  @if(isset($operatorRecord) && $operatorRecord)
  <div class="panel setari-operator-card setari-photos-card" id="poze">
    <h2 class="setari-section-title">
      <i class="fas fa-images"></i> Poze profil și copertă
    </h2>
    <p class="setari-photos-hint">Încarcă poza de profil și/sau poza de copertă (folosite pe pagina Datele mele).</p>
    <div class="setari-photo-forms">
      <form action="{{ route('operatori.photo.profil', $operatorRecord->id) }}" method="post" enctype="multipart/form-data" class="setari-photo-form">
        @csrf
        <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="setari-photo-input" id="setari-profil" onchange="this.form.submit()">
        <label for="setari-profil" class="setari-photo-btn"><i class="fas fa-user-circle"></i> Poza de profil</label>
      </form>
      <form action="{{ route('operatori.photo.coperta', $operatorRecord->id) }}" method="post" enctype="multipart/form-data" class="setari-photo-form">
        @csrf
        <input type="file" name="photo" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" class="setari-photo-input" id="setari-coperta" onchange="this.form.submit()">
        <label for="setari-coperta" class="setari-photo-btn"><i class="fas fa-image"></i> Poza de copertă</label>
      </form>
    </div>
    @if(session('success'))
    <div class="setari-message success" style="margin-top: 12px;">{{ session('success') }}</div>
    @endif
  </div>
  @endif
  {{-- Operator: Securitate (schimbare parolă) --}}
  <div class="panel setari-operator-card">
    <h2 class="setari-section-title">
      <i class="fas fa-lock"></i> Securitate
    </h2>
    <form method="POST" action="{{ route('setari.password') }}">
      @csrf
      <div class="form">
        <div class="field">
          <label>Parola curentă</label>
          <input type="password" name="current_password" placeholder="Introdu parola curentă" autocomplete="current-password">
        </div>
        <div class="field">
          <label>Parola nouă</label>
          <input type="password" name="new_password" placeholder="Introdu parola nouă" autocomplete="new-password">
        </div>
        <div class="field">
          <label>Confirmă parola nouă</label>
          <input type="password" name="confirm_password" placeholder="Confirmă parola nouă" autocomplete="new-password">
        </div>
        <div class="field">
          <button type="submit" name="change_password" class="btn" style="width:100%">Schimbă parola</button>
        </div>
        @if(session('passMessage'))
          <div class="setari-message {{ session('passMessage') === 'Parola a fost schimbată cu succes!' ? 'success' : 'error' }}">
            {{ session('passMessage') }}
          </div>
        @endif
      </div>
    </form>
  </div>
@else
  <div class="panel">
    <div class="tabs">
      <div class="tab active" data-tab="general"><i class="fas fa-sliders-h"></i> Generale</div>
      <div class="tab" data-tab="security"><i class="fas fa-lock"></i> Securitate</div>
      @if($isAdmin)
      <div class="tab" data-tab="data-refresh"><i class="fas fa-database"></i> Actualizare date</div>
      @endif
    </div>

    {{-- Generale --}}
    <form method="POST" action="{{ route('setari.update') }}" class="tab-content active" id="general">
      @csrf
      <div class="form">
        <div class="field">
          <label>Nume utilizator</label>
          <input type="text" name="username" value="{{ Auth::user()->username }}">
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" value="{{ Auth::user()->email ?? '' }}" placeholder="email@exemplu.com">
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
          <button type="submit" name="save_general" class="btn">Salvează setări</button>
        </div>
      </div>
    </form>

    {{-- Securitate --}}
    <form method="POST" action="{{ route('setari.password') }}" class="tab-content" id="security">
      @csrf
      <div class="form">
        <div class="field">
          <label>Parola curentă</label>
          <input type="password" name="current_password" placeholder="Introdu parola curentă" autocomplete="current-password">
        </div>
        <div class="field">
          <label>Parola nouă</label>
          <input type="password" name="new_password" placeholder="Introdu parola nouă" autocomplete="new-password">
        </div>
        <div class="field">
          <label>Confirmă parola nouă</label>
          <input type="password" name="confirm_password" placeholder="Confirmă parola nouă" autocomplete="new-password">
        </div>
        <div class="field">
          <button type="submit" name="change_password" class="btn">Schimbă parola</button>
        </div>
        @if(session('passMessage'))
          <div class="setari-message {{ session('passMessage') === 'Parola a fost schimbată cu succes!' ? 'success' : 'error' }}">
            {{ session('passMessage') }}
          </div>
        @endif
      </div>
    </form>

    @if($isAdmin)
    <div class="tab-content" id="data-refresh">
      <div class="form" style="display: block;">
        <div class="setari-1c-block">
          <div class="field">
            <label>Actualizare date curente</label>
            <p class="setari-1c-desc">Actualizează doar perioadele care lipsesc și păstrează datele existente.</p>
          </div>
          <div class="field" style="margin-top: 12px;">
            <button type="button" id="sync1cBtn" class="btn-sync-1c" title="Actualizare date">
              <i class="fas fa-sync-alt"></i> Actualizează
            </button>
            <div id="sync1cStatus" class="setari-sync-status" aria-live="polite"></div>
          </div>
        </div>
        <div class="setari-1c-block">
          <div class="field">
            <label>Reîmprospătare istoric</label>
            <p class="setari-1c-desc">Reîncarcă și rescrie în baza de date toate lunile trecute (ultimele 12 luni). Folosește doar dacă vrei să suprascrii datele existente.</p>
          </div>
          <div class="field" style="margin-top: 12px;">
            <button type="button" id="onecHardRefreshBtn" class="btn-hard-refresh">Rescrie istoricul</button>
          </div>
          <div id="onecHardRefreshStatus"></div>
        </div>
      </div>
    </div>
    @endif
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

// Actualizare date (admin)
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
          var msg = data.message || 'Eroare la actualizarea datelor.';
          if (data.error) {
            if (String(data.error).indexOf('Connection refused') !== -1) {
              msg = 'Serverul nu răspunde. Verifică accesul.';
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

// Reîmprospătare istoric (admin)
(function() {
  const btn = document.getElementById('onecHardRefreshBtn');
  const statusEl = document.getElementById('onecHardRefreshStatus');
  if (!btn || !statusEl) return;
  btn.addEventListener('click', function() {
    if (btn.disabled) return;
    btn.disabled = true;
    statusEl.style.display = 'block';
    statusEl.textContent = 'Se reîncarcă datele... (poate dura câteva minute)';
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
        statusEl.textContent += ' Apeluri: ' + data.onec_calls + '/' + (data.total_months || '?') + '.';
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
