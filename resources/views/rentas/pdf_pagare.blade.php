<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagaré - {{ $renta->folio }}</title>
    <style>
        @page { margin: 1cm; size: letter portrait; }
        body { font-family: Arial, sans-serif; color: #1b5e20; background: #ffffff; }
        
        /* Contenedor Principal (Marco grueso) */
        .pagare-wrapper {
            border: 5px solid #2e7d32;
            border-radius: 12px;
            padding: 10px;
            background-color: #e8f5e9; /* Fondo Verde Claro */
            margin-bottom: 20px;
        }
        
        /* Marco interior fino */
        .pagare-inner {
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 15px;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: bottom; }

        /* Títulos */
        .title-box {
            border: 2px solid #2e7d32;
            border-radius: 8px;
            padding: 5px 15px;
            font-size: 28px;
            font-weight: bold;
            font-style: italic;
            background: #ffffff;
            display: inline-block;
            box-shadow: 2px 2px 0px rgba(0,0,0,0.1);
        }

        .underline-input {
            border-bottom: 1px solid #2e7d32;
            text-align: center;
            font-weight: bold;
            display: inline-block;
            font-family: 'Courier New', Courier, monospace;
        }

        /* Cantidad Numérica */
        .amount-box {
            border: 2px solid #2e7d32;
            border-radius: 8px;
            background: #ffffff;
            padding: 5px 15px;
            font-size: 16px;
            font-weight: bold;
            display: inline-block;
            min-width: 140px;
            text-align: center;
            box-shadow: 2px 2px 0px rgba(0,0,0,0.1);
        }

        /* Cuerpo del Pagaré */
        .body-text {
            font-size: 13px;
            line-height: 1.8;
            text-align: justify;
            margin-top: 20px;
        }

        /* Cantidad en Letras */
        .amount-words {
            border: 2px solid #2e7d32;
            border-radius: 8px;
            background: #ffffff;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }

        /* Texto de intereses y moratorios */
        .legal-text {
            font-size: 10px;
            text-align: justify;
            margin-top: 15px;
            line-height: 1.5;
        }

        /* Caja de Datos del Deudor */
        .debtor-box {
            border: 1px solid #2e7d32;
            border-radius: 8px;
            padding: 10px;
            width: 95%;
            font-size: 12px;
            line-height: 1.8;
        }

        .signature-section {
            text-align: center;
            font-size: 12px;
        }

        .signature-line {
            border-bottom: 1px solid #2e7d32;
            width: 80%;
            margin: 40px auto 5px auto;
        }

        .footer-note {
            text-align: center;
            font-size: 9px;
            font-style: italic;
            margin-top: 10px;
        }
    </style>
</head>
<body>

@php
    $montoTotalVal = (float)($renta->total ?? 0);
    $depositoVal   = (float)($renta->deposito_garantia ?? ($renta->deposito ?? 0));
    $montoNetoVal  = max(0, $montoTotalVal - $depositoVal);

    $fFinVal = isset($renta->fecha_fin) ? \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') : date('d/m/Y');
    
    // Generar letra
    $formatter = new \NumberFormatter('es', \NumberFormatter::SPELLOUT);
    $entero = floor($montoNetoVal);
    $decimales = round(($montoNetoVal - $entero) * 100);
    $montoLetrasStr = strtoupper($formatter->format($entero)) . " PESOS " . str_pad($decimales, 2, '0', STR_PAD_LEFT) . "/100 M.N.";
@endphp

<div class="pagare-wrapper">
    <div class="pagare-inner">
        
        <!-- ENCABEZADO SUPERIOR -->
        <table>
            <tr>
                <td style="width: 30%;">
                    <div class="title-box">PAGARÉ</div>
                </td>
                <td style="width: 30%; text-align: center; font-size: 14px;">
                    <strong>No.</strong> <div class="underline-input" style="width: 100px;">{{ $renta->folio }}</div>
                </td>
                <td style="width: 40%; text-align: right; font-size: 16px;">
                    <strong>BUENO POR $</strong> <div class="amount-box">{{ number_format($montoNetoVal, 2) }}</div>
                </td>
            </tr>
        </table>

        <!-- FECHA Y LUGAR DE EXPEDICIÓN -->
        <table style="margin-top: 15px; font-size: 13px;">
            <tr>
                <td style="text-align: right;">
                    En <div class="underline-input" style="width: 160px;">{{ $renta->sucursal->nombre ?? 'Durango, Dgo.' }}</div> a 
                    <div class="underline-input" style="width: 40px;">{{ $renta->created_at->format('d') }}</div> de 
                    <div class="underline-input" style="width: 100px;">{{ $renta->created_at->translatedFormat('F') }}</div> de 
                    <div class="underline-input" style="width: 50px;">{{ $renta->created_at->format('Y') }}</div>
                </td>
            </tr>
            <tr>
                <td style="text-align: right; font-size: 9px; font-style: italic; padding-right: 60px;">
                    Lugar y fecha de expedición
                </td>
            </tr>
        </table>

        <!-- CUERPO PRINCIPAL -->
        <div class="body-text">
            Debo(mos) y pagaré(mos) incondicionalmente por este Pagaré a la orden de 
            <div class="underline-input" style="width: 350px;">{{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre') }}</div><br>
            <div style="font-size: 9px; font-style: italic; text-align: right; width: 85%; margin-top: -3px; margin-bottom: 5px;">Nombre de la persona a quien ha de pagarse</div>
            
            en <div class="underline-input" style="width: 300px;">esta ciudad o donde se me requiera</div> 
            el <div class="underline-input" style="width: 120px;">{{ $fFinVal }}</div>
            
            <table style="width: 100%; margin-top: -3px; margin-bottom: 10px;">
                <tr>
                    <td style="width: 50%; font-size: 9px; font-style: italic; text-align: center;">Lugar de pago</td>
                    <td style="width: 50%; font-size: 9px; font-style: italic; text-align: center;">Fecha de pago</td>
                </tr>
            </table>
            
            La cantidad de:
        </div>

        <div class="amount-words">
            ({{ $montoLetrasStr }})
        </div>

        <!-- CLÁUSULA LEGAL (LETRAS CHIQUITAS) -->
        <div class="legal-text">
            Valor Recibido a mi (nuestra) entera satisfacción. Este pagaré forma parte de una serie numerada del 1 al <div class="underline-input" style="width: 30px;">1</div> 
            y todos están sujetos a la condición de que, al no pagarse cualquiera de ellos a su vencimiento, serán exigibles todos los que le sigan en número, además de los ya vencidos, 
            desde la fecha de vencimiento de este documento hasta el día de su liquidación, causará intereses moratorios al tipo de <div class="underline-input" style="width: 40px;">10</div>% 
            mensual, pagadero en esta ciudad juntamente con el principal.
        </div>

        <!-- PIE DE FIRMAS Y DATOS -->
        <table style="width: 100%; margin-top: 20px;">
            <tr>
                <td style="width: 55%; vertical-align: top;">
                    <div class="debtor-box">
                        <div style="text-align: center; font-weight: bold; margin-bottom: 10px; font-size: 11px;">Datos del deudor</div>
                        <strong>Nombre:</strong> <div class="underline-input" style="width: 250px;">{{ $renta->cliente->nombre_completo ?? '' }}</div><br>
                        <strong>Dirección:</strong> <div class="underline-input" style="width: 235px;">{{ \Illuminate\Support\Str::limit($renta->cliente->direccion ?? '', 38) }}</div><br>
                        <strong>Población:</strong> <div class="underline-input" style="width: 120px;">{{ $renta->cliente->ciudad ?? '' }}</div> 
                        <strong>Tel:</strong> <div class="underline-input" style="width: 90px;">{{ $renta->cliente->telefono ?? '' }}</div>
                    </div>
                </td>
                <td style="width: 45%; vertical-align: bottom;">
                    <div class="signature-section">
                        <strong>Acepto(amos)</strong>
                        <div class="signature-line"></div>
                        Firma(s)
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            Escriba al reverso los datos personales y firma(s) del(os) aval(es).
        </div>

    </div>
</div>

</body>
</html>