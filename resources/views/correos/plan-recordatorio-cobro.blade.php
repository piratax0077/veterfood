{{-- Correo 12 · Recordatorio del próximo cobro del plan. Asunto: Tu plan se cobra el 21 de octubre --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $plan ??= 'Plan Alimento Inscrito';
    $dias ??= 3;
    $fecha ??= '21 de octubre de 2026';
    $monto ??= '$4.990';
    $tarjeta ??= '4821';
    $urlPlan ??= route('cliente.panel') . '#mi-plan';
@endphp
<x-correo.plantilla :asunto="'Tu plan se cobra el '.$fecha" :preheader="'Cargaremos '.$monto.' a tu tarjeta terminada en '.$tarjeta.'.'" contexto="Tu plan" :email="$email">
    <x-correo.pastilla tono="naranjo">Próximo cobro</x-correo.pastilla>
    <x-correo.titulo>Tu próximo cobro es en {{ $dias }} días</x-correo.titulo>
    <x-correo.texto>El {{ $fecha }} cargaremos {{ $monto }} a tu tarjeta terminada en {{ $tarjeta }} por el {{ $plan }}. Si quieres cambiar de tarjeta o cancelar el plan, hazlo antes de esa fecha.</x-correo.texto>
    <x-correo.boton :url="$urlPlan">Revisar mi plan</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Plan">{{ $plan }}</x-correo.fila>
        <x-correo.fila etiqueta="Fecha de cobro">{{ $fecha }}</x-correo.fila>
        <x-correo.fila etiqueta="Monto">{{ $monto }}</x-correo.fila>
        <x-correo.fila etiqueta="Medio de pago">Tarjeta terminada en {{ $tarjeta }}</x-correo.fila>
    </x-correo.datos>
</x-correo.plantilla>
