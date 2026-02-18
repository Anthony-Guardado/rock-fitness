<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Membresia extends Model
{
    protected $fillable =[
        'nombre',
        'duracion_mes',
        'precio'
    ];

     protected $casts = [
        'duracion_mes' => 'integer',
        'precio' => 'decimal:2'
    ];

    public function detalle_membresia()
    {
        return $this->hasMany(Detalle_Membresia::class);
    }
}
