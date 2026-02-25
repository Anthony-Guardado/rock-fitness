<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Metodo_Pago extends Model
{
    protected $table = "metodos_pagos";
    protected $fillable = [
        'nombre'
    ];

   public function pagos()
   {
    return $this->hasMany(Pago::class, 'metodo_pago_id');
   }
}
