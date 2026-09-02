<!DOCTYPE html>
@php
  $uiTheme = 'dark';
@endphp
<html lang="ro" data-theme="{{ $uiTheme }}">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Datele mele – VOLTA STATS')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('css/style.css') }}"/>
  <link rel="stylesheet" href="{{ url('css/operatori.css') }}?v={{ @filemtime(public_path('css/operatori.css')) ?: 0 }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  @stack('styles')
</head>
<body class="operator-portal">
  <div class="operator-app">
    <nav class="operator-nav" aria-label="Navigare principală">
      <div class="operator-nav-inner">
        <a href="{{ route('datele-mele') }}" class="operator-nav-brand">
          <span class="operator-nav-mark">
            <img src="{{ asset('images/volta-logo.png') }}" alt="" width="40" height="40" decoding="async">
          </span>
          <span class="operator-nav-wordmark">
            <span class="operator-nav-wordmark-volta">VOLTA</span>
            <span class="operator-nav-wordmark-stats">STATS</span>
          </span>
        </a>
        <div class="operator-nav-links">
          <a href="{{ route('datele-mele') }}" class="{{ request()->routeIs('datele-mele') ? 'active' : '' }}" title="Datele mele" @if(request()->routeIs('datele-mele')) aria-current="page" @endif>
            <i class="fas fa-chart-bar" aria-hidden="true"></i><span>Datele mele</span>
          </a>
          <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}" title="Livrări" @if(request()->routeIs('livrari*')) aria-current="page" @endif>
            <i class="fas fa-truck" aria-hidden="true"></i><span>Livrări</span>
          </a>
          <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari') ? 'active' : '' }}" title="Setări" @if(request()->routeIs('setari')) aria-current="page" @endif>
            <i class="fas fa-cog" aria-hidden="true"></i><span>Setări</span>
          </a>
        </div>
        <div class="operator-nav-right">
          <div class="operator-user">
            <div class="operator-user-avatar" aria-hidden="true">{{ Auth::check() ? strtoupper(substr(Auth::user()->username, 0, 1)) : 'U' }}</div>
            <div class="operator-user-info">
              <div class="operator-user-name">{{ Auth::check() ? Auth::user()->username : 'User' }}</div>
              <div class="operator-user-role">{{ Auth::check() ? (Auth::user()->role ?? 'Operator') : '' }}</div>
            </div>
          </div>
          <div class="operator-logout">
            <form action="{{ route('logout') }}" method="post">
              @csrf
              <button type="submit"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Ieși</button>
            </form>
          </div>
        </div>
      </div>
    </nav>
    <main class="operator-main" id="operator-main">
      @yield('content')
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/exceljs@4.4.0/dist/exceljs.min.js"></script>
  <script src="{{ asset('js/excel-export-exceljs.js') }}"></script>
  <script src="{{ asset('js/volta-chart-theme.js') }}"></script>
  @stack('scripts')
</body>
</html>
