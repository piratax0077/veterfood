<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClienteDispositivo;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ClienteMovilController extends Controller
{
    public function enrolar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'device_uuid' => ['required', 'string', 'max:120'],
            'nombre' => ['nullable', 'string', 'max:120'],
            'plataforma' => ['nullable', 'string', 'max:60'],
            'modelo' => ['nullable', 'string', 'max:120'],
            'version_sistema' => ['nullable', 'string', 'max:60'],
        ]);

        $cliente = User::where('email', $data['email'])
            ->whereIn('rol', ['cliente', 'dueno_mascota'])
            ->where('activo', true)
            ->first();

        if (! $cliente || ! Hash::check($data['password'], $cliente->password)) {
            return response()->json([
                'ok' => false,
                'message' => 'Credenciales no validas para enrolar este telefono.',
            ], 422);
        }

        $dispositivo = ClienteDispositivo::updateOrCreate(
            ['device_uuid' => $data['device_uuid']],
            [
                'user_id' => $cliente->id,
                'device_token' => hash('sha256', $cliente->id . '|' . $data['device_uuid'] . '|' . Str::random(64)),
                'nombre' => $data['nombre'] ?? 'Telefono principal',
                'plataforma' => $data['plataforma'] ?? null,
                'modelo' => $data['modelo'] ?? null,
                'version_sistema' => $data['version_sistema'] ?? null,
                'estado' => 'activo',
                'enrolado_at' => now(),
                'ultimo_uso_at' => now(),
                'ultimo_ip' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Telefono enrolado correctamente.',
            'cliente' => $cliente->only(['id', 'name', 'email', 'telefono']),
            'device_token' => $dispositivo->device_token,
            'enrolado_at' => optional($dispositivo->enrolado_at)->toIso8601String(),
        ]);
    }

    public function dispositivo(Request $request): JsonResponse
    {
        $dispositivo = $this->dispositivoDesdeToken($request);

        if (! $dispositivo) {
            return response()->json(['ok' => false, 'message' => 'Telefono no enrolado.'], 401);
        }

        $dispositivo->update([
            'ultimo_uso_at' => now(),
            'ultimo_ip' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
        ]);

        return response()->json([
            'ok' => true,
            'cliente' => $dispositivo->cliente->only(['id', 'name', 'email', 'telefono']),
            'dispositivo' => [
                'nombre' => $dispositivo->nombre,
                'estado' => $dispositivo->estado,
                'enrolado_at' => optional($dispositivo->enrolado_at)->toIso8601String(),
                'ultimo_uso_at' => optional($dispositivo->ultimo_uso_at)->toIso8601String(),
            ],
        ]);
    }

    public function tracking(Request $request, string $codigo): JsonResponse
    {
        $pedido = Pedido::with(['items', 'tracking' => fn ($query) => $query->latest()->limit(25), 'repartidor'])
            ->where('codigo_tracking', $codigo)
            ->first();

        if (! $pedido) {
            return response()->json([
                'ok' => false,
                'message' => 'Pedido no encontrado.',
            ], 404);
        }

        $ultimaUbicacion = $pedido->tracking
            ->where('estado', 'ubicacion')
            ->first();
        $destino = $this->extraerDestino($pedido);
        $eta = $this->calcularEta($ultimaUbicacion, $destino);

        return response()->json([
            'ok' => true,
            'pedido' => [
                'codigo_tracking' => $pedido->codigo_tracking,
                'estado' => $pedido->estado,
                'estado_pago' => $pedido->estado_pago,
                'cliente_nombre' => $pedido->cliente_nombre,
                'direccion_entrega' => $pedido->direccion_entrega,
                'fecha_entrega' => optional($pedido->fecha_entrega)->format('Y-m-d'),
                'total' => $pedido->total,
                'despachado_at' => optional($pedido->despachado_at)->toIso8601String(),
                'entregado_at' => optional($pedido->entregado_at)->toIso8601String(),
                'repartidor' => $pedido->repartidor?->only(['id', 'name', 'email']),
            ],
            'items' => $pedido->items->map(fn ($item) => [
                'nombre' => $item->nombre,
                'cantidad' => $item->cantidad,
                'precio_unitario' => $item->precio_unitario,
                'total' => $item->total,
            ])->values(),
            'ultima_ubicacion' => $ultimaUbicacion ? [
                'latitud' => (float) $ultimaUbicacion->latitud,
                'longitud' => (float) $ultimaUbicacion->longitud,
                'actualizado_at' => optional($ultimaUbicacion->created_at)->toIso8601String(),
            ] : null,
            'destino' => $destino,
            'eta' => $eta,
            'eventos' => $pedido->tracking->map(fn ($evento) => [
                'estado' => $evento->estado,
                'mensaje' => $evento->mensaje,
                'latitud' => $evento->latitud ? (float) $evento->latitud : null,
                'longitud' => $evento->longitud ? (float) $evento->longitud : null,
                'foto_entrega' => $evento->foto_entrega ? asset('storage/' . $evento->foto_entrega) : null,
                'fecha' => optional($evento->created_at)->toIso8601String(),
            ])->values(),
        ]);
    }

    public function ofertas(): JsonResponse
    {
        $productos = Producto::where('activo', true)
            ->whereIn('categoria', ['alimento_mascota', 'medicamento', 'juguete', 'utensilio', 'servicio', 'hotel', 'cuidado'])
            ->latest()
            ->limit(18)
            ->get();

        return response()->json([
            'ok' => true,
            'productos' => $productos->map(fn ($producto) => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'marca' => $producto->marca,
                'categoria' => $producto->categoria,
                'subcategoria' => $producto->subcategoria,
                'precio' => $producto->precio,
                'stock' => $producto->stock,
                'foto_url' => $producto->foto_url,
            ]),
        ]);
    }

    private function dispositivoDesdeToken(Request $request): ?ClienteDispositivo
    {
        $token = (string) $request->header('X-Device-Token');

        if ($token === '') {
            return null;
        }

        return ClienteDispositivo::with('cliente')
            ->where('device_token', $token)
            ->where('estado', 'activo')
            ->first();
    }

    private function extraerDestino(Pedido $pedido): ?array
    {
        $texto = trim(($pedido->notas_entrega ?? '') . ' ' . ($pedido->direccion_entrega ?? ''));

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $texto, $match)) {
            return ['latitud' => (float) $match[1], 'longitud' => (float) $match[2]];
        }

        if (preg_match('/[?&]q=(-?\d+\.\d+),(-?\d+\.\d+)/', $texto, $match)) {
            return ['latitud' => (float) $match[1], 'longitud' => (float) $match[2]];
        }

        if (preg_match('/(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)/', $texto, $match)) {
            return ['latitud' => (float) $match[1], 'longitud' => (float) $match[2]];
        }

        return null;
    }

    private function calcularEta($origen, ?array $destino): ?array
    {
        if (! $origen || ! $destino) {
            return null;
        }

        $km = $this->distanciaKm((float) $origen->latitud, (float) $origen->longitud, $destino['latitud'], $destino['longitud']);
        $minutos = max(3, (int) ceil(($km / 28) * 60) + 4);

        return [
            'distancia_km' => round($km, 2),
            'minutos' => $minutos,
            'texto' => $minutos < 60 ? $minutos . ' min' : floor($minutos / 60) . ' h ' . ($minutos % 60) . ' min',
        ];
    }

    private function distanciaKm(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radio = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $radio * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
