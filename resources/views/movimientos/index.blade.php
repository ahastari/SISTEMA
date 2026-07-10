@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">Historial de Movimientos</h3>
        <p class="text-secondary small mb-0">Registro y auditoría de transferencias, entradas, salidas y ajustes de stock</p>
    </div>
    <div class="d-flex gap-2 w-100 w-md-auto justify-content-start justify-content-md-end">
        <a href="{{ route('movimientos.create') }}" class="btn btn-primary btn-sm rounded-3 shadow-sm fw-semibold">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Movimiento
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

<div class="card border-0 shadow-sm mb-3 rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-2">
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Tipo</label>
                <select name="tipo" class="form-select form-select-sm bg-body text-body">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                </select>
            </div>
            
            <div class="col-6 col-md-3 col-lg-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm bg-body text-body">
                    <option value="">Todos</option>
                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            
            <div class="col-12 col-md-3 col-lg-4">
                <label class="form-label small fw-semibold text-body mb-1">Producto</label>
                <select name="equipo_id" class="form-select form-select-sm bg-body text-body">
                    <option value="">Todos los productos...</option>
                    @foreach($equipos as $equipo)
                        <option value="{{ $equipo->id }}" {{ request('equipo_id') == $equipo->id ? 'selected' : '' }}>
                            {{ $equipo->codigo }} - {{ $equipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-6 col-md-1.5 col-lg-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Desde</label>
                <input type="date" name="fecha_desde" class="form-control form-control-sm bg-body text-body" value="{{ request('fecha_desde') }}">
            </div>
            
            <div class="col-6 col-md-1.5 col-lg-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control form-control-sm bg-body text-body" value="{{ request('fecha_hasta') }}">
            </div>
            
            <div class="col-12 d-flex gap-2 mt-2 pt-1">
                <button type="submit" class="btn btn-primary btn-sm px-3 rounded-3">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm px-3 rounded-3">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-body-tertiary text-body border-bottom">
                    <tr>
                        <th class="py-2 px-3">ID</th>
                        <th class="py-2">Fecha</th>
                        <th class="py-2">Producto</th>
                        <th class="py-2">Origen</th>
                        <th class="py-2">Destino</th>
                        <th class="py-2">Cantidad</th>
                        <th class="py-2">Tipo</th>
                        <th class="py-2">Estado</th>
                        <th class="py-2">Usuario</th>
                        <th class="text-center py-2 px-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
                    <tr>
                        <td class="px-3">
                            <span class="badge bg-secondary font-monospace">#{{ $movimiento->id }}</span>
                        </td>
                        <td>
                            <small class="text-secondary d-block">
                                {{ $movimiento->fecha_movimiento->format('d/m/Y') }}
                            </small>
                            <small class="text-muted" style="font-size: 11px;">
                                {{ $movimiento->fecha_movimiento->format('H:i') }} hrs
                            </small>
                        </td>
                        <td>
                            <span class="fw-semibold text-body d-block text-truncate" style="max-width: 220px;">{{ $movimiento->equipo->nombre }}</span>
                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 10px;">{{ $movimiento->equipo->codigo }}</span>
                        </td>
                        <td>
                            <span class="badge bg-body border text-body font-weight-normal">
                                {{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-body border text-body font-weight-normal">
                                {{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-body fs-6">{{ $movimiento->cantidad }}</span>
                        </td>
                        <td>
                            @php
                                $badgeClass = [
                                    'entrada' => 'bg-success',
                                    'salida' => 'bg-danger',
                                    'transferencia' => 'bg-info',
                                    'ajuste' => 'bg-warning text-dark'
                                ][$movimiento->tipo] ?? 'bg-secondary';
                                
                                $icon = [
                                    'entrada' => 'bi-arrow-down-circle',
                                    'salida' => 'bi-arrow-up-circle',
                                    'transferencia' => 'bi-arrow-left-right',
                                    'ajuste' => 'bi-sliders'
                                ][$movimiento->tipo] ?? 'bi-circle';
                            @endphp
                            <span class="badge {{ $badgeClass }} rounded-pill px-2">
                                <i class="bi {{ $icon }} me-1"></i>
                                {{ ucfirst($movimiento->tipo) }}
                            </span>
                        </td>
                        <td>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-success-subtle text-success border border-success px-2 rounded-pill">
                                    <i class="bi bi-check-circle me-1"></i> Completado
                                </span>
                            @elseif($movimiento->estado == 'pendiente')
                                <span class="badge bg-warning-subtle text-warning border border-warning px-2 rounded-pill">
                                    <i class="bi bi-clock me-1"></i> Pendiente
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger px-2 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> Cancelado
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="text-secondary small">{{ $movimiento->usuario->name ?? 'N/A' }}</span>
                        </td>
                        <td class="text-center px-3">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('movimientos.show', $movimiento) }}" 
                                   class="btn btn-sm btn-outline-primary border-0 p-1" 
                                   title="Ver Detalles">
                                    <i class="bi bi-eye fs-6"></i>
                                </a>
                                
                                @if($movimiento->estado == 'completado')
                                    <a href="{{ route('movimientos.cancelar', $movimiento) }}" 
                                       class="btn btn-sm btn-outline-danger border-0 p-1" 
                                       title="Cancelar Movimiento"
                                       onclick="return confirm('¿Estás seguro de cancelar este movimiento? Se revertirá el stock.')">
                                        <i class="bi bi-x-lg fs-6"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            No hay movimientos registrados o que cumplan los filtros establecidos.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="p-3 d-flex justify-content-center">
            {{ $movimientos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection