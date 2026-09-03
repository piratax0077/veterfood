<?php

use App\Http\Controllers\Api\AuthTokenController;
use App\Http\Controllers\Contabilidad\AusenciaController;
use App\Http\Controllers\Contabilidad\ContratoController;
use App\Http\Controllers\Contabilidad\ConvenioController;
use App\Http\Controllers\Contabilidad\DocumentoTributarioController;
use App\Http\Controllers\Contabilidad\EscritorioContabilidadController;
use App\Http\Controllers\Contabilidad\FiniquitoController;
use App\Http\Controllers\Contabilidad\MovimientoContableController;
use App\Http\Controllers\Contabilidad\RemuneracionController;
use App\Http\Controllers\Contabilidad\TrabajadorController;
use App\Http\Controllers\Contabilidad\TerceroController;
use App\Http\Middleware\EnsureContabilidadAbility;
use App\Http\Middleware\VerifyCentroMedicoAccess;
use Illuminate\Support\Facades\Route;

Route::post('auth/token', [AuthTokenController::class, 'login'])
    ->middleware('throttle:5,1');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::delete('auth/token', [AuthTokenController::class, 'logout']);

    Route::prefix('centros-medicos/{centroMedico}/contabilidad')
        ->middleware(VerifyCentroMedicoAccess::class)
        ->group(function () {
            Route::middleware(EnsureContabilidadAbility::class . ':contabilidad:read')->group(function () {
                Route::get('resumen', [EscritorioContabilidadController::class, 'resumen']);
                Route::get('trabajadores', [TrabajadorController::class, 'index']);
                Route::get('trabajadores/{trabajador}', [TrabajadorController::class, 'show']);
                Route::get('remuneraciones', [RemuneracionController::class, 'index']);
                Route::get('movimientos', [MovimientoContableController::class, 'index']);
                Route::get('licencias-medicas', [AusenciaController::class, 'licencias']);
                Route::get('terceros', [TerceroController::class, 'index']);
                Route::get('documentos-tributarios', [DocumentoTributarioController::class, 'index']);
                Route::get('documentos-tributarios/{documento}', [DocumentoTributarioController::class, 'show']);
                Route::get('convenios', [ConvenioController::class, 'index']);
                Route::get('requerimientos', [EscritorioContabilidadController::class, 'requerimientos']);
            });

            Route::middleware(EnsureContabilidadAbility::class . ':contabilidad:write')->group(function () {
                Route::post('trabajadores', [TrabajadorController::class, 'store']);
                Route::put('trabajadores/{trabajador}', [TrabajadorController::class, 'update']);
                Route::delete('trabajadores/{trabajador}', [TrabajadorController::class, 'destroy']);
                Route::post('trabajadores/{trabajador}/contratos', [ContratoController::class, 'store']);
                Route::put('contratos/{contrato}', [ContratoController::class, 'update']);
                Route::post('contratos/{contrato}/finalizar', [ContratoController::class, 'finalizar']);

                Route::post('remuneraciones', [RemuneracionController::class, 'store']);
                Route::put('remuneraciones/{remuneracion}', [RemuneracionController::class, 'update']);
                Route::post('ausencias', [AusenciaController::class, 'storeSolicitud']);
                Route::post('ausencias/{solicitud}/autorizar', [AusenciaController::class, 'autorizar']);
                Route::post('licencias-medicas', [AusenciaController::class, 'storeLicencia']);
                Route::put('licencias-medicas/{licencia}', [AusenciaController::class, 'actualizarLicencia']);
                Route::post('finiquitos', [FiniquitoController::class, 'store']);
                Route::put('finiquitos/{finiquito}', [FiniquitoController::class, 'update']);

                Route::post('movimientos', [MovimientoContableController::class, 'store']);
                Route::put('movimientos/{movimiento}', [MovimientoContableController::class, 'update']);
                Route::delete('movimientos/{movimiento}', [MovimientoContableController::class, 'destroy']);
                Route::post('documentos-tributarios', [DocumentoTributarioController::class, 'store']);
                Route::post('requerimientos', [EscritorioContabilidadController::class, 'crearRequerimientoApi']);
                Route::post('terceros', [TerceroController::class, 'store']);
                Route::put('terceros/{tercero}', [TerceroController::class, 'update']);
                Route::post('convenios', [ConvenioController::class, 'store']);
                Route::put('convenios/{convenio}', [ConvenioController::class, 'update']);
            });

            Route::middleware(EnsureContabilidadAbility::class . ':contabilidad:invoice-upload')->group(function () {
                Route::post('facturas-emitidas', [DocumentoTributarioController::class, 'subirFacturaEmitida']);
            });

            Route::middleware(EnsureContabilidadAbility::class . ':contabilidad:form-upload')->group(function () {
                Route::post('requerimientos-cliente', [EscritorioContabilidadController::class, 'crearRequerimientoApi']);
                Route::post('requerimientos/{requerimiento}/firmar', [EscritorioContabilidadController::class, 'firmarRequerimiento']);
            });

            Route::middleware(EnsureContabilidadAbility::class . ':contabilidad:pay')->group(function () {
                Route::post('remuneraciones/{remuneracion}/pagar', [RemuneracionController::class, 'pagar']);
                Route::post('finiquitos/{finiquito}/pagar', [FiniquitoController::class, 'pagar']);
                Route::post('documentos-tributarios/{documento}/liquidar', [DocumentoTributarioController::class, 'liquidar']);
            });
        });
});
