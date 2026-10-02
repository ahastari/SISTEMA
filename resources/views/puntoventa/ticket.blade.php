<!DOCTYPE html>
<html lang="es" id="htmlElement" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Venta {{ $venta->folio }}</title>
    <style>
        /* Estilos optimizados para impresora térmica */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background-color: #f0f0f0;
        }
        .ticket {
            width: 80mm;
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
        
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 5px; }
        th, td { padding: 2px 0; }
        th { border-bottom: 1px dashed #000; font-weight: bold; }

        .factura-box {
            border: 1px solid #000;
            padding: 6px;
            margin-top: 8px;
            font-size: 10px;
            line-height: 1.3;
        }

        @media print {
            body { background-color: white; margin: 0; padding: 0; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="ticket">
        <!-- CABECERA DINÁMICA DE SUCURSAL -->
        <div class="text-center mb-2">
            <h2 style="margin: 0; font-size: 16px;">{{ $venta->sucursal->nombre ?? 'MI EMPRESA' }}</h2>
            <div class="mb-1">{{ $venta->sucursal->direccion ?? 'Dirección no especificada' }}</div>
            @if(!empty($venta->sucursal->telefono))
                <div>Tel: {{ $venta->sucursal->telefono }}</div>
            @endif
            @if(!empty($venta->sucursal->celular))
                <div>Cel: {{ $venta->sucursal->celular }}</div>
            @endif
        </div>

        <!-- DATOS DE LA VENTA -->
        <div class="border-top border-bottom mt-2">
            <div class="mb-1"><span class="bold">Folio:</span> {{ $venta->folio }}</div>
            <div class="mb-1"><span class="bold">Fecha:</span> {{ $venta->created_at->format('d/m/Y H:i') }}</div>
            
            @if($venta->cliente)
                <div class="mb-1"><span class="bold">Cliente:</span> {{ $venta->cliente->nombre_completo }}</div>
                @if($venta->requiere_factura && $venta->rfc_cliente)
                    <div class="mb-1"><span class="bold">RFC:</span> {{ $venta->rfc_cliente }}</div>
                @endif
            @else
                <div class="mb-1"><span class="bold">Cliente:</span> Público General</div>
            @endif
            
            <div style="margin-top: 5px; font-size: 9pt;">
                <strong>MÉTODO DE PAGO:</strong> {{ strtoupper($venta->metodo_pago) }}
                @if($venta->metodo_pago === 'credito')
                    <br><strong>PLAZO CRÉDITO:</strong> {{ $venta->dias_credito }} Días
                    <br><strong>VENCIMIENTO:</strong> {{ $venta->created_at->addDays($venta->dias_credito)->format('d/m/Y') }}
                @endif
            </div>
        </div>

        <hr style="border-top: 1px dashed #000;">

        <!-- DETALLE DE PRODUCTOS -->
        <table style="width: 100%; font-size: 9pt;">
            <tr>
                <td>SUBTOTAL:</td>
                <td style="text-align: right;">${{ number_format($venta->subtotal, 2) }}</td>
            </tr>
            @if($venta->descuento > 0)
            <tr style="color: #000;">
                <td><strong>DESCUENTO:</strong></td>
                <td style="text-align: right;"><strong>-${{ number_format($venta->descuento, 2) }}</strong></td>
            </tr>
            @endif
            @if($venta->iva > 0)
            <tr>
                <td>IVA (16%):</td>
                <td style="text-align: right;">${{ number_format($venta->iva, 2) }}</td>
            </tr>
            @endif
            <!-- <tr style="font-size: 11pt; font-weight: bold;">
                <td>TOTAL:</td>
                <td style="text-align: right;">${{ number_format($venta->total, 2) }}</td>
            </tr> -->
        </table>

        <!-- UNIFICACIÓN: TOTALES Y DESGLOSE DE COBRO -->
        <div class="border-top mt-2">
            <table style="margin: 0;">
                @if($venta->requiere_factura)
                    <tr>
                        <td class="text-right bold">Subtotal:</td>
                        <td class="text-right" style="width: 35%;">${{ number_format($venta->subtotal, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-right bold">IVA (16%):</td>
                        <td class="text-right">${{ number_format($venta->iva, 2) }}</td>
                    </tr>
                @endif

                <tr>
                    <td class="text-right bold" style="font-size: 14px; padding-top: 2px;">TOTAL:</td>
                    <td class="text-right bold" style="font-size: 14px; width: 35%; padding-top: 2px;">${{ number_format($venta->total, 2) }}</td>
                </tr>

                @php
                    $pagoCon = (float) ($venta->monto_recibido > 0 ? $venta->monto_recibido : $venta->total);
                    $cambioVal = (float) ($venta->cambio ?? ($pagoCon - $venta->total));
                @endphp

                <!-- DESGLOSE DETALLADO SOLO SI ES PAGO MIXTO -->
                @if(strtolower($venta->metodo_pago) === 'mixto' && !empty($venta->pagos_mixtos))
                    <tr>
                        <td colspan="2" class="text-left bold" style="padding-top: 6px; font-size: 11px;">Desglose Mixto:</td>
                    </tr>
                    @foreach($venta->pagos_mixtos as $pago)
                        <tr>
                            <td class="text-left" style="font-size: 11px; padding-left: 8px;">
                                • {{ ucfirst($pago['metodo'] ?? $pago->metodo) }}:
                            </td>
                            <td class="text-right" style="font-size: 11px;">
                                ${{ number_format($pago['monto'] ?? $pago->monto, 2) }}
                            </td>
                        </tr>
                    @endforeach
                @endif

                <tr>
                    <td class="text-right bold" style="padding-top: 6px;">Pagó con:</td>
                    <td class="text-right bold" style="padding-top: 6px;">${{ number_format($pagoCon, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right bold">Cambio:</td>
                    <td class="text-right bold">${{ number_format(max(0, $cambioVal), 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- AVISO DE SOLICITUD DE FACTURA -->
        @if($venta->requiere_factura)
            <div class="text-center factura-box">
                <div class="bold mb-1">*** REQUIERE FACTURA ***</div>
                <div>Favor de comunicarse con la sucursal o enviar sus datos fiscales para la emisión de su factura.</div>
                @if(!empty($venta->sucursal->telefono))
                    <div class="bold mt-2">Tel: {{ $venta->sucursal->telefono }}</div>
                @endif
                @if(!empty($venta->sucursal->celular))
                    <div class="bold {{ empty($venta->sucursal->telefono) ? 'mt-2' : '' }}">Cel: {{ $venta->sucursal->celular }}</div>
                @endif
            </div>
        @endif

        <!-- PIE DE PÁGINA -->
        <div class="text-center mt-2 border-top" style="padding-top: 10px;">
            <p style="margin: 0;">¡Gracias por su compra!</p>
            <p style="margin: 5px 0 0 0; font-size: 10px;">Este documento es un comprobante de venta.</p>
        </div>

    </div>
    
</body>
</html>