<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminProductosController extends Controller
{
    public function index()
    {
        try {
            $productos = Cache::remember('productos_agrupados', 1800, function () {
                return Producto::with(['Categoria:id_categoria,nombre'])
                    ->orderBy('nombre')
                    ->get()
                    ->groupBy('categoria.nombre');
            });

            return response()->json([
                'success' => true,
                'message' => 'Productos mostrados correctamente',
                'data' => $productos
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'id_categoria' => 'required|integer|exists:categoria,id_categoria',
                'nombre' => 'required|string|unique:producto,nombre',
                'descripcion' => 'required|string',
                'precio' => 'required|numeric',
                'tiempo_preparacion' => 'nullable|date_format:H:i',
                'imagen_url' => 'required|url',
                'activo' => 'nullable|integer|min:0|max:1'
            ]);

            return DB::transaction(function () use ($request) {
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

                Cache::forget('productos_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Producto creado correctamente',
                    'data' => $producto
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_categoria' => 'required|integer|exists:categoria,id_categoria',
                'nombre' => [
                    'required',
                    'string',
                    Rule::unique('producto', 'nombre')->ignore($id, 'id_producto')
                ],
                'descripcion' => 'required|string',
                'precio' => 'required|numeric',
                'tiempo_preparacion' => 'nullable|date_format:H:i',
                'imagen_url' => 'required|url'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $producto = Producto::findOrFail($id);

                $producto->update([
                    'id_categoria' => $request->id_categoria,
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'precio' => $request->precio,
                    'tiempo_preparacion' => $request->tiempo_preparacion ?? null,
                    'ultima_actualizacion' => now()
                ]);

                Cache::forget('productos_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Producto actualizado correctamente',
                    'data' => $producto
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, int $id)
    {
        try {
            $request->validate([
                'activo' => 'required|integer|min:0|max:1'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $producto = Producto::findOrFail($id);

                $producto->update([
                    'activo' => $request->activo,
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => $request->activo == 0 ? now() : null
                ]);

                Cache::forget('productos_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Cambio de estado de producto realizado correctamente',
                    'data' => $producto
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de el producto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
