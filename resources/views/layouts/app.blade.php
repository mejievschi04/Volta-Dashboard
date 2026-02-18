<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard – VOLTA')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('css/style.css') }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @stack('styles')
  <style>
    .sync-1c-sidebar { padding: 10px 14px; border-top: 1px solid rgba(255,255,255,0.08); }
    .sync-btn-sidebar { width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 14px; background: rgba(255,238,0,0.15); color: #FFEE00; border: 1px solid rgba(255,238,0,0.3); border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 13px; }
    .sync-btn-sidebar:hover { background: rgba(255,238,0,0.25); }
    .sync-btn-sidebar.syncing { opacity: 0.8; pointer-events: none; }
    .sync-btn-sidebar.syncing .fa-sync-alt { animation: spin 0.8s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
    .sync-status-sidebar { display: block; margin-top: 8px; min-height: 20px; padding: 6px 8px; font-size: 12px; line-height: 1.3; word-wrap: break-word; border-radius: 6px; background: rgba(0,0,0,0.2); color: var(--muted, #9CA3AF); }
    .sync-status-sidebar.sync-status-error { color: #F87171; background: rgba(239,68,68,0.1); }
  </style>
</head>
<body>
  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      <div class="logo">
        <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="logo-mark">
        <h1>VOLTA Dashboard</h1>
      </div>
      <nav class="nav">
        <h3>Menu</h3>
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-home"></i><span class="txt">Acasă</span>
        </a>
        <a href="{{ route('rapoarte') }}" class="{{ request()->routeIs('rapoarte') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i><span class="txt">Rapoarte</span>
        </a>
        <a href="{{ route('istoric') }}" class="{{ request()->routeIs('istoric') ? 'active' : '' }}">
          <i class="fas fa-history"></i><span class="txt">Istoric</span>
        </a>
        <a href="{{ route('operatori') }}" class="{{ request()->routeIs('operatori*') ? 'active' : '' }}">
          <i class="fas fa-users"></i><span class="txt">Operatori</span>
        </a>
        <a href="{{ route('produse') }}" class="{{ request()->routeIs('produse') ? 'active' : '' }}">
          <i class="fas fa-box"></i><span class="txt">Produse</span>
        </a>
        <a href="{{ route('trafic') }}" class="{{ request()->routeIs('trafic') ? 'active' : '' }}">
          <i class="fas fa-network-wired"></i><span class="txt">Trafic</span>
        </a>
        <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari') ? 'active' : '' }}">
          <i class="fas fa-cog"></i><span class="txt">Setări</span>
        </a>
        @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users*') ? 'active' : '' }}">
          <i class="fas fa-user-shield"></i><span class="txt">Utilizatori</span>
        </a>
        @endif
      </nav>

      <div class="sync-1c-sidebar">
        <button type="button" class="sync-btn sync-btn-sidebar" id="syncBtn" title="Sincronizare date 1C">
          <i class="fas fa-sync-alt"></i><span class="txt">Sync 1C</span>
        </button>
        <div id="sync1cStatus" class="sync-status sync-status-sidebar" aria-live="polite"></div>
      </div>

      <div class="logout-container">
        <form action="{{ route('logout') }}" method="post">
          @csrf
          <button type="submit" class="logout-btn">
            <i class="fas fa-sign-out-alt"></i> Ieși din cont
          </button>
        </form>
      </div>
    </aside>

    <!-- OVERLAY pentru mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- MAIN -->
    <section class="main">
      <!-- Top bar fixă pentru mobile -->
      <div class="top-bar" id="topBar">
        <div class="top-bar-content">
          <button class="hamburger" id="hamburgerBtn" aria-label="Toggle menu">
            <span class="bar"></span>
          </button>
          <div class="top-bar-brand">
            <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="top-bar-logo">
            <h2 class="top-bar-title">VOLTA Dashboard</h2>
          </div>
        </div>
      </div>

      <!-- TOPBAR - doar pe pagina home -->
      @if(request()->routeIs('dashboard'))
      <div class="header">
        <h1>@yield('header-title', 'Dashboard')</h1>
        <div class="user-menu">
          <div class="user-info">
            <div class="name">{{ Auth::check() ? Auth::user()->username : 'User' }}</div>
            <div class="role">{{ Auth::check() ? (Auth::user()->role ?? 'User') : 'User' }}</div>
          </div>
          <div class="user-avatar">{{ Auth::check() ? strtoupper(substr(Auth::user()->username, 0, 1)) : 'U' }}</div>
        </div>
      </div>
      @endif

      <!-- CONTENT -->
      <div class="content">
        @yield('content')
      </div>
    </section>
  </div>

  @stack('scripts')
  <script>
    // Suprimă erorile de la extensiile browserului
    window.addEventListener('error', function(e) {
      if (e.message && e.message.includes('message channel closed')) {
        e.preventDefault();
        return false;
      }
    });

    // Suprimă erorile de promise rejection pentru mesaje asincrone
    window.addEventListener('unhandledrejection', function(e) {
      if (e.reason && e.reason.message && e.reason.message.includes('message channel closed')) {
        e.preventDefault();
        return false;
      }
    });

    // Toggle sidebar pentru mobile
    document.addEventListener('DOMContentLoaded', function() {
      const hamburgerBtn = document.getElementById('hamburgerBtn');
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebarOverlay');
      
      console.log('Hamburger button:', hamburgerBtn);
      console.log('Sidebar:', sidebar);
      console.log('Overlay:', overlay);
      
      if (hamburgerBtn && sidebar && overlay) {
        // Asigură că hamburger-ul este vizibil pe mobile
        if (window.innerWidth <= 1100) {
          hamburgerBtn.style.display = 'flex';
          hamburgerBtn.style.visibility = 'visible';
          hamburgerBtn.style.opacity = '1';
        }
        
        hamburgerBtn.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          console.log('Hamburger clicked');
          sidebar.classList.toggle('open');
          overlay.classList.toggle('active');
          hamburgerBtn.classList.toggle('active');
          document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
        });
        
        overlay.addEventListener('click', function() {
          sidebar.classList.remove('open');
          overlay.classList.remove('active');
          hamburgerBtn.classList.remove('active');
          document.body.style.overflow = '';
        });
        
        // Închide sidebar când se face click pe un link
        const navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(link => {
          link.addEventListener('click', function() {
            if (window.innerWidth <= 1100) {
              sidebar.classList.remove('open');
              overlay.classList.remove('active');
              hamburgerBtn.classList.remove('active');
              document.body.style.overflow = '';
            }
          });
        });
        
        // Gestionează resize-ul ferestrei
        window.addEventListener('resize', function() {
          if (window.innerWidth <= 1100) {
            hamburgerBtn.style.display = 'flex';
            hamburgerBtn.style.visibility = 'visible';
            hamburgerBtn.style.opacity = '1';
          } else {
            hamburgerBtn.style.display = 'none';
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            document.body.style.overflow = '';
          }
        });
      } else {
        console.error('Elemente lipsă:', {
          hamburgerBtn: !!hamburgerBtn,
          sidebar: !!sidebar,
          overlay: !!overlay
        });
      }
    });

    // Buton sincronizare 1C - apel API backend
    document.addEventListener('DOMContentLoaded', function() {
      const syncBtn = document.getElementById('syncBtn');
      const statusEl = document.getElementById('sync1cStatus');
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const syncUrl = "{{ route('api.1c.sync.kpi') }}";

      function setStatus(text, isError) {
        if (!statusEl) return;
        statusEl.textContent = text;
        statusEl.className = 'sync-status' + (isError ? ' sync-status-error' : '');
      }

      if (syncBtn) {
        syncBtn.addEventListener('click', function() {
          setStatus('Se sincronizează...', false);
          syncBtn.classList.add('syncing');
          syncBtn.disabled = true;
          console.log('Sincronizare 1C →', syncUrl);

          // smart=1: doar perioade care lipsesc (luna curentă, luna trecută), fără apel 1C dacă există deja
          fetch(syncUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken || ''
            },
            body: JSON.stringify({ smart: true })
          })
          .then(async (response) => {
            const contentType = response.headers.get('content-type') || '';
            let data = {};
            if (contentType.includes('application/json')) {
              data = await response.json().catch(() => ({}));
            } else {
              const text = await response.text();
              console.error('Sync 1C: răspuns non-JSON', response.status, text.substring(0, 200));
              if (response.status === 419) {
                setStatus('Eroare 419: Token CSRF invalid. Reîncarcă pagina și încearcă din nou.', true);
                return;
              }
              if (response.status === 500) {
                setStatus('Eroare 500 de la server. Verifică storage/logs/laravel.log pe server.', true);
                return;
              }
              setStatus('Eroare ' + response.status + '. Răspuns invalid.', true);
              return;
            }

            if (!response.ok || !data?.success) {
              let msg = data?.message || 'Eroare la sincronizarea cu 1C.';
              if (data?.error) {
                if (data.error.includes('Connection refused') || data.error.includes('connection refused')) {
                  msg = 'Serverul 1C nu răspunde (conexiune refuzată). Verifică accesul de pe server.';
                } else {
                  msg = msg + ' Detalii: ' + data.error;
                }
              }
              setStatus(msg, true);
              console.error('Sync 1C error', data);
            } else {
              const msg = data.message || (data.date_start && data.date_end ? 'OK: ' + data.date_start + ' – ' + data.date_end : 'Sincronizare reușită.');
              setStatus(msg, false);
              console.log('Sync 1C success', data);
            }
          })
          .catch((error) => {
            console.error('Sync 1C network error', error);
            setStatus('Eroare rețea: ' + (error.message || 'Verifică consola (F12).'), true);
          })
          .finally(() => {
            syncBtn.classList.remove('syncing');
            syncBtn.disabled = false;
          });
        });
      }
    });
  </script>
</body>
</html>

