/*
 * Contacto de un servicio fúnebre comprado (Mis compras).
 * El botón "Contactar ahora" trae la compra en data-contacto-servicio; la ventana la abre modal.js.
 */
(function () {
    var modal = document.getElementById('modal-contactar-servicio');
    if (!modal) return;

    var nombre = modal.querySelector('[data-contacto-nombre]');
    var pedido = modal.querySelector('[data-contacto-pedido]');
    var reservado = modal.querySelector('[data-contacto-reservado]');
    var correo = modal.querySelector('[data-contacto-correo]');

    // Si el navegador no deja usar el portapapeles, copia con el metodo antiguo
    function copiar(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(texto).catch(function () { return copiarAntiguo(texto); });
        }
        return copiarAntiguo(texto);
    }

    function copiarAntiguo(texto) {
        var campo = document.createElement('textarea');
        campo.value = texto;
        campo.setAttribute('readonly', '');
        campo.style.position = 'fixed';
        campo.style.opacity = '0';
        modal.appendChild(campo);
        campo.select();
        var listo = false;
        try { listo = document.execCommand('copy'); } catch (e) {}
        modal.removeChild(campo);
        return listo ? Promise.resolve() : Promise.reject();
    }

    document.addEventListener('click', function (evento) {
        var abridor = evento.target.closest('[data-contacto-servicio]');
        if (abridor) {
            var datos;
            try { datos = JSON.parse(abridor.dataset.contactoServicio); } catch (e) { return; }

            nombre.textContent = datos.nombre || 'Servicio fúnebre';
            pedido.textContent = datos.pedido || '';
            reservado.hidden = !datos.reservado;
            // El correo sale con el pedido en el asunto
            correo.href = 'mailto:' + correo.dataset.contactoCorreo
                + '?subject=' + encodeURIComponent((datos.nombre || 'Servicio') + ' · pedido ' + (datos.pedido || ''));
            return;
        }

        var botonCopiar = evento.target.closest('[data-contacto-copiar]');
        if (!botonCopiar || !modal.contains(botonCopiar)) return;

        copiar(botonCopiar.dataset.contactoCopiar).then(function () {
            if (window.notificar) window.notificar(botonCopiar.dataset.contactoAviso, 'exito');
        }).catch(function () {
            if (window.notificar) window.notificar('No se pudo copiar. Selecciona el dato y cópialo a mano.', 'advertencia');
        });
    });
}());
