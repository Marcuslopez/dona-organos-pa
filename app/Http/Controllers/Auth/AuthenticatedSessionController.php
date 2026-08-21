<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSessionIsActive;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        $request->session()->put([
            EnsureAdminSessionIsActive::STARTED_AT_KEY => now()->timestamp,
            EnsureAdminSessionIsActive::LAST_ACTIVITY_KEY => now()->timestamp,
        ]);
        $request->user()->forceFill(['last_login_at' => now()])->save();

        $fallback = $request->user()->isMaster()
            ? route('admin.users.index')
            : route('admin.dashboard');

        return redirect()->intended($fallback);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
