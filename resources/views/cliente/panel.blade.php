@extends('layouts.app')

@section('title', 'Mi cuenta')
@section('estilos', 'css/cliente-panel.css')

@section('content')
<div class="client-page">
    @php
        $pedidoDespacho = $user->pedidos->whereNotIn('estado', ['entregado', 'cancelado'])->sortByDesc('created_at')->first();
        $estadosDespacho = ['listo_despacho', 'reparto_asignado', 'en_camino', 'asignado', 'en_ruta'];
        $fotosReferencia = [
            'Mordedor dental' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2d/Blue_dog_bone_toy.JPG/500px-Blue_dog_bone_toy.JPG',
            'Pelota resistente' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/55/Tennisball.jpg/500px-Tennisball.jpg',
            'Antiparasitario mensual' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5c/Deworming_tablet_packaging.jpg/500px-Deworming_tablet_packaging.jpg',
            'Suplemento articular' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/b/b0/Omega_3_capsules_in_white_bottle_%2852715127894%29.jpg/500px-Omega_3_capsules_in_white_bottle_%2852715127894%29.jpg',
            'Correa reflectante' => 'https://upload.wikimedia.org/wikipedia/commons/3/3e/PPD-Leash-PinkGreenStripes.jpg',
            'Dispensador de alimento' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Pet_Food_Dispenser.png',
            'Shampoo piel sensible' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/96/Green_shampoo_bottle.jpg/500px-Green_shampoo_bottle.jpg',
            'Toallitas higienicas' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/7/77/Wet_wipes_on_a_shelf.jpg/500px-Wet_wipes_on_a_shelf.jpg',
        ];
        $despachoEnCurso = $pedidoDespacho && in_array($pedidoDespacho->estado, $estadosDespacho, true);
        $menuCliente = [
            ['titulo' => 'Mi cuenta', 'items' => [
                ['seccion' => 'resumen', 'texto' => 'Resumen', 'icono' => 'inicio'],
                ['seccion' => 'perfil', 'texto' => 'Mi perfil', 'icono' => 'usuario'],
                ['seccion' => 'contrasena', 'texto' => 'Mi contraseña', 'icono' => 'candado'],
                ['seccion' => 'compras', 'texto' => 'Mis compras', 'icono' => 'compras', 'destacado' => $despachoEnCurso],
                ['seccion' => 'tarjetas', 'texto' => 'Tarjetas', 'icono' => 'tarjeta'],
            ]],
            ['titulo' => 'Mis servicios', 'items' => [
                ['seccion' => 'mi-plan', 'texto' => 'Mis suscripciones', 'icono' => 'suscripcion'],
                ['seccion' => 'mascotas', 'texto' => 'Mascotas', 'icono' => 'mascota'],
                ['seccion' => 'direcciones', 'texto' => 'Direcciones', 'icono' => 'locacion'],
                ['seccion' => 'pedido', 'texto' => 'Pedidos programados', 'icono' => 'carrito'],
                ['seccion' => 'ofertas', 'texto' => 'Ofertas', 'icono' => 'oferta'],
            ]],
            ['titulo' => 'Más', 'items' => [
                ['url' => route('encuesta.usuario'), 'texto' => 'Encuesta', 'icono' => 'encuesta'],
                ['url' => route('vouchers.usuario'), 'texto' => 'Mis vouchers', 'icono' => 'cupon'],
            ]],
        ];
    @endphp
<div class="menu-lateral-layout" data-menu-memoria="cliente">
<x-menu-lateral etiqueta="Navegación cuenta cliente" :grupos="$menuCliente" />

<div class="menu-lateral-contenido">

@if($notificaciones->isNotEmpty())
    <div class="notice-list">
        @foreach($notificaciones as $notificacion)
            <div class="notice-item">
                <span>
                    <strong>{{ $notificacion->titulo }}</strong><br>
                    <span class="muted">{{ $notificacion->mensaje }}</span>
                </span>
                @if($notificacion->url)
                    <a class="btn btn-success" href="{{ $notificacion->url }}">Agregar extras</a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<section class="menu-lateral-seccion is-activa" id="cliente-resumen" data-menu-panel="resumen">
    <div class="client-hero">
        <h1>Clientes y mascotas</h1>
        <p class="muted">Administra tus mascotas, direcciones y pedidos recurrentes desde un solo lugar.</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card"><strong>{{ $user->mascotas->count() }}</strong><span>Mascotas inscritas</span><x-icono nombre="mascota" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $user->direcciones->count() }}</strong><span>Direcciones guardadas</span><x-icono nombre="locacion" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $user->planesPedido->count() }}</strong><span>Pedidos recurrentes</span><x-icono nombre="suscripcion" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $vouchersPlan->count() }}</strong><span>Vouchers disponibles</span><x-icono nombre="cupon" class="summary-marca" /></div>
    </div>
    <div class="panel-card">
        <h2>Mis pedidos frecuentes</h2>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Producto</th><th>Mascota</th><th>Voucher</th><th>Frecuencia</th><th>Próxima entrega</th><th>Dirección</th></tr></thead>
                <tbody>
                    @forelse($user->planesPedido as $plan)
                        <tr>
                            <td>{{ $plan->producto->nombre }}</td>
                            <td>{{ $plan->mascota?->nombre ?? 'General' }}</td>
                            <td>{{ $plan->voucher?->codigo ?? 'Sin voucher' }}<br><span class="muted">{{ $plan->voucher?->titulo }}</span></td>
                            <td>{{ $plan->frecuencia }} x {{ $plan->cantidad }}</td>
                            <td>{{ $plan->proxima_entrega->format('d-m-Y') }}</td>
                            <td>{{ $plan->direccion_entrega }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Aún no tienes pedidos recurrentes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</section>

@php
    $perfilCliente = $user->perfilCliente;
    $celularPerfil = substr(preg_replace('/\D/', '', (string) $user->telefono), -9);
    $erroresPerfil = $errors->getBag('perfil');
    $erroresPassword = $errors->getBag('password');
    $erroresTarjeta = $errors->getBag('tarjeta');
    $ojoPassword = '<svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>'
        . '<svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
@endphp

<section class="menu-lateral-seccion" id="cliente-perfil" data-menu-panel="perfil">
    <div class="section-head">
        <div>
            <h2>Mi perfil</h2>
            <p class="muted">Tus datos personales. Se usan para tus compras, despachos y boletas.</p>
        </div>
        <button type="button" class="btn-edit" data-edit-toggle="form-perfil" aria-controls="form-perfil"><x-icono nombre="editar" />Editar datos</button>
    </div>
    <div class="panel-card">
        <form method="POST" action="{{ route('cliente.perfil.update') }}" id="form-perfil" class="editable-form {{ $erroresPerfil->any() ? 'is-editing' : '' }}" data-keep-open="1">
            @csrf
            @method('PATCH')
            <fieldset @disabled(!$erroresPerfil->any())>
                <div class="compact-form">
                    <div class="span-6 {{ $erroresPerfil->has('nombres') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_nombres">Nombre</label>
                        <input class="form-control form-control-sm" id="perfil_nombres" name="nombres" value="{{ old('nombres', $user->nombres ?? $user->name) }}" autocomplete="given-name" maxlength="120" required>
                        @error('nombres', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('apellidos') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_apellidos">Apellido</label>
                        <input class="form-control form-control-sm" id="perfil_apellidos" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" autocomplete="family-name" maxlength="120" required>
                        @error('apellidos', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('rut') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_rut">RUT</label>
                        <input class="form-control form-control-sm" id="perfil_rut" name="rut" value="{{ old('rut') ? \App\Rules\RutChileno::formatear(old('rut')) : \App\Rules\RutChileno::formatear($perfilCliente?->rut) }}" placeholder="12.345.678-9" maxlength="12" data-rut required>
                        @error('rut', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('fecha_nacimiento') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_fecha_nacimiento">Fecha de nacimiento</label>
                        <input class="form-control form-control-sm" type="date" id="perfil_fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->toDateString()) }}" min="1900-01-01" max="{{ now()->subDay()->toDateString() }}" autocomplete="bday" required>
                        @error('fecha_nacimiento', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('celular') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_celular">Celular</label>
                        <div class="phone-group">
                            <span class="phone-prefix" title="Chile">
                                <svg viewBox="0 0 30 20" aria-hidden="true"><rect width="30" height="20" fill="#fff"/><rect y="10" width="30" height="10" fill="#d52b1e"/><rect width="10" height="10" fill="#0039a6"/><path fill="#fff" d="M5 2.4l.59 1.79h1.88L5.95 5.31l.58 1.79L5 6l-1.53 1.1.58-1.79-1.52-1.12h1.88z"/></svg>
                                +56
                            </span>
                            <input class="form-control form-control-sm" type="tel" id="perfil_celular" name="celular" value="{{ old('celular', strlen($celularPerfil) === 9 ? $celularPerfil : '') }}" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="912345678" autocomplete="tel-national" aria-describedby="perfil_celular_ayuda" data-solo-digitos required>
                        </div>
                        <small class="field-hint" id="perfil_celular_ayuda">Ingrese 9 dígitos</small>
                        @error('celular', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('email') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_email">Email</label>
                        <input class="form-control form-control-sm" type="email" id="perfil_email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" maxlength="255" required>
                        @error('email', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </fieldset>
            <div class="edit-actions">
                <button type="button" class="btn btn-secondary" data-edit-cancel><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" />Guardar cambios</button>
            </div>
        </form>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-contrasena" data-menu-panel="contrasena">
    <div class="section-head">
        <div>
            <h2>Mi contraseña</h2>
            <p class="muted">Debe tener entre 6 y 8 caracteres y combinar letras con números y/o símbolos. Ej: luna#24</p>
        </div>
        <button type="button" class="btn-edit" data-edit-toggle="form-password" aria-controls="form-password"><x-icono nombre="editar" />Cambiar contraseña</button>
    </div>
    <div class="panel-card">
        <form method="POST" action="{{ route('cliente.password.update') }}" id="form-password" class="editable-form password-form {{ $erroresPassword->any() ? 'is-editing' : '' }}" data-keep-open="1">
            @csrf
            @method('PATCH')
            <div class="compact-form solo-vista">
                <div class="span-12">
                    <label class="floating-label-activo-sm" for="password_vista">Contraseña</label>
                    <input class="form-control form-control-sm" type="password" id="password_vista" value="contrasena" disabled aria-describedby="password_vista_ayuda">
                    <small class="field-hint" id="password_vista_ayuda">
                        @if($user->password_cambiada_at)
                            Última actualización: {{ $user->password_cambiada_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}
                        @else
                            Por seguridad no mostramos tu contraseña actual.
                        @endif
                    </small>
                </div>
            </div>
            <fieldset @disabled(!$erroresPassword->any()) class="solo-edicion">
                @unless($requierePasswordActual)
                    <p class="info-note"><x-icono nombre="candado" />Ingresaste con tu cuenta VET SDI, así que puedes crear una contraseña para esta tienda sin ingresar la actual.</p>
                @endunless
                <div class="compact-form">
                    @php
                        $camposPassword = array_filter([
                            $requierePasswordActual ? ['current_password', 'Contraseña actual', 'current-password'] : null,
                            ['password', 'Nueva contraseña', 'new-password'],
                            ['password_confirmation', 'Repetir nueva contraseña', 'new-password'],
                        ]);
                    @endphp
                    @foreach($camposPassword as [$campo, $etiqueta, $autocompletar])
                        <div class="span-12 {{ $erroresPassword->has($campo) ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="pass_{{ $campo }}">{{ $etiqueta }}</label>
                            <div class="pass-field">
                                <input class="form-control form-control-sm" type="password" id="pass_{{ $campo }}" name="{{ $campo }}" autocomplete="{{ $autocompletar }}" maxlength="{{ $campo === 'current_password' ? 72 : 8 }}" required>
                                <button type="button" class="pass-eye" data-pass-toggle="pass_{{ $campo }}" aria-label="Mostrar contraseña" aria-pressed="false">{!! $ojoPassword !!}</button>
                            </div>
                            @error($campo, 'password')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                    @endforeach
                    <div class="span-12">
                        <p class="pass-contador" data-pass-contador aria-live="polite">0 de 8</p>
                        <ul class="pass-rules" aria-live="polite">
                            <li data-regla="largo">Entre 6 y 8 caracteres</li>
                            <li data-regla="mezcla">Letras y además números y/o símbolos</li>
                            <li data-regla="coincide">Ambas contraseñas coinciden</li>
                        </ul>
                    </div>
                </div>
            </fieldset>
            <div class="edit-actions">
                <button type="button" class="btn btn-secondary" data-edit-cancel><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" />Guardar contraseña</button>
            </div>
        </form>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-compras" data-menu-panel="compras">
    @php
        $compras = $user->pedidos->sortByDesc('created_at')->values();
        $estadosCompra = [
            'recibido' => ['Pedido recibido', 'curso'],
            'en_preparacion' => ['En preparación', 'curso'],
            'preparando' => ['En preparación', 'curso'],
            'listo_despacho' => ['Listo para despacho', 'curso'],
            'reparto_asignado' => ['Repartidor asignado', 'curso'],
            'asignado' => ['Repartidor asignado', 'curso'],
            'en_camino' => ['En camino', 'curso'],
            'en_ruta' => ['En camino', 'curso'],
            'entregado' => ['Entregado', 'entregado'],
            'cancelado' => ['Cancelado', 'cancelado'],
        ];
        $metodosPago = [
            'simulado_local' => 'Pago en línea',
            'transferencia' => 'Transferencia',
            'efectivo_entrega' => 'Efectivo contra entrega',
            'tarjeta_guardada' => 'Tarjeta guardada',
            'tarjeta_debito' => 'Tarjeta de débito',
            'tarjeta_credito' => 'Tarjeta de crédito',
        ];

        // Pasos del seguimiento, los mismos de la pagina del pedido
        $pasosSeguimiento = [
            ['titulo' => 'Pedido recibido', 'icono' => 'compras', 'estados' => ['recibido']],
            ['titulo' => 'En preparación', 'icono' => 'caja', 'estados' => ['en_preparacion', 'preparando']],
            ['titulo' => 'Listo para despacho', 'icono' => 'tienda', 'estados' => ['listo_despacho', 'reparto_asignado', 'asignado']],
            ['titulo' => 'En camino', 'icono' => 'seguimiento', 'estados' => ['en_camino', 'en_ruta']],
            ['titulo' => 'Entregado', 'icono' => 'inicio', 'estados' => ['entregado']],
        ];
    @endphp
    <div class="section-head">
        <div>
            <h2>Mis compras</h2>
            <p class="muted">Revisa el estado y el detalle de todas las compras que has realizado.</p>
        </div>
    </div>

    @if($compras->isEmpty())
        <div class="panel-card">
            <div class="empty-state">
                <x-icono nombre="compras" class="empty-state-icon" />
                <strong>Aún no tienes compras</strong>
                <span>Cuando compres en la tienda, verás aquí tu historial.</span>
                <a class="btn btn-orange" style="margin-top:10px" href="{{ route('tienda.inicio') }}">Ir a la tienda</a>
            </div>
        </div>
    @else
        <div class="order-list">
            @foreach($compras as $compra)
                @php
                    [$estadoTexto, $estadoGrupo] = $estadosCompra[$compra->estado] ?? [ucfirst(str_replace('_', ' ', $compra->estado)), 'curso'];
                    $tarjetaPago = data_get($compra->pago?->detalle, 'tarjeta.descripcion');
                    $medioPago = $tarjetaPago ?: ($metodosPago[$compra->pago?->metodo] ?? ($compra->pago ? ucfirst(str_replace('_', ' ', $compra->pago->metodo)) : 'Sin información'));
                    $esRetiro = $compra->direccion_entrega === 'Retiro en tienda';
                    $unidades = $compra->items->sum('cantidad');
                @endphp
                <article class="order-card" data-order-group="{{ $estadoGrupo }}">
                    <header class="order-head">
                        <div class="order-meta"><span>Fecha de compra</span><strong>{{ $compra->created_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}</strong></div>
                        <div class="order-meta"><span>Total</span><strong>${{ number_format($compra->total, 0, ',', '.') }}</strong></div>
                        <div class="order-meta"><span>N° de pedido</span><strong>{{ $compra->codigo_tracking }}</strong></div>
                    </header>
                    <div class="order-body">
                        <div>
                            <p class="order-note">
                                @if($estadoGrupo === 'entregado')
                                    Entregado{{ $compra->entregado_at ? ' el ' . $compra->entregado_at->locale('es')->translatedFormat('j \d\e F') : '' }}
                                @elseif($estadoGrupo === 'cancelado')
                                    Compra cancelada
                                @else
                                    {{ $esRetiro ? 'Retiro en tienda' : 'Llega' }}{{ $compra->fecha_entrega ? ' el ' . $compra->fecha_entrega->locale('es')->translatedFormat('l j \d\e F') : '' }}
                                @endif
                                <span>· {{ $unidades }} {{ $unidades === 1 ? 'producto' : 'productos' }}</span>
                                @if($compra->frecuencia && $compra->frecuencia !== 'unico')<span class="order-tag">Pedido programado</span>@endif
                            </p>
                            <ul class="order-items">
                                @foreach($compra->items as $item)
                                    @php
                                        $fotoItem = $item->producto?->foto_url
                                            ? asset($item->producto->foto_url)
                                            : ($fotosReferencia[$item->producto_nombre] ?? null);
                                    @endphp
                                    <li class="order-item">
                                        <span class="item-thumb">
                                            @if($fotoItem)
                                                <img src="{{ $fotoItem }}" alt="" loading="lazy">
                                            @else
                                                <span>{{ mb_strtoupper(mb_substr($item->producto_nombre, 0, 3)) }}</span>
                                            @endif
                                        </span>
                                        <span class="order-item-info">
                                            <strong>{{ $item->producto_nombre }}</strong>
                                            <span>{{ $item->producto_marca ? $item->producto_marca . ' · ' : '' }}{{ $item->cantidad }} {{ $item->cantidad === 1 ? 'unidad' : 'unidades' }}</span>
                                        </span>
                                        <span class="order-item-price">${{ number_format($item->total, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="order-side">
                            <a class="btn btn-success" href="{{ route('tracking.show', $compra->codigo_tracking) }}">Ver detalle</a>
                            @if($compra->items->whereNotNull('producto_id')->isNotEmpty())
                                <form method="POST" action="{{ route('cliente.compras.repetir', $compra) }}" data-cargando-tienda>
                                    @csrf
                                    <button type="submit" class="btn btn-orange-outline">Volver a comprar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @php
                        $etapaCompra = collect($pasosSeguimiento)->search(fn ($paso) => in_array($compra->estado, $paso['estados'], true));
                        $etapaCompra = $etapaCompra === false ? 0 : $etapaCompra;
                        $fechaPasoCompra = fn ($paso) => $compra->tracking->first(fn ($evento) => in_array($evento->estado, $paso['estados'], true))?->created_at;
                    @endphp
                    <details class="order-detail order-seguimiento">
                        <summary>{{ $estadoGrupo === 'curso' ? 'Seguir pedido' : 'Ver seguimiento del envío' }}</summary>
                        <div class="order-seguimiento-cuerpo">
                            @if($estadoGrupo === 'cancelado')
                                <p class="muted">Esta compra fue cancelada, por eso no tiene seguimiento.</p>
                            @else
                                <ol class="seguimiento-pasos" style="--avance:{{ round($etapaCompra / (count($pasosSeguimiento) - 1), 3) }}">
                                    @foreach($pasosSeguimiento as $indicePaso => $paso)
                                        @php $fechaAlcanzada = $indicePaso <= $etapaCompra ? $fechaPasoCompra($paso) : null; @endphp
                                        <li @class(['paso', 'is-hecho' => $indicePaso < $etapaCompra, 'is-actual' => $indicePaso === $etapaCompra])>
                                            <span class="paso-icono" aria-hidden="true">
                                                @if($indicePaso < $etapaCompra)
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                                                @else
                                                    <x-icono :nombre="$paso['icono']" />
                                                @endif
                                            </span>
                                            <span class="paso-texto">
                                                <span class="paso-titulo">{{ $paso['titulo'] }}</span>
                                                <span class="paso-fecha">{{ $fechaAlcanzada ? $fechaAlcanzada->locale('es')->translatedFormat('j M · H:i') : ($indicePaso === $etapaCompra ? 'Ahora' : 'Pendiente') }}</span>
                                            </span>
                                            <span class="visually-hidden">{{ $indicePaso < $etapaCompra ? '(completado)' : ($indicePaso === $etapaCompra ? '(etapa actual)' : '(pendiente)') }}</span>
                                        </li>
                                    @endforeach
                                </ol>

                                @php
                                    $ubicacionCompra = $compra->tracking->whereNotNull('latitud')->whereNotNull('longitud')->sortByDesc('created_at')->first();
                                    $transportista = $compra->repartidor;
                                @endphp
                                <div class="order-despacho">
                                    <div class="order-despacho-datos">
                                        <div class="order-despacho-dato">
                                            <span>N° de pedido</span>
                                            <strong>{{ $compra->codigo_tracking }}</strong>
                                        </div>
                                        <div @class(['order-despacho-dato', 'order-transportista', 'is-asignado' => $transportista])>
                                            <span>Transportista</span>
                                            @if($esRetiro)
                                                <strong>No aplica</strong>
                                                <small>Es retiro en tienda.</small>
                                            @elseif($transportista)
                                                <strong><x-icono nombre="usuario" />{{ $transportista->name }}</strong>
                                                <small>{{ collect([$transportista->vehiculo_patente ? 'Patente ' . $transportista->vehiculo_patente : null, trim(($transportista->vehiculo_marca ?? '') . ' ' . ($transportista->vehiculo_modelo ?? ''))])->filter()->implode(' · ') ?: 'Asignado a tu pedido' }}</small>
                                            @else
                                                <strong class="is-pendiente">Aún no asignado</strong>
                                                <small>Te avisaremos cuando un transportista tome tu pedido.</small>
                                            @endif
                                        </div>
                                    </div>
                                    @unless($esRetiro)
                                        <div class="order-mapa">
                                            @if($ubicacionCompra)
                                                <iframe src="https://maps.google.com/maps?q={{ $ubicacionCompra->latitud }},{{ $ubicacionCompra->longitud }}&z=15&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Ubicación del pedido {{ $compra->codigo_tracking }}"></iframe>
                                            @else
                                                <span class="order-mapa-vacio"><x-icono nombre="locacion" />El mapa aparecerá cuando tu pedido salga a reparto.</span>
                                            @endif
                                        </div>
                                    @endunless
                                </div>
                            @endif
                        </div>
                    </details>
                    <details class="order-detail">
                        <summary>Ver detalle de la compra</summary>
                        <div class="order-detail-grid">
                            <div>
                                <h3>{{ $esRetiro ? 'Retiro' : 'Despacho' }}</h3>
                                <p>
                                    {{ $esRetiro ? 'Retiro en tienda' : $compra->direccion_entrega }}
                                    @if(!$esRetiro && ($compra->ciudad_nombre || $compra->region_nombre))
                                        <br><span class="muted">{{ collect([$compra->ciudad_nombre, $compra->region_nombre])->filter()->implode(', ') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <h3>Medio de pago</h3>
                                <p>{{ $medioPago }}@if($compra->pagado_at)<br><span class="muted">Pagado el {{ $compra->pagado_at->format('d-m-Y H:i') }}</span>@endif</p>
                            </div>
                            <div class="order-totals">
                                <h3>Resumen</h3>
                                <div><span>Subtotal</span><span>${{ number_format($compra->subtotal, 0, ',', '.') }}</span></div>
                                <div><span>Envío</span><span>{{ $compra->costo_envio > 0 ? '$' . number_format($compra->costo_envio, 0, ',', '.') : 'Gratis' }}</span></div>
                                @if($compra->descuento_total > 0)
                                    <div><span>Descuento</span><span>-${{ number_format($compra->descuento_total, 0, ',', '.') }}</span></div>
                                @endif
                                <div class="order-total"><span>Total</span><span>${{ number_format($compra->total, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="menu-lateral-seccion" id="cliente-tarjetas" data-menu-panel="tarjetas">
    @php
        $tarjetas = $user->tarjetas;
        $maxTarjetas = \App\Models\TarjetaCliente::MAXIMO_POR_CLIENTE;
        $puedeAgregarTarjeta = $tarjetas->count() < $maxTarjetas;
    @endphp
    <div class="section-head">
        <div>
            <h2>Mis tarjetas</h2>
            <p class="muted">{{ $tarjetas->count() }} de {{ $maxTarjetas }} tarjetas guardadas. Al comprar, usaremos automáticamente tu tarjeta predeterminada.</p>
        </div>
        @if($puedeAgregarTarjeta)
            <button type="button" class="btn-form-toggle" data-form-toggle="form-tarjeta" data-label-abierto="Cerrar formulario" data-label-cerrado="Agregar tarjeta" aria-controls="form-tarjeta" aria-expanded="{{ $erroresTarjeta->any() ? 'true' : 'false' }}"><x-icono nombre="plus" /><span data-toggle-label>{{ $erroresTarjeta->any() ? 'Cerrar formulario' : 'Agregar tarjeta' }}</span></button>
        @endif
    </div>
    <div class="panel-card">

        <div class="saved-cards">
            @foreach($tarjetas as $tarjeta)
                <div class="saved-card">
                    <div class="card-visual marca-{{ \Illuminate\Support\Str::slug($tarjeta->marca) }} {{ $tarjeta->vencida ? 'is-vencida' : '' }}">
                        <div class="card-top">
                            <div><span class="card-brand">{{ $tarjeta->marca }}</span><span class="card-kind">{{ $tarjeta->tipo === 'debito' ? 'Débito' : 'Crédito' }}</span></div>
                            <span class="card-chip" aria-hidden="true"></span>
                        </div>
                        <div class="card-number" aria-label="Terminada en {{ $tarjeta->ultimos_digitos }}">•••• •••• •••• {{ $tarjeta->ultimos_digitos }}</div>
                        <div class="card-bottom">
                            <div><span>Titular</span><strong>{{ $tarjeta->titular }}</strong></div>
                            <div><span>Vence</span><strong>{{ $tarjeta->vencimiento }}</strong></div>
                        </div>
                    </div>
                    <div class="card-meta">
                        @if($tarjeta->predeterminada)<span class="card-badge badge-default">Predeterminada</span>@endif
                        @if($tarjeta->vencida)<span class="card-badge badge-vencida">Vencida</span>@endif
                        @if($tarjeta->alias)<span class="muted">{{ $tarjeta->alias }}</span>@endif
                    </div>
                    <div class="card-actions">
                        @unless($tarjeta->predeterminada || $tarjeta->vencida)
                            <form method="POST" action="{{ route('cliente.tarjetas.predeterminada', $tarjeta) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="link-action">Usar como predeterminada</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('cliente.tarjetas.destroy', $tarjeta) }}" data-confirmar="¿Eliminar la tarjeta terminada en {{ $tarjeta->ultimos_digitos }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-action danger"><x-icono nombre="eliminar" /> Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        @if($tarjetas->isEmpty())
            <div class="empty-state" @if($erroresTarjeta->any()) hidden @endif>
                <x-icono nombre="tarjeta" class="empty-state-icon" />
                <strong>Aún no tienes tarjetas guardadas</strong>
                <span>Agrega una tarjeta para pagar tus compras más rápido.</span>
            </div>
        @endif

        @if($puedeAgregarTarjeta)
            <div class="card-form-wrap {{ $erroresTarjeta->any() ? 'is-open' : '' }}" id="form-tarjeta">
                <div class="card-preview">
                    <div class="card-visual" id="tarjeta_preview">
                        <div class="card-top">
                            <div><span class="card-brand" data-preview="marca">Tarjeta</span><span class="card-kind" data-preview="tipo">{{ old('tipo') === 'debito' ? 'Débito' : 'Crédito' }}</span></div>
                            <span class="card-chip" aria-hidden="true"></span>
                        </div>
                        <div class="card-number" data-preview="numero">•••• •••• •••• ••••</div>
                        <div class="card-bottom">
                            <div><span>Titular</span><strong data-preview="titular">{{ old('titular') ?: 'NOMBRE APELLIDO' }}</strong></div>
                            <div><span>Vence</span><strong data-preview="vencimiento">{{ old('vencimiento') ?: 'MM/AA' }}</strong></div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('cliente.tarjetas.store') }}" data-keep-open="1" autocomplete="on">
                    @csrf
                    <h3 class="form-title">Datos de la tarjeta de débito o crédito</h3>
                    <p class="info-note"><x-icono nombre="candado" />Por seguridad solo guardamos la marca, los últimos 4 dígitos y el vencimiento. Nunca almacenamos el número completo ni el código de seguridad (CVV).</p>
                    <div class="compact-form">
                        <div class="span-12">
                            <span class="floating-label-activo-sm">Tipo de tarjeta</span>
                            <div class="card-type-options" role="radiogroup" aria-label="Tipo de tarjeta">
                                <label class="type-option"><input type="radio" name="tipo" value="debito" @checked(old('tipo') === 'debito') required><span>Débito<small>Redcompra</small></span></label>
                                <label class="type-option"><input type="radio" name="tipo" value="credito" @checked(old('tipo', 'credito') === 'credito')><span>Crédito<small>Visa, Mastercard, Amex, Diners</small></span></label>
                            </div>
                            @error('tipo', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-6 {{ $erroresTarjeta->has('numero_tarjeta') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_numero">Número de tarjeta</label>
                            <div class="card-number-field">
                                <input class="form-control form-control-sm" id="tarjeta_numero" name="numero_tarjeta" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000" required>
                                <span class="brand-detected" id="tarjeta_marca" aria-live="polite"></span>
                            </div>
                            @error('numero_tarjeta', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-6 {{ $erroresTarjeta->has('titular') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_titular">Nombre del titular</label>
                            <input class="form-control form-control-sm" id="tarjeta_titular" name="titular" value="{{ old('titular') }}" autocomplete="cc-name" maxlength="120" placeholder="Como aparece en la tarjeta" required>
                            @error('titular', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-2 {{ $erroresTarjeta->has('vencimiento') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_vencimiento">Vencimiento</label>
                            <input class="form-control form-control-sm" id="tarjeta_vencimiento" name="vencimiento" value="{{ old('vencimiento') }}" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA" required>
                            @error('vencimiento', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-6">
                            <label class="floating-label-activo-sm" for="tarjeta_alias">Alias (opcional)</label>
                            <input class="form-control form-control-sm" id="tarjeta_alias" name="alias" value="{{ old('alias') }}" maxlength="60" placeholder="Ej: Tarjeta personal">
                        </div>
                        <div class="span-4">
                            <label class="check-row"><input type="checkbox" name="predeterminada" value="1" @checked(old('predeterminada') || $tarjetas->isEmpty())> Usar como predeterminada</label>
                        </div>
                    </div>
                    <div class="card-form-actions">
                        <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-tarjeta"><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                        <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" />Guardar tarjeta</button>
                    </div>
                </form>
            </div>
        @else
            <p class="info-note" style="margin-top:18px"><x-icono nombre="tarjeta" />Alcanzaste el máximo de {{ $maxTarjetas }} tarjetas. Elimina una para agregar otra.</p>
        @endif
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-mi-plan" data-menu-panel="mi-plan">
    @php
        $planActivoComercial = collect($planesDisponibles)->firstWhere('slug', $user->plan_preferido);
        $planesMejora = collect($planesDisponibles)->reject(fn($plan) => $plan['slug'] === ($planActivoComercial['slug'] ?? null));
    @endphp
    <div class="section-head">
        <div>
            <h2>Mis suscripciones</h2>
            <p class="muted">
                @if($planActivoComercial)
                    Revisa tu suscripción actual y mejórala cuando quieras.
                @else
                    Elige una alternativa y luego crea tu primer pedido recurrente. Las suscripciones permiten alimento automático, vouchers, QR, historial y servicios programados.
                @endif
            </p>
        </div>
    </div>
    @if($planActivoComercial)
        <div class="section-layout">
            <div class="panel-card">
                <span class="plan-badge">{{ $planActivoComercial['etiqueta'] }}</span>
                <h2>Mi suscripción actual</h2>
                <h3>{{ $planActivoComercial['nombre'] }}</h3>
                <p class="muted">{{ $planActivoComercial['descripcion'] }}</p>
                <div class="plan-price">
                    <div class="price-box">
                        <span>Pagado al contratar</span>
                        <strong>${{ number_format($planActivoComercial['valor_inicial'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="price-box">
                        <span>Cargo mensual</span>
                        <strong>${{ number_format($planActivoComercial['valor_mensual'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <ul class="plan-includes">
                    @foreach($planActivoComercial['incluye'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="quick-actions" style="margin-top:14px">
                    <button class="btn" type="button" data-menu-ir="pedido">Configurar pedido recurrente</button>
                    <button class="btn btn-success" type="button" data-menu-ir="ofertas">Ver beneficios</button>
                </div>
            </div>
            <div class="panel-card">
                <h2>Mejorar suscripción</h2>
                <p class="muted">Puedes cambiar a una suscripción superior o complementar con otro beneficio. El botón te lleva a la pasarela de pago de la suscripción elegida.</p>
                <div class="list-card">
                    @foreach($planesMejora->take(3) as $planMejora)
                        <div class="item-row">
                            <strong>{{ $planMejora['nombre'] }}</strong>
                            <br><span class="muted">{{ $planMejora['descripcion'] }}</span>
                            <br><strong>${{ number_format($planMejora['valor_inicial'], 0, ',', '.') }}</strong>
                            <span class="muted"> inicio · ${{ number_format($planMejora['valor_mensual'], 0, ',', '.') }} mensual</span>
                            <br><a class="btn btn-success" style="margin-top:10px" href="{{ route('cliente.planes.pago', $planMejora['slug']) }}">Mejorar y pagar</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="plan-empty-alert" role="status">
            <x-icono nombre="suscripcion" />
            <div>
                <strong>Aún no tienes una suscripción activa</strong>
                <span>Elige una de las alternativas de abajo para comenzar.</span>
            </div>
        </div>
        <div class="plan-grid">
            @foreach($planesDisponibles as $planDisponible)
                <article class="plan-choice">
                    <span class="plan-badge">{{ $planDisponible['etiqueta'] }}</span>
                    <div>
                        <h2>{{ $planDisponible['nombre'] }}</h2>
                        <p>{{ $planDisponible['descripcion'] }}</p>
                    </div>
                    <div class="plan-price">
                        <div class="price-box">
                            <span>Inicio</span>
                            <strong>${{ number_format($planDisponible['valor_inicial'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="price-box">
                            <span>Mensual</span>
                            <strong>${{ number_format($planDisponible['valor_mensual'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <ul class="plan-includes">
                        @foreach($planDisponible['incluye'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="btn btn-success" href="{{ route('cliente.planes.pago', $planDisponible['slug']) }}">Contratar y pagar</a>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="menu-lateral-seccion" id="cliente-mascotas" data-menu-panel="mascotas">
    @php
        $especiesMascota = ['perro' => 'Perro', 'gato' => 'Gato', 'otro' => 'Otro'];
        $sexosMascota = ['macho' => 'Macho', 'hembra' => 'Hembra', 'desconocido' => 'No sé'];
    @endphp
    <div class="section-head form-toggle-row">
        <div>
            <h2>Mis mascotas</h2>
            <p class="muted">Registra a tus mascotas para personalizar sus pedidos, suscripciones y beneficios.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-mascota" data-label-cerrado="Agregar mascota" data-label-abierto="Cerrar formulario" aria-controls="form-mascota" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Agregar mascota</span></button>
    </div>

    <div class="panel-card mascota-form-card collapsible-form" id="form-mascota">
        <form method="POST" enctype="multipart/form-data" action="{{ route('cliente.mascotas.store') }}" data-keep-open="1" data-form-mascota>
            @csrf
            <input type="hidden" name="mascota_id" value="">
            <div class="mascota-form-cabecera">
                <h3 class="form-title" data-mascota-titulo>Nueva mascota</h3>
                <p class="muted" data-mascota-subtitulo>Completa sus datos. Solo el nombre es obligatorio.</p>
            </div>

            <div class="mascota-form">
                <div class="mascota-form-campos compact-form">
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_nombre">Nombre</label>
                        <input class="form-control form-control-sm" name="nombre" id="mascota_nombre" placeholder="Ej: Max, Luna, Pelusa" required>
                    </div>
                    <div class="span-6">
                        <span class="floating-label-activo-sm">Especie</span>
                        <div class="card-type-options mascota-opciones" role="radiogroup" aria-label="Especie">
                            @foreach($especiesMascota as $valorEspecie => $textoEspecie)
                                <label class="type-option type-option--icono">
                                    <input type="radio" name="especie" value="{{ $valorEspecie }}" @checked($loop->first) required>
                                    <span><x-icono :nombre="$valorEspecie === 'otro' ? 'mascota' : $valorEspecie" />{{ $textoEspecie }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_raza">Raza</label>
                        <input class="form-control form-control-sm" name="raza" id="mascota_raza" placeholder="Ej: Mestizo, Poodle">
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_color">Color</label>
                        <input class="form-control form-control-sm" name="color" id="mascota_color" placeholder="Ej: Café con blanco">
                    </div>
                    <div class="span-6">
                        <span class="floating-label-activo-sm">Sexo</span>
                        <div class="card-type-options mascota-opciones" role="radiogroup" aria-label="Sexo">
                            @foreach($sexosMascota as $valorSexo => $textoSexo)
                                <label class="type-option type-option--icono">
                                    <input type="radio" name="sexo" value="{{ $valorSexo }}">
                                    <span>{{ $textoSexo }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_fecha">Fecha de nacimiento</label>
                        <input class="form-control form-control-sm" type="date" name="fecha_nacimiento" id="mascota_fecha" max="{{ now()->toDateString() }}">
                    </div>
                    <div class="span-12">
                        <label class="panel-interruptor">
                            <input type="checkbox" name="esterilizado" value="1">
                            <span class="panel-interruptor-pista" aria-hidden="true"></span>
                            <span>Está esterilizado(a)</span>
                        </label>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_alergias">Alergias o restricciones</label>
                        <textarea class="form-control form-control-sm" name="alergias" id="mascota_alergias" rows="3" placeholder="Ej: alergia al pollo, dieta renal"></textarea>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="mascota_observaciones">Cuidados especiales</label>
                        <textarea class="form-control form-control-sm" name="observaciones" id="mascota_observaciones" rows="3" placeholder="Ej: prefiere alimento húmedo, es nervioso"></textarea>
                    </div>
                </div>

                <div class="mascota-form-foto">
                    <span class="floating-label-activo-sm">Foto</span>
                    <x-zona-foto name="foto" id="mascota_foto" texto="Arrastra su foto aquí" />
                </div>
            </div>

            <div class="card-form-actions">
                <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-mascota"><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" /><span data-mascota-guardar>Guardar mascota</span></button>
            </div>
        </form>
    </div>

    <div class="mascotas-grid">
        @foreach($user->mascotas as $mascota)
            @php
                $especieTexto = Str::lower(Str::ascii(trim((string) $mascota->especie)));
                $especieCard = match (true) {
                    in_array($especieTexto, ['perro', 'perra', 'canino', 'canina', 'can'], true) => 'perro',
                    in_array($especieTexto, ['gato', 'gata', 'felino', 'felina'], true) => 'gato',
                    default => 'otro',
                };
                $sexoCard = match (Str::lower(trim((string) $mascota->sexo))) {
                    'm', 'macho' => 'macho',
                    'f', 'h', 'hembra' => 'hembra',
                    default => null,
                };
                $edadMascota = null;
                if ($mascota->fecha_nacimiento) {
                    $tiempo = $mascota->fecha_nacimiento->diff(now());
                    $edadMascota = $tiempo->y
                        ? $tiempo->y . ($tiempo->y === 1 ? ' año' : ' años')
                        : ($tiempo->m ? $tiempo->m . ($tiempo->m === 1 ? ' mes' : ' meses') : 'Recién nacido');
                }
                $datosMascota = [
                    'id' => $mascota->id,
                    'nombre' => $mascota->nombre,
                    'especie' => $especieCard,
                    'raza' => $mascota->raza,
                    'sexo' => $sexoCard,
                    'color' => $mascota->color,
                    'fecha' => optional($mascota->fecha_nacimiento)->toDateString(),
                    'esterilizado' => (bool) $mascota->esterilizado,
                    'alergias' => $mascota->alergias,
                    'observaciones' => $mascota->observaciones,
                    'foto' => $mascota->foto_url ? asset($mascota->foto_url) : null,
                ];
            @endphp
            <article class="mascota-card">
                <div class="mascota-card-foto">
                    <x-icono :nombre="$especieCard === 'otro' ? 'mascota' : $especieCard" />
                    @if($mascota->foto_url)
                        <img src="{{ asset($mascota->foto_url) }}" alt="{{ $mascota->nombre }}" loading="lazy" onerror="this.remove()">
                    @endif
                </div>
                <div class="mascota-card-cuerpo">
                    <h3>{{ $mascota->nombre }}</h3>
                    <p class="muted">{{ $especieCard === 'otro' ? ($mascota->especie ? Str::ucfirst($mascota->especie) : 'Otra especie') : $especiesMascota[$especieCard] }}{{ $mascota->raza ? ' · ' . $mascota->raza : '' }}</p>
                    <ul class="mascota-chips">
                        @if($sexoCard)<li>{{ $sexosMascota[$sexoCard] }}</li>@endif
                        @if($edadMascota)<li>{{ $edadMascota }}</li>@endif
                        @if($mascota->color)<li>{{ $mascota->color }}</li>@endif
                        @if($mascota->esterilizado)<li class="is-verde">Esterilizado</li>@endif
                    </ul>
                    @if($mascota->alergias)
                        <p class="mascota-alerta"><strong>Alergias:</strong> {{ $mascota->alergias }}</p>
                    @endif
                </div>
                <button type="button" class="mascota-card-editar" data-mascota-editar="{{ json_encode($datosMascota) }}"><x-icono nombre="editar" />Editar</button>
            </article>
        @endforeach

        <button type="button" @class(['mascota-card', 'mascota-card--nueva', 'is-sola' => $user->mascotas->isEmpty()]) data-mascota-nueva>
            <span class="mascota-card-nueva-icono" aria-hidden="true"><x-icono nombre="plus" /></span>
            <strong>{{ $user->mascotas->isEmpty() ? 'Agrega tu primera mascota' : 'Agregar otra mascota' }}</strong>
            <span class="muted">Con su foto y sus datos, en un minuto.</span>
        </button>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-direcciones" data-menu-panel="direcciones">
    @php $primeraDireccion = $user->direcciones->isEmpty(); @endphp
    <div class="section-head form-toggle-row">
        <div>
            <h2>Direcciones de entrega</h2>
            <p class="muted">Guarda dónde quieres recibir tus pedidos.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-direccion" data-label-cerrado="Agregar dirección" data-label-abierto="Cerrar formulario" aria-controls="form-direccion" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Agregar dirección</span></button>
    </div>

    <div class="panel-card direccion-form-card collapsible-form" id="form-direccion">
        <form method="POST" action="{{ route('cliente.direcciones.store') }}" data-keep-open="1" data-form-direccion>
            @csrf
            <input type="hidden" name="direccion_id" value="">
            {{-- Preferencias que ya no se muestran: se conservan tal cual al editar --}}
            <input type="hidden" name="dia_preferencia" value="">
            <input type="hidden" name="horario_preferencia" value="">
            <input type="hidden" name="forma_pago_preferida" value="">

            <div class="mascota-form-cabecera">
                <h3 class="form-title" data-direccion-titulo>Nueva dirección</h3>
                <p class="muted" data-direccion-subtitulo>Indica dónde quieres recibir tus pedidos.</p>
            </div>

            <div class="compact-form direccion-form">
                <div class="span-4">
                    <label class="floating-label-activo-sm" for="direccion_alias">Nombre de la dirección</label>
                    <input class="form-control form-control-sm" name="alias" id="direccion_alias" value="{{ $primeraDireccion ? 'Casa' : '' }}" placeholder="Ej: Casa, Trabajo" required>
                    <div class="direccion-sugerencias" aria-label="Nombres sugeridos">
                        @foreach(['Casa', 'Trabajo', 'Familiar'] as $sugerencia)
                            <button type="button" class="direccion-sugerencia" data-alias-sugerido="{{ $sugerencia }}">{{ $sugerencia }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="span-8">
                    <label class="floating-label-activo-sm" for="direccion_texto">Dirección</label>
                    <input class="form-control form-control-sm" name="direccion" id="direccion_texto" value="{{ $primeraDireccion ? $user->direccion : '' }}" placeholder="Calle, número y depto" autocomplete="street-address" required>
                </div>
                <div class="span-6">
                    <label class="floating-label-activo-sm" for="direccion_region">Región</label>
                    <select class="form-control form-control-sm" name="region_id" id="direccion_region" required>
                        <option value="">Selecciona una región</option>
                        @foreach($regionesVet as $region)
                            <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="span-6">
                    <label class="floating-label-activo-sm" for="direccion_comuna">Comuna</label>
                    <select class="form-control form-control-sm" name="comuna_id" id="direccion_comuna" required disabled>
                        <option value="">Selecciona primero una región</option>
                    </select>
                </div>
                <div class="span-12">
                    <label class="floating-label-activo-sm" for="direccion_referencia">Referencia para la entrega (opcional)</label>
                    <input class="form-control form-control-sm" name="referencia" id="direccion_referencia" placeholder="Ej: conserjería, portón azul, llamar antes">
                </div>
                <div class="span-12">
                    <label class="panel-interruptor">
                        <input type="checkbox" name="principal" value="1" @checked($primeraDireccion)>
                        <span class="panel-interruptor-pista" aria-hidden="true"></span>
                        <span>Usar como dirección principal</span>
                    </label>
                </div>
            </div>

            <div class="card-form-actions">
                <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-direccion"><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" /><span data-direccion-guardar>Guardar dirección</span></button>
            </div>
        </form>
    </div>

    <div class="direcciones-grid">
        @foreach($user->direcciones->sortByDesc('principal') as $direccion)
            @php
                $datosDireccion = [
                    'id' => $direccion->id,
                    'alias' => $direccion->alias,
                    'direccion' => $direccion->direccion,
                    'region_id' => $direccion->region_id,
                    'comuna_id' => $direccion->comuna_id,
                    'referencia' => $direccion->referencia,
                    'principal' => (bool) $direccion->principal,
                    'dia' => $direccion->dia_preferencia,
                    'horario' => $direccion->horario_preferencia,
                    'pago' => $direccion->forma_pago_preferida,
                ];
            @endphp
            <article @class(['direccion-card', 'is-principal' => $direccion->principal])>
                <div class="direccion-card-cabecera">
                    <span class="direccion-card-icono" aria-hidden="true"><x-icono nombre="locacion" /></span>
                    <strong>{{ $direccion->alias }}</strong>
                    @if($direccion->principal)<span class="direccion-card-principal">Principal</span>@endif
                </div>
                <p class="direccion-card-calle">{{ $direccion->direccion }}</p>
                @if($direccion->region || $direccion->comuna)
                    <p class="muted">{{ collect([$direccion->comuna, $direccion->region])->filter()->implode(', ') }}</p>
                @endif
                @if($direccion->referencia)
                    <p class="direccion-card-referencia">{{ $direccion->referencia }}</p>
                @endif
                <div class="direccion-card-acciones">
                    <button type="button" class="mascota-card-editar" data-direccion-editar="{{ json_encode($datosDireccion) }}"><x-icono nombre="editar" />Editar</button>
                    <form method="POST" action="{{ route('cliente.direcciones.destroy', $direccion) }}" data-confirmar="¿Eliminar la dirección «{{ $direccion->alias }}»?">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="direccion-card-eliminar" aria-label="Eliminar {{ $direccion->alias }}"><x-icono nombre="eliminar" /></button>
                    </form>
                </div>
            </article>
        @endforeach

        <button type="button" @class(['direccion-card', 'direccion-card--nueva', 'is-sola' => $primeraDireccion]) data-direccion-nueva>
            <span class="mascota-card-nueva-icono" aria-hidden="true"><x-icono nombre="plus" /></span>
            <strong>{{ $primeraDireccion ? 'Agrega tu primera dirección' : 'Agregar otra dirección' }}</strong>
            <span class="muted">Casa, trabajo o donde quieras recibir.</span>
        </button>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-pedido" data-menu-panel="pedido">
    <div class="section-head form-toggle-row">
        <div>
            <h2>Pedidos programados</h2>
            <p class="muted">Recibe el alimento de tus mascotas automáticamente, con la frecuencia que elijas.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-pedido" data-label-cerrado="Nuevo pedido" aria-controls="form-pedido" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Nuevo pedido</span></button>
    </div>
    <div class="wide-section-layout">
        <div class="panel-card collapsible-form" id="form-pedido">
            <form method="POST" action="{{ route('cliente.planes.store') }}" data-keep-open="1">
                @csrf
                <h3 class="form-title">Datos del pedido</h3>
                <div class="compact-plan-form">
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Suscripción</label>
                        <select class="form-control form-control-sm" name="plan_id" id="plan_id">
                            <option value="">Nueva suscripción</option>
                            @foreach($user->planesPedido->where('activo', true) as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->producto->nombre }} - {{ $plan->frecuencia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Mascota</label>
                        <select class="form-control form-control-sm" name="mascota_id" id="plan_mascota_id">
                            <option value="">Sin mascota específica</option>
                            @foreach($user->mascotas as $mascota)
                                <option value="{{ $mascota->id }}">{{ $mascota->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Alimento</label>
                        <select class="form-control form-control-sm" name="producto_id" id="plan_producto_id" required>
                            @foreach($productos->where('categoria', 'alimento_mascota') as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} - ${{ number_format($producto->precio, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Frecuencia</label>
                        <select class="form-control form-control-sm" name="frecuencia" id="plan_frecuencia">
                            <option value="mensual">Mensual</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Cantidad</label><input class="form-control form-control-sm" type="number" name="cantidad" id="plan_cantidad" value="1" min="1" required></div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Voucher</label>
                        <select class="form-control form-control-sm" name="voucher_descuento_id" id="plan_voucher_id">
                            <option value="">Sin voucher</option>
                            @foreach($vouchersPlan as $voucher)
                                <option value="{{ $voucher->id }}">{{ $voucher->codigo }} - {{ $voucher->titulo }} ({{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Próxima entrega</label><input class="form-control form-control-sm" type="date" name="proxima_entrega" id="plan_proxima_entrega" value="{{ now()->addWeek()->toDateString() }}" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Forma de pago</label>
                        <select class="form-control form-control-sm" name="forma_pago" id="plan_forma_pago">
                            <option value="">Usar forma de pago de la dirección</option>
                            <option value="tarjeta">Tarjeta inscrita</option>
                            <option value="cargo_mensual">Cargo mensual automático</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="efectivo">Efectivo al recibir</option>
                        </select>
                    </div>
                    <div class="span-8">
                        <label class="floating-label-activo-sm">Dirección de entrega</label>
                        <select class="form-control form-control-sm" name="direccion_entrega" id="plan_direccion_entrega" required>
                            <option value="{{ $user->direccion }}">{{ $user->direccion ?: 'Dirección principal' }}</option>
                            @foreach($user->direcciones as $direccion)
                                <option value="{{ $direccion->direccion }}">{{ $direccion->alias }} - {{ $direccion->direccion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4">
                        <button type="button" class="btn btn-secondary payment-register" id="inscribir_tarjeta_btn" data-menu-ir="tarjetas">Inscribir tarjeta</button>
                    </div>
                </div>
                <div class="card-form-actions">
                    <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-pedido"><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
                    <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" />Guardar pedido programado</button>
                </div>
            </form>
        </div>
        <div class="panel-card">
            <div class="list-card">
                @forelse($user->planesPedido->where('activo', true) as $plan)
                    <div class="item-row between">
                        <span class="item-main">
                            <span class="item-thumb"><x-icono nombre="carrito" /></span>
                            <span>
                                <strong>{{ $plan->producto->nombre }}</strong>
                                <br><span class="muted">{{ ucfirst($plan->frecuencia) }} · {{ $plan->cantidad }} {{ $plan->cantidad == 1 ? 'unidad' : 'unidades' }} · Próxima entrega {{ $plan->proxima_entrega->format('d-m-Y') }}</span>
                                <br><span>Mascota: {{ $plan->mascota?->nombre ?? 'General' }} · Voucher: {{ $plan->voucher?->codigo ?? 'Sin voucher' }}@if($plan->forma_pago) · Pago: {{ str_replace('_', ' ', $plan->forma_pago) }}@endif</span>
                                <br><span class="muted">{{ $plan->direccion_entrega }}</span>
                            </span>
                        </span>
                        <span class="actions">
                            <button type="button"
                                class="edit-plan-btn link-action"
                                data-id="{{ $plan->id }}"
                                data-mascota="{{ $plan->mascota_id }}"
                                data-producto="{{ $plan->producto_id }}"
                                data-voucher="{{ $plan->voucher_descuento_id }}"
                                data-frecuencia="{{ $plan->frecuencia }}"
                                data-cantidad="{{ $plan->cantidad }}"
                                data-proxima="{{ $plan->proxima_entrega->toDateString() }}"
                                data-direccion="{{ $plan->direccion_entrega }}"
                                data-pago="{{ $plan->forma_pago }}"><x-icono nombre="editar" /> Cambiar</button>
                            <form method="POST" action="{{ route('cliente.planes.anular', $plan) }}" class="inline-form" data-confirmar="¿Anular el pedido programado de {{ $plan->producto->nombre }}?">
                                @csrf
                                <button type="submit" class="btn-secondary link-action danger">Anular</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <div class="empty-state">
                        <x-icono nombre="suscripcion" class="empty-state-icon" />
                        <strong>Aún no tienes pedidos programados</strong>
                        <span>Configura un pedido para recibir alimento automáticamente.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-ofertas" data-menu-panel="ofertas">
    @php
        $ofertas = [
            [
                'titulo' => 'Alimentos recomendados',
                'texto' => 'Productos base para armar o complementar tu pedido recurrente.',
                'categoria' => 'alimento_mascota',
                'link' => route('tienda.catalogo', ['categoria' => 'alimento_mascota']),
                'items' => $productos->where('categoria', 'alimento_mascota')->take(4),
            ],
            [
                'titulo' => 'Farmacia y cuidados',
                'texto' => 'Antiparasitarios, suplementos, higiene y productos útiles para el cuidado diario.',
                'categoria' => 'medicamento',
                'link' => '#', // Farmacia tendra su propio sitio: enlace pendiente
                'items' => $productos->whereIn('categoria', ['medicamento', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Servicios a domicilio',
                'texto' => 'Baño, peluquería, veterinaria móvil, paseos, hotel y otros servicios programables.',
                'categoria' => 'servicio',
                'link' => route('tienda.catalogo', ['categoria' => 'servicios']),
                'items' => $productos->whereIn('categoria', ['servicio', 'hotel', 'paseo_diario', 'cementerio'])->take(4),
            ],
            [
                'titulo' => 'Juguetes y entretención',
                'texto' => 'Juguetes, mordedores y artículos para enriquecer la rutina de la mascota.',
                'categoria' => 'juguete',
                'link' => route('tienda.catalogo', ['categoria' => 'juguete']),
                'items' => $productos->where('categoria', 'juguete')->take(4),
            ],
            [
                'titulo' => 'Accesorios para casa',
                'texto' => 'Platos, correas, dispensadores, higiene y accesorios para el día a día.',
                'categoria' => 'utensilio',
                'link' => route('tienda.catalogo', ['categoria' => 'utensilio']),
                'items' => $productos->whereIn('categoria', ['utensilio', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Beneficios con voucher',
                'texto' => 'Descuentos disponibles para asociar a suscripciones, compras o servicios.',
                'categoria' => 'voucher',
                'link' => route('tienda.catalogo', ['categoria' => 'adicional']),
                'items' => collect(),
            ],
        ];
    @endphp
    <div class="offers-grid">
        @foreach($ofertas as $oferta)
            <article class="offer-card">
                <span class="offer-badge">{{ $oferta['categoria'] }}</span>
                <div>
                    <h2>{{ $oferta['titulo'] }}</h2>
                    <p>{{ $oferta['texto'] }}</p>
                </div>

                @if($oferta['categoria'] === 'voucher')
                    <ul class="offer-list">
                        @forelse($vouchersPlan->take(4) as $voucher)
                            <li>
                                <span><strong>{{ $voucher->titulo }}</strong><br><span class="muted">{{ $voucher->codigo }}</span></span>
                                <span class="offer-price">{{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <li><span class="muted">No hay vouchers disponibles por ahora.</span></li>
                        @endforelse
                    </ul>
                @else
                    <ul class="offer-list">
                        @forelse($oferta['items'] as $producto)
                            @php
                                $voucherProducto = $vouchersPlan
                                    ->filter(fn ($voucher) => ((int) $voucher->producto_id === (int) $producto->id || (!$voucher->producto_id && $voucher->categoria_aplicable === $producto->categoria)) && $producto->precio >= $voucher->monto_minimo)
                                    ->sortByDesc(function ($voucher) use ($producto) {
                                        return $voucher->tipo_descuento === 'porcentaje'
                                            ? (int) round($producto->precio * $voucher->valor / 100)
                                            : min($voucher->valor, $producto->precio);
                                    })
                                    ->first();
                                $descuentoProducto = $voucherProducto
                                    ? ($voucherProducto->tipo_descuento === 'porcentaje' ? (int) round($producto->precio * $voucherProducto->valor / 100) : min($voucherProducto->valor, $producto->precio))
                                    : 0;
                                $precioVoucher = max(0, $producto->precio - $descuentoProducto);
                            @endphp
                            <li @class(['has-voucher' => $voucherProducto])>
                                @if($voucherProducto)
                                    <span class="product-voucher-badge">% CON VOUCHER · {{ $voucherProducto->tipo_descuento === 'porcentaje' ? $voucherProducto->valor . '%' : '$' . number_format($voucherProducto->valor, 0, ',', '.') }}</span>
                                @endif
                                @php
                                    $fotoOferta = $producto->foto_url
                                        ? asset($producto->foto_url)
                                        : ($fotosReferencia[$producto->nombre] ?? null);
                                @endphp
                                <span class="item-thumb offer-thumb">
                                    @if($fotoOferta)
                                        <img src="{{ $fotoOferta }}" alt="{{ $producto->nombre }}" loading="lazy">
                                    @else
                                        <span>{{ strtoupper(substr($producto->categoria, 0, 3)) }}</span>
                                    @endif
                                </span>
                                <span class="offer-info"><strong>{{ $producto->nombre }}</strong><br><span class="muted">{{ $producto->marca }} {{ $producto->peso }}</span>@if($voucherProducto)<span class="voucher-code">Código: {{ $voucherProducto->codigo }}</span>@endif</span>
                                <span class="offer-side">
                                    @if($voucherProducto)
                                        <span class="old-offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                        <span class="offer-price discounted-offer-price">${{ number_format($precioVoucher, 0, ',', '.') }}</span>
                                    @else
                                        <span class="offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                    @endif
                                    <form class="offer-add" method="POST" action="{{ route('tienda.agregar', $producto) }}">
                                        @csrf
                                        <input type="hidden" name="cantidad" value="1">
                                        <button class="extra-btn" title="Agregar al carro" aria-label="Agregar {{ $producto->nombre }} al carro"><x-icono nombre="carrito" class="isdi-blanco" /></button>
                                    </form>
                                </span>
                            </li>
                        @empty
                            <li><span class="muted">Sin productos activos en esta categoría.</span></li>
                        @endforelse
                    </ul>
                @endif

                <a class="btn btn-orange-outline" href="{{ $oferta['link'] }}">Ver en tienda</a>
            </article>
        @endforeach
    </div>
</section>

</div>
</div>
</div>

<script>
    (function () {
        var botonTienda = document.querySelector('.carro-boton');
        var formularios = document.querySelectorAll('.offer-add');

        if (!formularios.length) {
            return;
        }

        function pintarContador(total) {
            if (!botonTienda) {
                return;
            }

            var contador = botonTienda.querySelector('.carro-contador');

            if (total > 0) {
                if (!contador) {
                    contador = document.createElement('span');
                    contador.className = 'carro-contador';
                    botonTienda.appendChild(contador);
                }

                contador.textContent = total;
                contador.classList.remove('is-nuevo');
                void contador.offsetWidth;
                contador.classList.add('is-nuevo');
                return;
            }

            if (contador) {
                contador.remove();
            }
        }

        formularios.forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                evento.preventDefault();

                if (formulario.dataset.enviando === '1') {
                    return;
                }

                var boton = formulario.querySelector('.extra-btn');
                formulario.dataset.enviando = '1';
                boton.classList.add('is-cargando');

                fetch(formulario.action, {
                    method: 'POST',
                    body: new FormData(formulario),
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (respuesta) {
                        if (!respuesta.ok) {
                            throw new Error(respuesta.status);
                        }
                        return respuesta.text();
                    })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var nuevo = doc.querySelector('.carro-contador');
                        pintarContador(nuevo ? parseInt(nuevo.textContent, 10) || 0 : 0);

                        boton.classList.remove('is-cargando');
                        boton.classList.add('is-listo');
                        setTimeout(function () {
                            boton.classList.remove('is-listo');
                        }, 900);

                        formulario.dataset.enviando = '0';
                    })
                    .catch(function () {
                        boton.classList.remove('is-cargando');
                        formulario.dataset.enviando = '0';
                        formulario.submit();
                    });
            });
        });
    }());
    function bindSelectLoader(selectId, mapping) {
        var select = document.getElementById(selectId);
        if (!select) return;

        select.addEventListener('change', function () {
            var option = select.options[select.selectedIndex];
            Object.keys(mapping).forEach(function (key) {
                var field = document.getElementById(mapping[key]);
                if (!field) return;
                if (field.type === 'checkbox') {
                    field.checked = option.dataset[key] === '1';
                    return;
                }
                field.value = option.dataset[key] || '';
            });
        });
    }

    var planFormaPago = document.getElementById('plan_forma_pago');
    var inscribirTarjetaBtn = document.getElementById('inscribir_tarjeta_btn');
    if (planFormaPago && inscribirTarjetaBtn) {
        var toggleTarjeta = function () {
            inscribirTarjetaBtn.classList.toggle('is-visible', ['tarjeta', 'cargo_mensual'].indexOf(planFormaPago.value) >= 0);
        };
        planFormaPago.addEventListener('change', toggleTarjeta);
        toggleTarjeta();
    }

    function pintarToggle(button, abierto) {
        var texto = abierto ? (button.dataset.labelAbierto || 'Cerrar formulario') : (button.dataset.labelCerrado || 'Abrir formulario');
        var etiqueta = button.querySelector('[data-toggle-label]');
        (etiqueta || button).textContent = texto;
        button.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    }

    function sincronizarToggles(id, abierto) {
        document.querySelectorAll('[data-form-toggle="' + id + '"]').forEach(function (toggle) {
            pintarToggle(toggle, abierto);
        });
    }

    // Abre un formulario desplegable, lo lleva a la vista y enfoca su primer campo.
    function abrirFormulario(id, enfocar) {
        var contenedor = document.getElementById(id);
        if (!contenedor) return null;
        contenedor.classList.add('is-open');
        sincronizarToggles(id, true);
        var vacio = contenedor.closest('.panel-card')?.querySelector('.empty-state');
        if (vacio) vacio.hidden = true;
        contenedor.scrollIntoView({behavior: 'smooth', block: 'start'});
        if (enfocar !== false) {
            var primero = contenedor.querySelector('input:not([type="hidden"]):not([type="radio"]):not([type="file"])');
            if (primero) primero.focus({preventScroll: true});
        }
        return contenedor;
    }

    function cerrarFormulario(id) {
        var contenedor = document.getElementById(id);
        if (!contenedor) return;
        contenedor.classList.remove('is-open');
        var formulario = contenedor.querySelector('form');
        if (formulario) {
            formulario.reset();
            formulario.querySelectorAll('select').forEach(function (select) {
                select.dispatchEvent(new Event('change'));
            });
            formulario.dispatchEvent(new Event('input'));
        }
        var vacio = contenedor.closest('.panel-card')?.querySelector('.empty-state');
        if (vacio) vacio.hidden = false;
        sincronizarToggles(id, false);
    }

    document.querySelectorAll('[data-form-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.dataset.formToggle;
            var contenedor = document.getElementById(id);
            if (!contenedor) return;
            if (contenedor.classList.contains('is-open')) {
                cerrarFormulario(id);
            } else {
                abrirFormulario(id);
            }
        });
    });

    document.querySelectorAll('[data-form-toggle-cerrar]').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.dataset.formToggleCerrar;
            cerrarFormulario(id);
            var toggle = document.querySelector('[data-form-toggle="' + id + '"]');
            if (toggle) toggle.focus();
        });
    });

    // Formularios con datos fijos que se habilitan con "Editar".
    document.querySelectorAll('[data-edit-toggle]').forEach(function (boton) {
        var formulario = document.getElementById(boton.dataset.editToggle);
        if (!formulario) return;
        var fieldset = formulario.querySelector('fieldset');

        function editar(activo) {
            formulario.classList.toggle('is-editing', activo);
            fieldset.disabled = !activo;
            boton.hidden = activo;
        }

        boton.addEventListener('click', function () {
            editar(true);
            var primero = fieldset.querySelector('input:not([type="hidden"])');
            if (primero) primero.focus();
        });

        formulario.querySelectorAll('[data-edit-cancel]').forEach(function (cancelar) {
            cancelar.addEventListener('click', function () {
                formulario.reset();
                formulario.querySelectorAll('.field-error').forEach(function (error) { error.remove(); });
                formulario.querySelectorAll('.has-error').forEach(function (campo) { campo.classList.remove('has-error'); });
                formulario.querySelectorAll('[data-pass-toggle][aria-pressed="true"]').forEach(function (ojo) { ojo.click(); });
                formulario.dispatchEvent(new Event('input'));
                editar(false);
                boton.focus();
            });
        });

        editar(formulario.classList.contains('is-editing'));
    });

    document.querySelectorAll('[data-pass-toggle]').forEach(function (ojo) {
        ojo.addEventListener('click', function () {
            var campo = document.getElementById(ojo.dataset.passToggle);
            if (!campo) return;
            var visible = campo.type === 'password';
            campo.type = visible ? 'text' : 'password';
            ojo.setAttribute('aria-pressed', visible ? 'true' : 'false');
            ojo.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    var formPassword = document.getElementById('form-password');
    if (formPassword) {
        var passNueva = document.getElementById('pass_password');
        var passRepetir = document.getElementById('pass_password_confirmation');
        var botonGuardarPassword = formPassword.querySelector('.edit-actions button[type="submit"]');
        var contadorPassword = formPassword.querySelector('[data-pass-contador]');
        var reglas = {
            largo: function () { return passNueva.value.length >= 6 && passNueva.value.length <= 8; },
            mezcla: function () { return /\p{L}/u.test(passNueva.value) && /[^\p{L}\s]/u.test(passNueva.value); },
            coincide: function () { return passNueva.value !== '' && passNueva.value === passRepetir.value; }
        };
        // Cada regla se evalúa recién cuando se escribió en su campo
        var campoDeRegla = { largo: passNueva, mezcla: passNueva, coincide: passRepetir };

        var pintarPassword = function () {
            var todoOk = true;
            Object.keys(reglas).forEach(function (regla) {
                var item = formPassword.querySelector('[data-regla="' + regla + '"]');
                var cumple = reglas[regla]();
                var escrito = campoDeRegla[regla].value !== '';
                todoOk = todoOk && cumple;
                if (!item) return;
                item.classList.toggle('ok', escrito && cumple);
                item.classList.toggle('is-falta', escrito && !cumple);
            });

            var nuevaOk = reglas.largo() && reglas.mezcla();
            passNueva.closest('.pass-field').classList.toggle('is-ok', passNueva.value !== '' && nuevaOk);
            passNueva.closest('.pass-field').classList.toggle('is-error', passNueva.value !== '' && !nuevaOk);
            passRepetir.closest('.pass-field').classList.toggle('is-ok', passRepetir.value !== '' && reglas.coincide());
            passRepetir.closest('.pass-field').classList.toggle('is-error', passRepetir.value !== '' && !reglas.coincide());

            if (contadorPassword) {
                contadorPassword.textContent = passNueva.value.length + ' de 8';
                contadorPassword.classList.toggle('is-ok', reglas.largo());
            }
            if (botonGuardarPassword) {
                botonGuardarPassword.disabled = !todoOk;
                botonGuardarPassword.title = todoOk ? '' : 'Completa los requisitos de la contraseña';
            }
        };

        ['input', 'keyup', 'change'].forEach(function (tipo) {
            passNueva.addEventListener(tipo, pintarPassword);
            passRepetir.addEventListener(tipo, pintarPassword);
        });
        formPassword.addEventListener('reset', function () {
            window.setTimeout(pintarPassword, 0);
        });
        pintarPassword();

        // Respaldo: aunque el botón esté activo, no se envía si algo no cumple
        formPassword.addEventListener('submit', function (evento) {
            var pendiente = Object.keys(reglas).filter(function (regla) { return !reglas[regla](); })[0];
            if (!pendiente) return;
            evento.preventDefault();
            (pendiente === 'coincide' ? passRepetir : passNueva).focus();
        });
    }

    document.querySelectorAll('[data-solo-digitos]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.value = campo.value.replace(/\D/g, '').slice(0, 9);
        });
    });

    document.querySelectorAll('[data-rut]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            var limpio = campo.value.replace(/[^0-9kK]/g, '').toUpperCase().slice(0, 9);
            if (limpio.length < 2) {
                campo.value = limpio;
                return;
            }
            var cuerpo = limpio.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            campo.value = cuerpo + '-' + limpio.slice(-1);
        });
    });

    document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!window.confirm(formulario.dataset.confirmar)) evento.preventDefault();
        });
    });

    // Formulario de tarjeta: formato del numero, marca detectada y vista previa.
    var formTarjeta = document.querySelector('#form-tarjeta form');
    if (formTarjeta) {
        var numeroTarjeta = document.getElementById('tarjeta_numero');
        var vencimientoTarjeta = document.getElementById('tarjeta_vencimiento');
        var titularTarjeta = document.getElementById('tarjeta_titular');
        var marcaTarjeta = document.getElementById('tarjeta_marca');
        var vistaTarjeta = document.getElementById('tarjeta_preview');
        var vista = function (clave) { return vistaTarjeta.querySelector('[data-preview="' + clave + '"]'); };
        var marcas = [
            [/^3[47]/, 'American Express', 'american-express'],
            [/^3(0[0-5]|[68])/, 'Diners Club', 'diners-club'],
            [/^4/, 'Visa', 'visa'],
            [/^(5[1-5]|2[2-7])/, 'Mastercard', 'mastercard'],
            [/^(50|5[6-9]|6)/, 'Maestro', 'maestro']
        ];

        function detectarMarca(digitos) {
            for (var i = 0; i < marcas.length; i++) {
                if (marcas[i][0].test(digitos)) return marcas[i];
            }
            return null;
        }

        numeroTarjeta.addEventListener('input', function () {
            var digitos = numeroTarjeta.value.replace(/\D/g, '').slice(0, 19);
            var marca = detectarMarca(digitos);
            var grupos = marca && marca[2] === 'american-express'
                ? [digitos.slice(0, 4), digitos.slice(4, 10), digitos.slice(10, 15)]
                : digitos.match(/.{1,4}/g) || [];
            numeroTarjeta.value = grupos.filter(Boolean).join(' ');
        });

        vencimientoTarjeta.addEventListener('input', function (evento) {
            var digitos = vencimientoTarjeta.value.replace(/\D/g, '').slice(0, 4);
            if (digitos.length === 1 && digitos > '1') digitos = '0' + digitos;
            var borrando = evento.inputType && evento.inputType.indexOf('delete') === 0;
            vencimientoTarjeta.value = digitos.length >= 2 && !(borrando && digitos.length === 2)
                ? digitos.slice(0, 2) + '/' + digitos.slice(2)
                : digitos;
        });

        formTarjeta.addEventListener('input', function () {
            var digitos = numeroTarjeta.value.replace(/\D/g, '');
            var marca = detectarMarca(digitos);
            var tipo = formTarjeta.querySelector('input[name="tipo"]:checked');
            marcaTarjeta.textContent = marca ? marca[1] : '';
            vistaTarjeta.className = 'card-visual' + (marca ? ' marca-' + marca[2] : '');
            vista('marca').textContent = marca ? marca[1] : 'Tarjeta';
            vista('tipo').textContent = tipo && tipo.value === 'debito' ? 'Débito' : 'Crédito';
            vista('numero').textContent = digitos ? '•••• •••• •••• ' + (digitos.slice(-4) + '••••').slice(0, 4) : '•••• •••• •••• ••••';
            vista('titular').textContent = titularTarjeta.value.trim().toUpperCase() || 'NOMBRE APELLIDO';
            vista('vencimiento').textContent = vencimientoTarjeta.value || 'MM/AA';
        });
        formTarjeta.dispatchEvent(new Event('input'));
    }

    document.querySelectorAll('.edit-plan-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            abrirFormulario('form-pedido', false);
            var setValue = function (id, value) {
                var field = document.getElementById(id);
                if (field) field.value = value || '';
            };

            setValue('plan_id', button.dataset.id);
            setValue('plan_mascota_id', button.dataset.mascota);
            setValue('plan_producto_id', button.dataset.producto);
            setValue('plan_voucher_id', button.dataset.voucher);
            setValue('plan_frecuencia', button.dataset.frecuencia);
            setValue('plan_cantidad', button.dataset.cantidad);
            setValue('plan_proxima_entrega', button.dataset.proxima);
            setValue('plan_direccion_entrega', button.dataset.direccion);
            setValue('plan_forma_pago', button.dataset.pago);
            if (planFormaPago) planFormaPago.dispatchEvent(new Event('change'));
        });
    });

    // Mis mascotas: el mismo formulario sirve para agregar y para editar
    var formMascota = document.querySelector('[data-form-mascota]');
    if (formMascota) {
        var tituloMascota = formMascota.querySelector('[data-mascota-titulo]');
        var subtituloMascota = formMascota.querySelector('[data-mascota-subtitulo]');
        var textoGuardarMascota = formMascota.querySelector('[data-mascota-guardar]');
        var zonaMascota = formMascota.querySelector('[data-zona-foto]');

        var modoMascota = function (nombre) {
            tituloMascota.textContent = nombre ? 'Editar a ' + nombre : 'Nueva mascota';
            subtituloMascota.textContent = nombre ? 'Actualiza sus datos y guarda los cambios.' : 'Completa sus datos. Solo el nombre es obligatorio.';
            textoGuardarMascota.textContent = nombre ? 'Guardar cambios' : 'Guardar mascota';
        };

        var marcarOpcion = function (campo, valor) {
            var encontrada = false;
            formMascota.querySelectorAll('input[name="' + campo + '"]').forEach(function (opcion) {
                opcion.checked = opcion.value === valor;
                encontrada = encontrada || opcion.checked;
            });
            return encontrada;
        };

        // Al cerrar o cancelar vuelve a quedar como "Nueva mascota"
        formMascota.addEventListener('reset', function () {
            formMascota.elements.mascota_id.value = '';
            modoMascota('');
        });

        document.querySelectorAll('[data-mascota-editar]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var datos = JSON.parse(boton.dataset.mascotaEditar);
                cerrarFormulario('form-mascota');

                formMascota.elements.mascota_id.value = datos.id;
                formMascota.elements.nombre.value = datos.nombre || '';
                formMascota.elements.raza.value = datos.raza || '';
                formMascota.elements.color.value = datos.color || '';
                formMascota.elements.fecha_nacimiento.value = datos.fecha || '';
                formMascota.elements.alergias.value = datos.alergias || '';
                formMascota.elements.observaciones.value = datos.observaciones || '';
                formMascota.elements.esterilizado.checked = !!datos.esterilizado;
                if (!marcarOpcion('especie', datos.especie)) marcarOpcion('especie', 'otro');
                marcarOpcion('sexo', datos.sexo || '');
                if (zonaMascota) zonaMascota.dispatchEvent(new CustomEvent('zona-foto:actual', { detail: datos.foto || '' }));

                modoMascota(datos.nombre);
                abrirFormulario('form-mascota');
            });
        });

        document.querySelectorAll('[data-mascota-nueva]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                cerrarFormulario('form-mascota');
                abrirFormulario('form-mascota');
            });
        });
    }

    var comunasVet = @json($comunasVet);
    var regionDireccion = document.getElementById('direccion_region');
    var comunaDireccion = document.getElementById('direccion_comuna');

    function cargarComunasDireccion(regionId, comunaSeleccionada) {
        if (!comunaDireccion) return;
        comunaDireccion.innerHTML = '<option value="">Seleccione una comuna</option>';
        (comunasVet || []).filter(function (comuna) {
            return String(comuna.id_region) === String(regionId);
        }).forEach(function (comuna) {
            var option = document.createElement('option');
            option.value = comuna.id;
            option.textContent = comuna.nombre;
            option.selected = String(comuna.id) === String(comunaSeleccionada || '');
            comunaDireccion.appendChild(option);
        });
        comunaDireccion.disabled = !regionId;
    }

    if (regionDireccion) {
        regionDireccion.addEventListener('change', function () {
            cargarComunasDireccion(regionDireccion.value, '');
        });
    }

    // Direcciones: el mismo formulario sirve para agregar y para editar
    var formDireccion = document.querySelector('[data-form-direccion]');
    if (formDireccion) {
        var modoDireccion = function (alias) {
            formDireccion.querySelector('[data-direccion-titulo]').textContent = alias ? 'Editar «' + alias + '»' : 'Nueva dirección';
            formDireccion.querySelector('[data-direccion-subtitulo]').textContent = alias ? 'Actualiza los datos y guarda los cambios.' : 'Indica dónde quieres recibir tus pedidos.';
            formDireccion.querySelector('[data-direccion-guardar]').textContent = alias ? 'Guardar cambios' : 'Guardar dirección';
        };

        // Al cerrar o cancelar vuelve a quedar como "Nueva dirección"
        formDireccion.addEventListener('reset', function () {
            ['direccion_id', 'dia_preferencia', 'horario_preferencia', 'forma_pago_preferida'].forEach(function (nombre) {
                formDireccion.elements[nombre].value = '';
            });
            modoDireccion('');
            window.setTimeout(function () {
                cargarComunasDireccion(regionDireccion.value, '');
            }, 0);
        });

        document.querySelectorAll('[data-direccion-editar]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var datos = JSON.parse(boton.dataset.direccionEditar);
                cerrarFormulario('form-direccion');

                var campos = formDireccion.elements;
                campos.direccion_id.value = datos.id;
                campos.alias.value = datos.alias || '';
                campos.direccion.value = datos.direccion || '';
                campos.referencia.value = datos.referencia || '';
                campos.dia_preferencia.value = datos.dia || '';
                campos.horario_preferencia.value = datos.horario || '';
                campos.forma_pago_preferida.value = datos.pago || '';
                campos.principal.checked = !!datos.principal;
                regionDireccion.value = datos.region_id || '';
                cargarComunasDireccion(regionDireccion.value, datos.comuna_id || '');

                modoDireccion(datos.alias);
                abrirFormulario('form-direccion');
            });
        });

        document.querySelectorAll('[data-direccion-nueva]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                cerrarFormulario('form-direccion');
                abrirFormulario('form-direccion');
            });
        });

        formDireccion.querySelectorAll('[data-alias-sugerido]').forEach(function (sugerencia) {
            sugerencia.addEventListener('click', function () {
                formDireccion.elements.alias.value = sugerencia.dataset.aliasSugerido;
                formDireccion.elements.direccion.focus();
            });
        });
    }
    cargarComunasDireccion(regionDireccion ? regionDireccion.value : '', '');
</script>
@endsection
