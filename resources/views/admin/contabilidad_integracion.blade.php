@extends('layouts.app')

@section('title', 'Integracion contabilidad')

@section('content')
<style>
    .accounting-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .accounting-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#061a3d}
    .accounting-icon{width:38px;height:38px;border-radius:10px;background:#0f172a;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .accounting-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}
    .metric{border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;padding:18px}
    .metric span{display:block;color:#657083;font-weight:800;margin-bottom:8px}
    .metric strong{display:block;font-size:28px;color:#061a3d}
    .status-pill{display:inline-flex;align-items:center;border-radius:999px;padding:7px 12px;font-weight:900;font-size:13px;background:#e5e7eb;color:#111827}
    .status-pill.conectado{background:#dcfce7;color:#14532d}
    .status-pill.pendiente_configuracion,.status-pill.falta_token{background:#fef3c7;color:#92400e}
    .status-pill.error_api,.status-pill.sin_conexion{background:#fee2e2;color:#991b1b}
    .sync-list{margin:10px 0 0;padding-left:18px;color:#334155;line-height:1.5}
    .config-box{background:#111827;color:#e5e7eb;border-radius:8px;padding:14px;overflow:auto;font-size:13px;line-height:1.6}
    .actions-row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-top:14px}
    .contador-layout{display:grid;grid-template-columns:minmax(280px,420px) minmax(0,1fr);gap:22px;align-items:start}
    .contador-form{display:grid;gap:12px}.contador-form label{margin-top:0}.contador-list{display:grid;gap:10px}
    .contador-item{display:flex;justify-content:space-between;gap:14px;align-items:center;padding:13px;border:1px solid #dbe3ee;border-radius:10px;background:#f8fafc}
    .contador-item strong,.contador-item span{display:block}.contador-item span{color:#657083;font-size:13px}
    @media(max-width:900px){.accounting-head,.accounting-grid{grid-template-columns:1fr}.span-12,.span-8,.span-6,.span-4{grid-column:span 12}.accounting-title{font-size:28px}}
    @media(max-width:700px){.contador-layout{grid-template-columns:1fr}.contador-item{align-items:flex-start;flex-direction:column}}
</style>

<div class="accounting-head">
    <a class="btn btn-secondary" href="{{ auth()->user()?->tieneRol('admin') ? route('admin.dashboard') : route('contabilidad.panel') }}">Volver</a>
    <h1 class="accounting-title"><span class="accounting-icon">C</span>Integracion con contabilidad</h1>
</div>

<div class="accounting-grid">
    <div class="classic-card span-12">
        <div class="between">
            <div>
                <h2>Estado API contable</h2>
                <p class="muted">Conexión desde Alimentos hacia la API externa de Contabilidad, autenticada con token y UUID del cliente.</p>
            </div>
            <span class="status-pill {{ $api['estado'] }}">{{ str_replace('_', ' ', $api['estado']) }}</span>
        </div>
        <p>{{ $api['mensaje'] }}</p>
        <div class="actions-row">
            @if($externalWebUrl)
                <a class="btn" href="{{ $externalWebUrl }}" target="_blank" rel="noopener">Abrir sistema contable externo</a>
            @endif
            <a class="btn btn-secondary" href="{{ route('admin.contabilidad.integracion') }}">Actualizar estado de la API</a>
        </div>
    </div>

    <div class="classic-card span-12">
        <div class="between">
            <div>
                <h2>Contadores de la API externa</h2>
                <p class="muted">El usuario se crea exclusivamente en Contabilidad API y queda asociado al cliente UUID configurado.</p>
            </div>
            <span class="badge">{{ count($contadoresApi['data']) }} contadores</span>
        </div>

        @if($errors->has('contabilidad'))
            <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first('contabilidad') }}</div>
        @endif

        <div class="contador-layout">
            <form class="contador-form" method="POST" action="{{ route('admin.contabilidad.contadores.store') }}" data-keep-open="1">
                @csrf
                <div>
                    <label for="contador-name">Nombre completo</label>
                    <input id="contador-name" name="name" value="{{ old('name') }}" required autocomplete="name">
                    @error('name')<span class="field-help" style="color:#b91c1c">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="contador-email">Correo de acceso</label>
                    <input id="contador-email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                    @error('email')<span class="field-help" style="color:#b91c1c">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="contador-password">Contraseña inicial</label>
                    <input id="contador-password" name="password" type="password" required autocomplete="new-password">
                    @error('password')<span class="field-help" style="color:#b91c1c">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="contador-password-confirmation">Confirmar contraseña</label>
                    <input id="contador-password-confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
                </div>
                <button type="submit" @disabled($api['estado'] !== 'conectado')>Crear contador en la API</button>
                @if($api['estado'] !== 'conectado')
                    <span class="field-help">Configure y conecte la API antes de crear contadores.</span>
                @endif
            </form>

            <div>
                @if($contadoresApi['error'])
                    <div class="alert" style="background:#fef3c7;color:#92400e">{{ $contadoresApi['error'] }}</div>
                @elseif(empty($contadoresApi['data']))
                    <p class="muted">Todavía no hay contadores asociados a este cliente contable.</p>
                @else
                    <div class="contador-list">
                        @foreach($contadoresApi['data'] as $contador)
                            <div class="contador-item">
                                <div><strong>{{ $contador['name'] }}</strong><span>{{ $contador['email'] }}</span></div>
                                <span class="status-pill {{ !empty($contador['activo']) ? 'conectado' : 'sin_conexion' }}">{{ !empty($contador['activo']) ? 'Activo' : 'Inactivo' }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="metric span-4">
        <span>Ventas Alimentos {{ $local['periodo'] }}</span>
        <strong>${{ number_format($local['ingresos'], 0, ',', '.') }}</strong>
    </div>
    <div class="metric span-4">
        <span>Descuentos y vouchers</span>
        <strong>${{ number_format($local['descuentos'], 0, ',', '.') }}</strong>
    </div>
    <div class="metric span-4">
        <span>Pedidos por sincronizar</span>
        <strong>{{ $local['pendiente_sincronizar'] }}</strong>
    </div>

    <div class="classic-card span-6">
        <h2>Datos locales disponibles para sincronización</h2>
        <table>
            <tbody>
                <tr><th>Subtotal productos</th><td>${{ number_format($local['subtotal'], 0, ',', '.') }}</td></tr>
                <tr><th>Ingresos cobrados</th><td>${{ number_format($local['ingresos'], 0, ',', '.') }}</td></tr>
                <tr><th>Costos de envio</th><td>${{ number_format($local['envios'], 0, ',', '.') }}</td></tr>
                <tr><th>Descuentos aplicados</th><td>${{ number_format($local['descuentos'], 0, ',', '.') }}</td></tr>
                <tr><th>Pedidos del periodo</th><td>{{ $local['pedidos'] }}</td></tr>
            </tbody>
        </table>
        <ul class="sync-list">
            <li>Registrar ventas como movimientos tipo ingreso.</li>
            <li>Registrar vouchers y descuentos como egresos comerciales o rebajas.</li>
            <li>Enviar pagos a profesionales desde liquidaciones.</li>
        </ul>
    </div>

    <div class="classic-card span-6">
        <h2>Configuracion API</h2>
        <p class="muted">La API contable es otra aplicación. Para conectarla en el servidor configura estas variables:</p>
        <div class="config-box">
            CONTABILIDAD_API_URL=http://contabilidad-api.test/api/v1<br>
            CONTABILIDAD_API_TOKEN=token_sanctum_contabilidad<br>
            CONTABILIDAD_CLIENTE_UUID=uuid_cliente_alimentos<br>
            CONTABILIDAD_WEB_URL={{ $externalWebUrl ?: 'opcional: URL web de la aplicación contable' }}
        </div>
        @if($api['url'])
            <p class="muted">Endpoint consultado: {{ $api['url'] }}</p>
        @endif
    </div>

    <div class="classic-card span-12">
        <h2>Ultimos movimientos desde contabilidad</h2>
        <div style="overflow-x:auto">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Descripcion</th>
                        <th>Monto</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($api['movimientos'] as $movimiento)
                        <tr>
                            <td>{{ $movimiento['fecha'] ?: 'Sin fecha' }}</td>
                            <td><span class="badge">{{ $movimiento['tipo'] ?: 'Movimiento' }}</span></td>
                            <td>{{ $movimiento['descripcion'] }}</td>
                            <td>${{ number_format($movimiento['monto'], 0, ',', '.') }}</td>
                            <td>{{ $movimiento['estado'] }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="muted">Sin movimientos recibidos desde la API contable.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
