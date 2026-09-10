@extends('layouts.app')

@section('title', 'Panel contable institucion')

@section('content')
<style>
    .client-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
    .client-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}
    .client-icon{width:46px;height:46px;border-radius:12px;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;font-weight:900;box-shadow:0 10px 20px rgba(15,118,110,.22)}
    .metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}
    .metric-card,.panel-card{background:#fff;border:1px solid #dbe3ee;border-radius:10px;padding:20px;box-shadow:0 10px 24px rgba(15,23,42,.07);margin-bottom:16px}
    .metric-card span{display:block;color:#52617a;font-weight:800;margin-bottom:8px}.metric-card strong{display:block;color:#061a3d;font-size:25px}
    .form-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end}.span-2{grid-column:span 2}.span-4{grid-column:span 4}
    .client-tabs{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;background:#fff;border:1px solid #dbe3ee;border-radius:10px;padding:10px}
    .client-tabs a{background:#e5e7eb;color:#061a3d;border-radius:7px;padding:11px 16px;font-weight:900;text-decoration:none}.client-tabs a:first-child{background:#2563eb;color:#fff}
    .table-wrap{overflow-x:auto}.pill{display:inline-flex;border-radius:999px;background:#dbeafe;color:#1e3a8a;padding:5px 9px;font-weight:900;font-size:12px}.muted{color:#52617a}
    .section-note{background:#f8fafc;border:1px solid #e5edf6;border-radius:9px;padding:12px;margin:10px 0 16px;color:#52617a}
    @media(max-width:950px){.metric-grid,.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.span-4{grid-column:span 2}}
    @media(max-width:620px){.metric-grid,.form-grid{grid-template-columns:1fr}.span-2,.span-4{grid-column:auto}.client-title{font-size:28px}}
</style>

<div class="client-head">
    <a class="btn btn-secondary" href="{{ route('contabilidad.panel') }}">Mis instituciones</a>
    <h1 class="client-title"><span class="client-icon">C</span>{{ $centroMedico->nombre_fantasia ?: $centroMedico->razon_social }}</h1>
</div>


<form method="GET" class="panel-card">
    <div class="form-grid">
        <div><label class="floating-label-activo-sm">Desde</label><input class="form-control form-control-sm" name="desde" type="date" value="{{ $desde->format('Y-m-d') }}"></div>
        <div><label class="floating-label-activo-sm">Hasta</label><input class="form-control form-control-sm" name="hasta" type="date" value="{{ $hasta->format('Y-m-d') }}"></div>
        <button class="btn" type="submit">Actualizar dashboard</button>
    </div>
</form>

@php
    $indicadores = [
        ['Ingresos periodo', '$'.number_format($resumen['ingresos'], 0, ',', '.')],
        ['Egresos periodo', '$'.number_format($resumen['egresos'], 0, ',', '.')],
        ['Resultado', '$'.number_format($resumen['resultado'], 0, ',', '.')],
        ['Por pagar', '$'.number_format($resumen['cuentas_por_pagar'], 0, ',', '.')],
        ['Por cobrar', '$'.number_format($resumen['cuentas_por_cobrar'], 0, ',', '.')],
        ['Remuneraciones pendientes', '$'.number_format($resumen['remuneraciones_pendientes'], 0, ',', '.')],
        ['Personal activo', $resumen['trabajadores_activos']],
        ['Requerimientos abiertos', $resumen['requerimientos_pendientes']],
    ];
@endphp

<div class="metric-grid">
    @foreach($indicadores as [$titulo, $valor])
        <div class="metric-card">
            <span>{{ $titulo }}</span>
            <strong>{{ $valor }}</strong>
        </div>
    @endforeach
</div>

<div class="client-tabs">
    <a href="#documentos">Subir documentos</a>
    <a href="#solicitudes">Nuevo requerimiento</a>
    <a href="#requerimientos">Seguimiento</a>
    <a href="#recibidos">Documentos recibidos</a>
    <a href="#contador">Documentos del contador</a>
</div>

<div id="documentos" class="panel-card">
    <h2>Subir documento para contabilidad</h2>
    <p class="muted">El archivo queda asociado solo a esta institucion y el contador lo revisa dentro de su panel multi-cliente.</p>
    <form method="POST" enctype="multipart/form-data" action="{{ route('contabilidad.cliente.documentos.store', ['centroMedico' => $centroMedico->id]) }}">
        @csrf
        <div class="form-grid">
            <div><label class="floating-label-activo-sm">Clasificacion</label><select class="form-control form-control-sm" name="clasificacion" required><option value="ventas">Ventas</option><option value="compras">Compras</option><option value="remuneraciones">Remuneraciones</option><option value="impuestos">Impuestos</option><option value="contratos">Contratos</option><option value="finiquitos">Finiquitos</option><option value="otros">Otros</option></select></div>
            <div><label class="floating-label-activo-sm">Naturaleza</label><select class="form-control form-control-sm" name="naturaleza" required><option value="venta">Venta / ingreso</option><option value="compra">Compra / egreso</option></select></div>
            <div><label class="floating-label-activo-sm">Tipo documento</label><select class="form-control form-control-sm" name="tipo_documento" required><option value="factura">Factura</option><option value="boleta">Boleta</option><option value="nota_credito">Nota credito</option><option value="nota_debito">Nota debito</option><option value="guia_despacho">Guia despacho</option><option value="otro">Otro</option></select></div>
            <div><label class="floating-label-activo-sm">Folio</label><input class="form-control form-control-sm" name="folio"></div>
            <div><label class="floating-label-activo-sm">Fecha emision</label><input class="form-control form-control-sm" name="fecha_emision" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
            <div><label class="floating-label-activo-sm">Total documento</label><input class="form-control form-control-sm" name="monto_total" type="number" min="0" required></div>
            <div class="span-2"><label class="floating-label-activo-sm">Archivo</label><input class="form-control form-control-sm" name="documento" type="file" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones" rows="2" placeholder="Factura proveedor, contrato firmado, liquidacion mensual, pago impuesto..."></textarea></div>
            <button class="btn" type="submit">Subir y clasificar</button>
        </div>
    </form>
</div>

<div id="solicitudes" class="panel-card">
    <h2>Nuevo requerimiento para el contador</h2>
    <div class="section-note">La institucion solicita documentos o gestiones. El contador prepara contrato, anexo, finiquito, liquidacion u otra respuesta y la deja disponible para firma digital.</div>
    <form method="POST" enctype="multipart/form-data" action="{{ route('contabilidad.cliente.solicitudes.store', ['centroMedico' => $centroMedico->id]) }}">
        @csrf
        <div class="form-grid">
            <div><label class="floating-label-activo-sm">Tipo requerimiento</label><select class="form-control form-control-sm" name="tipo_solicitud" required><option value="contrato">Contrato</option><option value="anexo_contrato">Anexo contrato</option><option value="despido">Despido</option><option value="vacaciones">Vacaciones</option><option value="variacion_sueldo">Variacion de sueldo</option><option value="cambio_afp">Cambio de AFP</option><option value="licencia">Licencia</option><option value="finiquito">Finiquito</option><option value="liquidacion">Liquidacion</option><option value="pago_vouchers">Pago vouchers</option><option value="declaracion_impuestos">Declaracion impuestos</option><option value="pago_cotizaciones">Pago cotizaciones</option><option value="pago_seguro_cesantia">Pago seguro cesantia</option><option value="pago_caja_compensacion">Pago caja compensacion</option><option value="pago_salud">Pago salud</option><option value="otro">Otro</option></select></div>
            <div><label class="floating-label-activo-sm">Prioridad</label><select class="form-control form-control-sm" name="prioridad"><option value="normal">Normal</option><option value="alta">Alta</option><option value="urgente">Urgente</option></select></div>
            <div><label class="floating-label-activo-sm">Trabajador</label><input class="form-control form-control-sm" name="trabajador" placeholder="Nombre completo"></div>
            <div><label class="floating-label-activo-sm">RUT trabajador</label><input class="form-control form-control-sm" name="rut_trabajador"></div>
            <div><label class="floating-label-activo-sm">Email trabajador</label><input class="form-control form-control-sm" name="email_trabajador" type="email"></div>
            <div><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono_trabajador"></div>
            <div><label class="floating-label-activo-sm">Cargo</label><input class="form-control form-control-sm" name="cargo" placeholder="Cargo contractual"></div>
            <div><label class="floating-label-activo-sm">Tipo contrato</label><select class="form-control form-control-sm" name="tipo_contrato"><option value="">No aplica</option><option value="indefinido">Indefinido</option><option value="plazo_fijo">Plazo fijo</option><option value="honorarios">Honorarios</option><option value="prestacion_servicios">Prestacion servicios</option></select></div>
            <div><label class="floating-label-activo-sm">Inicio</label><input class="form-control form-control-sm" name="fecha_inicio" type="date"></div>
            <div><label class="floating-label-activo-sm">Termino</label><input class="form-control form-control-sm" name="fecha_termino" type="date"></div>
            <div><label class="floating-label-activo-sm">Fecha requerida</label><input class="form-control form-control-sm" name="fecha_requerida" type="date"></div>
            <div><label class="floating-label-activo-sm">Sueldo base</label><input class="form-control form-control-sm" name="sueldo_base" type="number" min="0"></div>
            <div><label class="floating-label-activo-sm">Monto imponible</label><input class="form-control form-control-sm" name="monto_imponible" type="number" min="0"></div>
            <div><label class="floating-label-activo-sm">Horas semanales</label><input class="form-control form-control-sm" name="horas_semanales" type="number" min="1" max="60"></div>
            <div><label class="floating-label-activo-sm">AFP</label><input class="form-control form-control-sm" name="afp"></div>
            <div><label class="floating-label-activo-sm">Salud</label><select class="form-control form-control-sm" name="tipo_salud"><option value="">Seleccionar</option><option value="fonasa">FONASA</option><option value="isapre">ISAPRE</option><option value="ffaa">FF.AA.</option><option value="otro">Otro</option></select></div>
            <div><label class="floating-label-activo-sm">Institucion salud</label><input class="form-control form-control-sm" name="salud_previsional"></div>
            <div><label class="floating-label-activo-sm">Caja compensacion</label><input class="form-control form-control-sm" name="caja_compensacion"></div>
            <div><label class="floating-label-activo-sm">Mutualidad</label><input class="form-control form-control-sm" name="mutualidad"></div>
            <div><label class="floating-label-activo-sm">Cargas</label><input class="form-control form-control-sm" name="cargas_familiares" type="number" min="0" max="30"></div>
            <div class="span-4"><label class="floating-label-activo-sm">Funciones dentro de la empresa</label><textarea class="form-control form-control-sm" name="funciones" rows="2" placeholder="Funciones, lugar de trabajo, responsabilidades y condiciones especiales."></textarea></div>
            <div class="span-4"><label class="floating-label-activo-sm">Detalle para el contador</label><textarea class="form-control form-control-sm" name="detalle" rows="3" required placeholder="Instrucciones, condiciones, fechas y documentos que deben quedar para firma."></textarea></div>
            <div class="span-2"><label class="floating-label-activo-sm">Archivo respaldo</label><input class="form-control form-control-sm" name="archivo" type="file"></div>
            <button class="btn" type="submit">Enviar requerimiento</button>
        </div>
    </form>
</div>

<div id="requerimientos" class="panel-card">
    <h2>Seguimiento de requerimientos</h2>
    <p class="muted">Cada requerimiento queda asociado a esta institucion. El contador lo prepara y el documento final queda disponible para firma.</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Codigo</th><th>Tipo</th><th>Trabajador</th><th>Prioridad</th><th>Estado</th><th>Documento</th></tr></thead>
            <tbody>
                @forelse($requerimientos as $requerimiento)
                    <tr>
                        <td>{{ $requerimiento->codigo }}</td>
                        <td>{{ str_replace('_', ' ', $requerimiento->tipo) }}</td>
                        <td>{{ data_get($requerimiento->datos, 'trabajador', 'No informado') }}<br><span class="muted">{{ data_get($requerimiento->datos, 'rut_trabajador') }}</span></td>
                        <td><span class="pill">{{ $requerimiento->prioridad }}</span></td>
                        <td><span class="pill">{{ str_replace('_', ' ', $requerimiento->estado) }}</span></td>
                        <td>{{ $requerimiento->archivo_respuesta ? 'Disponible para firma' : 'En proceso' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Sin requerimientos enviados.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="recibidos" class="panel-card">
    <h2>Documentos recibidos</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Tipo</th><th>Folio</th><th>Total</th><th>Estado</th><th>Archivo</th></tr></thead>
            <tbody>
                @forelse($documentos as $documento)
                    <tr>
                        <td>{{ $documento->fecha_emision?->format('d-m-Y') }}</td>
                        <td>{{ $documento->naturaleza }} / {{ $documento->tipo_documento }}</td>
                        <td>{{ $documento->folio ?: '#'.$documento->id }}</td>
                        <td>${{ number_format($documento->total, 0, ',', '.') }}</td>
                        <td><span class="pill">{{ $documento->estado }}</span></td>
                        <td>{{ $documento->archivo ? 'Recibido' : '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Sin documentos recibidos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $documentos->links() }}
</div>

<div id="contador" class="panel-card">
    <h2>Documentos preparados por el contador</h2>
    <p class="muted">Aqui quedan disponibles liquidaciones, pagos de vouchers, contratos, finiquitos, impuestos y respuestas preparadas por el contador.</p>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Fecha</th><th>Requerimiento</th><th>Detalle</th><th>Estado</th><th>Firma</th><th>Accion</th></tr></thead>
            <tbody>
                @forelse($requerimientos->where('estado', 'listo_para_firma') as $requerimiento)
                    <tr>
                        <td>{{ $requerimiento->respondido_at?->format('d-m-Y') }}</td>
                        <td>{{ $requerimiento->codigo }}</td>
                        <td>{{ $requerimiento->titulo }}</td>
                        <td><span class="pill">{{ str_replace('_', ' ', $requerimiento->estado) }}</span></td>
                        <td>{{ $requerimiento->firmado_institucion_at ? 'Firmado institucion' : ($requerimiento->requiere_firma_trabajador ? 'Institucion + trabajador' : 'Institucion') }}</td>
                        <td>
                            @if(!$requerimiento->firmado_institucion_at)
                                <form method="POST" action="{{ route('contabilidad.requerimientos.firmar', ['centroMedico' => $centroMedico->id, 'requerimiento' => $requerimiento->id]) }}">
                                    @csrf
                                    <input type="hidden" name="firmante" value="institucion">
                                    <button class="btn" type="submit">Firmar</button>
                                </form>
                            @else
                                <span class="pill">OK</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="muted">Sin documentos preparados por el contador.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
