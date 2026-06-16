@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="bi bi-building"></i> Detalle de la Obra
    </h2>
    <div>
        <a href="{{ route('obras.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('obras.edit', $obra) }}" class="btn btn-warning">
            <i class="bi bi-pencil"></i> Editar
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Nombre:</th>
                        <td>{{ $obra->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Cliente:</th>
                        <td>{{ $obra->cliente->nombre_completo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td>{{ $obra->direccion }}</td>
                    </tr>
                    <tr>
                        <th>Colonia:</th>
                        <td>{{ $obra->colonia ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ciudad:</th>
                        <td>{{ $obra->ciudad ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Estado:</th>
                        <td>{{ $obra->estado ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Código Postal:</th>
                        <td>{{ $obra->codigo_postal ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono obra:</th>
                        <td>{{ $obra->telefono_obra ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Contacto obra:</th>
                        <td>{{ $obra->contacto_obra ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Estado obra:</th>
                        <td>
                            @if($obra->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-danger">Inactiva</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @if($obra->observaciones)
        <div class="row">
            <div class="col-md-12">
                <hr>
                <h6>Observaciones:</h6>
                <p>{{ $obra->observaciones }}</p>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Rentas asociadas a esta obra -->
<!-- Rentas asociadas a esta obra -->
<div class="card mt-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-file-text"></i> Rentas asociadas a esta obra</h5>
    </div>
    <div class="card-body">
        @if($obra->rentas && $obra->rentas->count() > 0)
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Folio</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th>Total</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obra->rentas as $renta)
                    <tr>
                        <td>{{ $renta->folio }}</td>
                        <td>{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ $renta->fecha_fin->format('d/m/Y') }}</td>
                        <td>${{ number_format($renta->total, 2) }}</td>
                        <td>
                            @if($renta->estado == 'activa')
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-info">Finalizada</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-muted text-center">No hay rentas asociadas a esta obra</p>
        @endif
    </div>
</div>
@endsection