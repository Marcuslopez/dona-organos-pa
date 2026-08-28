<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class MasterReauthenticationService
{
    public function verify(Request $request): void
    {
        $verifiedAt = (int) $request->session()->get('master_reauthentication_at', 0);
        $withinWindow = $verifiedAt > 0 && (now()->timestamp - $verifiedAt) <= max(1, (int) config('access_security.admin.master_reauthentication_seconds'));

        if ($withinWindow) return;

        if (! Hash::check((string) $request->input('current_master_password'), (string) $request->user()?->password)) {
            throw ValidationException::withMessages(['current_master_password' => 'Confirma tu contraseña para realizar esta acción de seguridad.']);
        }

        $request->session()->put('master_reauthentication_at', now()->timestamp);
    }
}
