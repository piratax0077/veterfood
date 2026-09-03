<?php

namespace App\Console\Commands;

use App\Services\ContabilidadApiService;
use Illuminate\Console\Command;
use Throwable;

class ProbarContabilidadApi extends Command
{
    protected $signature = 'contabilidad:probar';
    protected $description = 'Verifica token, aislamiento y comunicacion con Contabilidad API';

    public function handle(ContabilidadApiService $contabilidad): int
    {
        try {
            $resumen = $contabilidad->resumen();
            $this->info('Conexion correcta con Contabilidad API.');
            $this->line('Cliente: '.data_get($resumen, 'cliente.nombre'));
            $this->line('UUID: '.$contabilidad->clienteUuid());
            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error('No fue posible conectar con Contabilidad API: '.$exception->getMessage());
            return self::FAILURE;
        }
    }
}
