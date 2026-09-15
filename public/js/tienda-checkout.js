/* Pago y despacho: pasos, direcciones, medio de pago y voucher */
(function () {
    'use strict';

    var form = document.querySelector('[data-checkout]');
    if (!form) return;

    var subtotal = parseInt(form.dataset.subtotal, 10) || 0;
    var envioBase = parseInt(form.dataset.envio, 10) || 0;
    var ahorroOutlet = parseInt(form.dataset.ahorro, 10) || 0;
    var voucher = null;

    function pesos(monto) {
        return '$' + String(Math.round(monto || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function $(selector) { return form.querySelector(selector); }
    function todos(selector) { return Array.prototype.slice.call(form.querySelectorAll(selector)); }
    function valorRadio(nombre) {
        var elegido = form.querySelector('input[name="' + nombre + '"]:checked');
        return elegido ? elegido.value : '';
    }

    /* Pasos de arriba sincronizados con el asistente */
    var pasosWizard = todos('.wizard-paso');
    var pasosCompra = document.querySelectorAll('[data-pasos-compra] li');

    var pasoMostrado = 0;

    function pasoActual() {
        return Math.max(0, pasosWizard.findIndex(function (paso) { return paso.classList.contains('is-actual'); }));
    }

    function marcarPasoCompra() {
        var numero = pasoActual() === 1 ? 4 : 3;
        Array.prototype.forEach.call(pasosCompra, function (li) {
            var n = parseInt(li.dataset.paso, 10);
            li.classList.toggle('is-actual', n === numero);
            if (n === 3 || n === 4) li.classList.toggle('is-hecho', n < numero);
            if (n === numero) li.setAttribute('aria-current', 'step'); else li.removeAttribute('aria-current');
        });
    }

    // Solo reacciona cuando de verdad cambia el paso
    var observador = new MutationObserver(function () {
        var actual = pasoActual();
        if (actual === pasoMostrado) return;
        pasoMostrado = actual;
        marcarPasoCompra();
        form.scrollIntoView({behavior: 'smooth', block: 'start'});
    });
    pasosWizard.forEach(function (paso) { observador.observe(paso, {attributes: true, attributeFilter: ['class']}); });
    marcarPasoCompra();

    /* Despacho o retiro */
    function aplicarEntrega() {
        var retiro = valorRadio('entrega_tipo') === 'retiro';
        todos('[data-solo-despacho]').forEach(function (bloque) { bloque.hidden = retiro; });
        todos('[data-solo-retiro]').forEach(function (bloque) { bloque.hidden = !retiro; });
        todos('[data-texto-despacho]').forEach(function (texto) { texto.hidden = retiro; });
        todos('[data-texto-retiro]').forEach(function (texto) { texto.hidden = !retiro; });
        todos('[data-placeholder-retiro]').forEach(function (campo) {
            campo.placeholder = retiro ? campo.dataset.placeholderRetiro : campo.dataset.placeholderDespacho;
        });
        recalcular();
    }

    todos('input[name="entrega_tipo"]').forEach(function (opcion) { opcion.addEventListener('change', aplicarEntrega); });

    /* Retiro en tienda: punto elegido, otra persona que retira */
    var bloqueRetiro = $('[data-retiro]');
    if (bloqueRetiro) {
        var otraPersona = $('#retiro-otra-persona');

        var marcarRequeridos = function () {
            var activo = valorRadio('entrega_tipo') === 'retiro' && !otraPersona.hidden;
            todos('[data-retira-campo]').forEach(function (campo) { campo.required = activo; });
            var telefono = otraPersona.querySelector('[data-telefono-digitos]');
            if (telefono) telefono.required = activo;
        };

        todos('[data-retiro-toggle]').forEach(function (boton) {
            boton.addEventListener('click', function () {
                var panel = $('#' + boton.dataset.retiroToggle);
                var abrir = panel.hidden;
                panel.hidden = !abrir;
                boton.setAttribute('aria-expanded', abrir ? 'true' : 'false');
                marcarRequeridos();
                if (abrir) {
                    var primero = panel.querySelector('input:not([type="hidden"]):not([type="radio"])');
                    if (primero) primero.focus();
                }
            });
        });

        todos('input[name="punto_retiro"]').forEach(function (punto) {
            punto.addEventListener('change', function () {
                $('[data-retiro-nombre]').textContent = punto.dataset.nombre;
                $('[data-retiro-direccion]').textContent = punto.dataset.direccion;
                $('[data-retiro-horario]').textContent = punto.dataset.horario;
                var desplegable = $('#retiro-puntos');
                desplegable.hidden = true;
                $('[data-retiro-toggle="retiro-puntos"]').setAttribute('aria-expanded', 'false');
            });
        });

        // RUT con puntos y guion mientras se escribe
        var rutRetiro = $('[data-rut-retiro]');
        if (rutRetiro) {
            rutRetiro.addEventListener('input', function () {
                var limpio = rutRetiro.value.replace(/[^0-9kK]/g, '').toUpperCase().slice(0, 9);
                if (limpio.length < 2) {
                    rutRetiro.value = limpio;
                    return;
                }
                rutRetiro.value = limpio.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + limpio.slice(-1);
            });
        }

        todos('input[name="entrega_tipo"]').forEach(function (opcion) { opcion.addEventListener('change', marcarRequeridos); });
        marcarRequeridos();
    }
    /* Direcciones */
    var direccion = $('#direccion_entrega');
    var referencia = $('#direccion_referencia');
    var region = $('#region_id');
    var comuna = $('#ciudad_id');
    var bloqueDireccion = $('[data-bloque-direccion]');
    var avisoComuna = $('[data-aviso-comuna]');

    var pedidoComunas = 0;

    function cargarComunas(regionId, seleccion) {
        var numero = ++pedidoComunas;
        comuna.innerHTML = '<option value="">' + (regionId ? 'Cargando comunas…' : 'Selecciona primero la región') + '</option>';
        comuna.disabled = true;
        if (!regionId) return Promise.resolve();

        return fetch(form.dataset.urlCiudades.replace('__REGION__', regionId), {headers: {Accept: 'application/json'}})
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (lista) {
                // Si la persona cambió de región mientras cargaba, se ignora
                if (numero !== pedidoComunas) return;
                comuna.innerHTML = '<option value="">Selecciona una comuna</option>';
                lista.forEach(function (item) {
                    var opcion = new Option(item.nombre, item.id);
                    opcion.selected = String(item.id) === String(seleccion || '');
                    comuna.appendChild(opcion);
                });
                comuna.disabled = false;
                actualizarMapa();
            })
            .catch(function () {
                comuna.innerHTML = '<option value="">No pudimos cargar las comunas</option>';
            });
    }

    region.addEventListener('change', function () { cargarComunas(region.value, ''); });

    var bloqueGuardar = $('[data-guardar-direccion]');
    var resumenDireccion = $('[data-direccion-resumen]');
    var panelDirecciones = $('[data-direcciones-panel]');
    var toggleDirecciones = $('[data-direcciones-toggle]');

    function abrirPanelDirecciones(abrir) {
        if (!panelDirecciones) return;
        panelDirecciones.hidden = !abrir;
        toggleDirecciones.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    // Vista cerrada: muestra la dirección guardada que se está usando
    function pintarResumenDireccion(opcion) {
        if (!resumenDireccion) return;
        var guardada = !!opcion && opcion.value !== 'nueva';
        resumenDireccion.hidden = !guardada;
        todos('.checkout-direccion').forEach(function (tarjeta) {
            tarjeta.classList.toggle('is-elegida', guardada && tarjeta.contains(opcion));
        });
        if (!guardada) return;
        $('[data-resumen-alias]').textContent = opcion.dataset.alias || '';
        $('[data-resumen-texto]').textContent = opcion.dataset.texto || '';
        $('[data-resumen-favorita]').hidden = opcion.dataset.favorita !== '1';
        var referenciaResumen = $('[data-resumen-referencia]');
        referenciaResumen.textContent = opcion.dataset.referencia || '';
        referenciaResumen.hidden = !opcion.dataset.referencia;
    }

    if (toggleDirecciones) {
        toggleDirecciones.addEventListener('click', function () {
            abrirPanelDirecciones(panelDirecciones.hidden);
        });
    }

    function usarDireccion(opcion) {
        // "Guardar esta dirección" solo tiene sentido con una dirección nueva
        if (bloqueGuardar) bloqueGuardar.hidden = !!opcion && opcion.value !== 'nueva';
        pintarResumenDireccion(opcion);

        if (!opcion || opcion.value === 'nueva') {
            // Si venía de una dirección guardada, se limpia para escribir la nueva
            if (form.dataset.direccionAplicada) {
                form.dataset.direccionAplicada = '';
                direccion.value = '';
                referencia.value = '';
                region.value = '';
                cargarComunas('', '');
                if (opcion) direccion.focus();
            }
            if (bloqueDireccion) bloqueDireccion.hidden = false;
            if (avisoComuna) avisoComuna.hidden = true;
            return;
        }
        form.dataset.direccionAplicada = '1';
        direccion.value = opcion.dataset.direccion || '';
        referencia.value = opcion.dataset.referencia || '';
        if (opcion.dataset.horario) {
            var horario = form.querySelector('input[name="horario_preferencia"][value="' + opcion.dataset.horario + '"]');
            if (horario) horario.checked = true;
        }
        region.value = opcion.dataset.region || '';
        cargarComunas(region.value, opcion.dataset.comuna);

        // Direcciones antiguas sin comuna: se piden antes de seguir
        var incompleta = !opcion.dataset.region || !opcion.dataset.comuna;
        bloqueDireccion.hidden = !incompleta;
        avisoComuna.hidden = !incompleta;
    }

    todos('input[name="direccion_guardada"]').forEach(function (opcion) {
        opcion.addEventListener('change', function () {
            usarDireccion(opcion);
            if (opcion.value !== 'nueva') abrirPanelDirecciones(false);
        });
    });

    var botonNueva = $('[data-nueva-direccion]');
    if (botonNueva) {
        botonNueva.addEventListener('click', function () {
            var nueva = form.querySelector('input[name="direccion_guardada"][value="nueva"]');
            nueva.checked = true;
            direccion.value = '';
            referencia.value = '';
            usarDireccion(nueva);
            abrirPanelDirecciones(false);
            direccion.focus();
        });
    }

    /* Enlace al mapa */
    var enlaceMapa = $('[data-checkout-mapa]');
    var georef = $('[data-checkout-georef]');

    function actualizarMapa() {
        var partes = [direccion.value];
        if (comuna.selectedIndex > 0) partes.push(comuna.options[comuna.selectedIndex].text);
        if (region.selectedIndex > 0) partes.push(region.options[region.selectedIndex].text);
        var texto = partes.filter(Boolean).join(', ');
        var url = texto ? 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(texto) : '';
        georef.value = url;
        enlaceMapa.href = url || 'https://www.google.com/maps';
    }

    [direccion, comuna, region].forEach(function (campo) { campo.addEventListener('change', actualizarMapa); });
    direccion.addEventListener('input', actualizarMapa);

    /* Medio de pago */
    var tarjetaId = $('[data-tarjeta-id]');
    var metodoPago = $('[data-metodo-pago]');
    var detalleMedio = $('[data-medio-detalle]');
    var metodos = {debito: 'tarjeta_debito', credito: 'tarjeta_credito'};
    var textos = {
        debito: 'Pagarás con tu tarjeta de débito al confirmar.',
        credito: 'Pagarás con tu tarjeta de crédito al confirmar.'
    };

    function aplicarMedio() {
        var elegido = form.querySelector('input[name="medio_pago"]:checked');
        if (!elegido) return;

        if (elegido.value.indexOf('tarjeta:') === 0) {
            tarjetaId.value = elegido.value.slice(8);
            metodoPago.value = 'tarjeta_guardada';
            detalleMedio.textContent = 'Pagarás con tu ' + elegido.dataset.descripcion + '.';
        } else {
            tarjetaId.value = 'otro';
            metodoPago.value = metodos[elegido.value] || 'tarjeta_debito';
            detalleMedio.textContent = textos[elegido.value] || '';
        }
        pintarCuotas();
    }

    /* Cuotas con tarjeta de crédito (valores referenciales, como los informa la banca en Chile) */
    var bloqueCuotas = $('[data-cuotas]');
    var listaCuotas = $('[data-cuotas-lista]');
    var campoCuotas = $('[data-cuotas-campo]');
    var totalCompra = subtotal + (parseInt(form.dataset.envio, 10) || 0);
    // Hasta 3 cuotas sin interés; desde 6, tasa mensual referencial
    var PLANES_CUOTAS = [
        {cuotas: 1, tasa: 0},
        {cuotas: 3, tasa: 0},
        {cuotas: 6, tasa: 1.79},
        {cuotas: 12, tasa: 1.99},
        {cuotas: 18, tasa: 2.09},
        {cuotas: 24, tasa: 2.19},
        {cuotas: 36, tasa: 2.29}
    ];

    function calcularCuota(plan) {
        var i = plan.tasa / 100;
        var valor = i ? totalCompra * i / (1 - Math.pow(1 + i, -plan.cuotas)) : totalCompra / plan.cuotas;
        valor = Math.round(valor);
        var totalPagar = valor * plan.cuotas;
        return {
            valor: valor,
            totalPagar: totalPagar,
            costo: Math.max(0, totalPagar - totalCompra),
            cae: i ? (Math.pow(1 + i, 12) - 1) * 100 : 0
        };
    }

    function porcentaje(numero) {
        return numero.toFixed(2).replace('.', ',') + '%';
    }

    function pintarCuotas() {
        if (!bloqueCuotas) return;
        var elegido = form.querySelector('input[name="medio_pago"]:checked');
        var esCredito = !!elegido && elegido.dataset.credito === '1';
        bloqueCuotas.hidden = !esCredito;
        if (!esCredito) {
            campoCuotas.value = 1;
            return;
        }

        var seleccion = parseInt(campoCuotas.value, 10) || 1;
        listaCuotas.innerHTML = '';
        PLANES_CUOTAS.forEach(function (plan) {
            var calculo = calcularCuota(plan);
            var opcion = document.createElement('label');
            opcion.className = 'checkout-cuota' + (plan.cuotas === seleccion ? ' is-elegida' : '');
            opcion.innerHTML = '<input type="radio" name="cuotas_opcion" value="' + plan.cuotas + '"' + (plan.cuotas === seleccion ? ' checked' : '') + '>'
                + '<span class="checkout-cuota-caja">'
                + '<strong>' + (plan.cuotas === 1 ? 'Contado' : plan.cuotas + ' cuotas') + '</strong>'
                + '<span>' + (plan.cuotas === 1 ? pesos(calculo.valor) : pesos(calculo.valor) + ' c/u') + '</span>'
                + (plan.tasa ? '' : '<em>' + (plan.cuotas === 1 ? '1 cuota' : 'Sin interés') + '</em>')
                + '</span>';
            listaCuotas.appendChild(opcion);
        });

        var planElegido = PLANES_CUOTAS.filter(function (plan) { return plan.cuotas === seleccion; })[0] || PLANES_CUOTAS[0];
        var detalle = calcularCuota(planElegido);
        $('[data-cuota-valor]').textContent = planElegido.cuotas === 1 ? pesos(detalle.valor) + ' (1 cuota)' : planElegido.cuotas + ' × ' + pesos(detalle.valor);
        $('[data-cuota-tasa]').textContent = porcentaje(planElegido.tasa);
        $('[data-cuota-cae]').textContent = porcentaje(detalle.cae);
        $('[data-cuota-costo]').textContent = detalle.costo ? pesos(detalle.costo) : '$0';
        $('[data-cuota-total]').textContent = pesos(detalle.totalPagar);
        campoCuotas.value = planElegido.cuotas;

        var textoCuotas = planElegido.cuotas === 1
            ? 'Pagarás con tu tarjeta de crédito en una sola cuota.'
            : 'Pagarás en ' + planElegido.cuotas + ' cuotas de ' + pesos(detalle.valor) + (planElegido.tasa ? '.' : ' sin interés.');
        detalleMedio.textContent = elegido.value.indexOf('tarjeta:') === 0
            ? 'Pagarás con tu ' + elegido.dataset.descripcion + (planElegido.cuotas === 1 ? ' en una sola cuota.' : ' en ' + planElegido.cuotas + ' cuotas de ' + pesos(detalle.valor) + '.')
            : textoCuotas;
    }

    if (listaCuotas) {
        listaCuotas.addEventListener('change', function (evento) {
            if (evento.target.name !== 'cuotas_opcion') return;
            campoCuotas.value = evento.target.value;
            pintarCuotas();
        });
    }
    todos('input[name="medio_pago"]').forEach(function (opcion) { opcion.addEventListener('change', aplicarMedio); });

    /* Voucher: valida el código y muestra el descuento en el resumen */
    var campoVoucher = $('[data-voucher-campo]');
    var botonVoucher = $('[data-voucher-aplicar]');
    var estadoVoucher = $('[data-voucher-estado]');

    // Código de la promo del banner, de muestra mientras no esté cargado como voucher
    var VOUCHER_MUESTRA = {code: 'VETERSDI10', title: '10% en tu primera compra web', discount_type: 'percent', value: 10, product: null};

    function buscarVoucher(codigo) {
        var url = form.dataset.urlVouchers + (form.dataset.email ? '?email=' + encodeURIComponent(form.dataset.email) : '');
        return fetch(url, {headers: {Accept: 'application/json'}})
            .then(function (respuesta) { return respuesta.ok ? respuesta.json() : {data: []}; })
            .catch(function () { return {data: []}; })
            .then(function (datos) {
                var encontrado = (datos.data || []).find(function (item) { return String(item.code).toUpperCase() === codigo; });
                if (!encontrado && codigo === VOUCHER_MUESTRA.code) encontrado = VOUCHER_MUESTRA;
                return encontrado || null;
            });
    }

    function descuentoDe(item) {
        var base = subtotal;
        if (item.product) {
            // Voucher de un producto: solo descuenta ese producto
            var fila = document.querySelector('.checkout-productos li[data-producto="' + item.product.id + '"]');
            base = fila ? parseInt(fila.dataset.total, 10) || 0 : 0;
        }
        return item.discount_type === 'percent' ? Math.round(base * item.value / 100) : Math.min(item.value, base);
    }

    function mostrarVoucher() {
        estadoVoucher.innerHTML = '';
        estadoVoucher.className = 'checkout-voucher-estado';
        if (!voucher) return;

        estadoVoucher.classList.add('is-valido');
        var chip = document.createElement('span');
        chip.className = 'checkout-voucher-chip';
        chip.innerHTML = '<strong></strong><span></span>';
        chip.querySelector('strong').textContent = voucher.code;
        chip.querySelector('span').textContent = voucher.title + ' · ' + (voucher.discount_type === 'percent' ? voucher.value + '%' : pesos(voucher.value));
        var quitar = document.createElement('button');
        quitar.type = 'button';
        quitar.className = 'checkout-voucher-quitar';
        quitar.textContent = 'Quitar';
        quitar.addEventListener('click', function () {
            voucher = null;
            campoVoucher.value = '';
            mostrarVoucher();
            recalcular();
            campoVoucher.focus();
        });
        estadoVoucher.appendChild(chip);
        estadoVoucher.appendChild(quitar);
    }

    function aplicarVoucher() {
        var codigo = campoVoucher.value.trim().toUpperCase();
        if (!codigo) {
            estadoVoucher.className = 'checkout-voucher-estado is-error';
            estadoVoucher.textContent = 'Ingresa un código.';
            return;
        }

        botonVoucher.disabled = true;
        estadoVoucher.className = 'checkout-voucher-estado';
        estadoVoucher.textContent = 'Validando código…';

        buscarVoucher(codigo).then(function (encontrado) {
            botonVoucher.disabled = false;
            if (!encontrado || descuentoDe(encontrado) <= 0) {
                voucher = null;
                recalcular();
                estadoVoucher.className = 'checkout-voucher-estado is-error';
                estadoVoucher.textContent = encontrado ? 'Este voucher no aplica a los productos de tu carro.' : 'El código no es válido o ya venció.';
                return;
            }
            voucher = encontrado;
            campoVoucher.value = encontrado.code;
            mostrarVoucher();
            recalcular();
            if (window.notificar) window.notificar({tipo: 'exito', titulo: 'Voucher aplicado', mensaje: encontrado.title});
        });
    }

    botonVoucher.addEventListener('click', aplicarVoucher);
    campoVoucher.addEventListener('keydown', function (evento) {
        if (evento.key === 'Enter') {
            evento.preventDefault();
            aplicarVoucher();
        }
    });

    /* Resumen */
    function recalcular() {
        // El retiro en tienda no paga envío (solo visual por ahora)
        var envio = valorRadio('entrega_tipo') === 'retiro' ? 0 : envioBase;
        var descuento = voucher ? descuentoDe(voucher) : 0;
        var total = Math.max(0, subtotal + envio - descuento);

        $('[data-resumen-envio]').textContent = envio ? pesos(envio) : 'Gratis';
        $('[data-resumen-envio]').classList.toggle('es-gratis', !envio);
        $('[data-resumen-voucher-linea]').hidden = !descuento;
        $('[data-resumen-voucher-codigo]').textContent = voucher ? voucher.code : '';
        $('[data-resumen-voucher]').textContent = '−' + pesos(descuento);
        $('[data-resumen-total]').textContent = pesos(total);
        $('[data-resumen-total-boton]').textContent = pesos(total);
        totalCompra = total;
        pintarCuotas();

        var ahorro = ahorroOutlet + descuento;
        $('[data-resumen-ahorro]').hidden = !ahorro;
        $('[data-resumen-ahorro-monto]').textContent = pesos(ahorro);
    }

    /* Crear cuenta en la misma compra (solo invitados) */
    var registroCheck = $('[data-registro-check]');
    var campoEmail = $('#cliente_email');

    function aplicarRegistro() {
        if (!registroCheck) return;
        var registrar = registroCheck.checked;
        var temporal = valorRadio('tipo_clave') === 'temporal';

        $('[data-registro-detalle]').hidden = !registrar;
        $('[data-clave-crear]').hidden = temporal;
        $('[data-clave-temporal]').hidden = !temporal;
        todos('[data-clave-campo]').forEach(function (campo) { campo.required = registrar && !temporal; });
        // La cuenta necesita un correo
        campoEmail.required = registrar;
        $('[data-clave-correo]').textContent = campoEmail.value.trim() || 'tu correo';
        revisarClaves();
    }

    function revisarClaves() {
        var clave = $('#registro_password');
        var repetir = $('#registro_password_confirmation');
        if (!clave) return;
        var activa = clave.required && clave.value !== '';
        clave.setCustomValidity(activa && !(/\p{L}/u.test(clave.value) && /\d/.test(clave.value)) ? 'Usa letras y números.' : '');
        repetir.setCustomValidity(repetir.required && repetir.value !== clave.value ? 'Las contraseñas no coinciden.' : '');
    }

    if (registroCheck) {
        registroCheck.addEventListener('change', aplicarRegistro);
        todos('input[name="tipo_clave"]').forEach(function (opcion) { opcion.addEventListener('change', aplicarRegistro); });
        todos('[data-clave-campo]').forEach(function (campo) { campo.addEventListener('input', revisarClaves); });
        campoEmail.addEventListener('input', function () {
            $('[data-clave-correo]').textContent = campoEmail.value.trim() || 'tu correo';
        });
    }

    todos('[data-pass-toggle]').forEach(function (ojo) {
        ojo.addEventListener('click', function () {
            var campo = document.getElementById(ojo.dataset.passToggle);
            var mostrar = campo.type === 'password';
            campo.type = mostrar ? 'text' : 'password';
            ojo.setAttribute('aria-pressed', mostrar ? 'true' : 'false');
            ojo.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    /* Al enviar */
    form.addEventListener('submit', function () {
        aplicarMedio();
        actualizarMapa();
    });

    // Estado inicial
    var guardada = form.querySelector('input[name="direccion_guardada"]:checked');
    if (guardada && guardada.value !== 'nueva') {
        usarDireccion(guardada);
    } else {
        if (region.value) cargarComunas(region.value, comuna.dataset.seleccion);
        usarDireccion(guardada);
    }
    aplicarEntrega();
    aplicarMedio();
    aplicarRegistro();
    if (campoVoucher.value.trim()) aplicarVoucher();
}());

/* Carro: editar y eliminar direcciones guardadas */
(function () {
    'use strict';

    var formEditar = document.querySelector('[data-form-editar-direccion]');
    var modalEditar = document.getElementById('modal-editar-direccion');

    document.querySelectorAll('form[data-borrar-direccion]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!window.confirm(formulario.dataset.borrarDireccion)) evento.preventDefault();
        });
    });

    if (!formEditar || !modalEditar) return;

    var region = formEditar.querySelector('[name="region_id"]');
    var comuna = formEditar.querySelector('[name="comuna_id"]');
    var pedido = 0;

    function cargarComunas(regionId, seleccion) {
        var numero = ++pedido;
        comuna.innerHTML = '<option value="">' + (regionId ? 'Cargando comunas…' : 'Selecciona primero la región') + '</option>';
        comuna.disabled = true;
        if (!regionId) return;

        fetch(formEditar.dataset.urlCiudades.replace('__REGION__', regionId), {headers: {Accept: 'application/json'}})
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (lista) {
                if (numero !== pedido) return;
                comuna.innerHTML = '<option value="">Selecciona una comuna</option>';
                lista.forEach(function (item) {
                    var opcion = new Option(item.nombre, item.id);
                    opcion.selected = String(item.id) === String(seleccion || '');
                    comuna.appendChild(opcion);
                });
                comuna.disabled = false;
            })
            .catch(function () {
                comuna.innerHTML = '<option value="">No pudimos cargar las comunas</option>';
            });
    }

    region.addEventListener('change', function () { cargarComunas(region.value, ''); });

    document.querySelectorAll('[data-editar-direccion]').forEach(function (boton) {
        boton.setAttribute('data-modal-abrir', 'modal-editar-direccion');
        boton.addEventListener('click', function () {
            var datos = JSON.parse(boton.dataset.editarDireccion);
            var campos = formEditar.elements;
            campos.direccion_id.value = datos.id;
            campos.alias.value = datos.alias || '';
            campos.direccion.value = datos.direccion || '';
            campos.referencia.value = datos.referencia || '';
            campos.dia_preferencia.value = datos.dia || '';
            campos.horario_preferencia.value = datos.horario || '';
            campos.forma_pago_preferida.value = datos.pago || '';
            campos.principal.checked = !!datos.principal;
            region.value = datos.region_id || '';
            cargarComunas(region.value, datos.comuna_id);
            modalEditar.querySelector('h2').textContent = 'Editar «' + (datos.alias || 'dirección') + '»';
        });
    });
}());
