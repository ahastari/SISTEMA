@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">Detalle del Movimiento #{{ $movimiento->id }}</h3>
        <p class="text-secondary small mb-0">Auditoría detallada y estados técnicos de transferencia de stock</p>
    </div>
    <div class="d-flex gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>
        @if($movimiento->estado == 'completado')
            <form action="{{ route('movimientos.cancelar', $movimiento) }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" 
                        onclick="return confirm('¿Estás seguro de cancelar este movimiento? Se revertirá el stock.')">
                    <i class="bi bi-x-lg me-1"></i> Cancelar Movimiento
                </button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-8">
        <div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3">
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Producto</small>
                            <h5 class="mb-1 fw-bold text-body text-truncate" title="{{ $movimiento->equipo->nombre }}">{{ $movimiento->equipo->nombre }}</h5>
                            <span class="badge bg-secondary font-monospace">{{ $movimiento->equipo->codigo }}</span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Cantidad</small>
                            <h3 class="mb-0 fw-bold text-primary">{{ $movimiento->cantidad }}</h3>
                            <small class="text-secondary" style="font-size: 11px;">{{ $movimiento->equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Sucursal Origen</small>
                            <h6 class="mb-2 fw-bold text-body text-truncate" title="{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}">{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}</h6>
                            <span class="badge bg-body border text-danger small">
                                <i class="bi bi-arrow-up-circle me-1"></i> Sale de Almacén
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Sucursal Destino</small>
                            <h6 class="mb-2 fw-bold text-body text-truncate" title="{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}">{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}</h6>
                            <span class="badge bg-body border text-success small">
                                <i class="bi bi-arrow-down-circle me-1"></i> Recibe Almacén
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Tipo de Operación</small>
                            @php
                                $badgeClass = [
                                    'entrada' => 'bg-success',
                                    'salida' => 'bg-danger',
                                    'transferencia' => 'bg-info',
                                    'ajuste' => 'bg-warning text-dark'
                                ][$movimiento->tipo] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-3">
                                {{ ucfirst($movimiento->tipo) }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Estado en Sistema</small>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-success-subtle text-success border border-success px-2 rounded-pill small">
                                    <i class="bi bi-check-circle me-1"></i> Completado
                                </span>
                            @elseif($movimiento->estado == 'pendiente')
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 rounded-pill small">
                                    <i class="bi bi-clock me-1"></i> Pendiente
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 rounded-pill small">
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
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-body p-3">
                <h6 class="border-bottom pb-2 mb-3 fw-bold text-body"><i class="bi bi-info-circle me-2 text-primary"></i>Información del Registro</h6>
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Fecha del Movimiento</small>
                    <strong class="text-body small">{{ $movimiento->fecha_movimiento->format('d/m/Y H:i:s') }}</strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Fecha de Confirmación</small>
                    <strong class="text-body small">{{ $movimiento->fecha_confirmacion ? $movimiento->fecha_confirmacion->format('d/m/Y H:i:s') : 'Pendiente' }}</strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Realizado por</small>
                    <strong class="text-body small">{{ $movimiento->usuario->name ?? 'N/A' }}</strong>
                </div>
                
                @if($movimiento->confirmadoPor)
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Confirmado por</small>
                    <strong class="text-body small">{{ $movimiento->confirmadoPor->name }}</strong>
                </div>
                @endif
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Motivo autorizado</small>
                    <strong class="text-body small">{{ $movimiento->motivo ?? 'Sin motivo específico' }}</strong>
                </div>
                
                @if($movimiento->descripcion)
                <div class="border-top pt-2 mt-2">
                    <small class="text-secondary d-block" style="font-size: 11px;">Descripción u Observaciones</small>
                    <p class="text-body small mb-0 p-2 rounded-3 bg-body-tertiary mt-1 border" style="font-size: 12px; white-space: pre-wrap;">{{ $movimiento->descripcion }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection