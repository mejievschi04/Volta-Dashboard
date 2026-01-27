<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        try {
            \Log::info('Login request received', [
                'username' => $request->input('username'),
                'has_password' => $request->has('password')
            ]);

            $credentials = $request->validate([
                'username' => 'required|string',
                'password' => 'required|string',
            ], [
                'username.required' => 'Utilizatorul este obligatoriu.',
                'password.required' => 'Parola este obligatorie.',
            ]);

            \Log::info('Login attempt', ['username' => $credentials['username']]);

            // Verificăm manual pentru că folosim username în loc de email
            $user = User::where('username', $credentials['username'])->first();

            if (!$user) {
                \Log::warning('User not found', ['username' => $credentials['username']]);
                return redirect()->route('login')->withErrors([
                    'username' => 'Datele de autentificare nu sunt corecte.',
                ])->withInput($request->only('username'));
            }

            // Obținem valorile originale din baza de date, fără casting
            $attributes = $user->getAttributes();
            $storedPassword = $attributes['password_hash'] ?? $attributes['password'] ?? null;
            
            if (!$storedPassword) {
                \Log::warning('User has no password', ['username' => $credentials['username']]);
                return redirect()->route('login')->withErrors([
                    'username' => 'Datele de autentificare nu sunt corecte.',
                ])->withInput($request->only('username'));
            }
            
            if (\Hash::check($credentials['password'], $storedPassword)) {
                // Regenerăm sesiunea înainte de login pentru securitate
                $request->session()->regenerate();
                
                // Logăm utilizatorul
                Auth::login($user, $request->filled('remember'));
                
                // Salvez sesiunea explicit
                $request->session()->save();
                
                \Log::info('Login successful', [
                    'username' => $credentials['username'], 
                    'user_id' => $user->id,
                    'auth_check' => Auth::check(),
                    'auth_user_id' => Auth::id(),
                    'session_id' => $request->session()->getId(),
                    'session_data' => $request->session()->all()
                ]);
                
                // Forțăm redirect la dashboard în loc de intended
                $dashboardUrl = route('dashboard');
                \Log::info('Redirecting to dashboard', [
                    'route' => $dashboardUrl,
                    'session_persists' => $request->session()->has('_token'),
                    'auth_after_save' => Auth::check()
                ]);
                
                return redirect($dashboardUrl);
            } else {
                \Log::warning('Password mismatch', ['username' => $credentials['username']]);
            }

            return redirect()->route('login')->withErrors([
                'username' => 'Datele de autentificare nu sunt corecte.',
            ])->withInput($request->only('username'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('Validation failed', ['errors' => $e->errors()]);
            return redirect()->route('login')
                ->withErrors($e->errors())
                ->withInput($request->only('username'));
        } catch (\Exception $e) {
            \Log::error('Login error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('login')->withErrors([
                'username' => 'A apărut o eroare. Te rugăm să încerci din nou.',
            ])->withInput($request->only('username'));
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
