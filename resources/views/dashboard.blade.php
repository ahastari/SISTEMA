@extends('layouts.admin')

@section('content')
<style>
    /* =======================================================
       DISEÑO CORPORATIVO Y RESPONSIVE (ENTERPRISE UI)
       ======================================================= */

    .dashboard-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
        font-size: clamp(1.5rem, 2.5vw, 2rem);
    }

    .kpi-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.08);
        border-color: var(--bs-primary);
    }
    .kpi-card.kpi-highlight {
        background: linear-gradient(135deg, rgba(13,110,253,0.06), rgba(13,110,253,0.01));
        border-color: rgba(13,110,253,0.25);
    }
    .kpi-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 12px;
    }
    .kpi-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
    }
    .kpi-title {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--bs-secondary-color);
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }
    .kpi-value {
        font-size: 28px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 8px;
    }

    .dashboard-panel {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 24px;
        height: 100%;
        box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    }
    .panel-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        color: var(--bs-heading-color);
    }

    .tools-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: 12px;
    }
    .tool-tile {
        aspect-ratio: 1;
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: var(--bs-body-color);
        transition: all 0.2s ease;
        padding: 10px;
    }
    .tool-tile:hover {
        background: var(--bs-primary-bg-subtle);
        border-color: var(--bs-primary);
        color: var(--bs-primary);
        transform: scale(1.03);
    }
    .tool-tile i {
        font-size: 24px;
        margin-bottom: 6px;
    }
    .tool-tile span {
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        line-height: 1.1;
    }

    .enterprise-table th {
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        color: var(--bs-secondary-color);
        border-bottom: 2px solid var(--bs-border-color);
        padding-bottom: 12px;
    }
    .enterprise-table td {
        font-size: 13px;
        vertical-align: middle;
        padding: 12px 8px;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .enterprise-table tr:last-child td {
        border-bottom: none;
    }

    .ranking-item {
        display: flex;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .ranking-item:last-child { border-bottom: none; }

    .rank-badge {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 12px;
        font-weight: 800;
        margin-right: 12px;
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
    }
    .rank-1 { background: rgba(255, 215, 0, 0.15); color: #b8860b; }
    .rank-2 { background: rgba(192, 192, 192, 0.2); color: #6c757d; }
    .rank-3 { background: rgba(205, 127, 50, 0.15); color: #a0522d; }

    /* Panel de alertas operativas */
    .alert-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 12px;
        border: 1px solid transparent;
        margin-bottom: 10px;
    }
    .alert-item:last-child { margin-bottom: 0; }
    .alert-item.is-danger {
        background: rgba(220, 53, 69, 0.06);
        border-color: rgba(220, 53, 69, 0.2);
    }
    .alert-item.is-warning {
        background: rgba(255, 193, 7, 0.08);
        border-color: rgba(255, 193, 7, 0.25);
    }
    .alert-item.is-ok {
        background: rgba(25, 135, 84, 0.06);
        border-color: rgba(25, 135, 84, 0.2);
    }
    .alert-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 16px;
    }
    .alert-item.is-danger .alert-icon { background: rgba(220,53,69,0.15); color: #dc3545; }
    .alert-item.is-warning .alert-icon { background: rgba(255,193,7,0.2); color: #b8860b; }
    .alert-item.is-ok .alert-icon { background: rgba(25,135,84,0.15); color: #198754; }
    .alert-text { font-size: 13px; font-weight: 600; line-height: 1.3; }
    .alert-link { font-size: 11px; font-weight: 700; text-decoration: none; }

    .caja-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }
    .caja-badge.abierta { background: rgba(25,135,84,0.12); color: #198754; }
    .caja-badge.cerrada { background: rgba(220,53,69,0.12); color: #dc3545; }
    .caja-badge .dot {
        width: 7px; height: 7px; border-radius: 50%; background: currentColor;
    }
</style>

<!-- HEADER PRINCIPAL RESPONSIVE -->
<div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center mb-4 gap-3">
    <div>
        <h2 class="dashboard-title mb-1">Panel Directivo</h2>
        <p class="text-secondary small mb-0">
            Resumen operativo para <strong>{{ Auth::user()->name }}</strong>
            &middot;
            <span class="fw-semibold">{{ $sucursalNombre }}</span>
            @if($isGlobalAdmin)
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 10px;">Vista Global</span>
            @endif
        </p>
    </div>

    <div class="d-flex flex-column flex-sm-row gap-2 w-100 w-lg-auto align-items-sm-center">
        <span class="caja-badge {{ $cajaAbierta ? 'abierta' : 'cerrada' }} me-sm-2" @if($cajaAbierta) title="Total en ventas del turno: ${{ number_format($corteAbierto->total_ventas, 2) }}" @endif>
            <span class="dot"></span> Caja {{ $cajaAbierta ? 'Abierta' : 'Cerrada' }}
            @if($cajaAbierta)
                &middot; ${{ number_format($corteAbierto->total_ventas, 2) }}
            @endif
        </span>
        <a href="{{ route('puntoventa.reportes') }}" class="btn btn-outline-primary rounded-pill fw-bold px-4 py-2 flex-grow-1 flex-lg-grow-0 d-flex justify-content-center align-items-center">
            <i class="bi bi-pie-chart-fill me-2"></i> Finanzas
        </a>
        <a href="{{ route('autorizaciones.index') }}" class="btn btn-primary rounded-pill fw-bold px-4 py-2 flex-grow-1 flex-lg-grow-0 d-flex justify-content-center align-items-center position-relative">
            <i class="bi bi-shield-lock-fill me-2"></i> Autorizaciones
            <span id="badge-autorizaciones-dashboard" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light d-none" style="font-size: 10px;">0</span>
        </a>
    </div>
</div>

<!-- KPIs FINANCIEROS (lo primero que debe ver un directivo) -->
<div class="row g-3 mb-3">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card kpi-highlight">
            <div class="kpi-header">
                <span class="kpi-title">Ingresos del Mes</span>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">${{ number_format($ingresoTotalMes, 2) }}</div>
                <span class="badge bg-body-secondary text-body-secondary border px-2 py-1" style="font-size: 10px;">Ventas + Rentas</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Ventas de Hoy</span>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-cart-check-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">{{ $ventasHoy }}</div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 10px;">${{ number_format($ingresosVentasHoy, 2) }} facturado</span>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Contratos Activos</span>
                <div class="kpi-icon bg-warning bg-opacity-10 text-warning-emphasis">
                    <i class="bi bi-file-earmark-text-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-warning-emphasis">{{ $rentasActivas }}</div>
                @if($rentasVencidas > 0)
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 10px;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $rentasVencidas }} vencidos
                    </span>
                @else
                    <span class="badge bg-body-secondary text-body-secondary border px-2 py-1" style="font-size: 10px;">Sin vencidos</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Clientes</span>
                <div class="kpi-icon bg-info bg-opacity-10 text-info-emphasis">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">{{ $totalClientes }}</div>
                <a href="{{ route('clientes.index') }}" class="text-decoration-none text-info-emphasis fw-bold" style="font-size: 11px;">Gestionar directorio <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- KPIs OPERATIVOS -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Valor de Inventario</span>
                <div class="kpi-icon bg-success bg-opacity-10 text-success">
                    <i class="bi bi-box-seam-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">{{ $totalEquipos }}</div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-body-secondary text-body-secondary border px-2 py-1" style="font-size: 10px;">{{ $totalStock }} uds</span>
                    @if($stockBajo > 0)
                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis border border-warning border-opacity-25 px-2 py-1" style="font-size: 10px;">{{ $stockBajo }} bajos</span>
                    @endif
                    @if($stockAgotado > 0)
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" style="font-size: 10px;">{{ $stockAgotado }} agotados</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Tasa de Cierre</span>
                <div class="kpi-icon bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-check2-circle"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">
                    {{ $rentasNoCanceladas > 0 ? number_format(($rentasFinalizadas / $rentasNoCanceladas) * 100, 0) : 0 }}%
                </div>
                <span class="badge bg-body-secondary text-body-secondary border px-2 py-1" style="font-size: 10px;">{{ $rentasFinalizadas }} de {{ $rentasNoCanceladas }} finalizadas</span>
                @if($rentasCanceladas > 0)
                    <span class="badge bg-body-secondary text-body-secondary border px-2 py-1 ms-1" style="font-size: 10px;">{{ $rentasCanceladas }} canceladas</span>
                @endif
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Proyectos / Obras</span>
                <div class="kpi-icon bg-info bg-opacity-10 text-info-emphasis">
                    <i class="bi bi-buildings-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-info-emphasis">{{ $totalObras }}</div>
                <a href="{{ route('obras.index') }}" class="text-decoration-none text-info-emphasis fw-bold" style="font-size: 11px;">Supervisar obras <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="kpi-card">
            <div class="kpi-header">
                <span class="kpi-title">Rentas Históricas</span>
                <div class="kpi-icon bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-archive-fill"></i>
                </div>
            </div>
            <div>
                <div class="kpi-value text-body">{{ $rentasTotales }}</div>
                <a href="{{ route('rentas.index') }}" class="text-decoration-none text-secondary fw-bold" style="font-size: 11px;">Ver historial <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<!-- CONTENIDO PRINCIPAL -->
<div class="row g-4">

    <!-- COLUMNA IZQUIERDA -->
    <div class="col-12 col-xl-8 d-flex flex-column gap-4">

        <!-- Gráfico Financiero: Ventas vs Rentas -->
        <div class="dashboard-panel">
            <div class="panel-title">
                <i class="bi bi-bar-chart-line-fill fs-5 me-2 text-primary"></i> Rendimiento Mensual (Ventas vs Rentas)
            </div>
            <div style="position: relative; width:100%; height: 220px;">
                <canvas id="rentasChart"></canvas>
            </div>
        </div>

        <!-- Tabla de Últimas Operaciones -->
        <div class="dashboard-panel flex-grow-1">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
                <div class="panel-title mb-0">
                    <i class="bi bi-clock-history fs-5 me-2 text-success"></i> Actividad Reciente (Rentas)
                </div>
                <a href="{{ route('rentas.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3">Ver todas</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover enterprise-table mb-0">
                    <thead>
                        <tr>
                            <th class="ps-2">Folio</th>
                            <th>Cliente</th>
                            <th class="d-none d-md-table-cell text-center">F. Inicio</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-center d-none d-sm-table-cell">Estado</th>
                            <th class="text-end pe-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ultimasRentas as $renta)
                            <tr>
                                <td class="ps-2"><span class="badge bg-body-secondary text-body border font-monospace">{{ $renta->folio }}</span></td>
                                <td class="fw-bold text-body text-truncate" style="max-width: 150px;">{{ $renta->cliente->nombre_completo ?? 'N/A' }}</td>
                                <td class="d-none d-md-table-cell text-center text-secondary">{{ $renta->fecha_inicio->format('d M, Y') }}</td>
                                <td class="text-end fw-bold text-success">${{ number_format($renta->total, 2) }}</td>
                                <td class="text-center d-none d-sm-table-cell">
                                    @if($renta->estado == 'activa')
                                        @if($renta->estaVencida())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1" title="{{ $renta->dias_retraso }} día(s) de retraso">Vencida ({{ $renta->dias_retraso }}d)</span>
                                        @else
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">En curso</span>
                                        @endif
                                    @elseif($renta->estado == 'cancelada')
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">Cancelada</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2 py-1">Cerrada</span>
                                    @endif
                                </td>
                                <td class="text-end pe-2">
                                    <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-light border rounded-circle shadow-sm text-secondary hover-primary" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-5">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                    Sin registros recientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- COLUMNA DERECHA -->
    <div class="col-12 col-xl-4 d-flex flex-column gap-4">

        <!-- Panel de Alertas Operativas -->
        @if($rentasVencidas > 0 || $stockAgotado > 0 || $stockBajo > 0)
        <div class="dashboard-panel">
            <div class="panel-title text-danger">
                <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i> Alertas Operativas
            </div>

            @if($rentasVencidas > 0)
                <div class="alert-item is-danger">
                    <div class="alert-icon"><i class="bi bi-calendar-x-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="alert-text">{{ $rentasVencidas }} {{ $rentasVencidas == 1 ? 'contrato vencido' : 'contratos vencidos' }}</div>
                        <span class="d-block text-secondary" style="font-size: 11px;">Equipo sin recuperar / posible multa acumulándose</span>
                        <a href="{{ route('rentas.index') }}" class="alert-link text-danger">Revisar ahora <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endif

            @if($stockAgotado > 0)
                <div class="alert-item is-danger">
                    <div class="alert-icon"><i class="bi bi-box-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="alert-text">{{ $stockAgotado }} {{ $stockAgotado == 1 ? 'equipo agotado' : 'equipos agotados' }}</div>
                        <a href="{{ route('inventario.index') }}" class="alert-link text-danger">Ver inventario <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endif

            @if($stockBajo > 0)
                <div class="alert-item is-warning">
                    <div class="alert-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    <div class="flex-grow-1">
                        <div class="alert-text">{{ $stockBajo }} {{ $stockBajo == 1 ? 'equipo con stock crítico' : 'equipos con stock crítico' }}</div>
                        <a href="{{ route('inventario.index') }}" class="alert-link text-warning-emphasis">Ver inventario <i class="bi bi-arrow-right"></i></a>
                    </div>
                </div>
            @endif
        </div>
        @else
        <div class="dashboard-panel">
            <div class="panel-title text-success">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i> Estado Operativo
            </div>
            <div class="alert-item is-ok">
                <div class="alert-icon"><i class="bi bi-emoji-smile-fill"></i></div>
                <div class="alert-text">Todo en orden: sin vencidos ni faltantes de stock.</div>
            </div>
        </div>
        @endif

        <!-- Herramientas -->
        <div class="dashboard-panel">
            <div class="panel-title">
                <i class="bi bi-grid-1x2-fill fs-5 me-2 text-dark"></i> Centro de Mando
            </div>

            <div class="tools-grid">
                <a href="{{ route('configuracion.index') }}" class="tool-tile">
                    <i class="bi bi-sliders text-secondary"></i>
                    <span>Ajustes</span>
                </a>
                <a href="{{ route('inventario.index') }}" class="tool-tile">
                    <i class="bi bi-boxes text-success"></i>
                    <span>Catálogo</span>
                </a>
                <a href="{{ route('movimientos.create') }}" class="tool-tile">
                    <i class="bi bi-truck text-info-emphasis"></i>
                    <span>Traslados</span>
                </a>
                <a href="{{ route('puntoventa.index') }}" class="tool-tile">
                    <i class="bi bi-cart-check-fill text-danger"></i>
                    <span>Caja POS</span>
                </a>
            </div>
        </div>

        <!-- Ranking Top Clientes -->
        <div class="dashboard-panel flex-grow-1">
            <div class="panel-title text-warning-emphasis">
                <i class="bi bi-award-fill fs-5 me-2"></i> Top Clientes
            </div>

            <div class="mt-2">
                @forelse($topClientes as $index => $cliente)
                    <div class="ranking-item">
                        <div class="rank-badge rank-{{ $index + 1 }}">{{ $index + 1 }}</div>
                        <div class="flex-grow-1 text-truncate pe-2">
                            <span class="fw-bold text-body">{{ $cliente->cliente_nombre }}</span>
                        </div>
                        <span class="badge bg-body-secondary text-body-secondary border rounded-pill px-3 py-1">
                            {{ $cliente->total_rentas }} <i class="bi bi-file-text ms-1"></i>
                        </span>
                    </div>
                @empty
                    <div class="text-center text-secondary py-5">
                        <i class="bi bi-emoji-frown fs-2 d-block mb-2 opacity-50"></i>
                        Faltan datos para el ranking.
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Notificación de autorizaciones pendientes
    function revisarAutorizacionesDashboard() {
        fetch('{{ route("autorizaciones.notificaciones") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.getElementById('badge-autorizaciones-dashboard');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.classList.remove('d-none');
                        badge.classList.add('animate__animated', 'animate__bounceIn');
                    } else {
                        badge.classList.add('d-none');
                    }
                }
            })
            .catch(error => console.error('Error al revisar notificaciones:', error));
    }
    revisarAutorizacionesDashboard();
    setInterval(revisarAutorizacionesDashboard, 15000);

    // Datos combinados: Rentas y Ventas por mes
    const rentasLabels = @json($rentasPorMes->pluck('mes_nombre'));
    const rentasData = @json($rentasPorMes->pluck('total'));

    const ventasLabels = @json($ventasPorMes->pluck('mes'));
    const ventasData = @json($ventasPorMes->pluck('monto'));

    // Usamos las etiquetas de rentas como eje base (ya vienen formateadas en español)
    const labels = rentasLabels.length > 0 ? rentasLabels : ['Sin datos'];

    const ctx = document.getElementById('rentasChart').getContext('2d');
    const isDark = document.getElementById('htmlElement').getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#adb5bd' : '#6c757d';
    const gridColor = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

    const gradientRentas = ctx.createLinearGradient(0, 0, 0, 250);
    gradientRentas.addColorStop(0, 'rgba(13, 110, 253, 0.9)');
    gradientRentas.addColorStop(1, 'rgba(13, 110, 253, 0.05)');

    const gradientVentas = ctx.createLinearGradient(0, 0, 0, 250);
    gradientVentas.addColorStop(0, 'rgba(25, 135, 84, 0.9)');
    gradientVentas.addColorStop(1, 'rgba(25, 135, 84, 0.05)');

    const myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Contratos de Renta',
                    data: rentasData.length > 0 ? rentasData : [0],
                    backgroundColor: gradientRentas,
                    borderColor: 'rgba(13, 110, 253, 1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(13, 110, 253, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y'
                },
                {
                    label: 'Ingresos por Ventas ($)',
                    data: ventasData.length > 0 ? ventasData : [0],
                    backgroundColor: gradientVentas,
                    borderColor: 'rgba(25, 135, 84, 1)',
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: 'rgba(25, 135, 84, 1)',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { color: textColor, font: { size: 11, family: "'Inter', sans-serif" }, boxWidth: 10 }
                },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(0,0,0,0.9)' : 'rgba(255,255,255,0.95)',
                    titleColor: isDark ? '#fff' : '#000',
                    bodyColor: isDark ? '#fff' : '#000',
                    borderColor: 'rgba(13, 110, 253, 0.5)',
                    borderWidth: 1,
                    padding: 12,
                    boxPadding: 4,
                    usePointStyle: true,
                    titleFont: { size: 13, family: "'Inter', sans-serif" },
                    bodyFont: { size: 14, weight: 'bold', family: "'Inter', sans-serif" }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { stepSize: 1, color: textColor, font: { size: 11, family: "'Inter', sans-serif" } },
                    border: { display: false },
                    title: { display: true, text: '# Contratos', color: textColor, font: { size: 10 } }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 11, family: "'Inter', sans-serif" } },
                    border: { display: false },
                    title: { display: true, text: 'Ingresos ($)', color: textColor, font: { size: 10 } }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor, font: { size: 11, weight: '600', family: "'Inter', sans-serif" } },
                    border: { display: false }
                }
            }
        }
    });

    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            setTimeout(() => {
                const currentDark = document.getElementById('htmlElement').getAttribute('data-bs-theme') === 'dark';
                const updatedColor = currentDark ? '#adb5bd' : '#6c757d';
                const updatedGrid = currentDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.05)';

                myChart.options.scales.y.ticks.color = updatedColor;
                myChart.options.scales.y.grid.color = updatedGrid;
                myChart.options.scales.y1.ticks.color = updatedColor;
                myChart.options.scales.x.ticks.color = updatedColor;
                myChart.options.plugins.legend.labels.color = updatedColor;

                myChart.options.plugins.tooltip.backgroundColor = currentDark ? 'rgba(0,0,0,0.9)' : 'rgba(255,255,255,0.95)';
                myChart.options.plugins.tooltip.titleColor = currentDark ? '#fff' : '#000';
                myChart.options.plugins.tooltip.bodyColor = currentDark ? '#fff' : '#000';

                myChart.update();
            }, 50);
        });
    }
});
</script>
@endsection