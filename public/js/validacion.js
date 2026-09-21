/*
 * Validación en vivo de los formularios del cliente.
 * Se activa poniendo data-validar en el <form>. Toma las reglas del mismo campo
 * (required, type="email", minlength, pattern, min, max) y suma algunas propias:
 * data-rut, número de tarjeta (autocomplete="cc-number"), vencimiento (autocomplete="cc-exp")
 * y data-igual-a="id-del-otro-campo". El texto del error se puede cambiar con data-msg.
 */
(function () {
    'use strict';

    var ESPERA = 700; // ms sin escribir antes de mostrar un error nuevo
    var pendientes = new WeakMap();

    function rutValido(valor) {
        var limpio = valor.replace(/[^0-9kK]/g, '').toUpperCase();
        if (!/^\d{7,8}[\dK]$/.test(limpio)) return false;
        var cuerpo = limpio.slice(0, -1);
        var suma = 0;
        var factor = 2;
        for (var i = cuerpo.length - 1; i >= 0; i--) {
            suma += Number(cuerpo[i]) * factor;
            factor = factor === 7 ? 2 : factor + 1;
        }
        var resto = 11 - (suma % 11);
        var dv = resto === 11 ? '0' : resto === 10 ? 'K' : String(resto);
        return dv === limpio.slice(-1);
    }

    function tarjetaValida(valor) {
        var digitos = valor.replace(/\D/g, '');
        if (!/^\d{13,19}$/.test(digitos)) return false;
        var suma = 0;
        for (var i = 0; i < digitos.length; i++) {
            var n = Number(digitos[digitos.length - 1 - i]);
            if (i % 2 === 1) {
                n *= 2;
                if (n > 9) n -= 9;
            }
            suma += n;
        }
        return suma % 10 === 0;
    }

    function errorVencimiento(valor) {
        var partes = valor.replace(/\s/g, '').match(/^(0[1-9]|1[0-2])\/(\d{2})$/);
        if (!partes) return 'Escribe el vencimiento como MM/AA.';
        var fin = new Date(2000 + Number(partes[2]), Number(partes[1]), 0, 23, 59, 59);
        if (fin < new Date()) return 'La tarjeta está vencida.';
        return '';
    }

    function esCampo(campo) {
        return campo && campo.form && campo.form.hasAttribute('data-validar')
            && /^(INPUT|SELECT|TEXTAREA)$/.test(campo.tagName)
            && !/^(hidden|submit|button|reset|file|image)$/.test(campo.type)
            && !campo.closest('[data-sin-validar]');
    }

    // El select con buscador esconde el original: se mira su botón
    function visible(campo) {
        var objetivo = campo.classList.contains('sb-nativo') ? campo.previousElementSibling : campo;
        if (campo.type === 'radio' || campo.type === 'checkbox') objetivo = campo.closest('label') || campo;
        return !!objetivo && objetivo.getClientRects().length > 0 && !campo.matches(':disabled');
    }

    // Caja del campo: la que tiene el label, donde va el mensaje
    function caja(campo) {
        if (campo.type === 'radio' || campo.type === 'checkbox') {
            var grupo = campo.closest('[role="radiogroup"], .card-type-options');
            return (grupo || campo.closest('label') || campo).parentElement;
        }
        var nodo = campo.parentElement;
        while (nodo && nodo !== campo.form) {
            for (var i = 0; i < nodo.children.length; i++) {
                if (nodo.children[i].tagName === 'LABEL' || nodo.children[i].classList.contains('floating-label-activo-sm')) return nodo;
            }
            nodo = nodo.parentElement;
        }
        return campo.parentElement;
    }

    function mensaje(campo) {
        var valor = campo.value.trim();
        var v = campo.validity;

        if (v.valueMissing) {
            if (campo.type === 'radio') return campo.dataset.msg || 'Elige una opción.';
            if (campo.type === 'checkbox') return campo.dataset.msg || 'Marca esta casilla para continuar.';
            if (campo.tagName === 'SELECT') return campo.dataset.msg || 'Selecciona una opción.';
            return 'Completa este campo.';
        }
        if (v.badInput) return campo.type === 'date' ? 'Revisa la fecha.' : 'Revisa este dato.';
        if (valor === '') return v.customError ? campo.validationMessage : '';

        var propio = '';
        if (campo.type === 'email' && (v.typeMismatch || !/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(valor))) {
            propio = 'Revisa el email, por ejemplo nombre@correo.cl.';
        } else if (campo.type === 'tel' && !/^\d{9}$/.test(valor.replace(/\D/g, ''))) {
            propio = 'Ingresa los 9 dígitos del celular.';
        } else if ((campo.hasAttribute('data-rut') || campo.hasAttribute('data-rut-retiro')) && !rutValido(valor)) {
            propio = 'El RUT no es válido. Revisa el número y el dígito verificador.';
        } else if (campo.autocomplete === 'cc-number' && !tarjetaValida(valor)) {
            propio = 'El número de tarjeta no es válido.';
        } else if (campo.autocomplete === 'cc-exp') {
            propio = errorVencimiento(valor);
        } else if (campo.minLength > 0 && campo.value.length < campo.minLength) {
            propio = 'Debe tener al menos ' + campo.minLength + ' caracteres.';
        } else if (v.patternMismatch) {
            propio = campo.title || 'Revisa el formato.';
        } else if (v.rangeUnderflow || v.rangeOverflow || v.badInput) {
            propio = campo.type === 'date' ? 'Revisa la fecha.' : 'Revisa este dato.';
        } else if (campo.dataset.igualA) {
            var otro = document.getElementById(campo.dataset.igualA);
            if (otro && otro.value !== campo.value) propio = 'No coincide con el campo anterior.';
        }
        if (propio) return campo.dataset.msg || propio;

        return v.customError ? campo.validationMessage : '';
    }

    function pintar(campo, texto) {
        var contenedor = caja(campo);
        var error = contenedor.querySelector(':scope > .field-error');
        // El aviso que vino del servidor se reemplaza por el de aquí
        contenedor.querySelectorAll(':scope > .seguimiento-error').forEach(function (viejo) { viejo.remove(); });
        var lleno = campo.type === 'radio' || campo.type === 'checkbox'
            ? !!campo.form.querySelector('[name="' + campo.name + '"]:checked')
            : campo.value.trim() !== '';

        contenedor.classList.toggle('has-error', !!texto);
        contenedor.classList.toggle('is-ok', !texto && lleno && campo.type !== 'radio' && campo.type !== 'checkbox');

        if (!texto) {
            if (error) error.remove();
            campo.removeAttribute('aria-invalid');
            return;
        }
        if (!error) {
            error = document.createElement('small');
            error.className = 'field-error';
            error.setAttribute('role', 'alert');
            contenedor.appendChild(error);
        }
        error.textContent = texto;
        campo.setAttribute('aria-invalid', 'true');
    }

    function validar(campo, mostrarError) {
        clearTimeout(pendientes.get(campo));
        var texto = visible(campo) ? mensaje(campo) : '';
        var yaMarcado = caja(campo).classList.contains('has-error');
        if (!texto || mostrarError || yaMarcado) {
            pintar(campo, texto);
        } else {
            // Mientras escribe: el error aparece si deja de escribir un momento
            pendientes.set(campo, setTimeout(function () { pintar(campo, mensaje(campo)); }, ESPERA));
        }
        return !texto;
    }

    // Revisa también el campo que depende de este (repetir contraseña)
    function validarRelacionados(campo) {
        if (!campo.id || !campo.form) return;
        campo.form.querySelectorAll('[data-igual-a="' + campo.id + '"]').forEach(function (otro) {
            if (otro.value !== '') validar(otro, true);
        });
    }

    document.addEventListener('input', function (evento) {
        var campo = evento.target;
        if (!esCampo(campo)) return;
        validar(campo, campo.dataset.tocado === '1' && campo.value === '');
        validarRelacionados(campo);
    });

    document.addEventListener('change', function (evento) {
        var campo = evento.target;
        if (!esCampo(campo)) return;
        campo.dataset.tocado = '1';
        if (campo.type === 'radio') {
            campo.form.querySelectorAll('[name="' + campo.name + '"]').forEach(function (opcion) { opcion.dataset.tocado = '1'; });
        }
        validar(campo, true);
    });

    document.addEventListener('focusout', function (evento) {
        var campo = evento.target;
        if (!esCampo(campo) || campo.type === 'radio' || campo.type === 'checkbox') return;
        campo.dataset.tocado = '1';
        validar(campo, campo.value.trim() !== '' || campo.required);
    });

    // Revisa varios campos, marca los que fallan y devuelve el primero con error
    function revisar(campos) {
        var primero = null;
        var revisados = {};
        Array.prototype.forEach.call(campos, function (campo) {
            if (!esCampo(campo)) return;
            if (campo.type === 'radio') {
                if (revisados[campo.name]) return;
                revisados[campo.name] = true;
            }
            campo.dataset.tocado = '1';
            if (!validar(campo, true) && !primero) primero = campo;
        });
        return primero;
    }

    // Lleva al campo con error y avisa con una notificación
    function enfocar(campo) {
        var destino = campo.classList.contains('sb-nativo') ? campo.previousElementSibling.querySelector('.sb-boton') : campo;
        caja(campo).scrollIntoView({behavior: 'smooth', block: 'center'});
        if (destino) destino.focus({preventScroll: true});
        if (window.notificar) {
            window.notificar({tipo: 'error', titulo: 'Revisa el formulario', mensaje: 'Hay campos por completar o corregir.'});
        }
    }

    // Lo usa el asistente por pasos (wizard.js) antes de avanzar
    window.validacionEnVivo = {revisar: revisar, enfocar: enfocar};

    // Al enviar: se revisa todo y, si algo falta, no se envía
    document.addEventListener('submit', function (evento) {
        var form = evento.target;
        if (!form.hasAttribute || !form.hasAttribute('data-validar')) return;

        var primero = revisar(form.elements);
        if (!primero) return;
        evento.preventDefault();
        evento.stopImmediatePropagation();
        enfocar(primero);
    }, true);

    // Al limpiar un formulario se borran los avisos
    document.addEventListener('reset', function (evento) {
        var form = evento.target;
        if (!form.hasAttribute || !form.hasAttribute('data-validar')) return;
        form.querySelectorAll('.field-error').forEach(function (error) { error.remove(); });
        form.querySelectorAll('.has-error, .is-ok').forEach(function (nodo) { nodo.classList.remove('has-error', 'is-ok'); });
        Array.prototype.forEach.call(form.elements, function (campo) {
            delete campo.dataset.tocado;
            campo.removeAttribute('aria-invalid');
        });
    }, true);

    // Sin los globos del navegador: los mensajes van bajo cada campo
    document.querySelectorAll('form[data-validar]').forEach(function (form) {
        form.noValidate = true;
    });
}());
