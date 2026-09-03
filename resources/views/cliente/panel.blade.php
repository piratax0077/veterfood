@extends('layouts.app')

@section('title', 'Mi cuenta')

@section('content')
<style>
    .client-page{max-width:1480px;margin:0 auto}
    .client-hero{display:grid;grid-template-columns:minmax(0,1fr) auto;gap:18px;align-items:center;margin-bottom:18px}
    .client-hero h1{font-size:34px;margin:0 0 8px;color:#06152f}
    .client-hero .quick-actions{justify-content:flex-end}
    .client-nav{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;padding:12px;background:#fff;border:1px solid #dbe3ee;border-radius:8px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .client-nav-main{display:flex;gap:10px;flex-wrap:wrap;flex:1}
    .client-nav-actions{display:flex;gap:10px;flex-wrap:wrap;margin-left:auto}
    .client-tab{border:0;border-radius:7px;background:#e5e7eb;color:#111827;font-weight:900;padding:11px 16px;min-height:42px;cursor:pointer}
    .client-tab.active{background:#2563eb;color:#fff}
    .client-tab.success{background:#15803d;color:#fff}
    .client-tab.warn{background:#f59e0b;color:#111827}
    .client-tab.promo{background:#db2777;color:#fff}
    .client-tab.plan{background:#7c3aed;color:#fff}
    .client-tab.dispatch-active{background:#f97316;color:#fff;animation:dispatchPulse 1.15s ease-in-out infinite}
    @keyframes dispatchPulse{0%,100%{box-shadow:0 0 0 0 rgba(249,115,22,.55)}50%{box-shadow:0 0 0 9px rgba(249,115,22,0);transform:translateY(-1px)}}
    .client-section{display:none}
    .client-section.active{display:block}
.summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
    .summary-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:16px;box-shadow:0 3px 8px rgba(15,23,42,.06)}
    .summary-card strong{display:block;font-size:26px;color:#06152f}
    .summary-card span{display:block;color:#64748b;margin-top:4px}
    .section-layout{display:grid;grid-template-columns:minmax(360px,.75fr) minmax(0,1fr);gap:16px;align-items:start}
    .pets-layout{display:grid;grid-template-columns:1fr;gap:16px}
    .pets-form-card{width:100%}
    .pets-list-card{width:100%}
    .wide-section-layout{display:grid;grid-template-columns:1fr;gap:16px}
    .panel-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:22px;box-shadow:0 3px 8px rgba(15,23,42,.07)}
    .panel-card h2{font-size:26px;color:#06152f;margin-bottom:12px}
    .list-card{display:grid;gap:12px}
    .item-row{border:1px solid #e2e8f0;border-radius:8px;padding:14px;background:#f8fafc}
    .item-row strong{color:#06152f}
    .item-photo{width:72px;height:72px;object-fit:cover;border-radius:8px;margin:8px 0}
    .table-scroll{overflow:auto}
    .quick-actions{display:flex;gap:10px;flex-wrap:wrap}
    .form-actions{margin-top:12px}
    .compact-form{display:grid;grid-template-columns:repeat(12,1fr);gap:8px 10px}
    .compact-form>div{position:relative;padding-top:8px}
    .compact-form label{margin:2px 0 4px;font-size:13px;line-height:1.2}
    .compact-form input,.compact-form select,.compact-form textarea{min-height:38px;padding:8px;font-size:14px}
    .compact-form textarea{min-height:74px}
    .compact-form .check-row{display:flex;align-items:center;gap:8px;min-height:38px;padding:8px 0 0;font-weight:800;color:#172033}
    .compact-form .check-row input{width:auto;min-height:auto}
    .compact-plan-form{display:grid;grid-template-columns:repeat(12,1fr);gap:8px 10px}
    .compact-plan-form>div{position:relative;padding-top:8px}
    .compact-plan-form label{margin:2px 0 4px;font-size:13px;line-height:1.2}
    .compact-plan-form input,.compact-plan-form select{min-height:38px;padding:8px;font-size:14px}
    .client-page .compact-form .floating-label-activo-sm,
    .client-page .compact-plan-form .floating-label-activo-sm{position:static!important;display:block!important;background:transparent!important;color:#1d4ed8!important;padding:0!important;margin:0 0 4px!important;font-size:13px!important;font-weight:900!important}
    .client-page .compact-form>div,
    .client-page .compact-plan-form>div{padding-top:0!important}
    .compact-plan-form .plan-actions{grid-column:span 12;margin-top:4px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .span-2{grid-column:span 2}
    .span-compact-check{grid-column:span 4;display:flex;align-items:end;padding-bottom:8px}
    .offers-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .offer-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;box-shadow:0 3px 8px rgba(15,23,42,.07);display:grid;gap:12px}
    .offer-card h2{font-size:22px;margin:0;color:#06152f}
    .offer-card p{margin:0;color:#64748b;line-height:1.4}
    .offer-list{display:grid;gap:10px;margin:0;padding:0;list-style:none}
    .offer-list li{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border-top:1px solid #e2e8f0;padding-top:10px}
    .offer-list strong{color:#06152f}
    .offer-list li.has-voucher{position:relative;margin:0 -8px;padding:38px 10px 12px;border:2px solid #ec4899;border-radius:10px;background:linear-gradient(135deg,#fff1f7 0%,#fff 72%);box-shadow:0 5px 14px rgba(236,72,153,.15)}
    .product-voucher-badge{position:absolute;top:8px;left:10px;display:inline-flex;align-items:center;gap:6px;background:#db2777;color:#fff;border-radius:999px;padding:5px 10px;font-size:12px;font-weight:900;letter-spacing:.02em}
    .voucher-code{display:block;margin-top:4px;color:#be185d;font-size:12px;font-weight:800}
    .old-offer-price{color:#94a3b8;font-size:12px;text-decoration:line-through;text-align:right}
    .discounted-offer-price{color:#be185d!important;font-size:19px!important}
    .offer-price{font-weight:900;color:#166534;white-space:nowrap}
    .offer-badge{display:inline-flex;width:max-content;border-radius:999px;background:#fce7f3;color:#9d174d;font-size:12px;font-weight:900;padding:5px 9px}
    .offer-side{display:grid;gap:8px;justify-items:end}
    .extra-btn{min-width:118px;min-height:34px;padding:8px 10px;border-radius:6px;background:#15803d;font-size:13px}
    .section-title-row{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:12px}
    .section-title-row h2{margin-bottom:6px}
    .plan-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    .plan-choice{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;box-shadow:0 3px 8px rgba(15,23,42,.07);display:grid;gap:12px}
    .plan-choice h2{font-size:22px;margin:0;color:#06152f}
    .plan-choice p{margin:0;color:#64748b;line-height:1.45}
    .plan-badge{display:inline-flex;width:max-content;border-radius:999px;background:#ede9fe;color:#5b21b6;font-size:12px;font-weight:900;padding:5px 9px}
    .plan-price{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .price-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:12px}
    .price-box span{display:block;color:#64748b;font-size:12px;font-weight:900;text-transform:uppercase}
    .price-box strong{display:block;color:#06152f;font-size:22px;margin-top:4px}
    .plan-includes{display:flex;gap:8px;flex-wrap:wrap;margin:0;padding:0;list-style:none}
    .plan-includes li{background:#ecfdf5;color:#14532d;border-radius:999px;padding:7px 10px;font-weight:800;font-size:13px}
    .notice-list{display:grid;gap:10px;margin-bottom:18px}
    .notice-item{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;display:flex;justify-content:space-between;gap:12px;align-items:center}
    .notice-item strong{color:#1e3a8a}
    .payment-register{display:none;margin-top:8px}.payment-register.is-visible{display:inline-flex}
    .tracking-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,.8fr);gap:16px;align-items:start}
    .tracking-map{min-height:330px;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#64748b;font-weight:900}
    .tracking-map iframe{width:100%;height:360px;border:0}
    .tracking-timeline{display:grid;gap:10px;margin-top:14px}
    .tracking-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin:0 0 16px}.tracking-step{padding:10px 6px;border-radius:8px;background:#e5e7eb;color:#64748b;text-align:center;font-size:12px;font-weight:900}.tracking-step.done{background:#ccfbf1;color:#115e59}.tracking-step.current{background:#f97316;color:#fff}
    .tracking-event{border-left:4px solid #2563eb;background:#f8fafc;border-radius:8px;padding:10px 12px}
    .driver-card{display:grid;gap:10px}.driver-photo{width:100%;max-height:170px;object-fit:cover;border-radius:8px;border:1px solid #dbe3ee;background:#f8fafc}
    .vehicle-line{display:grid;grid-template-columns:110px 1fr;gap:8px;border-bottom:1px solid #e2e8f0;padding-bottom:7px}
    @media(max-width:950px){.client-hero,.section-layout,.summary-grid,.offers-grid,.plan-grid,.tracking-layout{grid-template-columns:1fr}.client-tab,.client-hero .btn,.client-nav-actions .btn{width:100%}.quick-actions a{width:100%}.client-nav-main,.client-nav-actions{width:100%;margin-left:0}.plan-price{grid-template-columns:1fr}.section-title-row{display:grid}.span-12,.span-8,.span-6,.span-4,.span-3,.span-2,.span-compact-check,.compact-plan-form .plan-actions{grid-column:span 12}.tracking-steps{grid-template-columns:1fr 1fr}}
</style>

<div class="client-page">
    @php
        $pedidoDespacho = $user->pedidos->whereNotIn('estado', ['entregado', 'cancelado'])->sortByDesc('created_at')->first();
        $estadosDespacho = ['listo_despacho', 'reparto_asignado', 'en_camino', 'asignado', 'en_ruta'];
    @endphp
    <div class="client-hero">
        <div>
            <h1>Clientes y mascotas</h1>
            <p class="muted">Administra mascotas, direcciones, pedidos recurrentes y productos adicionales desde secciones separadas.</p>
        </div>
        <div class="quick-actions">
            <a class="btn" href="{{ route('tienda.catalogo') }}">Ir a tienda</a>
        </div>
    </div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

@if($notificaciones->isNotEmpty())
    <div class="notice-list">
        @foreach($notificaciones as $notificacion)
            <div class="notice-item">
                <span>
                    <strong>{{ $notificacion->titulo }}</strong><br>
                    <span class="muted">{{ $notificacion->mensaje }}</span>
                </span>
                @if($notificacion->url)
                    <a class="btn btn-success" href="{{ $notificacion->url }}">Agregar extras</a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<div class="summary-grid">
    <div class="summary-card"><strong>{{ $user->mascotas->count() }}</strong><span>Mascotas inscritas</span></div>
    <div class="summary-card"><strong>{{ $user->direcciones->count() }}</strong><span>Direcciones guardadas</span></div>
    <div class="summary-card"><strong>{{ $user->planesPedido->count() }}</strong><span>Pedidos recurrentes</span></div>
    <div class="summary-card"><strong>{{ $vouchersPlan->count() }}</strong><span>Vouchers disponibles</span></div>
</div>

<nav class="client-nav" aria-label="Navegacion cuenta cliente">
    <div class="client-nav-main">
        <button class="client-tab active" type="button" data-client-tab="resumen">Resumen</button>
        <button class="client-tab plan" type="button" data-client-tab="mi-plan">Mi plan</button>
        <button class="client-tab success" type="button" data-client-tab="mascotas">Mascotas</button>
        <button class="client-tab" type="button" data-client-tab="direcciones">Direcciones</button>
        <button class="client-tab warn" type="button" data-client-tab="pedido">Pedido recurrente</button>
        <button class="client-tab promo" type="button" data-client-tab="ofertas">Ofertas</button>
        <button class="client-tab promo {{ $pedidoDespacho && in_array($pedidoDespacho->estado, $estadosDespacho, true) ? 'dispatch-active' : '' }}" type="button" data-client-tab="tracking">{{ $pedidoDespacho && in_array($pedidoDespacho->estado, $estadosDespacho, true) ? '● Pedido en despacho' : 'Ver tracking' }}</button>
    </div>
</nav>

<section class="client-section active" id="cliente-resumen">
    <div class="panel-card">
        <h2>Mis pedidos recurrentes</h2>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Producto</th><th>Mascota</th><th>Voucher</th><th>Frecuencia</th><th>Proxima entrega</th><th>Direccion</th></tr></thead>
                <tbody>
                    @forelse($user->planesPedido as $plan)
                        <tr>
                            <td>{{ $plan->producto->nombre }}</td>
                            <td>{{ $plan->mascota?->nombre ?? 'General' }}</td>
                            <td>{{ $plan->voucher?->codigo ?? 'Sin voucher' }}<br><span class="muted">{{ $plan->voucher?->titulo }}</span></td>
                            <td>{{ $plan->frecuencia }} x {{ $plan->cantidad }}</td>
                            <td>{{ $plan->proxima_entrega->format('d-m-Y') }}</td>
                            <td>{{ $plan->direccion_entrega }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Aun no tienes pedidos recurrentes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card" style="margin-top:16px">
        <div class="section-title-row">
            <div>
                <h2>Productos adicionales</h2>
                <p class="muted">Medicamentos, juguetes, utensilios y otros productos se agregan al carro junto con el pedido base.</p>
            </div>
            <a class="btn btn-success" href="{{ route('tienda.catalogo', ['categoria' => 'adicional']) }}">Agregar adicionales</a>
        </div>
        <div class="quick-actions">
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'medicamento']) }}">Medicamentos</a>
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Juguetes</a>
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">Utensilios</a>
        </div>
        <hr>
        <div class="offers-grid">
            @foreach($productos->whereIn('categoria', ['medicamento','juguete','utensilio'])->take(6) as $producto)
                <div class="item-row">
                    <strong>{{ $producto->nombre }}</strong>
                    <br><span class="muted">{{ $producto->categoria }} {{ $producto->marca }}</span>
                    <br><span class="offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

<section class="client-section" id="cliente-mi-plan">
    @php
        $planActivoComercial = collect($planesDisponibles)->firstWhere('slug', $user->plan_preferido);
        $planesMejora = collect($planesDisponibles)->reject(fn($plan) => $plan['slug'] === ($planActivoComercial['slug'] ?? null));
    @endphp
    @if(session('plan_pago'))
        <div class="alert">
            Pago aprobado: {{ session('plan_pago.plan') }} · referencia {{ session('plan_pago.referencia') }} · inicial ${{ number_format(session('plan_pago.monto_inicial'), 0, ',', '.') }}.
        </div>
    @endif
    @if($planActivoComercial)
        <div class="section-layout">
            <div class="panel-card">
                <span class="plan-badge">{{ $planActivoComercial['etiqueta'] }}</span>
                <h2>Mi plan actual</h2>
                <h3>{{ $planActivoComercial['nombre'] }}</h3>
                <p class="muted">{{ $planActivoComercial['descripcion'] }}</p>
                <div class="plan-price">
                    <div class="price-box">
                        <span>Pagado al contratar</span>
                        <strong>${{ number_format($planActivoComercial['valor_inicial'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="price-box">
                        <span>Cargo mensual</span>
                        <strong>${{ number_format($planActivoComercial['valor_mensual'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <ul class="plan-includes">
                    @foreach($planActivoComercial['incluye'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="quick-actions" style="margin-top:14px">
                    <button class="btn" type="button" data-client-tab="pedido">Configurar pedido recurrente</button>
                    <button class="btn btn-success" type="button" data-client-tab="ofertas">Ver beneficios</button>
                </div>
            </div>
            <div class="panel-card">
                <h2>Mejorar plan</h2>
                <p class="muted">Puedes cambiar a un plan superior o complementar con otro beneficio. El boton te lleva a la pasarela de pago del plan seleccionado.</p>
                <div class="list-card">
                    @foreach($planesMejora->take(3) as $planMejora)
                        <div class="item-row">
                            <strong>{{ $planMejora['nombre'] }}</strong>
                            <br><span class="muted">{{ $planMejora['descripcion'] }}</span>
                            <br><strong>${{ number_format($planMejora['valor_inicial'], 0, ',', '.') }}</strong>
                            <span class="muted"> inicio · ${{ number_format($planMejora['valor_mensual'], 0, ',', '.') }} mensual</span>
                            <br><a class="btn btn-success" style="margin-top:10px" href="{{ route('cliente.planes.pago', $planMejora['slug']) }}">Mejorar y pagar</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="panel-card" style="margin-bottom:16px">
            <h2>Aun no tienes un plan activo</h2>
            <p class="muted">Elige una alternativa y luego crea tu primer pedido recurrente. Los planes permiten alimento automatico, vouchers, QR, historial y servicios programados.</p>
        </div>
        <div class="plan-grid">
            @foreach($planesDisponibles as $planDisponible)
                <article class="plan-choice">
                    <span class="plan-badge">{{ $planDisponible['etiqueta'] }}</span>
                    <div>
                        <h2>{{ $planDisponible['nombre'] }}</h2>
                        <p>{{ $planDisponible['descripcion'] }}</p>
                    </div>
                    <div class="plan-price">
                        <div class="price-box">
                            <span>Inicio</span>
                            <strong>${{ number_format($planDisponible['valor_inicial'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="price-box">
                            <span>Mensual</span>
                            <strong>${{ number_format($planDisponible['valor_mensual'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <ul class="plan-includes">
                        @foreach($planDisponible['incluye'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="btn btn-success" href="{{ route('cliente.planes.pago', $planDisponible['slug']) }}">Contratar y pagar</a>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="client-section" id="cliente-mascotas">
    <div class="pets-layout">
        <div class="panel-card pets-form-card">
            <h2>Inscribir / editar mascota</h2>
            <form method="POST" enctype="multipart/form-data" action="{{ route('cliente.mascotas.store') }}">
                @csrf
                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm">Accion</label>
                        <select class="form-control form-control-sm" name="mascota_id" id="mascota_id">
                            <option value="">Agregar nueva mascota</option>
                            @foreach($user->mascotas as $mascota)
                                <option value="{{ $mascota->id }}"
                                    data-nombre="{{ $mascota->nombre }}"
                                    data-especie="{{ $mascota->especie }}"
                                    data-raza="{{ $mascota->raza }}"
                                    data-sexo="{{ $mascota->sexo }}"
                                    data-color="{{ $mascota->color }}"
                                    data-peso="{{ $mascota->peso_kg }}"
                                    data-fecha="{{ optional($mascota->fecha_nacimiento)->toDateString() }}"
                                    data-chip="{{ $mascota->numero_chip }}"
                                    data-esterilizado="{{ $mascota->esterilizado ? '1' : '0' }}"
                                    data-alergias="{{ $mascota->alergias }}"
                                    data-observaciones="{{ $mascota->observaciones }}">{{ $mascota->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6"><label class="floating-label-activo-sm">Nombre mascota</label><input class="form-control form-control-sm" name="nombre" id="mascota_nombre" placeholder="Ej: Max, Luna, Pelusa" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Especie</label>
                        <select class="form-control form-control-sm" name="especie" id="mascota_especie">
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Raza</label><input class="form-control form-control-sm" name="raza" id="mascota_raza" placeholder="Ej: Mestizo, Poodle"></div>
                    <div class="span-2">
                        <label class="floating-label-activo-sm">Sexo</label>
                        <select class="form-control form-control-sm" name="sexo" id="mascota_sexo">
                            <option value="">Seleccionar</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                            <option value="desconocido">Desconocido</option>
                        </select>
                    </div>
                    <div class="span-2"><label class="floating-label-activo-sm">Color</label><input class="form-control form-control-sm" name="color" id="mascota_color" placeholder="Ej: Café"></div>
                    <div class="span-2"><label class="floating-label-activo-sm">Peso kg</label><input class="form-control form-control-sm" type="number" name="peso_kg" id="mascota_peso" min="0" step="0.1" placeholder="Ej: 18"></div>
                    <div class="span-3"><label class="floating-label-activo-sm">Fecha nacimiento</label><input class="form-control form-control-sm" type="date" name="fecha_nacimiento" id="mascota_fecha"></div>
                    <div class="span-3"><label class="floating-label-activo-sm">Nro. chip</label><input class="form-control form-control-sm" name="numero_chip" id="mascota_chip" placeholder="Microchip si existe"></div>
                    <div class="span-6"><label class="floating-label-activo-sm">Foto mascota</label><input class="form-control form-control-sm" type="file" name="foto" accept="image/*"></div>
                    <div class="span-compact-check">
                        <label class="check-row"><input type="checkbox" name="esterilizado" id="mascota_esterilizado" value="1"> Esterilizado</label>
                    </div>
                    <div class="span-6"><label class="floating-label-activo-sm">Alergias / restricciones</label><textarea class="form-control form-control-sm" name="alergias" id="mascota_alergias" placeholder="Ej: alergia a pollo, dieta renal, medicamentos"></textarea></div>
                    <div class="span-6"><label class="floating-label-activo-sm">Observaciones de cuidado</label><textarea class="form-control form-control-sm" name="observaciones" id="mascota_observaciones" placeholder="Preferencias de alimento, conducta, cuidados especiales"></textarea></div>
                </div>
                <button class="btn-success form-actions">Guardar mascota</button>
            </form>
        </div>
        <div class="panel-card pets-list-card">
            <h2>Mascotas registradas</h2>
            <div class="list-card">
                @forelse($user->mascotas as $mascota)
                    <div class="item-row">
                        <strong>{{ $mascota->nombre }}</strong>
                        @if($mascota->foto_url)<br><img class="item-photo" src="{{ asset($mascota->foto_url) }}" alt="{{ $mascota->nombre }}">@endif
                        <br><span class="muted">{{ $mascota->especie }} {{ $mascota->raza }} {{ $mascota->sexo ? '- '.$mascota->sexo : '' }} {{ $mascota->peso_kg ? '- '.$mascota->peso_kg.' kg' : '' }}</span>
                        <br><span class="muted">Chip: {{ $mascota->numero_chip ?: 'Sin chip' }} {{ $mascota->esterilizado ? '- Esterilizado' : '' }}</span>
                        @if($mascota->alergias)<br>Alergias: {{ $mascota->alergias }}@endif
                        @if($mascota->observaciones)<br>{{ $mascota->observaciones }}@endif
                    </div>
                @empty
                    <p class="muted">Aun no tienes mascotas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="client-section" id="cliente-direcciones">
    <div class="section-layout">
        <div class="panel-card">
            <h2>Direcciones de entrega</h2>
            <form method="POST" action="{{ route('cliente.direcciones.store') }}">
                @csrf
                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm">Accion</label>
                        <select class="form-control form-control-sm" name="direccion_id" id="direccion_id">
                            <option value="">Agregar nueva direccion</option>
                            @foreach($user->direcciones as $direccion)
                                <option value="{{ $direccion->id }}"
                                    data-alias="{{ $direccion->alias }}"
                                    data-direccion="{{ $direccion->direccion }}"
                                    data-region-id="{{ $direccion->region_id }}"
                                    data-comuna-id="{{ $direccion->comuna_id }}"
                                    data-referencia="{{ $direccion->referencia }}"
                                    data-dia="{{ $direccion->dia_preferencia }}"
                                    data-horario="{{ $direccion->horario_preferencia }}"
                                    data-pago="{{ $direccion->forma_pago_preferida }}"
                                    data-principal="{{ $direccion->principal ? '1' : '0' }}">{{ $direccion->alias }} - {{ $direccion->direccion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4"><label class="floating-label-activo-sm">Nombre direccion</label><input class="form-control form-control-sm" name="alias" id="direccion_alias" value="Casa" placeholder="Ej: Casa, trabajo, parcela" required></div>
                    <div class="span-8"><label class="floating-label-activo-sm">Direccion despacho</label><input class="form-control form-control-sm" name="direccion" id="direccion_texto" value="{{ $user->direccion }}" placeholder="Calle, numero, depto o referencia principal" required></div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Región</label>
                        <select class="form-control form-control-sm" name="region_id" id="direccion_region" required>
                            <option value="">Seleccione una región</option>
                            @foreach($regionesVet as $region)
                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Comuna</label>
                        <select class="form-control form-control-sm" name="comuna_id" id="direccion_comuna" required disabled>
                            <option value="">Seleccione primero una región</option>
                        </select>
                    </div>
                    <div class="span-12"><label class="floating-label-activo-sm">Referencia entrega</label><input class="form-control form-control-sm" name="referencia" id="direccion_referencia" placeholder="Ej: conserjería, portón azul, llamar antes"></div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Dia preferido</label>
                        <select class="form-control form-control-sm" name="dia_preferencia" id="direccion_dia">
                            <option value="">Sin preferencia</option>
                            @foreach(['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia)
                                <option value="{{ $dia }}">{{ ucfirst($dia) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Horario preferido</label>
                        <select class="form-control form-control-sm" name="horario_preferencia" id="direccion_horario">
                            <option value="">Sin preferencia</option>
                            <option value="09:00 - 12:00">09:00 - 12:00</option>
                            <option value="12:00 - 15:00">12:00 - 15:00</option>
                            <option value="15:00 - 18:00">15:00 - 18:00</option>
                            <option value="18:00 - 21:00">18:00 - 21:00</option>
                        </select>
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Forma de pago</label>
                        <select class="form-control form-control-sm" name="forma_pago_preferida" id="direccion_pago">
                            <option value="">Definir al pagar</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="pago_mensual">Cargo plan mensual</option>
                        </select>
                    </div>
                    <div class="span-4">
                        <label class="check-row"><input type="checkbox" name="principal" id="direccion_principal" value="1"> Usar como direccion principal</label>
                    </div>
                    <div class="span-8">
                        <button class="btn-success form-actions">Guardar direccion</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-card">
            <h2>Direcciones guardadas</h2>
            <div class="list-card">
                @forelse($user->direcciones as $direccion)
                    <div class="item-row">
                        <strong>{{ $direccion->alias }}</strong>
                        @if($direccion->principal)<span class="badge">Principal</span>@endif
                        <br><span class="muted">{{ $direccion->direccion }}</span>
                        @if($direccion->region || $direccion->comuna)
                            <br><span class="muted">{{ collect([$direccion->comuna, $direccion->region])->filter()->implode(', ') }}</span>
                        @endif
                        @if($direccion->referencia)<br>{{ $direccion->referencia }}@endif
                        @if($direccion->dia_preferencia || $direccion->horario_preferencia || $direccion->forma_pago_preferida)
                            <br><span class="muted">
                                {{ $direccion->dia_preferencia ? 'Dia: '.ucfirst($direccion->dia_preferencia) : '' }}
                                {{ $direccion->horario_preferencia ? ' · Horario: '.$direccion->horario_preferencia : '' }}
                                {{ $direccion->forma_pago_preferida ? ' · Pago: '.str_replace('_', ' ', $direccion->forma_pago_preferida) : '' }}
                            </span>
                        @endif
                    </div>
                @empty
                    <p class="muted">Aun no tienes direcciones guardadas.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="client-section" id="cliente-pedido">
    <div class="wide-section-layout">
        <div class="panel-card">
            <h2>Pedido mensual / semanal</h2>
            <form method="POST" action="{{ route('cliente.planes.store') }}">
                @csrf
                <div class="compact-plan-form">
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Plan</label>
                        <select class="form-control form-control-sm" name="plan_id" id="plan_id">
                            <option value="">Nuevo plan</option>
                            @foreach($user->planesPedido->where('activo', true) as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->producto->nombre }} - {{ $plan->frecuencia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Mascota</label>
                        <select class="form-control form-control-sm" name="mascota_id" id="plan_mascota_id">
                            <option value="">Sin mascota especifica</option>
                            @foreach($user->mascotas as $mascota)
                                <option value="{{ $mascota->id }}">{{ $mascota->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Alimento</label>
                        <select class="form-control form-control-sm" name="producto_id" id="plan_producto_id" required>
                            @foreach($productos->where('categoria', 'alimento_mascota') as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} - ${{ number_format($producto->precio, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Frecuencia</label>
                        <select class="form-control form-control-sm" name="frecuencia" id="plan_frecuencia">
                            <option value="mensual">Mensual</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Cantidad</label><input class="form-control form-control-sm" type="number" name="cantidad" id="plan_cantidad" value="1" min="1" required></div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Voucher</label>
                        <select class="form-control form-control-sm" name="voucher_descuento_id" id="plan_voucher_id">
                            <option value="">Sin voucher</option>
                            @foreach($vouchersPlan as $voucher)
                                <option value="{{ $voucher->id }}">{{ $voucher->codigo }} - {{ $voucher->titulo }} ({{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Proxima entrega</label><input class="form-control form-control-sm" type="date" name="proxima_entrega" id="plan_proxima_entrega" value="{{ now()->addWeek()->toDateString() }}" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Forma de pago</label>
                        <select class="form-control form-control-sm" name="forma_pago" id="plan_forma_pago">
                            <option value="">Usar forma de pago de la direccion</option>
                            <option value="tarjeta">Tarjeta inscrita</option>
                            <option value="cargo_mensual">Cargo mensual automatico</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="efectivo">Efectivo al recibir</option>
                        </select>
                    </div>
                    <div class="span-8">
                        <label class="floating-label-activo-sm">Direccion de entrega</label>
                        <select class="form-control form-control-sm" name="direccion_entrega" id="plan_direccion_entrega" required>
                            <option value="{{ $user->direccion }}">{{ $user->direccion ?: 'Direccion principal' }}</option>
                            @foreach($user->direcciones as $direccion)
                                <option value="{{ $direccion->direccion }}">{{ $direccion->alias }} - {{ $direccion->direccion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4">
                        <a class="btn btn-secondary payment-register" id="inscribir_tarjeta_btn" href="{{ route('cliente.planes.pago', $planActivoComercial['slug'] ?? 'basico') }}">Inscribir tarjeta</a>
                    </div>
                    <div class="plan-actions">
                        <button class="btn-success">Guardar pedido recurrente</button>
                    </div>
                </div>
            </form>
        </div>
        <div class="panel-card">
            <h2>Pedidos recurrentes activos</h2>
            <div class="list-card">
                @forelse($user->planesPedido->where('activo', true) as $plan)
                    <div class="item-row between">
                        <span>
                        <strong>{{ $plan->producto->nombre }}</strong>
                        <br><span class="muted">{{ $plan->frecuencia }} x {{ $plan->cantidad }} · proxima {{ $plan->proxima_entrega->format('d-m-Y') }}</span>
                        <br>Mascota: {{ $plan->mascota?->nombre ?? 'General' }}
                        <br>Voucher: {{ $plan->voucher?->codigo ?? 'Sin voucher' }}
                        @if($plan->forma_pago)<br>Pago: {{ str_replace('_', ' ', $plan->forma_pago) }}@endif
                        <br><span class="muted">{{ $plan->direccion_entrega }}</span>
                        </span>
                        <span class="actions">
                            <button type="button"
                                class="edit-plan-btn"
                                data-id="{{ $plan->id }}"
                                data-mascota="{{ $plan->mascota_id }}"
                                data-producto="{{ $plan->producto_id }}"
                                data-voucher="{{ $plan->voucher_descuento_id }}"
                                data-frecuencia="{{ $plan->frecuencia }}"
                                data-cantidad="{{ $plan->cantidad }}"
                                data-proxima="{{ $plan->proxima_entrega->toDateString() }}"
                                data-direccion="{{ $plan->direccion_entrega }}"
                                data-pago="{{ $plan->forma_pago }}">Cambiar</button>
                            <form method="POST" action="{{ route('cliente.planes.anular', $plan) }}" class="inline-form">
                                @csrf
                                <button type="submit" class="btn-secondary">Anular</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <p class="muted">Aun no tienes pedidos recurrentes activos.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="client-section" id="cliente-tracking">
    @php
        $pedidoTracking = $user->pedidos
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->sortByDesc('created_at')
            ->first() ?? $user->pedidos->sortByDesc('created_at')->first();
        $ultimaUbicacion = $pedidoTracking?->tracking
            ? $pedidoTracking->tracking->whereNotNull('latitud')->whereNotNull('longitud')->sortByDesc('created_at')->first()
            : null;
        $eventosTracking = $pedidoTracking?->tracking ? $pedidoTracking->tracking->sortByDesc('created_at')->take(8) : collect();
        $repartidor = $pedidoTracking?->repartidor;
        $mapUrl = $ultimaUbicacion
            ? 'https://maps.google.com/maps?q=' . $ultimaUbicacion->latitud . ',' . $ultimaUbicacion->longitud . '&z=15&output=embed'
            : null;
    @endphp
    <div class="tracking-layout">
        <div class="panel-card">
            @php
                $secuencia = ['en_preparacion', 'listo_despacho', 'reparto_asignado', 'en_camino'];
                $estadoActual = ['preparando'=>'en_preparacion','asignado'=>'reparto_asignado','en_ruta'=>'en_camino'][$pedidoTracking?->estado] ?? $pedidoTracking?->estado;
                $posicion = array_search($estadoActual, $secuencia, true);
            @endphp
            <div class="tracking-steps">
                @foreach(['en_preparacion'=>'En preparación','listo_despacho'=>'Listo para despacho','reparto_asignado'=>'Reparto asignado','en_camino'=>'En camino'] as $estado=>$etiqueta)
                    @php $indice=array_search($estado,$secuencia,true); @endphp
                    <div class="tracking-step {{ $posicion !== false && $indice < $posicion ? 'done' : '' }} {{ $estadoActual === $estado ? 'current' : '' }}">{{ $etiqueta }}</div>
                @endforeach
            </div>
            <div class="between">
                <div>
                    <h2>Tracking del pedido</h2>
                    <p class="muted">
                        @if($pedidoTracking)
                            Pedido {{ $pedidoTracking->codigo_tracking }} · estado {{ str_replace('_', ' ', $pedidoTracking->estado) }}
                        @else
                            Aun no hay pedidos con tracking.
                        @endif
                    </p>
                </div>
                @if($pedidoTracking)
                    <a class="btn btn-success" href="{{ route('tracking.show', $pedidoTracking->codigo_tracking) }}">Abrir tracking completo</a>
                @endif
            </div>

            <div class="tracking-map">
                @if($mapUrl)
                    <iframe src="{{ $mapUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa tracking pedido"></iframe>
                @else
                    <span>Mapa pendiente: aun no hay ubicacion GPS enviada por el repartidor.</span>
                @endif
            </div>

            <div class="tracking-timeline">
                @forelse($eventosTracking as $evento)
                    <div class="tracking-event">
                        <strong>{{ ucfirst(str_replace('_', ' ', $evento->estado)) }}</strong>
                        <br><span>{{ $evento->mensaje }}</span>
                        <br><span class="muted">{{ $evento->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                @empty
                    <div class="tracking-event">
                        <strong>Sin eventos registrados</strong>
                        <br><span class="muted">Cuando central o el repartidor actualicen el pedido, apareceran los movimientos aqui.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="panel-card driver-card">
            <h2>Repartidor asignado</h2>
            @if($repartidor)
                @if($repartidor->foto_url)
                    <img class="driver-photo" src="{{ asset($repartidor->foto_url) }}" alt="{{ $repartidor->name }}">
                @endif
                <div class="vehicle-line"><strong>Nombre</strong><span>{{ $repartidor->name }}</span></div>
                <div class="vehicle-line"><strong>Telefono</strong><span>{{ $repartidor->telefono ?: 'No informado' }}</span></div>
                <div class="vehicle-line"><strong>Patente</strong><span>{{ $repartidor->vehiculo_patente ?: 'No informada' }}</span></div>
                <div class="vehicle-line"><strong>Vehiculo</strong><span>{{ trim(($repartidor->vehiculo_marca ?? '') . ' ' . ($repartidor->vehiculo_modelo ?? '')) ?: 'No informado' }}</span></div>
                @if($repartidor->vehiculo_foto_url)
                    <img class="driver-photo" src="{{ asset($repartidor->vehiculo_foto_url) }}" alt="Vehiculo {{ $repartidor->vehiculo_patente }}">
                @endif
            @else
                <p class="muted">Aun no hay repartidor asignado. Cuando central lo asigne, apareceran nombre, telefono, vehiculo y patente.</p>
            @endif
        </aside>
    </div>
</section>

<section class="client-section" id="cliente-ofertas">
    @php
        $ofertas = [
            [
                'titulo' => 'Alimentos recomendados',
                'texto' => 'Productos base para armar o complementar tu pedido recurrente.',
                'categoria' => 'alimento_mascota',
                'link' => route('tienda.catalogo', ['categoria' => 'alimento_mascota']),
                'items' => $productos->where('categoria', 'alimento_mascota')->take(4),
            ],
            [
                'titulo' => 'Farmacia y cuidados',
                'texto' => 'Antiparasitarios, suplementos, higiene y productos utiles para el cuidado diario.',
                'categoria' => 'medicamento',
                'link' => route('tienda.catalogo', ['categoria' => 'farmacia']),
                'items' => $productos->whereIn('categoria', ['medicamento', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Servicios a domicilio',
                'texto' => 'Bano, peluqueria, veterinaria movil, paseos, hotel y otros servicios programables.',
                'categoria' => 'servicio',
                'link' => route('tienda.catalogo', ['categoria' => 'servicios']),
                'items' => $productos->whereIn('categoria', ['servicio', 'hotel', 'paseo_diario', 'cementerio'])->take(4),
            ],
            [
                'titulo' => 'Juguetes y entretencion',
                'texto' => 'Juguetes, mordedores y articulos para enriquecer la rutina de la mascota.',
                'categoria' => 'juguete',
                'link' => route('tienda.catalogo', ['categoria' => 'juguete']),
                'items' => $productos->where('categoria', 'juguete')->take(4),
            ],
            [
                'titulo' => 'Utiles para casa',
                'texto' => 'Platos, correas, dispensadores, higiene y accesorios para el dia a dia.',
                'categoria' => 'utensilio',
                'link' => route('tienda.catalogo', ['categoria' => 'utensilio']),
                'items' => $productos->whereIn('categoria', ['utensilio', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Beneficios con voucher',
                'texto' => 'Descuentos disponibles para asociar a planes, compras o servicios.',
                'categoria' => 'voucher',
                'link' => route('tienda.catalogo', ['categoria' => 'adicional']),
                'items' => collect(),
            ],
        ];
    @endphp
    <div class="offers-grid">
        @foreach($ofertas as $oferta)
            <article class="offer-card">
                <span class="offer-badge">{{ $oferta['categoria'] }}</span>
                <div>
                    <h2>{{ $oferta['titulo'] }}</h2>
                    <p>{{ $oferta['texto'] }}</p>
                </div>

                @if($oferta['categoria'] === 'voucher')
                    <ul class="offer-list">
                        @forelse($vouchersPlan->take(4) as $voucher)
                            <li>
                                <span><strong>{{ $voucher->titulo }}</strong><br><span class="muted">{{ $voucher->codigo }}</span></span>
                                <span class="offer-price">{{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <li><span class="muted">No hay vouchers disponibles por ahora.</span></li>
                        @endforelse
                    </ul>
                @else
                    <ul class="offer-list">
                        @forelse($oferta['items'] as $producto)
                            @php
                                $voucherProducto = $vouchersPlan
                                    ->filter(fn ($voucher) => ((int) $voucher->producto_id === (int) $producto->id || (!$voucher->producto_id && $voucher->categoria_aplicable === $producto->categoria)) && $producto->precio >= $voucher->monto_minimo)
                                    ->sortByDesc(function ($voucher) use ($producto) {
                                        return $voucher->tipo_descuento === 'porcentaje'
                                            ? (int) round($producto->precio * $voucher->valor / 100)
                                            : min($voucher->valor, $producto->precio);
                                    })
                                    ->first();
                                $descuentoProducto = $voucherProducto
                                    ? ($voucherProducto->tipo_descuento === 'porcentaje' ? (int) round($producto->precio * $voucherProducto->valor / 100) : min($voucherProducto->valor, $producto->precio))
                                    : 0;
                                $precioVoucher = max(0, $producto->precio - $descuentoProducto);
                            @endphp
                            <li @class(['has-voucher' => $voucherProducto])>
                                @if($voucherProducto)
                                    <span class="product-voucher-badge">% CON VOUCHER · {{ $voucherProducto->tipo_descuento === 'porcentaje' ? $voucherProducto->valor . '%' : '$' . number_format($voucherProducto->valor, 0, ',', '.') }}</span>
                                @endif
                                <span><strong>{{ $producto->nombre }}</strong><br><span class="muted">{{ $producto->marca }} {{ $producto->peso }}</span>@if($voucherProducto)<span class="voucher-code">Codigo: {{ $voucherProducto->codigo }}</span>@endif</span>
                                <span class="offer-side">
                                    @if($voucherProducto)
                                        <span class="old-offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                        <span class="offer-price discounted-offer-price">${{ number_format($precioVoucher, 0, ',', '.') }}</span>
                                    @else
                                        <span class="offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                    @endif
                                    <form method="POST" action="{{ route('tienda.agregar', $producto) }}">
                                        @csrf
                                        <input type="hidden" name="cantidad" value="1">
                                        <button class="extra-btn">Agregar extra</button>
                                    </form>
                                </span>
                            </li>
                        @empty
                            <li><span class="muted">Sin productos activos en esta categoria.</span></li>
                        @endforelse
                    </ul>
                @endif

                <a class="btn" href="{{ $oferta['link'] }}">Ver en tienda</a>
            </article>
        @endforeach
    </div>
</section>

</div>

<script>
    function bindSelectLoader(selectId, mapping) {
        var select = document.getElementById(selectId);
        if (!select) return;

        select.addEventListener('change', function () {
            var option = select.options[select.selectedIndex];
            Object.keys(mapping).forEach(function (key) {
                var field = document.getElementById(mapping[key]);
                if (!field) return;
                if (field.type === 'checkbox') {
                    field.checked = option.dataset[key] === '1';
                    return;
                }
                field.value = option.dataset[key] || '';
            });
        });
    }

    function showClientSection(name) {
        document.querySelectorAll('.client-section').forEach(function (section) {
            section.classList.toggle('active', section.id === 'cliente-' + name);
        });
        document.querySelectorAll('.client-tab').forEach(function (tab) {
            tab.classList.toggle('active', tab.dataset.clientTab === name);
        });
        window.location.hash = name;
    }

    document.querySelectorAll('.client-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            showClientSection(tab.dataset.clientTab);
        });
    });

    document.querySelectorAll('button[data-client-tab]:not(.client-tab)').forEach(function (button) {
        button.addEventListener('click', function () {
            showClientSection(button.dataset.clientTab);
        });
    });

    var initialTab = window.location.hash.replace('#', '');
    if (['resumen', 'mi-plan', 'mascotas', 'direcciones', 'pedido', 'ofertas', 'tracking'].indexOf(initialTab) >= 0) {
        showClientSection(initialTab);
    }

    var planFormaPago = document.getElementById('plan_forma_pago');
    var inscribirTarjetaBtn = document.getElementById('inscribir_tarjeta_btn');
    if (planFormaPago && inscribirTarjetaBtn) {
        var toggleTarjeta = function () {
            inscribirTarjetaBtn.classList.toggle('is-visible', ['tarjeta', 'cargo_mensual'].indexOf(planFormaPago.value) >= 0);
        };
        planFormaPago.addEventListener('change', toggleTarjeta);
        toggleTarjeta();
    }

    document.querySelectorAll('.edit-plan-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            var setValue = function (id, value) {
                var field = document.getElementById(id);
                if (field) field.value = value || '';
            };

            setValue('plan_id', button.dataset.id);
            setValue('plan_mascota_id', button.dataset.mascota);
            setValue('plan_producto_id', button.dataset.producto);
            setValue('plan_voucher_id', button.dataset.voucher);
            setValue('plan_frecuencia', button.dataset.frecuencia);
            setValue('plan_cantidad', button.dataset.cantidad);
            setValue('plan_proxima_entrega', button.dataset.proxima);
            setValue('plan_direccion_entrega', button.dataset.direccion);
            setValue('plan_forma_pago', button.dataset.pago);
            if (planFormaPago) planFormaPago.dispatchEvent(new Event('change'));
            document.querySelector('#cliente-pedido form')?.scrollIntoView({behavior: 'smooth', block: 'start'});
        });
    });

    bindSelectLoader('mascota_id', {
        nombre: 'mascota_nombre',
        especie: 'mascota_especie',
        raza: 'mascota_raza',
        sexo: 'mascota_sexo',
        color: 'mascota_color',
        peso: 'mascota_peso',
        fecha: 'mascota_fecha',
        chip: 'mascota_chip',
        esterilizado: 'mascota_esterilizado',
        alergias: 'mascota_alergias',
        observaciones: 'mascota_observaciones'
    });

    var comunasVet = @json($comunasVet);
    var regionDireccion = document.getElementById('direccion_region');
    var comunaDireccion = document.getElementById('direccion_comuna');

    function cargarComunasDireccion(regionId, comunaSeleccionada) {
        if (!comunaDireccion) return;
        comunaDireccion.innerHTML = '<option value="">Seleccione una comuna</option>';
        (comunasVet || []).filter(function (comuna) {
            return String(comuna.id_region) === String(regionId);
        }).forEach(function (comuna) {
            var option = document.createElement('option');
            option.value = comuna.id;
            option.textContent = comuna.nombre;
            option.selected = String(comuna.id) === String(comunaSeleccionada || '');
            comunaDireccion.appendChild(option);
        });
        comunaDireccion.disabled = !regionId;
    }

    if (regionDireccion) {
        regionDireccion.addEventListener('change', function () {
            cargarComunasDireccion(regionDireccion.value, '');
        });
    }

    var direccionSelect = document.getElementById('direccion_id');
    if (direccionSelect) {
        direccionSelect.addEventListener('change', function () {
            var option = direccionSelect.options[direccionSelect.selectedIndex];
            var valores = {
                direccion_alias: option.dataset.alias || 'Casa',
                direccion_texto: option.dataset.direccion || '',
                direccion_referencia: option.dataset.referencia || '',
                direccion_dia: option.dataset.dia || '',
                direccion_horario: option.dataset.horario || '',
                direccion_pago: option.dataset.pago || ''
            };
            Object.keys(valores).forEach(function (id) {
                var campo = document.getElementById(id);
                if (campo) campo.value = valores[id];
            });
            document.getElementById('direccion_principal').checked = option.dataset.principal === '1';
            regionDireccion.value = option.dataset.regionId || '';
            cargarComunasDireccion(regionDireccion.value, option.dataset.comunaId || '');
        });
    }

    cargarComunasDireccion(regionDireccion ? regionDireccion.value : '', '');
</script>
@endsection
