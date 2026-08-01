@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-box-seam me-2 text-primary"></i>Inventario de Productos
        </h3>
        <p class="text-secondary small mb-0">Gestión de catálogo, existencias y códigos</p>
    </div>
    
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        <a href="{{ route('inventario.exportar') }}" class="btn btn-outline-success btn-sm rounded-3 shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-md-inline ms-1">Exportar</span>
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
            <i class="bi bi-upload"></i> <span class="d-none d-md-inline ms-1">Importar</span>
        </button>
        <a href="{{ route('inventario.kanban') }}" class="btn btn-info btn-sm text-white rounded-3 shadow-sm">
            <i class="bi bi-grid-3x3-gap-fill"></i> <span class="d-none d-md-inline ms-1">Vista Kanban</span>
        </a>
        <a href="{{ route('inventario.create') }}" class="btn btn-success btn-sm rounded-3 shadow-sm fw-semibold">
            <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline ms-1">Nuevo Producto</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3 p-md-4">
        
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <input type="hidden" name="view" value="table"> 
                    
                    <div class="input-group input-group-sm shadow-sm rounded">
                        <input type="text" name="search" class="form-control bg-body border-end-0" placeholder="Buscar por nombre, código o código de barras..." value="{{ request('search') }}" autofocus>
                        <button class="btn btn-primary px-3" type="submit"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-4 col-lg-3">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <input type="hidden" name="view" value="table">

                    <select name="categoria" class="form-select form-select-sm bg-body shadow-sm" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->nombre }}" {{ request('categoria') == $categoria->nombre ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>

        <div class="d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-body-tertiary text-body border-bottom">
                    <tr>
                        <th class="text-center py-2" style="width: 60px;">Imagen</th>
                        <th class="py-2">Código Interno</th>
                        <th class="py-2">Cód. Barras</th>
                        <th class="py-2">Nombre</th>
                        <th class="py-2">Categoría</th>
                        <th class="py-2">Costo de producción</th>
                        @if($isGlobalAdmin)
                            <th class="py-2">Distribución por Sucursal</th>
                        @endif
                        <th class="py-2">Stock {{ $isGlobalAdmin ? 'Total' : '' }}</th>
                        <th class="py-2">Estado</th>
                        <th class="text-center py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipos as $equipo)
                    <tr>
                        <td class="text-center">
                            @if($equipo->imagen && Storage::disk('public')->exists($equipo->imagen))
                                <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px;" class="shadow-sm">
                            @else
                                <div class="bg-body-secondary d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; border-radius: 8px;">
                                    <i class="bi bi-box-seam text-secondary fs-5"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary font-monospace">{{ $equipo->codigo }}</span>
                        </td>
                        <td>
                            @if($equipo->codigo_barras)
                                <div class="d-flex align-items-center text-body">
                                    <i class="bi bi-upc-scan me-2 text-secondary fs-6"></i>
                                    <span class="font-monospace fw-bold" style="letter-spacing: 0.5px;">{{ $equipo->codigo_barras }}</span>
                                </div>
                            @else
                                <span class="text-secondary small">N/A</span>
                            @endif
                        </td>
                        <td class="fw-semibold text-body">{{ $equipo->nombre }}</td>
                        <td>
                            @if($equipo->categoria)
                                <span class="badge rounded-pill" style="background-color: {{ $equipo->categoria->color ?? '#6c757d' }}; color: white; font-weight: 500;">
                                    {{ $equipo->categoria->nombre }}
                                </span>
                            @else
                                <span class="text-secondary small">N/A</span>
                            @endif
                        </td>
                        <!-- COLUMNA COSTO -->
                        <td class="fw-bold font-monospace text-body">
                            ${{ number_format($equipo->costo ?? 0, 2) }}
                        </td>
                        <!-- DESGLOSE POR SUCURSAL SOLO PARA ADMIN GLOBAL -->
                        @if($isGlobalAdmin)
                        <td>
                            <div class="d-flex flex-wrap gap-1" style="max-width: 250px;">
                                @forelse($equipo->sucursales as $suc)
                                    <span class="badge bg-body-tertiary text-body border font-monospace" style="font-size: 10px;">
                                        <i class="bi bi-building text-primary me-1"></i>{{ $suc->nombre }}: 
                                        <strong class="{{ $suc->pivot->stock <= 0 ? 'text-danger' : 'text-success' }}">{{ $suc->pivot->stock }}</strong>
                                    </span>
                                @empty
                                    <span class="text-muted small">Sin asignar</span>
                                @endforelse
                            </div>
                        </td>
                        @endif
                        <td>
                            @if($equipo->stock <= 0)
                                <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> {{ $equipo->stock }}</span>
                            @elseif($equipo->stock <= $equipo->stock_minimo)
                                <span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> {{ $equipo->stock }}</span>
                            @else
                                <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i> {{ $equipo->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if($equipo->activo)
                                <span class="text-success small fw-bold"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> Activo</span>
                            @else
                                <span class="text-danger small fw-bold"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('inventario.show', $equipo->id) }}" class="btn btn-sm btn-outline-primary border-0 p-1" title="Ver Detalles">
                                    <i class="bi bi-eye fs-6"></i>
                                </a>
                                <a href="{{ route('inventario.edit', $equipo->id) }}" class="btn btn-sm btn-outline-warning border-0 p-1" title="Editar Producto">
                                    <i class="bi bi-pencil fs-6"></i>
                                </a>
                                <form action="{{ route('inventario.destroy', $equipo->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto permanentemente?')">
                                        <i class="bi bi-trash fs-6"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ $isGlobalAdmin ? '10' : '9' }}" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No hay productos registrados o que coincidan con la búsqueda.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

        <div class="d-md-none">
            @forelse($equipos as $equipo)
            <div class="card mb-2 border rounded-3 p-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                <div class="d-flex align-items-center mb-2">
                    <div class="flex-shrink-0">
                        @if($equipo->imagen && Storage::disk('public')->exists($equipo->imagen))
                            <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;" class="shadow-sm">
                        @else
                            <div class="bg-body-secondary d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 50px; height: 50px; border-radius: 8px;">
                                <i class="bi bi-box-seam text-secondary fs-4"></i>
                            </div>
                        @endif
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="mb-1 fw-bold text-body">{{ $equipo->nombre }}</h6>
                        <span class="badge bg-secondary font-monospace me-1">{{ $equipo->codigo }}</span>
                        @if($equipo->categoria)
                            <span class="badge rounded-pill" style="background-color: {{ $equipo->categoria->color ?? '#6c757d' }}; color: white; font-weight: 500;">
                                {{ $equipo->categoria->nombre }}
                            </span>
                        @endif
                    </div>
                </div>
                
                @if($equipo->codigo_barras)
                <div class="mb-2 small">
                    <i class="bi bi-upc-scan me-1 text-secondary"></i>
                    <span class="font-monospace fw-bold text-body">{{ $equipo->codigo_barras }}</span>
                </div>
                @endif
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        @if($equipo->stock <= 0)
                            <span class="text-danger fw-bold small"><i class="bi bi-x-circle me-1"></i> Agotado ({{ $equipo->stock }})</span>
                        @elseif($equipo->stock <= $equipo->stock_minimo)
                            <span class="text-warning fw-bold small"><i class="bi bi-exclamation-triangle me-1"></i> Bajo ({{ $equipo->stock }})</span>
                        @else
                            <span class="text-success fw-bold small"><i class="bi bi-check-circle me-1"></i> Disponible ({{ $equipo->stock }})</span>
                        @endif
                    </div>
                    <div>
                        @if($equipo->activo)
                            <span class="text-success small fw-bold"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> Activo</span>
                        @else
                            <span class="text-danger small fw-bold"><i class="bi bi-circle-fill" style="font-size: 7px;"></i> Inactivo</span>
                        @endif
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <a href="{{ route('inventario.show', $equipo->id) }}" class="btn btn-outline-primary btn-sm flex-grow-1" title="Ver Detalles">
                        <i class="bi bi-eye me-1"></i> Ver
                    </a>
                    <a href="{{ route('inventario.edit', $equipo->id) }}" class="btn btn-outline-warning btn-sm flex-grow-1" title="Editar Producto">
                        <i class="bi bi-pencil me-1"></i> Editar
                    </a>
                    <form action="{{ route('inventario.destroy', $equipo->id) }}" method="POST" class="d-inline flex-grow-1">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto permanentemente?')">
                            <i class="bi bi-trash me-1"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-secondary">
                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                No hay productos registrados o que coincidan con la búsqueda.
            </div>
            @endforelse
        </div>

        <div class="mt-3 d-flex justify-content-center">
            {{ $equipos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportarExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-file-earmark-excel me-2"></i>Importar Inventario</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-secondary small mb-3">
                        Asegúrate de que tu archivo tenga los encabezados en la primera fila (código, nombre, categoría, unidad, etc). Puedes descargar un reporte y usarlo como plantilla.
                    </p>
                    <div class="mb-2">
                        <label for="documento_excel" class="form-label small fw-semibold text-body">Archivo Excel (.xlsx, .csv)</label>
                        <input class="form-control form-control-sm bg-body" type="file" id="documento_excel" name="documento_excel" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-semibold"><i class="bi bi-upload me-1"></i> Subir e Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection