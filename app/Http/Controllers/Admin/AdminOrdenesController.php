<?php

namespace App\Http\Controllers\Admin;

use App\Events\ActualizarEstadoOrden;
use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Orden;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrdenesController extends Controller
{
    public function index()
    {
        try {
            $ordenes = Orden::where('pagado', 1)
                ->where(function ($query) {
                    $query->where('estado', 'PENDIENTE')
                        ->orWhere('estado', 'PREPARANDO');
                })
                ->with('productos', 'usuario')
                ->orderBy('hora_recogida', 'asc')
                ->orderBy('id_orden', 'asc')
                ->get()
                ->map(function ($orden) {
                    return [
                        'id_orden' => $orden->id_orden,
                        'usuario' => $orden->usuario->nombre,
                        'estado' => $orden->estado,
                        'total' => $orden->total,
                        'metodo_pago' => $orden->metodo_pago,
                        'pagado' => $orden->pagado,
                        'hora_recogida' => $orden->hora_recogida,
                        'productos' => $orden->productos->map(function ($producto) {
                            return [
                                'id_producto' => $producto->id_producto,
                                'nombre' => $producto->nombre,
                                'precio' => $producto->precio,
                                'imagen_url' => $producto->imagen_url,
                                'cantidad' => $producto->pivot->cantidad,
                                'notas' => $producto->pivot->notas
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Ordenes mostradas correctamente',
                'data' => $ordenes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar ordenes'
            ], 500);
        }
    }

    public function cambiarEstado(int $id, Request $request)
    {
        try {
            $request->validate([
                'estado' => 'required|string|in:PREPARANDO,LISTO'
            ]);

            return DB::transaction(function () use ($id, $request) {

                $orden = Orden::findOrFail($id);
                $orden->update([
                    'estado' => $request->estado
                ]);

                $this->enviarNotificacion($orden->id_usuario, $orden->id_orden, $orden->estado);

                return response()->json([
                    'success' => true,
                    'message' => 'Orden actualizada correctamente',
                    'data' => $orden
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden'
            ], 500);
        }
    }

    private function enviarNotificacion($idUsuario, $idOrden, $estado)
    {
        $notificacion = Notificacion::create([
            'id_usuario' => $idUsuario,
            'id_orden' => $idOrden,
            'tipo' => 'ORDEN',
            'titulo' => 'Se ha actualizado tu orden!',
            'mensaje' => "Tu orden con ID:{$idOrden} se ha actualizado a {$estado}",
            'canal' => 'PUSH',
            'leido' => 0,
            'oculto' => 0,
            'fecha_creacion' => now()
        ]);
        broadcast(new ActualizarEstadoOrden($idUsuario, $idOrden, $estado, $notificacion->titulo));
        return $notificacion;
    }
}
