/*
 * Pestañas internas de la seccion "Facturación" del panel cliente:
 * Suscripciones, Pedidos programados, Historial de cobros y Tarjetas guardadas.
 */
(function () {
    var facturacion = document.getElementById('cliente-facturacion');
    if (!facturacion) return;

    var botonesTab = Array.prototype.slice.call(facturacion.querySelectorAll('.factura-tabs [data-factura-tab]'));
    var paneles = Array.prototype.slice.call(facturacion.querySelectorAll('[data-factura-panel]'));
    if (!paneles.length) return;

    function mostrar(nombre) {
        if (!paneles.some(function (panel) { return panel.dataset.facturaPanel === nombre; })) return;
        botonesTab.forEach(function (boton) { boton.classList.toggle('is-activa', boton.dataset.facturaTab === nombre); });
        paneles.forEach(function (panel) { panel.classList.toggle('is-activa', panel.dataset.facturaPanel === nombre); });
        try { sessionStorage.setItem('facturacionTab', nombre); } catch (e) {}
    }

    // Delegado en document: tambien reacciona a enlaces fuera de la seccion (ej. "Inscribir tarjeta")
    document.addEventListener('click', function (evento) {
        var disparador = evento.target.closest('[data-factura-tab]');
        if (disparador) mostrar(disparador.dataset.facturaTab);
    });

    var inicial = 'historial';
    try { inicial = sessionStorage.getItem('facturacionTab') || inicial; } catch (e) {}
    mostrar(inicial);

    // Tarjeta "Total mensual": cambia el monto segun el mes elegido
    var selectMes = facturacion.querySelector('[data-factura-mes-select]');
    var totalMes = facturacion.querySelector('[data-factura-mes-total]');
    if (selectMes && totalMes) {
        selectMes.addEventListener('change', function () {
            var opcion = selectMes.options[selectMes.selectedIndex];
            var total = parseInt(opcion.dataset.total || '0', 10);
            totalMes.textContent = '$' + total.toLocaleString('es-CL');
        });
    }
}());
