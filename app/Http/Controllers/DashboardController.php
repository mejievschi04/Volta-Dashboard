<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Operator;

class DashboardController extends Controller
{
    // Middleware-ul 'auth' este deja aplicat pe rute în routes/web.php
    // Nu mai este necesar să-l aplicăm și aici

    public function index(Request $request)
    {
        \Log::info('Dashboard accessed', [
            'user_id' => auth()->id(),
            'username' => auth()->user()?->username ?? 'not authenticated',
            'auth_check' => auth()->check(),
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession()
        ]);
        
        if (!auth()->check()) {
            \Log::warning('User not authenticated when accessing dashboard');
            return redirect()->route('login');
        }
        
        return view('dashboard.index');
    }

    public function trafic()
    {
        return view('dashboard.trafic');
    }

    public function setari()
    {
        $operatorRecord = null;
        if (Auth::check() && Auth::user()->isOperator()) {
            $user = Auth::user();
            $fullName = trim((string) ($user->full_name ?? $user->name ?? ''));
            if ($fullName !== '') {
                $operatorRecord = Operator::whereRaw('LOWER(TRIM(nume)) = ?', [mb_strtolower($fullName)])->first();
                if (! $operatorRecord) {
                    $operatorRecord = Operator::create(['nume' => $fullName, 'activ' => true]);
                }
            }
        }
        return view('dashboard.setari', compact('operatorRecord'));
    }

    public function traficStats()
    {
        return view('dashboard.trafic-stats');
    }

    public function traficAnaliza()
    {
        return view('dashboard.trafic-analiza');
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'currency' => 'nullable|string|max:10',
            'language' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:100',
            'theme' => 'nullable|in:dark,dark-red',
        ]);

        $user = auth()->user();
        if ($request->filled('username')) {
            $user->username = $request->username;
        }
        if ($request->filled('email')) {
            $user->email = $request->email;
        }
        if ($request->filled('currency')) {
            $user->currency = $request->currency;
        }
        if ($request->filled('language')) {
            $user->language = $request->language;
        }
        if ($request->filled('country')) {
            $user->country = $request->country;
        }
        if ($request->filled('theme')) {
            $user->theme = $request->theme;
        }
        $user->save();

        return redirect()->route('setari')
            ->with('toastMessage', 'Setările au fost salvate cu succes!');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = auth()->user();
        $currentPassword = $user->password_hash ?? $user->password;

        if (!Hash::check($request->current_password, $currentPassword)) {
            return redirect()->route('setari')
                ->with('passMessage', 'Parola curentă este incorectă.');
        }

        $user->password = Hash::make($request->new_password);
        $user->password_hash = Hash::make($request->new_password); // Pentru compatibilitate
        $user->save();

        return redirect()->route('setari')
            ->with('passMessage', 'Parola a fost schimbată cu succes!');
    }
}
