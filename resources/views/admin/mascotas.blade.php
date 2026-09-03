@extends('layouts.app')

@section('title', 'Mascotas')

@section('content')
<style>
    .pets-head{display:grid;grid-template-columns:160px minmax(0,1fr) 250px;gap:18px;align-items:center;margin:0 0 20px}
    .pets-title{display:flex;align-items:center;justify-content:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .paw-icon{width:36px;height:36px;border-radius:50%;background:#f97316;position:relative;display:inline-block;box-shadow:0 12px 0 -4px #f97316}
    .paw-icon:before{content:"";position:absolute;left:-9px;top:4px;width:14px;height:14px;border-radius:50%;background:#fb923c;box-shadow:15px -8px 0 #fb923c,30px 0 0 #fb923c}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .pet-photo{width:52px;height:52px;border-radius:8px;background:#e5e7eb;object-fit:cover;display:inline-flex;align-items:center;justify-content:center;color:#6b7280;font-weight:800}
    .pet-table th,.pet-table td{padding:12px 10px}
    .create-pet-btn{width:100%;min-height:48px;line-height:1.2}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(620px,100%)}
    .table-tools input{min-width:340px}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){.pets-head{grid-template-columns:1fr}.pets-title{justify-content:flex-start;font-size:28px}}
</style>

<div class="pets-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver a escritorio</a>
    <h1 class="pets-title"><span class="paw-icon"></span>Mascotas de Clientes</h1>
    <a class="btn create-pet-btn" href="{{ route('admin.mascotas.create') }}">Inscribir nueva mascota</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.mascotas.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Mascota, cliente, raza, especie o chip">
            </div>
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.mascotas.index') }}">Limpiar</a>@endif
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
                    <td>{{ $mascota->cliente?->name ?? 'Cliente historico' }}</td>
                    <td><strong>{{ $mascota->nombre }}</strong><br><span class="muted">{{ $mascota->especie }} {{ $mascota->color }}</span></td>
                    <td>{{ $mascota->raza }}<br><span class="muted">{{ $mascota->sexo ?: 'Sin sexo' }}</span></td>
                    <td>{{ $mascota->fecha_nacimiento?->format('d-m-Y') ?? 'Sin fecha' }}</td>
                    <td>{{ $mascota->numero_chip ?: 'Sin chip' }}</td>
                    <td>{{ $mascota->peso_kg ? $mascota->peso_kg . ' kg' : 'Sin peso' }}</td>
                    <td><a class="edit-btn" href="{{ route('admin.mascotas.edit', $mascota) }}">Editar</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="muted">No hay mascotas registradas.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $mascotas->links('vendor.pagination.admin') }}</div>
</div>
@endsection
