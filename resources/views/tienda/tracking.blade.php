@extends('layouts.app')

@section('title', 'Pedido #' . $pedido->codigo_tracking)
@section('estilos', 'css/tienda-tracking.css')

@section('content')
@php
    $pesos = fn ($monto) => '$' . number_format($monto, 0, ',', '.');
    $cancelado = $pedido->estado === 'cancelado';

    // Pasos del despacho y a que paso corresponde cada estado del pedido.
    $pasos = [
        ['titulo' => 'Pedido recibido', 'icono' => 'compras', 'estados' => ['recibido']],
        ['titulo' => 'En preparación', 'icono' => 'caja', 'estados' => ['en_preparacion', 'preparando']],
        ['titulo' => 'Listo para despacho', 'icono' => 'tienda', 'estados' => ['listo_despacho', 'reparto_asignado', 'asignado']],
        ['titulo' => 'En camino', 'icono' => 'seguimiento', 'estados' => ['en_camino', 'en_ruta']],
        ['titulo' => 'Entregado', 'icono' => 'inicio', 'estados' => ['entregado']],
    ];
    $etapa = collect($pasos)->search(fn ($paso) => in_array($pedido->estado, $paso['estados'], true));
    $etapa = $etapa === false ? 0 : $etapa;

    $estados = [
        'recibido' => ['Pedido recibido', 'Recibimos tu pedido y el pago está confirmado.'],
        'en_preparacion' => ['En preparación', 'Estamos preparando tus productos.'],
        'preparando' => ['En preparación', 'Estamos preparando tus productos.'],
        'listo_despacho' => ['Listo para despacho', 'Tu pedido está listo para salir.'],
        'reparto_asignado' => ['Repartidor asignado', 'Un repartidor ya tiene tu pedido asignado.'],
        'asignado' => ['Repartidor asignado', 'Un repartidor ya tiene tu pedido asignado.'],
        'en_camino' => ['En camino', '¡Tu pedido va en camino!'],
        'en_ruta' => ['En camino', '¡Tu pedido va en camino!'],
        'entregado' => ['Entregado', 'Tu pedido fue entregado.'],
        'cancelado' => ['Cancelado', 'Este pedido fue cancelado.'],
    ];
    [$estadoTexto, $estadoMensaje] = $estados[$pedido->estado] ?? [ucfirst(str_replace('_', ' ', $pedido->estado)), 'Revisa el historial para ver el detalle.'];
    $tonoEstado = $cancelado ? 'tono-rojo' : ($pedido->estado === 'entregado' ? 'tono-verde' : 'tono-naranjo');

    // Fecha y hora en que se alcanzo cada paso (segun el historial).
    $fechaPaso = fn ($paso) => $pedido->tracking->first(fn ($evento) => in_array($evento->estado, $paso['estados'], true))?->created_at;

    // Datos guardados en las notas del pedido (modalidad y horario elegidos al comprar).
    preg_match('/Horario preferido:\s*(.+)/', (string) $pedido->notas_entrega, $horario);
    $esRetiro = $pedido->direccion_entrega === 'Retiro en tienda';
    $metodosPago = [
        'simulado_local' => 'Pago en línea', 'transferencia' => 'Transferencia', 'efectivo_entrega' => 'Efectivo contra entrega',
        'tarjeta_guardada' => 'Tarjeta guardada', 'tarjeta_debito' => 'Tarjeta de débito', 'tarjeta_credito' => 'Tarjeta de crédito',
    ];
    $medioPago = data_get($pedido->pago?->detalle, 'tarjeta.descripcion') ?: ($metodosPago[$pedido->pago?->metodo] ?? null);
    $unidades = $pedido->items->sum('cantidad');
    $fecha = fn ($valor, $formato) => $valor?->locale('es')->translatedFormat($formato);

    // Recién comprado: llega aquí desde el pago con el aviso de éxito
    $recienComprado = session()->has('ok');
    $primerNombre = strtok((string) $pedido->cliente_nombre, ' ');
@endphp

<div class="seguimiento-vista">
    @if($recienComprado)
        @include('tienda.partials.pasos-compra', ['paso' => 5])

        <section class="panel-card seguimiento-gracias">
            <span class="seguimiento-gracias-icono" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
            </span>
            <div>
                <h2>¡Gracias por tu compra{{ $primerNombre ? ', ' . $primerNombre : '' }}!</h2>
                <p>
                    Tu número de pedido es <strong>#{{ $pedido->codigo_tracking }}</strong>.
                    @if($pedido->cliente_email)
                        Con él puedes seguirlo cuando quieras y también te lo enviamos a <strong>{{ $pedido->cliente_email }}</strong>.
                    @else
                        Con él puedes seguirlo cuando quieras.
                    @endif
                </p>
            </div>
            <a class="btn btn-success" href="{{ route('tienda.catalogo') }}">Seguir comprando</a>
        </section>
    @else
        <a class="seguimiento-volver" href="{{ route('tienda.seguimiento') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Seguir otro pedido
        </a>
    @endif

    <header class="seguimiento-cabecera">
        <div>
            <p class="seguimiento-antetitulo">Seguimiento de pedido</p>
            <h1>Pedido <span class="seguimiento-numero">#{{ $pedido->codigo_tracking }}</span></h1>
            <p class="muted">Realizado el {{ $fecha($pedido->created_at, 'j \d\e F \d\e Y') }} a las {{ $pedido->created_at->format('H:i') }} · {{ $unidades }} {{ $unidades === 1 ? 'producto' : 'productos' }}</p>
        </div>
        <span class="badge {{ $tonoEstado }} seguimiento-estado">{{ $estadoTexto }}</span>
    </header>

    <section class="panel-card seguimiento-progreso" aria-label="Estado del despacho">
        <div class="seguimiento-ahora {{ $cancelado ? 'is-cancelado' : '' }}">
            <span class="seguimiento-ahora-icono" aria-hidden="true"><x-icono :nombre="$cancelado ? 'eliminar' : $pasos[$etapa]['icono']" /></span>
            <div>
                <strong>{{ $estadoMensaje }}</strong>
                @unless($cancelado)
                    <span>
                        @if($pedido->estado === 'entregado')
                            Entregado{{ $pedido->entregado_at ? ' el ' . $fecha($pedido->entregado_at, 'l j \d\e F \a \l\a\s H:i') : '' }}.
                        @elseif($pedido->fecha_entrega)
                            {{ $esRetiro ? 'Retiro estimado' : 'Entrega estimada' }}: <b>{{ $fecha($pedido->fecha_entrega, 'l j \d\e F') }}</b>{{ !empty($horario[1]) ? ', entre ' . str_replace(' - ', ' y ', trim($horario[1])) . ' hrs.' : '.' }}
                        @endif
                    </span>
                @endunless
            </div>
        </div>

        @unless($cancelado)
            <ol class="seguimiento-pasos" style="--avance:{{ round($etapa / (count($pasos) - 1), 3) }}">
                @foreach($pasos as $indice => $paso)
                    @php $fechaAlcanzada = $indice <= $etapa ? $fechaPaso($paso) : null; @endphp
                    <li @class(['paso', 'is-hecho' => $indice < $etapa, 'is-actual' => $indice === $etapa])>
                        <span class="paso-icono" aria-hidden="true">
                            @if($indice < $etapa)
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                            @else
                                <x-icono :nombre="$paso['icono']" />
                            @endif
                        </span>
                        <span class="paso-texto">
                            <span class="paso-titulo">{{ $paso['titulo'] }}</span>
                            <span class="paso-fecha">{{ $fechaAlcanzada ? $fecha($fechaAlcanzada, 'j M · H:i') : ($indice === $etapa ? 'Ahora' : 'Pendiente') }}</span>
                        </span>
                        <span class="visually-hidden">{{ $indice < $etapa ? '(completado)' : ($indice === $etapa ? '(etapa actual)' : '(pendiente)') }}</span>
                    </li>
                @endforeach
            </ol>
        @endunless
    </section>

    <div class="seguimiento-grid">
        <section class="panel-card seguimiento-detalle" aria-labelledby="titulo-detalle">
            <h2 id="titulo-detalle">Detalle del pedido</h2>
            <ul class="seguimiento-productos">
                @foreach($pedido->items as $item)
                    <li>
                        <span class="seguimiento-foto">
                            @if($item->producto?->foto_url)
                                <img src="{{ asset($item->producto->foto_url) }}" alt="{{ $item->producto_nombre }}" loading="lazy">
                            @else
                                <x-icono nombre="mascota" />
                            @endif
                        </span>
                        <span class="seguimiento-producto-info">
                            <strong>{{ $item->producto_nombre }}</strong>
                            <span>{{ $item->producto_marca ? $item->producto_marca . ' · ' : '' }}{{ $item->cantidad }} × {{ $pesos($item->precio_unitario) }}</span>
                        </span>
                        <span class="seguimiento-producto-total">{{ $pesos($item->total) }}</span>
                    </li>
                @endforeach
            </ul>

            <dl class="seguimiento-totales">
                <div><dt>Subtotal</dt><dd>{{ $pesos($pedido->subtotal) }}</dd></div>
                <div><dt>Envío</dt><dd class="{{ $pedido->costo_envio ? '' : 'es-gratis' }}">{{ $pedido->costo_envio ? $pesos($pedido->costo_envio) : 'Gratis' }}</dd></div>
                @if(($pedido->descuento_total ?? 0) > 0)
                    <div><dt>Descuento{{ $pedido->voucher?->codigo ? ' (' . $pedido->voucher->codigo . ')' : '' }}</dt><dd class="es-descuento">−{{ $pesos($pedido->descuento_total) }}</dd></div>
                @endif
                <div class="seguimiento-total"><dt>Total</dt><dd>{{ $pesos($pedido->total) }}</dd></div>
            </dl>

            <p class="seguimiento-pago">
                <x-icono nombre="tarjeta" />
                <span>
                    <strong>{{ $pedido->estado_pago === 'pagado' ? 'Pago confirmado' : 'Pago ' . str_replace('_', ' ', $pedido->estado_pago) }}</strong>
                    {{ $medioPago ? '· ' . $medioPago : '' }}{{ $pedido->pagado_at ? ' · ' . $pedido->pagado_at->format('d-m-Y H:i') : '' }}
                </span>
            </p>
        </section>

        @php
            $ubicacionGps = $pedido->tracking->whereNotNull('latitud')->whereNotNull('longitud')->sortByDesc('created_at')->first();
            $direccionMapa = $esRetiro ? null : collect([$pedido->direccion_entrega, $pedido->ciudad_nombre, $pedido->region_nombre, 'Chile'])->filter()->implode(', ');
            $consultaMapa = $ubicacionGps ? $ubicacionGps->latitud . ',' . $ubicacionGps->longitud : $direccionMapa;
        @endphp
        <section class="panel-card seguimiento-mapa-card" aria-labelledby="titulo-mapa">
            <div class="seguimiento-mapa-cabecera">
                <h2 id="titulo-mapa">Mapa del pedido</h2>
                @if($ubicacionGps)
                    <span class="seguimiento-mapa-estado is-vivo">Ubicación del repartidor · {{ $fecha($ubicacionGps->created_at, 'j M, H:i') }}</span>
                @elseif(!$esRetiro)
                    <span class="seguimiento-mapa-estado">Dirección de entrega</span>
                @endif
            </div>
            <div class="seguimiento-mapa-marco">
                @if($consultaMapa && !$cancelado)
                    <iframe src="https://maps.google.com/maps?q={{ urlencode($consultaMapa) }}&z={{ $ubicacionGps ? 15 : 14 }}&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa del pedido {{ $pedido->codigo_tracking }}"></iframe>
                @else
                    <span class="seguimiento-mapa-vacio">
                        <x-icono :nombre="$esRetiro ? 'tienda' : 'locacion'" />
                        <strong>{{ $cancelado ? 'Pedido cancelado' : 'Retiro en tienda' }}</strong>
                        <span>{{ $cancelado ? 'Este pedido no tiene recorrido.' : 'Este pedido no tiene recorrido: te avisaremos cuando esté listo para retirar.' }}</span>
                    </span>
                @endif
            </div>
            @if(!$ubicacionGps && !$esRetiro && !$cancelado)
                <p class="seguimiento-mapa-nota">Cuando el repartidor salga con tu pedido verás aquí su ubicación.</p>
            @endif
        </section>

        <aside class="seguimiento-lateral">
            <section class="panel-card seguimiento-despacho" aria-labelledby="titulo-despacho">
                <h2 id="titulo-despacho">{{ $esRetiro ? 'Retiro' : 'Despacho' }}</h2>
                <dl class="seguimiento-datos">
                    <div>
                        <dt><x-icono nombre="locacion" />{{ $esRetiro ? 'Lugar' : 'Dirección' }}</dt>
                        <dd>{{ $esRetiro ? 'Retiro en tienda' : $pedido->direccion_entrega }}@if(!$esRetiro && ($pedido->ciudad_nombre || $pedido->region_nombre))<br><span class="muted">{{ collect([$pedido->ciudad_nombre, $pedido->region_nombre])->filter()->implode(', ') }}</span>@endif</dd>
                    </div>
                    <div>
                        <dt><x-icono nombre="usuario" />Recibe</dt>
                        <dd>{{ $pedido->cliente_nombre }}</dd>
                    </div>
                    <div>
                        <dt><x-icono nombre="seguimiento" />Repartidor</dt>
                        <dd>
                            @if($pedido->repartidor)
                                {{ $pedido->repartidor->name }}
                                @if($pedido->repartidor->vehiculo_patente)<br><span class="muted">{{ trim(($pedido->repartidor->vehiculo_marca ?? '') . ' ' . ($pedido->repartidor->vehiculo_modelo ?? '')) }} · Patente {{ $pedido->repartidor->vehiculo_patente }}</span>@endif
                            @else
                                <span class="muted">Se asignará cuando el pedido esté listo.</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            <section class="panel-card seguimiento-historial" aria-labelledby="titulo-historial">
                <h2 id="titulo-historial">Historial</h2>
                <ol class="seguimiento-linea">
                    @forelse($pedido->tracking->sortByDesc('created_at') as $evento)
                        <li @class(['is-ultimo' => $loop->first])>
                            <strong>{{ $estados[$evento->estado][0] ?? ucfirst(str_replace('_', ' ', $evento->estado)) }}</strong>
                            <time datetime="{{ $evento->created_at->toIso8601String() }}">{{ $fecha($evento->created_at, 'j \d\e F, H:i') }}</time>
                            @if($evento->mensaje)<p>{{ $evento->mensaje }}</p>@endif
                            @if($evento->latitud && $evento->longitud)
                                <a class="seguimiento-mapa" target="_blank" rel="noopener" href="https://maps.google.com/?q={{ $evento->latitud }},{{ $evento->longitud }}"><x-icono nombre="locacion" />Ver ubicación en el mapa</a>
                            @endif
                            @if($evento->foto_entrega)
                                <img class="seguimiento-foto-entrega" src="{{ asset('storage/' . $evento->foto_entrega) }}" alt="Foto de la entrega">
                            @endif
                        </li>
                    @empty
                        <li class="is-ultimo"><strong>Sin movimientos aún</strong><p>Aquí verás cada actualización de tu pedido.</p></li>
                    @endforelse
                </ol>
            </section>
        </aside>
    </div>
</div>
@endsection
