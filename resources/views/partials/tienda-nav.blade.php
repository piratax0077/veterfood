@php
    $carroTotal = array_sum((array) session('carro_alimentos', []));
    $esCliente = auth()->check() && auth()->user()->tieneRol('cliente', 'dueno_mascota');

    // Ubicacion de despacho: la elegida en el menu o, si no hay, la direccion principal del cliente.
    $ubicacionDespacho = session('ubicacion_despacho');
    if (!$ubicacionDespacho && $esCliente) {
        $direccionPrincipal = auth()->user()->direcciones()->whereNotNull('comuna_id')->orderByDesc('principal')->first();
        if ($direccionPrincipal) {
            $ubicacionDespacho = [
                'region_id' => $direccionPrincipal->region_id,
                'region' => $direccionPrincipal->region,
                'ciudad_id' => $direccionPrincipal->comuna_id,
                'ciudad' => $direccionPrincipal->comuna,
            ];
        }
    }
    $chevron = '<svg class="shop-chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';
    $chevMenu = '<svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>';

    // Categorías del menú (config/tienda_categorias.php)
    $categoriasTienda = config('tienda_categorias');
    $enlaceCategoria = fn ($seccion, $uno = null, $dos = null) => route('tienda.categoria', array_filter(['seccion' => $seccion, 'grupo' => $uno ? Str::slug($uno) : null, 'categoria' => $dos ? Str::slug($dos) : null]));
    $primerNombre = auth()->check() ? Str::before(auth()->user()->name, ' ') : '';
@endphp

<div class="shop-sticky" data-shop-sticky>
<header class="shop-head">
    <div class="shop-head-inner">
        <a class="shop-logo" href="{{ route('tienda.inicio') }}">
            <img src="{{ asset('images/logotipo/logo-veterfood.svg') }}" alt="VeterFood Comercializadora Alimentos">
        </a>

        <form class="shop-search" method="GET" action="{{ route('tienda.catalogo') }}" role="search">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="" aria-label="Buscar en la tienda" data-escritura-animada="¿Qué estás buscando?">
            <button type="submit" aria-label="Buscar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
            </button>
        </form>

        <div class="shop-actions">
            {{-- Ubicacion de despacho --}}
            <div class="shop-drop" data-shop-drop>
                <button type="button" class="shop-accion" aria-expanded="false" aria-controls="shop-ubicacion-panel" data-shop-drop-trigger title="Ubicación de despacho">
                    <x-icono nombre="locacion" /><span class="shop-accion-texto" data-ubicacion-texto>{{ $ubicacionDespacho['ciudad'] ?? 'Ubicación' }}</span>{!! $chevron !!}
                </button>
                <div class="shop-drop-panel shop-drop-panel--ubicacion" id="shop-ubicacion-panel">
                    <p class="shop-drop-titulo">¿Dónde quieres recibir tu pedido?</p>
                    <p class="shop-drop-texto">Elige tu comuna para ver las opciones de despacho a tu zona.</p>
                    <form class="shop-ubicacion-form" method="POST" action="{{ route('tienda.ubicacion') }}" data-ubicacion-form data-validar data-url-regiones="{{ route('tienda.regiones') }}" data-url-ciudades="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}">
                        @csrf
                        <div>
                            <label class="floating-label-activo-sm" for="ubicacion_region">Región</label>
                            <select class="form-control" id="ubicacion_region" name="region_id" data-ubicacion-region data-seleccion="{{ $ubicacionDespacho['region_id'] ?? '' }}" required>
                                <option value="">Cargando regiones…</option>
                            </select>
                        </div>
                        <div>
                            <label class="floating-label-activo-sm" for="ubicacion_ciudad">Comuna</label>
                            <select class="form-control" id="ubicacion_ciudad" name="ciudad_id" data-ubicacion-ciudad data-seleccion="{{ $ubicacionDespacho['ciudad_id'] ?? '' }}" required disabled>
                                <option value="">Selecciona una región</option>
                            </select>
                        </div>
                        <button type="submit" class="shop-drop-boton">Guardar ubicación</button>
                    </form>
                </div>
            </div>

            <a class="shop-accion shop-accion--compacta" href="{{ route('tienda.seguimiento') }}" title="Seguir pedido"><x-icono nombre="seguimiento" /><span class="shop-accion-texto">Seguir pedido</span></a>

            {{-- Cuenta --}}
            @if($esCliente)
                @include('partials.cuenta-dropdown')
            @elseif(auth()->check())
                <div class="shop-drop" data-shop-drop>
                    <a class="shop-accion" href="{{ route('redirect.role') }}" aria-haspopup="true" aria-expanded="false" aria-controls="shop-cuenta-panel" data-shop-drop-trigger><x-icono nombre="usuario" /><span class="shop-accion-texto shop-accion-texto--cuenta"><span class="shop-saludo">Hola, {{ $primerNombre }}</span><span class="shop-accion-sub">Mi cuenta</span></span></a>
                    <div class="shop-drop-panel shop-drop-panel--menu" id="shop-cuenta-panel">
                        <a href="{{ route('redirect.role') }}"><x-icono nombre="inicio" />Mi cuenta</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<input type="hidden" name="desde" value="tienda"><button type="submit"><x-icono nombre="salir" />Cerrar sesi&oacute;n</button></form>
                        <div class="shop-drop-panel-sdi">
                            <a class="shop-drop-sdi" href="{{ config('services.sdi_sso.vet_web_url') }}"><x-icono nombre="inicio" />Ir a escritorio VET-SDI</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="shop-drop" data-shop-drop>
                    <button type="button" class="shop-accion" aria-expanded="false" aria-controls="shop-cuenta-panel" data-shop-drop-trigger>
                        <x-icono nombre="usuario" /><span class="shop-accion-texto shop-accion-texto--cuenta"><span class="shop-saludo">Bienvenido/a</span><span class="shop-accion-sub">Iniciar Sesión</span></span>
                    </button>
                    <div class="shop-drop-panel" id="shop-cuenta-panel">
                        <a class="shop-drop-boton" href="{{ route('inicio', ['desde' => 'tienda']) }}#login" data-modal-abrir="modal-iniciar-sesion">Iniciar Sesión</a>
                        <hr class="shop-drop-separador">
                        <a class="shop-drop-enlace" href="{{ route('inicio', ['desde' => 'tienda']) }}#inscripcion" data-modal-abrir="modal-crear-cuenta">Registrarme</a>
                    </div>
                </div>
            @endif

            <a class="shop-cart" href="{{ route('tienda.carro') }}" aria-label="Ver carro de compras" data-tooltip="Mi carrito">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 3.5h2.2l2.3 11.2a1.8 1.8 0 0 0 1.8 1.4h8.4a1.8 1.8 0 0 0 1.8-1.4l1.6-7.3H6"/><circle cx="9.5" cy="20" r="1.5"/><circle cx="17.5" cy="20" r="1.5"/></svg>
                @if($carroTotal > 0)<span class="shop-cart-count">{{ $carroTotal }}</span>@endif
            </a>
        </div>
    </div>
</header>

<nav class="shop-menu" aria-label="Menú de la tienda">
    <div class="shop-menu-inner">
        <button class="shop-menu-toggle" type="button" aria-expanded="false" aria-controls="shop-menu-lista" data-menu-toggle>
            <span class="shop-menu-burger" aria-hidden="true"></span>Men&uacute;
        </button>
        <ul class="shop-menu-main" id="shop-menu-lista">
            <li><a @class(['is-active' => request()->routeIs('tienda.inicio')]) href="{{ route('tienda.inicio') }}"><x-icono nombre="inicio" class="isdi-izq shop-menu-icon" />Inicio</a></li>
            <li>
                <a href="{{ route('tienda.catalogo') }}" aria-haspopup="true"><x-icono nombre="categoria" class="isdi-izq shop-menu-icon" />Categor&iacute;as
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub">
                    <ul>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'alimento_mascota']) }}">Alimentos</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Accesorios y Juguetes</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'hotel']) }}">Hoteles</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'paseo_diario']) }}">Paseos</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'cementerio']) }}">Cementerio</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'servicio']) }}">Servicios</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">Accesorios</a></li>
                    </ul>
                </div>
            </li>
            @foreach(['perros', 'gatos'] as $seccionMenu)
                @php $datosMenu = $categoriasTienda[$seccionMenu]; @endphp
                <li>
                    <a href="{{ $enlaceCategoria($seccionMenu) }}" aria-haspopup="true"><x-icono :nombre="$datosMenu['icono']" class="isdi-izq shop-menu-icon" />{{ $datosMenu['titulo'] }}{!! $chevMenu !!}</a>
                    <div class="shop-sub shop-sub--mascota">
                        <ul>
                            {{-- Alimento abre su propia lista al costado --}}
                            <li class="mega-item mega-item--flyout">
                                <a href="{{ $enlaceCategoria($seccionMenu, 'Alimento') }}" aria-haspopup="true">Alimento{!! $chevMenu !!}</a>
                                <div class="mega-flyout">
                                    <ul>
                                        <li><a href="{{ $enlaceCategoria($seccionMenu, 'Alimento seco') }}">Alimento seco</a></li>
                                        <li><a href="{{ $enlaceCategoria($seccionMenu, 'Alimento húmedo') }}">Alimento húmedo</a></li>
                                    </ul>
                                </div>
                            </li>
                            @foreach($datosMenu['categorias'] as $categoriaMenu => $infoMenu)
                                @continue(!empty($infoMenu['hijas']) || isset($infoMenu['padre']))
                                <li><a href="{{ $enlaceCategoria($seccionMenu, $categoriaMenu) }}">{{ $categoriaMenu }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @endforeach
            <li class="tiene-mega">
                <a href="{{ $enlaceCategoria('exoticos') }}" aria-haspopup="true"><x-icono nombre="mascota" class="isdi-izq shop-menu-icon" />Ex&oacute;ticos{!! $chevMenu !!}</a>
                <div class="shop-sub shop-sub--exoticos">
                    <ul class="mega-grupos">
                        @foreach($categoriasTienda['exoticos']['grupos'] as $grupoMenu => $itemsMenu)
                            <li @class(['mega-grupo', 'is-activo' => $loop->first]) data-mega-grupo>
                                <button type="button" class="mega-grupo-boton" aria-expanded="false">{{ $grupoMenu }}{!! $chevMenu !!}</button>
                                <div class="mega-panel">
                                    <div class="mega-panel-cabecera">
                                        <p class="mega-panel-titulo">{{ $grupoMenu }}</p>
                                        <a class="mega-panel-todo" href="{{ $enlaceCategoria('exoticos', $grupoMenu) }}">Ver todo</a>
                                    </div>
                                    <ul class="mega-panel-lista">
                                        @foreach($itemsMenu as $itemMenu)
                                            <li><a href="{{ $enlaceCategoria('exoticos', $grupoMenu, $itemMenu) }}">{{ $itemMenu }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </li>
            <li>
                <a href="{{ $enlaceCategoria('servicios') }}" aria-haspopup="true"><x-icono nombre="servicios" class="isdi-izq shop-menu-icon" />Servicios{!! $chevMenu !!}</a>
                <div class="shop-sub">
                    <ul>
                        @foreach($categoriasTienda['servicios']['categorias'] as $servicioMenu => $infoMenu)
                            <li><a href="{{ $enlaceCategoria('servicios', $servicioMenu) }}">{{ $servicioMenu }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </li>
            <li><a @class(['is-outlet', 'is-active' => request()->routeIs('tienda.outlet')]) href="{{ route('tienda.outlet') }}"><x-icono nombre="oferta" class="isdi-izq shop-menu-icon" />Ofertas</a></li>
            {{-- Farmacia tendra su propio sitio: enlace pendiente --}}
            <li><a href="#"><x-icono nombre="farmacia" class="isdi-izq shop-menu-icon" />Farmacia</a></li>
        </ul>
        <ul class="shop-menu-side">
            <li><a href="{{ route('vouchers.usuario') }}"><x-icono nombre="cupon" class="isdi-izq shop-menu-icon" />Mis vouchers</a></li>
            <li><a href="{{ route('encuesta.usuario') }}"><x-icono nombre="encuesta" class="isdi-izq shop-menu-icon" />Encuestas</a></li>
        </ul>
    </div>
</nav>
</div>

{{-- Panel lateral del carro --}}
<div class="carro-panel" id="carro-panel" role="dialog" aria-modal="true" aria-labelledby="carro-panel-titulo" aria-hidden="true">
    <div class="carro-panel-fondo" data-carro-cerrar></div>
    <aside class="carro-panel-caja">
        <header class="carro-panel-head">
            <h2 id="carro-panel-titulo">Mi carro</h2>
            <button type="button" class="carro-panel-cerrar" data-carro-cerrar aria-label="Cerrar el resumen del carro">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </header>

        <div class="carro-panel-cuerpo" data-carro-cuerpo></div>

        {{-- Plantilla de cada producto del panel (la completa public/js/tienda-carro.js) --}}
        <template data-carro-plantilla>
            <li class="carro-item" data-carro-linea>
                <span class="carro-item-foto"><img alt="" loading="lazy"><x-icono nombre="mascota" /></span>
                <div class="carro-item-datos">
                    <p class="carro-item-nombre" data-campo="nombre"></p>
                    <p class="carro-item-marca" data-campo="detalle"></p>
                    <p class="carro-item-detalle" data-campo="precio"></p>
                    <div class="qty qty--sm" data-carro-qty>
                        <button class="qty-btn" type="button" data-qty-paso="-1" aria-label="Quitar una unidad">&minus;</button>
                        <input class="qty-campo" type="number" min="1" aria-label="Cantidad" data-qty-campo>
                        <button class="qty-btn" type="button" data-qty-paso="1" aria-label="Agregar una unidad">+</button>
                    </div>
                </div>
                <div class="carro-item-lado">
                    <button type="button" class="carro-item-quitar" data-carro-quitar aria-label="Eliminar del carro"><x-icono nombre="eliminar" /></button>
                    <span class="carro-item-total" data-carro-linea-total></span>
                </div>
            </li>
        </template>

        <footer class="carro-panel-pie" data-carro-pie hidden>
            <div class="carro-totales" data-carro-totales></div>
            <a class="carro-cta" href="{{ route('tienda.checkout') }}">Ir a pagar</a>
            <a class="carro-seguir" href="{{ route('tienda.carro') }}">Ver carro completo</a>
        </footer>
    </aside>
</div>
