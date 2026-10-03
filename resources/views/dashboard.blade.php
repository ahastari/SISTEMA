@extends('layouts.admin')

@section('content')
<style>
    .dashboard-header { border-bottom: 1px solid var(--bs-border-color); padding-bottom: 18px; margin-bottom: 20px; }
    .font-mono { font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Courier New", monospace; }
    .kpi-card { background: var(--bs-body-bg); border-radius: 12px; padding: 16px 20px; border: 1px solid var(--bs-border-color); height: 100%; transition: transform .2s ease, box-shadow .2s ease; }
    .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 8px 18px rgba(0,0,0,.07) !important; }
    .kpi-title { font-size: 10.5px; font-weight: 700; color: var(--bs-secondary-color); text-transform: uppercase; letter-spacing: .6px; }
    .kpi-value { font-size: 22px; font-weight: 700; margin-top: 4px; margin-bottom: 0; }
    .kpi-sub { font-size: 11px; color: var(--bs-secondary-color); }
    .chart-card { background: var(--bs-body-bg); border-radius: 14px; padding: 20px; border: 1px solid var(--bs-border-color); height: 100%; }
    .chart-title { font-size: 11.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: var(--bs-secondary-color); margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .filter-card { background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color); border-radius: 12px; padding: 14px 18px; }
    .table-executive { font-size: 12.5px; }
    .table-executive th { font-size: 10.5px; text-transform: uppercase; letter-spacing: .5px; font-weight: 700; color: var(--bs-secondary-color); background: var(--bs-tertiary-bg) !important; border-bottom: 1px solid var(--bs-border-color); }
    .var-badge { font-size: 10px; font-weight: 700; padding: 3px 8px; border-radius: 20px; white-space: nowrap; }
    .var-up { background: rgba(25,135,84,.12); color: #198754; }
    .var-down { background: rgba(220,53,69,.12); color: #dc3545; }
    .var-flat { background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); }
    .share-bar { height: 6px; border-radius: 6px; background: var(--bs-tertiary-bg); overflow: hidden; min-width: 70px; }
    .share-bar > span { display: block; height: 100%; background: #3b82f6; border-radius: 6px; }
    .caja-badge { display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .caja-badge.abierta { background: rgba(25,135,84,.12); color: #198754; }
    .caja-badge.cerrada { background: rgba(220,53,69,.12); color: #dc3545; }
    .caja-badge .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .alert-row { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; border: 1px solid transparent; margin-bottom: 8px; }
    .alert-row:last-child { margin-bottom: 0; }
    .alert-row.is-danger { background: rgba(220,53,69,.06); border-color: rgba(220,53,69,.2); }
    .alert-row.is-warning { background: rgba(255,193,7,.08); border-color: rgba(255,193,7,.25); }
    .alert-row.is-ok { background: rgba(25,135,84,.06); border-color: rgba(25,135,84,.2); }
    .alert-row .ic { width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .alert-row.is-danger .ic { background: rgba(220,53,69,.15); color: #dc3545; }
    .alert-row.is-warning .ic { background: rgba(255,193,7,.2); color: #b8860b; }
    .alert-row.is-ok .ic { background: rgba(25,135,84,.15); color: #198754; }
    .tool-tile { background: var(--bs-tertiary-bg); border: 1px solid var(--bs-border-color); border-radius: 12px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-decoration: none; color: var(--bs-body-color); padding: 12px 6px; transition: all .2s ease; }
    .tool-tile:hover { background: var(--bs-primary-bg-subtle); border-color: var(--bs-primary); color: var(--bs-primary); }
    .tool-tile i { font-size: 22px; margin-bottom: 4px; }
    .tool-tile span { font-size: 11px; font-weight: 700; }
    .rank-badge { width: 24px; height: 24px; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 800; background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); }
    .rank-1 { background: rgba(255,215,0,.18); color: #b8860b; }
    .rank-2 { background: rgba(192,192,192,.25); color: #6c757d; }
    .rank-3 { background: rgba(205,127,50,.18); color: #a0522d; }
    .sin-datos { text-align: center; color: var(--bs-secondary-color); font-size: 13px; padding: 48px 0; }
</style>

@php
    // ---- Valores opcionales enviados por el controlador (si faltan, el panel queda vacío u oculto)
    $inicio  = $inicio ?? \Carbon\Carbon::now()->startOfMonth();
    $fin     = $fin ?? \Carbon\Carbon::now()->endOfDay();
    $flujo   = $flujo ?? ['labels' => [], 'ventas' => [], 'rentas' => [], 'por_mes' => false];
    $ingresosPeriodo         = $ingresosPeriodo ?? 0;
    $ingresosPeriodoAnterior = $ingresosPeriodoAnterior ?? null;
    $ventasPeriodoN          = $ventasPeriodoN ?? 0;
    $ventasPeriodoMonto      = $ventasPeriodoMonto ?? 0;
    $rentasPeriodoMonto      = $rentasPeriodoMonto ?? 0;
    $ticketPromedio          = $ticketPromedio ?? 0;
    $ventasPorMetodoPago = collect($ventasPorMetodoPago ?? []);
    $topEquipos          = collect($topEquipos ?? []);
    $sucursalesTendencia = $sucursalesTendencia ?? null;
    $suc = collect($sucursalesComparativo ?? [])->map(fn($s) => (object) $s)
            ->sortByDesc(fn($s) => ($s->ventas ?? 0) + ($s->rentas ?? 0))->values();
    $totalSuc = max($suc->sum(fn($s) => ($s->ventas ?? 0) + ($s->rentas ?? 0)), 0.01);
    $hayTabSuc = $isGlobalAdmin && $suc->count() > 0;
    $tabActiva = (request('tab') === 'sucursales' && $hayTabSuc) ? 'sucursales' : 'resumen';
    $rentasEnCurso = max($rentasActivas - $rentasVencidas, 0);
    $cartera = \App\Http\Controllers\CreditoController::resumenCartera();
    $varPeriodo = ($ingresosPeriodoAnterior !== null && $ingresosPeriodoAnterior > 0)
        ? (($ingresosPeriodo - $ingresosPeriodoAnterior) / $ingresosPeriodoAnterior) * 100 : null;
    $tasaCierre = $rentasNoCanceladas > 0 ? ($rentasFinalizadas / $rentasNoCanceladas) * 100 : 0;
@endphp

<div class="container-fluid p-0 py-1">

    <!-- HEADER -->
    <div class="dashboard-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-1 fw-bold text-body">
                <i class="bi bi-speedometer2 text-primary me-2"></i>Panel Directivo
            </h4>
            <p class="text-secondary small mb-0">
                Resumen operativo para <strong>{{ Auth::user()->name }}</strong> &middot;
                <span class="fw-semibold">{{ $sucursalNombre }}</span>
                @if($isGlobalAdmin)
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle ms-1" style="font-size: 10px;">Vista Global</span>
                @endif
            </p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="caja-badge {{ $cajaAbierta ? 'abierta' : 'cerrada' }}" @if($cajaAbierta) title="Total en ventas del turno: ${{ number_format($corteAbierto->total_ventas, 2) }}" @endif>
                <span class="dot"></span> Caja {{ $cajaAbierta ? 'Abierta' : 'Cerrada' }}
                @if($cajaAbierta) &middot; ${{ number_format($corteAbierto->total_ventas, 2) }} @endif
            </span>
            <a href="{{ route('puntoventa.reportes') }}" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">
                <i class="bi bi-pie-chart-fill me-1"></i> Finanzas
            </a>
            <a href="{{ route('autorizaciones.index') }}" class="btn btn-primary btn-sm rounded-pill fw-bold px-3 position-relative">
                <i class="bi bi-shield-lock-fill me-1"></i> Autorizaciones
                <span id="badge-autorizaciones-dashboard" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-light d-none" style="font-size: 10px;">0</span>
            </a>
        </div>
    </div>

    <!-- KPIs GENERALES (fotografía actual: mes / hoy / cartera / inventario) -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Ingresos del Mes</span><i class="bi bi-cash-stack text-primary fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-primary">${{ number_format($ingresoTotalMes, 2) }}</h3>
                <span class="kpi-sub">Ventas + cobros de rentas</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-success shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Ventas de Hoy</span><i class="bi bi-cart-check-fill text-success fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-success">{{ $ventasHoy }}</h3>
                <span class="kpi-sub">${{ number_format($ingresosVentasHoy, 2) }} facturado</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-warning shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Contratos Activos</span><i class="bi bi-file-earmark-text-fill text-warning fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-warning">{{ $rentasActivas }}</h3>
                @if($rentasVencidas > 0)
                    <span class="kpi-sub text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $rentasVencidas }} vencidos</span>
                @else
                    <span class="kpi-sub">Sin contratos vencidos</span>
                @endif
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-info shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Clientes</span><i class="bi bi-people-fill text-info fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-info">{{ $totalClientes }}</h3>
                <a href="{{ route('clientes.index') }}" class="kpi-sub text-decoration-none fw-bold">Gestionar directorio <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="kpi-card border-start border-4 border-warning shadow-sm">
                <span class="kpi-title">Cartera por Cobrar</span>
                <h3 class="kpi-value font-mono text-warning">${{ number_format($cartera['por_cobrar'], 2) }}</h3>
                <a href="{{ route('puntoventa.creditos') }}" class="kpi-sub text-decoration-none fw-bold">{{ $cartera['n_abiertos'] }} créditos <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="kpi-card border-start border-4 border-danger shadow-sm">
                <span class="kpi-title">Cartera Vencida</span>
                <h3 class="kpi-value font-mono {{ $cartera['vencido'] > 0 ? 'text-danger' : 'text-body' }}">${{ number_format($cartera['vencido'], 2) }}</h3>
                <span class="kpi-sub">{{ $cartera['n_vencidos'] }} vencido(s)</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="kpi-card border-start border-4 border-success shadow-sm">
                <span class="kpi-title">Equipos en Catálogo</span>
                <h3 class="kpi-value font-mono">{{ $totalEquipos }}</h3>
                <span class="kpi-sub">{{ $totalStock }} uds
                    @if($stockBajo > 0)· <span class="text-warning-emphasis fw-bold">{{ $stockBajo }} bajos</span>@endif
                    @if($stockAgotado > 0)· <span class="text-danger fw-bold">{{ $stockAgotado }} agotados</span>@endif
                </span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <span class="kpi-title">Tasa de Cierre</span>
                <h3 class="kpi-value font-mono">{{ number_format($tasaCierre, 0) }}%</h3>
                <span class="kpi-sub">{{ $rentasFinalizadas }} de {{ $rentasNoCanceladas }} finalizadas</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <div class="kpi-card border-start border-4 border-secondary shadow-sm">
                <span class="kpi-title">Rentas Históricas</span>
                <h3 class="kpi-value font-mono">{{ $rentasTotales }}</h3>
                <a href="{{ route('rentas.index') }}" class="kpi-sub text-decoration-none fw-bold">Ver historial <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <!-- FILTRO DE PERÍODO (auto-submit) -->
    <div class="filter-card mb-4 shadow-sm">
        <form id="formFiltroFechas" action="{{ url()->current() }}" method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="tab" id="tabInput" value="{{ $tabActiva }}">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">Fecha Inicial (Desde)</label>
                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm bg-body text-body font-mono" value="{{ $inicio->format('Y-m-d') }}" required>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-secondary mb-1">Fecha Final (Hasta)</label>
                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm bg-body text-body font-mono" value="{{ $fin->format('Y-m-d') }}" required>
            </div>
            <div class="col-12 col-md-6 d-flex flex-wrap gap-2 justify-content-md-end">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3" data-preset="hoy">Hoy</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3" data-preset="7">7 días</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3" data-preset="mes">Este mes</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3" data-preset="90">90 días</button>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-bold px-3" data-preset="anio">Este año</button>
            </div>
        </form>
    </div>

    <!-- KPIs DEL PERÍODO -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-primary shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Ingresos del Período</span><i class="bi bi-graph-up-arrow text-primary fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-primary">${{ number_format($ingresosPeriodo, 2) }}</h3>
                <span class="kpi-sub">Del {{ $inicio->format('d/m/Y') }} al {{ $fin->format('d/m/Y') }}</span>
                @if($varPeriodo !== null)
                    <span class="var-badge ms-1 {{ $varPeriodo > 0.5 ? 'var-up' : ($varPeriodo < -0.5 ? 'var-down' : 'var-flat') }}" title="Período anterior: ${{ number_format($ingresosPeriodoAnterior, 2) }}">
                        <i class="bi bi-{{ $varPeriodo >= 0 ? 'arrow-up-right' : 'arrow-down-right' }}"></i> {{ number_format(abs($varPeriodo), 1) }}%
                    </span>
                @endif
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-success shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Ventas POS</span><i class="bi bi-shop text-success fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-success">${{ number_format($ventasPeriodoMonto, 2) }}</h3>
                <span class="kpi-sub">{{ $ventasPeriodoN }} ventas completadas</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-info shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Cobranza de Rentas</span><i class="bi bi-calendar2-range text-info fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-info">${{ number_format($rentasPeriodoMonto, 2) }}</h3>
                <span class="kpi-sub">Pagos registrados en el período</span>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card border-start border-4 border-warning shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="kpi-title">Ticket Promedio POS</span><i class="bi bi-receipt text-warning fs-5"></i>
                </div>
                <h3 class="kpi-value font-mono text-warning">${{ number_format($ticketPromedio, 2) }}</h3>
                <span class="kpi-sub">Ventas ÷ transacciones</span>
            </div>
        </div>
    </div>

    <!-- PESTAÑAS -->
    <ul class="nav nav-tabs mb-4" id="dashTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold {{ $tabActiva === 'resumen' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#paneResumen" data-tab="resumen" type="button" role="tab">
                <i class="bi bi-bar-chart-line-fill me-1"></i> Resumen
            </button>
        </li>
        @if($hayTabSuc)
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold {{ $tabActiva === 'sucursales' ? 'active' : '' }}" data-bs-toggle="tab" data-bs-target="#paneSucursales" data-tab="sucursales" type="button" role="tab">
                <i class="bi bi-diagram-3-fill me-1"></i> Comparativo por Sucursal
            </button>
        </li>
        @endif
    </ul>

    <div class="tab-content">

    <!-- ===================== PESTAÑA RESUMEN ===================== -->
    <div class="tab-pane fade {{ $tabActiva === 'resumen' ? 'show active' : '' }}" id="paneResumen" role="tabpanel" tabindex="0">
        <div class="row g-3 mb-4">
            <!-- Actividad reciente -->
            <div class="col-12 col-xl-8">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                    <div class="card-header bg-body border-bottom py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-clock-history text-success me-2"></i>Actividad Reciente (Rentas)</h6>
                        <a href="{{ route('rentas.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill fw-bold px-3">Ver todas</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-executive mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th class="ps-3">Folio</th><th>Cliente</th>
                                    <th class="d-none d-md-table-cell text-center">F. Inicio</th>
                                    <th class="text-end">Monto Total</th>
                                    <th class="text-center d-none d-sm-table-cell">Estado</th>
                                    <th class="text-end pe-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($ultimasRentas as $renta)
                                <tr>
                                    <td class="ps-3"><span class="badge bg-body-secondary text-body border font-mono">{{ $renta->folio }}</span></td>
                                    <td class="fw-semibold text-truncate" style="max-width: 160px;">{{ $renta->cliente->nombre_completo ?? 'N/A' }}</td>
                                    <td class="d-none d-md-table-cell text-center text-secondary font-mono">{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                                    <td class="text-end font-mono fw-bold text-success">${{ number_format($renta->total, 2) }}</td>
                                    <td class="text-center d-none d-sm-table-cell">
                                        @if($renta->estado == 'activa')
                                            @if($renta->estaVencida())
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill" title="{{ $renta->dias_retraso }} día(s) de retraso">Vencida ({{ $renta->dias_retraso }}d)</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">En curso</span>
                                            @endif
                                        @elseif($renta->estado == 'cancelada')
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Cancelada</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Cerrada</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-light border rounded-circle shadow-sm text-secondary" style="width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-chevron-right"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="sin-datos"><i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>Sin registros recientes.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Alertas + Top clientes + Accesos -->
            <div class="col-12 col-xl-4 d-flex flex-column gap-3">
                <!-- <div class="chart-card shadow-sm">
                    @if($rentasVencidas > 0 || $stockAgotado > 0 || $stockBajo > 0)
                        <div class="chart-title text-danger"><span><i class="bi bi-exclamation-octagon-fill me-1"></i> Alertas Operativas</span></div>
                        @if($rentasVencidas > 0)
                            <div class="alert-row is-danger">
                                <div class="ic"><i class="bi bi-calendar-x-fill"></i></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ $rentasVencidas }} {{ $rentasVencidas == 1 ? 'contrato vencido' : 'contratos vencidos' }}</div>
                                    <a href="{{ route('rentas.index') }}" class="small fw-bold text-danger text-decoration-none">Revisar ahora <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        @endif
                        @if($stockAgotado > 0)
                            <div class="alert-row is-danger">
                                <div class="ic"><i class="bi bi-box-fill"></i></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ $stockAgotado }} {{ $stockAgotado == 1 ? 'equipo agotado' : 'equipos agotados' }}</div>
                                    <a href="{{ route('inventario.index') }}" class="small fw-bold text-danger text-decoration-none">Ver inventario <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        @endif
                        @if($stockBajo > 0)
                            <div class="alert-row is-warning">
                                <div class="ic"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                <div class="flex-grow-1">
                                    <div class="fw-bold small">{{ $stockBajo }} {{ $stockBajo == 1 ? 'equipo con stock crítico' : 'equipos con stock crítico' }}</div>
                                    <a href="{{ route('inventario.index') }}" class="small fw-bold text-warning-emphasis text-decoration-none">Ver inventario <i class="bi bi-arrow-right"></i></a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="chart-title text-success"><span><i class="bi bi-check-circle-fill me-1"></i> Estado Operativo</span></div>
                        <div class="alert-row is-ok">
                            <div class="ic"><i class="bi bi-emoji-smile-fill"></i></div>
                            <div class="fw-bold small">Todo en orden: sin vencidos ni faltantes de stock.</div>
                        </div>
                    @endif
                </div> -->

                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-award-fill text-warning me-1"></i> Top Clientes</span></div>
                    @forelse($topClientes as $index => $cliente)
                        <div class="d-flex align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <span class="rank-badge rank-{{ $index + 1 }} me-2">{{ $index + 1 }}</span>
                            <span class="flex-grow-1 text-truncate pe-2 fw-semibold small">{{ $cliente->cliente_nombre }}</span>
                            <span class="badge bg-body-secondary text-body-secondary border rounded-pill font-mono">{{ $cliente->total_rentas }} <i class="bi bi-file-text ms-1"></i></span>
                        </div>
                    @empty
                        <div class="sin-datos py-4">Faltan datos para el ranking.</div>
                    @endforelse
                </div>

                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-grid-1x2-fill me-1"></i> Centro de Mando</span></div>
                    <div class="row g-2">
                        <div class="col-6 col-sm-3 col-xl-6"><a href="{{ route('configuracion.index') }}" class="tool-tile"><i class="bi bi-sliders text-secondary"></i><span>Ajustes</span></a></div>
                        <div class="col-6 col-sm-3 col-xl-6"><a href="{{ route('inventario.index') }}" class="tool-tile"><i class="bi bi-boxes text-success"></i><span>Catálogo</span></a></div>
                        <div class="col-6 col-sm-3 col-xl-6"><a href="{{ route('movimientos.create') }}" class="tool-tile"><i class="bi bi-truck text-info"></i><span>Traslados</span></a></div>
                        <div class="col-6 col-sm-3 col-xl-6"><a href="{{ route('puntoventa.index') }}" class="tool-tile"><i class="bi bi-cart-check-fill text-danger"></i><span>Caja POS</span></a></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-lightning-fill text-warning me-1"></i> Flujo de Ingresos del Período</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">{{ $flujo['por_mes'] ? 'Mensual' : 'Diario' }}</span>
                    </div>
                    <div id="chFlujo"></div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-pie-chart-fill text-primary me-1"></i> Estado de Contratos</span></div>
                    <div id="chEstado"></div>
                </div>
            </div>

            <div class="col-12 col-xl-8">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-calendar3 text-primary me-1"></i> Rendimiento Anual (Contratos vs Ventas)</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">{{ date('Y') }}</span>
                    </div>
                    <div id="chMensual"></div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-credit-card-2-front text-warning me-1"></i> Salud de la Cartera</span></div>
                    <div id="chCartera"></div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-wallet2 text-success me-1"></i> Ventas por Método de Pago</span></div>
                    <div id="chMetodos"></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-tools text-info me-1"></i> Equipos Más Rentados</span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">Top</span>
                    </div>
                    <div id="chEquipos"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- ===================== PESTAÑA SUCURSALES ===================== -->
    @if($hayTabSuc)
    <div class="tab-pane fade {{ $tabActiva === 'sucursales' ? 'show active' : '' }}" id="paneSucursales" role="tabpanel" tabindex="0">
        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-7">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-bar-chart-fill text-primary me-1"></i> Ingresos por Sucursal</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">{{ $inicio->format('d/m') }} – {{ $fin->format('d/m') }}</span>
                    </div>
                    <div id="chSucComp"></div>
                </div>
            </div>
            <div class="col-12 col-xl-5">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-pie-chart-fill text-success me-1"></i> Participación en Ingresos</span></div>
                    <div id="chSucPart"></div>
                </div>
            </div>
            @if($sucursalesTendencia && count($sucursalesTendencia['series'] ?? []) > 0)
            <div class="col-12 col-xl-7">
                <div class="chart-card shadow-sm">
                    <div class="chart-title">
                        <span><i class="bi bi-graph-up-arrow text-info me-1"></i> Tendencia por Sucursal</span>
                        <span class="badge bg-body-tertiary text-secondary border font-mono">6 meses</span>
                    </div>
                    <div id="chSucTend"></div>
                </div>
            </div>
            @endif
            <div class="{{ $sucursalesTendencia ? 'col-12 col-xl-5' : 'col-12' }}">
                <div class="chart-card shadow-sm">
                    <div class="chart-title"><span><i class="bi bi-box-seam-fill text-warning me-1"></i> Salud de Inventario por Sucursal</span></div>
                    <div id="chSucInv"></div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4 rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body border-bottom py-3">
                <h6 class="fw-bold mb-0 text-body small text-uppercase"><i class="bi bi-trophy-fill text-warning me-2"></i>Ranking de Sucursales</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-executive mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="ps-3">#</th><th>Sucursal</th>
                            <th class="text-end">Ventas</th><th class="text-end">Cobranza Rentas</th>
                            <th class="text-end">Total</th><th class="text-center">vs Período Ant.</th>
                            <th style="min-width:130px;">Participación</th>
                            <th class="text-center">Vencidos</th><th class="text-center pe-3">Stock Crítico</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($suc as $i => $s)
                            @php
                                $tot = ($s->ventas ?? 0) + ($s->rentas ?? 0);
                                $ant = $s->mes_anterior ?? null;
                                $v = ($ant !== null && $ant > 0) ? (($tot - $ant) / $ant) * 100 : null;
                                $pct = ($tot / $totalSuc) * 100;
                                $crit = ($s->stock_bajo ?? 0) + ($s->stock_agotado ?? 0);
                            @endphp
                            <tr>
                                <td class="ps-3"><span class="rank-badge rank-{{ $i + 1 }}">{{ $i + 1 }}</span></td>
                                <td class="fw-semibold">{{ $s->nombre }}</td>
                                <td class="text-end font-mono">${{ number_format($s->ventas ?? 0, 2) }}</td>
                                <td class="text-end font-mono">${{ number_format($s->rentas ?? 0, 2) }}</td>
                                <td class="text-end font-mono fw-bold text-success">${{ number_format($tot, 2) }}</td>
                                <td class="text-center">
                                    @if($v === null)<span class="text-secondary">—</span>
                                    @else<span class="var-badge {{ $v > 0.5 ? 'var-up' : ($v < -0.5 ? 'var-down' : 'var-flat') }}">{{ $v >= 0 ? '+' : '-' }}{{ number_format(abs($v), 1) }}%</span>@endif
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="share-bar flex-grow-1"><span style="width: {{ min($pct, 100) }}%"></span></div>
                                        <small class="text-secondary font-mono" style="width:38px;">{{ number_format($pct, 0) }}%</small>
                                    </div>
                                </td>
                                <td class="text-center">@if(($s->vencidas ?? 0) > 0)<span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $s->vencidas }}</span>@else<span class="text-secondary">0</span>@endif</td>
                                <td class="text-center pe-3">@if($crit > 0)<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $crit }}</span>@else<span class="text-secondary">0</span>@endif</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="fw-bold">
                            <td></td><td class="text-uppercase small">Total</td>
                            <td class="text-end font-mono">${{ number_format($suc->sum('ventas'), 2) }}</td>
                            <td class="text-end font-mono">${{ number_format($suc->sum('rentas'), 2) }}</td>
                            <td class="text-end font-mono text-success">${{ number_format($totalSuc, 2) }}</td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    </div><!-- /tab-content -->
</div>

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ---------- Notificación de autorizaciones pendientes
    function revisarAutorizacionesDashboard() {
        fetch('{{ route("autorizaciones.notificaciones") }}')
            .then(r => r.json())
            .then(data => {
                const badge = document.getElementById('badge-autorizaciones-dashboard');
                if (!badge) return;
                if (data.count > 0) { badge.textContent = data.count; badge.classList.remove('d-none'); }
                else { badge.classList.add('d-none'); }
            })
            .catch(e => console.error('Error al revisar notificaciones:', e));
    }
    revisarAutorizacionesDashboard();
    setInterval(revisarAutorizacionesDashboard, 15000);

    // ---------- Filtro de fechas: auto-submit + atajos
    const form = document.getElementById('formFiltroFechas');
    const fi = document.getElementById('fecha_inicio');
    const ff = document.getElementById('fecha_fin');
    const iso = d => d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
    const enviar = () => { if (fi.value && ff.value) form.submit(); };
    fi.addEventListener('change', enviar);
    ff.addEventListener('change', enviar);
    document.querySelectorAll('[data-preset]').forEach(btn => btn.addEventListener('click', () => {
        const hoy = new Date(); let ini = new Date();
        switch (btn.dataset.preset) {
            case 'hoy': break;
            case '7': ini.setDate(hoy.getDate() - 6); break;
            case '90': ini.setDate(hoy.getDate() - 89); break;
            case 'mes': ini = new Date(hoy.getFullYear(), hoy.getMonth(), 1); break;
            case 'anio': ini = new Date(hoy.getFullYear(), 0, 1); break;
        }
        fi.value = iso(ini); ff.value = iso(hoy); enviar();
    }));

    // ---------- Datos
    const F   = @json($flujo);
    const M   = { rentas: @json($rentasPorMes->pluck('total')), ventas: @json($ventasPorMes->pluck('monto')->map(fn($v) => (float) $v)), meses: @json($rentasPorMes->pluck('mes_nombre')) };
    const EST = [{{ $rentasEnCurso }}, {{ $rentasVencidas }}, {{ $rentasFinalizadas }}, {{ $rentasCanceladas }}];
    const CAR = [{{ max(($cartera['por_cobrar'] ?? 0) - ($cartera['vencido'] ?? 0), 0) }}, {{ $cartera['vencido'] ?? 0 }}];
    const MET = @json($ventasPorMetodoPago->values());
    const EQU = @json($topEquipos->values());
    const SUC = @json($suc->values());
    const TEN = @json($sucursalesTendencia);

    // ---------- Utilidades / tema
    const money      = v => '$' + Number(v || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const moneyCorto = v => '$' + Number(v || 0).toLocaleString('es-MX', { maximumFractionDigits: 0 });
    const html = document.documentElement;
    const colors = () => {
        const dark = (html.getAttribute('data-bs-theme') || 'light') === 'dark';
        return { dark, label: dark ? '#a1a1aa' : '#475467', grid: dark ? 'rgba(255,255,255,0.08)' : '#e4e4e7' };
    };
    const PAL = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#84cc16'];
    const sumar = w => moneyCorto(w.globals.seriesTotals.reduce((a, b) => a + b, 0));

    // ---------- Definición de gráficas: id -> [datos para validar vacío, constructor de opciones]
    const defs = {
        chFlujo: () => [F.ventas.concat(F.rentas), c => ({
            chart: { type: 'area', height: 300 }, colors: ['#10b981', '#3b82f6'],
            stroke: { curve: 'smooth', width: 2 }, dataLabels: { enabled: false },
            fill: { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0.05 } },
            series: [{ name: 'Ventas POS', data: F.ventas }, { name: 'Cobranza de Rentas', data: F.rentas }],
            xaxis: { categories: F.labels, labels: { style: { colors: c.label }, rotate: -45, hideOverlappingLabels: true } },
            yaxis: { labels: { style: { colors: c.label }, formatter: moneyCorto } },
            tooltip: { y: { formatter: money } }, legend: { position: 'top' }
        })],
        chMensual: () => [M.rentas.concat(M.ventas), c => ({
            chart: { type: 'line', height: 300 }, colors: ['#3b82f6', '#10b981'],
            stroke: { width: [0, 3], curve: 'smooth' }, dataLabels: { enabled: false },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '45%' } },
            series: [{ name: 'Contratos', type: 'column', data: M.rentas }, { name: 'Ventas ($)', type: 'line', data: M.ventas }],
            xaxis: { categories: M.meses, labels: { style: { colors: c.label } } },
            yaxis: [
                { title: { text: '# Contratos', style: { color: c.label } }, labels: { style: { colors: c.label }, formatter: v => Math.round(v) } },
                { opposite: true, title: { text: 'Ventas ($)', style: { color: c.label } }, labels: { style: { colors: c.label }, formatter: moneyCorto } }
            ],
            tooltip: { shared: true, y: [{ formatter: v => v + ' contratos' }, { formatter: money }] },
            legend: { position: 'top' }
        })],
        chEstado: () => [EST, c => ({
            chart: { type: 'donut', height: 300 }, colors: ['#10b981', '#ef4444', '#64748b', '#cbd5e1'],
            series: EST, labels: ['En curso', 'Vencidos', 'Finalizados', 'Cancelados'],
            dataLabels: { enabled: false }, legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Contratos', color: c.label } } } } }
        })],
        chCartera: () => [CAR, c => ({
            chart: { type: 'donut', height: 300 }, colors: ['#f59e0b', '#ef4444'],
            series: CAR, labels: ['Vigente', 'Vencido'], dataLabels: { enabled: false },
            legend: { position: 'bottom' }, tooltip: { y: { formatter: money } },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Por cobrar', color: c.label, formatter: sumar } } } } }
        })],
        chMetodos: () => [MET.map(m => m.total), c => ({
            chart: { type: 'donut', height: 300 }, colors: PAL,
            series: MET.map(m => m.total), labels: MET.map(m => m.metodo),
            dataLabels: { enabled: false }, legend: { position: 'bottom' }, tooltip: { y: { formatter: money } },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', color: c.label, formatter: sumar } } } } }
        })],
        chEquipos: () => [EQU.map(e => e.veces), c => ({
            chart: { type: 'bar', height: Math.max(260, EQU.length * 44) }, colors: ['#06b6d4'],
            plotOptions: { bar: { horizontal: true, barHeight: '45%', borderRadius: 4 } },
            dataLabels: { enabled: true, formatter: v => v + ' uds' },
            series: [{ name: 'Piezas rentadas', data: EQU.map(e => e.veces) }],
            xaxis: { categories: EQU.map(e => e.nombre), labels: { style: { colors: c.label } } },
            yaxis: { labels: { style: { colors: c.label } } }
        })],
        chSucComp: () => [SUC.map(s => (+s.ventas || 0) + (+s.rentas || 0)), c => ({
            chart: { type: 'bar', height: 320 }, colors: ['#10b981', '#3b82f6', '#94a3b8'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } }, dataLabels: { enabled: false },
            series: [
                { name: 'Ventas POS', data: SUC.map(s => +s.ventas || 0) },
                { name: 'Cobranza Rentas', data: SUC.map(s => +s.rentas || 0) },
                { name: 'Período anterior (total)', data: SUC.map(s => +s.mes_anterior || 0) }
            ],
            xaxis: { categories: SUC.map(s => s.nombre), labels: { style: { colors: c.label } } },
            yaxis: { labels: { style: { colors: c.label }, formatter: moneyCorto } },
            tooltip: { y: { formatter: money } }, legend: { position: 'top' }
        })],
        chSucPart: () => [SUC.map(s => (+s.ventas || 0) + (+s.rentas || 0)), c => ({
            chart: { type: 'donut', height: 320 }, colors: PAL,
            series: SUC.map(s => (+s.ventas || 0) + (+s.rentas || 0)), labels: SUC.map(s => s.nombre),
            dataLabels: { enabled: false }, legend: { position: 'bottom' }, tooltip: { y: { formatter: money } },
            plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'Total', color: c.label, formatter: sumar } } } } }
        })],
        chSucTend: () => [TEN && TEN.series ? [].concat(...TEN.series.map(s => s.data)) : [], c => ({
            chart: { type: 'line', height: 300 }, colors: PAL,
            stroke: { curve: 'smooth', width: 3 }, markers: { size: 4 }, dataLabels: { enabled: false },
            series: TEN.series.map(s => ({ name: s.nombre, data: s.data })),
            xaxis: { categories: TEN.labels, labels: { style: { colors: c.label } } },
            yaxis: { labels: { style: { colors: c.label }, formatter: moneyCorto } },
            tooltip: { y: { formatter: money } }, legend: { position: 'top' }
        })],
        chSucInv: () => [SUC.map(s => (+s.stock_bajo || 0) + (+s.stock_agotado || 0)), c => ({
            chart: { type: 'bar', height: 300, stacked: true }, colors: ['#f59e0b', '#ef4444'],
            plotOptions: { bar: { borderRadius: 4, columnWidth: '50%' } }, dataLabels: { enabled: false },
            series: [{ name: 'Stock crítico', data: SUC.map(s => +s.stock_bajo || 0) }, { name: 'Agotados', data: SUC.map(s => +s.stock_agotado || 0) }],
            xaxis: { categories: SUC.map(s => s.nombre), labels: { style: { colors: c.label } } },
            yaxis: { labels: { style: { colors: c.label }, formatter: v => Math.round(v) } },
            legend: { position: 'top' }
        })]
    };

    // ---------- Render (respeta tema; las pestañas ocultas se dibujan al abrirse)
    const vivos = {};
    function dibujar(id) {
        const el = document.getElementById(id);
        if (!el || !defs[id]) return;
        if (vivos[id]) { vivos[id].destroy(); delete vivos[id]; }
        const [datos, build] = defs[id]();
        if (!datos.some(v => Number(v) > 0)) {
            el.innerHTML = '<div class="sin-datos">Sin datos en el período seleccionado.</div>';
            return;
        }
        el.innerHTML = '';
        const c = colors();
        const o = build(c);
        o.theme = { mode: c.dark ? 'dark' : 'light' };
        o.chart = Object.assign({ background: 'transparent', toolbar: { show: false }, fontFamily: 'system-ui, -apple-system, sans-serif' }, o.chart);
        o.grid = { borderColor: c.grid };
        o.legend = Object.assign({ labels: { colors: c.label } }, o.legend);
        vivos[id] = new ApexCharts(el, o);
        vivos[id].render();
    }
    const dibujarPanel = pane => pane && pane.querySelectorAll('[id^="ch"]').forEach(n => dibujar(n.id));

    dibujarPanel(document.querySelector('.tab-pane.active'));

    // Pestañas: guarda la activa para el filtro y dibuja sus gráficas al abrirla
    const tabInput = document.getElementById('tabInput');
    document.querySelectorAll('#dashTabs [data-bs-toggle="tab"]').forEach(btn => {
        btn.addEventListener('shown.bs.tab', e => {
            tabInput.value = e.target.dataset.tab;
            dibujarPanel(document.querySelector(e.target.dataset.bsTarget));
        });
    });

    // Cambio de tema claro/oscuro: redibuja lo ya dibujado
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => setTimeout(() => Object.keys(vivos).forEach(dibujar), 80));
    }
});
</script>
@endsection