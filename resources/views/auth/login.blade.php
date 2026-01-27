<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Logare Dashboard – VOLTA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="login-logo">
        <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="logo-mark">
        <h1>VOLTA Dashboard</h1>
        <p>Autentificare</p>
      </div>
      @if ($errors->any())
        <div class="error" style="background: #ff4444; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
          @foreach ($errors->all() as $error)
            <strong>{{ $error }}</strong>
          @endforeach
        </div>
      @endif
      @if (session('status'))
        <div style="background: #44ff44; color: white; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
          {{ session('status') }}
        </div>
      @endif
      <form action="{{ route('login.post') }}" method="POST" id="loginForm">
        @csrf
        <div class="input-group">
          <input type="text" name="username" id="username" placeholder="Utilizator" required autofocus value="{{ old('username') }}">
        </div>
        <div class="input-group">
          <input type="password" name="password" id="password" placeholder="Parola" required>
        </div>
        <button type="submit" class="btn-login">Autentificare</button>
      </form>
      <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {
          console.log('Form submitted');
          console.log('Username:', document.getElementById('username').value);
        });
      </script>
    </div>
  </div>
</body>
</html>

