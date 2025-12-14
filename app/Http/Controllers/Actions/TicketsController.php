<?php

namespace App\Http\Controllers\Actions;

use App\Http\Controllers\Controller;
use App\Models\Orden;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TicketsController extends Controller
{
    public function generarTicketPdf($idOrden)
    {
        $orden = Orden::with(['usuario', 'ticket', 'productos'])->findOrFail($idOrden);

        $qrUrl = $orden->imagen_url;
        $qr = str_replace(asset('storage/'), '', $qrUrl);

        $datosApp = [
            'nombre' => config('app.name', 'Mi Aplicación'),
            'correo' => config('app.email', 'contacto@miapp.com'),
            'sitio_web' => config('app.url', 'www.miapp.com')
        ];

        $pdf = Pdf::loadView('tickets.orden-pdf', [
            'orden' => $orden,
            'usuario' => $orden->usuario,
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
}
