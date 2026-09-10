{{-- Menu de cuenta del cliente (menu del sitio y de la tienda) --}}
<div class="shop-drop" data-shop-drop>
    <a class="shop-accion" href="{{ route('cliente.panel') }}" aria-haspopup="true" aria-expanded="false" aria-controls="menu-cuenta-cliente" data-shop-drop-trigger><x-icono nombre="usuario" /><span class="shop-accion-texto">Mi cuenta</span></a>
    <div class="shop-drop-panel shop-drop-panel--menu" id="menu-cuenta-cliente">
        <a href="{{ route('cliente.panel') }}#resumen"><x-icono nombre="inicio" />Mi escritorio</a>
        <a href="{{ route('cliente.panel') }}#compras"><x-icono nombre="compras" />Mis compras</a>
        <a href="{{ route('cliente.panel') }}#tracking"><x-icono nombre="seguimiento" />Mis despachos</a>
        <a href="{{ route('vouchers.usuario') }}"><x-icono nombre="cupon" />Vouchers</a>
        <a href="{{ route('encuesta.usuario') }}"><x-icono nombre="encuesta" />Encuesta</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icono nombre="salir" />Cerrar sesi&oacute;n</button></form>
    </div>
</div>
