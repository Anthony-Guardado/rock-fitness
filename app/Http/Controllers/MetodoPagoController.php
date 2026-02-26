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
 
             //marcas ordenadas
        $metodopago = Metodo_Pago::orderBy('id','desc')->get();
        return response()->json([
            'metodos' => $metodopago
        ],200);

        return response()->json([
            'message' => $metodopago
        ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener los metodos de pago',
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
            'message' => 'Metodo de pago registrado correctamente',
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
    try{
            $metodopago = Metodo_Pago::findOrfail($id);
            return response()->json($metodopago);

        }catch(\exception $e){
              return response()->json([
                'message' => 'Metdodo de pago no encontrado',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try{
             //primero obtenemos el registro de la bd
            $metodopago = Metodo_Pago::findOrFail($id);
            //aplicamos validaciones a nivel de request
            $request->validate(
                [
                    'nombre' => [
                        'required',
                        'string',
                        'min:2',
                        'max:80',
                        Rule::unique('metodos_pagos', 'nombre')->ignore($id)
                    ]
                ],
                [
                    'nombre.unique' => 'Ya existe un metodo de pago con este nombre en la base de datos'
                ]
            );

            //mandamos a actualizar el registro
            $metodopago->update([
                'nombre' =>$request->nombre
            ]);
            return response()->json([
                'message' => 'Metodo de pago actualizado correctamente',
                'metodopago' => $metodopago
            ],202);    
        }catch(\Exception $e){
             return response()->json([
                'message' => 'Metodo de pago no encontrado',
                'error' => $e->getMessage()
            ],500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         try {
            $metodopago = Metodo_Pago::with('pagos')->findOrFail($id);

            if ($metodopago->pagos()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar el metodo de pago porque tiene pagos asociados.'
                ], 409);
            }

            $metodopago->delete();

            return response()->json([
                'message' => 'Metodo de pago eliminado correctamente.'
            ], 200);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Metodo de pago no encontrado, con el ID = ' .$id
            ], 404);
        }
    }
}
