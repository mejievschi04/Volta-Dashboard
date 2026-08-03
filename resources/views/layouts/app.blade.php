<!DOCTYPE html>
@php
  $uiTheme = auth()->check() ? (auth()->user()->theme ?? 'dark') : 'dark';
  if (!in_array($uiTheme, ['dark', 'dark-red'], true)) {
    $uiTheme = 'dark';
  }
@endphp
<html lang="ro" data-theme="{{ $uiTheme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Dashboard – VOLTA')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <script>
    (function() {
      try {
        var collapsed = localStorage.getItem('volta.sidebar.collapsed') === '1';
        document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
      } catch (e) {}
      document.documentElement.classList.add('preload-sidebar-state');
    })();
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  @php $styleCssVersion = @filemtime(public_path('css/style.css')) ?: 0; @endphp
  <link rel="stylesheet" href="{{ asset('css/style.css') }}?v={{ $styleCssVersion }}"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @stack('styles')
</head>
<body>
  <div class="app">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
      @php
        $isMobileModule = request()->routeIs('mobile.analytics*', 'mobile.crashes*', 'mobile.feedback*');
        $canAccessMobileModule = auth()->check()
          && auth()->user()->isDev()
          && !auth()->user()->isAdmin()
          && !auth()->user()->isOperator();
        $mobileQuery = request()->only(['start', 'end']);
      @endphp
      <button type="button" class="logo" id="sidebarLogoToggle" aria-label="Restrange meniul lateral">
        <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="logo-mark">
        <h1>VOLTA STATS</h1>
      </button>
      <nav class="nav">
        @if(auth()->check() && auth()->user()->isOperator())
        <a href="{{ route('datele-mele') }}" class="{{ request()->routeIs('datele-mele') ? 'active' : '' }}">
          <i class="fas fa-chart-bar"></i><span class="txt">Datele mele</span>
        </a>
        <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}">
          <i class="fas fa-truck"></i><span class="txt">Livrări</span>
        </a>
        <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari*') ? 'active' : '' }}">
          <i class="fas fa-cog"></i><span class="txt">Setări</span>
        </a>
        @elseif($isMobileModule && $canAccessMobileModule)
        <a href="{{ route('mobile.analytics', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i><span class="txt">Prezentare</span>
        </a>
        <a href="{{ route('mobile.analytics.events', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.events') ? 'active' : '' }}">
          <i class="fas fa-bolt"></i><span class="txt">Evenimente</span>
        </a>
        <a href="{{ route('mobile.analytics.funnels', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.funnels') ? 'active' : '' }}">
          <i class="fas fa-filter-circle-dollar"></i><span class="txt">Pâlnie</span>
        </a>
        <a href="{{ route('mobile.analytics.pages', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.pages') ? 'active' : '' }}">
          <i class="fas fa-file-lines"></i><span class="txt">Pagini</span>
        </a>
        <a href="{{ route('mobile.analytics.event-types', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.event-types') ? 'active' : '' }}">
          <i class="fas fa-list-check"></i><span class="txt">Tipuri</span>
        </a>
        <a href="{{ route('mobile.analytics.banners', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.banners') ? 'active' : '' }}">
          <i class="fas fa-rectangle-ad"></i><span class="txt">Bannere</span>
        </a>
        <a href="{{ route('mobile.analytics.recent-events', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.recent-events') ? 'active' : '' }}">
          <i class="fas fa-clock-rotate-left"></i><span class="txt">Recente</span>
        </a>
        <a href="{{ route('mobile.analytics.abandon', $mobileQuery) }}" class="{{ request()->routeIs('mobile.analytics.abandon') ? 'active' : '' }}">
          <i class="fas fa-cart-arrow-down"></i><span class="txt">Abandon coș</span>
        </a>
        <a href="{{ route('mobile.crashes', $mobileQuery) }}" class="{{ request()->routeIs('mobile.crashes*') ? 'active' : '' }}">
          <i class="fas fa-bug"></i><span class="txt">Crash-uri</span>
        </a>
        <a href="{{ route('mobile.feedback', $mobileQuery) }}" class="{{ request()->routeIs('mobile.feedback*') ? 'active' : '' }}">
          <i class="fas fa-comment-dots"></i><span class="txt">Feedback</span>
        </a>
        @else
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <i class="fas fa-home"></i><span class="txt">Acasă</span>
        </a>
        <a href="{{ route('rapoarte') }}" class="{{ request()->routeIs('rapoarte', 'rapoarte.comparare') ? 'active' : '' }}">
          <i class="fas fa-chart-line"></i><span class="txt">Rapoarte</span>
        </a>
        <a href="{{ route('istoric') }}" class="{{ request()->routeIs('istoric') ? 'active' : '' }}">
          <i class="fas fa-history"></i><span class="txt">Istoric</span>
        </a>
        <a href="{{ route('operatori') }}" class="{{ request()->routeIs('operatori*') ? 'active' : '' }}">
          <i class="fas fa-users"></i><span class="txt">Operatori</span>
        </a>
        <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}">
          <i class="fas fa-truck"></i><span class="txt">Livrări</span>
        </a>
        <a href="{{ route('trafic') }}" class="{{ request()->routeIs('trafic*') ? 'active' : '' }}">
          <i class="fas fa-network-wired"></i><span class="txt">Trafic</span>
        </a>
        <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari*') ? 'active' : '' }}">
          <i class="fas fa-cog"></i><span class="txt">Setări</span>
        </a>
        @if(auth()->check() && auth()->user()->isAdmin())
        <a href="{{ route('rapoarte.raport-lunar') }}" class="{{ request()->routeIs('rapoarte.raport-lunar') ? 'active' : '' }}">
          <i class="fas fa-file-contract"></i><span class="txt">Raport lunar</span>
        </a>
        @endif
        @if(auth()->check() && auth()->user()->isDev())
        <a href="{{ route('dev-mode.panel') }}" class="{{ request()->routeIs('dev-mode.*') ? 'active' : '' }}">
          <i class="fas fa-code"></i><span class="txt">Dev</span>
        </a>
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users*') ? 'active' : '' }}">
          <i class="fas fa-user-shield"></i><span class="txt">Utilizatori</span>
        </a>
        @endif
        @endif
      </nav>

      <div class="logout-container">
        @if($canAccessMobileModule)
        <a
          href="{{ $isMobileModule ? route('dashboard') : route('mobile.analytics', $mobileQuery) }}"
          class="sidebar-mobile-entry {{ $isMobileModule ? 'sidebar-mobile-entry--back' : 'sidebar-mobile-entry--mobile' }}"
        >
          <i class="fas {{ $isMobileModule ? 'fa-arrow-left' : 'fa-mobile-screen-button' }}"></i>
          <span>{{ $isMobileModule ? 'Înapoi la Dashboard' : 'Volta App' }}</span>
        </a>
        @endif
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
            <h2 class="top-bar-title">VOLTA STATS</h2>
          </div>
        </div>
      </div>

      <!-- TOPBAR - doar pe prima pagină -->
      @if(request()->routeIs('dashboard') || request()->routeIs('datele-mele') || request()->routeIs('mobile.analytics*', 'mobile.crashes*', 'mobile.feedback*'))
      <div class="header">
        <h1>@yield('header-title', request()->routeIs('datele-mele') ? 'Datele mele' : (request()->routeIs('mobile.analytics*', 'mobile.crashes*', 'mobile.feedback*') ? 'Volta App' : 'Dashboard'))</h1>
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

  <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
  <script src="{{ asset('js/excel-export-exceljs.js') }}"></script>
  <script src="{{ asset('js/volta-chart-theme.js') }}"></script>
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
      const logoToggle = document.getElementById('sidebarLogoToggle');
      const collapseStorageKey = 'volta.sidebar.collapsed';
      
      console.log('Hamburger button:', hamburgerBtn);
      console.log('Sidebar:', sidebar);
      console.log('Overlay:', overlay);
      
      if (hamburgerBtn && sidebar && overlay) {
        const applyDesktopCollapsed = (collapsed) => {
          if (window.innerWidth <= 1100) return;
          document.body.classList.toggle('sidebar-collapsed', collapsed);
          document.documentElement.classList.toggle('sidebar-collapsed', collapsed);
        };

        const storedCollapsed = localStorage.getItem(collapseStorageKey) === '1';
        applyDesktopCollapsed(storedCollapsed);

        if (logoToggle) {
          logoToggle.addEventListener('click', function() {
            if (window.innerWidth <= 1100) {
              return;
            }
            const nextCollapsed = !document.body.classList.contains('sidebar-collapsed');
            document.body.classList.toggle('sidebar-collapsed', nextCollapsed);
            document.documentElement.classList.toggle('sidebar-collapsed', nextCollapsed);
            localStorage.setItem(collapseStorageKey, nextCollapsed ? '1' : '0');
          });
        }

        // Asigură că hamburger-ul este vizibil pe mobile
        if (window.innerWidth <= 1100) {
          hamburgerBtn.style.display = 'flex';
          hamburgerBtn.style.visibility = 'visible';
          hamburgerBtn.style.opacity = '1';
          document.body.classList.remove('sidebar-collapsed');
          document.documentElement.classList.remove('sidebar-collapsed');
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
            document.body.classList.remove('sidebar-collapsed');
            document.documentElement.classList.remove('sidebar-collapsed');
          } else {
            hamburgerBtn.style.display = 'none';
            sidebar.classList.remove('open');
            overlay.classList.remove('active');
            hamburgerBtn.classList.remove('active');
            document.body.style.overflow = '';
            applyDesktopCollapsed(localStorage.getItem(collapseStorageKey) === '1');
          }
        });
      } else {
        console.error('Elemente lipsă:', {
          hamburgerBtn: !!hamburgerBtn,
          sidebar: !!sidebar,
          overlay: !!overlay
        });
      }

      window.requestAnimationFrame(function() {
        window.requestAnimationFrame(function() {
          document.documentElement.classList.remove('preload-sidebar-state');
        });
      });
    });

  </script>
</body>
</html>
