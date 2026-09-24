@extends('layouts.app')

@section('title', 'Anular compra')
@section('estilos', 'css/cliente-panel.css, css/cliente-anular-compra.css')

@section('content')
@php
    $motivos = [
        'No recibí mi compra en el tiempo estimado',
        'Producto dañado o en mal estado',
        'Producto distinto al solicitado',
        'Producto vencido o próximo a vencer',
        'Me arrepentí de la compra',
        'Otro motivo',
    ];
    $mediosPago = [
        'credito' => 'Tarjeta de crédito',
        'debito' => 'Tarjeta de débito (Redcompra)',
        'prepago' => 'Tarjeta de prepago (MACH, Tenpo, Mercado Pago, etc.)',
        'transferencia' => 'Transferencia bancaria',
    ];
    $bancos = [
        'BancoEstado', 'BCI', 'Santander', 'Banco de Chile', 'Scotiabank', 'Itaú',
        'BICE', 'Security', 'Falabella', 'Ripley', 'Consorcio', 'Internacional',
        'Coopeuch', 'Tenpo', 'Mercado Pago', 'MACH', 'Copec Pay',
    ];
    $tiposCuenta = [
        'corriente' => 'Cuenta corriente',
        'vista' => 'Cuenta vista',
        'rut' => 'Cuenta RUT',
        'ahorro' => 'Cuenta de ahorro',
    ];
    $iconoInfo = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><circle cx="12" cy="12" r="9"/><path d="M12 11v5.5"/><path d="M12 7.6v.1"/></svg>';
    $iconoFoto = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 8.5A2.5 2.5 0 0 1 6.5 6h1.8l1.4-2h4.6l1.4 2h1.8A2.5 2.5 0 0 1 20 8.5v9a2.5 2.5 0 0 1-2.5 2.5h-11A2.5 2.5 0 0 1 4 17.5z"/><circle cx="12" cy="13" r="3.6"/></svg>';
    $iconoListo = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>';
@endphp
<div class="client-page">
    <x-encabezado-pagina class="anular-encabezado" titulo="Anular compra" descripcion="Pedido {{ $pedido->codigo_tracking }} · {{ $pedido->created_at->format('d/m/Y') }}" :volver="route('cliente.panel') . '#compras'" volver-texto="Volver a mis compras" />

    <div class="panel-card anular-compra-card">
        <div class="aviso-caja">
            {!! $iconoInfo !!}
            <p>El plazo de devolución de tu dinero depende del medio de pago que usaste en la compra.</p>
        </div>

        <form data-anular-compra data-validar data-volver="{{ route('cliente.panel') }}#compras" data-total="{{ $pedido->total }}" data-ultimos-digitos="{{ $ultimosDigitos }}">
            {{-- Paso 1: formulario --}}
            <div data-paso="formulario">
                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm" for="anular_motivo">1. Motivo de la devolución</label>
                        <select class="form-control form-control-sm" id="anular_motivo" name="motivo" required data-msg="Elige el motivo de la devolución.">
                            <option value="" disabled selected>Elige una opción</option>
                            @foreach($motivos as $motivo)
                                <option value="{{ $motivo }}">{{ $motivo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="span-12">
                        <label class="floating-label-activo-sm" for="anular_medio_pago">2. Medio de pago</label>
                        <select class="form-control form-control-sm" id="anular_medio_pago" name="medio_pago" required data-msg="Elige el medio de pago." data-anular-medio>
                            @foreach($mediosPago as $valor => $texto)
                                <option value="{{ $valor }}" @selected($medioPago === $valor)>{{ $texto }}</option>
                            @endforeach
                        </select>
                        <small class="field-hint">Detectamos el medio con el que pagaste. Cámbialo si no es el correcto.</small>
                    </div>
                </div>

                <div class="anular-destino">
                    <span class="floating-label-activo-sm">3. Destino del reembolso</span>

                    <div data-destino="credito" hidden>
                        <div class="anular-tarjeta-detectada">
                            <x-icono nombre="tarjeta" />
                            <span>Tarjeta terminada en **** {{ $ultimosDigitos }}</span>
                        </div>
                        <div class="aviso-caja">
                            {!! $iconoInfo !!}
                            <p>La reversa se verá reflejada en tu estado de cuenta en un plazo de 5 a 10 días hábiles, según tu banco emisor. Si ya pagaste la cuota, el monto quedará como saldo a favor.</p>
                        </div>
                    </div>

                    <div data-destino="cuenta" hidden>
                        <p class="anular-pregunta">¿Dónde quieres recibir tu reembolso?</p>
                        <div class="anular-radios" role="radiogroup" aria-label="Destino del reembolso">
                            <label class="anular-radio">
                                <input type="radio" name="destino_cuenta" value="misma" required data-anular-destino-cuenta data-msg="Elige dónde quieres recibir tu reembolso.">
                                <span><strong>En la misma cuenta con la que pagué</strong><small>Se hace reversa automática, sin pedir más datos.</small></span>
                            </label>
                            <label class="anular-radio">
                                <input type="radio" name="destino_cuenta" value="otra" required data-anular-destino-cuenta>
                                <span><strong>En otra cuenta bancaria</strong><small>Vas a ingresar los datos de esa cuenta.</small></span>
                            </label>
                        </div>
                        <div class="aviso-caja">
                            {!! $iconoInfo !!}
                            <p>La reversa se hará en un plazo de 3 a 5 días hábiles contados desde el día siguiente a tu solicitud de nota de crédito.</p>
                        </div>
                    </div>

                    <div data-destino="banco" hidden>
                        <p class="anular-pregunta">Datos bancarios</p>
                        <div class="compact-form">
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_titular">Nombre completo del titular</label>
                                <input class="form-control form-control-sm" id="anular_titular" name="titular" maxlength="120" required>
                            </div>
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_rut">RUT del titular</label>
                                <input class="form-control form-control-sm" id="anular_rut" name="rut_titular" placeholder="12.345.678-9" maxlength="12" data-rut required>
                            </div>
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_banco">Banco</label>
                                <select class="form-control form-control-sm" id="anular_banco" name="banco" required data-msg="Selecciona tu banco.">
                                    <option value="" disabled selected>Selecciona tu banco</option>
                                    @foreach($bancos as $banco)
                                        <option value="{{ $banco }}">{{ $banco }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_tipo_cuenta">Tipo de cuenta</label>
                                <select class="form-control form-control-sm" id="anular_tipo_cuenta" name="tipo_cuenta" required data-msg="Selecciona el tipo de cuenta.">
                                    <option value="" disabled selected>Selecciona el tipo</option>
                                    @foreach($tiposCuenta as $valor => $texto)
                                        <option value="{{ $valor }}">{{ $texto }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_cuenta">N° de cuenta</label>
                                <input class="form-control form-control-sm" id="anular_cuenta" name="numero_cuenta" inputmode="numeric" pattern="[0-9]+" title="Solo números." maxlength="20" required>
                            </div>
                            <div class="span-6">
                                <label class="floating-label-activo-sm" for="anular_correo">Correo para el comprobante</label>
                                <input class="form-control form-control-sm" type="email" id="anular_correo" name="correo_comprobante" value="{{ auth()->user()->email }}" maxlength="150" required>
                            </div>
                        </div>
                        <label class="anular-check">
                            <input type="checkbox" name="declaracion_datos" value="1" required data-msg="Marca esta casilla para continuar.">
                            <span>Declaro que los datos bancarios son correctos y autorizo el depósito en esta cuenta.</span>
                        </label>
                    </div>
                </div>

                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm" for="anular_comentario">4. Comentario</label>
                        <textarea class="form-control form-control-sm" id="anular_comentario" name="comentario" rows="4" maxlength="200" placeholder="Cuéntanos más (opcional)" data-contar></textarea>
                        <span class="anular-contador" data-contador>0 / 200</span>
                    </div>
                </div>

                <div class="anular-fotos">
                    <span class="floating-label-activo-sm">Adjuntar fotos (opcional)</span>
                    <small class="field-hint">Recomendado si el producto llegó dañado.</small>
                    <label class="anular-fotos-zona" for="anular_fotos_input">
                        {!! $iconoFoto !!}
                        <strong>Arrastra tus fotos aquí</strong>
                        <span>o <u>búscalas en tu equipo</u></span>
                    </label>
                    <input class="anular-fotos-input" type="file" id="anular_fotos_input" accept="image/*" multiple hidden>
                    <div class="anular-fotos-lista" data-anular-fotos-lista hidden></div>
                </div>

                <div class="edit-actions">
                    <a class="btn btn-cancelar" href="{{ route('cliente.panel') }}#compras"><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</a>
                    <button type="button" class="btn btn-peligro" data-anular-revisar><x-icono nombre="siguiente" class="isdi-izq" />Revisar solicitud</button>
                </div>
            </div>

            {{-- Paso 2: confirmación --}}
            <div data-paso="resumen" hidden>
                <h3 class="anular-resumen-titulo">5. Confirma tu solicitud</h3>
                <dl class="anular-resumen">
                    <div><dt>Motivo</dt><dd data-resumen-motivo>—</dd></div>
                    <div><dt>Medio de pago</dt><dd data-resumen-medio>—</dd></div>
                    <div><dt>Destino del reembolso</dt><dd data-resumen-destino>—</dd></div>
                    <div class="anular-resumen-total"><dt>Monto a devolver</dt><dd data-resumen-monto>—</dd></div>
                </dl>
                <div class="edit-actions">
                    <button type="button" class="btn btn-cancelar" data-anular-volver><x-icono nombre="volver" class="isdi-izq" />Cancelar</button>
                    <button type="submit" class="btn btn-peligro"><x-icono nombre="eliminar" class="isdi-izq" />Enviar solicitud</button>
                </div>
            </div>

            {{-- Paso 3: listo --}}
            <div data-paso="final" hidden class="anular-final">
                <span class="anular-final-icono" aria-hidden="true">{!! $iconoListo !!}</span>
                <p data-final-mensaje>Solicitud recibida.</p>
                <a class="btn btn-success" href="{{ route('cliente.panel') }}#compras">Volver a mis compras</a>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('js/cliente-anular-compra.js') }}?v={{ filemtime(public_path('js/cliente-anular-compra.js')) }}" defer></script>
@endsection
