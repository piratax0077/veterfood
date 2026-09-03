@extends('layouts.app')

@section('title', 'App repartidor')

@section('content')
<style>
.driver-app{max-width:1100px;margin:auto}.driver-head{display:flex;justify-content:space-between;gap:16px;align-items:center;margin-bottom:18px}.driver-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(290px,1fr));gap:16px}.delivery{border-top:5px solid #0f9f95}.delivery select{width:100%;margin:10px 0}.gps-state{font-size:13px;color:#64748b}.gps-state.ok{color:#15803d;font-weight:800}.delivery-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px}.delivery-actions .btn{width:100%}@media(max-width:600px){.driver-head{align-items:flex-start;flex-direction:column}.delivery-actions{grid-template-columns:1fr}}
</style>
<div class="driver-app">
    <div class="driver-head"><div><h1>App del repartidor</h1><p class="muted">Actualiza el despacho y comparte tu ubicación durante el reparto.</p></div><span id="gps-global" class="gps-state">GPS pendiente</span></div>
    <div class="driver-grid">
    @forelse($pedidos as $pedido)
        <article class="card delivery" data-pedido="{{ $pedido->id }}">
            <span class="badge">{{ ucwords(str_replace('_', ' ', $pedido->estado)) }}</span>
            <h2>{{ $pedido->codigo_tracking }}</h2>
            <p><strong>{{ $pedido->cliente_nombre }}</strong><br><span class="muted">{{ $pedido->direccion_entrega }}</span></p>
            <p>Entrega: {{ $pedido->fecha_entrega?->format('d-m-Y') }}</p>
            <label for="estado-{{ $pedido->id }}">Estado del pedido</label>
            <select id="estado-{{ $pedido->id }}" class="driver-status">
                @foreach(['en_preparacion'=>'En preparación','listo_despacho'=>'Listo para despacho','reparto_asignado'=>'Reparto asignado','en_camino'=>'En camino','entregado'=>'Entregado'] as $value=>$label)
                    <option value="{{ $value }}" @selected($pedido->estado===$value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="delivery-actions"><button type="button" class="btn save-status">Guardar estado</button><a class="btn btn-secondary" href="{{ route('tracking.show', $pedido->codigo_tracking) }}">Ver tracking</a></div>
            <p class="gps-state card-gps">La ubicación se enviará al comenzar “En camino”.</p>
        </article>
    @empty
        <div class="card">Aún no tienes pedidos asignados.</div>
    @endforelse
    </div>
</div>
<script>
(() => {
 const csrf='{{ csrf_token() }}', repartidor={{ (int) auth()->id() }};
 const post=(url,data)=>fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify(data)}).then(async r=>{if(!r.ok)throw new Error((await r.json()).message||'No fue posible guardar');return r.json()});
 document.querySelectorAll('.save-status').forEach(btn=>btn.addEventListener('click',async()=>{const card=btn.closest('.delivery'), estado=card.querySelector('.driver-status').value;btn.disabled=true;try{await post(`/api/repartidor/pedidos/${card.dataset.pedido}/estado`,{estado,repartidor_id:repartidor});card.querySelector('.badge').textContent=card.querySelector('.driver-status').selectedOptions[0].textContent;if(estado==='en_camino') enviarGps(card,true);if(estado==='entregado') card.style.opacity='.65';}catch(e){alert(e.message)}finally{btn.disabled=false}}));
 function enviarGps(card,showError=false){if(!navigator.geolocation)return;navigator.geolocation.getCurrentPosition(async p=>{try{await post(`/api/repartidor/pedidos/${card.dataset.pedido}/gps`,{repartidor_id:repartidor,latitud:p.coords.latitude,longitud:p.coords.longitude});card.querySelector('.card-gps').textContent='Ubicación enviada '+new Date().toLocaleTimeString();card.querySelector('.card-gps').classList.add('ok');document.querySelector('#gps-global').textContent='GPS activo';document.querySelector('#gps-global').classList.add('ok')}catch(e){if(showError)alert(e.message)}},()=>{if(showError)alert('Autoriza la ubicación del navegador para iniciar el tracking.')},{enableHighAccuracy:true,maximumAge:15000,timeout:12000})}
 setInterval(()=>document.querySelectorAll('.delivery').forEach(card=>{if(card.querySelector('.driver-status').value==='en_camino')enviarGps(card)}),30000);
})();
</script>
@endsection
