<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureAdminSessionIsActive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SessionActivityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $now = now()->timestamp;
        $startedAt = (int) $request->session()->get(
            EnsureAdminSessionIsActive::STARTED_AT_KEY,
            $now,
        );
        $idleTimeout = max(1, (int) config('admin_session.idle_timeout'));
        $maxLifetime = max(0, (int) config('admin_session.max_lifetime'));
        $absoluteRemaining = $maxLifetime > 0
            ? max(0, $maxLifetime - ($now - $startedAt))
            : $idleTimeout;

        return response()->json([
            'expires_in' => min($idleTimeout, $absoluteRemaining),
        ]);
    }
}
