<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('username')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'name' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'role' => ['nullable', 'string', Rule::in(['user', 'admin', 'administrator', 'operator', 'operatori', 'dev'])],
            'operator_nume' => 'nullable|string|max:255',
        ]);

        $user = new User();
        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? null;
        $user->name = $validated['name'] ?? null;
        $user->full_name = ($validated['full_name'] ?? null) ? trim($validated['full_name']) : null;
        $user->role = $validated['role'] ?? 'user';
        $user->operator_nume = ($validated['operator_nume'] ?? null) ? trim($validated['operator_nume']) : null;
        $user->password_hash = Hash::make($validated['password']);
        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilizatorul a fost adăugat cu succes!');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'email' => 'nullable|email|max:255|unique:users,email,' . $id,
            'password' => 'nullable|string|min:6|confirmed',
            'name' => 'nullable|string|max:255',
            'full_name' => 'nullable|string|max:255',
            'role' => ['nullable', 'string', Rule::in(['user', 'admin', 'administrator', 'operator', 'operatori', 'dev'])],
            'operator_nume' => 'nullable|string|max:255',
        ]);

        $user->username = $validated['username'];
        $user->email = $validated['email'] ?? null;
        $user->name = $validated['name'] ?? null;
        $user->full_name = ($validated['full_name'] ?? null) ? trim($validated['full_name']) : null;
        $user->role = $validated['role'] ?? $user->role;
        $user->operator_nume = ($validated['operator_nume'] ?? null) ? trim($validated['operator_nume']) : null;

        if (!empty($validated['password'])) {
            $user->password_hash = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilizatorul a fost actualizat cu succes!');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Nu permite ștergerea propriului cont
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'Nu puteți șterge propriul cont!');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Utilizatorul a fost șters cu succes!');
    }
}
