@extends('layouts.app')

@section('title', 'Rapoarte – VOLTA')

@section('content')
<div style="padding: 0; max-width: 1200px; margin: 0 auto;">
  <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px;">
    <a href="{{ route('rapoarte.comparare') }}" style="text-decoration: none;">
      <div style="
        background: linear-gradient(135deg, #111827 0%, #1F2937 100%);
        border: 2px solid #ffee00;
        border-radius: 12px;
        padding: 30px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(255, 238, 0, 0.1);
      " onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 8px 30px rgba(255, 238, 0, 0.3)';" 
         onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 20px rgba(255, 238, 0, 0.1)';">
        <div style="color: #ffee00; font-size: 48px; margin-bottom: 15px; text-align: center;">
          <i class="fas fa-balance-scale"></i>
        </div>
        <h2 style="color: #ffee00; margin: 0 0 10px 0; text-align: center; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;">
          Comparare Rapoarte
        </h2>
        <p style="color: #999; margin: 0; text-align: center; font-size: 14px; line-height: 1.6;">
          Compară performanța între două perioade pentru a analiza evoluția indicatorilor cheie cu grafice și tabele detaliate
        </p>
      </div>
    </a>
    
    <a href="{{ route('istoric') }}" style="text-decoration: none;">
      <div style="
        background: linear-gradient(135deg, #111827 0%, #1F2937 100%);
        border: 2px solid #9CA3AF;
        border-radius: 12px;
        padding: 30px;
        transition: all 0.3s ease;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      " onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='#ffee00'; this.style.boxShadow='0 8px 30px rgba(255, 238, 0, 0.2)';" 
         onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='#9CA3AF'; this.style.boxShadow='0 4px 20px rgba(0, 0, 0, 0.3)';">
        <div style="color: #888; font-size: 48px; margin-bottom: 15px; text-align: center;">
          <i class="fas fa-history"></i>
        </div>
        <h2 style="color: #fff; margin: 0 0 10px 0; text-align: center; font-size: 22px; text-transform: uppercase; letter-spacing: 1px;">
          Istoric
        </h2>
        <p style="color: #999; margin: 0; text-align: center; font-size: 14px; line-height: 1.6;">
          Vezi istoricul complet al rapoartelor și analizează tendințele pe termen lung
        </p>
      </div>
    </a>
  </div>
</div>
@endsection

