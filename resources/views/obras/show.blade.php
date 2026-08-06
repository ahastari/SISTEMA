@extends('layouts.admin')

@section('content')
<style>
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }
    .info-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color) !important;
        border-radius: 16px;
    }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-building text-primary me-2"></i>Detalle de la Obra
        </h2>
        <p class="text-body-secondary small mb-0">Información de ubicación, contacto y rentas asociadas a este proyecto.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('obras.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Regresar
        </a>
        <a href="{{ route('obras.edit', $obra) }}" class="btn btn-warning btn-sm rounded-3 px-3 text-dark fw-semibold">
            <i class="bi bi-pencil me-1"></i> Editar
        </a>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- DATOS DE LA OBRA -->
    <div class="col-12 col-lg-6">
        <div class="card info-card shadow-sm h-100">
            <div class="card-header bg-body-tertiary border-bottom py-2 px-3 fw-bold text-body">
                <i class="bi bi-info-circle me-2 text-primary"></i>Información General
            </div>
            <div class="card-body p-3">
                <table class="table table-borderless table-sm mb-0 text-body" style="font-size: 13px;">
                    <tbody>
                        <tr>
                            <th class="text-body-secondary py-1" style="width: 140px;">Nombre Obra:</th>
                            <td class="fw-bold py-1">{{ $obra->nombre }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Cliente:</th>
                            <td class="py-1">
                                @if($obra->cliente)
                                    <a href="{{ route('clientes.show', $obra->cliente) }}" class="text-decoration-none fw-semibold">
                                        {{ $obra->cliente->nombre_completo }}
                                    </a>
                                @else
                                    <span class="text-body-secondary">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Contacto Obra:</th>
                            <td class="py-1">{{ $obra->contacto_obra ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Teléfono Obra:</th>
                            <td class="py-1"><i class="bi bi-telephone text-primary me-1"></i>{{ $obra->telefono_obra ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <th class="text-body-secondary py-1">Estatus:</th>
                            <td class="py-1">
                                @if($obra->activa)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activa</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill">Inactiva</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- DIRECCIÓN Y UBICACIÓN -->
    <div class="col-12 col-lg-6">
        <div class="card info-card shadow-sm h-100 d-flex flex-column justify-content-between">
            <div>
                <div class="card-header bg-body-tertiary border-bottom py-2 px-3 fw-bold text-body">
                    <i class="bi bi-geo-alt me-2 text-info"></i>Dirección de Entrega
                </div>
                <div class="card-body p-3">
                    <table class="table table-borderless table-sm mb-0 text-body" style="font-size: 13px;">
                        <tbody>
                            <tr>
                                <th class="text-body-secondary py-1" style="width: 120px;">Dirección:</th>
                                <td class="py-1">{{ $obra->direccion }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Colonia:</th>
                                <td class="py-1">{{ $obra->colonia ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Ciudad:</th>
                                <td class="py-1">{{ $obra->ciudad ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">Estado:</th>
                                <td class="py-1">{{ $obra->estado ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th class="text-body-secondary py-1">C.P.:</th>
                                <td class="py-1">{{ $obra->codigo_postal ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            @if($obra->observaciones)
            <div class="p-3 border-top">
                <div class="fw-bold small text-body mb-1"><i class="bi bi-chat-left-text me-1 text-secondary"></i> Observaciones:</div>
                <p class="small text-body-secondary mb-0">{{ $obra->observaciones }}</p>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- RENTAS ASOCIADAS A ESTA OBRA -->
<div class="card info-card shadow-sm mb-4">
    <div class="card-header bg-body-tertiary border-bottom py-2 px-3 fw-bold text-body">
        <i class="bi bi-file-text me-2 text-primary"></i>Rentas Asociadas a esta Obra
    </div>
    <div class="card-body p-0">
        @if($obra->rentas && $obra->rentas->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-body" style="font-size: 13px;">
                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                        <tr>
                            <th class="ps-3 py-2">Folio</th>
                            <th class="py-2">Fecha Inicio</th>
                            <th class="py-2">Fecha Fin</th>
                            <th class="py-2">Total</th>
                            <th class="py-2">Estado</th>
                            <th class="text-center pe-3 py-2" style="width: 100px;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($obra->rentas as $renta)
                        <tr class="border-bottom">
                            <td class="ps-3 fw-bold text-primary py-2">{{ $renta->folio }}</td>
                            <td class="py-2">{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                            <td class="py-2">{{ $renta->fecha_fin->format('d/m/Y') }}</td>
                            <td class="py-2 fw-bold text-success">${{ number_format($renta->total, 2) }}</td>
                            <td class="py-2">
                                @if($renta->estado == 'activa')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activa</span>
                                @else
                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill">Finalizada</span>
                                @endif
                            </td>
                            <td class="text-center pe-3 py-2">
                                <a href="{{ route('rentas.show', $renta) }}" class="btn btn-sm btn-outline-primary rounded-3 px-2" title="Ver Renta">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4 text-body-secondary">
                <i class="bi bi-file-text fs-2 d-block mb-1 text-body-tertiary"></i>
                <small>No hay rentas asociadas a esta obra.</small>
            </div>
        @endif
    </div>
</div>
@endsection