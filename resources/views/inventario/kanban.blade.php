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

<!-- PESTAÑAS (TABS) PARA USUARIOS NO GLOBALES -->
@if(!$isGlobalAdmin)
<ul class="nav nav-tabs mb-3" style="border-bottom: 2px solid var(--bs-border-color);">
    <li class="nav-item">
        <a class="nav-link {{ $tabActivo == 'local' ? 'active fw-bold border-primary border-bottom-0 text-primary' : 'text-secondary border-0' }}" style="background: {{ $tabActivo == 'local' ? 'var(--bs-body-bg)' : 'transparent' }};" 
           href="{{ route('inventario.kanban', array_merge(request()->query(), ['tab' => 'local'])) }}">
            <i class="bi bi-grid me-1"></i> Mi Inventario
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tabActivo == 'externos' ? 'active fw-bold border-info border-bottom-0 text-info' : 'text-secondary border-0' }}" style="background: {{ $tabActivo == 'externos' ? 'var(--bs-body-bg)' : 'transparent' }};" 
           href="{{ route('inventario.kanban', array_merge(request()->query(), ['tab' => 'externos'])) }}">
            <i class="bi bi-globe me-1"></i> Catálogo Externo
        </a>
    </li>
</ul>
@endif

<!-- 🔥 NUEVO BUSCADOR PARA KANBAN -->
<div class="row g-2 mb-3">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="input-group input-group-sm shadow-sm rounded">
            <span class="input-group-text bg-body text-secondary border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" id="inputBusquedaKanban" class="form-control bg-body border-start-0" placeholder="Buscar por nombre, código o escáner..." autofocus>
        </div>
    </div>
</div>

<div class="row g-3" id="productsGrid">
    @foreach($equipos as $equipo)
        @php
            if($tabActivo === 'externos') {
                $stockClass = 'externo'; 
                $stockStatus = 'En otras sucursales'; 
                $bgClass = 'bg-info text-white';
            } else {
                if($equipo->stock <= 0) { $stockClass = 'agotado'; $stockStatus = 'Agotado'; $bgClass = 'bg-danger text-white'; } 
                elseif($equipo->stock <= $equipo->stock_minimo) { $stockClass = 'bajo'; $stockStatus = 'Stock Bajo'; $bgClass = 'bg-warning text-dark'; } 
                else { $stockClass = 'normal'; $stockStatus = 'Disponible'; $bgClass = 'bg-success text-white'; }
            }
        @endphp
        
        <!-- 🔥 Atributos data-* agregados para la búsqueda JS -->
        <div class="col-12 col-sm-6 col-md-4 col-lg-3 product-item {{ $stockClass }}"
             data-texto-busqueda="{{ strtolower($equipo->nombre . ' ' . $equipo->codigo . ' ' . ($equipo->codigo_barras ?? '')) }}">
            
            <!-- (El resto del contenido de tu tarjeta se mantiene igual) -->
            <div class="card product-card shadow-sm h-100 position-relative">
                <img src="{{ $equipo->imagen ? Storage::url($equipo->imagen) : '' }}" class="product-image {{ !$equipo->imagen ? 'bg-secondary bg-opacity-25' : '' }}" alt="Imagen">
                <div class="stock-badge {{ $bgClass }}">{{ $stockStatus }}</div>
                
                <div class="card-body p-3 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-body border text-body small font-weight-normal">{{ $equipo->categoria->nombre ?? 'General' }}</span>
                        <span class="badge bg-secondary font-monospace">{{ $equipo->codigo }}</span>
                    </div>
                    
                    <h6 class="card-title fw-bold text-body text-truncate mb-2">{{ $equipo->nombre }}</h6>
                    
                    <div class="pt-2 border-top mb-3">
                        @if($tabActivo === 'externos')
                            <!-- 🔥 VISTA: CATÁLOGO EXTERNO -->
                            <small class="text-info fw-bold d-block mb-2" style="font-size: 11px;">
                                <i class="bi bi-globe me-1"></i> Disponible en otras sucursales:
                            </small>
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($equipo->sucursales as $suc)
                                    <!-- Filtramos para que solo muestre las que NO son tu sucursal y que tengan stock mayor a 0 -->
                                    @if($suc->id != session('activo_sucursal_id') && $suc->pivot->stock > 0)
                                        <span class="badge bg-info-subtle text-info border border-info-subtle font-monospace" style="font-size: 10px;">
                                            <i class="bi bi-building me-1"></i>{{ $suc->nombre }} ({{ $suc->pivot->stock }})
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <!-- 🔥 VISTA: MI INVENTARIO (Local - Se mantiene sin distracciones) -->
                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-secondary">En Almacén:</small>
                                <strong class="{{ $equipo->stock <= 0 ? 'text-danger' : 'text-body' }} small">
                                    {{ $equipo->stock }} {{ $equipo->unidadMedida->abreviatura ?? 'uds' }}
                                </strong>
                            </div>
                        @endif
                    </div>
                    
                    <div class="d-flex gap-1 mt-auto">
                        <a href="{{ route('inventario.show', $equipo) }}" class="btn btn-sm btn-outline-primary border-0 btn-action flex-grow-1" title="Ver Detalles">
                            <i class="bi bi-eye fs-6"></i> Ver Detalles
                        </a>
                        
                        @if($tabActivo === 'local' || $isGlobalAdmin)
                            <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-sm btn-outline-warning border-0 btn-action" title="Editar Producto">
                                <i class="bi bi-pencil fs-6"></i>
                            </a>
                        @endif
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
let filtroEstadoActivo = 'all';

// Evento para el campo de texto (Búsqueda instantánea)
const inputBusquedaKanban = document.getElementById('inputBusquedaKanban');
if(inputBusquedaKanban) {
    inputBusquedaKanban.addEventListener('input', aplicarFiltrosKanban);
}

// Función para botones (Stock Normal, Bajo, etc.)
function filterProducts(type, buttonElement) {
    // Actualizar apariencia visual de botones
    document.querySelectorAll('#filterButtonGroup .btn').forEach(btn => btn.classList.remove('active'));
    buttonElement.classList.add('active');
    
    // Guardar el estado activo y aplicar filtros
    filtroEstadoActivo = type;
    aplicarFiltrosKanban();
}

// Función maestra que procesa Búsqueda de Texto + Estado de Stock simultáneamente
function aplicarFiltrosKanban() {
    const textoBuscado = inputBusquedaKanban ? inputBusquedaKanban.value.toLowerCase().trim() : '';
    const tarjetas = document.querySelectorAll('.product-item');

    tarjetas.forEach(item => {
        // 1. Validar filtro de botones de estado
        let coincideEstado = (filtroEstadoActivo === 'all') || item.classList.contains(filtroEstadoActivo);

        // 2. Validar búsqueda de texto
        let coincideTexto = true;
        if (textoBuscado !== '') {
            const contenido = item.getAttribute('data-texto-busqueda');
            coincideTexto = contenido.includes(textoBuscado);
        }

        // Si cumple ambas condiciones, mostrar. Si falla alguna, ocultar.
        if (coincideEstado && coincideTexto) {
            item.style.display = ''; 
        } else {
            item.style.display = 'none';
        }
    });
}
</script>
@endsection