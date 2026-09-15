<?php

namespace App\Http\Controllers;

use App\Services\ContabilidadApiService;
use Throwable;

class ContabilidadIntegracionController extends Controller
{
    public function index(ContabilidadApiService $contabilidad)
    {
        $estado = ['conectado'=>false, 'mensaje'=>'Sin configuración', 'resumen'=>null];
        try {
            $estado = ['conectado'=>true, 'mensaje'=>'Conexión activa', 'resumen'=>$contabilidad->resumen()];
        } catch (Throwable $exception) {
            $estado['mensaje'] = $exception->getMessage();
        }

        return view('integraciones.contabilidad', compact('estado'));
    }
}
