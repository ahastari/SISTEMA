<!DOCTYPE html>
<html lang="es" id="htmlElement" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Pago - {{ $pago->renta->folio }}</title>
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

        .obs-box {
            border: 1px solid #000;
            padding: 6px;
            margin-top: 8px;
            font-size: 11px;
            line-height: 1.3;
        }

        @media print {
            body { background-color: white; margin: 0; padding: 0; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 100%; }
        }
    </style>
</head>
<body>

    @php
        // Extraemos las observaciones y el equipo devuelto de la cadena guardada en el pago
        $obs = $pago->observaciones ?? '';
        $retorno = 'Ninguno';
        if(str_contains($obs, '| Equipo devuelto:')) {
            $parts = explode('| Equipo devuelto:', $obs);
            $obs = trim($parts[0]);
            $retorno = trim($parts[1]);
        }
    @endphp

    <div class="ticket">
        <!-- CABECERA DINÁMICA DE SUCURSAL -->
        <div class="text-center mb-2">
            <h2 style="margin: 0; font-size: 16px;">
                {{ $pago->renta->sucursal->nombre ?? \App\Helpers\ContentHelper::getNombreMostrar() ?? 'MI EMPRESA' }}
            </h2>
            <div class="mb-1">
                {{ $pago->renta->sucursal->direccion ?? \App\Helpers\ContentHelper::getCompanyData('empresa_direccion') ?? 'Dirección no especificada' }}
            </div>
            @php
                $sucTel = $pago->renta->sucursal->telefono ?? null;
                $sucCel = $pago->renta->sucursal->celular ?? null;
            @endphp
            @if($sucTel || $sucCel)
                @if($sucTel)<div>Tel: {{ $sucTel }}</div>@endif
                @if($sucCel)<div>Cel: {{ $sucCel }}</div>@endif
            @else
                <div>Tel: {{ \App\Helpers\ContentHelper::getCompanyData('empresa_telefono') ?? '' }}</div>
            @endif
        </div>

        <div class="text-center bold border-top pt-1 mb-1">
            COMPROBANTE DE PAGO
        </div>

        <!-- DATOS DE LA OPERACIÓN -->
        <div class="border-top border-bottom mt-2">
            <div class="mb-1"><span class="bold">Folio Renta:</span> {{ $pago->renta->folio }}</div>
            <div class="mb-1"><span class="bold">Fecha:</span> {{ $pago->fecha_pago->format('d/m/Y H:i') }}</div>
            
            @if($pago->renta->cliente)
                <div class="mb-1"><span class="bold">Cliente:</span> {{ $pago->renta->cliente->nombre_completo }}</div>
            @else
                <div class="mb-1"><span class="bold">Cliente:</span> Público General</div>
            @endif
            
            <div class="mb-1"><span class="bold">Método de Pago:</span> {{ strtoupper($pago->metodo_pago) }}</div>

            <!-- 🔥 NUEVA LÍNEA: Mostrar Referencia si aplica -->
            @if(in_array($pago->metodo_pago, ['transferencia', 'tarjeta', 'mixto']) && $pago->referencia)
                <div><span class="bold">Ref / Folio:</span> {{ $pago->referencia }}</div>
            @endif
        </div>

        <!-- DETALLE DE LA OPERACIÓN -->
        <table>
            <thead>
                <tr>
                    <th class="text-left">Cant</th>
                    <th class="text-left">Descripción</th>
                    <th class="text-right">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-left" style="vertical-align: top;">1</td>
                    <td class="text-left">
                        Operación: {{ strtoupper($pago->tipo) }}
                    </td>
                    <td class="text-right" style="vertical-align: top;">${{ number_format($pago->monto, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- TOTALES -->
        <div class="border-top mt-2">
            <table style="margin: 0;">
                <tr>
                    <td class="text-right bold" style="font-size: 14px; padding-top: 2px;">TOTAL PAGADO:</td>
                    <td class="text-right bold" style="font-size: 14px; width: 40%; padding-top: 2px;">${{ number_format($pago->monto, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- RETORNO DE EQUIPO Y OBSERVACIONES -->
        <div class="obs-box">
            <div class="bold mb-1">EQUIPO RETORNADO:</div>
            <div>{{ $retorno }}</div>
        </div>

        @if($obs && $obs !== 'Registro de abono' && $obs !== 'Registro de pago / liquidación final')
        <div class="obs-box" style="margin-top: 5px;">
            <div class="bold mb-1">OBSERVACIONES:</div>
            <div>{{ $obs }}</div>
        </div>
        @endif

        <!-- PIE DE PÁGINA -->
        <div class="text-center mt-2 border-top" style="padding-top: 10px;">
            <p style="margin: 0;">Este comprobante ampara el abono o</p>
            <p style="margin: 3px 0 0 0;">liquidación a la renta especificada.</p>
            <p style="margin: 8px 0 0 0; font-size: 11px; font-weight: bold;">¡Gracias por su preferencia!</p>
        </div>

    </div>
    
    <!-- Script para auto-impresión segura -->
    <script>
        window.addEventListener('load', function() {
            setTimeout(function() {
                window.print();
            }, 500); // 500ms de retraso asegura que el navegador no lo bloquee
        });
    </script>
</body>
</html>