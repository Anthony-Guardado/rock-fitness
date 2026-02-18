<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
       protected $fillable = [
    'monto',
    'fecha_pago',
    'referencia_pago',
    'referencia_tarjeta',
    'metodo_pago_id',      // <-- Agregar esta
    'detalle_membresia_id' // <-- Agregar esta
];

    protected $casts =[
        'monto' => 'decimal:2',
        'fecha_pago' => 'date', 
        'referencia_pago' => 'int',
        'referencia_tarjeta' => 'int'
    ];

    public function metodo_pago()
    {
        return $this->belongsTo(Metodo_Pago::class);
    }

     public function detalle_membresia()
    {
        return $this->belongsTo(Detalle_Membresia::class);
    }
}
