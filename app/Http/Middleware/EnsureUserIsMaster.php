<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMaster
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isMaster(), 403, 'No tienes autorización para gestionar usuarios administrativos.');

        return $next($request);
    }
}
