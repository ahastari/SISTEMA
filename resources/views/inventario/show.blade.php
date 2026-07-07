@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2>Ficha Técnica del Producto</h2>
    <div class="d-flex gap-2">
        @php $rutaRegreso = session('inventory_view') == 'kanban' ? route('inventario.kanban') : route('inventario.index'); @endphp
        <a href="{{ $rutaRegreso }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
        <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Editar Ficha</a>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-4 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" class="img-fluid rounded border shadow-sm mb-4" style="max-height: 250px; width: 100%; object-fit: contain;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center rounded border mb-4" style="height: 250px; width: 100%;">
                        <i class="bi bi-box-seam text-secondary" style="font-size: 80px;"></i>
                    </div>
                @endif

                <div class="row text-start mt-3">
                    <div class="col-6 border-end">
                        <small class="text-muted d-block text-center mb-1">Código Interno</small>
                        <div class="text-center">
                            <span class="badge bg-secondary fs-6">{{ $equipo->codigo }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block text-center mb-1">Código de Barras</small>
                        <div class="text-center">
                            @if($equipo->codigo_barras)
                                <span class="font-monospace fw-bold fs-6 text-dark"><i class="bi bi-upc-scan"></i> {{ $equipo->codigo_barras }}</span>
                            @else
                                <span class="text-muted small">Sin registrar</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start mb-3 gap-2">
                    <h3 class="fw-bold mb-0 text-primary">{{ $equipo->nombre }}</h3>
                    @if($equipo->activo)
                        <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check-circle"></i> Activo en Sistema</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle"></i> Inactivo</span>
                    @endif
                </div>
                
                <p class="text-muted mb-4">{{ $equipo->descripcion ?? 'Sin descripción adicional detallada registrada para este equipo.' }}</p>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex bg-light rounded p-3 align-items-center border">
                            <div class="bg-white rounded p-2 me-3 shadow-sm text-primary"><i class="bi bi-tags fs-4"></i></div>
                            <div>
                                <small class="text-muted d-block">Categoría del Equipo</small>
                                <span class="fw-bold">{{ $equipo->categoria ? $equipo->categoria->nombre : 'Sin categoría' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex bg-light rounded p-3 align-items-center border">
                            <div class="bg-white rounded p-2 me-3 shadow-sm text-dark"><i class="bi bi-boxes fs-4"></i></div>
                            <div>
                                <small class="text-muted d-block">Existencia Actual en Almacén</small>
                                <span class="fw-bold fs-5 {{ $equipo->stock <= $equipo->stock_minimo ? 'text-danger' : 'text-success' }}">
                                    {{ $equipo->stock }} <small class="text-muted fs-6">{{ $equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                                </span>
                                <small class="d-block text-muted" style="font-size: 11px;">(Alerta en {{ $equipo->stock_minimo }})</small>
                            </div>
                        </div>
                    </div>
                </div>

                <h5 class="border-bottom pb-2 mb-3"><i class="bi bi-currency-dollar me-2 text-primary"></i>Información Comercial</h5>
                
                <div class="row g-3">
                    @if(in_array($equipo->tipo_operacion, ['renta', 'ambas']))
                    <div class="col-12 col-sm-6">
                        <div class="border rounded p-3 border-primary border-opacity-25 bg-primary bg-opacity-10 text-center">
                            <span class="d-block text-primary fw-bold mb-1"><i class="bi bi-clock-history"></i> Tarifa de Renta</span>
                            <span class="fs-3 fw-bold text-dark">${{ number_format($equipo->precio_dia, 2) }}</span>
                            <small class="text-muted">/ por día</small>
                        </div>
                    </div>
                    @endif

                    @if(in_array($equipo->tipo_operacion, ['venta', 'ambas']))
                    <div class="col-12 col-sm-6">
                        <div class="border rounded p-3 border-success border-opacity-25 bg-success bg-opacity-10 text-center">
                            <span class="d-block text-success fw-bold mb-1"><i class="bi bi-cart-check"></i> Precio de Venta Público</span>
                            <span class="fs-3 fw-bold text-dark">${{ number_format($equipo->precio_venta, 2) }}</span>
                            <small class="text-muted">IVA incluido</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection