<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Detalle_Membresia extends Model
{
    protected $table = "detalle_membresias";

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'usuario_id',
        'membresia_id',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime'
    ];

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class,'usuario_id');
    }


     public function estado_membresia()
    {
        return $this->belongsTo(Estado_Membresia::class);
    }

    public function membresia()
    {
        return $this->belongsTo(Membresia::class);
    }
}
