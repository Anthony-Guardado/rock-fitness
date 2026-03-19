<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Membresia;
use App\Models\Membresia;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DetalleMembresiaController extends Controller
{
    // vamos a listar todo los registros de detalle_membresias
    public function index()
    {

        $detalle = Detalle_Membresia::with(['user', 'membresia'])
            ->get();


        foreach ($detalle as $item) {
            if ($item->estado === 'activa' && $item->fecha_fin && now()->greaterThanOrEqualTo($item->fecha_fin)) {
                $item->update(['estado' => 'inactiva']);
            }
        }

        return response()->json([
            'message' => 'La lista de las membresías fue obtenida con éxito',
            'data' => $detalle,
        ], 200);
    }

    // ASIGANR MEMBRESIA EL USUARIO
    public function store(Request $request)
    {

        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'membresia_id' => 'required|exists:membresias,id',
            'fecha_inicio' => 'required|date',
        ]);


        $exists = Detalle_Membresia::where('usuario_id', $request->usuario_id)
            ->where('estado', 'activa')
            ->exists();


        if ($exists) {
            return response()->json([
                'message' => 'Este usuario ya tiene una membresía activa',
            ], 409);
        }

        // Aquí voy a calcular la fecha que va ha finalizar la memresia segun su duracion
        $membresia = Membresia::find($request->membresia_id);
        $fecha_inicio = Carbon::parse($request->fecha_inicio);
        $fecha_fin = $fecha_inicio->copy()->addMonths($membresia->duracion_mes);

        // mandamos que la mambresia es cttiva porque en la DB es inactiva por defaAUTL
        $detalle = Detalle_Membresia::create([
            'usuario_id' => $request->usuario_id,
            'membresia_id' => $request->membresia_id,
            'estado' => 'inactiva',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);


        return response()->json([
            'message' => 'La membresía fue asignada exitosamente',
            'data' => $detalle,
        ], 201);
    }


    public function show(string $id)
    {
        // Busco la membresia del usuario y me traigo sus relaciones
        $detalle = Detalle_Membresia::with(['membresia', 'user'])
            ->find($id);


        if (! $detalle) {
            return response()->json([
                'message' => 'La membresía no existe',
            ], 404);
        }

        if ($detalle->estado === 'activa' && now()->greaterThanOrEqualTo($detalle->fecha_fin)) {
            $detalle->update(['estado' => 'inactiva']);
        }


        return response()->json([
            'message' => 'Membresía encontrada',
            'data' => $detalle,
        ], 200);
    }


    public function cambiarMembresia(Request $request, $usuario_id)
    {
        // CValido que silleguie el nuev tipo de membresia
        $request->validate([
            'membresia_id' => 'required|exists:membresias,id',
        ]);

        // BBusco la membresia  del usuario que esta activa
        $detalle = Detalle_Membresia::where('usuario_id', $usuario_id)
            ->where('estado', 'activa')
            ->first();

        if (! $detalle) {
            return response()->json([
                'message' => 'El usuario no tiene una membresía activa para cambiar',

            ], 404);
        }

        if ($detalle->membresia_id == $request->membresia_id) {
            return response()->json([
                'message' => 'El usuario ya cuenta con ese tipo de membresía',
            ], 409);
        }

        // Recalculo fecha_fin con el nuevo plan desd la fecha del ininio original

        $nuevaMembresia = Membresia::find($request->membresia_id);
        $fecha_fin = Carbon::parse($detalle->fecha_inicio)
            ->copy()
            ->addMonths($nuevaMembresia->duracion_mes);


        $detalle->update([
            'membresia_id' => $request->membresia_id,
            'fecha_fin' => $fecha_fin,
        ]);


        return response()->json([
            'message' => 'El tipo de membresía fue cambiada correctamente',
            'data' => $detalle,
        ], 200);
    }


    public function cambiarEstado(Request $request, $id)
    {
        // Validmaos que el estados se auno de los que tenemos
        $request->validate([
            'estado' => 'required|in:activa,suspendida,cancelada,inactiva',
        ]);

        //
        $detalle = Detalle_Membresia::where('usuario_id', $id)
            ->first();

        if (! $detalle) {
            return response()->json([
                'message' => 'Este usuario aún no tiene una membresía asignada para cambiar de estado',
            ], 404);
        }


        if ($detalle->estado === $request->estado) {
            return response()->json([
                'message' => 'La membresía ya se encuentra en ese estado',
            ], 409);
        }


        $detalle->update([
            'estado' => $request->estado,
        ]);

        return response()->json([
            'message' => 'El estado de la membresía se actualizo correctamente',
            'data' => $detalle,
        ], 200);
    }

    public function miMembresia(Request $request)
    {
        $detalle = Detalle_Membresia::with(['membresia'])
            ->where('usuario_id', $request->user()->id)
            ->latest()
            ->first();

        if (! $detalle) {
            return response()->json([
                'message' => 'No tienes una membresía asignada',
            ], 404);
        }

        if ($detalle->estado === 'activa' && now()->greaterThanOrEqualTo($detalle->fecha_fin)) {
            $detalle->update(['estado' => 'inactiva']);
        }

        return response()->json([
            'data' => $detalle,
        ], 200);
    }

    public function seleccionarMembresia(Request $request)
    {

        $request->validate([
            'membresia_id' => 'required|exists:membresias,id',
        ]);


        $detalle = Detalle_Membresia::where('usuario_id', $request->user()->id)
            ->latest()
            ->first();


        if (! $detalle) {
            return response()->json([
                'message' => 'No se encontró el detalle de membresía del usuario',
            ], 404);
        }


        if ($detalle->membresia_id == $request->membresia_id && $detalle->estado === 'activa') {
            return response()->json([
                'message' => 'Ya tienes activa esa membresía',
            ], 409);
        }


        $detalle->update([
            'membresia_id' => $request->membresia_id,
            'estado' => 'inactiva',
            'fecha_inicio' => null,
            'fecha_fin' => null,
        ]);

        
        return response()->json([
            'message' => 'La membresía fue seleccionada correctamente',
            'data' => $detalle,
        ], 200);
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
