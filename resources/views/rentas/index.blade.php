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

    /* Eleva la tarjeta sobre las demás cuando el menú de 3 puntos está abierto */
    .renta-card:has(.show),
    .renta-card.dropdown-abierto {
        z-index: 1050 !important;
    }

    /* Badges y Elementos de Estado */
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

    /* Indicadores de Estado (Pill con Dot) */
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

    /* Barra de Filtros */
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

    /* Paginación */
    .pagination {
        gap: 6px;
        margin-bottom: 0;
    }
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

<!-- Alertas Informativas -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill fs-5 me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
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

<!-- Panel de Filtros Adaptativos -->
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
                <option value="aprobada_adeudo">Finalizadas c/ Adeudo (Aprobadas)</option>
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
    @endphp
    
    <div class="renta-card" 
         data-estado="{{ $estadoData }}" 
         data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}" 
         data-adeudo="{{ $renta->saldo_pendiente > 0 ? 'si' : 'no' }}"
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
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small px-2" title="Esta renta requiere emisión de factura (+16% IVA)">
                            <i class="bi bi-receipt me-1"></i> Requiere Factura
                        </span>
                    @else
                        <span class="badge bg-body-tertiary text-body-secondary border border-secondary-subtle rounded-pill small px-2" title="Esta renta no requiere factura">
                            <i class="bi bi-file-earmark me-1"></i> Sin Factura
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="cliente-avatar-icon">
                        {{ $inicialCliente }}
                    </div>
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

            <!-- Montos y Saldo Pendiente -->
            <div class="col-6 col-md-2 col-lg-3 text-end text-md-center">
                @if($renta->estado == 'cancelada')
                    <small class="text-body-secondary d-block text-decoration-line-through" style="font-size: 11px;">Original: ${{ number_format($renta->total_real, 2) }}</small>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;">
                        <i class="bi bi-slash-circle me-1"></i> Deuda Anulada
                    </span>
                @else
                    <small class="text-body-secondary d-block" style="font-size: 11px;">
                        Total: <strong class="text-body">${{ number_format($renta->total_real, 2) }}</strong>
                        @if($renta->facturar) <span class="text-primary fw-bold" style="font-size: 10px;">(Inc. IVA)</span> @endif
                    </small>
                    
                    @if($renta->saldo_pendiente > 0)
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 mt-1" style="font-size: 11px;">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> Debe: ${{ number_format($renta->saldo_pendiente, 2) }}
                        </span>
                    @else
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 mt-1" style="font-size: 11px;">
                            <i class="bi bi-check-circle-fill me-1"></i> Pagado
                        </span>
                    @endif
                @endif
            </div>

            <!-- Estado de Renta y Menú de Acciones -->
            <div class="col-12 col-md-2 col-lg-2">
                <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    
                    @if($esAprobadaConAdeudo)
                        <span class="status-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Autorizada por Gerente para finalizar con adeudo">
                            <span class="status-dot bg-warning"></span> FINALIZADA C/ ADEUDO
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
                                    {{ $esAprobadaConAdeudo ? 'Liquidar / Registrar Devolución' : 'Ver detalle' }}
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-body" onclick="verDocumento('{{ route('rentas.contrato', $renta) }}', 'Contrato de Renta - Folio {{ $renta->folio }}')">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Ver PDF
                                </button>
                            </li>
                            @if($renta->estado == 'activa' && !$renta->autorizacion_aprobada)
                            <li>
                                <a class="dropdown-item py-2 text-body" href="{{ route('rentas.finalizar', $renta) }}" onclick="return confirm('¿Finalizar esta renta?')">
                                    <i class="bi bi-check-lg me-2 text-success"></i> Finalizar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary-subtle"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('rentas.cancelar', $renta) }}" onclick="return confirm('¿Seguro que deseas cancelar esta renta? El stock se devolverá automáticamente.')">
                                    <i class="bi bi-x-octagon me-2"></i> Cancelar Renta
                                </a>
                            </li>
                            @endif
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
            
            <!-- Encabezado del Modal -->
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-file-earmark-pdf fs-6 text-white"></i>
                    </div>
                    <h6 class="modal-title fw-bold mb-0 text-white" id="modalVerDocumentoTitulo">Visualizador de Documento</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Modal (Iframe Visor) -->
            <div class="modal-body p-0 bg-body-tertiary position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-body-secondary mt-2">Cargando documento...</div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 78vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>

            <!-- Pie del Modal -->
            <div class="modal-footer bg-body-tertiary py-2 px-3 border-top d-flex justify-content-between" style="border-color: var(--bs-border-color) !important;">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>

<!-- JavaScript de Filtros, Elevación de Dropdown, Clic en la Tarjeta y Visor PDF -->
<script>
function filtrarRentas() {
    const busqueda = document.getElementById('buscarInput').value.toLowerCase();
    const estado = document.getElementById('estadoSelect').value;
    const factura = document.getElementById('facturaSelect').value;
    const fecha = document.getElementById('fechaFilter').value;
    
    const rentas = document.querySelectorAll('.renta-card');
    
    rentas.forEach(renta => {
        let mostrar = true;
        
        // 1. Filtro por texto
        if (busqueda) {
            const texto = renta.innerText.toLowerCase();
            if (!texto.includes(busqueda)) {
                mostrar = false;
            }
        }
        
        // 2. Filtro por estado
        if (mostrar && estado) {
            if (estado === 'adeudo') {
                if (renta.dataset.adeudo !== 'si') {
                    mostrar = false;
                }
            } else if (renta.dataset.estado !== estado) {
                mostrar = false;
            }
        }

        // 3. Filtro por factura
        if (mostrar && factura) {
            if (renta.dataset.factura !== factura) {
                mostrar = false;
            }
        }
        
        // 4. Filtro por fecha
        if (mostrar && fecha) {
            if (renta.dataset.fecha !== fecha) {
                mostrar = false;
            }
        }
        
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

// ==========================================
// FUNCIÓN PARA VISUALIZAR PDF EN MODAL
// ==========================================
function verDocumento(url, titulo) {
    const tituloEl = document.getElementById('modalVerDocumentoTitulo');
    const iframeEl = document.getElementById('iframeDocumento');
    const loaderEl = document.getElementById('loaderDocumento');
    const btnDescargar = document.getElementById('btnDescargarDocumento');

    if (tituloEl) tituloEl.textContent = titulo;
    if (btnDescargar) btnDescargar.href = url;
    
    if (loaderEl) loaderEl.classList.remove('d-none');
    if (iframeEl) iframeEl.src = url;

    const modalEl = document.getElementById('modalVerDocumento');
    if (modalEl) {
        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    }
}

// ==========================================
// CONTROL DE CLIC EN LA TARJETA Y DROPDOWN
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // 1. Redireccionar al contrato al hacer clic en la tarjeta
    document.querySelectorAll('.renta-card').forEach(function(card) {
        card.addEventListener('click', function(e) {
            // Ignorar el clic si se hace sobre el menú de tres puntos, botones o enlaces
            if (e.target.closest('.dropdown') || e.target.closest('a') || e.target.closest('button')) {
                return;
            }
            const url = this.dataset.url;
            if (url) {
                window.location.href = url;
            }
        });

        // 2. Control de z-index al desplegar el menú de opciones
        const dropdown = card.querySelector('.dropdown');
        if (dropdown) {
            dropdown.addEventListener('show.bs.dropdown', function () {
                card.classList.add('dropdown-abierto');
            });
            dropdown.addEventListener('hide.bs.dropdown', function () {
                card.classList.remove('dropdown-abierto');
            });
        }
    });

    // 3. Limpiar iframe del modal al cerrarlo para liberar recursos
    const modalVerDoc = document.getElementById('modalVerDocumento');
    if (modalVerDoc) {
        modalVerDoc.addEventListener('hidden.bs.modal', function () {
            const iframeEl = document.getElementById('iframeDocumento');
            if (iframeEl) iframeEl.src = '';
        });
    }

});
</script>
@endsection