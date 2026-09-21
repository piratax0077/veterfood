{{--
    Modal "Crear voucher" con asistente por pasos. Se abre con cualquier boton data-modal-abrir="modal-nuevo-voucher"
    (opcional: data-modal-rellenar='{"titulo":"...","valor":15}' para sugerir datos, ej. desde Planes comerciales).
    Datos: $datosVoucher (View Composer en AppServiceProvider). Se reabre solo si al guardar hubo errores.
--}}
<x-modal
    id="modal-nuevo-voucher"
    titulo="Crear voucher con QR seguro"
    descripcion="Completa los pasos para generar el bono. Puedes volver a un paso anterior cuando quieras."
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-voucher' || ($abrirConNuevo ?? false)">
    <form id="form-nuevo-voucher" method="POST" action="{{ route('admin.vouchers.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-voucher">
        @include('admin.partials.voucher-campos')
    </form>
    <x-slot:pie>
        <span class="wizard-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-secondary" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Anterior</button>
        <button type="button" class="encabezado-boton" data-wizard-siguiente>Siguiente<x-icono nombre="siguiente" /></button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-voucher" data-wizard-final hidden><x-icono nombre="plus" />Generar voucher</button>
    </x-slot:pie>
</x-modal>
