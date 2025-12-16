<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use App\Models\Orden;
use App\Models\Producto;
use App\Models\Ticket;
use App\Models\Usuario;
use FFI\Exception;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        try {
            $ordenes = Orden::get();
            $tickets = Ticket::get();
            $usuarios = Usuario::get();
            $productos = Producto::get();
            $categorias = Categoria::get();

            return response()->json([
                'success' => true,
                'message' => 'Datos obtenidos correctamente',
                'ordenes' => $ordenes,
                'tickets' => $tickets,
                'usuarios' => $usuarios,
                'productos' => $productos,
                'categorias' => $categorias
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos'
            ]);
        }
    }
}
