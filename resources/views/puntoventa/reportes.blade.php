@extends('layouts.admin')

@section('content')
<style>
    /* Estilos dinámicos para KPIs y Gráficos */
    .dashboard-header { 
        border-bottom: 1px solid var(--bs-border-color); 
        padding-bottom: 16px; 
        margin-bottom: 24px; 
    }
    .kpi-card { 
        background: var(--bs-body-bg); 
        border-radius: 12px; 
        padding: 20px; 
        border: 1px solid var(--bs-border-color); 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
    }
    .kpi-title { 
        font-size: 11px; 
        font-weight: 700; 
        color: var(--bs-secondary-color); 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
    }
    .kpi-value { 
        font-size: 24px; 
        font-weight: 700; 
        margin-top: 6px; 
        margin-bottom: 0; 
    }
    .chart-card { 
        background: var(--bs-body-bg); 
        border-radius: 16px; 
        padding: 24px; 
        border: 1px solid var(--bs-border-color); 
        box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
        height: 100%; 
    }
    .chart-title { 
        font-size: 13px; 
        font-weight: 700; 
        color: var(--bs-heading-color); 
        margin-bottom: 15px; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
    }
</style>

<div class="container-fluid p-0 py-2">
    <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-body">
                <i class="bi bi-pie-chart-fill text-primary me-2"></i>Dashboard y Control de Auditoría
            </h4>
            <p class="text-secondary small mb-0">Balance contable consolidado e informes analíticos</p>
        </div>
        <div>
            <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-bold d-inline-flex align-items-center gap-2">
                <i class="bi bi-arrow-left-short fs-5"></i> 
                <span>Regresar al POS</span>
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary">
                <span class="kpi-title">Ingresos Brutos Históricos</span>
                @php $bruto = \App\Models\Venta::where('estado', 'completada')->sum('total'); @endphp
                <h3 class="kpi-value text-primary">${{ number_format($bruto, 2) }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-success">
                <span class="kpi-title">Utilidad Neta Estimada</span>
                <h3 class="kpi-value text-success">${{ number_format($bruto * 0.40, 2) }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-secondary">
                <span class="kpi-title">Costo Operativo Base</span>
                <h3 class="kpi-value text-secondary">${{ number_format($bruto * 0.60, 2) }}</h3>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-warning">
                <span class="kpi-title">Pérdidas por Descuadre</span>
                @php $perdidas = abs(\App\Models\CorteCaja::where('diferencia', '<', 0)->sum('diferencia')); @endphp
                <h3 class="kpi-value text-danger">${{ number_format($perdidas, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
        <div class="card-body p-3 p-md-4">
            <h6 class="fw-bold text-body mb-3"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Centro de Auditoría e Impresión de Balances</h6>
            <form action="{{ route('puntoventa.generarReporte') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo" value="personalizado">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label small fw-semibold text-body mb-1">Desde (Fecha Apertura)</label>
                        <input type="date" name="fecha_inicio" class="form-control form-control-sm bg-body text-body" value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4">
                        <label class="form-label small fw-semibold text-body mb-1">Hasta (Fecha Límite)</label>
                        <input type="date" name="fecha_fin" class="form-control form-control-sm bg-body text-body" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-dark btn-sm w-100 fw-bold py-2 shadow-sm rounded-3">
                            <i class="bi bi-cloud-arrow-down-fill me-1"></i> Descargar Balance Financiero
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <div class="chart-title text-body"><i class="bi bi-lightning-fill text-warning"></i> Flujo Operativo de Ventas (Hoy por Hora)</div>
                <div id="chartVentasDia"></div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="chart-card">
                <div class="chart-title text-body"><i class="bi bi-calendar3 text-primary"></i> Tendencia de Facturación Anual (Por Mes)</div>
                <div id="chartVentasMes"></div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title text-body"><i class="bi bi-box-seam-fill text-success"></i> Artículos con Mayor Demanda Comercial Reales</div>
                <div id="chartProductosReales"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // Configuración responsiva de paleta de colores para ApexCharts basado en el tema actual
    const isDarkTheme = document.getElementById('htmlElement').getAttribute('data-bs-theme') === 'dark';
    const chartLabelColor = isDarkTheme ? '#adb5bd' : '#475467';
    const chartGridColor = isDarkTheme ? 'rgba(255,255,255,0.08)' : '#f2f4f7';

    const baseThemeOptions = {
        theme: { mode: isDarkTheme ? 'dark' : 'light' },
        chart: { background: 'transparent', fontFamily: 'Inter, sans-serif' },
        grid: { borderColor: chartGridColor },
        legend: { labels: { colors: chartLabelColor } }
    };

    // 1. Gráfica de Ventas del Día Actual
    var optDia = Apex.merge(baseThemeOptions, {
        chart: { type: 'area', height: 280, toolbar: { show: false } },
        colors: ['#ffc107'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        series: [{ name: 'Vendido Hoy ($)', data: @json($montosDia) }],
        xaxis: { categories: @json($horasDia), labels: { style: { colors: chartLabelColor } } },
        yaxis: { labels: { style: { colors: chartLabelColor } } }
    });
    new ApexCharts(document.querySelector("#chartVentasDia"), optDia).render();

    // 2. Gráfica de Ventas Históricas por Mes
    var optMes = Apex.merge(baseThemeOptions, {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        colors: ['#0d6efd'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        series: [{ name: 'Facturado ($)', data: @json($montosMes) }],
        xaxis: { categories: @json($mesesNombres), labels: { style: { colors: chartLabelColor } } },
        yaxis: { labels: { style: { colors: chartLabelColor } } }
    });
    new ApexCharts(document.querySelector("#chartVentasMes"), optMes).render();

    // 3. Gráfica de Productos Más Vendidos
    var optProd = Apex.merge(baseThemeOptions, {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        colors: ['#10b981'],
        plotOptions: { bar: { horizontal: true, barHeight: '50%', borderRadius: 4 } },
        dataLabels: { enabled: true, formatter: function(v) { return v + " pzas"; } },
        series: [{ name: 'Cantidad Comprada', data: @json($topProductosCantidades) }],
        xaxis: { categories: @json($topProductosNombres), labels: { style: { colors: chartLabelColor } } },
        yaxis: { labels: { style: { colors: chartLabelColor } } }
    });
    new ApexCharts(document.querySelector("#chartProductosReales"), optProd).render();
</script>
@endsection