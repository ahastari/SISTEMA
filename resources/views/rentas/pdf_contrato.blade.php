<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Contrato de Renta - {{ $renta->folio }}</title>
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
            font-size: 12pt;
            font-weight: 800;
            text-align: center;
            text-transform: uppercase;
            color: #0f172a;
            margin: 12px 0;
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
            margin-top: 10px;
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
            padding: 5px 7px;
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

        /* CAJA DE TEXTO EDITABLE */
        .legal-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 10px;
            font-size: 8.5pt;
            text-align: justify;
            line-height: 1.4;
            color: #1e293b;
            margin-bottom: 10px;
        }

        /* FIRMAS ESTRUCTURADAS */
        .signature-table {
            width: 100%;
            margin-top: 35px;
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
            font-size: 7pt;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
        .page-break { page-break-before: always; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .font-mono { font-family: 'Courier New', Courier, monospace; }
    </style>
</head>
<body>

@php
    // 1. DATO CORPORATIVO PRINCIPAL (Nombre de la Empresa Global)
    $empresaNombreGlobal = \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'ANDAMIOS Y MADERA VIRAMONTES');

    // 2. OBTENCIÓN DE DATOS DE LA SUCURSAL ASIGNADA
    $sucursal = $renta->sucursal ?? null;
    
    $sucNombre    = $sucursal->nombre ?? 'MATRIZ GENERAL';
    $sucRfc       = $sucursal->rfc ?? \App\Helpers\ContentHelper::getCompanyData('empresa_rfc', 'VIMM9103023K7');
    $sucDireccion = $sucursal->direccion ?? \App\Helpers\ContentHelper::getCompanyData('empresa_direccion', 'AVE. DEL CIPRÉS #314 COL. MASIE');
    $sucTelefono  = $sucursal->telefono ?? \App\Helpers\ContentHelper::getCompanyData('empresa_telefono', '618 455 36 71');
    $sucLogoPath  = $sucursal->logo ?? \App\Helpers\ContentHelper::getCompanyData('empresa_logo');

    // 3. PARSEO DE PLANTILLAS DE CONTRATO Y PAGARÉ
    $pContrato = \App\Models\PlantillaDocumento::where('tipo', 'contrato_renta')->first() 
              ?? \App\Models\PlantillaDocumento::where('tipo', 'contrato')->first();

    $textoContrato = $pContrato ? $pContrato->contenido : "CONTRATO DE PRESTACIÓN DE SERVICIOS DE RENTA QUE CELEBRARÁN POR UNA PARTE EL PRESTADOR DE SERVICIOS {empresa} Y POR OTRA PARTE EL USUARIO DENOMINADO CLIENTE {cliente}.\n\nCLÁUSULAS:\n1.- El prestador de servicios se compromete a entregar en perfectas condiciones de trabajo el equipo.\n2.- El cliente tiene la obligación de verificar el buen estado en que recibe el equipo y entregarlo de igual forma.\n3.- Las piezas faltantes o averiadas se cobrarán en efectivo, no siendo sustituidas por otras, pues esto es indispensable para la uniformidad y el buen estado del equipo.\n4.- En la renta del equipo NO HAY CRÉDITO por lo que al devolver el equipo se deberá liquidar la renta.\n5.- El cliente está obligado a dejar un depósito por la cantidad de \${deposito} en DOCUMENTO que garantiza la devolución del equipo en buen estado.\n6.- El prestador de servicios se compromete a no hacer uso de este depósito, salvo si el cliente llegara a hacer mal uso del equipo, se niegue a regresar el equipo o se niegue a pagar la renta del mismo.\n7.- En caso de que el equipo se rente por periodos mayores a 30 días se cobrará cada 15 días.\n8.- Se cobrará el día de salida y el día de entrada.";

    $pPagare = \App\Models\PlantillaDocumento::where('tipo', 'pagare_renta')->first() 
            ?? \App\Models\PlantillaDocumento::where('tipo', 'pagare')->first();

    $textoPagare = $pPagare ? $pPagare->contenido : "DEBO (EMOS) Y PAGARÉ (EMOS) INCONDICIONALMENTE POR ESTE PAGARÉ A LA ORDEN DE {empresa} EN DURANGO, DGO. EL DÍA {fecha_fin} LA CANTIDAD DE \${monto_total} ({monto_total_letra}) VALOR RECIBIDO A MI (NUESTRA) ENTERA SATISFACCIÓN, EN CASO DE DEMORA PARCIALMENTE INSOLUTO SIN QUE POR ELLO SE CONSIDERE PRORROGADO EL PLAZO FIJADO.";

    // 4. REEMPLAZO AUTOMÁTICO DE ETIQUETAS
    $montoTotalVal = (float)($renta->total ?? 0);
    $depositoVal   = (float)($renta->deposito_garantia ?? ($renta->deposito ?? 0));
    $montoNetoVal  = max(0, $montoTotalVal - $depositoVal);

    $fFinVal = isset($renta->fecha_fin) ? \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') : (isset($renta->fecha_devolucion_estimada) ? \Carbon\Carbon::parse($renta->fecha_devolucion_estimada)->format('d/m/Y') : date('d/m/Y'));

    $reemplazos = [
        '{empresa}'          => $empresaNombreGlobal,
        '{cliente}'          => $renta->cliente->nombre_completo ?? 'PÚBLICO GENERAL',
        '{folio}'            => $renta->folio ?? 'N/A',
        '{deposito}'         => number_format($depositoVal, 2),
        '{monto_total}'      => number_format($montoTotalVal, 2),
        '{monto_neto}'       => number_format($montoNetoVal, 2),
        '{fecha_fin}'        => $fFinVal,
        '{fecha_inicio}'     => isset($renta->created_at) ? \Carbon\Carbon::parse($renta->created_at)->format('d/m/Y') : date('d/m/Y'),
    ];

    foreach ($reemplazos as $tag => $val) {
        $textoContrato = str_replace($tag, $val, $textoContrato);
        $textoPagare   = str_replace($tag, $val, $textoPagare);
    }
@endphp

<!-- ============================================ -->
<!-- PRIMERA PÁGINA: CONTRATO DE PRESTACIÓN DE SERVICIOS -->
<!-- ============================================ -->
<div>
    <!-- CABECERA SUCURSAL DINÁMICA -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                @if($sucLogoPath && file_exists(public_path('storage/' . $sucLogoPath)))
                    <img src="{{ public_path('storage/' . $sucLogoPath) }}" class="logo-img" alt="Logo Sucursal"><br>
                @endif
                <h1 class="brand-title">{{ $empresaNombreGlobal }}</h1>
                <div class="branch-badge">SUCURSAL: <strong>{{ strtoupper($sucursal->nombre ?? 'MATRIZ GENERAL') }}</strong></div>
                <div class="branch-details">
                    <strong>RFC:</strong> {{ $sucRfc }}<br>
                    <strong>Dirección:</strong> {{ $sucDireccion }}<br>
                    <strong>Contacto:</strong> {{ $sucTelefono }}
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div style="font-size: 11pt; font-weight: bold; color: #0f172a;">CONTRATO DE RENTA</div>
                <div style="font-size: 10pt; font-weight: bold; color: #0284c7; margin-top: 4px;" class="font-mono">FOLIO: {{ $renta->folio }}</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                    Fecha Emisión: {{ \Carbon\Carbon::parse($renta->created_at ?? now())->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <div class="doc-title">{{ $pContrato->titulo ?? 'CONTRATO DE PRESTACIÓN DE SERVICIOS DE RENTA' }}</div>

    <!-- CLÁUSULAS EDITABLES -->
    <div class="section-header">Cláusulas</div>
    <div class="legal-box">
        {!! nl2br(e($textoContrato)) !!}
    </div>

    <!-- DATOS DEL CLIENTE -->
    <div class="section-header">Datos del Cliente</div>
    <table class="data-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">NOMBRE:</td>
            <td colspan="3" style="font-weight: bold; color: #0284c7;">{{ $renta->cliente->nombre_completo ?? 'Cliente de Mostrador' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">DIRECCIÓN:</td>
            <td colspan="3">{{ $renta->cliente->direccion ?? 'No especificada' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold; width: 15%;">COLONIA:</td>
            <td style="width: 35%;">{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
            <td style="font-weight: bold; width: 15%;">CIUDAD:</td>
            <td style="width: 35%;">{{ $renta->cliente->ciudad ?? 'Durango, Dgo.' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">TELÉFONO:</td>
            <td>{{ $renta->cliente->telefono ?? 'S/T' }}</td>
            <td style="font-weight: bold;">IDENTIFICACIÓN:</td>
            <td>{{ $renta->cliente->ine_numero ?? 'INE/OFICIAL' }}</td>
        </tr>
    </table>

    <!-- DATOS DE LA OBRA -->
    @if($renta->obra_id && $renta->obra)
    <div class="section-header">Ubicación de la Obra</div>
    <table class="data-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">OBRA:</td>
            <td colspan="3">{{ $renta->obra->nombre }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">DIRECCIÓN:</td>
            <td colspan="3">{{ $renta->obra->direccion }}</td>
        </tr>
    </table>
    @endif

    <!-- EQUIPO RENTADO -->
    <div class="section-header">Detalle de Equipos y Mercancías en Renta</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%; text-align: center;">CANT.</th>
                <th>DESCRIPCIÓN DEL EQUIPO</th>
                <th style="width: 18%; text-align: right;">PRECIO / DÍA</th>
                <th style="width: 12%; text-align: center;">DÍAS</th>
                <th style="width: 20%; text-align: right;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($renta->detalles as $detalle)
            <tr>
                <td style="text-align: center;" class="font-mono">{{ $detalle->cantidad }}</td>
                <td><strong>{{ $detalle->equipo->nombre ?? ($detalle->concepto_especial ?? 'Equipo en Renta') }}</strong></td>
                <td style="text-align: right;" class="font-mono">${{ number_format($detalle->precio_dia ?? $detalle->precio_unitario, 2) }}</td>
                <td style="text-align: center;" class="font-mono">{{ $detalle->dias ?? 1 }}</td>
                <td style="text-align: right;" class="font-mono">${{ number_format($detalle->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="text-right bold">TOTAL RENTA:</td>
                <td class="text-right bold font-mono" style="color: #0284c7;">${{ number_format($montoTotalVal, 2) }}</td>
            </tr>
            @if($depositoVal > 0)
            <tr>
                <td colspan="4" class="text-right bold">DEPÓSITO DE GARANTÍA:</td>
                <td class="text-right bold font-mono" style="color: #d97706;">${{ number_format($depositoVal, 2) }}</td>
            </tr>
            @endif
        </tfoot>
    </table>

    <!-- FIRMAS CONTRATO -->
    <table class="signature-table">
        <tr>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <div class="signature-title">{{ $renta->cliente->nombre_completo ?? 'Juan Carrillo' }}</div>
                <div class="signature-sub">Nombre y Firma del Cliente (Aceptante)</div>
            </td>
            <td style="width: 10%;"></td>
            <td style="width: 45%; text-align: center; vertical-align: top;">
                <div class="signature-line"></div>
                <div class="signature-title">ING. GODOFREDO VIRAMONTES MEDINA</div>
                <div class="signature-sub">Prestador del Servicio / Representante Legal</div>
            </td>
        </tr>
    </table>

    <div class="footer">
        Documento oficial de arrendamiento expedido en Durango, Dgo. el {{ \Carbon\Carbon::parse($renta->created_at ?? now())->format('d/m/Y H:i:s') }}
    </div>
</div>

<!-- ============================================ -->
<!-- SEGUNDA PÁGINA: PAGARÉ FORMATO EJECUTIVO -->
<!-- ============================================ -->
<div class="page-break">

    <!-- CABECERA PAGARÉ CON SUCURSAL -->
    <table class="header-table">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <h1 class="brand-title">{{ $sucNombre }}</h1>
                <div class="branch-badge">SUCURSAL: <strong>{{ strtoupper($sucursal->nombre ?? 'MATRIZ GENERAL') }}</strong></div>
                <div class="branch-details">
                    <strong>RFC:</strong> {{ $sucRfc }} | <strong>Tel:</strong> {{ $sucTelefono }}
                </div>
            </td>
            <td style="width: 40%; text-align: right; vertical-align: top;">
                <div style="font-size: 11pt; font-weight: bold; color: #0f172a;">TITULO DE CRÉDITO - PAGARÉ</div>
                <div style="font-size: 10pt; font-weight: bold; color: #0284c7; margin-top: 4px;" class="font-mono">PAGARÉ No. {{ $renta->folio }}</div>
            </td>
        </tr>
    </table>

    <div class="doc-title" style="margin-top: 20px;">{{ $pPagare->titulo ?? 'PAGARÉ' }}</div>

    <!-- CUERPO DEL PAGARÉ -->
    <div class="legal-box" style="border: 2px solid #0f172a; padding: 15px; margin-top: 15px;">
        {!! nl2br(e($textoPagare)) !!}
    </div>

    <!-- DATOS DEL ACEPTANTE EN PAGARÉ -->
    <div class="section-header">Datos del Aceptante (Deudor)</div>
    <table class="data-table">
        <tr>
            <td style="width: 15%; font-weight: bold;">NOMBRE:</td>
            <td style="width: 35%; font-weight: bold;">{{ $renta->cliente->nombre_completo ?? 'Juan Carrillo' }}</td>
            <td style="width: 15%; font-weight: bold;">DIRECCIÓN:</td>
            <td style="width: 35%;">{{ $renta->cliente->direccion ?? 'DIAMANTE 118' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">COLONIA:</td>
            <td>{{ $renta->cliente->colonia ?? 'No especificada' }}</td>
            <td style="font-weight: bold;">CIUDAD:</td>
            <td>{{ $renta->cliente->ciudad ?? 'Durango, Dgo.' }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">TELÉFONO:</td>
            <td colspan="3">{{ $renta->cliente->telefono ?? '6181461516' }}</td>
        </tr>
    </table>

    <!-- FIRMAS PAGARÉ -->
    <table class="signature-table" style="margin-top: 50px;">
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
        Este pagaré se expide en Durango, Dgo. a {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>

</div>

</body>
</html>