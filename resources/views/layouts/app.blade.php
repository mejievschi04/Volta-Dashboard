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
        @if(auth()->check() && auth()->user()->isOperator())
        <a href="{{ route('datele-mele') }}" class="{{ request()->routeIs('datele-mele') ? 'active' : '' }}">
          <i class="fas fa-chart-bar"></i><span class="txt">Datele mele</span>
        </a>
        <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}">
          <i class="fas fa-truck"></i><span class="txt">Livrări</span>
        </a>
        <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari') ? 'active' : '' }}">
          <i class="fas fa-cog"></i><span class="txt">Setări</span>
        </a>
        @else
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
        <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}">
          <i class="fas fa-truck"></i><span class="txt">Livrări</span>
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
        @endif
      </nav>

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

      <!-- TOPBAR - pe dashboard și pe Datele mele (operatori) -->
      @if(request()->routeIs('dashboard') || request()->routeIs('datele-mele'))
      <div class="header">
        <h1>@yield('header-title', request()->routeIs('datele-mele') ? 'Datele mele' : 'Dashboard')</h1>
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

  </script>
</body>
</html>

