<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estado_Membresia extends Model
{
    protected $table = "estados_membresias";
   protected $fillable =[
        'nombre',
        'descripcion',
   ];
       
    public function detalle_membresia()
    {
        return $this->hasMany(Detalle_Membresia::class);
    }
}
