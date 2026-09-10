@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
<style>
    .checkout-page-title{margin:0 0 22px;line-height:1.15}
    .checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) 420px;gap:18px;align-items:start}
    .checkout-card{background:#fff;border:1px solid var(--line);border-radius:8px;padding:18px;box-shadow:0 8px 18px rgba(15,23,42,.05)}
    .checkout-form{display:grid;grid-template-columns:repeat(12,1fr);gap:14px 10px}
    .checkout-form input,.checkout-form select,.checkout-form textarea{min-height:38px;padding:8px;font-size:14px}
    .checkout-form textarea{min-height:70px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .map-box{grid-column:span 12;background:#f8fafc;border:1px solid #dbe3ee;border-radius:8px;padding:12px}
    .map-actions{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:10px}
    .map-frame{width:100%;height:220px;border:0;border-radius:8px;background:#e5e7eb}
    .pay-actions{grid-column:span 12;margin-top:6px}
    #metodo_pago_campo[hidden]{display:none}
    .pay-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:10px;margin-top:4px}
    .pay-card-option{position:relative;display:block;margin:0;cursor:pointer}
    .pay-card-option input{position:absolute;width:1px;height:1px;min-height:0;opacity:0}
    .pay-card-option span{display:flex;flex-direction:column;gap:2px;min-height:58px;padding:10px 14px;border:1.5px solid #cbd5e1;border-radius:10px;background:#fff;transition:border-color .15s ease,background .15s ease}
    .pay-card-option strong{color:#12313b;font-size:14.5px}
    .pay-card-option small{color:#607780;font-size:12.5px;font-weight:700}
    .pay-card-option input:checked+span{border-color:#10a37f;background:#e7f5f0;box-shadow:inset 0 0 0 1px #10a37f}
    .pay-card-option input:focus-visible+span{outline:2px solid #10a37f;outline-offset:2px}
    .pay-card-option.is-disabled{cursor:not-allowed;opacity:.55}
    .pay-cards-error{display:block;margin-top:6px;color:#b42318;font-size:13px;font-weight:700}
    .pay-cards-link{display:inline-block;margin-top:8px;font-size:13px;font-weight:700;color:#03715b}
    @media(max-width:950px){.checkout-grid,.checkout-form{grid-template-columns:1fr}.span-12,.span-8,.span-6,.span-4,.span-3,.map-box,.pay-actions{grid-column:span 1}}
</style>

@php
    $telefonoCliente = trim((string) old('cliente_telefono', auth()->user()?->telefono));
    if (in_array(mb_strtolower($telefonoCliente), ['s/i', 'sin información', 'sin informacion', 'no informado', '-'], true)) {
        $telefonoCliente = '';
    }
@endphp

<h1 class="checkout-page-title">Pago y despacho - Toda la tienda</h1>
@if($planExtra)
    <div class="alert">
        Estos productos vienen desde el aviso de tu pedido mensual de {{ $planExtra->producto?->nombre }}.
    </div>
@endif
<div class="checkout-grid">
    <div>
        <div class="checkout-card">
            <form method="POST" action="{{ route('tienda.confirmar') }}">
                @csrf
                <div class="checkout-form">
                    <div class="span-6"><label class="floating-label-activo-sm">Nombre</label><input class="form-control form-control-sm" name="cliente_nombre" value="{{ old('cliente_nombre', auth()->user()?->name) }}" required></div>
                    <div class="span-6"><label class="floating-label-activo-sm">Email</label><input class="form-control form-control-sm" type="email" name="cliente_email" value="{{ old('cliente_email', auth()->user()?->email) }}"></div>
                    <div class="span-4"><label for="cliente_telefono">Teléfono de contacto</label><input class="form-control form-control-sm" id="cliente_telefono" name="cliente_telefono" type="tel" value="{{ $telefonoCliente }}" placeholder="Ej.: +56 9 1234 5678" autocomplete="tel"></div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Modalidad</label>
                        <select class="form-control form-control-sm" name="entrega_tipo" id="entrega_tipo">
                            <option value="despacho">Despacho a domicilio</option>
                            <option value="retiro">Retiro en tienda</option>
                        </select>
                    </div>
                    <div class="span-4" id="metodo_pago_campo" @if($tarjetasPago->isNotEmpty() && old('tarjeta_id', $tarjetaSugerida?->id ?? 'otro') !== 'otro') hidden @endif>
                        <label class="floating-label-activo-sm">Pago</label>
                        <select class="form-control form-control-sm" name="metodo_pago">
                            <option value="simulado_local">Pago local simulado</option>
                            <option value="transferencia" @selected(old('metodo_pago') === 'transferencia')>Transferencia</option>
                            <option value="efectivo_entrega" @selected(old('metodo_pago') === 'efectivo_entrega')>Efectivo contra entrega</option>
                        </select>
                    </div>
                    @if($tarjetasPago->isNotEmpty())
                        @php $tarjetaElegida = (string) old('tarjeta_id', $tarjetaSugerida?->id ?? 'otro'); @endphp
                        <div class="span-12">
                            <span class="floating-label-activo-sm">Pagar con</span>
                            <div class="pay-cards" role="radiogroup" aria-label="Medio de pago">
                                @foreach($tarjetasPago as $tarjeta)
                                    <label class="pay-card-option {{ $tarjeta->vencida ? 'is-disabled' : '' }}">
                                        <input type="radio" name="tarjeta_id" value="{{ $tarjeta->id }}" @checked($tarjetaElegida === (string) $tarjeta->id) @disabled($tarjeta->vencida)>
                                        <span>
                                            <strong>{{ $tarjeta->marca }} •••• {{ $tarjeta->ultimos_digitos }}</strong>
                                            <small>{{ $tarjeta->tipo === 'debito' ? 'Débito' : 'Crédito' }} · vence {{ $tarjeta->vencimiento }}{{ $tarjeta->predeterminada ? ' · Predeterminada' : '' }}{{ $tarjeta->vencida ? ' · Vencida' : '' }}</small>
                                        </span>
                                    </label>
                                @endforeach
                                <label class="pay-card-option">
                                    <input type="radio" name="tarjeta_id" value="otro" @checked($tarjetaElegida === 'otro')>
                                    <span><strong>Otro medio de pago</strong><small>Transferencia o efectivo</small></span>
                                </label>
                            </div>
                            <a class="muted pay-cards-link" href="{{ route('cliente.panel') }}#tarjetas">Administrar mis tarjetas</a>
                            @error('tarjeta_id')<small class="pay-cards-error">{{ $message }}</small>@enderror
                        </div>
                    @endif
                    <div class="span-6"><label class="floating-label-activo-sm">Dirección de despacho</label><input class="form-control form-control-sm" name="direccion_entrega" id="direccion_entrega" value="{{ old('direccion_entrega', auth()->user()?->direccion) }}" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Región</label>
                        <select class="form-control form-control-sm" name="region_id" id="region_id" required>
                            <option value="">Seleccione región</option>
                            @foreach($regiones as $region)
                                <option value="{{ $region->id }}" @selected((string) old('region_id', session('ubicacion_despacho.region_id')) === (string) $region->id)>{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Ciudad / comuna</label>
                        <select class="form-control form-control-sm" name="ciudad_id" id="ciudad_id" data-selected="{{ old('ciudad_id', session('ubicacion_despacho.ciudad_id')) }}" required>
                            <option value="">Seleccione primero la región</option>
                        </select>
                    </div>
                    <div class="span-12"><label class="floating-label-activo-sm">Referencia para el reparto</label><input class="form-control form-control-sm" name="direccion_referencia" id="direccion_referencia" value="{{ old('direccion_referencia') }}" placeholder="Villa, condominio, departamento o indicaciones para llegar"></div>
                    @error('region_id')<div class="span-12 alert">{{ $message }}</div>@enderror
                    @error('ciudad_id')<div class="span-12 alert">{{ $message }}</div>@enderror
                    <div class="span-4"><label class="floating-label-activo-sm">Dia</label><input class="form-control form-control-sm" type="date" name="fecha_entrega" value="{{ old('fecha_entrega', now()->addDay()->toDateString()) }}"></div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Horario</label>
                        <select class="form-control form-control-sm" name="horario_preferencia">
                            <option value="09:00 - 12:00">09:00 - 12:00</option>
                            <option value="12:00 - 15:00">12:00 - 15:00</option>
                            <option value="15:00 - 18:00">15:00 - 18:00</option>
                            <option value="18:00 - 21:00">18:00 - 21:00</option>
                        </select>
                    </div>
                    <div class="span-4"><label class="floating-label-activo-sm">Notas</label><textarea class="form-control form-control-sm" name="notas_entrega">{{ old('notas_entrega') }}</textarea></div>
                    <input type="hidden" name="georeferencia_url" id="georeferencia_url" value="{{ old('georeferencia_url') }}">
                    @if($planExtra)
                        <div class="span-12" style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:10px">
                            <label class="row floating-label-activo-sm" style="font-weight:700;margin:0">
                                <input type="checkbox" name="incluir_en_plan_mensual" value="1" style="width:auto;min-height:auto">
                                Incluir estos extras también en mi pedido mensual
                            </label>
                        </div>
                    @endif
                    <div class="map-box">
                        <div class="map-actions">
                            <button class="btn" type="button" id="activar-mapa">Activar mapa por direccion</button>
                            <a class="btn btn-secondary" id="abrir-mapa" href="https://www.google.com/maps" target="_blank" rel="noopener">Abrir mapa</a>
                            <span class="muted">Se guarda en notas para conectar con reparto.</span>
                        </div>
                        <iframe class="map-frame" id="mapa-despacho" src=""></iframe>
                    </div>
                    <div class="pay-actions"><button class="btn-success">Confirmar pedido</button></div>
                </div>
            </form>
        </div>
    </div>
    <div>
        <div class="checkout-card">
            <h2>Resumen</h2>
            @foreach($items as $item)
                <p class="between"><span>{{ $item['cantidad'] }} x {{ $item['producto']->nombre }}<br><span class="muted">{{ $item['producto']->categoria }}</span></span><strong>${{ number_format($item['total'], 0, ',', '.') }}</strong></p>
            @endforeach
            <hr>
            <p class="between"><span>Subtotal</span><strong>${{ number_format($subtotal, 0, ',', '.') }}</strong></p>
            <p class="between"><span>Envio</span><strong>${{ number_format($costoEnvio, 0, ',', '.') }}</strong></p>
            <h2 class="between"><span>Total</span><span>${{ number_format($total, 0, ',', '.') }}</span></h2>
        </div>
    </div>
</div>
<script>
    var entregaTipo = document.getElementById('entrega_tipo');
    var direccion = document.getElementById('direccion_entrega');
    var referencia = document.getElementById('direccion_referencia');
    var region = document.getElementById('region_id');
    var ciudad = document.getElementById('ciudad_id');
    var georef = document.getElementById('georeferencia_url');
    var mapa = document.getElementById('mapa-despacho');
    var abrirMapa = document.getElementById('abrir-mapa');
    var activarMapa = document.getElementById('activar-mapa');

    function actualizarModalidad() {
        if (entregaTipo.value === 'retiro') {
            direccion.value = 'Retiro en tienda';
            direccion.setAttribute('readonly', 'readonly');
            region.disabled = true;
            ciudad.disabled = true;
            region.removeAttribute('required');
            ciudad.removeAttribute('required');
            return;
        }

        if (direccion.value === 'Retiro en tienda') {
            direccion.value = '';
        }
        direccion.removeAttribute('readonly');
        region.disabled = false;
        ciudad.disabled = false;
        region.setAttribute('required', 'required');
        ciudad.setAttribute('required', 'required');
    }

    entregaTipo.addEventListener('change', actualizarModalidad);

    var campoMetodoPago = document.getElementById('metodo_pago_campo');
    document.querySelectorAll('input[name="tarjeta_id"]').forEach(function (opcion) {
        opcion.addEventListener('change', function () {
            campoMetodoPago.hidden = opcion.value !== 'otro';
        });
    });

    function actualizarMapa() {
        var regionTexto = region.options[region.selectedIndex]?.text || '';
        var ciudadTexto = ciudad.options[ciudad.selectedIndex]?.text || '';
        var texto = [direccion.value, ciudadTexto, regionTexto, referencia.value].filter(function(valor) {
            return valor && !valor.startsWith('Seleccione');
        }).join(' ').trim();
        if (!texto || texto === 'Retiro en tienda') {
            mapa.removeAttribute('src');
            abrirMapa.setAttribute('href', 'https://www.google.com/maps');
            georef.value = '';
            return;
        }
        var query = encodeURIComponent(texto);
        var url = 'https://www.google.com/maps/search/?api=1&query=' + query;
        georef.value = url;
        abrirMapa.setAttribute('href', url);
        mapa.setAttribute('src', 'https://maps.google.com/maps?q=' + query + '&output=embed');
    }

    activarMapa.addEventListener('click', actualizarMapa);
    async function cargarCiudades(regionId, seleccionada) {
        ciudad.innerHTML = '<option value="">Cargando ciudades...</option>';
        ciudad.disabled = true;

        if (!regionId) {
            ciudad.innerHTML = '<option value="">Seleccione primero la región</option>';
            ciudad.disabled = entregaTipo.value === 'retiro';
            return;
        }

        try {
            var plantilla = @json(route('tienda.ciudades', ['region' => '__REGION__']));
            var respuesta = await fetch(plantilla.replace('__REGION__', regionId), {
                headers: {'Accept': 'application/json'}
            });
            if (!respuesta.ok) throw new Error('No fue posible cargar las ciudades');
            var ciudades = await respuesta.json();
            ciudad.innerHTML = '<option value="">Seleccione ciudad</option>';
            ciudades.forEach(function(item) {
                var opcion = document.createElement('option');
                opcion.value = item.id;
                opcion.textContent = item.nombre;
                opcion.selected = String(item.id) === String(seleccionada || '');
                ciudad.appendChild(opcion);
            });
        } catch (error) {
            ciudad.innerHTML = '<option value="">Error al cargar ciudades</option>';
            console.error(error);
        } finally {
            ciudad.disabled = entregaTipo.value === 'retiro';
        }
    }

    region.addEventListener('change', function() {
        cargarCiudades(this.value, '');
    });
    ciudad.addEventListener('change', actualizarMapa);
    if (region.value) cargarCiudades(region.value, ciudad.dataset.selected);
    actualizarModalidad();
    if (direccion.value) actualizarMapa();
</script>
@endsection
