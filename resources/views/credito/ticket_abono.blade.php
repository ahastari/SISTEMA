<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Abono - {{ $venta->folio }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 0; background-color: #f0f0f0; }
        .ticket { width: 80mm; max-width: 100%; background: #fff; margin: 20px auto; padding: 15px; box-shadow: 0 0 5px rgba(0,0,0,0.2); }
        .text-center { text-align: center; } .text-right { text-align: right; } .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; } .mb-2 { margin-bottom: 10px; } .mt-2 { margin-top: 10px; }
        .border-top { border-top: 1px dashed #000; padding-top: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 5px; }
        th, td { padding: 2px 0; }
        .obs-box { border: 1px solid #000; padding: 6px; margin-top: 8px; font-size: 11px; line-height: 1.3; }
        @media print {
            body { background-color: white; }
            .ticket { margin: 0; padding: 0; box-shadow: none; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="text-center mb-2">
            <h2 style="margin: 0; font-size: 16px;">{{ $venta->sucursal->nombre ?? 'MI EMPRESA' }}</h2>
            <div class="mb-1">{{ $venta->sucursal->direccion ?? '' }}</div>
            @if(!empty($venta->sucursal->telefono))<div>Tel: {{ $venta->sucursal->telefono }}</div>@endif
            @if(!empty($venta->sucursal->celular))<div>Cel: {{ $venta->sucursal->celular }}</div>@endif
        </div>

        <div class="text-center bold border-top mb-1">COMPROBANTE DE ABONO</div>

        <div class="border-top border-bottom mt-2">
            <div class="mb-1"><span class="bold">Venta a crédito:</span> {{ $venta->folio }}</div>
            <div class="mb-1"><span class="bold">Fecha abono:</span> {{ $abono->created_at->format('d/m/Y H:i') }}</div>
            <div class="mb-1"><span class="bold">Cliente:</span> {{ $venta->cliente_nombre ?: ($venta->cliente->nombre_completo ?? 'Público General') }}</div>
            <div class="mb-1"><span class="bold">Método:</span> {{ strtoupper($abono->metodo) }}</div>
            @if($abono->metodo === 'mixto')
                @foreach(\App\Http\Controllers\CreditoController::desglosePago($abono) as $p)
                    <div class="mb-1" style="padding-left: 8px;">- {{ ucfirst($p['metodo']) }}: ${{ number_format($p['monto'], 2) }}</div>
                @endforeach
            @endif
            @if($abono->referencia)<div class="mb-1"><span class="bold">Ref:</span> {{ $abono->referencia }}</div>@endif
            <div><span class="bold">Recibió:</span> {{ $abono->usuario->name ?? '—' }}</div>
        </div>

        <table>
            <tr><td>Total del crédito:</td><td class="text-right">${{ number_format($venta->total, 2) }}</td></tr>
            <tr><td>Plazo:</td><td class="text-right">{{ $venta->dias_credito }} días</td></tr>
            <tr><td>Vencimiento:</td><td class="text-right">{{ $vence->format('d/m/Y') }}</td></tr>
            <tr><td>Abonado a la fecha:</td><td class="text-right">${{ number_format($abonadoHastaAqui, 2) }}</td></tr>
        </table>

        <div class="border-top mt-2">
            <table style="margin: 0;">
                <tr>
                    <td class="text-right bold" style="font-size: 14px;">ESTE ABONO:</td>
                    <td class="text-right bold" style="font-size: 14px; width: 40%;">${{ number_format($abono->monto, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-right bold">SALDO RESTANTE:</td>
                    <td class="text-right bold">${{ number_format($saldoRestante, 2) }}</td>
                </tr>
            </table>
        </div>

        @if($saldoRestante <= 0.009)
            <div class="obs-box text-center bold">*** CRÉDITO LIQUIDADO ***</div>
        @endif
        @if($abono->observaciones)
            <div class="obs-box"><div class="bold mb-1">OBSERVACIONES:</div><div>{{ $abono->observaciones }}</div></div>
        @endif

        <div class="text-center mt-2 border-top" style="padding-top: 10px;">
            <p style="margin: 0;">Este comprobante ampara el abono</p>
            <p style="margin: 3px 0 0 0;">a la venta a crédito especificada.</p>
            <p style="margin: 8px 0 0 0; font-size: 11px; font-weight: bold;">¡Gracias por su preferencia!</p>
        </div>
    </div>

    <script>
        // Solo auto-imprime si se abre en su propia pestaña (no dentro del modal)
        if (window.self === window.top) {
            window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 500); });
        }
    </script>
</body>
</html>