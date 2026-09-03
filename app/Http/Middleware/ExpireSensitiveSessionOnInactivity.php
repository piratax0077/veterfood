<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ExpireSensitiveSessionOnInactivity
{
    private const TIMEOUT_SECONDS = 3600;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->tieneRol('admin', 'auditor', 'central_ventas')) {
            return $next($request);
        }

        $lastActivity = (int) $request->session()->get('sensitive_last_activity_at', now()->timestamp);

        if (now()->timestamp - $lastActivity > self::TIMEOUT_SECONDS) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Sesion cerrada por inactividad. Ingresa nuevamente para continuar.']);
        }

        $request->session()->put('sensitive_last_activity_at', now()->timestamp);

        return $next($request);
    }
}
