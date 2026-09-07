<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Mascota;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VetSdiSsoController extends Controller
{
    public function login(Request $request): RedirectResponse
    {
        $encoded = (string) $request->query('payload');
        $receivedSignature = (string) $request->query('signature');
        $expectedSignature = hash_hmac('sha256', $encoded, (string) config('services.sdi_sso.key'));

        abort_unless($encoded !== '' && hash_equals($expectedSignature, $receivedSignature), 403, 'Enlace de acceso invalido.');

        $decoded = base64_decode(strtr($encoded, '-_', '+/'), true);
        $payload = $decoded === false ? null : json_decode($decoded, true);

        abort_unless(is_array($payload), 403, 'Datos de acceso invalidos.');
        $aud = (string) ($payload['aud'] ?? '');
        abort_unless(($payload['iss'] ?? null) === 'vet-sdi' && in_array($aud, ['alimentos-vet', 'veterfarma'], true), 403);
        abort_unless((int) ($payload['iat'] ?? 0) <= now()->timestamp + 30, 403);
        abort_unless((int) ($payload['exp'] ?? 0) >= now()->timestamp, 403, 'El enlace de acceso expiro.');

        $email = mb_strtolower(trim((string) ($payload['email'] ?? '')));
        abort_unless(filter_var($email, FILTER_VALIDATE_EMAIL), 422, 'El correo de VET SDI no es valido.');

        $user = DB::transaction(function () use ($payload, $email) {
            $name = trim((string) ($payload['name'] ?? '')) ?: 'Tutor VET SDI';
            $cliente = Cliente::firstOrNew(['email' => $email]);
            $cliente->nombre = $name;
            $cliente->rut = $payload['rut'] ?? $cliente->rut;
            $cliente->telefono = $payload['telefono'] ?? $cliente->telefono;
            $cliente->fecha_inscripcion ??= now();
            $cliente->save();

            $user = User::firstOrNew(['email' => $email]);
            $user->name = $name;
            $user->vet_sdi_user_id = $payload['user_id'] ?? $payload['sub'] ?? $user->vet_sdi_user_id;
            if (!$user->exists) {
                $user->password = Str::random(64);
            }
            if (!in_array($user->rol, ['admin', 'central_ventas', 'repartidor', 'auditor', 'contabilidad'], true)) {
                $user->rol = 'cliente';
            }
            $user->activo = true;
            $user->telefono = $payload['telefono'] ?? $user->telefono;
            $user->direccion = $payload['direccion'] ?? $user->direccion;
            $user->cliente_id = $cliente->id;
            $user->save();

            $comunaId = (int) ($payload['comuna_id'] ?? 0);
            $regionId = (int) ($payload['region_id'] ?? 0);
            $direccionTexto = trim((string) ($payload['direccion'] ?? ''));
            $ubicacion = $comunaId && $regionId
                ? DB::connection('vet_sdi')->table('ciudades as c')
                    ->join('regiones as r', 'r.id', '=', 'c.id_region')
                    ->where('c.id', $comunaId)
                    ->where('r.id', $regionId)
                    ->first(['c.nombre as comuna', 'r.nombre as region'])
                : null;

            if ($direccionTexto !== '' && $ubicacion) {
                $direccionCliente = $user->direcciones()->where('principal', true)->first()
                    ?: $user->direcciones()->first();
                $user->direcciones()->update(['principal' => false]);
                $direccionCliente ??= new \App\Models\DireccionCliente(['user_id' => $user->id]);
                $direccionCliente->fill([
                    'alias' => $direccionCliente->alias ?: 'VET SDI',
                    'direccion' => $direccionTexto,
                    'region_id' => $regionId,
                    'region' => $ubicacion->region,
                    'comuna_id' => $comunaId,
                    'comuna' => $ubicacion->comuna,
                    'principal' => true,
                ]);
                $direccionCliente->save();
                $user->update(['direccion' => $direccionTexto]);
            }

            foreach ((array) ($payload['mascotas'] ?? []) as $datosMascota) {
                $origenId = (int) ($datosMascota['vet_sdi_id'] ?? 0);
                if ($origenId < 1 || trim((string) ($datosMascota['nombre'] ?? '')) === '') {
                    continue;
                }

                $mascota = Mascota::firstOrNew([
                    'origen_sistema' => 'vet-sdi',
                    'origen_id' => $origenId,
                ]);
                $mascota->fill([
                    'user_id' => $user->id,
                    'cliente_id' => $cliente->id,
                    'nombre' => $datosMascota['nombre'],
                    'especie' => $datosMascota['especie'] ?: 'Otra',
                    'raza' => $datosMascota['raza'] ?? null,
                    'sexo' => $datosMascota['sexo'] ?? 'desconocido',
                    'fecha_nacimiento' => $datosMascota['fecha_nacimiento'] ?? null,
                    'numero_chip' => $datosMascota['numero_chip'] ?? null,
                    'esterilizado' => (bool) ($datosMascota['esterilizado'] ?? false),
                    'observaciones' => $datosMascota['observaciones'] ?? null,
                    'foto_url' => $this->normalizarFotoVetSdi($datosMascota['foto_url'] ?? null),
                ]);
                $mascota->save();
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('acceso_vet_sdi_tutor', true);

        return redirect()->route('cliente.panel')->with('ok', 'Cuenta sincronizada con VET SDI. Bienvenido a Alimentos y Farmacia.');
    }

    private function normalizarFotoVetSdi(?string $foto): ?string
    {
        $foto = str_replace('\\', '/', trim((string) $foto));
        if ($foto === '') {
            return null;
        }

        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            $partes = parse_url($foto);
            $ruta = $partes['path'] ?? '';
            if (str_starts_with($ruta, '/mascotas/') || str_starts_with($ruta, '/imagenes/')) {
                $origen = ($partes['scheme'] ?? 'http') . '://' . ($partes['host'] ?? '');
                if (!empty($partes['port'])) {
                    $origen .= ':' . $partes['port'];
                }
                return $origen . '/storage' . $ruta;
            }
            return $foto;
        }

        $base = preg_replace('#/Paciente/Inicio/?$#', '', (string) config('services.sdi_sso.vet_web_url'));
        $ruta = ltrim($foto, '/');
        if (!str_starts_with($ruta, 'storage/')) {
            $ruta = str_contains($ruta, '/') ? 'storage/' . $ruta : 'storage/imagenes/temp/' . $ruta;
        }

        return rtrim($base, '/') . '/' . $ruta;
    }
}
