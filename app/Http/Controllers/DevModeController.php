<?php

namespace App\Http\Controllers;

use App\Support\DevMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DevModeController extends Controller
{
    public function panel()
    {
        $this->authorizeOwner();

        return view('dev-mode.panel', [
            'state' => DevMode::state(),
        ]);
    }

    public function enable(Request $request)
    {
        $this->authorizeOwner();

        $validated = $request->validate([
            'message' => 'nullable|string|max:180',
        ]);

        DevMode::enable(Auth::user(), $validated['message'] ?? null);

        return redirect()->route('dev-mode.panel')->with('status', 'Modul dev a fost activat.');
    }

    public function disable()
    {
        $this->authorizeOwner();
        DevMode::disable();

        return redirect()->route('dev-mode.panel')->with('status', 'Modul dev a fost dezactivat.');
    }

    private function authorizeOwner(): void
    {
        abort_unless(Auth::check() && DevMode::isOwner(Auth::user()), 403);
    }
}
