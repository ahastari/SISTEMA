@extends('layouts.admin')

@section('content')
<style>
    /* Soporte total a Temas dinámicos usando variables nativas */
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid var(--bs-border-color);
        background-color: var(--bs-body-bg);
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.15) !important;
    }
    .product-image {
        height: 180px;
        object-fit: cover;
        width: 100%;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 6px;
        padding: 5px 10px;
        font-weight: bold;
        font-size: 0.8rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
        z-index: 10;
    }
    .btn-action {
        border-radius: 6px;
        padding: 6px 12px;
        transition: all 0.2s;
    }
    .barcode-container {
        background-color: var(--bs-tertiary-bg);
        border: 1px dashed var(--bs-border-color);
        border-radius: 6px;
        padding: 6px;
        text-align: center;
    }
    @media (max-width: 768px) {
        .product-image {
            height: 140px;
        }
    }
</style>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">Inventario de Productos</h3>
        <p class="text-secondary small mb-0">Catálogo visual y distribución de stock</p>
    </div>
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        <a href="{{ route('inventario.exportar') }}" class="btn btn-outline-success btn-sm rounded-3 shadow-sm">
            <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-md-inline ms-1">Exportar</span>
        </a>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportarExcel">
            <i class="bi bi-upload"></i> <span class="d-none d-md-inline ms-1">Importar</span>
        </button>
        <a href="{{ route('inventario.index', ['view' => 'table']) }}" class="btn btn-outline-secondary btn-sm rounded-3 shadow-sm">
            <i class="bi bi-table"></i> <span class="d-none d-md-inline ms-1">Vista Tabla</span>
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

<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex flex-wrap gap-2" id="filterButtonGroup">
            <button class="btn btn-outline-primary btn-sm active flex-fill flex-md-grow-0 rounded-3" onclick="filterProducts('all', this)">
                Todos <span class="badge bg-primary ms-1">{{ $equipos->count() }}</span>
            </button>
            <button class="btn btn-outline-success btn-sm flex-fill flex-md-grow-0 rounded-3" onclick="filterProducts('normal', this)">
                Stock Normal 
                <span class="badge bg-success ms-1">
                    {{ $equipos->filter(function($e) { return $e->stock > $e->stock_minimo; })->count() }}
                </span>
            </button>
            <button class="btn btn-outline-warning btn-sm flex-fill flex-md-grow-0 rounded-3" onclick="filterProducts('bajo', this)">
                Stock Bajo 
                <span class="badge bg-warning text-dark ms-1">
                    {{ $equipos->filter(function($e) { return $e->stock > 0 && $e->stock <= $e->stock_minimo; })->count() }}
                </span>
            </button>
            <button class="btn btn-outline-danger btn-sm flex-fill flex-md-grow-0 rounded-3" onclick="filterProducts('agotado', this)">
                Agotados 
                <span class="badge bg-danger ms-1">
                    {{ $equipos->where('stock', 0)->count() }}
                </span>
            </button>
        </div>
    </div>
</div>

<div class="row g-3" id="productsGrid">
    @foreach($equipos as $equipo)
        @php
            if($equipo->stock <= 0) { $stockClass = 'agotado'; $stockStatus = 'Agotado'; $bgClass = 'bg-danger text-white'; } 
            elseif($equipo->stock <= $equipo->stock_minimo) { $stockClass = 'bajo'; $stockStatus = 'Stock Bajo'; $bgClass = 'bg-warning text-dark'; } 
            else { $stockClass = 'normal'; $stockStatus = 'Disponible'; $bgClass = 'bg-success text-white'; }
        @endphp
        
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 product-item {{ $stockClass }}">
            <div class="card product-card shadow-sm h-100 position-relative">
                
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" class="product-image" alt="{{ $equipo->nombre }}">
                @else
                    <div class="product-image bg-body-secondary d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-seam text-secondary" style="font-size: 44px;"></i>
                    </div>
                @endif
                
                <div class="stock-badge {{ $bgClass }}">
                    {{ $stockStatus }}
                </div>
                
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-body border text-body small font-weight-normal"><i class="bi bi-tag me-1 text-primary"></i>{{ $equipo->categoria ? $equipo->categoria->nombre : 'General' }}</span>
                        <span class="badge bg-secondary font-monospace">{{ $equipo->codigo }}</span>
                    </div>
                    
                    <h6 class="card-title fw-bold text-body text-truncate mb-2" title="{{ $equipo->nombre }}">{{ $equipo->nombre }}</h6>
                    
                    @if($equipo->codigo_barras)
                    <div class="barcode-container mb-3">
                        <i class="bi bi-upc-scan text-secondary me-2"></i>
                        <span class="font-monospace fw-bold text-body small">{{ $equipo->codigo_barras }}</span>
                    </div>
                    @else
                    <div class="mb-3" style="height: 33px;"></div>
                    @endif
                    
                    <div class="mt-auto mb-3" style="font-size: 13px;">
                        @if(in_array($equipo->tipo_operacion, ['renta', 'ambas']))
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="text-secondary">Renta:</span>
                                <strong class="text-primary">${{ number_format($equipo->precio_dia, 2) }} <small class="text-secondary fw-normal">/día</small></strong>
                            </div>
                        @endif
                        @if(in_array($equipo->tipo_operacion, ['venta', 'ambas']))
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-secondary">Venta:</span>
                                <strong class="text-success">${{ number_format($equipo->precio_venta, 2) }}</strong>
                            </div>
                        @endif
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center pt-2 border-top mb-3">
                        <small class="text-secondary">En Almacén:</small>
                        <strong class="{{ $equipo->stock <= 0 ? 'text-danger' : 'text-body' }} small">
                            {{ $equipo->stock }} {{ $equipo->unidadMedida ? $equipo->unidadMedida->abreviatura : 'uds' }}
                        </strong>
                    </div>
                    
                    <div class="d-flex gap-1">
                        <a href="{{ route('inventario.show', $equipo) }}" class="btn btn-sm btn-outline-primary border-0 btn-action flex-grow-1" title="Ver Detalles">
                            <i class="bi bi-eye fs-6"></i>
                        </a>
                        <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-sm btn-outline-warning border-0 btn-action flex-grow-1" title="Editar Producto">
                            <i class="bi bi-pencil fs-6"></i>
                        </a>
                        <form action="{{ route('inventario.destroy', $equipo) }}" method="POST" class="d-flex flex-grow-1 m-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger border-0 btn-action w-100" title="Eliminar Producto" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?')">
                                <i class="bi bi-trash fs-6"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
                <div class="modal-body p-3">
                    <p class="text-secondary small mb-3">
                        Asegúrate de que tu archivo tenga los encabezados en la primera fila. Puedes descargar un reporte y usarlo como plantilla.
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

<script>
function filterProducts(type, buttonElement) {
    // Buscar botones solo dentro de su contenedor de filtros
    document.querySelectorAll('#filterButtonGroup .btn').forEach(btn => btn.classList.remove('active'));
    buttonElement.classList.add('active');
    
    document.querySelectorAll('.product-item').forEach(item => {
        if(type === 'all') { 
            item.style.display = ''; 
        } else {
            item.style.display = item.classList.contains(type) ? '' : 'none';
        }
    });
}
</script>
@endsection