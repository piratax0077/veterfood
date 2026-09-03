<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user || !$user->activo) {
            abort(403, 'No autorizado');
        }

        if ($request->session()->get('acceso_vet_sdi_tutor')
            && (in_array('cliente', $roles, true) || in_array('dueno_mascota', $roles, true))) {
            return $next($request);
        }

        if (!$user->tieneRol(...$roles)) {
            abort(403, 'No autorizado');
        }

        return $next($request);
    }
}
