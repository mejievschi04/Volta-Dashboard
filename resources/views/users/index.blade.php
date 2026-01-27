@extends('layouts.app')

@section('title', 'Utilizatori – VOLTA')

@section('content')
<div style="padding: 20px; width: 100%; box-sizing: border-box;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
      <h1 style="color: #FFEE00; margin: 0; font-size: 32px; font-weight: 800; text-shadow: 0 0 20px rgba(255, 238, 0, 0.5);">Utilizatori</h1>
      <p style="color: #B3B3B3; margin: 5px 0 0 0; font-size: 14px;">Gestionare utilizatori și permisiuni</p>
    </div>
    @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
    <a href="{{ route('users.create') }}" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 238, 0, 0.3); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(255, 238, 0, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(255, 238, 0, 0.3)'">
      <i class="fas fa-user-plus"></i> Adaugă Utilizator
    </a>
    @endif
  </div>
  
  @if(session('success'))
  <div style="background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%); color: #FFFFFF; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);">
    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div style="background: linear-gradient(135deg, #F44336 0%, #EF5350 100%); color: #FFFFFF; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);">
    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>{{ session('error') }}
  </div>
  @endif

  @if(count($users) > 0)
  <div class="operator-card" style="background: linear-gradient(135deg, #2B2B2B 0%, #2B2B2B 100%); border: 1px solid rgba(255, 238, 0, 0.3); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); width: 100%; box-sizing: border-box;">
    <div style="overflow-x: auto; width: 100%;">
      <table id="usersTable" style="width: 100%; border-collapse: collapse; min-width: 100%;">
        <thead>
          <tr style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000;">
            <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">
              <i class="fas fa-user" style="margin-right: 8px;"></i>Username
            </th>
            <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">
              <i class="fas fa-id-card" style="margin-right: 8px;"></i>Nume
            </th>
            <th style="padding: 16px; text-align: left; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">
              <i class="fas fa-envelope" style="margin-right: 8px;"></i>Email
            </th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">
              <i class="fas fa-shield-alt" style="margin-right: 8px;"></i>Rol
            </th>
            <th style="padding: 16px; text-align: center; border-bottom: 2px solid rgba(0, 0, 0, 0.2); font-weight: 700;">
              <i class="fas fa-cog" style="margin-right: 8px;"></i>Acțiuni
            </th>
          </tr>
        </thead>
        <tbody>
          @foreach($users as $user)
          <tr class="table-row-hover" style="border-bottom: 1px solid rgba(255, 255, 255, 0.05); transition: all 0.2s;">
            <td style="padding: 16px;">
              <div style="color: #fff; font-weight: 700; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                <div style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); display: flex; align-items: center; justify-content: center; color: #000; font-weight: 700;">
                  {{ strtoupper(substr($user->username, 0, 1)) }}
                </div>
                {{ $user->username }}
              </div>
            </td>
            <td class="row-text" style="padding: 16px; color: #FFFFFF; font-weight: 600;">
              {{ $user->name ?? '-' }}
            </td>
            <td class="row-text" style="padding: 16px; color: #B3B3B3; font-size: 14px;">
              @if($user->email)
                <i class="fas fa-envelope" style="margin-right: 6px; color: #CCCCCC;"></i>{{ $user->email }}
              @else
                <span style="color: #B3B3B3;">-</span>
              @endif
            </td>
            <td style="padding: 16px; text-align: center;">
              @if(strtolower($user->role ?? '') === 'admin' || strtolower($user->role ?? '') === 'administrator')
                <span style="color: #FFFFFF; background: linear-gradient(135deg, #F44336 0%, #EF5350 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-crown"></i>Admin
                </span>
              @else
                <span style="color: #FFFFFF; background: linear-gradient(135deg, #2196F3 0%, #42A5F5 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-user"></i>{{ ucfirst($user->role ?? 'User') }}
                </span>
              @endif
            </td>
            <td style="padding: 16px; text-align: center;">
              <div style="display: flex; gap: 8px; justify-content: center;">
                <a href="{{ route('users.edit', $user->id) }}" style="background: rgba(117, 117, 117, 0.15); color: #B3B3B3; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.2s; border: 1px solid rgba(117, 117, 117, 0.3); display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(117, 117, 117, 0.25)'; this.style.color='#FFFFFF'" onmouseout="this.style.background='rgba(117, 117, 117, 0.15)'; this.style.color='#B3B3B3'">
                  <i class="fas fa-edit"></i>Edit
                </a>
                @if($user->id !== auth()->id())
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;" 
                      onsubmit="return confirm('Sigur doriți să ștergeți acest utilizator? Această acțiune este ireversibilă.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" style="background: rgba(244, 67, 54, 0.15); color: #F44336; padding: 8px 16px; border: 1px solid rgba(244, 67, 54, 0.3); border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(244, 67, 54, 0.25)'; this.style.color='#FFFFFF'" onmouseout="this.style.background='rgba(244, 67, 54, 0.15)'; this.style.color='#F44336'">
                    <i class="fas fa-trash"></i>Șterge
                  </button>
                </form>
                @else
                <span style="color: #B3B3B3; font-size: 12px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-info-circle"></i>Utilizator curent
                </span>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
  @else
  <div class="operator-card" style="margin-bottom: 30px; padding: 60px 20px; background: linear-gradient(135deg, #2B2B2B 0%, #2B2B2B 100%); border: 2px dashed rgba(255, 255, 255, 0.1);">
    <div style="text-align: center;">
      <i class="fas fa-users" style="font-size: 64px; color: #B3B3B3; margin-bottom: 20px; display: block;"></i>
      <p style="color: #B3B3B3; font-size: 18px; margin: 0 0 20px 0;">Nu există utilizatori în sistem momentan.</p>
      @if(auth()->check() && (strtolower(auth()->user()->role ?? '') === 'admin' || strtolower(auth()->user()->role ?? '') === 'administrator'))
      <a href="{{ route('users.create') }}" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 15px rgba(255, 238, 0, 0.3);">
        <i class="fas fa-user-plus"></i>Adaugă Primul Utilizator
      </a>
      @endif
    </div>
  </div>
  @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('css/operatori.css') }}">
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Hover effects pentru tabel
  const rows = document.querySelectorAll('.table-row-hover');
  rows.forEach(row => {
    row.addEventListener('mouseenter', function() {
      this.style.background = 'rgba(255, 238, 0, 0.15)';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#000';
        }
      });
    });
    row.addEventListener('mouseleave', function() {
      this.style.background = 'transparent';
      const cells = this.querySelectorAll('.row-text');
      cells.forEach(cell => {
        if (!cell.querySelector('span')) {
          cell.style.color = '#FFFFFF';
        }
      });
    });
  });
});
</script>
@endpush
