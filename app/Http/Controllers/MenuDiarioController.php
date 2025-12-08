<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class MenuDiarioController extends Controller
{
    public function menuDiario()
    {
        try {

            $fechaHoy = now()->startOfDay();

            $menuDia = Cache::remember('menu_diario', 1800, function () use ($fechaHoy) {
                return Menu::where('fecha', $fechaHoy)->where('activo', 1)->with(
                    'productos:id_producto,id_categoria,nombre,descripcion,precio,tiempo_preparacion,imagen_url',
                    'productos.categoria:id_categoria,nombre'
                )->get()->map(function ($menu) {
                    return [
                        'id_menu' => $menu->id_menu,
                        'fecha' => $menu->fecha,
                        'activo' => $menu->activo,
                        'productos' => $menu->productos->filter(function ($producto) {
                            return $producto->pivot->cantidad_disponible > 0;
                        })->sortBy('nombre')->groupBy('categoria.nombre')->sortKeys()->map(function ($productos) {
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
                'message' => 'Menú del dia mostrado correctamente',
                'data' => $menuDia
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar el menú del dia'
            ]);
        }
    }
}
