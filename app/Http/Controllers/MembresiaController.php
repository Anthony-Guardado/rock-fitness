<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membresia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\Rule;


class MembresiaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $membresias = Membresia::all();
         return response()->json($membresias, 200);

        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener las las membresias',
            ],500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{

        //valido lo que a nievel de $request para crear nuevo plan membresia
        $request->validate(
            [
                'nombre' => 'required|string|max:30|unique:membresias,nombre',
                'duracion_mes' => 'required|integer|min:0',
                'precio' => 'required|numeric|min:0'
                
            ]
        );
        $membresia = Membresia::create([
            'nombre' => $request->nombre,
            'duracion_mes' => $request->duracion_mes,
            'precio' => $request->precio
        ]);
        return response()->json([
            'message' => 'El nuevo plan de membresia  fue creada exitosamente',
            'membresia' => $membresia
        ],201);
        }catch(\Exception $e){
            return response()->json([
            'message' => 'Error al crear el plan de membresia',
            'error' => $e->getMessage(),
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show( $id)
    {
        try{
            $membresia = Membresia::find($id);

            if(!$membresia){
                return response()->json([
                    'message' => 'Membresia no encontrada'
                ],400);
            }

            return response()->json($membresia, 200);

        }catch(\Exception $e){
            return response([
                'message' =>  'Error al obtener la membresia'
            ],500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        try{
            $membresia = Membresia::findOrFail($id);
          

            $request->validate([
                'nombre' => [
                'required',
                'string',
                'max:80',
            Rule::unique('membresias', 'nombre')->ignore($id)
            ],

            'duracion_mes' => [
                'required',
                'integer', 
                'min:0'
            ],

            'precio' => [
                'required',
                'numeric',
                'min:0'
            ]
        ]);

        //actualizamos la membresia editada
        $membresia->update([
        'nombre' => $request->nombre,
        'duracion_mes' => $request->duracion_mes,
        'precio' => $request->precio
    ]);

    return response()->json([
        'message' => 'Membresía actualizada exitosamente',
        'membresia' => $membresia
    ], 200);



        }catch(\Exception $e){
            return response()->json([
            'message' => 'Error la membresia no pudo ser editada'
            ],422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $membresia = Membresia::findOrFail($id);
            if($membresia->detalle_membresia()->exists()){
                return response()->json([
                    'message' => 'Error no se puede eliminar la membresia porque tiene usuarios asociados'
                ],409);
            }
            $membresia->delete();

            return response()->json([
                'message' => 'Plan membresia eliminada correctamente'
            ],200);

        }catch(ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Membresia no fue econtrada, con el ID ='.$id
            ],404);
    }
}

}