@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">Ficha Técnica del Producto</h3>
        <p class="text-secondary small mb-0">Detalles técnicos, existencias y tarifas comerciales</p>
    </div>
    <div class="d-flex gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        @php $rutaRegreso = session('inventory_view') == 'kanban' ? route('inventario.kanban') : route('inventario.index'); @endphp
        <a href="{{ $rutaRegreso }}" class="btn btn-outline-secondary btn-sm rounded-3"><i class="bi bi-arrow-left"></i> Volver</a>
        <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-warning btn-sm rounded-3 text-dark fw-semibold"><i class="bi bi-pencil"></i> Editar Ficha</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-body text-center p-3">
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" class="img-fluid rounded-3 border shadow-sm mb-3" style="max-height: 250px; width: 100%; object-fit: contain;">
                @else
                    <div class="bg-body-tertiary d-flex align-items-center justify-content-center rounded-3 border mb-3" style="height: 250px; width: 100%;">
                        <i class="bi bi-box-seam text-secondary" style="font-size: 70px;"></i>
                    </div>
                @endif

                <div class="row text-start g-2 border-top pt-3">
                    <div class="col-6 border-end">
                        <small class="text-secondary d-block text-center mb-1" style="font-size: 11px;">Código Interno</small>
                        <div class="text-center">
                            <span class="badge bg-secondary font-monospace">{{ $equipo->codigo }}</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <small class="text-secondary d-block text-center mb-1" style="font-size: 11px;">Código de Barras</small>
                        <div class="text-center">
                            @if($equipo->codigo_barras)
                                <span class="font-monospace fw-bold small text-body"><i class="bi bi-upc-scan me-1"></i>{{ $equipo->codigo_barras }}</span>
                            @else
                                <span class="text-secondary small">Sin registrar</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-body p-3 p-md-4">
                
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start mb-3 gap-2">
                    <h4 class="fw-bold mb-0 text-primary">{{ $equipo->nombre }}</h4>
                    @if($equipo->activo)
                        <span class="badge bg-success-subtle text-success border border-success rounded-pill px-2 py-1 small"><i class="bi bi-check-circle me-1"></i>Activo en Sistema</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger rounded-pill px-2 py-1 small"><i class="bi bi-x-circle me-1"></i>Inactivo</span>
                    @endif
                </div>
                
                <p class="text-secondary small mb-4">{{ $equipo->descripcion ?? 'Sin descripción adicional detallada registrada para este equipo.' }}</p>

                <div class="row g-2 mb-4">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex bg-body-tertiary rounded-3 p-3 align-items-center border">
                            <div class="bg-body border rounded-3 p-2 me-3 shadow-sm text-primary"><i class="bi bi-tags fs-4"></i></div>
                            <div>
                                <small class="text-secondary d-block" style="font-size: 11px;">Categoría del Equipo</small>
                                <span class="fw-bold text-body small">{{ $equipo->categoria ? $equipo->categoria->nombre : 'Sin categoría' }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex bg-body-tertiary rounded-3 p-3 align-items-center border">
                            <div class="bg-body border rounded-3 p-2 me-3 shadow-sm text-body"><i class="bi bi-boxes fs-4"></i></div>
                            <div>
                                <small class="text-secondary d-block" style="font-size: 11px;">Existencia Total</small>
                                <span class="fw-bold fs-5 {{ $equipo->stock <= $equipo->stock_minimo ? 'text-danger' : 'text-success' }}">
                                    {{ $equipo->stock }} <small class="text-secondary fs-6">{{ $equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                                </span>
                                <small class="d-block text-secondary" style="font-size: 11px;">(Alerta en {{ $equipo->stock_minimo }})</small>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="border-bottom pb-2 mb-3 text-body fw-bold"><i class="bi bi-building me-2 text-primary"></i>Stock por Sucursal</h6>
                
                <div class="row g-2 mb-4">
                    @forelse($sucursalesConStock as $sucursal)
                    <div class="col-12 col-sm-6 col-md-4">
                        <div class="border rounded-3 p-3 bg-body-tertiary h-100">
                            <div class="d-flex justify-content-between align-items-start gap-1">
                                <span class="d-block fw-bold text-body small text-truncate" title="{{ $sucursal['nombre'] }}">
                                    <i class="bi bi-building text-primary me-1"></i> {{ $sucursal['nombre'] }}
                                </span>
                                <span class="badge bg-secondary rounded-pill" style="font-size: 9px;">{{ $sucursal['stock_minimo'] }} mín.</span>
                            </div>
                            <div class="my-1">
                                <span class="fs-3 fw-bold {{ $sucursal['stock'] <= $sucursal['stock_minimo'] ? 'text-danger' : 'text-success' }}">
                                    {{ $sucursal['stock'] }}
                                </span>
                                <small class="text-secondary small">{{ $equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                            </div>
                            <div>
                                @if($sucursal['stock'] <= 0)
                                    <span class="badge bg-danger" style="font-size: 10px;"><i class="bi bi-x-circle me-1"></i>Agotado</span>
                                @elseif($sucursal['stock'] <= $sucursal['stock_minimo'])
                                    <span class="badge bg-warning text-dark" style="font-size: 10px;"><i class="bi bi-exclamation-triangle me-1"></i>Stock Bajo</span>
                                @else
                                    <span class="badge bg-success" style="font-size: 10px;"><i class="bi bi-check-circle me-1"></i>Disponible</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="alert alert-info mb-0 py-2 px-3 small">
                            <i class="bi bi-info-circle me-2"></i>
                            Este producto no tiene stock asignado en ninguna sucursal.
                        </div>
                    </div>
                    @endforelse
                </div>

                <h6 class="border-bottom pb-2 mb-3 text-body fw-bold"><i class="bi bi-currency-dollar me-2 text-primary"></i>Información Comercial</h6>
                
                <div class="row g-2">
                    @if(in_array($equipo->tipo_operacion, ['renta', 'ambas']))
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3 text-center" style="background: rgba(13, 110, 253, 0.08); border-color: rgba(13, 110, 253, 0.25) !important;">
                            <span class="d-block text-primary fw-bold small mb-1"><i class="bi bi-clock-history me-1"></i> Tarifa de Renta</span>
                            <span class="fs-4 fw-bold text-body">${{ number_format($equipo->precio_dia, 2) }}</span>
                            <small class="text-secondary d-block" style="font-size: 11px;">/ por día</small>
                        </div>
                    </div>
                    @endif

                    @if(in_array($equipo->tipo_operacion, ['venta', 'ambas']))
                    <div class="col-12 col-sm-6">
                        <div class="border rounded-3 p-3 text-center" style="background: rgba(25, 135, 84, 0.08); border-color: rgba(25, 135, 84, 0.25) !important;">
                            <span class="d-block text-success fw-bold small mb-1"><i class="bi bi-cart-check me-1"></i> Precio de Venta Público</span>
                            <span class="fs-4 fw-bold text-body">${{ number_format($equipo->precio_venta, 2) }}</span>
                            <small class="text-secondary d-block" style="font-size: 11px;">IVA incluido</small>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="mt-4 pt-3 border-top d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('movimientos.create') }}?equipo_id={{ $equipo->id }}" class="btn btn-primary btn-sm rounded-3">
                        <i class="bi bi-arrow-left-right me-1"></i> Registrar Movimiento
                    </a>
                    <small class="text-secondary">Transferir o ajustar existencias de este producto entre tus almacenes.</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection