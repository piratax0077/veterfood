{{-- Correo 14 · Alta de pedido programado. Asunto: Programaste tu pedido de alimento --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $producto ??= 'Alimento seco para perro adulto, 10 kg';
    $cantidad ??= 1;
    $frecuencia ??= 'cada mes';
    $primera ??= 'jueves 24 de septiembre';
    $direccion ??= 'Los Leones 1234, depto 56, Providencia';
    $tarjeta ??= '4821';
    $valor ??= '$38.990';
    $urlPedido ??= route('cliente.panel') . '#pedido';
@endphp
<x-correo.plantilla asunto="Programaste tu pedido de alimento" :preheader="'Primera entrega el '.$primera.'. Después, '.$frecuencia.'.'" contexto="Pedido programado" :email="$email">
    <x-correo.pastilla tono="verde">Pedido activo</x-correo.pastilla>
    <x-correo.titulo>Tu pedido programado está listo</x-correo.titulo>
    <x-correo.texto>Desde ahora te enviamos {{ $producto }} {{ $frecuencia }}. La primera entrega es el {{ $primera }}. Unos días antes te avisamos por si quieres agregar algo más.</x-correo.texto>
    <x-correo.boton :url="$urlPedido">Ver mi pedido programado</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Producto">{{ $producto }} × {{ $cantidad }}</x-correo.fila>
        <x-correo.fila etiqueta="Valor por entrega">{{ $valor }}</x-correo.fila>
        <x-correo.fila etiqueta="Frecuencia">{{ ucfirst($frecuencia) }}</x-correo.fila>
        <x-correo.fila etiqueta="Primera entrega">{{ ucfirst($primera) }}</x-correo.fila>
        <x-correo.fila etiqueta="Dirección">{{ $direccion }}</x-correo.fila>
        <x-correo.fila etiqueta="Medio de pago">Tarjeta terminada en {{ $tarjeta }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.texto suave>Puedes cambiar los datos o cancelarlo cuando quieras desde tu cuenta.</x-correo.texto>
</x-correo.plantilla>
