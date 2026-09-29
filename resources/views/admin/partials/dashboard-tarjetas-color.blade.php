{{-- Tarjetas de color del inicio de administrador. Recibe $claves (orden a mostrar) y $tarjetas (catalogo).
     Cada tarjeta usa su llave 'tono' para el color y 'icono' para la imagen de la esquina. --}}
<div class="admin-tarjetas-color">
    @foreach($claves as $clave)
        @php $tarjeta = $tarjetas[$clave]; @endphp
        <a class="tarjeta-color tarjeta-color--{{ $tarjeta['tono'] ?? 'agua' }}" href="{{ $tarjeta['url'] }}">
            <span class="tarjeta-color-circulos" aria-hidden="true"></span>
            <h3 class="tarjeta-color-titulo">{{ $tarjeta['titulo'] }}</h3>
            <p class="tarjeta-color-texto">{{ $tarjeta['texto'] }}</p>
            <span class="tarjeta-color-ir">
                Ir
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M5 12h13M12 5l7 7-7 7" />
                </svg>
            </span>
            <img class="tarjeta-color-icono" src="{{ asset($tarjeta['icono'] ?? 'images/iconos/icono-tarjeta-3d.svg') }}" alt="" aria-hidden="true">
        </a>
    @endforeach
</div>
