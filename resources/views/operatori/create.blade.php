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
  <div style="background: #f87171; color: #fff; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
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
                 style="width: 100%; padding: 10px; border: 1px solid #333; border-radius: 8px; background: #1f2937; color: #fff;">
        </div>

        <div>
          <label style="display: block; color: #FFEE00; margin-bottom: 5px; font-weight: 600;">Data Angajare *</label>
          <input type="date" name="data_angajare" value="{{ old('data_angajare') }}" required
                 style="width: 100%; padding: 10px; border: 1px solid #333; border-radius: 8px; background: #1f2937; color: #fff;">
        </div>
      </div>

      <div style="display: flex; gap: 10px;">
        <button type="submit" 
                style="background: #FFEE00; color: #000; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
          Salvează Operator
        </button>
        <a href="{{ route('operatori') }}" 
           style="background: #333; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 600; display: inline-block;">
          Anulează
        </a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

