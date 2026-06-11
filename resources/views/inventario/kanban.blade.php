@extends('layouts.admin')

@section('content')
<style>
    .product-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
        border-radius: 16px;
        overflow: hidden;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .product-image {
        height: 180px;
        object-fit: cover;
        width: 100%;
    }
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        border-radius: 20px;
        padding: 5px 12px;
        font-weight: bold;
    }
    .price-tag {
        font-size: 24px;
        font-weight: bold;
        color: #2c7da0;
    }
    .btn-action {
        border-radius: 30px;
        padding: 8px 16px;
        font-weight: 500;
    }
    .category-badge {
        background: #e9ecef;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        color: #495057;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-grid-3x3-gap-fill me-2"></i> Inventario de Equipos
    </h2>
    <div>
        <a href="{{ route('inventario.index') }}" class="btn btn-outline-secondary me-2">
            <i class="bi bi-table"></i> Vista Tabla
        </a>
        <a href="{{ route('inventario.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Equipo
        </a>
    </div>
</div>

<!-- Filtros rápidos -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <button class="btn btn-outline-primary active" onclick="filterProducts('all')">
                Todos <span class="badge bg-primary ms-1">{{ $equipos->count() }}</span>
            </button>
            <button class="btn btn-outline-success" onclick="filterProducts('normal')">
                Stock Normal <span class="badge bg-success ms-1">{{ $equipos->where('stock', '>', 5)->count() }}</span>
            </button>
            <button class="btn btn-outline-warning" onclick="filterProducts('bajo')">
                Stock Bajo <span class="badge bg-warning ms-1">{{ $equipos->where('stock', '>', 0)->where('stock', '<=', 5)->count() }}</span>
            </button>
            <button class="btn btn-outline-danger" onclick="filterProducts('agotado')">
                Agotados <span class="badge bg-danger ms-1">{{ $equipos->where('stock', 0)->count() }}</span>
            </button>
        </div>
    </div>
</div>

<!-- Grid de productos estilo Kanban -->
<div class="row" id="productsGrid">
    @foreach($equipos as $equipo)
        @php
            $stockClass = '';
            $stockStatus = '';
            if($equipo->stock <= 0) {
                $stockClass = 'agotado';
                $stockStatus = 'Agotado';
            } elseif($equipo->stock <= 5) {
                $stockClass = 'bajo';
                $stockStatus = 'Stock Bajo';
            } else {
                $stockClass = 'normal';
                $stockStatus = 'Disponible';
            }
        @endphp
        
        <div class="col-md-3 mb-4 product-item {{ $stockClass }}">
            <div class="card product-card shadow-sm h-100">
                <!-- Imagen -->
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" class="product-image" alt="{{ $equipo->nombre }}">
                @else
                    <div class="product-image bg-light d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-seam" style="font-size: 60px; color: #adb5bd;"></i>
                    </div>
                @endif
                
                <!-- Badge de stock -->
                <div class="stock-badge {{ $equipo->stock <= 0 ? 'bg-danger' : ($equipo->stock <= 5 ? 'bg-warning' : 'bg-success') }} text-white">
                    <i class="bi {{ $equipo->stock <= 0 ? 'bi-x-circle' : ($equipo->stock <= 5 ? 'bi-exclamation-triangle' : 'bi-check-circle') }} me-1"></i>
                    {{ $stockStatus }}
                </div>
                
                <div class="card-body">
                    <!-- Código y categoría -->
                    <div class="d-flex justify-content-between mb-2">
                        <span class="category-badge">
                            <i class="bi bi-tag me-1"></i>{{ $equipo->categoria }}
                        </span>
                        <small class="text-muted">{{ $equipo->codigo }}</small>
                    </div>
                    
                    <!-- Nombre -->
                    <h5 class="card-title fw-bold mb-2">{{ $equipo->nombre }}</h5>
                    
                    <!-- Precio -->
                    <div class="price-tag mb-3">
                        ${{ number_format($equipo->precio_dia, 2) }}
                        <small class="text-muted" style="font-size: 14px;">/ día</small>
                    </div>
                    
                    <!-- Stock -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Unidades disponibles</small>
                            <strong class="{{ $equipo->stock <= 0 ? 'text-danger' : ($equipo->stock <= 5 ? 'text-warning' : 'text-success') }}">
                                {{ $equipo->stock }}
                            </strong>
                        </div>
                        <div class="progress" style="height: 6px;">
                            @php
                                $maxStock = 50;
                                $porcentaje = min(100, ($equipo->stock / $maxStock) * 100);
                            @endphp
                            <div class="progress-bar {{ $equipo->stock <= 0 ? 'bg-danger' : ($equipo->stock <= 5 ? 'bg-warning' : 'bg-success') }}" 
                                 style="width: {{ $porcentaje }}%"></div>
                        </div>
                    </div>
                    
                    <!-- Botones de acción -->
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventario.show', $equipo) }}" class="btn btn-outline-info btn-action flex-grow-1">
                            <i class="bi bi-eye"></i> Ver
                        </a>
                        <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-outline-warning btn-action">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('inventario.destroy', $equipo) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger btn-action" onclick="return confirm('¿Eliminar este equipo?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
function filterProducts(type) {
    // Actualizar estado de los botones
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');
    
    // Filtrar productos
    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        if(type === 'all') {
            item.style.display = '';
        } else {
            if(item.classList.contains(type)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        }
    });
}
</script>
@endsection