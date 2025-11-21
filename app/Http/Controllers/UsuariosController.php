<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuariosController extends Controller
{
    public function show(int $id)
    {
        try {
            $usuario = Usuario::select('id_usuario','nombre','apellido','email','telefono','tipo','grupo','imagen_url')->with('tarjetaLocal:id_tarjeta_local,id_usuario,saldo')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Datos de usuario mostrados correctamente',
                'data' => $usuario
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar datos de usuario'
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'nombre' => 'required|string',
                'apellido' => 'required|string',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('usuario', 'email')->ignore($id, 'id_usuario')
                ],
                'telefono' => [
                    'required',
                    'numeric',
                    'min_digits:10',
                    'max_digits:10',
                    Rule::unique('usuario', 'telefono')->ignore($id, 'id_usuario')
                ],
                'grupo' => 'nullable|string',
            ]);

            return DB::transaction(function () use ($request, $id) {
                $usuario = Usuario::where('activo', 1)->findOrFail($id);

                $usuario->update([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'grupo' => $request->grupo ?? $usuario->grupo,
                    'ultima_actualizacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Datos de usuario actualizados correctamente',
                    'data' => $usuario
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los datos de usuario'
            ], 500);
        }
    }

    public function updateImagen(Request $request, int $id)
    {
        try {
            $request->validate([
                'imagen_url' => 'required|url'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $usuario = Usuario::where('activo', 1)->findOrFail($id);

                $usuario->update([
                    'imagen_url' => $request->imagen_url,
                    'ultima_actualizacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Imagen actualizada correctamente',
                    'data' => $usuario
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actulizar imagen'
            ], 500);
        }
    }

    public function updatePassword(Request $request, int $id)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:6',
                'passwordNueva' => 'required|string|min:6'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $usuario = Usuario::where('activo', 1)->findOrFail($id);

                if (!Hash::check($request->password, $usuario->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Credenciales inválidas',
                    ], 401);
                }

                $usuario->update([
                    'password' => Hash::make($request->passwordNueva),
                    'ultima_actualizacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Contraseña actualizada correctamente'
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar contraseña'
            ], 500);
        }
    }

    public function desactivar(Request $request, int $id)
    {
        try {
            $request->validate([
                'password' => 'required|string|min:6'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $usuario = Usuario::where('activo', 1)->findOrFail($id);

                if (!Hash::check($request->password, $usuario->password)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Credenciales inválidas',
                    ], 401);
                }

                $request->user()->currentAccessToken()->delete();

                $usuario->update([
                    'activo' => 0,
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Cuenta desactivada correctamente'
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al desactivar la cuenta'
            ], 500);
        }
    }
}
