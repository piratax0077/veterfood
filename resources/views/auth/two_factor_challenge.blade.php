@extends('layouts.app')

@section('title', 'Codigo 2FA')

@section('content')
<style>
    .challenge-wrap{max-width:520px;margin:42px auto}
    .challenge-title{display:flex;align-items:center;gap:12px}
    .shield{width:42px;height:42px;border-radius:10px;background:#0f766e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:22px}
    .code-input{font-size:26px;letter-spacing:5px;text-align:center;font-weight:900}
</style>

<div class="challenge-wrap">
    <div class="card">
        <h1 class="challenge-title"><span class="shield">2</span>Codigo de seguridad</h1>
        <p class="muted">Esta vista no es un error: es la validacion 2FA requerida para abrir administracion, contabilidad y otros escritorios protegidos.</p>
        <p class="muted">Ingresa el codigo de 6 digitos de tu aplicacion de autenticacion y volveras automaticamente a la vista solicitada.</p>


        <form method="POST" action="{{ route('two-factor.confirm') }}">
            @csrf
            <label class="floating-label-activo-sm">Codigo de la app</label>
            <input class="code-input form-control form-control-sm" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="12" required autofocus>
            <button class="btn" style="margin-top:14px;width:100%">Verificar y entrar</button>
        </form>
    </div>
</div>
@endsection
