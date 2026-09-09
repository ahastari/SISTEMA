@extends('layouts.admin')

@section('content')
<style>
    /* TIPOGRAFÍA Y TÍTULOS */
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }

    /* PESTAÑAS PREMIUM (Consistentes con el resto del sistema) */
    .premium-tabs {
        border-bottom: 1px solid var(--bs-border-color);
        gap: 8px;
        margin-bottom: 24px;
    }
    .premium-tabs .nav-link {
        color: var(--bs-secondary-color);
        font-weight: 600;
        font-size: 14px;
        padding: 12px 20px;
        border: none;
        background: transparent;
        border-bottom: 3px solid transparent;
        border-radius: 6px 6px 0 0;
        transition: all 0.2s ease;
    }
    .premium-tabs .nav-link:hover:not(.active) {
        color: var(--bs-body-color);
        background: var(--bs-tertiary-bg);
    }
    .premium-tabs .nav-link.active {
        color: var(--bs-primary);
        border-bottom-color: var(--bs-primary);
        background: var(--bs-primary-bg-subtle);
        font-weight: 700;
    }

    /* TARJETAS DE AUTORIZACIÓN (UX/UI MEJORADO) */
    .auth-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
    }
    .auth-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08) !important;
    }
    
    /* Borde lateral de color para identificar el tipo rápidamente */
    .auth-card.type-renta { border-left: 4px solid #ffc107; }
    .auth-card.type-movimiento { border-left: 4px solid #0dcaf0; }
    .auth-card.type-venta { border-left: 4px solid #dc3545; }

    .auth-header {
        padding: 12px 16px;
        border-bottom: 1px dashed var(--bs-border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: var(--bs-tertiary-bg);
    }
    .auth-body {
        padding: 16px;
        flex-grow: 1;
    }
    .auth-footer {
        padding: 12px 16px;
        background: var(--bs-tertiary-bg);
        border-top: 1px solid var(--bs-border-color);
    }

    /* MINI-STATS GLOBALES */
    .stat-pill {
        display: inline-flex;
        align-items: center;
        padding: 8px 16px;
        border-radius: 50rem;
        font-weight: 600;
        font-size: 13px;
        border: 1px solid;
        background: var(--bs-body-bg);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .stat-pill:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08);
        filter: brightness(0.95);
    }
    
    html {
        scroll-behavior: smooth;
    }

    .seccion-ancla {
        scroll-margin-top: 90px;
    }
    
    /* ESTADO VACÍO GLOBAL */
    .empty-state-global {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        background: var(--bs-body-bg);
        border: 1px dashed var(--bs-border-color);
        border-radius: 20px;
        text-align: center;
    }
    .empty-state-icon {
        width: 80px;
        height: 80px;
        background: var(--bs-success-bg-subtle);
        color: var(--bs-success);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        margin-bottom: 16px;
    }
</style>

<!-- HEADER DE SECCIÓN -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Panel de Autorizaciones
        </h2>
        <p class="text-body-secondary small mb-0">Revisión y resolución de operaciones restringidas del sistema.</p>
    </div>
</div>

@php
    $totalRentas = $autorizacionesRentas->count();
    $totalRentasCanc = $rentasCancelacion->count();
    $totalMovs = $movimientosPendientes->count();
    $totalVentas = $autorizacionesVentas->count();
    $totalPendientes = $totalRentas + $totalRentasCanc + $totalMovs + $totalVentas;
@endphp

<!-- PESTAÑAS DE NAVEGACIÓN -->
<ul class="nav premium-tabs" id="autorizacionesTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Solicitudes Pendientes 
            @if($totalPendientes > 0)
                <span class="badge bg-danger ms-1 rounded-pill" id="badge-contador-tabs">{{ $totalPendientes }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
            <i class="bi bi-archive me-1"></i> Historial de Resoluciones
        </button>
    </li>
</ul>

<div class="tab-content" id="autorizacionesTabsContent">
    
    <!-- ========================================== -->
    <!-- PESTAÑA: PENDIENTES -->
    <!-- ========================================== -->
    <div class="tab-pane fade show active" id="pendientes" role="tabpanel" tabindex="0">
        <div id="contenedor-autorizaciones">

            @if($totalPendientes === 0)
                <div class="empty-state-global shadow-sm">
                    <div class="empty-state-icon">
                        <i class="bi bi-check2-all"></i>
                    </div>
                    <h4 class="fw-bold text-body">¡Estás al día!</h4>
                    <p class="text-secondary mb-0">No hay ninguna solicitud pendiente de autorización en este momento.</p>
                </div>
            @else
                <!-- QUICK STATS -->
                <div class="d-flex flex-wrap gap-3 mb-4">
                    @if($totalRentas > 0)
                    <a href="#seccion-rentas" class="stat-pill border-warning text-warning-emphasis">
                        <i class="bi bi-file-earmark-text me-2"></i> {{ $totalRentas }} Rentas por cerrar
                    </a>
                    @endif
                    
                    @if($totalMovs > 0)
                    <a href="#seccion-movimientos" class="stat-pill border-info text-info-emphasis">
                        <i class="bi bi-arrow-left-right me-2"></i> {{ $totalMovs }} Transferencias
                    </a>
                    @endif

                    @if($totalVentas > 0)
                    <a href="#seccion-ventas" class="stat-pill border-danger text-danger-emphasis">
                        <i class="bi bi-cart-x me-2"></i> {{ $totalVentas }} Cancelaciones Venta
                    </a>
                    @endif

                    @if($totalRentasCanc > 0)
                    <a href="#seccion-rentas-canc" class="stat-pill border-danger text-danger-emphasis">
                        <i class="bi bi-x-octagon me-2"></i> {{ $totalRentasCanc }} Cancelaciones Renta
                    </a>
                    @endif
                </div>

                <!-- 1. SOLICITUDES DE RENTAS (CIERRE CON ADEUDO) -->
                @if($totalRentas > 0)
                <div id="seccion-rentas" class="mb-5 seccion-ancla">
                    <h6 class="fw-bold text-body mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-circle-fill text-warning me-2" style="font-size: 8px; vertical-align: middle;"></i>Cierres de Renta con Adeudo
                    </h6>
                    <div class="row g-3">
                        @foreach($autorizacionesRentas as $renta)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="auth-card type-renta">
                                <div class="auth-header">
                                    <span class="badge bg-warning text-dark font-monospace px-2 py-1"><i class="bi bi-file-text me-1"></i>{{ $renta->folio }}</span>
                                    <span class="text-secondary" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ $renta->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="auth-body">
                                    <h6 class="fw-bold text-body mb-2 text-truncate" title="{{ $renta->cliente->nombre_completo ?? 'Cliente General' }}">
                                        <i class="bi bi-person text-warning me-1"></i> {{ $renta->cliente->nombre_completo ?? 'Cliente General' }}
                                    </h6>
                                    <div class="p-2 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 small text-body mb-3">
                                        <strong class="text-warning-emphasis d-block mb-1"><i class="bi bi-info-circle me-1"></i>Motivo:</strong>
                                        {{ $renta->motivo_autorizacion }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <span class="d-block text-secondary" style="font-size: 11px;">Solicitó:</span>
                                            <span class="fw-semibold text-body small">{{ $renta->solicitadoPor->name ?? 'Usuario' }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-secondary" style="font-size: 11px;">Deuda a perdonar:</span>
                                            <!-- AQUI USAMOS EL SALDO PENDIENTE REAL QUE CREASTE EN EL CONTROLADOR -->
                                            <strong class="text-danger fs-6">${{ number_format($renta->saldo_pendiente_real ?? $renta->saldo_pendiente, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="auth-footer d-flex gap-2">
                                    <form action="{{ route('autorizaciones.aprobar', $renta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-success btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Confirmas que deseas APROBAR esta renta y perdonar el adeudo?">
                                            <i class="bi bi-check-lg me-1"></i> Aprobar
                                        </button>
                                    </form>
                                    <form action="{{ route('autorizaciones.rechazar', $renta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Confirmas que deseas RECHAZAR la petición y mantener el cobro?">
                                            <i class="bi bi-x-lg me-1"></i> Rechazar
                                        </button>
                                    </form>
                                    <a href="{{ route('rentas.show', $renta) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3" title="Ver Detalles">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 2. SOLICITUDES DE TRANSFERENCIAS -->
                @if($totalMovs > 0)
                <div id="seccion-movimientos" class="mb-5 seccion-ancla">
                    <h6 class="fw-bold text-body mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-circle-fill text-info me-2" style="font-size: 8px; vertical-align: middle;"></i>Transferencias de Inventario
                    </h6>
                    <div class="row g-3">
                        @foreach($movimientosPendientes as $movimiento)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="auth-card type-movimiento">
                                <div class="auth-header">
                                    <span class="badge bg-info text-dark font-monospace px-2 py-1"><i class="bi bi-box-seam me-1"></i>Stock Req.</span>
                                    <span class="text-secondary" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ $movimiento->fecha_movimiento->diffForHumans() }}</span>
                                </div>
                                <div class="auth-body">
                                    <h6 class="fw-bold text-body mb-1 text-truncate" title="{{ $movimiento->equipo->nombre }}">
                                        {{ $movimiento->equipo->nombre }}
                                    </h6>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace mb-3">{{ $movimiento->equipo->codigo }}</span>
                                    
                                    <div class="d-flex align-items-center justify-content-between bg-body-tertiary border rounded-3 p-2 mb-3 small">
                                        <div class="text-center w-50 border-end">
                                            <span class="d-block text-secondary" style="font-size: 10px;">Origen</span>
                                            <strong class="text-danger text-truncate d-block px-1">{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="text-center w-50">
                                            <span class="d-block text-secondary" style="font-size: 10px;">Destino</span>
                                            <strong class="text-success text-truncate d-block px-1">{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}</strong>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-end">
                                        <div style="max-width: 60%;">
                                            <span class="d-block text-secondary" style="font-size: 11px;">Motivo:</span>
                                            <span class="fw-semibold text-body small text-truncate d-block" title="{{ $movimiento->motivo }}">{{ $movimiento->motivo }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-secondary" style="font-size: 11px;">Cantidad:</span>
                                            <strong class="text-info-emphasis fs-6">{{ $movimiento->cantidad }} <small>uds</small></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="auth-footer d-flex gap-2">
                                    <form action="{{ route('movimientos.aprobar', $movimiento) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-success btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Aprobar transferencia y poner el stock en tránsito?">
                                            <i class="bi bi-check-lg me-1"></i> Aprobar
                                        </button>
                                    </form>
                                    <form action="{{ route('movimientos.rechazar', $movimiento) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Rechazar solicitud y devolver el stock apartado a origen?">
                                            <i class="bi bi-x-lg me-1"></i> Rechazar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 3. SOLICITUDES DE VENTAS -->
                @if($totalVentas > 0)
                <div id="seccion-ventas" class="mb-5 seccion-ancla">
                    <h6 class="fw-bold text-body mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-circle-fill text-danger me-2" style="font-size: 8px; vertical-align: middle;"></i>Cancelaciones de Venta
                    </h6>
                    <div class="row g-3">
                        @foreach($autorizacionesVentas as $venta)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="auth-card type-venta">
                                <div class="auth-header">
                                    <span class="badge bg-danger text-white font-monospace px-2 py-1"><i class="bi bi-receipt me-1"></i>{{ $venta->folio }}</span>
                                    <span class="text-secondary" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ $venta->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="auth-body">
                                    <h6 class="fw-bold text-body mb-2 text-truncate" title="{{ $venta->cliente_nombre ?? 'Público General' }}">
                                        <i class="bi bi-person text-danger me-1"></i> {{ $venta->cliente_nombre ?? 'Público General' }}
                                    </h6>
                                    <div class="p-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 small text-body mb-3">
                                        <strong class="text-danger d-block mb-1"><i class="bi bi-info-circle me-1"></i>Justificación Cajero:</strong>
                                        {{ $venta->motivo_cancelacion }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <span class="d-block text-secondary" style="font-size: 11px;">Método Origen:</span>
                                            <span class="fw-bold text-body small text-uppercase"><i class="bi bi-wallet2 text-secondary me-1"></i>{{ $venta->metodo_pago }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-secondary" style="font-size: 11px;">Monto a devolver:</span>
                                            <strong class="text-danger fs-6">${{ number_format($venta->total, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="auth-footer d-flex gap-2">
                                    <form action="{{ route('autorizaciones.aprobarVenta', $venta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-danger btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Aprobar cancelación? El stock regresará y el dinero se descontará de la caja.">
                                            <i class="bi bi-check-lg me-1"></i> Cancelar Venta
                                        </button>
                                    </form>
                                    <form action="{{ route('autorizaciones.rechazarVenta', $venta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Denegar la solicitud y mantener la venta cobrada?">
                                            <i class="bi bi-x-lg me-1"></i> Denegar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- 4. SOLICITUDES DE CANCELACIÓN DE RENTAS -->
                @if($totalRentasCanc > 0)
                <div id="seccion-rentas-canc" class="mb-5 seccion-ancla">
                    <h6 class="fw-bold text-body mb-3 text-uppercase" style="letter-spacing: 0.5px;">
                        <i class="bi bi-circle-fill text-danger me-2" style="font-size: 8px; vertical-align: middle;"></i>Cancelaciones de Renta
                    </h6>
                    <div class="row g-3">
                        @foreach($rentasCancelacion as $renta)
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="auth-card type-venta">
                                <div class="auth-header">
                                    <span class="badge bg-danger text-white font-monospace px-2 py-1"><i class="bi bi-file-text me-1"></i>{{ $renta->folio }}</span>
                                    <span class="text-secondary" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ $renta->updated_at->diffForHumans() }}</span>
                                </div>
                                <div class="auth-body">
                                    <h6 class="fw-bold text-body mb-2 text-truncate" title="{{ $renta->cliente->nombre_completo ?? 'Cliente General' }}">
                                        <i class="bi bi-person text-danger me-1"></i> {{ $renta->cliente->nombre_completo ?? 'Cliente General' }}
                                    </h6>
                                    <div class="p-2 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 small text-body mb-3">
                                        <strong class="text-danger d-block mb-1"><i class="bi bi-info-circle me-1"></i>Motivo Cajero:</strong>
                                        {{ str_replace('[CANCELACION] ', '', $renta->motivo_autorizacion) }}
                                    </div>
                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <span class="d-block text-secondary" style="font-size: 11px;">Solicitó:</span>
                                            <span class="fw-semibold text-body small">{{ $renta->solicitadoPor->name ?? 'Usuario' }}</span>
                                        </div>
                                        <div class="text-end">
                                            <span class="d-block text-secondary" style="font-size: 11px;">Saldo pendiente:</span>
                                            <strong class="text-danger fs-6">${{ number_format($renta->saldo_pendiente_real ?? $renta->saldo_pendiente, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="auth-footer d-flex gap-2">
                                    <form action="{{ route('autorizaciones.aprobarCancelacionRenta', $renta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-danger btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Aprobar cancelación? Los equipos regresarán al inventario.">
                                            <i class="bi bi-check-lg me-1"></i> Cancelar Renta
                                        </button>
                                    </form>
                                    <form action="{{ route('autorizaciones.rechazarCancelacionRenta', $renta) }}" method="POST" class="flex-fill form-autorizacion">
                                        @csrf
                                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 fw-bold rounded-3 btn-submit-auth" data-confirm="¿Denegar la solicitud y mantener la renta activa?">
                                            <i class="bi bi-x-lg me-1"></i> Denegar
                                        </button>
                                    </form>
                                    <a href="{{ route('rentas.show', $renta) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3" title="Ver Detalles">
                                        <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            @endif

        </div>
    </div>

    <!-- ========================================== -->
    <!-- PESTAÑA: HISTORIAL -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="historial" role="tabpanel" tabindex="0">
        <div class="card border shadow-sm rounded-4 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-4 py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Referencia / Fecha</th>
                            <th class="py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Cliente / Sucursal</th>
                            <th class="py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Solicitó</th>
                            <th class="py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Gerente</th>
                            <th class="py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Resolución</th>
                            <th class="text-center pe-4 py-3 fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $item)
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                @if($item->tipo === 'renta')
                                    <span class="badge bg-warning text-dark mb-1 rounded-pill px-2 py-1 fw-bold font-monospace"><i class="bi bi-file-text me-1"></i>{{ $item->identificador }}</span>
                                @elseif($item->tipo === 'venta')
                                    <span class="badge bg-danger text-white mb-1 rounded-pill px-2 py-1 fw-bold font-monospace"><i class="bi bi-receipt me-1"></i>{{ $item->identificador }}</span>
                                @else
                                    <span class="badge bg-info-subtle text-info-emphasis mb-1 rounded-pill px-2 py-1 fw-bold border border-info-subtle font-monospace"><i class="bi bi-arrow-left-right me-1"></i>{{ $item->identificador }}</span>
                                @endif
                                <br>
                                <small class="text-body-secondary fw-medium">{{ \Carbon\Carbon::parse($item->fecha)->format('d/m/Y h:i A') }}</small>
                            </td>
                            <td class="text-body fw-bold py-3">
                                @if($item->tipo === 'renta')
                                    <i class="bi bi-person me-1 text-warning"></i>
                                @elseif($item->tipo === 'venta')
                                    <i class="bi bi-person me-1 text-danger"></i>
                                @else
                                    <i class="bi bi-buildings me-1 text-info"></i>
                                @endif
                                {{ $item->entidad }}
                            </td>
                            <td class="text-body py-3">
                                <span class="d-flex align-items-center gap-2">
                                    <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;"><i class="bi bi-person text-secondary" style="font-size: 12px;"></i></div>
                                    {{ $item->solicitado_por }}
                                </span>
                            </td>
                            <td class="text-body py-3">
                                <span class="d-flex align-items-center gap-2">
                                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 24px; height: 24px;"><i class="bi bi-shield-check text-primary" style="font-size: 12px;"></i></div>
                                    {{ $item->autorizado_por }}
                                </span>
                            </td>
                            <td class="py-3">
                                @if($item->estado === 'aprobada')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aprobada
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1.5">
                                        <i class="bi bi-x-circle-fill me-1"></i> Rechazada
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4 py-3">
                                @if($item->tipo === 'renta')
                                    <a href="{{ route('rentas.show', $item->id) }}" class="btn btn-sm btn-light border rounded-3 px-3 shadow-sm text-secondary hover-primary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @elseif($item->tipo === 'venta')
                                    <a href="{{ route('puntoventa.historial') }}?fecha={{ \Carbon\Carbon::parse($item->fecha)->format('Y-m-d') }}" class="btn btn-sm btn-light border rounded-3 px-3 shadow-sm text-secondary hover-primary" title="Ver en Historial">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('movimientos.show', $item->id) }}" class="btn btn-sm btn-light border rounded-3 px-3 shadow-sm text-secondary hover-primary" title="Ver Detalle">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-5">
                                <div class="empty-state-icon mx-auto bg-body-tertiary text-secondary mb-3"><i class="bi bi-archive"></i></div>
                                <h6 class="fw-bold text-body">No hay historial registrado.</h6>
                                <p class="small mb-0">Las resoluciones aparecerán aquí una vez procesadas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historial->hasPages())
            <div class="card-footer bg-body border-top p-3 d-flex justify-content-end">
                {{ $historial->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // INTERACCIÓN DE BOTONES: Evitar doble clic y mostrar spinner en los botones de aprobar/rechazar
        document.querySelectorAll('.btn-submit-auth').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const form = this.closest('form');
                const confirmMsg = this.getAttribute('data-confirm');
                
                if (confirm(confirmMsg)) {
                    // Deshabilitar todos los botones de la misma tarjeta para evitar doble acción
                    const card = this.closest('.auth-card');
                    card.querySelectorAll('.btn-submit-auth').forEach(btn => {
                        btn.classList.add('disabled');
                        btn.style.pointerEvents = 'none';
                    });
                    
                    // Mostrar spinner en el botón clickeado
                    const originalContent = this.innerHTML;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
                    
                    form.submit();
                }
            });
        });

        // RECARGA EN TIEMPO REAL AJAX
        let conteoActual = parseInt("{{ $totalPendientes }}") || 0;
        const urlNotificaciones = "{{ route('autorizaciones.notificaciones') }}";

        function actualizarPanel() {
            fetch(urlNotificaciones)
                .then(response => response.json())
                .then(data => {
                    if (data.count !== conteoActual) {
                        fetch(window.location.href)
                            .then(res => res.text())
                            .then(html => {
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                
                                const nuevoContenedor = doc.getElementById('contenedor-autorizaciones');
                                const contenedorActual = document.getElementById('contenedor-autorizaciones');
                                if (nuevoContenedor && contenedorActual) {
                                    contenedorActual.innerHTML = nuevoContenedor.innerHTML;
                                    // Re-asignar eventos a los nuevos botones insertados vía AJAX
                                    asignarEventosBotones();
                                }
                                
                                const oldBadge = document.getElementById('badge-contador-tabs');
                                const newBadge = doc.getElementById('badge-contador-tabs');
                                
                                if (oldBadge && newBadge) {
                                    oldBadge.outerHTML = newBadge.outerHTML;
                                } else if (!oldBadge && newBadge) {
                                    document.getElementById('pendientes-tab').innerHTML += newBadge.outerHTML;
                                } else if (oldBadge && !newBadge) {
                                    oldBadge.remove();
                                }
                                
                                conteoActual = data.count;
                            });
                    }
                });
        }
        
        function asignarEventosBotones() {
            document.querySelectorAll('.btn-submit-auth').forEach(button => {
                const nuevoBoton = button.cloneNode(true);
                button.parentNode.replaceChild(nuevoBoton, button);
                
                nuevoBoton.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('form');
                    const confirmMsg = this.getAttribute('data-confirm');
                    if (confirm(confirmMsg)) {
                        const card = this.closest('.auth-card');
                        card.querySelectorAll('.btn-submit-auth').forEach(btn => {
                            btn.classList.add('disabled');
                            btn.style.pointerEvents = 'none';
                        });
                        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Procesando...';
                        form.submit();
                    }
                });
            });
        }

        setInterval(actualizarPanel, 15000);
    });
</script>
@endsection