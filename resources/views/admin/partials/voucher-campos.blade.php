{{--
    Campos del voucher en pasos (asistente). Lo usa el modal "Crear voucher" (admin/modales/nuevo-voucher).
    Recibe $datosVoucher (App\Support\FormulariosAdmin::datosVoucher). Los valores sugeridos pueden venir por
    parametros (?titulo=...) o desde el boton que abre el modal (data-modal-rellenar).
    Estilos: admin-formularios.css + wizard.css · Comportamiento: public/js/form-voucher.js
--}}
@php
    $conError = fn (string $campo) => $errors->has($campo) ? 'has-error' : '';
    $destinatarios = ['publico_general' => 'Público general', 'tutores' => 'Tutores de mascotas', 'clientes' => 'Clientes inscritos', 'profesionales' => 'Profesionales veterinarios', 'locales_comerciales' => 'Locales comerciales'];
    $beneficios = ['general' => 'Cualquier compra', 'alimentos' => 'Alimentos', 'farmacia' => 'Farmacia y medicamentos', 'consultas' => 'Consultas veterinarias', 'procedimientos' => 'Procedimientos veterinarios', 'servicios' => 'Otros servicios'];
    $segmentos = ['todos' => 'Todos los usuarios compatibles', 'todas_mascotas' => 'Todas las mascotas', 'mascotas_sector' => 'Mascotas del sector seleccionado', 'mascota_especifica' => 'Una mascota específica', 'usuarios_sector' => 'Usuarios del sector seleccionado', 'usuario_especifico' => 'Un usuario específico'];
@endphp

<div class="wizard" data-wizard data-form-voucher data-comunas="{{ $datosVoucher['comunas']->toJson() }}">
    <section class="wizard-paso" data-titulo="Datos del bono">
        <div class="campos">
            <div class="form-divider">Datos del bono</div>
            <div class="span-12 {{ $conError('titulo') }}">
                <label class="floating-label-activo-sm">Nombre del voucher</label>
                <input class="form-control form-control-sm" name="titulo" value="{{ old('titulo', request('titulo')) }}" placeholder="Ej: Descuento alimento gatos adultos" maxlength="255" required>
                @error('titulo')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Tipo de descuento</label>
                <select class="form-control form-control-sm" name="tipo_descuento" required>
                    <option value="porcentaje" @selected(old('tipo_descuento', request('tipo_descuento', 'porcentaje')) === 'porcentaje')>Porcentaje</option>
                    <option value="monto_fijo" @selected(old('tipo_descuento', request('tipo_descuento')) === 'monto_fijo')>Monto fijo</option>
                </select>
            </div>
            <div class="span-4 {{ $conError('valor') }}">
                <label class="floating-label-activo-sm">Valor descuento</label>
                <input class="form-control form-control-sm" type="number" name="valor" min="1" value="{{ old('valor', request('valor')) }}" placeholder="Ej: 15 o 2000" required>
                <span class="field-help">Porcentaje o monto según el tipo.</span>
                @error('valor')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('monto_minimo') }}">
                <label class="floating-label-activo-sm">Compra mínima</label>
                <input class="form-control form-control-sm" type="number" name="monto_minimo" min="0" value="{{ old('monto_minimo', request('monto_minimo', 0)) }}" placeholder="Ej: 10000">
                <span class="field-help">0 = sin mínimo.</span>
            </div>
            <div class="span-4 {{ $conError('usos_maximos') }}">
                <label class="floating-label-activo-sm">Cantidad de usos</label>
                <input class="form-control form-control-sm" type="number" name="usos_maximos" min="1" value="{{ old('usos_maximos', request('usos_maximos', 1)) }}" required>
                <span class="field-help">1 = uso único; más para una campaña.</span>
                @error('usos_maximos')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('valido_desde') }}">
                <label class="floating-label-activo-sm">Válido desde</label>
                <input class="form-control form-control-sm" type="date" name="valido_desde" value="{{ old('valido_desde', now()->toDateString()) }}" data-voucher-desde>
                @error('valido_desde')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 {{ $conError('valido_hasta') }}">
                <label class="floating-label-activo-sm">Válido hasta</label>
                <input class="form-control form-control-sm" type="date" name="valido_hasta" value="{{ old('valido_hasta', now()->addMonth()->toDateString()) }}" data-voucher-hasta>
                @error('valido_hasta')<small class="field-error">{{ $message }}</small>@enderror
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Cobertura">
        <div class="campos">
            <div class="form-divider">Origen comercial y cobertura</div>
            <div class="span-12">
                <label class="floating-label-activo-sm">Empresa patrocinante</label>
                <select class="form-control form-control-sm" name="local_venta_id">
                    <option value="">Comercializadora / administración central</option>
                    @foreach($datosVoucher['locales'] as $local)
                        <option value="{{ $local->id }}" @selected((string) old('local_venta_id') === (string) $local->id)>{{ $local->nombre }} · {{ ucfirst(str_replace('_', ' ', $local->tipo)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Cobertura territorial</label>
                <select class="form-control form-control-sm" name="alcance_territorial" required data-voucher-alcance>
                    <option value="nacional" @selected(old('alcance_territorial', 'nacional') === 'nacional')>Nacional</option>
                    <option value="regional" @selected(old('alcance_territorial') === 'regional')>Región específica</option>
                    <option value="comunal" @selected(old('alcance_territorial') === 'comunal')>Región y comuna</option>
                </select>
            </div>
            <div class="span-4 campo-condicional {{ $conError('region_id') }}" data-voucher-campo-region>
                <label class="floating-label-activo-sm">Región</label>
                <select class="form-control form-control-sm" name="region_id" data-voucher-region>
                    <option value="">Seleccione región</option>
                    @foreach($datosVoucher['regiones'] as $region)
                        <option value="{{ $region->id }}" @selected((string) old('region_id') === (string) $region->id)>{{ $region->nombre }}</option>
                    @endforeach
                </select>
                @error('region_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-4 campo-condicional {{ $conError('comuna_id') }}" data-voucher-campo-comuna>
                <label class="floating-label-activo-sm">Comuna</label>
                <select class="form-control form-control-sm" name="comuna_id" data-voucher-comuna data-selected="{{ old('comuna_id') }}"><option value="">Seleccione comuna</option></select>
                @error('comuna_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            @if($datosVoucher['regiones']->isEmpty())
                <p class="nota-formulario">No fue posible cargar las regiones desde VET-SDI. Por ahora solo se pueden crear vouchers de cobertura nacional.</p>
            @endif
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Público y beneficio">
        <div class="campos">
            <div class="form-divider">Público y beneficio objetivo</div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Tipo de usuario beneficiado</label>
                <select class="form-control form-control-sm" name="tipo_destinatario" required>
                    @foreach($destinatarios as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo_destinatario', 'publico_general') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Tipo de compra o prestación</label>
                <select class="form-control form-control-sm" name="tipo_beneficio" required>
                    @foreach($beneficios as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('tipo_beneficio', request('tipo_beneficio', 'general')) === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-12"><label class="floating-label-activo-sm">Finalidad o campaña</label><input class="form-control form-control-sm" name="destinatario_nombre" value="{{ old('destinatario_nombre') }}" placeholder="Ej: Tutores nuevos, campaña invierno" maxlength="255"></div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Productos">
        <div class="campos">
            <div class="form-divider">Productos beneficiados</div>
            <div class="span-12">
                <label class="floating-label-activo-sm">Producto específico</label>
                <select class="form-control form-control-sm" name="producto_id" data-voucher-producto>
                    <option value="">Todos o seleccionar por categoría</option>
                    @foreach($datosVoucher['productos'] as $producto)
                        <option value="{{ $producto->id }}" @selected((string) old('producto_id', request('producto_id')) === (string) $producto->id)>{{ $producto->nombre }} · {{ str_replace('_', ' ', $producto->categoria) }} · ${{ number_format($producto->precio, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <span class="field-help">El voucher destacará este producto en Ofertas.</span>
            </div>
            <div class="span-12">
                <label class="floating-label-activo-sm">O categoría completa</label>
                <select class="form-control form-control-sm" name="categoria_aplicable" data-voucher-categoria>
                    <option value="">Voucher general</option>
                    @foreach($datosVoucher['categorias'] as $categoria)
                        <option value="{{ $categoria }}" @selected(old('categoria_aplicable', request('categoria_aplicable')) === $categoria)>{{ ucfirst(str_replace('_', ' ', $categoria)) }}</option>
                    @endforeach
                </select>
                <span class="field-help">Se aplica a todos los productos activos de esa categoría. Elegir un producto o una categoría deja el otro en blanco.</span>
            </div>
        </div>
    </section>

    <section class="wizard-paso" data-titulo="Destinatarios">
        <div class="campos">
            <div class="form-divider">Destinatarios VET-SDI y condiciones</div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Grupo destinatario</label>
                <select class="form-control form-control-sm" name="segmento_destinatario" required data-voucher-segmento>
                    @foreach($segmentos as $valor => $texto)
                        <option value="{{ $valor }}" @selected(old('segmento_destinatario', 'todos') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-6 campo-condicional {{ $conError('mascota_id') }}" data-voucher-campo-mascota>
                <label class="floating-label-activo-sm">Mascota sincronizada</label>
                <select class="form-control form-control-sm" name="mascota_id" data-voucher-mascota>
                    <option value="">Seleccione mascota</option>
                    @foreach($datosVoucher['mascotasDestino'] as $mascota)
                        <option value="{{ $mascota->id }}" data-email="{{ $mascota->cliente?->email }}" @selected((string) old('mascota_id') === (string) $mascota->id)>{{ $mascota->nombre }} · {{ $mascota->especie }} · Tutor: {{ $mascota->cliente?->name ?: 'No identificado' }}</option>
                    @endforeach
                </select>
                @error('mascota_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 campo-condicional {{ $conError('usuario_destinatario_id') }}" data-voucher-campo-usuario>
                <label class="floating-label-activo-sm">Usuario sincronizado con VET-SDI</label>
                <select class="form-control form-control-sm" name="usuario_destinatario_id" data-voucher-usuario>
                    <option value="">Seleccione usuario</option>
                    @foreach($datosVoucher['usuariosDestino'] as $usuarioDestino)
                        <option value="{{ $usuarioDestino->id }}" data-email="{{ $usuarioDestino->email }}" @selected((string) old('usuario_destinatario_id') === (string) $usuarioDestino->id)>{{ $usuarioDestino->name }} · {{ $usuarioDestino->email }}</option>
                    @endforeach
                </select>
                @error('usuario_destinatario_id')<small class="field-error">{{ $message }}</small>@enderror
            </div>
            <div class="span-6 campo-condicional" data-voucher-campo-email>
                <label class="floating-label-activo-sm">Email sincronizado VET-SDI / Alimentos</label>
                <input class="form-control form-control-sm" type="email" name="destinatario_email" value="{{ old('destinatario_email') }}" readonly placeholder="Se completa al seleccionar" data-voucher-email>
            </div>
            <input type="hidden" name="vendedor_id" value="">
            <div class="span-12">
                <label class="floating-label-activo-sm">Descripción y condiciones</label>
                <textarea class="form-control form-control-sm" name="descripcion" maxlength="1000" placeholder="Ej: Válido para alimento de gatos, no acumulable, uso en tienda o reparto.">{{ old('descripcion', request('descripcion')) }}</textarea>
            </div>
        </div>
    </section>
</div>
<script src="{{ asset('js/form-voucher.js') }}?v={{ filemtime(public_path('js/form-voucher.js')) }}" defer></script>
