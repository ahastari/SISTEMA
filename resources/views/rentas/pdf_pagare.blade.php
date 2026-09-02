<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $pagareTitulo }} - {{ $renta->folio }}</title>
    <style>
        @page { margin: 1cm; size: letter portrait; }
        body {
            font-family: Arial, sans-serif;
            color: {{ $pagareColorPrincipal }}; /* Toma el verde configurado */
            background: #ffffff;
            font-size: {{ $pagareTamanoTexto }}px;
            margin: 0;
        }
        
        /* Contenedores principal e interno (Doble borde como en la foto) */
        .pagare-wrapper {
            border: 5px solid {{ $pagareColorPrincipal }};
            border-radius: 12px;
            padding: 4px;
            background-color: {{ $pagareColorFondo }};
        }
        .pagare-inner {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 8px;
            padding: 15px;
        }

        /* Estilos para las cajas blancas redondeadas */
        .box-title {
            border: 2px solid {{ $pagareColorPrincipal }};
            border-radius: 10px;
            padding: 5px 15px;
            font-size: 22px;
            font-weight: bold;
            font-style: italic;
            background: #ffffff;
            display: inline-block;
            color: {{ $pagareColorPrincipal }};
        }
        .box-input {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 10px;
            background: #ffffff;
            padding: 4px 10px;
            color: #000;
            font-weight: bold;
            text-align: center;
            display: inline-block;
        }
        .box-full {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 10px;
            background: #ffffff;
            padding: 8px;
            color: #000;
            font-weight: bold;
            text-align: center;
            margin-top: 2px;
        }

        /* Líneas para rellenar datos */
        .linea {
            border-bottom: 1px solid {{ $pagareColorPrincipal }};
            color: #000;
            font-weight: bold;
            text-align: center;
            vertical-align: bottom;
        }
        .linea-left {
            border-bottom: 1px solid {{ $pagareColorPrincipal }};
            color: #000;
            font-weight: bold;
            text-align: left;
            vertical-align: bottom;
            padding-left: 5px;
        }

        /* Textos pequeños debajo de las líneas */
        .sub-label {
            font-size: 9px;
            font-style: italic;
            color: {{ $pagareColorPrincipal }};
            text-align: center;
            vertical-align: top;
        }

        /* Recuadro inferior izquierdo (Datos del deudor) */
        .box-deudor {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 11px;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: bottom; padding-bottom: 2px; }
    </style>
</head>
<body>

<div class="pagare-wrapper">
    <div class="pagare-inner">

        <!-- FILA 1: Título, No. y Bueno por -->
        <table>
            <tr>
                <td width="30%">
                    <div class="box-title">{{ $pagareTitulo }}</div>
                </td>
                <td width="30%" align="center" style="font-weight: bold;">
                    {{ $pagareEtiquetaNumero }} <div class="box-input" style="width: 80px;">{{ $pagareNumeroMostrar }}</div>
                </td>
                <td width="40%" align="right" style="font-weight: bold;">
                    {{ $pagareTextoBuenoPor }} <div class="box-input" style="width: 120px;">{{ $pagareMontoMostrar }}</div>
                </td>
            </tr>
        </table>

        <!-- FILA 2: Lugar y Fecha de expedición -->
        <table style="margin-top: 10px;">
            <tr>
                <td width="40%"></td>
                <td width="5%" align="right" style="padding-right: 5px;">{{ $pagareTextoEn }}</td>
                <td width="25%" class="linea">{{ $pagareLugarExpedicionMostrar }}</td>
                <td width="5%" align="center">{{ $pagareTextoA }}</td>
                <td width="5%" class="linea">{{ $pagareDiaExpedicionMostrar }}</td>
                <td width="5%" align="center">{{ $pagareTextoDeMes }}</td>
                <td width="10%" class="linea">{{ $pagareMesExpedicionMostrar }}</td>
                <td width="5%" align="center">{{ $pagareTextoDeAnio }}</td>
                <td width="5%" class="linea">{{ $pagareAnioExpedicionMostrar }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td colspan="7" class="sub-label">{{ $pagareEtiquetaExpedicion }}</td>
            </tr>
        </table>

        <!-- FILA 3: Orden de -->
        <table style="margin-top: 5px;">
            <tr>
                <td width="55%" style="white-space: nowrap;">{{ $pagareTextoPromesa }}</td>
                <td width="45%" class="linea">{{ $pagareBeneficiario }}</td>
            </tr>
            <tr>
                <td></td>
                <td class="sub-label">{{ $pagareEtiquetaBeneficiario }}</td>
            </tr>
        </table>

        <!-- FILA 4: Lugar y fecha de pago -->
        <table style="margin-top: 0px;">
            <tr>
                <td width="35%" class="linea">&nbsp;</td>
                <td width="5%" align="center">en</td>
                <td width="30%" class="linea">{{ $pagareLugarPago }}</td>
                <td width="5%" align="center">el</td>
                <td width="25%" class="linea">{{ $pagareFechaPagoMostrar }}</td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td class="sub-label" align="center">{{ $pagareEtiquetaLugarPago }}</td>
                <td></td>
                <td class="sub-label" align="center">{{ $pagareEtiquetaFechaPago }}</td>
            </tr>
        </table>

        <!-- FILA 5: Cantidad en Letras -->
        <div style="margin-top: 10px; margin-bottom: 2px;">{{ $pagareTextoCantidad }}</div>
        <div class="box-full">
            {{ $pagareMontoLetrasMostrar }}
        </div>

        <!-- FILA 6: Clausula Legal -->
        <div style="margin-top: 10px; text-align: justify; line-height: 1.4; font-size: 11px;">
            {!! nl2br(e($pagareClausulaLegal)) !!}
        </div>

        <!-- FILA 7: Deudor y Firma -->
        <table style="margin-top: 15px;">
            <tr>
                <td width="55%" valign="top">
                    <div class="box-deudor">
                        <div style="text-align: center; margin-bottom: 5px; font-weight: bold;">{{ $pagareTituloDeudor }}</div>
                        <table style="margin-bottom: 4px;">
                            <tr>
                                <td width="15%" style="padding-bottom:0;">{{ $pagareEtiquetaNombre }}</td>
                                <td width="85%" class="linea-left" style="padding-bottom:0;">{{ $nombreCliente }}</td>
                            </tr>
                        </table>
                        <table style="margin-bottom: 4px;">
                            <tr>
                                <td width="15%" style="padding-bottom:0;">{{ $pagareEtiquetaDireccion }}</td>
                                <td width="55%" class="linea-left" style="padding-bottom:0;">{{ $direccionCliente }}</td>
                                <td width="10%" align="right" style="padding-bottom:0; padding-right:3px;">{{ $pagareEtiquetaTelefono }}</td>
                                <td width="20%" class="linea-left" style="padding-bottom:0;">{{ $telefonoCliente }}</td>
                            </tr>
                        </table>
                        <table>
                            <tr>
                                <td width="15%" style="padding-bottom:0;">{{ $pagareEtiquetaPoblacion }}</td>
                                <td width="85%" class="linea-left" style="padding-bottom:0;">{{ $ciudadCliente }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
                <td width="5%"></td>
                <td width="40%" valign="bottom" align="center">
                    <div style="margin-bottom: 25px; font-weight: bold;">{{ $pagareTextoAcepto }}</div>
                    <div style="border-top: 1px solid {{ $pagareColorPrincipal }}; width: 100%;"></div>
                    <div class="sub-label" style="margin-top: 2px;">{{ $pagareTextoFirma }}</div>
                </td>
            </tr>
        </table>

    </div>
</div>

</body>
</html>