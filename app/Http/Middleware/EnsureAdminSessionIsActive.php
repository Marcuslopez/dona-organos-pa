<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSessionIsActive
{
    public const LAST_ACTIVITY_KEY = 'admin_session.last_activity';

    public const STARTED_AT_KEY = 'admin_session.started_at';

    public function handle(Request $request, Closure $next): Response
    {
        $now = now()->timestamp;
        $startedAt = (int) $request->session()->get(self::STARTED_AT_KEY, $now);
        $lastActivity = (int) $request->session()->get(self::LAST_ACTIVITY_KEY, $now);
        $idleTimeout = max(1, (int) config('admin_session.idle_timeout'));
        $maxLifetime = max(0, (int) config('admin_session.max_lifetime'));

        if ($request->user()?->active_session_id && $request->user()->active_session_id !== $request->session()->getId()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('status', 'La sesión fue cerrada porque se inició sesión en otro equipo o navegador.');
        }

        $idleExpired = ($now - $lastActivity) >= $idleTimeout;
        $absoluteExpired = $maxLifetime > 0 && ($now - $startedAt) >= $maxLifetime;

        if ($idleExpired || $absoluteExpired) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'La sesión administrativa finalizó por seguridad.',
                    'redirect' => route('login'),
                ], 401);
            }

            return redirect()->route('login')->with(
                'status',
                'La sesión administrativa finalizó por inactividad. Ingresa nuevamente.',
            );
        }

        $request->session()->put([
            self::STARTED_AT_KEY => $startedAt,
            self::LAST_ACTIVITY_KEY => $now,
        ]);

        return $next($request);
    }
}
