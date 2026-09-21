{{-- Iniciar sesión sin salir de la tienda; al entrar vuelve a la misma página. Abre con data-modal-abrir="modal-iniciar-sesion" (css/modal-cuenta.css, js/modal-cuenta.js) --}}
<x-modal id="modal-iniciar-sesion" ancho="chico" titulo="Iniciar sesión" :logo="asset('images/logotipo/logo-veterfood.svg')" :abierto="$errors->getBag('login')->any()">
    <form method="POST" action="{{ route('login.store') }}" data-validar>
        @csrf
        <input type="hidden" name="desde" value="tienda">
        <input type="hidden" name="volver" value="{{ url()->full() }}">
        <div class="cuenta-campos cuenta-campos--uno">
            <div>
                <label class="floating-label-activo-sm" for="ingreso-email">Ingresa tu email</label>
                <input class="form-control form-control-sm" type="email" id="ingreso-email" name="email" value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="cuenta-clave">
                <label class="floating-label-activo-sm" for="ingreso-clave">Ingresa tu contraseña</label>
                <input class="form-control form-control-sm" type="password" id="ingreso-clave" name="password" autocomplete="current-password" required>
                <button type="button" class="cuenta-ojo" aria-label="Mostrar contraseña" aria-controls="ingreso-clave" data-ver-clave></button>
            </div>
        </div>
        <label class="cuenta-recordar"><input type="checkbox" name="remember" value="1">Mantener mi sesión iniciada</label>
        <button type="submit" class="btn btn-success cuenta-boton">Iniciar sesión</button>
        <p class="cuenta-cambiar">¿Aún no tienes cuenta? <a href="#inscripcion" data-modal-abrir="modal-crear-cuenta">Crear cuenta</a></p>
    </form>
</x-modal>
