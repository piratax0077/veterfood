<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CentralVentasController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EncuestaUsuarioController;
use App\Http\Controllers\Contabilidad\EscritorioContabilidadController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\VetSdiSsoController;
use App\Http\Controllers\VoucherUsuarioController;
use App\Models\Pedido;
use Illuminate\Support\Facades\Route;

Route::get('/', [AuthController::class, 'inicio'])->name('inicio');

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.store');
Route::post('/inscripcion', [AuthController::class, 'registrarCliente'])->name('registro.cliente');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/sso/vet-sdi', [VetSdiSsoController::class, 'login'])
    ->middleware('throttle:30,1')
    ->name('sso.vet-sdi');

Route::middleware('auth')->group(function () {
    Route::get('/mis-vouchers', [VoucherUsuarioController::class, 'index'])->name('vouchers.usuario');
    Route::get('/mi-encuesta', [EncuestaUsuarioController::class, 'index'])->name('encuesta.usuario');
    Route::post('/mi-encuesta', [EncuestaUsuarioController::class, 'store'])->name('encuesta.usuario.store');
    Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::get('/two-factor/qr', [TwoFactorController::class, 'qr'])->name('two-factor.qr');
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/confirm', [TwoFactorController::class, 'confirm'])->name('two-factor.confirm');
});

Route::middleware('auth')->get('/integraciones/contabilidad-central', [\App\Http\Controllers\ContabilidadIntegracionController::class, 'index'])
    ->name('integraciones.contabilidad-central');

Route::get('/redirect-by-role', function () {
    $user = auth()->user();

    return match ($user?->rol) {
        'admin' => redirect()->route('admin.dashboard'),
        'contabilidad' => redirect()->route('contabilidad.panel'),
        'central_ventas', 'secretaria', 'secretaria_veterchile', 'secretaria_clinica' => redirect()->route('central.panel'),
        'repartidor' => redirect()->route('repartidor.pedidos'),
        'auditor' => redirect()->route('auditor.vouchers.index'),
        'cliente', 'dueno_mascota' => redirect()->route('cliente.panel'),
        default => redirect()->route('tienda.catalogo'),
    };
})->middleware('auth')->name('redirect.role');

Route::get('/tienda', [TiendaController::class, 'catalogo'])->name('tienda.catalogo');
Route::post('/tienda/productos/{producto}/agregar', [TiendaController::class, 'agregar'])->name('tienda.agregar');
Route::get('/tienda/carro', [TiendaController::class, 'carro'])->name('tienda.carro');
Route::post('/tienda/carro', [TiendaController::class, 'actualizarCarro'])->name('tienda.carro.actualizar');
Route::get('/tienda/checkout', [TiendaController::class, 'checkout'])->name('tienda.checkout');
Route::get('/tienda/ciudades/{region}', [TiendaController::class, 'ciudadesPorRegion'])->whereNumber('region')->name('tienda.ciudades');
Route::post('/tienda/checkout', [TiendaController::class, 'confirmar'])->name('tienda.confirmar');
Route::get('/tracking/{codigo}', [TiendaController::class, 'tracking'])->middleware('throttle:60,1')->name('tracking.show');
Route::get('/vouchers/{voucher}/qr', [AdminController::class, 'voucherQr'])->middleware('throttle:30,1')->name('vouchers.qr');
Route::get('/vouchers/verificar/{codigo}', [AdminController::class, 'verificarVoucher'])->middleware('throttle:30,1')->name('vouchers.verificar');

Route::middleware(['auth', 'role:cliente,dueno_mascota'])->prefix('cliente')->name('cliente.')->group(function () {
    Route::get('/panel', [ClienteController::class, 'panel'])->name('panel');
    Route::post('/mascotas', [ClienteController::class, 'guardarMascota'])->name('mascotas.store');
    Route::post('/direcciones', [ClienteController::class, 'guardarDireccion'])->name('direcciones.store');
    Route::post('/planes', [ClienteController::class, 'guardarPlan'])->name('planes.store');
    Route::post('/planes/{plan}/anular', [ClienteController::class, 'anularPlan'])->name('planes.anular');
    Route::get('/planes/pago/{slug}', [ClienteController::class, 'pagoPlan'])->name('planes.pago');
    Route::post('/planes/pago/{slug}', [ClienteController::class, 'confirmarPagoPlan'])->name('planes.pago.confirmar');
});

Route::middleware(['auth', 'role:admin,contabilidad', '2fa', 'secure.session'])->group(function () {
    Route::get('/contabilidad', [EscritorioContabilidadController::class, 'instituciones'])->name('contabilidad.panel');
    Route::post('/contabilidad/instituciones', [EscritorioContabilidadController::class, 'storeInstitucion'])->name('contabilidad.instituciones.store');
    Route::post('/contabilidad/instituciones/{centroMedico}/aceptar', [EscritorioContabilidadController::class, 'aceptarRelacion'])->name('contabilidad.instituciones.aceptar');
});

Route::middleware(['auth', 'role:admin', '2fa', 'secure.session'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/integraciones/vet-sdi/sincronizar', [AdminController::class, 'sincronizarVetSdi'])->name('integraciones.vet-sdi.sync');
    Route::post('/contabilidad/relaciones/{centroMedico}/{user}/aprobar', [AdminController::class, 'aprobarRelacionContable'])->name('contabilidad.relaciones.aprobar');
    Route::get('/dashboard-financiero', [AdminController::class, 'dashboardFinancieroAnalisis'])->name('financiero');
    Route::get('/contabilidad-integracion', [AdminController::class, 'contabilidadIntegracion'])->name('contabilidad.integracion');
    Route::post('/contabilidad-integracion/contadores', [AdminController::class, 'guardarContadorApi'])->name('contabilidad.contadores.store');
    Route::get('/usuarios', [AdminController::class, 'usuarios'])->name('usuarios.index');
    Route::get('/usuarios/crear', [AdminController::class, 'crearUsuario'])->name('usuarios.create');
    Route::get('/usuarios/{user}/editar', [AdminController::class, 'editarUsuario'])->name('usuarios.edit');
    Route::patch('/usuarios/{user}', [AdminController::class, 'actualizarUsuario'])->name('usuarios.update');
    Route::get('/clientes', [AdminController::class, 'clientes'])->name('clientes.index');
    Route::get('/clientes/crear', [AdminController::class, 'crearCliente'])->name('clientes.create');
    Route::post('/clientes', [AdminController::class, 'guardarCliente'])->name('clientes.store');
    Route::get('/clientes/{user}/editar', [AdminController::class, 'editarCliente'])->name('clientes.edit');
    Route::patch('/clientes/{user}', [AdminController::class, 'actualizarCliente'])->name('clientes.update');
    Route::patch('/clientes/{user}/estado', [AdminController::class, 'estadoCliente'])->name('clientes.estado');
    Route::get('/planes-comerciales', [AdminController::class, 'planesComerciales'])->name('planes.comerciales');
    Route::get('/encuestas-profesionales', [AdminController::class, 'encuestasProfesionales'])->name('encuestas.profesionales');
    Route::get('/locales', [AdminController::class, 'locales'])->name('locales.index');
    Route::get('/locales/crear', [AdminController::class, 'crearLocal'])->name('locales.create');
    Route::post('/locales', [AdminController::class, 'local'])->name('locales.store');
    Route::get('/locales/{local}/editar', [AdminController::class, 'editarLocal'])->name('locales.edit');
    Route::patch('/locales/{local}', [AdminController::class, 'actualizarLocal'])->name('locales.update');
    Route::patch('/locales/{local}/estado', [AdminController::class, 'estadoLocal'])->name('locales.estado');
    Route::get('/profesionales', [AdminController::class, 'profesionales'])->name('profesionales.index');
    Route::get('/profesionales/crear', [AdminController::class, 'crearProfesional'])->name('profesionales.create');
    Route::post('/profesionales', [AdminController::class, 'profesional'])->name('profesionales.store');
    Route::get('/profesionales/{profesional}/editar', [AdminController::class, 'editarProfesional'])->name('profesionales.edit');
    Route::patch('/profesionales/{profesional}', [AdminController::class, 'actualizarProfesional'])->name('profesionales.update');
    Route::get('/mascotas', [AdminController::class, 'mascotas'])->name('mascotas.index');
    Route::get('/mascotas/crear', [AdminController::class, 'crearMascota'])->name('mascotas.create');
    Route::get('/mascotas/{mascota}/editar', [AdminController::class, 'editarMascota'])->name('mascotas.edit');
    Route::patch('/mascotas/{mascota}', [AdminController::class, 'actualizarMascota'])->name('mascotas.update');
    Route::get('/vendedores', [AdminController::class, 'vendedores'])->name('vendedores.index');
    Route::get('/vendedores/crear', [AdminController::class, 'crearVendedor'])->name('vendedores.create');
    Route::post('/vendedores', [AdminController::class, 'guardarVendedor'])->name('vendedores.store');
    Route::get('/vendedores/{user}/editar', [AdminController::class, 'editarVendedor'])->name('vendedores.edit');
    Route::patch('/vendedores/{user}', [AdminController::class, 'actualizarVendedor'])->name('vendedores.update');
    Route::patch('/vendedores/{user}/estado', [AdminController::class, 'estadoVendedor'])->name('vendedores.estado');
    Route::get('/repartidores', [AdminController::class, 'repartidores'])->name('repartidores.index');
    Route::get('/repartidores/crear', [AdminController::class, 'crearRepartidor'])->name('repartidores.create');
    Route::post('/repartidores', [AdminController::class, 'guardarRepartidor'])->name('repartidores.store');
    Route::get('/repartidores/{user}/editar', [AdminController::class, 'editarRepartidor'])->name('repartidores.edit');
    Route::patch('/repartidores/{user}', [AdminController::class, 'actualizarRepartidor'])->name('repartidores.update');
    Route::patch('/repartidores/{user}/estado', [AdminController::class, 'estadoRepartidor'])->name('repartidores.estado');
    Route::get('/repartos/historial', [AdminController::class, 'historialRepartos'])->name('repartos.historial');
    Route::patch('/repartos/{pedido}/feedback', [AdminController::class, 'actualizarFeedbackReparto'])->name('repartos.feedback');
    Route::get('/vouchers', [AdminController::class, 'vouchers'])->name('vouchers.index');
    Route::get('/vouchers/crear', [AdminController::class, 'crearVoucher'])->name('vouchers.create');
    Route::post('/vouchers', [AdminController::class, 'guardarVoucher'])->name('vouchers.store');
    Route::get('/vouchers/{voucher}', [AdminController::class, 'voucherDetalle'])->name('vouchers.show');
    Route::patch('/vouchers/{voucher}/estado', [AdminController::class, 'estadoVoucher'])->name('vouchers.estado');
    Route::get('/servicios', [AdminController::class, 'servicios'])->name('servicios.index');
    Route::post('/servicios', [AdminController::class, 'guardarServicio'])->name('servicios.store');
    Route::get('/servicios/{producto}/editar', [AdminController::class, 'editarServicio'])->name('servicios.edit');
    Route::patch('/servicios/{producto}', [AdminController::class, 'actualizarServicio'])->name('servicios.update');
    Route::patch('/servicios/{producto}/estado', [AdminController::class, 'estadoServicio'])->name('servicios.estado');
    Route::get('/finanzas-vouchers', [AdminController::class, 'finanzasVouchers'])->name('finanzas.vouchers');
    Route::get('/finanzas-vouchers/reparto', [AdminController::class, 'finanzasVouchersReparto'])->name('finanzas.vouchers.reparto');
    Route::get('/finanzas-vouchers/canje', [AdminController::class, 'finanzasVouchersCanje'])->name('finanzas.vouchers.canje');
    Route::get('/finanzas-vouchers/cobros', [AdminController::class, 'finanzasVouchersCobros'])->name('finanzas.vouchers.cobros');
    Route::get('/finanzas-vouchers/rendiciones', [AdminController::class, 'finanzasVouchersRendiciones'])->name('finanzas.vouchers.rendiciones');
    Route::get('/finanzas-vouchers/liquidaciones', [AdminController::class, 'finanzasVouchersLiquidaciones'])->name('finanzas.vouchers.liquidaciones');
    Route::post('/finanzas-vouchers/movimientos', [AdminController::class, 'guardarMovimientoVoucher'])->name('finanzas.vouchers.movimientos');
    Route::post('/bodegas', [AdminController::class, 'bodega'])->name('bodegas.store');
    Route::post('/usuarios', [AdminController::class, 'usuario'])->name('usuarios.store');
    Route::post('/mascotas', [AdminController::class, 'mascota'])->name('mascotas.store');
    Route::post('/existencias', [AdminController::class, 'existencia'])->name('existencias.store');
    Route::post('/planes-pedido', [AdminController::class, 'planPedido'])->name('planes.store');
    Route::post('/generar-pedidos', [AdminController::class, 'generarPedidos'])->name('pedidos.generar');
    Route::post('/rutas', [AdminController::class, 'ruta'])->name('rutas.store');
});

Route::middleware(['auth', 'role:auditor', '2fa', 'secure.session'])->prefix('auditor-vouchers')->name('auditor.vouchers.')->group(function () {
    Route::get('/', [AdminController::class, 'auditorVouchers'])->name('index');
});

Route::middleware(['auth', 'role:admin,auditor', '2fa', 'secure.session'])->prefix('auditor-vouchers')->name('auditor.vouchers.')->group(function () {
    Route::get('/alertas', [AdminController::class, 'auditorVouchersAlertas'])->name('alertas');
});

Route::middleware(['auth', 'role:admin,central_ventas,secretaria,secretaria_veterchile,secretaria_clinica', '2fa', 'secure.session'])->group(function () {
    Route::get('/central-ventas', [CentralVentasController::class, 'panel'])->name('central.panel');
    Route::get('/central-ventas/ventas/{vista?}', [CentralVentasController::class, 'ventas'])->name('central.ventas');
    Route::get('/central-ventas/rubros/{rubro}', [CentralVentasController::class, 'rubro'])->name('central.rubro');
    Route::get('/central-ventas/rubros/{rubro}/ingreso', [CentralVentasController::class, 'ingreso'])->name('central.ingreso');
    Route::post('/central-ventas/productos', [CentralVentasController::class, 'producto'])->name('central.productos');
    Route::post('/central-ventas/pedidos/{pedido}/asignar', [CentralVentasController::class, 'asignar'])->name('central.asignar');
    Route::post('/central-ventas/pedidos/{pedido}/estado', [CentralVentasController::class, 'estado'])->name('central.estado');
    Route::post('/central-ventas/pedidos/{pedido}/avisar-entrega', [CentralVentasController::class, 'avisarEntregaMensual'])->name('central.avisar.entrega');
});

Route::middleware(['auth', 'role:repartidor'])->group(function () {
    Route::get('/repartidor/pedidos', function () {
        return view('repartidor.pedidos', [
            'pedidos' => Pedido::with('items')->where('repartidor_id', auth()->id())->latest()->get(),
        ]);
    })->name('repartidor.pedidos');
});
