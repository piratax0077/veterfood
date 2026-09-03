<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SdiHubClient
{
    private function http(): PendingRequest
    {
        return Http::acceptJson()->timeout(config('sdi_hub.timeout', 15))->withHeaders([
            'X-SDI-App' => (string) config('sdi_hub.app'),
            'X-SDI-Key' => (string) config('sdi_hub.key'),
        ]);
    }

    private function url(string $path): string { return rtrim((string) config('sdi_hub.url'), '/').'/api/v1/'.ltrim($path, '/'); }

    public function health(): array { return $this->http()->get($this->url('health'))->throw()->json(); }

    public function modules(): array { return $this->http()->get($this->url('modules'))->throw()->json(); }

    public function publish(string $type, array $payload, ?string $destination = null, ?string $entityType = null, string|int|null $entityId = null, ?string $idempotencyKey = null): array
    {
        return $this->http()->post($this->url('events'), [
            'event_type' => $type, 'payload' => $payload, 'destination' => $destination,
            'entity_type' => $entityType, 'entity_id' => $entityId,
            'idempotency_key' => $idempotencyKey ?: Str::uuid()->toString(),
        ])->throw()->json();
    }

    public function inbox(int $limit = 50): array { return $this->http()->get($this->url('events'), ['limit' => $limit])->throw()->json(); }

    public function acknowledge(string $eventId, bool $processed = true, ?string $error = null): array
    {
        return $this->http()->post($this->url("events/{$eventId}/ack"), ['status' => $processed ? 'processed' : 'failed', 'error' => $error])->throw()->json();
    }
}
