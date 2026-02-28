<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\UserController;//poner la ruta de este en delante
use App\Http\Controllers\MembresiaController;
use App\Http\Controllers\DetalleMembresiaController;
use App\Http\Controllers\EstadoMembresiaController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\ImagenController;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Creación de rutas para las APIs
Route::apiResource('metodos_pagos', MetodoPagoController::class);
Route::apiResource('users', UserController::class);
Route::apiResource('membresias', MembresiaController::class);
Route::apiResource('detalle_membresias', DetalleMembresiaController::class);
Route::apiResource('estados_membresias', EstadoMembresiaController::class);
Route::apiResource('pagos', PagoController::class);
Route::apiResource('imagenes', ImagenController::class);


