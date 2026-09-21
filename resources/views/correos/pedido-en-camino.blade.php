{{-- Correo 6 · Pedido en camino, con seguimiento. Asunto: Tu pedido VF-20418 va en camino --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $repartidor ??= 'Matías R.';
    $llegada ??= 'hoy, entre 14:00 y 18:00 hrs';
    $direccion ??= 'Los Leones 1234, depto 56, Providencia';
    $urlPedido ??= route('tracking.show', ['codigo' => $pedido]);
@endphp
<x-correo.plantilla :asunto="'Tu pedido '.$pedido.' va en camino'" :preheader="'Llega '.$llegada.'. Revisa el seguimiento aquí.'" :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="naranjo">En camino</x-correo.pastilla>
    <x-correo.titulo>Tu pedido va en camino</x-correo.titulo>
    <x-correo.texto>{{ $repartidor }} salió con tu pedido y llega {{ $llegada }}. Si no vas a estar en casa, avísanos por WhatsApp.</x-correo.texto>
    <x-correo.estado :paso="4" />
    <x-correo.boton :url="$urlPedido">Seguir mi pedido</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Código de seguimiento">{{ $pedido }}</x-correo.fila>
        <x-correo.fila etiqueta="Repartidor">{{ $repartidor }}</x-correo.fila>
        <x-correo.fila etiqueta="Dirección de entrega">{{ $direccion }}</x-correo.fila>
    </x-correo.datos>
</x-correo.plantilla>
