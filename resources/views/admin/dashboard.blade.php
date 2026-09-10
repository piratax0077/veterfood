@extends('layouts.app')

@section('title', 'Administracion')
@section('estilos', 'css/admin-inicio.css')

@section('content')
@php
    $tarjetas = [
        'finanzas' => ['titulo' => 'Dashboard Financiero', 'texto' => 'Ingresos, egresos, productos, planes y variaciones.', 'boton' => 'Analisis financiero por productos y planes', 'color' => 'admin-cyan', 'url' => route('admin.financiero')],
        'contabilidad' => ['titulo' => 'Conexion contabilidad', 'texto' => 'API contable, movimientos, ventas del mes y sincronizacion con el sistema contable.', 'boton' => 'Administrar integracion contable', 'color' => 'admin-dark', 'url' => route('admin.contabilidad.integracion')],
        'locales' => ['titulo' => 'Locales y comercios', 'texto' => 'Sucursales, puntos de venta, retiro y comercios adheridos.', 'boton' => 'Administrar locales', 'color' => 'admin-green', 'url' => route('admin.locales.index')],
        'bodega' => ['titulo' => 'Administración Bodegas y pedidos', 'texto' => 'Administración de bodegas y productos', 'boton' => 'Administración y manejos de stock', 'color' => 'admin-wine', 'url' => route('central.panel')],
        'planes' => ['titulo' => 'Planes comerciales', 'texto' => 'Alimento automatico, vacunas, historial clinico, voucher y placa QR.', 'boton' => 'Ver planes', 'color' => 'admin-blue', 'url' => route('admin.planes.comerciales')],
        'encuestas' => ['titulo' => 'Encuestas comerciales', 'texto' => 'Opinion sobre vouchers y FONAVET: propuesta mensual desde $6.990 para atencion, beneficios y descuentos.', 'boton' => 'Ver encuestas', 'color' => 'admin-blue', 'url' => route('admin.encuestas.profesionales')],
        'historial' => ['titulo' => 'Historial de repartos', 'texto' => 'Conformidad del cliente, reclamos y trazabilidad.', 'boton' => 'Ver historial', 'color' => 'admin-green', 'url' => route('admin.repartos.historial')],
        'usuarios' => ['titulo' => 'Usuarios', 'texto' => 'Crear usuarios, roles y permisos.', 'boton' => 'Administrar Usuarios', 'color' => 'admin-blue', 'url' => route('admin.usuarios.index')],
        'clientes' => ['titulo' => 'Clientes', 'texto' => 'Crear Clientes de reparto mensual Clientes VIP', 'boton' => 'Administrar Clientes', 'color' => 'admin-yellow', 'url' => route('admin.clientes.index')],
        'servicios' => ['titulo' => 'Servicios', 'texto' => 'Administración de prestaciones veterinarias.', 'boton' => 'Ver Servicios', 'color' => 'admin-blue', 'url' => route('admin.servicios.index')],
        'profesionales' => ['titulo' => 'Profesionales', 'texto' => 'Veterinarios, laboratorios y centros autorizados.', 'boton' => 'Ver Profesionales', 'color' => 'admin-dark', 'url' => route('admin.profesionales.index')],
        'vendedores' => ['titulo' => 'Vendedores', 'texto' => 'Vendedores autorizados para emitir vouchers.', 'boton' => 'Ver Vendedores', 'color' => 'admin-green', 'url' => route('admin.vendedores.index')],
        'repartidores' => ['titulo' => 'Repartidores', 'texto' => 'Registro de repartidores', 'boton' => 'Ver repartidores', 'color' => 'admin-green', 'url' => route('admin.repartidores.index')],
        'mascotas' => ['titulo' => 'Mascotas', 'texto' => 'Registro y administración de mascotas.', 'boton' => 'Ver Mascotas', 'color' => 'admin-blue', 'url' => route('admin.mascotas.index')],
        'vouchers' => ['titulo' => 'Vouchers de descuento', 'texto' => 'Creación, distribución, vigencia y seguimiento de vouchers.', 'boton' => 'Administrar vouchers', 'color' => 'admin-pink', 'url' => route('admin.vouchers.index')],
        'liquidaciones' => ['titulo' => 'Liquidaciones', 'texto' => 'Pagos a profesionales y comisión VETERCHILE.', 'boton' => 'Ver Liquidaciones', 'color' => 'admin-green', 'url' => route('admin.finanzas.vouchers.liquidaciones')],
        'alertas' => ['titulo' => 'Alertas Voucher', 'texto' => 'Alertas de riesgo, duplicados y control antifraude.', 'boton' => 'Ver Alertas', 'color' => 'admin-red', 'url' => route('auditor.vouchers.alertas')],
        'rendiciones' => ['titulo' => 'Rendiciones', 'texto' => 'Cobros enviados a pago por profesionales.', 'boton' => 'Ver Rendiciones', 'color' => 'admin-yellow', 'url' => route('admin.finanzas.vouchers.rendiciones')],
    ];
    $grupoOperacion = ['finanzas', 'contabilidad', 'locales', 'bodega', 'planes', 'encuestas', 'historial'];
    $grupoRed = ['clientes', 'servicios', 'profesionales', 'vendedores', 'repartidores'];
    $grupoVouchers = ['vouchers', 'liquidaciones', 'alertas', 'rendiciones'];
    $aprobacionesPendientes = $relacionesContablesPendientes->count();
    $mascotasConPedido = $planes->pluck('mascota_id')->filter()->unique()->count();

    $menuAdmin = [
        ['titulo' => 'Administrador', 'items' => [
            ['seccion' => 'operacion', 'texto' => 'Operación administrativa', 'icono' => 'categoria'],
            ['seccion' => 'usuarios', 'texto' => 'Usuarios', 'icono' => 'usuario'],
            ['seccion' => 'aprobaciones', 'texto' => 'Aprobaciones', 'icono' => 'aprobacion', 'contador' => $aprobacionesPendientes],
            ['seccion' => 'red-comercial', 'texto' => 'Usuarios y red comercial', 'icono' => 'red-comercial'],
            ['seccion' => 'mascotas', 'texto' => 'Mascotas', 'icono' => 'mascota'],
            ['seccion' => 'vouchers', 'texto' => 'Vouchers y pagos', 'icono' => 'cupon'],
        ]],
    ];
@endphp

<div class="admin-hero">
    <div class="admin-shield" aria-hidden="true"></div>
    <h1>Administrador <span>Comercializadora Alimentos&nbsp;&nbsp; productos y servicios veterinarios</span></h1>
</div>

<div class="menu-lateral-layout" data-menu-memoria="admin">
<x-menu-lateral etiqueta="Navegación administrador" :grupos="$menuAdmin" />

<div class="menu-lateral-contenido">

<section class="menu-lateral-seccion is-activa" id="admin-operacion" data-menu-panel="operacion">
    <div class="section-head">
        <div>
            <h2>Operación administrativa</h2>
            <p class="muted">Finanzas, locales, inventario, planes y controles generales.</p>
        </div>
        <span class="admin-module-badge">{{ count($grupoOperacion) }} módulos relacionados</span>
    </div>

    <div class="integration-panel">
        <div class="integration-grid">
            <div class="integration-main">
                <h3>Datos relacionados con VET-SDI</h3>
                <p class="muted">La identidad clinica se vincula por ID de origen. Los roles comerciales, locales, bodegas, stock y repartos permanecen en Alimentos.</p>
                <span class="sync-state {{ $integracionVetSdi['disponible'] ? '' : 'offline' }}"><span class="sync-dot"></span>{{ $integracionVetSdi['disponible'] ? 'Conexion disponible' : 'Conexion no disponible' }}</span>
            </div>
            <div class="integration-stat"><strong>{{ $integracionVetSdi['usuarios_vinculados'] }}</strong><span>Usuarios vinculados</span></div>
            <div class="integration-stat"><strong>{{ $integracionVetSdi['mascotas_vinculadas'] }}</strong><span>Mascotas vinculadas</span></div>
            <div class="integration-stat"><strong>{{ $integracionVetSdi['profesionales_vinculados'] }}</strong><span>Profesionales vinculados</span></div>
            <form class="integration-action" method="POST" action="{{ route('admin.integraciones.vet-sdi.sync') }}">
                @csrf
                <button class="btn admin-green" type="submit" @disabled(!$integracionVetSdi['disponible'])>Sincronizar ahora</button>
            </form>
        </div>
    </div>

    @include('admin.partials.dashboard-tarjetas', ['claves' => $grupoOperacion])
</section>

<section class="menu-lateral-seccion" id="admin-usuarios" data-menu-panel="usuarios">
    <div class="section-head">
        <div>
            <h2>Usuarios</h2>
            <p class="muted">Cuentas de acceso al sistema, roles y permisos.</p>
        </div>
        <span class="admin-module-badge">{{ $usuarios->count() }} usuarios registrados</span>
    </div>

    <div class="admin-resumen">
        <div class="admin-dato"><strong>{{ $usuarios->count() }}</strong><span>Usuarios registrados</span></div>
        <div class="admin-dato"><strong>{{ $clientes->count() }}</strong><span>Clientes</span></div>
        <div class="admin-dato"><strong>{{ $vendedores->count() }}</strong><span>Vendedores</span></div>
        <div class="admin-dato"><strong>{{ $repartidores->count() }}</strong><span>Repartidores</span></div>
    </div>

    @include('admin.partials.dashboard-tarjetas', ['claves' => ['usuarios']])
</section>

<section class="menu-lateral-seccion" id="admin-aprobaciones" data-menu-panel="aprobaciones">
    <div class="section-head">
        <div>
            <h2>Aprobaciones</h2>
            <p class="muted">Solicitudes que esperan la autorización de administración.</p>
        </div>
        <span @class(['admin-module-badge', 'is-pendiente' => $aprobacionesPendientes > 0])>{{ $aprobacionesPendientes }} {{ $aprobacionesPendientes === 1 ? 'pendiente' : 'pendientes' }}</span>
    </div>

    <div class="approval-panel">
        <h3>Aprobaciones contables pendientes</h3>
        <p class="muted">Autoriza la relacion entre una institucion y su contador. El contador debe aceptar despues para activar el acceso.</p>
        @if($relacionesContablesPendientes->isEmpty())
            <div class="empty-state">
                <x-icono nombre="aprobacion" class="empty-state-icon" />
                <strong>No hay relaciones contables esperando aprobación</strong>
                <span>Cuando una institución solicite vincular a su contador, aparecerá aquí.</span>
            </div>
        @else
            <div class="table-scroll">
                <table class="approval-table">
                    <thead>
                        <tr><th>Institucion</th><th>Contador</th><th>Valor pactado</th><th>Solicitado</th><th>Accion</th></tr>
                    </thead>
                    <tbody>
                        @foreach($relacionesContablesPendientes as $relacion)
                            <tr>
                                <td><strong>{{ $relacion->razon_social }}</strong><br><span class="muted">{{ $relacion->rut }}</span></td>
                                <td>{{ $relacion->contador_nombre }}<br><span class="muted">{{ $relacion->contador_email }}</span></td>
                                <td>${{ number_format((int) $relacion->valor_pactado_servicio, 0, ',', '.') }}</td>
                                <td>{{ optional($relacion->created_at ? \Carbon\Carbon::parse($relacion->created_at) : null)->format('d-m-Y H:i') }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.contabilidad.relaciones.aprobar', ['centroMedico' => $relacion->centro_medico_id, 'user' => $relacion->user_id]) }}" class="inline-form">
                                        @csrf
                                        <x-boton-tabla tipo="aprobar">Aprobar relación</x-boton-tabla>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</section>

<section class="menu-lateral-seccion" id="admin-red-comercial" data-menu-panel="red-comercial">
    <div class="section-head">
        <div>
            <h2>Usuarios y red comercial</h2>
            <p class="muted">Clientes, servicios y actores operativos relacionados con cada local.</p>
        </div>
        <span class="admin-module-badge">{{ count($grupoRed) }} categorías</span>
    </div>

    @include('admin.partials.dashboard-tarjetas', ['claves' => $grupoRed])
</section>

<section class="menu-lateral-seccion" id="admin-mascotas" data-menu-panel="mascotas">
    <div class="section-head">
        <div>
            <h2>Mascotas</h2>
            <p class="muted">Registro de mascotas de los clientes y su vínculo con VET-SDI.</p>
        </div>
        <span class="admin-module-badge">{{ $mascotas->count() }} mascotas registradas</span>
    </div>

    <div class="admin-resumen">
        <div class="admin-dato"><strong>{{ $mascotas->count() }}</strong><span>Mascotas registradas</span></div>
        <div class="admin-dato"><strong>{{ $integracionVetSdi['mascotas_vinculadas'] }}</strong><span>Vinculadas con VET-SDI</span></div>
        <div class="admin-dato"><strong>{{ $mascotasConPedido }}</strong><span>Con pedido recurrente</span></div>
    </div>

    @include('admin.partials.dashboard-tarjetas', ['claves' => ['mascotas']])
</section>

<section class="menu-lateral-seccion" id="admin-vouchers" data-menu-panel="vouchers">
    <div class="section-head">
        <div>
            <h2>Vouchers y pagos</h2>
            <p class="muted">Creación de vouchers, liquidación, control antifraude y rendición del mismo circuito.</p>
        </div>
        <span class="admin-module-badge is-voucher">Sistema relacionado</span>
    </div>

    @include('admin.partials.dashboard-tarjetas', ['claves' => $grupoVouchers])
</section>

</div>
</div>
@endsection
