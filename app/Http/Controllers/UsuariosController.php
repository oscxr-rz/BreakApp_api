<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuariosController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'email',
                'telefono' => 'numeric',
                'password' => 'required|string'
            ]);

            $usuario = Usuario::where(function ($query) use ($request) {
                if ($request->has('email')) {
                    $query->where('email', $request->email);
                }
                if ($request->has('telefono')) {
                    $query->orWhere('telefono', $request->telefono);
                }
            })->where('activo', 1)->first();

            if (!$usuario || !Hash::check($request->password, $usuario->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales inválidas',
                ], 401);
            }

            $token = $usuario->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Inicio de sesión exitoso',
                'data' => $usuario,
                'token' => $token
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string',
                'apellido' => 'required|string',
                'email' => 'required|email',
                'telefono' => 'required|numeric',
                'password' => 'required|string|min:6',
                'tipo' => 'required|string',
                'grupo' => 'string',
                'imagen_url' => 'string'
            ]);

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

            $token = $usuario->createToken('api_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Usuario registrado exitosamente',
                'data' => $usuario,
                'token' => $token
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar el usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cierre de sesión exitoso'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
