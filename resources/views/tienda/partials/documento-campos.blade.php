{{-- Datos de facturacion (se usan en el carro y en el pago) --}}
<p class="carro-factura-titulo">Datos de facturación</p>
<p class="carro-factura-intro">Escribe los datos tal como están en el Rol Único Tributario (RUT) de tu empresa, así la factura sale sin errores.</p>
@php
    // Si el pago volvió con un dato de factura malo, se avisa arriba del formulario
    $errorFactura = collect(['factura_rut', 'factura_empresa', 'factura_giro', 'factura_region', 'factura_comuna', 'factura_calle', 'factura_numero', 'factura_celular', 'factura_correo'])
        ->map(fn ($campo) => $errors->first($campo))->filter()->first();
@endphp
@if($errorFactura)
    <p class="carro-factura-error">{{ $errorFactura }}</p>
@endif

<div class="carro-factura-campos">
    <div class="campo campo--completo">
        <label class="floating-label-activo-sm" for="factura_rut">RUT de la empresa</label>
        <input class="form-control form-control-sm" id="factura_rut" name="factura_rut" value="{{ old('factura_rut') }}" placeholder="12.345.678-9" maxlength="12" autocomplete="off" data-rut data-factura-campo="rut" required>
        <small class="carro-factura-ayuda">Lo escribimos con puntos y guión mientras lo tipeas.</small>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_empresa">Nombre de la empresa</label>
        <input class="form-control form-control-sm" id="factura_empresa" name="factura_empresa" value="{{ old('factura_empresa') }}" placeholder="Ej: Comercial Las Rosas SpA" maxlength="160" autocomplete="organization" data-factura-campo="empresa" required>
        <small class="carro-factura-ayuda">Razón social inscrita en el SII.</small>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_giro">Giro comercial</label>
        <input class="form-control form-control-sm" id="factura_giro" name="factura_giro" value="{{ old('factura_giro') }}" placeholder="Ej: Venta de alimentos para mascotas" maxlength="160" data-factura-campo="giro" required>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_region">Región</label>
        <select class="form-control form-control-sm" id="factura_region" name="factura_region" data-select-buscador data-factura-campo="region" required>
            <option value="">Selecciona una región</option>
            @foreach($regiones as $region)
                <option value="{{ $region->id }}" @selected((string) old('factura_region') === (string) $region->id)>{{ $region->nombre }}</option>
            @endforeach
        </select>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_comuna">Comuna</label>
        <select class="form-control form-control-sm" id="factura_comuna" name="factura_comuna" data-seleccion="{{ old('factura_comuna') }}" data-select-buscador data-factura-campo="comuna" required disabled>
            <option value="">Selecciona primero la región</option>
        </select>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_calle">Calle</label>
        <input class="form-control form-control-sm" id="factura_calle" name="factura_calle" value="{{ old('factura_calle') }}" placeholder="Ej: Av. Providencia" maxlength="160" data-factura-campo="calle" required>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_numero">Número</label>
        <input class="form-control form-control-sm" id="factura_numero" name="factura_numero" value="{{ old('factura_numero') }}" placeholder="Ej: 1234" maxlength="20" data-factura-campo="numero" required>
    </div>
    <div class="campo">
        <label class="floating-label-activo-sm" for="factura_depto">Dpto / casa / oficina (opcional)</label>
        <input class="form-control form-control-sm" id="factura_depto" name="factura_depto" value="{{ old('factura_depto') }}" placeholder="Ej: Oficina 501" maxlength="60" data-factura-campo="depto">
    </div>
    <div class="campo">
        <x-campo-telefono name="factura_celular" id="factura_celular" :value="old('factura_celular')" label="Celular" :required="true" data-factura-campo="celular" />
    </div>
    <div class="campo campo--completo">
        <label class="floating-label-activo-sm" for="factura_correo">Correo para recibir la factura</label>
        <input class="form-control form-control-sm" type="email" id="factura_correo" name="factura_correo" value="{{ old('factura_correo') }}" placeholder="pagos@empresa.cl" maxlength="150" autocomplete="email" data-factura-campo="correo" required>
    </div>
</div>

<p class="carro-factura-aviso"><x-icono nombre="correo" /><span>La factura llega en PDF y XML al correo indicado, dentro de las 24 horas hábiles siguientes a la compra.</span></p>
