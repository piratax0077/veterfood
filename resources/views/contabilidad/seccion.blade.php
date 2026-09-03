@extends('layouts.app')

@section('title', $modulo['titulo'])

@section('content')
<style>
    .acct-head{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;margin-bottom:18px}
    .acct-title{display:flex;align-items:center;gap:12px;margin:0;color:#061a3d;font-size:34px}.acct-icon{width:44px;height:44px;border-radius:10px;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900}
    .tabs{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:12px;margin-bottom:16px}
    .tab-link{background:#e5e7eb;color:#061a3d;border-radius:7px;padding:11px 10px;font-weight:900;text-decoration:none;text-align:center;min-height:44px;display:flex;align-items:center;justify-content:center}.tab-link.active{background:#2563eb;color:#fff}
    .card-panel{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:20px;box-shadow:0 3px 10px rgba(15,23,42,.07);margin-bottom:16px}
    .search-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;align-items:end}
    .form-grid{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:12px;align-items:end}
    .form-grid>div{grid-column:span 2}.form-grid>.form-wide{grid-column:span 4}.form-grid>.form-xwide{grid-column:span 6}.form-grid>.form-actions{grid-column:1/-1}
    .two-col{display:grid;grid-template-columns:1fr;gap:16px}.entry-panel{order:2}.list-panel{order:1}.form-wide{grid-column:span 4}.form-xwide{grid-column:span 6}.form-actions{display:flex;gap:10px;align-items:center;justify-content:flex-start;flex-wrap:wrap;padding-top:6px;margin-top:4px;border-top:1px solid #edf2f7}
    .section-title{margin:0 0 14px;color:#061a3d}.table-wrap{overflow-x:auto}.pill{display:inline-flex;border-radius:999px;background:#dbeafe;color:#1e3a8a;padding:5px 9px;font-weight:900;font-size:12px}
    .compact-actions{display:flex;gap:8px;flex-wrap:wrap}.inline-action-form{display:inline;margin:0}.action-btn{display:inline-flex;align-items:center;justify-content:center;min-width:108px;height:38px;border:0;border-radius:7px;color:#fff;font-weight:900;text-decoration:none;padding:0 14px;font-size:14px;line-height:1;cursor:pointer}.compact-actions .action-btn{min-width:78px;height:34px;font-size:13px}.action-save{background:#2563eb}.action-edit{background:#f97316}.action-pdf{background:#dc2626}.card-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.toggle-form{background:#0f766e;color:#fff;border:0;border-radius:7px;padding:10px 15px;font-weight:900;cursor:pointer}.toggle-form.secondary{background:#e5e7eb;color:#061a3d}.collapsible-body{display:none;margin-top:14px}.collapsible-body.open{display:block}.table-kpi{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}.kpi-chip{background:#f1f5f9;border:1px solid #dbe3ee;border-radius:999px;padding:7px 11px;color:#061a3d;font-weight:900;font-size:13px}
    .stats-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:16px}.stat-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:16px;box-shadow:0 3px 10px rgba(15,23,42,.06)}.stat-card span{display:block;color:#52617a;font-weight:900;margin-bottom:8px}.stat-card strong{display:block;color:#061a3d;font-size:25px}.chart-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;margin-bottom:16px}.bar-chart{display:grid;grid-template-columns:repeat(12,minmax(42px,1fr));gap:10px;align-items:end;min-height:250px;border-bottom:1px solid #dbe3ee;padding:18px 0 8px;overflow-x:auto}.bar-group{display:flex;flex-direction:column;align-items:center;gap:6px}.bars{height:180px;display:flex;align-items:end;gap:4px}.bar{width:12px;min-height:3px;border-radius:6px 6px 0 0}.bar-income{background:#16a34a}.bar-expense{background:#dc2626}.bar-result{background:#2563eb}.bar-label{font-size:11px;color:#52617a;white-space:nowrap}.legend{display:flex;gap:14px;flex-wrap:wrap;margin-top:12px;color:#52617a;font-weight:800}.legend i{width:11px;height:11px;border-radius:3px;display:inline-block;margin-right:6px}.summary-strip{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px;margin-bottom:14px}.summary-item{background:#f8fafc;border:1px solid #dbe3ee;border-radius:8px;padding:12px}.summary-item span{display:block;color:#52617a;font-weight:900;font-size:13px}.summary-item strong{display:block;color:#061a3d;font-size:22px;margin-top:4px}.hint-box{background:#f8fafc;border:1px solid #dbe3ee;border-radius:8px;padding:12px;color:#52617a;line-height:1.35;margin-bottom:12px}
    .api-box{background:#111827;color:#e5e7eb;border-radius:8px;padding:14px;font-family:Consolas,monospace;font-size:13px;line-height:1.65;overflow:auto}
    @media(max-width:1050px){.tabs{grid-template-columns:repeat(3,minmax(0,1fr))}.search-grid,.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.form-grid>div,.form-grid>.form-wide,.form-grid>.form-xwide,.form-wide,.form-xwide{grid-column:span 1}.form-grid>.form-actions{grid-column:1/-1}.stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:650px){.tabs{grid-template-columns:1fr 1fr}.search-grid,.form-grid{grid-template-columns:1fr}.form-grid>div,.form-grid>.form-wide,.form-grid>.form-xwide,.form-wide,.form-xwide{grid-column:auto}.acct-title{font-size:28px}.action-btn{width:100%}.stats-grid{grid-template-columns:1fr}}
</style>

<div class="acct-head">
    <a class="btn btn-secondary" href="{{ route('contabilidad.escritorio', ['centroMedico' => $centroMedico->id]) }}">Volver al escritorio</a>
    <h1 class="acct-title"><span class="acct-icon" style="background:{{ $modulo['color'] }}">{{ $modulo['icono'] }}</span>{{ $modulo['titulo'] }}</h1>
    @if(auth()->user()?->tieneRol('admin'))
        <a class="btn" href="{{ route('admin.contabilidad.integracion') }}">Administracion</a>
    @endif
</div>

@if(session('status'))
    <div class="alert ok">{{ session('status') }}</div>
@endif
@if($errors->any())
    <div class="alert danger">{{ $errors->first() }}</div>
@endif

@php
    $seccionKey = trim((string) $seccion);
@endphp

<div class="tabs">
    @foreach($modulos as $slug => $item)
        <a class="tab-link {{ $slug === $seccionKey ? 'active' : '' }}" href="{{ route('contabilidad.secciones.show', ['centroMedico' => $centroMedico->id, 'seccion' => $slug]) }}">{{ $item['titulo'] }}</a>
    @endforeach
</div>

<form method="GET" class="card-panel">
    <div class="search-grid">
        <div style="grid-column:span 3">
            <label class="floating-label-activo-sm">Buscar</label>
            <input class="form-control form-control-sm" name="buscar" value="{{ $buscar }}" placeholder="Nombre, RUT, folio, categoria o glosa">
        </div>
        <button class="btn" type="submit">Buscar</button>
    </div>
</form>

@if(in_array($seccionKey, ['rrhh', 'info-pago-sueldos'], true))
    <div class="card-panel">
        <div class="acct-head" style="margin-bottom:14px">
            <h2 class="section-title" style="margin:0">Personal activo</h2>
            <a class="btn" href="{{ route('contabilidad.trabajadores.create', ['centroMedico' => $centroMedico->id]) }}">Crear trabajador</a>
        </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Nombre</th><th>RUT</th><th>Contacto</th><th>Tipo</th><th>Estado</th><th>Acciones</th></tr></thead>
                    <tbody>
                        @forelse($trabajadores as $trabajador)
                            <tr>
                                <td>{{ $trabajador->nombre_completo }}</td>
                                <td>{{ $trabajador->rut }}</td>
                                <td>{{ $trabajador->email ?: 'Sin email' }}<br>{{ $trabajador->telefono ?: 'Sin telefono' }}</td>
                                <td><span class="pill">{{ $trabajador->tipo }}</span></td>
                                <td>{{ $trabajador->activo ? 'Activo' : 'Inactivo' }}</td>
                                <td>
                                    <div class="compact-actions">
                                        <a class="action-btn action-edit" href="{{ route('contabilidad.trabajadores.edit', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}" title="Editar">Editar</a>
                                        <a class="action-btn action-save" href="{{ route('contabilidad.trabajadores.gestion', ['centroMedico' => $centroMedico->id, 'trabajador' => $trabajador->id]) }}" title="Gestion laboral">Gestion</a>
                                    <button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Ficha trabajador')" title="PDF">PDF</button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="muted">Sin trabajadores registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $trabajadores->links() }}
    </div>
@endif

@if(in_array($seccionKey, ['remuneraciones', 'liquidaciones', 'info-pago-sueldos'], true))
    @php
        $esLiquidaciones = $seccionKey === 'liquidaciones';
    @endphp
    <div class="two-col">
        <div class="card-panel entry-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">{{ $esLiquidaciones ? 'Liquidar profesional' : 'Calcular remuneracion' }}</h2>
                <button class="toggle-form" type="button" data-toggle-form>{{ $esLiquidaciones ? 'Abrir liquidacion' : 'Abrir calculo' }}</button>
            </div>
            @if($esLiquidaciones)
                <p class="muted">Seleccione el profesional, ingrese atenciones, honorarios, gratificaciones y descuentos. El historial queda con PDF por liquidacion y boton de deposito.</p>
            @endif
            <div class="collapsible-body">
            <form method="POST" action="{{ route('contabilidad.remuneraciones.store', ['centroMedico' => $centroMedico->id]) }}" {!! $esLiquidaciones ? 'data-liquidacion-form' : '' !!}>
                    @csrf
                    <div class="form-grid">
                    <div class="form-wide"><label class="floating-label-activo-sm">{{ $esLiquidaciones ? 'Profesional / contrato' : 'Nombre / contrato' }}</label><select class="form-control form-control-sm" name="contrato_id" required data-contrato-select>@foreach($contratos as $contrato)<option value="{{ $contrato->id }}" data-sueldo="{{ $contrato->sueldo_base }}" data-colacion="{{ $contrato->porcentaje_colacion }}" data-movilizacion="{{ $contrato->porcentaje_movilizacion }}" data-centro="{{ $contrato->porcentaje_caja_compensacion }}">{{ $contrato->trabajador?->nombre_completo }}</option>@endforeach</select></div>
                    <div><label class="floating-label-activo-sm">Año</label><input class="form-control form-control-sm" name="anio" type="number" value="{{ now()->year }}" required></div>
                    <div><label class="floating-label-activo-sm">Mes</label><input class="form-control form-control-sm" name="mes" type="number" min="1" max="12" value="{{ now()->month }}" required></div>
                    @if($esLiquidaciones)
                        <div><label class="floating-label-activo-sm">Atenciones</label><input class="form-control form-control-sm" type="number" value="0" min="0" data-liquidacion-atenciones></div>
                        <div><label class="floating-label-activo-sm">Valor atencion</label><input class="form-control form-control-sm" type="number" value="0" min="0" data-liquidacion-valor></div>
                        <div><label class="floating-label-activo-sm">Bruto / honorarios</label><input class="form-control form-control-sm" name="sueldo_base" type="number" value="0" min="0" required data-liquidacion-bruto></div>
                        <div><label class="floating-label-activo-sm">Gratificacion</label><input class="form-control form-control-sm" name="bonos" type="number" value="0" min="0" data-liquidacion-bonos></div>
                        <div><label class="floating-label-activo-sm">% centro / contrato</label><input class="form-control form-control-sm" type="number" value="0" min="0" max="100" data-liquidacion-porcentaje></div>
                        <div><label class="floating-label-activo-sm">Gastos / otros desc.</label><input class="form-control form-control-sm" type="number" value="0" min="0" data-liquidacion-descuento-manual></div>
                        <input name="otros_descuentos" type="hidden" value="0" data-liquidacion-descuento-total>
                        <div><label class="floating-label-activo-sm">Liquido estimado</label><input class="form-control form-control-sm" type="text" value="$0" readonly data-liquidacion-liquido></div>
                    @else
                        <div><label class="floating-label-activo-sm">Sueldo base</label><input class="form-control form-control-sm" name="sueldo_base" type="number" value="0" min="0" data-sueldo-base></div>
                        <div><label class="floating-label-activo-sm">Bonos</label><input class="form-control form-control-sm" name="bonos" type="number" value="0" min="0"></div>
                        <div><label class="floating-label-activo-sm">Horas extra</label><input class="form-control form-control-sm" name="horas_extra" type="number" value="0" min="0"></div>
                        <div><label class="floating-label-activo-sm">Otros imponibles</label><input class="form-control form-control-sm" name="otros_imponibles" type="number" value="0" min="0"></div>
                        <div><label class="floating-label-activo-sm">Colacion</label><input class="form-control form-control-sm" name="colacion" type="number" value="0" min="0" placeholder="Auto"></div>
                        <div><label class="floating-label-activo-sm">Movilizacion</label><input class="form-control form-control-sm" name="movilizacion" type="number" value="0" min="0" placeholder="Auto"></div>
                        <div><label class="floating-label-activo-sm">Asig. familiar</label><input class="form-control form-control-sm" name="asignacion_familiar" type="number" value="0" min="0"></div>
                        <div><label class="floating-label-activo-sm">AFP auto</label><input class="form-control form-control-sm" name="afp" type="number" value="0" min="0" placeholder="10,77%"></div>
                        <div><label class="floating-label-activo-sm">Salud auto</label><input class="form-control form-control-sm" name="salud" type="number" value="0" min="0" placeholder="7%"></div>
                        <div><label class="floating-label-activo-sm">Cesantia auto</label><input class="form-control form-control-sm" name="seguro_cesantia" type="number" value="0" min="0" placeholder="0,6%"></div>
                        <div><label class="floating-label-activo-sm">Anticipos</label><input class="form-control form-control-sm" name="anticipos" type="number" value="0" min="0"></div>
                        <div><label class="floating-label-activo-sm">Otros desc.</label><input class="form-control form-control-sm" name="otros_descuentos" type="number" value="0" min="0"></div>
                    @endif
                    <div><label class="floating-label-activo-sm">Estado</label><select class="form-control form-control-sm" name="estado"><option value="calculada">Calculada</option><option value="borrador">Borrador</option></select></div>
                <div class="form-actions"><button class="action-btn action-save" type="submit" title="Guardar">Guardar</button><button class="action-btn action-edit" type="reset" title="Limpiar">Limpiar</button><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, '{{ $esLiquidaciones ? 'Formulario liquidacion profesional' : 'Formulario remuneracion' }}')" title="PDF">PDF</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-panel list-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">{{ $esLiquidaciones ? 'Historial de liquidaciones' : 'Remuneraciones' }}</h2>
                <span class="kpi-chip">{{ $remuneraciones->total() }} registros</span>
            </div>
            <div class="table-wrap">
                <table>
                    @if($esLiquidaciones)
                        <thead><tr><th>Profesional</th><th>Periodo</th><th>Bruto</th><th>Descuento centro/gastos</th><th>Liquido</th><th>Estado</th><th>Accion</th></tr></thead>
                    @else
                        <thead><tr><th>Trabajador</th><th>Periodo</th><th>Imponible</th><th>AFP</th><th>Salud</th><th>Cesantia</th><th>Liquido</th><th>Estado</th><th>Accion</th></tr></thead>
                    @endif
                    <tbody>
                        @forelse($remuneraciones as $remuneracion)
                            @if($esLiquidaciones)
                                <tr>
                                    <td>{{ $remuneracion->contrato?->trabajador?->nombre_completo }}</td>
                                    <td>{{ $remuneracion->mes }}/{{ $remuneracion->anio }}</td>
                                    <td>${{ number_format($remuneracion->total_haberes, 0, ',', '.') }}</td>
                                    <td>${{ number_format($remuneracion->total_descuentos, 0, ',', '.') }}</td>
                                    <td>${{ number_format($remuneracion->liquido_pagar, 0, ',', '.') }}</td>
                                    <td><span class="pill">{{ $remuneracion->estado }}</span></td>
                                    <td>
                                        <div class="compact-actions">
                                            <button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Liquidacion profesional')" title="PDF">PDF</button>
                                            @if($remuneracion->estado !== 'pagada')
                                                <form method="POST" action="{{ route('contabilidad.remuneraciones.pagar', ['centroMedico' => $centroMedico->id, 'remuneracion' => $remuneracion->id]) }}" class="inline-action-form">
                                                    @csrf
                                                    <input type="hidden" name="fecha_pago" value="{{ now()->format('Y-m-d') }}">
                                                    <input type="hidden" name="comprobante" value="Deposito cuenta corriente">
                                                    <button class="action-btn action-save" type="submit" title="Depositar">Depositar</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @else
                                <tr><td>{{ $remuneracion->contrato?->trabajador?->nombre_completo }}</td><td>{{ $remuneracion->mes }}/{{ $remuneracion->anio }}</td><td>${{ number_format($remuneracion->total_imponible, 0, ',', '.') }}</td><td>${{ number_format($remuneracion->afp, 0, ',', '.') }}</td><td>${{ number_format($remuneracion->salud, 0, ',', '.') }}</td><td>${{ number_format($remuneracion->seguro_cesantia, 0, ',', '.') }}</td><td>${{ number_format($remuneracion->liquido_pagar, 0, ',', '.') }}</td><td><span class="pill">{{ $remuneracion->estado }}</span></td><td><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Remuneracion')" title="PDF">PDF</button></td></tr>
                            @endif
                        @empty
                            <tr><td colspan="{{ $esLiquidaciones ? 7 : 9 }}" class="muted">{{ $esLiquidaciones ? 'Sin liquidaciones registradas.' : 'Sin remuneraciones registradas.' }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $remuneraciones->links() }}
        </div>
    </div>
@endif

@if($seccionKey === 'estadisticas')
    <div class="stats-grid">
        <div class="stat-card"><span>Ingresos 12 meses</span><strong>${{ number_format($estadisticas['totales']['ingresos'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Egresos 12 meses</span><strong>${{ number_format($estadisticas['totales']['egresos'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Resultado</span><strong>${{ number_format($estadisticas['totales']['resultado'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Margen</span><strong>{{ number_format($estadisticas['totales']['margen'], 1, ',', '.') }}%</strong></div>
    </div>

    <div class="chart-card">
        <h2 class="section-title">Ingresos, egresos y resultado mensual</h2>
        <div class="bar-chart">
            @foreach($estadisticas['meses'] as $mes)
                <div class="bar-group">
                    <div class="bars">
                        <span class="bar bar-income" title="Ingresos ${{ number_format($mes['ingresos'], 0, ',', '.') }}" style="height:{{ max(3, round(($mes['ingresos'] / $estadisticas['maximo_grafico']) * 180)) }}px"></span>
                        <span class="bar bar-expense" title="Egresos ${{ number_format($mes['egresos'], 0, ',', '.') }}" style="height:{{ max(3, round(($mes['egresos'] / $estadisticas['maximo_grafico']) * 180)) }}px"></span>
                        <span class="bar bar-result" title="Resultado ${{ number_format($mes['resultado'], 0, ',', '.') }}" style="height:{{ max(3, round((abs($mes['resultado']) / $estadisticas['maximo_grafico']) * 180)) }}px"></span>
                    </div>
                    <span class="bar-label">{{ $mes['periodo'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="legend">
            <span><i class="bar-income"></i>Ingresos</span>
            <span><i class="bar-expense"></i>Egresos</span>
            <span><i class="bar-result"></i>Resultado</span>
        </div>
    </div>

    <div class="two-col">
        <div class="card-panel">
            <h2 class="section-title">Ingresos por categoria</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Categoria</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($estadisticas['categorias_ingreso'] as $categoria)
                            <tr><td>{{ $categoria['categoria'] }}</td><td>${{ number_format($categoria['total'], 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="muted">Sin ingresos en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-panel">
            <h2 class="section-title">Egresos por categoria</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Categoria</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($estadisticas['categorias_egreso'] as $categoria)
                            <tr><td>{{ $categoria['categoria'] }}</td><td>${{ number_format($categoria['total'], 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="muted">Sin egresos en el periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card-panel">
        <h2 class="section-title">Resumen tributario estimado</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Indicador</th><th>Monto</th><th>Lectura</th></tr></thead>
                <tbody>
                    <tr><td>IVA debito estimado</td><td>${{ number_format($estadisticas['iva_estimado']['debito'], 0, ',', '.') }}</td><td>19% referencial sobre ingresos registrados.</td></tr>
                    <tr><td>IVA credito estimado</td><td>${{ number_format($estadisticas['iva_estimado']['credito'], 0, ',', '.') }}</td><td>19% referencial sobre egresos registrados.</td></tr>
                    <tr><td>Pago neto estimado</td><td>${{ number_format($estadisticas['iva_estimado']['pago'], 0, ',', '.') }}</td><td>Estimacion contable; no reemplaza calculo tributario oficial.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
@endif

@if($seccionKey === 'impuestos')
    <div class="stats-grid">
        <div class="stat-card"><span>Ventas netas {{ $impuestos['periodo'] }}</span><strong>${{ number_format($impuestos['resumen']['ventas_neto'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>Compras netas {{ $impuestos['periodo'] }}</span><strong>${{ number_format($impuestos['resumen']['compras_neto'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>IVA debito</span><strong>${{ number_format($impuestos['resumen']['debito_iva'], 0, ',', '.') }}</strong></div>
        <div class="stat-card"><span>IVA a pagar</span><strong>${{ number_format($impuestos['resumen']['iva_pagar'], 0, ',', '.') }}</strong></div>
    </div>

    <div class="two-col">
        <div class="card-panel">
            <h2 class="section-title">Declaracion mensual estimada</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Concepto</th><th>Monto</th><th>Detalle</th></tr></thead>
                    <tbody>
                        <tr><td>Debito fiscal IVA</td><td>${{ number_format($impuestos['resumen']['debito_iva'], 0, ',', '.') }}</td><td>{{ $impuestos['resumen']['documentos_venta'] }} documentos de venta</td></tr>
                        <tr><td>Credito fiscal IVA</td><td>${{ number_format($impuestos['resumen']['credito_iva'], 0, ',', '.') }}</td><td>{{ $impuestos['resumen']['documentos_compra'] }} documentos de compra</td></tr>
                        <tr><td>IVA neto estimado</td><td>${{ number_format($impuestos['resumen']['iva_pagar'], 0, ',', '.') }}</td><td>Monto referencial para F29</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-panel">
            <h2 class="section-title">Checklist tributario</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Item</th><th>Estado</th></tr></thead>
                    <tbody>
                        @foreach($impuestos['checklist'] as $item)
                            <tr><td>{{ $item['nombre'] }}</td><td><span class="pill">{{ $item['estado'] }}</span></td></tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="card-panel">
            <h2 class="section-title">Libro de ventas del periodo</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Documento</th><th>Folio</th><th>Cliente</th><th>IVA</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($impuestos['documentos_venta'] as $documento)
                            <tr><td>{{ $documento->fecha_emision?->format('d-m-Y') }}</td><td>{{ $documento->tipo_documento }}</td><td>{{ $documento->folio ?: '#'.$documento->id }}</td><td>{{ $documento->tercero?->razon_social ?: 'Sin tercero' }}</td><td>${{ number_format($documento->impuesto, 0, ',', '.') }}</td><td>${{ number_format($documento->total, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="muted">Sin ventas tributarias este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-panel">
            <h2 class="section-title">Libro de compras del periodo</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Documento</th><th>Folio</th><th>Proveedor</th><th>IVA</th><th>Total</th></tr></thead>
                    <tbody>
                        @forelse($impuestos['documentos_compra'] as $documento)
                            <tr><td>{{ $documento->fecha_emision?->format('d-m-Y') }}</td><td>{{ $documento->tipo_documento }}</td><td>{{ $documento->folio ?: '#'.$documento->id }}</td><td>{{ $documento->tercero?->razon_social ?: 'Sin tercero' }}</td><td>${{ number_format($documento->impuesto, 0, ',', '.') }}</td><td>${{ number_format($documento->total, 0, ',', '.') }}</td></tr>
                        @empty
                            <tr><td colspan="6" class="muted">Sin compras tributarias este mes.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="two-col">
        <div class="card-panel">
            <h2 class="section-title">Cotizaciones, salud y caja</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Tipo</th><th>Institucion</th><th>Vence</th><th>Monto</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse($impuestos['obligaciones'] as $obligacion)
                            <tr><td>{{ $obligacion->tipo }}</td><td>{{ $obligacion->institucion ?: 'Sin institucion' }}</td><td>{{ $obligacion->fecha_vencimiento?->format('d-m-Y') }}</td><td>${{ number_format($obligacion->monto, 0, ',', '.') }}</td><td><span class="pill">{{ $obligacion->estado }}</span></td></tr>
                        @empty
                            <tr><td colspan="5" class="muted">Sin obligaciones laborales registradas para este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-panel">
            <h2 class="section-title">Pagos tributarios registrados</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Categoria</th><th>Glosa</th><th>Monto</th><th>Estado</th></tr></thead>
                    <tbody>
                        @forelse($impuestos['pagos_pendientes'] as $pago)
                            <tr><td>{{ $pago->fecha?->format('d-m-Y') }}</td><td>{{ $pago->categoria }}</td><td>{{ $pago->glosa }}</td><td>${{ number_format($pago->monto, 0, ',', '.') }}</td><td>{{ $pago->estado }}</td></tr>
                        @empty
                            <tr><td colspan="5" class="muted">Sin pagos tributarios registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endif

@if(in_array($seccionKey, ['contable', 'ingresos', 'egresos'], true))
    @php
        $ingresosPagina = $movimientos->getCollection()->where('tipo', 'ingreso')->sum('monto');
        $egresosPagina = $movimientos->getCollection()->where('tipo', 'egreso')->sum('monto');
        $saldoPagina = $ingresosPagina - $egresosPagina;
    @endphp
    <div class="two-col">
        <div class="card-panel entry-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">Registrar movimiento</h2>
                <button class="toggle-form" type="button" data-toggle-form>Abrir movimiento</button>
            </div>
            <div class="hint-box">Libro diario: registre cada ingreso y egreso con fecha, glosa, medio de pago, referencia y estado. Los pagos de remuneraciones y liquidaciones se agregan automaticamente al pagarlos.</div>
            <div class="collapsible-body">
                <form method="POST" action="{{ route('contabilidad.movimientos.store', ['centroMedico' => $centroMedico->id]) }}">
                    @csrf
                    <input type="hidden" name="_redirect_to" value="1">
                    <div class="form-grid">
                    <div><label class="floating-label-activo-sm">Tipo</label><select class="form-control form-control-sm" name="tipo" required><option value="ingreso" {{ $seccionKey === 'ingresos' ? 'selected' : '' }}>Ingreso</option><option value="egreso" {{ $seccionKey === 'egresos' ? 'selected' : '' }}>Egreso</option></select></div>
                    <div><label class="floating-label-activo-sm">Fecha</label><input class="form-control form-control-sm" name="fecha" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                    <div><label class="floating-label-activo-sm">Categoria</label><input class="form-control form-control-sm" name="categoria" value="{{ $seccionKey === 'egresos' ? 'gasto operativo' : 'venta alimentos' }}" required></div>
                    <div><label class="floating-label-activo-sm">Monto</label><input class="form-control form-control-sm" name="monto" type="number" min="1" required></div>
                    <div class="form-wide"><label class="floating-label-activo-sm">Glosa</label><input class="form-control form-control-sm" name="glosa" required></div>
                    <div><label class="floating-label-activo-sm">Medio pago</label><select class="form-control form-control-sm" name="medio_pago"><option value="transferencia">Transferencia</option><option value="efectivo">Efectivo</option><option value="tarjeta">Tarjeta</option><option value="cheque">Cheque</option><option value="otro">Otro</option></select></div>
                    <div><label class="floating-label-activo-sm">Estado</label><select class="form-control form-control-sm" name="estado"><option value="pagado">Pagado</option><option value="pendiente">Pendiente</option><option value="conciliado">Conciliado</option></select></div>
                <div class="form-actions"><button class="action-btn action-save" type="submit" title="Guardar">Guardar</button><button class="action-btn action-edit" type="reset" title="Limpiar">Limpiar</button><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Formulario movimiento')" title="PDF">PDF</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-panel list-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">Libro diario</h2>
                <span class="kpi-chip">{{ $movimientos->total() }} registros</span>
            </div>
            <div class="summary-strip">
                <div class="summary-item"><span>Ingresos vista</span><strong>${{ number_format($ingresosPagina, 0, ',', '.') }}</strong></div>
                <div class="summary-item"><span>Egresos vista</span><strong>${{ number_format($egresosPagina, 0, ',', '.') }}</strong></div>
                <div class="summary-item"><span>Saldo vista</span><strong>${{ number_format($saldoPagina, 0, ',', '.') }}</strong></div>
                <div class="summary-item"><span>Registros</span><strong>{{ $movimientos->total() }}</strong></div>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Tipo</th><th>Categoria</th><th>Glosa</th><th>Monto</th><th>Estado</th><th>Accion</th></tr></thead>
                    <tbody>
                        @forelse($movimientos as $movimiento)
                        <tr><td>{{ $movimiento->fecha?->format('d-m-Y') }}</td><td><span class="pill">{{ $movimiento->tipo }}</span></td><td>{{ $movimiento->categoria }}</td><td>{{ $movimiento->glosa }}</td><td>${{ number_format($movimiento->monto, 0, ',', '.') }}</td><td>{{ $movimiento->estado }}</td><td><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Movimiento contable')" title="PDF">PDF</button></td></tr>
                        @empty
                            <tr><td colspan="7" class="muted">Sin movimientos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $movimientos->links() }}
        </div>
    </div>
@endif

@if(in_array($seccionKey, ['factura', 'ingresos', 'egresos'], true))
    <div class="two-col">
        <div class="card-panel entry-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">Registrar documento tributario</h2>
                <button class="toggle-form" type="button" data-toggle-form>Abrir documento</button>
            </div>
            <div class="collapsible-body">
                <form method="POST" action="{{ route('contabilidad.documentos.store', ['centroMedico' => $centroMedico->id]) }}">
                    @csrf
                    <input type="hidden" name="_redirect_to" value="1">
                    <div class="form-grid">
                    <div class="form-wide"><label class="floating-label-activo-sm">Tercero</label><select class="form-control form-control-sm" name="tercero_id"><option value="">Sin tercero</option>@foreach($terceros as $tercero)<option value="{{ $tercero->id }}">{{ $tercero->razon_social }}</option>@endforeach</select></div>
                    <div><label class="floating-label-activo-sm">Naturaleza</label><select class="form-control form-control-sm" name="naturaleza" required><option value="venta" {{ $seccionKey !== 'egresos' ? 'selected' : '' }}>Venta</option><option value="compra" {{ $seccionKey === 'egresos' ? 'selected' : '' }}>Compra</option></select></div>
                    <div><label class="floating-label-activo-sm">Documento</label><select class="form-control form-control-sm" name="tipo_documento" required><option value="factura">Factura</option><option value="boleta">Boleta</option><option value="guia_despacho">Guia despacho</option><option value="otro">Otro</option></select></div>
                    <div><label class="floating-label-activo-sm">Folio</label><input class="form-control form-control-sm" name="folio"></div>
                    <div><label class="floating-label-activo-sm">Fecha</label><input class="form-control form-control-sm" name="fecha_emision" type="date" value="{{ now()->format('Y-m-d') }}" required></div>
                    <div class="form-wide"><label class="floating-label-activo-sm">Descripcion</label><input class="form-control form-control-sm" name="detalles[0][descripcion]" value="Venta de alimentos y servicios" required></div>
                    <div><label class="floating-label-activo-sm">Cantidad</label><input class="form-control form-control-sm" name="detalles[0][cantidad]" type="number" min="0.001" step="0.001" value="1" required></div>
                    <div><label class="floating-label-activo-sm">Precio</label><input class="form-control form-control-sm" name="detalles[0][precio_unitario]" type="number" min="0" step="0.001" inputmode="decimal" required></div>
                <div class="form-actions"><button class="action-btn action-save" type="submit" title="Guardar">Guardar</button><button class="action-btn action-edit" type="reset" title="Limpiar">Limpiar</button><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Formulario documento tributario')" title="PDF">PDF</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-panel list-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">Documentos</h2>
                <span class="kpi-chip">{{ $documentos->total() }} registros</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Fecha</th><th>Documento</th><th>Folio</th><th>Tercero</th><th>Total</th><th>Estado</th><th>Accion</th></tr></thead>
                    <tbody>
                        @forelse($documentos as $documento)
                            <tr>
                                <td>{{ $documento->fecha_emision?->format('d-m-Y') }}</td>
                                <td>{{ $documento->naturaleza }} / {{ $documento->tipo_documento }}</td>
                                <td>{{ $documento->folio ?: '#'.$documento->id }}</td>
                                <td>{{ $documento->tercero?->razon_social ?: 'Sin tercero' }}</td>
                                <td>${{ number_format($documento->total, 0, ',', '.') }}</td>
                                <td>{{ $documento->estado }}</td>
                            <td><div class="compact-actions"><a class="action-btn action-edit" href="{{ route('contabilidad.documentos.emitir', ['centroMedico' => $centroMedico->id, 'documento' => $documento->id]) }}" title="Emitir">Emitir</a><a class="action-btn action-pdf" href="{{ route('contabilidad.documentos.emitir', ['centroMedico' => $centroMedico->id, 'documento' => $documento->id]) }}" title="PDF">PDF</a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="muted">Sin documentos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $documentos->links() }}
        </div>
    </div>
@endif

@if(in_array($seccionKey, ['proveedores', 'convenios'], true))
    <div class="two-col">
        <div class="card-panel entry-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">{{ $seccionKey === 'proveedores' ? 'Crear proveedor' : 'Crear convenio' }}</h2>
                <button class="toggle-form" type="button" data-toggle-form>{{ $seccionKey === 'proveedores' ? 'Abrir proveedor' : 'Abrir convenio' }}</button>
            </div>
            <div class="collapsible-body">
                <form method="POST" action="{{ route('contabilidad.terceros.store', ['centroMedico' => $centroMedico->id]) }}">
                    @csrf
                    <input type="hidden" name="_redirect_to" value="1">
                    <div class="form-grid">
                    <div><label class="floating-label-activo-sm">Tipo</label><select class="form-control form-control-sm" name="tipo" required><option value="proveedor" {{ $seccionKey === 'proveedores' ? 'selected' : '' }}>Proveedor</option><option value="cliente" {{ $seccionKey === 'convenios' ? 'selected' : '' }}>Cliente convenio</option><option value="ambos">Ambos</option></select></div>
                    <div><label class="floating-label-activo-sm">RUT</label><input class="form-control form-control-sm" name="rut" required></div>
                    <div class="form-wide"><label class="floating-label-activo-sm">Razon social</label><input class="form-control form-control-sm" name="razon_social" required></div>
                    <div><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" name="email" type="email"></div>
                    <div><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono"></div>
                    <div class="form-wide"><label class="floating-label-activo-sm">Giro / convenio</label><input class="form-control form-control-sm" name="giro"></div>
                    <div class="form-actions"><button class="action-btn action-save" type="submit" title="Guardar">Guardar</button><button class="action-btn action-edit" type="reset" title="Limpiar">Limpiar</button></div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-panel list-panel">
            <div class="card-head">
                <h2 class="section-title" style="margin:0">Registros</h2>
                <span class="kpi-chip">{{ $terceros->total() }} registros</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>RUT</th><th>Razon social</th><th>Tipo</th><th>Contacto</th><th>Accion</th></tr></thead>
                    <tbody>
                        @forelse($terceros as $tercero)
                        <tr><td>{{ $tercero->rut }}</td><td>{{ $tercero->razon_social }}</td><td><span class="pill">{{ $tercero->tipo }}</span></td><td>{{ $tercero->email }}<br>{{ $tercero->telefono }}</td><td><button class="action-btn action-pdf" type="button" onclick="printContabilidadItem(this, 'Registro tercero')" title="PDF">PDF</button></td></tr>
                        @empty
                            <tr><td colspan="5" class="muted">Sin registros.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $terceros->links() }}
        </div>
    </div>
@endif

<div class="card-panel">
    <h2 class="section-title">API del modulo</h2>
    <p class="muted">Estas rutas permiten unir este modulo con centros externos, instituciones o contador externo mediante token Sanctum y permisos por centro.</p>
    <div class="api-box">
        GET /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/movimientos<br>
        GET /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/documentos-tributarios<br>
        POST /api/centros-medicos/{{ $centroMedico->id }}/contabilidad/terceros
    </div>
</div>

<script>
document.querySelectorAll('[data-toggle-form]').forEach((button) => {
    const panel = button.closest('.card-panel');
    const body = panel?.querySelector('.collapsible-body');
    if (!body) {
        return;
    }
    const openText = button.textContent.trim();
    const closeText = openText.replace('Abrir', 'Ocultar');
    button.addEventListener('click', () => {
        const isOpen = body.classList.toggle('open');
        button.textContent = isOpen ? closeText : openText;
        button.classList.toggle('secondary', isOpen);
    });
});

document.querySelectorAll('form').forEach((form) => {
    const contratoSelect = form.querySelector('[data-contrato-select]');
    if (!contratoSelect) {
        return;
    }
    const sueldoBase = form.querySelector('[data-sueldo-base]');
    const porcentajeCentro = form.querySelector('[data-liquidacion-porcentaje]');
    const applyContract = () => {
        const selected = contratoSelect.options[contratoSelect.selectedIndex];
        if (sueldoBase && (!parseInt(sueldoBase.value || '0', 10))) {
            sueldoBase.value = selected?.dataset?.sueldo || 0;
        }
        if (porcentajeCentro && (!parseFloat(porcentajeCentro.value || '0'))) {
            porcentajeCentro.value = selected?.dataset?.centro || 0;
            porcentajeCentro.dispatchEvent(new Event('input'));
        }
    };
    contratoSelect.addEventListener('change', applyContract);
    applyContract();
});

document.querySelectorAll('[data-liquidacion-form]').forEach((form) => {
    const money = new Intl.NumberFormat('es-CL', { style: 'currency', currency: 'CLP', maximumFractionDigits: 0 });
    const atenciones = form.querySelector('[data-liquidacion-atenciones]');
    const valor = form.querySelector('[data-liquidacion-valor]');
    const bruto = form.querySelector('[data-liquidacion-bruto]');
    const bonos = form.querySelector('[data-liquidacion-bonos]');
    const porcentaje = form.querySelector('[data-liquidacion-porcentaje]');
    const descuentoManual = form.querySelector('[data-liquidacion-descuento-manual]');
    const descuentoTotal = form.querySelector('[data-liquidacion-descuento-total]');
    const liquido = form.querySelector('[data-liquidacion-liquido]');

    const numberValue = (field) => Math.max(0, parseInt(field?.value || '0', 10) || 0);
    const recalculate = (source) => {
        const calculatedBase = numberValue(atenciones) * numberValue(valor);
        if (source !== bruto && calculatedBase > 0) {
            bruto.value = calculatedBase;
        }
        const base = numberValue(bruto);
        const extra = numberValue(bonos);
        const centerDiscount = Math.round(base * (numberValue(porcentaje) / 100));
        const discounts = centerDiscount + numberValue(descuentoManual);
        descuentoTotal.value = discounts;
        liquido.value = money.format(Math.max(0, base + extra - discounts));
    };

    [atenciones, valor, bruto, bonos, porcentaje, descuentoManual].forEach((field) => {
        field?.addEventListener('input', () => recalculate(field));
    });
    recalculate();
});

function printContabilidadItem(trigger, title) {
    const row = trigger.closest('tr');
    const source = row || trigger.closest('form') || trigger.closest('.card-panel');
    if (!source) {
        return;
    }

    let content = '';
    if (row) {
        const table = row.closest('table');
        const header = table?.querySelector('thead')?.cloneNode(true);
        header?.querySelector('tr')?.lastElementChild?.remove();
        const cleanRow = row.cloneNode(true);
        cleanRow.querySelectorAll('.compact-actions, .action-btn, button, a').forEach((item) => item.remove());
        content = `<table>${header ? header.outerHTML : ''}<tbody>${cleanRow.outerHTML}</tbody></table>`;
    } else {
        const clone = source.cloneNode(true);
        clone.querySelectorAll('.compact-actions, .form-actions, .action-btn, button, a').forEach((item) => item.remove());
        clone.querySelectorAll('input, select, textarea').forEach((field) => {
            const wrapper = field.closest('div') || field.parentElement;
            const label = wrapper?.querySelector('label')?.textContent?.trim() || field.name || 'Campo';
            const value = field.tagName === 'SELECT'
                ? field.options[field.selectedIndex]?.text || ''
                : field.value || '';
            const replacement = document.createElement('div');
            replacement.className = 'print-field';
            replacement.innerHTML = `<strong>${label}</strong><span>${value || 'Sin informar'}</span>`;
            field.replaceWith(replacement);
        });
        content = clone.outerHTML;
    }

    const printWindow = window.open('', '_blank', 'width=900,height=700');
    printWindow.document.write(`
        <!doctype html>
        <html>
        <head>
            <title>${title}</title>
            <style>
                body{font-family:Arial,sans-serif;color:#061a3d;margin:28px}
                h1{font-size:24px;margin:0 0 16px}
                table{width:100%;border-collapse:collapse;margin-top:12px}
                th,td{border-bottom:1px solid #dbe3ee;padding:10px;text-align:left;vertical-align:top}
                th{font-size:13px;color:#52617a;text-transform:uppercase}
                .card-panel,form{border:1px solid #dbe3ee;border-radius:8px;padding:18px}
                .form-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}
                .print-field{border:1px solid #dbe3ee;border-radius:6px;padding:8px;margin:6px 0}
                .print-field strong{display:block;font-size:12px;color:#52617a;margin-bottom:4px}
                .print-field span{font-size:14px;color:#061a3d}
                .pill{display:inline-block;border-radius:999px;background:#dbeafe;color:#1e3a8a;padding:4px 8px;font-weight:700}
                @media print{body{margin:18px}}
            </style>
        </head>
        <body>
            <h1>${title}</h1>
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
}
</script>
@endsection
