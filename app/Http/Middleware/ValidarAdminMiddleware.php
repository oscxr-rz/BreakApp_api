<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidarAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = $request->user('sanctum');

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado'
            ], 401);
        }

        if ($usuario->tipo !== 'ADMINISTRADOR') {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado'
            ], 403);
        }
        return $next($request);
    }
}
