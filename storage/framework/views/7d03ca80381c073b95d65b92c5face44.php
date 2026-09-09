<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo $__env->yieldContent('title', 'Comercializadora Alimentos'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#f6f8fb;--panel:#fff;--ink:#172033;--muted:#657083;--line:#dfe5ed;--primary:#166534;--accent:#2563eb;--danger:#b91c1c}
        *{box-sizing:border-box} body{margin:0;background:var(--bg);color:var(--ink);font-family:'Nunito','Segoe UI',Arial,sans-serif}
        a{color:var(--accent);text-decoration:none}.nav{background:#fff;border-bottom:1px solid var(--line);position:sticky;top:0;z-index:5}
        .nav-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;max-width:1240px;margin:auto;padding:12px 18px}
        .brand{font-weight:800;color:var(--primary)}.links{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.links a,.link-button{font-weight:700;color:#334155;background:none;border:0;padding:0;cursor:pointer;font-size:16px}.links .vet-sdi-return{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border:1px solid #a7e0cc;border-radius:100px;background:#e3f7ee;color:#0f8f68;font-weight:700;font-size:14px;transition:color .15s ease,border-color .15s ease,background .15s ease}.links .vet-sdi-return:hover{color:#0a7554;border-color:#8ed4bb;background:#d5f2e4}
        main{max-width:1240px;margin:auto;padding:24px 18px}.grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}.card{background:var(--panel);border:1px solid var(--line);border-radius:8px;padding:18px;box-shadow:0 8px 18px rgba(15,23,42,.05)}
        .col-3{grid-column:span 3}.col-4{grid-column:span 4}.col-5{grid-column:span 5}.col-7{grid-column:span 7}.col-8{grid-column:span 8}.col-12{grid-column:span 12}
        h1,h2,h3{margin-top:0}.muted{color:var(--muted)}.row{display:flex;gap:10px;align-items:center;flex-wrap:wrap}.between{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap}
        .info-tip{display:inline-flex;align-items:center;justify-content:center;position:relative;width:22px;height:22px;margin-left:8px;border:2px solid #111827;border-radius:999px;color:#111827;font-size:14px;font-weight:900;font-style:italic;line-height:1;cursor:help;background:#fff}
        .info-tip[data-tip]:hover:after,.info-tip[data-tip]:focus:after{content:attr(data-tip);position:absolute;right:0;top:30px;z-index:20;width:min(360px,80vw);padding:12px 14px;border-radius:8px;background:#111827;color:#fff;font-size:13px;font-style:normal;font-weight:700;line-height:1.35;box-shadow:0 10px 24px rgba(15,23,42,.22)}
        .field-help{display:block;margin-top:5px;color:var(--muted);font-size:13px;line-height:1.35}
        input,select,textarea{width:100%;border:1px solid var(--line);border-radius:8px;padding:10px;min-height:42px}label{display:block;font-weight:700;margin:10px 0 6px}
        div:has(>.floating-label-activo-sm+.form-control){position:relative;padding-top:10px;min-width:0}
        .floating-label-activo-sm,
        .field .floating-label-activo-sm,
        .field-sm .floating-label-activo-sm,
        .field-md .floating-label-activo-sm,
        .field-lg .floating-label-activo-sm,
        .field-xl .floating-label-activo-sm{display:block!important;position:static;z-index:auto;margin:14px 0 5px!important;padding:0!important;background:transparent;color:var(--line)!important;font-size:16px!important;font-weight:800!important;line-height:1.2!important;letter-spacing:0}
        div:has(>.floating-label-activo-sm+.form-control)>.floating-label-activo-sm{display:inline-flex!important;align-items:center;position:absolute;z-index:2;top:1px;left:10px;margin:0!important;padding:0 6px!important;background:#fff;color:#334155!important;line-height:1.15!important;white-space:nowrap;max-width:calc(100% - 20px);overflow:hidden;text-overflow:ellipsis}
        input.form-control.form-control-sm,
        select.form-control.form-control-sm,
        textarea.form-control.form-control-sm,
        .field input.form-control.form-control-sm,
        .field select.form-control.form-control-sm,
        .field textarea.form-control.form-control-sm,
        .field-sm input.form-control.form-control-sm,
        .field-sm select.form-control.form-control-sm,
        .field-md input.form-control.form-control-sm,
        .field-lg input.form-control.form-control-sm,
        .field-xl textarea.form-control.form-control-sm{display:block!important;width:100%!important;min-height:34px!important;border:1px solid #cbd5e1!important;border-radius:7px!important;background:#fff!important;color:#172033!important;padding:8px 10px 7px!important;font-size:14px!important;line-height:1.2!important;box-shadow:none!important;outline:none!important}
        textarea.form-control.form-control-sm{min-height:36px!important;resize:vertical}
        select.form-control.form-control-sm{padding-right:28px!important}
        .form-control.form-control-sm::placeholder{color:#94a3b8!important}
        .form-control.form-control-sm:focus{border-color:#2bab82!important;box-shadow:0 0 0 2px rgba(43,171,130,.10)!important}
        .form-grid{row-gap:14px!important}
        .filter-grid{row-gap:14px!important}
        .form-collapse-toggle{margin:8px 0 12px;min-height:36px;min-width:132px;padding:8px 12px;border-radius:7px;background:#e5e7eb!important;color:#0f172a!important;font-size:14px}
        .form-collapse-toggle.is-open{background:#2563eb!important;color:#fff!important}
        .auto-collapsed-form{display:none!important}
        .form-collapsed-card{position:relative}
        button,.btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:80px;background:var(--accent);color:#fff;font-family:inherit;font-size:inherit;font-weight:800;padding:12px 18px;min-height:45px;min-width:122px;line-height:1.2;text-align:center;white-space:normal;cursor:pointer}
        .link-button{min-width:0;min-height:0;padding:0;line-height:1.2}
        .btn-secondary{background:#e5e7eb;color:#111827}.btn-success{background:var(--primary)}.badge{display:inline-block;border-radius:999px;background:#e0f2fe;color:#075985;font-weight:800;font-size:12px;padding:5px 10px}
        .desktop-return{max-width:1240px;margin:0 auto 14px;display:flex;justify-content:flex-start}.desktop-return .btn{min-height:40px;padding:9px 14px;background:#e5e7eb;color:#111827}
        .actions{display:flex;gap:8px;align-items:center;flex-wrap:wrap}.actions form{margin:0;display:inline-flex}.edit-btn,.inactive-btn,.active-btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:6px!important;color:#fff!important;font-weight:900;font-size:14px;line-height:1.2;min-width:84px;min-height:42px!important;padding:10px 16px!important;box-shadow:none;cursor:pointer}.edit-btn{background:#f97316!important}.inactive-btn{background:#dc3545!important}.active-btn{background:#198754!important}
        .compact-actions{display:flex!important;gap:6px!important;align-items:center!important;flex-wrap:nowrap!important}
        .compact-actions .action-btn,
        .action-btn{display:inline-flex!important;align-items:center!important;justify-content:center!important;min-width:auto!important;width:auto!important;height:30px!important;min-height:30px!important;border:1px solid #dbe3ee!important;border-radius:7px!important;padding:0 10px!important;background:#f8fafc!important;color:#0f172a!important;font-size:12px!important;font-weight:900!important;line-height:1!important;text-decoration:none!important;box-shadow:none!important}
        .action-btn:hover{background:#e5e7eb!important}
        .action-save{color:#1d4ed8!important;border-color:#bfdbfe!important;background:#eff6ff!important}
        .action-edit{color:#c2410c!important;border-color:#fed7aa!important;background:#fff7ed!important}
        .action-pdf{color:#b91c1c!important;border-color:#fecaca!important;background:#fef2f2!important}
        table{width:100%;border-collapse:collapse}th,td{padding:10px;border-bottom:1px solid var(--line);text-align:left;vertical-align:top}.alert{padding:12px;border-radius:8px;background:#dcfce7;color:#14532d;margin-bottom:14px}
        /* Sistema visual VeterChile: fluido, accesible y responsivo. */
        :root{
            --bg:#f2f7f8;--panel:#fff;--ink:#12313b;--muted:#607780;--line:#d7e4e7;
            --primary:#087f8c;--primary-dark:#075d68;--accent:#1098a7;--danger:#c2414a;
            --vet-soft:#e8f6f7;--vet-navy:#123f4b;--vet-green:#03715b;--vet-green-dark:#025443;--vet-orange:#f39200;--radius:13px;--shadow:0 2px 6px rgba(18,63,75,.05),0 16px 40px rgba(18,63,75,.13)
        }
        html{min-width:320px;scroll-behavior:smooth}
        body{min-height:100vh;background:linear-gradient(180deg,#edf6f7 0,#f7fafb 240px,#f2f7f8 100%);font-family:'Nunito',"Segoe UI",Roboto,Arial,sans-serif;line-height:1.5}
        .nav{background:rgba(255,255,255,.96);border-bottom:1px solid rgba(8,127,140,.17);box-shadow:0 4px 18px rgba(18,63,75,.06);backdrop-filter:blur(12px)}
        .nav-inner{width:100%;max-width:none;padding:13px clamp(16px,2.4vw,40px)}
        .brand{display:inline-flex;align-items:center;gap:10px;color:var(--vet-navy);font-size:18px;letter-spacing:-.02em}
        .brand img{height:40px;width:auto;max-width:100%;display:block}
        .brand:before{content:"V";display:none;place-items:center;width:36px;height:36px;border-radius:11px;background:linear-gradient(135deg,var(--primary),#16b7b1);color:#fff;font-size:18px;box-shadow:0 6px 15px rgba(8,127,140,.24)}
        .links{gap:8px}.links a,.link-button{padding:9px 12px;border-radius:9px;color:#181818;transition:.18s ease}
        .links a:hover,.link-button:hover{background:var(--vet-soft);color:var(--primary-dark)}
        main{width:100%;max-width:none;min-height:calc(100vh - 64px);margin:0;padding:clamp(18px,2.3vw,36px) clamp(14px,2.4vw,40px) 120px}
        main>*{max-width:none}.desktop-return{max-width:none;margin-bottom:18px}
        .card,.classic-card,.panel-card,.card-panel,.api-card,.form-card,.filter-card,
        .admin-menu-card,.admin-comm-card,.summary-card,.stat-card,.metric-card,.chart-card,
        .feature-card,.pay-card,.checkout-card,.plan-card,.offer-card,.module-card,
        .institution-card,.inbox-card,.driver-card,.sales-card,.request-card,.tutor-card,
        .benefits-card,.auth-card,.survey-card,.voucher-user-card,.pets-list-card,.pets-form-card{border:0!important;border-radius:var(--radius)!important;box-shadow:var(--shadow)!important;background:#fff!important}
        h1,h2,h3{color:var(--vet-navy);letter-spacing:-.025em}h1{font-size:clamp(1.65rem,2.5vw,2.35rem)}
        input,select,textarea,.form-control{max-width:100%;border-color:#bfd2d7!important;border-radius:10px!important;background:#fff!important;transition:border-color .18s,box-shadow .18s}
        input:focus,select:focus,textarea:focus,.form-control:focus{border-color:var(--accent)!important;box-shadow:0 0 0 3px rgba(16,152,167,.14)!important;outline:0}
        label,.floating-label-activo-sm{color:#294b54!important}
        button,.btn{border-radius:80px;background:linear-gradient(135deg,var(--primary),var(--accent));box-shadow:0 5px 13px rgba(8,127,140,.18);transition:transform .18s,box-shadow .18s}
        button:hover,.btn:hover{transform:translateY(-1px);box-shadow:0 8px 18px rgba(8,127,140,.23)}
        .btn-secondary{background:#e7eef0;color:#244751;box-shadow:none}.btn-success{background:linear-gradient(135deg,#087f67,#10a37f)}
        .btn-orange{background:var(--vet-orange);color:#fff;border-radius:100px;box-shadow:0 5px 13px rgba(243,146,0,.28)}
        .btn-orange:hover{background:var(--vet-orange);color:#fff;box-shadow:0 8px 18px rgba(243,146,0,.34)}
        .btn-orange-outline{background:#fff;color:var(--vet-orange);border:2px solid var(--vet-orange);border-radius:100px;box-shadow:none}
        .btn-orange-outline:hover{background:rgba(243,146,0,.08);color:var(--vet-orange);box-shadow:none}
        .grid,.form-grid,.filter-grid,.client-form-grid{width:100%}
        table{min-width:720px}table thead{background:#edf7f8}th{color:var(--vet-navy);font-size:13px;letter-spacing:.02em}tbody tr:hover{background:#f6fbfc}
        main :is(.table-wrap,.table-responsive,.table-scroll,.responsive-table-shell){width:100%;max-width:100%;overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:10px}
        .responsive-table-shell:focus{outline:3px solid rgba(16,152,167,.2);outline-offset:2px}
        .alert{border:1px solid #afe0d2;border-radius:11px;background:#e9f8f2}
        @media(max-width:900px){
            .col-3,.col-4,.col-5,.col-7,.col-8{grid-column:span 12}
            .nav-inner{align-items:stretch;flex-direction:column;padding:11px 14px}.links{width:100%;overflow-x:auto;flex-wrap:nowrap;padding-bottom:3px}.links a,.link-button{white-space:nowrap}
            main{padding:18px 14px 92px}.card,.classic-card,.panel-card,.card-panel,.api-card,.form-card,.filter-card{padding:18px!important}
            main form :is(.form-grid,.filter-grid,.client-form-grid){grid-template-columns:repeat(2,minmax(0,1fr))!important}
            main form :is(.span-3,.span-4,.span-6,.field,.field-sm,.field-md,.field-lg){grid-column:span 1!important}
            main form :is(.span-12,.field-xl,.form-divider,.map-panel){grid-column:1/-1!important}
            .between{align-items:flex-start}.actions{width:100%}
        }
        @media(max-width:600px){
            main{padding-inline:10px}.brand{font-size:16px}.brand:before{width:32px;height:32px}.brand img{height:28px}
            .card,.classic-card,.panel-card,.card-panel,.api-card,.form-card,.filter-card{padding:15px!important;border-radius:13px!important}
            main form :is(.form-grid,.filter-grid,.client-form-grid){grid-template-columns:1fr!important}
            main form :is(.span-3,.span-4,.span-6,.span-12,.field,.field-sm,.field-md,.field-lg,.field-xl,.form-divider,.map-panel){grid-column:1/-1!important}
            .actions,.form-actions,.inline-actions{display:grid!important;grid-template-columns:1fr;width:100%}.actions>* ,.form-actions>* ,.inline-actions>*{width:100%!important}
            button,.btn{width:100%;min-width:0}.compact-actions{display:flex!important;width:auto}.compact-actions .action-btn{width:auto!important}
            table{min-width:640px}th,td{padding:9px 8px}
        }
    </style>
<?php
    $assetVersionado = function ($ruta) {
        $absoluta = public_path($ruta);
        return asset($ruta) . '?v=' . (is_file($absoluta) ? filemtime($absoluta) : '1');
    };
?>
    <link rel="stylesheet" href="<?php echo e($assetVersionado('css/iconos-sdi.css')); ?>">
    <link rel="stylesheet" href="<?php echo e($assetVersionado('css/cuenta.css')); ?>">
    <?php if(request()->routeIs('tienda.*', 'tracking.show')): ?>
        <link rel="stylesheet" href="<?php echo e($assetVersionado('css/tienda-nav.css')); ?>">
        <script src="<?php echo e($assetVersionado('js/tienda-nav.js')); ?>" defer></script>
        <script src="<?php echo e($assetVersionado('js/tienda-carro.js')); ?>" defer></script>
        <script src="<?php echo e($assetVersionado('js/tienda-catalogo.js')); ?>" defer></script>
    <?php endif; ?>
</head>
<body data-auth="<?php echo e(auth()->check() ? '1' : '0'); ?>" data-route="<?php echo e(request()->route()?->getName()); ?>">
<?php
    $enTienda = request()->routeIs('tienda.*', 'tracking.show');
    $rutaSeguimiento = auth()->check() && auth()->user()->tieneRol('cliente', 'dueno_mascota')
        ? route('cliente.panel')
        : route('inicio') . '#login';
?>
<?php if($enTienda): ?>
<div class="topbar">
    <div class="topbar-inner">
        <div class="topbar-rotador">
            <div class="topbar-pista">
                <p class="topbar-promo">&iexcl;10% dto. en tu primera compra web! Usa el c&oacute;digo <strong>VETERSDI10</strong><span class="topbar-extra"><span class="topbar-sep">&middot;</span>Inicia sesi&oacute;n antes de usarlo</span></p>
                <p class="topbar-promo">Despacho gratis sobre $50.000 en RM</p>
                <p class="topbar-promo" aria-hidden="true">&iexcl;10% dto. en tu primera compra web! Usa el c&oacute;digo <strong>VETERSDI10</strong><span class="topbar-extra"><span class="topbar-sep">&middot;</span>Inicia sesi&oacute;n antes de usarlo</span></p>
            </div>
        </div>
        <nav class="topbar-links" aria-label="Accesos rapidos de la tienda">
            <a class="topbar-seguir" href="<?php echo e($rutaSeguimiento); ?>">Seguir mi pedido</a>
        </nav>
    </div>
</div>
<?php endif; ?>
<?php if($enTienda): ?>
    <?php echo $__env->make('partials.tienda-nav', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php else: ?>
<nav class="nav">
    <div class="nav-inner">
        <a class="brand" href="<?php echo e(auth()->check() && auth()->user()->tieneRol('admin') ? route('admin.dashboard') : route('inicio')); ?>"><img src="<?php echo e(asset('images/logotipo/logo-veterfood.svg')); ?>" alt="Comercializadora Alimentos"></a>
        <div class="links">
            <?php if(auth()->guard()->check()): ?>
                <?php if(auth()->user()->tieneRol('cliente', 'dueno_mascota')): ?>
                    <a class="vet-sdi-return" href="<?php echo e(config('services.sdi_sso.vet_web_url')); ?>">&#8962; Ir a mi escritorio VET-SDI</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('cliente','dueno_mascota') && request()->routeIs('tienda.catalogo')): ?>
                    <a href="<?php echo e(route('tienda.catalogo')); ?>">Tienda</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('cliente','dueno_mascota') && request()->routeIs('tienda.carro', 'tienda.checkout')): ?>
                    <a href="<?php echo e(route('tienda.carro')); ?>">Carro</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('cliente','dueno_mascota') && request()->routeIs('cliente.*', 'encuesta.usuario', 'vouchers.usuario')): ?>
                    <?php echo $__env->make('partials.cuenta-dropdown', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('admin') && request()->routeIs('admin.*')): ?>
                    <a href="<?php echo e(route('admin.dashboard')); ?>">Administracion</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('admin','contabilidad') && request()->routeIs('contabilidad.*')): ?>
                    <a href="<?php echo e(route('contabilidad.panel')); ?>">Contabilidad</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('admin','auditor') && request()->routeIs('auditor.*')): ?>
                    <a href="<?php echo e(auth()->user()->tieneRol('auditor') ? route('auditor.vouchers.index') : route('auditor.vouchers.alertas')); ?>"><?php echo e(auth()->user()->tieneRol('auditor') ? 'Auditor Vouchers' : 'Alertas Voucher'); ?></a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('admin','central_ventas') && request()->routeIs('central.*')): ?>
                    <a href="<?php echo e(route('central.panel')); ?>">Central</a>
                <?php endif; ?>
                <?php if(auth()->user()->tieneRol('repartidor') && request()->routeIs('repartidor.*')): ?>
                    <a href="<?php echo e(route('repartidor.pedidos')); ?>">Repartidor</a>
                <?php endif; ?>
                <?php if (! (auth()->user()->tieneRol('cliente','dueno_mascota') && request()->routeIs('cliente.*', 'encuesta.usuario', 'vouchers.usuario'))): ?>
                    <form method="POST" action="<?php echo e(route('logout')); ?>"><?php echo csrf_field(); ?><button class="link-button" type="submit">Salir</button></form>
                <?php endif; ?>
            <?php else: ?>
                <?php if(request()->routeIs('inicio')): ?>
                    <a href="<?php echo e(route('tienda.catalogo')); ?>">Tienda</a>
                    <a href="#inscripcion">¡Crear cuenta!</a>
                    <a href="#login">Ingresar</a>
                <?php endif; ?>
                <?php if(request()->routeIs('tienda.catalogo')): ?>
                    <a href="<?php echo e(route('tienda.catalogo')); ?>">Tienda</a>
                <?php endif; ?>
                <?php if(request()->routeIs('tienda.carro', 'tienda.checkout')): ?>
                    <a href="<?php echo e(route('tienda.carro')); ?>">Carro</a>
                <?php endif; ?>
                <?php if(request()->routeIs('login')): ?>
                    <a href="<?php echo e(route('login')); ?>">Ingresar</a>
                <?php endif; ?>
                <?php if(!request()->routeIs('inicio','login')): ?>
                    <a href="<?php echo e(route('inicio')); ?>#login">Ingresar</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</nav>
<?php endif; ?>
<main>
    <?php if(session('ok')): ?><div class="alert"><?php echo e(session('ok')); ?></div><?php endif; ?>
    <?php if(auth()->guard()->check()): ?>
        <?php
            $desktopRoute = null;
            if (auth()->user()->tieneRol('admin')) {
                $desktopRoute = 'admin.dashboard';
            } elseif (auth()->user()->tieneRol('contabilidad')) {
                $desktopRoute = 'contabilidad.panel';
            } elseif (auth()->user()->tieneRol('central_ventas', 'secretaria', 'secretaria_veterchile', 'secretaria_clinica')) {
                $desktopRoute = 'central.panel';
            } elseif (auth()->user()->tieneRol('cliente', 'dueno_mascota')) {
                $desktopRoute = 'cliente.panel';
            } elseif (auth()->user()->tieneRol('repartidor')) {
                $desktopRoute = 'repartidor.pedidos';
            } elseif (auth()->user()->tieneRol('auditor')) {
                $desktopRoute = 'auditor.vouchers.index';
            }
            $suppressDesktopReturn = request()->routeIs('admin.*', 'contabilidad.*', 'cliente.planes.pago', 'auditor.*', 'tienda.*', 'tracking.show', 'vouchers.usuario', 'encuesta.usuario');
        ?>
        <?php if($desktopRoute && !request()->routeIs($desktopRoute) && ! $suppressDesktopReturn): ?>
            <div class="desktop-return">
                <a class="btn" href="<?php echo e(route($desktopRoute)); ?>">Volver al escritorio</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    <?php echo $__env->yieldContent('content'); ?>
</main>
<style id="veterchile-responsive-overrides">
    /* Esta capa se carga despues de los estilos locales de cada vista. */
    html,body{max-width:100%;overflow-x:clip}
    main,main>*,main section,main article,main form,main form>*,main [class*="-grid"],main [class*="-head"]{min-width:0;max-width:100%}
    img,svg,video,canvas,iframe{max-width:100%;height:auto}
    input,select,textarea{min-width:0}
    .responsive-table-shell{display:block;width:100%;max-width:100%;overflow-x:auto;overscroll-behavior-inline:contain}

    @media(max-width:900px){
        main{width:100%!important;max-width:100%!important;margin:0!important;padding:16px 12px 92px!important}
        main :is(
            .client-form-head,.local-form-head,.pets-head,.pro-head,.role-head,.voucher-head,.voucher-form-head,
            .clients-head,.local-head,.history-head,.survey-head,.accounting-head,.plans-head,.fin-head,
            .worker-head,.store-header,.invoice-head,.invoice-meta,.checkout-grid,.pay-layout,.plan-pay-head,
            .module-banner,.intro-panel,.qr-box,.integration-grid
        ){grid-template-columns:minmax(0,1fr)!important;width:100%!important}
        main :is(
            .client-form-grid,.local-form-grid,.pet-form-grid,.pro-form-grid,.role-form-grid,.voucher-grid,
            .accounting-grid,.fin-grid,.checkout-form,.form-grid,.filter-grid
        ){grid-template-columns:repeat(2,minmax(0,1fr))!important;width:100%!important}
        main :is(.summary-grid,.summary,.survey-kpis,.metric-grid,.plans-grid,.fonavet-grid,.price-grid){grid-template-columns:repeat(2,minmax(0,1fr))!important}
        main :is(.span-12,.span-8,.form-divider,.map-panel,.service-options,.tutor-card,.benefits-card,.field-xl){grid-column:1/-1!important}
        main :is(.span-6,.span-4,.span-3,.span-2,.field,.field-sm,.field-md,.field-lg){grid-column:span 1!important}
        .module-nav,.tabbar,.category-tabs{max-width:100%;overflow-x:auto;flex-wrap:nowrap!important;padding-bottom:8px!important}
        .module-nav a,.tabbar>*,.category-tabs>*{flex:0 0 auto;width:auto!important;white-space:nowrap}
        .table-tools,.table-tools form,.filters,.tools{width:100%!important;min-width:0!important;max-width:100%!important}
        .table-tools form{display:grid!important;grid-template-columns:minmax(0,1fr) auto!important}
        .table-tools input,.table-tools select{width:100%!important;min-width:0!important}
        .invoice-sheet{max-width:100%!important;padding:18px!important}
        .totals{width:100%;max-width:none}
    }

    @media(max-width:600px){
        main{padding:12px 8px 78px!important}
        main :is(
            .client-form-grid,.local-form-grid,.pet-form-grid,.pro-form-grid,.role-form-grid,.voucher-grid,
            .accounting-grid,.fin-grid,.checkout-form,.form-grid,.filter-grid,.summary-grid,.summary,
            .survey-kpis,.metric-grid,.plans-grid,.fonavet-grid,.price-grid,.price-row,.split-list,
            .service-options,.tutor-card,.benefit-list,.store-filters,.filters,.tools,.feedback-form
        ){grid-template-columns:minmax(0,1fr)!important}
        main :is(.span-12,.span-8,.span-6,.span-4,.span-3,.span-2,.field,.field-sm,.field-md,.field-lg,.field-xl,.form-divider,.map-panel){grid-column:1/-1!important}
        .table-tools form{grid-template-columns:minmax(0,1fr)!important}
        .table-tools form>*,.filter-actions>*,.pay-actions>*,.form-actions>*{width:100%!important;max-width:100%!important}
        .between,.users-head,.head-actions,.form-banner{align-items:stretch!important;flex-direction:column!important}
        .between>*,.head-actions>*,.form-actions>*,.inline-actions>*{max-width:100%}
        .qr-dialog{width:calc(100vw - 16px)!important;max-height:calc(100vh - 16px);overflow:auto;padding:16px!important}
        .qr-box img{width:min(200px,100%)!important;height:auto!important;aspect-ratio:1}
        h1{font-size:clamp(1.45rem,8vw,1.9rem)!important;overflow-wrap:anywhere}
    }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('main table').forEach((table) => {
        if (table.parentElement?.classList.contains('responsive-table-shell')) return;
        const shell = document.createElement('div');
        shell.className = 'responsive-table-shell';
        shell.setAttribute('role', 'region');
        shell.setAttribute('aria-label', 'Tabla con desplazamiento horizontal');
        shell.tabIndex = 0;
        table.parentNode.insertBefore(shell, table);
        shell.appendChild(table);
    });

    if (document.body.dataset.auth !== '1') return;

    const route = document.body.dataset.route || '';
    const routeKeepsOpen = route.includes('.create') || route.includes('.edit') || route.includes('login') || route.includes('checkout') || route.includes('plan_pago');
    if (routeKeepsOpen) return;

    const cards = document.querySelectorAll('main .panel-card, main .card-panel, main .api-card, main .form-card, main .card, main .filter-card');
    cards.forEach((card) => {
        const forms = Array.from(card.querySelectorAll(':scope > form, :scope > div > form'));
        forms.forEach((form) => {
            if (form.dataset.keepOpen === '1' || form.dataset.noCollapse === '1') return;
            if ((form.getAttribute('method') || 'GET').toUpperCase() === 'GET') return;
            if (form.closest('td') || form.closest('.actions') || form.closest('.compact-actions') || form.classList.contains('inline-form')) return;
            if (form.querySelector('input[type="password"]')) return;

            const visibleControls = form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]), select, textarea');
            if (visibleControls.length < 2) return;

            const heading = card.querySelector(':scope > h2, :scope > h3, :scope > .between h2, :scope > .between h3');
            const title = heading ? heading.textContent.trim().replace(/\s+/g, ' ') : 'formulario';
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'form-collapse-toggle';
            button.textContent = 'Abrir formulario';
            button.setAttribute('aria-expanded', 'false');

            form.style.setProperty('display', 'none', 'important');
            form.classList.add('auto-collapsed-form');
            card.classList.add('form-collapsed-card');
            form.setAttribute('aria-label', title);
            form.parentNode.insertBefore(button, form);

            button.addEventListener('click', () => {
                const wasClosed = form.classList.contains('auto-collapsed-form');
                form.classList.toggle('auto-collapsed-form');
                const isVisible = wasClosed;
                form.style.setProperty('display', isVisible ? '' : 'none', isVisible ? '' : 'important');
                button.classList.toggle('is-open', isVisible);
                button.setAttribute('aria-expanded', isVisible ? 'true' : 'false');
                button.textContent = isVisible ? 'Ocultar formulario' : 'Abrir formulario';
            });
        });
    });
});
</script>
</body>
</html>
<?php /**PATH C:\wamp64\www\veterfood\resources\views/layouts/app.blade.php ENDPATH**/ ?>