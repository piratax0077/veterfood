<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Integracion contable</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-5" style="max-width:900px">
    <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">Volver</a>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
                <div><div class="text-uppercase text-secondary small fw-bold">Contabilidad central</div><h1 class="h3 mb-1">Integracion contable</h1></div>
                <span class="badge {{ $estado['conectado'] ? 'text-bg-success' : 'text-bg-danger' }}">{{ $estado['conectado'] ? 'Conectado' : 'Sin conexion' }}</span>
            </div>
            @if($estado['conectado'])
                <h2 class="h4">{{ data_get($estado, 'resumen.cliente.nombre') }}</h2>
                <p class="text-secondary">Cliente aislado: {{ data_get($estado, 'resumen.cliente.uuid') }}</p>
                <div class="row g-3 mt-2">
                    <div class="col-md-4"><div class="p-3 bg-light rounded"><small>Documentos</small><div class="fs-3 fw-bold">{{ data_get($estado, 'resumen.documentos.total', 0) }}</div></div></div>
                    <div class="col-md-4"><div class="p-3 bg-light rounded"><small>Ingresos</small><div class="fs-3 fw-bold">${{ number_format(data_get($estado, 'resumen.movimientos.ingresos', 0), 0, ',', '.') }}</div></div></div>
                    <div class="col-md-4"><div class="p-3 bg-light rounded"><small>Egresos</small><div class="fs-3 fw-bold">${{ number_format(data_get($estado, 'resumen.movimientos.egresos', 0), 0, ',', '.') }}</div></div></div>
                </div>
                <div class="border rounded-3 p-4 mt-4 bg-white">
                    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
                        <div>
                            <h3 class="h5 mb-2">Solicitudes y documentos contables</h3>
                            <p class="text-secondary mb-0">Solicita contratos, anexos, finiquitos, liquidaciones, declaraciones, pago de cotizaciones, AFP, salud, seguro de cesantía y caja de compensación. Aquí también puedes subir respaldos, seguir su estado y recibir los documentos preparados por el contador.</p>
                        </div>
                        <a href="{{ route('contabilidad.panel') }}" class="btn btn-primary btn-lg text-nowrap">Abrir gestión documental</a>
                    </div>
                    <div class="row g-2 mt-3">
                        <div class="col-md-4"><div class="p-3 bg-light rounded h-100"><strong>1. Pedir</strong><br><small class="text-secondary">Nuevo requerimiento al contador.</small></div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded h-100"><strong>2. Revisar</strong><br><small class="text-secondary">Seguimiento y estado de cada solicitud.</small></div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded h-100"><strong>3. Recibir y firmar</strong><br><small class="text-secondary">Contratos, leyes sociales y documentos terminados.</small></div></div>
                    </div>
                </div>
            @else
                <div class="alert alert-danger mb-0"><strong>No fue posible conectar.</strong><br>{{ $estado['mensaje'] }}</div>
            @endif
        </div>
    </div>
</main>
</body>
</html>
