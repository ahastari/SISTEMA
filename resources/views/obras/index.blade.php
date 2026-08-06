@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    .filter-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
    }
    .obra-avatar-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background-color: rgba(255, 193, 7, 0.15);
        color: #d69e2e;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        flex-shrink: 0;
    }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-building text-primary me-2"></i>Obras y Proyectos
        </h2>
        <p class="text-body-secondary small mb-0">Directorio de ubicaciones de entrega y proyectos asignados a clientes.</p>
    </div>
    <div>
        <a href="{{ route('obras.create') }}" class="btn btn-primary px-3 py-2 rounded-3 fw-bold shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> Nueva Obra
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

<!-- Tabla de Obras -->
<div class="card border shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
            <thead class="bg-body-tertiary text-body-secondary border-bottom">
                <tr>
                    <th class="ps-3 py-2.5">Obra / Proyecto</th>
                    <th class="py-2.5">Cliente</th>
                    <th class="py-2.5">Dirección / Ubicación</th>
                    <th class="py-2.5">Estado</th>
                    <th class="text-center pe-3 py-2.5" style="width: 140px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($obras as $obra)
                @php
                    $inicial = strtoupper(substr($obra->nombre ?? 'O', 0, 1));
                @endphp
                <tr class="border-bottom">
                    <td class="ps-3 py-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <div class="obra-avatar-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <strong class="text-body d-block">{{ $obra->nombre }}</strong>
                                @if($obra->contacto_obra)
                                    <small class="text-body-secondary" style="font-size: 11px;">
                                        <i class="bi bi-person me-1"></i>{{ $obra->contacto_obra }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-2.5">
                        @if($obra->cliente)
                            <a href="{{ route('clientes.show', $obra->cliente) }}" class="text-decoration-none fw-semibold text-body">
                                <i class="bi bi-person-circle text-primary me-1"></i>{{ $obra->cliente->nombre_completo }}
                            </a>
                        @else
                            <span class="text-body-secondary">Sin cliente asignado</span>
                        @endif
                    </td>
                    <td class="py-2.5">
                        <span class="text-body d-block">{{ $obra->direccion }}</span>
                        <small class="text-body-secondary" style="font-size: 11px;">
                            {{ $obra->ciudad ?? 'N/A' }}, {{ $obra->estado ?? 'N/A' }}
                        </small>
                    </td>
                    <td class="py-2.5">
                        @if($obra->activa)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Activa
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-2.5 py-1">
                                <i class="bi bi-dash-circle me-1"></i> Inactiva
                            </span>
                        @endif
                    </td>
                    <td class="text-center pe-3 py-2.5">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            <a href="{{ route('obras.show', $obra) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2" title="Ver Detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('obras.edit', $obra) }}" class="btn btn-sm btn-outline-warning rounded-3 px-2" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-body-secondary py-5">
                        <i class="bi bi-building fs-1 d-block mb-2 text-body-tertiary"></i>
                        No se encontraron obras o proyectos registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($obras->hasPages())
    <div class="card-footer bg-body-tertiary border-top p-3 d-flex justify-content-center" style="border-color: var(--bs-border-color) !important;">
        {{ $obras->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection