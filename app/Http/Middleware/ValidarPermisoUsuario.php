<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarPermisoUsuario
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = (int) $request->route('id');
        $usuario = $request->user('sanctum');

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        if ((int) $usuario->id_usuario !== $id) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }
        return $next($request);
    }
}
