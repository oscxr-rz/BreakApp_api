<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Compra</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f5f5f5;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f5f5; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.05); max-width: 600px;">

                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 30px; text-align: center; border-bottom: 1px solid #e5e5e5;">
                            <h1 style="margin: 0; font-size: 24px; font-weight: 600; color: #1a1a1a; line-height: 1.3;">
                                Aquí está tu ticket de compra
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="margin: 0 0 24px; font-size: 16px; line-height: 1.6; color: #4a4a4a;">
                                Gracias por tu compra. Encuentra adjunto el PDF con todos los detalles de tu pedido.
                            </p>

                            <!-- Ticket Info Box -->
                            <div style="background-color: #f9f9f9; padding: 24px; border-radius: 6px; margin: 24px 0;">
                                @if (isset($ticket->numero_ticket))
                                    <p style="margin: 0 0 12px; font-size: 14px; color: #666; line-height: 1.5;">
                                        <strong style="color: #1a1a1a;">Número de ticket:</strong>
                                        {{ $ticket->numero_ticket }}
                                    </p>
                                @endif

                                @if (isset($ticket->fecha_creacion))
                                    <p style="margin: 0 0 12px; font-size: 14px; color: #666; line-height: 1.5;">
                                        <strong style="color: #1a1a1a;">Fecha:</strong> {{ $ticket->fecha_creacion }}
                                    </p>
                                @endif
                            </div>

                            <!-- PDF Icon -->
                            <div
                                style="background-color: #f0f7ff; padding: 24px; border-radius: 6px; margin: 24px 0; text-align: center; border: 1px solid #d6e9ff;">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#2563eb"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    style="margin: 0 auto 12px; display: block;">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                <p style="margin: 0; font-size: 15px; color: #2563eb; font-weight: 500;">
                                    Tu ticket se encuentra adjunto en este correo
                                </p>
                            </div>

                            <p style="margin: 24px 0 0; font-size: 14px; line-height: 1.6; color: #666;">
                                Conserva este ticket para cualquier consulta o trámite relacionado con tu compra.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="padding: 30px 40px; text-align: center; border-top: 1px solid #e5e5e5; background-color: #fafafa;">
                            <p style="margin: 0 0 8px; font-size: 13px; color: #999; line-height: 1.5;">
                                Si tienes alguna pregunta, no dudes en contactarnos.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #bbb;">
                                Este es un correo automático, por favor no respondas a este mensaje.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>
