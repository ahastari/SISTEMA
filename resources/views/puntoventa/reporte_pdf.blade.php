<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo ?? 'Balance Financiero' }}</title>
    <style>
        /* CONFIGURACIÓN CARTA VERTICAL ESTRICTA */
        @page { 
            size: letter portrait;
            margin: 1.2cm 1.2cm 1.5cm 1.2cm; 
        }
        
        * { box-sizing: border-box; }

        body { 
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; 
            color: #0f172a; 
            font-size: 8.5pt; 
            line-height: 1.25; 
            margin: 0;
            padding: 0;
        }

        table { width: 100%; border-collapse: collapse; }

        /* ENCABEZADO Y SUCURSAL */
        .header-table {
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .logo-img {
            max-height: 48px;
            max-width: 150px;
            margin-bottom: 4px;
        }
        .brand-title { 
            font-size: 11pt; 
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
            font-size: 7pt;
            font-weight: bold;
            padding: 2px 5px;
            border-radius: 3px;
            border: 1px solid #bae6fd;
            margin-top: 2px;
            margin-bottom: 4px;
        }
        .branch-details {
            font-size: 7pt;
            color: #475569;
            line-height: 1.2;
        }

        /* TITULARES */
        .section-header { 
            font-size: 7.5pt; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 0.4px;
            color: #0f172a; 
            background: #f8fafc;
            border-left: 3px solid #0284c7;
            padding: 3px 6px;
            margin-top: 10px; 
            margin-bottom: 5px; 
        }

        /* CARDS KPIS */
        .kpi-table { margin-bottom: 8px; }
        .kpi-card { 
            background: #ffffff; 
            border: 1px solid #cbd5e1; 
            padding: 6px 8px; 
            border-radius: 4px; 
        }
        .kpi-title { 
            font-size: 6.5pt; 
            color: #64748b; 
            text-transform: uppercase; 
            font-weight: 700; 
        }
        .kpi-amount { 
            font-size: 11pt; 
            font-weight: 800; 
            margin-top: 2px; 
            font-family: 'Courier New', Courier, monospace;
        }

        /* ANALÍTICAS DE BARRAS */
        .analytics-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 5px 7px;
        }
        .bar-bg {
            width: 100%;
            background-color: #f1f5f9;
            height: 6px;
            border-radius: 2px;
        }
        .bar-fill-blue { height: 6px; background-color: #0284c7; border-radius: 2px; }
        .bar-fill-dark { height: 6px; background-color: #0f172a; border-radius: 2px; }

        /* BITÁCORA */
        .data-table { margin-top: 4px; }
        .data-table th { 
            background: #0f172a; 
            color: #ffffff; 
            font-size: 7pt; 
            font-weight: 700; 
            text-transform: uppercase; 
            padding: 4px 6px; 
            text-align: left; 
        }
        .data-table td { 
            padding: 4px 6px; 
            border-bottom: 1px solid #e2e8f0; 
            color: #334155; 
            font-size: 7.5pt;
        }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        .nowrap { white-space: nowrap; }
        .font-mono { font-family: 'Courier New', Courier, monospace; font-weight: bold; }

        /* FOOTER */
        .footer { 
            position: fixed; 
            bottom: -0.8cm; 
            left: 0; 
            right: 0; 
            font-size: 6.5pt; 
            color: #94a3b8; 
            border-top: 1px solid #e2e8f0;
            padding-top: 3px;
        }
    </style>
</head>
<body>

    <!-- BLOQUE DE CÁLCULO SEGURO PREVIO A TODO EL HTML -->
    @php 
        $coleccionVentas = $ventas ?? collect();
        $totalVentaPDF = $totalVentas ?? $coleccionVentas->sum('total');
        $fletes = 0; 
        $manoObra = 0;
        $costoProduccionPDF = 0;

        foreach($coleccionVentas as $v) {
            if(isset($v->detalles)) {
                foreach($v->detalles as $d) {
                    if(str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                        $fletes += $d->subtotal;
                    } elseif(str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                        $manoObra += $d->subtotal;
                    } else {
                        // Costo de adquisición/producción
                        $costoUnit = $d->costo ?? ($d->equipo->costo ?? 0);
                        $costoProduccionPDF += ($costoUnit * $d->cantidad);
                    }
                }
            }
        }
        $utilidadPDF = $totalVentaPDF - $costoProduccionPDF;
    @endphp

    <!-- ENCABEZADO PRINCIPAL CON LOGO Y DATOS DE SUCURSAL -->
    <table class="header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                @if(isset($logoBase64) && $logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo Sucursal"><br>
                @endif
                
                <h1 class="brand-title">ANDAMIOS Y MADERA VIRAMONTES</h1>
                
                <div class="branch-badge">
                    SUCURSAL: <strong>{{ strtoupper($sucursalNombre ?? 'Matriz General') }}</strong>
                </div>

                @if(isset($sucursalObj) && $sucursalObj)
                    <div class="branch-details">
                        @if($sucursalObj->direccion)
                            <strong>Dirección:</strong> {{ $sucursalObj->direccion }}<br>
                        @endif
                        @if($sucursalObj->telefono || $sucursalObj->email)
                            <strong>Contacto:</strong> {{ $sucursalObj->telefono ?? 'S/T' }} {{ $sucursalObj->email ? '| ' . $sucursalObj->email : '' }}
                        @endif
                    </div>
                @endif
            </td>
            
            <td style="width: 42%; text-align: right; vertical-align: top;">
                <div style="font-size: 10pt; font-weight: bold; color: #0f172a;">BALANCE FINANCIERO DE CAJA</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 3px;">
                    <strong>Período:</strong> {{ isset($inicio) ? (\Carbon\Carbon::parse($inicio)->format('d/m/Y')) : date('01/m/Y') }} 
                    al 
                    {{ isset($fin) ? (\Carbon\Carbon::parse($fin)->format('d/m/Y')) : date('d/m/Y') }}
                </div>
                <div style="font-size: 6.5pt; color: #94a3b8; margin-top: 2px;">
                    Generado: {{ date('d/m/Y H:i:s') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- KPIS DE MÉTRICAS PRINCIPALES -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%; padding-right: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #0284c7;">
                    <div class="kpi-title">Total Venta</div>
                    <div class="kpi-amount" style="color: #0284c7;">${{ number_format($totalVentaPDF, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #d97706;">
                    <div class="kpi-title">Costo Producción</div>
                    <div class="kpi-amount" style="color: #d97706;">${{ number_format($costoProduccionPDF, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #059669;">
                    <div class="kpi-title">Utilidad Bruta Est.</div>
                    <div class="kpi-amount" style="color: #059669;">${{ number_format($utilidadPDF, 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #4f46e5;">
                    <div class="kpi-title">Fletes / MO</div>
                    <div class="kpi-amount" style="color: #4f46e5;">${{ number_format($fletes + $manoObra, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ANALÍTICAS DE DOS COLUMNAS -->
    <table>
        <tr>
            <td style="width: 49%; vertical-align: top; padding-right: 5px;">
                <div class="section-header">Método de Pago</div>
                <div class="analytics-box">
                    <table>
                        @php $pagosList = $pagosPorMetodo ?? []; @endphp
                        @forelse($pagosList as $metodo => $monto)
                        @php $pct = $totalVentaPDF > 0 ? ($monto / $totalVentaPDF) * 100 : 0; @endphp
                        <tr>
                            <td style="padding: 2px 0; font-size: 7.5pt; font-weight: bold; width: 32%;" class="nowrap">{{ ucfirst($metodo) }}</td>
                            <td style="padding: 2px 0; width: 43%;">
                                <div class="bar-bg">
                                    <div class="bar-fill-blue" style="width: {{ max($pct, 2) }}%;"></div>
                                </div>
                            </td>
                            <td style="padding: 2px 0; font-size: 7pt; font-weight: bold; text-align: right; width: 25%; font-family: monospace;" class="nowrap">
                                ${{ number_format($monto, 0) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-size: 7.5pt; padding: 6px;">
                                Sin métodos registrados.
                            </td>
                        </tr>
                        @endforelse
                    </table>
                </div>
            </td>

            <td style="width: 49%; vertical-align: top; padding-left: 5px;">
                <div class="section-header">Demanda Comercial (Top Productos)</div>
                <div class="analytics-box">
                    @php 
                        $topProdList = $topProductos ?? [];
                        $maxCantidad = !empty($topProdList) ? max($topProdList) : 1; 
                    @endphp
                    <table>
                        @forelse($topProdList as $nombre => $cantidad)
                        @php $pctProd = ($cantidad / $maxCantidad) * 100; @endphp
                        <tr>
                            <td style="padding: 2px 0; font-size: 7.5pt; font-weight: bold; width: 45%;" class="nowrap">
                                {{ \Illuminate\Support\Str::limit($nombre, 18) }}
                            </td>
                            <td style="padding: 2px 0; width: 35%;">
                                <div class="bar-bg">
                                    <div class="bar-fill-dark" style="width: {{ max($pctProd, 2) }}%;"></div>
                                </div>
                            </td>
                            <td style="padding: 2px 0; font-size: 7pt; font-weight: bold; text-align: right; width: 20%; font-family: monospace;" class="nowrap">
                                {{ $cantidad }} u
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #94a3b8; font-size: 7.5pt; padding: 6px;">
                                Sin mercancías despachadas.
                            </td>
                        </tr>
                        @endforelse
                    </table>
                </div>
            </td>
        </tr>
    </table>

    <!-- BITÁCORA DETALLADA DE VENTAS -->
    <div class="section-header">Bitácora Detallada de Ventas</div>
    
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Folio</th>
                <th style="width: 34%;">Cliente</th>
                <th style="width: 22%;">Fecha & Hora</th>
                <th style="width: 12%;">Método</th>
                <th style="width: 12%; text-align: right;">Monto Bruto</th>
            </tr>
        </thead>
        <tbody>
            @forelse($coleccionVentas as $venta)
                <tr>
                    <td class="font-mono nowrap" style="color: #0284c7;">{{ $venta->folio }}</td>
                    <td class="nowrap"><strong>{{ \Illuminate\Support\Str::limit($venta->cliente_nombre ?? 'Público General', 26) }}</strong></td>
                    <td style="color: #64748b;" class="nowrap">
                        {{ is_object($venta->created_at) ? $venta->created_at->format('d/m/Y H:i') : \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y H:i') }}
                    </td>
                    <td class="nowrap">
                        <span style="font-size: 6pt; background: #e2e8f0; padding: 1px 4px; border-radius: 2px; font-weight: bold; color: #334155;">
                            {{ strtoupper($venta->metodo_pago) }}
                        </span>
                    </td>
                    <td style="text-align: right;" class="font-mono nowrap">${{ number_format($venta->total, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 10px;">
                        No existen registros de transacciones comerciales en el período seleccionado.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <table>
            <tr>
                <td style="width: 60%; text-align: left;">
                    Documento Oficial de Auditoría — <strong>Andamios y Madera Viramontes</strong> | {{ $sucursalNombre ?? 'Matriz General' }}
                </td>
                <td style="width: 40%; text-align: right;">
                    Generado el {{ date('d/m/Y H:i:s') }}
                </td>
            </tr>
        </table>
    </div>

</body>
</html>