@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Inventario de Productos</h2>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('inventario.exportar') }}" class="btn btn-outline-success shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-md-inline">Exportar</span>
        </a>
        <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
            <i class="bi bi-upload"></i> <span class="d-none d-md-inline">Importar</span>
        </button>
        <a href="{{ route('inventario.kanban') }}" class="btn btn-info text-white shadow-sm">
            <i class="bi bi-grid-3x3-gap-fill"></i> <span class="d-none d-md-inline">Vista Kanban</span>
        </a>
        <a href="{{ route('inventario.create') }}" class="btn btn-success shadow-sm">
            <i class="bi bi-plus-lg"></i> <span class="d-none d-md-inline">Nuevo Producto</span>
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-12 col-md-5 mb-2 mb-md-0">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <div class="input-group shadow-sm rounded">
                        <input type="text" name="search" class="form-control border-end-0" placeholder="Buscar por nombre, código o escanear código de barras..." value="{{ request('search') }}" autofocus>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i> Buscar</button>
                    </div>
                </form>
            </div>
            <div class="col-12 col-md-3">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <select name="categoria" class="form-select shadow-sm" onchange="this.form.submit()">
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
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center">Imagen</th>
                            <th>Código Interno</th>
                            <th>Cód. Barras</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipos as $equipo)
                        <tr>
                            <td class="text-center">
                                @if($equipo->imagen && Storage::disk('public')->exists($equipo->imagen))
                                    <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 8px;" class="shadow-sm">
                                @else
                                    <div class="bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px; border-radius: 8px;">
                                        <i class="bi bi-box-seam text-secondary"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $equipo->codigo }}</span>
                            </td>
                            <td>
                                @if($equipo->codigo_barras)
                                    <div class="d-flex align-items-center text-dark">
                                        <i class="bi bi-upc-scan me-2 text-muted fs-5"></i>
                                        <span class="font-monospace fw-bold" style="letter-spacing: 0.5px;">{{ $equipo->codigo_barras }}</span>
                                    </div>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="fw-medium">{{ $equipo->nombre }}</td>
                            <td>
                                @if($equipo->categoria)
                                    <span class="badge rounded-pill" style="background-color: {{ $equipo->categoria->color ?? '#6c757d' }}; color: white; font-weight: normal;">
                                        {{ $equipo->categoria->nombre }}
                                    </span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
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
                                    <span class="text-success small fw-bold"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Activo</span>
                                @else
                                    <span class="text-danger small fw-bold"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('inventario.show', $equipo->id) }}" class="btn btn-sm btn-light border shadow-sm text-primary" title="Ver Detalles">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('inventario.edit', $equipo->id) }}" class="btn btn-sm btn-light border shadow-sm text-warning" title="Editar Producto">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('inventario.destroy', $equipo->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border shadow-sm text-danger" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto permanentemente?')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
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
            <div class="card mb-3 shadow-sm border">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            @if($equipo->imagen && Storage::disk('public')->exists($equipo->imagen))
                                <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;" class="shadow-sm">
                            @else
                                <div class="bg-light d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 60px; height: 60px; border-radius: 8px;">
                                    <i class="bi bi-box-seam text-secondary fs-4"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1 fw-bold">{{ $equipo->nombre }}</h6>
                            <span class="badge bg-secondary me-1">{{ $equipo->codigo }}</span>
                            @if($equipo->categoria)
                                <span class="badge rounded-pill" style="background-color: {{ $equipo->categoria->color ?? '#6c757d' }}; color: white; font-weight: normal;">
                                    {{ $equipo->categoria->nombre }}
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    @if($equipo->codigo_barras)
                    <div class="mb-2">
                        <i class="bi bi-upc-scan me-2 text-muted"></i>
                        <span class="font-monospace fw-bold text-dark">{{ $equipo->codigo_barras }}</span>
                    </div>
                    @endif
                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            @if($equipo->stock <= 0)
                                <span class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Agotado ({{ $equipo->stock }})</span>
                            @elseif($equipo->stock <= $equipo->stock_minimo)
                                <span class="text-warning fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Bajo ({{ $equipo->stock }})</span>
                            @else
                                <span class="text-success fw-bold"><i class="bi bi-check-circle me-1"></i> Disponible ({{ $equipo->stock }})</span>
                            @endif
                        </div>
                        <div>
                            @if($equipo->activo)
                                <span class="text-success small fw-bold"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Activo</span>
                            @else
                                <span class="text-danger small fw-bold"><i class="bi bi-circle-fill" style="font-size: 8px;"></i> Inactivo</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventario.show', $equipo->id) }}" class="btn btn-light border btn-sm flex-grow-1 text-primary shadow-sm" title="Ver Detalles">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <a href="{{ route('inventario.edit', $equipo->id) }}" class="btn btn-light border btn-sm flex-grow-1 text-warning shadow-sm" title="Editar Producto">
                            <i class="bi bi-pencil"></i> Editar
                        </a>
                        <form action="{{ route('inventario.destroy', $equipo->id) }}" method="POST" class="d-inline flex-grow-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light border btn-sm w-100 text-danger shadow-sm" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto permanentemente?')">
                                <i class="bi bi-trash"></i> Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                No hay productos registrados o que coincidan con la búsqueda.
            </div>
            @endforelse
        </div>

        <div class="mt-3">
            {{ $equipos->appends(request()->query())->links() }}
        </div>
    </div>
</div>

<div class="modal fade" id="modalImportarExcel" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-file-earmark-excel me-2"></i>Importar Inventario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('inventario.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small">
                        Asegúrate de que tu archivo tenga los encabezados en la primera fila (código, nombre, categoría, unidad, etc). Puedes descargar un reporte y usarlo como plantilla.
                    </p>
                    <div class="mb-3">
                        <label for="documento_excel" class="form-label fw-bold">Archivo Excel (.xlsx, .csv)</label>
                        <input class="form-control" type="file" id="documento_excel" name="documento_excel" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-upload me-1"></i> Subir e Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection