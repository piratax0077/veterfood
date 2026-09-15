{{--
    Modal "Crear vendedor" / "Crear repartidor" con asistente por pasos.
    Recibe $rol ('vendedor' | 'repartidor'). Se abre con data-modal-abrir="modal-nuevo-{rol}".
    Datos: $localesAsignables (View Composer en AppServiceProvider). Se reabre solo si al guardar hubo errores.
--}}
@php
    $configRol = \App\Support\FormulariosAdmin::rolOperativo($rol);
    $singular = strtolower($configRol['singular']);
@endphp
<x-modal
    id="modal-nuevo-{{ $rol }}"
    :titulo="'Crear ' . $singular"
    :descripcion="$rol === 'repartidor' ? 'Datos de acceso, contacto, local asignado y vehículo del repartidor.' : 'Datos de acceso, contacto y local del vendedor autorizado para emitir vouchers.'"
    ancho="grande"
    :abierto="old('_modal') === 'nuevo-' . $rol || ($abrirConNuevo ?? false)">
    <form id="form-nuevo-{{ $rol }}" method="POST" enctype="multipart/form-data" action="{{ route('admin.' . $configRol['ruta'] . '.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nuevo-{{ $rol }}">
        @include('admin.partials.usuario-rol-campos', ['usuarioEditar' => null, 'locales' => $localesAsignables, 'conPasos' => true])
    </form>
    <x-slot:pie>
        <span class="wizard-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-secondary" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-secondary" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Anterior</button>
        <button type="button" class="encabezado-boton" data-wizard-siguiente>Siguiente<x-icono nombre="siguiente" /></button>
        <button type="submit" class="encabezado-boton" form="form-nuevo-{{ $rol }}" data-wizard-final hidden><x-icono nombre="plus" />Crear {{ $singular }}</button>
    </x-slot:pie>
</x-modal>
