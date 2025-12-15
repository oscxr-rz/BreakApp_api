<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Mail\actions\enviarTicket;
use App\Models\Orden;
use App\Models\Ticket;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TicketsController extends Controller
{
    public function generarTicketPdf($idOrden, ?string $email = null, ?string $nombreCliente = null)
    {
        $orden = Orden::with(['usuario', 'ticket', 'productos'])->findOrFail($idOrden);

        $qrUrl = $orden->imagen_url;
        $qr = str_replace(asset('storage/'), '', $qrUrl);

        $datosApp = [
            'nombre' => config('app.name', 'Mi Aplicación'),
            'correo' => env('MAIL_USERNAME'),
            'sitio_web' => config('app.url', 'www.miapp.com')
        ];

        if($nombreCliente && $email){
            $usuarioDatos = [
                'nombre' => $nombreCliente,
                'apellido' => ' ',
                'email' => $email
            ];

            $usuario = (object) $usuarioDatos;
        }

        $pdf = Pdf::loadView('tickets.orden-pdf', [
            'orden' => $orden,
            'usuario' => $usuario ?? $orden->usuario,
            'ticket' => $orden->ticket,
            'qr' => $qr,
            'datosApp' => $datosApp
        ])
            ->setPaper([0, 0, 226.77, 600], 'portrait')
            ->setOption('margin-top', 5)
            ->setOption('margin-right', 5)
            ->setOption('margin-bottom', 5)
            ->setOption('margin-left', 5);

        $directorio = 'tickets/' . $orden->id_usuario;
        Storage::disk('public')->makeDirectory($directorio);

        $nombreArchivo = $orden->ticket->numero_ticket . '.pdf';
        $rutaCompleta = $directorio . '/' . $nombreArchivo;

        Storage::disk('public')->put($rutaCompleta, $pdf->output());

        $pdfUrl = asset('storage/' . $rutaCompleta);

        $orden->ticket->update([
            'pdf_url' => $pdfUrl
        ]);

        return $orden;
    }

    public function enviarTicket(int $id, Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email'
            ]);

            $orden = Orden::findOrFail($id);

            return DB::transaction(function () use ($orden, $request) {
                $ticket = $orden->ticket;
                $pdfUrl = $ticket->pdf_url;
                $pdf = str_replace(asset('storage/'), '', $pdfUrl);

                if (!$ticket) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Ticket no encontrado'
                    ], 404);
                }

                Mail::to($request->email)->send(new enviarTicket($ticket, $pdf));

                return response()->json([
                    'success' => true,
                    'message' => 'Ticket enviado correctamente a ' . $request->email,
                    'data' => $ticket
                ], 200);
            });
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el ticket'
            ], 500);
        }
    }
}
