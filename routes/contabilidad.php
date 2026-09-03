<?php

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
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin,contabilidad,central_ventas,secretaria,secretaria_veterchile,secretaria_clinica', '2fa', 'secure.session', 'contabilidad.centro'])
    ->prefix('centros-medicos/{centroMedico}/contabilidad')
    ->name('contabilidad.')
    ->group(function () {
        Route::get('/', [EscritorioContabilidadController::class, 'index'])->name('escritorio');
        Route::get('cliente', [EscritorioContabilidadController::class, 'cliente'])->name('cliente');
        Route::post('cliente/documentos', [EscritorioContabilidadController::class, 'subirDocumentoCliente'])->name('cliente.documentos.store');
        Route::post('cliente/solicitudes', [EscritorioContabilidadController::class, 'solicitarDocumentoCliente'])->name('cliente.solicitudes.store');
        Route::get('requerimientos', [EscritorioContabilidadController::class, 'requerimientos'])->name('requerimientos.index');
        Route::post('requerimientos/{requerimiento}/firmar', [EscritorioContabilidadController::class, 'firmarRequerimiento'])->name('requerimientos.firmar');
        Route::post('entregas-contador', [EscritorioContabilidadController::class, 'entregarDocumentoContador'])->name('entregas.store');
        Route::get('secciones/{seccion}', [EscritorioContabilidadController::class, 'seccion'])->name('secciones.show');
        Route::get('trabajadores/crear', [EscritorioContabilidadController::class, 'crearTrabajador'])->name('trabajadores.create');
        Route::get('trabajadores/{trabajador}/editar', [EscritorioContabilidadController::class, 'editarTrabajador'])->name('trabajadores.edit');
        Route::get('trabajadores/{trabajador}/gestion', [EscritorioContabilidadController::class, 'gestionarTrabajador'])->name('trabajadores.gestion');

        Route::apiResource('trabajadores', TrabajadorController::class)
            ->parameters(['trabajadores' => 'trabajador']);
        Route::post('trabajadores/{trabajador}/contratos', [ContratoController::class, 'store'])->name('contratos.store');
        Route::put('contratos/{contrato}', [ContratoController::class, 'update'])->name('contratos.update');
        Route::post('contratos/{contrato}/finalizar', [ContratoController::class, 'finalizar'])->name('contratos.finalizar');

        Route::get('remuneraciones', [RemuneracionController::class, 'index'])->name('remuneraciones.index');
        Route::post('remuneraciones', [RemuneracionController::class, 'store'])->name('remuneraciones.store');
        Route::put('remuneraciones/{remuneracion}', [RemuneracionController::class, 'update'])->name('remuneraciones.update');
        Route::post('remuneraciones/{remuneracion}/pagar', [RemuneracionController::class, 'pagar'])->name('remuneraciones.pagar');

        Route::post('ausencias', [AusenciaController::class, 'storeSolicitud'])->name('ausencias.store');
        Route::post('ausencias/{solicitud}/autorizar', [AusenciaController::class, 'autorizar'])->name('ausencias.autorizar');
        Route::get('licencias-medicas', [AusenciaController::class, 'licencias'])->name('licencias.index');
        Route::post('licencias-medicas', [AusenciaController::class, 'storeLicencia'])->name('licencias.store');
        Route::put('licencias-medicas/{licencia}', [AusenciaController::class, 'actualizarLicencia'])->name('licencias.update');
        Route::post('finiquitos', [FiniquitoController::class, 'store'])->name('finiquitos.store');
        Route::put('finiquitos/{finiquito}', [FiniquitoController::class, 'update'])->name('finiquitos.update');
        Route::post('finiquitos/{finiquito}/pagar', [FiniquitoController::class, 'pagar'])->name('finiquitos.pagar');

        Route::apiResource('movimientos', MovimientoContableController::class)->except('show');
        Route::get('documentos-tributarios', [DocumentoTributarioController::class, 'index'])->name('documentos.index');
        Route::get('documentos-tributarios/{documento}/emitir', [DocumentoTributarioController::class, 'emitir'])->name('documentos.emitir');
        Route::get('documentos-tributarios/{documento}/archivo', [DocumentoTributarioController::class, 'archivo'])->name('documentos.archivo');
        Route::get('documentos-tributarios/{documento}', [DocumentoTributarioController::class, 'show'])->name('documentos.show');
        Route::post('documentos-tributarios', [DocumentoTributarioController::class, 'store'])->name('documentos.store');
        Route::post('documentos-tributarios/{documento}/liquidar', [DocumentoTributarioController::class, 'liquidar'])->name('documentos.liquidar');
        Route::apiResource('terceros', TerceroController::class)->only(['index', 'store', 'update']);
        Route::apiResource('convenios', ConvenioController::class)->only(['index', 'store', 'update']);
    });
