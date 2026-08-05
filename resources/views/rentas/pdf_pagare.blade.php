<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pagaré - {{ $renta->folio }}</title>
    <style>
        @page {
            margin: 1.2cm 1.2cm 1.5cm 1.2cm;
            size: letter portrait;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #0f172a;
            margin: 0;
            padding: 0;
        }

        /* ENCABEZADO CORPORATIVO SUCURSAL */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-img {
            max-height: 55px;
            max-width: 160px;
            margin-bottom: 4px;
        }
        .brand-title {
            font-size: 12pt;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.3px;
        }
        .branch-badge {
            display: inline-block;
            background: #f0f9ff;
            color: #0369a1;
            font-size: 7.5pt;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            border: 1px solid #bae6fd;
            margin-top: 2px;
            margin-bottom: 3px;
        }
        .branch-details {
            font-size: 7.5pt;
            color: #475569;
            line-height: 1.25;
        }

        /* TITULARES Y SECCIONES */
        .doc-title {
            font-size: 13pt;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            color: #0f172a;
            margin: 15px 0 10px 0;
            letter-spacing: 0.5px;
        }
        .section-header {
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0f172a;
            background: #f8fafc;
            border-left: 3px solid #0284c7;
            padding: 3px 6px;
            margin-top: 15px;
            margin-bottom: 6px;
        }

        /* TABLAS DE DATOS */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        .data-table td, .data-table th {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 8.5pt;
            vertical-align: middle;
        }
        .data-table th {
            background: #0f172a;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 7.5pt;
        }
        .data-table tr:nth-child(even) td {
            background: #f8fafc;
        }

        /* CAJA DE TEXTO EDITABLE DEL PAGARÉ */
        .legal-box {
            background: #ffffff;
            border: 2px solid #0f172a;
            border-radius: 4px;
            padding: 14px;
            font-size: 9pt;
            text-align: justify;
            line-height: 1.45;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .amount-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 12px;
            margin-bottom: 12px;
            font-size: 9pt;
        }

        /* FIRMAS ESTRUCTURADAS */
        .signature-table {
            width: 100%;
            margin-top: 45px;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            width: 80%;
            margin: 0 auto 4px auto;
        }
        .signature-title {
            font-size: 8.5pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
        }
        .signature-sub {
            font-size: 7.5pt;
            color: #64748b;
        }

        /* AUXILIARES */
        .footer {
            position: fixed;
            bottom: -0.8cm;
            left: 0;
            right: 0;
            font-size: 7.5pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            text-align: center;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>

@php
    // 1. DATO CORPORATIVO PRINCIPAL Y SUCURSAL
    $empresaNombreGlobal = \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'ANDAMIOS Y MADERA VIRAMONTES');
    
    $sucursal     = $renta->sucursal ?? null;
    $sucNombre    = $sucursal->nombre ?? 'MATRIZ GENERAL';
    $sucRfc       = $sucursal->rfc ?? \App\Helpers\ContentHelper::getCompanyData('empresa_rfc', 'VIMM9103023K7');
    $sucDireccion = $sucursal->direccion ?? \App\Helpers\ContentHelper::getCompanyData('empresa_direccion', 'AVE. DEL CIPRÉS #314 COL. MASIE');
    $sucTelefono  = $sucursal->telefono ?? \App\Helpers\ContentHelper::getCompanyData('empresa_telefono', '618 455 36 71');
    $sucLogoPath  = $sucursal->logo ?? \App\Helpers\ContentHelper::getCompanyData('empresa_logo');

    // 2. PARSEO DE PLANTILLA DEL PAGARÉ
    $pPagare = \App\Models\PlantillaDocumento::where('tipo', 'pagare_renta')->first() 
            ?? \App\Models\PlantillaDocumento::where('tipo', 'pagare')->first();

    $textoPagare = $pPagare ? $pPagare->contenido : "Por este pagaré me (nos) obligo (amos) incondicionalmente a pagar a la orden de {empresa} en Durango, Dgo. el día {fecha_fin} la cantidad de \${monto_neto} ({monto_total_letra}), valor recibido a mi (nuestra) entera satisfacción.\n\nEn caso de demora parcialmente insoluto sin que por ello se considere prorrogado el plazo fijado, el deudor pagará intereses moratorios al 5% mensual sobre el saldo insoluto.";

    // 3. REEMPLAZO AUTOMÁTICO DE MONTOS Y ETIQUETAS
    $montoTotalVal = (float)($renta->total ?? 0);
    $depositoVal   = (float)($renta->deposito_garantia ?? ($renta->deposito ?? 0));
    $montoNetoVal  = max(0, $montoTotalVal - $depositoVal);

    $fFinVal = isset($renta->fecha_fin) ? \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') : (isset($renta->fecha_devolucion_estimada) ? \Carbon\Carbon::parse($renta->fecha_devolucion_estimada)->format('d/m/Y') : date('d/m/Y'));

    // Convertidor a letras rápido como respaldo
    $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    $entero = floor($montoNetoVal);
    $decimales = round(($montoNetoVal - $entero) * 100);
    $montoLetrasStr = strtoupper($formatter->format($entero)) . " PESOS " . str_pad($decimales, 2, '0', STR_PAD_LEFT) . "/100 M.N.";

    $reemplazos = [
        '{empresa}'          => $empresaNombreGlobal,
        '{cliente}'          => $renta->cliente->nombre_completo ?? 'PÚBLICO GENERAL',
        '{folio}'            => $renta->folio ?? 'N/A',
        '{deposito}'         => number_format($depositoVal, 2),
        '{monto_total}'      => number_format($montoTotalVal, 2),
        '{monto_neto}'       => number_format($montoNetoVal, 2),
        '{monto_total_letra}'=> $montoLetrasStr,
        '{fecha_fin}'        => $fFinVal,
        '{fecha_inicio}'     => isset($renta->created_at) ? \Carbon\Carbon::parse($renta->created_at)->format('d/m/Y') : date('d/m/Y'),
    ];

    foreach ($reemplazos as $tag => $val) {
        $textoPagare = str_replace($tag, $val, $textoPagare);
    }
@endphp

<!-- CABECERA SUCURSAL DINÁMICA -->
<table class="header-table">
    <tr>
        <td style="width: 60%; vertical-align: top;">
            @if($sucLogoPath && file_exists(public_path('storage/' . $sucLogoPath)))
                <img src="{{ public_path('storage/' . $sucLogoPath) }}" class="logo-img" alt="Logo Sucursal"><br>
            @endif
            <h1 class="brand-title">{{ $empresaNombreGlobal }}</h1>
            <div class="branch-badge">SUCURSAL: <strong>{{ strtoupper($sucNombre) }}</strong></div>
            <div class="branch-details">
                <strong>RFC:</strong> {{ $sucRfc }}<br>
                <strong>Dirección:</strong> {{ $sucDireccion }}<br>
                <strong>Contacto:</strong> {{ $sucTelefono }}
            </div>
        </td>
        <td style="width: 40%; text-align: right; vertical-align: top;">
            <div style="font-size: 11pt; font-weight: bold; color: #0f172a;">TÍTULO DE CRÉDITO</div>
            <div style="font-size: 10pt; font-weight: bold; color: #0284c7; margin-top: 4px;" class="font-mono">PAGARÉ No. {{ $renta->folio }}</div>
            <div style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                Fecha Emisión: {{ \Carbon\Carbon::parse($renta->created_at ?? now())->format('d/m/Y') }}
            </div>
        </td>
    </tr>
</table>

<div class="doc-title">{{ $pPagare->titulo ?? 'PAGARÉ DE GARANTÍA' }}</div>

<!-- CUADRO DE RESUMEN DE IMPORTE -->
<div class="amount-box">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 35%; border: none;">
                <strong>BUENO POR:</strong> <span class="font-mono" style="font-size: 11pt; font-weight: bold; color: #0284c7;">${{ number_format($montoNetoVal, 2) }}</span>
            </td>
            <td style="width: 65%; border: none; text-align: right;">
                <strong>VENCIMIENTO:</strong> <span class="font-mono" style="font-weight: bold;">{{ $fFinVal }}</span>
            </td>
        </tr>
        <tr>
            <td colspan="2" style="border: none; padding-top: 6px;">
                <strong>IMPORTE EN LETRA:</strong> {{ $montoLetrasStr }}
            </td>
        </tr>
    </table>
</div>

<!-- TEXTO EDITABLE DEL PAGARÉ DESDE CONFIGURACIÓN -->
<div class="legal-box">
    {!! nl2br(e($textoPagare)) !!}
</div>

<!-- DATOS DEL ACEPTANTE -->
<div class="section-header">Datos del Aceptante (Deudor)</div>
<table class="data-table">
    <tr>
        <td style="width: 15%; font-weight: bold;">NOMBRE:</td>
        <td style="width: 35%; font-weight: bold; color: #0284c7;">{{ $renta->cliente->nombre_completo ?? 'Cliente de Mostrador' }}</td>
        <td style="width: 15%; font-weight: bold;">DIRECCIÓN:</td>
        <td style="width: 35%;">{{ $renta->cliente->direccion ?? 'No especificada' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">COLONIA:</td>
        <td>{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
        <td style="font-weight: bold;">CIUDAD:</td>
        <td>{{ $renta->cliente->ciudad ?? 'Durango, Dgo.' }}</td>
    </tr>
    <tr>
        <td style="font-weight: bold;">TELÉFONO:</td>
        <td colspan="3">{{ $renta->cliente->telefono ?? 'S/T' }}</td>
    </tr>
</table>

<!-- FIRMAS PAGARÉ -->
<table class="signature-table">
    <tr>
        <td style="width: 45%; text-align: center; vertical-align: top;">
            <div class="signature-line"></div>
            <div class="signature-title">ACEPTAMOS</div>
            <div class="signature-sub">{{ $renta->cliente->nombre_completo ?? 'Juan Carrillo' }}</div>
        </td>
        <td style="width: 10%;"></td>
        <td style="width: 45%; text-align: center; vertical-align: top;">
            <div class="signature-line"></div>
            <div class="signature-title">ING. GODOFREDO VIRAMONTES MEDINA</div>
            <div class="signature-sub">Prestador del Servicio / Acreedor</div>
        </td>
    </tr>
</table>

<div class="footer">
    Este pagaré se expide en Durango, Dgo. el {{ \Carbon\Carbon::now()->format('d/m/Y') }}
</div>

</body>
</html>