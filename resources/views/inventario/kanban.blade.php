@extends('layouts.admin')

@section('content')
<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e9ecef;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.08) !important;
    }
    .product-image {
        height: 180px;
        object-fit: cover;
        width: 100%;
        border-bottom: 1px solid #f8f9fa;
    }
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 6px;
        padding: 5px 10px;
        font-weight: bold;
        font-size: 0.8rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .btn-action {
        border-radius: 6px;
        padding: 6px 12px;
        transition: all 0.2s;
    }
    .btn-action:hover {
        background-color: #e9ecef;
    }
    .barcode-container {
        background-color: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 6px;
        padding: 6px;
        text-align: center;
    }
    @media (max-width: 768px) {
        .product-image {
            height: 120px;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Inventario de Productos</h2>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('inventario.exportar') }}" class="btn btn-outline-success shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-md-inline">Exportar</span>
        </a>
        <button type="button" class="btn btn-outline-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
            <i class="bi bi-upload"></i> <span class="d-none d-md-inline">Importar</span>
        </button>
        <a href="{{ route('inventario.index', ['view' => 'table']) }}" class="btn btn-outline-secondary shadow-sm">
            <i class="bi bi-table"></i> <span class="d-none d-md-inline">Vista Tabla</span>
        </a>
        <a href="{{ route('inventario.create') }}" class="btn btn-primary shadow-sm">
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

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2">
            <button class="btn btn-outline-primary active flex-fill flex-md-grow-0" onclick="filterProducts('all')">
                Todos <span class="badge bg-primary ms-1">{{ $equipos->count() }}</span>
            </button>
            <button class="btn btn-outline-success flex-fill flex-md-grow-0" onclick="filterProducts('normal')">
                Stock Normal 
                <span class="badge bg-success ms-1">
                    {{ $equipos->filter(function($e) { return $e->stock > $e->stock_minimo; })->count() }}
                </span>
            </button>
            <button class="btn btn-outline-warning flex-fill flex-md-grow-0" onclick="filterProducts('bajo')">
                Stock Bajo 
                <span class="badge bg-warning ms-1">
                    {{ $equipos->filter(function($e) { return $e->stock > 0 && $e->stock <= $e->stock_minimo; })->count() }}
                </span>
            </button>
            <button class="btn btn-outline-danger flex-fill flex-md-grow-0" onclick="filterProducts('agotado')">
                Agotados 
                <span class="badge bg-danger ms-1">
                    {{ $equipos->where('stock', 0)->count() }}
                </span>
            </button>
        </div>
    </div>
</div>

<div class="row" id="productsGrid">
    @foreach($equipos as $equipo)
        @php
            if($equipo->stock <= 0) { $stockClass = 'agotado'; $stockStatus = 'Agotado'; $bgClass = 'bg-danger'; } 
            elseif($equipo->stock <= $equipo->stock_minimo) { $stockClass = 'bajo'; $stockStatus = 'Stock Bajo'; $bgClass = 'bg-warning text-dark'; } 
            else { $stockClass = 'normal'; $stockStatus = 'Disponible'; $bgClass = 'bg-success'; }
        @endphp
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 mb-4 product-item {{ $stockClass }}">
            <div class="card product-card shadow-sm h-100 border-0">
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" class="product-image" alt="{{ $equipo->nombre }}">
                @else
                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-seam" style="font-size: 50px; color: #dee2e6;"></i>
                    </div>
                @endif
                
                <div class="stock-badge {{ $bgClass }} text-white">
                    {{ $stockStatus }}
                </div>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-dark border"><i class="bi bi-tag me-1"></i>{{ $equipo->categoria ? $equipo->categoria->nombre : 'General' }}</span>
                        <span class="badge bg-secondary">{{ $equipo->codigo }}</span>
                    </div>
                    
                    <h5 class="card-title fw-bold text-truncate" title="{{ $equipo->nombre }}">{{ $equipo->nombre }}</h5>
                    
                    @if($equipo->codigo_barras)
                    <div class="barcode-container mb-3 mt-1">
                        <i class="bi bi-upc-scan text-muted me-2"></i>
                        <span class="font-monospace fw-bold text-dark">{{ $equipo->codigo_barras }}</span>
                    </div>
                    @else
                    <div class="mb-3 mt-1" style="height: 34px;"></div>
                    @endif
                    
                    <div class="mt-auto mb-3">
                        @if(in_array($equipo->tipo_operacion, ['renta', 'ambas']))
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <small class="text-muted">Renta:</small>
                                <strong class="text-primary">${{ number_format($equipo->precio_dia, 2) }} <small class="fw-normal">/día</small></strong>
                            </div>
                        @endif
                        @if(in_array($equipo->tipo_operacion, ['venta', 'ambas']))
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted">Venta:</small>
                                <strong class="text-success">${{ number_format($equipo->precio_venta, 2) }}</strong>
                            </div>
                        @endif
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-3 border-top mb-3">
                        <small class="text-muted">En inventario:</small>
                        <strong class="{{ $equipo->stock <= 0 ? 'text-danger' : 'text-dark' }} fs-6">
                            {{ $equipo->stock }} {{ $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'uds' }}
                        </strong>
                    </div>
                    
                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('inventario.show', $equipo) }}" class="btn btn-light border btn-action flex-grow-1 text-primary shadow-sm" title="Ver Detalles">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-light border btn-action flex-grow-1 text-warning shadow-sm" title="Editar Producto">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('inventario.destroy', $equipo) }}" method="POST" class="d-flex flex-grow-1">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-light border btn-action w-100 text-danger shadow-sm" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
                        Asegúrate de que tu archivo tenga los encabezados en la primera fila. Puedes descargar un reporte y usarlo como plantilla.
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

<script>
function filterProducts(type) {
    document.querySelectorAll('.btn-group .btn, .d-flex.flex-wrap .btn').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
    
    document.querySelectorAll('.product-item').forEach(item => {
        if(type === 'all') { item.style.display = ''; } 
        else {
            if(item.classList.contains(type)) { item.style.display = ''; } 
            else { item.style.display = 'none'; }
        }
    });
}
</script>
@endsection