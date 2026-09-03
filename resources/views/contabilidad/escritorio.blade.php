@extends('layouts.app')

@section('title', 'Escritorio contabilidad')

@section('content')
<style>
    .acct-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
    .acct-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}
    .acct-icon{width:44px;height:44px;border-radius:10px;background:#0f172a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;box-shadow:0 7px 16px rgba(15,23,42,.18)}
    .filter-card,.module-card,.metric-card,.api-card,.inbox-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;box-shadow:0 3px 10px rgba(15,23,42,.07)}
    .filter-card{margin-bottom:16px}.filter-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;align-items:end}
    .metric-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin:16px 0}
    .metric-card span{display:block;color:#52617a;font-weight:800;margin-bottom:8px}.metric-card strong{display:block;color:#061a3d;font-size:25px}
    .module-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:16px 0}
    .module-card{min-height:154px;display:flex;flex-direction:column;gap:10px;text-decoration:none;color:#061a3d;transition:transform .14s ease,box-shadow .14s ease}
    .module-card:hover{transform:translateY(-2px);box-shadow:0 9px 20px rgba(15,23,42,.12)}
    .module-top{display:flex;align-items:center;gap:10px}.module-icon{width:38px;height:38px;border-radius:10px;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900}
    .module-card h2{font-size:20px;margin:0}.module-card p{margin:0;color:#52617a;line-height:1.35}.module-button{margin-top:auto;background:#2563eb;color:#fff;border-radius:7px;padding:10px 12px;text-align:center;font-weight:900}
    .api-box{background:#111827;color:#e5e7eb;border-radius:8px;padding:14px;font-family:Consolas,monospace;font-size:13px;line-height:1.65;overflow:auto}
    .inbox-card{border-left:6px solid #f59e0b}.request-card{border-left:6px solid #2563eb}.inbox-list{display:grid;gap:10px}.inbox-item{display:grid;grid-template-columns:1fr auto;gap:12px;align-items:center;border:1px solid #e5edf6;border-radius:8px;padding:12px;background:#f8fafc}.inbox-item strong{color:#061a3d}.inbox-meta{color:#52617a;font-size:13px}.pill{display:inline-flex;border-radius:999px;background:#fef3c7;color:#92400e;padding:5px 9px;font-weight:900;font-size:12px}.helper-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.upload-list{margin:0;padding-left:18px;color:#52617a;line-height:1.5}.admin-comm-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}.admin-comm-card{border:1px solid #dbe3ee;border-radius:8px;padding:16px;background:#f8fafc}.admin-comm-card h3{margin:0 0 12px;color:#061a3d}.admin-comm-card .filter-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.wide,.full-row{grid-column:1/-1}.mini-section{margin:12px 0 4px;padding-top:10px;border-top:1px solid #dbe3ee;color:#061a3d;font-weight:900}.compact-table{width:100%;border-collapse:collapse}.compact-table th,.compact-table td{border-bottom:1px solid #dbe3ee;padding:9px;text-align:left;vertical-align:top}
    @media(max-width:1050px){.module-grid,.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.filter-grid{grid-template-columns:1fr 1fr}.admin-comm-grid,.helper-grid{grid-template-columns:1fr}}
    @media(max-width:650px){.module-grid,.metric-grid,.filter-grid{grid-template-columns:1fr}.acct-title{font-size:28px}.full-row{grid-column:auto}}
</style>

<div class="acct-head">
    <a class="btn btn-secondary" href="{{ auth()->user()?->tieneRol('admin') ? route('admin.contabilidad.integracion') : route('redirect.role') }}">Volver</a>
    <h1 class="acct-title"><span class="acct-icon">C</span>Escritorio contabilidad</h1>
    <a class="btn" href="#api-contable">API</a>
</div>

@if(session('status'))
    <div class="alert ok">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

<form method="GET" class="filter-card">
    <div class="filter-grid">
        <div>
            <label class="floating-label-activo-sm" for="desde">Desde</label>
            <input class="form-control form-control-sm" id="desde" name="desde" type="date" value="{{ $desde->format('Y-m-d') }}">
        </div>
        <div>
            <label class="floating-label-activo-sm" for="hasta">Hasta</label>
            <input class="form-control form-control-sm" id="hasta" name="hasta" type="date" value="{{ $hasta->format('Y-m-d') }}">
        </div>
        <button class="btn" type="submit">Actualizar resumen</button>
    </div>
</form>

@php
    $indicadores = [
        ['Personal activo', $resumen['trabajadores_activos']],
        ['Ingresos periodo', '$'.number_format($resumen['ingresos'], 0, ',', '.')],
        ['Egresos periodo', '$'.number_format($resumen['egresos'], 0, ',', '.')],
        ['Resultado', '$'.number_format($resumen['resultado'], 0, ',', '.')],
        ['Sueldos pendientes', '$'.number_format($resumen['remuneraciones_pendientes'], 0, ',', '.')],
        ['Requerimientos pendientes', $resumen['requerimientos_pendientes']],
        ['Por cobrar', '$'.number_format($resumen['cuentas_por_cobrar'], 0, ',', '.')],
        ['Por pagar', '$'.number_format($resumen['cuentas_por_pagar'], 0, ',', '.')],
    ];
    $modulos = [
        'rrhh' => ['Recursos humanos', 'Personal, contratos, cuentas y datos laborales.', 'RH', '#2563eb'],
        'info-pago-sueldos' => ['Info. sueldos personal', 'Pagos, bancos y estado del personal.', '$', '#0891b2'],
        'liquidaciones' => ['Liquidaciones profesionales', 'Honorarios, pagos y documentos pendientes.', 'L', '#15803d'],
        'remuneraciones' => ['Pago remuneraciones', 'Calculo mensual, descuentos y pagos.', 'R', '#7c3aed'],
        'contable' => ['Libro contable', 'Movimientos, conciliacion y resultado.', 'LC', '#0f172a'],
        'ingresos' => ['Ingresos', 'Ventas, cobros y documentos emitidos.', '+', '#16a34a'],
        'egresos' => ['Egresos', 'Compras, pagos, costos y gastos.', '-', '#dc2626'],
        'impuestos' => ['Impuestos', 'Resumen tributario y declaracion.', '%', '#ea580c'],
        'convenios' => ['Convenios', 'Instituciones, clientes convenio y acuerdos.', 'CV', '#0d9488'],
        'proveedores' => ['Proveedores', 'Proveedores, contacto y documentos.', 'P', '#64748b'],
        'factura' => ['Facturar', 'Boletas, facturas, compras y ventas.', 'F', '#f59e0b'],
        'estadisticas' => ['Estadisticas', 'Indicadores mensuales y analisis.', 'G', '#db2777'],
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

@if(auth()->user()?->tieneRol('admin'))
    <div class="api-card request-card">
        <h2>Comunicacion y documentos para el contador</h2>
        <p class="muted">Zona del administrador para enviar mensajes, completar datos, solicitar acciones y subir documentos. Todo queda asociado a esta institucion y visible para el contador.</p>
        <div class="admin-comm-grid">
            <form class="admin-comm-card" method="POST" enctype="multipart/form-data" action="{{ route('contabilidad.cliente.solicitudes.store', ['centroMedico' => $centroMedico->id]) }}" data-no-collapse="1">
                @csrf
                <h3>Mensaje / requerimiento</h3>
                <div class="filter-grid">
                    <div>
                        <label class="floating-label-activo-sm">Solicitud</label>
                        <select class="form-control form-control-sm" name="tipo_solicitud" required>
                            <option value="declaracion_impuestos">Declaracion impuestos</option>
                            <option value="pago_cotizaciones">Pago cotizaciones</option>
                            <option value="pago_seguro_cesantia">Pago seguro cesantia</option>
                            <option value="pago_caja_compensacion">Pago caja compensacion</option>
                            <option value="pago_salud">Pago salud</option>
                            <option value="contrato">Contrato trabajador</option>
                            <option value="finiquito">Finiquito</option>
                            <option value="liquidacion">Liquidacion</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Prioridad</label>
                        <select class="form-control form-control-sm" name="prioridad">
                            <option value="normal">Normal</option>
                            <option value="alta">Alta</option>
                            <option value="urgente">Urgente</option>
                        </select>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Fecha requerida</label>
                        <input class="form-control form-control-sm" name="fecha_requerida" type="date">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Trabajador / tercero</label>
                        <input class="form-control form-control-sm" name="trabajador" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">RUT</label>
                        <input class="form-control form-control-sm" name="rut_trabajador" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Email trabajador</label>
                        <input class="form-control form-control-sm" name="email_trabajador" type="email" placeholder="Opcional">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Telefono</label>
                        <input class="form-control form-control-sm" name="telefono_trabajador" placeholder="Opcional">
                    </div>
                    <div class="wide mini-section">Datos laborales opcionales</div>
                    <div>
                        <label class="floating-label-activo-sm">Cargo / funcion</label>
                        <input class="form-control form-control-sm" name="cargo" placeholder="Cargo">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Tipo contrato</label>
                        <select class="form-control form-control-sm" name="tipo_contrato">
                            <option value="">No aplica</option>
                            <option value="indefinido">Indefinido</option>
                            <option value="plazo_fijo">Plazo fijo</option>
                            <option value="honorarios">Honorarios</option>
                            <option value="prestacion_servicios">Prestacion servicios</option>
                        </select>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Inicio</label>
                        <input class="form-control form-control-sm" name="fecha_inicio" type="date">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Termino</label>
                        <input class="form-control form-control-sm" name="fecha_termino" type="date">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Sueldo base</label>
                        <input class="form-control form-control-sm" name="sueldo_base" type="number" min="0">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Imponible</label>
                        <input class="form-control form-control-sm" name="monto_imponible" type="number" min="0">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">AFP</label>
                        <input class="form-control form-control-sm" name="afp">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Salud</label>
                        <input class="form-control form-control-sm" name="salud_previsional" placeholder="Fonasa / Isapre">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Caja compensacion</label>
                        <input class="form-control form-control-sm" name="caja_compensacion">
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Mutualidad</label>
                        <input class="form-control form-control-sm" name="mutualidad">
                    </div>
                    <div class="wide">
                        <label class="floating-label-activo-sm">Funciones / instrucciones</label>
                        <textarea class="form-control form-control-sm" name="funciones" rows="2" placeholder="Funciones del cargo, cambios solicitados o instrucciones para documento laboral."></textarea>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Archivo</label>
                        <input class="form-control form-control-sm" name="archivo" type="file">
                    </div>
                    <div class="wide">
                        <label class="floating-label-activo-sm">Mensaje interno para el contador</label>
                        <textarea class="form-control form-control-sm" name="detalle" rows="3" required placeholder="Escriba la accion que necesita, periodo, monto, archivo adjunto o instruccion."></textarea>
                    </div>
                    <button class="btn" type="submit">Enviar al contador</button>
                </div>
            </form>

            <form class="admin-comm-card" method="POST" enctype="multipart/form-data" action="{{ route('contabilidad.cliente.documentos.store', ['centroMedico' => $centroMedico->id]) }}" data-no-collapse="1">
                @csrf
                <h3>Subir documento contable</h3>
                <div class="filter-grid">
                    <div>
                        <label class="floating-label-activo-sm">Tipo documento</label>
                        <select class="form-control form-control-sm" name="tipo_documento" required>
                            <option value="factura">Factura</option>
                            <option value="boleta">Boleta</option>
                            <option value="guia_despacho">Guia despacho</option>
                            <option value="nota_credito">Nota credito</option>
                            <option value="nota_debito">Nota debito</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Naturaleza</label>
                        <select class="form-control form-control-sm" name="naturaleza" required>
                            <option value="compra">Compra / gasto</option>
                            <option value="venta">Venta / ingreso</option>
                        </select>
                    </div>
                    <div>
                        <label class="floating-label-activo-sm">Clasificacion</label>
                        <select class="form-control form-control-sm" name="clasificacion" required>
                            <option value="compras">Compras</option>
                            <option value="ventas">Ventas</option>
                            <option value="impuestos">Impuestos</option>
                            <option value="remuneraciones">Remuneraciones</option>
                            <option value="contratos">Contratos</option>
                            <option value="finiquitos">Finiquitos</option>
                            <option value="otros">Otros</option>
                        </select>
                    </div>
                    <div><label class="floating-label-activo-sm">Folio</label><input class="form-control form-control-sm" name="folio"></div>
                    <div><label class="floating-label-activo-sm">Fecha</label><input class="form-control form-control-sm" name="fecha_emision" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                    <div><label class="floating-label-activo-sm">Monto total</label><input class="form-control form-control-sm" name="monto_total" type="number" min="0" required></div>
                    <div><label class="floating-label-activo-sm">Archivo</label><input class="form-control form-control-sm" name="documento" type="file" required></div>
                    <div class="wide"><label class="floating-label-activo-sm">Observaciones</label><textarea class="form-control form-control-sm" name="observaciones" rows="3" placeholder="Detalle, proveedor, periodo, instruccion para clasificar o comentario para el contador."></textarea></div>
                    <button class="btn" type="submit">Subir documento</button>
                </div>
            </form>

            <div class="admin-comm-card full-row">
                <h3>Seguimiento enviado al contador</h3>
                <div class="table-wrap">
                    <table class="compact-table">
                        <thead><tr><th>Fecha</th><th>Codigo</th><th>Solicitud</th><th>Estado</th><th>Prioridad</th></tr></thead>
                        <tbody>
                            @forelse($requerimientosRecientes as $requerimiento)
                                <tr>
                                    <td>{{ $requerimiento->created_at?->format('d-m-Y H:i') }}</td>
                                    <td>{{ $requerimiento->codigo }}</td>
                                    <td><strong>{{ $requerimiento->titulo }}</strong><br><span class="muted">{{ $requerimiento->detalle }}</span></td>
                                    <td><span class="pill">{{ $requerimiento->estado }}</span></td>
                                    <td>{{ $requerimiento->prioridad }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="muted">Aun no hay mensajes ni requerimientos enviados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@else
<div class="inbox-card">
    <h2>Bandeja de entrada del contador</h2>
    <p class="muted">Requerimientos enviados por esta institucion mediante el panel o API segura. Use esta lista para preparar contratos, declaraciones, cotizaciones, salud, caja de compensacion y documentos laborales.</p>
    <div class="inbox-list">
        @forelse($requerimientosPendientes as $requerimiento)
            <div class="inbox-item">
                <div>
                    <strong>{{ $requerimiento->codigo }} - {{ $requerimiento->titulo }}</strong>
                    <div class="inbox-meta">
                        {{ str_replace('_', ' ', $requerimiento->tipo) }} · {{ data_get($requerimiento->datos, 'rut_trabajador', 'sin RUT') }} · enviado {{ $requerimiento->created_at?->format('d-m-Y H:i') }}
                    </div>
                </div>
                <span class="pill">{{ $requerimiento->prioridad }}</span>
            </div>
        @empty
            <p class="muted">Sin requerimientos pendientes para esta institucion.</p>
        @endforelse
    </div>
</div>

<div class="api-card" style="margin-top:16px">
    <div class="acct-head" style="margin-bottom:12px">
        <div>
            <h2 style="margin:0;color:#061a3d">Documentos recibidos desde administracion</h2>
            <p class="muted" style="margin:6px 0 0">Facturas, boletas, guias, respaldos PDF y documentos subidos por la institucion para que el contador los clasifique y procese.</p>
        </div>
        <a class="btn" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => 'factura']) }}">Ver modulo documentos</a>
    </div>
    <div class="table-wrap">
        <table class="compact-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Documento</th>
                    <th>Folio</th>
                    <th>Naturaleza</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Archivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentosRecientes as $documento)
                    <tr>
                        <td>{{ $documento->fecha_emision?->format('d-m-Y') }}</td>
                        <td>{{ str_replace('_', ' ', $documento->tipo_documento) }}</td>
                        <td>{{ $documento->folio ?: '#'.$documento->id }}</td>
                        <td><span class="pill">{{ $documento->naturaleza }}</span></td>
                        <td>${{ number_format($documento->total, 0, ',', '.') }}</td>
                        <td>{{ $documento->estado }}</td>
                        <td>
                            @if($documento->archivo)
                                <a class="btn btn-secondary" href="{{ route('contabilidad.documentos.archivo', ['centroMedico' => $centroMedico->id, 'documento' => $documento->id]) }}">Abrir PDF</a>
                            @else
                                <span class="muted">Sin archivo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Aun no hay documentos subidos por administracion.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="module-grid">
    @foreach($modulos as $slug => [$titulo, $descripcion, $icono, $color])
        <a class="module-card" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => $slug]) }}">
            <div class="module-top">
                <span class="module-icon" style="background:{{ $color }}">{{ $icono }}</span>
                <h2>{{ $titulo }}</h2>
            </div>
            <p>{{ $descripcion }}</p>
            <span class="module-button">Abrir modulo</span>
        </a>
    @endforeach
</div>

<div class="api-card">
    <h2>Entregar documentos a la institucion</h2>
    <p class="muted">Zona del contador para dejar disponibles liquidaciones de bonos, pagos de vouchers, contratos, finiquitos, remuneraciones, impuestos o respuestas a solicitudes del cliente.</p>
    <form method="POST" enctype="multipart/form-data" action="{{ route('contabilidad.entregas.store', ['centroMedico' => $centroMedico->id]) }}">
        @csrf
        <div class="filter-grid">
            <div>
                <label class="floating-label-activo-sm">Responder requerimiento</label>
                <select class="form-control form-control-sm" name="requerimiento_id">
                    <option value="">Entrega general</option>
                    @foreach($requerimientosPendientes as $requerimiento)
                        <option value="{{ $requerimiento->id }}">{{ $requerimiento->codigo }} - {{ $requerimiento->titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="floating-label-activo-sm">Tipo entrega</label>
                <select class="form-control form-control-sm" name="tipo_entrega" required>
                    <option value="liquidacion_bonos">Liquidacion bonos profesional</option>
                    <option value="pago_vouchers">Liquidacion y pago vouchers</option>
                    <option value="contrato">Contrato</option>
                    <option value="finiquito">Finiquito</option>
                    <option value="remuneracion">Remuneracion</option>
                    <option value="impuesto">Impuesto</option>
                    <option value="declaracion_impuestos">Declaracion impuestos</option>
                    <option value="pago_cotizaciones">Pago cotizaciones</option>
                    <option value="pago_seguro_cesantia">Pago seguro cesantia</option>
                    <option value="pago_caja_compensacion">Pago caja compensacion</option>
                    <option value="pago_salud">Pago salud</option>
                    <option value="respuesta_solicitud">Respuesta solicitud</option>
                    <option value="otro">Otro</option>
                </select>
            </div>
            <div><label class="floating-label-activo-sm">Destinatario</label><input class="form-control form-control-sm" name="destinatario" placeholder="Institucion, profesional o trabajador"></div>
            <div><label class="floating-label-activo-sm">Folio / referencia</label><input class="form-control form-control-sm" name="folio"></div>
            <div><label class="floating-label-activo-sm">Fecha</label><input class="form-control form-control-sm" name="fecha_emision" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
            <div><label class="floating-label-activo-sm">Monto</label><input class="form-control form-control-sm" name="monto_total" type="number" min="0" value="0"></div>
            <div><label class="floating-label-activo-sm">Archivo</label><input class="form-control form-control-sm" name="archivo" type="file" required></div>
            <div style="grid-column:span 2"><label class="floating-label-activo-sm">Observaciones</label><input class="form-control form-control-sm" name="observaciones" placeholder="Detalle breve para el cliente"></div>
            <button class="btn" type="submit">Dejar disponible</button>
        </div>
    </form>
</div>

<div id="api-contable" class="api-card">
    <h2>Conexion API multiinstitucion</h2>
    <p class="muted">El sistema contable queda dentro de Laravel 13 y tambien expone API con token Sanctum para conectar otros centros, instituciones o contadores externos sin mezclar datos.</p>
    <div class="api-box">
        POST /api/auth/token<br>
        GET /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/resumen<br>
        GET /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/movimientos<br>
        POST /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/documentos-tributarios
    </div>
</div>
@endsection
