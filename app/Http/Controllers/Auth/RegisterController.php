<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Carrito;
use App\Models\TarjetaLocal;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string',
                'apellido' => 'required|string',
                'email' => 'required|email',
                'telefono' => 'required|numeric|min_digits:10,max_digits:10',
                'password' => 'required|string|min:6',
                'tipo' => 'required|string',
                'grupo' => 'nullable|string',
                'imagen_url' => 'nullable|string'
            ]);

            return DB::transaction(function () use ($request) {
                $usuarioExiste = Usuario::where(function ($query) use ($request) {
                    $query->where('email', $request->email)
                        ->orWhere('telefono', $request->telefono);
                })->where('activo', 1)->exists();

                if ($usuarioExiste) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ya existe un usuario con estos datos',
                    ], 409);
                }

                $usuario = Usuario::create([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'email' => $request->email,
                    'telefono' => $request->telefono,
                    'password' => Hash::make($request->password),
                    'tipo' => $request->tipo,
                    'grupo' => $request->grupo ?? null,
                    'imagen_url' => $request->imagen_url ?? null,
                    'activo' => 1,
                    'fecha_registro' => now(),
                    'ultima_actualizacion' => now()
                ]);

                $tarjetaLocal = TarjetaLocal::create([
                    'id_usuario' => $usuario->id_usuario,
                    'saldo' => 0,
                    'fecha_creacion' => now(),
                    'ultima_actualizacion' => now()
                ]);

                $carrito = Carrito::create([
                    'id_usuario' => $usuario->id_usuario,
                    'fecha_creacion' => now(),
                    'ultima_actualizacion' => now()
                ]);

                $token = $usuario->createToken('api_token')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Usuario registrado exitosamente',
                    'data' => $usuario,
                    'tarjeta' => $tarjetaLocal,
                    'token' => $token
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario'
            ], 500);
        }
    }
}
