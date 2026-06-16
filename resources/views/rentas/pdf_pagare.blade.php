<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pagare - {{ $renta->folio }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            padding: 40px;
        }
        .pagare-container {
            border: 2px solid #1a3c6e;
            padding: 25px;
            position: relative;
        }
        .pagare-title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            color: #1a3c6e;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .monto-letra {
            border: 1px solid #ddd;
            padding: 15px;
            margin: 20px 0;
            background: #f9f9f9;
            font-size: 13px;
        }
        .firma-linea {
            border-top: 1px solid #333;
            width: 60%;
            margin: 30px auto 5px auto;
        }
        .footer {
            margin-top: 50px;
            font-size: 9px;
            color: #999;
            text-align: center;
        }
        .data-field {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 150px;
            text-align: center;
            font-weight: bold;
        }
        .clausula-pagare {
            font-size: 10px;
            margin-top: 30px;
            padding: 10px;
            background: #f5f5f5;
        }
    </style>
</head>
<body>

<div class="pagare-container">
    <div class="pagare-title">
        PAGARÉ
    </div>

    <div style="margin-bottom: 15px;">
        <strong>FECHA:</strong> 
        <span class="data-field">
            {{ \Carbon\Carbon::now()->format('d/m/Y') }}
        </span>
    </div>

    <div style="margin-bottom: 15px;">
        <strong>MONTO:</strong> 
        <span class="data-field">
            ${{ number_format($renta->total - ($renta->deposito ?? 0), 2) }}
        </span>
    </div>

    <div class="monto-letra">
        <strong>Importe en letra:</strong><br>
        {{ $convertirNumeroALetras($renta->total - ($renta->deposito ?? 0)) }} PESOS 00/100 M.N.
    </div>

    <div style="margin-bottom: 20px;">
        Por este pagaré me (nos) obligo (amos) incondicionalmente a pagar a la orden del <strong>ING. GODOFREDO VIRAMONTES MEDINA</strong> 
        en <strong>DURANGO, DGO.</strong> el día 
        <span class="data-field">{{ \Carbon\Carbon::parse($renta->fecha_fin)->format('d/m/Y') }}</span> 
        la cantidad de <strong>${{ number_format($renta->total - ($renta->deposito ?? 0), 2) }}</strong>, 
        valor recibido a mi (nuestra) entera satisfacción.
    </div>

    <div style="margin-bottom: 15px;">
        <strong>DEBO (ADEMOS) Y PAGARÉ (EMOS) INCONDICIONALMENTE POR ESTE PAGARÉ</strong>
    </div>

    <div class="clausula-pagare">
        En caso de demora parcialmente insoluto sin que por ello se considere prorrogado el plazo fijado, 
        el deudor pagará intereses moratorios al 5% mensual sobre el saldo insoluto.
    </div>

    <!-- Datos del cliente para el pagaré -->
    <div style="margin: 30px 0;">
        <strong>NOMBRE DEL ACEPTANTE:</strong> {{ $renta->cliente->nombre_completo }}<br>
        <strong>DIRECCIÓN:</strong> {{ $renta->cliente->direccion ?? 'No especificada' }}<br>
        <strong>CIUDAD:</strong> {{ $renta->cliente->ciudad ?? 'Durango' }}, {{ $renta->cliente->estado ?? 'Dgo.' }}<br>
        <strong>TELÉFONO:</strong> {{ $renta->cliente->telefono }}
    </div>

    <!-- Firmas -->
    <div style="margin-top: 40px;">
        <div style="width: 45%; float: left; text-align: center;">
            <div class="firma-linea"></div>
            <strong>NOMBRE Y FIRMA</strong><br>
            <small>Aceptamos</small>
        </div>
        <div style="width: 10%; float: left;"></div>
        <div style="width: 45%; float: left; text-align: center;">
            <div class="firma-linea"></div>
            <strong>ING. GODOFREDO VIRAMONTES MEDINA</strong><br>
            <small>Nombre y firma del prestador</small>
        </div>
    </div>
    <div class="clearfix"></div>

    <div class="footer">
        Este pagaré se expide en DURANGO, DGO. a {{ \Carbon\Carbon::now()->format('d/m/Y') }}
    </div>
</div>

<style>
.clearfix {
    clear: both;
}
</style>

</body>
</html>