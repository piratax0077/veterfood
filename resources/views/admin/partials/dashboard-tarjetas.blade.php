{{-- Grilla de tarjetas de modulos del panel administrador. Recibe $claves (orden a mostrar) y $tarjetas (catalogo). --}}
<div class="admin-menu">
    @foreach($claves as $clave)
        @php $tarjeta = $tarjetas[$clave]; @endphp
        <div class="admin-menu-card">
            <div>
                <h3 class="admin-menu-title icon-{{ $clave }}">{{ $tarjeta['titulo'] }}</h3>
                <p>{{ $tarjeta['texto'] }}</p>
            </div>
            <a class="btn {{ $tarjeta['color'] }}" href="{{ $tarjeta['url'] }}">{{ $tarjeta['boton'] }}</a>
        </div>
    @endforeach
</div>
