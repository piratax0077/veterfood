<?php

use App\Http\Controllers\Api\RepartidorController;
use App\Http\Controllers\Api\ClienteMovilController;
use App\Http\Controllers\Api\VoucherPublicoController;
use Illuminate\Support\Facades\Route;

Route::get('/cliente/tracking/{codigo}', [ClienteMovilController::class, 'tracking']);
Route::get('/cliente/ofertas', [ClienteMovilController::class, 'ofertas']);
Route::get('/vouchers/available', [VoucherPublicoController::class, 'disponibles']);
Route::post('/cliente/dispositivos/enrolar', [ClienteMovilController::class, 'enrolar']);
Route::get('/cliente/dispositivos/estado', [ClienteMovilController::class, 'dispositivo']);

Route::post('/repartidor/login', [RepartidorController::class, 'login']);
Route::get('/repartidor/{repartidorId}/pedidos', [RepartidorController::class, 'pedidos']);
Route::post('/repartidor/pedidos/{pedido}/estado', [RepartidorController::class, 'estado']);
Route::post('/repartidor/pedidos/{pedido}/gps', [RepartidorController::class, 'gps']);
Route::post('/repartidor/pedidos/{pedido}/foto-entrega', [RepartidorController::class, 'fotoEntrega']);
