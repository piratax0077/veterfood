{{-- Pasos de la compra. $paso: 1 carro, 3 entrega, 4 pago, 5 compra lista --}}
@php
    $pasos = [
        1 => ['Carro de compras', 'Revisa tus productos'],
        2 => ['Inicio de sesión', auth()->check() ? 'Sesión iniciada' : 'Opcional'],
        3 => ['Entrega', 'Elige dónde recibir tu compra'],
        4 => ['Pago', 'Elige cómo vas a pagar'],
        5 => ['¡Listo!', 'Revisa el detalle de tu compra'],
    ];
@endphp
<nav class="pasos-compra" aria-label="Pasos de la compra">
    <ol data-pasos-compra>
        @foreach($pasos as $numero => [$titulo, $detalle])
            @php
                $hecho = $numero < $paso || ($numero === 2 && auth()->check()) || $paso === 5;
                $volverAlCarro = $numero === 1 && $paso > 1 && $paso < 5;
            @endphp
            <li @class(['is-hecho' => $hecho, 'is-actual' => $numero === $paso]) data-paso="{{ $numero }}" @if($numero === $paso) aria-current="step" @endif>
                @if($volverAlCarro)
                    <a class="pasos-compra-marca" href="{{ route('tienda.carro') }}">
                @else
                    <span class="pasos-compra-marca">
                @endif
                    <span class="pasos-compra-numero">
                        <span class="pasos-compra-cifra">{{ $numero }}</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                    </span>
                    <span class="pasos-compra-titulo">{{ $titulo }}</span>
                    <span class="pasos-compra-detalle">{{ $detalle }}</span>
                @if($volverAlCarro)
                    </a>
                @else
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
