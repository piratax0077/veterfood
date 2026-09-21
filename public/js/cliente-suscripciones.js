// Panel del cliente: cupones, Pedidos programados y Comparte y gana
(function () {
    function avisar(titulo, mensaje, tipo) {
        if (window.notificar) window.notificar({tipo: tipo || 'exito', titulo: titulo, mensaje: mensaje});
    }

    // Copia al portapapeles; si el navegador no deja, usa el metodo antiguo
    function copiar(texto) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(texto).catch(function () { return copiarAntiguo(texto); });
        }
        return copiarAntiguo(texto);
    }

    function copiarAntiguo(texto) {
        return new Promise(function (resolver, fallar) {
            var campo = document.createElement('textarea');
            campo.value = texto;
            campo.setAttribute('readonly', '');
            campo.style.cssText = 'position:fixed;top:0;left:0;opacity:0';
            document.body.appendChild(campo);
            campo.select();
            try {
                document.execCommand('copy') ? resolver() : fallar();
            } catch (error) {
                fallar(error);
            }
            campo.remove();
        });
    }

    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest('[data-copiar]');
        if (!boton) return;
        copiar(boton.dataset.copiar).then(function () {
            boton.classList.add('is-copiado');
            setTimeout(function () { boton.classList.remove('is-copiado'); }, 1600);
            avisar('Copiado', boton.dataset.copiarAviso || 'Código ' + boton.dataset.copiar + ' copiado. Pégalo al pagar.');
        }, function () {
            avisar('No pudimos copiarlo', 'Selecciona el texto y cópialo a mano.', 'error');
        });
    });

    // Suscripciones de ejemplo: cancelar solo en pantalla
    document.querySelectorAll('[data-sus-cancelar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            if (!window.confirm('¿Cancelar la suscripción «' + boton.dataset.susCancelar + '»? Ya no se renovará el próximo mes.')) return;
            var tarjeta = boton.closest('.sus-orden');
            tarjeta.classList.add('is-saliendo');
            setTimeout(function () { tarjeta.remove(); }, 260);
            avisar('Suscripción cancelada', 'No se renovará el próximo mes ni se te hará otro cobro.', 'neutral');
        });
    });

    // Boton "Más" con el menu de compartir del telefono o computador
    document.querySelectorAll('[data-compartir]').forEach(function (boton) {
        if (!navigator.share) return;
        boton.hidden = false;
        boton.addEventListener('click', function () {
            // El texto ya trae el enlace, asi no se repite
            navigator.share({title: 'VeterFood', text: boton.dataset.compartirTexto}).catch(function () {});
        });
    });
}());
