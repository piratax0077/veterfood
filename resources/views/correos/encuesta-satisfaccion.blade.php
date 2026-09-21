{{-- Correo 13 · Encuesta de satisfacción (después de una compra o de contratar un plan). Asunto: ¿Cómo te fue con tu pedido VF-20418? --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $sobre ??= 'tu pedido VF-20418';
    $urlEncuesta ??= '#';
@endphp
<x-correo.plantilla :asunto="'¿Cómo te fue con '.$sobre.'?'" preheader="Una sola pregunta, menos de 30 segundos." contexto="Tu opinión" :email="$email">
    <x-correo.pastilla>Encuesta breve</x-correo.pastilla>
    <x-correo.titulo>¿Cómo fue tu experiencia?</x-correo.titulo>
    <x-correo.texto>Califica {{ $sobre }} del 1 al 5. Con tu respuesta mejoramos el despacho y los productos que ofrecemos.</x-correo.texto>
    <x-correo.calificacion :url="$urlEncuesta" />
    <x-correo.texto suave>¿Prefieres contarnos más? <x-correo.enlace :url="$urlEncuesta">Responde la encuesta completa</x-correo.enlace>.</x-correo.texto>
</x-correo.plantilla>
