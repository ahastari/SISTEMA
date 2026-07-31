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
        // Parseo y formateo de fechas a nivel Carbon
        $rawInicio = request('fecha_inicio', date('Y-m-01'));
        $rawFin = request('fecha_fin', date('Y-m-d'));

        $inicio = \Carbon\Carbon::parse($rawInicio)->startOfDay();
        $fin = \Carbon\Carbon::parse($rawFin)->endOfDay();

        $ventasPeriodo = \App\Models\Venta::with('detalles')
            ->where('estado', 'completada')
            ->whereBetween('created_at', [$inicio, $fin])
            ->get();

        $facturacionTotal = $ventasPeriodo->sum('total');

        $montoFletes = 0;
        $montoManoObra = 0;
        foreach($ventasPeriodo as $v) {
            foreach($v->detalles as $d) {
                if(str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                    $montoFletes += $d->subtotal;
                } elseif(str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                    $montoManoObra += $d->subtotal;
                }
            }
        }

        $descuadresPeriodo = abs(\App\Models\CorteCaja::where('diferencia', '<', 0)
            ->whereBetween('fecha_apertura', [$inicio, $fin])
            ->sum('diferencia'));
    @endphp

    <!-- FILTRO DE FECHAS CON AUTO-SUBMIT Y VISTA EN DD/MM/YYYY -->
    <div class="filter-card mb-4 shadow-sm">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-8">
                <form id="formFiltroFechas" action="{{ route('puntoventa.reportes') }}" method="GET" class="row g-2 align-items-end">
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

            <!-- EXPORTAR PDF CON RANGO FORMATEADO EN DD/MM/YYYY -->
            <div class="col-12 col-md-4 d-flex justify-content-md-end align-items-end">
                <form action="{{ route('puntoventa.generarReporte') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tipo" value="personalizado">
                    <input type="hidden" name="fecha_inicio" value="{{ $inicio->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $fin->format('Y-m-d') }}">
                    <button type="submit" class="btn btn-dark btn-sm rounded-3 fw-bold px-3 w-100">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-1"></i> Exportar PDF
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- METRICAS CLAVE (KPIs) -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">TOTAL</span>
                    <i class="bi bi-currency-dollar text-primary fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-primary">${{ number_format($facturacionTotal, 2) }}</h3>
                <small class="text-secondary" style="font-size: 11px;">Del {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</small>
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
                            <th class="pe-3 text-end">% Participación</th>
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
                            <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($efeTotal / $facturacionTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-credit-card text-primary me-2"></i>Terminal</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'tarjeta')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($tarTotal, 2) }}</td>
                            <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($tarTotal / $facturacionTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-bank text-info me-2"></i>Transferencia</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'transferencia')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($traTotal, 2) }}</td>
                            <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($traTotal / $facturacionTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                        <tr>
                            <td class="ps-3 fw-semibold"><i class="bi bi-diagram-3 text-warning me-2"></i>Pago Mixto Combinado</td>
                            <td class="text-center font-mono">{{ $ventasPeriodo->where('metodo_pago', 'mixto')->count() }}</td>
                            <td class="text-end font-mono fw-bold text-body">${{ number_format($mixTotal, 2) }}</td>
                            <td class="pe-3 text-end font-mono text-secondary">{{ $facturacionTotal > 0 ? number_format(($mixTotal / $facturacionTotal) * 100, 1) : 0 }}%</td>
                        </tr>
                    </tbody>
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

</div>

<!-- SCRIPT AUTOMÁTICO DE RECARGA EN 'CHANGE' + APEXCHARTS -->
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
            series: [{ name: 'Ventas ($)', data: @json($montosDia) }],
            xaxis: { categories: @json($horasDia), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartVentasDia"), optDia).render();

        // Gráfica Mes Anual
        var optMes = Object.assign({}, chartOptions, {
            chart: { type: 'bar', height: 250, toolbar: { show: false }, background: 'transparent' },
            colors: ['#3b82f6'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            dataLabels: { enabled: false },
            series: [{ name: 'Facturado ($)', data: @json($montosMes) }],
            xaxis: { categories: @json($mesesNombres), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartVentasMes"), optMes).render();

        // Gráfica Productos Top
        var optProd = Object.assign({}, chartOptions, {
            chart: { type: 'bar', height: 260, toolbar: { show: false }, background: 'transparent' },
            colors: ['#10b981'],
            plotOptions: { bar: { horizontal: true, barHeight: '45%', borderRadius: 4 } },
            dataLabels: { enabled: true, formatter: function(v) { return v + " pzas"; } },
            series: [{ name: 'Vendidos', data: @json($topProductosCantidades) }],
            xaxis: { categories: @json($topProductosNombres), labels: { style: { colors: labelColor } } },
            yaxis: { labels: { style: { colors: labelColor } } }
        });
        new ApexCharts(document.querySelector("#chartProductosReales"), optProd).render();
    });
</script>
@endsection