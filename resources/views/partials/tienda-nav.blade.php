@php
    $carroTotal = array_sum((array) session('carro_alimentos', []));
    $esCliente = auth()->check() && auth()->user()->tieneRol('cliente', 'dueno_mascota');
    $urlCuenta = $esCliente ? route('cliente.panel') : route('inicio') . '#login';
@endphp

<div class="shop-sticky" data-shop-sticky>
<header class="shop-head">
    <div class="shop-head-inner">
        <a class="shop-logo" href="{{ route('tienda.catalogo') }}">
            <img src="{{ asset('images/logotipo/logo-veterfood.svg') }}" alt="VeterFood Comercializadora Alimentos">
        </a>

        <form class="shop-search" method="GET" action="{{ route('tienda.catalogo') }}" role="search">
            <input type="search" name="buscar" value="{{ request('buscar') }}" placeholder="&iquest;Qu&eacute; est&aacute;s buscando?" aria-label="Buscar en la tienda">
            <button type="submit" aria-label="Buscar">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.6-3.6"/></svg>
            </button>
        </form>

        <div class="shop-actions">
            @if($esCliente)
                <div class="shop-account">
                    <a href="{{ $urlCuenta }}"><x-icono nombre="usuario" class="isdi-izq" />Mi cuenta</a>
                    <div class="shop-account-menu">
                        <a href="{{ route('cliente.panel') }}"><x-icono nombre="usuario" class="isdi-izq isdi-verde" />Mi escritorio</a>
                        <a href="{{ route('encuesta.usuario') }}"><x-icono nombre="encuesta" class="isdi-izq isdi-verde" />Encuesta</a>
                        <a href="{{ route('vouchers.usuario') }}"><x-icono nombre="cupon" class="isdi-izq isdi-verde" />Vouchers</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icono nombre="salir" class="isdi-izq isdi-verde" />Cerrar sesi&oacute;n</button></form>
                    </div>
                </div>
            @else
                <a href="{{ $urlCuenta }}">Ingresar</a>
                @auth
                    <form method="POST" action="{{ route('logout') }}">@csrf<button class="shop-link" type="submit"><x-icono nombre="salir" class="isdi-izq" />Cerrar sesi&oacute;n</button></form>
                @endauth
            @endif
            <a class="shop-cart" href="{{ route('tienda.carro') }}" aria-label="Ver carro de compras">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.5 3.5h2.2l2.3 11.2a1.8 1.8 0 0 0 1.8 1.4h8.4a1.8 1.8 0 0 0 1.8-1.4l1.6-7.3H6"/><circle cx="9.5" cy="20" r="1.5"/><circle cx="17.5" cy="20" r="1.5"/></svg>
                @if($carroTotal > 0)<span class="shop-cart-count">{{ $carroTotal }}</span>@endif
            </a>
        </div>
    </div>
</header>

<nav class="shop-menu" aria-label="Menu de la tienda">
    <div class="shop-menu-inner">
        <button class="shop-menu-toggle" type="button" aria-expanded="false" aria-controls="shop-menu-lista" data-menu-toggle>
            <span class="shop-menu-burger" aria-hidden="true"></span>Men&uacute;
        </button>
        <ul class="shop-menu-main" id="shop-menu-lista">
            <li><a href="{{ route('tienda.catalogo') }}"><x-icono nombre="inicio" class="isdi-izq shop-menu-icon" />Inicio</a></li>
            <li>
                <a href="{{ route('tienda.catalogo') }}" aria-haspopup="true"><x-icono nombre="categoria" class="isdi-izq shop-menu-icon" />Categor&iacute;as
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub">
                    <ul>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'alimento_mascota']) }}">Alimentos</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'medicamento']) }}">Farmacia</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Accesorios y Juguetes</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'hotel']) }}">Hoteles</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'paseo_diario']) }}">Paseos</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'cementerio']) }}">Cementerio</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'servicio']) }}">Servicios</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">&Uacute;tiles</a></li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#" aria-haspopup="true"><x-icono nombre="perro" class="isdi-izq shop-menu-icon" />Perros
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub">
                    <ul>
                        <li><a href="#">Alimentaci&oacute;n</a></li>
                        <li><a href="#">Accesorios y Juguetes</a></li>
                        <li><a href="#">Cuidado</a></li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#" aria-haspopup="true"><x-icono nombre="gato" class="isdi-izq shop-menu-icon" />Gatos
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub">
                    <ul>
                        <li><a href="#">Alimentaci&oacute;n</a></li>
                        <li><a href="#">Accesorios y Juguetes</a></li>
                        <li><a href="#">Cuidado</a></li>
                    </ul>
                </div>
            </li>
            <li>
                <a href="#" aria-haspopup="true"><x-icono nombre="mascota" class="isdi-izq shop-menu-icon" />Ex&oacute;ticos
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub shop-sub--wide">
                    <ul>
                        <li><a href="#">Peces</a></li>
                        <li><a href="#">Conejos</a></li>
                        <li><a href="#">Aves</a></li>
                        <li><a href="#">Caballos</a></li>
                        <li><a href="#">Tortugas</a></li>
                        <li><a href="#">Lagartos</a></li>
                        <li><a href="#">Cuys / Cobayos</a></li>
                        <li><a href="#">H&aacute;mster</a></li>
                        <li><a href="#">Hur&oacute;n</a></li>
                        <li><a href="#">Erizo de tierra</a></li>
                    </ul>
                </div>
            </li>
            <li><a class="{{ request('categoria') === 'medicamento' ? 'is-active' : '' }}" href="{{ route('tienda.catalogo', ['categoria' => 'medicamento']) }}"><x-icono nombre="farmacia" class="isdi-izq shop-menu-icon" />Farmacia</a></li>
            <li>
                <a href="#" aria-haspopup="true"><x-icono nombre="servicios" class="isdi-izq shop-menu-icon" />Servicios
                    <svg class="chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                </a>
                <div class="shop-sub">
                    <ul>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'hotel']) }}">Hoteles</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'paseo_diario']) }}">Paseos</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'cementerio']) }}">Cementerio</a></li>
                        <li><a href="{{ route('tienda.catalogo', ['categoria' => 'servicio']) }}">Servicios a domicilio</a></li>
                    </ul>
                </div>
            </li>
            <li><a class="is-outlet" href="#"><x-icono nombre="oferta" class="isdi-izq shop-menu-icon" />Outlet</a></li>
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

        <footer class="carro-panel-pie" data-carro-pie hidden>
            <div class="carro-totales" data-carro-totales></div>
            <a class="carro-cta" href="{{ route('tienda.carro') }}">Ver carrito</a>
            <button type="button" class="carro-seguir" data-carro-cerrar>Seguir comprando</button>
        </footer>
    </aside>
</div>
