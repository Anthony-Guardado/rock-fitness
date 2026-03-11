<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use App\Models\Detalle_Membresia;

class PagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(request $request): JssonResponse
    {
        if (! $request->user()->hasRole('ADMIN')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pagos = Pago::with(['metodo_pago', 'detalle_membresia'])
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['pagos' => $pagos]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'detalle_membresia_id' => 'required|exists:detalle_membresias,id',
            'metodo_pago_id' => 'required|exists:metodos_pagos,id',
        ]);

        // Buscamos el detalle de membresía
        $detalle = Detalle_Membresia::with('membresia')->findOrFail($request->detalle_membresia_id);

        // Se manda a trear el precio de la memebreia para que se inserte en el monto de pago
        $monto = $detalle->membresia->precio;

        $pago = Pago::create([
            'monto' => $monto,
            'fecha' => now(),
            'referencia' => 'REF-'.strtoupper(uniqid()),
            'estado' => 'pendiente',
            'detalle_membresia_id' => $request->detalle_membresia_id,
            'metodo_pago_id' => $request->metodo_pago_id,
        ]);

        return response()->json([
            'pago_id' => $pago->id,
            'monto' => $monto,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Para que usuarios puedan ver sus pagos
    public function misPagos(Request $request): JsonResponse
    {
        $pagos = Pago::with(['metodo_pago', 'detalle_membresia'])
            ->whereHas('detalle_membresia', function ($query) use ($request) {
                $query->where('usuario_id', $request->user()->id);
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'pagos' => $pagos,
        ]);
    }
}
