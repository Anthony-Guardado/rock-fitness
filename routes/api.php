<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\DetalleMembresiaController;
use App\Http\Controllers\EstadoMembresiaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ImagenController;
use App\Http\Controllers\Auth\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Rutas para authcontroller
Route::prefix('auth')->group(function(){
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function(){
        Route::get('me',[AuthController::class, 'me']);
        Route::post('logout',[AuthController::class, 'logout']);
        Route::post('refresh',[AuthController::class, 'refresh']);
    });
});


// Creación de rutas para las APIs
Route::apiResource('metodos_pagos', MetodoPagoController::class);
Route::apiResource('membresias', MembresiaController::class);
Route::apiResource('detalle_membresias', DetalleMembresiaController::class);
Route::apiResource('estados_membresias', EstadoMembresiaController::class);
Route::apiResource('pagos', PagoController::class);



