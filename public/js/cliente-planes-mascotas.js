/*
 * Planes para mascotas (cliente).
 *  - Cada card se desglosa por tipo de mascota, tamaño o formato: al elegir una opción se muestra su precio exacto.
 *  - "Contratar plan" abre el modal por pasos: plan y mascota, medio de pago (tarjeta guardada o nueva) y confirmación.
 *  Estilos: css/cliente-planes-mascotas.css · Pasos: js/wizard.js
 */
(function () {
    'use strict';

    var modal = document.getElementById('modal-contratar-plan');
    var form = document.querySelector('[data-contratar-form]');
    var fuente = document.querySelector('[data-contratar-datos]');
    if (!modal || !form || !fuente) return;

    var planes = {};
    try { planes = JSON.parse(fuente.textContent); } catch (e) { return; }
    var rutaIconos = form.dataset.iconos || '';

    function pesos(valor) {
        return '$' + Number(valor || 0).toLocaleString('es-CL');
    }

    function esc(texto) {
        return String(texto == null ? '' : texto).replace(/[&<>"]/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;'}[c];
        });
    }

    function icono(nombre) {
        return '<span class="isdi" style="--isdi-src:url(\'' + rutaIconos + '/' + esc(nombre) + '.svg\')" aria-hidden="true"></span>';
    }

    function fecha(dias) {
        var d = new Date();
        d.setMonth(d.getMonth() + (dias || 0));
        return d.toLocaleDateString('es-CL');
    }

    // ---- Cards: el precio cambia según la opción elegida ----
    function opcionCard(card) {
        var select = card.querySelector('[data-pm-variante]');
        return select ? select.options[select.selectedIndex] : null;
    }

    function pintarCard(card) {
        var opcion = opcionCard(card);
        if (!opcion) return;
        card.querySelector('[data-pm-precio]').textContent = pesos(opcion.dataset.precio);
    }

    document.querySelectorAll('[data-pm-card]').forEach(function (card) {
        card.addEventListener('change', function (evento) {
            if (evento.target.hasAttribute('data-pm-variante')) pintarCard(card);
        });
    });

    // ---- Modal: se arma con el plan que se apretó ----
    var campoPlan = form.querySelector('[data-contratar-plan]');
    var cajaIcono = form.querySelector('[data-contratar-icono]');
    var cajaNombre = form.querySelector('[data-contratar-nombre]');
    var cajaResumen = form.querySelector('[data-contratar-resumen]');
    var cajaBadge = form.querySelector('[data-contratar-badge]');
    var tituloVariantes = form.querySelector('[data-contratar-titulo-variantes]');
    var cajaVariantes = form.querySelector('[data-contratar-variantes]');
    var cajaTarjeta = form.querySelector('[data-contratar-tarjeta]');
    var cajaConfirmar = form.querySelector('[data-contratar-resumen-caja]');

    function configurar(slug, elegida) {
        var plan = planes[slug];
        if (!plan) return;

        campoPlan.value = slug;
        cajaIcono.innerHTML = icono(plan.icono);
        cajaNombre.textContent = plan.nombre;
        cajaResumen.textContent = plan.resumen;
        cajaBadge.textContent = plan.tipo;
        cajaBadge.className = 'badge tono-' + plan.tono;
        tituloVariantes.textContent = plan.titulo_precios;
        cajaVariantes.setAttribute('aria-label', plan.titulo_precios);

        cajaVariantes.innerHTML = plan.precios.map(function (fila, i) {
            var unico = Number(fila.pago_unico || 0);
            return '<label class="pm-variante">'
                + '<input type="radio" name="variante" value="' + i + '" required data-msg="Elige una opción para tu mascota."'
                + ' data-precio="' + fila.precio + '" data-pago-unico="' + unico + '" data-etiqueta="' + esc(fila.etiqueta) + '"'
                + (i === elegida ? ' checked' : '') + '>'
                + '<span class="pm-variante-caja">'
                + '<span class="pm-variante-icono">' + icono(fila.icono) + '</span>'
                + '<span class="pm-variante-texto"><strong>' + esc(fila.etiqueta) + '</strong>'
                + (fila.extra ? '<small>' + esc(fila.extra) + '</small>' : '') + '</span>'
                + '<span class="pm-variante-precio"><strong>' + pesos(fila.precio) + '</strong><span>/ mes</span>'
                + (unico ? '<span>+ ' + pesos(unico) + ' una vez</span>' : '') + '</span>'
                + '</span></label>';
        }).join('');

        refrescarTarjeta();
        pintarConfirmacion();
    }

    // El formulario de tarjeta nueva solo se llena (y se valida) si es el medio elegido
    function refrescarTarjeta() {
        var medio = form.querySelector('[data-contratar-medio]:checked');
        var nueva = !!medio && medio.hasAttribute('data-nueva');
        cajaTarjeta.hidden = !nueva;
        cajaTarjeta.querySelectorAll('input').forEach(function (campo) { campo.disabled = !nueva; });
    }

    function textoMedio() {
        var medio = form.querySelector('[data-contratar-medio]:checked');
        if (!medio) return 'Por elegir';
        if (!medio.hasAttribute('data-nueva')) return medio.dataset.descripcion;
        var numero = (form.querySelector('[data-contratar-numero]').value || '').replace(/\D/g, '');
        return numero.length >= 4 ? 'Tarjeta nueva •••• ' + numero.slice(-4) : 'Tarjeta nueva';
    }

    function pintarConfirmacion() {
        var plan = planes[campoPlan.value];
        var opcion = form.querySelector('[name="variante"]:checked');
        if (!plan || !opcion || !cajaConfirmar) return;

        var mensual = Number(opcion.dataset.precio);
        var unico = Number(opcion.dataset.pagoUnico);
        var mascota = form.querySelector('[name="mascota_id"]');
        var nombreMascota = mascota && mascota.value ? mascota.options[mascota.selectedIndex].text : 'La eliges después';

        var filas = [
            ['Plan', plan.nombre],
            ['Opción', opcion.dataset.etiqueta],
            ['Mascota', nombreMascota],
            ['Medio de pago', textoMedio()],
            ['Cargo mensual', pesos(mensual)]
        ];
        if (unico) filas.push(['Placa para el collar (pago único)', pesos(unico)]);
        filas.push(['Próximo cobro', fecha(1)]);

        cajaConfirmar.innerHTML = filas.map(function (fila) {
            return '<div class="pm-resumen-fila"><span>' + esc(fila[0]) + '</span><span>' + esc(fila[1]) + '</span></div>';
        }).join('') + '<div class="pm-resumen-fila pm-resumen-total"><span>Pagas hoy</span><span>' + pesos(mensual + unico) + '</span></div>';
    }

    form.addEventListener('change', function (evento) {
        if (evento.target.hasAttribute('data-contratar-medio')) refrescarTarjeta();
        pintarConfirmacion();
    });
    form.addEventListener('input', pintarConfirmacion);

    // Antes de que el modal se abra se deja listo el plan que corresponde (por eso va en captura)
    document.addEventListener('click', function (evento) {
        var boton = evento.target.closest('[data-pm-contratar]');
        if (!boton) return;
        var card = document.querySelector('[data-pm-card][data-plan="' + boton.dataset.plan + '"]');
        var opcion = card ? opcionCard(card) : null;
        configurar(boton.dataset.plan, opcion ? Number(opcion.value) : 0);
    }, true);

    // Número de tarjeta y vencimiento con el formato de siempre
    var campoNumero = form.querySelector('[data-contratar-numero]');
    if (campoNumero) {
        campoNumero.addEventListener('input', function () {
            var digitos = campoNumero.value.replace(/\D/g, '').slice(0, 19);
            campoNumero.value = digitos.replace(/(.{4})/g, '$1 ').trim();
        });
    }
    var campoVence = form.querySelector('[data-contratar-vencimiento]');
    if (campoVence) {
        campoVence.addEventListener('input', function () {
            var digitos = campoVence.value.replace(/\D/g, '').slice(0, 4);
            campoVence.value = digitos.length > 2 ? digitos.slice(0, 2) + '/' + digitos.slice(2) : digitos;
        });
    }

    refrescarTarjeta();

    // Si el servidor devolvió un error, se vuelve a abrir el modal en ese plan
    if (form.dataset.planInicial) {
        configurar(form.dataset.planInicial, 0);
        if (window.abrirModal) window.abrirModal('modal-contratar-plan');
    }
}());
