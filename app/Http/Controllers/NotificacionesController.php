<?php

namespace App\Http\Controllers;

use App\Events\ActualizarNotificacion;
use App\Models\Notificacion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function ocultar(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_notificacion' => 'required|integer|exists:notificacion,id_notificacion'
            ]);
            
            $notificacion = Notificacion::where('id_usuario', $id)->findOrFail($request->id_notificacion);

            if ($notificacion->leido !== 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'La notificación no se puede ocultar si no ha sido leída'
                ], 422);
            }

            return DB::transaction(function () use ($notificacion) {

                $notificacion->update([
                    'oculto' => 1,
                    'ultima_actualizacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Notificación oculta correctamente',
                    'data' => $notificacion
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ocultar la notificación'
            ], 500);
        }
    }
}
