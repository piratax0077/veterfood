{{-- Aviso de 10% para quien aún no tiene cuenta: aparece al minuto de navegar la tienda y se quita con la X (public/js/popup-descuento.js) --}}
<aside class="popup-descuento" aria-labelledby="popup-descuento-titulo" hidden data-popup-descuento>
    <button type="button" class="popup-descuento-cerrar" aria-label="Quitar aviso" data-popup-cerrar>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <p class="popup-descuento-titulo" id="popup-descuento-titulo">Obtén 10% en tu primera compra</p>
    <p class="popup-descuento-detalle">Regístrate en VeterFood y te enviaremos un correo con el cupón. Úsalo en tu primer pedido.</p>
    <a class="popup-descuento-boton" href="{{ route('inicio', ['desde' => 'tienda']) }}#inscripcion" data-modal-abrir="modal-crear-cuenta" data-popup-cerrar>Registrarme</a>
</aside>
