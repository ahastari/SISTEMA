<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contrato de Renta - {{ $renta->folio }}</title>
    <style>
        @page {
            margin: 1.5cm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .company-address {
            font-size: 9px;
            margin: 5px 0;
        }
        .company-rfc {
            font-size: 9px;
            font-weight: bold;
        }
        .title {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            text-transform: uppercase;
        }
        .folio {
            text-align: right;
            font-size: 10px;
            margin-bottom: 10px;
        }
        .section-title {
            font-weight: bold;
            margin: 15px 0 5px 0;
            text-decoration: underline;
        }
        .clausulas {
            margin: 15px 0;
        }
        .clausula {
            margin: 5px 0;
        }
        .clausula-number {
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .data-table td, .data-table th {
            border: 1px solid #000;
            padding: 6px;
            vertical-align: top;
        }
        .data-table th {
            background: #f0f0f0;
            font-weight: bold;
        }
        .signature {
            margin-top: 40px;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 80%;
            margin: 30px auto 5px auto;
        }
        .signature-text {
            text-align: center;
            font-size: 10px;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
        }
        .page-break {
            page-break-before: always;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body>

<!-- PRIMERA PÁGINA -->
<div>
    <!-- Encabezado con logo -->
    <div class="header">
        <img src="{{ public_path('images/logo.jpeg') }}" class="logo" alt="Logo">
        <div class="company-name">ANDAMIOS Y MADERA VIRAMONTES</div>
        <div class="company-address">MARCO ANTONIO VIRAMONTES MATURINO - VIMM9103023K7</div>
        <div class="company-address">AVE. DEL CIPRES #314 COL. MASIE DURANGO, DGO. C.P. 34217</div>
        <div class="company-address">TEL. 618 455 36 71 CEL. 618 159 70 19</div>
    </div>

    <div class="folio">
        <strong>N° {{ $renta->folio }}</strong>
    </div>

    <div class="title">
        CONTRATO DE PRESTACIÓN DE SERVICIOS DE RENTA
    </div>

    <div style="margin: 10px 0;">
        Que celebran por una parte el prestador de servicios <strong>ING. GODOFREDO VIRAMONTES MEDINA</strong> 
        y por otra parte el usuario denominado <strong>CLIENTE</strong>.
    </div>

    <!-- CLAUSULAS -->
    <div class="section-title">CLAUSULAS</div>
    <div class="clausulas">
        <div class="clausula"><span class="clausula-number">1.</span> - El prestador de servicios se compromete a entregar en perfectas condiciones de trabajo el equipo.</div>
        <div class="clausula"><span class="clausula-number">2.</span> - El cliente tiene la obligación de verificar el buen estado en que recibe el equipo y entregarlo de igual forma.</div>
        <div class="clausula"><span class="clausula-number">3.</span> - Las piezas faltantes o averiadas se cobrarán en efectivo, no siendo sustituidas por otras, pues esto es indispensable para la uniformidad y el buen estado del equipo.</div>
        <div class="clausula"><span class="clausula-number">4.</span> - En la renta del equipo NO HAY CREDITO por lo que al devolver el equipo se deberá liquidar la renta.</div>
        <div class="clausula"><span class="clausula-number">5.</span> - El cliente está obligado a dejar un depósito por la cantidad de <strong>${{ number_format($renta->deposito ?? 0, 2) }}</strong> que garantiza la devolución del equipo en buen estado.</div>
        <div class="clausula"><span class="clausula-number">6.</span> - El prestador de servicios se compromete a no hacer uso de este depósito, salvo si el cliente llegara a hacer mal uso del equipo, se niegue a regresar el equipo o se niegue a pagar la renta del mismo.</div>
        <div class="clausula"><span class="clausula-number">7.</span> - En caso de que el equipo se rente por periodos mayor a 30 días se cobrará cada 15 días.</div>
        <div class="clausula"><span class="clausula-number">8.</span> - Se cobrará el día de salida y el día de entrada.</div>
    </div>

    <!-- DATOS DEL CLIENTE -->
    <div class="section-title">DATOS DEL CLIENTE</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">NOMBRE</th>
            <td colspan="3">{{ $renta->cliente->nombre_completo }}</td>
        </tr>
        <tr>
            <th>DIRECCIÓN</th>
            <td colspan="3">{{ $renta->cliente->direccion ?? 'No especificada' }}</td>
        </tr>
        <tr>
            <th>COLONIA</th>
            <td style="width: 25%;">{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
            <th style="width: 25%;">CIUDAD</th>
            <td style="width: 25%;">{{ $renta->cliente->ciudad ?? 'Durango' }}</td>
        </tr>
        <tr>
            <th>TELÉFONO</th>
            <td>{{ $renta->cliente->telefono }}</td>
            <th>IDENTIFICACIÓN</th>
            <td>{{ $renta->cliente->ine_numero ?? 'No especificada' }}</td>
        </tr>
    </table>

    <!-- EQUIPO RENTADO -->
    <div class="section-title">EQUIPO RENTADO</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>CANTIDAD</th>
                <th>DESCRIPCIÓN</th>
                <th>PRECIO/DÍA</th>
                <th>SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($renta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->cantidad }} x {{ $detalle->dias }} días</td>
                <td>{{ $detalle->equipo->nombre }} ({{ $detalle->equipo->codigo }})</td>
                <td class="text-right">${{ number_format($detalle->precio_dia, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right bold">Subtotal:</td>
                <td class="text-right">${{ number_format($renta->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right bold">IVA (16%):</td>
                <td class="text-right">${{ number_format($renta->iva, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right bold">TOTAL:</td>
                <td class="text-right bold">${{ number_format($renta->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- DATOS DE LA OBRA -->
    <div class="section-title">DATOS DE LA OBRA</div>
    <table class="data-table">
        <tr>
            <th style="width: 25%;">NOMBRE</th>
            <td style="width: 25%;">{{ $renta->observaciones ?? 'No especificado' }}</td>
            <th style="width: 25%;">DIRECCIÓN</th>
            <td style="width: 25%;">{{ $renta->cliente->direccion ?? 'No especificada' }}</td>
        </tr>
        <tr>
            <th>COLONIA</th>
            <td>{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
            <th>CIUDAD</th>
            <td>{{ $renta->cliente->ciudad ?? 'Durango' }}</td>
        </tr>
        <tr>
            <th>TELÉFONO</th>
            <td>{{ $renta->cliente->telefono }}</td>
            <th>FECHA</th>
            <td>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</td>
        </tr>
    </table>
</div>

<!-- SEGUNDA PÁGINA - PAGARÉ -->
<div class="page-break">
    <div class="title">PAGARÉ</div>
    
    <div style="margin: 20px 0;">
        <p>DURANGO, DGO. A {{ \Carbon\Carbon::now()->format('d') }} DE {{ strtoupper(\Carbon\Carbon::now()->locale('es')->monthName) }} DE {{ \Carbon\Carbon::now()->format('Y') }}</p>
        
        <p>BUENO POR ${{ number_format($renta->total - ($renta->deposito ?? 0), 2) }}</p>
        
        <p>DEBO (EMOS) Y PAGARÉ (EMOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DEL <strong>ING. GODOFREDO VIRAMONTES MEDINA</strong> EN DURANGO, DGO. EL DÍA <strong>{{ \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') }}</strong> LA CANTIDAD DE <strong>${{ number_format($renta->total - ($renta->deposito ?? 0), 2) }}</strong> VALOR RECIBIDO A MI (NUESTRA) ENTERA SATISFACCIÓN, EN CASO DE DEMORA PARCIALMENTE INSOLUTO SIN QUE POR ELLO SE CONSIDERE PRORROGADO EL PLAZO FIJADO.</p>
    </div>

    <table class="data-table" style="margin-top: 40px;">
        <tr>
            <th style="width: 25%;">NOMBRE</th>
            <td style="width: 25%;">{{ $renta->cliente->nombre_completo }}</td>
            <th style="width: 25%;">DIRECCIÓN</th>
            <td style="width: 25%;">{{ $renta->cliente->direccion ?? 'No especificada' }}</td>
        </tr>
        <tr>
            <th>COLONIA</th>
            <td>{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
            <th>CIUDAD</th>
            <td>{{ $renta->cliente->ciudad ?? 'Durango' }}</td>
        </tr>
        <tr>
            <th>TELÉFONO</th>
            <td colspan="3">{{ $renta->cliente->telefono }}</td>
        </tr>
    </table>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="signature-text">ACEPTAMOS</div>
        <div class="signature-text" style="margin-top: 10px;"><strong>{{ $renta->cliente->nombre_completo }}</strong></div>
    </div>

    <div class="footer">
        Gracias por su preferencia
    </div>
</div>

<!-- TERCERA PÁGINA - NOTA DE REMISIÓN (opcional) -->
<div class="page-break">
    <div class="title">NOTA DE REMISIÓN</div>
    
    <div style="margin: 20px 0;">
        <p><strong>FECHA:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
        <p><strong>PARA:</strong> {{ $renta->cliente->nombre_completo }}</p>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>DESCRIPCIÓN</th>
                <th>CANTIDAD</th>
                <th>COSTO UNITARIO</th>
                <th>IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($renta->detalles as $detalle)
            <tr>
                <td>{{ $detalle->equipo->nombre }}</td>
                <td>{{ $detalle->cantidad }}</td>
                <td class="text-right">${{ number_format($detalle->precio_dia, 2) }}</td>
                <td class="text-right">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right bold">SUBTOTAL:</td>
                <td class="text-right">${{ number_format($renta->subtotal, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right bold">IVA (16%):</td>
                <td class="text-right">${{ number_format($renta->iva, 2) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right bold">TOTAL:</td>
                <td class="text-right bold">${{ number_format($renta->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div style="margin-top: 30px;">
        <p>Quedo enterado que al momento de terminar de usar el equipo en renta avisaré a "Andamios y Madera Viramontes" pasar por este mismo (andamios, revolvedora, cimbra, vibrador de concreto).</p>
        <p><strong>(Si no se avisa seguirá corriendo la renta en cuestión y se cobrará los días extras que se generen hasta que se dé aviso "Andamios y Madera Viramontes")</strong></p>
    </div>

    <div class="signature">
        <div class="signature-line"></div>
        <div class="signature-text"><strong>{{ $renta->cliente->nombre_completo }}</strong></div>
        <div class="signature-text">FIRMA</div>
    </div>

    <div class="footer">
        Sin otro particular a que hacer referencia y esperando vemos favorecidos con sus apreciables ordenes nos es grato quedar de usted como sus amigos y S.S.<br><br>
        <strong>GRACIAS POR SU PREFERENCIA</strong>
    </div>
</div>

</body>
</html>