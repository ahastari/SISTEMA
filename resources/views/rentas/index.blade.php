@extends('layouts.admin')

@section('content')
<style>
    /* ==========================================================================
       ESTILOS VISUALES ADAPTABLES A MODO OSCURO / CLARO
       ========================================================================== */
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }

    /* Tarjetas de Estadísticas Principales */
    .stats-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 20px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    .metric-icon-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .stats-card p {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        color: var(--bs-secondary-color);
        margin-bottom: 2px;
    }
    .stats-card h3 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
        margin-bottom: 0;
    }

    /* Tarjetas de Lista de Rentas */
    .renta-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 12px;
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }
    .renta-card:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }

    .renta-card:has(.show),
    .renta-card.dropdown-abierto {
        z-index: 1050 !important;
    }

    .folio-badge {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 50rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);
    }

    .cliente-avatar-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background-color: rgba(13, 110, 253, 0.12);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .status-pill {
        padding: 5px 12px;
        border-radius: 50rem;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    .filter-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
    }
    .filtro-input {
        border-radius: 10px !important;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .pagination { gap: 6px; margin-bottom: 0; }
    .pagination .page-item .page-link {
        color: var(--bs-body-color);
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px !important;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
    }
    .pagination .page-item.active .page-link {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #ffffff;
        font-weight: 700;
    }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Historial de Rentas
        </h2>
        <p class="text-body-secondary small mb-0">Gestión de contratos, control de cobros y estados de facturación.</p>
    </div>
    
    <div>
        <a href="{{ route('rentas.create') }}" class="btn btn-primary px-3 py-2 rounded-3 fw-bold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Nueva Renta
        </a>
    </div>
</div>

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

<!-- Tarjetas de Estadísticas Financieras -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #0d6efd;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Total Histórico</p>
                    <h3>${{ number_format($totalFacturado, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-primary-subtle text-primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #198754;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Dinero Recibido</p>
                    <h3 class="text-success">${{ number_format($totalPagado, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-success-subtle text-success">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #dc3545;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Rentas por Cobrar</p>
                    <h3 class="text-danger">${{ number_format($totalPendiente, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #ffc107;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Rentas Registradas</p>
                    <h3>{{ $rentas->total() }}</h3>
                </div>
                <div class="metric-icon-avatar bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-card-checklist"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Panel de Filtros -->
<div class="filter-card p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0 rounded-start-3">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="buscarInput" class="form-control bg-body text-body border-start-0 filtro-input rounded-end-3" 
                       placeholder="Buscar folio o cliente...">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <select id="estadoSelect" class="form-select form-select-sm bg-body text-body filtro-input">
                <option value="">Estado: Todos</option>
                <option value="activa">Activas</option>
                <option value="aprobada_adeudo">Finalizadas c/ Adeudo</option>
                <option value="finalizada">Finalizadas</option>
                <option value="cancelada">Canceladas</option>
                <option value="adeudo">Con Adeudo General</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <select id="facturaSelect" class="form-select form-select-sm bg-body text-body filtro-input">
                <option value="">Factura: Todas</option>
                <option value="si">Requiere Factura</option>
                <option value="no">Sin Factura</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <input type="date" id="fechaFilter" class="form-control form-control-sm bg-body text-body filtro-input">
        </div>

        <div class="col-6 col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100 filtro-input fw-semibold" id="limpiarFiltros">
                <i class="bi bi-x-circle me-1"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Lista de Rentas -->
<div id="rentasLista">
    @forelse($rentas as $renta)
    @php
        $esAprobadaConAdeudo = ($renta->estado == 'activa' && $renta->autorizacion_aprobada);
        $estadoData = $esAprobadaConAdeudo ? 'aprobada_adeudo' : $renta->estado;
        $inicialCliente = strtoupper(substr($renta->cliente->nombre_completo ?? 'C', 0, 1));

        // 🔥 CÁLCULO DE MULTA Y DEUDA REAL
        $multaGenerada = ($renta->estado == 'activa' && isset($renta->total_real)) ? max(0, $renta->total_real - $renta->total) : 0;
        $saldoPendienteReal = $renta->estado == 'cancelada' ? 0 : ($renta->saldo_pendiente + $multaGenerada);
    @endphp
    
    <div class="renta-card" 
         data-estado="{{ $estadoData }}" 
         data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}" 
         data-adeudo="{{ $saldoPendienteReal > 0 ? 'si' : 'no' }}"
         data-factura="{{ $renta->facturar ? 'si' : 'no' }}"
         data-url="{{ route('rentas.show', $renta) }}">
        
        <div class="row align-items-center g-3">
            
            <!-- Folio, Factura y Cliente -->
            <div class="col-12 col-md-4 col-lg-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="folio-badge">
                        <i class="bi bi-file-text me-1"></i>{{ $renta->folio }}
                    </span>
                    @if($renta->facturar)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small px-2">
                            <i class="bi bi-receipt me-1"></i> Factura
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="cliente-avatar-icon">{{ $inicialCliente }}</div>
                    <div class="text-truncate">
                        <h6 class="mb-0 fw-bold text-body text-truncate" style="max-width: 180px;">
                            {{ $renta->cliente->nombre_completo ?? 'Cliente general' }}
                        </h6>
                        <small class="text-body-secondary d-block" style="font-size: 11px;">
                            <i class="bi bi-telephone me-1"></i>{{ $renta->cliente->telefono ?? 'S/N' }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Período de Renta -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <span class="text-body-secondary d-block fw-semibold" style="font-size: 11px;">Período</span>
                <strong class="text-body small d-block">
                    {{ $renta->fecha_inicio->format('d/m') }} - {{ $renta->fecha_fin->format('d/m/Y') }}
                </strong>
                <small class="text-body-secondary d-block" style="font-size: 11px;">{{ $renta->dias_totales }} días</small>
            </div>

            <!-- Días Restantes / Retraso -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <span class="text-body-secondary d-block fw-semibold" style="font-size: 11px;">Restante</span>
                @if($renta->estado == 'activa' && !$renta->autorizacion_aprobada)
                    @if($renta->dias_restantes > 0)
                        <span class="fw-bold small 
                            @if($renta->dias_restantes <= 3) text-danger 
                            @elseif($renta->dias_restantes <= 7) text-warning 
                            @else text-success 
                            @endif">
                            <i class="bi bi-hourglass-split me-1"></i>{{ $renta->dias_restantes }} días
                        </span>
                    @else
                        <span class="text-danger fw-bold small"><i class="bi bi-exclamation-triangle-fill me-1"></i>¡Vencida!</span>
                    @endif
                @elseif($esAprobadaConAdeudo)
                    <span class="text-warning-emphasis fw-bold small"><i class="bi bi-clock-history me-1"></i>Por Liquidar</span>
                @else
                    <span class="text-body-secondary small">—</span>
                @endif
            </div>

            <!-- DESGLOSE DE MONTOS Y SALDO (CORREGIDO) -->
            <div class="col-6 col-md-2 col-lg-3 text-end text-md-center">
                @if($renta->estado == 'cancelada')
                    <small class="text-body-secondary d-block text-decoration-line-through" style="font-size: 11px;">Original: ${{ number_format($renta->total, 2) }}</small>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;">
                        <i class="bi bi-slash-circle me-1"></i> Deuda Anulada
                    </span>
                @else
                    {{-- Total Base del Contrato --}}
                    <small class="text-body-secondary d-block" style="font-size: 11px;">
                        Contrato: <strong class="text-body">${{ number_format($renta->total, 2) }}</strong>
                    </small>
                    
                    {{-- Multa desglosada si el contrato está vencido --}}
                    @if($multaGenerada > 0)
                        <small class="text-danger d-block fw-bold mt-1" style="font-size: 10.5px;">
                            + Retraso: ${{ number_format($multaGenerada, 2) }}
                        </small>
                    @endif
                    
                    {{-- Gran Total a Deber contemplando la multa --}}
                    @if($saldoPendienteReal > 0)
                        <div class="mt-1">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 12px; font-weight: 800;">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Debe Total: ${{ number_format($saldoPendienteReal, 2) }}
                            </span>
                        </div>
                    @else
                        <div class="mt-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 11px;">
                                <i class="bi bi-check-circle-fill me-1"></i> Liquidada
                            </span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Estado de Renta y Menú de Acciones -->
            <div class="col-12 col-md-2 col-lg-2">
                <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    
                    @if($esAprobadaConAdeudo)
                        <span class="status-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            <span class="status-dot bg-warning"></span> FINALIZADA
                        </span>
                    @elseif($renta->estado == 'activa')
                        <span class="status-pill bg-success-subtle text-success border border-success-subtle">
                            <span class="status-dot bg-success"></span> ACTIVA
                        </span>
                    @elseif($renta->estado == 'finalizada')
                        <span class="status-pill bg-primary-subtle text-primary border border-primary-subtle">
                            <span class="status-dot bg-primary"></span> FINALIZADA
                        </span>
                    @else
                        <span class="status-pill bg-secondary-subtle text-secondary border border-secondary-subtle">
                            <span class="status-dot bg-secondary"></span> CANCELADA
                        </span>
                    @endif
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical fs-6"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                            <li>
                                <a class="dropdown-item py-2 text-body" href="{{ route('rentas.show', $renta) }}">
                                    <i class="bi bi-eye me-2 text-primary"></i> 
                                    Ver detalle y cobrar
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-body" onclick="verDocumento('{{ route('rentas.contrato', $renta) }}', 'Contrato de Renta - Folio {{ $renta->folio }}')">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Ver PDF
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
    @empty
    <div class="text-center py-5 rounded-4 bg-body border border-dashed">
        <i class="bi bi-inbox text-body-secondary" style="font-size: 48px;"></i>
        <h5 class="mt-3 text-body fw-bold">No hay rentas registradas</h5>
        <p class="text-body-secondary small mb-0">Comienza creando tu primera renta en el sistema.</p>
    </div>
    @endforelse
</div>

<!-- Paginación con Estilos Bootstrap 5 -->
<div class="mt-4 d-flex justify-content-center">
    {{ $rentas->links('pagination::bootstrap-5') }}
</div>

<!-- MODAL: VISUALIZADOR DINÁMICO DE DOCUMENTOS -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border shadow-lg rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="modalVerDocumentoTitulo">Visualizador de Documento</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10 position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 78vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>
            <div class="modal-footer bg-body-tertiary py-2 px-3 border-top">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarRentas() {
    const busqueda = document.getElementById('buscarInput').value.toLowerCase();
    const estado = document.getElementById('estadoSelect').value;
    const factura = document.getElementById('facturaSelect').value;
    const fecha = document.getElementById('fechaFilter').value;
    
    document.querySelectorAll('.renta-card').forEach(renta => {
        let mostrar = true;
        
        if (busqueda && !renta.innerText.toLowerCase().includes(busqueda)) mostrar = false;
        
        if (mostrar && estado) {
            if (estado === 'adeudo' && renta.dataset.adeudo !== 'si') mostrar = false;
            else if (estado !== 'adeudo' && renta.dataset.estado !== estado) mostrar = false;
        }

        if (mostrar && factura && renta.dataset.factura !== factura) mostrar = false;
        if (mostrar && fecha && renta.dataset.fecha !== fecha) mostrar = false;
        
        renta.style.display = mostrar ? '' : 'none';
    });
}

document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('buscarInput').value = '';
    document.getElementById('estadoSelect').value = '';
    document.getElementById('facturaSelect').value = '';
    document.getElementById('fechaFilter').value = '';
    filtrarRentas();
});

document.getElementById('buscarInput').addEventListener('keyup', filtrarRentas);
document.getElementById('estadoSelect').addEventListener('change', filtrarRentas);
document.getElementById('facturaSelect').addEventListener('change', filtrarRentas);
document.getElementById('fechaFilter').addEventListener('change', filtrarRentas);

function verDocumento(url, titulo) {
    document.getElementById('modalVerDocumentoTitulo').textContent = titulo;
    document.getElementById('btnDescargarDocumento').href = url;
    document.getElementById('loaderDocumento').classList.remove('d-none');
    document.getElementById('iframeDocumento').src = url;
    new bootstrap.Modal(document.getElementById('modalVerDocumento')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.renta-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown') && !e.target.closest('a') && !e.target.closest('button')) {
                window.location.href = this.dataset.url;
            }
        });
        
        const dropdown = card.querySelector('.dropdown');
        if (dropdown) {
            dropdown.addEventListener('show.bs.dropdown', () => card.classList.add('dropdown-abierto'));
            dropdown.addEventListener('hide.bs.dropdown', () => card.classList.remove('dropdown-abierto'));
        }
    });

    document.getElementById('modalVerDocumento').addEventListener('hidden.bs.modal', function () {
        document.getElementById('iframeDocumento').src = '';
    });
});
</script>
@endsection