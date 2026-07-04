<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta {{ $venta->folio }}</title>
    <style>
        /* Estilos optimizados para impresora térmica */
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente monoespaciada ideal para tickets */
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .ticket {
            width: 80mm; /* Ancho estándar de impresora térmica */
            max-width: 100%;
            background: #fff;
            margin: 20px auto;
            padding: 15px;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 10px; }
        th, td { padding: 2px 0; }
        th { border-bottom: 1px dashed #000; font-weight: bold; }
        
        .acciones {
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            padding: 8px 15px;
            background: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-family: Arial, sans-serif;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .btn-secondary { background: #6c757d; }

        /* Ocultar elementos de la interfaz al imprimir */
        @media print {
            body { background-color: white; margin: 0; padding: 0; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 100%; }
            .acciones { display: none; }
        }
    </style>
</head>
<body>

    <div class="ticket">
        <div class="text-center mb-2">
            <h2 style="margin: 0;">MI EMPRESA</h2>
            <div class="mb-1">Dirección de la sucursal</div>
            <div>Tel: (123) 456-7890</div>
        </div>

        <div class="border-top border-bottom mt-2">
            <div class="mb-1"><span class="bold">Folio:</span> {{ $venta->folio }}</div>
            <div class="mb-1"><span class="bold">Fecha:</span> {{ $venta->created_at->format('d/m/Y H:i') }}</div>
            @if($venta->cliente)
                <div class="mb-1"><span class="bold">Cliente:</span> {{ $venta->cliente->nombre_completo }}</div>
            @else
                <div class="mb-1"><span class="bold">Cliente:</span> Público General</div>
            @endif
            <div><span class="bold">Método:</span> {{ strtoupper($venta->metodo_pago) }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th class="text-left">Cant</th>
                    <th class="text-left">Descripción</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach($venta->detalles as $detalle)
                <tr>
                    <td class="text-left" style="vertical-align: top;">{{ $detalle->cantidad }}</td>
                    <td class="text-left">
                        {{ $detalle->equipo->nombre }}
                        <br>
                        <small>${{ number_format($detalle->precio_unitario, 2) }} c/u</small>
                    </td>
                    <td class="text-right" style="vertical-align: top;">${{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="border-top mt-2">
            @if($venta->requiere_factura)
                <table style="margin: 0;">
                    <tr>
                        <td class="text-right bold">Subtotal:</td>
                        <td class="text-right" style="width: 35%;">${{ number_format($venta->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right bold">IVA (16%):</td>
                        <td class="text-right">${{ number_format($venta->iva, 2) }}</td>
                    </tr>
                </table>
            @endif
            
            <table style="margin: 0; font-size: 14px;">
                <tr>
                    <td class="text-right bold" style="padding-top: 5px;">TOTAL:</td>
                    <td class="text-right bold" style="width: 35%; padding-top: 5px;">${{ number_format($venta->total, 2) }}</td>
                </tr>
            </table>
        </div>

        <div class="text-center mt-2 border-top" style="padding-top: 10px;">
            <p style="margin: 0;">¡Gracias por su compra!</p>
            <p style="margin: 5px 0 0 0; font-size: 10px;">Este documento es un comprobante de venta.</p>
        </div>

    </div>
    
</body>
</html>