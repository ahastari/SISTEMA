<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Estado Financiero de Rentas</title>
    <style>
        @page { size: letter landscape; margin: 1.1cm 1.1cm 1.4cm 1.1cm; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0f172a; font-size: 8pt; line-height: 1.25; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }

        .header-table { border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin-bottom: 10px; }
        .logo-img { max-height: 46px; max-width: 150px; margin-bottom: 4px; }
        .brand-title { font-size: 11pt; font-weight: 800; text-transform: uppercase; margin: 0; letter-spacing: 0.3px; }
        .branch-badge { display: inline-block; background: #f0f9ff; color: #0369a1; font-size: 7pt; font-weight: bold; padding: 2px 5px; border-radius: 3px; border: 1px solid #bae6fd; margin-top: 2px; margin-bottom: 4px; }
        .branch-details { font-size: 7pt; color: #475569; line-height: 1.2; }

        .section-header { font-size: 7.5pt; font-weight: 800; text-transform: uppercase; letter-spacing: 0.4px; background: #f8fafc; border-left: 3px solid #0284c7; padding: 3px 6px; margin-top: 10px; margin-bottom: 5px; }

        .kpi-table { margin-bottom: 6px; }
        .kpi-card { background: #fff; border: 1px solid #cbd5e1; padding: 5px 8px; border-radius: 4px; }
        .kpi-title { font-size: 6.5pt; color: #64748b; text-transform: uppercase; font-weight: 700; }
        .kpi-amount { font-size: 11pt; font-weight: 800; margin-top: 2px; font-family: 'Courier New', Courier, monospace; }
        .kpi-sub { font-size: 6.3pt; color: #64748b; margin-top: 1px; }

        .data-table { margin-top: 4px; }
        .data-table th { background: #0f172a; color: #fff; font-size: 6.8pt; font-weight: 700; text-transform: uppercase; padding: 4px 5px; text-align: left; }
        .data-table td { padding: 3px 5px; border-bottom: 1px solid #e2e8f0; color: #334155; font-size: 7.2pt; }
        .data-table tr:nth-child(even) { background: #f8fafc; }
        .data-table tr { page-break-inside: avoid; }
        .data-table tfoot td { font-weight: bold; background: #f1f5f9; border-top: 1px solid #94a3b8; }
        .nowrap { white-space: nowrap; }
        .r { text-align: right; }
        .c { text-align: center; }
        .mono { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .tag { font-size: 6pt; padding: 1px 4px; border-radius: 2px; font-weight: bold; background: #e2e8f0; color: #334155; }
        .tag-red { background: #fee2e2; color: #b91c1c; }
        .tag-green { background: #dcfce7; color: #15803d; }
        .tag-amber { background: #fef3c7; color: #b45309; }
        .tag-blue { background: #dbeafe; color: #1d4ed8; }

        .bar-bg { width: 100%; background-color: #f1f5f9; height: 7px; border-radius: 2px; }
        .bar-blue { height: 7px; background-color: #0284c7; border-radius: 2px; }
        .bar-green { height: 7px; background-color: #059669; border-radius: 2px; }
        .bar-cyan { height: 7px; background-color: #0891b2; border-radius: 2px; }
        .bar-red { height: 7px; background-color: #dc2626; border-radius: 2px; }

        .footer { position: fixed; bottom: -0.8cm; left: 0; right: 0; font-size: 6.5pt; color: #94a3b8; border-top: 1px solid #e2e8f0; padding-top: 3px; }
    </style>
</head>
<body>
@php
    $rk  = $repRentas['kpi'];
    $rco = $repRentas['cobro'];
    $rca = $repRentas['cartera'];
    $tagEstado = ['Vencida' => 'tag-red', 'Activa' => 'tag-blue'];
    $tagPago = ['Liquidado' => 'tag-green', 'Pendiente' => 'tag-amber', 'Con abonos' => 'tag-blue'];
    $nombreEmpresa = \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'ANDAMIOS Y MADERA VIRAMONTES');
@endphp

    <!-- ENCABEZADO -->
    <table class="header-table">
        <tr>
            <td style="width: 58%; vertical-align: top;">
                @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo"><br>
                @endif
                <h1 class="brand-title">{{ $nombreEmpresa }}</h1>
                <div class="branch-badge">SUCURSAL: <strong>{{ strtoupper($sucursalNombre ?? 'Matriz General') }}</strong></div>
                @if(!empty($sucursalObj))
                    <div class="branch-details">
                        @if($sucursalObj->direccion)<strong>Dirección:</strong> {{ $sucursalObj->direccion }}<br>@endif
                        @if($sucursalObj->telefono || $sucursalObj->email)<strong>Contacto:</strong> {{ $sucursalObj->telefono ?? 'S/T' }} {{ $sucursalObj->email ? '| ' . $sucursalObj->email : '' }}@endif
                    </div>
                @endif
            </td>
            <td style="width: 42%; text-align: right; vertical-align: top;">
                <div style="font-size: 10pt; font-weight: bold;">ESTADO FINANCIERO DE RENTAS</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 3px;">
                    <strong>Período:</strong> {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}
                </div>
                <div style="font-size: 6.5pt; color: #94a3b8; margin-top: 2px;">Generado: {{ date('d/m/Y H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <!-- KPIs -->
    <table class="kpi-table">
        <tr>
            <td style="width: 25%; padding-right: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #0284c7;">
                    <div class="kpi-title">Rentas Contratadas</div>
                    <div class="kpi-amount" style="color: #0284c7;">${{ number_format($rk['total'], 2) }}</div>
                    <div class="kpi-sub">{{ $rk['n'] }} rentas @if($rk['canceladas_n'] > 0)· {{ $rk['canceladas_n'] }} cancelada(s) excluida(s)@endif</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #059669;">
                    <div class="kpi-title">Cobranza del Período</div>
                    <div class="kpi-amount" style="color: #059669;">${{ number_format($rco['total'], 2) }}</div>
                    <div class="kpi-sub">Pagos ${{ number_format($rco['pagos_monto'], 2) }} · Depósitos ${{ number_format($rco['depositos'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #d97706;">
                    <div class="kpi-title">Saldo de Rentas del Período</div>
                    <div class="kpi-amount" style="color: #d97706;">${{ number_format($rk['saldo'], 2) }}</div>
                    <div class="kpi-sub">Pagado a la fecha ${{ number_format($rk['pagado'] + $rk['depositos'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #dc2626;">
                    <div class="kpi-title">Cartera por Cobrar (a hoy)</div>
                    <div class="kpi-amount" style="color: #dc2626;">${{ number_format($rca['por_cobrar'], 2) }}</div>
                    <div class="kpi-sub">Vencido ${{ number_format($rca['vencido'], 2) }} ({{ $rca['n_vencidas'] }}) · Abiertas {{ $rca['n_abiertas'] }}</div>
                </div>
            </td>
        </tr>
    </table>
    <table class="kpi-table">
        <tr>
            <td style="width: 25%; padding-right: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #059669;">
                    <div class="kpi-title">Descuentos Otorgados</div>
                    <div class="kpi-amount" style="color: #059669;">${{ number_format($rk['descuentos'], 2) }}</div>
                    <div class="kpi-sub">{{ $rk['descuentos_n'] }} renta(s) · Subtotal bruto ${{ number_format($rk['subtotal'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #4f46e5;">
                    <div class="kpi-title">Flete / Mano de Obra</div>
                    <div class="kpi-amount" style="color: #4f46e5;">${{ number_format($rk['fletes'] + $rk['mano_obra'], 2) }}</div>
                    <div class="kpi-sub">Flete ${{ number_format($rk['fletes'], 2) }} · MO ${{ number_format($rk['mano_obra'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 0 2px;">
                <div class="kpi-card" style="border-left: 3px solid #dc2626;">
                    <div class="kpi-title">Cargos Extra</div>
                    <div class="kpi-amount" style="color: #dc2626;">${{ number_format($rk['cargos_extra'], 2) }}</div>
                    <div class="kpi-sub">Multas, daños y faltantes · IVA ${{ number_format($rk['iva'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding-left: 3px;">
                <div class="kpi-card" style="border-left: 3px solid #d97706;">
                    <div class="kpi-title">Retraso Estimado sin Cargar</div>
                    <div class="kpi-amount" style="color: #d97706;">${{ number_format($rca['multa'], 2) }}</div>
                    <div class="kpi-sub">Días extra de rentas activas vencidas</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- COBRANZA -->
    <div class="section-header">Cobranza de Rentas por Método y Tipo de Pago</div>
    <table>
        <tr>
            <td style="width: 49%; vertical-align: top; padding-right: 6px;">
                <table class="data-table">
                    <thead><tr><th>Método / Concepto</th><th class="c">Transacc.</th><th class="r">Monto</th></tr></thead>
                    <tbody>
                        <tr><td>Efectivo</td><td class="c">{{ $rco['por_metodo']['efectivo']['n'] }}</td><td class="r mono">${{ number_format($rco['por_metodo']['efectivo']['total'], 2) }}</td></tr>
                        <tr><td>Transferencia</td><td class="c">{{ $rco['por_metodo']['transferencia']['n'] }}</td><td class="r mono">${{ number_format($rco['por_metodo']['transferencia']['total'], 2) }}</td></tr>
                        <tr><td>Tarjeta / Terminal</td><td class="c">{{ $rco['por_metodo']['tarjeta']['n'] }}</td><td class="r mono">${{ number_format($rco['por_metodo']['tarjeta']['total'], 2) }}</td></tr>
                        <tr><td>Depósitos en garantía (rentas del período)</td><td></td><td class="r mono">${{ number_format($rco['depositos'], 2) }}</td></tr>
                    </tbody>
                    <tfoot><tr><td>COBRANZA TOTAL</td><td class="c">{{ $rco['n'] }}</td><td class="r mono">${{ number_format($rco['total'], 2) }}</td></tr></tfoot>
                </table>
            </td>
            <td style="width: 51%; vertical-align: top; padding-left: 6px;">
                <table class="data-table">
                    <thead><tr><th>Tipo de pago</th><th class="c">Transacc.</th><th class="r">Monto</th></tr></thead>
                    <tbody>
                        @foreach($rco['por_tipo'] as $t)
                            <tr><td>{{ $t['texto'] }}</td><td class="c">{{ $t['n'] }}</td><td class="r mono">${{ number_format($t['total'], 2) }}</td></tr>
                        @endforeach
                    </tbody>
                    <tfoot><tr><td>PAGOS RECIBIDOS</td><td class="c">{{ $rco['n'] }}</td><td class="r mono">${{ number_format($rco['pagos_monto'], 2) }}</td></tr></tfoot>
                </table>
            </td>
        </tr>
    </table>

    @php
        $g = $repRentas['graf'];
        $topU = array_slice($g['equipos'], 0, 8);
        $topI = $g['equipos'];
        usort($topI, fn ($a, $b) => $b['ingreso'] <=> $a['ingreso']);
        $topI = array_slice($topI, 0, 8);
        $topC = array_slice($g['clientes_all'], 0, 8);
        $maxU = max(1, collect($topU)->max('unidades') ?? 1);
        $maxI = max(1, collect($topI)->max('ingreso') ?? 1);
        $maxC = max(1, collect($topC)->max('rentas') ?? 1);
        $maxA = max(1, max($g['aging_all']) ?: 1);
    @endphp

    <!-- ANÁLISIS: EQUIPOS Y CLIENTES -->
    <div class="section-header">Equipos Más Rentados y Clientes Frecuentes</div>
    <table>
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 6px;">
                <table class="data-table">
                    <thead><tr><th style="width: 38%;">Equipo (por unidades)</th><th style="width: 40%;">&nbsp;</th><th class="r" style="width: 22%;">Unidades</th></tr></thead>
                    <tbody>
                        @forelse($topU as $e)
                            <tr>
                                <td class="nowrap">{{ \Illuminate\Support\Str::limit($e['nombre'], 26) }}</td>
                                <td><div class="bar-bg"><div class="bar-blue" style="width: {{ round($e['unidades'] / $maxU * 100) }}%;"></div></div></td>
                                <td class="r mono">{{ $e['unidades'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c" style="color: #94a3b8;">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 6px;">
                <table class="data-table">
                    <thead><tr><th style="width: 38%;">Equipo (por ingreso)</th><th style="width: 32%;">&nbsp;</th><th class="r" style="width: 30%;">Importe</th></tr></thead>
                    <tbody>
                        @forelse($topI as $e)
                            <tr>
                                <td class="nowrap">{{ \Illuminate\Support\Str::limit($e['nombre'], 26) }}</td>
                                <td><div class="bar-bg"><div class="bar-green" style="width: {{ round($e['ingreso'] / $maxI * 100) }}%;"></div></div></td>
                                <td class="r mono nowrap">${{ number_format($e['ingreso'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="c" style="color: #94a3b8;">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
        </tr>
        <tr>
            <td style="width: 50%; vertical-align: top; padding-right: 6px; padding-top: 6px;">
                <table class="data-table">
                    <thead><tr><th style="width: 34%;">Cliente frecuente</th><th style="width: 26%;">&nbsp;</th><th class="c" style="width: 12%;">Rentas</th><th class="r" style="width: 28%;">Contratado</th></tr></thead>
                    <tbody>
                        @forelse($topC as $c)
                            <tr>
                                <td class="nowrap">{{ \Illuminate\Support\Str::limit($c['cliente'], 22) }}</td>
                                <td><div class="bar-bg"><div class="bar-cyan" style="width: {{ round($c['rentas'] / $maxC * 100) }}%;"></div></div></td>
                                <td class="c mono">{{ $c['rentas'] }}</td>
                                <td class="r mono nowrap">${{ number_format($c['total'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="c" style="color: #94a3b8;">Sin datos.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </td>
            <td style="width: 50%; vertical-align: top; padding-left: 6px; padding-top: 6px;">
                <table class="data-table">
                    <thead><tr><th style="width: 38%;">Antigüedad de cartera</th><th style="width: 32%;">&nbsp;</th><th class="r" style="width: 30%;">Saldo</th></tr></thead>
                    <tbody>
                        @foreach($g['aging_all'] as $nombre => $monto)
                            <tr>
                                <td class="nowrap">{{ $nombre }}</td>
                                <td><div class="bar-bg"><div class="bar-red" style="width: {{ round($monto / $maxA * 100) }}%;"></div></div></td>
                                <td class="r mono nowrap">${{ number_format($monto, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <!-- RENTAS DEL PERÍODO -->
    <div class="section-header">Rentas Contratadas en el Período</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Folio</th><th>Fecha</th><th>Cliente</th><th class="c">Estado</th><th class="c">Pago</th><th>Fin</th>
                <th class="r">Subtotal</th><th class="r">Desc.</th><th class="r">IVA</th><th class="r">Cargos</th>
                <th class="r">Total</th><th class="r">Depósito</th><th class="r">Pagado</th><th class="r">Saldo</th>
            </tr>
        </thead>
        <tbody>
            @forelse($repRentas['rows'] as $r)
                <tr>
                    <td class="mono nowrap" style="color: #0284c7;">{{ $r['folio'] }}</td>
                    <td class="nowrap">{{ $r['fecha']->format('d/m/Y') }}</td>
                    <td class="nowrap">{{ \Illuminate\Support\Str::limit($r['cliente'], 24) }}</td>
                    <td class="c"><span class="tag {{ $tagEstado[$r['estado']] ?? '' }}">{{ strtoupper($r['estado']) }}</span></td>
                    <td class="c"><span class="tag {{ $tagPago[$r['estado_pago']] ?? '' }}">{{ strtoupper($r['estado_pago']) }}</span></td>
                    <td class="nowrap">{{ $r['fin'] ? $r['fin']->format('d/m/Y') : '—' }}</td>
                    <td class="r mono nowrap">${{ number_format($r['subtotal'], 2) }}</td>
                    <td class="r mono nowrap">{{ $r['descuento'] > 0 ? '-$' . number_format($r['descuento'], 2) : '—' }}</td>
                    <td class="r mono nowrap">${{ number_format($r['iva'], 2) }}</td>
                    <td class="r mono nowrap">{{ $r['cargos_extra'] > 0 ? '$' . number_format($r['cargos_extra'], 2) : '—' }}</td>
                    <td class="r mono nowrap">${{ number_format($r['total'], 2) }}</td>
                    <td class="r mono nowrap">${{ number_format($r['deposito'], 2) }}</td>
                    <td class="r mono nowrap">${{ number_format($r['pagado'], 2) }}</td>
                    <td class="r mono nowrap" style="color: {{ $r['saldo'] > 0 ? '#dc2626' : '#15803d' }};">${{ number_format($r['saldo'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="14" class="c" style="color: #94a3b8; padding: 8px;">No se contrataron rentas en el período seleccionado.</td></tr>
            @endforelse
        </tbody>
        @if(count($repRentas['rows']))
        <tfoot>
            <tr>
                <td colspan="6" class="r">TOTAL</td>
                <td class="r mono nowrap">${{ number_format($rk['subtotal'], 2) }}</td>
                <td class="r mono nowrap">-${{ number_format($rk['descuentos'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['iva'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['cargos_extra'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['total'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['depositos'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['pagado'], 2) }}</td>
                <td class="r mono nowrap">${{ number_format($rk['saldo'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <!-- PAGOS DEL PERÍODO -->
    <div class="section-header">Pagos de Rentas Recibidos en el Período</div>
    <table class="data-table">
        <thead>
            <tr><th>Fecha</th><th>Renta</th><th>Cliente</th><th>Tipo</th><th>Método</th><th>Referencia</th><th class="r">Monto</th></tr>
        </thead>
        <tbody>
            @forelse($rco['rows'] as $p)
                <tr>
                    <td class="nowrap">{{ $p['fecha']->format('d/m/Y H:i') }}</td>
                    <td class="mono nowrap" style="color: #0284c7;">{{ $p['folio'] }}</td>
                    <td class="nowrap">{{ \Illuminate\Support\Str::limit($p['cliente'], 30) }}</td>
                    <td>{{ $p['tipo'] }}</td>
                    <td>{{ ucfirst($p['metodo']) }}</td>
                    <td>{{ $p['referencia'] ?: '—' }}</td>
                    <td class="r mono nowrap">${{ number_format($p['monto'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="c" style="color: #94a3b8; padding: 8px;">No hubo pagos de rentas en el período.</td></tr>
            @endforelse
        </tbody>
        @if(count($rco['rows']))
        <tfoot><tr><td colspan="6" class="r">TOTAL PAGOS</td><td class="r mono nowrap">${{ number_format($rco['pagos_monto'], 2) }}</td></tr></tfoot>
        @endif
    </table>

    <!-- CARTERA -->
    <div class="section-header">Cartera de Rentas con Saldo Pendiente (a la fecha de generación)</div>
    <table class="data-table">
        <thead>
            <tr><th>Folio</th><th>Cliente</th><th class="c">Estado</th><th>Fin de renta</th><th class="c">Retraso</th><th class="r">Total</th><th class="r">Abonado</th><th class="r">Saldo</th><th class="r">Retraso est.</th></tr>
        </thead>
        <tbody>
            @forelse($rca['rows'] as $r)
                <tr>
                    <td class="mono nowrap" style="color: #0284c7;">{{ $r['folio'] }}</td>
                    <td class="nowrap">{{ \Illuminate\Support\Str::limit($r['cliente'], 30) }}</td>
                    <td class="c"><span class="tag {{ $tagEstado[$r['estado']] ?? '' }}">{{ strtoupper($r['estado']) }}</span></td>
                    <td class="nowrap">{{ $r['fin'] ? $r['fin']->format('d/m/Y') : '—' }}</td>
                    <td class="c">{{ $r['dias_retraso'] > 0 ? $r['dias_retraso'] . ' d' : '—' }}</td>
                    <td class="r mono nowrap">${{ number_format($r['total'], 2) }}</td>
                    <td class="r mono nowrap">${{ number_format($r['deposito'] + $r['pagado'], 2) }}</td>
                    <td class="r mono nowrap" style="color: #dc2626;">${{ number_format($r['saldo'], 2) }}</td>
                    <td class="r mono nowrap">{{ $r['multa'] > 0 ? '$' . number_format($r['multa'], 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="9" class="c" style="color: #94a3b8; padding: 8px;">No hay rentas con saldo pendiente.</td></tr>
            @endforelse
        </tbody>
        @if(count($rca['rows']))
        <tfoot><tr><td colspan="7" class="r">TOTAL POR COBRAR</td><td class="r mono nowrap">${{ number_format($rca['por_cobrar'], 2) }}</td><td class="r mono nowrap">${{ number_format($rca['multa'], 2) }}</td></tr></tfoot>
        @endif
    </table>

    <div style="margin-top: 8px; font-size: 6.5pt; color: #94a3b8;">
        El depósito en garantía se cuenta como abonado. Las rentas canceladas no entran en ningún total. El saldo no incluye el retraso estimado, que se muestra por separado.
    </div>

    <div class="footer">
        <table>
            <tr>
                <td style="width: 60%;">Estado Financiero de Rentas — <strong>{{ $nombreEmpresa }}</strong> | {{ $sucursalNombre ?? 'Matriz General' }}</td>
                <td style="width: 40%; text-align: right;">Generado el {{ date('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
    </div>
</body>
</html>