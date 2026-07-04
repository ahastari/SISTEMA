@extends('layouts.admin')

@section('content')
<style>
    body { background-color: #f9fafb; }
    .dashboard-header { border-bottom: 1px solid #eaecf0; padding-bottom: 16px; margin-bottom: 24px; }
    .kpi-card { background: white; border-radius: 12px; padding: 20px; border: 1px solid #eaecf0; box-shadow: 0 1px 3px rgba(16,24,40,0.05); }
    .kpi-title { font-size: 11px; font-weight: 700; color: #667085; text-transform: uppercase; letter-spacing: 0.5px; }
    .kpi-value { font-size: 24px; font-weight: 700; color: #101828; margin-top: 6px; margin-bottom: 0; }
    .chart-card { background: white; border-radius: 16px; padding: 24px; border: 1px solid #eaecf0; box-shadow: 0 1px 3px rgba(16,24,40,0.05); height: 100%; }
    .chart-title { font-size: 13px; font-weight: 700; color: #344054; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
    .form-label { font-size: 12px; font-weight: 600; color: #344054; }
    .export-card { background: #ffffff; border: 1px solid #eaecf0; border-radius: 12px; padding: 20px; }
</style>

<div class="container-fluid py-2">
    <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
    <div>
        <h4 class="mb-1 fw-bold text-dark">
            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Dashboard y Control de Auditoría
        </h4>
        <p class="text-muted small mb-0">Andamios y Madera Viramontes</p>
    </div>
    
    <div>
        <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 10px; padding: 10px 16px;">
            <i class="bi bi-arrow-left-short fs-5 lh-1"></i> 
            <span>Regresar al Punto de Venta</span>
        </a>
    </div>
</div>

    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card border-start border-4 border-primary">
                <span class="kpi-title">Ingresos Brutos Históricos</span>
                @php $bruto = \App\Models\Venta::where('estado', 'completada')->sum('total'); @endphp
                <h3 class="kpi-value text-primary">${{ number_format($bruto, 2) }}</h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card border-start border-4 border-success">
                <span class="kpi-title">Utilidad Neta (Ganancias)</span>
                <h3 class="kpi-value text-success">${{ number_format($bruto * 0.40, 2) }}</h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card border-start border-4 border-secondary">
                <span class="kpi-title">Costo de Inversión (Mercancía)</span>
                <h3 class="kpi-value text-muted">${{ number_format($bruto * 0.60, 2) }}</h3>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card border-start border-4 border-warning">
                <span class="kpi-title">Pérdidas por Descuadre de Caja</span>
                @php $perdidas = abs(\App\Models\CorteCaja::where('diferencia', '<', 0)->sum('diferencia')); @endphp
                <h3 class="kpi-value text-danger">${{ number_format($perdidas, 2) }}</h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 rounded-3 border">
        <div class="card-body p-4 bg-white">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Centro de Auditoría e Impresión de Balances</h6>
            <form action="{{ route('puntoventa.generarReporte') }}" method="POST">
                @csrf
                <input type="hidden" name="tipo" value="personalizado">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Desde (Fecha Apertura)</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="{{ date('Y-m-01') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Hasta (Fecha Límite)</label>
                        <input type="date" name="fecha_fin" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2 shadow-sm">
                            <i class="bi bi-cloud-arrow-down-fill me-2"></i> Descargar Balance Financiero
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-lightning-fill text-warning"></i> Flujo Operativo de Ventas de Hoy (Por Hora)</div>
                <div id="chartVentasDia"></div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-calendar3 text-primary"></i> Tendencia de Facturación Anual (Por Mes)</div>
                <div id="chartVentasMes"></div>
            </div>
        </div>

        <div class="col-12">
            <div class="chart-card">
                <div class="chart-title"><i class="bi bi-box-seam-fill text-success"></i> Artículos con Mayor Demanda Comercial Reales (Tu Almacén)</div>
                <div id="chartProductosReales"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    // 1. Gráfica de Ventas del Día Actual
    var optDia = {
        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
        colors: ['#ffc107'],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        series: [{ name: 'Vendido Hoy ($)', data: @json($montosDia) }],
        xaxis: { categories: @json($horasDia), axisBorder: { show: false } },
        grid: { borderColor: '#f8f9fa' }
    };
    new ApexCharts(document.querySelector("#chartVentasDia"), optDia).render();

    // 2. Gráfica de Ventas Históricas por Mes
    var optMes = {
        chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
        colors: ['#0d6efd'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } },
        dataLabels: { enabled: false },
        series: [{ name: 'Facturado ($)', data: @json($montosMes) }],
        xaxis: { categories: @json($mesesNombres), axisBorder: { show: false } },
        grid: { borderColor: '#f8f9fa' }
    };
    new ApexCharts(document.querySelector("#chartVentasMes"), optMes).render();

    // 3. Gráfica Real de Productos Más Vendidos de tu Base de Datos
    var optProd = {
        chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
        colors: ['#10b981'],
        plotOptions: { bar: { horizontal: true, barHeight: '50%', borderRadius: 4 } },
        dataLabels: { enabled: true, formatter: function(v) { return v + " pzas"; } },
        series: [{ name: 'Cantidad Comprada', data: @json($topProductosCantidades) }],
        xaxis: { categories: @json($topProductosNombres) }
    };
    new ApexCharts(document.querySelector("#chartProductosReales"), optProd).render();
</script>
@endsection