@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-file-earmark-text me-2 text-primary"></i>Detalle de la Operación
        </h3>
        <p class="text-secondary small mb-0">Auditoría detallada y estados técnicos de la transferencia de stock</p>
    </div>
    
    <!-- Botones de Acción según Estado -->
    <div class="d-flex flex-wrap gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left me-1"></i> Volver
        </a>

        @if($movimiento->estado == 'pendiente' && (auth()->user()->isGerente() || auth()->user()->isAdmin()))
            <form action="{{ route('movimientos.aprobar', $movimiento) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm rounded-3 fw-semibold">
                    <i class="bi bi-check-lg me-1"></i> Aprobar Transferencia
                </button>
            </form>

            <form action="{{ route('movimientos.rechazar', $movimiento) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm rounded-3" onclick="return confirm('¿Rechazar esta solicitud y devolver la cantidad al stock de origen?')">
                    <i class="bi bi-x-lg me-1"></i> Rechazar
                </button>
            </form>
        @endif

        @if($movimiento->estado == 'aprobado')
            <form action="{{ route('movimientos.confirmarRecepcion', $movimiento) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm rounded-3 fw-semibold" onclick="return confirm('¿Confirmar que los productos fueron ingresados físicamente al destino?')">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Confirmar Recepción de Material
                </button>
            </form>
        @endif

        @if(in_array($movimiento->estado, ['completado', 'pendiente', 'aprobado']))
            <form action="{{ route('movimientos.procesarCancelacion', $movimiento) }}" method="POST" class="d-inline m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm rounded-3" 
                        onclick="return confirm('¿Estás seguro de cancelar este movimiento? Se revertirán las cantidades correspondientes.')">
                    <i class="bi bi-slash-circle me-1"></i> Cancelar Movimiento
                </button>
            </form>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <!-- Información Principal del Movimiento -->
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
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Cantidad Solicitada</small>
                            <h3 class="mb-0 fw-bold text-primary">{{ $movimiento->cantidad }}</h3>
                            <small class="text-secondary" style="font-size: 11px;">{{ $movimiento->equipo->unidadMedida->abreviatura ?? 'uds' }}</small>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Sucursal Origen</small>
                            <h6 class="mb-2 fw-bold text-body text-truncate" title="{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}">{{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}</h6>
                            <span class="badge bg-body border text-danger small">
                                <i class="bi bi-arrow-up-circle me-1"></i> Descontado del Stock
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-12 col-sm-6">
                        <div class="p-3 bg-body-tertiary rounded-3 border h-100">
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Sucursal Destino</small>
                            <h6 class="mb-2 fw-bold text-body text-truncate" title="{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}">{{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}</h6>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-body border text-success small">
                                    <i class="bi bi-check-circle me-1"></i> Recibido en Stock
                                </span>
                            @else
                                <span class="badge bg-body border text-warning small">
                                    <i class="bi bi-clock me-1"></i> Pendiente de Recepción
                                </span>
                            @endif
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
                            <small class="text-secondary d-block mb-1" style="font-size: 11px;">Estado de la Operación</small>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-success-subtle text-success border border-success px-2 rounded-pill small">
                                    <i class="bi bi-check-circle me-1"></i> Completado
                                </span>
                            @elseif($movimiento->estado == 'pendiente')
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 rounded-pill small">
                                    <i class="bi bi-clock me-1"></i> Pendiente Autorización
                                </span>
                            @elseif($movimiento->estado == 'aprobado')
                                <span class="badge bg-info-subtle text-info border border-info px-2 rounded-pill small">
                                    <i class="bi bi-truck me-1"></i> Aprobado (En Tránsito)
                                </span>
                            @elseif($movimiento->estado == 'rechazado')
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 rounded-pill small">
                                    <i class="bi bi-x-circle me-1"></i> Rechazado
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary px-2 rounded-pill small">
                                    <i class="bi bi-slash-circle me-1"></i> Cancelado
                                </span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    
    <!-- Registro y Auditoría del Sistema -->
    <div class="col-12 col-md-4">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <div class="card-body p-3">
                <h6 class="border-bottom pb-2 mb-3 fw-bold text-body"><i class="bi bi-shield-check me-2 text-primary"></i>Datos de Auditoría</h6>
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Fecha de Solicitud</small>
                    <strong class="text-body small">{{ $movimiento->fecha_movimiento ? $movimiento->fecha_movimiento->format('d/m/Y H:i:s') : 'N/A' }}</strong>
                </div>
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Solicitado por</small>
                    <strong class="text-body small">{{ $movimiento->usuario->name ?? 'Usuario Sistema' }}</strong>
                </div>

                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Fecha Autorización / Respuesta</small>
                    <strong class="text-body small">{{ $movimiento->fecha_confirmacion ? $movimiento->fecha_confirmacion->format('d/m/Y H:i:s') : 'Pendiente' }}</strong>
                </div>
                
                @if($movimiento->confirmadoPor)
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Atendido / Autorizado por</small>
                    <strong class="text-body small">{{ $movimiento->confirmadoPor->name }}</strong>
                </div>
                @endif
                
                <div class="mb-3">
                    <small class="text-secondary d-block" style="font-size: 11px;">Motivo</small>
                    <strong class="text-body small">{{ $movimiento->motivo ?? 'Sin motivo registrado' }}</strong>
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