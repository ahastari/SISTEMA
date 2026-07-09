@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Detalle del Movimiento #{{ $movimiento->id }}</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
        @if($movimiento->estado == 'completado')
            <form action="{{ route('movimientos.cancelar', $movimiento) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger" 
                        onclick="return confirm('¿Estás seguro de cancelar este movimiento?')">
                    <i class="bi bi-x-lg me-1"></i> Cancelar
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-8">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Producto</small>
                            <h5 class="mb-1">{{ $movimiento->equipo->nombre }}</h5>
                            <span class="badge bg-secondary">{{ $movimiento->equipo->codigo }}</span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Cantidad</small>
                            <h3 class="mb-0 fw-bold">{{ $movimiento->cantidad }}</h3>
                            <small class="text-muted">{{ $movimiento->equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Sucursal Origen</small>
                            <h5 class="mb-1">{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}</h5>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-arrow-up-circle text-danger"></i> Sale
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Sucursal Destino</small>
                            <h5 class="mb-1">{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}</h5>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-arrow-down-circle text-success"></i> Recibe
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Tipo</small>
                            @php
                                $badgeClass = [
                                    'entrada' => 'bg-success',
                                    'salida' => 'bg-danger',
                                    'transferencia' => 'bg-info',
                                    'ajuste' => 'bg-warning text-dark'
                                ][$movimiento->tipo] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} fs-6">
                                {{ ucfirst($movimiento->tipo) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block">Estado</small>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-success fs-6">
                                    <i class="bi bi-check-circle me-1"></i> Completado
                                </span>
                            @elseif($movimiento->estado == 'pendiente')
                                <span class="badge bg-warning text-dark fs-6">
                                    <i class="bi bi-clock me-1"></i> Pendiente
                                </span>
                            @else
                                <span class="badge bg-danger fs-6">
                                    <i class="bi bi-x-circle me-1"></i> Cancelado
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-md-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h6 class="border-bottom pb-2 mb-3"><i class="bi bi-info-circle me-2"></i>Información del Movimiento</h6>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha del Movimiento</small>
                    <strong>{{ $movimiento->fecha_movimiento->format('d/m/Y H:i:s') }}</strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Fecha de Confirmación</small>
                    <strong>{{ $movimiento->fecha_confirmacion ? $movimiento->fecha_confirmacion->format('d/m/Y H:i:s') : 'Pendiente' }}</strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-muted d-block">Realizado por</small>
                    <strong>{{ $movimiento->usuario->name ?? 'N/A' }}</strong>
                </div>
                
                @if($movimiento->confirmadoPor)
                <div class="mb-3">
                    <small class="text-muted d-block">Confirmado por</small>
                    <strong>{{ $movimiento->confirmadoPor->name }}</strong>
                </div>
                @endif
                
                <div class="mb-3">
                    <small class="text-muted d-block">Motivo</small>
                    <strong>{{ $movimiento->motivo ?? 'Sin motivo' }}</strong>
                </div>
                
                @if($movimiento->descripcion)
                <div>
                    <small class="text-muted d-block">Descripción</small>
                    <p class="text-muted small mb-0">{{ $movimiento->descripcion }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection