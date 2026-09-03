@extends('layouts.app')

@section('title', 'Profesionales Autorizados')

@section('content')
<style>
    .pro-head{display:grid;grid-template-columns:120px minmax(0,1fr) 250px;gap:18px;align-items:center;margin:0 0 20px}
    .pro-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .hospital-icon{width:38px;height:38px;border:3px solid #111827;background:#ecfeff;position:relative;display:inline-block}
    .hospital-icon:before{content:"";position:absolute;left:14px;top:5px;width:6px;height:23px;background:#ef4444}
    .hospital-icon:after{content:"";position:absolute;left:6px;top:13px;width:23px;height:6px;background:#ef4444}
    .hospital-icon span{position:absolute;right:-9px;bottom:5px;width:15px;height:21px;border:3px solid #111827;background:#fff}
    .hospital-icon span:before{content:"";position:absolute;left:4px;top:3px;width:3px;height:10px;background:#ef4444}
    .hospital-icon span:after{content:"";position:absolute;left:1px;top:6px;width:9px;height:3px;background:#ef4444}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .pro-table th,.pro-table td{padding:12px 10px}
    .active-check{display:inline-flex;width:18px;height:18px;align-items:center;justify-content:center;background:#19d319;color:#fff;border:2px solid #065f08;font-weight:900;line-height:1}
    .create-pro-btn{width:100%;min-height:48px;line-height:1.2}
    .survey-pill{display:inline-flex;border-radius:999px;background:#e0f2fe;color:#075985;font-size:12px;font-weight:900;padding:5px 10px}
    .table-tools{display:flex;gap:12px;align-items:end;justify-content:space-between;margin-bottom:18px}
    .table-tools form{display:flex;gap:10px;align-items:end;min-width:min(620px,100%)}
    .table-tools input{min-width:340px}
    .pagination-wrap{margin-top:18px}
    @media(max-width:900px){.pro-head{grid-template-columns:1fr}.pro-title{font-size:28px}}
</style>

<div class="pro-head">
    <a class="btn btn-secondary" href="{{ route('admin.dashboard') }}">Volver</a>
    <h1 class="pro-title"><span class="hospital-icon"><span></span></span>Profesionales Autorizados</h1>
    <a class="btn create-pro-btn" href="{{ route('admin.profesionales.create') }}">Crear nuevo profesional</a>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <div class="table-tools">
        <form method="GET" action="{{ route('admin.profesionales.index') }}">
            <div>
                <label class="floating-label-activo-sm">Buscar</label>
                <input class="form-control form-control-sm" name="buscar" value="{{ $buscar ?? '' }}" placeholder="Nombre, RUT, especialidad, email o zona">
            </div>
            <button class="btn">Buscar</button>
            @if(!empty($buscar))<a class="btn btn-secondary" href="{{ route('admin.profesionales.index') }}">Limpiar</a>@endif
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
                        <span class="survey-pill">{{ $encuestas[$profesional->encuesta_sistema_nacional] ?? 'Sin respuesta' }}</span>
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
                            <span class="active-check">✓</span>
                        @else
                            <span class="muted">No</span>
                        @endif
                    </td>
                    <td><a class="edit-btn" href="{{ route('admin.profesionales.edit', $profesional) }}">Editar</a></td>
                </tr>
            @empty
                <tr><td colspan="11" class="muted">No hay profesionales registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="pagination-wrap">{{ $profesionales->links('vendor.pagination.admin') }}</div>
</div>
@endsection
