@extends('layouts.app')

@section('title', 'Adaugă Utilizator – VOLTA')

@section('content')
<div style="padding: 20px; width: 100%; box-sizing: border-box;">
  <div style="margin-bottom: 30px;">
    <a href="{{ route('users.index') }}" style="color: #FFEE00; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; transition: color 0.2s;" onmouseover="this.style.color='#FFEE00'" onmouseout="this.style.color='#FFEE00'">
      <i class="fas fa-arrow-left"></i> Înapoi la Utilizatori
    </a>
  </div>
  
  <div style="margin-bottom: 30px;">
    <h1 style="color: #FFEE00; margin: 0; font-size: 32px; font-weight: 800; display: flex; align-items: center; gap: 12px;">
      <div style="width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center;">
        <i class="fas fa-user-plus" style="color: #000; font-size: 20px;"></i>
      </div>
      Adaugă Utilizator Nou
    </h1>
    <p style="color: #9CA3AF; margin: 10px 0 0 60px; font-size: 14px;">Completează formularul pentru a adăuga un nou utilizator în sistem</p>
  </div>

  @if($errors->any())
  <div style="background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); color: #FFFFFF; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
    <div style="display: flex; align-items: center; margin-bottom: 12px;">
      <i class="fas fa-exclamation-circle" style="margin-right: 8px; font-size: 20px;"></i>
      <strong>Erori de validare:</strong>
    </div>
    <ul style="margin: 0; padding-left: 28px; list-style: disc;">
      @foreach($errors->all() as $error)
      <li style="margin-bottom: 4px;">{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="operator-card" style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 1px solid rgba(255, 238, 0, 0.3); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4);">
    <form action="{{ route('users.store') }}" method="POST">
      @csrf
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; margin-bottom: 30px;">
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-user" style="margin-right: 6px;"></i>Username *
          </label>
          <input type="text" name="username" value="{{ old('username') }}" required 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Introduceți username-ul">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-id-card" style="margin-right: 6px;"></i>Nume (afișare)
          </label>
          <input type="text" name="name" value="{{ old('name') }}" 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Nume afișat">
        </div>
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-user-check" style="margin-right: 6px;"></i>Full name (nume complet)
          </label>
          <input type="text" name="full_name" value="{{ old('full_name') }}" 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Nume complet folosit pentru profil">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-envelope" style="margin-right: 6px;"></i>Email
          </label>
          <input type="email" name="email" value="{{ old('email') }}" 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="exemplu@email.com">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-shield-alt" style="margin-right: 6px;"></i>Rol
          </label>
          <select name="role" id="role"
                  style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s; cursor: pointer;"
                  onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                  onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'">
            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>User</option>
            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
            <option value="dev" {{ old('role') === 'dev' ? 'selected' : '' }}>Dev</option>
            <option value="operator" {{ old('role') === 'operator' ? 'selected' : '' }}>Operator</option>
          </select>
        </div>

        <div id="operator-nume-wrap" style="display: none;">
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-user-tag" style="margin-right: 6px;"></i>Nume operator *
          </label>
          <input type="text" name="operator_nume" value="{{ old('operator_nume') }}" 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Numele asociat utilizatorului">
          <p style="color: #9CA3AF; font-size: 12px; margin-top: 6px; margin-bottom: 0;">
            <i class="fas fa-info-circle" style="margin-right: 4px;"></i>Trebuie să coincidă cu numele operatorului asociat contului.
          </p>
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-lock" style="margin-right: 6px;"></i>Parolă *
          </label>
          <input type="password" name="password" required 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Minim 6 caractere">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 8px; font-weight: 700; font-size: 14px;">
            <i class="fas fa-lock" style="margin-right: 6px;"></i>Confirmă Parola *
          </label>
          <input type="password" name="password_confirmation" required 
                 style="width: 100%; padding: 12px 16px; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 14px; transition: all 0.2s;"
                 onfocus="this.style.borderColor='#FFEE00'; this.style.background='rgba(255, 238, 0, 0.15)'"
                 onblur="this.style.borderColor='rgba(255, 255, 255, 0.1)'; this.style.background='rgba(255, 255, 255, 0.05)'"
                 placeholder="Reintroduceți parola">
        </div>
      </div>

      <div style="display: flex; gap: 12px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.1);">
        <button type="submit" 
                style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 14px 28px; border: none; border-radius: 10px; font-weight: 700; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.35); transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;"
                onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 238, 0, 0.4)'"
                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 238, 0, 0.3)'">
          <i class="fas fa-save"></i> Salvează Utilizator
        </button>
        <a href="{{ route('users.index') }}" 
           style="background: rgba(255, 255, 255, 0.05); color: #fff; padding: 14px 28px; border-radius: 10px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 8px; border: 1px solid rgba(255, 255, 255, 0.1); transition: all 0.2s;"
           onmouseover="this.style.background='rgba(255, 255, 255, 0.1)'"
           onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'">
          <i class="fas fa-times"></i> Anulează
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
<style>
@media (max-width: 768px) {
  div[style*="padding: 20px"] {
    padding: 12px !important;
    padding-top: 75px !important;
  }
  
  div[style*="margin-bottom: 30px"] {
    margin-bottom: 20px !important;
  }
  
  h1[style*="font-size: 32px"] {
    font-size: 18px !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
  }
  
  div[style*="width: 48px; height: 48px"] {
    width: 32px !important;
    height: 32px !important;
  }
  
  i[style*="font-size: 20px"] {
    font-size: 14px !important;
  }
  
  p[style*="font-size: 14px"] {
    font-size: 11px !important;
    margin-left: 0 !important;
    margin-top: 8px !important;
  }
  
  div[style*="padding: 16px"] {
    padding: 12px !important;
    font-size: 12px !important;
  }
  
  .operator-card {
    padding: 15px !important;
  }
  
  div[style*="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))"] {
    grid-template-columns: 1fr !important;
    gap: 15px !important;
    margin-bottom: 20px !important;
  }
  
  label[style*="font-size: 14px"] {
    font-size: 11px !important;
    margin-bottom: 6px !important;
  }
  
  label[style*="font-size: 14px"] i {
    font-size: 10px !important;
    margin-right: 4px !important;
  }
  
  input[style*="padding: 12px 16px"],
  select[style*="padding: 12px 16px"] {
    padding: 10px 12px !important;
    font-size: 13px !important;
    min-height: 44px !important;
  }
  
  div[style*="display: flex; gap: 12px"] {
    flex-direction: column !important;
    gap: 10px !important;
    padding-top: 15px !important;
  }
  
  button[style*="padding: 14px 28px"],
  a[style*="padding: 14px 28px"] {
    width: 100% !important;
    padding: 10px 16px !important;
    font-size: 12px !important;
    justify-content: center !important;
  }
  
  button[style*="padding: 14px 28px"] i,
  a[style*="padding: 14px 28px"] i {
    font-size: 11px !important;
  }
}

@media (max-width: 480px) {
  div[style*="padding: 20px"] {
    padding: 10px !important;
    padding-top: 70px !important;
  }
  
  h1[style*="font-size: 32px"] {
    font-size: 16px !important;
  }
  
  div[style*="width: 48px; height: 48px"] {
    width: 28px !important;
    height: 28px !important;
  }
  
  i[style*="font-size: 20px"] {
    font-size: 12px !important;
  }
  
  label[style*="font-size: 14px"] {
    font-size: 10px !important;
  }
  
  input[style*="padding: 12px 16px"],
  select[style*="padding: 12px 16px"] {
    padding: 8px 10px !important;
    font-size: 12px !important;
    min-height: 42px !important;
  }
  
  button[style*="padding: 14px 28px"],
  a[style*="padding: 14px 28px"] {
    padding: 8px 12px !important;
    font-size: 11px !important;
  }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var role = document.getElementById('role');
  var wrap = document.getElementById('operator-nume-wrap');
  if (!role || !wrap) return;
  function toggle() {
    var v = (role.value || '').toLowerCase();
    wrap.style.display = (v === 'operator' || v === 'operatori') ? '' : 'none';
    wrap.querySelector('input').required = (v === 'operator' || v === 'operatori');
  }
  role.addEventListener('change', toggle);
  toggle();
});
</script>
@endpush
