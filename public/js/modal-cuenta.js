/*
 * Modales de cuenta: "Crear cuenta cliente" e "Iniciar sesión".
 * Pone el ojo para ver la contraseña y abre el modal si la dirección trae #inscripcion o #login.
 */
(function () {
    'use strict';

    var ojoAbierto = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7Z"/><circle cx="12" cy="12" r="3"/></svg>';
    var ojoCerrado = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a20.3 20.3 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 4c7 0 11 7 11 7a20.3 20.3 0 0 1-2.68 3.68M14.12 14.12a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

    document.querySelectorAll('.modal [data-ver-clave]').forEach(function (boton) {
        var campo = document.getElementById(boton.getAttribute('aria-controls'));
        if (!campo) return;

        boton.innerHTML = ojoAbierto;
        boton.addEventListener('click', function () {
            var visible = campo.type === 'text';
            campo.type = visible ? 'password' : 'text';
            boton.innerHTML = visible ? ojoAbierto : ojoCerrado;
            boton.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });
    });

    // Enlaces que llegan con #inscripcion o #login también abren su modal (si está en la página)
    var modalPorDireccion = { '#inscripcion': 'modal-crear-cuenta', '#login': 'modal-iniciar-sesion' };

    function abrirDesdeDireccion() {
        var id = modalPorDireccion[window.location.hash];
        if (!id || !document.getElementById(id) || !window.abrirModal) return;
        history.replaceState(null, '', window.location.pathname + window.location.search);
        window.abrirModal(id);
    }

    window.addEventListener('hashchange', abrirDesdeDireccion);
    abrirDesdeDireccion();
}());

/* Contraseña de "Crear cuenta cliente": misma regla que el cambio de contraseña del panel (entre 6 y 8, letras + números/símbolos) */
(function () {
    'use strict';

    var formulario = document.querySelector('#modal-crear-cuenta form');
    var claveNueva = document.getElementById('registro-clave');
    var claveRepetir = document.getElementById('registro-clave-repetir');

    if (!formulario || !claveNueva || !claveRepetir) return;

    var boton = formulario.querySelector('.cuenta-boton');
    var contador = formulario.querySelector('[data-pass-contador]');
    var reglas = {
        largo: function () { return claveNueva.value.length >= 6 && claveNueva.value.length <= 8; },
        mezcla: function () { return /\p{L}/u.test(claveNueva.value) && /[^\p{L}\s]/u.test(claveNueva.value); },
        coincide: function () { return claveNueva.value !== '' && claveNueva.value === claveRepetir.value; }
    };
    // Cada regla se evalúa recién cuando se escribió en su campo
    var campoDeRegla = { largo: claveNueva, mezcla: claveNueva, coincide: claveRepetir };

    function pintar() {
        var todoOk = true;
        Object.keys(reglas).forEach(function (regla) {
            var item = formulario.querySelector('[data-regla="' + regla + '"]');
            var cumple = reglas[regla]();
            var escrito = campoDeRegla[regla].value !== '';
            todoOk = todoOk && cumple;
            if (!item) return;
            item.classList.toggle('ok', escrito && cumple);
            item.classList.toggle('is-falta', escrito && !cumple);
        });

        if (contador) {
            contador.textContent = claveNueva.value.length + ' de 8';
            contador.classList.toggle('is-ok', reglas.largo());
        }
        if (boton) {
            boton.disabled = !todoOk;
            boton.title = todoOk ? '' : 'Completa los requisitos de la contraseña';
        }
    }

    ['input', 'keyup', 'change'].forEach(function (tipo) {
        claveNueva.addEventListener(tipo, pintar);
        claveRepetir.addEventListener(tipo, pintar);
    });
    formulario.addEventListener('reset', function () {
        window.setTimeout(pintar, 0);
    });
    pintar();

    formulario.addEventListener('submit', function (evento) {
        var pendiente = Object.keys(reglas).filter(function (regla) { return !reglas[regla](); })[0];
        if (!pendiente) return;
        evento.preventDefault();
        (pendiente === 'coincide' ? claveRepetir : claveNueva).focus();
    });
}());
