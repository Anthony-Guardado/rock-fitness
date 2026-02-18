<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tipo_Usuario extends Model
{
    protected $table = "tipos_usuarios";
    
    protected $fillable =[
        'nombre'
    ];

    public function users()
{
    return $this->hasMany(User::classs);
}

}
