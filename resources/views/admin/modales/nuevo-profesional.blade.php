{{--
    Modal "Crear profesional" con asistente por pasos. Se abre con cualquier boton data-modal-abrir="modal-nuevo-profesional".
    Se reabre solo si al guardar hubo errores.
--}}
<x-modal
    id="modal-nuevo-profesional"
    titulo="Crear profesional"
    descripcion="Veterinario, laboratorio o centro autorizado. Completa los pasos para inscribirlo."
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-profesional' || ($abrirConNuevo ?? false)">
    <form id="form-nuevo-profesional" method="POST" enctype="multipart/form-data" action="{{ route('admin.profesionales.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-profesional">
        @include('admin.partials.profesional-campos', ['profesionalEditar' => null, 'conPasos' => true])
    </form>
    <x-slot:pie>
        <span class="wizard-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-secondary" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Anterior</button>
        <button type="button" class="encabezado-boton" data-wizard-siguiente>Siguiente<x-icono nombre="siguiente" /></button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-profesional" data-wizard-final hidden><x-icono nombre="plus" />Crear profesional</button>
    </x-slot:pie>
</x-modal>
