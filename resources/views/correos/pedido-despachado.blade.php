{{-- Correo 5 (segundo aviso) · Pedido listo para despacho, con repartidor asignado. Asunto: Tu pedido VF-20418 ya tiene repartidor --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $repartidor ??= 'Matías R.';
    $entrega ??= 'Jueves 24 de septiembre, entre 14:00 y 18:00 hrs';
    $direccion ??= 'Los Leones 1234, depto 56, Providencia';
    $urlPedido ??= route('tracking.show', ['codigo' => $pedido]);
@endphp
<x-correo.plantilla :asunto="'Tu pedido '.$pedido.' ya tiene repartidor'" preheader="Está listo para salir. Te avisamos cuando vaya en camino." :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="naranjo">Listo para despacho</x-correo.pastilla>
    <x-correo.titulo>Tu pedido ya tiene repartidor</x-correo.titulo>
    <x-correo.texto>{{ $repartidor }} llevará tu pedido a tu dirección. Cuando salga a ruta te avisamos de nuevo.</x-correo.texto>
    <x-correo.estado :paso="3" />
    <x-correo.boton :url="$urlPedido">Ver estado de mi pedido</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Repartidor">{{ $repartidor }}</x-correo.fila>
        <x-correo.fila etiqueta="Entrega estimada">{{ $entrega }}</x-correo.fila>
        <x-correo.fila etiqueta="Dirección">{{ $direccion }}</x-correo.fila>
    </x-correo.datos>
</x-correo.plantilla>
