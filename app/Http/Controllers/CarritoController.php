<?php

namespace App\Http\Controllers;

use App\Events\ActualizarCarrito;
use App\Models\Carrito;
use App\Models\CarritoProducto;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\isEmpty;

class CarritoController extends Controller
{
    public function show(int $id)
    {
        try {
            $carritoUsuario = Carrito::with('productos', 'productos.categoria', 'productos.menus')
                ->where('id_usuario', $id)
                ->get()
                ->map(function ($carrito) {
                    return [
                        'id_carrito' => $carrito->id_carrito,
                        'id_usuario' => $carrito->id_usuario,
                        'productos' => $carrito->productos->sortBy('nombre')->groupBy('categoria.nombre')->sortKeys()->map(function ($productos) {
                            return $productos->map(function ($producto) use ($productos) {

                                $menuHoy = $producto->menus->first(function ($menu) {
                                    return Carbon::parse($menu->fecha)->isToday();
                                });

                                $activoAhora = $menuHoy ? $menuHoy->activo : 0;

                                $disponible = false;
                                $cantidadDisponible = null;

                                if ($menuHoy) {
                                    $productoEnMenu = $menuHoy->productos->firstWhere('id_producto', $producto->id_producto);
                                    $disponible = $productoEnMenu
                                        ? $productoEnMenu->pivot->cantidad_disponible >= $producto->pivot->cantidad
                                        : false;

                                    $cantidadDisponible = $productoEnMenu ? $productoEnMenu->pivot->cantidad_disponible : null;
                                }

                                return [
                                    'id_producto' => $producto->id_producto,
                                    'nombre' => $producto->nombre,
                                    'descripcion' => $producto->descripcion,
                                    'precio_unitario' => $producto->precio,
                                    'tiempo_preparacion' => $producto->tiempo_preparacion,
                                    'imagen_url' => $producto->imagen_url,
                                    'cantidad' => $producto->pivot->cantidad,
                                    'categoria' => $producto->categoria->nombre,
                                    'activoAhora' => $activoAhora,
                                    'id_menu' => $menuHoy->id_menu ?? null,
                                    'disponible' => $disponible,
                                    'cantidad_disponible' => $cantidadDisponible
                                ];
                            });
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Carrito mostrado correctamente',
                'data' => $carritoUsuario
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar el carrito'
            ], 500);
        }
    }

    public function addCarrito(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_producto' => [
                    'required',
                    'integer',
                    'distinct',
                    Rule::exists('producto', 'id_producto')->where(function ($query) {
                        $query->where('activo', 1);
                    })
                ],
                'cantidad' => 'required|integer|min:1'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $carrito = Carrito::where('id_usuario', $id)->firstOrFail();

                $carrito->update([
                    'ultima_actualizacion' => now()
                ]);

                $carritoProducto = $this->addCarritoProducto($carrito->id_carrito, $request->id_producto, $request->cantidad);

                $carrito->refresh();

                broadcast(new ActualizarCarrito($id, $carrito->id_carrito));

                return response()->json([
                    'success' => true,
                    'message' => 'Producto agregado correctamente',
                    'data' => $carrito->load('productos')
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al agregar al carrito'
            ], 500);
        }
    }

    private function addCarritoProducto(int $idCarrito, int $idProducto, int $cantidad)
    {
        $existe = CarritoProducto::where('id_carrito', $idCarrito)
            ->where('id_producto', $idProducto)
            ->exists();

        if ($existe) {
            CarritoProducto::where('id_carrito', $idCarrito)
                ->where('id_producto', $idProducto)
                ->increment('cantidad', $cantidad);

            $carritoProducto = CarritoProducto::where('id_carrito', $idCarrito)
                ->where('id_producto', $idProducto)
                ->first();
        } else {
            $carritoProducto = CarritoProducto::create([
                'id_carrito' => $idCarrito,
                'id_producto' => $idProducto,
                'cantidad' => $cantidad
            ]);
        }

        return $carritoProducto;
    }

    public function removeCarrito(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_producto' => 'required|integer|exists:producto,id_producto',
                'cantidad' => 'required|integer|min:1'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $carrito = Carrito::where('id_usuario', $id)->firstOrFail();

                $carrito->update([
                    'ultima_actualizacion' => now()
                ]);

                $carritoProducto = $this->removeCarritoProducto($carrito->id_carrito, $request->id_producto, $request->cantidad);

                $carrito->refresh();

                broadcast(new ActualizarCarrito($id, $carrito->id_carrito));

                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado correctamente',
                    'data' => $carrito->load('productos')
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar del carrito'
            ], 500);
        }
    }

    private function removeCarritoProducto(int $idCarrito, int $idProducto, int $cantidad)
    {
        $carritoProducto = CarritoProducto::where('id_carrito', $idCarrito)
            ->where('id_producto', $idProducto)
            ->firstOrFail();

        if ($carritoProducto->cantidad <= $cantidad) {
            CarritoProducto::where('id_carrito', $idCarrito)
                ->where('id_producto', $idProducto)
                ->delete();
        }


        CarritoProducto::where('id_carrito', $idCarrito)
            ->where('id_producto', $idProducto)
            ->decrement('cantidad', $cantidad);

        $carritoProducto = CarritoProducto::where('id_carrito', $idCarrito)
            ->where('id_producto', $idProducto)
            ->first();

        return $carritoProducto;
    }

    public function eliminarCarrito(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_producto' => 'required|integer|exists:producto,id_producto'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $carrito = Carrito::where('id_usuario', $id)->firstOrFail();

                $carrito->update([
                    'ultima_actualizacion' => now()
                ]);

                $carritoProducto = $this->eliminarCarritoProducto($carrito->id_carrito, $request->id_producto);

                $carrito->refresh();

                broadcast(new ActualizarCarrito($id, $carrito->id_carrito));

                return response()->json([
                    'success' => true,
                    'message' => 'Producto eliminado correctamente',
                    'data' => $carrito->load('productos')
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar del carrito'
            ], 500);
        }
    }

    private function eliminarCarritoProducto(int $idCarrito, int $idProducto)
    {
        $carritoProducto = CarritoProducto::where('id_carrito', $idCarrito)
            ->where('id_producto', $idProducto)
            ->delete();


        return $carritoProducto;
    }
}
