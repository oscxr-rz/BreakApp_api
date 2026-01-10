<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Ticket - {{ $ticket->numero_ticket }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 10px;
            line-height: 1.4;
            color: #000;
            padding: 8px;
        }

        .ticket {
            width: 100%;
            max-width: 80mm;
        }

        /* LOGO */
        .logo {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 6px;
        }

        .logo img {
            max-width: 100px;
            max-height: 45px;
            display: inline-block;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 10px;
            border-bottom: 2px dashed #000;
            padding-bottom: 8px;
        }

        .app-name {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .contact-info {
            font-size: 8px;
            margin-top: 3px;
            line-height: 1.3;
        }

        /* SEPARADORES */
        .separator {
            text-align: center;
            margin: 8px 0;
            font-size: 10px;
            letter-spacing: 2px;
        }

        /* SECCIONES */
        .section {
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px dashed #000;
        }

        .section-title {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 4px;
            text-align: center;
            letter-spacing: 0.5px;
            padding: 3px 0;
            border-bottom: 1px solid #000;
        }

        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
            font-size: 9px;
        }

        .info-label {
            display: table-cell;
            font-weight: bold;
            width: 45%;
        }

        .info-value {
            display: table-cell;
            width: 55%;
            text-align: right;
        }

        /* TABLA DE PRODUCTOS */
        .productos {
            margin: 8px 0;
        }

        .productos-header {
            border-bottom: 2px solid #000;
            border-top: 2px solid #000;
            padding: 3px 0;
            font-weight: bold;
            font-size: 8px;
            display: table;
            width: 100%;
            margin-bottom: 3px;
        }

        .productos-header>span {
            display: table-cell;
        }

        .col-producto {
            width: 45%;
            text-align: left;
        }

        .col-cant {
            width: 15%;
            text-align: center;
        }

        .col-precio {
            width: 20%;
            text-align: right;
        }

        .col-total {
            width: 20%;
            text-align: right;
        }

        .producto-item {
            display: table;
            width: 100%;
            padding: 4px 0;
            border-bottom: 1px dotted #999;
            font-size: 8px;
        }

        .producto-item:last-child {
            border-bottom: 2px solid #000;
        }

        .producto-item>span {
            display: table-cell;
            vertical-align: top;
        }

        .producto-nombre {
            font-weight: bold;
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .producto-descripcion {
            font-size: 7px;
            color: #444;
            line-height: 1.2;
            margin-top: 2px;
        }

        /* TOTALES */
        .totales {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 2px solid #000;
        }

        .total-row {
            display: table;
            width: 100%;
            margin-bottom: 3px;
            font-size: 9px;
        }

        .total-label {
            display: table-cell;
            font-weight: bold;
            width: 55%;
            text-align: right;
            padding-right: 15px;
        }

        .total-value {
            display: table-cell;
            width: 45%;
            text-align: right;
            font-weight: bold;
        }

        .total-final {
            font-size: 12px;
            font-weight: bold;
            margin-top: 5px;
            padding-top: 5px;
            border-top: 2px solid #000;
        }

        /* PAGO */
        .pago-section {
            margin: 8px 0;
            padding: 6px 0;
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
        }

        /* QR CODE */
        .qr-section {
            text-align: center;
            margin: 8px 0;
            padding: 6px;
            border: 2px solid #000;
        }

        .qr-title {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .qr-section img {
            width: 80px;
            height: 80px;
            display: inline-block;
        }

        /* CÓDIGO DE BARRAS */
        .barcode-section {
            text-align: center;
            margin: 8px 0;
            padding: 6px 0;
            border: 1px solid #000;
        }

        .barcode {
            font-size: 14px;
            font-family: monospace;
            letter-spacing: 2px;
            font-weight: bold;
        }

        /* CONDICIONES */
        .condiciones {
            font-size: 7px;
            text-align: center;
            margin: 8px 0;
            padding: 6px;
            border: 2px solid #000;
            line-height: 1.4;
            background-color: #f5f5f5;
        }

        .condiciones-title {
            font-weight: bold;
            font-size: 8px;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        /* AGRADECIMIENTO */
        .agradecimiento {
            text-align: center;
            font-size: 10px;
            margin-top: 8px;
            font-weight: bold;
            letter-spacing: 0.5px;
            line-height: 1.4;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            font-size: 7px;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 2px dashed #000;
            line-height: 1.4;
            color: #666;
        }

        /* BOX DESTACADO */
        .box-highlight {
            border: 2px solid #000;
            padding: 6px;
            margin: 8px 0;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <!-- LOGO -->
        <div class="logo">
            <img src="{{ public_path('storage/logo.png') }}" alt="Logo">
        </div>

        <!-- HEADER -->
        <div class="header">
            <div class="app-name">{{ $datosApp['nombre'] }}</div>
            <div class="contact-info">
                {{ $datosApp['correo'] }}<br>
                {{ $datosApp['sitio_web'] }}
            </div>
        </div>

        <!-- FECHA Y HORA -->
        <div class="section">
            <div class="info-row">
                <span class="info-label">FECHA:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($orden->fecha_creacion)->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">HORA:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($orden->fecha_creacion)->format('H:i:s') }}</span>
            </div>
        </div>

        <!-- INFORMACIÓN DE LA ORDEN -->
        <div class="section">
            <div class="section-title">Detalles de la Orden</div>
            <div class="info-row">
                <span class="info-label">Orden ID:</span>
                <span class="info-value">#{{ $orden->id_orden }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Numero Ticket:</span>
                <span class="info-value">{{ $ticket->numero_ticket }}</span>
            </div>
        </div>

        <!-- INFORMACIÓN DEL USUARIO -->
        <div class="section">
            <div class="section-title">Cliente</div>
            <div class="info-row">
                <span class="info-label">Nombre:</span>
                <span class="info-value">{{ $usuario->nombre }} {{ $usuario->apellido }}</span>
            </div>
            @if ($usuario->email)
                <div class="info-row">
                    <span class="info-label">Email:</span>
                    <span class="info-value" style="font-size: 7px;">{{ $usuario->email }}</span>
                </div>
            @endif
            @if (!empty($usuario->telefono))
                <div class="info-row">
                    <span class="info-label">Telefono:</span>
                    <span class="info-value">{{ $usuario->telefono }}</span>
                </div>
            @endif
        </div>

        <!-- PRODUCTOS -->
        @if ($orden->productos && $orden->productos->count() > 0)
            <div class="productos">
                <div class="section-title">Productos</div>

                <div class="productos-header">
                    <span class="col-producto">PRODUCTO</span>
                    <span class="col-cant">CANT</span>
                    <span class="col-precio">P.UNIT</span>
                    <span class="col-total">TOTAL</span>
                </div>

                @php
                    $subtotal = 0;
                @endphp

                @foreach ($orden->productos as $producto)
                    @php
                        $cantidad = $producto->pivot->cantidad ?? 1;
                        $precioUnitario = floatval($producto->precio);
                        $totalProducto = $cantidad * $precioUnitario;
                        $subtotal += $totalProducto;
                    @endphp
                    <div class="producto-item">
                        <span class="col-producto">
                            <div class="producto-nombre">{{ $producto->nombre }}</div>
                            @if ($producto->descripcion && strlen($producto->descripcion) > 0)
                                <div class="producto-descripcion">{{ $producto->descripcion }}</div>
                            @endif
                        </span>
                        <span class="col-cant">{{ $cantidad }}</span>
                        <span class="col-precio">${{ number_format($precioUnitario, 2) }}</span>
                        <span class="col-total">${{ number_format($totalProducto, 2) }}</span>
                    </div>
                @endforeach
            </div>

            <!-- TOTALES -->
            <div class="totales">
                <div class="total-row">
                    <span class="total-label">SUBTOTAL:</span>
                    <span class="total-value">${{ number_format($subtotal, 2) }}</span>
                </div>

                <div class="total-row total-final">
                    <span class="total-label">TOTAL:</span>
                    <span class="total-value">${{ number_format($orden->total, 2) }} MXN</span>
                </div>
            </div>
        @else
            <!-- SI NO HAY PRODUCTOS DETALLADOS -->
            <div class="totales">
                <div class="total-row total-final">
                    <span class="total-label">TOTAL:</span>
                    <span class="total-value">${{ number_format($orden->total, 2) }} MXN</span>
                </div>
            </div>
        @endif

        <!-- MÉTODO DE PAGO -->
        <div class="pago-section">
            <div class="info-row">
                <span class="info-label">Metodo de Pago:</span>
                <span class="info-value">{{ strtoupper($orden->metodo_pago) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Estado Pago:</span>
                <span class="info-value">PAGADO</span>
            </div>
        </div>

        <!-- CÓDIGO QR -->
        <div class="qr-section">
            <div class="qr-title">Codigo QR de Verificacion</div>
            <img src="{{ public_path('storage/' . $qr) }}" alt="QR Code">
        </div>

        <!-- CÓDIGO DE BARRAS DEL TICKET -->
        @if ($ticket->numero_ticket)
            <div class="barcode-section">
                <div class="barcode">{{ str_replace('-', '', $ticket->numero_ticket) }}</div>
            </div>
        @endif

        <!-- CONDICIONES -->
        <div class="condiciones">
            <div class="condiciones-title">IMPORTANTE</div>
            Este ticket NO es reembolsable.<br>
            Conserve este comprobante para cualquier aclaracion.<br>
            Valido unicamente con este comprobante y codigo QR.
        </div>

        <!-- MENSAJE DE AGRADECIMIENTO -->
        <div class="agradecimiento">
            Gracias por tu preferencia!<br>
            Esperamos verte pronto
        </div>

        <!-- FOOTER -->
        <div class="footer">
            Documento generado electronicamente<br>
            {{ now()->format('d/m/Y H:i:s') }}<br>
            Sistema de Tickets - {{ $datosApp['nombre'] }}
        </div>
    </div>
</body>

</html>
