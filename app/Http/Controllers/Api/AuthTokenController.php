<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthTokenController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales no son válidas.'],
            ]);
        }

        $user->tokens()->where('name', $credentials['device_name'])->delete();

        $memberships = DB::table('centro_medico_user')
            ->join('centros_medicos', 'centros_medicos.id', '=', 'centro_medico_user.centro_medico_id')
            ->where('user_id', $user->id)
            ->where('centro_medico_user.activo', true)
            ->where('centros_medicos.activo', true)
            ->select([
                'centro_medico_user.centro_medico_id',
                'centro_medico_user.rol',
                'centro_medico_user.permisos',
                'centros_medicos.razon_social',
                'centros_medicos.nombre_fantasia',
                'centros_medicos.rut',
            ])
            ->get();

        $abilities = $memberships
            ->flatMap(function ($membership) {
                if ($membership->permisos) {
                    return json_decode($membership->permisos, true) ?: [];
                }

                return $this->defaultAbilitiesForRole($membership->rol);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($abilities)) {
            throw ValidationException::withMessages([
                'email' => ['El usuario no tiene acceso activo a contabilidad.'],
            ]);
        }

        $token = $user->createToken($credentials['device_name'], $abilities);

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'tipo_acceso' => $this->accessTypeForUser($user->rol),
            ],
            'centros' => $memberships->map(function ($membership) {
                $permissions = $membership->permisos
                    ? json_decode($membership->permisos, true) ?: []
                    : $this->defaultAbilitiesForRole($membership->rol);

                return [
                    'id' => $membership->centro_medico_id,
                    'rut' => $membership->rut,
                    'nombre' => $membership->nombre_fantasia ?: $membership->razon_social,
                    'rol' => $membership->rol,
                    'permisos' => $permissions,
                ];
            })->values(),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
        }

        return response()->json(['message' => 'Sesión cerrada.']);
    }

    private function defaultAbilitiesForRole(string $role): array
    {
        $abilities = [
            'administrador' => ['contabilidad:read', 'contabilidad:write', 'contabilidad:pay'],
            'contador' => ['contabilidad:read', 'contabilidad:write', 'contabilidad:pay'],
            'central_alimentos' => ['contabilidad:read', 'contabilidad:invoice-upload', 'contabilidad:form-upload'],
            'institucion' => ['contabilidad:read', 'contabilidad:invoice-upload', 'contabilidad:form-upload'],
            'rrhh' => ['contabilidad:read', 'contabilidad:write'],
            'consulta' => ['contabilidad:read'],
        ];

        return $abilities[$role] ?? [];
    }

    private function accessTypeForUser(string $role): string
    {
        return match ($role) {
            'contabilidad' => 'contador_externo',
            'admin' => 'administrador_sistema',
            'central_ventas', 'secretaria', 'secretaria_veterchile', 'secretaria_clinica' => 'central_alimentos',
            default => 'institucion',
        };
    }
}
