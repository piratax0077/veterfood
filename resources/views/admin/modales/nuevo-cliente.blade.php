{{--
    Modal "Crear cliente" con asistente por pasos. Se abre con cualquier boton data-modal-abrir="modal-nuevo-cliente".
    Se reabre solo si al guardar hubo errores.
--}}
<x-modal
    id="modal-nuevo-cliente"
    titulo="Crear cliente"
    descripcion="Cliente de reparto mensual o VIP. Completa los pasos para inscribirlo."
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-cliente' || ($abrirConNuevo ?? false)">
    <form id="form-nuevo-cliente" method="POST" action="{{ route('admin.clientes.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-cliente">
        @include('admin.partials.cliente-campos', ['clienteEditar' => null, 'conPasos' => true])
    </form>
    <x-slot:pie>
        <span class="wizard-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-secondary" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Anterior</button>
        <button type="button" class="encabezado-boton" data-wizard-siguiente>Siguiente<x-icono nombre="siguiente" /></button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-cliente" data-wizard-final hidden><x-icono nombre="plus" />Crear cliente</button>
    </x-slot:pie>
</x-modal>
