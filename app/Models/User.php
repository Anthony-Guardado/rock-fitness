<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    protected $table = 'users';
    
    //Importacion para poder inactivar usuarios
    use SoftDeletes;

    protected $dates = ['deleted_at'];

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    //Sobreescribir la variable en $guard_name
    protected $guard_name = 'api';

    //Implementación de los metodos de jwt
    public function getJwtIdentifier(){
        return $this->getKey();
    }

    public function getJWTCustomClaims(){
        return[];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'nombre',
    'apellido',
    'email',
    'telefono',
    'dui',
    'password'
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function imagenes()
{

    return $this->hasMany(Imagen::class, 'usuario_id');
}

}
