<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Renta - {{ $renta->folio }}</title>
    <style>
        @page { margin: 0.8cm 1.5cm; size: letter portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; color: #000; line-height: 1.3; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }

        /* === MARCOS INDEPENDIENTES === */
        .marco-seccion {
            border: 1.5px solid #000;
            padding: 4px 8px;
            margin-bottom: 5px;
            border-radius: 4px;
            page-break-inside: avoid;
        }

        .header-title { font-size: 16px; font-weight: bold; margin: 0; letter-spacing: 1px; }
        .header-sub { font-size: 11px; font-weight: bold; margin: 2px 0 3px 0; }
        .header-info { font-size: 8px; margin: 1px 0; }

        .clausulas-container { text-align: justify; }
        .clausulas-title { font-weight: bold; font-size: 12px; margin-bottom: 3px; text-align: center; }

        .data-title { font-weight: bold; font-size: 11px; margin-bottom: 6px; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 1px; }
        .data-row { margin-bottom: 3px; font-size: 10px; }
        .data-label { display: inline-block; width: 95px; font-weight: bold; }
        .data-value { border-bottom: 1px solid #000; display: inline-block; width: calc(100% - 100px); }

        /* ================= PAGARÉ TIPO TALONARIO (BLANCO Y NEGRO) ================= */
        .pagare-wrapper {
            border: 2px solid #000;
            border-radius: 6px;
            padding: 2px;
            margin-bottom: 0;
            page-break-inside: avoid;
        }
        .pagare-inner {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 6px 10px;
        }
        .box-title {
            border: 1.5px solid #000;
            border-radius: 5px;
            padding: 2px 10px;
            font-size: 15px;
            font-weight: bold;
            font-style: italic;
            display: inline-block;
            color: #000;
        }
        .box-input {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 3px 6px;
            color: #000;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            display: inline-block;
        }
        .box-full {
            border: 1px solid #000;
            border-radius: 4px;
            padding: 4px;
            color: #000;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin-top: 2px;
        }
        .linea {
            border-bottom: 1px solid #000;
            color: #000;
            font-weight: bold;
            text-align: center;
            vertical-align: bottom;
        }
        .linea-left {
            border-bottom: 1px solid #000;
            color: #000;
            font-weight: bold;
            text-align: left;
            vertical-align: bottom;
            padding-left: 5px;
        }
        .sub-label {
            font-size: 8px;
            font-style: italic;
            color: #000;
            text-align: center;
            vertical-align: top;
        }
        .box-deudor {
            border: 1px solid #000;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 9px;
        }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

@php
    $empresaNombreGlobal = \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'INDUSTRIAS VIRAMONTES');
    $dueno = \App\Helpers\ContentHelper::getCompanyData('empresa_dueno', 'GODOFREDO VIRAMONTES MEDINA');
    $sucursal = $renta->sucursal ?? null;
    $sucRfc = $sucursal->rfc ?? \App\Helpers\ContentHelper::getCompanyData('empresa_rfc', 'VIMG530129544');
    $sucDireccion = $sucursal->direccion ?? \App\Helpers\ContentHelper::getCompanyData('empresa_direccion', 'AV. DEL CIPRES 314 COL. MASIE, DURANGO, DGO, C.P. 34217');
    // Teléfono y celular de la sucursal; si no tiene ninguno, se usa el de la empresa
    $sucTel = $sucursal->telefono ?? null;
    $sucCel = $sucursal->celular ?? null;
    $sucTelefono = ($sucTel || $sucCel)
        ? implode(' / ', array_filter([$sucTel ? 'TEL. ' . $sucTel : null, $sucCel ? 'CEL. ' . $sucCel : null]))
        : \App\Helpers\ContentHelper::getCompanyData('empresa_telefono', 'TEL. 455-36-71 CEL. 618-159-70-19');
    $depositoVal = (float)($renta->deposito_garantia ?? ($renta->deposito ?? 0));
    $montoTotalVal = (float)($renta->total ?? 0);

    // =========================================================
    // MONTOS: el pagaré ampara el VALOR DE VENTA de los equipos.
    // Si por alguna razón no llega desde el controlador, se calcula aquí.
    // =========================================================
    $montoVentaTotal = $montoVentaTotal ?? $renta->valor_venta;
    $montoVentaLetras = $montoVentaLetras ?? '';
    $montoTotalLetras = $montoTotalLetras ?? '';

    $montoPagare = $montoVentaTotal;
    $montoPagareLetras = $montoVentaLetras;

    // Fechas y variables
    $fechaExpedicion = isset($fechaExpedicion) ? $fechaExpedicion : ($renta->created_at ? \Carbon\Carbon::parse($renta->created_at) : now());
    $fechaPago = isset($fechaPago) ? $fechaPago : ($renta->fecha_fin ? \Carbon\Carbon::parse($renta->fecha_fin) : now());
    $fFinVal = $fechaPago->format('d/m/Y');
    $fInicioVal = $renta->fecha_inicio ? $renta->fecha_inicio->format('d/m/Y') : $fechaExpedicion->format('d/m/Y');

    // Procesamiento de plantilla del contrato
    $pContrato = \App\Models\PlantillaDocumento::where('tipo', 'contrato_renta')->first() ?? \App\Models\PlantillaDocumento::where('tipo', 'contrato')->first();
    $textoContrato = $pContrato ? $pContrato->contenido : "Cláusulas no definidas...";

    $reemplazos = [
        '{empresa}'            => $empresaNombreGlobal,
        '{dueno_empresa}'      => $dueno,
        '{cliente}'            => $renta->cliente->nombre_completo ?? '',
        '{folio}'              => $renta->folio ?? '',
        '{deposito}'           => number_format($depositoVal, 2),
        '{monto_total}'        => number_format($montoTotalVal, 2),
        '{monto_total_letras}' => $montoTotalLetras,
        '{monto_venta}'        => number_format($montoVentaTotal, 2),
        '{monto_venta_letras}' => $montoVentaLetras,
        '{fecha_inicio}'       => $fInicioVal,
        '{fecha_fin}'          => $fFinVal,
    ];

    foreach ($reemplazos as $tag => $val) {
        $textoContrato = str_replace($tag, $val, $textoContrato);
    }

    $ciudadCliente = $renta->cliente->ciudad ?? 'Durango, Dgo.';
    $direccionCliente = $renta->cliente->direccion ?? '';
    if (!empty($renta->cliente->colonia)) {
        $direccionCliente .= ', ' . $renta->cliente->colonia;
    }

    // Logo Base64
    $logoBase64 = null;
    $empresaLogo = \App\Helpers\ContentHelper::getCompanyData('empresa_logo');
    $rutaLogo = ($sucursal && $sucursal->logo && file_exists(public_path('storage/' . $sucursal->logo)))
        ? public_path('storage/' . $sucursal->logo)
        : ( ($empresaLogo && file_exists(public_path('storage/' . $empresaLogo))) ? public_path('storage/' . $empresaLogo) : null );

    if ($rutaLogo) {
        $tipo = pathinfo($rutaLogo, PATHINFO_EXTENSION);
        $logoBase64 = 'data:image/' . $tipo . ';base64,' . base64_encode(file_get_contents($rutaLogo));
    }
@endphp

<!-- ========================================== -->
<!-- HOJA 1: CONTRATO Y PAGARÉ -->
<!-- ========================================== -->

<!-- MARCO 1: ENCABEZADO -->
<div class="marco-seccion">
    <table width="100%">
        <tr>
            <td width="25%" style="vertical-align: middle; text-align: left;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 110px; max-height: 55px; object-fit: contain;">
                @endif
            </td>
            <td width="50%" class="text-center" style="vertical-align: middle;">
                <h1 class="header-title">{{ $empresaNombreGlobal }}</h1>
                <h2 class="header-sub">{{ mb_strtoupper($dueno, 'UTF-8') }}</h2>
                <p class="header-info">R.F.C. {{ $sucRfc }}</p>
                <p class="header-info">{{ $sucDireccion }}</p>
                <p class="header-info fw-bold">{{ $sucTelefono }}</p>
            </td>
            <td width="25%" style="vertical-align: middle; text-align: right;">
                <div style="font-weight: bold; font-size: 13px; color: #dc3545;">
                    Folio: {{ $renta->folio }}
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- MARCO 2: CLÁUSULAS -->
<div class="marco-seccion">
    <div class="clausulas-container">
        <p style="margin-top: 0; margin-bottom: 3px; font-size: 10px;">Contrato de Prestación de servicios de Renta de {{ $empresaNombreGlobal }} que celebrarán por una parte el prestador de servicio y por otra, el usuario denominado CLIENTE</p>
        <div class="clausulas-title">CLAUSULAS</div>
        <div style="padding: 0 5px; font-size: 10px;">
            {!! nl2br(e($textoContrato)) !!}
        </div>
    </div>
</div>

<!-- MARCO 3: DATOS DEL CLIENTE Y OBRA -->
<table width="100%" style="margin-bottom: 5px;">
    <tr>
        <td width="49%">
            <div class="marco-seccion" style="margin-bottom: 0; height: 115px;">
                <div class="data-title">DATOS DEL CLIENTE</div>
                <div class="data-row"><span class="data-label">NOMBRE</span> <span class="data-value">{{ mb_strtoupper($renta->cliente->nombre_completo ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">DIRECCION</span> <span class="data-value">{{ mb_strtoupper($renta->cliente->direccion ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">COLONIA</span> <span class="data-value">{{ mb_strtoupper($renta->cliente->colonia ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">CIUDAD</span> <span class="data-value">{{ mb_strtoupper($renta->cliente->ciudad ?? 'DURANGO, DGO.', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">TELEFONO</span> <span class="data-value">{{ $renta->cliente->telefono ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">IDENTIFICACION</span> <span class="data-value">{{ mb_strtoupper($renta->cliente->ine_numero ?? 'INE', 'UTF-8') }}</span></div>
            </div>
        </td>
        <td width="2%"></td>
        <td width="49%">
            <div class="marco-seccion" style="margin-bottom: 0; height: 115px;">
                <div class="data-title">DATOS DE LA OBRA</div>
                <div class="data-row"><span class="data-label">NOMBRE</span> <span class="data-value">{{ mb_strtoupper($renta->obra->nombre ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">DIRECCION</span> <span class="data-value">{{ mb_strtoupper($renta->obra->direccion ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">COLONIA</span> <span class="data-value">{{ mb_strtoupper($renta->obra->colonia ?? '', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">CIUDAD</span> <span class="data-value">{{ mb_strtoupper($renta->obra->ciudad ?? 'DURANGO, DGO.', 'UTF-8') }}</span></div>
                <div class="data-row"><span class="data-label">TELEFONO</span> <span class="data-value">{{ $renta->obra->contacto_obra ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">&nbsp;</span> <span class="data-value" style="border-bottom: none;">&nbsp;</span></div>
            </div>
        </td>
    </tr>
</table>

<!-- MARCO 4: EQUIPO RENTADO -->
<div class="marco-seccion">
    <table width="100%">
        <tr>
            <td width="16%" style="font-weight: bold; font-size: 10px; vertical-align: top;">EQUIPO RENTADO:</td>
            <td width="84%" style="font-size: 10px; text-align: justify; line-height: 1.4;">
                @php $equiposArray = []; @endphp
                @foreach($renta->detalles as $detalle)
                    @php
                        $equiposArray[] = '<strong>' . $detalle->cantidad . '</strong> ' . e(mb_strtoupper($detalle->equipo->nombre ?? ($detalle->concepto_especial ?? 'EQUIPO'), 'UTF-8'));
                    @endphp
                @endforeach
                {!! implode(' &nbsp; | &nbsp; ', $equiposArray) !!}
            </td>
        </tr>
    </table>
</div>

<!-- ================= PAGARÉ DISEÑO TALONARIO (BLANCO Y NEGRO) ================= -->
<!-- El monto ($ y en letras) corresponde al VALOR DE VENTA de los equipos -->
<div class="pagare-wrapper">
    <div class="pagare-inner">

        <!-- FILA 1: Título, No. y Bueno por -->
        <table width="100%">
            <tr>
                <td width="30%" style="vertical-align: middle;">
                    <div class="box-title">PAGARÉ</div>
                </td>
                <td width="30%" align="center" style="font-weight: bold; vertical-align: middle; font-size: 11px;">
                    No. <div class="box-input" style="width: 50px;">1/1</div>
                </td>
                <td width="40%" align="right" style="font-weight: bold; vertical-align: middle; font-size: 11px;">
                    BUENO POR $ <div class="box-input" style="width: 90px;">{{ number_format($montoPagare, 2) }}</div>
                </td>
            </tr>
        </table>

        <!-- FILA 2: Lugar y Fecha de expedición -->
        <table style="margin-top: 4px;" width="100%">
            <tr>
                <td width="40%"></td>
                <td width="5%" align="right" style="padding-right: 5px;">En</td>
                <td width="25%" class="linea">{{ mb_strtoupper($ciudadCliente, 'UTF-8') }}</td>
                <td width="5%" align="center">a</td>
                <td width="5%" class="linea">{{ $fechaExpedicion->format('d') }}</td>
                <td width="5%" align="center">de</td>
                <td width="10%" class="linea">{{ mb_strtoupper($fechaExpedicion->locale('es')->translatedFormat('F'), 'UTF-8') }}</td>
                <td width="5%" align="center">de</td>
                <td width="5%" class="linea">{{ $fechaExpedicion->format('Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td colspan="7" class="sub-label">Lugar y fecha de expedición</td>
            </tr>
        </table>

        <!-- FILA 3: Orden de -->
        <table style="margin-top: 3px;" width="100%">
            <tr>
                <td width="50%" style="white-space: nowrap; font-size: 10px;">Debo(mos) y pagaré(mos) incondicionalmente por este Pagaré a la orden de</td>
                <td width="50%" class="linea">{{ mb_strtoupper($dueno, 'UTF-8') }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="sub-label">Nombre de la persona a quien ha de pagarse</td>
            </tr>
        </table>

        <!-- FILA 4: Lugar y fecha de pago -->
        <table style="margin-top: 2px;" width="100%">
            <tr>
                <td width="30%" class="linea">&nbsp;</td>
                <td width="5%" align="center">en</td>
                <td width="35%" class="linea">{{ mb_strtoupper($ciudadCliente, 'UTF-8') }}</td>
                <td width="5%" align="center">el</td>
                <td width="25%" class="linea">{{ $fechaPago->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="sub-label" align="center">Lugar de pago</td>
                <td></td>
                <td class="sub-label" align="center">Fecha de pago</td>
            </tr>
        </table>

        <!-- FILA 5: Cantidad en Letras (valor de venta) -->
        <div style="margin-top: 4px; margin-bottom: 2px; font-size: 10px;">La cantidad de:</div>
        <div class="box-full">
            {{ mb_strtoupper($montoPagareLetras, 'UTF-8') }}
        </div>

        <!-- FILA 6: Clausula Legal -->
        <div style="margin-top: 4px; text-align: justify; line-height: 1.2; font-size: 9px;">
            Valor recibido a mi (nuestra) entera satisfacción. Este pagaré forma parte de una serie numerada del 1 al __ y todos están sujetos a la condición de que, al no pagarse cualquiera de ellos a su vencimiento, serán exigibles todos los que le sigan en número, además de los ya vencidos, desde la fecha de vencimiento de este documento hasta el día de su liquidación, causará intereses moratorios al tipo de 5% mensual, pagadero en esta ciudad juntamente con el principal.
        </div>

        <!-- FILA 7: Deudor y Firma -->
        <table style="margin-top: 6px;" width="100%">
            <tr>
                <td width="60%" valign="top">
                    <div class="box-deudor">
                        <div style="text-align: center; margin-bottom: 4px; font-weight: bold; font-size: 10px;">Datos del deudor</div>
                        <table style="margin-bottom: 2px;" width="100%">
                            <tr>
                                <td width="15%" style="padding-bottom:0;">Nombre:</td>
                                <td width="85%" class="linea-left" style="padding-bottom:0;">{{ mb_strtoupper($renta->cliente->nombre_completo ?? '', 'UTF-8') }}</td>
                            </tr>
                        </table>
                        <table style="margin-bottom: 2px;" width="100%">
                            <tr>
                                <td width="15%" style="padding-bottom:0;">Dirección:</td>
                                <td width="55%" class="linea-left" style="padding-bottom:0;">{{ mb_strtoupper($direccionCliente ?? '', 'UTF-8') }}</td>
                                <td width="10%" align="right" style="padding-bottom:0; padding-right:3px;">Tel:</td>
                                <td width="20%" class="linea-left" style="padding-bottom:0;">{{ $renta->cliente->telefono ?? '' }}</td>
                            </tr>
                        </table>
                        <table width="100%">
                            <tr>
                                <td width="15%" style="padding-bottom:0;">Población:</td>
                                <td width="85%" class="linea-left" style="padding-bottom:0;">{{ mb_strtoupper($ciudadCliente, 'UTF-8') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="5%"></td>
                <td width="35%" valign="bottom" align="center">
                    <div style="margin-bottom: 20px; font-weight: bold; font-size: 10px;">Acepto(amos) y Pagaré(mos)</div>
                    <div style="border-top: 1px solid #000; width: 100%;"></div>
                    <div class="sub-label" style="margin-top: 2px;">Firma(s)</div>
                </td>
            </tr>
        </table>

    </div>
</div>

<!-- ========================================== -->
<!-- HOJA 2: AVISO DE RECOLECCIÓN -->
<!-- ========================================== -->
<div class="page-break"></div>

<!-- MARCO ENCABEZADO HOJA 2 -->
<div class="marco-seccion">
    <table width="100%">
        <tr>
            <td width="25%" style="vertical-align: middle; text-align: left;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" style="max-width: 130px; max-height: 70px; object-fit: contain;">
                @endif
            </td>
            <td width="50%" class="text-center" style="vertical-align: middle;">
                <h1 class="header-title">{{ $empresaNombreGlobal }}</h1>
                <h2 class="header-sub">{{ mb_strtoupper($dueno, 'UTF-8') }}</h2>
                <p class="header-info">R.F.C. {{ $sucRfc }}</p>
                <p class="header-info">{{ $sucDireccion }}</p>
                <p class="header-info fw-bold">{{ $sucTelefono }}</p>
            </td>
            <td width="25%" style="vertical-align: middle; text-align: right;">
                <div style="font-weight: bold; font-size: 14px; color: #dc3545;">
                    Folio: {{ $renta->folio }}
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- MARCO AVISO RECOLECCIÓN -->
<div class="marco-seccion" style="padding: 20px; min-height: 300px;">
    <div style="font-weight: bold; margin-bottom: 15px; font-size: 14px;">PARA: Quien corresponda</div>

    <p style="text-align: justify; font-size: 12px; line-height: 1.5;">
        Quedo enterado que al momento de terminar de usar el equipo en renta avisaré a "{{ $empresaNombreGlobal }}" pasar por este mismo (andamios, revolvedora, cimbra, vibrador de concreto).
    </p>

    <p style="text-align: justify; font-weight: bold; font-size: 12px; line-height: 1.5;">
        (Si no se avisa seguirá corriendo la renta en cuestión y se cobrará los días extras que se generen hasta que se dé aviso a "{{ $empresaNombreGlobal }}")
    </p>

    <div style="margin-top: 80px; text-align: center;">
        <div style="border-top: 1px solid #000; width: 250px; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 12px;">Firma del Cliente</div>
    </div>

    <p style="margin-top: 50px; text-align: center; font-size: 11px; line-height: 1.4;">
        Sin otro particular a que hacer referencia y esperando vernos favorecidos con sus apreciables ordenes nos es grato quedar de usted como sus amigos y S.S.<br><br>
        <strong>Gracias por su preferencia</strong>
    </p>
</div>

</body>
</html>