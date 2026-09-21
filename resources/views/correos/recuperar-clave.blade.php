{{-- Correo 2 · Recuperar contraseña. Asunto: Restablece tu contraseña de VeterFood --}}
@php
    $email ??= 'camila.rojas@correo.cl';
    $urlClave ??= '#';
    $minutos ??= 60;
@endphp
<x-correo.plantilla asunto="Restablece tu contraseña de VeterFood" :preheader="'El enlace vence en '.$minutos.' minutos.'" contexto="Tu cuenta" :email="$email">
    <x-correo.pastilla>Seguridad</x-correo.pastilla>
    <x-correo.titulo>Crea una contraseña nueva</x-correo.titulo>
    <x-correo.texto>Recibimos una solicitud para cambiar la contraseña de {{ $email }}. Elige una nueva con el botón; el enlace vence en {{ $minutos }} minutos.</x-correo.texto>
    <x-correo.boton :url="$urlClave">Crear contraseña nueva</x-correo.boton>
    <x-correo.texto suave>Si no lo pediste, ignora este correo: tu contraseña actual sigue funcionando.</x-correo.texto>
    <x-correo.texto suave>¿El botón no abre? Copia este enlace en tu navegador:<br><span style="word-break:break-all;">{{ $urlClave }}</span></x-correo.texto>
</x-correo.plantilla>
