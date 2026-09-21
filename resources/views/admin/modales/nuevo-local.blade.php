{{--
    Modal "Crear lugar de venta" con asistente por pasos. Se abre con cualquier boton data-modal-abrir="modal-nuevo-local".
    Datos: $tiposLocal (View Composer en AppServiceProvider). Se reabre solo si al guardar hubo errores.
--}}
<x-modal
    id="modal-nuevo-local"
    titulo="Crear lugar de venta"
    descripcion="Sucursal, comercio adherido, punto de retiro u otro lugar. Completa los pasos para inscribirlo."
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-local' || ($abrirConNuevo ?? false)">
    <form id="form-nuevo-local" method="POST" action="{{ route('admin.locales.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-local">
        @include('admin.partials.local-campos', ['localEditar' => null, 'conPasos' => true])
    </form>
    <x-slot:pie>
        <span class="wizard-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-secondary" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Anterior</button>
        <button type="button" class="encabezado-boton" data-wizard-siguiente>Siguiente<x-icono nombre="siguiente" /></button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-local" data-wizard-final hidden><x-icono nombre="plus" />Crear lugar de venta</button>
    </x-slot:pie>
</x-modal>
