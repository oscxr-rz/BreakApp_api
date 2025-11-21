<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuProducto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminMenuController extends Controller
{
    public function index()
    {
        try {
            $menus = Cache::remember('menus_agrupados', 1800, function () {
                return Menu::with(
                    'productos:id_producto,id_categoria,nombre,descripcion,precio,tiempo_preparacion,imagen_url',
                    'productos.categoria:id_categoria,nombre'
                )->get()->map(function ($menu) {
                    return [
                        'id_menu' => $menu->id_menu,
                        'fecha' => $menu->fecha,
                        'activo' => $menu->activo,
                        'productos' => $menu->productos->sortBy('nombre')->groupBy('categoria.nombre')->sortKeys()->map(function ($productos) {
                            return $productos->map(function ($producto) {
                                return [
                                    'id_producto' => $producto->id_producto,
                                    'nombre' => $producto->nombre,
                                    'descripcion' => $producto->descripcion,
                                    'precio' => $producto->precio,
                                    'tiempo_preparacion' => $producto->tiempo_preparacion,
                                    'imagen_url' => $producto->imagen_url,
                                    'cantidad_disponible' => $producto->pivot->cantidad_disponible,
                                    'categoria' => $producto->categoria->nombre
                                ];
                            });
                        })
                    ];
                });
            });

            return response()->json([
                'success' => true,
                'message' => 'Menús mostrados correctamente',
                'data' => $menus
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar menús'
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'fecha' => 'required|date|unique:menu,fecha',
                'activo' => 'required|integer|min:0|max:1',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('producto', 'id_producto')->where(function ($query) {
                        $query->where('activo', 1);
                    })
                ],
                'productos.*.cantidad_disponible' => 'required|integer|min:0'
            ]);

            if (Carbon::parse($request->fecha)->lt(now()->startOfDay())) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha no puede ser menor a la fecha actual'
                ], 422);
            }

            return DB::transaction(function () use ($request) {
                $menu = Menu::create([
                    'fecha' => $request->fecha,
                    'activo' => $request->activo,
                    'fecha_creacion' => now(),
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => $request->activo == 0 ? now() : null
                ]);

                $this->addMenuProducto($menu->id_menu, $request->productos);

                Cache::forget('menus_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Menú creado correctamente',
                    'data' => $menu->load('productos')
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear menú'
            ], 500);
        }
    }

    private function addMenuProducto($idMenu, $productos)
    {
        $productosMenu = [];
        foreach ($productos as $producto) {
            $productoMenu = MenuProducto::create([
                'id_menu' => $idMenu,
                'id_producto' => $producto['id_producto'],
                'cantidad_disponible' => $producto['cantidad_disponible']
            ]);

            $productosMenu[] = $productoMenu;
        }

        return $productosMenu;
    }

    public function update(Request $request, int $id)
    {
        try {
            $request->validate([
                'fecha' => [
                    'required',
                    'date',
                    Rule::unique('menu', 'fecha')->ignore($id, 'id_menu')
                ],
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('producto', 'id_producto')->where(function ($query) {
                        $query->where('activo', 1);
                    })
                ],
                'productos.*.cantidad_disponible' => 'required|integer|min:0'
            ]);

            if (Carbon::parse($request->fecha)->lt(now()->startOfDay())) {
                return response()->json([
                    'success' => false,
                    'message' => 'La fecha no puede ser menor a la fecha actual'
                ], 422);
            }

            return DB::transaction(function () use ($request, $id) {
                $menu = Menu::findOrFail($id);

                $menu->update([
                    'fecha' => $request->fecha,
                    'ultima_actualizacion' => now()
                ]);

                $this->updateMenuProducto($menu->id_menu, $request->productos);

                Cache::forget('menus_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Menú actualizado correctamente',
                    'data' => $menu->load('productos')
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar menú'
            ], 500);
        }
    }

    private function updateMenuProducto($idMenu, $productos)
    {
        MenuProducto::where('id_menu', $idMenu)->delete();

        $productosMenu = [];

        foreach ($productos as $producto) {
            $productoMenu = MenuProducto::create([
                'id_menu' => $idMenu,
                'id_producto' => $producto['id_producto'],
                'cantidad_disponible' => $producto['cantidad_disponible']
            ]);

            $productosMenu[] = $productoMenu;
        }

        return $productosMenu;
    }

    public function cambiarEstado(Request $request, int $id)
    {
        try {
            $request->validate([
                'activo' => 'required|integer|min:0|max:1'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $menu = Menu::findOrFail($id);

                $menu->update([
                    'activo' => $request->activo,
                    'ultima_actualizacion' => now(),
                    'fecha_eliminacion' => $request->activo == 0 ? now() : null
                ]);

                Cache::forget('menus_agrupados');

                return response()->json([
                    'success' => true,
                    'message' => 'Cambio de estado de menú realizado correctamente',
                    'data' => $menu
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar el estado de el menú'
            ], 500);
        }
    }
}
