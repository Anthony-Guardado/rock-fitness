<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->decimal('monto', 10, 2);
            $table->date('fecha_pago');
            $table->integer('referencia_pago');
            $table->integer('referencia_tarjeta');
            $table->unsignedBigInteger('metodo_pago_id');
            $table->foreign('metodo_pago_id')->references('id')->on('metodos_pagos');
             $table->unsignedBigInteger('detelle_membresia_id');
            $table->foreign('detelle_membresia_id')->references('id')->on('detalle_membresias');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
