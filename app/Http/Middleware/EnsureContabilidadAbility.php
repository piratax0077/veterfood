<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureContabilidadAbility
{
    public function handle(Request $request, Closure $next, string $ability)
    {
        $user = $request->user();

        if (!$user || !$user->tokenCan($ability)) {
            abort(403, 'El token no posee el permiso requerido.');
        }

        $membership = $request->attributes->get('centro_medico_membership');
        if (!$membership || !$this->membershipCan($membership, $ability)) {
            abort(403, 'No posee este permiso en el centro médico solicitado.');
        }

        return $next($request);
    }

    private function membershipCan($membership, string $ability): bool
    {
        if ($membership->permisos) {
            $permissions = json_decode($membership->permisos, true) ?: [];

            return in_array($ability, $permissions, true);
        }

        $roleAbilities = [
            'administrador' => ['contabilidad:read', 'contabilidad:write', 'contabilidad:pay'],
            'contador' => ['contabilidad:read', 'contabilidad:write', 'contabilidad:pay'],
            'central_alimentos' => ['contabilidad:read', 'contabilidad:invoice-upload', 'contabilidad:form-upload'],
            'institucion' => ['contabilidad:read', 'contabilidad:invoice-upload', 'contabilidad:form-upload'],
            'rrhh' => ['contabilidad:read', 'contabilidad:write'],
            'consulta' => ['contabilidad:read'],
        ];

        return in_array($ability, $roleAbilities[$membership->rol] ?? [], true);
    }
}
