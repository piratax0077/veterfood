{{-- Tarjetas grandes del panel administrador. Recibe $claves (orden a mostrar) y $tarjetas (catalogo).
     Cada tarjeta puede traer 'icono' con la ruta de su imagen; si no, usa la de reserva. --}}
<div class="admin-tarjetas">
    @foreach($claves as $clave)
        @php $tarjeta = $tarjetas[$clave]; @endphp
        <a class="tarjeta-admin" href="{{ $tarjeta['url'] }}">
            <h3 class="tarjeta-admin-titulo">{{ $tarjeta['titulo'] }}</h3>
            <p class="tarjeta-admin-texto">{{ $tarjeta['texto'] }}</p>
            <span class="tarjeta-admin-pie">
                <span class="tarjeta-admin-ir">
                    Ir
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M5 12h13M12 5l7 7-7 7" />
                    </svg>
                </span>
                <img class="tarjeta-admin-icono" src="{{ asset($tarjeta['icono'] ?? 'images/iconos/cruz-veterinaria.svg') }}" alt="" aria-hidden="true">
            </span>
        </a>
    @endforeach
</div>
