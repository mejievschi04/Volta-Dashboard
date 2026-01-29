@extends('layouts.app')

@section('title', 'Editează Operator – VOLTA')

@section('content')
<div style="padding: 20px;">
  <div style="margin-bottom: 20px;">
    <a href="{{ route('operatori') }}" style="color: #FFEE00; text-decoration: none; font-weight: 600;">
      ← Înapoi la Operatori
    </a>
  </div>
  
  <h1 style="color: #fff; margin-bottom: 20px;">Editează Operator: {{ $operator->nume }}</h1>

  @if($errors->any())
  <div style="background: #F87171; color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
    <ul style="margin: 0; padding-left: 20px;">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <div class="operator-card">
    <form action="{{ route('operatori.update', $operator->id) }}" method="POST">
      @csrf
      @method('PUT')
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Nume *</label>
          <input type="text" name="nume" value="{{ old('nume', $operator->nume) }}" required 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Email</label>
          <input type="email" name="email" value="{{ old('email', $operator->email) }}" 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Telefon</label>
          <input type="text" name="telefon" value="{{ old('telefon', $operator->telefon) }}" 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Data Angajare</label>
          <input type="date" name="data_angajare" value="{{ old('data_angajare', $operator->data_angajare ? $operator->data_angajare->format('Y-m-d') : '') }}" 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Departament</label>
          <input type="text" name="departament" value="{{ old('departament', $operator->departament) }}" 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Funcție</label>
          <input type="text" name="functie" value="{{ old('functie', $operator->functie) }}" 
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Adresă</label>
        <textarea name="adresa" rows="3" 
                  style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">{{ old('adresa', $operator->adresa) }}</textarea>
      </div>

      <div style="margin-bottom: 20px;">
        <label style="display: flex; align-items: center; color: #FFEE00; cursor: pointer;">
          <input type="checkbox" name="activ" value="1" {{ old('activ', $operator->activ) ? 'checked' : '' }} 
                 style="margin-right: 10px; width: 20px; height: 20px;">
          <span>Operator Activ</span>
        </label>
      </div>

      <div style="display: flex; gap: 10px;">
        <button type="submit" 
                style="background: #FFEE00; color: #000; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Actualizează Operator
        </button>
        <a href="{{ route('operatori.show', $operator->id) }}" 
           style="background: #111827; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
          Anulează
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
  
  div[style*="margin-bottom: 20px"] {
    margin-bottom: 15px !important;
  }
  
  h1[style*="color: #fff"] {
    font-size: 18px !important;
    margin-bottom: 15px !important;
  }
  
  a[style*="color: #FFEE00"] {
    font-size: 12px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 6px 12px !important;
    background: rgba(31, 41, 55, 0.3) !important;
    border-radius: 8px !important;
    border: 1px solid rgba(255, 238, 0, 0.2) !important;
  }
  
  div[style*="background: #F87171"] {
    padding: 10px !important;
    font-size: 12px !important;
    margin-bottom: 15px !important;
  }
  
  .operator-card {
    padding: 15px !important;
  }
  
  div[style*="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))"] {
    grid-template-columns: 1fr !important;
    gap: 15px !important;
    margin-bottom: 15px !important;
  }
  
  label[style*="color: #FFEE00"] {
    font-size: 11px !important;
    margin-bottom: 6px !important;
  }
  
  input[style*="padding: 10px"],
  input[type="date"],
  textarea[style*="padding: 10px"] {
    padding: 10px 12px !important;
    font-size: 13px !important;
    min-height: 44px !important;
  }
  
  textarea[style*="padding: 10px"] {
    min-height: 80px !important;
  }
  
  label[style*="display: flex; align-items: center"] {
    font-size: 12px !important;
  }
  
  input[type="checkbox"][style*="width: 20px"] {
    width: 18px !important;
    height: 18px !important;
  }
  
  div[style*="display: flex; gap: 10px"] {
    flex-direction: column !important;
    gap: 10px !important;
  }
  
  button[style*="padding: 12px 24px"],
  a[style*="padding: 12px 24px"] {
    width: 100% !important;
    padding: 10px 16px !important;
    font-size: 12px !important;
    min-height: 44px !important;
    justify-content: center !important;
    display: flex !important;
    align-items: center !important;
  }
}

@media (max-width: 480px) {
  div[style*="padding: 20px"] {
    padding: 10px !important;
    padding-top: 70px !important;
  }
  
  h1[style*="color: #fff"] {
    font-size: 16px !important;
    margin-bottom: 12px !important;
  }
  
  a[style*="color: #FFEE00"] {
    font-size: 11px !important;
    padding: 5px 10px !important;
  }
  
  div[style*="background: #F87171"] {
    padding: 8px !important;
    font-size: 11px !important;
  }
  
  .operator-card {
    padding: 12px !important;
  }
  
  div[style*="grid-template-columns: repeat(auto-fit, minmax(300px, 1fr))"] {
    gap: 12px !important;
    margin-bottom: 12px !important;
  }
  
  label[style*="color: #FFEE00"] {
    font-size: 10px !important;
    margin-bottom: 5px !important;
  }
  
  input[style*="padding: 10px"],
  input[type="date"],
  textarea[style*="padding: 10px"] {
    padding: 8px 10px !important;
    font-size: 12px !important;
    min-height: 42px !important;
  }
  
  textarea[style*="padding: 10px"] {
    min-height: 70px !important;
  }
  
  label[style*="display: flex; align-items: center"] {
    font-size: 11px !important;
  }
  
  input[type="checkbox"][style*="width: 20px"] {
    width: 16px !important;
    height: 16px !important;
  }
  
  button[style*="padding: 12px 24px"],
  a[style*="padding: 12px 24px"] {
    padding: 8px 12px !important;
    font-size: 11px !important;
    min-height: 42px !important;
  }
}
</style>
@endpush

