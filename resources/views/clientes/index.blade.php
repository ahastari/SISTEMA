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
    .cliente-avatar-icon {
        width: 38px;
        height: 38px;
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
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-people-fill text-primary me-2"></i>Agenda de Clientes
        </h2>
        <p class="text-body-secondary small mb-0">Directorio de clientes registrados, datos de contacto y fiscalización.</p>
    </div>
    <div>
        <a href="{{ route('clientes.create') }}" class="btn btn-primary px-3 py-2 rounded-3 fw-bold shadow-sm">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
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

<!-- Barra de Filtro y Búsqueda -->
<div class="filter-card p-3 mb-4">
    <form method="GET" action="{{ route('clientes.index') }}">
        <div class="row g-2 align-items-center">
            <div class="col-12 col-md-6 col-lg-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0 rounded-start-3">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control bg-body text-body border-start-0 rounded-end-3" 
                           placeholder="Buscar por nombre, teléfono, RFC o empresa..." value="{{ request('search') }}">
                </div>
            </div>

            @if($puedeVerInactivos)
            <div class="col-12 col-md-3 col-lg-3">
                <select name="estado_cliente" class="form-select form-select-sm bg-body text-body rounded-3">
                    <option value="">Estado: Todos</option>
                    <option value="activos" {{ request('estado_cliente') == 'activos' ? 'selected' : '' }}>Activos</option>
                    <option value="inactivos" {{ request('estado_cliente') == 'inactivos' ? 'selected' : '' }}>Deshabilitados (+2 años)</option>
                </select>
            </div>
            @endif

            <div class="col-12 col-md-3 col-lg-4 d-flex gap-2">
                <button class="btn btn-sm btn-primary rounded-3 px-3 w-100 fw-semibold" type="submit">
                    <i class="bi bi-search me-1"></i> Buscar
                </button>
                @if(request('search') || request('estado_cliente'))
                    <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3">Limpiar</a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tabla de Clientes -->
<div class="card border shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
    <div class="card-body p-0 table-responsive">
        <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
            <thead class="bg-body-tertiary text-body-secondary border-bottom">
                <tr>
                    <th class="ps-3 py-2.5">Cliente</th>
                    <th class="py-2.5">Teléfono</th>
                    <th class="py-2.5">RFC / Empresa</th>
                    <th class="py-2.5">Estado</th>
                    <th class="text-center pe-3 py-2.5" style="width: 160px;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clientes as $cliente)
                @php
                    $inicial = strtoupper(substr($cliente->nombre_completo ?? 'C', 0, 1));
                @endphp
                <tr class="border-bottom {{ !$cliente->activo ? 'bg-body-tertiary bg-opacity-50' : '' }}">
                    <td class="ps-3 py-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <div class="cliente-avatar-icon">
                                {{ $inicial }}
                            </div>
                            <div>
                                <strong class="text-body d-block">{{ $cliente->nombre_completo }}</strong>
                                @if($cliente->email)
                                    <small class="text-body-secondary" style="font-size: 11px;">
                                        <i class="bi bi-envelope me-1"></i>{{ $cliente->email }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="py-2.5">
                        <span class="text-body fw-semibold"><i class="bi bi-telephone text-primary me-1"></i>{{ $cliente->telefono }}</span>
                    </td>
                    <td class="py-2.5">
                        @if($cliente->empresa)
                            <strong class="text-body d-block">{{ $cliente->empresa }}</strong>
                        @endif
                        @if($cliente->rfc)
                            <span class="badge bg-body-tertiary text-body-secondary border border-secondary-subtle font-monospace" style="font-size: 10px;">
                                {{ $cliente->rfc }}
                            </span>
                        @else
                            <span class="text-body-secondary small">S/N</span>
                        @endif
                    </td>
                    <td class="py-2.5">
                        @if($cliente->activo)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                <i class="bi bi-check-circle-fill me-1"></i> Activo
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1" title="Deshabilitado por inactividad superior a 2 años">
                                <i class="bi bi-clock-history me-1"></i> Inactivo (+2 años)
                            </span>
                        @endif
                    </td>
                    <td class="text-center pe-3 py-2.5">
                        <div class="d-flex justify-content-center align-items-center gap-1">
                            @if($cliente->activo || $puedeVerInactivos)
                                <a href="{{ route('clientes.show', $cliente) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2" title="Ver Detalle">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-sm btn-outline-warning rounded-3 px-2" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            @endif

                            @if(!$cliente->activo && $puedeVerInactivos)
                                <form action="{{ route('clientes.reactivar', $cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success rounded-3 px-2" title="Reactivar Cliente" onclick="return confirm('¿Deseas reactivar a este cliente?')">
                                        <i class="bi bi-person-check-fill me-1"></i> Reactivar
                                    </button>
                                </form>
                            @endif

                            @if($puedeVerInactivos && $cliente->activo)
                                <form action="{{ route('clientes.destroy', $cliente) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3 px-2" onclick="return confirm('¿Eliminar este cliente?')" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-body-secondary py-5">
                        <i class="bi bi-people fs-1 d-block mb-2 text-body-tertiary"></i>
                        No se encontraron clientes registrados.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($clientes->hasPages())
    <div class="card-footer bg-body-tertiary border-top p-3 d-flex justify-content-center" style="border-color: var(--bs-border-color) !important;">
        {{ $clientes->appends(request()->query())->links('pagination::bootstrap-5') }}
    </div>
    @endif
</div>
@endsection