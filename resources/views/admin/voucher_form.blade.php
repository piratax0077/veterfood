@extends('layouts.app')

@section('title', 'Crear voucher')

@section('content')
<style>
    .voucher-form-head{display:grid;grid-template-columns:170px minmax(0,1fr);gap:18px;align-items:center;margin:0 0 20px}
    .voucher-title{display:flex;align-items:center;gap:12px;margin:0;font-size:34px;color:#111827}
    .voucher-icon{width:38px;height:38px;border-radius:10px;background:#ec4899;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:900;font-size:24px}
    .classic-card{background:#fff;border:1px solid #e5e7eb;border-radius:6px;padding:28px 24px;box-shadow:0 3px 8px rgba(15,23,42,.08)}
    .voucher-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:12px}
    .voucher-grid>div{position:static!important;padding-top:0!important}
    .voucher-grid .floating-label-activo-sm{position:static!important;display:block!important;background:transparent!important;color:#1d4ed8!important;padding:0!important;margin:0 0 6px!important;font-size:13px!important;font-weight:900!important}
    .voucher-grid .form-control-sm{min-height:38px!important}
    .field-help{display:block;margin-top:4px;color:#64748b;font-size:12px;line-height:1.25}
    .span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .form-divider{grid-column:span 12;border-top:1px solid #dbe3ee;margin:10px 0 2px;padding-top:16px;font-size:20px;font-weight:900;color:#111827}
    .form-actions{display:flex;justify-content:center;gap:12px;margin-top:18px}
    @media(max-width:900px){.voucher-form-head{grid-template-columns:1fr}.voucher-title{font-size:28px}.span-8,.span-6,.span-4,.span-3{grid-column:span 12}}
</style>

<div class="voucher-form-head">
    <a class="btn btn-secondary" href="{{ route('admin.vouchers.index') }}">Volver</a>
    <h1 class="voucher-title"><span class="voucher-icon">%</span>Crear voucher con QR seguro</h1>
</div>

@if($errors->any())
    <div class="alert" style="background:#fee2e2;color:#991b1b">{{ $errors->first() }}</div>
@endif

<div class="classic-card">
    <form method="POST" action="{{ route('admin.vouchers.store') }}">
        @csrf
        <div class="voucher-grid">
            <div class="form-divider">Datos del bono</div>
            <div class="span-6"><label class="floating-label-activo-sm">Nombre del voucher</label><input class="form-control form-control-sm" name="titulo" value="{{ old('titulo', request('titulo')) }}" placeholder="Ej: Descuento alimento gatos adultos" required></div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Tipo de descuento</label>
                <select class="form-control form-control-sm" name="tipo_descuento">
                    <option value="porcentaje" @selected(old('tipo_descuento', request('tipo_descuento')) === 'porcentaje')>Porcentaje</option>
                    <option value="monto_fijo" @selected(old('tipo_descuento', request('tipo_descuento')) === 'monto_fijo')>Monto fijo</option>
                </select>
            </div>
            <div class="span-3"><label class="floating-label-activo-sm">Valor descuento</label><input class="form-control form-control-sm" type="number" name="valor" min="1" value="{{ old('valor', request('valor')) }}" placeholder="Ej: 15 o 2000" required><span class="field-help">Porcentaje o monto segun tipo.</span></div>
            <div class="span-3"><label class="floating-label-activo-sm">Compra minima</label><input class="form-control form-control-sm" type="number" name="monto_minimo" min="0" value="{{ old('monto_minimo', request('monto_minimo', 0)) }}" placeholder="Ej: 10000"><span class="field-help">0 = sin minimo.</span></div>
            <div class="span-3"><label class="floating-label-activo-sm">Cantidad de usos</label><input class="form-control form-control-sm" type="number" name="usos_maximos" min="1" value="{{ old('usos_maximos', request('usos_maximos', 1)) }}" placeholder="Ej: 1" required><span class="field-help">1 uso unico, mayor para campana.</span></div>
            <div class="span-3"><label class="floating-label-activo-sm">Valido desde</label><input class="form-control form-control-sm" type="date" name="valido_desde" value="{{ old('valido_desde', now()->toDateString()) }}"></div>
            <div class="span-3"><label class="floating-label-activo-sm">Valido hasta</label><input class="form-control form-control-sm" type="date" name="valido_hasta" value="{{ old('valido_hasta', now()->addMonth()->toDateString()) }}"></div>

            <div class="form-divider">Origen comercial y cobertura</div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Empresa patrocinante</label>
                <select class="form-control form-control-sm" name="local_venta_id">
                    <option value="">Comercializadora / administración central</option>
                    @foreach($locales as $local)
                        <option value="{{ $local->id }}" @selected((string) old('local_venta_id') === (string) $local->id)>{{ $local->nombre }} · {{ ucfirst(str_replace('_', ' ', $local->tipo)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-3">
                <label class="floating-label-activo-sm">Cobertura territorial</label>
                <select class="form-control form-control-sm" id="alcance_territorial" name="alcance_territorial" required>
                    <option value="nacional" @selected(old('alcance_territorial', 'nacional') === 'nacional')>Nacional</option>
                    <option value="regional" @selected(old('alcance_territorial') === 'regional')>Region especifica</option>
                    <option value="comunal" @selected(old('alcance_territorial') === 'comunal')>Region y comuna</option>
                </select>
            </div>
            <div class="span-3 conditional-scope" id="campo_region">
                <label class="floating-label-activo-sm">Region</label>
                <select class="form-control form-control-sm" id="voucher_region" name="region_id">
                    <option value="">Seleccione region</option>
                    @foreach($regiones as $region)<option value="{{ $region->id }}" @selected((string) old('region_id') === (string) $region->id)>{{ $region->nombre }}</option>@endforeach
                </select>
            </div>
            <div class="span-2 conditional-scope" id="campo_comuna">
                <label class="floating-label-activo-sm">Comuna</label>
                <select class="form-control form-control-sm" id="voucher_comuna" name="comuna_id" data-selected="{{ old('comuna_id') }}"><option value="">Seleccione comuna</option></select>
            </div>

            <div class="form-divider">Publico y beneficio objetivo</div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Tipo de usuario beneficiado</label>
                <select class="form-control form-control-sm" name="tipo_destinatario" required>
                    @foreach(['publico_general'=>'Publico general','tutores'=>'Tutores de mascotas','clientes'=>'Clientes inscritos','profesionales'=>'Profesionales veterinarios','locales_comerciales'=>'Locales comerciales'] as $valor=>$texto)
                        <option value="{{ $valor }}" @selected(old('tipo_destinatario', 'publico_general') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Tipo de compra o prestacion</label>
                <select class="form-control form-control-sm" id="tipo_beneficio" name="tipo_beneficio" required>
                    @foreach(['general'=>'Cualquier compra','alimentos'=>'Alimentos','farmacia'=>'Farmacia y medicamentos','consultas'=>'Consultas veterinarias','procedimientos'=>'Procedimientos veterinarios','servicios'=>'Otros servicios'] as $valor=>$texto)
                        <option value="{{ $valor }}" @selected(old('tipo_beneficio', request('tipo_beneficio', 'general')) === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4"><label class="floating-label-activo-sm">Finalidad o campaña</label><input class="form-control form-control-sm" name="destinatario_nombre" value="{{ old('destinatario_nombre') }}" placeholder="Ej: Tutores nuevos, campaña invierno"></div>

            <div class="form-divider">Productos beneficiados</div>
            <div class="span-6">
                <label class="floating-label-activo-sm">Producto especifico</label>
                <select class="form-control form-control-sm" id="voucher-producto" name="producto_id">
                    <option value="">Todos o seleccionar por categoria</option>
                    @foreach($productos as $producto)
                        <option value="{{ $producto->id }}" @selected((string) old('producto_id', request('producto_id')) === (string) $producto->id)>{{ $producto->nombre }} · {{ str_replace('_', ' ', $producto->categoria) }} · ${{ number_format($producto->precio, 0, ',', '.') }}</option>
                    @endforeach
                </select>
                <span class="field-help">El voucher destacara este producto en Ofertas.</span>
            </div>
            <div class="span-6">
                <label class="floating-label-activo-sm">O categoria completa</label>
                <select class="form-control form-control-sm" id="voucher-categoria" name="categoria_aplicable">
                    <option value="">Voucher general</option>
                    @foreach($categorias as $categoria)
                        <option value="{{ $categoria }}" @selected(old('categoria_aplicable', request('categoria_aplicable')) === $categoria)>{{ ucfirst(str_replace('_', ' ', $categoria)) }}</option>
                    @endforeach
                </select>
                <span class="field-help">Se aplica a todos los productos activos de esa categoria.</span>
            </div>

            <div class="form-divider">Selección de destinatarios VET-SDI</div>
            <div class="span-4">
                <label class="floating-label-activo-sm">Grupo destinatario</label>
                <select class="form-control form-control-sm" id="segmento_destinatario" name="segmento_destinatario" required>
                    @foreach(['todos'=>'Todos los usuarios compatibles','todas_mascotas'=>'Todas las mascotas','mascotas_sector'=>'Mascotas del sector seleccionado','mascota_especifica'=>'Una mascota específica','usuarios_sector'=>'Usuarios del sector seleccionado','usuario_especifico'=>'Un usuario específico'] as $valor=>$texto)
                        <option value="{{ $valor }}" @selected(old('segmento_destinatario', 'todos') === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4" id="campo_mascota_destino">
                <label class="floating-label-activo-sm">Mascota sincronizada</label>
                <select class="form-control form-control-sm" id="mascota_destino" name="mascota_id">
                    <option value="">Seleccione mascota</option>
                    @foreach($mascotasDestino as $mascota)
                        <option value="{{ $mascota->id }}" data-email="{{ $mascota->cliente?->email }}" @selected((string) old('mascota_id') === (string) $mascota->id)>{{ $mascota->nombre }} · {{ $mascota->especie }} · Tutor: {{ $mascota->cliente?->name ?: 'No identificado' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4" id="campo_usuario_destino">
                <label class="floating-label-activo-sm">Usuario sincronizado con VET-SDI</label>
                <select class="form-control form-control-sm" id="usuario_destino" name="usuario_destinatario_id">
                    <option value="">Seleccione usuario</option>
                    @foreach($usuariosDestino as $usuarioDestino)
                        <option value="{{ $usuarioDestino->id }}" data-email="{{ $usuarioDestino->email }}" @selected((string) old('usuario_destinatario_id') === (string) $usuarioDestino->id)>{{ $usuarioDestino->name }} · {{ $usuarioDestino->email }}</option>
                    @endforeach
                </select>
            </div>
            <div class="span-4" id="campo_email_sincronizado"><label class="floating-label-activo-sm">Email sincronizado VET-SDI / Alimentos</label><input class="form-control form-control-sm" id="email_sincronizado" type="email" name="destinatario_email" value="{{ old('destinatario_email') }}" readonly placeholder="Se completa al seleccionar"></div>
            <input type="hidden" name="vendedor_id" value="">
            <div class="span-8"><label class="floating-label-activo-sm">Descripcion y condiciones</label><textarea class="form-control form-control-sm" name="descripcion" placeholder="Ej: Valido para alimento de gatos, no acumulable, uso en tienda o reparto.">{{ old('descripcion', request('descripcion')) }}</textarea></div>
        </div>

        <div class="form-actions">
            <a class="btn btn-secondary" href="{{ route('admin.vouchers.index') }}">Cancelar</a>
            <button class="btn-success">Generar voucher</button>
        </div>
    </form>
</div>
<script>
    const comunasVoucher = @json($comunas);
    const alcanceVoucher = document.getElementById('alcance_territorial');
    const regionVoucher = document.getElementById('voucher_region');
    const comunaVoucher = document.getElementById('voucher_comuna');
    function cargarComunasVoucher() {
        const seleccionada = comunaVoucher.dataset.selected || comunaVoucher.value;
        comunaVoucher.innerHTML = '<option value="">Seleccione comuna</option>';
        comunasVoucher.filter(c => String(c.id_region) === regionVoucher.value).forEach(c => comunaVoucher.add(new Option(c.nombre, c.id, false, String(c.id) === String(seleccionada))));
    }
    function adaptarCoberturaVoucher() {
        document.getElementById('campo_region').style.display = alcanceVoucher.value === 'nacional' ? 'none' : '';
        document.getElementById('campo_comuna').style.display = alcanceVoucher.value === 'comunal' ? '' : 'none';
        regionVoucher.required = alcanceVoucher.value !== 'nacional';
        comunaVoucher.required = alcanceVoucher.value === 'comunal';
    }
    alcanceVoucher.addEventListener('change', adaptarCoberturaVoucher);
    regionVoucher.addEventListener('change', cargarComunasVoucher);
    cargarComunasVoucher();
    adaptarCoberturaVoucher();
    const segmentoDestino = document.getElementById('segmento_destinatario');
    const mascotaDestino = document.getElementById('mascota_destino');
    const usuarioDestino = document.getElementById('usuario_destino');
    const emailSincronizado = document.getElementById('email_sincronizado');
    function adaptarDestinatarioVoucher() {
        const esMascota = segmentoDestino.value === 'mascota_especifica';
        const esUsuario = segmentoDestino.value === 'usuario_especifico';
        document.getElementById('campo_mascota_destino').style.display = esMascota ? '' : 'none';
        document.getElementById('campo_usuario_destino').style.display = esUsuario ? '' : 'none';
        document.getElementById('campo_email_sincronizado').style.display = esMascota || esUsuario ? '' : 'none';
        mascotaDestino.required = esMascota;
        usuarioDestino.required = esUsuario;
        if (!esMascota && !esUsuario) emailSincronizado.value = '';
        if (esMascota) emailSincronizado.value = mascotaDestino.selectedOptions[0]?.dataset.email || '';
        if (esUsuario) emailSincronizado.value = usuarioDestino.selectedOptions[0]?.dataset.email || '';
    }
    segmentoDestino.addEventListener('change', adaptarDestinatarioVoucher);
    mascotaDestino.addEventListener('change', adaptarDestinatarioVoucher);
    usuarioDestino.addEventListener('change', adaptarDestinatarioVoucher);
    adaptarDestinatarioVoucher();
    const productoVoucher = document.getElementById('voucher-producto');
    const categoriaVoucher = document.getElementById('voucher-categoria');
    productoVoucher?.addEventListener('change', () => {
        if (productoVoucher.value) categoriaVoucher.value = '';
    });
    categoriaVoucher?.addEventListener('change', () => {
        if (categoriaVoucher.value) productoVoucher.value = '';
    });
</script>
@endsection
