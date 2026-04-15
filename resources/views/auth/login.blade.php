<!DOCTYPE html>
<html lang="ro" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <title>Logare Dashboard – VOLTA</title>
  <link rel="icon" type="image/png" href="{{ asset('images/volta-logo.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="{{ url('css/login.css') }}">
</head>
<body>
  <canvas id="darkVeilCanvas" class="dark-veil-canvas" aria-hidden="true"></canvas>
  <div class="login-container">
    <div class="login-box">
      <div class="login-logo">
        <div class="logo-wrapper">
          <img src="{{ asset('images/volta-logo.png') }}" alt="VOLTA Logo" class="logo-mark">
        </div>
        <h1>VOLTA STATS</h1>
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

  <p class="login-powered-by" aria-hidden="true">powered by Mejievski</p>

  <div id="loginWelcomeOverlay" class="login-welcome-overlay" hidden aria-hidden="true">
    <div class="login-welcome-inner">
      <p class="login-welcome-line login-welcome-ink" id="loginWelcomeGreeting" aria-hidden="true"></p>
      <p class="login-welcome-name login-welcome-ink" id="loginWelcomeName" aria-hidden="true"></p>
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
    
    // Form submission: efect stele → ecran ca în app (gradient) + text tipar animat → trimitere formular
    let loginSubmitInProgress = false;
    document.getElementById('loginForm').addEventListener('submit', function(e) {
      if (loginSubmitInProgress) return;
      e.preventDefault();

      const btn = this.querySelector('.btn-login');
      btn.classList.add('loading');
      btn.disabled = true;

      const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      const effectMs = reduceMotion ? 80 : 400;

      // Trigger a quick star collapse while the next page loads.
      const transitionState = (window.__voltaLoginTransition = window.__voltaLoginTransition || {
        active: false,
        startAt: 0,
        targetX: 0.5,
        targetY: 0.5
      });
      transitionState.active = true;
      transitionState.startAt = performance.now();
      transitionState.targetX = 0.5;
      transitionState.targetY = 0.5;

      loginSubmitInProgress = true;
      const form = this;
      const overlay = document.getElementById('loginWelcomeOverlay');
      const greetingEl = document.getElementById('loginWelcomeGreeting');
      const nameEl = document.getElementById('loginWelcomeName');
      const usernameInput = document.getElementById('username');
      const greetingText = 'Bine ai revenit,';
      const rawNamePreview = (usernameInput && usernameInput.value) ? usernameInput.value.trim() : '';
      const nameTextPreview = rawNamePreview || 'prietene';
      const totalCharsPreview = Array.from(greetingText).length + Array.from(nameTextPreview).length;
      const staggerPreview = totalCharsPreview > 28 ? 48 : 62;
      const welcomeHoldMsPreview =
        reduceMotion || !overlay || !nameEl || !greetingEl
          ? 0
          : Math.min(4800, Math.max(1500, (totalCharsPreview - 1) * staggerPreview + 520 + 420));
      const failResetMs = Math.max(4000, effectMs + welcomeHoldMsPreview + 2200);

      function hideWelcomeOverlay() {
        if (!overlay) return;
        overlay.classList.remove('is-visible');
        document.body.classList.remove('login-welcome-active');
        if (greetingEl) {
          greetingEl.textContent = '';
          greetingEl.setAttribute('aria-hidden', 'true');
        }
        if (nameEl) {
          nameEl.textContent = '';
          nameEl.setAttribute('aria-hidden', 'true');
        }
        window.setTimeout(function () {
          if (!overlay.classList.contains('is-visible')) {
            overlay.setAttribute('hidden', '');
            overlay.setAttribute('aria-hidden', 'true');
          }
        }, 320);
      }

      function doNativeSubmit() {
        form.submit();
      }

      /**
       * Umple un rând cu litere animate (efect tipărire discret).
       * @returns {number} numărul de caractere adăugate
       */
      function fillInkLine(container, text, startCharIndex, staggerMs, reduced) {
        if (!container) return 0;
        container.textContent = '';
        if (reduced) {
          container.textContent = text;
          return Array.from(text).length;
        }
        var chars = Array.from(text);
        for (var i = 0; i < chars.length; i++) {
          var ch = chars[i];
          var span = document.createElement('span');
          span.className = 'login-type-char';
          span.style.setProperty('--type-delay', (startCharIndex + i) * staggerMs + 'ms');
          span.textContent = ch === ' ' ? '\u00a0' : ch;
          if (ch === ' ') span.classList.add('login-type-space');
          container.appendChild(span);
        }
        return chars.length;
      }

      if (reduceMotion || !overlay || !nameEl || !greetingEl) {
        window.setTimeout(doNativeSubmit, effectMs);
      } else {
        var rawName = (usernameInput && usernameInput.value) ? usernameInput.value.trim() : '';
        var nameText = rawName || 'prietene';
        var totalChars = Array.from(greetingText).length + Array.from(nameText).length;
        var staggerMs = totalChars > 28 ? 48 : 62;
        var strokeMs = 520;
        var tailMs = 420;
        var welcomeHoldMs = Math.min(4800, Math.max(1500, (totalChars - 1) * staggerMs + strokeMs + tailMs));

        window.setTimeout(function () {
          fillInkLine(greetingEl, greetingText, 0, staggerMs, false);
          var nG = Array.from(greetingText).length;
          fillInkLine(nameEl, nameText, nG, staggerMs, false);
          greetingEl.setAttribute('aria-hidden', 'false');
          nameEl.setAttribute('aria-hidden', 'false');
          overlay.removeAttribute('hidden');
          overlay.setAttribute('aria-hidden', 'false');
          document.body.classList.add('login-welcome-active');
          requestAnimationFrame(function () {
            overlay.classList.add('is-visible');
          });
        }, effectMs);

        window.setTimeout(doNativeSubmit, effectMs + welcomeHoldMs);
      }

      // If login fails and we stay on page, allow retry (după ce s-a terminat animația + timp server).
      window.setTimeout(function () {
        if (document.contains(form)) {
          loginSubmitInProgress = false;
          btn.classList.remove('loading');
          btn.disabled = false;
          transitionState.active = false;
          hideWelcomeOverlay();
        }
      }, failResetMs);
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

    // Galaxy background effect (React Bits shader port) with VOLTA yellow
    (function initGalaxy() {
      const canvas = document.getElementById('darkVeilCanvas');
      if (!canvas) return;

      const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (reduceMotion) return;

      const gl = canvas.getContext('webgl', { alpha: true, premultipliedAlpha: false, antialias: false });
      if (!gl) return;

      const vertexShaderSource = `
        attribute vec2 uv;
        attribute vec2 position;
        varying vec2 vUv;
        void main() {
          vUv = uv;
          gl_Position = vec4(position, 0.0, 1.0);
        }
      `;

      const fragmentShaderSource = `
        precision highp float;
        uniform float uTime;
        uniform vec3 uResolution;
        uniform vec2 uFocal;
        uniform vec2 uRotation;
        uniform float uStarSpeed;
        uniform float uDensity;
        uniform float uHueShift;
        uniform float uSpeed;
        uniform vec2 uMouse;
        uniform float uGlowIntensity;
        uniform float uSaturation;
        uniform bool uMouseRepulsion;
        uniform float uTwinkleIntensity;
        uniform float uRotationSpeed;
        uniform float uRepulsionStrength;
        uniform float uMouseActiveFactor;
        uniform float uAutoCenterRepulsion;
        uniform bool uTransparent;
        varying vec2 vUv;
        #define NUM_LAYER 4.0
        #define STAR_COLOR_CUTOFF 0.2
        #define MAT45 mat2(0.7071, -0.7071, 0.7071, 0.7071)
        #define PERIOD 3.0

        float Hash21(vec2 p) {
          p = fract(p * vec2(123.34, 456.21));
          p += dot(p, p + 45.32);
          return fract(p.x * p.y);
        }
        float tri(float x) { return abs(fract(x) * 2.0 - 1.0); }
        float tris(float x) {
          float t = fract(x);
          return 1.0 - smoothstep(0.0, 1.0, abs(2.0 * t - 1.0));
        }
        float trisn(float x) {
          float t = fract(x);
          return 2.0 * (1.0 - smoothstep(0.0, 1.0, abs(2.0 * t - 1.0))) - 1.0;
        }
        vec3 hsv2rgb(vec3 c) {
          vec4 K = vec4(1.0, 2.0 / 3.0, 1.0 / 3.0, 3.0);
          vec3 p = abs(fract(c.xxx + K.xyz) * 6.0 - K.www);
          return c.z * mix(K.xxx, clamp(p - K.xxx, 0.0, 1.0), c.y);
        }
        float Star(vec2 uv, float flare) {
          float d = length(uv);
          float m = (0.05 * uGlowIntensity) / d;
          float rays = smoothstep(0.0, 1.0, 1.0 - abs(uv.x * uv.y * 1000.0));
          m += rays * flare * uGlowIntensity;
          uv *= MAT45;
          rays = smoothstep(0.0, 1.0, 1.0 - abs(uv.x * uv.y * 1000.0));
          m += rays * 0.3 * flare * uGlowIntensity;
          m *= smoothstep(1.0, 0.2, d);
          return m;
        }
        vec3 StarLayer(vec2 uv) {
          vec3 col = vec3(0.0);
          vec2 gv = fract(uv) - 0.5;
          vec2 id = floor(uv);
          for (int y = -1; y <= 1; y++) {
            for (int x = -1; x <= 1; x++) {
              vec2 offset = vec2(float(x), float(y));
              vec2 si = id + vec2(float(x), float(y));
              float seed = Hash21(si);
              float size = fract(seed * 345.32);
              float glossLocal = tri(uStarSpeed / (PERIOD * seed + 1.0));
              float flareSize = smoothstep(0.9, 1.0, size) * glossLocal;
              float whiteMix = smoothstep(0.35, 0.9, Hash21(si + 7.0));
              vec3 yellow = vec3(1.0, 0.933, 0.0); // #ffee00
              vec3 white = vec3(1.0, 1.0, 1.0);
              vec3 base = mix(yellow, white, whiteMix);
              vec2 pad = vec2(tris(seed * 34.0 + uTime * uSpeed / 10.0), tris(seed * 38.0 + uTime * uSpeed / 30.0)) - 0.5;
              float star = Star(gv - offset - pad, flareSize);
              float twinkle = trisn(uTime * uSpeed + seed * 6.2831) * 0.5 + 1.0;
              twinkle = mix(1.0, twinkle, uTwinkleIntensity);
              star *= twinkle;
              col += star * size * base;
            }
          }
          return col;
        }
        void main() {
          vec2 focalPx = uFocal * uResolution.xy;
          vec2 uv = (vUv * uResolution.xy - focalPx) / uResolution.y;
          vec2 mouseNorm = uMouse - vec2(0.5);
          if (uAutoCenterRepulsion > 0.0) {
            vec2 centerUV = vec2(0.0, 0.0);
            float centerDist = length(uv - centerUV);
            vec2 attraction = normalize(centerUV - uv) * (uAutoCenterRepulsion / (centerDist + 0.1));
            uv += attraction * 0.05;
          } else if (uMouseRepulsion) {
            vec2 mousePosUV = (uMouse * uResolution.xy - focalPx) / uResolution.y;
            float mouseDist = length(uv - mousePosUV);
            vec2 repulsion = normalize(uv - mousePosUV) * (uRepulsionStrength / (mouseDist + 0.1));
            uv += repulsion * 0.05 * uMouseActiveFactor;
          } else {
            vec2 mouseOffset = mouseNorm * 0.1 * uMouseActiveFactor;
            uv += mouseOffset;
          }
          float autoRotAngle = uTime * uRotationSpeed;
          mat2 autoRot = mat2(cos(autoRotAngle), -sin(autoRotAngle), sin(autoRotAngle), cos(autoRotAngle));
          uv = autoRot * uv;
          uv = mat2(uRotation.x, -uRotation.y, uRotation.y, uRotation.x) * uv;
          vec3 col = vec3(0.0);
          for (float i = 0.0; i < 1.0; i += 1.0 / NUM_LAYER) {
            float depth = fract(i + uStarSpeed * uSpeed);
            float scale = mix(20.0 * uDensity, 0.5 * uDensity, depth);
            float fade = depth * smoothstep(1.0, 0.9, depth);
            col += StarLayer(uv * scale + i * 453.32) * fade;
          }
          if (uTransparent) {
            float alpha = length(col);
            alpha = smoothstep(0.0, 0.3, alpha);
            alpha = min(alpha, 1.0);
            gl_FragColor = vec4(col, alpha);
          } else {
            gl_FragColor = vec4(col, 1.0);
          }
        }
      `;

      function compileShader(type, src) {
        const shader = gl.createShader(type);
        if (!shader) return null;
        gl.shaderSource(shader, src);
        gl.compileShader(shader);
        if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) {
          console.error(gl.getShaderInfoLog(shader));
          gl.deleteShader(shader);
          return null;
        }
        return shader;
      }

      const vs = compileShader(gl.VERTEX_SHADER, vertexShaderSource);
      const fs = compileShader(gl.FRAGMENT_SHADER, fragmentShaderSource);
      if (!vs || !fs) return;

      const program = gl.createProgram();
      if (!program) return;
      gl.attachShader(program, vs);
      gl.attachShader(program, fs);
      gl.linkProgram(program);
      if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return;
      gl.useProgram(program);

      const vertices = new Float32Array([
        -1, -1, 0, 0,
         1, -1, 1, 0,
        -1,  1, 0, 1,
        -1,  1, 0, 1,
         1, -1, 1, 0,
         1,  1, 1, 1
      ]);

      const stride = 4 * Float32Array.BYTES_PER_ELEMENT;
      const buffer = gl.createBuffer();
      gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
      gl.bufferData(gl.ARRAY_BUFFER, vertices, gl.STATIC_DRAW);

      const posLoc = gl.getAttribLocation(program, 'position');
      const uvLoc = gl.getAttribLocation(program, 'uv');
      gl.enableVertexAttribArray(posLoc);
      gl.vertexAttribPointer(posLoc, 2, gl.FLOAT, false, stride, 0);
      gl.enableVertexAttribArray(uvLoc);
      gl.vertexAttribPointer(uvLoc, 2, gl.FLOAT, false, stride, 2 * Float32Array.BYTES_PER_ELEMENT);

      const uniforms = {
        uTime: gl.getUniformLocation(program, 'uTime'),
        uResolution: gl.getUniformLocation(program, 'uResolution'),
        uFocal: gl.getUniformLocation(program, 'uFocal'),
        uRotation: gl.getUniformLocation(program, 'uRotation'),
        uStarSpeed: gl.getUniformLocation(program, 'uStarSpeed'),
        uDensity: gl.getUniformLocation(program, 'uDensity'),
        uHueShift: gl.getUniformLocation(program, 'uHueShift'),
        uSpeed: gl.getUniformLocation(program, 'uSpeed'),
        uMouse: gl.getUniformLocation(program, 'uMouse'),
        uGlowIntensity: gl.getUniformLocation(program, 'uGlowIntensity'),
        uSaturation: gl.getUniformLocation(program, 'uSaturation'),
        uMouseRepulsion: gl.getUniformLocation(program, 'uMouseRepulsion'),
        uTwinkleIntensity: gl.getUniformLocation(program, 'uTwinkleIntensity'),
        uRotationSpeed: gl.getUniformLocation(program, 'uRotationSpeed'),
        uRepulsionStrength: gl.getUniformLocation(program, 'uRepulsionStrength'),
        uMouseActiveFactor: gl.getUniformLocation(program, 'uMouseActiveFactor'),
        uAutoCenterRepulsion: gl.getUniformLocation(program, 'uAutoCenterRepulsion'),
        uTransparent: gl.getUniformLocation(program, 'uTransparent')
      };

      let rafId = null;
      const loginTransition = (window.__voltaLoginTransition = window.__voltaLoginTransition || {
        active: false,
        startAt: 0,
        targetX: 0.5,
        targetY: 0.5
      });
      const targetMouse = { x: 0.5, y: 0.5, active: 0.0 };
      const smoothMouse = { x: 0.5, y: 0.5, active: 0.0 };
      const cfg = {
        focalX: 0.5,
        focalY: 0.5,
        rotationX: 1.0,
        rotationY: 0.0,
        starSpeed: 0.38,
        density: 0.82,
        hueShift: 52.0, // closer to VOLTA yellow (#ffee00)
        speed: 0.72,
        glowIntensity: 0.24,
        saturation: 1.1,
        mouseRepulsion: false,
        twinkleIntensity: 0.12,
        rotationSpeed: 0.045,
        repulsionStrength: 2.0,
        autoCenterRepulsion: 0.0,
        transparent: true
      };

      function resize() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        const width = window.innerWidth;
        const height = window.innerHeight;
        canvas.width = Math.floor(width * dpr);
        canvas.height = Math.floor(height * dpr);
        canvas.style.width = width + 'px';
        canvas.style.height = height + 'px';
        gl.viewport(0, 0, canvas.width, canvas.height);
        if (uniforms.uResolution) gl.uniform3f(uniforms.uResolution, canvas.width, canvas.height, canvas.width / canvas.height);
      }

      function frame(time) {
        const t = time * 0.001;
        const lerp = 0.05;
        smoothMouse.x += (targetMouse.x - smoothMouse.x) * lerp;
        smoothMouse.y += (targetMouse.y - smoothMouse.y) * lerp;
        smoothMouse.active += (targetMouse.active - smoothMouse.active) * lerp;

        let focalX = cfg.focalX;
        let focalY = cfg.focalY;
        let density = cfg.density;
        let glowIntensity = cfg.glowIntensity;
        let twinkleIntensity = cfg.twinkleIntensity;
        let rotationSpeed = cfg.rotationSpeed;
        let autoCenterRepulsion = cfg.autoCenterRepulsion;
        let starSpeed = cfg.starSpeed;
        let speed = cfg.speed;
        let mouseActiveFactor = smoothMouse.active;

        if (loginTransition.active) {
          const elapsed = Math.max(0, (performance.now() - loginTransition.startAt) / 1000);
          const progress = Math.min(1, elapsed / 0.36);
          const easeOut = 1 - Math.pow(1 - progress, 3);
          const easeInOut = progress < 0.5
            ? 4 * progress * progress * progress
            : 1 - Math.pow(-2 * progress + 2, 3) / 2;

          focalX = cfg.focalX + (loginTransition.targetX - cfg.focalX) * easeInOut;
          focalY = cfg.focalY + (loginTransition.targetY - cfg.focalY) * easeInOut;
          density = cfg.density * (1 - easeInOut * 0.86);
          glowIntensity = cfg.glowIntensity * (1 + easeInOut * 0.55);
          twinkleIntensity = cfg.twinkleIntensity * (1 - easeInOut * 0.92);
          rotationSpeed = cfg.rotationSpeed * (1 + easeInOut * 2.6);
          autoCenterRepulsion = 9.0 * easeInOut;
          starSpeed = cfg.starSpeed * (1 + easeInOut * 5.3);
          speed = cfg.speed * (1 + easeInOut * 5.6);
          mouseActiveFactor = 0.0;
        }

        gl.clear(gl.COLOR_BUFFER_BIT);
        if (uniforms.uTime) gl.uniform1f(uniforms.uTime, t);
        if (uniforms.uFocal) gl.uniform2f(uniforms.uFocal, focalX, focalY);
        if (uniforms.uRotation) gl.uniform2f(uniforms.uRotation, cfg.rotationX, cfg.rotationY);
        if (uniforms.uStarSpeed) gl.uniform1f(uniforms.uStarSpeed, (t * starSpeed) / 10.0);
        if (uniforms.uDensity) gl.uniform1f(uniforms.uDensity, density);
        if (uniforms.uHueShift) gl.uniform1f(uniforms.uHueShift, cfg.hueShift);
        if (uniforms.uSpeed) gl.uniform1f(uniforms.uSpeed, speed);
        if (uniforms.uMouse) gl.uniform2f(uniforms.uMouse, smoothMouse.x, smoothMouse.y);
        if (uniforms.uGlowIntensity) gl.uniform1f(uniforms.uGlowIntensity, glowIntensity);
        if (uniforms.uSaturation) gl.uniform1f(uniforms.uSaturation, cfg.saturation);
        if (uniforms.uMouseRepulsion) gl.uniform1i(uniforms.uMouseRepulsion, cfg.mouseRepulsion ? 1 : 0);
        if (uniforms.uTwinkleIntensity) gl.uniform1f(uniforms.uTwinkleIntensity, twinkleIntensity);
        if (uniforms.uRotationSpeed) gl.uniform1f(uniforms.uRotationSpeed, rotationSpeed);
        if (uniforms.uRepulsionStrength) gl.uniform1f(uniforms.uRepulsionStrength, cfg.repulsionStrength);
        if (uniforms.uMouseActiveFactor) gl.uniform1f(uniforms.uMouseActiveFactor, mouseActiveFactor);
        if (uniforms.uAutoCenterRepulsion) gl.uniform1f(uniforms.uAutoCenterRepulsion, autoCenterRepulsion);
        if (uniforms.uTransparent) gl.uniform1i(uniforms.uTransparent, cfg.transparent ? 1 : 0);

        gl.drawArrays(gl.TRIANGLES, 0, 6);
        rafId = requestAnimationFrame(frame);
      }

      gl.enable(gl.BLEND);
      gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
      gl.clearColor(0, 0, 0, 0);

      resize();
      window.addEventListener('resize', resize, { passive: true });
      rafId = requestAnimationFrame(frame);

      window.addEventListener('beforeunload', function () {
        if (rafId) cancelAnimationFrame(rafId);
      });
    })();
  </script>
</body>
</html>

