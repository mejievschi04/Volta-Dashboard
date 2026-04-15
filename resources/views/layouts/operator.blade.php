<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Datele mele – VOLTA')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('css/style.css') }}"/>
  <link rel="stylesheet" href="{{ url('css/operatori.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* Layout operator: bară meniu sus, fără sidebar — aceleași tokeni ca Volta Academy */
    .operator-app { min-height: 100vh; background: transparent; color: var(--text-primary); }
    .operator-nav {
      position: sticky; top: 0; z-index: 100;
      display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap;
      padding: 12px 24px; gap: 16px;
      background: var(--bg-sidebar-toggle);
      border-bottom: 1px solid var(--border-primary);
      box-shadow: 0 2px 16px rgba(0,0,0,0.25);
    }
    .operator-nav-brand {
      display: flex; align-items: center; gap: 12px; text-decoration: none; color: var(--text-primary);
    }
    .operator-nav-brand img { height: 40px; width: auto; }
    .operator-nav-brand span { font-weight: 700; font-size: 18px; letter-spacing: -0.02em; }
    .operator-nav-links { display: flex; align-items: center; gap: 8px; }
    .operator-nav-links a {
      padding: 10px 18px; border-radius: 12px; text-decoration: none; font-weight: 500; font-size: 14px;
      color: var(--text-secondary); transition: background-color 0.2s, color 0.2s;
      border: none;
    }
    .operator-nav-links a { font-weight: 600; line-height: 1.2; }
    .operator-nav-links a:hover { background: var(--bg-secondary); color: var(--text-primary); }
    .operator-nav-links a.active { background: var(--brand-10); color: var(--brand); }
    .operator-nav-right { display: flex; align-items: center; gap: 16px; }
    .operator-user { display: flex; align-items: center; gap: 10px; }
    .operator-user-avatar {
      width: 40px; height: 40px; border-radius: 50%; background: var(--brand);
      color: var(--text-inverse); font-weight: 700; font-size: 16px; display: flex; align-items: center; justify-content: center;
      border: 1px solid var(--border-secondary);
    }
    .operator-user-info { text-align: right; }
    .operator-user-name { font-weight: 600; font-size: 14px; color: var(--text-primary); }
    .operator-user-role { font-size: 12px; color: var(--text-tertiary); }
    .operator-logout form { margin: 0; }
    .operator-logout button {
      padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 13px; cursor: pointer;
      background: rgba(239, 68, 68, 0.2); color: #F87171; border: 1px solid rgba(239, 68, 68, 0.3);
      transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;
    }
    .operator-logout button:hover { background: rgba(239, 68, 68, 0.35); color: #fff; }
    .operator-main { padding: 24px clamp(16px, 3vw, 32px); max-width: 1600px; margin: 0 auto; }
    @media (max-width: 768px) {
      .operator-nav { padding: 10px 16px; }
      .operator-nav-brand span { font-size: 16px; }
      .operator-nav-links a { padding: 8px 12px; font-size: 13px; }
      .operator-main { padding: 16px; }
    }
  </style>
  @stack('styles')
</head>
<body>
  <div class="operator-app">
    <nav class="operator-nav">
      <a href="{{ route('datele-mele') }}" class="operator-nav-brand">
        <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA">
        <span>VOLTA Dashboard</span>
      </a>
      <div class="operator-nav-links">
        <a href="{{ route('datele-mele') }}" class="{{ request()->routeIs('datele-mele') ? 'active' : '' }}">
          <i class="fas fa-chart-bar" style="margin-right: 6px;"></i>Datele mele
        </a>
        <a href="{{ route('livrari') }}" class="{{ request()->routeIs('livrari*') ? 'active' : '' }}">
          <i class="fas fa-truck" style="margin-right: 6px;"></i>Livrări
        </a>
        <a href="{{ route('setari') }}" class="{{ request()->routeIs('setari') ? 'active' : '' }}">
          <i class="fas fa-cog" style="margin-right: 6px;"></i>Setări
        </a>
      </div>
      <div class="operator-nav-right">
        <div class="operator-user">
          <div class="operator-user-avatar">{{ Auth::check() ? strtoupper(substr(Auth::user()->username, 0, 1)) : 'U' }}</div>
          <div class="operator-user-info">
            <div class="operator-user-name">{{ Auth::check() ? Auth::user()->username : 'User' }}</div>
            <div class="operator-user-role">{{ Auth::check() ? (Auth::user()->role ?? 'Operator') : '' }}</div>
          </div>
        </div>
        <div class="operator-logout">
          <form action="{{ route('logout') }}" method="post">
            @csrf
            <button type="submit"><i class="fas fa-sign-out-alt"></i> Ieși</button>
          </form>
        </div>
      </div>
    </nav>
    <main class="operator-main">
      @yield('content')
    </main>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <script src="{{ asset('js/excel-export.js') }}"></script>
  <script src="{{ asset('js/volta-chart-theme.js') }}"></script>
  @stack('scripts')
</body>
</html>
