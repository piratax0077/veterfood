{{-- Crear cuenta cliente: se usa en el inicio y en la tienda. Abre con data-modal-abrir="modal-crear-cuenta" (css/modal-cuenta.css, js/modal-cuenta.js) --}}
<x-modal id="modal-crear-cuenta" titulo="Crear cuenta cliente" descripcion="Registra tus datos y luego podrás ingresar mascotas, direcciones y planes de alimento." :abierto="$errors->getBag('registro')->any()">
    <form method="POST" action="{{ route('registro.cliente') }}" data-validar>
        @csrf
        <div class="cuenta-campos">
            <div>
                <label class="floating-label-activo-sm" for="registro-nombre">Nombre completo</label>
                <input class="form-control form-control-sm" id="registro-nombre" name="name" value="{{ old('name') }}" autocomplete="name" required>
            </div>
            <div>
                <label class="floating-label-activo-sm" for="registro-rut">RUT</label>
                <input class="form-control form-control-sm" id="registro-rut" name="rut" value="{{ old('rut') ? \App\Rules\RutChileno::formatear(old('rut')) : '' }}" placeholder="12.345.678-9" maxlength="12" autocomplete="off" data-rut required>
            </div>
            <div>
                <label class="floating-label-activo-sm" for="registro-email">Email</label>
                <input class="form-control form-control-sm" type="email" id="registro-email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            </div>
            <div><x-campo-telefono name="telefono" :value="old('telefono')" label="Celular" id="registro-celular" /></div>
            <div class="cuenta-clave">
                <label class="floating-label-activo-sm" for="registro-clave">Contraseña</label>
                <input class="form-control form-control-sm" type="password" id="registro-clave" name="password" autocomplete="new-password" minlength="8" required>
                <button type="button" class="cuenta-ojo" aria-label="Mostrar contraseña" aria-controls="registro-clave" data-ver-clave></button>
            </div>
            <div class="cuenta-clave">
                <label class="floating-label-activo-sm" for="registro-clave-repetir">Confirmar contraseña</label>
                <input class="form-control form-control-sm" type="password" id="registro-clave-repetir" name="password_confirmation" autocomplete="new-password" data-igual-a="registro-clave" data-msg="Las contraseñas no coinciden." required>
                <button type="button" class="cuenta-ojo" aria-label="Mostrar contraseña" aria-controls="registro-clave-repetir" data-ver-clave></button>
            </div>
            <div class="cuenta-ancho">
                <label class="floating-label-activo-sm" for="registro-direccion">Dirección principal</label>
                <input class="form-control form-control-sm" id="registro-direccion" name="direccion" value="{{ old('direccion') }}" autocomplete="street-address">
            </div>
            <div>
                <label class="floating-label-activo-sm" for="registro-comuna">Comuna</label>
                <input class="form-control form-control-sm" id="registro-comuna" name="comuna" value="{{ old('comuna') }}">
            </div>
            <div>
                <label class="floating-label-activo-sm" for="registro-referencia">Referencia</label>
                <input class="form-control form-control-sm" id="registro-referencia" name="referencia" value="{{ old('referencia') }}">
            </div>
        </div>
        <p class="cuenta-nota">Al registrarte, quedas como cliente. Más adelante, si corresponde, el administrador puede cambiar tu rol.</p>
        <button type="submit" class="btn btn-success cuenta-boton">Crear cuenta</button>
        {{-- En la tienda también se puede ingresar desde aquí --}}
        @if($conIniciarSesion ?? false)
            <p class="cuenta-cambiar">¿Ya tienes cuenta? <a href="#login" data-modal-abrir="modal-iniciar-sesion">Iniciar sesión</a></p>
        @endif
    </form>
</x-modal>
