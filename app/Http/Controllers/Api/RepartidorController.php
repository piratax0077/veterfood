<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pedido;
use App\Models\TrackingEvento;
use App\Models\User;
use App\Services\SdiRegistry;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RepartidorController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['repartidor_id' => ['required', 'integer']]);
        $repartidor = User::where('id', $data['repartidor_id'])->where('rol', 'repartidor')->where('activo', true)->first();

        return $repartidor
            ? response()->json(['ok' => true, 'repartidor' => $repartidor])
            : response()->json(['ok' => false, 'message' => 'Repartidor no encontrado'], 404);
    }

    public function pedidos(int $repartidorId)
    {
        return Pedido::with(['items', 'tracking'])
            ->where('repartidor_id', $repartidorId)
            ->whereIn('estado', ['en_preparacion', 'listo_despacho', 'reparto_asignado', 'en_camino', 'asignado', 'preparando', 'en_ruta'])
            ->orderBy('fecha_entrega')
            ->get()
            ->map(function (Pedido $pedido) {
                $ultimaUbicacion = $pedido->tracking->where('estado', 'ubicacion')->sortByDesc('created_at')->first();
                $destino = $this->extraerDestino($pedido);

                return [
                    'id' => $pedido->id,
                    'codigo_tracking' => $pedido->codigo_tracking,
                    'estado' => $pedido->estado,
                    'cliente_nombre' => $pedido->cliente_nombre,
                    'cliente_telefono' => $pedido->cliente_telefono,
                    'direccion_entrega' => $pedido->direccion_entrega,
                    'notas_entrega' => $pedido->notas_entrega,
                    'fecha_entrega' => optional($pedido->fecha_entrega)->format('Y-m-d'),
                    'total' => $pedido->total,
                    'items' => $pedido->items,
                    'ultima_ubicacion' => $ultimaUbicacion ? [
                        'latitud' => (float) $ultimaUbicacion->latitud,
                        'longitud' => (float) $ultimaUbicacion->longitud,
                        'actualizado_at' => optional($ultimaUbicacion->created_at)->toIso8601String(),
                    ] : null,
                    'destino' => $destino,
                    'eta' => $this->calcularEta($ultimaUbicacion, $destino),
                ];
            })
            ->values();
    }

    public function estado(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'estado' => ['required', Rule::in(['en_preparacion', 'listo_despacho', 'reparto_asignado', 'en_camino', 'entregado'])],
            'repartidor_id' => ['nullable', 'integer'],
        ]);

        $pedido->update([
            'estado' => $data['estado'],
            'despachado_at' => $data['estado'] === 'en_camino' ? now() : $pedido->despachado_at,
            'entregado_at' => $data['estado'] === 'entregado' ? now() : $pedido->entregado_at,
        ]);

        TrackingEvento::create([
            'pedido_id' => $pedido->id,
            'repartidor_id' => $data['repartidor_id'] ?? $pedido->repartidor_id,
            'estado' => $data['estado'],
            'mensaje' => 'Estado actualizado desde app repartidor.',
        ]);

        app(SdiRegistry::class)->syncPedido($pedido);

        return ['ok' => true, 'pedido' => $pedido->fresh(['tracking'])];
    }

    public function gps(Request $request, Pedido $pedido)
    {
        $data = $request->validate([
            'repartidor_id' => ['nullable', 'integer'],
            'latitud' => ['required', 'numeric'],
            'longitud' => ['required', 'numeric'],
        ]);

        TrackingEvento::create([
            'pedido_id' => $pedido->id,
            'repartidor_id' => $data['repartidor_id'] ?? $pedido->repartidor_id,
            'estado' => 'ubicacion',
            'mensaje' => 'Ubicacion actualizada.',
            'latitud' => $data['latitud'],
            'longitud' => $data['longitud'],
        ]);

        return ['ok' => true];
    }

    public function fotoEntrega(Request $request, Pedido $pedido)
    {
        $request->validate([
            'foto_entrega' => ['required', 'image', 'max:4096'],
            'repartidor_id' => ['nullable', 'integer'],
        ]);

        $path = $request->file('foto_entrega')->store('entregas', 'public');

        $pedido->update(['estado' => 'entregado', 'entregado_at' => now()]);
        TrackingEvento::create([
            'pedido_id' => $pedido->id,
            'repartidor_id' => $request->integer('repartidor_id') ?: $pedido->repartidor_id,
            'estado' => 'entregado',
            'mensaje' => 'Entrega registrada con foto.',
            'foto_entrega' => $path,
        ]);

        app(SdiRegistry::class)->syncPedido($pedido);

        return ['ok' => true, 'foto' => asset('storage/' . $path)];
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
