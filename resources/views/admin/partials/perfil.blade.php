{{-- Mi perfil del administrador: foto del menu lateral, datos de la cuenta, de la empresa y de la tienda. --}}
@php
    $cuentaPerfil = auth()->user();
    // Los mismos textos que ya salen en la cabecera del menu lateral
    $nombreAdmin = $nombrePersona ?? (trim(($cuentaPerfil?->nombres ?: '') . ' ' . ($cuentaPerfil?->apellidos ?: '')) ?: ($cuentaPerfil?->name ?: ''));
    $nombreDeLaTienda = $nombreTienda ?? config('app.name');
    $telefonoAdmin = $cuentaPerfil?->celular ?: $cuentaPerfil?->telefono;
@endphp

<section class="menu-lateral-seccion" id="admin-perfil" data-menu-panel="perfil">
    <div class="section-head">
        <div>
            <h2>Mi perfil</h2>
            <p class="muted">La foto y el nombre que se ven en el menú, más los datos de la empresa y de la tienda.</p>
        </div>
        <button type="button" class="perfil-btn-editar" data-perfil-editar><x-icono nombre="editar" />Editar</button>
    </div>

    <form class="perfil-form" id="form-perfil-admin" data-perfil-form data-keep-open="1" autocomplete="off">
        <fieldset disabled>
            <div class="perfil-columnas">
                <article class="perfil-card perfil-card--foto">
                    <h3>Foto de perfil</h3>
                    <p class="muted">Es la imagen que aparece arriba del menú lateral.</p>

                    <div class="perfil-avatar">
                        <span class="perfil-avatar-marco">
                            <img src="" alt="Foto de perfil" hidden data-perfil-avatar>
                            <span class="perfil-avatar-vacio" data-perfil-avatar-vacio><x-icono nombre="tienda" /></span>
                        </span>
                        <div class="perfil-avatar-texto">
                            <strong>Así se verá en el menú</strong>
                            <span>Usa una imagen cuadrada para que no se recorte.</span>
                        </div>
                    </div>

                    <x-zona-foto name="foto_perfil" id="perfil-admin-foto" texto="Arrastra la foto aquí" :maximo-mb="4" class="solo-edicion" />
                </article>

                <article class="perfil-card">
                    <h3>Cuenta</h3>
                    <p class="muted">Identificación de quien administra la tienda.</p>

                    <div class="perfil-campos">
                        <div class="span-12">
                            <label class="floating-label-activo-sm" for="perfil-tienda-nombre">Nombre de la tienda</label>
                            <input class="form-control form-control-sm" id="perfil-tienda-nombre" name="tienda_nombre" value="{{ $nombreDeLaTienda }}" maxlength="80" data-perfil-tienda>
                        </div>
                        <div class="span-12">
                            <label class="floating-label-activo-sm" for="perfil-admin-nombre">Nombre del administrador</label>
                            <input class="form-control form-control-sm" id="perfil-admin-nombre" name="admin_nombre" value="{{ $nombreAdmin }}" maxlength="120" data-perfil-persona>
                        </div>
                        <div class="span-12">
                            <label class="floating-label-activo-sm" for="perfil-admin-cargo">Cargo</label>
                            <input class="form-control form-control-sm perfil-bloqueado" id="perfil-admin-cargo" value="Administrador" readonly disabled aria-describedby="perfil-admin-cargo-ayuda">
                            <small class="perfil-nota" id="perfil-admin-cargo-ayuda"><x-icono nombre="candado" />El cargo viene de los permisos de la cuenta y no se edita aquí.</small>
                        </div>
                        <div class="span-12">
                            <label class="floating-label-activo-sm" for="perfil-admin-email">Email de la cuenta</label>
                            <input class="form-control form-control-sm" type="email" id="perfil-admin-email" name="admin_email" value="{{ $cuentaPerfil?->email }}" maxlength="255">
                        </div>
                        <div class="span-12">
                            <x-campo-telefono name="admin_telefono" :value="$telefonoAdmin" label="Teléfono del administrador" />
                        </div>
                    </div>
                </article>
            </div>

            <article class="perfil-card">
                <h3>Datos de la empresa</h3>
                <p class="muted">Se usan en boletas, facturas y documentos tributarios.</p>

                <div class="perfil-campos">
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-empresa-razon">Razón social</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-razon" name="empresa_razon_social" value="Comercializadora VeterFood SpA" maxlength="150">
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm" for="perfil-empresa-rut">RUT de la empresa</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-rut" name="empresa_rut" value="76.543.210-K" placeholder="12.345.678-9" maxlength="12" data-rut>
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm" for="perfil-empresa-giro">Giro</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-giro" name="empresa_giro" value="Venta de alimentos y artículos para mascotas" maxlength="150">
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-empresa-direccion">Dirección comercial</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-direccion" name="empresa_direccion" value="Av. Providencia 1234, oficina 802" maxlength="180">
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm" for="perfil-empresa-comuna">Comuna</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-comuna" name="empresa_comuna" value="Providencia" maxlength="80">
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm" for="perfil-empresa-region">Región</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-region" name="empresa_region" value="Metropolitana" maxlength="80">
                    </div>
                    <div class="span-4">
                        <x-campo-telefono name="empresa_telefono" value="+56229876543" label="Teléfono de la empresa" />
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm" for="perfil-empresa-email">Email de facturación</label>
                        <input class="form-control form-control-sm" type="email" id="perfil-empresa-email" name="empresa_email" value="facturacion@veterfood.cl" maxlength="255">
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm" for="perfil-empresa-web">Sitio web</label>
                        <input class="form-control form-control-sm" id="perfil-empresa-web" name="empresa_web" value="www.veterfood.cl" maxlength="150">
                    </div>
                </div>
            </article>

            <article class="perfil-card">
                <h3>Datos de la tienda</h3>
                <p class="muted">Lo que ven los clientes cuando compran o piden un despacho.</p>

                <div class="perfil-campos">
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-local-nombre">Nombre visible en la tienda</label>
                        <input class="form-control form-control-sm" id="perfil-local-nombre" name="tienda_visible" value="VeterFood Providencia" maxlength="120">
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-local-direccion">Dirección de la tienda</label>
                        <input class="form-control form-control-sm" id="perfil-local-direccion" name="tienda_direccion" value="Av. Providencia 1234, local 3" maxlength="180">
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm" for="perfil-local-comuna">Comuna</label>
                        <input class="form-control form-control-sm" id="perfil-local-comuna" name="tienda_comuna" value="Providencia" maxlength="80">
                    </div>
                    <div class="span-3">
                        <x-campo-telefono name="tienda_telefono" value="+56912345678" label="Teléfono de contacto" />
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-local-email">Email de contacto</label>
                        <input class="form-control form-control-sm" type="email" id="perfil-local-email" name="tienda_email" value="contacto@veterfood.cl" maxlength="255">
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm" for="perfil-local-horario">Horario de atención</label>
                        <input class="form-control form-control-sm" id="perfil-local-horario" name="tienda_horario" value="Lunes a viernes de 9:00 a 19:00, sábado de 10:00 a 14:00" maxlength="180">
                    </div>
                    <div class="span-12">
                        <label class="floating-label-activo-sm" for="perfil-local-descripcion">Descripción corta</label>
                        <textarea class="form-control form-control-sm" id="perfil-local-descripcion" name="tienda_descripcion" rows="3" maxlength="300">Alimento, farmacia y accesorios para mascotas, con despacho propio en Santiago.</textarea>
                    </div>
                </div>
            </article>
        </fieldset>

        <div class="perfil-acciones">
            <button type="button" class="btn btn-cancelar" data-perfil-cancelar><x-icono nombre="cerrar" class="isdi-izq" />Cancelar</button>
            <button type="submit" class="btn btn-success"><x-icono nombre="guardar" class="isdi-izq" />Guardar cambios</button>
        </div>
    </form>
</section>
