@extends('layouts.app')

@section('title', 'Mi encuesta de beneficios')

@section('content')
<style>
    .survey-hero{background:linear-gradient(120deg,#0f766e,#14b8a6);color:#fff;border-radius:16px;padding:26px;margin-bottom:20px;box-shadow:0 12px 30px rgba(15,118,110,.2)}
    .survey-hero h1{margin-bottom:8px;color:#fff}.survey-hero p{margin:0;max-width:850px;line-height:1.5}
    .survey-contexts{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:18px}
    .survey-card{background:#fff;border:1px solid #dbe3ee;border-radius:14px;overflow:hidden;box-shadow:0 8px 22px rgba(15,23,42,.07)}
    .survey-card-head{display:flex;gap:12px;align-items:center;padding:18px;background:#f8fafc;border-bottom:1px solid #e2e8f0}
    .survey-avatar{width:44px;height:44px;border-radius:12px;background:#ede9fe;color:#6d28d9;display:flex;align-items:center;justify-content:center;font-size:21px;font-weight:900}
    .survey-card h2{font-size:19px;margin:0 0 4px}.survey-card p{margin:0}.survey-form{padding:18px;display:grid;gap:12px}
    .interest-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
    .interest-option{display:flex;align-items:center;gap:8px;border:1px solid #dbe3ee;border-radius:8px;padding:9px;font-weight:600;margin:0}
    .interest-option input{width:auto;min-height:auto;margin:0}.voucher-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .answer-state{margin-left:auto;background:#dcfce7;color:#166534;border-radius:999px;padding:5px 9px;font-size:12px;font-weight:800}
    @media(max-width:850px){.survey-contexts{grid-template-columns:1fr}.voucher-row{grid-template-columns:1fr}}
</style>

<div class="survey-hero">
    <h1>Encuesta de beneficios VET SDI</h1>
    <p>Responde por tu perfil y por las entidades que administras. Tus respuestas ayudan a diseñar planes, vouchers, alimentos, farmacia y servicios útiles para cada realidad.</p>
</div>

<div class="survey-contexts">
@foreach($contextos as $contexto)
    @php($respuesta = $respuestas->get($contexto['tipo'] . ':' . $contexto['id']))
    <article class="survey-card">
        <div class="survey-card-head">
            <span class="survey-avatar">{{ $contexto['icono'] }}</span>
            <div><h2>{{ $contexto['titulo'] }}</h2><p class="muted">{{ $contexto['detalle'] }}</p></div>
            @if($respuesta)<span class="answer-state">Respondida</span>@endif
        </div>
        <form class="survey-form" method="POST" action="{{ route('encuesta.usuario.store') }}" data-keep-open="1">
            @csrf
            <input type="hidden" name="tipo_contexto" value="{{ $contexto['tipo'] }}">
            <input type="hidden" name="contexto_id" value="{{ $contexto['id'] }}">
            <div>
                <label>¿Qué te parece un sistema mensual con atención, descuentos y beneficios?</label>
                <select name="opinion" required>
                    <option value="">Selecciona una respuesta</option>
                    @foreach(['muy_interesante'=>'Muy interesante','interesante'=>'Interesante','neutral'=>'Neutral','poco_interesante'=>'Poco interesante','no_interesa'=>'No me interesa'] as $valor=>$texto)
                        <option value="{{ $valor }}" @selected($respuesta?->opinion === $valor)>{{ $texto }}</option>
                    @endforeach
                </select>
            </div>
            <div><label>Beneficios de mayor interés</label><div class="interest-grid">
                @foreach(['atencion_veterinaria'=>'Atención veterinaria','alimentos'=>'Alimentos','farmacia'=>'Farmacia','servicios'=>'Servicios','vouchers'=>'Vouchers','recordatorios'=>'Recordatorios'] as $valor=>$texto)
                    <label class="interest-option"><input type="checkbox" name="intereses[]" value="{{ $valor }}" @checked(in_array($valor, $respuesta?->intereses ?? []))>{{ $texto }}</label>
                @endforeach
            </div></div>
            <div class="voucher-row">
                <label class="interest-option"><input type="checkbox" name="recibe_voucher" value="1" @checked($respuesta?->recibe_voucher)> Quiero recibir vouchers</label>
                <div><label>Descuento esperado (%)</label><input type="number" name="porcentaje_descuento" min="0" max="100" value="{{ $respuesta?->porcentaje_descuento }}" placeholder="Ej: 15"></div>
            </div>
            <div><label>Comentario o necesidad</label><textarea name="comentario" rows="3" placeholder="Cuéntanos qué beneficio o servicio sería útil.">{{ $respuesta?->comentario }}</textarea></div>
            <button class="btn-success" type="submit">{{ $respuesta ? 'Actualizar respuesta' : 'Enviar respuesta' }}</button>
        </form>
    </article>
@endforeach
</div>
@endsection
