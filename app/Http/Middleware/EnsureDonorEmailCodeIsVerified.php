<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDonorEmailCodeIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $verification = $request->session()->get('identity_verification');
        $isExistingDonor = is_array($verification) && filled($verification['donor_id'] ?? null);

        if ($isExistingDonor && ! filled($verification['email_verified_at'] ?? null)) {
            return redirect()->route('registration.email-code.create')->with('status', 'Confirma el código enviado a tu correo para continuar.');
        }

        return $next($request);
    }
}
