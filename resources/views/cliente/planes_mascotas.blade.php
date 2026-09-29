@extends('layouts.app')

@section('title', 'Planes para mascotas')
@section('estilos', 'css/cliente-planes-mascotas.css')

@php
    $pesos = fn ($valor) => '$' . number_format((int) $valor, 0, ',', '.');
    $erroresPlan = $errors->getBag('contratar');
    $planAbrir = session('contratar_plan');
    // Lo que necesita el script para armar el modal de contratacion
    $datosPlanes = collect($planes)->mapWithKeys(fn ($plan) => [$plan['slug'] => [
        'nombre' => $plan['nombre'],
        'tipo' => $plan['tipo'],
        'tono' => $plan['tono'],
        'icono' => $plan['icono'],
        'resumen' => $plan['resumen'],
        'titulo_precios' => $plan['titulo_precios'],
        'precios' => $plan['precios'],
    ]])->all();
    $maxTarjetas = \App\Models\TarjetaCliente::MAXIMO_POR_CLIENTE;
    $tarjetasVigentes = $tarjetas->filter(fn ($tarjeta) => !$tarjeta->vencida);
@endphp

@section('content')
<div class="pm-page">
    <a class="btn btn-success pm-volver" href="{{ route('cliente.panel') }}#mi-plan"><x-icono nombre="volver" class="isdi-izq" />Volver a Mis suscripciones</a>

    <header class="pm-hero">
        <h1>Planes para tu mascota</h1>
        <p class="muted">Seguros de salud, identidad QR y suscripciones de grooming, exclusivos para clientes registrados en VeterFood.</p>
    </header>

    <div class="pm-grid">
        @foreach($planes as $plan)
            @php $primera = $plan['precios'][0]; @endphp
            <article class="pm-card" data-pm-card data-plan="{{ $plan['slug'] }}">
                <div class="pm-card-cabecera">
                    <h2>{{ $plan['nombre'] }}</h2>
                    <span class="badge tono-{{ $plan['tono'] }}">{{ $plan['tipo'] }}</span>
                </div>
                <p class="pm-card-resumen">{{ $plan['resumen'] }}</p>

                <div class="pm-opciones">
                    <label class="floating-label-activo-sm" for="pm-opcion-{{ $plan['slug'] }}">{{ $plan['titulo_precios'] }}</label>
                    <select class="form-control form-control-sm" id="pm-opcion-{{ $plan['slug'] }}" data-sin-buscador data-pm-variante>
                        @foreach($plan['precios'] as $indice => $fila)
                            <option value="{{ $indice }}" data-precio="{{ $fila['precio'] }}" @selected($indice === 0)>{{ $fila['etiqueta'] }}{{ !empty($fila['pago_unico']) ? ' (+' . $pesos($fila['pago_unico']) . ' una vez)' : '' }}</option>
                        @endforeach
                    </select>
                </div>

                <p class="pm-card-precio"><strong data-pm-precio>{{ $pesos($primera['precio']) }}</strong><span>/ mes</span></p>

                <ul class="pm-card-destacados">
                    @foreach($plan['destacados'] as $destacado)
                        <li><x-icono nombre="activar" />{{ $destacado }}</li>
                    @endforeach
                </ul>

                <button type="button" class="pm-enlace" data-modal-abrir="modal-plan-{{ $plan['slug'] }}">Ver condiciones y cobertura</button>

                @if(in_array($plan['slug'], $contratados, true))
                    <p class="pm-card-activo"><x-icono nombre="aprobacion" />Ya lo tienes activo. Míralo en <a href="{{ route('cliente.panel') }}#mi-plan">Mis suscripciones</a>.</p>
                @endif

                <button type="button" class="btn pm-contratar" data-pm-contratar data-plan="{{ $plan['slug'] }}" data-modal-abrir="modal-contratar-plan">{{ in_array($plan['slug'], $contratados, true) ? 'Contratar otro' : 'Contratar plan' }}</button>
            </article>
        @endforeach
    </div>

    <div class="pm-extras">
        <h2>Extras y promociones</h2>
        <ul>
            <li>10% de descuento por pago semestral y 15% por pago anual.</li>
            <li>Un baño de regalo en el cumpleaños de la mascota.</li>
            <li>Plan aparte «Solo uñas»: $6.990 al mes.</li>
            <li><strong>Combo:</strong> seguro Urgencia Peluda + plan Limpio y Cuidado de grooming, con 10% de descuento en ambos.</li>
        </ul>
    </div>
</div>

{{-- Detalle de cada plan: precios exactos, cobertura y condiciones --}}
@foreach($planes as $plan)
    @php $tienePreciosConDetalle = collect($plan['precios'])->contains(fn ($fila) => !empty($fila['extra']) || !empty($fila['pago_unico'])); @endphp
    <x-modal id="modal-plan-{{ $plan['slug'] }}" :titulo="$plan['nombre']" :descripcion="$plan['resumen']" ancho="grande">
        <div class="pm-detalle">
            <h3>Precio mensual</h3>
            <div class="pm-tabla-wrap">
                <table class="pm-tabla">
                    <thead>
                        <tr>
                            <th>{{ $plan['columna_precios'] }}</th>
                            <th>Precio / mes</th>
                            @if($tienePreciosConDetalle)
                                <th>Detalle</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($plan['precios'] as $fila)
                            <tr>
                                <td>{{ $fila['etiqueta'] }}</td>
                                <td>{{ $pesos($fila['precio']) }}@if(!empty($fila['pago_unico']))<small class="pm-tabla-unico">+ {{ $pesos($fila['pago_unico']) }} una sola vez</small>@endif</td>
                                @if($tienePreciosConDetalle)
                                    <td>{{ $fila['extra'] ?? '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if(!empty($plan['nota_precio']))
                <p class="pm-nota">{{ $plan['nota_precio'] }}</p>
            @endif

            @if(!empty($plan['cobertura']))
                <h3>Qué incluye la cobertura</h3>
                <div class="pm-tabla-wrap">
                    <table class="pm-tabla">
                        <thead><tr><th>Cobertura</th><th>Detalle y tope</th></tr></thead>
                        <tbody>
                            @foreach($plan['cobertura'] as $item)
                                <tr><td>{{ $item['titulo'] }}</td><td>{{ $item['detalle'] }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if(!empty($plan['incluye']))
                <h3>Qué incluye</h3>
                <ul class="pm-lista-check">
                    @foreach($plan['incluye'] as $item)
                        <li><x-icono nombre="activar" />{{ $item }}</li>
                    @endforeach
                </ul>
            @endif

            @if(!empty($plan['condiciones']))
                <h3>Condiciones</h3>
                <ul class="pm-lista-condiciones">
                    @foreach($plan['condiciones'] as $condicion)
                        <li>{{ $condicion }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <x-slot:pie>
            <button type="button" class="btn btn-cancelar" data-modal-cerrar>Cerrar</button>
            <button type="button" class="btn btn-success" data-pm-contratar data-plan="{{ $plan['slug'] }}" data-modal-abrir="modal-contratar-plan">Contratar plan</button>
        </x-slot:pie>
    </x-modal>
@endforeach

{{-- Contratar: elegir la mascota, confirmar el medio de pago y autorizar el cobro --}}
<x-modal id="modal-contratar-plan" titulo="Contratar plan" descripcion="Tres pasos y tu plan queda activo." ancho="grande" class="modal-contratar">
    <form id="form-contratar-plan" class="pm-contrato" method="POST" action="{{ route('cliente.planes.mascotas.contratar') }}" data-contratar-form data-validar data-iconos="{{ asset('iconos-sdi') }}" @if($planAbrir) data-plan-inicial="{{ $planAbrir }}" @endif>
        @csrf
        <input type="hidden" name="plan" value="" data-contratar-plan>

        <div class="wizard" data-wizard>
            {{-- 1. El plan --}}
            <section class="wizard-paso" data-titulo="Tu plan">
                <div class="pm-contrato-plan">
                    <span class="pm-contrato-icono" data-contratar-icono></span>
                    <div>
                        <strong data-contratar-nombre></strong>
                        <small data-contratar-resumen></small>
                    </div>
                    <span class="badge" data-contratar-badge></span>
                </div>

                <p class="pm-contrato-etiqueta" data-contratar-titulo-variantes></p>
                <div class="pm-variantes" role="radiogroup" data-contratar-variantes></div>
                @error('variante', 'contratar')<small class="field-error">{{ $message }}</small>@enderror

                <div class="pm-contrato-campo">
                    <label class="floating-label-activo-sm" for="contratar-mascota">¿Para cuál de tus mascotas?</label>
                    <select class="form-control form-control-sm" id="contratar-mascota" name="mascota_id">
                        <option value="">La elijo después</option>
                        @foreach($mascotas as $mascota)
                            <option value="{{ $mascota->id }}" @selected(old('mascota_id') == $mascota->id)>{{ $mascota->nombre }}{{ $mascota->especie ? ' · ' . $mascota->especie : '' }}{{ $mascota->raza ? ' · ' . $mascota->raza : '' }}</option>
                        @endforeach
                    </select>
                    @if($mascotas->isEmpty())
                        <small class="pm-ayuda">Todavía no tienes mascotas registradas. Puedes contratar igual y asignarla después desde tu panel.</small>
                    @endif
                </div>
            </section>

            {{-- 2. Medio de pago --}}
            <section class="wizard-paso" data-titulo="Medio de pago">
                @if($tarjetasVigentes->isNotEmpty())
                    <p class="pm-contrato-etiqueta">Tus tarjetas guardadas</p>
                    <div class="pm-medios" role="radiogroup" aria-label="Tus tarjetas guardadas">
                        @foreach($tarjetas as $tarjeta)
                            <label @class(['pm-medio', 'is-vencida' => $tarjeta->vencida])>
                                <input type="radio" name="medio" value="tarjeta:{{ $tarjeta->id }}" required data-msg="Elige con qué tarjeta quieres pagar." data-contratar-medio
                                       data-descripcion="{{ $tarjeta->marca }} •••• {{ $tarjeta->ultimos_digitos }}"
                                       @checked($tarjeta->predeterminada && !$tarjeta->vencida) @disabled($tarjeta->vencida)>
                                <span class="pm-medio-caja">
                                    <span class="pm-medio-icono"><x-icono nombre="tarjeta" /></span>
                                    <span class="pm-medio-texto">
                                        <strong>{{ $tarjeta->marca }} {{ $tarjeta->tipo === 'debito' ? 'débito' : 'crédito' }}</strong>
                                        <small>•••• {{ $tarjeta->ultimos_digitos }} · {{ $tarjeta->vencida ? 'Vencida' : 'Vence ' . $tarjeta->vencimiento }}{{ $tarjeta->predeterminada ? ' · Predeterminada' : '' }}</small>
                                    </span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                <p class="pm-contrato-etiqueta">{{ $tarjetasVigentes->isNotEmpty() ? 'O paga con otra tarjeta' : 'Agrega la tarjeta para el cobro mensual' }}</p>
                <div class="pm-medios" role="radiogroup" aria-label="Otra tarjeta">
                    <label class="pm-medio">
                        <input type="radio" name="medio" value="nueva" required data-msg="Elige con qué tarjeta quieres pagar." data-contratar-medio data-nueva @disabled($tarjetas->count() >= $maxTarjetas)
                               data-descripcion="Tarjeta nueva" @checked($tarjetasVigentes->isEmpty() || $erroresPlan->hasAny(['numero_tarjeta', 'titular', 'vencimiento', 'tipo']))>
                        <span class="pm-medio-caja">
                            <span class="pm-medio-icono"><x-icono nombre="plus" /></span>
                            <span class="pm-medio-texto">
                                <strong>Usar una tarjeta nueva</strong>
                                <small>{{ $tarjetas->count() >= $maxTarjetas ? 'Ya tienes ' . $maxTarjetas . ' tarjetas guardadas: elimina una en Medios de pago' : 'La guardamos en tus medios de pago para los próximos cobros' }}</small>
                            </span>
                        </span>
                    </label>
                </div>
                @error('medio', 'contratar')<small class="field-error">{{ $message }}</small>@enderror

                <div class="pm-tarjeta-nueva" data-contratar-tarjeta hidden>
                    <p class="pm-contrato-titulo"><x-icono nombre="candado" />Datos de tu tarjeta</p>
                    <div class="pm-contrato-campos">
                        <div class="pm-campo-ancho">
                            <span class="floating-label-activo-sm">Tipo de tarjeta</span>
                            <div class="pm-tipos" role="radiogroup" aria-label="Tipo de tarjeta">
                                <label class="pm-tipo"><input type="radio" name="tipo" value="debito" required data-msg="Indica si es débito o crédito." @checked(old('tipo') === 'debito')><span>Débito<small>Redcompra</small></span></label>
                                <label class="pm-tipo"><input type="radio" name="tipo" value="credito" required @checked(old('tipo', 'credito') === 'credito')><span>Crédito<small>Visa, Mastercard, Amex</small></span></label>
                            </div>
                            @error('tipo', 'contratar')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="pm-campo-ancho">
                            <label class="floating-label-activo-sm" for="contratar-numero">Número de tarjeta</label>
                            <input class="form-control form-control-sm" id="contratar-numero" name="numero_tarjeta" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000" required data-contratar-numero>
                            @error('numero_tarjeta', 'contratar')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="pm-campo-ancho">
                            <label class="floating-label-activo-sm" for="contratar-titular">Nombre del titular</label>
                            <input class="form-control form-control-sm" id="contratar-titular" name="titular" value="{{ old('titular') }}" autocomplete="cc-name" maxlength="120" placeholder="Como aparece en la tarjeta" required>
                            @error('titular', 'contratar')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label class="floating-label-activo-sm" for="contratar-vencimiento">Vencimiento</label>
                            <input class="form-control form-control-sm" id="contratar-vencimiento" name="vencimiento" value="{{ old('vencimiento') }}" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA" required data-contratar-vencimiento>
                            @error('vencimiento', 'contratar')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div>
                            <label class="floating-label-activo-sm" for="contratar-cvv">Código de seguridad</label>
                            <input class="form-control form-control-sm" id="contratar-cvv" type="password" inputmode="numeric" maxlength="4" pattern="[0-9]{3,4}" placeholder="•••" autocomplete="cc-csc" required>
                        </div>
                    </div>
                    <p class="pm-ayuda"><x-icono nombre="candado" />Guardamos solo la marca, los últimos 4 dígitos y el vencimiento. Nunca el número completo ni el código de seguridad.</p>
                </div>
            </section>

            {{-- 3. Confirmar --}}
            <section class="wizard-paso" data-titulo="Confirmar">
                <div class="pm-resumen" data-contratar-resumen-caja></div>
                <label class="pm-autorizo">
                    <input type="checkbox" name="acepta_cargo" value="1" required data-msg="Necesitamos tu autorización para el cobro mensual.">
                    <span>Autorizo el cobro mensual automático de este plan en la tarjeta elegida. Puedo cancelarlo cuando quiera desde <strong>Mis suscripciones</strong>.</span>
                </label>
                @error('acepta_cargo', 'contratar')<small class="field-error">{{ $message }}</small>@enderror
            </section>
        </div>
    </form>

    <script type="application/json" data-contratar-datos>@json($datosPlanes)</script>

    <x-slot:pie>
        <span class="pm-pie-contador" data-wizard-contador></span>
        <button type="button" class="btn btn-cancelar" data-modal-cerrar data-wizard-solo-inicio>Cancelar</button>
        <button type="button" class="btn btn-cancelar" data-wizard-anterior hidden><x-icono nombre="volver" class="isdi-izq" />Atrás</button>
        <button type="button" class="btn btn-success" data-wizard-siguiente>Continuar<x-icono nombre="siguiente" class="isdi-der" /></button>
        <button type="submit" class="btn btn-success" form="form-contratar-plan" data-wizard-final hidden><x-icono nombre="candado" class="isdi-izq" />Pagar y activar</button>
    </x-slot:pie>
</x-modal>

<script src="{{ asset('js/cliente-planes-mascotas.js') }}?v={{ @filemtime(public_path('js/cliente-planes-mascotas.js')) }}" defer></script>
@endsection
