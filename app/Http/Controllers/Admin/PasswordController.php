<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class PasswordController extends Controller
{
    public function edit(): View
    {
        return view('admin.password.edit');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => [$request->user()->must_change_password ? 'nullable' : 'required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
            'must_change_password' => false,
        ]);

        DB::table('admin_user_audits')->insert([
            'actor_user_id' => $request->user()->id,
            'target_user_id' => $request->user()->id,
            'action' => 'password_changed',
            'changes' => json_encode(['must_change_password' => ['from' => true, 'to' => false]]),
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
        ]);

        $destination = $request->user()->isMaster()
            ? 'admin.users.index'
            : 'admin.dashboard';

        return redirect()->route($destination)->with('status', 'Contraseña actualizada correctamente.');
    }
}
