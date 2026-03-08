<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Imagen;

class UserController extends Controller
{
    /**
     * Metodo para que el admin pueda ver los usuarios
     */
    public function index()
    {
        try {
            $users = User::with(['roles:id,name', 'imagenes'])
            ->orderBy('id', 'desc')
            ->get();

            return response()->json($users, 200);

        } catch (\Exception $e) {
    return response()->json([
        'message' => 'Error al obtener la lista de los usuarios.',
        'error' => $e->getMessage(),
        'linea' => $e->getLine()
    ], 500);
}
    }

    /**
     * Este Metodo para que el usuario pueda crear los usuarios
     */
    public function store(Request $request)
    {
    try {
        if (!$request->has('user')) {
            return response()->json(['message' => 'El objeto user es requerido'], 422);
        }

        $userData = json_decode($request->user, true);

        if (!$userData) {
            return response()->json(['message' => 'El formato del JSON es inválido'], 422);
        }

        $data = [
            'nombre'   => $userData['nombre'] ?? null,
            'apellido' => $userData['apellido'] ?? null,
            'email'    => $userData['email'] ?? null,
            'telefono' => $userData['telefono'] ?? null,
            'dui'      => $userData['dui'] ?? null,
            'password' => $userData['password'] ?? null,
        ];

        // Validacion de user
        $validator = Validator::make($data, [
            'nombre'   => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'telefono' => 'required|string|max:20|unique:users,telefono',
            'dui'      => 'required|string|max:20|unique:users,dui',
            'password' => 'required|string|min:8|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        // Asignarle el rol al usuario creado
        $user = User::create($data);


        if (isset($userData['rol'])) {
            $user->assignRole($userData['rol']);
        } else {
            $user->assignRole('cliente');
        }

        // Imagen para el usuario
        if ($request->hasFile('imagenes')) {
            foreach ($request->file('imagenes') as $file) {
                $nombreImagen = time() . '_' . $file->getClientOriginalName();
                $rutaDestino = public_path('images/users');

                if (!file_exists($rutaDestino)) {
                    mkdir($rutaDestino, 0755, true);
                }

                $file->move($rutaDestino, $nombreImagen);

                Imagen::create([
                    'nombre' => $nombreImagen,
                    'usuario_id' => $user->id
                ]);
            }
        }

        DB::commit();

        $user->load(['roles:id,name', 'imagenes']);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user' => $user
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'message' => 'Error al procesar la petición',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     *Metodo show para el admin y usario cliente
     */
    public function show(string $id){
    try {

        $user = User::with(['roles:id,name', 'imagenes'])->findOrFail($id);
        $verUser = auth()->user();

        // Si el token no existe o es inválido
        if (!$verUser) {
            return response()->json(['message' => 'No autenticado'], 401);
        }
        
        if ($verUser->hasRole('ADMIN')) {
            return response()->json($user, 200);
        }

        if ($verUser->id !== $user->id) {
            return response()->json([
                'message' => 'No tienes permiso para ver este perfil.'
            ], 403);
        }

        // Lo que vera si es usuario
        return response()->json([
            'id'       => $user->id,
            'nombre'   => $user->nombre,
            'apellido' => $user->apellido,
            'email'    => $user->email,
            'telefono' => $user->telefono,
            'imagenes' => $user->imagenes,
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json([
            'message' => 'No se ha encontrado el usuario con ID=' . $id
        ], 404);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error inesperado',
            'error'   => $e->getMessage()
        ], 500);
    }
}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
