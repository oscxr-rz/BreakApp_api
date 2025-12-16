<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TarjetaLocal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTarjetaLocalController extends Controller
{
    public function show(int $id)
    {
        try {
            $tarjetaLocal = TarjetaLocal::with('usuario')->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Tarjeta local encontrada correctamente',
                'data' => $tarjetaLocal
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar tarjeta'
            ], 500);
        }
    }

    public function recargar(int $id, Request $request)
    {
        try {
            $request->validate([
                'monto' => 'required|numeric|min:1'
            ]);
            $tarjetaLocal = TarjetaLocal::findOrFail($id);

            return DB::transaction(function () use ($tarjetaLocal, $request) {
                $tarjetaLocal->increment('saldo', $request->monto);

                return response()->json([
                    'success' => true,
                    'message' => 'Tarjeta local recargada correctamente',
                    'data' => $tarjetaLocal
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recargar la tarjeta'
            ], 500);
        }
    }
}
