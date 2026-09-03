<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SdiRegistry
{
    public function tutor(array $data): ?array { return $this->post('tutors', $data); }
    public function pet(array $data): ?array { return $this->post('pets', $data); }
    public function order(array $data): ?array { return $this->post('orders', $data); }

    public function syncPedido(Pedido $pedido): void
    {
        $pedido->loadMissing(['items', 'cliente']);
        $cliente = $pedido->cliente;
        $email = $pedido->cliente_email ?: $cliente?->email;
        $tutor = $this->tutor([
            'rut' => $cliente?->rut,
            'email' => $email,
            'name' => $pedido->cliente_nombre ?: $cliente?->name ?: 'Cliente',
            'phone' => $pedido->cliente_telefono,
            'source_id' => (string) ($pedido->user_id ?: 'pedido-'.$pedido->id),
            'metadata' => ['origin' => 'pedido_comercio'],
        ]);

        $tutorUuid = data_get($tutor, 'tutor.id');
        if (!$tutorUuid) return;

        $this->order([
            'tutor_uuid' => $tutorUuid,
            'source_id' => (string) $pedido->id,
            'status' => $this->centralStatus((string) $pedido->estado),
            'total' => (float) $pedido->total,
            'currency' => 'CLP',
            'tracking_code' => $pedido->codigo_tracking,
            'items' => $pedido->items->map(fn ($item) => [
                'name' => $item->producto_nombre,
                'quantity' => (int) $item->cantidad,
                'unit_price' => (float) $item->precio_unitario,
                'total' => (float) $item->total,
            ])->values()->all(),
            'metadata' => [
                'payment_status' => $pedido->estado_pago,
                'delivery_address' => $pedido->direccion_entrega,
                'delivery_date' => optional($pedido->fecha_entrega)->format('Y-m-d'),
                'courier_id' => $pedido->repartidor_id,
            ],
        ]);
    }

    private function centralStatus(string $status): string
    {
        return match ($status) {
            'pagado', 'recibido' => 'received',
            'preparando', 'en_preparacion' => 'preparing',
            'listo_despacho' => 'ready',
            'asignado', 'reparto_asignado' => 'assigned',
            'en_ruta', 'en_camino' => 'in_transit',
            'entregado' => 'delivered',
            'cancelado', 'cancelled' => 'cancelled',
            default => 'received',
        };
    }

    private function post(string $resource, array $data): ?array
    {
        if (!config('services.sdi_hub.enabled')) return null;
        try {
            $response = Http::acceptJson()->timeout((int) config('services.sdi_hub.timeout', 5))
                ->withHeaders(['X-SDI-App' => config('services.sdi_hub.app'), 'X-SDI-Key' => config('services.sdi_hub.key')])
                ->post(rtrim(config('services.sdi_hub.url'), '/').'/api/v1/registry/'.$resource, $data);
            if ($response->failed()) Log::warning('SDI Hub rechazó sincronización', ['resource' => $resource, 'status' => $response->status(), 'body' => $response->json()]);
            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('SDI Hub no disponible; la operación local continúa', ['resource' => $resource, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
