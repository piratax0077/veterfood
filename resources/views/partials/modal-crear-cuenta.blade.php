{{-- Crear cuenta cliente: se usa en el inicio y en la tienda. Abre con data-modal-abrir="modal-crear-cuenta" (css/modal-cuenta.css, js/modal-cuenta.js) --}}
<x-modal id="modal-crear-cuenta" titulo="Crear cuenta cliente" descripcion="Registra tus datos y luego podrás ingresar mascotas, direcciones y planes de alimento." :abierto="$errors->getBag('registro')->any()">
    <form method="POST" action="{{ route('registro.cliente') }}" data-validar>
        @csrf
        <div class="cuenta-campos">
            <div class="cuenta-ancho cuenta-fila-3">
                <div>
                    <label class="floating-label-activo-sm" for="registro-rut">RUT</label>
                    <input class="form-control form-control-sm" id="registro-rut" name="rut" value="{{ old('rut') ? \App\Rules\RutChileno::formatear(old('rut')) : '' }}" placeholder="12.345.678-9" maxlength="12" autocomplete="off" data-rut required>
                </div>
                <div>
                    <label class="floating-label-activo-sm" for="registro-nombres">Nombre</label>
                    <input class="form-control form-control-sm" id="registro-nombres" name="nombres" value="{{ old('nombres') }}" autocomplete="given-name" maxlength="120" required>
                </div>
                <div>
                    <label class="floating-label-activo-sm" for="registro-apellidos">Apellido</label>
                    <input class="form-control form-control-sm" id="registro-apellidos" name="apellidos" value="{{ old('apellidos') }}" autocomplete="family-name" maxlength="120" required>
                </div>
            </div>
            <div>
                <label class="floating-label-activo-sm" for="registro-email">Email</label>
                <input class="form-control form-control-sm" type="email" id="registro-email" name="email" value="{{ old('email') }}" autocomplete="email" required>
            </div>
            <div><x-campo-telefono name="telefono" :value="old('telefono')" label="Celular" id="registro-celular" /></div>
            <div class="cuenta-clave" data-sin-validar>
                <label class="floating-label-activo-sm" for="registro-clave">Contraseña</label>
                <input class="form-control form-control-sm" type="password" id="registro-clave" name="password" autocomplete="new-password" minlength="6" maxlength="8" required>
                <button type="button" class="cuenta-ojo" aria-label="Mostrar contraseña" aria-controls="registro-clave" data-ver-clave></button>
            </div>
            <div class="cuenta-clave" data-sin-validar>
                <label class="floating-label-activo-sm" for="registro-clave-repetir">Confirmar contraseña</label>
                <input class="form-control form-control-sm" type="password" id="registro-clave-repetir" name="password_confirmation" autocomplete="new-password" maxlength="8" required>
                <button type="button" class="cuenta-ojo" aria-label="Mostrar contraseña" aria-controls="registro-clave-repetir" data-ver-clave></button>
            </div>
            <div class="cuenta-ancho">
                <p class="pass-contador" data-pass-contador aria-live="polite">0 de 8</p>
                <ul class="pass-rules" aria-live="polite">
                    <li data-regla="largo">Entre 6 y 8 caracteres</li>
                    <li data-regla="mezcla">Letras y además números y/o símbolos</li>
                    <li data-regla="coincide">Ambas contraseñas coinciden</li>
                </ul>
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
