@extends('layouts.admin')

@section('content')
<style>
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 15px 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }
    .stats-card .icon {
        font-size: 24px;
        width: 45px;
        height: 45px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        flex-shrink: 0;
    }
    .stats-card h3 {
        font-size: 22px;
        font-weight: bold;
        margin-bottom: 0;
        line-height: 1.2;
    }
    .stats-card p {
        color: #6c757d;
        font-size: 12px;
        margin-bottom: 0;
    }
    .stats-card .small-text {
        font-size: 11px;
    }
    .quick-action {
        background: white;
        border-radius: 10px;
        padding: 12px;
        text-align: center;
        border: 1px solid #e9ecef;
        transition: all 0.2s;
        text-decoration: none;
        color: #212529;
        display: block;
        height: 100%;
    }
    .quick-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        border-color: #0d6efd;
    }
    .quick-action .icon {
        font-size: 24px;
        display: block;
    }
    .quick-action span {
        font-size: 12px;
    }
    .chart-container {
        background: white;
        border-radius: 12px;
        padding: 15px 18px;
        border: 1px solid #e9ecef;
        height: 100%;
    }
    .chart-container h6 {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 10px;
    }
    .chart-container canvas {
        max-height: 150px;
    }
    .table-sm-custom {
        font-size: 13px;
    }
    .table-sm-custom th,
    .table-sm-custom td {
        padding: 6px 10px;
        vertical-align: middle;
    }
    .badge-sm {
        font-size: 10px;
        padding: 3px 8px;
    }
    .top-cliente-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 13px;
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
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0 fw-bold">Dashboard</h4>
        <p class="text-muted welcome-text">Bienvenido <strong>{{ Auth::user()->name }}</strong>, aquí tienes el resumen</p>
    </div>
    <span class="badge bg-success">
        <i class="bi bi-calendar-check"></i> {{ now()->format('d/m/Y') }}
    </span>
</div>

<!-- Tarjetas de Estadísticas (2 columnas en móvil, 4 en desktop) -->
<div class="row g-2 mb-3">
    <div class="col-6 col-md-3">
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
            <div class="mt-1">
                <a href="{{ route('clientes.index') }}" class="text-primary text-decoration-none small-text">
                    Ver todos <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stats-card">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3>{{ $totalEquipos }}</h3>
                    <p>Equipos</p>
                </div>
                <div class="icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam"></i>
                </div>
            </div>
            <div class="mt-1">
                <span class="text-muted small-text">Stock: {{ $totalStock }} und</span>
                @if($stockBajo > 0)
                    <span class="badge bg-warning badge-sm ms-1">{{ $stockBajo }} bajo</span>
                @endif
                @if($stockAgotado > 0)
                    <span class="badge bg-danger badge-sm ms-1">{{ $stockAgotado }} agot</span>
                @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
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
            <div class="mt-1">
                <span class="text-muted small-text">Total: {{ $rentasTotales }}</span>
                <span class="text-success small-text ms-1">{{ $rentasFinalizadas }} finalizadas</span>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
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
            <div class="mt-1">
                <a href="{{ route('obras.index') }}" class="text-info text-decoration-none small-text">
                    Ver obras <i class="bi bi-arrow-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Gráfico y Top Clientes -->
<div class="row g-2 mb-3">
    <div class="col-md-8">
        <div class="chart-container">
            <h6><i class="bi bi-graph-up"></i> Rentas por Mes</h6>
            <canvas id="rentasChart" height="120"></canvas>
        </div>
    </div>
    <div class="col-md-4">
        <div class="chart-container">
            <h6><i class="bi bi-trophy"></i> Top Clientes</h6>
            <div class="mt-1">
                @forelse($topClientes as $cliente)
                    <div class="top-cliente-item">
                        <span>
                            <span class="badge bg-secondary rounded-circle me-1" style="font-size: 10px; width: 20px; height: 20px; display: inline-flex; align-items: center; justify-content: center;">{{ $loop->iteration }}</span>
                            {{ \Illuminate\Support\Str::limit($cliente->cliente_nombre, 20) }}
                        </span>
                        <span class="badge bg-primary badge-sm">{{ $cliente->total_rentas }} rentas</span>
                    </div>
                @empty
                    <p class="text-muted small-text text-center">Sin datos</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Accesos Rápidos (6 items en 3 columnas en móvil, 6 en desktop) -->
<div class="row g-2 mb-3">
    <div class="col-12">
        <h6 class="fw-bold mb-2"><i class="bi bi-rocket-takeoff"></i> Accesos Rápidos</h6>
        <div class="row g-2">
            <div class="col-4 col-md-2">
                <a href="{{ route('clientes.create') }}" class="quick-action">
                    <span class="icon text-primary"><i class="bi bi-person-plus"></i></span>
                    <span>Nuevo Cliente</span>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="{{ route('rentas.create') }}" class="quick-action">
                    <span class="icon text-success"><i class="bi bi-file-plus"></i></span>
                    <span>Nueva Renta</span>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="{{ route('inventario.create') }}" class="quick-action">
                    <span class="icon text-success"><i class="bi bi-file-plus"></i></span>
                    <span>Nuevo Equipo</span>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="{{ route('obras.create') }}" class="quick-action">
                    <span class="icon text-info"><i class="bi bi-building-add"></i></span>
                    <span>Nueva Obra</span>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="{{ route('inventario.kanban') }}" class="quick-action">
                    <span class="icon text-danger"><i class="bi bi-grid-3x3-gap-fill"></i></span>
                    <span>Kanban</span>
                </a>
            </div>
            <div class="col-4 col-md-2">
                <a href="{{ route('rentas.index') }}" class="quick-action">
                    <span class="icon text-secondary"><i class="bi bi-list-ul"></i></span>
                    <span>Ver Rentas</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Últimas Rentas -->
<div class="row g-2">
    <div class="col-12">
        <div class="chart-container">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0"><i class="bi bi-clock-history"></i> Últimas Rentas</h6>
                <a href="{{ route('rentas.index') }}" class="text-primary text-decoration-none small-text">
                    Ver todas <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="ultimas-rentas">
                <div class="table-responsive">
                    <table class="table table-sm table-sm-custom table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Folio</th>
                                <th>Cliente</th>
                                <th>Inicio</th>
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
                                    <td>{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>${{ number_format($renta->total, 0) }}</td>
                                    <td>
                                        @if($renta->estado == 'activa')
                                            <span class="badge badge-sm bg-success">Activa</span>
                                        @else
                                            <span class="badge badge-sm bg-info">Finalizada</span>
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
                                    <td colspan="6" class="text-center text-muted small-text">No hay rentas registradas</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Rentas',
                data: data,
                backgroundColor: 'rgba(13, 110, 253, 0.6)',
                borderColor: 'rgba(13, 110, 253, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1,
                        font: { size: 10 }
                    }
                },
                x: {
                    ticks: {
                        font: { size: 10 }
                    }
                }
            }
        }
    });
});
</script>
@endsection