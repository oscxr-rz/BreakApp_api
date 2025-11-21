<?php

namespace App\Http\Controllers;

use App\Events\ActualizarMenu;
use App\Models\Menu;
use App\Models\MenuProducto;
use App\Models\Orden;
use App\Models\OrdenDetalle;
use App\Models\TarjetaLocal;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class OrdenController extends Controller
{
    public function index(int $id)
    {
        try {
            $ordenes = Orden::with('productos')->where('id_usuario', $id)->get();

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
                'id_menu' => 'required|integer|exists:menu,id_menu',
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

                if ($request->metodo_pago === 'SALDO') {
                    $this->procesarPago($tarjetaLocal, $total);
                }

                $this->crearImgQr($id, $qr, $orden->id_orden);

                $orden->refresh();

                $menuActualizado = Menu::with('productos')
                    ->find($request->id_menu);

                broadcast(new ActualizarMenu($menuActualizado->toArray));

                return response()->json([
                    'success' => true,
                    'message' => 'Orden creada correctamente',
                    'data' => $orden->load('productos')
                ], 201);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
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
                'codigo_qr' => $qr,
                'imagen_url' => $imagenUrl
            ]);
    }
}
