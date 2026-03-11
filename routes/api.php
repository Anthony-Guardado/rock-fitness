<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DetalleMembresiaController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para authcontroller
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

// Rutas para recuperar contraseña
Route::post('password/email', [PasswordResetController::class, 'sendResetLink']);
Route::post('password/reset', [PasswordResetController::class, 'resetPassword']);
// Ruta para reactivar un usuario
Route::post('/users/{id}/restore', [UserController::class, 'restoreUser']);

// Creación de rutas para las APIs
Route::apiResource('metodos_pagos', MetodoPagoController::class);
Route::apiResource('membresias', MembresiaController::class);
Route::apiResource('detalle_membresias', DetalleMembresiaController::class);
Route::apiResource('pagos', PagoController::class);
Route::apiResource('user', UserController::class);

//  Rutas personalizadas ANTES del apiResource
Route::patch('detalle_membresias/{id}/cambiar', [DetalleMembresiaController::class, 'cambiarMembresia']);
Route::patch('detalle_membresias/{id}/estado',  [DetalleMembresiaController::class, 'cambiarEstado']);


// Rutas para la pasarela de pagos
Route::middleware('auth:api')->group(function () {
    Route::post('pagos/crear', [PagoController::class, 'store']);
    Route::post('payment/crear-intent', [PaymentController::class, 'crearPaymentIntent']);
    Route::get('pagos/mis-pagos', [PagoController::class, 'misPagos']);
});

Route::post('stripe/webhook', [StripeWebhookController::class, 'handle']);
