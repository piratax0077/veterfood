<?php

namespace App\Http\Controllers;

use App\Services\ContabilidadApiService;
use Throwable;

class ContabilidadIntegracionController extends Controller
{
    public function index(ContabilidadApiService $contabilidad)
    {
        $estado = ['conectado'=>false, 'mensaje'=>'Sin configuracion', 'resumen'=>null];
        try {
            $estado = ['conectado'=>true, 'mensaje'=>'Conexion activa', 'resumen'=>$contabilidad->resumen()];
        } catch (Throwable $exception) {
            $estado['mensaje'] = $exception->getMessage();
        }

        return view('integraciones.contabilidad', compact('estado'));
    }
}
