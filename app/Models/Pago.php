<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
       protected $fillable = [
    'monto',
    'fecha',
    'referencia',
    'referencia_tarj',
    'metodo_pago_id',      // <-- Agregar esta
    'detalle_membresia_id' // <-- Agregar esta
];

    protected $casts =[
        'monto' => 'decimal:2',
        'fecha' => 'dateTime', 
        'referencia' => 'int',
        'referencia_tarj' => 'int'
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
