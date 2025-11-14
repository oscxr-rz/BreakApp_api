<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Exception;
use Illuminate\Http\Request;

class AdminProductosController extends Controller
{
    public function index() {
        try {
            $productos = Producto::with('Categoria')->orderBy('id_categoria')->orderBy('nombre')->get();

            return response()->json([
                'success' => true,
                'message' => 'Productos mostrados correctamente',
                'data' => $productos
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'seccess' => false,
                'message' => 'Error al mostrar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request) {
        try {
            $request->validate([
                'id_categoria' => 'required|integer|exists:categoria,id_categoria',
                'nombre' => 'required|string|unique:producto,nombre',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric',
                'tiempo_preparacion' => 'date_format:H,i',
                'imagen_url' => 'required|url',
                'activo' => 'integer|min:0|max:1'
            ]);

            $producto = Producto::create([
                'id_categoria' => $request->id_categoria,
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'tiempo_preparacion' => $request->tiempo_preparacion ?? null,
                'imagen_url' => $request->imagen_url,
                'activo' => $request->activo ?? 1,
                'fecha_creacion' => now(),
                'ultima_actualizacion' => now()
            ]);

            return response()->json([
               'success' => true,
               'message' => 'Producto creado correctamente',
               'data' => $producto 
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ]);
        }
    }
}
