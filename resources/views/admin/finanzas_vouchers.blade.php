@extends('layouts.app')

@section('title', $esModuloAuditor ? 'Auditor de vouchers' : 'Finanzas de vouchers')

@section('content')
@php
    $modulos = [
        'reparto' => [
            'color' => '#0f766e',
            'icono' => 'R',
            'descripcion' => 'Control de vouchers entregados a reparto y responsables de ruta.',
            'form_titulo' => 'Registrar entrega a reparto',
            'boton' => 'Guardar reparto seguro',
            'estado' => 'entregado a reparto',
            'busqueda' => 'Buscar codigo, repartidor, ruta, firma o detalle',
            'detalle' => 'Detalle de ruta o entrega',
            'principal' => 'Repartidor / responsable',
        ],
        'canje' => [
            'color' => '#2563eb',
            'icono' => 'C',
            'descripcion' => 'Canjes realizados por clientes o comercios asociados.',
            'form_titulo' => 'Registrar canje de voucher',
            'boton' => 'Guardar canje',
            'estado' => 'canjeado',
            'busqueda' => 'Buscar codigo, cliente, comercio, firma o detalle',
            'detalle' => 'Detalle del canje',
            'principal' => 'Responsable canje',
        ],
        'cobro' => [
            'color' => '#0891b2',
            'icono' => '$',
            'descripcion' => 'Cobros generados por prestaciones, comercios o profesionales.',
            'form_titulo' => 'Registrar cobro de voucher',
            'boton' => 'Guardar cobro',
            'estado' => 'cobro emitido',
            'busqueda' => 'Buscar codigo, cobro, responsable, firma o detalle',
            'detalle' => 'Detalle del cobro',
            'principal' => 'Responsable cobro',
        ],
        'rendicion' => [
            'color' => '#eab308',
            'icono' => '$',
            'descripcion' => 'Cobros enviados a pago por profesionales, con respaldo y firma.',
            'form_titulo' => 'Registrar rendicion enviada a pago',
            'boton' => 'Guardar rendicion',
            'estado' => 'enviado a pago',
            'busqueda' => 'Buscar codigo, profesional, estado, firma o detalle de rendicion',
            'detalle' => 'Detalle de rendicion',
            'principal' => 'Responsable rendicion',
        ],
        'liquidacion' => [
            'color' => '#047857',
            'icono' => 'L',
            'descripcion' => 'Pagos efectuados a profesionales y comisiones VETERCHILE.',
            'form_titulo' => 'Registrar liquidacion de pago',
            'boton' => 'Guardar liquidacion',
            'estado' => 'pagado',
            'busqueda' => 'Buscar codigo, profesional, pago, firma o detalle de liquidacion',
            'detalle' => 'Detalle de liquidacion',
            'principal' => 'Responsable liquidacion',
        ],
        'auditoria' => [
            'color' => '#dc2626',
            'icono' => '!',
            'descripcion' => 'Trazabilidad, fraudes, anulaciones, cambios manuales y control interno.',
            'form_titulo' => 'Registrar evento de auditoria',
            'boton' => 'Guardar evento auditoria',
            'estado' => 'en revision',
            'busqueda' => 'Buscar codigo, fraude, anulacion, firma o detalle auditoria',
            'detalle' => 'Hallazgo o accion de auditoria',
            'principal' => 'Auditor / responsable',
        ],
        'alertas' => [
            'color' => '#be123c',
            'icono' => '!',
            'descripcion' => 'Alertas automaticas por duplicados, riesgo financiero y control antifraude.',
            'form_titulo' => '',
            'boton' => '',
            'estado' => '',
            'busqueda' => '',
            'detalle' => '',
            'principal' => '',
        ],
    ];
    $modulo = $modulos[$tablaActiva];
@endphp
<style>
    .fin-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .fin-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .fin-icon{width:38px;height:38px;border-radius:10px;background:{{ $modulo['color'] }};color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .summary{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px}
    .summary .classic-card{padding:18px}.summary h2{margin-bottom:4px}
    .fin-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}.span-2{grid-column:span 2}.span-12{grid-column:span 12}
    .audit-pill{display:inline-flex;background:#111827;color:#fff;border-radius:6px;padding:4px 8px;font-size:12px;font-weight:800;max-width:160px;overflow:hidden;text-overflow:ellipsis}
    .tabbar{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 18px}
    .tabbar .tab{min-width:132px;min-height:44px;background:#e5e7eb;color:#111827;border-radius:6px}
    .tabbar .tab.active{background:#2563eb;color:#fff}
    .tools{display:grid;grid-template-columns:minmax(0,1fr) 150px;gap:10px;align-items:end;margin-bottom:16px}
    .pager{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:16px;padding-top:14px;border-top:1px solid #e5e7eb}
    .pager-actions{display:flex;gap:8px;align-items:center}
    .pager-actions .btn{min-width:110px}
    .table-wrap{overflow-x:auto}
    .module-banner{border-left:8px solid {{ $modulo['color'] }};display:grid;grid-template-columns:minmax(0,1fr) 180px 180px;gap:16px;align-items:center;margin-bottom:18px}
    .module-banner h2{margin-bottom:6px}
    .metric{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:14px}
    .metric strong{display:block;font-size:26px;color:#111827;margin-bottom:4px}
    @media(max-width:900px){.fin-head,.summary,.tools,.module-banner{grid-template-columns:1fr}.span-6,.span-4,.span-3,.span-2{grid-column:span 12}.fin-title{font-size:28px}}
</style>

<div class="fin-head">
    <a class="btn btn-secondary" href="{{ auth()->user()?->tieneRol('admin') ? route('admin.dashboard') : route('auditor.vouchers.index') }}">Volver al panel</a>
    <h1 class="fin-title"><span class="fin-icon">{{ $modulo['icono'] }}</span>{{ $tituloModulo }}</h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card module-banner">
    <div>
        <h2>{{ $esModuloAuditor ? 'Funcion auditor de vouchers' : 'Operacion financiera de vouchers' }} · {{ $secciones[$tablaActiva] }}</h2>
        <p class="muted">{{ $modulo['descripcion'] }}</p>
    </div>
    <div class="metric">
        <strong>{{ $moduloTotal }}</strong>
        <span class="muted">{{ $tablaActiva === 'alertas' ? 'Alertas activas' : 'Registros encontrados' }}</span>
    </div>
    <div class="metric">
        <strong>${{ number_format($moduloMonto, 0, ',', '.') }}</strong>
        <span class="muted">Monto del modulo</span>
    </div>
</div>

@if($tablaActiva !== 'alertas')
<div class="classic-card">
    <h2>{{ $modulo['form_titulo'] }}</h2>
    <form method="POST" action="{{ route('admin.finanzas.vouchers.movimientos') }}">
        @csrf
        <input type="hidden" name="tipo" value="{{ $tablaActiva }}">
        <div class="fin-grid">
            <div class="span-4">
                <label class="floating-label-activo-sm">Voucher activo</label>
                <select class="form-control form-control-sm" name="voucher_descuento_id" required>
                    @foreach($vouchers as $voucher)
                        <option value="{{ $voucher->id }}">{{ $voucher->codigo }} - {{ $voucher->titulo }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Estado</label><input class="form-control form-control-sm" name="estado" value="{{ $modulo['estado'] }}" required></div>
            <div class="span-4"><label class="floating-label-activo-sm">Monto</label><input class="form-control form-control-sm" type="number" name="monto" min="0" value="0"></div>
            <div class="span-4"><label class="floating-label-activo-sm">{{ $modulo['principal'] }}</label><select class="form-control form-control-sm" name="responsable_id"><option value="">Sin responsable</option>@foreach($responsables as $responsable)<option value="{{ $responsable->id }}">{{ $responsable->name }} - {{ $responsable->rol }}</option>@endforeach</select></div>
            <div class="span-4"><label class="floating-label-activo-sm">Profesional</label><select class="form-control form-control-sm" name="profesional_id"><option value="">Sin profesional</option>@foreach($profesionales as $profesional)<option value="{{ $profesional->id }}">{{ $profesional->nombre }}</option>@endforeach</select></div>
            <div class="span-4"><label class="floating-label-activo-sm">ID Pedido</label><input class="form-control form-control-sm" type="number" name="pedido_id" min="1"></div>
            <div class="span-12"><label class="floating-label-activo-sm">{{ $modulo['detalle'] }}</label><textarea class="form-control form-control-sm" name="detalle"></textarea></div>
        </div>
        <div style="display:flex;justify-content:center;margin-top:16px"><button class="btn-success">{{ $modulo['boton'] }}</button></div>
    </form>
</div>
@endif

<div class="classic-card" style="margin-top:22px">
    <div class="tabbar">
        @foreach($secciones as $tipo => $titulo)
            <a class="btn tab {{ $tablaActiva === $tipo ? 'active' : '' }}" href="{{ route($rutasSecciones[$tipo]) }}">{{ $titulo }}</a>
        @endforeach
    </div>

    @if($tablaActiva === 'alertas')
        <h2>Alertas Auditoria</h2>
        @forelse($alertas as $alerta)
            <p><span class="badge" style="background:#fee2e2;color:#991b1b">{{ $alerta['nivel'] }}</span> {{ $alerta['mensaje'] }}</p>
        @empty
            <p class="muted">Sin alertas activas.</p>
        @endforelse
    @else
        <form method="GET" action="{{ route($rutasSecciones[$tablaActiva]) }}" class="tools">
            <div>
                <label class="floating-label-activo-sm">Buscar en {{ strtolower($secciones[$tablaActiva]) }}</label>
                <input class="form-control form-control-sm" name="q" value="{{ $busqueda }}" placeholder="{{ $modulo['busqueda'] }}">
            </div>
            <button class="btn">Buscar</button>
        </form>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Voucher</th>
                        <th>Estado {{ strtolower($secciones[$tablaActiva]) }}</th>
                        <th>Monto</th>
                        <th>{{ $modulo['principal'] }}</th>
                        <th>Profesional</th>
                        <th>Firma auditoria</th>
                        <th>{{ $modulo['detalle'] }}</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($movimientosPagina as $movimiento)
                    <tr>
                        <td>{{ $movimiento->voucher?->codigo }}<br><span class="muted">{{ $movimiento->created_at->format('d-m-Y H:i') }}</span></td>
                        <td>{{ $movimiento->estado }}</td>
                        <td>${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                        <td>{{ $movimiento->responsable?->name ?? 'Sin responsable' }}</td>
                        <td>{{ $movimiento->profesional?->nombre ?? 'Sin profesional' }}</td>
                        <td><span class="audit-pill">{{ substr($movimiento->firma_seguridad, 0, 16) }}</span></td>
                        <td>{{ $movimiento->detalle }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="muted">Sin movimientos para esta tabla.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($movimientosPagina->hasPages())
            <div class="pager">
                <span class="muted">Mostrando {{ $movimientosPagina->firstItem() }}-{{ $movimientosPagina->lastItem() }} de {{ $movimientosPagina->total() }}</span>
                <div class="pager-actions">
                    @if($movimientosPagina->onFirstPage())
                        <span class="btn btn-secondary">Anterior</span>
                    @else
                        <a class="btn btn-secondary" href="{{ $movimientosPagina->previousPageUrl() }}">Anterior</a>
                    @endif
                    <span class="muted">Pagina {{ $movimientosPagina->currentPage() }} / {{ $movimientosPagina->lastPage() }}</span>
                    @if($movimientosPagina->hasMorePages())
                        <a class="btn btn-secondary" href="{{ $movimientosPagina->nextPageUrl() }}">Siguiente</a>
                    @else
                        <span class="btn btn-secondary">Siguiente</span>
                    @endif
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
