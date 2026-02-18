<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_Membresia extends Model
{
    protected $table = "detalle_membresias";
    protected $fillable =[
        'fecha_inicio',
        'fecha_fin',
        'usuario_id',              // <--- Agregar
    'estado_membresia_id',   // <--- Agregar
    'membresia_id'
    ];

    protected $casts = [
         'fecha_inicio' => 'date',
        'fecha_fin' => 'date'
    ];

 
    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function user()
    {
        return $this->belongTo(User::class,'usuario_id');
    }


     public function estado_membresia()
    {
        return $this->belongTo(Estado_Membresia::class);
    }

    public function membresia()
    {
        return $this->belongTo(Membresia::class);
    }
   
    
}

