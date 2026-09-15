/*
 * Zona para arrastrar o elegir una foto (componente x-zona-foto).
 * La foto queda en un input file normal, así el formulario se envía como siempre.
 * Para mostrar una foto ya guardada: zona.dispatchEvent(new CustomEvent('zona-foto:actual', { detail: url }))
 */
(function () {
    'use strict';

    var TIPOS = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    function avisar(mensaje) {
        if (window.notificar) {
            window.notificar(mensaje, 'error');
        } else {
            window.alert(mensaje);
        }
    }

    function tamanoLegible(bytes) {
        return bytes >= 1048576 ? (bytes / 1048576).toFixed(1).replace('.', ',') + ' MB' : Math.max(1, Math.round(bytes / 1024)) + ' KB';
    }

    function preparar(zona) {
        var input = zona.querySelector('[data-zona-foto-input]');
        var area = zona.querySelector('[data-zona-foto-area]');
        var vista = zona.querySelector('[data-zona-foto-vista]');
        var pie = zona.querySelector('[data-zona-foto-pie]');
        var nombre = zona.querySelector('[data-zona-foto-nombre]');
        var quitar = zona.querySelector('[data-zona-foto-quitar]');
        var maximo = (parseFloat(zona.dataset.maximoMb) || 4) * 1048576;
        var urlTemporal = null;
        var contador = 0;

        function soltarUrl() {
            if (urlTemporal) {
                URL.revokeObjectURL(urlTemporal);
                urlTemporal = null;
            }
        }

        function pintar(src) {
            vista.hidden = !src;
            if (src) {
                vista.src = src;
            } else {
                vista.removeAttribute('src');
            }
            zona.classList.toggle('tiene-foto', !!src);
        }

        // Sin foto nueva: vuelve a la foto guardada (si hay) o a la zona vacía
        function limpiar() {
            soltarUrl();
            input.value = '';
            pie.hidden = true;
            nombre.textContent = '';
            pintar(zona.dataset.actual || '');
        }

        function usarArchivo(archivo) {
            if (!archivo) {
                return false;
            }
            if (TIPOS.indexOf(archivo.type) === -1) {
                avisar('Ese archivo no es una foto. Usa JPG, PNG o WEBP.');
                return false;
            }
            if (archivo.size > maximo) {
                avisar('La foto pesa ' + tamanoLegible(archivo.size) + '. El máximo es ' + tamanoLegible(maximo) + '.');
                return false;
            }

            soltarUrl();
            urlTemporal = URL.createObjectURL(archivo);
            pintar(urlTemporal);
            nombre.textContent = archivo.name + ' · ' + tamanoLegible(archivo.size);
            pie.hidden = false;
            return true;
        }

        input.addEventListener('change', function () {
            if (!usarArchivo(input.files[0])) {
                limpiar();
            }
        });

        ['dragenter', 'dragover'].forEach(function (tipo) {
            area.addEventListener(tipo, function (evento) {
                evento.preventDefault();
                if (tipo === 'dragenter') {
                    contador++;
                }
                zona.classList.add('is-arrastrando');
            });
        });

        area.addEventListener('dragleave', function () {
            contador = Math.max(0, contador - 1);
            if (!contador) {
                zona.classList.remove('is-arrastrando');
            }
        });

        area.addEventListener('drop', function (evento) {
            evento.preventDefault();
            contador = 0;
            zona.classList.remove('is-arrastrando');

            var archivo = evento.dataTransfer && evento.dataTransfer.files[0];
            if (!usarArchivo(archivo)) {
                return;
            }

            // Deja la foto soltada dentro del input para que viaje con el formulario
            try {
                var transferencia = new DataTransfer();
                transferencia.items.add(archivo);
                input.files = transferencia.files;
            } catch (error) {
                avisar('Tu navegador no permite soltar la foto. Haz clic en la zona para elegirla.');
                limpiar();
            }
        });

        quitar.addEventListener('click', limpiar);

        zona.addEventListener('zona-foto:actual', function (evento) {
            zona.dataset.actual = evento.detail || '';
            limpiar();
        });

        var formulario = zona.closest('form');
        if (formulario) {
            formulario.addEventListener('reset', function () {
                zona.dataset.actual = '';
                window.setTimeout(limpiar, 0);
            });
        }

        limpiar();
    }

    function iniciar() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-zona-foto]'), preparar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
}());
