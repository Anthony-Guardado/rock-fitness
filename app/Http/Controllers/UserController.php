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

     //Metodo para que el admin pueda ver los usuarios
    public function index()
    {
        try {
            $verUser = auth()->user();

        // Validacion para que solo un admin pueda listar los usuarios
        if (!$verUser || !$verUser->hasRole('ADMIN')) {
            return response()->json(['message' => 'No tienes permiso para listar usuarios'], 403);
        }

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

    // Este Metodo para que el usuario pueda crear los usuarios
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

        // Validacion de los atributos
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


        $user = User::create($data);

        // Asignarle el rol al usuario creado
        if (isset($userData['rol'])) {
            $user->assignRole($userData['rol']);
        } else {
            $user->assignRole('CLIENTE');
        }

        // Imagen para el usuario o el admin
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
        //trae en la misma consulta el rol y la imagen
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
        //verifica que este autenticado el usuario o el admin
        $user = User::with(['roles:id,name', 'imagenes'])->findOrFail($id);
        $verUser = auth()->user();

        if (!$verUser) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        //si es un admin le manda todos los datos del usuario mediante su objeto
        if ($verUser->hasRole('ADMIN')) {
            return response()->json($user, 200);
        }

        //valida que si le manda los datos sea el mismo usuario que quiere ver sus datos
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
            'dui'       => $user->dui,
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

    // Update este dependera si es usuario cliente o admin para los campos que modificara
    public function update(Request $request, string $id)
{
    try {
        //Busca los usuarios validados
        $user = User::withTrashed()->findOrFail($id);

        //Valida que los datos de un usuario inactivo nose puedan modificar
        if ($user->trashed()) {
        return response()->json([
        'message' => 'No puedes editar un usuario inactivo. Debes activarlo primero.'
        ], 422);
        }
        $user = User::findOrFail($id);
        $verUser = auth()->user();

        if (!$verUser) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // verifica que sea admin o que sea el mismo perfil de la persona
        if (!$verUser->hasRole('ADMIN') && $verUser->id !== $user->id) {
            return response()->json(['message' => 'No tienes permiso para editar este perfil'], 403);
        }

        if (!$request->has('user')) {
            return response()->json(['message' => 'El objeto user es requerido'], 422);
        }

        $userData = json_decode($request->user, true);

        if (!$userData) {
        return response()->json(['message' => 'El formato del JSON dentro del campo user es inválido'], 422);
}


        // Los atributos que editara el usuario
        $data = [
            'email'    => $userData['email'] ?? $user->email,
            'telefono' => $userData['telefono'] ?? $user->telefono,
        ];

        // pero el admin puede editar los datos de edentidad
        if ($verUser->hasRole('ADMIN')) {
            $data['nombre']   = $userData['nombre'] ?? $user->nombre;
            $data['apellido'] = $userData['apellido'] ?? $user->apellido;
            $data['dui']      = $userData['dui'] ?? $user->dui;
        }

        //Validacion
        $campos = [
            'email'    => 'required|email|unique:users,email,' . $id,
            'telefono' => 'required|string|unique:users,telefono,' . $id,
        ];

        // Si es admin agregamos los campos extra
        if ($verUser->hasRole('ADMIN')) {
            $campos['nombre']   = 'required|string|max:255';
            $campos['nombre']   = 'required|string|max:255';
            $campos['apellido'] = 'required|string|max:255';
            $campos['dui']      = 'required|string|unique:users,dui,' . $id;
        }

        $validator = Validator::make($data, $campos);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();

        $user->update($data);

        // Gestion de imagnes
        if ($request->hasFile('imagenes')) {
            foreach ($user->imagenes as $img) {
                $ruta = public_path('images/users/' . $img->nombre);
                if (file_exists($ruta)) { unlink($ruta); }
                $img->delete();
            }

            foreach ($request->file('imagenes') as $file) {
                $nombreImagen = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('images/users'), $nombreImagen);
                Imagen::create([
                    'nombre' => $nombreImagen,
                    'usuario_id' => $user->id
                ]);
            }
        }

        DB::commit();

        if ($verUser->hasRole('ADMIN')) {
            $mensaje = 'Usuario actualizado con éxito ';
        } else {
            $mensaje = 'Perfil actualizado. Los datos de identidad están bloqueados.';
        }

        return response()->json([
            'message' => $mensaje,
            'user' => $user->load(['roles:id,name', 'imagenes'])
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['message' => 'Error al actualizar', 'error' => $e->getMessage()], 500);
    }
}

    // Metodo para el admin
    public function destroy(string $id)
    {
        try {
        $user = User::findOrFail($id);
        $verUser = auth()->user();

        if (!$verUser || !$verUser->hasRole('ADMIN')) {
            return response()->json(['message' => 'No tienes permisos'], 403);
        }

        if ($verUser->id === $user->id) {
            return response()->json(['message' => 'No puedes desactivarte a ti mismo'], 400);
        }

        // Pone inactivo el usuario
        $user->delete();

        return response()->json([
            'message' => 'Usuario desactivado correctamente. Sus datos aún existen para reportes históricos.'
        ], 200);

    } catch (ModelNotFoundException $e) {
        return response()->json(['message' => 'Usuario no encontrado'], 404);
    }
        }

    // Funcion para reactivar un usuario pero solo el admin tiene el permiso de realizar la funcion
    public function restoreUser(Request $request, $id)
    {
         // Verificamos si el que está logueado el admin
        if (!$request->user()->hasRole('ADMIN')) {
            return response()->json([
                'message' => 'No tienes permisos de administrador.'
            ], 403);
        }

         // Busca todos los usuarios asta los inactivos
        $user = User::withTrashed()->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        // Verificamos si realmente estaba inactivo
        if ($user->trashed()) {
            //regresa el delete a null
            $user->restore();
            return response()->json([
                'message' => "El usuario {$user->nombre} ha sido reactivado con éxito."
            ], 200);
        }

        return response()->json(['message' => 'El usuario ya se encuentra activo.'], 400);
    }
    }

