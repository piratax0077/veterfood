@extends('layouts.app')

@section('title', 'Ingresar')

@section('content')
<div class="grid">
    <div class="col-4">
        <div class="card">
            <h2>Ingreso</h2>
            <form method="POST" action="{{ route('login.store') }}">
                @csrf
                <label class="floating-label-activo-sm">Email</label>
                <input class="form-control form-control-sm" type="email" name="email" value="{{ old('email') }}" required>
                <label class="floating-label-activo-sm">Clave</label>
                <input class="form-control form-control-sm" type="password" name="password" required>
                <button class="btn-success" style="margin-top:14px;width:100%">Entrar</button>
            </form>
        </div>
    </div>
</div>
@endsection
