/*
 * RUT chileno: cualquier campo con data-rut se escribe con puntos y guion (12.345.678-9).
 */
(function () {
    'use strict';

    function formatear(valor) {
        var limpio = valor.replace(/[^0-9kK]/g, '').toUpperCase().slice(0, 9);
        if (limpio.length < 2) return limpio;
        return limpio.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + limpio.slice(-1);
    }

    document.addEventListener('input', function (evento) {
        var campo = evento.target;
        if (campo.matches && campo.matches('input[data-rut]')) {
            campo.value = formatear(campo.value);
        }
    });
}());
