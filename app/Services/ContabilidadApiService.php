<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ContabilidadApiService
{
    private string $baseUrl;
    private string $token;
    private string $clienteUuid;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.contabilidad.url'), '/');
        $this->token = (string) config('services.contabilidad.token');
        $this->clienteUuid = (string) config('services.contabilidad.cliente_uuid');
    }

    public function configurado(): bool
    {
        return $this->baseUrl !== '' && $this->token !== '' && $this->clienteUuid !== '';
    }

    public function resumen(): array
    {
        return $this->request()->get($this->clienteUrl('resumen'))->throw()->json();
    }

    public function documentos(array $filtros = []): array
    {
        return $this->request()->get($this->clienteUrl('documentos'), $filtros)->throw()->json();
    }

    public function registrarDocumento(array $documento): array
    {
        return $this->request()->post($this->clienteUrl('documentos'), $documento)->throw()->json();
    }

    public function movimientos(array $filtros = []): array
    {
        return $this->request()->get($this->clienteUrl('movimientos'), $filtros)->throw()->json();
    }

    public function registrarMovimiento(array $movimiento): array
    {
        return $this->request()->post($this->clienteUrl('movimientos'), $movimiento)->throw()->json();
    }

    public function contadores(): array
    {
        return $this->request()->get($this->clienteUrl('contadores'))->throw()->json();
    }

    public function registrarContador(array $contador): array
    {
        return $this->request()->post($this->clienteUrl('contadores'), $contador)->throw()->json();
    }

    public function clienteUuid(): string
    {
        return $this->clienteUuid;
    }

    private function request(): PendingRequest
    {
        if (!$this->configurado()) {
            throw new RuntimeException('La integracion con Contabilidad API no esta configurada.');
        }

        return Http::acceptJson()
            ->asJson()
            ->withToken($this->token)
            ->connectTimeout((int) config('services.contabilidad.connect_timeout', 3))
            ->timeout((int) config('services.contabilidad.timeout', 10))
            ->retry(2, 200, throw: false);
    }

    private function clienteUrl(string $recurso): string
    {
        return "{$this->baseUrl}/clientes/{$this->clienteUuid}/{$recurso}";
    }
}
