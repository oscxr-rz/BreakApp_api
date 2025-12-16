<?php

namespace App\Http\Controllers\Admin;

use App\Events\ActualizarEstadoOrden;
use App\Events\ActualizarMenu;
use App\Events\Admin\ActualizarOrdenes;
use App\Http\Controllers\Actions\TicketsController;
use App\Http\Controllers\Controller;
use App\Models\MenuProducto;
use App\Models\Notificacion;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\Ticket;
use App\Models\Transaccion;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use phpDocumentor\Reflection\DocBlock\Tags\Extends_;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AdminOrdenesController extends TicketsController
{
    public function index()
    {
        try {
            $ordenes = Orden::where('pagado', 1)
                ->where(function ($query) {
                    $query->where('estado', 'PENDIENTE')
                        ->orWhere('estado', 'PREPARANDO');
                })
                ->with('productos', 'usuario')
                ->orderByRaw("CASE WHEN estado = 'PREPARANDO' THEN 0 ELSE 1 END")
                ->orderBy('hora_recogida', 'asc')
                ->orderBy('id_orden', 'asc')
                ->get()
                ->map(function ($orden) {
                    return [
                        'id_orden' => $orden->id_orden,
                        'usuario' => $orden->usuario->nombre,
                        'estado' => $orden->estado,
                        'total' => $orden->total,
                        'metodo_pago' => $orden->metodo_pago,
                        'pagado' => $orden->pagado,
                        'hora_recogida' => $orden->hora_recogida,
                        'productos' => $orden->productos->map(function ($producto) {
                            return [
                                'id_producto' => $producto->id_producto,
                                'nombre' => $producto->nombre,
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

    public function registrarOrden(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'required|in:COMPRA,CAPTURA',
                'id_menu' => 'nullable|integer|exists:menu,id_menu',
                'nombre' => 'nullable|string',
                'email' => 'nullable|email',
                'estado' => 'required|string|in:PENDIENTE,ENTREGADO',
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

            return DB::transaction(function () use ($request) {
                $user = $request->user('sanctum');
                $id = $user->id_usuario;

                $total = $this->sumarTotal($request->productos);

                $orden = $this->crearOrden($id, $request, $total);
                $this->crearDetalleOrden($request->tipo, $orden, $request->productos, $request->id_menu);
                $qr = $this->crearQR($id, $orden->id_orden);

                $this->crearImgQr($qr, $orden->id_orden);
                $this->registrarTransaccion($id, $orden->id_orden, $total);

                $orden->refresh();

                if ($request->tipo === 'COMPRA') {
                    $this->crearTicket($orden->id_orden);
                    $this->generarTicketPdf($orden->id_orden, $request->email, $request->nombre);
                    broadcast(new ActualizarMenu($request->id_menu));
                    broadcast(new ActualizarOrdenes($orden->id_orden));
                }

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

    private function crearOrden(int $id, Request $data, float $total): Orden
    {
        return Orden::create([
            'id_usuario' => $id,
            'nombre' => $data['nombre'],
            'estado' => $data['estado'],
            'total' => $total,
            'metodo_pago' => 'EFECTIVO',
            'pagado' => 1,
            'oculto' => 1,
            'hora_recogida' => now(),
            'fecha_creacion' => now(),
            'ultima_actualizacion' => now()
        ]);
    }

    private function crearQR(int $id, int $idOrden): string
    {
        $token = Str::random(60);
        return $id . '_' . $idOrden . '_' . $token;
    }

    private function crearDetalleOrden(string $tipo, Orden $orden, array $productos, ?int $idMenu): array
    {
        $detalleOrden = [];

        foreach ($productos as $producto) {
            if ($tipo === 'COMPRA') {
                if (!$idMenu) {
                    throw new Exception('id_menu es requerido para tipo COMPRA');
                }

                $menuProducto = MenuProducto::where('id_menu', $idMenu)
                    ->where('id_producto', $producto['id_producto'])
                    ->firstOrFail();

                if ($menuProducto->cantidad_disponible < $producto['cantidad']) {
                    throw new Exception('Cantidad no disponible para el producto ID: ' . $producto['id_producto']);
                }

                MenuProducto::where('id_menu', $idMenu)
                    ->where('id_producto', $producto['id_producto'])
                    ->decrement('cantidad_disponible', $producto['cantidad']);
            }

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

    private function crearImgQr(string $qr, int $idOrden): void
    {
        $qrCodeSvg = QrCode::size(300)
            ->format('svg')
            ->generate($qr);

        $this->guardarEnStorage($idOrden, $qrCodeSvg, $qr);
    }

    private function guardarEnStorage(int $idOrden, string $qrCode, string $qr): void
    {
        $nombre = $idOrden . '_' . time() . '.svg';
        $path = 'ordenes/' . 'admin/' . $nombre;

        Storage::disk('public')->put($path, $qrCode);

        $imagenUrl = asset('storage/' . $path);

        $this->actualizarOrden($idOrden, $qr, $imagenUrl);
    }

    private function actualizarOrden(int $idOrden, string $qr, string $imagenUrl): void
    {
        Orden::where('id_orden', $idOrden)
            ->update([
                'codigo_qr' => $qr,
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

    public function cambiarEstado(int $id, Request $request)
    {
        try {
            $request->validate([
                'estado' => 'required|string|in:PREPARANDO,LISTO'
            ]);

            return DB::transaction(function () use ($id, $request) {

                $orden = Orden::findOrFail($id);
                $orden->update([
                    'estado' => $request->estado
                ]);

                $this->enviarNotificacion($orden->id_usuario, $orden->id_orden, $orden->estado);

                broadcast(new ActualizarOrdenes($orden->id_orden));

                return response()->json([
                    'success' => true,
                    'message' => 'Orden actualizada correctamente',
                    'data' => $orden
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la orden'
            ], 500);
        }
    }

    private function enviarNotificacion($idUsuario, $idOrden, $estado)
    {
        $notificacion = Notificacion::create([
            'id_usuario' => $idUsuario,
            'id_orden' => $idOrden,
            'tipo' => 'ORDEN',
            'titulo' => 'Se ha actualizado tu orden!',
            'mensaje' => "Tu orden con ID:{$idOrden} se ha actualizado a {$estado}",
            'canal' => 'PUSH',
            'leido' => 0,
            'oculto' => 0,
            'fecha_creacion' => now(),
            'ultima_actualizacion' => now()
        ]);
        broadcast(new ActualizarEstadoOrden($idUsuario, $idOrden, $estado, $notificacion->titulo));
        return $notificacion;
    }
}
