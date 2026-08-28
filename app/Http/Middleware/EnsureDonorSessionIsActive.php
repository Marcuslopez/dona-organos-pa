<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureDonorSessionIsActive
{
    public const LAST_ACTIVITY_KEY = 'donor_session.last_activity';

    public const STARTED_AT_KEY = 'donor_session.started_at';

    public function handle(Request $request, Closure $next): Response
    {
        $now = now()->timestamp;
        $startedAt = (int) $request->session()->get(self::STARTED_AT_KEY, $now);
        $lastActivity = (int) $request->session()->get(self::LAST_ACTIVITY_KEY, $now);
        $idleTimeout = max(1, (int) config('donor_session.idle_timeout'));
        $maxLifetime = max(0, (int) config('donor_session.max_lifetime'));
        $hasIdentity = is_array($request->session()->get('identity_verification'));
        $verification = $request->session()->get('identity_verification', []);
        $donorId = is_array($verification) ? (int) ($verification['donor_id'] ?? 0) : 0;
        $accessToken = is_array($verification) ? $verification['active_access_token'] ?? null : null;
        $sessionWasReplaced = $donorId > 0 && filled($accessToken)
            && DB::table('donors')->where('id', $donorId)->value('active_access_token') !== $accessToken;
        $idleExpired = ($now - $lastActivity) >= $idleTimeout;
        $absoluteExpired = $maxLifetime > 0 && ($now - $startedAt) >= $maxLifetime;

        if (! $hasIdentity || $idleExpired || $absoluteExpired || $sessionWasReplaced) {
            $request->session()->forget([
                'identity_verification',
                self::LAST_ACTIVITY_KEY,
                self::STARTED_AT_KEY,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sesión del donante finalizó por inactividad.',
                    'redirect' => route('registration.identity'),
                ], 401);
            }

            return redirect()->route('registration.identity')->withErrors([
                'document_number' => $sessionWasReplaced
                    ? 'La sesión fue reemplazada por un acceso más reciente. Valida nuevamente tu identidad.'
                    : 'La sesión finalizó por inactividad. Valida nuevamente tu identidad.',
            ]);
        }

        $request->session()->put([
            self::STARTED_AT_KEY => $startedAt,
            self::LAST_ACTIVITY_KEY => $now,
        ]);

        return $next($request);
    }
}
