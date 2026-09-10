{{-- Reune los avisos de la sesion y los errores de formularios para mostrarlos como notificaciones (public/js/notificaciones.js). --}}
@php
    $notificacionesIniciales = [];
    $tiposFlash = [
        'ok' => 'exito', 'exito' => 'exito', 'success' => 'exito', 'status' => 'exito',
        'info' => 'info', 'neutral' => 'neutral',
        'aviso' => 'advertencia', 'advertencia' => 'advertencia', 'warning' => 'advertencia',
        'error' => 'error',
    ];

    foreach ($tiposFlash as $clave => $tipo) {
        $valor = session($clave);
        if (is_string($valor) && trim($valor) !== '') {
            $notificacionesIniciales[] = ['tipo' => $tipo, 'mensaje' => $valor];
        }
    }

    $avisosDetallados = session('notificaciones', []);
    if (is_array(session('notificacion'))) {
        $avisosDetallados[] = session('notificacion');
    }
    foreach ((array) $avisosDetallados as $aviso) {
        if (is_array($aviso) && (!empty($aviso['mensaje']) || !empty($aviso['titulo']))) {
            $notificacionesIniciales[] = array_intersect_key($aviso, array_flip(['tipo', 'titulo', 'mensaje', 'duracion']));
        }
    }

    if (isset($errors)) {
        foreach ($errors->getBags() as $bolsa) {
            if ($bolsa->any()) {
                $restantes = $bolsa->count() - 1;
                $notificacionesIniciales[] = [
                    'tipo' => 'error',
                    'titulo' => 'Revisa los datos',
                    'mensaje' => $bolsa->first() . ($restantes > 0 ? ' (' . $restantes . ($restantes === 1 ? ' campo más' : ' campos más') . ' por revisar)' : ''),
                ];
            }
        }
    }
@endphp
@if($notificacionesIniciales)
    <script type="application/json" id="notificaciones-iniciales">@json($notificacionesIniciales)</script>
@endif
<noscript>
    @foreach($notificacionesIniciales as $aviso)
        <div class="alert">{{ $aviso['titulo'] ?? '' }} {{ $aviso['mensaje'] ?? '' }}</div>
    @endforeach
</noscript>
