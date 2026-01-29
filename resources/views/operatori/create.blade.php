@extends('layouts.app')

@section('title', 'Adaugă Operator – VOLTA')

@section('content')
<div style="padding: 20px;">
  <div style="margin-bottom: 20px;">
    <a href="{{ route('operatori') }}" style="color: #FFEE00; text-decoration: none; font-weight: 600;">
      ← Înapoi la Operatori
    </a>
  </div>
  
  <h1 style="color: #fff; margin-bottom: 20px;">Adaugă Operator Nou</h1>

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
    <form action="{{ route('operatori.store') }}" method="POST">
      @csrf
      
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Nume și Prenume *</label>
          <input type="text" name="nume" value="{{ old('nume') }}" required 
                 placeholder="Ex: Ion Popescu"
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Data Angajare *</label>
          <input type="date" name="data_angajare" value="{{ old('data_angajare') }}" required
                 style="width: 100%; padding: 10px; border: 1px solid #9CA3AF; border-radius: 8px; background: #1F2937; color: #fff;">
        </div>
      </div>

      <div style="display: flex; gap: 10px;">
        <button type="submit" 
                style="background: #FFEE00; color: #000; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Salvează Operator
        </button>
        <a href="{{ route('operatori') }}" 
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
  input[type="date"] {
    padding: 10px 12px !important;
    font-size: 13px !important;
    min-height: 44px !important;
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
  input[type="date"] {
    padding: 8px 10px !important;
    font-size: 12px !important;
    min-height: 42px !important;
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

