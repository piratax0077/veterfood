@extends('layouts.app')

@section('title', 'Inicio Comercializadora Alimentos')

@section('content')
<style>
    main{max-width:none;padding:0}
    .home-shell{background:#eef4f8;min-height:calc(100vh - 57px)}
    .hero-wrap{position:relative;background-image:linear-gradient(90deg,rgba(238,244,248,.84) 0%,rgba(238,244,248,.5) 48%,rgba(238,244,248,.08) 100%),url('{{ asset('images/inicio-alimentos-hero.png') }}');background-size:cover;background-position:center;overflow:hidden}
    .hero-wrap:after{content:"";position:absolute;inset:auto 0 0 0;height:120px;background:linear-gradient(180deg,rgba(238,244,248,0),#eef4f8);pointer-events:none}
    .hero{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(340px,.62fr);gap:34px;align-items:center;max-width:1240px;margin:0 auto;padding:56px 18px 54px;min-height:650px}
    .hero-copy{padding:30px 0}
    .hero-kicker{display:inline-flex;align-items:center;gap:10px;background:#dcfce7;color:#14532d;border:1px solid #bbf7d0;border-radius:999px;padding:8px 12px;font-weight:900;font-size:13px}
    .hero-title{font-size:54px;line-height:1.02;letter-spacing:0;margin:18px 0 16px;color:#06152f;max-width:760px}
    .hero-title span{color:#166534}
    .hero-copy p{font-size:18px;line-height:1.55;color:#475569;max-width:690px}
    .hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin:24px 0}
    .hero-actions .btn,.hero-actions .btn-secondary{min-width:170px}
    .trust-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:26px;max-width:760px}
    .trust-item{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:14px;box-shadow:0 6px 16px rgba(15,23,42,.05)}
    .trust-item strong{display:block;font-size:22px;color:#06152f}
    .trust-item span{display:block;color:#64748b;margin-top:4px}
    .access-panel{background:rgba(255,255,255,.94);border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 18px 50px rgba(15,23,42,.18);overflow:hidden;backdrop-filter:blur(10px)}
    .access-head{padding:20px 22px 0}
    .access-head h2{margin:0 0 6px;color:#06152f}
    .form-section{padding:22px}
    .form-section h2{color:#06152f;margin-bottom:6px}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .span-2{grid-column:span 2}
    .mini-note{background:#f0f9ff;border:1px solid #bae6fd;color:#075985;border-radius:8px;padding:11px;margin:12px 0;font-weight:800}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.58);padding:22px;z-index:50}
    .modal:target,.modal.has-errors{display:flex}
    .modal-dialog{width:min(760px,100%);max-height:92vh;overflow:auto;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 28px 90px rgba(15,23,42,.38)}
    .modal-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;border-bottom:1px solid #e2e8f0;padding:20px 22px}
    .modal-head h2{margin:0;color:#06152f}
    .modal-close{width:42px;height:42px;min-width:42px;min-height:42px;border-radius:50%;background:#e5e7eb;color:#111827;font-size:24px;padding:0}
    .feature-band{background:#fff;border-top:1px solid #dbe3ee;border-bottom:1px solid #dbe3ee}
    .feature-inner{max-width:1240px;margin:0 auto;padding:28px 18px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .feature-card{border:1px solid #e2e8f0;border-radius:8px;padding:18px;background:#fff}
    .feature-icon{width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:900;margin-bottom:12px}
    .i1{background:#166534}.i2{background:#2563eb}.i3{background:#ec4899}.i4{background:#0f766e}
    .feature-card h3{font-size:18px;margin:0 0 8px;color:#06152f}
    .feature-card p{margin:0;color:#64748b;line-height:1.45}
    .workflow{max-width:1240px;margin:0 auto;padding:30px 18px 42px}
    .workflow h2{font-size:30px;color:#06152f}
    .steps{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .step{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:16px}
    .step-number{width:30px;height:30px;border-radius:999px;background:#166534;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;margin-bottom:10px}
    .step strong{display:block;color:#06152f;margin-bottom:6px}
    .step span{color:#64748b;line-height:1.4}
    .site-footer{background:#071426;color:#dbeafe;border-top:1px solid rgba(255,255,255,.12)}
    .footer-inner{max-width:1240px;margin:0 auto;padding:34px 18px;display:grid;grid-template-columns:1.25fr repeat(3,minmax(0,1fr));gap:24px}
    .footer-brand strong{display:block;font-size:22px;color:#fff;margin-bottom:8px}
    .footer-brand p,.footer-col p{color:#b6c6db;line-height:1.5;margin:0}
    .footer-col h3{font-size:16px;margin:0 0 12px;color:#fff}
    .footer-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}
    .footer-list li,.footer-list a{color:#cbd5e1;text-decoration:none}
    .footer-list a:hover{color:#fff;text-decoration:underline}
    .footer-bottom{border-top:1px solid rgba(255,255,255,.1);max-width:1240px;margin:0 auto;padding:14px 18px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#9fb0c6;font-size:14px}
    @media(max-width:980px){.hero-wrap{background-image:linear-gradient(180deg,rgba(238,244,248,.88),rgba(238,244,248,.54)),url('{{ asset('images/inicio-alimentos-hero.png') }}')}.hero{grid-template-columns:1fr;min-height:auto}.hero-title{font-size:40px}.trust-grid,.feature-inner,.steps,.footer-inner{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.footer-bottom{display:block}}
</style>

<div class="home-shell">
    <section class="hero-wrap">
    <div class="hero">
        <div class="hero-copy">
            <span class="hero-kicker">Alimentos, servicios, vouchers y reparto conectado</span>
            <h1 class="hero-title">Gestion inteligente para <span>mascotas y centros de distribucion</span></h1>
            <p>Un sistema completo para clientes con mascotas, planes de alimento, tienda, vouchers con QR, pagos, stock, rutas de reparto y administracion segura con 2FA.</p>
            <div class="hero-actions">
                <a class="btn" href="#inscripcion">Inscribirme</a>
                <a class="btn btn-secondary" href="#login">Ingresar al sistema</a>
                <a class="btn btn-success" href="{{ route('tienda.catalogo') }}">Ver tienda</a>
            </div>
            <div class="trust-grid">
                <div class="trust-item"><strong>2FA</strong><span>Administracion protegida</span></div>
                <div class="trust-item"><strong>QR</strong><span>Vouchers y placa mascota</span></div>
                <div class="trust-item"><strong>Tracking</strong><span>Pedidos y repartidores</span></div>
            </div>
        </div>

        <div class="access-panel">
            <div class="access-head">
                <h2>Ingreso al sistema</h2>
                <p class="muted">Acceso para administracion, central, auditor, repartidor y clientes.</p>
            </div>
            <div id="login" class="form-section">
                @if($errors->any())<p style="color:var(--danger)">{{ $errors->first() }}</p>@endif
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label class="floating-label-activo-sm">Email</label>
                    <input class="form-control form-control-sm" type="email" name="email" required>
                    <label class="floating-label-activo-sm">Clave</label>
                    <input class="form-control form-control-sm" type="password" name="password" required>
                    <button class="btn" style="margin-top:14px;width:100%">Entrar</button>
                </form>
                <div class="mini-note">Si aun no tienes cuenta, puedes crear una inscripcion de cliente.</div>
                <a class="btn btn-success" href="#inscripcion" style="width:100%">Abrir inscripcion</a>
            </div>
        </div>
    </div>
    </section>

    <div id="inscripcion" class="modal {{ $errors->getBag('registro')->any() ? 'has-errors' : '' }}" role="dialog" aria-modal="true">
        <div class="modal-dialog">
            <div class="modal-head">
                <div>
                    <h2>Crear cuenta cliente</h2>
                    <p class="muted" style="margin:6px 0 0">Registra tus datos y luego podras ingresar mascotas, direcciones y planes de alimento.</p>
                </div>
                <a class="btn modal-close" href="{{ route('inicio') }}" aria-label="Cerrar">&times;</a>
            </div>
            <div class="form-section">
                @if($errors->getBag('registro')->any())<p style="color:var(--danger)">{{ $errors->getBag('registro')->first() }}</p>@endif
                <form method="POST" action="{{ route('registro.cliente') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="span-2"><label class="floating-label-activo-sm">Nombre completo</label><input class="form-control form-control-sm" name="name" value="{{ old('name') }}" required></div>
                        <div><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email') }}" required></div>
                        <div><label class="floating-label-activo-sm">Telefono</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono') }}"></div>
                        <div><label class="floating-label-activo-sm">Clave</label><input class="form-control form-control-sm" type="password" name="password" required></div>
                        <div><label class="floating-label-activo-sm">Confirmar clave</label><input class="form-control form-control-sm" type="password" name="password_confirmation" required></div>
                        <div class="span-2"><label class="floating-label-activo-sm">Direccion principal</label><input class="form-control form-control-sm" name="direccion" value="{{ old('direccion') }}"></div>
                        <div><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna') }}"></div>
                        <div><label class="floating-label-activo-sm">Referencia</label><input class="form-control form-control-sm" name="referencia" value="{{ old('referencia') }}"></div>
                    </div>
                    <div class="mini-note">La inscripcion inicial queda como cliente. El administrador puede convertir roles despues si corresponde.</div>
                    <button class="btn-success" style="width:100%">Crear cuenta</button>
                </form>
            </div>
        </div>
    </div>

    <section class="feature-band">
        <div class="feature-inner">
            <div class="feature-card"><span class="feature-icon i1">A</span><h3>Planes de alimento</h3><p>Pedidos recurrentes mensuales o semanales conectados a tienda y stock.</p></div>
            <div class="feature-card"><span class="feature-icon i2">R</span><h3>Reparto tipo app</h3><p>Asignacion, tracking, GPS, foto de entrega, reclamos y conformidad.</p></div>
            <div class="feature-card"><span class="feature-icon i3">V</span><h3>Vouchers seguros</h3><p>QR, firma, control de canje, auditoria y beneficios asociados a planes.</p></div>
            <div class="feature-card"><span class="feature-icon i4">S</span><h3>Servicios veterinarios</h3><p>Profesionales, banos, peluqueria, hotel, cuidados y atenciones a domicilio.</p></div>
        </div>
    </section>

    <section class="workflow">
        <h2>Flujo operativo del sistema</h2>
        <div class="steps">
            <div class="step"><span class="step-number">1</span><strong>Cliente</strong><span>Se inscribe y registra mascotas.</span></div>
            <div class="step"><span class="step-number">2</span><strong>Plan</strong><span>Define alimento, frecuencia, direccion y voucher.</span></div>
            <div class="step"><span class="step-number">3</span><strong>Central</strong><span>Genera pedidos, stock y rutas.</span></div>
            <div class="step"><span class="step-number">4</span><strong>Repartidor</strong><span>Entrega con tracking y evidencia.</span></div>
            <div class="step"><span class="step-number">5</span><strong>Auditoria</strong><span>Controla vouchers, pagos y alertas.</span></div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <strong>Comercializadora Alimentos</strong>
                <p>Plataforma para planes de alimento, tienda, servicios veterinarios, vouchers seguros, reparto y administracion de centros de distribucion.</p>
            </div>
            <div class="footer-col">
                <h3>Consultas</h3>
                <ul class="footer-list">
                    <li>Atencion clientes: +56 9 1234 5678</li>
                    <li>Soporte sistema: soporte@alimentos.local</li>
                    <li>Ventas y convenios: ventas@alimentos.local</li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Ubicacion</h3>
                <ul class="footer-list">
                    <li>Centro de distribucion principal</li>
                    <li>Santiago, Chile</li>
                    <li>Despacho programado y retiro en tienda</li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Accesos</h3>
                <ul class="footer-list">
                    <li><a href="{{ route('tienda.catalogo') }}">Tienda</a></li>
                    <li><a href="#inscripcion">Inscripcion cliente</a></li>
                    <li><a href="#login">Ingreso al sistema</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>Horario referencial: lunes a sabado, 09:00 a 19:00 hrs.</span>
            <span>Vouchers QR, pagos y tracking protegidos por auditoria.</span>
        </div>
    </footer>
</div>
@endsection
