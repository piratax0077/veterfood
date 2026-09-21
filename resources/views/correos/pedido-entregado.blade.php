{{-- Correo 7 · Pedido entregado. Asunto: Entregamos tu pedido VF-20418 --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $fecha ??= 'jueves 24 de septiembre a las 15:42 hrs';
    $direccion ??= 'Los Leones 1234, depto 56, Providencia';
    $urlCompra ??= route('cliente.panel');
    $urlProgramar ??= url('/tienda/pedido-programado');
@endphp
<x-correo.plantilla :asunto="'Entregamos tu pedido '.$pedido" :preheader="'Llegó el '.$fecha.'.'" :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="verde">Entregado</x-correo.pastilla>
    <x-correo.titulo>Tu pedido llegó</x-correo.titulo>
    <x-correo.texto>Entregamos el pedido {{ $pedido }} el {{ $fecha }} en {{ $direccion }}. Si algo llegó dañado o no coincide con lo que pediste, escríbenos por WhatsApp y lo resolvemos.</x-correo.texto>
    <x-correo.estado :paso="5" />
    <x-correo.boton :url="$urlCompra">Ver mi compra</x-correo.boton>
    <x-correo.texto suave>¿Quieres recibirlo de nuevo sin volver a pedirlo? <x-correo.enlace :url="$urlProgramar">Programa tu próximo pedido</x-correo.enlace>.</x-correo.texto>
</x-correo.plantilla>
