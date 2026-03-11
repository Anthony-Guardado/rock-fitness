<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Membresia;
use App\Models\Detalle_Membresia;
use Carbon\Carbon;
class DetalleMembresiaController extends Controller
{
    //vamos a listar todo los registros de detalle_membresias
    public function index()
    {
        //Traemos todos los registrosy su relacion_es
        $detalle = Detalle_Membresia::with(['user', 'membresia'])
        ->get();

        //Vemos si alguna embresia ya vencio
        //aqui hubo un pequeñ cambio ya que esta funcion si el si la membresía se paso del año 
        //automaticamente pus la pone como vencida
        foreach($detalle as $item)
            if($item->estado === 'activa' && Carbon::now()->isAfter($item->fecha_fin)){
            $item->update(['estado' => 'inactiva']);
            }
        
        //Devolvemos la LISTA
            return response()->json([
                'message' => 'La lista de las membresías fue obtenida con éxito',
                'data' => $detalle
            ],200);
    }

    //ASIGANR MEMBRESIA EL USUARIO
    public function store(Request $request)
    {
        //voy a validar que los datos sean correctos
        $request->validate([
            'usuario_id' => 'required|exists:users,id',
            'membresia_id' => 'required|exists:membresias,id',
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
                'message' => 'Este usuario ya tiene una membresía activa',
            ],409);
        }

        //Aquí voy a calcular la fecha que va ha finalizar la memresia segun su duracion
        $membresia = Membresia::find($request->membresia_id);
        $fecha_inicio = Carbon::parse($request->fecha_inicio);
        $fecha_fin = $fecha_inicio->copy()->addMonths($membresia->duracion_mes);


        //Guardamos el registro que se ha echo
        //mandamos que la mambresia es cttiva porque en la DB es inactiva por defaAUTL
        $detalle = Detalle_Membresia::create([
            'usuario_id' => $request->usuario_id,
            'membresia_id' => $request->membresia_id,
            'estado' => 'activa',
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
        ]);

        //Y si pues si se asigno sin errores la membresia mandamos un mensaje
        return response()->json([
            'message' => 'La membresía fue asignada exitosamente',
            'data' => $detalle
        ],201);
    }

   
    //Aqui vamos  a ver el estado actual de la membresia del usuario
    //mas la actualizacion automatica del usuario
    public function show(string $id)
    {
        //Busco la membresia del usuario y me traigo sus relaciones
        $detalle = Detalle_Membresia::with(['membresia', 'user'])
        ->find($id);
        

        //se verifica si exista
        if(!$detalle) {
            return response()->json([
                'message' => 'La membresía no existe'
            ],404);
        }

        //Con esta funcion vemos si la membresi vecnion automaticamente
        if($detalle->estado === 'activa' && Carbon::now()->isAfter($detalle->fecha_fin)){
            $detalle->update(['estado' => 'inactiva']);
        }
            

        //Devolvemos los datos de esa membresia
        return response()->json([
            'message' => 'Membresía encontrada',
            'data' => $detalle
        ],200);
    }
    //AQUI CAMBIAMOS EL TIPO DE MEMBRESIA EL CLIENTE EN CASO EL LO DESEE
    //YA SEA BÁSICA , PREMIUN O VIP

    public function cambiarMembresia(Request $request,  $usuario_id)
    {
        //CValido que silleguie el nuev tipo de membresia
        $request->validate([
            'membresia_id' => 'required|exists:membresias,id',
        ]);

        //BBusco la membresia  del usuario que esta activa
        $detalle = Detalle_Membresia::where('usuario_id', $usuario_id)
        ->where('estado', 'activa')
        ->first();

        if(!$detalle){
            return response()->json([
                'message' => 'El usuario no tiene una membresía activa para cambiar',

            ],404);
        }
        //verifico que no sea la misma membresia la que se le asigne
    if($detalle->membresia_id == $request->membresia_id) {
        return response()->json([
            'message' => 'El usuario ya cuenta con ese tipo de membresía'
        ],409);
    }


        //Recalculo fecha_fin con el nuevo plan desd la fecha del ininio original

    $nuevaMembresia = Membresia::find($request->membresia_id);
    $fecha_fin = Carbon::parse($detalle->fecha_inicio)
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
    
    
    //AQUI CAMBIAMOS EL ESTADO DE LA MEMBRESIA (Activa, Suspendida, Cancelada, Inactiva)
    public function cambiarEstado(Request $request, $id)
    {
        //Validmaos que el estados se auno de los que tenemos
        $request->validate([
            'estado' => 'required|in:activa,suspendida,cancelada,inactiva',
        ]);

        //
        $detalle = Detalle_Membresia::where('usuario_id', $id)
        //->where('estado' , '!=' , 'cancelada')
        //->lastest() // lastest lo que es que toma la membresia mas reciente en caso de que el usuario tenga varias
        ->first();

        if(!$detalle){
            return response()->json([
                'message' => 'Este usuario aún no tiene una membresía asignada para cambiar de estado',
            ],404);
        }

        //Aqui se verifica que o se el mismo estado que se quiera cambiar
        //el que ya tenga
        if($detalle->estado === $request->estado){
            return response()->json([
                'message' => 'La membresía ya se encuentra en ese estado',
            ],409);
        }

        //Si todo sale bien puesya actuaizamos 
        $detalle->update([
            'estado' => $request->estado,
        ]);

        return response()->json([
            'message' => 'El estado de la membresía se actualizo correctamente',
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
