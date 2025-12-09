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

class LoginGoogleController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'nombre' => 'required|string',
                'apellido' => 'required|string'
            ]);

            return DB::transaction(function () use ($request) {
                $usuario = Usuario::where('email', $request->email)->where('activo', 1)->first();

                if ($usuario) {
                    $token = $usuario->createToken('api_token')->plainTextToken;

                    return response()->json([
                        'success' => true,
                        'message' => 'Inicio de sesión exitoso',
                        'data' => $usuario,
                        'token' => $token
                    ], 200);
                }

                $usuario = Usuario::create([
                    'nombre' => $request->nombre,
                    'apellido' => $request->apellido,
                    'email' => $request->email,
                    'tipo' => 'ALUMNO',
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
                'message' => 'Error al iniciar sesión'
            ], 500);
        }
    }
}
