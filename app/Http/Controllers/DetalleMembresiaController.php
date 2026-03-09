<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membresia;
use App\Models\Detalle_Memnresia;
use Carbon\Carbon;
class DetalleMembresiaController extends Controller
{
    //vamos a listar todo los registros de detalle_membresias
    public function index()
    {
        //Traemos todos los registrosy su relaciones
        $detalle = Detalle_Membresia::with(['user', 'membresia'])
        ->get();

        //Vemos si alguna embresia ya vencio
        foreach($detalle as $detalle){
            if($detalle->estado === 'activa' && carbon::now()->isAfter($detalle->fecha_fin)){
            $detalle->update(['estado' => 'inactiva']);
            }
        }
        //Devolvemos la LISTA
            return response()->json([
                'message' => 'La lista de las membresias fue obtenida con exito',
                'data' => $detalle
            ],200);
    }

    //ASIGANR MEMBRESIA EL USUARIO
    public function store(Request $request)
    {
        //voy a validar que los datos sean correctos
        $request->validate([
            'usuario_id' => 'required|exists:user,id',
            'membresia_id' => 'required|exists:membresia,id',
            'fecha_inicio' => 'required|date'
        ]);

        //aqui verifico que el usuario no tenga una membresi ya activa
        $exists = Detalle_Membresia::where('usuario_id', $request->usuario_id)
        ->where('estado', 'activa') 
        ->exists();
        
        //con un if decimo que si el usuario ya tiene una membresi, pues que me 
        //arroje un mensaje
        if($exists){
            return response()->json([
                'message' => 'Este usuario ya tiene una membresia activa',
            ],409);
        }

        //Aquí voy a calcular la fecha que va ha finalizar la memresia segun su duracion
        $membresia = Membresia::find($request->membresia_id);

        $fecha_inicio = carbon::parse($request->fecha_inicio);
        $fecha_fin = $fecha_inicio->copy()->addMonths($membresia->duracion_mes);


        //Guardamos el registro que se ha echo
        $detalle = Detalle_Membresia::create([
            'usuario_id' => $request->usuario_id,
            'membresia_id' => $request->membresia_id,
            'estado' => 'activa',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);

        //Y si pues si se asigno sin errores la membresia mandamos un mensaje
        return response()->json([
            'message' => 'La membresia fue asignada exitosamente',
            'data' => $detalle
        ],201);
    }

   
    //Aqui vamos  a ver el estado actual de la membresia del usuario
    //mas la actualizacion automatica del usuario
    public function show(string $id)
    {
        //Busco la membresia del usuario y me traigo sus relaciones
        $detalle = Detalle_Membresia::with(['membresia'])
        ->where('usuario_id', $id)
        ->first();

        //se verifica si exista
        if($detalle) {
            return response()->json([
                'message' => 'El usuario aun no tiene una membresia asignada'
            ],404);
        }

        //Con esta funcion vemos si la membresi vecnion automaticamente
        if(!$detalle->estado === 'activa' && carbon::now()->isAfter($detalle->fecha_fin)){
            $detalle->update(['estado' => 'inactiva']);
        }
            

        //Devolvemos los datos de esa membresia
        return response()->json([
            'message' => 'Membresia encontrada',
            'data' => $detalle
        ],200);
    }
    //AQUI CAMBIAMOS EL TIPO DE MEMBRESIA EL CLIENTE EN CASO EL LO DESEE

    public function cambiarMembresia(Request $request,  $usuario_id)
    {
        //CValido que silleguie el nuev tipo de membresia
        $request->validate([
            'membresia_id' => 'required|exists:membresia,id',
        ]);

        //BBusco la membresia  del usuario que esta activa
        $detalle = Detalle_Membresia::where('usuario_id', $usuario_id)
        ->where('estado', 'activa')
        ->first();

        if(!$detalle){
            return response()->json([
                'message' => 'El usuario no tiene una membresia activa para cambiar',

            ],404);
        }

        //Verifico que la membresia que se quire cambiar no sea la misma que tenga 
    //el usuario
    $nuevaMembresia = Membresia::find($request->membresia_id);
    $fecha_fin = carbon::parse($detalle->fecha_inicio)
    ->copy()
    ->addMonths($nuevaMembresia->duracion_mes);

    //Actiualiamos el Registro
    $detalle->update([
        'membresia_id' => $request->membresia_id,
        'fecha_fin' => $fecha_fin,
    ]);

    //Y si el cambio de membresia sale bien pues mandamos un mesaje
    return response()->json([
        'message' => 'El tipo de membresía fue cambiada correctamente',
        'data' => $detalle
    ],200);
    }
    //AQUI CAMBIAMOS EL ESTADO DE LA MEMBRESIA POR ALGUN INCUMPLIENMTO DE ALGUNA REGLA U OTRA COSA
    public function cambiarEstado(Reuquest $request, $usuario_id)
    {
        //Validmaos que el estados se auno de los que tenemos
        $request->validate([
            'estado' => 'required|in:activa,suspendida,cancelada,inactiva',
        ]);

        //Busco la membresia de usuario
        $detalle = Detalle_Membresia::where('usuario_id', $usuario_id)
        ->first();

        if(!$detalle){
            return response()->json([
                'message' => 'Este usuario no tiene una membresia asignada',
            ],404);
        }

        //Aqui se verifica que o se el mismo estado que se quiera cambiar
        //el que ya tenga
        if($detalle->estado === $request->estado){
            return response()->json([
                'message' => 'La membresia ya se encuentra en ese estado',
            ],409);
        }

        //Si todo sale bien puesya actuaizamos 
        $detalle->update([
            'estado' => $request->estado,
        ]);

        return respose()->json([
            'message' => 'El estado de la membresia se actualizo correctamente',
            'data' => $detalle
        ],200);
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
