<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Pago - {{ $pago->renta->folio }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 10px; max-width: 80mm; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; font-size: 12px; }
        .ticket-header { font-size: 16px; margin-bottom: 5px; }
        .returned-box { margin-top: 10px; font-size: 11px; }
    </style>
</head>
<body onload="window.print();">

    @php
        $obs = $pago->observaciones ?? '';
        $retorno = 'Ninguno';
        if(str_contains($obs, '| Equipo devuelto:')) {
            $parts = explode('| Equipo devuelto:', $obs);
            $obs = trim($parts[0]);
            $retorno = trim($parts[1]);
        }
    @endphp

    <div class="text-center bold ticket-header">
        {{ \App\Helpers\ContentHelper::getNombreMostrar() }}
    </div>
    <div class="text-center">COMPROBANTE DE PAGO</div>
    <div class="text-center" style="font-size: 10px;">{{ $pago->fecha_pago->format('d/m/Y H:i') }}</div>
    <div class="line"></div>
    
    <div><strong>FOLIO RENTA:</strong> {{ $pago->renta->folio }}</div>
    <div><strong>CLIENTE:</strong> {{ \Illuminate\Support\Str::limit($pago->renta->cliente->nombre_completo ?? 'General', 25) }}</div>
    
    <div class="line"></div>
    
    <div class="text-center bold" style="font-size: 14px; margin: 5px 0;">
        OPERACIÓN: {{ strtoupper($pago->tipo) }}<br>
        PAGO VÍA: {{ strtoupper($pago->metodo_pago) }}
    </div>
    
    <div class="line"></div>
    
    <table>
        <tr>
            <td class="bold">MONTO PAGADO:</td>
            <td class="text-right bold" style="font-size: 16px;">${{ number_format($pago->monto, 2) }}</td>
        </tr>
    </table>
    
    <div class="line"></div>

    <div class="returned-box">
        <div class="bold">EQUIPO RETORNADO:</div>
        <div style="margin-top: 3px;">{{ $retorno }}</div>
    </div>

    @if($obs && $obs !== 'Registro de abono' && $obs !== 'Registro de pago / liquidación final')
    <div class="returned-box">
        <div class="bold">OBSERVACIONES:</div>
        <div style="margin-top: 3px;">{{ $obs }}</div>
    </div>
    @endif
    
    <div class="line"></div>
    
    <div class="text-center" style="margin-top: 10px; font-size: 11px;">
        Este comprobante ampara el abono/liquidación a la renta especificada.<br><br>
        ¡Gracias por su preferencia!
    </div>
</body>
</html>