{{-- Correo 5 · Pedido en preparación. Asunto: Estamos preparando tu pedido VF-20418 --}}
@php
    $nombre ??= 'Camila';
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $entrega ??= 'Jueves 24 de septiembre, entre 14:00 y 18:00 hrs';
    $urlPedido ??= route('tracking.show', ['codigo' => $pedido]);
@endphp
<x-correo.plantilla :asunto="'Estamos preparando tu pedido '.$pedido" preheader="Ya estamos reuniendo tus productos. Sigue el avance aquí." :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="naranjo">En preparación</x-correo.pastilla>
    <x-correo.titulo>Estamos preparando tu pedido</x-correo.titulo>
    <x-correo.texto>Nuestro equipo está reuniendo y revisando tus productos, {{ $nombre }}. Cuando tenga repartidor asignado te enviamos otro aviso.</x-correo.texto>
    <x-correo.estado :paso="2" />
    <x-correo.boton :url="$urlPedido">Ver estado de mi pedido</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Pedido">{{ $pedido }}</x-correo.fila>
        <x-correo.fila etiqueta="Entrega estimada">{{ $entrega }}</x-correo.fila>
    </x-correo.datos>
</x-correo.plantilla>
