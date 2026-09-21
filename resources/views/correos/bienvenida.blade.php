{{-- Correo 1 · Bienvenida y activación de cuenta. Asunto: Activa tu cuenta de VeterFood --}}
@php
    // Datos de ejemplo para la vista previa; al enviar se reemplazan por los reales
    $nombre ??= 'Camila';
    $email ??= 'camila.rojas@correo.cl';
    $urlActivar ??= '#';
    $codigo ??= 'VETERSDI10';
@endphp
<x-correo.plantilla asunto="Activa tu cuenta de VeterFood" preheader="Confirma tu correo y aprovecha 10 % de descuento en tu primera compra." contexto="Tu cuenta" :email="$email">
    <x-correo.pastilla tono="verde">Cuenta creada</x-correo.pastilla>
    <x-correo.titulo>Activa tu cuenta, {{ $nombre }}</x-correo.titulo>
    <x-correo.texto>Creamos tu cuenta en VeterFood con este correo. Confírmalo y ya puedes comprar, seguir tus pedidos y guardar los datos de tu mascota.</x-correo.texto>
    <x-correo.boton :url="$urlActivar">Activar mi cuenta</x-correo.boton>
    <x-correo.aviso tono="verde">Tu primera compra tiene <strong>10 % de descuento</strong>. Usa el código <strong>{{ $codigo }}</strong> al pagar.</x-correo.aviso>
    <x-correo.texto suave>El enlace vale 24 horas. Si no creaste la cuenta, ignora este correo.</x-correo.texto>
</x-correo.plantilla>
