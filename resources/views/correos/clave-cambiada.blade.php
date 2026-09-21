{{-- Correo 3 · Contraseña cambiada. Asunto: Cambiaste tu contraseña de VeterFood --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $fecha ??= '21 de septiembre de 2026, 15:42 hrs';
    $dispositivo ??= 'Chrome en Windows';
    $ubicacion ??= 'Santiago, Chile';
    $urlProteger ??= '#';
@endphp
<x-correo.plantilla asunto="Cambiaste tu contraseña de VeterFood" preheader="Si no fuiste tú, protege tu cuenta ahora." contexto="Tu cuenta" :email="$email">
    <x-correo.pastilla>Seguridad</x-correo.pastilla>
    <x-correo.titulo>Cambiaste tu contraseña</x-correo.titulo>
    <x-correo.texto>Actualizaste la contraseña de tu cuenta desde tu panel. Si fuiste tú, no tienes que hacer nada más.</x-correo.texto>
    <x-correo.datos>
        <x-correo.fila etiqueta="Fecha">{{ $fecha }}</x-correo.fila>
        <x-correo.fila etiqueta="Dispositivo">{{ $dispositivo }}</x-correo.fila>
        <x-correo.fila etiqueta="Ubicación aproximada">{{ $ubicacion }}</x-correo.fila>
    </x-correo.datos>
    <x-correo.aviso tono="rojo">¿No reconoces este cambio? Restablece tu contraseña ahora. Si no puedes entrar a tu cuenta, escríbenos por WhatsApp.</x-correo.aviso>
    <x-correo.boton :url="$urlProteger">No fui yo, proteger mi cuenta</x-correo.boton>
</x-correo.plantilla>
