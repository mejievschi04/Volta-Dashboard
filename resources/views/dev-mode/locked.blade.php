<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VOLTA - Mod dezvoltare</title>
  <style>
    :root { color-scheme: dark; }
    body {
      margin: 0;
      min-height: 100vh;
      display: grid;
      place-items: center;
      background: #0f172a;
      color: #e5e7eb;
      font-family: "Noto Sans", system-ui, sans-serif;
    }
    main {
      width: min(560px, calc(100% - 32px));
      padding: 36px;
      border: 1px solid rgba(255, 238, 0, 0.18);
      border-radius: 18px;
      background: linear-gradient(165deg, #1e293b 0%, #111827 100%);
      box-shadow: 0 24px 64px rgba(0, 0, 0, 0.45);
      text-align: center;
    }
    .mark {
      width: 64px;
      height: 64px;
      margin: 0 auto 18px;
      display: grid;
      place-items: center;
      border-radius: 16px;
      background: rgba(255, 238, 0, 0.12);
      color: #ffee00;
      font-size: 30px;
      font-weight: 800;
    }
    h1 { margin: 0 0 10px; font-size: 1.75rem; color: #fff; }
    p { margin: 0; color: #cbd5e1; line-height: 1.6; }
    .message {
      margin-top: 18px;
      padding: 14px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.06);
      color: #f8fafc;
    }
  </style>
</head>
<body>
  <main>
    <div class="mark">V</div>
    <h1>Platforma este in mod dezvoltare</h1>
    <p>Lucram la actualizari. Accesul este temporar blocat.</p>
    @if(!empty($state['message']))
      <div class="message">{{ $state['message'] }}</div>
    @endif
  </main>
</body>
</html>
