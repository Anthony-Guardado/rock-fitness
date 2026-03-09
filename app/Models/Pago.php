<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $fillable = [
        'monto',
        'fecha',
        'referencia',
        'descripcion_metodo_pago',
        'metodo_pago_id',
        'detalle_membresia_id',
        'stripe_payment_intent_id',
        'estado',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha' => 'datetime',
        'referencia' => 'string',
        'descripcion_metodo_pago' => 'string',
        'stripe_payment_intent_id' => 'string',
        'estado' => 'string',
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

