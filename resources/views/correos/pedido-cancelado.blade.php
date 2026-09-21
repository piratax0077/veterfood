{{-- Correo 8 · Pedido cancelado. Asunto: Cancelamos tu pedido VF-20418 --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $motivo ??= 'Sin stock de uno de los productos';
    $total ??= '$47.622';
    $pago ??= 'Tarjeta terminada en 4821';
    $plazo ??= '5 a 10 días hábiles';
    $urlTienda ??= route('tienda.catalogo');
@endphp
<x-correo.plantilla :asunto="'Cancelamos tu pedido '.$pedido" :preheader="'Te devolvemos '.$total.' en '.$plazo.'.'" :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="rojo">Pedido cancelado</x-correo.pastilla>
    <x-correo.titulo>Cancelamos tu pedido</x-correo.titulo>
    <x-correo.texto>El pedido {{ $pedido }} quedó cancelado. Ya pedimos la devolución de {{ $total }} a tu tarjeta; tu banco la refleja en {{ $plazo }}.</x-correo.texto>
    <x-correo.boton :url="$urlTienda">Volver a la tienda</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Pedido">{{ $pedido }}</x-correo.fila>
        <x-correo.fila etiqueta="Motivo">{{ $motivo }}</x-correo.fila>
        <x-correo.fila etiqueta="Monto devuelto">{{ $total }}</x-correo.fila>
        <x-correo.fila etiqueta="Devolución a">{{ $pago }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.texto suave>¿Crees que fue un error? Escríbenos por WhatsApp y lo revisamos contigo.</x-correo.texto>
</x-correo.plantilla>
