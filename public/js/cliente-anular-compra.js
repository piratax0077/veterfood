/* Vista: cliente/anular-compra.blade.php. Prototipo visual: no hay devolución real, solo el flujo. */
(function () {
    var form = document.querySelector('[data-anular-compra]');
    if (!form) return;

    var pasos = {
        formulario: form.querySelector('[data-paso="formulario"]'),
        resumen: form.querySelector('[data-paso="resumen"]'),
        final: form.querySelector('[data-paso="final"]')
    };

    function mostrarPaso(nombre) {
        Object.keys(pasos).forEach(function (clave) {
            pasos[clave].hidden = clave !== nombre;
        });
        pasos[nombre].scrollIntoView({behavior: 'smooth', block: 'start'});
    }

    /* Contador de caracteres del comentario */
    var comentario = form.querySelector('[data-contar]');
    var contador = form.querySelector('[data-contador]');
    if (comentario && contador) {
        var actualizarContador = function () {
            contador.textContent = comentario.value.length + ' / ' + comentario.maxLength;
        };
        comentario.addEventListener('input', actualizarContador);
        actualizarContador();
    }

    /* Destino del reembolso: cambia segun el medio de pago y, si es cuenta, segun "misma" u "otra" */
    var selectMedio = form.querySelector('[data-anular-medio]');
    var bloqueCredito = form.querySelector('[data-destino="credito"]');
    var bloqueCuenta = form.querySelector('[data-destino="cuenta"]');
    var bloqueBanco = form.querySelector('[data-destino="banco"]');
    var radiosDestinoCuenta = form.querySelectorAll('[data-anular-destino-cuenta]');

    function actualizarDestino() {
        var medio = selectMedio.value;
        var otraCuenta = form.querySelector('[data-anular-destino-cuenta]:checked');

        bloqueCredito.hidden = medio !== 'credito';
        bloqueCuenta.hidden = medio !== 'debito' && medio !== 'prepago';
        bloqueBanco.hidden = !(medio === 'transferencia' || (bloqueCuenta.hidden === false && otraCuenta && otraCuenta.value === 'otra'));
    }

    selectMedio.addEventListener('change', function () {
        radiosDestinoCuenta.forEach(function (radio) { radio.checked = false; });
        actualizarDestino();
    });
    radiosDestinoCuenta.forEach(function (radio) {
        radio.addEventListener('change', actualizarDestino);
    });
    actualizarDestino();

    /* Adjuntar fotos: solo vista previa local, no se sube a ningun lado */
    var inputFotos = form.querySelector('.anular-fotos-input');
    var listaFotos = form.querySelector('[data-anular-fotos-lista]');
    if (inputFotos && listaFotos) {
        inputFotos.addEventListener('change', function () {
            listaFotos.innerHTML = '';
            var archivos = Array.prototype.slice.call(inputFotos.files || []);
            listaFotos.hidden = archivos.length === 0;
            archivos.forEach(function (archivo) {
                var chip = document.createElement('span');
                chip.className = 'anular-foto-chip';
                var lector = new FileReader();
                lector.onload = function () {
                    var img = document.createElement('img');
                    img.src = lector.result;
                    img.alt = archivo.name;
                    chip.prepend(img);
                };
                lector.readAsDataURL(archivo);
                var nombre = document.createElement('small');
                nombre.textContent = archivo.name;
                chip.appendChild(nombre);
                listaFotos.appendChild(chip);
            });
        });
    }

    /* Paso 1 -> paso 2: valida solo los campos visibles del formulario (js/validacion.js) */
    var botonRevisar = form.querySelector('[data-anular-revisar]');
    botonRevisar.addEventListener('click', function () {
        var enVivo = window.validacionEnVivo;
        var invalido = enVivo ? enVivo.revisar(pasos.formulario.querySelectorAll('input, select, textarea')) : null;
        if (invalido) {
            enVivo.enfocar(invalido);
            return;
        }

        var pesos = function (monto) { return '$' + Number(monto).toLocaleString('es-CL'); };
        var textoSeleccionado = function (select) {
            return select.options[select.selectedIndex] ? select.options[select.selectedIndex].textContent : '';
        };

        form.querySelector('[data-resumen-motivo]').textContent = textoSeleccionado(form.querySelector('#anular_motivo'));
        form.querySelector('[data-resumen-medio]').textContent = textoSeleccionado(selectMedio);

        var destinoTexto = '';
        var medio = selectMedio.value;
        if (medio === 'credito') {
            destinoTexto = 'Tarjeta terminada en **** ' + form.dataset.ultimosDigitos;
        } else {
            var otraCuenta = form.querySelector('[data-anular-destino-cuenta]:checked');
            if (medio !== 'transferencia' && otraCuenta && otraCuenta.value === 'misma') {
                destinoTexto = 'Misma cuenta con la que pagaste';
            } else {
                var banco = textoSeleccionado(form.querySelector('#anular_banco'));
                var tipoCuenta = textoSeleccionado(form.querySelector('#anular_tipo_cuenta'));
                var numeroCuenta = form.querySelector('#anular_cuenta').value;
                destinoTexto = [banco, tipoCuenta, numeroCuenta ? 'cuenta terminada en ' + numeroCuenta.slice(-4) : ''].filter(Boolean).join(' · ');
            }
        }
        form.querySelector('[data-resumen-destino]').textContent = destinoTexto;
        form.querySelector('[data-resumen-monto]').textContent = pesos(form.dataset.total);

        mostrarPaso('resumen');
    });

    /* Paso 2 -> paso 1 */
    form.querySelector('[data-anular-volver]').addEventListener('click', function () {
        mostrarPaso('formulario');
    });

    /* Paso 2 -> paso 3: el envio real no existe, solo se simula */
    form.addEventListener('submit', function (evento) {
        evento.preventDefault();

        var numero = Math.floor(1000 + Math.random() * 9000);
        form.querySelector('[data-final-mensaje]').textContent = 'Solicitud N° ' + numero + ' recibida. Te enviaremos la nota de crédito y el seguimiento a tu correo.';
        mostrarPaso('final');

        if (window.notificar) {
            window.notificar({
                tipo: 'exito',
                titulo: 'Solicitud enviada',
                mensaje: 'Recibimos tu solicitud de devolución N° ' + numero + '.'
            });
        }
    });
})();
