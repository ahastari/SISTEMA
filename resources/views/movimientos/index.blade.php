@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Historial de Movimientos</h2>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('movimientos.create') }}" class="btn btn-primary shadow-sm">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Movimiento
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

<!-- Filtros -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('movimientos.index') }}" class="row g-3">
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted">Tipo</label>
                <select name="tipo" class="form-select">
                    <option value="">Todos</option>
                    <option value="entrada" {{ request('tipo') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                    <option value="salida" {{ request('tipo') == 'salida' ? 'selected' : '' }}>Salida</option>
                    <option value="transferencia" {{ request('tipo') == 'transferencia' ? 'selected' : '' }}>Transferencia</option>
                    <option value="ajuste" {{ request('tipo') == 'ajuste' ? 'selected' : '' }}>Ajuste</option>
                </select>
            </div>
            
            <div class="col-12 col-md-3">
                <label class="form-label small text-muted">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="completado" {{ request('estado') == 'completado' ? 'selected' : '' }}>Completado</option>
                    <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="cancelado" {{ request('estado') == 'cancelado' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted">Producto</label>
                <select name="equipo_id" class="form-select">
                    <option value="">Todos</option>
                    @foreach($equipos as $equipo)
                        <option value="{{ $equipo->id }}" {{ request('equipo_id') == $equipo->id ? 'selected' : '' }}>
                            {{ $equipo->codigo }} - {{ $equipo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted">Desde</label>
                <input type="date" name="fecha_desde" class="form-control" value="{{ request('fecha_desde') }}">
            </div>
            
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted">Hasta</label>
                <input type="date" name="fecha_hasta" class="form-control" value="{{ request('fecha_hasta') }}">
            </div>
            
            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search me-1"></i> Filtrar
                </button>
                <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de movimientos -->
<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Cantidad</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Usuario</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movimientos as $movimiento)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">#{{ $movimiento->id }}</span>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $movimiento->fecha_movimiento->format('d/m/Y H:i') }}
                            </small>
                        </td>
                        <td>
                            <span class="fw-medium">{{ $movimiento->equipo->nombre }}</span>
                            <br>
                            <small class="text-muted">{{ $movimiento->equipo->codigo }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $movimiento->sucursalOrigen->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                {{ $movimiento->sucursalDestino->nombre ?? 'N/A' }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold">{{ $movimiento->cantidad }}</span>
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
                            <span class="badge {{ $badgeClass }}">
                                <i class="bi {{ $icon }} me-1"></i>
                                {{ ucfirst($movimiento->tipo) }}
                            </span>
                        </td>
                        <td>
                            @if($movimiento->estado == 'completado')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i> Completado
                                </span>
                            @elseif($movimiento->estado == 'pendiente')
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i> Pendiente
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="bi bi-x-circle me-1"></i> Cancelado
                                </span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $movimiento->usuario->name ?? 'N/A' }}</small>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <a href="{{ route('movimientos.show', $movimiento) }}" 
                                   class="btn btn-sm btn-light border shadow-sm text-primary" 
                                   title="Ver Detalles">
                                    <i class="bi bi-eye"></i>
                                </a>
                                
                                @if($movimiento->estado == 'completado')
                                    <a href="{{ route('movimientos.cancelar', $movimiento) }}" 
                                       class="btn btn-sm btn-light border shadow-sm text-danger" 
                                       title="Cancelar Movimiento"
                                       onclick="return confirm('¿Estás seguro de cancelar este movimiento? Se revertirá el stock.')">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No hay movimientos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-3">
            {{ $movimientos->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection