/*
 * Carro de compras: interruptor "Quiero factura".
 *
 * Apagado, la compra sale con boleta electronica (lo de siempre). Encendido, no se emite
 * boleta: se abre el formulario con los datos de facturacion y el resumen muestra el neto
 * y el IVA. Lo escrito queda guardado en el navegador para no repetirlo en la proxima
 * compra; todavia no se envia al servidor.
 *
 * Vista: resources/views/tienda/carro.blade.php · Estilos: public/css/tienda-carro.css
 */
(function () {
    'use strict';

    var bloque = document.querySelector('[data-factura]');

    if (!bloque) {
        return;
    }

    var CLAVE = 'veterfood-factura';
    var IVA = 0.19;

    var interruptor = bloque.querySelector('[data-factura-switch]');
    var formulario = bloque.querySelector('[data-factura-form]');
    var textoBoleta = bloque.querySelector('[data-factura-texto-boleta]');
    var textoFactura = bloque.querySelector('[data-factura-texto-factura]');
    var region = formulario.querySelector('[data-factura-campo="region"]');
    var comuna = formulario.querySelector('[data-factura-campo="comuna"]');

    var pagina = document.querySelector('[data-carro-pagina]');
    var total = document.querySelector('[data-carro-total], [data-resumen-total]');
    var lineaNeto = document.querySelector('[data-factura-linea-neto]');
    var lineaIva = document.querySelector('[data-factura-linea-iva]');
    var chip = document.querySelector('[data-factura-chip]');
    var pagar = pagina ? pagina.querySelector('.carro-pagar') : null;

    /* ---------- Utilidades ---------- */
    function pesos(monto) {
        return '$' + String(Math.round(monto || 0)).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }

    function numero(texto) {
        return parseInt(String(texto || '').replace(/\D/g, ''), 10) || 0;
    }

    function campos() {
        return formulario.querySelectorAll('[data-factura-campo]');
    }

    function leer() {
        try {
            return JSON.parse(window.localStorage.getItem(CLAVE)) || {};
        } catch (error) {
            return {};
        }
    }

    function guardar() {
        var datos = {activo: interruptor.checked};

        Array.prototype.forEach.call(campos(), function (campo) {
            datos[campo.dataset.facturaCampo] = campo.value;
        });

        try {
            window.localStorage.setItem(CLAVE, JSON.stringify(datos));
        } catch (error) {
            /* sin almacenamiento: los datos duran lo que dure la pagina */
        }
    }

    // Al apagar el interruptor se van los avisos rojos, pero no lo escrito.
    function limpiarAvisos() {
        formulario.querySelectorAll('.field-error').forEach(function (aviso) {
            aviso.remove();
        });
        formulario.querySelectorAll('.has-error, .is-ok').forEach(function (caja) {
            caja.classList.remove('has-error', 'is-ok');
        });
        Array.prototype.forEach.call(campos(), function (campo) {
            delete campo.dataset.tocado;
            delete campo.dataset.editado;
            campo.removeAttribute('aria-invalid');
        });
    }

    /* ---------- Comunas de la region elegida ---------- */
    var pedido = 0;

    function cargarComunas(idRegion, seleccion) {
        var numeroPedido = ++pedido;

        comuna.innerHTML = '<option value="">' + (idRegion ? 'Cargando comunas…' : 'Selecciona primero la región') + '</option>';
        comuna.disabled = true;

        if (!idRegion) {
            return;
        }

        fetch(formulario.dataset.urlCiudades.replace('__REGION__', idRegion), {headers: {Accept: 'application/json'}})
            .then(function (respuesta) { return respuesta.json(); })
            .then(function (lista) {
                if (numeroPedido !== pedido) {
                    return;
                }
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

    /* ---------- Neto e IVA en el resumen ---------- */
    function pintarResumen() {
        if (!total || !lineaNeto || !lineaIva) {
            return;
        }

        var conFactura = interruptor.checked;
        var monto = numero(total.textContent);
        var neto = Math.round(monto / (1 + IVA));

        lineaNeto.hidden = !conFactura;
        lineaIva.hidden = !conFactura;
        lineaNeto.querySelector('[data-factura-neto]').textContent = pesos(neto);
        lineaIva.querySelector('[data-factura-iva]').textContent = pesos(monto - neto);

        if (chip) {
            chip.hidden = !conFactura;
        }
    }

    /* ---------- Encender y apagar ---------- */
    function aplicar(porLaPersona) {
        var conFactura = interruptor.checked;

        formulario.hidden = !conFactura;
        textoBoleta.hidden = conFactura;
        textoFactura.hidden = !conFactura;
        pintarResumen();

        if (!conFactura) {
            limpiarAvisos();
            return;
        }

        if (porLaPersona) {
            var primero = campos()[0];
            if (primero) {
                primero.focus({preventScroll: true});
            }
        }
    }

    /* ---------- Lo guardado la vez anterior ---------- */
    (function restaurar() {
        var datos = leer();

        Array.prototype.forEach.call(campos(), function (campo) {
            var valor = datos[campo.dataset.facturaCampo];

            // La comuna se completa cuando llega la lista de la region
            // Si el formulario vuelve con datos (por un error al pagar), esos mandan
            if (campo === comuna || campo.value !== '' || typeof valor !== 'string' || valor === '') {
                return;
            }

            campo.value = valor;
            // El telefono guarda aparte el numero con +56 y el RUT se escribe con puntos
            campo.dispatchEvent(new Event('input', {bubbles: true}));
        });

        if (region.value) {
            cargarComunas(region.value, comuna.dataset.seleccion || datos.comuna);
        }

        interruptor.checked = interruptor.checked || !!datos.activo;
        aplicar(false);
        limpiarAvisos();
    }());

    /* ---------- Eventos ---------- */
    interruptor.addEventListener('change', function () {
        aplicar(true);
        guardar();
    });

    region.addEventListener('change', function () {
        cargarComunas(region.value, '');
    });

    formulario.addEventListener('input', guardar);
    formulario.addEventListener('change', guardar);

    // Enter dentro del carro: solo guarda (en el pago los campos viajan con la compra)
    if (formulario.tagName === 'FORM') {
        formulario.addEventListener('submit', function (evento) {
            evento.preventDefault();
            guardar();
            if (window.notificar) {
                window.notificar({tipo: 'exito', titulo: 'Datos guardados', mensaje: 'Con estos datos emitimos tu factura.'});
            }
        });
    }

    // Con factura, los datos tienen que estar completos antes de ir al pago
    if (pagar) {
        pagar.addEventListener('click', function (evento) {
            if (!interruptor.checked) {
                return;
            }

            guardar();
            var validacion = window.validacionEnVivo;
            if (!validacion) {
                return;
            }

            var primero = validacion.revisar(campos());
            if (primero) {
                evento.preventDefault();
                validacion.enfocar(primero);
            }
        });
    }

    // El total cambia al sumar o quitar productos (tienda-carro.js lo reescribe)
    if (total && window.MutationObserver) {
        new MutationObserver(pintarResumen).observe(total, {childList: true, characterData: true, subtree: true});
    }
}());
