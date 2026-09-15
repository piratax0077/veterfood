/*
 * Formulario de lugar de venta (admin/partials/local-campos): adapta modalidades y servicios al tipo de lugar
 * y muestra el enlace de ofertas. Todo dentro de [data-form-local]. El mapa: public/js/mapa-direccion.js
 */
(function () {
    var perfiles = {
        sucursal: {texto: 'Sucursal propia: operación, stock, venta, retiro y despacho administrados directamente.', modalidades: {operacion_propia: 'Operación propia', venta_directa: 'Venta directa'}, servicios: ['alimentos', 'farmacia', 'atencion_veterinaria', 'peluqueria', 'hotel_guarderia', 'retiro', 'despacho']},
        comercio_adherido: {texto: 'Comercio adherido: defina comisión, pago mensual o un esquema mixto.', modalidades: {comision_venta: 'Comisión por ventas', pago_mensual: 'Pago mensual', mixto: 'Mensual más comisión'}, servicios: ['alimentos', 'farmacia', 'retiro', 'despacho', 'marketplace']},
        punto_retiro: {texto: 'Punto de retiro: registre la forma de pago por entrega o mensual.', modalidades: {comision_entrega: 'Pago por entrega', pago_mensual: 'Pago mensual', mixto: 'Mensual más entrega'}, servicios: ['retiro', 'despacho']},
        farmacia: {texto: 'Farmacia asociada: configure el convenio comercial y los productos disponibles.', modalidades: {comision_venta: 'Comisión por ventas', pago_mensual: 'Pago mensual', mixto: 'Mensual más comisión'}, servicios: ['farmacia', 'alimentos', 'retiro', 'despacho']},
        clinica_veterinaria: {texto: 'Clínica o veterinaria: defina prestaciones, farmacia y beneficios publicados.', modalidades: {comision_venta: 'Comisión por servicios', pago_mensual: 'Pago mensual', mixto: 'Mensual más comisión'}, servicios: ['atencion_veterinaria', 'farmacia', 'peluqueria', 'hotel_guarderia']},
        marketplace: {texto: 'Convenio o marketplace: determine comisión, catálogo y publicación de ofertas.', modalidades: {marketplace: 'Comisión marketplace', pago_mensual: 'Pago mensual', mixto: 'Mensual más comisión'}, servicios: ['marketplace', 'alimentos', 'farmacia', 'atencion_veterinaria']},
        otro: {texto: 'Otro tipo de lugar: seleccione solo las prestaciones que correspondan.', modalidades: {otro: 'Acuerdo personalizado'}, servicios: ['alimentos', 'farmacia', 'atencion_veterinaria', 'peluqueria', 'hotel_guarderia', 'retiro', 'despacho', 'marketplace']}
    };

    function q(raiz, selector) { return raiz.querySelector(selector); }

    function adaptarTipo(raiz) {
        var perfil = perfiles[q(raiz, '[data-local-tipo]').value] || perfiles.otro;
        var modalidad = q(raiz, '[data-local-modalidad]');
        var valorActual = modalidad.value || modalidad.dataset.selected;
        q(raiz, '[data-local-nota]').textContent = perfil.texto;
        modalidad.innerHTML = '<option value="">Seleccione modalidad</option>';
        Object.keys(perfil.modalidades).forEach(function (valor) {
            modalidad.add(new Option(perfil.modalidades[valor], valor, false, valor === valorActual));
        });
        raiz.querySelectorAll('[data-local-servicio]').forEach(function (opcion) {
            var visible = perfil.servicios.indexOf(opcion.dataset.localServicio) >= 0;
            var casilla = opcion.querySelector('input');
            opcion.classList.toggle('is-hidden', !visible);
            casilla.disabled = !visible;
            if (!visible) casilla.checked = false;
        });
    }

    function adaptarOfertas(raiz) {
        q(raiz, '[data-local-campo-url]').classList.toggle('is-hidden', q(raiz, '[data-local-ofertas]').value !== '1');
    }

    document.addEventListener('change', function (evento) {
        var raiz = evento.target.closest && evento.target.closest('[data-form-local]');
        if (!raiz) return;
        if (evento.target.matches('[data-local-tipo]')) adaptarTipo(raiz);
        if (evento.target.matches('[data-local-ofertas]')) adaptarOfertas(raiz);
    });

    // El mapa por direccion lo maneja public/js/mapa-direccion.js
    function iniciar(raiz) {
        adaptarTipo(raiz);
        adaptarOfertas(raiz);
    }

    document.addEventListener('reset', function (evento) {
        setTimeout(function () { evento.target.querySelectorAll('[data-form-local]').forEach(iniciar); });
    }, true);

    function iniciarTodos() { document.querySelectorAll('[data-form-local]').forEach(iniciar); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciarTodos); else iniciarTodos();
}());
