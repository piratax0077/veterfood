/*
 * Formulario de voucher (admin/partials/voucher-campos): cobertura region/comuna, destinatario especifico,
 * producto o categoria (excluyentes) y fecha "hasta" no anterior a "desde". Todo dentro de [data-form-voucher].
 */
(function () {
    function q(raiz, selector) { return raiz.querySelector(selector); }

    function cargarComunas(raiz) {
        var region = q(raiz, '[data-voucher-region]');
        var comuna = q(raiz, '[data-voucher-comuna]');
        var comunas = [];
        try { comunas = JSON.parse(raiz.dataset.comunas || '[]'); } catch (e) {}
        var seleccionada = comuna.dataset.selected || comuna.value;
        comuna.innerHTML = '<option value="">Seleccione comuna</option>';
        comunas.filter(function (c) { return String(c.id_region) === region.value; }).forEach(function (c) {
            comuna.add(new Option(c.nombre, c.id, false, String(c.id) === String(seleccionada)));
        });
        comuna.dataset.selected = '';
    }

    function adaptarCobertura(raiz) {
        var alcance = q(raiz, '[data-voucher-alcance]').value;
        q(raiz, '[data-voucher-campo-region]').classList.toggle('is-hidden', alcance === 'nacional');
        q(raiz, '[data-voucher-campo-comuna]').classList.toggle('is-hidden', alcance !== 'comunal');
        q(raiz, '[data-voucher-region]').required = alcance !== 'nacional';
        q(raiz, '[data-voucher-comuna]').required = alcance === 'comunal';
    }

    function adaptarDestinatario(raiz) {
        var segmento = q(raiz, '[data-voucher-segmento]').value;
        var mascota = q(raiz, '[data-voucher-mascota]');
        var usuario = q(raiz, '[data-voucher-usuario]');
        var email = q(raiz, '[data-voucher-email]');
        var esMascota = segmento === 'mascota_especifica';
        var esUsuario = segmento === 'usuario_especifico';
        q(raiz, '[data-voucher-campo-mascota]').classList.toggle('is-hidden', !esMascota);
        q(raiz, '[data-voucher-campo-usuario]').classList.toggle('is-hidden', !esUsuario);
        q(raiz, '[data-voucher-campo-email]').classList.toggle('is-hidden', !esMascota && !esUsuario);
        mascota.required = esMascota;
        usuario.required = esUsuario;
        var elegido = esMascota ? mascota : (esUsuario ? usuario : null);
        email.value = elegido && elegido.selectedOptions[0] ? (elegido.selectedOptions[0].dataset.email || '') : '';
    }

    function ajustarFechas(raiz) {
        var desde = q(raiz, '[data-voucher-desde]');
        var hasta = q(raiz, '[data-voucher-hasta]');
        hasta.min = desde.value || '';
    }

    function adaptarTodo(raiz) {
        cargarComunas(raiz);
        adaptarCobertura(raiz);
        adaptarDestinatario(raiz);
        ajustarFechas(raiz);
    }

    document.addEventListener('change', function (evento) {
        var campo = evento.target;
        var raiz = campo.closest && campo.closest('[data-form-voucher]');
        if (!raiz) return;
        if (campo.matches('[data-voucher-alcance]')) adaptarCobertura(raiz);
        if (campo.matches('[data-voucher-region]')) cargarComunas(raiz);
        if (campo.matches('[data-voucher-segmento], [data-voucher-mascota], [data-voucher-usuario]')) adaptarDestinatario(raiz);
        if (campo.matches('[data-voucher-desde]')) ajustarFechas(raiz);
        if (campo.matches('[data-voucher-producto]') && campo.value) q(raiz, '[data-voucher-categoria]').value = '';
        if (campo.matches('[data-voucher-categoria]') && campo.value) q(raiz, '[data-voucher-producto]').value = '';
    });

    document.addEventListener('reset', function (evento) {
        setTimeout(function () { evento.target.querySelectorAll('[data-form-voucher]').forEach(adaptarTodo); });
    }, true);

    function iniciar() { document.querySelectorAll('[data-form-voucher]').forEach(adaptarTodo); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', iniciar); else iniciar();
}());
