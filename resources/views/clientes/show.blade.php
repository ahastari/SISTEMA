@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    .stats-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 16px 20px;
        transition: transform 0.2s ease;
    }
    .stats-card:hover {
        transform: translateY(-2px);
    }
    .stats-card h3 {
        font-size: 24px;
        font-weight: 800;
        margin-bottom: 0;
    }
    .stats-card p {
        color: var(--bs-secondary-color);
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        margin-bottom: 2px;
    }
    .info-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        border-radius: 16px;
    }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-person-circle text-primary me-2"></i>Detalle del Cliente
        </h2>
        <p class="text-body-secondary small mb-0">Información de contacto, expediente digital y resumen de obras y rentas.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Regresar
        </a>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning btn-sm rounded-3 px-3 text-dark fw-semibold">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
    </div>
</div>

<!-- Tarjetas de Estadísticas Rápidas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #0d6efd;">
            <p>Total de Rentas</p>
            <h3 class="text-primary">{{ $cliente->rentas->count() }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #198754;">
            <p>Rentas Activas</p>
            <h3 class="text-success">{{ $rentasActivas->count() }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #0dcaf0;">
            <p>Rentas Finalizadas</p>
            <h3 class="text-info">{{ $rentasFinalizadas->count() }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #ffc107;">
            <p>Obras / Proyectos</p>
            <h3 class="text-warning-emphasis">{{ $cliente->obras->count() }}</h3>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- DATOS DEL CLIENTE -->
    <div class="col-12 col-lg-6">
        <div class="card info-card shadow-sm h-100">
            <div class="card-header bg-body-tertiary border-bottom py-2 px-3 fw-bold text-body">
                <i class="bi bi-person me-2 text-primary"></i>Datos Personales e Identificación
            </div>
            <div class="card-body p-3">
                <table class="table table-borderless table-sm mb-0 text-body" style="font-size: 13px;">
                    <tbody>
                        <tr>
                            <th class="text-body-secondary py-1" style="width: 120px;">Nombre:</th>
                            <td class="fw-bold py-1">{{ $cliente->nombre_completo }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Teléfono:</th>
                            <td class="py-1"><i class="bi bi-telephone text-primary me-1"></i>{{ $cliente->telefono }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Teléfono Alt:</th>
                            <td class="py-1">{{ $cliente->telefono_alternativo ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Correo Email:</th>
                            <td class="py-1">{{ $cliente->email ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Empresa:</th>
                            <td class="py-1">{{ $cliente->empresa ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">RFC:</th>
                            <td class="py-1"><code class="text-body fw-bold">{{ $cliente->rfc ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">CURP:</th>
                            <td class="py-1"><code class="text-body">{{ $cliente->curp ?? 'N/A' }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Documento INE:</th>
                            <td class="py-1">
                                @if($cliente->ine_documento)
                                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-3" onclick="verDocumento('{{ Storage::url($cliente->ine_documento) }}', 'INE - {{ $cliente->nombre_completo }}')" style="font-size: 11px;">
                                        <i class="bi bi-file-earmark-pdf me-1"></i> Ver Documento
                                    </button>
                                @else
                                    <span class="text-body-secondary">No adjuntado</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DIRECCIÓN Y OBSERVACIONES -->
    <div class="col-12 col-lg-6">
        <div class="card info-card shadow-sm h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="card-header bg-body-tertiary border-bottom py-2 px-3 fw-bold text-body">
                    <i class="bi bi-geo-alt me-2 text-info"></i>Dirección
                </div>
                <div class="card-body p-3">
                    <table class="table table-borderless table-sm mb-0 text-body" style="font-size: 13px;">
                        <tbody>
                            <tr>
                                <th class="text-body-secondary py-1" style="width: 120px;">Dirección:</th>
                                <td class="py-1">{{ $cliente->direccion ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Ciudad:</th>
                                <td class="py-1">{{ $cliente->ciudad ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Estado:</th>
                                <td class="py-1">{{ $cliente->estado ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Código Postal:</th>
                                <td class="py-1">{{ $cliente->codigo_postal ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($cliente->observaciones)
            <div class="p-3 border-top">
                <div class="fw-bold small text-body mb-1"><i class="bi bi-chat-left-text me-1 text-secondary"></i> Observaciones:</div>
                <p class="small text-body-secondary mb-0">{{ $cliente->observaciones }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- OBRAS / PROYECTOS -->
<div class="card info-card shadow-sm mb-4">
    <div class="card-header bg-body-tertiary border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
        <span class="fw-bold text-body"><i class="bi bi-building me-2 text-warning-emphasis"></i>Obras y Proyectos del Cliente</span>
    </div>
    <div class="card-body p-0">
        @if($cliente->obras->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-3 py-2">Nombre de la obra</th>
                            <th class="py-2">Dirección</th>
                            <th class="py-2">Ciudad</th>
                            <th class="py-2">Contacto</th>
                            <th class="py-2">Estado</th>
                            <th class="py-2">Rentas</th>
                            <th class="text-center pe-3 py-2">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->obras as $obra)
                        <tr class="border-bottom">
                            <td class="ps-3 fw-bold py-2">{{ $obra->nombre }}</td>
                            <td class="py-2">{{ $obra->direccion }}</td>
                            <td class="py-2">{{ $obra->ciudad ?? 'N/A' }}</td>
                            <td class="py-2">{{ $obra->contacto_obra ?? 'N/A' }}</td>
                            <td class="py-2">
                                @if($obra->activa)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activa</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactiva</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">{{ $obra->rentas->count() }}</span>
                            </td>
                            <td class="text-center pe-3 py-2">
                                <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2">
                                    <i class="bi bi-eye me-1"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-body-secondary">
                <i class="bi bi-building fs-2 d-block mb-1 text-body-tertiary"></i>
                <small>Este cliente no tiene obras registradas.</small>
            </div>
        @endif
    </div>
</div>

<!-- RENTAS ACTIVAS -->
<div class="card info-card shadow-sm mb-4">
    <div class="card-header bg-body-tertiary border-bottom py-2 px-3">
        <span class="fw-bold text-body"><i class="bi bi-check-circle me-2 text-success"></i>Rentas Activas</span>
    </div>
    <div class="card-body p-0">
        @if($rentasActivas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-3 py-2">Folio</th>
                            <th class="py-2">Equipos</th>
                            <th class="py-2">Período</th>
                            <th class="py-2">Días</th>
                            <th class="py-2">Total</th>
                            <th class="text-center pe-3 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentasActivas as $renta)
                        <tr class="border-bottom">
                            <td class="ps-3 fw-bold text-primary py-2">{{ $renta->folio }}</td>
                            <td class="py-2">
                                @foreach($renta->detalles as $detalle)
                                    <span class="badge bg-body-tertiary text-body border border-secondary-subtle me-1">
                                        {{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-2">{{ $renta->fecha_inicio->format('d/m/Y') }} - {{ $renta->fecha_fin->format('d/m/Y') }}</td>
                            <td class="py-2">{{ $renta->dias_totales }} días</td>
                            <td class="py-2 fw-bold text-success">${{ number_format($renta->total, 2) }}</td>
                            <td class="text-center pe-3 py-2">
                                <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2 me-1">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <a href="{{ route('rentas.finalizar', $renta) }}" class="btn btn-sm btn-success rounded-3 px-2" onclick="return confirm('¿Finalizar esta renta?')">
                                    <i class="bi bi-check-lg"></i> Finalizar
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-body-secondary">
                <i class="bi bi-check-circle fs-2 d-block mb-1 text-body-tertiary"></i>
                <small>No hay rentas activas para este cliente.</small>
            </div>
        @endif
    </div>
</div>

<!-- HISTORIAL DE RENTAS -->
<div class="card info-card shadow-sm mb-4">
    <div class="card-header bg-body-tertiary border-bottom py-2 px-3">
        <span class="fw-bold text-body"><i class="bi bi-clock-history me-2 text-info"></i>Historial de Rentas Finalizadas</span>
    </div>
    <div class="card-body p-0">
        @if($rentasFinalizadas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-3 py-2">Folio</th>
                            <th class="py-2">Equipos</th>
                            <th class="py-2">Período</th>
                            <th class="py-2">Total</th>
                            <th class="py-2">Devolución</th>
                            <th class="text-center pe-3 py-2">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentasFinalizadas as $renta)
                        <tr class="border-bottom">
                            <td class="ps-3 fw-bold text-primary py-2">{{ $renta->folio }}</td>
                            <td class="py-2">
                                @foreach($renta->detalles as $detalle)
                                    <span class="badge bg-body-tertiary text-body border border-secondary-subtle me-1">
                                        {{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="py-2">{{ $renta->fecha_inicio->format('d/m/Y') }} - {{ $renta->fecha_fin->format('d/m/Y') }}</td>
                            <td class="py-2 fw-bold text-body">${{ number_format($renta->total, 2) }}</td>
                            <td class="py-2 text-body-secondary">{{ $renta->fecha_devolucion ? \Carbon\Carbon::parse($renta->fecha_devolucion)->format('d/m/Y') : 'N/A' }}</td>
                            <td class="text-center pe-3 py-2">
                                <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2">
                                    <i class="bi bi-eye me-1"></i> Ver
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-body-secondary">
                <i class="bi bi-clock-history fs-2 d-block mb-1 text-body-tertiary"></i>
                <small>No hay rentas finalizadas en el historial.</small>
            </div>
        @endif
    </div>
</div>

<!-- MODAL: VISUALIZADOR DINÁMICO DE DOCUMENTOS -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border shadow-lg rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-6 text-white"></i>
                    <h6 class="modal-title fw-bold mb-0 text-white" id="modalVerDocumentoTitulo">Documento</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-body-tertiary position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-body-secondary mt-2">Cargando archivo...</div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 75vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>
            <div class="modal-footer bg-body-tertiary py-2 px-3 border-top d-flex justify-content-between" style="border-color: var(--bs-border-color) !important;">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>
@endsection