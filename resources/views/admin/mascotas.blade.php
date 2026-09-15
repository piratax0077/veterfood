@extends('layouts.app')

@section('title', 'Mascotas')
@section('estilos', 'css/admin-mascotas.css')

@section('content')

<x-encabezado-pagina
    titulo="Mascotas"
    descripcion="Registro y administración de las mascotas de los clientes."
    :volver="route('admin.dashboard') . '#mascotas'">
    <button type="button" class="encabezado-boton" data-modal-abrir="modal-nueva-mascota"><x-icono nombre="plus" />Inscribir nueva mascota</button>
</x-encabezado-pagina>


<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.mascotas.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Mascota, cliente, raza, especie o chip">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.mascotas.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $mascotas->total() }} registros</span>
    </div>
    <table class="pet-table">
        <thead>
            <tr>
                <th>Foto</th>
                <th>Cliente</th>
                <th>Mascota</th>
                <th>Raza / Sexo</th>
                <th>Nacimiento</th>
                <th>Chip</th>
                <th>Peso</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($mascotas as $mascota)
                <tr>
                    <td>
                        @if($mascota->foto_url)
                            <img class="pet-photo" src="{{ asset($mascota->foto_url) }}" alt="{{ $mascota->nombre }}">
                        @else
                            <span class="pet-photo">PET</span>
                        @endif
                    </td>
                    <td>{{ $mascota->cliente?->name ?? 'Cliente histórico' }}</td>
                    <td><strong>{{ $mascota->nombre }}</strong><br><span class="muted">{{ $mascota->especie }} {{ $mascota->color }}</span></td>
                    <td>{{ $mascota->raza }}<br><span class="muted">{{ $mascota->sexo ?: 'Sin sexo' }}</span></td>
                    <td>{{ $mascota->fecha_nacimiento?->format('d-m-Y') ?? 'Sin fecha' }}</td>
                    <td>{{ $mascota->numero_chip ?: 'Sin chip' }}</td>
                    <td>{{ $mascota->peso_kg ? $mascota->peso_kg . ' kg' : 'Sin peso' }}</td>
                    <td><x-boton-tabla tipo="editar" href="{{ route('admin.mascotas.edit', $mascota) }}">Editar</x-boton-tabla></td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No hay mascotas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $mascotas->links('vendor.pagination.admin') }}</div>
</div>

{{-- Modal de crear: lo abre el boton del encabezado --}}
@include('admin.modales.nueva-mascota', ['abrirConNuevo' => request()->boolean('nuevo')])
@endsection
