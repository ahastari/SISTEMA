@extends('layouts.admin')

@section('content')
<style>
    .stats-card {
        background: var(--bs-body-bg);
        border-radius: 12px;
        padding: 14px 16px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid var(--bs-border-color);
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stats-card .icon {
        font-size: 20px;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        flex-shrink: 0;
    }
    .stats-card h3 {
        font-size: 20px;
        font-weight: bold;
        margin-bottom: 0;
        line-height: 1.2;
        color: var(--bs-heading-color);
    }
    .stats-card p {
        color: var(--bs-secondary-color);
        font-size: 12px;
        margin-bottom: 0;
    }
    .stats-card .small-text {
        font-size: 11px;
    }
    .quick-action {
        background: var(--bs-body-bg);
        border-radius: 10px;
        padding: 10px;
        text-align: center;
        border: 1px solid var(--bs-border-color);
        transition: all 0.2s;
        text-decoration: none;
        color: var(--bs-body-color);
        display: block;
        height: 100%;
    }
    .quick-action:hover {
        border-color: #0d6efd;
        color: var(--bs-body-color);
    }
    .quick-action .icon {
        font-size: 22px;
        display: block;
    }
    .quick-action span {
        font-size: 11px;
        display: block;
        text-truncate: true;
    }
    .chart-container {
        background: var(--bs-body-bg);
        border-radius: 12px;
        padding: 15px;
        border: 1px solid var(--bs-border-color);
        height: 100%;
    }
    .chart-container canvas {
        max-height: 180px;
        width: 100% !important;
    }
    .top-cliente-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        border-bottom: 1px solid var(--bs-border-color);
        font-size: 12px;
    }
    .top-cliente-item:last-child {
        border-bottom: none;
    }
    .top-cliente-item .badge {
        font-size: 11px;
        padding: 3px 8px;
    }
    .ultimas-rentas {
        max-height: 200px;
        overflow-y: auto;
    }
    .ultimas-rentas::-webkit-scrollbar {
        width: 4px;
    }
    .ultimas-rentas::-webkit-scrollbar-thumb {
        background: #dee2e6;
        border-radius: 4px;
    }
    .row.g-2 {
        --bs-gutter-y: 0.5rem;
        --bs-gutter-x: 0.5rem;
    }
    .welcome-text {
        font-size: 14px;
        margin-bottom: 0;
    }
    .welcome-text strong {
        font-size: 16px;
    }
    .sucursal-context {
        background: #f0f7ff;
        border: 1px solid #b6d4fe;
        border-radius: 8px;
        padding: 8px 15px;
        font-size: 13px;
        color: #084298;
        margin-bottom: 16px;
    }
    .sucursal-context i {
        color: #0d6efd;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4 class="mb-0 fw-bold text-body fs-5">Dashboard</h4>
        <p class="text-secondary small mb-0">Bienvenido <strong>{{ Auth::user()->name }}</strong></p>
    </div>
    <span class="badge bg-success py-2 px-3">
        <i class="bi bi-calendar-check me-1"></i> {{ now()->format('d/m/Y') }}
    </span>
</div>

{{-- 🔒 INDICADOR DE CONTEXTO DE SUCURSAL --}}
@if(!$isGlobalAdmin)
    <div class="sucursal-context">
        <i class="bi bi-info-circle-fill me-2"></i>
        <strong>Vista filtrada:</strong> Mostrando datos exclusivos de <strong>{{ $sucursalNombre }}</strong>
        @if(Auth::user()->isAdmin())
            <span class="text-muted ms-2">(Como Admin puedes cambiar a vista global)</span>
        @endif
    </div>
@else
    <div class="sucursal-context" style="background: #f0fff4; border-color: #a3cfbb; color: #0f5132;">
        <i class="bi bi-globe2 me-2"></i>
        <strong>Vista Global:</strong> Mostrando datos consolidados de todas las sucursales
    </div>
@endif

<!-- Tarjetas de Estadísticas -->
<div class="row g-2 mb-3">
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>{{ $totalClientes }}</h3>
                    <p>Clientes</p>
                </div>
                <div class="icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people"></i>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('clientes.index') }}" class="text-primary text-decoration-none small-text">
                    Ver todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>{{ $totalEquipos }}</h3>
                    <p>Productos</p>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="mt-2 d-flex flex-wrap gap-1 align-items-center">
                <span class="text-secondary small-text">Stock: {{ $totalStock }} und</span>
                @if($stockBajo > 0)
                    <span class="badge bg-warning text-dark" style="font-size: 9px;">{{ $stockBajo }} bajo</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>{{ $rentasActivas }}</h3>
                    <p>Rentas Activas</p>
                </div>
                <div class="icon bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-file-text"></i>
                </div>
            </div>
            <div class="mt-2">
                <span class="text-secondary small-text">Total: {{ $rentasTotales }}</span>
                <span class="text-success small-text ms-1">{{ $rentasFinalizadas }} fin</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>{{ $totalObras }}</h3>
                    <p>Obras/Proyectos</p>
                </div>
                <div class="icon bg-info bg-opacity-10 text-info">
                    <i class="bi bi-building"></i>
                </div>
            </div>
            <div class="mt-2">
                <a href="{{ route('obras.index') }}" class="text-info text-decoration-none small-text">
                    Ver obras <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-3">
    <div class="col-12 col-lg-8">
        <div class="chart-container">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-graph-up me-1"></i> 
                Rentas por Mes 
                @if(!$isGlobalAdmin)
                    <small class="text-muted">- {{ $sucursalNombre }}</small>
                @endif
            </h6>
            <div style="position: relative; width:100%;">
                <canvas id="rentasChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="chart-container">
            <h6 class="fw-bold mb-2">
                <i class="bi bi-trophy me-1"></i> 
                Top Clientes
                @if(!$isGlobalAdmin)
                    <small class="text-muted">- {{ $sucursalNombre }}</small>
                @endif
            </h6>
            <div class="mt-1">
                @forelse($topClientes as $cliente)
                    <div class="top-cliente-item">
                        <span class="text-truncate" style="max-width: 180px;">
                            <span class="badge bg-secondary rounded-circle me-1">{{ $loop->iteration }}</span>
                            {{ $cliente->cliente_nombre }}
                        </span>
                        <span class="badge bg-primary">{{ $cliente->total_rentas }} rentas</span>
                    </div>
                @empty
                    <p class="text-muted small-text text-center">Sin datos para esta sucursal</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="row g-2 mb-3">
    <div class="col-12">
        <h6 class="fw-bold mb-2 text-body"><i class="bi bi-rocket-takeoff me-1"></i> Accesos Rápidos</h6>
        <div class="row g-2">
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('clientes.create') }}" class="quick-action">
                    <span class="icon text-primary"><i class="bi bi-person-plus"></i></span>
                    <span>Nuevo Cliente</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('rentas.create') }}" class="quick-action">
                    <span class="icon text-success"><i class="bi bi-file-plus"></i></span>
                    <span>Nueva Renta</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('inventario.create') }}" class="quick-action">
                    <span class="icon text-success"><i class="bi bi-box-seam"></i></span>
                    <span>Nuevo Equipo</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('obras.create') }}" class="quick-action">
                    <span class="icon text-info"><i class="bi bi-building-add"></i></span>
                    <span>Nueva Obra</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('inventario.kanban') }}" class="quick-action">
                    <span class="icon text-danger"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                    <span>Kanban</span>
                </a>
            </div>
            <div class="col-6 col-sm-4 col-md-2">
                <a href="{{ route('rentas.index') }}" class="quick-action">
                    <span class="icon text-secondary"><i class="bi bi-list-ul"></i></span>
                    <span>Ver Rentas</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-2">
    <div class="col-12">
        <div class="chart-container">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 fw-bold">
                    <i class="bi bi-clock-history me-1"></i> 
                    Últimas Rentas
                    @if(!$isGlobalAdmin)
                        <small class="text-muted">- {{ $sucursalNombre }}</small>
                    @endif
                </h6>
                <a href="{{ route('rentas.index') }}" class="text-primary text-decoration-none small-text">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Cliente</th>
                            <th class="d-none d-sm-table-cell">Inicio</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasRentas as $renta)
                            <tr>
                                <td><strong>{{ $renta->folio }}</strong></td>
                                <td>{{ \Illuminate\Support\Str::limit($renta->cliente->nombre_completo ?? 'N/A', 15) }}</td>
                                <td class="d-none d-sm-table-cell">{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                                <td>${{ number_format($renta->total, 0) }}</td>
                                <td>
                                    @if($renta->estado == 'activa')
                                        <span class="badge bg-success">Activa</span>
                                    @else
                                        <span class="badge bg-info">Finalizada</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-primary py-0 px-2">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted small-text py-3">No hay rentas registradas en esta sucursal</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('rentasChart').getContext('2d');
    
    const labels = @json($rentasPorMes->pluck('mes_nombre'));
    const data = @json($rentasPorMes->pluck('total'));
    
    // Si no hay datos, mostrar array vacío con placeholder
    const hasData = data.length > 0 && data.some(val => val > 0);
    
    // Detectar tema actual
    const isDark = document.getElementById('htmlElement').getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';

    const myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.length > 0 ? labels : ['Sin datos'],
            datasets: [{
                label: 'Rentas',
                data: data.length > 0 ? data : [0],
                backgroundColor: isDark ? 'rgba(13, 110, 253, 0.75)' : 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false } 
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { 
                        stepSize: 1, 
                        color: textColor, 
                        font: { size: 10 } 
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        color: textColor, 
                        font: { size: 10 } 
                    }
                }
            }
        }
    });

    // Actualizar colores del gráfico cuando cambia el tema
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            setTimeout(() => {
                const currentDark = document.getElementById('htmlElement').getAttribute('data-bs-theme') === 'dark';
                const updatedColor = currentDark ? '#adb5bd' : '#6c757d';
                const updatedGrid = currentDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.05)';
                
                myChart.options.scales.y.ticks.color = updatedColor;
                myChart.options.scales.y.grid.color = updatedGrid;
                myChart.options.scales.x.ticks.color = updatedColor;
                myChart.update();
            }, 50);
        });
    }
});
</script>
@endsection