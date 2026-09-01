<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <title>
        {{ $pagareTitulo }} - {{ $renta->folio }}
    </title>

    <style>

        @page {
            margin: 1cm;
            size: letter portrait;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            color: {{ $pagareColorPrincipal }};
            background: #ffffff;
            font-size: {{ $pagareTamanoTexto }}px;
            margin: 0;
            padding: 0;
        }


        /* ==============================================
           CONTENEDOR
        ============================================== */

        .pagare-wrapper {
            border: 5px solid {{ $pagareColorPrincipal }};
            border-radius: 12px;
            padding: 9px;
            background-color: {{ $pagareColorFondo }};
        }

        .pagare-inner {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 8px;
            padding: 15px;
        }


        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            vertical-align: middle;
        }


        /* ==============================================
           ENCABEZADO
        ============================================== */

        .title-box {
            border: 2px solid {{ $pagareColorPrincipal }};
            border-radius: 7px;
            padding: 5px 14px;

            font-size: 26px;
            font-weight: bold;
            font-style: italic;

            background: #ffffff;

            display: inline-block;
        }


        .amount-box {
            border: 2px solid {{ $pagareColorPrincipal }};
            border-radius: 7px;
            background: #ffffff;

            padding: 5px 12px;

            min-width: 110px;

            text-align: center;
            font-weight: bold;

            display: inline-block;
        }


        .underline {
            border-bottom: 1px solid {{ $pagareColorPrincipal }};
            text-align: center;
            font-weight: bold;
            display: inline-block;
        }


        /* ==============================================
           EXPEDICIÓN
        ============================================== */

        .expedicion {
            margin-top: 15px;
            text-align: right;
            line-height: 1.8;
        }

        .sub-label {
            font-size: 8px;
            font-style: italic;
            color: {{ $pagareColorPrincipal }};
        }


        /* ==============================================
           CUERPO
        ============================================== */

        .promesa {
            margin-top: 20px;
            line-height: 1.6;
            text-align: justify;
        }


        .beneficiario {
            margin-top: 8px;
            text-align: center;
        }


        .beneficiario .valor {
            width: 65%;
        }


        .pago-table {
            margin-top: 15px;
        }


        .pago-table td {
            padding: 2px 6px;
        }


        .amount-words {
            border: 2px solid {{ $pagareColorPrincipal }};
            border-radius: 7px;

            background: #ffffff;

            margin-top: 8px;

            padding: 8px 12px;

            font-weight: bold;

            text-align: center;
        }


        .porcentaje {
            margin-top: 5px;
            font-size: 8px;
            text-align: right;
        }


        /* ==============================================
           CLÁUSULA LEGAL
        ============================================== */

        .legal-text {
            font-size: 9px;
            line-height: 1.45;

            margin-top: 14px;

            text-align: justify;
        }


        /* ==============================================
           DEUDOR
        ============================================== */

        .footer-table {
            margin-top: 18px;
        }


        .debtor-box {
            border: 1px solid {{ $pagareColorPrincipal }};
            border-radius: 7px;

            padding: 9px;

            width: 95%;

            line-height: 1.8;

            font-size: 10px;
        }


        .debtor-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 7px;
        }


        /* ==============================================
           FIRMA
        ============================================== */

        .signature-section {
            text-align: center;
        }


        .signature-line {
            border-bottom: 1px solid {{ $pagareColorPrincipal }};

            width: 80%;

            margin: 38px auto 5px auto;
        }


        .footer-note {
            margin-top: 10px;

            text-align: center;

            font-size: 8px;
            font-style: italic;
        }

    </style>

</head>


<body>


<div class="pagare-wrapper">

    <div class="pagare-inner">


        {{-- ======================================================
             ENCABEZADO
        ======================================================= --}}

        <table>

            <tr>

                <td style="width: 30%;">

                    <div class="title-box">

                        {{ $pagareTitulo }}

                    </div>

                </td>


                <td
                    style="
                        width: 25%;
                        text-align: center;
                    "
                >

                    <strong>
                        {{ $pagareEtiquetaNumero }}
                    </strong>

                    <span
                        class="underline"
                        style="width: 80px;"
                    >

                        {{ $pagareNumeroMostrar }}

                    </span>

                </td>


                <td
                    style="
                        width: 45%;
                        text-align: right;
                    "
                >

                    <strong>
                        {{ $pagareTextoBuenoPor }}
                    </strong>

                    <div class="amount-box">
                        {{ $pagareMontoMostrar }}
                    </div>

                </td>

            </tr>

        </table>



        {{-- ======================================================
             CIUDAD Y FECHA
        ======================================================= --}}

        <div class="expedicion">

            {{ $pagareTextoEn }}

            <span
                class="underline"
                style="width: 160px;"
            >
                {{ $pagareLugarExpedicionMostrar }}
            </span>


            {{ $pagareTextoA }}


            <span
                class="underline"
                style="width: 35px;"
            >
                {{ $pagareDiaExpedicionMostrar }}
            </span>


            {{ $pagareTextoDeMes }}


            <span
                class="underline"
                style="width: 95px;"
            >
                {{ $pagareMesExpedicionMostrar }}
            </span>


            {{ $pagareTextoDeAnio }}


            <span
                class="underline"
                style="width: 50px;"
            >
                {{ $pagareAnioExpedicionMostrar }}
            </span>


            <div
                class="sub-label"
                style="
                    padding-right: 45px;
                    margin-top: -2px;
                "
            >

                {{ $pagareEtiquetaExpedicion }}

            </div>

        </div>



        {{-- ======================================================
             PROMESA DE PAGO
        ======================================================= --}}

        <div class="promesa">

            {{ $pagareTextoPromesa }}

        </div>


        <div class="beneficiario">

            <span
                class="underline valor"
            >
                {{ $pagareBeneficiario }}
            </span>

            <div class="sub-label">

                {{ $pagareEtiquetaBeneficiario }}

            </div>

        </div>



        {{-- ======================================================
             LUGAR / FECHA DE PAGO
        ======================================================= --}}

        <table class="pago-table">

            <tr>

                <td style="width: 5%;">
                    en
                </td>


                <td
                    style="
                        width: 45%;
                        text-align: center;
                    "
                >

                    <span
                        class="underline"
                        style="width: 95%;"
                    >

                        {{ $pagareLugarPago }}

                    </span>

                </td>


                <td
                    style="
                        width: 5%;
                        text-align: center;
                    "
                >
                    el
                </td>


                <td
                    style="
                        width: 45%;
                        text-align: center;
                    "
                >

                    <span
                        class="underline"
                        style="width: 95%;"
                    >

                        {{ $pagareFechaPagoMostrar }}

                    </span>

                </td>

            </tr>


            <tr>

                <td></td>

                <td
                    class="sub-label"
                    style="text-align: center;"
                >

                    {{ $pagareEtiquetaLugarPago }}

                </td>


                <td></td>


                <td
                    class="sub-label"
                    style="text-align: center;"
                >

                    {{ $pagareEtiquetaFechaPago }}

                </td>

            </tr>

        </table>



        {{-- ======================================================
             CANTIDAD
        ======================================================= --}}

        <div style="margin-top: 9px;">

            {{ $pagareTextoCantidad }}

        </div>


        <div class="amount-words">
            ({{ $pagareMontoLetrasMostrar }})
        </div>


        @if(!empty($pagareTextoImporte))

            <div class="porcentaje">
                {{ $pagareTextoImporte }}
            </div>

        @endif



        {{-- ======================================================
             CLÁUSULA EDITABLE
        ======================================================= --}}

        @if(!empty(trim($pagareClausulaLegal)))

            <div class="legal-text">

                {!! nl2br(
                    e($pagareClausulaLegal)
                ) !!}

            </div>

        @endif



        {{-- ======================================================
             DEUDOR / FIRMA
        ======================================================= --}}

        <table class="footer-table">

            <tr>

                <td
                    style="
                        width: 57%;
                        vertical-align: top;
                    "
                >

                    <div class="debtor-box">


                        <div class="debtor-title">

                            {{ $pagareTituloDeudor }}

                        </div>


                        <strong>
                            {{ $pagareEtiquetaNombre }}
                        </strong>

                        <span
                            class="underline"
                            style="width: 235px;"
                        >

                            {{ $nombreCliente }}

                        </span>

                        <br>


                        <strong>
                            {{ $pagareEtiquetaDireccion }}
                        </strong>

                        <span
                            class="underline"
                            style="width: 220px;"
                        >

                            {{ $direccionCliente }}

                        </span>

                        <br>


                        <strong>
                            {{ $pagareEtiquetaPoblacion }}
                        </strong>

                        <span
                            class="underline"
                            style="width: 110px;"
                        >

                            {{ $ciudadCliente }}

                        </span>


                        <strong>
                            {{ $pagareEtiquetaTelefono }}
                        </strong>

                        <span
                            class="underline"
                            style="width: 85px;"
                        >

                            {{ $telefonoCliente }}

                        </span>


                    </div>

                </td>


                <td
                    style="
                        width: 43%;
                        vertical-align: bottom;
                    "
                >

                    <div class="signature-section">


                        <strong>

                            {{ $pagareTextoAcepto }}

                        </strong>


                        <div class="signature-line"></div>


                        {{ $pagareTextoFirma }}


                    </div>

                </td>

            </tr>

        </table>



        {{-- ======================================================
             PIE
        ======================================================= --}}

        <div class="footer-note">

            {{ $pagareTextoPie }}

        </div>


    </div>

</div>


</body>

</html>