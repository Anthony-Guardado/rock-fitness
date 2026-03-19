<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class ReporteController extends Controller
{
    public function reportePagos(Request $request){

        if (!$request->user()->hasRole('ADMIN')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

         //Obtenemos los datos para hacer el filtro
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;
        $estado = $request->estado;
        //Creamos la consulta para obtener la informacion
        $query = Pago::with(['detalle_membresia.user', 'detalle_membresia.membresia'])
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', 'pagado');
        //Ordenamos el resultado
        $pagos = $query->orderBy('fecha', 'desc')->get();
        $totalVentas = $pagos->sum('monto');
        $totalPagos = $pagos->count();
        //Cargamos el resultado en PDF
        $pdf = Pdf::loadView('reportes.pagos', [
            'pagos' => $pagos,
            'fechaInicio' => $fechaInicio,
            'estado' => $estado,
            'fechaFin' => $fechaFin,
            'totalVentas' => $totalVentas,
            'totalPagos' => $totalPagos
        ]);
        $pdf->setPaper('A4', 'portrait');
        $pdf->getDomPDF()->set_option("enable_php", true);
        return $pdf->stream('reporte_ordenes.pdf');
    }

}
