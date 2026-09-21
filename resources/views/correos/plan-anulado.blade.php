{{-- Correo 11 · Plan anulado. Asunto: Cancelaste tu Plan Alimento Inscrito --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $plan ??= 'Plan Alimento Inscrito';
    $fecha ??= '2 de octubre de 2026';
    $ultimoCobro ??= '21 de septiembre de 2026';
    $montoUltimo ??= '$4.990';
    $tarjeta ??= '4821';
    $urlPlanes ??= route('cliente.panel') . '#mi-plan';
@endphp
<x-correo.plantilla :asunto="'Cancelaste tu '.$plan" preheader="No habrá nuevos cobros a partir de hoy." contexto="Tu plan" :email="$email">
    <x-correo.pastilla>Plan cancelado</x-correo.pastilla>
    <x-correo.titulo>Tu plan quedó cancelado</x-correo.titulo>
    <x-correo.texto>Cancelamos el {{ $plan }} a partir de hoy. No habrá nuevos cobros en tu tarjeta terminada en {{ $tarjeta }}.</x-correo.texto>
    <x-correo.datos>
        <x-correo.fila etiqueta="Plan">{{ $plan }}</x-correo.fila>
        <x-correo.fila etiqueta="Cancelado el">{{ $fecha }}</x-correo.fila>
        <x-correo.fila etiqueta="Último cobro">{{ $ultimoCobro }}</x-correo.fila>
        <x-correo.fila etiqueta="Monto">{{ $montoUltimo }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.boton :url="$urlPlanes" variante="secundario">Ver planes disponibles</x-correo.boton>
    <x-correo.texto suave>¿Lo cancelaste por error? Puedes contratarlo de nuevo cuando quieras.</x-correo.texto>
</x-correo.plantilla>
