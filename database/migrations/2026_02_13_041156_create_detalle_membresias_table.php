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
        Schema::create('detalle_membresias', function (Blueprint $table) {
        $table->id();
        $table->dateTime('fecha_inicio');
        $table->dateTime('fecha_fin');
        $table->unsignedBigInteger('usuario_id');
        $table->foreign('usuario_id')->references('id')->on('users');
        $table->unsignedBigInteger('membresia_id');
        $table->foreign('membresia_id')->references('id')->on('membresias');
        // ENUM para estado de la membresía
        $table->enum('estado', [
            'activa',
            'inactiva',
            'cancelada',
            'suspendida'
        ])->default('inactiva');

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_membresias');
    }
};
