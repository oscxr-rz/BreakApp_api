<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'nullable|email',
                'telefono' => 'nullable|numeric',
                'password' => 'required|string|min:6'
            ]);

            return DB::transaction(function () use ($request) {
                $usuario = Usuario::where(function ($query) use ($request) {
                    if ($request->filled('email')) {
                        $query->where('email', $request->email);
                    }
                    if ($request->filled('telefono')) {
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
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión'
            ], 500);
        }
    }
}
