@extends('layouts.app')

@section('title', 'Activar 2FA')

@section('content')
<style>
    .auth-wrap{max-width:860px;margin:28px auto}
    .auth-card{display:grid;grid-template-columns:320px minmax(0,1fr);gap:28px;align-items:start}
    .qr-panel{border:1px solid #dbe3ee;border-radius:8px;background:#fff;padding:18px;text-align:center}
    .qr-panel img{width:260px;height:260px}
    .secret-code{font-family:Consolas,monospace;background:#111827;color:#fff;border-radius:6px;padding:10px 12px;display:block;overflow-wrap:anywhere}
    .code-input{font-size:24px;letter-spacing:4px;text-align:center;font-weight:800}
    @media(max-width:800px){.auth-card{grid-template-columns:1fr}.qr-panel img{width:220px;height:220px}}
</style>

<div class="auth-wrap">
    <h1>Activar autenticacion de administrador</h1>
    <div class="card auth-card">
        <div class="qr-panel">
            <img src="{{ route('two-factor.qr') }}" alt="QR autenticador">
            <p class="muted">Escanea este QR con tu app de autenticacion.</p>
        </div>
        <div>
            <h2>Verificacion 2FA</h2>
            <p class="muted">Despues de escanear, escribe el codigo de 6 digitos que muestra tu aplicacion.</p>

            <label class="floating-label-activo-sm">Clave manual</label>
            <span class="secret-code">{{ $secret }}</span>


            <form method="POST" action="{{ route('two-factor.confirm') }}" style="margin-top:18px">
                @csrf
                <label class="floating-label-activo-sm">Codigo de la app</label>
                <input class="code-input form-control form-control-sm" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="12" required autofocus>
                <button class="btn" style="margin-top:14px;width:100%">Activar y entrar</button>
            </form>
        </div>
    </div>
</div>
@endsection
