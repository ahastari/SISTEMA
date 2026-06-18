@extends('layouts.admin')

@section('content')
<style>
    .info-card {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        border-left: 4px solid #0d6efd;
    }
    .info-card h6 {
        color: #0d6efd;
        font-weight: bold;
        margin-bottom: 15px;
    }
    .info-card .label {
        color: #6c757d;
        font-size: 12px;
        text-transform: uppercase;
        font-weight: bold;
    }
    .info-card .value {
        font-weight: 500;
        font-size: 15px;
    }
    .badge-renta-activa {
        background: #d4edda;
        color: #155724;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .badge-renta-finalizada {
        background: #cfe2ff;
        color: #084298;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .stats-card {
        background: white;
        border-radius: 12px;
        padding: 15px 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        text-align: center;
        border: 1px solid #e9ecef;
    }
    .stats-card h3 {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 2px;
    }
    .stats-card p {
        color: #6c757d;
        font-size: 13px;
        margin-bottom: 0;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-person-circle me-2"></i>Detalle del Cliente
    </h2>
    <div>
        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <!-- <a href="{{ route('rentas.create') }}?cliente={{ $cliente->id }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nueva Renta
        </a> -->
    </div>
</div>

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card">
            <h3 class="text-primary">{{ $cliente->rentas->count() }}</h3>
            <p>Total de Rentas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 class="text-success">{{ $rentasActivas->count() }}</h3>
            <p>Rentas Activas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 class="text-info">{{ $rentasFinalizadas->count() }}</h3>
            <p>Rentas Finalizadas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <h3 class="text-warning">{{ $cliente->obras->count() }}</h3>
            <p>Obras/Proyectos</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- Datos del Cliente -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-person"></i> Datos del Cliente</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="120">Nombre:</th>
                        <td><strong>{{ $cliente->nombre_completo }}</strong></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $cliente->telefono }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono alt.:</th>
                        <td>{{ $cliente->telefono_alternativo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $cliente->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>RFC:</th>
                        <td>{{ $cliente->rfc ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>CURP:</th>
                        <td>{{ $cliente->curp ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>INE:</th>
                        <td>
                            @if($cliente->ine_numero || $cliente->ine_documento)
                                @if($cliente->ine_numero)
                                    <span class="badge bg-primary">{{ $cliente->ine_numero }}</span>
                                @endif
                                @if($cliente->ine_documento)
                                    <a href="{{ Storage::url($cliente->ine_documento) }}" target="_blank" class="btn btn-sm btn-info ms-2">
                                        <i class="bi bi-file-earmark-pdf"></i> Ver INE
                                    </a>
                                @endif
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Empresa:</th>
                        <td>{{ $cliente->empresa ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Dirección -->
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Dirección</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="120">Dirección:</th>
                        <td>{{ $cliente->direccion ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ciudad:</th>
                        <td>{{ $cliente->ciudad ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>{{ $cliente->estado ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Código Postal:</th>
                        <td>{{ $cliente->codigo_postal ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Observaciones -->
        @if($cliente->observaciones)
        <div class="card mt-3">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0"><i class="bi bi-chat"></i> Observaciones</h5>
            </div>
            <div class="card-body">
                <p class="mb-0">{{ $cliente->observaciones }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- OBRAS / PROYECTOS -->
<div class="card mt-4">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-building"></i> Obras / Proyectos del Cliente</h5>
        <!-- <a href="{{ route('obras.create') }}?cliente={{ $cliente->id }}" class="btn btn-sm btn-light">
            <i class="bi bi-plus-circle"></i> Agregar Obra
        </a> -->
    </div>
    <div class="card-body">
        @if($cliente->obras->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Nombre de la obra</th>
                            <th>Dirección</th>
                            <th>Ciudad</th>
                            <th>Contacto</th>
                            <th>Estado</th>
                            <th>Rentas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cliente->obras as $obra)
                        <tr>
                            <td><strong>{{ $obra->nombre }}</strong></td>
                            <td>{{ $obra->direccion }}</td>
                            <td>{{ $obra->ciudad ?? 'N/A' }}</td>
                            <td>{{ $obra->contacto_obra ?? 'N/A' }}</td>
                            <td>
                                @if($obra->activa)
                                    <span class="badge bg-success">Activa</span>
                                @else
                                    <span class="badge bg-danger">Inactiva</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary">{{ $obra->rentas->count() }}</span>
                            </td>
                            <td>
                                <a href="{{ route('obras.show', $obra) }}" class="btn btn-info btn-sm">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-3">
                <i class="bi bi-building" style="font-size: 40px; color: #ccc;"></i>
                <p class="mt-2 text-muted">Este cliente no tiene obras registradas</p>
                <a href="{{ route('obras.create') }}?cliente={{ $cliente->id }}" class="btn btn-success btn-sm">
                    <i class="bi bi-plus-circle"></i> Registrar obra
                </a>
            </div>
        @endif
    </div>
</div>

<!-- RENTAS ACTIVAS -->
<div class="card mt-4">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-check-circle"></i> Rentas Activas</h5>
    </div>
    <div class="card-body">
        @if($rentasActivas->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Equipos</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Días</th>
                            <th>Total</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentasActivas as $renta)
                        <tr>
                            <td><strong>{{ $renta->folio }}</strong></td>
                            <td>
                                @foreach($renta->detalles as $detalle)
                                    <span class="badge bg-secondary">{{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}</span>
                                @endforeach
                            </td>
                            <td>{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $renta->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ $renta->dias_totales }}</td>
                            <td>${{ number_format($renta->total, 2) }}</td>
                            <td>
                                <a href="{{ route('rentas.show', $renta) }}" class="btn btn-info btn-sm">Ver</a>
                                <a href="{{ route('rentas.finalizar', $renta) }}" class="btn btn-success btn-sm" onclick="return confirm('¿Finalizar esta renta?')">Finalizar</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-3">
                <i class="bi bi-check-circle" style="font-size: 40px; color: #ccc;"></i>
                <p class="mt-2 text-muted">No hay rentas activas para este cliente</p>
            </div>
        @endif
    </div>
</div>

<!-- RENTAS FINALIZADAS -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Historial de Rentas</h5>
    </div>
    <div class="card-body">
        @if($rentasFinalizadas->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Folio</th>
                            <th>Equipos</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Días</th>
                            <th>Total</th>
                            <th>Devolución</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rentasFinalizadas as $renta)
                        <tr>
                            <td><strong>{{ $renta->folio }}</strong></td>
                            <td>
                                @foreach($renta->detalles as $detalle)
                                    <span class="badge bg-secondary">{{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}</span>
                                @endforeach
                            </td>
                            <td>{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                            <td>{{ $renta->fecha_fin->format('d/m/Y') }}</td>
                            <td>{{ $renta->dias_totales }}</td>
                            <td>${{ number_format($renta->total, 2) }}</td>
                            <td>{{ $renta->fecha_devolucion ? \Carbon\Carbon::parse($renta->fecha_devolucion)->format('d/m/Y') : 'N/A' }}</td>
                            <td>
                                <a href="{{ route('rentas.show', $renta) }}" class="btn btn-info btn-sm">Ver</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-3">
                <i class="bi bi-clock-history" style="font-size: 40px; color: #ccc;"></i>
                <p class="mt-2 text-muted">No hay rentas finalizadas para este cliente</p>
            </div>
        @endif
    </div>
</div>
@endsection