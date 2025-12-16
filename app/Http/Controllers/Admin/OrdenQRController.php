<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrdenQRController extends Controller
{
    public function show(Request $request)
    {
        try {
            $request->validate([
                'qr' => 'required|string'
            ]);

            $orden = Orden::with('usuario', 'productos')->where('codigo_qr', $request->qr)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Orden encontrada',
                'entregada' => $orden->estado === 'ENTREGADO' ? true : false,
                'pagado' => $orden->pagado === 1 ? true : false,
                'usuario' => $orden->usuario->nombre,
                'productos' => $orden->productos,
                'total' => $orden->total
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al buscar la orden'
            ]);
        }
    }

    public function pagar(Request $request)
    {
        try {
            $request->validate([
                'qr' => 'required|string'
            ]);

            $orden = Orden::where('codigo_qr', $request->qr)->firstOrFail();

            return DB::transaction(function () use ($orden) {
                $orden->update([
                    'pagado' => 1
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Orden pagada correctamente',
                    'pagado' => $orden->pagado === 1 ? true : false
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al buscar la orden'
            ]);
        }
    }

    public function entregar(Request $request)
    {
        try {
            $request->validate([
                'qr' => 'required|string'
            ]);

            $orden = Orden::where('codigo_qr', $request->qr)->firstOrFail();

            return DB::transaction(function () use ($orden) {
                $orden->update([
                    'estado' => 'ENTREGADO'
                ]);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Orden entregada correctamente',
                    'entregada' => $orden->estado === 'ENTREGADO' ? true : false
                ]);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ocurrio un error al buscar la orden'
            ]);
        }
    }
}
