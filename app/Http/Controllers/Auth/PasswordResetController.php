<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
// Es la importación hacia donde se mandará el token
use App\Mail\ResetPasswordMail;
// Acceso a las funciones de Laravel
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
// Genera los token aleatorios
use Illuminate\Support\Str;
// Librería de fecha y hora
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    // Envia el enlace al correo.
    public function sendResetLink(Request $request)
    {
        // validacion
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // genera un token aleatorio
        $token = Str::random(64);

        // Guardar o actualizar el token en la tabla password_reset_tokens
        DB::table('password_reset_tokens')->updateOrInsert(
            // Condicion de busqueda del correo
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => Carbon::now() // Marca de tiempo actual para controlar la expiración
            ]
        );

        // manda el email a mailable
        Mail::to($request->email)->send(new ResetPasswordMail($token, $request->email));

        // retorna la respuesta
        return response()->json([
            'message' => 'Hemos enviado un código a tu correo electrónico.'
        ], 200);
    }

    // procesa el cambio de la contraseña
    public function resetPassword(Request $request)
    {
        // validacion de los datos
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => 'required|min:8|confirmed',
        ]);

        // verifica si es el token que se le mando al correo
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        // en caso de que el token no pertenezca al email se mandara el error
        if (!$record) {
            return response()->json([
                'message' => 'El código proporcionado es inválido o no corresponde a este correo.'
            ], 400);
        }

        // valida el tiempo del token
        $expiresAt = Carbon::parse($record->created_at)->addMinutes(60);

        if (Carbon::now()->gt($expiresAt)) {
            // Si expiró se borra el token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'El código ha expirado.'], 400);
        }


        // Buscamos que el usuario exista o no este desactivado
        $user = User::where('email', trim($request->email))->first();

        // Verificamos que el usuario realmente exista o no este inactivo
        if (!$user) {
            return response()->json([
                'message' => 'Error crítico: El usuario esta inactivo.'
            ], 404);
        }

        // cifra la contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // al cambiar la contraseña con exito se elimina el token de la base de datos
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json([
            'message' => 'Tu contraseña ha sido actualizada con éxito.'
        ], 200);
    }
}
