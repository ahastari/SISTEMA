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

<!-- PESTAÑAS (TABS) PARA USUARIOS NO GLOBALES -->
@if(!$isGlobalAdmin)
<ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--bs-border-color);">
    <li class="nav-item">
        <a class="nav-link {{ $tabActivo == 'local' ? 'active fw-bold border-primary border-bottom-0 text-primary' : 'text-secondary border-0' }}" style="background: {{ $tabActivo == 'local' ? 'var(--bs-body-bg)' : 'transparent' }};" 
           href="{{ route('inventario.index', array_merge(request()->query(), ['tab' => 'local'])) }}">
            <i class="bi bi-box-seam me-1"></i> Mi Inventario
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tabActivo == 'externos' ? 'active fw-bold border-info border-bottom-0 text-info' : 'text-secondary border-0' }}" style="background: {{ $tabActivo == 'externos' ? 'var(--bs-body-bg)' : 'transparent' }};" 
           href="{{ route('inventario.index', array_merge(request()->query(), ['tab' => 'externos'])) }}">
            <i class="bi bi-globe me-1"></i> Catálogo Externo
        </a>
    </li>
</ul>
@endif

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3 p-md-4">
        
        <!-- Tu formulario de búsqueda (Buscador y Select) aquí se mantiene igual -->
        <div class="row g-2 mb-3">
            <div class="col-12 col-md-6 col-lg-5">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <input type="hidden" name="view" value="table"> 
                    <input type="hidden" name="tab" value="{{ $tabActivo }}"> 
                    <div class="input-group input-group-sm shadow-sm rounded">
                        <input type="text" name="search" class="form-control bg-body border-end-0" placeholder="Buscar por nombre, código o código de barras..." value="{{ request('search') }}" autofocus>
                        <button class="btn btn-primary px-3" type="submit"><i class="bi bi-search"></i> Buscar</button>
                    </div>
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
                            <th class="py-2">Nombre</th>
                            <th class="py-2">Categoría</th>
                            
                            @if($tabActivo === 'externos')
                                <th class="py-2">Ubicaciones Disponibles</th>
                            @else
                                <th class="py-2">Stock {{ $isGlobalAdmin ? 'Total' : '' }}</th>
                                <th class="py-2">Estado</th>
                            @endif
                            
                            <th class="text-center py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipos as $equipo)
                        <tr>
                            <td class="text-center">
                                @if($equipo->imagen)
                                    <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 42px; height: 42px; object-fit: cover; border-radius: 8px;" class="shadow-sm">
                                @else
                                    <div class="bg-body-secondary d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; border-radius: 8px;">
                                        <i class="bi bi-box-seam text-secondary fs-5"></i>
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-secondary font-monospace">{{ $equipo->codigo }}</span></td>
                            <td class="fw-semibold text-body">{{ $equipo->nombre }}</td>
                            <td>
                                <span class="badge rounded-pill bg-secondary">{{ $equipo->categoria->nombre ?? 'General' }}</span>
                            </td>
                            
                            <!-- COLUMNAS DINÁMICAS -->
                            @if($tabActivo === 'externos')
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @foreach($equipo->sucursales as $suc)
                                            @if($suc->pivot->stock > 0)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size: 10px;">
                                                    <i class="bi bi-building me-1"></i>{{ $suc->nombre }} ({{ $suc->pivot->stock }})
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                            @else
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
                            @endif

                            <!-- BOTONES DE ACCIÓN DINÁMICOS -->
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <a href="{{ route('inventario.show', $equipo->id) }}" class="btn btn-sm btn-outline-primary border-0 p-1" title="Ver Detalles">
                                        <i class="bi bi-eye fs-6"></i>
                                    </a>
                                    
                                    @if($tabActivo === 'local' || $isGlobalAdmin)
                                        <a href="{{ route('inventario.edit', $equipo->id) }}" class="btn btn-sm btn-outline-warning border-0 p-1" title="Editar Producto">
                                            <i class="bi bi-pencil fs-6"></i>
                                        </a>
                                        <form action="{{ route('inventario.destroy', $equipo->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="return confirm('¿Eliminar producto?')">
                                                <i class="bi bi-trash fs-6"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-secondary">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No hay productos en esta sección.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Aquí va el renderizado d-md-none para móviles (Aplica la misma lógica a los botones) -->
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