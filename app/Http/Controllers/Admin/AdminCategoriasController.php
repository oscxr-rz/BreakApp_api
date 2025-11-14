<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCategoriasController extends Controller
{
    public function index()
    {
        try {
            $categorias = Categoria::orderBy('nombre')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categorías obtenidas correctamente',
                'data' => $categorias
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nombre' => 'required|string|unique:categoria,nombre',
                'descripcion' => 'required|string',
                'activo' => 'integer|min:0|max:1'
            ]);

            $categoria = Categoria::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'activo' => $request->activo ?? 1,
                'fecha_creacion' => now(),
                'ultima_actualizacion' => now(),
                'fecha_eliminacion' => $request->activo == 0 ? now() : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría creada correctamente',
                'data' => $categoria
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
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

            $categoria = Categoria::findOrFail($id);

            $categoria->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'ultima_actualizacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Categoría actualizada correctamente',
                'data' => $categoria
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        try {
            $request->validate([
                'activo' => 'required|integer|min:0|max:1'
            ]);

            $categoria = Categoria::findOrFail($id);

            $categoria->update([
                'activo' => $request->activo,
                'ultima_actualizacion' => now(),
                'fecha_eliminacion' => $request->activo == 0 ? now() : null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Cambio de estado de categoría realizado correctamente',
                'data' => $categoria
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de la categoría',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
