<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VerifyCentroMedicoAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        $centroMedico = $request->route('centroMedico');
        $centroMedicoId = is_object($centroMedico) ? $centroMedico->id : $centroMedico;

        if (!$user || !$centroMedicoId) {
            abort(401, 'Autenticacion requerida.');
        }

        if (!$request->is('api/*') && method_exists($user, 'tieneRol') && $user->tieneRol('admin')) {
            return $next($request);
        }

        $membership = DB::table('centro_medico_user')
            ->where('centro_medico_id', $centroMedicoId)
            ->where('user_id', $user->id)
            ->where('activo', true)
            ->first();

        if (!$membership) {
            abort(403, 'No tiene acceso a este centro medico.');
        }

        $request->attributes->set('centro_medico_membership', $membership);

        return $next($request);
    }
}
