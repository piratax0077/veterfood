{{-- Menu de cuenta --}}
<div class="shop-account">
    <a href="{{ route('cliente.panel') }}"><x-icono nombre="usuario" class="isdi-izq" />Mi cuenta</a>
    <div class="shop-account-menu">
        <a href="{{ route('cliente.panel') }}"><x-icono nombre="usuario" class="isdi-izq isdi-verde" />Mi escritorio</a>
        <a href="{{ route('cliente.panel') }}#tracking"><x-icono nombre="seguimiento" class="isdi-izq isdi-verde" />Seguir pedido</a>
        <a href="{{ route('encuesta.usuario') }}"><x-icono nombre="encuesta" class="isdi-izq isdi-verde" />Encuesta</a>
        <a href="{{ route('vouchers.usuario') }}"><x-icono nombre="cupon" class="isdi-izq isdi-verde" />Vouchers</a>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit"><x-icono nombre="salir" class="isdi-izq isdi-verde" />Cerrar sesi&oacute;n</button></form>
    </div>
</div>
