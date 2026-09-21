{{-- Correo 10 · Plan contratado (pago confirmado). Asunto: Contrataste el Plan Alimento Inscrito --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $plan ??= 'Plan Alimento Inscrito';
    $referencia ??= 'PLAN-20260921154212-K7QF';
    $inicial ??= '$14.990';
    $mensual ??= '$4.990';
    $proximoCobro ??= '21 de octubre de 2026';
    $tarjeta ??= '4821';
    $urlPlan ??= route('cliente.panel') . '#mi-plan';
@endphp
<x-correo.plantilla :asunto="'Contrataste el '.$plan" :preheader="'Pago inicial confirmado. Tu próximo cobro es el '.$proximoCobro.'.'" contexto="Tu plan" :email="$email">
    <x-correo.pastilla tono="verde">Plan activo</x-correo.pastilla>
    <x-correo.titulo>Tu plan ya está activo</x-correo.titulo>
    <x-correo.texto>Recibimos el pago inicial de {{ $inicial }} del {{ $plan }}. Desde ahora se cobran {{ $mensual }} cada mes a tu tarjeta terminada en {{ $tarjeta }}.</x-correo.texto>
    <x-correo.boton :url="$urlPlan">Ver mi plan</x-correo.boton>
    <x-correo.datos>
        <x-correo.fila etiqueta="Plan">{{ $plan }}</x-correo.fila>
        <x-correo.fila etiqueta="Referencia">{{ $referencia }}</x-correo.fila>
        <x-correo.fila etiqueta="Pago inicial">{{ $inicial }}</x-correo.fila>
        <x-correo.fila etiqueta="Cargo mensual">{{ $mensual }}</x-correo.fila>
        <x-correo.fila etiqueta="Próximo cobro">{{ $proximoCobro }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.texto suave>Puedes cancelar el plan cuando quieras desde tu cuenta.</x-correo.texto>
</x-correo.plantilla>
