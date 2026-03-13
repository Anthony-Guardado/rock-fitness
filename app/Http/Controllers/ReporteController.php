<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // Lista todos los pagos exitosos - solo admin
    public function pagosExitosos(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('ADMIN')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $pagos = Pago::with(['metodo_pago', 'detalle_membresia'])
            ->where('estado', 'pagado')
            ->orderByDesc('created_at')
            ->get();

        return response()->json(['pagos' => $pagos]);
    }

    // Total recaudado por mes - solo admin
    public function totalPorMes(Request $request): JsonResponse
    {
        if (!$request->user()->hasRole('ADMIN')) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $totales = Pago::select(
                DB::raw('YEAR(fecha) as año'),
                DB::raw('MONTH(fecha) as mes'),
                DB::raw('SUM(monto) as total_recaudado'),
                DB::raw('COUNT(*) as cantidad_pagos')
            )
            ->where('estado', 'pagado')
            ->groupBy(DB::raw('YEAR(fecha)'), DB::raw('MONTH(fecha)'))
            ->orderByDesc('año')
            ->orderByDesc('mes')
            ->get();

        return response()->json(['totales' => $totales]);
    }
}
