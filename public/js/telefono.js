/*
 * Campo de telefono de Chile (componente <x-campo-telefono>).
 * El usuario escribe solo los 9 digitos; se guarda "+56" + digitos en el campo oculto con el name real.
 */
(function () {
    function sincronizar(campo) {
        var grupo = campo.closest('[data-telefono]');
        var oculto = grupo && grupo.querySelector('[data-telefono-valor]');
        var digitos = campo.value.replace(/\D/g, '');
        // Si pegan el numero con 56 adelante, se quita el prefijo
        if (digitos.length > 9 && digitos.indexOf('56') === 0) digitos = digitos.slice(2);
        digitos = digitos.slice(0, 9);
        if (campo.value !== digitos) campo.value = digitos;
        if (oculto) oculto.value = digitos ? '+56' + digitos : '';
    }

    document.addEventListener('input', function (evento) {
        if (evento.target.matches && evento.target.matches('[data-telefono-digitos]')) sincronizar(evento.target);
    });

    // Al limpiar un formulario (ej. al reabrir un modal) el campo oculto vuelve a coincidir con lo visible
    document.addEventListener('reset', function (evento) {
        setTimeout(function () {
            evento.target.querySelectorAll('[data-telefono-digitos]').forEach(sincronizar);
        });
    }, true);
}());
