@extends('layouts.app')

@section('title', 'Integracion contabilidad')
@section('estilos', 'css/admin-contabilidad.css')

@section('content')

<x-encabezado-pagina
    titulo="Conexión contabilidad"
    descripcion="API contable, movimientos, ventas del mes y sincronización con el sistema contable."
    :volver="auth()->user()?->tieneRol('admin') ? route('admin.dashboard') . '#operacion' : route('contabilidad.panel')" />

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
