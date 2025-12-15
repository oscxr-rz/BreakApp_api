<?php

namespace App\Http\Controllers;

use App\Events\ActualizarCarrito;
use App\Events\ActualizarMenu;
use App\Events\ActualizarOrden;
use App\Events\Admin\ActualizarOrdenes;
use App\Http\Controllers\Actions\TicketsController;
use App\Models\Carrito;
use App\Models\CarritoProducto;
use App\Models\Menu;
use App\Models\MenuProducto;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\TarjetaLocal;
use App\Models\Ticket;
use App\Models\Transaccion;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrdenController extends TicketsController
{
    public function index(int $id)
    {
        try {
            $ordenes = Orden::where('oculto', 0)->with('productos')->where('id_usuario', $id)->orderByDesc('id_orden')->get()
                ->map(function ($orden) {
                    return [
                        'id_orden' => $orden->id_orden,
                        'estado' => $orden->estado,
                        'total' => $orden->total,
                        'metodo_pago' => $orden->metodo_pago,
                        'pagado' => $orden->pagado,
                        'imagen_url' => $orden->imagen_url,
                        'hora_recogida' => $orden->hora_recogida,
                        'productos' => $orden->productos->map(function ($producto) {
                            return [
                                'id_producto' => $producto->id_producto,
                                'id_categoria' => $producto->id_categoria,
                                'nombre' => $producto->nombre,
                                'descripcion' => $producto->descripcion,
                                'precio' => $producto->precio,
                                'imagen_url' => $producto->imagen_url,
                                'cantidad' => $producto->pivot->cantidad,
                                'notas' => $producto->pivot->notas
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Ordenes mostradas correctamente',
                'data' => $ordenes
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al mostrar ordenes'
            ], 500);
        }
    }

    public function store(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_menu' => [
                    'required',
                    'integer',
                    Rule::exists('menu', 'id_menu')->where(function ($query) {
                        $query->where('activo', 1);
                    })
                ],
                'metodo_pago' => 'required|string|in:SALDO,EFECTIVO,TARJETA',
                'hora_recogida' => 'required|date_format:H:i',
                'productos' => 'required|array|min:1',
                'productos.*.id_producto' => [
                    'required',
                    'integer',
                    Rule::exists('producto', 'id_producto')->where(function ($query) {
                        $query->where('activo', 1);
                    })
                ],
                'productos.*.cantidad' => 'required|integer|min:1',
                'productos.*.precio_unitario' => 'required|numeric|min:0',
                'productos.*.notas' => 'nullable|string'
            ]);

            return DB::transaction(function () use ($request, $id) {
                $total = $this->sumarTotal($request->productos);

                $tarjetaLocal = null;
                if ($request->metodo_pago === 'SALDO') {
                    $tarjetaLocal = $this->verificarSaldoTarjeta($id, $total);
                }

                $orden = $this->crearOrden($id, $request, $total);
                $qr = $this->crearQR($id, $orden->id_orden);
                $this->crearDetalleOrden($orden, $request->productos, $request->id_menu);
                $this->actualizarcarrito($id, $request->productos);

                if ($request->metodo_pago === 'SALDO') {
                    $this->procesarPago($tarjetaLocal, $total);
                }

                $this->crearImgQr($id, $qr, $orden->id_orden);
                $this->registrarTransaccion($id, $orden->id_orden, $total);

                $orden->refresh();

                $this->crearTicket($orden->id_orden);

                if ($request->metodo_pago === 'SALDO') {
                    $this->generarTicketPdf($orden->id_orden);
                }

                broadcast(new ActualizarMenu($request->id_menu));
                broadcast(new ActualizarOrdenes($orden->id_orden));

                return response()->json([
                    'success' => true,
                    'message' => 'Orden creada correctamente',
                    'data' => $orden->load('productos')
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden'
            ], 500);
        }
    }

    private function sumarTotal(array $productos): float
    {
        $total = 0.0;
        foreach ($productos as $producto) {
            $total += $producto['precio_unitario'] * $producto['cantidad'];
        }
        return $total;
    }

    private function verificarSaldoTarjeta(int $id, float $total): TarjetaLocal
    {
        $tarjeta = TarjetaLocal::where('id_usuario', $id)->firstOrFail();

        if ($tarjeta->saldo < $total) {
            throw new Exception('Saldo insuficiente en tarjeta local');
        }

        return $tarjeta;
    }

    private function crearOrden(int $id, Request $data, float $total): Orden
    {
        return Orden::create([
            'id_usuario' => $id,
            'estado' => 'PENDIENTE',
            'total' => $total,
            'metodo_pago' => $data['metodo_pago'],
            'pagado' => $data['metodo_pago'] === 'EFECTIVO' ? 0 : 1,
            'oculto' => 0,
            'hora_recogida' => $data['hora_recogida'],
            'fecha_creacion' => now(),
            'ultima_actualizacion' => now()
        ]);
    }

    private function crearQR(int $id, int $idOrden): string
    {
        $token = Str::random(60);
        return $id . '_' . $idOrden . '_' . $token;
    }

    private function crearDetalleOrden(Orden $orden, array $productos, int $idMenu): array
    {
        $detalleOrden = [];

        foreach ($productos as $producto) {
            $menuProducto = MenuProducto::where('id_menu', $idMenu)
                ->where('id_producto', $producto['id_producto'])
                ->firstOrFail();

            if ($menuProducto->cantidad_disponible < $producto['cantidad']) {
                throw new Exception('Cantidad no disponible para el producto ID: ' . $producto['id_producto']);
            }

            MenuProducto::where('id_menu', $idMenu)
                ->where('id_producto', $producto['id_producto'])
                ->decrement('cantidad_disponible', $producto['cantidad']);

            $detalle = OrdenDetalle::create([
                'id_orden' => $orden->id_orden,
                'id_producto' => $producto['id_producto'],
                'cantidad' => $producto['cantidad'],
                'precio_unitario' => $producto['precio_unitario'],
                'notas' => $producto['notas'] ?? null,
            ]);

            $detalleOrden[] = $detalle;
        }

        return $detalleOrden;
    }

    private function actualizarcarrito(int $idUsuario, array $productos)
    {
        $carritoProductos = [];
        $carrito = Carrito::where('id_usuario', $idUsuario)->firstOrFail();
        $carrito->update([
            'ultima_actualizacion' => now()
        ]);

        foreach ($productos as $producto) {
            $carritoProducto = CarritoProducto::where('id_carrito', $carrito->id_carrito)
                ->where('id_producto', $producto['id_producto'])
                ->delete();
            $carritoProductos[] = $carritoProducto;
        }

        return $carritoProductos;
    }

    private function procesarPago(TarjetaLocal $tarjeta, float $total): void
    {
        TarjetaLocal::where('id_tarjeta_local', $tarjeta->id_tarjeta_local)
            ->decrement('saldo', $total);
    }

    private function crearImgQr(int $id, string $qr, int $idOrden): void
    {
        $qrCodeSvg = QrCode::size(300)
            ->format('svg')
            ->generate($qr);

        $this->guardarEnStorage($id, $idOrden, $qrCodeSvg, $qr);
    }

    private function guardarEnStorage(int $id, int $idOrden, string $qrCode, string $qr): void
    {
        $nombre = $id . '_' . $idOrden . '_' . time() . '.svg';
        $path = 'ordenes/' . $id . '/' . $nombre;

        Storage::disk('public')->put($path, $qrCode);

        $imagenUrl = asset('storage/' . $path);

        $this->actualizarOrden($idOrden, $qr, $imagenUrl);
    }

    private function actualizarOrden(int $idOrden, string $qr, string $imagenUrl): void
    {
        Orden::where('id_orden', $idOrden)
            ->update([
                'codigo_qr' => Hash::make($qr),
                'imagen_url' => $imagenUrl
            ]);
    }

    private function registrarTransaccion($id, $idOrden, $monto)
    {
        Transaccion::create([
            'id_usuario' => $id,
            'id_orden' => $idOrden,
            'monto' => $monto,
            'tipo' => 'COMPRA',
            'fecha_creacion' => now()
        ]);
    }

    private function crearTicket($idOrden)
    {
        $ticket = Ticket::create([
            'id_orden' => $idOrden,
            'numero_ticket' => 'TCK',
            'fecha_creacion' => now(),
            'ultima_actualizacion' => now()
        ]);

        $ticket->update([
            'numero_ticket' => 'TCK-' . now()->format('Y') . '-' . $ticket->id_ticket
        ]);

        return $ticket;
    }

    public function ocultar(Request $request, int $id)
    {
        try {
            $request->validate([
                'id_orden' => 'required|integer|exists:orden,id_orden'
            ]);

            $orden = Orden::where('id_usuario', $id)->findOrFail($request->id_orden);

            if ($orden->estado !== 'ENTREGADO') {
                return response()->json([
                    'success' => false,
                    'message' => 'La orden no se puede ocultar si no ha sido entregada'
                ], 422);
            }

            return DB::transaction(function () use ($id, $orden) {

                $orden->update([
                    'oculto' => 1,
                    'ultima_actualizacion' => now()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Orden oculta correctamente',
                    'data' => $orden
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al ocultar orden'
            ], 500);
        }
    }
}
