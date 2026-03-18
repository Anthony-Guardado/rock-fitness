<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reporte de Pagos - GymRook</title>
    <style>
        @include('reportes.CSS.pdf')
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td width="20%">
                <img src="{{ public_path('images/logo.jpeg') }}" alt="logo GymRook" class="logo">
            </td>
            <td width="80%">
                <div class="empresa">
                    GYMROOK S.A. de C.V.
                </div>
                <div class="titulo">
                    REPORTE DE PAGOS
                </div>
                <div class="subtitulo">
                    Reporte de pagos procesados del {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    @foreach ($pagos as $pago)
        <div class="orden">
            <strong>Referencia:</strong> {{ $pago->referencia ?? 'Pago sin referencia' }} <br>
            <strong>Cliente:</strong> {{ $pago->detalle_membresia->user->nombre ?? 'Usuario no encontrado' }} <br>
            <strong>Fecha de Pago:</strong> {{ $pago->fecha->format('d/m/Y h:i A') }} <br>
            <strong>Estado:</strong> <span style="text-transform: capitalize;">{{ $pago->estado }}</span>

             <table>
                <thead>
                    <tr>
                        <th>Membresía Adquirida</th>
                        <th>Inicio del Plan</th>
                        <th>Fin del Plan</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            {{ $pago->detalle_membresia->membresia->nombre ?? 'Plan Estándar' }}
                        </td>
                        <td style="text-align:center;">
                            {{ $pago->detalle_membresia->fecha_inicio ? $pago->detalle_membresia->fecha_inicio->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td style="text-align:center;">
                            {{ $pago->detalle_membresia->fecha_fin ? $pago->detalle_membresia->fecha_fin->format('d/m/Y') : 'N/A' }}
                        </td>
                        <td>
                            $ {{ number_format($pago->monto, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="total">
                Total Pagado: ${{ number_format($pago->monto, 2) }}
            </div>
        </div>
    @endforeach

    <div class="resumen">
        <strong>Total de Pagos Procesados: </strong> {{ $totalPagos }} <br>
        <strong>Total: </strong> ${{ number_format($totalVentas, 2) }}
    </div>

    <script type="text/php">
        if ( isset($pdf) ) {
            $font = $fontMetrics->get_font("DejaVu Sans", "normal");
            $pdf->page_text(500, 820, "Página {PAGE_NUM} de {PAGE_COUNT}", $font, 9);
        }
    </script>
</body>
</html>
