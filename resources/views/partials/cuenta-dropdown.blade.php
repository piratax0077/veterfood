{{-- Menu de cuenta del cliente (menu del sitio y de la tienda) --}}
<div class="shop-drop" data-shop-drop>
    <a class="shop-accion" href="{{ route('cliente.panel') }}" aria-haspopup="true" aria-expanded="false" aria-controls="menu-cuenta-cliente" data-shop-drop-trigger><x-icono nombre="usuario" /><span class="shop-accion-texto shop-accion-texto--cuenta"><span class="shop-saludo">Hola, {{ Str::before(auth()->user()->name, ' ') }}</span><span class="shop-accion-sub">Mi cuenta</span></span></a>
    <div class="shop-drop-panel shop-drop-panel--menu" id="menu-cuenta-cliente">
        <a href="{{ route('cliente.panel') }}#resumen"><x-icono nombre="inicio" />Mi cuenta</a>
        <a href="{{ route('cliente.panel') }}#compras"><x-icono nombre="compras" />Mis compras</a>
        <a href="{{ route('vouchers.usuario') }}"><x-icono nombre="cupon" />Vouchers</a>
        <a href="{{ route('encuesta.usuario') }}"><x-icono nombre="encuesta" />Encuesta</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<input type="hidden" name="desde" value="tienda"><button type="submit"><x-icono nombre="salir" />Cerrar sesi&oacute;n</button></form>
        <div class="shop-drop-panel-sdi">
            <a class="shop-drop-sdi" href="{{ config('services.sdi_sso.vet_web_url') }}"><x-icono nombre="inicio" />Ir a escritorio VET-SDI</a>
        </div>
    </div>
</div>
