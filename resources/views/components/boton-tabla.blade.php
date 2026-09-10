{{--
    Boton de accion dentro de tablas (Editar, Activar, Inactivar, QR...). Mismo tamano en todo el sistema.
    Uso:
    <div class="tabla-acciones">
        <x-boton-tabla tipo="editar" :href="route('admin.clientes.edit', $cliente)">Editar</x-boton-tabla>
        <form method="POST" action="...">@csrf <x-boton-tabla tipo="inactivar">Inactivar</x-boton-tabla></form>
    </div>
    Con href es un enlace; sin href es un boton que envia el formulario.
    Tipos: editar, activar, aprobar, guardar, inactivar, eliminar, qr, ver, tienda.
    tono (opcional) cambia el color: azul, gris, verde, rojo, naranjo, celeste, oscuro. Estilos: public/css/tablas.css
--}}
@props(['tipo' => 'ver', 'href' => null, 'tono' => null])

@php
    // tipo => [icono, tono]
    $tipos = [
        'editar' => ['editar', 'naranjo'],
        'activar' => ['activar', 'verde'],
        'aprobar' => ['activar', 'verde'],
        'guardar' => ['guardar', 'verde'],
        'inactivar' => ['inactivar', 'rojo'],
        'eliminar' => ['eliminar', 'rojo'],
        'qr' => ['qr', 'celeste'],
        'ver' => ['seguimiento', 'azul'],
        'tienda' => ['tienda', 'azul'],
    ];
    [$icono, $tonoTipo] = $tipos[$tipo] ?? $tipos['ver'];
    $clases = ['tabla-accion', 'tono-' . ($tono ?? $tonoTipo)];
@endphp

@if($href)
    <a {{ $attributes->class($clases) }} href="{{ $href }}"><x-icono :nombre="$icono" />{{ $slot }}</a>
@else
    <button {{ $attributes->class($clases)->merge(['type' => 'submit']) }}><x-icono :nombre="$icono" />{{ $slot }}</button>
@endif
