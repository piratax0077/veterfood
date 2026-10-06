{{-- Contacto para coordinar un servicio fúnebre ya comprado. Lo abre el botón "Contactar ahora" de Mis compras.
     Estilos: css/cliente-panel.css · Datos de la compra y copiar: js/contactar-servicio.js --}}
@php
    // Datos de contacto que ve el cliente
    $contactoServicio = [
        'telefono' => '+56 9 8488 2443',
        'correo' => 'contacto@veterfood.cl',
        'horario' => 'Lunes a sábado, de 09:00 a 19:00 hrs.',
    ];
    $telefonoEnlace = preg_replace('/[^\d+]/', '', $contactoServicio['telefono']);
@endphp

<x-modal id="modal-contactar-servicio" titulo="Contactar ahora" descripcion="Coordina el servicio con nuestro equipo." ancho="chico" class="modal-contacto">
    <p class="contacto-compra">
        <span>Servicio contratado</span>
        <strong data-contacto-nombre>Servicio fúnebre</strong>
        <small>N° pedido <b data-contacto-pedido></b></small>
    </p>

    <p class="contacto-reservado" data-contacto-reservado hidden><x-icono nombre="activar" />Ya nos comunicamos contigo y tu servicio quedó reservado.</p>

    <ul class="contacto-lista">
        <li class="contacto-fila">
            <span class="contacto-icono"><x-icono nombre="telefono" /></span>
            <span class="contacto-dato">
                <span>Teléfono</span>
                <a href="tel:{{ $telefonoEnlace }}">{{ $contactoServicio['telefono'] }}</a>
            </span>
            <button type="button" class="contacto-copiar" data-contacto-copiar="{{ $contactoServicio['telefono'] }}" data-contacto-aviso="Teléfono copiado" aria-label="Copiar teléfono"><x-icono nombre="copiar" /></button>
        </li>
        <li class="contacto-fila">
            <span class="contacto-icono"><x-icono nombre="correo" /></span>
            <span class="contacto-dato">
                <span>Correo</span>
                <a href="mailto:{{ $contactoServicio['correo'] }}" data-contacto-correo="{{ $contactoServicio['correo'] }}">{{ $contactoServicio['correo'] }}</a>
            </span>
            <button type="button" class="contacto-copiar" data-contacto-copiar="{{ $contactoServicio['correo'] }}" data-contacto-aviso="Correo copiado" aria-label="Copiar correo"><x-icono nombre="copiar" /></button>
        </li>
    </ul>

    <p class="contacto-horario">{{ $contactoServicio['horario'] }}</p>

    <x-slot:pie>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar><x-icono nombre="cerrar" class="isdi-izq" />Cerrar</button>
        <a class="btn btn-success" href="tel:{{ $telefonoEnlace }}"><x-icono nombre="telefono" class="isdi-izq" />Llamar</a>
    </x-slot:pie>
</x-modal>
