<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\User;
use App\Rules\RutChileno;
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
        $request->merge(['rut' => RutChileno::normalizar((string) $request->input('rut'))]);

        $data = $request->validateWithBag('registro', [
            'nombres' => ['required', 'string', 'max:120'],
            'apellidos' => ['required', 'string', 'max:120'],
            'rut' => ['required', new RutChileno, 'unique:clientes,rut'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            // Entre 6 y 8 caracteres: letras (también ñ y tildes) más números y/o símbolos
            'password' => ['required', 'string', 'min:6', 'max:8', 'regex:/\pL/u', 'regex:/[^\pL\s]/u', 'confirmed'],
            'telefono' => ['nullable', 'string', 'max:80'],
        ], [
            'rut.required' => 'Ingresa tu RUT.',
            'rut.unique' => 'Este RUT ya está registrado en otra cuenta.',
            'nombres.required' => 'Ingresa tu nombre.',
            'apellidos.required' => 'Ingresa tu apellido.',
            'password.min' => 'La contraseña debe tener entre 6 y 8 caracteres.',
            'password.max' => 'La contraseña debe tener entre 6 y 8 caracteres.',
            'password.regex' => 'La contraseña debe combinar letras con números y/o símbolos.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $nombreCompleto = trim($data['nombres'] . ' ' . $data['apellidos']);

        $user = DB::transaction(function () use ($data, $nombreCompleto) {
            $user = User::create([
                'name' => $nombreCompleto,
                'nombres' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'email' => $data['email'],
                'password' => $data['password'],
                'rol' => 'cliente',
                'activo' => true,
                'telefono' => $data['telefono'] ?? null,
                'plan_preferido' => 'sin_plan',
            ]);

            $cliente = Cliente::create([
                'nombre' => $user->name,
                'rut' => $data['rut'],
                'telefono' => $user->telefono,
                'email' => $user->email,
                'fecha_inscripcion' => now(),
            ]);

            $user->update(['cliente_id' => $cliente->id]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('cliente.panel')->with('ok', 'Inscripción creada. Ahora puedes registrar tus mascotas y planes.');
    }

    public function login(Request $request)
    {
        // La entrada de la tienda es solo para clientes y usa su propio modal:
        // sus errores van en la bolsa "login" para que el modal se vuelva a abrir
        $desdeTienda = $request->input('desde') === 'tienda';
        $bolsaErrores = $desdeTienda ? 'login' : 'default';

        $credentials = $request->validateWithBag($bolsaErrores, [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $credentials['activo'] = true;

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            $this->sincronizarUsuarioVetSdi($credentials['email'], $credentials['password']);
        }

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Credenciales inválidas o usuario inactivo.'], $bolsaErrores)->onlyInput('email');
        }

        $request->session()->regenerate();

        // Un ingreso manual inicia un contexto nuevo. No debe heredar el acceso
        // SSO ni la URL destinada al panel del tutor anterior.
        $request->session()->forget([
            'acceso_vet_sdi_tutor',
            'url.intended',
            'sensitive_last_activity_at',
        ]);

        if ($desdeTienda && !Auth::user()->tieneRol('cliente', 'dueno_mascota')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withErrors(['email' => 'Esta cuenta no es de cliente. Ingresa desde el acceso del sistema.'], $bolsaErrores)
                ->onlyInput('email');
        }

        if ($desdeTienda) {
            $primerNombre = strtok(trim((string) Auth::user()->name), ' ') ?: Auth::user()->name;

            return redirect()->to($this->regresoTienda($request))
                ->with('ok', '¡Hola, ' . $primerNombre . '! Ya iniciaste sesión.');
        }

        if (config('two_factor.enabled', true) && Auth::user()->tieneRol('admin', 'auditor') && !in_array(mb_strtolower(Auth::user()->email), config('two_factor.bypass_emails', []), true)) {
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
     * Página de la tienda a la que va el cliente después de ingresar: el inicio de la tienda.
     * Si ingresó mientras pagaba, vuelve al pago para no perder la compra.
     */
    private function regresoTienda(Request $request): string
    {
        $volver = (string) $request->input('volver', '');

        return str_starts_with($volver, route('tienda.checkout')) ? $volver : route('tienda.inicio');
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

        return redirect()->route('tienda.inicio')->with('ok', 'Sesión cerrada correctamente.');
    }
}
