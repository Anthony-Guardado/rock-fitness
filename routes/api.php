<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\DetalleMembresiaController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::post('password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);
Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);

// Rutas protegidas - necesitan token
Route::middleware('auth:api')->group(function () {

    // Auth
    Route::get('auth/me', [AuthController::class, 'me']);
    Route::post('auth/logout', [AuthController::class, 'logout']);
    Route::post('auth/refresh', [AuthController::class, 'refresh']);

    // Usuarioios
    Route::apiResource('user', UserController::class);
    Route::post('/users/{id}/restore', [UserController::class, 'restoreUser']);

    // Membresías
    Route::apiResource('metodos_pagos', MetodoPagoController::class);
    Route::apiResource('membresias', MembresiaController::class);
    Route::patch('detalle_membresias/{id}/cambiar', [DetalleMembresiaController::class, 'cambiarMembresia']);
    Route::patch('detalle_membresias/{id}/estado', [DetalleMembresiaController::class, 'cambiarEstado']);
    Route::apiResource('detalle_membresias', DetalleMembresiaController::class);

    // Pagos
    Route::post('pagos/crear', [PagoController::class, 'store']);
    Route::post('payment/crear-intent', [PaymentController::class, 'crearPaymentIntent']);
    Route::get('pagos/mispagos', [PagoController::class, 'misPagos']);
    Route::apiResource('pagos', PagoController::class)->except(['store']);

    // Reportes
    Route::get('reportes/pagos', [ReporteController::class, 'pagosExitosos']);
    Route::get('reportes/total', [ReporteController::class, 'totalPorMes']);
});

//Esta ruta no esta protegida ya q es la que muestra las membresias al usuario antes de registrase
Route::get('/membresias', [App\Http\Controllers\MembresiaController::class, 'index']);
