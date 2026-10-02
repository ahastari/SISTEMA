@extends('layouts.admin')

@section('content')
<style>
    .dashboard-header { 
        border-bottom: 1px solid var(--bs-border-color); 
        padding-bottom: 18px; 
        margin-bottom: 20px; 
    }
    .font-mono {
        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace;
    }
    .kpi-card { 
        background: var(--bs-body-bg); 
        border-radius: 12px; 
        padding: 16px 20px; 
        border: 1px solid var(--bs-border-color); 
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .kpi-title { 
        font-size: 10.5px; 
        font-weight: 700; 
        color: var(--bs-secondary-color); 
        text-transform: uppercase; 
        letter-spacing: 0.6px; 
    }
    .kpi-value { 
        font-size: 22px; 
        font-weight: 700; 
        margin-top: 4px; 
        margin-bottom: 0; 
    }
    .chart-card { 
        background: var(--bs-body-bg); 
        border-radius: 14px; 
        padding: 20px; 
        border: 1px solid var(--bs-border-color); 
        min-height: 320px;
    }
    .chart-title { 
        font-size: 11.5px; 
        font-weight: 700; 
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--bs-secondary-color); 
        margin-bottom: 15px; 
        display: flex; 
        align-items: center; 
        justify-content: space-between;
    }
    .filter-card {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        padding: 14px 18px;
    }
    .table-executive { font-size: 12.5px; }
    .table-executive th {
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--bs-secondary-color);
        background: var(--bs-tertiary-bg) !important;
        border-bottom: 1px solid var(--bs-border-color);
    }
</style>

<div class="container-fluid p-0 py-1">
    
    <!-- HEADER -->
    <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-body">
                <i class="bi bi-graph-up-arrow text-primary me-2"></i>Dashboard & Auditoría Financiera
            </h4>
            <p class="text-secondary small mb-0">Análisis consolidados de ingresos, margen de operación y rendimiento comercial</p>
        </div>
        <div>
            <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">
                <i class="bi bi-arrow-left me-1"></i> Regresar al POS
            </a>
        </div>
    </div>

    @php
        $tabActiva = request('tab') === 'rentas' ? 'rentas' : 'pos';
        $rawInicio = request('fecha_inicio', date('Y-m-01'));
        $rawFin = request('fecha_fin', date('Y-m-d'));

        $inicio = \Carbon\Carbon::parse($rawInicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($rawFin)->endOfDay();

        $sucursalId = session('activo_sucursal_id');
        $user = auth()->user();
        $isGlobalAdmin = $user->isAdmin() && $sucursalId === 'global';

        // 1. CONSULTA DE VENTAS
        $queryVentas = \App\Models\Venta::with(['detalles.equipo'])
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$inicio, $fin]);

        if (!$isGlobalAdmin) {
            $queryVentas->where('sucursal_id', $sucursalId);
        }

        $ventasPeriodo = $queryVentas->get();
        $facturacionTotal = $ventasPeriodo->sum('total');

        $montoFletes = 0;
        $montoManoObra = 0;
        $costoProduccionTotal = 0;

        foreach($ventasPeriodo as $v) {
            foreach($v->detalles as $d) {
                if(str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                    $montoFletes += $d->subtotal;
                } elseif(str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                    $montoManoObra += $d->subtotal;
                } else {
                    $costoUnitario = $d->costo ?? ($d->equipo->costo ?? 0);
                    $costoProduccionTotal += ($costoUnitario * $d->cantidad);
                }
            }
        }

        $utilidadBruta = $facturacionTotal - $costoProduccionTotal;

        // 2. 🔥 DEFINICIÓN DE LA VARIABLE $descuadresPeriodo (RESUELVE EL ERROR)
        $qCortes = \App\Models\CorteCaja::where('diferencia', '<', 0)
            ->whereBetween('fecha_apertura', [$inicio, $fin]);

        if (!$isGlobalAdmin && \Illuminate\Support\Facades\Schema::hasColumn('cortes_caja', 'sucursal_id')) {
            $qCortes->where('sucursal_id', $sucursalId);
        }

        $descuadresPeriodo = abs($qCortes->sum('diferencia') ?? 0);
    @endphp
    <!-- FILTRO DE FECHAS CON AUTO-SUBMIT Y VISTA EN DD/MM/YYYY -->
    <div class="filter-card mb-4 shadow-sm">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <form id="formFiltroFechas" action="{{ route('puntoventa.reportes') }}" method="GET" class="row g-2 align-items-end">
                    <input type="hidden" name="tab" id="tabInput" value="{{ $tabActiva }}">
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Fecha Inicial (Desde)</label>
                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm bg-body text-body font-mono" value="{{ $inicio->format('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-sm-6">
                        <label class="form-label small fw-bold text-secondary mb-1">Fecha Final (Hasta)</label>
                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm bg-body text-body font-mono" value="{{ $fin->format('Y-m-d') }}" required>
                    </div>
                </form>
            </div>

            <!-- EXPORTAR PDF / EXCEL CON EL RANGO SELECCIONADO -->
            <div id="exportPos" class="col-12 col-md-4 d-flex justify-content-md-end align-items-end gap-2 {{ $tabActiva === 'pos' ? '' : 'd-none' }}">
                <form action="{{ route('puntoventa.generarReporte') }}" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="tipo" value="personalizado">
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-dark btn-sm rounded-3 fw-bold px-3 w-100">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> PDF
                    </button>
                </form>
                <form action="{{ route('puntoventa.exportarExcel') }}" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-success btn-sm rounded-3 fw-bold px-3 w-100">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel
                    </button>
                </form>
            </div>

            <!-- EXPORTAR PDF / EXCEL DE RENTAS CON EL RANGO SELECCIONADO -->
            <div id="exportRentas" class="col-12 col-md-4 d-flex justify-content-md-end align-items-end gap-2 {{ $tabActiva === 'rentas' ? '' : 'd-none' }}">
                <form action="{{ route('puntoventa.reporteRentas') }}" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-dark btn-sm rounded-3 fw-bold px-3 w-100">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> PDF Rentas
                    </button>
                </form>
                <form action="{{ route('puntoventa.exportarExcelRentas') }}" method="POST" class="flex-fill">
                    @csrf
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-success btn-sm rounded-3 fw-bold px-3 w-100">
                        <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel Rentas
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS -->
    <ul class="nav nav-tabs mb-4" id="reportesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold {{ $tabActiva === 'pos' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#panePos" data-tab="pos" type="button" role="tab">
                <i class="bi bi-shop me-1"></i> Ventas POS
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold {{ $tabActiva === 'rentas' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#paneRentas" data-tab="rentas" type="button" role="tab">
                <i class="bi bi-calendar2-range me-1"></i> Finanzas de Rentas
            </button>
        </li>
    </ul>

    <div class="tab-content">
    <div class="tab-pane fade {{ $tabActiva === 'pos' ? 'show active' : '' }}" id="panePos" role="tabpanel" tabindex="0">

    <!-- METRICAS CLAVE (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">TOTAL VENDIDO</span>
                    <i class="bi bi-currency-dollar text-primary fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-primary">${{ number_format($facturacionTotal, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Del {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</small>
            </div>
        </div>

        <!-- COSTO DE PRODUCCIÓN / ADQUISICIÓN -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-warning shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Costo Producción</span>
                    <i class="bi bi-box-seam-fill text-warning fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-warning">${{ number_format($costoProduccionTotal, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Costo base de mercancía</small>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-info shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Cobro Flete / Mano Obra</span>
                    <i class="bi bi-truck text-info fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-info">${{ number_format($montoFletes + $montoManoObra, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Flete: ${{ number_format($montoFletes, 2) }} | MO: ${{ number_format($montoManoObra, 2) }}</small>
            </div>
        </div>

        <!-- <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-success shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Utilidad Operativa Est.</span>
                    <i class="bi bi-pie-chart text-success fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-success">${{ number_format($facturacionTotal * 0.40, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Margen proyectado al 40%</small>
            </div>
        </div> -->

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-danger shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Descuadres de Caja</span>
                    <i class="bi bi-shield-slash text-danger fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-danger">${{ number_format($descuadresPeriodo, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Faltantes acumulados en arqueos</small>
            </div>
        </div>
    </div>


    <!-- KPIs: DESCUENTOS Y CRÉDITOS -->
    @php
        $rd = $rep['desc'];
        $rc = $rep['cred'];
        $cartera = $rc['cartera'];
    @endphp
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-success shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Descuentos Otorgados</span>
                    <i class="bi bi-tag-fill text-success fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-success">${{ number_format($rd['total'], 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Ventas: ${{ number_format($rd['ventas_monto'], 2) }} ({{ $rd['ventas_n'] }}) | Rentas: ${{ number_format($rd['rentas_monto'], 2) }} ({{ $rd['rentas_n'] }})</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-warning shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Créditos Otorgados</span>
                    <i class="bi bi-credit-card-2-front text-warning fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-warning">${{ number_format($rc['otorgado_monto'], 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">{{ $rc['otorgados_n'] }} ventas a crédito en el período</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Abonos Recibidos</span>
                    <i class="bi bi-cash-coin text-primary fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-primary">${{ number_format($rc['abonos_monto'], 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Efe: ${{ number_format($rc['abonos_efectivo'], 2) }} | Transf: ${{ number_format($rc['abonos_transferencia'], 2) }} | Tarj: ${{ number_format($rc['abonos_tarjeta'], 2) }}</small>
                @if($rc['abonos_mixto_n'] > 0)
                    <br><small class="text-secondary" style="font-size: 11px;"><i class="bi bi-diagram-3 me-1"></i>Incluye ${{ number_format($rc['abonos_mixto'], 2) }} en {{ $rc['abonos_mixto_n'] }} abono(s) pagados en mixto</small>
                @endif
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-danger shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Cartera por Cobrar</span>
                    <i class="bi bi-hourglass-split text-danger fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-danger">${{ number_format($cartera['por_cobrar'], 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Vencido: ${{ number_format($cartera['vencido'], 2) }} ({{ $cartera['n_vencidos'] }}) | Abiertos: {{ $cartera['n_abiertos'] }}</small>
            </div>
        </div>
    </div>

    <!-- TABLA DESGLOSE CANALES -->
    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
        <div class="card-header bg-body border-bottom py-3">
            <h6 class="fw-bold mb-0 text-body small text-uppercase tracking-wider">
                <i class="bi bi-wallet2 text-primary me-2"></i>Desglose de Recaudación por Canal de Pago
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-executive mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">Método / Canal</th>
                            <th class="text-center">Transacciones</th>
                            <th class="text-end">Monto Bruto Ingresado</th>
                            <!-- <th class="pe-3 text-end">% Participación</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $efeTotal = $ventasPeriodo->where('metodo_pago', 'efectivo')->sum('total');
                            $tarTotal = $ventasPeriodo->where('metodo_pago', 'tarjeta')->sum('total');
                            $traTotal = $ventasPeriodo->where('metodo_pago', 'transferencia')->sum('total');
                            $mixTotal = $ventasPeriodo->where('metodo_pago', 'mixto')->sum('total');
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-cash text-success me-2"></i>Efectivo</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'efectivo')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($efeTotal, 2) }}</td>
                            <!-- <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($efeTotal / $facturacionTotal) * 100, 1) : 0 }}%</td> -->
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-credit-card text-primary me-2"></i>Terminal</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'tarjeta')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($tarTotal, 2) }}</td>
                            <!-- <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($tarTotal / $facturacionTotal) * 100, 1) : 0 }}%</td> -->
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-bank text-info me-2"></i>Transferencia</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'transferencia')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($traTotal, 2) }}</td>
                            <!-- <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($traTotal / $facturacionTotal) * 100, 1) : 0 }}%</td> -->
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-diagram-3 text-warning me-2"></i>Pago Mixto Combinado</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'mixto')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($mixTotal, 2) }}</td>
                            <!-- <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($mixTotal / $facturacionTotal) * 100, 1) : 0 }}%</td> -->
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-credit-card-2-front text-warning me-2"></i>A Crédito <small class="text-secondary">(por cobrar)</small></td>
                            <td class="text-center font-mono">{{ $rep['canales']['credito']['n'] }}</td>
                            <td class="text-end font-mono fw-bold text-warning-emphasis">${{ number_format($rep['canales']['credito']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-cash-coin text-primary me-2"></i>Abonos de crédito recibidos</td>
                            <td class="text-center font-mono">{{ $rc['abonos_n'] }}</td>
                            <td class="text-end font-mono fw-bold text-primary">${{ number_format($rc['abonos_monto'], 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <td class="ps-3 fw-bold">Cobranza real del período <small class="text-secondary fw-normal">(ventas sin crédito + abonos)</small></td>
                            <td></td>
                            <td class="text-end font-mono fw-bold text-success">${{ number_format($rc['cobranza_real'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DESCUENTOS OTORGADOS -->
    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
        <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-tag-fill text-success me-2"></i>Descuentos Otorgados (Ventas y Rentas)</h6>
            <span class="badge bg-success-subtle text-success border border-success-subtle">{{ number_format($rd['pct_ventas'], 1) }}% del subtotal bruto vendido</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 380px;">
                <table class="table table-hover table-executive mb-0 align-middle">
                    <thead class="sticky-top">
                        <tr>
                            <th class="ps-3">Tipo</th><th>Folio</th><th>Fecha</th><th>Cliente</th><th>Sucursal</th>
                            <th class="text-end">Subtotal</th><th class="text-end">Descuento</th><th class="text-end">%</th>
                            <th class="text-end">Total</th><th>Autorizó</th><th class="pe-3">Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rd['rows'] as $r)
                            <tr>
                                <td class="ps-3"><span class="badge {{ $r['tipo'] === 'Renta' ? 'bg-info-subtle text-info-emphasis border border-info-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">{{ $r['tipo'] }}</span></td>
                                <td class="font-mono">{{ $r['folio'] }}</td>
                                <td class="text-secondary">{{ $r['fecha']->format('d/m/Y H:i') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($r['cliente'], 28) }}</td>
                                <td class="text-secondary">{{ $r['sucursal'] }}</td>
                                <td class="text-end font-mono">${{ number_format($r['subtotal'], 2) }}</td>
                                <td class="text-end font-mono fw-bold text-success">-${{ number_format($r['descuento'], 2) }}</td>
                                <td class="text-end font-mono text-secondary">{{ number_format($r['pct'], 1) }}%</td>
                                <td class="text-end font-mono">${{ number_format($r['total'], 2) }}</td>
                                <td>{{ $r['autorizo'] }}</td>
                                <td class="pe-3 text-secondary" style="max-width: 220px;">{{ $r['motivo'] ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center text-secondary py-4">Sin descuentos en el período seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($rd['rows']))
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td colspan="6" class="ps-3 text-end">TOTAL DESCUENTOS</td>
                            <td class="text-end font-mono text-success">-${{ number_format($rd['total'], 2) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- CRÉDITOS OTORGADOS EN EL PERÍODO -->
    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
        <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-credit-card-2-front text-warning me-2"></i>Créditos Otorgados en el Período</h6>
            <a href="{{ route('puntoventa.creditos') }}" class="btn btn-sm btn-outline-warning fw-bold rounded-pill px-3">Ir a la cartera <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 380px;">
                <table class="table table-hover table-executive mb-0 align-middle">
                    <thead class="sticky-top">
                        <tr>
                            <th class="ps-3">Folio</th><th>Fecha</th><th>Cliente</th><th>Sucursal</th>
                            <th class="text-center">Plazo</th><th>Vence</th>
                            <th class="text-end">Monto</th><th class="text-end">Abonado</th><th class="text-end">Saldo</th><th class="pe-3 text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rc['rows'] as $r)
                            @php
                                $cls = ['Liquidado' => 'bg-success-subtle text-success border-success-subtle', 'Vencido' => 'bg-danger-subtle text-danger border-danger-subtle', 'Con abonos' => 'bg-info-subtle text-info-emphasis border-info-subtle', 'Pendiente' => 'bg-secondary-subtle text-secondary border-secondary-subtle'][$r['estado']] ?? '';
                            @endphp
                            <tr>
                                <td class="ps-3 font-mono">{{ $r['folio'] }}</td>
                                <td class="text-secondary">{{ $r['fecha']->format('d/m/Y') }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($r['cliente'], 28) }}</td>
                                <td class="text-secondary">{{ $r['sucursal'] }}</td>
                                <td class="text-center font-mono">{{ $r['dias'] }} d</td>
                                <td class="text-secondary">{{ $r['vence']->format('d/m/Y') }}</td>
                                <td class="text-end font-mono">${{ number_format($r['total'], 2) }}</td>
                                <td class="text-end font-mono text-success">${{ number_format($r['abonado'], 2) }}</td>
                                <td class="text-end font-mono fw-bold text-warning-emphasis">${{ number_format($r['saldo'], 2) }}</td>
                                <td class="pe-3 text-center"><span class="badge rounded-pill border {{ $cls }}">{{ $r['estado'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-secondary py-4">No se otorgaron créditos en el período.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ABONOS RECIBIDOS EN EL PERÍODO -->
    <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
        <div class="card-header bg-body border-bottom py-3">
            <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-cash-coin text-primary me-2"></i>Abonos de Crédito Recibidos en el Período</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 380px;">
                <table class="table table-hover table-executive mb-0 align-middle">
                    <thead class="sticky-top">
                        <tr><th class="ps-3">Fecha</th><th>Crédito</th><th>Cliente</th><th>Método</th><th>Referencia</th><th>Recibió</th><th class="text-end pe-3">Monto</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rc['abonos'] as $a)
                            <tr>
                                <td class="ps-3 text-secondary">{{ $a['fecha']->format('d/m/Y H:i') }}</td>
                                <td class="font-mono">{{ $a['folio'] }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($a['cliente'], 28) }}</td>
                                <td class="text-capitalize">{{ $a['metodo'] }}</td>
                                <td class="text-secondary">{{ $a['referencia'] ?: '—' }}</td>
                                <td>{{ $a['recibio'] }}</td>
                                <td class="text-end font-mono fw-bold text-success pe-3">${{ number_format($a['monto'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-secondary py-4">No hubo abonos en el período.</td></tr>
                        @endforelse
                    </tbody>
                    @if(count($rc['abonos']))
                    <tfoot>
                        <tr class="fw-bold border-top">
                            <td colspan="6" class="ps-3 text-end">TOTAL ABONADO</td>
                            <td class="text-end font-mono text-success pe-3">${{ number_format($rc['abonos_monto'], 2) }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- SECCIÓN DE GRÁFICAS APEXCHARTS -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-lightning-fill text-warning me-1"></i> Flujo Diario en Rango</span>
                    <span class="badge bg-body-tertiary text-secondary border font-mono">Diario</span>
                </div>
                <div id="chartVentasDia"></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-calendar3 text-primary me-1"></i> Histórico Anual por Mes</span>
                    <span class="badge bg-body-tertiary text-secondary border font-mono">{{ date('Y') }}</span>
                </div>
                <div id="chartVentasMes"></div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-box-seam-fill text-success me-1"></i> Artículos Más Vendidos en Rango</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Top Comercial</span>
                </div>
                <div id="chartProductosReales"></div>
            </div>
        </div>
    </div>


    <!-- ANÁLISIS GRÁFICO ADICIONAL DEL POS -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-8">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-graph-up text-primary me-1"></i> Vendido vs Cobranza Real</span>
                    <span class="badge bg-body-tertiary text-secondary border font-mono">{{ $rep['graf']['flujo']['por_mes'] ? 'Mensual' : 'Diario' }}</span>
                </div>
                <div id="chPosFlujo"></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-card shadow-sm">
                <div class="chart-title"><span><i class="bi bi-pie-chart-fill text-success me-1"></i> Ventas por Canal de Pago</span></div>
                <div id="chPosCanales"></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-currency-dollar text-success me-1"></i> Artículos que Más Ingresan</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle">Importe</span>
                </div>
                <div id="chPosIngreso"></div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-people-fill text-info me-1"></i> Clientes Frecuentes</span>
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Compras en el período</span>
                </div>
                <div id="chPosClientes"></div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-clock-fill text-warning me-1"></i> Ventas por Hora del Día</span>
                    <span class="badge bg-body-tertiary text-secondary border">Horas pico</span>
                </div>
                <div id="chPosHoras"></div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="chart-card shadow-sm">
                <div class="chart-title"><span><i class="bi bi-diagram-3 text-primary me-1"></i> Composición de Ingresos</span></div>
                <div id="chPosComposicion"></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-calendar-week text-primary me-1"></i> Ventas por Día de la Semana</span>
                </div>
                <div id="chPosDias"></div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="chart-card shadow-sm">
                <div class="chart-title">
                    <span><i class="bi bi-hourglass-bottom text-danger me-1"></i> Antigüedad de Créditos</span>
                    <span class="badge bg-body-tertiary text-secondary border">Otorgados en el período</span>
                </div>
                <div id="chPosAging"></div>
            </div>
        </div>
    </div>

    </div><!-- /panePos -->

    <!-- ============================================================ -->
    <!-- PESTAÑA: FINANZAS DE RENTAS                                    -->
    <!-- ============================================================ -->
    <div class="tab-pane fade {{ $tabActiva === 'rentas' ? 'show active' : '' }}" id="paneRentas" role="tabpanel" tabindex="0">
        @php
            $rr  = $repRentas;
            $rk  = $rr['kpi'];
            $rco = $rr['cobro'];
            $rca = $rr['cartera'];
            $estadoCls = [
                'Activa' => 'bg-primary-subtle text-primary border-primary-subtle',
                'Vencida' => 'bg-danger-subtle text-danger border-danger-subtle',
                'Finalizada' => 'bg-secondary-subtle text-secondary border-secondary-subtle',
            ];
            $pagoCls = [
                'Liquidado' => 'bg-success-subtle text-success border-success-subtle',
                'Con abonos' => 'bg-info-subtle text-info-emphasis border-info-subtle',
                'Pendiente' => 'bg-warning-subtle text-warning-emphasis border-warning-subtle',
            ];
        @endphp

        <!-- KPIs: CONTRATACIÓN Y COBRANZA -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-primary shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Rentas Contratadas</span>
                        <i class="bi bi-receipt text-primary fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-primary">${{ number_format($rk['total'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">{{ $rk['n'] }} rentas @if($rk['canceladas_n'] > 0)· {{ $rk['canceladas_n'] }} cancelada(s) excluida(s)@endif</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Cobranza del Período</span>
                        <i class="bi bi-cash-coin text-success fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-success">${{ number_format($rco['total'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Pagos: ${{ number_format($rco['pagos_monto'], 2) }} | Depósitos: ${{ number_format($rco['depositos'], 2) }}</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-warning shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Saldo de Rentas del Período</span>
                        <i class="bi bi-hourglass-split text-warning fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-warning">${{ number_format($rk['saldo'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Pagado a la fecha: ${{ number_format($rk['pagado'] + $rk['depositos'], 2) }}</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-danger shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Cartera por Cobrar (a hoy)</span>
                        <i class="bi bi-exclamation-triangle text-danger fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-danger">${{ number_format($rca['por_cobrar'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Vencido: ${{ number_format($rca['vencido'], 2) }} ({{ $rca['n_vencidas'] }}) | Abiertas: {{ $rca['n_abiertas'] }}</small>
                </div>
            </div>
        </div>

        <!-- KPIs: COMPONENTES DEL COBRO -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-success shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Descuentos Otorgados</span>
                        <i class="bi bi-tag-fill text-success fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-success">${{ number_format($rk['descuentos'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">{{ $rk['descuentos_n'] }} renta(s) con descuento | Subtotal bruto: ${{ number_format($rk['subtotal'], 2) }}</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-info shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Cobro Flete / Mano Obra</span>
                        <i class="bi bi-truck text-info fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-info">${{ number_format($rk['fletes'] + $rk['mano_obra'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Flete: ${{ number_format($rk['fletes'], 2) }} | MO: ${{ number_format($rk['mano_obra'], 2) }}</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-danger shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Cargos Extra</span>
                        <i class="bi bi-wrench-adjustable text-danger fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-danger">${{ number_format($rk['cargos_extra'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Multas, daños y faltantes | IVA: ${{ number_format($rk['iva'], 2) }}</small>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="kpi-card border-start border-4 border-warning shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="kpi-title">Retraso Estimado sin Cargar</span>
                        <i class="bi bi-clock-history text-warning fs-5"></i>
                    </div>
                    <h3 class="kpi-value font-mono text-warning">${{ number_format($rca['multa'], 2) }}</h3>
                    <small class="text-secondary" style="font-size: 11px;">Días extra de rentas activas vencidas</small>
                </div>
            </div>
        </div>

        <!-- ANÁLISIS GRÁFICO DE RENTAS -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-lg-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-graph-up text-primary me-1"></i> Contratado vs Cobrado</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">{{ $rr['graf']['flujo']['por_mes'] ? 'Mensual' : 'Diario' }}</span>
                    </div>
                    <div id="chRentasFlujo"></div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-pie-chart-fill text-success me-1"></i> Cobranza por Método</span></div>
                    <div id="chRentasMetodos"></div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-box-seam-fill text-primary me-1"></i> Equipos Más Rentados</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Unidades</span>
                    </div>
                    <div id="chRentasEquiposU"></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-currency-dollar text-success me-1"></i> Equipos que Más Ingresan</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Importe</span>
                    </div>
                    <div id="chRentasEquiposI"></div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-people-fill text-info me-1"></i> Clientes Frecuentes</span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle">Rentas en el período</span>
                    </div>
                    <div id="chRentasClientes"></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-hourglass-bottom text-danger me-1"></i> Antigüedad de la Cartera</span>
                        <span class="badge bg-body-tertiary text-secondary border">A la fecha</span>
                    </div>
                    <div id="chRentasAging"></div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-calendar3 text-primary me-1"></i> Histórico Anual por Mes</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">{{ $rr['graf']['mensual']['anio'] }}</span>
                    </div>
                    <div id="chRentasMensual"></div>
                </div>
            </div>
            <div class="col-12 col-lg-4">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-diagram-3 text-warning me-1"></i> Rentas por Estado</span></div>
                    <div id="chRentasEstados"></div>
                </div>
            </div>
        </div>

        <!-- DESGLOSE DE COBRANZA -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body border-bottom py-3">
                <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-wallet2 text-primary me-2"></i>Cobranza de Rentas por Método y Tipo de Pago</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-executive mb-0 align-middle">
                        <thead>
                            <tr>
                                <th class="ps-3">Concepto</th>
                                <th class="text-center">Transacciones</th>
                                <th class="text-end pe-3">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="ps-3 fw-semibold"><i class="bi bi-cash text-success me-2"></i>Efectivo</td>
                                <td class="text-center font-mono">{{ $rco['por_metodo']['efectivo']['n'] }}</td>
                                <td class="text-end font-mono fw-bold pe-3">${{ number_format($rco['por_metodo']['efectivo']['total'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-semibold"><i class="bi bi-bank text-info me-2"></i>Transferencia</td>
                                <td class="text-center font-mono">{{ $rco['por_metodo']['transferencia']['n'] }}</td>
                                <td class="text-end font-mono fw-bold pe-3">${{ number_format($rco['por_metodo']['transferencia']['total'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-semibold"><i class="bi bi-credit-card text-primary me-2"></i>Tarjeta / Terminal</td>
                                <td class="text-center font-mono">{{ $rco['por_metodo']['tarjeta']['n'] }}</td>
                                <td class="text-end font-mono fw-bold pe-3">${{ number_format($rco['por_metodo']['tarjeta']['total'], 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="ps-3 fw-bold">Pagos recibidos en el período</td>
                                <td class="text-center font-mono fw-bold">{{ $rco['n'] }}</td>
                                <td class="text-end font-mono fw-bold text-success pe-3">${{ number_format($rco['pagos_monto'], 2) }}</td>
                            </tr>
                            <tr>
                                <td class="ps-3 fw-semibold"><i class="bi bi-shield-check text-secondary me-2"></i>Depósitos en garantía <small class="text-secondary">(rentas contratadas en el período)</small></td>
                                <td></td>
                                <td class="text-end font-mono fw-bold pe-3">${{ number_format($rco['depositos'], 2) }}</td>
                            </tr>
                            <tr class="border-top">
                                <td class="ps-3 fw-bold">Cobranza total del período</td>
                                <td></td>
                                <td class="text-end font-mono fw-bold text-success pe-3">${{ number_format($rco['total'], 2) }}</td>
                            </tr>
                            @foreach($rco['por_tipo'] as $t)
                            <tr class="text-secondary">
                                <td class="ps-4"><small>· {{ $t['texto'] }}</small></td>
                                <td class="text-center font-mono"><small>{{ $t['n'] }}</small></td>
                                <td class="text-end font-mono pe-3"><small>${{ number_format($t['total'], 2) }}</small></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- RENTAS DEL PERÍODO -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-receipt text-primary me-2"></i>Rentas Contratadas en el Período</h6>
                <a href="{{ route('rentas.index') }}" class="btn btn-sm btn-outline-primary fw-bold rounded-pill px-3">Ir a rentas <i class="bi bi-arrow-right"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 460px;">
                    <table class="table table-hover table-executive mb-0 align-middle">
                        <thead class="sticky-top">
                            <tr>
                                <th class="ps-3">Folio</th><th>Fecha</th><th>Cliente</th><th>Sucursal</th>
                                <th class="text-center">Estado</th><th class="text-center">Pago</th><th>Fin</th>
                                <th class="text-end">Subtotal</th><th class="text-end">Desc.</th><th class="text-end">IVA</th><th class="text-end">Cargos</th>
                                <th class="text-end">Total</th><th class="text-end">Depósito</th><th class="text-end">Pagado</th><th class="text-end pe-3">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rr['rows'] as $r)
                                <tr>
                                    <td class="ps-3 font-mono">{{ $r['folio'] }}</td>
                                    <td class="text-secondary">{{ $r['fecha']->format('d/m/Y') }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($r['cliente'], 26) }}</td>
                                    <td class="text-secondary">{{ $r['sucursal'] }}</td>
                                    <td class="text-center"><span class="badge rounded-pill border {{ $estadoCls[$r['estado']] ?? '' }}">{{ $r['estado'] }}</span></td>
                                    <td class="text-center"><span class="badge rounded-pill border {{ $pagoCls[$r['estado_pago']] ?? '' }}">{{ $r['estado_pago'] }}</span></td>
                                    <td class="text-secondary">{{ $r['fin'] ? $r['fin']->format('d/m/Y') : '—' }}</td>
                                    <td class="text-end font-mono">${{ number_format($r['subtotal'], 2) }}</td>
                                    <td class="text-end font-mono text-success">{{ $r['descuento'] > 0 ? '-$' . number_format($r['descuento'], 2) : '—' }}</td>
                                    <td class="text-end font-mono text-secondary">${{ number_format($r['iva'], 2) }}</td>
                                    <td class="text-end font-mono text-danger">{{ $r['cargos_extra'] > 0 ? '$' . number_format($r['cargos_extra'], 2) : '—' }}</td>
                                    <td class="text-end font-mono fw-bold">${{ number_format($r['total'], 2) }}</td>
                                    <td class="text-end font-mono">${{ number_format($r['deposito'], 2) }}</td>
                                    <td class="text-end font-mono text-success">${{ number_format($r['pagado'], 2) }}</td>
                                    <td class="text-end font-mono fw-bold pe-3 {{ $r['saldo'] > 0 ? 'text-danger' : 'text-success' }}">${{ number_format($r['saldo'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="15" class="text-center text-secondary py-4">No se contrataron rentas en el período seleccionado.</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($rr['rows']))
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="7" class="ps-3 text-end">TOTAL</td>
                                <td class="text-end font-mono">${{ number_format($rk['subtotal'], 2) }}</td>
                                <td class="text-end font-mono text-success">-${{ number_format($rk['descuentos'], 2) }}</td>
                                <td class="text-end font-mono">${{ number_format($rk['iva'], 2) }}</td>
                                <td class="text-end font-mono text-danger">${{ number_format($rk['cargos_extra'], 2) }}</td>
                                <td class="text-end font-mono">${{ number_format($rk['total'], 2) }}</td>
                                <td class="text-end font-mono">${{ number_format($rk['depositos'], 2) }}</td>
                                <td class="text-end font-mono text-success">${{ number_format($rk['pagado'], 2) }}</td>
                                <td class="text-end font-mono text-danger pe-3">${{ number_format($rk['saldo'], 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- PAGOS RECIBIDOS EN EL PERÍODO -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body border-bottom py-3">
                <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-cash-coin text-success me-2"></i>Pagos de Rentas Recibidos en el Período</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 380px;">
                    <table class="table table-hover table-executive mb-0 align-middle">
                        <thead class="sticky-top">
                            <tr><th class="ps-3">Fecha</th><th>Renta</th><th>Cliente</th><th>Sucursal</th><th>Tipo</th><th>Método</th><th>Referencia</th><th class="text-end pe-3">Monto</th></tr>
                        </thead>
                        <tbody>
                            @forelse($rco['rows'] as $p)
                                <tr>
                                    <td class="ps-3 text-secondary">{{ $p['fecha']->format('d/m/Y H:i') }}</td>
                                    <td class="font-mono">{{ $p['folio'] }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($p['cliente'], 28) }}</td>
                                    <td class="text-secondary">{{ $p['sucursal'] }}</td>
                                    <td>{{ $p['tipo'] }}</td>
                                    <td class="text-capitalize">{{ $p['metodo'] }}</td>
                                    <td class="text-secondary">{{ $p['referencia'] ?: '—' }}</td>
                                    <td class="text-end font-mono fw-bold text-success pe-3">${{ number_format($p['monto'], 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-secondary py-4">No hubo pagos de rentas en el período.</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($rco['rows']))
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="7" class="ps-3 text-end">TOTAL PAGOS</td>
                                <td class="text-end font-mono text-success pe-3">${{ number_format($rco['pagos_monto'], 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        <!-- CARTERA DE RENTAS CON SALDO -->
        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-exclamation-triangle text-danger me-2"></i>Cartera de Rentas con Saldo Pendiente (a la fecha)</h6>
                <span class="badge bg-body-tertiary text-secondary border">No depende del período seleccionado</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 420px;">
                    <table class="table table-hover table-executive mb-0 align-middle">
                        <thead class="sticky-top">
                            <tr>
                                <th class="ps-3">Folio</th><th>Cliente</th><th>Sucursal</th><th class="text-center">Estado</th><th>Fin de renta</th>
                                <th class="text-center">Retraso</th><th class="text-end">Total</th><th class="text-end">Abonado</th>
                                <th class="text-end">Saldo</th><th class="text-end pe-3">Retraso est.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rca['rows'] as $r)
                                <tr>
                                    <td class="ps-3 font-mono">{{ $r['folio'] }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($r['cliente'], 28) }}</td>
                                    <td class="text-secondary">{{ $r['sucursal'] }}</td>
                                    <td class="text-center"><span class="badge rounded-pill border {{ $estadoCls[$r['estado']] ?? '' }}">{{ $r['estado'] }}</span></td>
                                    <td class="text-secondary">{{ $r['fin'] ? $r['fin']->format('d/m/Y') : '—' }}</td>
                                    <td class="text-center font-mono {{ $r['dias_retraso'] > 0 ? 'text-danger fw-bold' : 'text-secondary' }}">{{ $r['dias_retraso'] > 0 ? $r['dias_retraso'] . ' d' : '—' }}</td>
                                    <td class="text-end font-mono">${{ number_format($r['total'], 2) }}</td>
                                    <td class="text-end font-mono text-success">${{ number_format($r['deposito'] + $r['pagado'], 2) }}</td>
                                    <td class="text-end font-mono fw-bold text-danger">${{ number_format($r['saldo'], 2) }}</td>
                                    <td class="text-end font-mono pe-3 {{ $r['multa'] > 0 ? 'text-warning-emphasis' : 'text-secondary' }}">{{ $r['multa'] > 0 ? '$' . number_format($r['multa'], 2) : '—' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="10" class="text-center text-secondary py-4">No hay rentas con saldo pendiente.</td></tr>
                            @endforelse
                        </tbody>
                        @if(count($rca['rows']))
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="8" class="ps-3 text-end">TOTAL POR COBRAR</td>
                                <td class="text-end font-mono text-danger">${{ number_format($rca['por_cobrar'], 2) }}</td>
                                <td class="text-end font-mono text-warning-emphasis pe-3">${{ number_format($rca['multa'], 2) }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
    </div>

    </div><!-- /tab-content -->

</div>

<!-- SCRIPT AUTOMÁTICO DE RECARGA EN 'CHANGE' + APEXCHARTS -->
<!-- SCRIPT AUTOMÁTICO DE RECARGA + APEXCHARTS CORREGIDO -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {

        // 1. AUTO-SUBMIT AL SELECCIONAR CUALQUIERA DE LAS DOS FECHAS
        const form = document.getElementById('formFiltroFechas');
        const inputInicio = document.getElementById('fecha_inicio');
        const inputFin = document.getElementById('fecha_fin');

        if (inputInicio && inputFin && form) {
            const enviarFormularioSeguro = () => {
                if (inputInicio.value && inputFin.value) {
                    form.submit();
                }
            };

            inputInicio.addEventListener('change', enviarFormularioSeguro);
            inputFin.addEventListener('change', enviarFormularioSeguro);
        }

        // 1b. PESTAÑAS: conserva la pestaña activa al recargar por fechas y alterna los botones PDF/Excel
        const tabInput = document.getElementById('tabInput');
        document.querySelectorAll('#reportesTabs [data-bs-toggle="tab"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', function (e) {
                const t = e.target.dataset.tab;
                if (tabInput) tabInput.value = t;
                document.getElementById('exportPos').classList.toggle('d-none', t !== 'pos');
                document.getElementById('exportRentas').classList.toggle('d-none', t !== 'rentas');
                // las gráficas calculan su ancho solo cuando su pestaña es visible
                window.dispatchEvent(new Event('resize'));
            });
        });

        // 2. APEXCHARTS CONFIG
        const htmlTheme = document.documentElement.getAttribute('data-bs-theme') || 'light';
        const isDark = htmlTheme === 'dark';
        
        const labelColor = isDark ? '#a1a1aa' : '#475467';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : '#e4e4e7';

        const chartOptions = {
            theme: { mode: isDark ? 'dark' : 'light' },
            chart: { background: 'transparent', fontFamily: 'system-ui, -apple-system, sans-serif' },
            grid: { borderColor: gridColor },
            legend: { labels: { colors: labelColor } }
        };

        // Gráfica Flujo por Fecha
        var optDia = Object.assign({}, chartOptions, {
            chart: { type: 'area', height: 250, toolbar: { show: false }, background: 'transparent' },
            colors: ['#f59e0b'],
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            series: [{ name: 'Ventas ($)', data: @json($montosDia ?? []) }],
            xaxis: { categories: @json($horasDia ?? []), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartVentasDia"), optDia).render();

        // Gráfica Mes Anual
        var optMes = Object.assign({}, chartOptions, {
            chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
            colors: ['#3b82f6'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            series: [{ name: 'Facturado ($)', data: @json($montosMes ?? []) }],
            xaxis: { categories: @json($mesesNombres ?? []), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartVentasMes"), optMes).render();

        // Gráfica Productos Top (Sintaxis limpia)
        var optProd = Object.assign({}, chartOptions, {
            chart: { type: 'bar', height: 260, toolbar: { show: false }, background: 'transparent' },
            colors: ['#10b981'],
            plotOptions: { bar: { horizontal: true, barHeight: '45%', borderRadius: 4 } },
            dataLabels: { enabled: true, formatter: function(v) { return v + " pzas"; } },
            series: [{ name: 'Vendidos', data: @json($topProductosCantidades ?? []) }],
            xaxis: { categories: @json($topProductosNombres ?? []), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartProductosReales"), optProd).render();

        // 3. GRÁFICAS DE RENTAS (se dibujan al abrir la pestaña, porque ApexCharts necesita que el contenedor sea visible)
        const R = @json($repRentas['graf']);
        const money = (v) => '$' + Number(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const moneyCorto = (v) => '$' + Number(v || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 });
        let rentasChartsListo = false;

        function dibujarGrafica(id, opciones, datos) {
            const el = document.querySelector(id);
            if (!el) return;
            const hayDatos = datos.some(function (v) { return Number(v) > 0; });
            if (!hayDatos) {
                el.innerHTML = '<div class="text-center text-secondary small py-5">Sin datos en el período seleccionado.</div>';
                return;
            }
            new ApexCharts(el, Object.assign({}, chartOptions, opciones)).render();
        }

        function initRentasCharts() {
            if (rentasChartsListo) return;
            rentasChartsListo = true;
            const ejes = { labels: { style: { colors: labelColor } } };
            const base = { background: 'transparent', toolbar: { show: false }, fontFamily: 'system-ui, -apple-system, sans-serif' };

            // Contratado vs cobrado
            dibujarGrafica('#chRentasFlujo', {
                chart: Object.assign({ type: 'area', height: 280 }, base),
                colors: ['#3b82f6', '#10b981'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
                series: [{ name: 'Contratado', data: R.flujo.contratado }, { name: 'Cobrado', data: R.flujo.cobrado }],
                xaxis: { categories: R.flujo.labels, labels: { style: { colors: labelColor }, rotate: -45, hideOverlappingLabels: true } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: money } },
                legend: { position: 'top', labels: { colors: labelColor } }
            }, R.flujo.contratado.concat(R.flujo.cobrado));

            // Cobranza por método
            dibujarGrafica('#chRentasMetodos', {
                chart: Object.assign({ type: 'donut', height: 280 }, base),
                colors: ['#10b981', '#06b6d4', '#3b82f6', '#94a3b8'],
                series: R.metodos.values,
                labels: R.metodos.labels,
                dataLabels: { enabled: false },
                legend: { position: 'bottom', labels: { colors: labelColor } },
                tooltip: { y: { formatter: money } },
                plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Total', color: labelColor, formatter: function (w) { return moneyCorto(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)); } } } } } }
            }, R.metodos.values);

            // Equipos más rentados (unidades)
            dibujarGrafica('#chRentasEquiposU', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#3b82f6'],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: function (v) { return v + ' pzas'; } },
                series: [{ name: 'Unidades rentadas', data: R.top_unidades.values }],
                xaxis: { categories: R.top_unidades.labels, labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor }, maxWidth: 170 } }
            }, R.top_unidades.values);

            // Equipos por ingreso
            dibujarGrafica('#chRentasEquiposI', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#10b981'],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: moneyCorto },
                series: [{ name: 'Ingreso', data: R.top_ingreso.values }],
                xaxis: { categories: R.top_ingreso.labels, labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                yaxis: { labels: { style: { colors: labelColor }, maxWidth: 170 } },
                tooltip: { y: { formatter: money } }
            }, R.top_ingreso.values);

            // Clientes frecuentes
            dibujarGrafica('#chRentasClientes', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#06b6d4'],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: function (v) { return v + (v === 1 ? ' renta' : ' rentas'); } },
                series: [{ name: 'Rentas', data: R.clientes.values }],
                xaxis: { categories: R.clientes.labels, labels: { style: { colors: labelColor }, formatter: function (v) { return Math.round(v); } } },
                yaxis: { labels: { style: { colors: labelColor }, maxWidth: 170 } },
                tooltip: { y: { formatter: function (v, o) { return v + ' renta(s) · ' + money(R.clientes.totales[o.dataPointIndex]); } } }
            }, R.clientes.values);

            // Antigüedad de la cartera
            dibujarGrafica('#chRentasAging', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#3b82f6', '#94a3b8', '#f59e0b', '#f97316', '#ef4444', '#b91c1c'],
                plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: true, formatter: moneyCorto, style: { fontSize: '10px' } },
                legend: { show: false },
                series: [{ name: 'Saldo', data: R.aging.values }],
                xaxis: { categories: R.aging.labels, labels: { style: { colors: labelColor }, rotate: -25, trim: true } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: money } }
            }, R.aging.values);

            // Histórico anual
            dibujarGrafica('#chRentasMensual', {
                chart: Object.assign({ type: 'bar', height: 280 }, base),
                colors: ['#3b82f6', '#10b981'],
                plotOptions: { bar: { borderRadius: 3, columnWidth: '60%' } },
                dataLabels: { enabled: false },
                series: [{ name: 'Contratado', data: R.mensual.contratado }, { name: 'Cobrado', data: R.mensual.cobrado }],
                xaxis: { categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'], labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: money } },
                legend: { position: 'top', labels: { colors: labelColor } }
            }, R.mensual.contratado.concat(R.mensual.cobrado));

            // Rentas por estado
            dibujarGrafica('#chRentasEstados', {
                chart: Object.assign({ type: 'donut', height: 280 }, base),
                colors: ['#3b82f6', '#ef4444', '#94a3b8'],
                series: R.estados.values,
                labels: R.estados.labels,
                dataLabels: { enabled: true },
                legend: { position: 'bottom', labels: { colors: labelColor } },
                plotOptions: { pie: { donut: { size: '60%' } } }
            }, R.estados.values);
        }

        @if($tabActiva === 'rentas')
            initRentasCharts();
        @endif
        document.querySelectorAll('#reportesTabs [data-tab="rentas"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', initRentasCharts);
        });

        // 4. GRÁFICAS ADICIONALES DEL POS (se dibujan cuando su pestaña es visible)
        const P = @json($rep['graf']);
        let posChartsListo = false;

        function initPosCharts() {
            if (posChartsListo) return;
            posChartsListo = true;
            const base = { background: 'transparent', toolbar: { show: false }, fontFamily: 'system-ui, -apple-system, sans-serif' };

            // Vendido vs cobranza real vs crédito
            dibujarGrafica('#chPosFlujo', {
                chart: Object.assign({ type: 'area', height: 280 }, base),
                colors: ['#3b82f6', '#10b981', '#f59e0b'],
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.03 } },
                series: [{ name: 'Vendido', data: P.flujo.vendido }, { name: 'Cobranza real', data: P.flujo.cobrado }, { name: 'A crédito', data: P.flujo.credito }],
                xaxis: { categories: P.flujo.labels, labels: { style: { colors: labelColor }, rotate: -45, hideOverlappingLabels: true } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: money } },
                legend: { position: 'top', labels: { colors: labelColor } }
            }, P.flujo.vendido);

            // Canales de pago
            dibujarGrafica('#chPosCanales', {
                chart: Object.assign({ type: 'donut', height: 280 }, base),
                colors: ['#10b981', '#3b82f6', '#06b6d4', '#8b5cf6', '#f59e0b'],
                series: P.canales.values,
                labels: P.canales.labels,
                dataLabels: { enabled: false },
                legend: { position: 'bottom', labels: { colors: labelColor } },
                tooltip: { y: { formatter: money } },
                plotOptions: { pie: { donut: { size: '62%', labels: { show: true, total: { show: true, label: 'Vendido', color: labelColor, formatter: function (w) { return moneyCorto(w.globals.seriesTotals.reduce(function (a, b) { return a + b; }, 0)); } } } } } }
            }, P.canales.values);

            // Artículos por ingreso
            dibujarGrafica('#chPosIngreso', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#10b981'],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: moneyCorto },
                series: [{ name: 'Ingreso', data: P.top_ingreso.values }],
                xaxis: { categories: P.top_ingreso.labels, labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                yaxis: { labels: { style: { colors: labelColor }, maxWidth: 170 } },
                tooltip: { y: { formatter: money } }
            }, P.top_ingreso.values);

            // Clientes frecuentes
            dibujarGrafica('#chPosClientes', {
                chart: Object.assign({ type: 'bar', height: 300 }, base),
                colors: ['#06b6d4'],
                plotOptions: { bar: { horizontal: true, barHeight: '55%', borderRadius: 4 } },
                dataLabels: { enabled: true, formatter: function (v) { return v + (v === 1 ? ' compra' : ' compras'); } },
                series: [{ name: 'Compras', data: P.clientes.values }],
                xaxis: { categories: P.clientes.labels, labels: { style: { colors: labelColor }, formatter: function (v) { return Math.round(v); } } },
                yaxis: { labels: { style: { colors: labelColor }, maxWidth: 170 } },
                tooltip: { y: { formatter: function (v, o) { return v + ' compra(s) · ' + money(P.clientes.totales[o.dataPointIndex]); } } }
            }, P.clientes.values);

            // Ventas por hora
            dibujarGrafica('#chPosHoras', {
                chart: Object.assign({ type: 'bar', height: 280 }, base),
                colors: ['#f59e0b'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: false },
                series: [{ name: 'Vendido', data: P.horas.totales }],
                xaxis: { categories: P.horas.labels, labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: function (v, o) { return money(v) + ' · ' + P.horas.n[o.dataPointIndex] + ' venta(s)'; } } }
            }, P.horas.totales);

            // Composición de ingresos
            dibujarGrafica('#chPosComposicion', {
                chart: Object.assign({ type: 'donut', height: 280 }, base),
                colors: ['#3b82f6', '#06b6d4', '#8b5cf6'],
                series: P.composicion.values,
                labels: P.composicion.labels,
                dataLabels: { enabled: true },
                legend: { position: 'bottom', labels: { colors: labelColor } },
                tooltip: { y: { formatter: money } },
                plotOptions: { pie: { donut: { size: '60%' } } }
            }, P.composicion.values);

            // Día de la semana
            dibujarGrafica('#chPosDias', {
                chart: Object.assign({ type: 'bar', height: 280 }, base),
                colors: ['#3b82f6'],
                plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
                dataLabels: { enabled: false },
                series: [{ name: 'Vendido', data: P.dias.totales }],
                xaxis: { categories: P.dias.labels, labels: { style: { colors: labelColor } } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: function (v, o) { return money(v) + ' · ' + P.dias.n[o.dataPointIndex] + ' venta(s)'; } } }
            }, P.dias.totales);

            // Antigüedad de créditos
            dibujarGrafica('#chPosAging', {
                chart: Object.assign({ type: 'bar', height: 280 }, base),
                colors: ['#3b82f6', '#f59e0b', '#f97316', '#ef4444', '#b91c1c'],
                plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '55%' } },
                dataLabels: { enabled: true, formatter: moneyCorto, style: { fontSize: '10px' } },
                legend: { show: false },
                series: [{ name: 'Saldo', data: P.aging.values }],
                xaxis: { categories: P.aging.labels, labels: { style: { colors: labelColor }, trim: true } },
                yaxis: { labels: { style: { colors: labelColor }, formatter: moneyCorto } },
                tooltip: { y: { formatter: money } }
            }, P.aging.values);
        }

        @if($tabActiva === 'pos')
            initPosCharts();
        @endif
        document.querySelectorAll('#reportesTabs [data-tab="pos"]').forEach(function (btn) {
            btn.addEventListener('shown.bs.tab', initPosCharts);
        });
    });
</script>
@endsection