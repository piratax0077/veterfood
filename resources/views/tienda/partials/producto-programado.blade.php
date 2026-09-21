{{--
    Pedido programado de un producto: una sola regla para la tarjeta del catálogo y la ficha.
    Recibe $producto y $modo: 'sello' (taco de la tarjeta) o 'aviso' (recuadro de la ficha).
    Solo alimentos para perro o gato, y arena para gato, y no en todos (algunos productos no se adhieren).
--}}
@php
    $textoProducto = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($producto->nombre . ' ' . $producto->descripcion));
    $esPerro = in_array($producto->subcategoria, ['Perros', 'Cachorros']) || \Illuminate\Support\Str::contains($textoProducto, ['perro', 'perros', 'canino', 'cachorro']);
    $esGato = $producto->subcategoria === 'Gatos' || \Illuminate\Support\Str::contains($textoProducto, ['gato', 'gatos', 'felino', 'gatito']);
    $esArena = \Illuminate\Support\Str::contains($textoProducto, ['arena']);
    $elegibleProgramado = ($producto->categoria === 'alimento_mascota' && ($esPerro || $esGato)) || ($esArena && $esGato);
    $conPedidoProgramado = $elegibleProgramado && $producto->id % 2 === 0;
@endphp
@if($modo === 'sello')
    {{-- Sin taco se deja el espacio para que los nombres queden alineados --}}
    @if($conPedidoProgramado)
        <img class="producto-sello-programado" src="{{ asset('images/inicio/programado.svg') }}" alt="Pedido programado">
    @else
        <span class="producto-sello-vacio" aria-hidden="true"></span>
    @endif
@elseif($conPedidoProgramado)
    <div class="ficha-plan">
        <div>
            <img class="ficha-plan-sello" src="{{ asset('images/inicio/programado.svg') }}" alt="Pedido programado">
            <strong>¿Lo compras todos los meses?</strong>
            <span>Prográmalo con una suscripción de alimento y recíbelo en tu casa sin preocuparte.</span>
        </div>
        <a href="{{ route('tienda.pedido-programado') }}">Más información</a>
    </div>
@endif
