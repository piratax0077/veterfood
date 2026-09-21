{{--
    Modal "Inscribir mascota". Se abre con cualquier boton data-modal-abrir="modal-nueva-mascota"
    (opcional: data-modal-rellenar='{"user_id": 5}' para dejar elegido al tutor).
    Datos: $clientesMascota (View Composer en AppServiceProvider). Se reabre solo si al guardar hubo errores.
--}}
<x-modal
    id="modal-nueva-mascota"
    titulo="Inscribir mascota"
    descripcion="Elige al cliente dueño y completa los datos de la mascota."
    ancho="grande"
    :abierto="old('_modal') === 'nueva-mascota' || ($abrirConNuevo ?? false)">
    <form id="form-nueva-mascota" method="POST" action="{{ route('admin.mascotas.store') }}" data-keep-open="1">
        @csrf
        <input type="hidden" name="_modal" value="nueva-mascota">
        @include('admin.partials.mascota-campos', ['mascotaEditar' => null, 'clientes' => $clientesMascota])
    </form>
    <x-slot:pie>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar>Cancelar</button>
        <button type="submit" class="encabezado-boton" form="form-nueva-mascota"><x-icono nombre="plus" />Inscribir mascota</button>
    </x-slot:pie>
</x-modal>
