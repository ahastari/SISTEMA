<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        @page { margin: 2cm 1.5cm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #1e293b; font-size: 11px; line-height: 1.6; }
        
        /* Cabecera Corporativa de Alta Costura */
        .header { border-bottom: 2px solid #0d6efd; padding-bottom: 14px; margin-bottom: 25px; }
        .company-name { font-size: 13px; font-weight: bold; color: #0f172a; letter-spacing: 1px; margin: 0; }
        .report-title { font-size: 18px; color: #475569; margin: 4px 0 0 0; font-weight: 300; }
        .meta-date { text-align: right; color: #64748b; font-size: 9px; float: right; margin-top: -25px; }
        
        h3 { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #0f172a; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-top: 24px; margin-bottom: 12px; }
        
        /* Bloques de Estados Financieros KPI */
        .kpi-table { width: 100%; margin-bottom: 20px; border-spacing: 8px 0; margin-left: -8px; }
        .kpi-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 6px; text-align: center; }
        .kpi-lbl { font-size: 8px; color: #64748b; text-transform: uppercase; font-weight: bold; }
        .kpi-val { font-size: 15px; font-weight: bold; margin-top: 4px; }
        
        .text-primary { color: #0d6efd; }
        .text-success { color: #16a34a; }
        .text-muted { color: #475569; }

        /* Estructuras de Tablas */
        .table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .table th { background: #f1f5f9; color: #475569; font-size: 8px; font-weight: bold; text-transform: uppercase; padding: 8px 10px; text-align: left; }
        .table td { padding: 9px 10px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .text-right { text-align: right; }

        /* Contenedores de Gráficos SVG Nativos Vectoriales */
        .chart-row { width: 100%; margin-bottom: 20px; }
        .chart-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 15px; border-radius: 8px; text-align: center; }
        
        .footer { text-align: center; position: fixed; bottom: -0.6cm; left: 0; right: 0; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <h1 class="company-name">ANDAMIOS Y MADERA VIRAMONTES</h1>
        <h2 class="report-title">Balance e Informes de Gestión Financiera</h2>
        <div class="meta-date">Período: {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</div>
    </div>

    <table class="kpi-table">
        <tr>
            <td style="width: 33%;">
                <div class="kpi-card">
                    <div class="kpi-lbl">Ingresos Totales (Ventas)</div>
                    <div class="kpi-val text-primary">${{ number_format($totalVentas, 2) }}</div>
                </div>
            </td>
            <td style="width: 33%;">
                <div class="kpi-card">
                    <div class="kpi-lbl">Costo de Inversión (Mercancía)</div>
                    <div class="kpi-val text-muted">${{ number_format($totalCostos, 2) }}</div>
                </div>
            </td>
            <td style="width: 34%;">
                <div class="kpi-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                    <div class="kpi-lbl text-success">Utilidad Neta Neto (Ganancia)</div>
                    <div class="kpi-val text-success">${{ number_format($gananciaNeta, 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="chart-row" style="width: 100%;">
        <tr>
            <td style="width: 50%; padding-right: 10px; vertical-align: top;">
                <h3>Ventas por Canal Liquidación</h3>
                <div class="chart-box">
                    <svg width="220" height="130" viewBox="0 0 220 130">
                        @php $y = 10; @endphp
                        @foreach($pagosPorMetodo as $metodo => $monto)
                            @php 
                                $porcentaje = $totalVentas > 0 ? ($monto / $totalVentas) * 100 : 0; 
                                $anchoBarra = $porcentaje * 1.1; // Ajuste al contenedor del PDF
                            @endphp
                            <text x="5" y="{{ $y + 9 }}" font-size="9" fill="#334155" font-weight="bold">{{ ucfirst($metodo) }}</text>
                            <rect x="80" y="{{ $y }}" width="110" height="11" rx="2" fill="#e2e8f0"/>
                            <rect x="80" y="{{ $y }}" width="{{ max($anchoBarra, 1) }}" height="11" rx="2" fill="#0d6efd"/>
                            <text x="195" y="{{ $y + 9 }}" font-size="8" fill="#475569" text-anchor="end">${{ number_format($monto, 0) }}</text>
                            @php $y += 24; @endphp
                        @endforeach
                    </svg>
                </div>
            </td>
            
            <td style="width: 50%; padding-left: 10px; vertical-align: top;">
                <h3>Top Productos Despachados</h3>
                <div class="chart-box">
                    <svg width="220" height="130" viewBox="0 0 220 130">
                        @php 
                            $yProd = 10; 
                            $maxCantidad = count($topProductos) > 0 ? max($topProductos) : 1;
                        @endphp
                        @foreach($topProductos as $nombre => $cantidad)
                            @php 
                                $anchoBarraProd = ($cantidad / $maxCantidad) * 100;
                            @endphp
                            <text x="5" y="{{ $yProd + 9 }}" font-size="8" fill="#334155" font-weight="bold">{{ \Illuminate\Support\Str::limit($nombre, 12) }}</text>
                            <rect x="85" y="{{ $yProd }}" width="105" height="11" rx="2" fill="#e2e8f0"/>
                            <rect x="85" y="{{ $yProd }}" width="{{ max($anchoBarraProd, 1) }}" height="11" rx="2" fill="#f59e0b"/>
                            <text x="195" y="{{ $yProd + 9 }}" font-size="8" fill="#475569" text-anchor="end">{{ $cantidad }} u</text>
                            @php $yProd += 17; @endphp
                        @endforeach
                    </svg>
                </div>
            </td>
        </tr>
    </table>

    <h3>Bitácora Detallada de Transacciones Comerciales</h3>
    <table class="table">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Cliente Destinatario</th>
                <th>Fecha Registro</th>
                <th>Método</th>
                <th class="text-right">Monto Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
                <tr>
                    <td style="font-family: monospace; font-size: 11px; font-weight: bold;">{{ $venta->folio }}</td>
                    <td>{{ $venta->cliente_nombre }}</td>
                    <td>{{ $venta->created_at->format('d/m/Y H:i') }}</td>
                    <td><span style="font-size: 8px; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; color: #475569; font-weight: bold;">{{ strtoupper($venta->metodo_pago) }}</span></td>
                    <td class="text-right" style="font-weight: bold; color: #16a34a;">${{ number_format($venta->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Informe Analítico de Auditoría de Sucursal - Andamios y Madera Viramontes</p>
    </div>
</body>
</html>