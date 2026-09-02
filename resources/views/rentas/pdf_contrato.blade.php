<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Renta - {{ $renta->folio }}</title>
    <style>
        @page { margin: 1.5cm; size: letter portrait; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; line-height: 1.4; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        /* Encabezado */
        .header-title { font-size: 20px; font-weight: bold; margin: 0; letter-spacing: 1px; }
        .header-sub { font-size: 14px; font-weight: bold; margin: 2px 0 5px 0; }
        .header-info { font-size: 10px; margin: 2px 0; }

        /* Cláusulas */
        .clausulas-container { margin-top: 20px; text-align: justify; }
        .clausulas-title { font-weight: bold; font-size: 12px; margin-bottom: 5px; text-align: center; }

        /* Secciones de Datos */
        .data-section { margin-top: 20px; }
        .data-title { font-weight: bold; font-size: 12px; margin-bottom: 8px; border-bottom: 1px solid #000; display: inline-block; padding-bottom: 2px; }
        .data-row { margin-bottom: 6px; font-size: 11px; }
        .data-label { display: inline-block; width: 100px; font-weight: bold; }
        .data-value { border-bottom: 1px solid #000; display: inline-block; width: calc(100% - 105px); }

        /* Aviso */
        .aviso-container { margin-top: 40px; border-top: 1px dashed #000; padding-top: 20px; }
    </style>
</head>
<body>

@php
    $empresaNombreGlobal = \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'INDUSTRIAS VIRAMONTES');
    $sucursal = $renta->sucursal ?? null;
    $sucRfc = $sucursal->rfc ?? \App\Helpers\ContentHelper::getCompanyData('empresa_rfc', 'VIMG530129544');
    $sucDireccion = $sucursal->direccion ?? \App\Helpers\ContentHelper::getCompanyData('empresa_direccion', 'AV. DEL CIPRES 314 COL. MASIE, DURANGO, DGO, C.P. 34217');
    $sucTelefono = $sucursal->telefono ?? \App\Helpers\ContentHelper::getCompanyData('empresa_telefono', 'TEL. 455-36-71 CEL. 618-159-70-19');
    $depositoVal = (float)($renta->deposito_garantia ?? ($renta->deposito ?? 0));
    $montoTotalVal = (float)($renta->total ?? 0);
    $fFinVal = isset($renta->fecha_fin) ? \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') : date('d/m/Y');

    // Procesamiento de la plantilla dinámica del contrato
    $pContrato = \App\Models\PlantillaDocumento::where('tipo', 'contrato_renta')->first() ?? \App\Models\PlantillaDocumento::where('tipo', 'contrato')->first();
    $textoContrato = $pContrato ? $pContrato->contenido : "Cláusulas no definidas...";

    $reemplazos = [
        '{empresa}' => $empresaNombreGlobal,
        '{cliente}' => $renta->cliente->nombre_completo ?? '',
        '{folio}' => $renta->folio ?? '',
        '{deposito}' => number_format($depositoVal, 2),
        '{monto_total}' => number_format($montoTotalVal, 2),
        '{fecha_fin}' => $fFinVal,
        '{fecha_inicio}' => isset($renta->created_at) ? \Carbon\Carbon::parse($renta->created_at)->format('d/m/Y') : date('d/m/Y'),
    ];

    foreach ($reemplazos as $tag => $val) {
        $textoContrato = str_replace($tag, $val, $textoContrato);
    }

    $logoBase64 = null;
    $empresaLogo = \App\Helpers\ContentHelper::getCompanyData('empresa_logo');
    $rutaLogo = null;

    // 1. Validar si la sucursal tiene logo propio
    if ($sucursal && $sucursal->logo && file_exists(public_path('storage/' . $sucursal->logo))) {
        $rutaLogo = public_path('storage/' . $sucursal->logo);
    } 
    // 2. Si no tiene, usar el logo global de la empresa
    elseif ($empresaLogo && file_exists(public_path('storage/' . $empresaLogo))) {
        $rutaLogo = public_path('storage/' . $empresaLogo);
    }

    // 3. Convertir a Base64 (Esto asegura que DomPDF siempre cargue la imagen sin fallar)
    if ($rutaLogo) {
        $tipo = pathinfo($rutaLogo, PATHINFO_EXTENSION);
        $data = file_get_contents($rutaLogo);
        $logoBase64 = 'data:image/' . $tipo . ';base64,' . base64_encode($data);
    }
@endphp

<!-- ENCABEZADO CON LOGO -->
<table width="100%" style="margin-bottom: 15px;">
    <tr>
        <!-- Columna Izquierda: LOGO -->
        <td width="25%" style="vertical-align: top; text-align: left;">
            @if($logoBase64)
                <img src="{{ $logoBase64 }}" style="max-width: 140px; max-height: 100px; object-fit: contain;">
            @endif
        </td>
        
        <!-- Columna Central: TEXTOS DE LA EMPRESA -->
        <td width="50%" class="text-center" style="vertical-align: top;">
            <h1 class="header-title">{{ $empresaNombreGlobal }}</h1>
            <h2 class="header-sub">{{ \App\Helpers\ContentHelper::getCompanyData('empresa_dueno', 'GODOFREDO VIRAMONTES MEDINA') }}</h2>
            <p class="header-info">R.F.C. {{ $sucRfc }}</p>
            <p class="header-info">{{ $sucDireccion }}</p>
            <p class="header-info fw-bold">{{ $sucTelefono }}</p>
        </td>
        
        <!-- Columna Derecha: FOLIO ROJO -->
        <td width="25%" style="vertical-align: top; text-align: right;">
            <div style="font-weight: bold; font-size: 14px; color: #dc3545;">
                Folio: {{ $renta->folio }}
            </div>
        </td>
    </tr>
</table>

<!-- CLÁUSULAS (Dinámicas desde la BD) -->
<div class="clausulas-container">
    <p>Contrato de Prestación de servicios de Renta de {{ $empresaNombreGlobal }} que celebrarán por una parte el prestador de servicio y por otra, el usuario denominado CLIENTE</p>
    <div class="clausulas-title">CLAUSULAS</div>
    <div style="padding: 0 10px;">
        {!! nl2br(e($textoContrato)) !!}
    </div>
</div>

<!-- DATOS DEL CLIENTE Y OBRA -->
<div class="data-section">
    <table width="100%">
        <tr>
            <td width="48%">
                <div class="data-title">DATOS DEL CLIENTE</div>
                <div class="data-row"><span class="data-label">NOMBRE</span> <span class="data-value">{{ $renta->cliente->nombre_completo ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">DIRECCION</span> <span class="data-value">{{ $renta->cliente->direccion ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">COLONIA</span> <span class="data-value">{{ $renta->cliente->colonia ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">CIUDAD</span> <span class="data-value">{{ $renta->cliente->ciudad ?? 'DURANGO, DGO.' }}</span></div>
                <div class="data-row"><span class="data-label">TELEFONO</span> <span class="data-value">{{ $renta->cliente->telefono ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">IDENTIFICACION</span> <span class="data-value">{{ $renta->cliente->ine_numero ?? 'INE' }}</span></div>
            </td>
            <td width="4%"></td>
            <td width="48%">
                <div class="data-title">DATOS DE LA OBRA</div>
                <div class="data-row"><span class="data-label">NOMBRE</span> <span class="data-value">{{ $renta->obra->nombre ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">DIRECCION</span> <span class="data-value">{{ $renta->obra->direccion ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">COLONIA</span> <span class="data-value">{{ $renta->obra->colonia ?? '' }}</span></div>
                <div class="data-row"><span class="data-label">CIUDAD</span> <span class="data-value">{{ $renta->obra->ciudad ?? 'DURANGO, DGO.' }}</span></div>
                <div class="data-row"><span class="data-label">TELEFONO</span> <span class="data-value">{{ $renta->obra->contacto_obra ?? '' }}</span></div>
            </td>
        </tr>
    </table>
</div>

<!-- EQUIPO RENTADO -->
<div class="data-section">
    <div class="data-title">EQUIPO RENTADO:</div>
    <div style="margin-left: 20px;">
        @foreach($renta->detalles as $detalle)
            <div style="margin-bottom: 3px;">
                <strong>{{ $detalle->cantidad }}</strong> {{ strtoupper($detalle->equipo->nombre ?? ($detalle->concepto_especial ?? 'EQUIPO')) }}
            </div>
        @endforeach
    </div>
</div>

<!-- AVISO DE RECOLECCIÓN (Texto Original) -->
<<div class="aviso-container" style="page-break-before: always; border-top: none;">
    <div style="font-weight: bold; margin-bottom: 10px;">PARA: Quien corresponda</div>
    <p style="text-align: justify;">
        Quedo enterado que al momento de terminar de usar el equipo en renta avisaré a "{{ $empresaNombreGlobal }}" pasar por este mismo (andamios, revolvedora, cimbra, vibrador de concreto).
    </p>
    <p style="text-align: justify; font-weight: bold;">
        (Si no se avisa seguirá corriendo la renta en cuestión y se cobrará los días extras que se generen hasta que se dé aviso a "{{ $empresaNombreGlobal }}")
    </p>
    
    <div style="margin-top: 50px; text-align: center;">
        <div style="border-top: 1px solid #000; width: 250px; margin: 0 auto; padding-top: 5px; font-weight: bold;">Firma del Cliente</div>
    </div>
    
    <p style="margin-top: 30px; text-align: center; font-size: 10px;">
        Sin otro particular a que hacer referencia y esperando vernos favorecidos con sus apreciables ordenes nos es grato quedar de usted como sus amigos y S.S.<br>
        <strong>Gracias por su preferencia</strong>
    </p>
</div>

</body>
</html>