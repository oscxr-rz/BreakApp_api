<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
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
}
