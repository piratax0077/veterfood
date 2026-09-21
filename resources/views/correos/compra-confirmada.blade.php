{{-- Correo 4 · Compra confirmada (pedido recibido). Asunto: Recibimos tu pedido VF-20418 --}}
@php
    $nombre ??= 'Camila';
    $email ??= 'camila.rojas@correo.cl';
    $pedido ??= 'VF-20418';
    $entrega ??= 'jueves 24 de septiembre, entre 14:00 y 18:00 hrs';
    $productos ??= [
        ['nombre' => 'Alimento seco para perro adulto, 10 kg', 'cantidad' => 1, 'precio' => '$38.990'],
        ['nombre' => 'Arena sanitaria para gato, 8 kg', 'cantidad' => 1, 'precio' => '$9.490'],
    ];
    $subtotal ??= '$48.480';
    $descuento ??= '-$4.848';
    $despacho ??= '$3.990';
    $total ??= '$47.622';
    $direccion ??= 'Los Leones 1234, depto 56, Providencia';
    $pago ??= 'Tarjeta terminada en 4821';
    $urlPedido ??= route('tracking.show', ['codigo' => $pedido]);
@endphp
<x-correo.plantilla :asunto="'Recibimos tu pedido '.$pedido" :preheader="'Pago confirmado. Te llega el '.$entrega.'.'" :contexto="'Pedido '.$pedido" :email="$email">
    <x-correo.pastilla tono="verde">Pago confirmado</x-correo.pastilla>
    <x-correo.titulo>Recibimos tu pedido</x-correo.titulo>
    <x-correo.texto>Confirmamos tu pago de {{ $total }} y ya estamos armando tu compra, {{ $nombre }}. Te llega el {{ $entrega }}.</x-correo.texto>
    <x-correo.estado :paso="1" />
    <x-correo.boton :url="$urlPedido">Seguir mi pedido</x-correo.boton>
    <x-correo.subtitulo>Resumen</x-correo.subtitulo>
    <x-correo.datos modo="monto">
        @foreach($productos as $producto)
            <x-correo.fila :etiqueta="$producto['nombre']" :detalle="'Cantidad: '.$producto['cantidad']" :fuerte="true">{{ $producto['precio'] }}</x-correo.fila>
        @endforeach
        <x-correo.fila etiqueta="Subtotal">{{ $subtotal }}</x-correo.fila>
        <x-correo.fila etiqueta="Descuento 10 %">{{ $descuento }}</x-correo.fila>
        <x-correo.fila etiqueta="Despacho">{{ $despacho }}</x-correo.fila>
        <x-correo.fila etiqueta="Total" :destacado="true">{{ $total }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.columnas titulo-izquierda="Entrega" titulo-derecha="Pago">
        <x-slot:izquierda>{{ $direccion }}</x-slot:izquierda>
        <x-slot:derecha>{{ $pago }}</x-slot:derecha>
    </x-correo.columnas>
</x-correo.plantilla>
