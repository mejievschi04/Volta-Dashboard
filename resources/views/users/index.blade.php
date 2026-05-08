@extends('layouts.app')

@section('title', 'Utilizatori – VOLTA')

@section('content')
<div style="padding: 20px; width: 100%; box-sizing: border-box;">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
      <h1 style="color: #FFEE00; margin: 0; font-size: 32px; font-weight: 800;">Utilizatori</h1>
      <p style="color: #9CA3AF; margin: 5px 0 0 0; font-size: 14px;">Gestionare utilizatori și permisiuni</p>
    </div>
    @if(auth()->check() && auth()->user()->isDev())
    <a href="{{ route('users.create') }}" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.35); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 18px rgba(0,0,0,0.45)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.35)'">
      <i class="fas fa-user-plus"></i> Adaugă Utilizator
    </a>
    @endif
  </div>
  
  @if(session('success'))
  <div style="background: linear-gradient(135deg, #10B981 0%, #34D399 100%); color: #FFFFFF; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
    <i class="fas fa-check-circle" style="margin-right: 8px;"></i>{{ session('success') }}
  </div>
  @endif

  @if(session('error'))
  <div style="background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); color: #FFFFFF; padding: 16px; border-radius: 10px; margin-bottom: 20px; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
    <i class="fas fa-exclamation-circle" style="margin-right: 8px;"></i>{{ session('error') }}
  </div>
  @endif

  @if(count($users) > 0)
  <div class="operator-card" style="background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 1px solid rgba(255, 238, 0, 0.3); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.4); width: 100%; box-sizing: border-box;">
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
            <td class="row-text" style="padding: 16px; color: #9CA3AF; font-size: 14px;">
              @if($user->email)
                <i class="fas fa-envelope" style="margin-right: 6px; color: #9CA3AF;"></i>{{ $user->email }}
              @else
                <span style="color: #9CA3AF;">-</span>
              @endif
            </td>
            <td style="padding: 16px; text-align: center;">
              @if($user->isDev())
                <span style="color: #FFFFFF; background: linear-gradient(135deg, #7C3AED 0%, #A78BFA 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-code"></i>Dev
                </span>
              @elseif($user->isAdmin())
                <span style="color: #FFFFFF; background: linear-gradient(135deg, #EF4444 0%, #F87171 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-crown"></i>Admin
                </span>
              @else
                <span style="color: #FFFFFF; background: linear-gradient(135deg, #3B82F6 0%, #60A5FA 100%); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 700; box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3); display: inline-flex; align-items: center; gap: 6px;">
                  <i class="fas fa-user"></i>{{ ucfirst($user->role ?? 'User') }}
                </span>
              @endif
            </td>
            <td style="padding: 16px; text-align: center;">
              <div style="display: flex; gap: 8px; justify-content: center;">
                <a href="{{ route('users.edit', $user->id) }}" style="background: rgba(156, 163, 175, 0.15); color: #9CA3AF; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-weight: 600; transition: all 0.2s; border: 1px solid rgba(156, 163, 175, 0.3); display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(156, 163, 175, 0.25)'; this.style.color='#FFFFFF'" onmouseout="this.style.background='rgba(156, 163, 175, 0.15)'; this.style.color='#9CA3AF'">
                  <i class="fas fa-edit"></i>Edit
                </a>
                @if($user->id !== auth()->id())
                <form action="{{ route('users.destroy', $user->id) }}" method="POST" style="display: inline;" 
                      onsubmit="return confirm('Sigur doriți să ștergeți acest utilizator? Această acțiune este ireversibilă.');">
                  @csrf
                  @method('DELETE')
                  <button type="submit" style="background: rgba(239, 68, 68, 0.15); color: #EF4444; padding: 8px 16px; border: 1px solid rgba(239, 68, 68, 0.3); border-radius: 6px; cursor: pointer; font-weight: 600; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px;" onmouseover="this.style.background='rgba(239, 68, 68, 0.25)'; this.style.color='#FFFFFF'" onmouseout="this.style.background='rgba(239, 68, 68, 0.15)'; this.style.color='#EF4444'">
                    <i class="fas fa-trash"></i>Șterge
                  </button>
                </form>
                @else
                <span style="color: #9CA3AF; font-size: 12px; padding: 8px 16px; display: inline-flex; align-items: center; gap: 6px;">
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
  <div class="operator-card" style="margin-bottom: 30px; padding: 60px 20px; background: linear-gradient(135deg, #1F2937 0%, #1F2937 100%); border: 2px dashed rgba(255, 255, 255, 0.1);">
    <div style="text-align: center;">
      <i class="fas fa-users" style="font-size: 64px; color: #9CA3AF; margin-bottom: 20px; display: block;"></i>
      <p style="color: #9CA3AF; font-size: 18px; margin: 0 0 20px 0;">Nu există utilizatori în sistem momentan.</p>
      @if(auth()->check() && auth()->user()->isDev())
      <a href="{{ route('users.create') }}" style="background: linear-gradient(135deg, #FFEE00 0%, #FFEE00 100%); color: #000; padding: 12px 24px; border-radius: 10px; text-decoration: none; font-weight: 700; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.35);">
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
<style>
@media (max-width: 768px) {
  div[style*="padding: 20px"] {
    padding: 12px !important;
    padding-top: 75px !important;
  }
  
  div[style*="display: flex; justify-content: space-between"] {
    flex-direction: column !important;
    align-items: flex-start !important;
    gap: 12px !important;
    margin-bottom: 20px !important;
  }
  
  h1[style*="font-size: 32px"] {
    font-size: 20px !important;
    margin-bottom: 5px !important;
  }
  
  p[style*="font-size: 14px"] {
    font-size: 11px !important;
  }
  
  a[style*="padding: 12px 24px"] {
    width: 100% !important;
    padding: 10px 16px !important;
    font-size: 12px !important;
    justify-content: center !important;
  }
  
  a[style*="padding: 12px 24px"] i {
    font-size: 12px !important;
  }
  
  div[style*="padding: 16px"] {
    padding: 12px !important;
    font-size: 12px !important;
  }
  
  .operator-card {
    padding: 12px !important;
    margin-bottom: 15px !important;
  }
  
  table#usersTable {
    font-size: 11px !important;
    min-width: 700px !important;
  }
  
  table#usersTable th {
    padding: 10px 8px !important;
    font-size: 10px !important;
  }
  
  table#usersTable th i {
    font-size: 10px !important;
    margin-right: 4px !important;
  }
  
  table#usersTable td {
    padding: 10px 8px !important;
    font-size: 11px !important;
  }
  
  div[style*="width: 40px; height: 40px"] {
    width: 32px !important;
    height: 32px !important;
    font-size: 14px !important;
  }
  
  div[style*="font-size: 16px"] {
    font-size: 13px !important;
  }
  
  span[style*="padding: 6px 12px"] {
    padding: 4px 8px !important;
    font-size: 10px !important;
  }
  
  span[style*="padding: 6px 12px"] i {
    font-size: 9px !important;
  }
  
  a[style*="padding: 8px 16px"],
  button[style*="padding: 8px 16px"],
  span[style*="padding: 8px 16px"] {
    padding: 6px 10px !important;
    font-size: 10px !important;
  }
  
  a[style*="padding: 8px 16px"] i,
  button[style*="padding: 8px 16px"] i {
    font-size: 9px !important;
  }
  
  div[style*="display: flex; gap: 8px"] {
    flex-wrap: wrap !important;
    gap: 6px !important;
  }
  
  div[style*="padding: 60px 20px"] {
    padding: 40px 15px !important;
  }
  
  i[style*="font-size: 64px"] {
    font-size: 40px !important;
    margin-bottom: 15px !important;
  }
  
  p[style*="font-size: 18px"] {
    font-size: 14px !important;
    margin-bottom: 15px !important;
  }
}

@media (max-width: 480px) {
  div[style*="padding: 20px"] {
    padding: 10px !important;
    padding-top: 70px !important;
  }
  
  h1[style*="font-size: 32px"] {
    font-size: 18px !important;
  }
  
  p[style*="font-size: 14px"] {
    font-size: 10px !important;
  }
  
  a[style*="padding: 12px 24px"] {
    padding: 8px 12px !important;
    font-size: 11px !important;
  }
  
  div[style*="padding: 16px"] {
    padding: 10px !important;
    font-size: 11px !important;
  }
  
  .operator-card {
    padding: 10px !important;
  }
  
  table#usersTable {
    font-size: 10px !important;
    min-width: 600px !important;
  }
  
  table#usersTable th {
    padding: 8px 6px !important;
    font-size: 9px !important;
  }
  
  table#usersTable td {
    padding: 8px 6px !important;
    font-size: 10px !important;
  }
  
  div[style*="width: 40px; height: 40px"] {
    width: 28px !important;
    height: 28px !important;
    font-size: 12px !important;
  }
  
  div[style*="font-size: 16px"] {
    font-size: 12px !important;
  }
  
  span[style*="padding: 6px 12px"] {
    padding: 3px 6px !important;
    font-size: 9px !important;
  }
  
  a[style*="padding: 8px 16px"],
  button[style*="padding: 8px 16px"],
  span[style*="padding: 8px 16px"] {
    padding: 5px 8px !important;
    font-size: 9px !important;
  }
  
  div[style*="padding: 60px 20px"] {
    padding: 30px 12px !important;
  }
  
  i[style*="font-size: 64px"] {
    font-size: 32px !important;
    margin-bottom: 12px !important;
  }
  
  p[style*="font-size: 18px"] {
    font-size: 12px !important;
    margin-bottom: 12px !important;
  }
}
</style>
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
