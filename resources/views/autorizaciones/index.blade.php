@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    .custom-nav-tabs {
        border-bottom: 1px solid var(--bs-border-color);
        gap: 4px;
    }
    .custom-nav-tabs .nav-link {
        color: var(--bs-secondary-color);
        background-color: transparent;
        border: 1px solid transparent;
        border-bottom: none;
        border-radius: 12px 12px 0 0;
        padding: 10px 18px;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .custom-nav-tabs .nav-link.active {
        color: var(--bs-primary);
        background-color: var(--bs-body-bg);
        border-color: var(--bs-border-color) var(--bs-border-color) var(--bs-body-bg);
    }
    .card-autorizacion {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        overflow: hidden;
    }
    .card-autorizacion:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0, 0, 0, 0.06) !important;
    }
    .folio-badge {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 50rem;
        font-size: 11px;
        font-weight: 700;
    }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-shield-lock-fill text-warning me-2"></i>Panel de Autorizaciones
        </h2>
        <p class="text-body-secondary small mb-0">Gestión y revisión de solicitudes de transferencias entre sucursales y cierres de rentas.</p>
    </div>
</div>

<!-- Alertas Informativas -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    $totalPendientes = $autorizacionesRentas->count() + $movimientosPendientes->count();
@endphp

<!-- Navegación de Pestañas -->
<ul class="nav custom-nav-tabs mb-4" id="autorizacionesTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="pendientes-tab" data-bs-toggle="tab" data-bs-target="#pendientes" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Pendientes 
            @if($totalPendientes > 0)
                <span class="badge bg-danger ms-1 rounded-pill" id="badge-contador-tabs">{{ $totalPendientes }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
            <i class="bi bi-archive me-1"></i> Historial de Decisiones
        </button>
    </li>
</ul>

<div class="tab-content" id="autorizacionesTabsContent">
    
    <!-- PESTAÑA PENDIENTES -->
    <div class="tab-pane fade show active" id="pendientes" role="tabpanel" tabindex="0">
        <div id="contenedor-autorizaciones">

            <!-- 🔄 SECCIÓN 1: SOLICITUDES DE TRANSFERENCIA ENTRE SUCURSALES -->
            <div class="mb-5">
                <h5 class="fw-bold mb-3 text-body d-flex align-items-center">
                    <i class="bi bi-arrow-left-right text-info me-2"></i> Transferencias de Productos entre Sucursales
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill ms-2 font-monospace" style="font-size: 11px;">
                        {{ $movimientosPendientes->count() }}
                    </span>
                </h5>

                <div class="row g-3">
                    @forelse($movimientosPendientes as $movimiento)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card card-autorizacion border-info-subtle shadow-sm h-100">
                            
                            <div class="card-header bg-info-subtle text-info-emphasis py-2 px-3 d-flex justify-content-between align-items-center border-bottom border-info-subtle">
                                <span class="badge bg-info text-dark fw-bold">
                                    <i class="bi bi-arrow-up-circle me-1"></i> Solicitud de Envío
                                </span>
                                <span class="badge bg-warning text-dark font-monospace" style="font-size: 10px;">
                                    <i class="bi bi-clock-fill me-1"></i> Requiere Aprobación
                                </span>
                            </div>

                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-body mb-1 text-truncate" title="{{ $movimiento->equipo->nombre }}">
                                        <i class="bi bi-box-seam me-1 text-primary"></i> {{ $movimiento->equipo->nombre }}
                                    </h6>
                                    <span class="badge bg-secondary font-monospace mb-2">{{ $movimiento->equipo->codigo }}</span>

                                    <div class="bg-body-tertiary border rounded-3 p-2 mb-2 small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-body-secondary">Origen:</span>
                                            <strong class="text-danger"><i class="bi bi-building me-1"></i>{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-body-secondary">Destino:</span>
                                            <strong class="text-success"><i class="bi bi-building me-1"></i>{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                                            <span class="text-body-secondary">Cantidad:</span>
                                            <strong class="text-primary fs-6">{{ $movimiento->cantidad }} unidades</strong>
                                        </div>
                                    </div>

                                    <div class="alert alert-secondary bg-body-tertiary border p-2 mb-2 rounded-3 small">
                                        <strong>Motivo:</strong> {{ $movimiento->motivo }}
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                    <form action="{{ route('movimientos.aprobar', $movimiento) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold rounded-3" onclick="return confirm('¿Aprobar esta solicitud de transferencia?')">
                                            <i class="bi bi-check-lg me-1"></i> Aprobar
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('movimientos.rechazar', $movimiento) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold rounded-3" onclick="return confirm('¿Rechazar esta solicitud y devolver el stock a la sucursal de origen?')">
                                            <i class="bi bi-x-lg me-1"></i> Rechazar
                                        </button>
                                    </form>

                                    <a href="{{ route('movimientos.show', $movimiento) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-2.5" title="Ver Detalle Movimiento">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer bg-body-tertiary text-body-secondary small py-2 px-3 border-top">
                                <i class="bi bi-person me-1"></i> Solicitó: {{ $movimiento->usuario->name ?? 'Usuario' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 rounded-4 bg-body border border-dashed text-secondary">
                        <i class="bi bi-check2-all text-success fs-3 d-block mb-1"></i>
                        No hay solicitudes de transferencias pendientes de autorización.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- 📋 SECCIÓN 2: SOLICITUDES DE FINALIZACIÓN DE RENTAS CON ADEUDO -->
            <div>
                <h5 class="fw-bold mb-3 text-body d-flex align-items-center">
                    <i class="bi bi-file-earmark-text text-warning me-2"></i> Cierres de Rentas con Adeudo Pendiente
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill ms-2 font-monospace" style="font-size: 11px;">
                        {{ $autorizacionesRentas->count() }}
                    </span>
                </h5>

                <div class="row g-3">
                    @forelse($autorizacionesRentas as $renta)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card card-autorizacion border-warning-subtle shadow-sm h-100">
                            
                            <div class="card-header bg-warning-subtle text-warning-emphasis py-2 px-3 d-flex justify-content-between align-items-center border-bottom border-warning-subtle">
                                <span class="folio-badge">
                                    <i class="bi bi-file-text me-1"></i>{{ $renta->folio }}
                                </span>
                                <span class="badge bg-warning text-dark font-monospace" style="font-size: 10px;">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i> Requiere Revisión
                                </span>
                            </div>

                            <div class="card-body p-3 d-flex flex-column justify-content-between">
                                <div>
                                    <h6 class="card-title fw-bold text-body mb-2 text-truncate" title="{{ $renta->cliente->nombre_completo ?? 'Cliente General' }}">
                                        <i class="bi bi-person me-1 text-primary"></i> {{ $renta->cliente->nombre_completo ?? 'Cliente General' }}
                                    </h6>

                                    <div class="alert alert-danger bg-danger-subtle text-danger border-0 p-2 mb-3 rounded-3 small">
                                        <div class="fw-bold mb-1"><i class="bi bi-chat-left-dots me-1"></i> Motivo:</div>
                                        {{ $renta->motivo_autorizacion }}
                                    </div>

                                    <div class="bg-body-tertiary border rounded-3 p-2.5 mb-3 small">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <span class="text-body-secondary">Solicitado por:</span>
                                            <strong class="text-primary">
                                                <i class="bi bi-person-badge me-1"></i>{{ $renta->solicitadoPor->name ?? 'Usuario' }}
                                            </strong>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-body-secondary">Saldo Pendiente:</span>
                                            <strong class="text-danger fs-6">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2 pt-2 border-top">
                                    <form action="{{ route('autorizaciones.aprobar', $renta) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold rounded-3" onclick="return confirm('¿Confirmas que deseas APROBAR esta renta con adeudo?');">
                                            <i class="bi bi-check-lg me-1"></i> Aprobar
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('autorizaciones.rechazar', $renta) }}" method="POST" class="flex-fill">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold rounded-3" onclick="return confirm('¿Confirmas que deseas RECHAZAR la petición?');">
                                            <i class="bi bi-x-lg me-1"></i> Rechazar
                                        </button>
                                    </form>

                                    <a href="{{ route('rentas.show', $renta) }}" class="btn btn-outline-secondary btn-sm rounded-3 px-2.5" title="Ver Detalles de Renta">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="card-footer bg-body-tertiary text-body-secondary small py-2 px-3 border-top">
                                <i class="bi bi-clock me-1"></i> Solicitado el: {{ $renta->updated_at->format('d/m/Y h:i A') }}
                            </div>

                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-center py-4 rounded-4 bg-body border border-dashed text-secondary">
                        <i class="bi bi-shield-check text-success fs-3 d-block mb-1"></i>
                        No hay solicitudes de cierre de rentas pendientes.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    <!-- PESTAÑA HISTORIAL -->
    <div class="tab-pane fade" id="historial" role="tabpanel" tabindex="0">
        <div class="card border shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-3 py-2.5">Folio / Fecha</th>
                            <th class="py-2.5">Cliente</th>
                            <th class="py-2.5">Solicitó</th>
                            <th class="py-2.5">Decisión (Gerente)</th>
                            <th class="py-2.5">Resultado</th>
                            <th class="text-center pe-3 py-2.5">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($historial as $item)
                        <tr class="border-bottom">
                            <td class="ps-3 py-2.5">
                                <span class="folio-badge mb-1 d-inline-block">{{ $item->folio }}</span><br>
                                <small class="text-body-secondary">{{ $item->updated_at->format('d/m/Y H:i') }}</small>
                            </td>
                            <td class="text-body fw-semibold py-2.5">{{ $item->cliente->nombre_completo ?? 'N/A' }}</td>
                            <td class="text-body py-2.5">
                                <i class="bi bi-person me-1 text-secondary"></i>{{ $item->solicitadoPor->name ?? 'Desconocido' }}
                            </td>
                            <td class="text-body py-2.5">
                                <i class="bi bi-person-check me-1 text-primary"></i>{{ $item->autorizadoPor->name ?? 'Desconocido' }}
                            </td>
                            <td class="py-2.5">
                                @if($item->estado === 'finalizada')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1.5">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aprobada
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1.5">
                                        <i class="bi bi-x-circle-fill me-1"></i> Rechazada
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-3 py-2.5">
                                <a href="{{ route('rentas.show', $item) }}" class="btn btn-sm btn-outline-secondary rounded-3 px-2.5" title="Ver Detalle de Renta">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-body-secondary py-5">
                                <i class="bi bi-inbox fs-2 d-block mb-2 text-body-tertiary"></i>
                                No hay historial registrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($historial->hasPages())
            <div class="card-footer bg-body-tertiary border-top p-3 d-flex justify-content-end">
                {{ $historial->links('pagination::bootstrap-5') }}
            </div>
            @endif
        </div>
    </div>
</div>

<!-- JavaScript para actualización en tiempo real (AJAX) -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
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
        setInterval(actualizarPanel, 10000);
    });
</script>
@endsection