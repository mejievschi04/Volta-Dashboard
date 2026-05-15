<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Statistici Mobile - VOLTA')</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('css/style.css') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body.mobile-analytics-layout {
      margin: 0;
      min-height: 100vh;
      font-family: 'Noto Sans', system-ui, sans-serif;
      background: radial-gradient(140% 120% at 15% 0%, #171d28 0%, #0d121b 52%, #070a10 100%);
      color: var(--text-primary, #e5e7eb);
    }
    .mobile-shell {
      max-width: 1560px;
      margin: 0 auto;
      padding: 18px 22px 28px;
      position: relative;
      isolation: isolate;
    }
    .mobile-shell::before {
      content: '';
      position: absolute;
      inset: 10px 4% auto;
      height: 180px;
      border-radius: 999px;
      background: radial-gradient(circle, rgba(255, 238, 0, 0.06) 0%, rgba(255, 238, 0, 0) 72%);
      z-index: -1;
      pointer-events: none;
    }
    .mobile-shell__topbar {
      position: sticky;
      top: 0;
      z-index: 20;
      margin: 0 0 14px;
      background: rgba(8, 12, 18, 0.9);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(148, 163, 184, 0.14);
      border-radius: 16px;
      padding: 10px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    }
    .mobile-shell__brand {
      display: inline-flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }
    .mobile-shell__title {
      margin: 0;
      font-size: 0.95rem;
      font-weight: 700;
      letter-spacing: 0.02em;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .mobile-shell__subtitle {
      display: block;
      margin-top: 2px;
      color: #94a3b8;
      font-size: 0.71rem;
      font-weight: 600;
      letter-spacing: 0.04em;
      text-transform: uppercase;
    }
    .mobile-shell__actions {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      flex-wrap: wrap;
      justify-content: flex-end;
    }
    .mobile-shell__link,
    .mobile-shell__logout {
      appearance: none;
      border: 1px solid rgba(148, 163, 184, 0.22);
      background: rgba(15, 23, 42, 0.52);
      color: #e2e8f0;
      border-radius: 10px;
      min-height: 34px;
      padding: 0 11px;
      font-size: 0.75rem;
      font-weight: 700;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 7px;
      cursor: pointer;
    }
    .mobile-shell__link:hover,
    .mobile-shell__logout:hover {
      border-color: rgba(255, 238, 0, 0.48);
      color: #fff;
    }
    .mobile-shell__module-nav {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin: 0 0 14px;
    }
    .mobile-shell__module-link {
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
      border: 1px solid rgba(148, 163, 184, 0.2);
      background: rgba(15, 23, 42, 0.45);
      color: #cbd5e1;
      border-radius: 999px;
      min-height: 36px;
      padding: 0 12px;
      font-size: 0.78rem;
      font-weight: 700;
      letter-spacing: 0.01em;
      transition: border-color 0.2s ease, color 0.2s ease, background 0.2s ease;
    }
    .mobile-shell__module-link:hover {
      color: #f8fafc;
      border-color: rgba(255, 238, 0, 0.45);
    }
    .mobile-shell__module-link.is-active {
      color: #fff;
      border-color: rgba(255, 238, 0, 0.75);
      background: linear-gradient(180deg, rgba(32, 42, 61, 0.95) 0%, rgba(19, 27, 43, 0.98) 100%);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.3);
    }
    @media (max-width: 760px) {
      .mobile-shell {
        padding: 12px 12px 20px;
      }
      .mobile-shell__topbar {
        border-radius: 12px;
        padding: 8px 10px;
      }
      .mobile-shell__actions {
        gap: 6px;
      }
      .mobile-shell__link,
      .mobile-shell__logout {
        min-height: 32px;
        font-size: 0.6875rem;
      }
    }
  </style>
  @stack('styles')
</head>
<body class="mobile-analytics-layout">
  <div class="mobile-shell">
    @php
      $mobileQuery = request()->only(['start', 'end']);
    @endphp
    <header class="mobile-shell__topbar">
      <div class="mobile-shell__brand">
        <div>
          <h1 class="mobile-shell__title">Statistici Mobile</h1>
          <span class="mobile-shell__subtitle">Spațiu separat de analiză</span>
        </div>
      </div>
      <div class="mobile-shell__actions">
        <a class="mobile-shell__link" href="{{ auth()->check() && auth()->user()->isOperator() ? route('datele-mele') : route('dashboard') }}">
          <i class="fas fa-arrow-left" aria-hidden="true"></i>
          <span>Înapoi în aplicație</span>
        </a>
        <form action="{{ route('logout') }}" method="post">
          @csrf
          <button type="submit" class="mobile-shell__logout">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
            <span>Ieșire</span>
          </button>
        </form>
      </div>
    </header>

    <nav class="mobile-shell__module-nav" aria-label="Navigare Statistici Mobile">
      <a href="{{ route('mobile.analytics', $mobileQuery) }}" class="mobile-shell__module-link {{ request()->routeIs('mobile.analytics') ? 'is-active' : '' }}">
        <i class="fas fa-chart-line" aria-hidden="true"></i>
        <span>Prezentare</span>
      </a>
      <a href="{{ route('mobile.analytics.events', $mobileQuery) }}" class="mobile-shell__module-link {{ request()->routeIs('mobile.analytics.events') ? 'is-active' : '' }}">
        <i class="fas fa-bolt" aria-hidden="true"></i>
        <span>Evenimente</span>
      </a>
      <a href="{{ route('mobile.analytics.funnels', $mobileQuery) }}" class="mobile-shell__module-link {{ request()->routeIs('mobile.analytics.funnels') ? 'is-active' : '' }}">
        <i class="fas fa-filter-circle-dollar" aria-hidden="true"></i>
        <span>Pâlnie conversie</span>
      </a>
    </nav>

    @yield('content')
  </div>

  <script src="{{ asset('js/volta-chart-theme.js') }}"></script>
  @stack('scripts')
</body>
</html>
