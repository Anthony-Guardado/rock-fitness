<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\Metodo_Pago; 

class MetodoPagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{

        $metodopago = Metodo_Pago::all();

        return response()->json([
            'message' => $metodopago
        ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'error al obtener los metodos de pago',
                'error' => $e->getMessage()
            ], 500);
            
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {try {
        // 1. Validación corregida (sin el pipe al final)
        $request->validate([
            'nombre' => 'required|string|min:3|max:22|unique:metodos_pagos,nombre'
        ]);

        // 2. Creación corregida (sin el 201 adentro)
        $metodopago = Metodo_Pago::create([
            'nombre' => $request->nombre
        ]);

        // 3. Mensaje lógico corregido
        return response()->json([
            'message' => 'Método de pago registrado correctamente',
            'metodopago' => $metodopago
        ], 201);

    // 4. Excepciones bien escritas
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'message' => 'Error de validación.',
            'errores' => $e->errors()
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error interno del servidor',
            'errores' => $e->getMessage()
        ], 500);
    }
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
}
