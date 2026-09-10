{{--
    Modal global (ventana sobre la pagina). Uso:
    <button type="button" data-modal-abrir="modal-nuevo-usuario">Nuevo usuario</button>

    <x-modal id="modal-nuevo-usuario" titulo="Nuevo usuario" descripcion="..." :abierto="$errors->any()">
        ...contenido (por ejemplo un <form id="form-x">)...
        <x-slot:pie>
            <button type="button" class="btn btn-secondary" data-modal-cerrar>Cancelar</button>
            <button type="submit" class="encabezado-boton" form="form-x">Guardar</button>
        </x-slot:pie>
    </x-modal>
    Props: id, titulo, descripcion, abierto (se abre al cargar, ej. tras un error de validacion), ancho (mediano | grande).
    Se cierra con la X, con Cancelar (data-modal-cerrar), con Esc o haciendo clic fuera.
    Estilos: public/css/modal.css · Comportamiento: public/js/modal.js
--}}
@props(['id', 'titulo', 'descripcion' => null, 'abierto' => false, 'ancho' => 'mediano'])

<dialog {{ $attributes->class(['modal', 'modal--' . $ancho]) }} id="{{ $id }}" aria-labelledby="{{ $id }}-titulo" @if($abierto) data-modal-abierto @endif>
    <div class="modal-caja">
        <header class="modal-cabecera">
            <div>
                <h2 id="{{ $id }}-titulo">{{ $titulo }}</h2>
                @if($descripcion)<p>{{ $descripcion }}</p>@endif
            </div>
            <button type="button" class="modal-cerrar" data-modal-cerrar aria-label="Cerrar"><x-icono nombre="cerrar" /></button>
        </header>
        <div class="modal-cuerpo">
            {{ $slot }}
        </div>
        @isset($pie)
            <footer class="modal-pie">{{ $pie }}</footer>
        @endisset
    </div>
</dialog>
