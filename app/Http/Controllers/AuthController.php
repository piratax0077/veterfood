<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function inicio()
    {
        return view('inicio');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function registrarCliente(Request $request)
    {
        $data = $request->validateWithBag('registro', [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:80'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'comuna' => ['nullable', 'string', 'max:120'],
            'referencia' => ['nullable', 'string', 'max:500'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'rol' => 'cliente',
                'activo' => true,
                'telefono' => $data['telefono'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'plan_preferido' => 'sin_plan',
                'georeferencia_url' => $this->urlMapa($data['direccion'] ?? null, $data['comuna'] ?? null),
            ]);

            $cliente = Cliente::create([
                'nombre' => $user->name,
                'telefono' => $user->telefono,
                'email' => $user->email,
                'fecha_inscripcion' => now(),
            ]);

            $user->update(['cliente_id' => $cliente->id]);

            if (!empty($data['direccion'])) {
                $user->direcciones()->create([
                    'alias' => 'Principal',
                    'direccion' => $data['direccion'],
                    'comuna' => $data['comuna'] ?? null,
                    'referencia' => $data['referencia'] ?? null,
                    'principal' => true,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('cliente.panel')->with('ok', 'Inscripcion creada. Ahora puedes registrar tus mascotas y planes.');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['activo'] = true;

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->sincronizarUsuarioVetSdi($credentials['email'], $credentials['password']);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciales invalidas o usuario inactivo.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        // Un ingreso manual inicia un contexto nuevo. No debe heredar el acceso
        // SSO ni la URL destinada al panel del tutor anterior.
        $request->session()->forget([
            'acceso_vet_sdi_tutor',
            'url.intended',
            'sensitive_last_activity_at',
        ]);

        if (Auth::user()->tieneRol('admin', 'auditor') && !in_array(mb_strtolower(Auth::user()->email), config('two_factor.bypass_emails', []), true)) {
            $request->session()->forget(['two_factor_verified', 'two_factor_verified_at']);

            return redirect()->route(
                Auth::user()->two_factor_confirmed_at
                    ? 'two-factor.challenge'
                    : 'two-factor.setup'
            );
        }

        return redirect()->route('redirect.role');
    }

    /**
     * Permite usar en Alimentos las mismas credenciales del tutor de VET SDI.
     * Se copia solamente el hash de Laravel; nunca se almacena la clave en texto plano.
     */
    private function sincronizarUsuarioVetSdi(string $email, string $password): void
    {
        try {
            $vet = DB::connection('vet_sdi')->table('users')
                ->whereRaw('LOWER(email) = ?', [mb_strtolower(trim($email))])
                ->first(['id', 'name', 'email', 'password']);
        } catch (\Throwable $exception) {
            report($exception);
            return;
        }

        if (!$vet || !Hash::check($password, $vet->password)) {
            return;
        }

        DB::transaction(function () use ($vet) {
            $user = User::firstOrNew(['email' => mb_strtolower(trim($vet->email))]);
            $user->name = $vet->name ?: $user->name ?: 'Tutor VET SDI';
            $user->vet_sdi_user_id = $vet->id;
            $user->password = $vet->password;
            $user->rol = in_array($user->rol, ['admin', 'central_ventas', 'repartidor'], true)
                ? $user->rol
                : 'cliente';
            $user->activo = true;
            $user->save();

            if (!$user->cliente_id) {
                $cliente = Cliente::firstOrCreate(
                    ['email' => $user->email],
                    ['nombre' => $user->name, 'fecha_inscripcion' => now()]
                );
                $user->update(['cliente_id' => $cliente->id]);
            }
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->forget([
            'acceso_vet_sdi_tutor',
            'url.intended',
            'two_factor_verified',
            'two_factor_verified_at',
            'sensitive_last_activity_at',
        ]);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('inicio')->with('ok', 'Sesion cerrada correctamente.');
    }

    private function urlMapa(?string $direccion, ?string $comuna): ?string
    {
        $texto = trim(implode(' ', array_filter([$direccion, $comuna])));

        return $texto ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($texto) : null;
    }
}
