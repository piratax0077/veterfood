@extends('layouts.app')

@section('title', 'Administración')
@section('estilos', 'css/admin-inicio.css, css/admin-tarjetas-color.css, css/admin-resumen.css, css/admin-perfil.css')

@section('content')
@php
    // Catalogo de tarjetas del inicio: 'tono' define el color e 'icono' la imagen de la esquina.
    $tarjetas = [
        'finanzas' => ['titulo' => 'Dashboard Financiero', 'texto' => 'Ingresos, egresos, productos, planes y variaciones.', 'tono' => 'oceano', 'icono' => 'images/iconos/tarjetas/finanzas.svg', 'url' => route('admin.financiero')],
        'contabilidad' => ['titulo' => 'Conexión contabilidad', 'texto' => 'API contable, movimientos, ventas del mes y sincronización con el sistema contable.', 'tono' => 'azul', 'icono' => 'images/iconos/tarjetas/contabilidad.svg', 'url' => route('admin.contabilidad.integracion')],
        'locales' => ['titulo' => 'Locales y comercios', 'texto' => 'Sucursales, puntos de venta, retiro y comercios adheridos.', 'tono' => 'agua', 'icono' => 'images/iconos/tarjetas/locales.svg', 'url' => route('admin.locales.index')],
        'bodega' => ['titulo' => 'Administración Bodegas y pedidos', 'texto' => 'Administración de bodegas y productos', 'tono' => 'vino', 'icono' => 'images/iconos/tarjetas/bodega.svg', 'url' => route('central.panel')],
        'planes' => ['titulo' => 'Planes comerciales', 'texto' => 'Alimento automático, vacunas, historial clínico, voucher y placa QR.', 'tono' => 'lila', 'icono' => 'images/iconos/tarjetas/planes.svg', 'url' => route('admin.planes.comerciales')],
        'encuestas' => ['titulo' => 'Encuestas comerciales', 'texto' => 'Opinión sobre vouchers y FONAVET: propuesta mensual desde $6.990.', 'tono' => 'rosa', 'icono' => 'images/iconos/tarjetas/encuestas.svg', 'url' => route('admin.encuestas.profesionales')],
        'historial' => ['titulo' => 'Historial de repartos', 'texto' => 'Conformidad del cliente, reclamos y trazabilidad.', 'tono' => 'verde', 'icono' => 'images/iconos/tarjetas/historial.svg', 'url' => route('admin.repartos.historial')],
        'usuarios' => ['titulo' => 'Usuarios', 'texto' => 'Crear usuarios, roles y permisos.', 'tono' => 'azul', 'icono' => 'images/iconos/tarjetas/usuarios.svg', 'url' => route('admin.usuarios.index')],
        'clientes' => ['titulo' => 'Clientes', 'texto' => 'Crear Clientes de reparto mensual Clientes VIP', 'tono' => 'agua', 'icono' => 'images/iconos/tarjetas/clientes.svg', 'url' => route('admin.clientes.index')],
        'servicios' => ['titulo' => 'Servicios', 'texto' => 'Administración de prestaciones veterinarias.', 'tono' => 'azul', 'icono' => 'images/iconos/tarjetas/servicios.svg', 'url' => route('admin.servicios.index')],
        'profesionales' => ['titulo' => 'Profesionales', 'texto' => 'Veterinarios, laboratorios y centros autorizados.', 'tono' => 'lila', 'icono' => 'images/iconos/tarjetas/profesionales.svg', 'url' => route('admin.profesionales.index')],
        'vendedores' => ['titulo' => 'Vendedores', 'texto' => 'Vendedores autorizados para emitir vouchers.', 'tono' => 'verde', 'icono' => 'images/iconos/tarjetas/vendedores.svg', 'url' => route('admin.vendedores.index')],
        'repartidores' => ['titulo' => 'Repartidores', 'texto' => 'Registro de repartidores', 'tono' => 'coral', 'icono' => 'images/iconos/tarjetas/repartidores.svg', 'url' => route('admin.repartidores.index')],
        'mascotas' => ['titulo' => 'Mascotas', 'texto' => 'Registro y administración de mascotas.', 'tono' => 'naranjo', 'icono' => 'images/iconos/tarjetas/mascotas.svg', 'url' => route('admin.mascotas.index')],
        'vouchers' => ['titulo' => 'Vouchers de descuento', 'texto' => 'Creación, distribución, vigencia y seguimiento de vouchers.', 'tono' => 'rosa', 'icono' => 'images/iconos/tarjetas/vouchers.svg', 'url' => route('admin.vouchers.index')],
        'liquidaciones' => ['titulo' => 'Liquidaciones', 'texto' => 'Pagos a profesionales y comisión VETERCHILE.', 'tono' => 'verde', 'icono' => 'images/iconos/tarjetas/liquidaciones.svg', 'url' => route('admin.finanzas.vouchers.liquidaciones')],
        'alertas' => ['titulo' => 'Alertas Voucher', 'texto' => 'Alertas de riesgo, duplicados y control antifraude.', 'tono' => 'vino', 'icono' => 'images/iconos/tarjetas/alertas.svg', 'url' => route('auditor.vouchers.alertas')],
        'rendiciones' => ['titulo' => 'Rendiciones', 'texto' => 'Cobros enviados a pago por profesionales.', 'tono' => 'naranjo', 'icono' => 'images/iconos/tarjetas/rendiciones.svg', 'url' => route('admin.finanzas.vouchers.rendiciones')],
    ];
    $grupoOperacion = ['finanzas', 'contabilidad', 'locales', 'bodega', 'planes', 'encuestas', 'historial'];
    $grupoRed = ['clientes', 'servicios', 'profesionales', 'vendedores', 'repartidores'];
    $grupoVouchers = ['vouchers', 'liquidaciones', 'alertas', 'rendiciones'];
    $aprobacionesPendientes = $relacionesContablesPendientes->count();
    $mascotasConPedido = $planes->pluck('mascota_id')->filter()->unique()->count();

    // Cabecera del menu: arriba la tienda que distribuye, al medio la persona y abajo su cargo
    $cuenta = auth()->user();
    $nombreTienda = $cuenta?->localVenta?->nombre ?: config('app.name');
    // Nombre y apellido de quien tiene la sesion abierta; si la cuenta no los trae separados, usa el nombre completo
    $nombrePersona = trim(($cuenta?->nombres ?: '') . ' ' . ($cuenta?->apellidos ?: '')) ?: ($cuenta?->name ?: '');

    // El perfil ya va en la cabecera del menu, asi que el grupo no lleva titulo
    $menuAdmin = [
        ['items' => [
            ['seccion' => 'inicio', 'texto' => 'Inicio', 'icono' => 'inicio'],
            ['seccion' => 'operacion', 'texto' => 'Operación administrativa', 'icono' => 'categoria'],
            ['seccion' => 'usuarios', 'texto' => 'Usuarios', 'icono' => 'usuario'],
            ['seccion' => 'aprobaciones', 'texto' => 'Aprobaciones', 'icono' => 'aprobacion', 'contador' => $aprobacionesPendientes],
            ['seccion' => 'red-comercial', 'texto' => 'Usuarios y red comercial', 'icono' => 'red-comercial'],
            ['seccion' => 'mascotas', 'texto' => 'Mascotas', 'icono' => 'mascota'],
            ['seccion' => 'vouchers', 'texto' => 'Vouchers y pagos', 'icono' => 'cupon'],
            ['seccion' => 'perfil', 'texto' => 'Mi perfil', 'icono' => 'usuario'],
        ]],
    ];
@endphp



<div class="menu-lateral-layout menu-lateral-layout--completo" data-menu-memoria="admin">
<x-menu-lateral etiqueta="Navegación administrador" :grupos="$menuAdmin" :titulo="$nombreTienda" :persona="$nombrePersona" subtitulo="Administrador" icono="tienda" :logo="asset('images/logotipo/logo-veterfood.svg')" />

<div class="menu-lateral-contenido">

@php
    // Alto de cada barra del grafico, en porcentaje sobre el mejor dia de la semana
    $mejorDia = max(1, collect($resumen['semana'])->max('total'));
    $peso = fn (int $total) => $total > 0 ? max(6, (int) round($total * 100 / $mejorDia)) : 2;
    $plata = fn (int $monto) => '$' . number_format($monto, 0, ',', '.');
@endphp

<section class="menu-lateral-seccion is-activa" id="admin-inicio" data-menu-panel="inicio">
    <div class="section-head">
        <div>
            <h2>Inicio</h2>
            <p class="muted">Cómo va la tienda hoy y el estado de la conexión con VET-SDI.</p>
        </div>
    </div>

    <div class="integration-panel">
        <div class="integration-grid">
            <div class="integration-main">
                <h3>Datos relacionados con VET-SDI</h3>
                <p class="muted">La identidad clínica se vincula por ID de origen. Los roles comerciales, locales, bodegas, stock y repartos permanecen en Alimentos.</p>
                <span class="sync-state {{ $integracionVetSdi['disponible'] ? '' : 'offline' }}"><span class="sync-dot"></span>{{ $integracionVetSdi['disponible'] ? 'Conexión disponible' : 'Conexión no disponible' }}</span>
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

    <div class="resumen-grilla">
        <article class="resumen-dato resumen-dato--verde">
            <span class="resumen-icono"><x-icono nombre="carrito" /></span>
            <p class="resumen-nombre">Ventas de hoy</p>
            <p class="resumen-cifra">{{ $plata($resumen['ventas_dia']) }}</p>
        </article>

        <article class="resumen-dato resumen-dato--azul">
            <span class="resumen-icono"><x-icono nombre="efectivo" /></span>
            <p class="resumen-nombre">Ventas del mes</p>
            <p class="resumen-cifra">{{ $plata($resumen['ventas_mes']) }}</p>
        </article>

        <article class="resumen-dato resumen-dato--lila">
            <span class="resumen-icono"><x-icono nombre="usuario" /></span>
            <p class="resumen-nombre">Usuarios registrados</p>
            <p class="resumen-cifra">{{ $resumen['usuarios'] }}</p>
        </article>

        <article class="resumen-dato resumen-dato--naranjo">
            <span class="resumen-icono"><x-icono nombre="mascota" /></span>
            <p class="resumen-nombre">Mascotas de los usuarios</p>
            <p class="resumen-cifra">{{ $resumen['mascotas'] }}</p>
        </article>

        <article class="resumen-dato resumen-dato--agua">
            <span class="resumen-icono"><x-icono nombre="suscripcion" /></span>
            <p class="resumen-nombre">Usuarios con pedidos programados</p>
            <p class="resumen-cifra">{{ $resumen['usuarios_programados'] }}</p>
        </article>

        <article class="resumen-dato resumen-dato--rosa">
            <span class="resumen-icono"><x-icono nombre="seguimiento" /></span>
            <p class="resumen-nombre">Pedidos en curso</p>
            <p class="resumen-cifra">{{ $resumen['pedidos_en_curso'] }}</p>
        </article>
    </div>

    <div class="resumen-grafico">
        <div class="resumen-grafico-cabecera">
            <h3>Ventas de los últimos 7 días</h3>
            <span class="resumen-grafico-total">{{ $plata(collect($resumen['semana'])->sum('total')) }} en la semana</span>
        </div>
        <div class="grafico-barras">
            @foreach($resumen['semana'] as $dia)
                <div class="grafico-dia" title="{{ $dia['fecha'] }}: {{ $plata($dia['total']) }}">
                    <span class="grafico-monto">{{ $dia['total'] > 0 ? $plata($dia['total']) : '' }}</span>
                    <span class="grafico-barra" style="--alto:{{ $peso($dia['total']) }}%"></span>
                    <span class="grafico-etiqueta">{{ $dia['etiqueta'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="menu-lateral-seccion" id="admin-operacion" data-menu-panel="operacion">
    <div class="section-head">
        <div>
            <h2>Operación administrativa</h2>
            <p class="muted">Finanzas, locales, inventario, planes y controles generales.</p>
        </div>
    </div>

    @include('admin.partials.dashboard-tarjetas-color', ['claves' => $grupoOperacion])
</section>

<section class="menu-lateral-seccion" id="admin-usuarios" data-menu-panel="usuarios">
    <div class="section-head">
        <div>
            <h2>Usuarios</h2>
            <p class="muted">Cuentas de acceso al sistema, roles y permisos.</p>
        </div>
    </div>

    <div class="admin-resumen">
        <div class="admin-dato"><strong>{{ $usuarios->count() }}</strong><span>Usuarios registrados</span></div>
        <div class="admin-dato"><strong>{{ $clientes->count() }}</strong><span>Clientes</span></div>
        <div class="admin-dato"><strong>{{ $vendedores->count() }}</strong><span>Vendedores</span></div>
        <div class="admin-dato"><strong>{{ $repartidores->count() }}</strong><span>Repartidores</span></div>
    </div>

    @include('admin.partials.dashboard-tarjetas-color', ['claves' => ['usuarios']])
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
        <p class="muted">Autoriza la relación entre una institución y su contador. El contador debe aceptar después para activar el acceso.</p>
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
                        <tr><th>Institución</th><th>Contador</th><th>Valor pactado</th><th>Solicitado</th><th>Acción</th></tr>
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
    </div>

    @include('admin.partials.dashboard-tarjetas-color', ['claves' => $grupoRed])
</section>

<section class="menu-lateral-seccion" id="admin-mascotas" data-menu-panel="mascotas">
    <div class="section-head">
        <div>
            <h2>Mascotas</h2>
            <p class="muted">Registro de mascotas de los clientes y su vínculo con VET-SDI.</p>
        </div>
    </div>

    <div class="admin-resumen">
        <div class="admin-dato"><strong>{{ $mascotas->count() }}</strong><span>Mascotas registradas</span></div>
        <div class="admin-dato"><strong>{{ $integracionVetSdi['mascotas_vinculadas'] }}</strong><span>Vinculadas con VET-SDI</span></div>
        <div class="admin-dato"><strong>{{ $mascotasConPedido }}</strong><span>Con pedido recurrente</span></div>
    </div>

    @include('admin.partials.dashboard-tarjetas-color', ['claves' => ['mascotas']])
</section>

<section class="menu-lateral-seccion" id="admin-vouchers" data-menu-panel="vouchers">
    <div class="section-head">
        <div>
            <h2>Vouchers y pagos</h2>
            <p class="muted">Creación de vouchers, liquidación, control antifraude y rendición del mismo circuito.</p>
        </div>
    </div>

    @include('admin.partials.dashboard-tarjetas-color', ['claves' => $grupoVouchers])
</section>

@include('admin.partials.perfil')

</div>
</div>

<script src="{{ asset('js/admin-perfil.js') }}?v={{ filemtime(public_path('js/admin-perfil.js')) }}" defer></script>
@endsection
