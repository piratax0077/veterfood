@extends('layouts.app')

@section('title', 'Inicio Comercializadora Alimentos')

@section('content')
<style>
    main{max-width:none;padding:0}
    .home-shell{background:#eef4f8;min-height:calc(100vh - 57px)}
    .hero-wrap{position:relative;background-image:linear-gradient(90deg,rgba(7,54,48,.92) 0%,rgba(8,79,71,.72) 45%,rgba(8,79,71,.28) 100%),url('{{ asset('images/inicio-alimentos-hero.png') }}');background-size:cover;background-position:center;overflow:hidden}
    .hero-wrap:after{content:"";position:absolute;inset:auto 0 0 0;height:40px;background:linear-gradient(180deg,rgba(7,54,48,0),rgba(7,54,48,.35));pointer-events:none}
    .hero{position:relative;z-index:1;display:grid;grid-template-columns:minmax(0,1.05fr) minmax(340px,.62fr);gap:34px;align-items:center;max-width:1240px;margin:0 auto;padding:56px 18px 54px;min-height:650px}
    .hero-copy{padding:30px 0}
    .hero-kicker{display:inline-flex;align-items:center;gap:10px;background:rgba(255,255,255,.14);color:#fff;border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:8px 12px;font-weight:900;font-size:13px}
    .hero-title{font-size:54px;line-height:1.02;letter-spacing:0;margin:18px 0 16px;color:#fff;max-width:760px}
    .hero-title span{color:#fff}
    .hero-copy p{font-size:18px;line-height:1.55;color:#fff;max-width:690px}
    .hero-actions{display:flex;gap:12px;flex-wrap:wrap;margin:24px 0}
    .hero-actions .btn,.hero-actions .btn-secondary{min-width:170px}
    .hero-cta{background:#d9822f;box-shadow:0 6px 16px rgba(217,130,47,.3);font-size:19px;padding:16px 30px;min-height:58px;min-width:210px;animation:cta-pulse 2.8s ease-in-out infinite}
    @keyframes cta-pulse{0%,100%{transform:scale(1);box-shadow:0 6px 16px rgba(217,130,47,.3)}50%{transform:scale(1.025);box-shadow:0 6px 20px rgba(217,130,47,.45)}}
    @media(prefers-reduced-motion:reduce){.hero-cta{animation:none}}
    .trust-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:26px;max-width:760px}
    .trust-item{background:#fff;border-radius:8px;padding:14px;box-shadow:0 6px 16px rgba(15,23,42,.27)}
    .trust-item strong{display:block;font-size:22px;color:#06152f}
    .trust-item span{display:block;color:#64748b;margin-top:4px}
    .access-panel{background:rgba(255,255,255,.94);border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 6px 16px rgba(15,23,42,.27);overflow:hidden;backdrop-filter:blur(10px)}
    .access-head{padding:20px 22px 0}
    .access-head h2{margin:0 0 6px;color:#06152f;font-weight:800}
    .form-section{padding:22px}
    .form-section h2{color:#06152f;margin-bottom:6px}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .span-2{grid-column:span 2}
    .mini-note{background:#f8fafc;border:1px solid #eceff3;color:#7c8899;border-radius:8px;padding:10px 12px;margin:12px 0;font-size:13px;font-weight:600;line-height:1.45}
    .pw-input{padding-right:38px!important}
    .pw-toggle{position:absolute;right:6px;top:27px;transform:translateY(-50%);display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;min-width:0;min-height:0;padding:0;border:0;background:none;box-shadow:none;color:#00785f;cursor:pointer;z-index:3;line-height:0}
    .pw-toggle:hover,.pw-toggle:focus,.pw-toggle:active{transform:translateY(-50%);box-shadow:none;background:none}
    .pw-toggle svg{width:20px;height:20px}
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(15,23,42,.58);padding:22px;z-index:50}
    .modal:target,.modal.has-errors{display:flex}
    .modal-dialog{width:min(760px,100%);max-height:92vh;overflow:auto;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 28px 90px rgba(15,23,42,.38)}
    .modal-head{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;border-bottom:1px solid #e2e8f0;padding:20px 22px}
    .modal-head h2{margin:0;color:#06152f}
    .modal-close{width:42px;height:42px;min-width:42px;min-height:42px;border-radius:50%;background:#e5e7eb;color:#111827;font-size:24px;padding:0}
    .feature-band{background:#fff;border-top:1px solid #dbe3ee;border-bottom:1px solid #dbe3ee}
    .feature-inner{max-width:1240px;margin:0 auto;padding:28px 18px;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}
    .feature-card{border:1px solid #e2e8f0;border-radius:13px;padding:18px;background:#fff}
    .feature-icon{width:38px;height:38px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:900;margin-bottom:12px}
    .feature-icon-img{display:block;width:clamp(32px,4vw,44px);height:clamp(32px,4vw,44px);object-fit:contain;margin-bottom:12px}
    .i1{background:#166534}.i2{background:#2563eb}.i3{background:#ec4899}.i4{background:#0f766e}
    .feature-card h3{font-size:20px;margin:0 0 8px;color:#00785f;font-weight:800}
    .feature-card p{margin:0;color:#64748b;line-height:1.45}
    .workflow{max-width:1240px;margin:0 auto;padding:30px 18px 42px}
    .workflow h2{font-size:30px;color:#06152f;font-weight:800}
    .steps{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px}
    .step{background:#fff;border-radius:13px;padding:16px;box-shadow:0 4px 10px rgba(15,23,42,.08)}
    .step-number{width:30px;height:30px;border-radius:999px;background:linear-gradient(135deg,#087f67,#10a37f);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;margin-bottom:10px}
    .step strong{display:block;font-size:22px;color:#06152f;margin-bottom:6px}
    .step span{color:#64748b;line-height:1.4}
    .step .step-number{color:#fff}
    .site-footer{background:#071426;color:#dbeafe;border-top:1px solid rgba(255,255,255,.12)}
    .footer-inner{max-width:1240px;margin:0 auto;padding:34px 18px;display:grid;grid-template-columns:1.25fr repeat(3,minmax(0,1fr));gap:24px}
    .footer-brand strong{display:block;font-size:22px;color:#fff;margin-bottom:8px}
    .footer-logo{display:block;height:auto;width:clamp(140px,16vw,200px);max-width:100%;margin-bottom:10px}
    .footer-brand p,.footer-col p{color:#b6c6db;line-height:1.5;margin:0}
    .footer-col h3{font-size:16px;margin:0 0 12px;color:#fff}
    .footer-list{display:grid;gap:8px;margin:0;padding:0;list-style:none}
    .footer-list li,.footer-list a{color:#cbd5e1;text-decoration:none}
    .footer-list a:hover{color:#fff;text-decoration:underline}
    .footer-bottom{border-top:1px solid rgba(255,255,255,.1);max-width:1240px;margin:0 auto;padding:14px 18px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;color:#9fb0c6;font-size:14px}
    @media(max-width:980px){.hero-wrap{background-image:linear-gradient(180deg,rgba(7,54,48,.92),rgba(8,79,71,.6)),url('{{ asset('images/inicio-alimentos-hero.png') }}')}.hero{grid-template-columns:1fr;min-height:auto}.hero-title{font-size:40px}.trust-grid,.feature-inner,.steps,.footer-inner{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.footer-bottom{display:block}}

    .whatsapp-fab{position:fixed;right:20px;bottom:20px;z-index:40;display:inline-flex;align-items:center;justify-content:center;width:58px;height:58px;border-radius:50%;background:#25d366;color:#fff;box-shadow:0 6px 18px rgba(37,211,102,.4);transition:transform .2s ease,box-shadow .2s ease}
    .whatsapp-fab:hover{transform:scale(1.06);box-shadow:0 8px 22px rgba(37,211,102,.5)}
    .whatsapp-fab svg{width:32px;height:32px}
    @media(max-width:600px){.whatsapp-fab{right:14px;bottom:14px;width:52px;height:52px}.whatsapp-fab svg{width:28px;height:28px}}

    @keyframes fade-up{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)}}
    .hero-kicker,.hero-title,.hero-copy p,.hero-actions,.access-panel{animation:fade-up 1.1s ease both}
    .hero-title{animation-delay:.15s}
    .hero-copy p{animation-delay:.3s}
    .hero-actions{animation-delay:.45s}
    .access-panel{animation-delay:.55s}

    .reveal{opacity:0;transform:translateY(20px);transition:opacity 1.1s ease,transform 1.1s ease}
    .reveal.is-visible{opacity:1;transform:translateY(0)}
    .feature-inner .feature-card:nth-child(1){transition-delay:.1s}
    .feature-inner .feature-card:nth-child(2){transition-delay:.28s}
    .feature-inner .feature-card:nth-child(3){transition-delay:.46s}
    .feature-inner .feature-card:nth-child(4){transition-delay:.64s}
    .steps .step:nth-child(1){transition-delay:.1s}
    .steps .step:nth-child(2){transition-delay:.24s}
    .steps .step:nth-child(3){transition-delay:.38s}
    .steps .step:nth-child(4){transition-delay:.52s}
    .steps .step:nth-child(5){transition-delay:.66s}

    @media(prefers-reduced-motion:reduce){
        .hero-kicker,.hero-title,.hero-copy p,.hero-actions,.access-panel{animation:none}
        .reveal{opacity:1;transform:none;transition:none}
    }
</style>

<div class="home-shell">
    <section class="hero-wrap">
    <div class="hero">
        <div class="hero-copy">
            <span class="hero-kicker">Alimentos, servicios, vouchers y reparto conectado</span>
            <h1 class="hero-title">Gestión inteligente para <span>mascotas y centros de distribución</span></h1>
            <p>Un sistema completo para clientes con mascotas, planes de alimento, tienda, vouchers con QR, pagos, stock, rutas de reparto y administración segura con 2FA.</p>
            <div class="hero-actions">
                <a class="btn hero-cta" href="#inscripcion">¡Crear cuenta!</a>
              {{---  <a class="btn btn-secondary" href="#login">Iniciar sesión</a>
                <a class="btn btn-success" href="{{ route('tienda.catalogo') }}">Ver tienda</a>--}}
            </div>
            {{--<div class="trust-grid">
                <div class="trust-item"><strong>2FA</strong><span>Administración protegida</span></div>
                <div class="trust-item"><strong>QR</strong><span>Vouchers y placa mascota</span></div>
                <div class="trust-item"><strong>Tracking</strong><span>Pedidos y repartidores</span></div>
            </div>--}}
        </div>

        <div class="access-panel">
            <div class="access-head">
                <h2>Ingreso al sistema</h2>
                <p class="muted">Acceso para administración, central, auditor, repartidor y clientes.</p>
            </div>
            <div id="login" class="form-section">
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label class="floating-label-activo-sm">Email</label>
                    <input class="form-control form-control-sm" type="email" name="email" required>
                    <label class="floating-label-activo-sm">Contraseña</label>
                    <input class="form-control form-control-sm" type="password" name="password" required>
                    <button class="btn btn-success" style="margin-top:14px;width:100%">Ingresar</button>
                </form>
                {{--<div class="mini-note">Si aun no tienes cuenta, puedes registrarte cómo cliente.</div>
                <a class="btn btn-success" href="#inscripcion" style="width:100%">¡Crear cuenta!</a>--}}
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
                <form method="POST" action="{{ route('registro.cliente') }}">
                    @csrf
                    <div class="form-grid">
                        <div class="span-2"><label class="floating-label-activo-sm">Nombre completo</label><input class="form-control form-control-sm" name="name" value="{{ old('name') }}" required></div>
                        <div><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="email" value="{{ old('email') }}" required></div>
                        <div><label class="floating-label-activo-sm">Celular</label><input class="form-control form-control-sm" name="telefono" value="{{ old('telefono', '+56') }}"></div>
                        <div><label class="floating-label-activo-sm">Contraseña</label><input class="form-control form-control-sm pw-input" type="password" name="password" required><button type="button" class="pw-toggle" aria-label="Mostrar contraseña" data-pw-toggle></button></div>
                        <div><label class="floating-label-activo-sm">Confirmar contraseña</label><input class="form-control form-control-sm pw-input" type="password" name="password_confirmation" required><button type="button" class="pw-toggle" aria-label="Mostrar contraseña" data-pw-toggle></button></div>
                        <div class="span-2"><label class="floating-label-activo-sm">Dirección principal</label><input class="form-control form-control-sm" name="direccion" value="{{ old('direccion') }}"></div>
                        <div><label class="floating-label-activo-sm">Comuna</label><input class="form-control form-control-sm" name="comuna" value="{{ old('comuna') }}"></div>
                        <div><label class="floating-label-activo-sm">Referencia</label><input class="form-control form-control-sm" name="referencia" value="{{ old('referencia') }}"></div>
                    </div>
                    <div class="mini-note">Al registrarte, quedas como cliente. Más adelante, si corresponde, el administrador puede cambiar tu rol.</div>
                    <button class="btn btn-success" style="width:100%">Crear cuenta</button>
                </form>
            </div>
        </div>
    </div>

    <section class="feature-band">
        <div class="feature-inner">
            <div class="feature-card reveal"><img class="feature-icon-img" src="{{ asset('images/iconos/alimento-mascota.svg') }}" alt=""><h3>Planes de alimento</h3><p>Pedidos recurrentes mensuales o semanales conectados a tienda y stock.</p></div>
            <div class="feature-card reveal"><img class="feature-icon-img" src="{{ asset('images/iconos/telefono-tracking.svg') }}" alt=""><h3>Reparto tipo app</h3><p>Asignacion, tracking, GPS, foto de entrega, reclamos y conformidad.</p></div>
            <div class="feature-card reveal"><img class="feature-icon-img" src="{{ asset('images/iconos/cupon-descuento.svg') }}" alt=""><h3>Vouchers seguros</h3><p>QR, firma, control de canje, auditoría y beneficios asociados a planes.</p></div>
            <div class="feature-card reveal"><img class="feature-icon-img" src="{{ asset('images/iconos/cruz-veterinaria.svg') }}" alt=""><h3>Servicios veterinarios</h3><p>Profesionales, baños, peluquería, hotel, cuidados y atenciones a domicilio.</p></div>
        </div>
    </section>

    <section class="workflow">
        <h2 class="reveal">Flujo operativo del sistema</h2>
        <div class="steps">
            <div class="step reveal"><span class="step-number">1</span><strong>Cliente</strong><span>Crea su cuenta y registra a mascotas.</span></div>
            <div class="step reveal"><span class="step-number">2</span><strong>Plan</strong><span>Define alimento, frecuencia, dirección y vouchers.</span></div>
            <div class="step reveal"><span class="step-number">3</span><strong>Central</strong><span>Genera pedidos, stock y rutas.</span></div>
            <div class="step reveal"><span class="step-number">4</span><strong>Repartidor</strong><span>Entrega con tracking y evidencia.</span></div>
            <div class="step reveal"><span class="step-number">5</span><strong>Auditoría</strong><span>Controla vouchers, pagos y alertas.</span></div>
        </div>
    </section>

    <footer class="site-footer">
        <div class="footer-inner">
            <div class="footer-brand">
                <img class="footer-logo" src="{{ asset('images/logotipo/logo-veterfood-blanco.svg') }}" alt="Comercializadora Alimentos">
                <p>Plataforma para planes de alimento, tienda, servicios veterinarios, vouchers seguros, reparto y administración de centros de distribución.</p>
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
                <h3>Ubicación</h3>
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
                    <li><a href="#inscripcion">Registro cliente</a></li>
                    <li><a href="#login">Ingreso al sistema</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>Horario referencial: lunes a sabado, 09:00 a 19:00 hrs.</span>
            <span>Vouchers QR, pagos y tracking protegidos por auditoria.</span>
        </div>
    </footer>

    <a class="whatsapp-fab" href="https://wa.me/56984882443" target="_blank" rel="noopener noreferrer" aria-label="Escríbenos por WhatsApp" title="Escríbenos por WhatsApp">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.78-1.47-1.75-1.65-2.05-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.01-1.04 2.47s1.06 2.87 1.21 3.07c.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.28.17-1.41-.07-.13-.27-.2-.57-.35M12.04 21.5h-.01a9.5 9.5 0 0 1-4.84-1.33l-.35-.2-3.6.94.96-3.51-.23-.36a9.46 9.46 0 0 1-1.45-5.05c0-5.23 4.27-9.5 9.53-9.5 2.54 0 4.93.99 6.73 2.79a9.44 9.44 0 0 1 2.79 6.72c0 5.24-4.27 9.5-9.53 9.5M20.5 3.49A11.44 11.44 0 0 0 12.04 0C5.73 0 .6 5.13.6 11.44c0 2.02.53 3.98 1.53 5.72L.5 24l7-1.83a11.4 11.4 0 0 0 5.46 1.39h.01c6.3 0 11.44-5.13 11.44-11.44 0-3.06-1.19-5.93-3.36-8.09"/></svg>
    </a>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const revealEls = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window) || !revealEls.length) {
        revealEls.forEach((el) => el.classList.add('is-visible'));
        return;
    }
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });
    revealEls.forEach((el) => observer.observe(el));

    const eyeOpen = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    const eyeClosed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.3 20.3 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a20.3 20.3 0 0 1-2.68 3.68M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    document.querySelectorAll('[data-pw-toggle]').forEach((btn) => {
        btn.innerHTML = eyeOpen;
        btn.addEventListener('click', () => {
            const input = btn.previousElementSibling;
            if (!input) return;
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.innerHTML = showing ? eyeOpen : eyeClosed;
            btn.setAttribute('aria-label', showing ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });
});
</script>
@endsection
