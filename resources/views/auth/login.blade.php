<!DOCTYPE html>
<html lang="ro">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Logare Dashboard – VOLTA</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <div class="login-logo">
        <div class="logo-wrapper">
          <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="logo-mark">
        </div>
        <h1>VOLTA Dashboard</h1>
        <p>Bine ai revenit! Te rugăm să te autentifici</p>
      </div>
      
      @if ($errors->any())
        <div class="alert alert-error">
          <i class="fas fa-exclamation-circle"></i>
          <div class="alert-content">
            @foreach ($errors->all() as $error)
              <strong>{{ $error }}</strong>
            @endforeach
          </div>
        </div>
      @endif
      
      @if (session('status'))
        <div class="alert alert-success">
          <i class="fas fa-check-circle"></i>
          <div class="alert-content">
            {{ session('status') }}
          </div>
        </div>
      @endif
      
      <form action="{{ route('login.post') }}" method="POST" id="loginForm">
        @csrf
        <div class="input-group">
          <i class="fas fa-user input-icon"></i>
          <input type="text" name="username" id="username" placeholder="Utilizator" required autofocus value="{{ old('username') }}">
          <div class="input-focus-line"></div>
        </div>
        
        <div class="input-group">
          <i class="fas fa-lock input-icon"></i>
          <input type="password" name="password" id="password" placeholder="Parola" required>
          <button type="button" class="password-toggle" id="passwordToggle" aria-label="Afișează parola">
            <i class="fas fa-eye"></i>
          </button>
          <div class="input-focus-line"></div>
        </div>
        
        <button type="submit" class="btn-login">
          <span class="btn-text">Autentificare</span>
          <i class="fas fa-arrow-right btn-icon"></i>
          <div class="btn-shine"></div>
        </button>
      </form>
    </div>
    
    <div class="floating-particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>
  </div>
  
  <script>
    // Password toggle functionality
    const passwordToggle = document.getElementById('passwordToggle');
    const passwordInput = document.getElementById('password');
    
    if (passwordToggle && passwordInput) {
      passwordToggle.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        
        const icon = this.querySelector('i');
        icon.classList.toggle('fa-eye');
        icon.classList.toggle('fa-eye-slash');
      });
    }
    
    // Form submission
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      const btn = this.querySelector('.btn-login');
      btn.classList.add('loading');
      btn.disabled = true;
    });
    
    // Input focus animations
    const inputs = document.querySelectorAll('.input-group input');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        this.parentElement.classList.add('focused');
      });
      
      input.addEventListener('blur', function() {
        if (!this.value) {
          this.parentElement.classList.remove('focused');
        }
      });
      
      // Check if input has value on load
      if (input.value) {
        input.parentElement.classList.add('focused');
      }
    });
  </script>
</body>
</html>

