<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VOLTA - Dev Mode</title>
  <style>
    :root { color-scheme: dark; }
    body {
      margin: 0;
      min-height: 100vh;
      background: #0f172a;
      color: #e5e7eb;
      font-family: "Noto Sans", system-ui, sans-serif;
      display: grid;
      place-items: center;
      padding: 24px;
    }
    main {
      width: min(720px, 100%);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 18px;
      background: linear-gradient(165deg, #1e293b 0%, #111827 100%);
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
      overflow: hidden;
    }
    header { padding: 28px 32px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
    h1 { margin: 0 0 8px; font-size: 1.6rem; color: #fff; }
    p { margin: 0; color: #cbd5e1; line-height: 1.55; }
    section { padding: 28px 32px; display: grid; gap: 18px; }
    .status {
      padding: 18px;
      border-radius: 14px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .status strong { color: #ffee00; }
    .flash {
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(16, 185, 129, 0.14);
      color: #34d399;
      border: 1px solid rgba(16, 185, 129, 0.28);
    }
    label { display: grid; gap: 8px; color: #cbd5e1; font-weight: 700; }
    textarea {
      min-height: 92px;
      resize: vertical;
      border: 1px solid rgba(255, 255, 255, 0.12);
      border-radius: 12px;
      background: rgba(15, 23, 42, 0.8);
      color: #fff;
      padding: 12px;
      font: inherit;
    }
    .actions { display: flex; flex-wrap: wrap; gap: 12px; }
    button, a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-height: 44px;
      padding: 0 18px;
      border-radius: 10px;
      border: 0;
      font-weight: 800;
      text-decoration: none;
      cursor: pointer;
    }
    .primary { background: #ffee00; color: #111827; }
    .danger { background: #ef4444; color: #fff; }
    .ghost { background: rgba(255, 255, 255, 0.07); color: #e5e7eb; }
    .warning {
      padding: 12px 14px;
      border-radius: 12px;
      background: rgba(245, 158, 11, 0.14);
      color: #fbbf24;
      border: 1px solid rgba(245, 158, 11, 0.28);
    }
  </style>
</head>
<body>
  <main>
    <header>
      <h1>Dev Mode</h1>
      <p>Controleaza blocarea temporara a platformei pentru utilizatori.</p>
    </header>
    <section>
      @if(session('status'))
        <div class="flash">{{ session('status') }}</div>
      @endif

      <div class="status">
        Status:
        <strong>{{ $state['enabled'] ? 'ACTIV' : 'INACTIV' }}</strong>
        @if($state['enabled'])
          <p>Activat la {{ $state['enabled_at'] }} de {{ $state['enabled_by_username'] ?? 'necunoscut' }}.</p>
        @endif
      </div>

      @if($state['enabled'])
        <form method="POST" action="{{ route('dev-mode.disable') }}">
          @csrf
          <div class="actions">
            <button type="submit" class="danger">Dezactiveaza modul dev</button>
            <a href="{{ route('login') }}" class="ghost">Login</a>
          </div>
        </form>
      @else
        <form method="POST" action="{{ route('dev-mode.enable') }}">
          @csrf
          <label>
            Mesaj optional pentru utilizatori
            <textarea name="message" maxlength="180" placeholder="Ex: Revenim in cateva minute."></textarea>
          </label>
          <div class="actions">
            <button type="submit" class="primary">Activeaza modul dev</button>
            <a href="{{ route('dashboard') }}" class="ghost">Inapoi in platforma</a>
          </div>
        </form>
      @endif
    </section>
  </main>
</body>
</html>
