<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Exception;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    public function index($id)
    {
        try {
            $notificaciones = Notificacion::where('oculto', 0)->where('id_usuario', $id)->orderByDesc('id_notificacion')->get();

            return response()->json([
                'success' => true,
                'message' => 'Notificaciones mostradas correctamente',
                'data' => $notificaciones
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar notificaciones'
            ], 500);
        }
    }
}
