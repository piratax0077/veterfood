@extends('layouts.app')

@section('title', 'Administracion')

@section('content')
<style>
    .admin-hero{display:flex;align-items:center;gap:16px;margin-bottom:24px}
    .admin-shield{width:34px;height:42px;border:3px solid #111827;border-radius:18px 18px 22px 22px;position:relative;background:#fff;box-shadow:inset 10px 0 0 #e11d48}
    .admin-shield:after{content:"";position:absolute;left:12px;top:7px;width:8px;height:20px;border-left:3px solid #111827;border-bottom:3px solid #111827;transform:rotate(-22deg)}
    .admin-hero h1{font-size:34px;line-height:1.15;margin:0;color:#061a3d}
    .admin-hero span{font-weight:400;color:#000;margin-left:12px}
    .admin-menu{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:38px 24px;margin-bottom:34px}
    .admin-menu-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:24px 24px 22px;min-height:158px;box-shadow:0 3px 8px rgba(15,23,42,.08);display:flex;flex-direction:column;justify-content:space-between}
    .admin-menu-title{font-size:22px;font-weight:800;margin:0 0 10px;color:#030712;display:flex;align-items:center;gap:10px}
    .admin-menu-title:before{width:32px;height:32px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:900;box-shadow:0 4px 10px rgba(15,23,42,.15)}
    .icon-finanzas:before{content:"$";background:#0891b2}
    .icon-contabilidad:before{content:"C";background:#0f172a}
    .icon-usuarios:before{content:"U";background:#2563eb}
    .icon-clientes:before{content:"C";background:#f59e0b;color:#111827}
    .icon-locales:before{content:"L";background:#0f766e}
    .icon-planes:before{content:"P";background:#7c3aed}
    .icon-encuestas:before{content:"E";background:#9333ea}
    .icon-mascotas:before{content:"P";background:#ea580c}
    .icon-profesionales:before{content:"+";background:#111827}
    .icon-vendedores:before{content:"V";background:#15803d}
    .icon-repartidores:before{content:"R";background:#16a34a}
    .icon-historial:before{content:"H";background:#0f766e}
    .icon-vauchers:before{content:"%";background:#ec4899}
    .icon-bodega:before{content:"B";background:#9f1239}
    .icon-rendiciones:before{content:"$";background:#eab308;color:#111827}
    .icon-liquidaciones:before{content:"L";background:#047857}
    .icon-servicios:before{content:"S";background:#1d4ed8}
    .icon-alertas:before{content:"!";background:#be123c}
    .admin-menu-card p{margin:0 0 18px;color:#111827}
    .admin-menu-card .btn{width:100%;border-radius:6px;color:#fff;min-height:52px;padding:12px 18px;line-height:1.2}
    .admin-cyan{background:#22c1dc;color:#00111f}
    .admin-blue{background:#1f6feb}
    .admin-yellow{background:#f7b500;color:#111827!important}
    .admin-dark{background:#22272e}
    .admin-green{background:#198754}
    .admin-pink{background:#f7a8c4}
    .admin-wine{background:#a00017}
    .admin-red{background:#dc3545}
    .admin-section-title{margin:14px 0 16px;padding-top:10px;border-top:1px solid var(--line)}
    .approval-panel{background:#fff;border:1px solid #f59e0b;border-radius:8px;padding:18px;margin:0 0 22px;box-shadow:0 8px 18px rgba(245,158,11,.12)}
    .approval-panel h2{margin:0 0 6px;color:#061a3d}.approval-table th,.approval-table td{vertical-align:middle}.approval-empty{color:#64748b;margin:8px 0 0}
    .integration-panel{background:#fff;border:1px solid #14b8a6;border-radius:8px;padding:18px;margin:0 0 22px;box-shadow:0 8px 18px rgba(20,184,166,.10)}
    .integration-grid{display:grid;grid-template-columns:minmax(250px,1fr) repeat(3,150px) auto;gap:14px;align-items:center}
    .integration-stat{padding:10px 12px;border-radius:8px;background:#f0fdfa}.integration-stat strong{display:block;font-size:22px;color:#0f766e}.integration-stat span{font-size:12px;color:#64748b}
    .sync-state{display:inline-flex;align-items:center;gap:7px;font-weight:800;color:#15803d}.sync-state.offline{color:#b91c1c}.sync-dot{width:9px;height:9px;border-radius:50%;background:currentColor}
    @media(max-width:1100px){.integration-grid{grid-template-columns:1fr 1fr}.integration-main,.integration-action{grid-column:1/-1}}
    .admin-module{margin:0 0 28px;padding:20px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc;box-shadow:0 4px 14px rgba(15,23,42,.04)}
    .admin-module-head{display:flex;align-items:center;justify-content:space-between;gap:18px;margin:0 0 16px}.admin-module-head h2{margin:0;color:#0f172a;font-size:23px}.admin-module-head p{margin:4px 0 0;color:#64748b}.admin-module-badge{border-radius:999px;padding:6px 11px;background:#e0f2fe;color:#0369a1;font-size:12px;font-weight:900}
    .admin-module .admin-menu{gap:14px;margin-bottom:0}.admin-module .admin-menu-card{min-height:170px;border-radius:9px;padding:20px}
    .voucher-module{border-color:#f9a8d4;background:#fdf2f8}.voucher-module .admin-module-badge{background:#fce7f3;color:#be185d}
    @media(max-width:980px){.admin-menu{grid-template-columns:1fr}.admin-module{padding:14px}.admin-module-head{align-items:flex-start;flex-direction:column}.admin-hero{align-items:flex-start}.admin-hero h1{font-size:28px}.admin-hero span{display:block;margin:6px 0 0}}
</style>

<div class="admin-hero">
    <div class="admin-shield" aria-hidden="true"></div>
    <h1>Administrador <span>Comercializadora Alimentos&nbsp;&nbsp; productos y servicios veterinarios</span></h1>
</div>

<section class="approval-panel">
    <div class="between">
        <div>
            <h2>Aprobaciones contables pendientes</h2>
            <p class="muted">Autoriza la relacion entre una institucion y su contador. El contador debe aceptar despues para activar el acceso.</p>
        </div>
        <span class="badge">{{ $relacionesContablesPendientes->count() }} pendientes</span>
    </div>
    @if($relacionesContablesPendientes->isEmpty())
        <p class="approval-empty">No hay relaciones contables esperando aprobacion.</p>
    @else
        <table class="approval-table">
            <thead>
                <tr><th>Institucion</th><th>Contador</th><th>Valor pactado</th><th>Solicitado</th><th>Accion</th></tr>
            </thead>
            <tbody>
                @foreach($relacionesContablesPendientes as $relacion)
                    <tr>
                        <td><strong>{{ $relacion->razon_social }}</strong><br><span class="muted">{{ $relacion->rut }}</span></td>
                        <td>{{ $relacion->contador_nombre }}<br><span class="muted">{{ $relacion->contador_email }}</span></td>
                        <td>${{ number_format((int) $relacion->valor_pactado_servicio, 0, ',', '.') }}</td>
                        <td>{{ optional($relacion->created_at ? \Carbon\Carbon::parse($relacion->created_at) : null)->format('d-m-Y H:i') }}</td>
                        <td>
                            <form method="POST" action="{{ route('admin.contabilidad.relaciones.aprobar', ['centroMedico' => $relacion->centro_medico_id, 'user' => $relacion->user_id]) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn-success">Aprobar relacion</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</section>

<section class="integration-panel">
    <div class="integration-grid">
        <div class="integration-main">
            <h2>Datos relacionados con VET-SDI</h2>
            <p class="muted">La identidad clinica se vincula por ID de origen. Los roles comerciales, locales, bodegas, stock y repartos permanecen en Alimentos.</p>
            <span class="sync-state {{ $integracionVetSdi['disponible'] ? '' : 'offline' }}"><span class="sync-dot"></span>{{ $integracionVetSdi['disponible'] ? 'Conexion disponible' : 'Conexion no disponible' }}</span>
        </div>
        <div class="integration-stat"><strong>{{ $integracionVetSdi['usuarios_vinculados'] }}</strong><span>Usuarios vinculados</span></div>
        <div class="integration-stat"><strong>{{ $integracionVetSdi['mascotas_vinculadas'] }}</strong><span>Mascotas vinculadas</span></div>
        <div class="integration-stat"><strong>{{ $integracionVetSdi['profesionales_vinculados'] }}</strong><span>Profesionales vinculados</span></div>
        <form class="integration-action" method="POST" action="{{ route('admin.integraciones.vet-sdi.sync') }}">
            @csrf
            <button class="btn admin-green" type="submit" @disabled(!$integracionVetSdi['disponible'])>Sincronizar ahora</button>
        </form>
    </div>
</section>

<div class="admin-menu">
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-finanzas">Dashboard Financiero</h2>
            <p>Ingresos, egresos, productos, planes y variaciones.</p>
        </div>
        <a class="btn admin-cyan" href="{{ route('admin.financiero') }}">Analisis financiero por productos y planes</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-contabilidad">Conexion contabilidad</h2>
            <p>API contable, movimientos, ventas del mes y sincronizacion con el sistema contable.</p>
        </div>
        <a class="btn admin-dark" href="{{ route('admin.contabilidad.integracion') }}">Administrar integracion contable</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-usuarios">Usuarios</h2>
            <p>Crear usuarios, roles y permisos.</p>
        </div>
        <a class="btn admin-blue" href="{{ route('admin.usuarios.index') }}">Administrar Usuarios</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-clientes">Clientes</h2>
            <p>Crear Clientes de reparto mensual Clientes VIP</p>
        </div>
        <a class="btn admin-yellow" href="{{ route('admin.clientes.index') }}">Administrar Clientes</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-locales">Locales y comercios</h2>
            <p>Sucursales, puntos de venta, retiro y comercios adheridos.</p>
        </div>
        <a class="btn admin-green" href="{{ route('admin.locales.index') }}">Administrar locales</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-planes">Planes comerciales</h2>
            <p>Alimento automatico, vacunas, historial clinico, voucher y placa QR.</p>
        </div>
        <a class="btn admin-blue" href="{{ route('admin.planes.comerciales') }}">Ver planes</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-encuestas">Encuestas comerciales</h2>
            <p>Opinion sobre vouchers y FONAVET: propuesta mensual desde $6.990 para atencion, beneficios y descuentos.</p>
        </div>
        <a class="btn admin-blue" href="{{ route('admin.encuestas.profesionales') }}">Ver encuestas</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-mascotas">Mascotas</h2>
            <p>Registro y administración de mascotas.</p>
        </div>
        <a class="btn admin-blue" href="{{ route('admin.mascotas.index') }}">Ver Mascotas</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-profesionales">Profesionales</h2>
            <p>Veterinarios, laboratorios y centros autorizados.</p>
        </div>
        <a class="btn admin-dark" href="{{ route('admin.profesionales.index') }}">Ver Profesionales</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-vendedores">Vendedores</h2>
            <p>Vendedores autorizados para emitir vouchers.</p>
        </div>
        <a class="btn admin-green" href="{{ route('admin.vendedores.index') }}">Ver Vendedores</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-repartidores">Repartidores</h2>
            <p>Registro de repartidores</p>
        </div>
        <a class="btn admin-green" href="{{ route('admin.repartidores.index') }}">Ver repartidores</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-historial">Historial de repartos</h2>
            <p>Conformidad del cliente, reclamos y trazabilidad.</p>
        </div>
        <a class="btn admin-green" href="{{ route('admin.repartos.historial') }}">Ver historial</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-vauchers">Vouchers de descuento</h2>
            <p>Creación, distribución, vigencia y seguimiento de vouchers.</p>
        </div>
        <a class="btn admin-pink" href="{{ route('admin.vouchers.index') }}">Administrar vouchers</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-bodega">Administración Bodegas y pedidos</h2>
            <p>Administración de bodegas y productos</p>
        </div>
        <a class="btn admin-wine" href="{{ route('central.panel') }}">Administración y manejos de stock</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-rendiciones">Rendiciones</h2>
            <p>Cobros enviados a pago por profesionales.</p>
        </div>
        <a class="btn admin-yellow" href="{{ route('admin.finanzas.vouchers.rendiciones') }}">Ver Rendiciones</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-liquidaciones">Liquidaciones</h2>
            <p>Pagos a profesionales y comisión VETERCHILE.</p>
        </div>
        <a class="btn admin-green" href="{{ route('admin.finanzas.vouchers.liquidaciones') }}">Ver Liquidaciones</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-alertas">Alertas Voucher</h2>
            <p>Alertas de riesgo, duplicados y control antifraude.</p>
        </div>
        <a class="btn admin-red" href="{{ route('auditor.vouchers.alertas') }}">Ver Alertas</a>
    </div>
    <div class="admin-menu-card">
        <div>
            <h2 class="admin-menu-title icon-servicios">Servicios</h2>
            <p>Administración de prestaciones veterinarias.</p>
        </div>
        <a class="btn admin-blue" href="{{ route('admin.servicios.index') }}">Ver Servicios</a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const original = document.querySelector('.admin-menu');
    if (!original || original.dataset.grouped === '1') return;

    const cards = Array.from(original.querySelectorAll(':scope > .admin-menu-card'));
    const byIcon = (name) => cards.find(card => card.querySelector('.icon-' + name));
    const createModule = (className, title, description, badge, icons) => {
        const section = document.createElement('section');
        section.className = 'admin-module ' + className;
        section.innerHTML = '<div class="admin-module-head"><div><h2>' + title + '</h2><p>' + description + '</p></div><span class="admin-module-badge">' + badge + '</span></div><div class="admin-menu"></div>';
        const grid = section.querySelector('.admin-menu');
        icons.forEach(icon => { const card = byIcon(icon); if (card) grid.appendChild(card); });
        original.parentNode.insertBefore(section, original);
        return section;
    };

    const principal = createModule('', 'Operacion administrativa', 'Finanzas, locales, inventario, planes y controles generales.', '9 modulos relacionados', [
        'finanzas', 'locales', 'bodega', 'planes', 'encuestas', 'historial', 'usuarios', 'contabilidad', 'vauchers'
    ]);
    createModule('', 'Usuarios y red comercial', 'Personas, mascotas y actores operativos relacionados con cada local.', '6 categorias', [
        'clientes', 'servicios', 'profesionales', 'mascotas', 'vendedores', 'repartidores'
    ]);
    createModule('voucher-module', 'Vouchers de descuento y pagos', 'Liquidacion, control antifraude y rendicion del mismo circuito de vouchers.', 'Sistema relacionado', [
        'liquidaciones', 'alertas', 'rendiciones'
    ]);

    const approvals = document.querySelector('.approval-panel');
    if (approvals && principal) principal.insertAdjacentElement('afterend', approvals);
    original.dataset.grouped = '1';
    original.remove();
});
</script>

@endsection
