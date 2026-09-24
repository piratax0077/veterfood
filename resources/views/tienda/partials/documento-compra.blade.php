{{-- Documento de la compra: boleta por defecto o factura con el interruptor.
     $enPago = true cuando va dentro del formulario de pago (ahi los campos se envian al servidor). --}}
@php
    $enPago = $enPago ?? false;
    $conFactura = (bool) old('factura');
@endphp

<section @class(['panel-card', 'carro-documento', 'checkout-bloque' => $enPago]) data-factura>
    <div class="carro-documento-cabecera">
        @if($enPago)
            <span class="checkout-numero" aria-hidden="true">5</span>
        @else
            <span class="carro-documento-icono" aria-hidden="true"><x-icono nombre="documento" /></span>
        @endif
        <div>
            <h2>Documento de la compra</h2>
            <p data-factura-texto-boleta @if($conFactura) hidden @endif>
                @auth
                    La boleta electrónica se envía al correo <strong>{{ auth()->user()->email }}</strong> cuando confirmes el pedido.
                @else
                    La boleta electrónica se envía al correo que indiques al pagar.
                @endauth
            </p>
            <p data-factura-texto-factura @unless($conFactura) hidden @endunless>Esta compra se emite con factura electrónica a nombre de tu empresa: no se emite boleta.</p>
        </div>
    </div>

    <label class="interruptor">
        <input type="checkbox" @if($enPago) name="factura" value="1" @endif @checked($conFactura) data-factura-switch>
        <span class="interruptor-pista" aria-hidden="true"></span>
        <span class="interruptor-texto">
            <strong>Quiero factura</strong>
            <small>Para compras a nombre de una empresa, con el IVA desglosado.</small>
        </span>
    </label>

    @if($enPago)
        {{-- Dentro del formulario de pago no puede ir otro <form> --}}
        <div class="carro-factura" data-factura-form data-url-ciudades="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}" @unless($conFactura) hidden @endunless>
            @include('tienda.partials.documento-campos')
        </div>
    @else
        <form class="carro-factura" data-factura-form data-validar data-url-ciudades="{{ route('tienda.ciudades', ['region' => '__REGION__']) }}" hidden>
            @include('tienda.partials.documento-campos')
        </form>
    @endif
</section>
