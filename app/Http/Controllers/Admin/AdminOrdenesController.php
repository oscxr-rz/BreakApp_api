<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Exception;
use Illuminate\Http\Request;

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
}
