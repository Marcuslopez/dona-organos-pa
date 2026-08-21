<?php

namespace App\Http\Controllers;

use App\Http\Middleware\EnsureDonorSessionIsActive;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonorSessionActivityController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $now = now()->timestamp;
        $startedAt = (int) $request->session()->get(
            EnsureDonorSessionIsActive::STARTED_AT_KEY,
            $now,
        );
        $idleTimeout = max(1, (int) config('donor_session.idle_timeout'));
        $maxLifetime = max(0, (int) config('donor_session.max_lifetime'));
        $absoluteRemaining = $maxLifetime > 0
            ? max(0, $maxLifetime - ($now - $startedAt))
            : $idleTimeout;

        return response()->json([
            'expires_in' => min($idleTimeout, $absoluteRemaining),
        ]);
    }
}
