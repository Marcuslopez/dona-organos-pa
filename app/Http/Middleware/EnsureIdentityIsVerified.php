<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $verification = $request->session()->get('identity_verification');
        if (! is_array($verification)) {
            $request->session()->forget('identity_verification');

            return redirect()->route('registration.identity')->withErrors([
                'document_number' => 'La validación expiró. Ingresa nuevamente los datos.',
            ]);
        }

        return $next($request);
    }
}
