{{-- Correo 8 (segundo caso) · Pago no procesado. Asunto: No pudimos procesar el pago de tu pedido --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $total ??= '$47.622';
    $tarjeta ??= '4821';
    $motivo ??= 'Rechazado por el banco';
    $urlPago ??= route('tienda.checkout');
@endphp
<x-correo.plantilla asunto="No pudimos procesar el pago de tu pedido" preheader="No se hizo ningún cobro. Prueba de nuevo o con otro medio de pago." :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="naranjo">Pago no procesado</x-correo.pastilla>
    <x-correo.titulo>No pudimos cobrar tu pedido</x-correo.titulo>
    <x-correo.texto>Tu banco no autorizó el pago de {{ $total }} con la tarjeta terminada en {{ $tarjeta }}. No se hizo ningún cobro y tu pedido todavía no está confirmado.</x-correo.texto>
    <x-correo.boton :url="$urlPago">Reintentar el pago</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Pedido">{{ $pedido }}</x-correo.fila>
        <x-correo.fila etiqueta="Monto">{{ $total }}</x-correo.fila>
        <x-correo.fila etiqueta="Medio de pago">Tarjeta terminada en {{ $tarjeta }}</x-correo.fila>
        <x-correo.fila etiqueta="Motivo informado">{{ $motivo }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.texto suave>Puedes intentarlo con la misma tarjeta o con otra. Si sigue fallando, escríbenos por WhatsApp y te ayudamos a pagar.</x-correo.texto>
</x-correo.plantilla>
