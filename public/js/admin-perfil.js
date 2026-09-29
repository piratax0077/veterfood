/*
 * Mi perfil del administrador (pantalla de prueba).
 *
 * Editar habilita los campos, Cancelar los deja como estaban y Guardar
 * confirma con una notificación y deja la foto y los nombres en el menú lateral.
 * Todavía no hay grabado en el servidor: lo guardado queda en este navegador.
 */
(function () {
    'use strict';

    var LLAVE = 'veterfood:perfil-admin';
    var LADO_FOTO = 320; // la foto se achica antes de guardarla, para no llenar el navegador

    function leerGuardado() {
        try {
            return JSON.parse(window.localStorage.getItem(LLAVE)) || {};
        } catch (error) {
            return {};
        }
    }

    function guardar(datos) {
        try {
            window.localStorage.setItem(LLAVE, JSON.stringify(datos));
            return true;
        } catch (error) {
            return false;
        }
    }

    function avisar(mensaje, tipo) {
        if (window.notificar) {
            window.notificar(mensaje, tipo || 'exito');
        }
    }

    // Deja la foto cuadrada y liviana
    function achicarFoto(archivo) {
        return new Promise(function (resolver) {
            var lector = new FileReader();
            lector.onload = function () {
                var imagen = new Image();
                imagen.onload = function () {
                    try {
                        var lado = Math.min(imagen.width, imagen.height);
                        var lienzo = document.createElement('canvas');
                        lienzo.width = LADO_FOTO;
                        lienzo.height = LADO_FOTO;
                        lienzo.getContext('2d').drawImage(
                            imagen,
                            (imagen.width - lado) / 2, (imagen.height - lado) / 2, lado, lado,
                            0, 0, LADO_FOTO, LADO_FOTO
                        );
                        resolver(lienzo.toDataURL('image/jpeg', 0.85));
                    } catch (error) {
                        resolver('');
                    }
                };
                imagen.onerror = function () { resolver(''); };
                imagen.src = lector.result;
            };
            lector.onerror = function () { resolver(''); };
            lector.readAsDataURL(archivo);
        });
    }

    function iniciar() {
        var formulario = document.querySelector('[data-perfil-form]');
        if (!formulario) return;

        var fieldset = formulario.querySelector('fieldset');
        var botonEditar = document.querySelector('[data-perfil-editar]');
        var botonCancelar = formulario.querySelector('[data-perfil-cancelar]');
        var campoFoto = formulario.querySelector('#perfil-admin-foto');
        var zonaFoto = formulario.querySelector('[data-zona-foto]');
        var avatar = formulario.querySelector('[data-perfil-avatar]');
        var avatarVacio = formulario.querySelector('[data-perfil-avatar-vacio]');
        var campoTienda = formulario.querySelector('[data-perfil-tienda]');
        var campoPersona = formulario.querySelector('[data-perfil-persona]');
        var campos = Array.prototype.slice.call(formulario.querySelectorAll('[name]')).filter(function (campo) {
            return campo.type !== 'file';
        });

        var datos = leerGuardado();
        var respaldo = {};

        function pintarAvatar(foto) {
            if (avatar) {
                avatar.src = foto || '';
                avatar.hidden = !foto;
            }
            if (avatarVacio) avatarVacio.hidden = !!foto;
        }

        // El menú lateral muestra foto, nombre de la tienda y nombre del administrador
        function pintarMenu() {
            var marco = document.querySelector('.menu-lateral-foto');
            if (marco) {
                var imagen = marco.querySelector('img');
                if (datos.foto) {
                    if (!imagen) {
                        imagen = document.createElement('img');
                        marco.innerHTML = '';
                        marco.appendChild(imagen);
                    }
                    imagen.src = datos.foto;
                    imagen.alt = campoTienda ? campoTienda.value : 'Foto de perfil';
                }
            }

            var nombre = document.querySelector('.menu-lateral-nombre');
            if (nombre && campoTienda && campoTienda.value.trim()) nombre.textContent = campoTienda.value.trim();

            var persona = document.querySelector('.menu-lateral-persona');
            if (persona && campoPersona && campoPersona.value.trim()) persona.textContent = campoPersona.value.trim();
        }

        function aplicarGuardado() {
            campos.forEach(function (campo) {
                if (Object.prototype.hasOwnProperty.call(datos, campo.name)) {
                    campo.value = datos[campo.name];
                }
            });
            // Los teléfonos rearman su valor con prefijo al escuchar un cambio
            formulario.querySelectorAll('[data-telefono-digitos]').forEach(function (telefono) {
                telefono.dispatchEvent(new Event('input', { bubbles: true }));
            });
            pintarAvatar(datos.foto || '');
            pintarMenu();
        }

        // Texto que se muestra en la ficha: el telefono guarda el numero completo aparte
        function textoDeCampo(caja) {
            var telefono = caja.querySelector('[data-telefono-valor]');
            if (telefono) return telefono.value;

            var control = caja.querySelector('select, textarea, input:not([type="hidden"]):not([type="file"])');
            if (!control) return '';
            if (control.tagName === 'SELECT') {
                var opcion = control.options[control.selectedIndex];
                return opcion ? opcion.textContent : '';
            }
            return control.value;
        }

        // Ficha de solo lectura: el mismo titulo y los mismos datos, sin campos
        function pintarLectura() {
            formulario.querySelectorAll('.perfil-campos').forEach(function (grilla) {
                var lectura = grilla.nextElementSibling;
                if (!lectura || !lectura.classList.contains('perfil-lectura')) {
                    lectura = document.createElement('div');
                    lectura.className = 'perfil-lectura';
                    grilla.parentNode.insertBefore(lectura, grilla.nextSibling);
                }
                lectura.textContent = '';

                Array.prototype.forEach.call(grilla.children, function (caja) {
                    var etiqueta = caja.querySelector('label');
                    if (!etiqueta) return;

                    var item = document.createElement('div');
                    item.className = 'perfil-lectura-item' + (caja.querySelector('textarea') ? ' es-largo' : '');

                    var titulo = document.createElement('span');
                    titulo.className = 'perfil-lectura-label';
                    titulo.textContent = etiqueta.textContent.trim();

                    var valor = document.createElement('span');
                    valor.className = 'perfil-lectura-valor';
                    valor.textContent = (textoDeCampo(caja) || '').trim() || '—';

                    item.appendChild(titulo);
                    item.appendChild(valor);
                    lectura.appendChild(item);
                });
            });
        }

        function editar(activo) {
            formulario.classList.toggle('is-editando', activo);
            fieldset.disabled = !activo;
            if (botonEditar) botonEditar.hidden = activo;
            if (!activo) pintarLectura();
        }

        aplicarGuardado();
        editar(false);

        if (botonEditar) {
            botonEditar.addEventListener('click', function () {
                respaldo = {};
                campos.forEach(function (campo) { respaldo[campo.name] = campo.value; });
                editar(true);
                var primero = formulario.querySelector('input:not([type="hidden"]):not([disabled])');
                if (primero) primero.focus();
            });
        }

        if (botonCancelar) {
            botonCancelar.addEventListener('click', function () {
                campos.forEach(function (campo) {
                    if (Object.prototype.hasOwnProperty.call(respaldo, campo.name)) campo.value = respaldo[campo.name];
                });
                if (campoFoto) {
                    campoFoto.value = '';
                    if (zonaFoto) zonaFoto.dispatchEvent(new CustomEvent('zona-foto:actual', { detail: '' }));
                }
                pintarAvatar(datos.foto || '');
                editar(false);
                if (botonEditar) botonEditar.focus();
            });
        }

        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();

            var archivo = campoFoto && campoFoto.files ? campoFoto.files[0] : null;
            var foto = archivo ? achicarFoto(archivo) : Promise.resolve(datos.foto || '');

            foto.then(function (imagen) {
                campos.forEach(function (campo) { datos[campo.name] = campo.value; });
                datos.foto = imagen;

                var ok = guardar(datos);
                pintarAvatar(imagen);
                pintarMenu();
                if (campoFoto) campoFoto.value = '';
                if (zonaFoto) zonaFoto.dispatchEvent(new CustomEvent('zona-foto:actual', { detail: '' }));
                editar(false);

                avisar(ok ? 'Perfil actualizado.' : 'Los cambios se ven en pantalla, pero no se pudieron guardar en este navegador.', ok ? 'exito' : 'advertencia');
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
