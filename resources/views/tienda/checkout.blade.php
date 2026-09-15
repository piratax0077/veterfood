@extends('layouts.app')

@section('title', 'Pago y despacho')
@section('estilos', 'css/tienda-checkout.css')

@section('content')
@php
    $pesos = fn ($monto) => '$' . number_format($monto, 0, ',', '.');
    $usuario = auth()->user();

    $telefonoCliente = trim((string) old('cliente_telefono', $usuario?->telefono));
    if (in_array(mb_strtolower($telefonoCliente), ['s/i', 'sin información', 'sin informacion', 'no informado', '-'], true)) {
        $telefonoCliente = '';
    }

    // Direcciones guardadas del cliente (la principal primero)
    $direcciones = $usuario?->tieneRol('cliente', 'dueno_mascota')
        ? $usuario->direcciones->sortByDesc('principal')->values()
        : collect();
    $direccionElegida = (string) old('direccion_guardada', $direcciones->first()?->id ?? 'nueva');

    $entregaTipo = old('entrega_tipo', 'despacho');

    // Medio de pago sugerido: la tarjeta predeterminada vigente o débito
    $medioElegido = old('medio_pago', $tarjetaSugerida ? 'tarjeta:' . $tarjetaSugerida->id : 'debito');

    $ahorroOutlet = $items->sum(fn ($item) => $item['producto']->en_oferta ? ($item['producto']->precio - $item['precio']) * $item['cantidad'] : 0);
@endphp

@include('tienda.partials.pasos-compra', ['paso' => 3])

@if($planExtra)
    <p class="checkout-nota"><x-icono nombre="suscripcion" /><span>Estos productos vienen del aviso de tu pedido mensual de <strong>{{ $planExtra->producto?->nombre }}</strong>.</span></p>
@endif

<form method="POST" action="{{ route('tienda.confirmar') }}" class="checkout" data-checkout data-keep-open="1"
      data-url-ciudades="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}"
      data-url-vouchers="{{ url('/api/vouchers/available') }}"
      data-email="{{ $usuario?->email }}"
      data-subtotal="{{ $subtotal }}" data-envio="{{ $costoEnvio }}" data-ahorro="{{ $ahorroOutlet }}">
    @csrf

    <div class="checkout-layout">
        <div class="checkout-principal wizard" data-wizard>

            {{-- Paso: Entrega --}}
            <section class="wizard-paso" data-titulo="Entrega">
                @guest
                    <div class="checkout-sesion">
                        <span class="checkout-sesion-icono" aria-hidden="true"><x-icono nombre="usuario" /></span>
                        <p><strong>¿Ya tienes cuenta?</strong> Inicia sesión para usar tus direcciones y tarjetas guardadas, o sigue como invitado.</p>
                        <a class="btn btn-secondary" href="{{ route('inicio', ['desde' => 'tienda']) }}#login" data-modal-abrir="modal-iniciar-sesion">Iniciar sesión</a>
                    </div>
                @endguest

                <div class="panel-card checkout-bloque">
                    <h2 class="checkout-titulo"><span class="checkout-numero">1</span>¿Cómo quieres recibir tu compra?</h2>
                    <div class="checkout-opciones" role="radiogroup" aria-label="Forma de entrega">
                        <label class="checkout-opcion">
                            <input type="radio" name="entrega_tipo" value="despacho" @checked($entregaTipo === 'despacho')>
                            <span class="checkout-opcion-caja">
                                <span class="checkout-opcion-icono"><x-icono nombre="seguimiento" /></span>
                                <span><strong>Despacho a domicilio</strong><small>{{ $costoEnvio ? 'Envío ' . $pesos($costoEnvio) . ' · gratis sobre $50.000' : 'Envío gratis' }}</small></span>
                            </span>
                        </label>
                        <label class="checkout-opcion">
                            <input type="radio" name="entrega_tipo" value="retiro" @checked($entregaTipo === 'retiro')>
                            <span class="checkout-opcion-caja">
                                <span class="checkout-opcion-icono"><x-icono nombre="tienda" /></span>
                                <span><strong>Retiro en tienda</strong><small>Sin costo · te avisamos cuando esté listo</small></span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="panel-card checkout-bloque" data-solo-despacho>
                    <div class="checkout-bloque-cabecera">
                        <h2 class="checkout-titulo"><span class="checkout-numero">2</span>¿Dónde lo enviamos?</h2>
                        @if($direcciones->isNotEmpty())
                            <button type="button" class="retiro-enlace" data-direcciones-toggle aria-controls="checkout-lista-direcciones" aria-expanded="false">
                                Elegir otra dirección
                                <svg class="retiro-chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                            </button>
                        @endif
                    </div>

                    @if($direcciones->isNotEmpty())
                        {{-- Vista cerrada: solo la dirección elegida --}}
                        <div class="direccion-elegida" data-direccion-resumen>
                            <span class="retiro-marca" aria-hidden="true"></span>
                            <div class="direccion-elegida-datos">
                                <p class="direccion-elegida-titulo"><strong data-resumen-alias></strong><span class="direccion-favorita-chip" data-resumen-favorita hidden>Dirección favorita</span></p>
                                <p data-resumen-texto></p>
                                <p class="muted" data-resumen-referencia hidden></p>
                            </div>
                        </div>

                        <div class="direcciones-panel" id="checkout-lista-direcciones" data-direcciones-panel hidden>
                            <p class="direcciones-panel-titulo">Elige dónde quieres recibir tu compra</p>
                            <button type="button" class="retiro-enlace direcciones-panel-agregar" data-nueva-direccion>Agregar nueva dirección</button>

                            <div class="checkout-direcciones checkout-direcciones--lista" role="radiogroup" aria-label="Tus direcciones">
                                @foreach($direcciones as $direccion)
                                    @php
                                        $textoDireccion = collect([$direccion->direccion, $direccion->comuna, $direccion->region])->filter()->implode(', ') . '.';
                                        $datosEdicion = [
                                            'id' => $direccion->id,
                                            'alias' => $direccion->alias,
                                            'direccion' => $direccion->direccion,
                                            'region_id' => $direccion->region_id,
                                            'comuna_id' => $direccion->comuna_id,
                                            'referencia' => $direccion->referencia,
                                            'principal' => (bool) $direccion->principal,
                                            'dia' => $direccion->dia_preferencia,
                                            'horario' => $direccion->horario_preferencia,
                                            'pago' => $direccion->forma_pago_preferida,
                                        ];
                                    @endphp
                                    <div @class(['checkout-direccion', 'is-favorita' => $direccion->principal])>
                                        @if($direccion->principal)<span class="direccion-favorita">Dirección favorita</span>@endif
                                        <label class="checkout-direccion-elegir">
                                            <input type="radio" name="direccion_guardada" value="{{ $direccion->id }}"
                                                   data-alias="{{ $direccion->alias }}" data-texto="{{ $textoDireccion }}" data-favorita="{{ $direccion->principal ? '1' : '0' }}"
                                                   data-direccion="{{ $direccion->direccion }}" data-region="{{ $direccion->region_id }}"
                                                   data-comuna="{{ $direccion->comuna_id }}" data-referencia="{{ $direccion->referencia }}"
                                                   data-horario="{{ $direccion->horario_preferencia }}"
                                                   @checked($direccionElegida === (string) $direccion->id)>
                                            <span class="checkout-direccion-radio" aria-hidden="true"></span>
                                            <strong>{{ $direccion->alias }}</strong>
                                        </label>
                                        <span class="checkout-direccion-acciones">
                                            <button type="button" class="checkout-direccion-accion" data-editar-direccion="{{ json_encode($datosEdicion) }}" aria-label="Editar {{ $direccion->alias }}" title="Editar"><x-icono nombre="editar" /></button>
                                            <span class="checkout-direccion-separador" aria-hidden="true"></span>
                                            <button type="submit" class="checkout-direccion-accion is-eliminar" form="borrar-direccion-{{ $direccion->id }}" aria-label="Eliminar {{ $direccion->alias }}" title="Eliminar"><x-icono nombre="eliminar" /></button>
                                        </span>
                                        <p class="checkout-direccion-texto">{{ $textoDireccion }}</p>
                                    </div>
                                @endforeach
                                <input class="checkout-direccion-nueva" type="radio" name="direccion_guardada" value="nueva" aria-label="Dirección nueva" tabindex="-1" @checked($direccionElegida === 'nueva')>
                            </div>
                        </div>
                    @endif

                    <div class="checkout-campos" data-bloque-direccion @if($direcciones->isNotEmpty() && $direccionElegida !== 'nueva') hidden @endif>
                        <p class="checkout-aviso-campo" data-aviso-comuna hidden>Completa la región y comuna de esta dirección.</p>
                        <div class="campo campo--completo {{ $errors->has('direccion_entrega') ? 'has-error' : '' }}">
                            <label for="direccion_entrega">Dirección (calle y número)</label>
                            <input class="form-control form-control-sm" id="direccion_entrega" name="direccion_entrega" value="{{ old('direccion_entrega', $usuario?->direccion) }}" placeholder="Ej: Av. Providencia 1234, depto 56" autocomplete="street-address" required>
                            @error('direccion_entrega')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="campo {{ $errors->has('region_id') ? 'has-error' : '' }}">
                            <label for="region_id">Región</label>
                            <select class="form-control form-control-sm" id="region_id" name="region_id" required>
                                <option value="">Selecciona una región</option>
                                @foreach($regiones as $region)
                                    <option value="{{ $region->id }}" @selected((string) old('region_id', session('ubicacion_despacho.region_id')) === (string) $region->id)>{{ $region->nombre }}</option>
                                @endforeach
                            </select>
                            @error('region_id')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="campo {{ $errors->has('ciudad_id') ? 'has-error' : '' }}">
                            <label for="ciudad_id">Comuna</label>
                            <select class="form-control form-control-sm" id="ciudad_id" name="ciudad_id" data-seleccion="{{ old('ciudad_id', session('ubicacion_despacho.ciudad_id')) }}" required>
                                <option value="">Selecciona primero la región</option>
                            </select>
                            @error('ciudad_id')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="campo campo--completo">
                            <label for="direccion_referencia">Referencia (opcional)</label>
                            <input class="form-control form-control-sm" id="direccion_referencia" name="direccion_referencia" value="{{ old('direccion_referencia') }}" placeholder="Villa, condominio, portón o indicaciones para llegar">
                        </div>
                        @if($usuario?->tieneRol('cliente', 'dueno_mascota'))
                            <div class="campo campo--completo" data-guardar-direccion>
                                <label class="checkout-interruptor">
                                    <input type="checkbox" name="guardar_direccion" value="1" @checked(old('_token') ? old('guardar_direccion') : true)>
                                    <span class="checkout-interruptor-pista" aria-hidden="true"></span>
                                    <span class="checkout-interruptor-texto">
                                        <strong>Guardar esta dirección en mi cuenta</strong>
                                        <small>{{ $direcciones->isEmpty() ? 'Quedará como tu dirección principal para las próximas compras.' : 'La tendrás lista para elegirla en tu próxima compra.' }}</small>
                                    </span>
                                </label>
                            </div>
                        @endif
                    </div>

                    <a class="checkout-mapa" href="https://www.google.com/maps" target="_blank" rel="noopener" data-checkout-mapa><x-icono nombre="locacion" />Ver la dirección en el mapa</a>
                    <input type="hidden" name="georeferencia_url" value="{{ old('georeferencia_url') }}" data-checkout-georef>
                </div>

                @php
                    // Puntos de retiro de muestra mientras no estén cargados en el sistema
                    $puntosRetiro = [
                        'providencia' => ['nombre' => 'VeterFood Providencia', 'direccion' => 'Av. Providencia 2133, Providencia', 'horario' => 'Lunes a Domingo 09:30 a 20:30 hrs.'],
                        'los-andes' => ['nombre' => 'VeterFood Los Andes', 'direccion' => 'Santa Teresita 683, Los Andes', 'horario' => 'Lunes a Sábado 10:00 a 19:00 hrs.'],
                        'vina-del-mar' => ['nombre' => 'VeterFood Viña del Mar', 'direccion' => 'Av. Libertad 1150, Viña del Mar', 'horario' => 'Lunes a Domingo 10:00 a 21:00 hrs.'],
                    ];
                    $puntoElegido = array_key_exists(old('punto_retiro'), $puntosRetiro) ? old('punto_retiro') : array_key_first($puntosRetiro);
                    $retiraOtra = filled(old('retira_nombre')) || filled(old('retira_rut'));
                @endphp
                <div class="panel-card checkout-bloque" data-solo-retiro hidden>
                    <h2 class="checkout-titulo"><span class="checkout-numero">2</span>¿Dónde lo retiras?</h2>

                    <div class="retiro-card" data-retiro>
                        <div class="retiro-cabecera">
                            <span class="retiro-titulo"><x-icono nombre="tienda" />Punto de retiro</span>
                            <span class="retiro-gratis">Gratis</span>
                        </div>

                        <div class="retiro-elegido">
                            <span class="retiro-marca" aria-hidden="true"></span>
                            <div class="retiro-datos">
                                <p>Retira desde el <strong>{{ now()->addDays(2)->locale('es')->translatedFormat('j \d\e F') }}</strong></p>
                                <p><strong data-retiro-nombre>{{ $puntosRetiro[$puntoElegido]['nombre'] }}</strong>, <span data-retiro-direccion>{{ $puntosRetiro[$puntoElegido]['direccion'] }}</span></p>
                                <p class="retiro-horario" data-retiro-horario>{{ $puntosRetiro[$puntoElegido]['horario'] }}</p>
                            </div>
                        </div>

                        <div class="retiro-acciones">
                            <button type="button" class="retiro-enlace" data-retiro-toggle="retiro-otra-persona" aria-controls="retiro-otra-persona" aria-expanded="{{ $retiraOtra ? 'true' : 'false' }}">¿Retira otra persona?</button>
                            <button type="button" class="retiro-enlace" data-retiro-toggle="retiro-puntos" aria-controls="retiro-puntos" aria-expanded="false">
                                Elegir otro punto de retiro
                                <svg class="retiro-chev" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M2.5 4.5 6 8l3.5-3.5"/></svg>
                            </button>
                        </div>

                        <div class="retiro-desplegable" id="retiro-puntos" hidden>
                            <div class="retiro-puntos" role="radiogroup" aria-label="Puntos de retiro">
                                @foreach($puntosRetiro as $slugPunto => $punto)
                                    <label class="retiro-punto">
                                        <input type="radio" name="punto_retiro" value="{{ $slugPunto }}" data-nombre="{{ $punto['nombre'] }}" data-direccion="{{ $punto['direccion'] }}" data-horario="{{ $punto['horario'] }}" @checked($puntoElegido === $slugPunto)>
                                        <span class="retiro-punto-caja">
                                            <strong>{{ $punto['nombre'] }}</strong>
                                            <span>{{ $punto['direccion'] }}</span>
                                            <small>{{ $punto['horario'] }}</small>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="retiro-desplegable" id="retiro-otra-persona" @unless($retiraOtra) hidden @endunless>
                            <p class="checkout-etiqueta">Datos de quien retira</p>
                            <div class="checkout-campos">
                                <div class="campo">
                                    <label for="retira_rut">RUT</label>
                                    <input class="form-control form-control-sm" id="retira_rut" name="retira_rut" value="{{ old('retira_rut') }}" placeholder="12.345.678-9" maxlength="12" inputmode="text" autocomplete="off" data-retira-campo data-rut-retiro>
                                </div>
                                <div class="campo">
                                    <label for="retira_nombre">Nombre y apellido</label>
                                    <input class="form-control form-control-sm" id="retira_nombre" name="retira_nombre" value="{{ old('retira_nombre') }}" placeholder="Ej: María Soto" maxlength="120" autocomplete="off" data-retira-campo>
                                </div>
                                <div class="campo">
                                    <x-campo-telefono name="retira_telefono" id="retira_telefono" :value="old('retira_telefono')" label="Teléfono" :flotante="false" />
                                </div>
                            </div>
                            <p class="retiro-aviso"><x-icono nombre="usuario" />Quien retira debe presentarse con su cédula de identidad.</p>
                        </div>
                    </div>
                </div>

                <div class="panel-card checkout-bloque">
                    <h2 class="checkout-titulo"><span class="checkout-numero">3</span>Tus datos de contacto</h2>
                    <div class="checkout-campos">
                        <div class="campo {{ $errors->has('cliente_nombre') ? 'has-error' : '' }}">
                            <label for="cliente_nombre">Nombre y apellido</label>
                            <input class="form-control form-control-sm" id="cliente_nombre" name="cliente_nombre" value="{{ old('cliente_nombre', $usuario?->name) }}" autocomplete="name" required>
                            @error('cliente_nombre')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="campo">
                            <label for="cliente_email">Email</label>
                            <input class="form-control form-control-sm" type="email" id="cliente_email" name="cliente_email" value="{{ old('cliente_email', $usuario?->email) }}" autocomplete="email">
                        </div>
                        <div class="campo">
                            <x-campo-telefono name="cliente_telefono" id="cliente_telefono" :value="$telefonoCliente" label="Celular" :flotante="false" />
                        </div>
                        <div class="campo campo--completo">
                            <label for="notas_entrega">
                                <span data-texto-despacho>Indicaciones para la entrega (opcional)</span>
                                <span data-texto-retiro hidden>Comentarios para el retiro (opcional)</span>
                            </label>
                            <textarea class="form-control form-control-sm" id="notas_entrega" name="notas_entrega" rows="2"
                                      placeholder="Ej: dejar con conserjería, llamar antes de llegar"
                                      data-placeholder-despacho="Ej: dejar con conserjería, llamar antes de llegar"
                                      data-placeholder-retiro="Ej: lo retira otra persona, llamar cuando esté listo">{{ old('notas_entrega') }}</textarea>
                        </div>
                    </div>

                    @guest
                        {{-- Invitado: puede guardar sus datos y crear su cuenta en la misma compra --}}
                        <div class="checkout-registro" data-registro>
                            <label class="checkout-registro-opcion">
                                <input type="checkbox" name="crear_cuenta" value="1" @checked(old('crear_cuenta')) data-registro-check>
                                <span class="checkout-registro-caja">
                                    <span>
                                        <strong>Guardar mis datos y crear mi cuenta</strong>
                                        <small>Regístrate y obtén descuentos y beneficios especiales en tus próximas compras.</small>
                                    </span>
                                </span>
                            </label>

                            <div class="checkout-registro-detalle" data-registro-detalle @unless(old('crear_cuenta')) hidden @endunless>
                                <ul class="checkout-beneficios">
                                    <li>Descuentos exclusivos para clientes registrados</li>
                                    <li>Tus direcciones y tarjetas listas para comprar más rápido</li>
                                    <li>El detalle y seguimiento de tus pedidos en Mis compras</li>
                                </ul>

                                <p class="checkout-etiqueta">Tu contraseña</p>
                                <div class="checkout-opciones" role="radiogroup" aria-label="Contraseña de tu cuenta">
                                    <label class="checkout-opcion">
                                        <input type="radio" name="tipo_clave" value="crear" @checked(old('tipo_clave', 'crear') === 'crear')>
                                        <span class="checkout-opcion-caja">
                                            <span class="checkout-opcion-icono"><x-icono nombre="candado" /></span>
                                            <span><strong>Crearla ahora</strong><small>La usas desde hoy para entrar</small></span>
                                        </span>
                                    </label>
                                    <label class="checkout-opcion">
                                        <input type="radio" name="tipo_clave" value="temporal" @checked(old('tipo_clave') === 'temporal')>
                                        <span class="checkout-opcion-caja">
                                            <span class="checkout-opcion-icono"><x-icono nombre="correo" /></span>
                                            <span><strong>Enviarme una temporal</strong><small>Te llega al correo y la cambias al entrar</small></span>
                                        </span>
                                    </label>
                                </div>

                                <div class="checkout-campos" data-clave-crear>
                                    @foreach(['password' => 'Contraseña', 'password_confirmation' => 'Repetir contraseña'] as $campoClave => $etiquetaClave)
                                        <div class="campo">
                                            <label for="registro_{{ $campoClave }}">{{ $etiquetaClave }}</label>
                                            <div class="pass-field">
                                                <input class="form-control form-control-sm" type="password" id="registro_{{ $campoClave }}" name="{{ $campoClave }}" minlength="8" maxlength="72" autocomplete="new-password" data-clave-campo>
                                                <button type="button" class="pass-eye" data-pass-toggle="registro_{{ $campoClave }}" aria-label="Mostrar contraseña" aria-pressed="false">
                                                    <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                    <p class="checkout-ayuda campo--completo">Al menos 8 caracteres, con letras y números.</p>
                                </div>
                                <p class="checkout-medio" data-clave-temporal hidden>Al confirmar la compra te enviaremos una contraseña temporal a <strong data-clave-correo>tu correo</strong>.</p>
                            </div>
                        </div>
                    @endguest

                    <label class="checkout-check">
                        <input type="checkbox" name="acepta_comunicaciones" value="1" @checked(old('_token') ? old('acepta_comunicaciones') : true)>
                        <span>Acepto recibir ofertas, promociones, concursos y comunicaciones de VeterFood.</span>
                    </label>
                </div>
            </section>

            {{-- Paso: Pago --}}
            <section class="wizard-paso" data-titulo="Pago">
                <div class="panel-card checkout-bloque {{ $errors->has('tarjeta_id') ? 'has-error' : '' }}">
                    <h2 class="checkout-titulo"><span class="checkout-numero">4</span>¿Cómo quieres pagar?</h2>

                    @if($tarjetasPago->isNotEmpty())
                        <p class="checkout-etiqueta">Tus tarjetas guardadas</p>
                        <div class="checkout-tarjetas" role="radiogroup" aria-label="Tarjetas guardadas">
                            @foreach($tarjetasPago as $tarjeta)
                                <label @class(['checkout-tarjeta', 'is-vencida' => $tarjeta->vencida])>
                                    <input type="radio" name="medio_pago" value="tarjeta:{{ $tarjeta->id }}" data-credito="{{ $tarjeta->tipo === 'debito' ? '0' : '1' }}" data-descripcion="{{ $tarjeta->marca }} {{ $tarjeta->tipo === 'debito' ? 'débito' : 'crédito' }} terminada en {{ $tarjeta->ultimos_digitos }}" @checked($medioElegido === 'tarjeta:' . $tarjeta->id) @disabled($tarjeta->vencida)>
                                    <span class="checkout-tarjeta-caja marca-{{ \Illuminate\Support\Str::slug($tarjeta->marca) }}">
                                        <span class="checkout-tarjeta-top">
                                            <strong>{{ $tarjeta->marca }}</strong>
                                            <span class="checkout-tarjeta-tipo">{{ $tarjeta->tipo === 'debito' ? 'Débito' : 'Crédito' }}</span>
                                        </span>
                                        <span class="checkout-tarjeta-numero">•••• •••• {{ $tarjeta->ultimos_digitos }}</span>
                                        <span class="checkout-tarjeta-pie">{{ $tarjeta->vencida ? 'Vencida' : 'Vence ' . $tarjeta->vencimiento }}{{ $tarjeta->predeterminada ? ' · Predeterminada' : '' }}</span>
                                    </span>
                                </label>
                            @endforeach
                            <a class="checkout-tarjeta checkout-tarjeta--agregar" href="{{ route('cliente.panel') }}#tarjetas">
                                <span class="checkout-tarjeta-caja"><span class="checkout-mas" aria-hidden="true">+</span>Agregar tarjeta</span>
                            </a>
                        </div>
                    @endif

                    <p class="checkout-etiqueta">{{ $tarjetasPago->isNotEmpty() ? 'Otros medios de pago' : 'Medios de pago' }}</p>
                    <div class="checkout-opciones checkout-opciones--pago" role="radiogroup" aria-label="Medios de pago">
                        @foreach([
                            'debito' => ['Tarjeta de débito', 'Redcompra', 'tarjeta'],
                            'credito' => ['Tarjeta de crédito', 'Visa, Mastercard, Amex', 'tarjeta'],
                        ] as $valor => [$nombre, $detalle, $icono])
                            <label class="checkout-opcion">
                                <input type="radio" name="medio_pago" value="{{ $valor }}" data-credito="{{ $valor === 'credito' ? '1' : '0' }}" data-descripcion="{{ $nombre }}" @checked($medioElegido === $valor)>
                                <span class="checkout-opcion-caja">
                                    <span class="checkout-opcion-icono"><x-icono :nombre="$icono" /></span>
                                    <span><strong>{{ $nombre }}</strong><small>{{ $detalle }}</small></span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <p class="checkout-medio" data-medio-detalle aria-live="polite"></p>

                    <div class="checkout-cuotas" data-cuotas hidden>
                        <p class="checkout-etiqueta">¿En cuántas cuotas?</p>
                        <div class="checkout-cuotas-lista" role="radiogroup" aria-label="Número de cuotas" data-cuotas-lista></div>
                        <dl class="checkout-cuotas-detalle" aria-live="polite">
                            <div><dt>Valor cuota</dt><dd data-cuota-valor>—</dd></div>
                            <div><dt>Tasa de interés mensual</dt><dd data-cuota-tasa>—</dd></div>
                            <div><dt>CAE</dt><dd data-cuota-cae>—</dd></div>
                            <div><dt>Costo total del crédito</dt><dd data-cuota-costo>—</dd></div>
                            <div class="checkout-cuotas-total"><dt>Monto total a pagar</dt><dd data-cuota-total>—</dd></div>
                        </dl>
                        <p class="checkout-cuotas-nota">Valores referenciales. La tasa, la CAE y el costo final los informa el banco emisor de tu tarjeta al momento de pagar.</p>
                        <input type="hidden" name="cuotas" value="{{ old('cuotas', 1) }}" data-cuotas-campo>
                    </div>
                    @error('tarjeta_id')<small class="field-error">{{ $message }}</small>@enderror

                    {{-- Lo que recibe el servidor segun el medio elegido --}}
                    <input type="hidden" name="tarjeta_id" value="{{ str_starts_with($medioElegido, 'tarjeta:') ? substr($medioElegido, 8) : 'otro' }}" data-tarjeta-id>
                    <input type="hidden" name="metodo_pago" value="{{ str_starts_with($medioElegido, 'tarjeta:') ? 'tarjeta_guardada' : 'tarjeta_debito' }}" data-metodo-pago>
                </div>

                <div class="panel-card checkout-bloque">
                    <h2 class="checkout-titulo"><span class="checkout-numero">5</span>¿Tienes un voucher?</h2>
                    <div class="checkout-voucher" data-voucher>
                        <div class="checkout-voucher-fila">
                            <input class="form-control form-control-sm" name="codigo_voucher" value="{{ old('codigo_voucher') }}" placeholder="Ingresa tu código" autocomplete="off" aria-label="Código de voucher" data-voucher-campo>
                            <button type="button" class="btn checkout-voucher-boton" data-voucher-aplicar>Aplicar</button>
                        </div>
                        <div class="checkout-voucher-estado" data-voucher-estado aria-live="polite"></div>
                    </div>
                </div>

                @if($planExtra)
                    <label class="panel-card checkout-bloque checkout-plan">
                        <input type="checkbox" name="incluir_en_plan_mensual" value="1" @checked(old('incluir_en_plan_mensual'))>
                        <span>Incluir estos productos también en mi pedido mensual</span>
                    </label>
                @endif
            </section>
        </div>

        {{-- Resumen --}}
        <aside class="checkout-resumen">
            <div class="panel-card checkout-resumen-card">
                <div class="checkout-resumen-cabecera">
                    <h2>Resumen de la compra</h2>
                    <a href="{{ route('tienda.carro') }}">Editar</a>
                </div>

                <ul class="checkout-productos">
                    @foreach($items as $item)
                        <li data-producto="{{ $item['producto']->id }}" data-total="{{ $item['total'] }}">
                            <span class="checkout-producto-foto">
                                @if($item['foto'])
                                    <img src="{{ $item['foto'] }}" alt="" loading="lazy">
                                @else
                                    <x-icono nombre="mascota" />
                                @endif
                                <span class="checkout-producto-cantidad">{{ $item['cantidad'] }}</span>
                            </span>
                            <span class="checkout-producto-info">
                                <strong>{{ $item['producto']->nombre }}</strong>
                                <span>
                                    {{ $item['cantidad'] }} × {{ $pesos($item['precio']) }}
                                    @if($item['producto']->en_oferta)<s>{{ $pesos($item['producto']->precio) }}</s>@endif
                                </span>
                            </span>
                            <span class="checkout-producto-total">{{ $pesos($item['total']) }}</span>
                        </li>
                    @endforeach
                </ul>

                <dl class="checkout-lineas">
                    <div><dt>Productos ({{ $unidades }})</dt><dd>{{ $pesos($subtotal) }}</dd></div>
                    <div><dt>Envío</dt><dd data-resumen-envio>{{ $costoEnvio ? $pesos($costoEnvio) : 'Gratis' }}</dd></div>
                    <div data-resumen-voucher-linea hidden><dt>Voucher <span data-resumen-voucher-codigo></span></dt><dd class="es-descuento" data-resumen-voucher></dd></div>
                    <div class="checkout-total"><dt>Total</dt><dd data-resumen-total>{{ $pesos($total) }}</dd></div>
                </dl>

                <p class="checkout-ahorro" data-resumen-ahorro @if(!$ahorroOutlet) hidden @endif>
                    Ahorras <strong data-resumen-ahorro-monto>{{ $pesos($ahorroOutlet) }}</strong> en esta compra
                </p>

                <div class="checkout-acciones">
                    <button type="button" class="btn btn-success checkout-boton" data-wizard-siguiente>
                        Continuar al pago
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                    <button type="submit" class="btn btn-success checkout-boton" data-wizard-final>Confirmar y pagar <span data-resumen-total-boton>{{ $pesos($total) }}</span></button>
                    <button type="button" class="checkout-volver" data-wizard-anterior>← Volver a la entrega</button>
                </div>
                <p class="checkout-seguro"><x-icono nombre="candado" />Al confirmar recibirás tu número de seguimiento por correo.</p>
            </div>
        </aside>
    </div>
</form>

{{-- Editar y eliminar direcciones desde el carro (van fuera del formulario de compra) --}}
@if($direcciones->isNotEmpty())
    @foreach($direcciones as $direccion)
        <form id="borrar-direccion-{{ $direccion->id }}" method="POST" action="{{ route('cliente.direcciones.destroy', $direccion) }}" data-borrar-direccion="¿Eliminar la dirección «{{ $direccion->alias }}»?" hidden>
            @csrf
            @method('DELETE')
            <input type="hidden" name="volver" value="checkout">
        </form>
    @endforeach

    <x-modal id="modal-editar-direccion" titulo="Editar dirección" descripcion="Los cambios quedan guardados en tu cuenta.">
        <form id="form-editar-direccion" method="POST" action="{{ route('cliente.direcciones.store') }}" class="checkout-campos checkout-editar-direccion" data-form-editar-direccion data-url-ciudades="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}">
            @csrf
            <input type="hidden" name="direccion_id" value="">
            <input type="hidden" name="dia_preferencia" value="">
            <input type="hidden" name="horario_preferencia" value="">
            <input type="hidden" name="forma_pago_preferida" value="">
            <div class="campo">
                <label for="editar_alias">Nombre de la dirección</label>
                <input class="form-control form-control-sm" id="editar_alias" name="alias" maxlength="120" placeholder="Ej: Casa, Trabajo" required>
            </div>
            <div class="campo">
                <label for="editar_direccion">Dirección (calle y número)</label>
                <input class="form-control form-control-sm" id="editar_direccion" name="direccion" maxlength="500" placeholder="Ej: Av. Providencia 1234, depto 56" required>
            </div>
            <div class="campo">
                <label for="editar_region">Región</label>
                <select class="form-control form-control-sm" id="editar_region" name="region_id" data-select-buscador required>
                    <option value="">Selecciona una región</option>
                    @foreach($regiones as $region)
                        <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                    @endforeach
                </select>
            </div>
            <div class="campo">
                <label for="editar_comuna">Comuna</label>
                <select class="form-control form-control-sm" id="editar_comuna" name="comuna_id" data-select-buscador required disabled>
                    <option value="">Selecciona primero la región</option>
                </select>
            </div>
            <div class="campo campo--completo">
                <label for="editar_referencia">Referencia (opcional)</label>
                <input class="form-control form-control-sm" id="editar_referencia" name="referencia" maxlength="500" placeholder="Villa, condominio, portón o indicaciones para llegar">
            </div>
            <div class="campo campo--completo">
                <label class="checkout-interruptor">
                    <input type="checkbox" name="principal" value="1">
                    <span class="checkout-interruptor-pista" aria-hidden="true"></span>
                    <span class="checkout-interruptor-texto">
                        <strong>Dirección favorita</strong>
                        <small>Vendrá elegida por defecto en tus próximas compras.</small>
                    </span>
                </label>
            </div>
        </form>
        <x-slot:pie>
            <button type="button" class="btn btn-secondary" data-modal-cerrar><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
            <button type="submit" class="btn btn-success" form="form-editar-direccion"><x-icono nombre="guardar" class="isdi-izq" />Guardar cambios</button>
        </x-slot:pie>
    </x-modal>
@endif

<script src="{{ asset('js/tienda-checkout.js') }}?v={{ @filemtime(public_path('js/tienda-checkout.js')) }}" defer></script>
@endsection
