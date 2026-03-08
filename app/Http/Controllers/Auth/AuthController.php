<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Spatie\Permission\Traits\HasRoles;

class AuthController extends Controller
{
    //Metodo para autenicar


    public function login(Request $request){
        $credenciales = $request->only('email','password');
        //Evalumos si no se obtine un token valido
        if(!$token = Auth::attempt($credenciales)){
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401);
        }
        // En caso de exitoso retornamos el token
        return $this->responseWithToken($token);
    }

    //metodo para el registro de usuarios
    public function register(Request $request)
    {
        //validamos datos a t r aves de request
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:191',
            'apellido' => 'required|string|max:191',
            'email' => 'required|string|email|max:191|unique:users',
            'telefono' => 'required|string|max:20|unique:users',
            'dui' => 'required|string|max:20|unique:users',
            'password' => 'required|string|min:8'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        //creamos el usuario
        $user = User::create([
            'nombre' => $request->nombre,
            'apellido' => $request->apellido,
            'email' => $request->email,
            'telefono' => $request->telefono,
            'dui' => $request->dui,
            'password' => Hash::make($request->password),
        ]);

          // Recordatorio--Asignar rol por defecto
            $user->assignRole('CLIENTE');
        // generamos el token
        $token = JWTAuth::fromUser($user);
        // retornamos la respuesta

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ], 201);
    }

    protected function responseWithToken($token){
    return response()->json([
        'access_token' => $token, // corregí 'acces_token' (faltaba una s)
        'token_type' => 'bearer',
        'user' => auth()->user()->load('roles:id,name'),
        'expires_in' => auth()->factory()->getTTL() * 60,
    ]);
}

    public function me(){
        return response()->json(auth()->user());
    }

     // método para invalidar un token (logout)
    public function logout()
    {
        auth()->logout();

        return response()->json([
            'message' => 'Sesión cerrada correctamente',
        ]);
    }

    // método para refrescar el token
    public function refresh()
    {
        return $this->responseWithToken(auth()->refresh());
    }
}



