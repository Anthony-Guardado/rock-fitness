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
    'metodo_pago_id',      
    'detalle_membresia_id' 
];

    protected $casts =[
        'monto' => 'decimal:2',
        'fecha' => 'datetime', 
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
