<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureIdentityIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $verification = $request->session()->get('identity_verification');
        $hasExpiration = is_array($verification) && array_key_exists('expires_at', $verification);
        $expiresAt = $hasExpiration ? $verification['expires_at'] : null;
        $isUpdateForm = in_array($request->route()?->getName(), [
            'registration.update.form',
            'registration.update.store',
        ], true);
        $hasStartedUpdate = ($verification['active_form_flow'] ?? null) === 'update';
        $canContinueUpdate = $isUpdateForm && $hasStartedUpdate;
        $isRegistrationRoute = in_array($request->route()?->getName(), [
            'registration.form',
            'registration.store',
        ], true);
        $isNewDocument = is_array($verification)
            && filled($verification['document_number'] ?? null)
            && ! DB::table('donors')->where('document_number', $verification['document_number'])->exists();
        $canContinueNewRegistration = $isRegistrationRoute && $isNewDocument;

        if (! is_array($verification)
            || ! $hasExpiration
            || (($expiresAt !== null && $expiresAt < now()->timestamp)
                && ! $canContinueUpdate
                && ! $canContinueNewRegistration)) {
            $request->session()->forget('identity_verification');

            return redirect()->route('registration.identity')->withErrors([
                'document_number' => 'La validación expiró. Ingresa nuevamente los datos.',
            ]);
        }

        return $next($request);
    }
}
