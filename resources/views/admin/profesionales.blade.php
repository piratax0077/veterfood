@extends('layouts.app')

@section('title', 'Profesionales Autorizados')
@section('estilos', 'css/admin-profesionales.css')

@section('content')

<x-encabezado-pagina
    titulo="Profesionales"
    descripcion="Veterinarios, laboratorios y centros autorizados."
    :volver="route('admin.dashboard') . '#red-comercial'">
    <a class="encabezado-boton" href="{{ route('admin.profesionales.create') }}"><x-icono nombre="plus" />Crear nuevo profesional</a>
</x-encabezado-pagina>


<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.profesionales.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, RUT, especialidad, email o zona">
            </div>
            <button class="btn boton-buscar">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary boton-buscar" href="{{ route('admin.profesionales.index') }}">Limpiar</a>@endif
        </form>
        <span class="muted">{{ $profesionales->total() }} registros</span>
    </div>
    <table class="pro-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>RUT</th>
                <th>Especialidad</th>
                <th>Email</th>
                <th>Zona consulta</th>
                <th>Voucher</th>
                <th>Encuesta</th>
                <th>Banco</th>
                <th>Cuenta</th>
                <th>Activo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse($profesionales as $profesional)
                <tr>
                    <td>{{ $profesional->nombre }}</td>
                    <td>{{ $profesional->rut }}</td>
                    <td>{{ $profesional->especialidad }}</td>
                    <td>{{ $profesional->email }}</td>
                    <td>
                        <strong>{{ $profesional->codigo_geolocalizacion ?: 'Sin codigo' }}</strong><br>
                        <span class="muted">{{ $profesional->geolocalizacion ?: 'Sin geolocalizacion' }}</span>
                    </td>
                    <td>
                        {{ $profesional->recibe_voucher ? 'Si recibe' : 'No recibe' }}<br>
                        <span class="muted">{{ $profesional->porcentaje_descuento_voucher !== null ? $profesional->porcentaje_descuento_voucher . '% descuento' : 'Sin % definido' }}</span>
                    </td>
                    <td>
                        @php($encuestas = ['muy_interesante' => 'Muy interesante', 'interesante' => 'Interesante', 'neutral' => 'Neutral', 'poco_interesante' => 'Poco interesante', 'no_interesa' => 'No le interesa'])
                        <span class="badge tono-celeste">{{ $encuestas[$profesional->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span>
                        @if($profesional->comentario_sistema_nacional)
                            <br><span class="muted">{{ $profesional->comentario_sistema_nacional }}</span>
                        @endif
                    </td>
                    <td>{{ $profesional->banco }}</td>
                    <td>
                        @if($profesional->numero_cuenta)
                            ****{{ substr($profesional->numero_cuenta, -4) }}
                        @else
                            <span class="muted">Sin cuenta</span>
                        @endif
                    </td>
                    <td>
                        @if($profesional->activo)
                            <span class="badge tono-verde">Activo</span>
                        @else
                            <span class="badge tono-gris">Inactivo</span>
                        @endif
                    </td>
                    <td><x-boton-tabla tipo="editar" href="{{ route('admin.profesionales.edit', $profesional) }}">Editar</x-boton-tabla></td>
                </tr>
            @empty
                <tr><td colspan="11" class="muted">No hay profesionales registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $profesionales->links('vendor.pagination.admin') }}</div>
</div>
@endsection
