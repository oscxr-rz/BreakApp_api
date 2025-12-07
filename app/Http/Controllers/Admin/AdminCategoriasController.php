<?php

namespace App\Http\Controllers\Admin;

use App\Events\Admin\ActualizarCategoria;
use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminCategoriasController extends Controller
{
    public function index()
    {
        try {
            $categorias = Cache::remember('categorias_agrupadas', 1800, function () {
                return Categoria::orderBy('nombre')->get();
            });

            return response()->json([
                'success' => true,
                'message' => 'Categorías obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar categorías'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|unique:categoria,nombre',
                'descripcion' => 'required|string',
                'activo' => 'nullable|integer|min:0|max:1'
            ]);

            return DB::transaction(function () use ($request) {
                $categoria = Categoria::create([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'activo' => $request->activo ?? 1,
                    'fecha_creacion' => now(),
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => $request->activo == 0 ? now() : null
                ]);

                Cache::forget('categorias_agrupadas');
                Cache::forget('productos_agrupados');
                Cache::forget('menus_agrupados');

                broadcast(new ActualizarCategoria($categoria->id_categoria));

                return response()->json([
                    'success' => true,
                    'message' => 'Categoría creada correctamente',
                    'data' => $categoria
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría'
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'nombre' => [
                    'required',
                    'string',
                    Rule::unique('categoria', 'nombre')->ignore($id, 'id_categoria')
                ],
                'descripcion' => 'required|string'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $categoria = Categoria::findOrFail($id);

                $categoria->update([
                    'nombre' => $request->nombre,
                    'descripcion' => $request->descripcion,
                    'ultima_actualizacion' => now()
                ]);

                Cache::forget('categorias_agrupadas');
                Cache::forget('productos_agrupados');
                Cache::forget('menus_agrupados');

                broadcast(new ActualizarCategoria($categoria->id_categoria));

                return response()->json([
                    'success' => true,
                    'message' => 'Categoría actualizada correctamente',
                    'data' => $categoria
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría'
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
                $categoria = Categoria::findOrFail($id);

                $categoria->update([
                    'activo' => $request->activo,
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => $request->activo == 0 ? now() : null
                ]);

                Cache::forget('categorias_agrupadas');
                Cache::forget('productos_agrupados');
                Cache::forget('menus_agrupados');

                broadcast(new ActualizarCategoria($categoria->id_categoria));

                return response()->json([
                    'success' => true,
                    'message' => 'Cambio de estado de categoría realizado correctamente',
                    'data' => $categoria
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la categoría'
            ], 500);
        }
    }
}
