<?php

namespace App\Http\Controllers;

use App\Models\TarjetaLocal;
use Exception;
use Illuminate\Http\Request;

class TarjetaLocalController extends Controller
{
    public function show(int $id) {
        try {
            $tarjetaLocal = TarjetaLocal::where('id_usuario', $id)->firstOrFail();

            return response()->json([
                'success' => true,
                'message' => 'Datos de tarjeta local mostrados correctamente',
                'data' => $tarjetaLocal
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar datos de tarjeta local'
            ], 500);
        }
    }
}
