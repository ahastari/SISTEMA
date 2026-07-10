@extends('layouts.admin')

@section('content')
<style>
    /* Estilos dinámicos para Tema Claro y Oscuro */
    .info-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-left: 4px solid #0d6efd;
        padding: 15px;
        margin-bottom: 15px;
        border-radius: 8px;
    }
    .info-card h6 {
        color: #0d6efd;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .badge-estado {
        font-size: 13px;
        padding: 6px 12px;
        border-radius: 20px;
    }
    .table-renta thead {
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
    }
    .clausula {
        background: rgba(255, 193, 7, 0.15);
        border-left: 4px solid #ffc107;
        padding: 10px 15px;
        margin-bottom: 8px;
        border-radius: 5px;
        font-size: 13px;
    }
</style>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>Contrato de Renta
        </h3>
        <small class="text-secondary">Folio: <strong class="text-body">{{ $renta->folio }}</strong></small>
    </div>
    
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('rentas.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
            <i class="bi bi-arrow-left"></i> <span class="d-none d-sm-inline ms-1">Regresar</span>
        </a>
        <a href="{{ route('rentas.contrato', $renta) }}" class="btn btn-danger btn-sm rounded-3" target="_blank">
            <i class="bi bi-file-pdf"></i> <span class="d-none d-sm-inline ms-1">Contrato PDF</span>
        </a>
        <a href="{{ route('rentas.pagare', $renta) }}" class="btn btn-warning btn-sm rounded-3 text-dark" target="_blank">
            <i class="bi bi-file-text"></i> <span class="d-none d-sm-inline ms-1">Pagaré</span>
        </a>
        @if($renta->estado == 'activa')
            <button class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalAmpliar">
                <i class="bi bi-plus-circle me-1"></i> Ampliar Días
            </button>
            <button class="btn btn-success btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                <i class="bi bi-check-lg me-1"></i> Finalizar
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-12 col-md-6">
        <div class="info-card h-100">
            <h6><i class="bi bi-person me-1"></i> DATOS DEL CLIENTE</h6>
            <div class="text-body small">
                <strong>Nombre:</strong> {{ $renta->cliente->nombre_completo }}<br>
                <strong>Teléfono:</strong> {{ $renta->cliente->telefono }}<br>
                <strong>Email:</strong> {{ $renta->cliente->email ?? 'No especificado' }}<br>
                <strong>RFC:</strong> {{ $renta->cliente->rfc ?? 'No especificado' }}
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="info-card h-100" style="border-left-color: #198754;">
            <h6 class="text-success"><i class="bi bi-calendar me-1"></i> PERIODO DE RENTA</h6>
            <div class="text-body small">
                <strong>Inicio:</strong> {{ $renta->fecha_inicio->format('d/m/Y') }}<br>
                <strong>Fin:</strong> {{ $renta->fecha_fin->format('d/m/Y') }}<br>
                <strong>Días totales:</strong> {{ $renta->dias_totales }} días<br>
                @if($renta->dias_ampliados > 0)
                    <strong>Días ampliados:</strong> {{ $renta->dias_ampliados }} días<br>
                    <strong>Fecha ampliación:</strong> {{ $renta->fecha_ampliacion ? \Carbon\Carbon::parse($renta->fecha_ampliacion)->format('d/m/Y') : 'N/A' }}<br>
                @endif
                <strong>Estado:</strong>
                @if($renta->estado == 'activa')
                    <span class="badge bg-success rounded-pill px-2">ACTIVA</span>
                @else
                    <span class="badge bg-info rounded-pill px-2">FINALIZADA</span>
                @endif
            </div>
        </div>
    </div>

    @if($renta->obra_id && $renta->obra)
    <div class="col-12">
        <div class="info-card" style="border-left-color: #6f42c1;">
            <h6 style="color: #6f42c1;"><i class="bi bi-building me-1"></i> OBRA / PROYECTO</h6>
            <div class="text-body small">
                <strong>Nombre:</strong> {{ $renta->obra->nombre }}<br>
                <strong>Dirección:</strong> {{ $renta->obra->direccion }}<br>
                <strong>Contacto:</strong> {{ $renta->obra->contacto_obra ?? 'No especificado' }}
            </div>
        </div>
    </div>
    @endif

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-box-seam text-primary me-2"></i>EQUIPO RENTADO</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Cant.</th>
                            <th>Equipo</th>
                            <th>Código</th>
                            <th>Precio/día</th>
                            <th>Días</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renta->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>{{ $detalle->equipo->nombre }}</td>
                            <td><code>{{ $detalle->equipo->codigo }}</code></td>
                            <td>${{ number_format($detalle->precio_dia, 2) }}</td>
                            <td>{{ $detalle->dias }}</td>
                            <td class="fw-semibold">${{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Subtotal:</td>
                            <td class="fw-semibold">${{ number_format($renta->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">IVA (16%):</td>
                            <td class="fw-semibold">${{ number_format($renta->iva, 2) }}</td>
                        </tr>
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end fs-6">TOTAL:</td>
                            <td class="fs-6 text-success">${{ number_format($renta->total, 2) }}</td>
                        </tr>
                        
                        @if($renta->deposito > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Depósito:</td>
                            <td class="text-primary">-${{ number_format($renta->deposito, 2) }}</td>
                        </tr>
                        @endif
                        
                        @if($renta->total_pagado > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total pagado:</td>
                            <td class="text-info">-${{ number_format($renta->total_pagado, 2) }}</td>
                        </tr>
                        @endif
                        
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end fs-6">SALDO PENDIENTE:</td>
                            <td class="fs-6 {{ $renta->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                ${{ number_format($renta->saldo_pendiente, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-credit-card text-success me-2"></i>HISTORIAL DE PAGOS</h5>
            @if($renta->pagos->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Monto</th>
                                <th>Método</th>
                                <th>Referencia</th>
                                <th>Observaciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($renta->pagos as $pago)
                            <tr>
                                <td>{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                                <td class="fw-bold">${{ number_format($pago->monto, 2) }}</td>
                                <td>
                                    <span class="badge 
                                        @if($pago->metodo_pago == 'efectivo') bg-success
                                        @elseif($pago->metodo_pago == 'transferencia') bg-info
                                        @else bg-warning text-dark
                                        @endif">
                                        {{ ucfirst($pago->metodo_pago) }}
                                    </span>
                                </td>
                                <td>{{ $pago->referencia ?? 'N/A' }}</td>
                                <td>{{ $pago->observaciones ?? '' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-secondary small mb-0">No hay pagos registrados para este contrato.</p>
            @endif
        </div>
    </div>

    @if($renta->observaciones)
    <div class="col-12">
        <div class="info-card" style="border-left-color: #6c757d;">
            <h6 class="text-secondary"><i class="bi bi-chat me-1"></i> OBSERVACIONES</h6>
            <p class="mb-0 text-body small">{!! nl2br(e($renta->observaciones)) !!}</p>
        </div>
    </div>
    @endif
</div>

<div class="modal fade" id="modalAmpliar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Ampliar Días de Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Días a ampliar <span class="text-danger">*</span></label>
                        <input type="number" name="dias_extra" class="form-control form-control-sm bg-body" min="1" required placeholder="Ej: 3">
                        <small class="text-secondary" style="font-size: 11px;">Se recalculará el total automáticamente</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Motivo de la ampliación</label>
                        <textarea name="motivo" class="form-control form-control-sm bg-body" rows="2" placeholder="Ej: Cliente solicita extensión por lluvias"></textarea>
                    </div>
                    <div class="alert alert-info py-2 px-3 small mb-0">
                        <i class="bi bi-info-circle me-1"></i> 
                        <strong>Nueva fecha tentativa:</strong> 
                        {{ $renta->fecha_fin->addDays(1)->format('d/m/Y') }}
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Ampliar Días</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-check-circle me-1"></i> Finalizar Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.finalizarConPago', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="alert alert-info py-2 px-3 small h-100 mb-0">
                                <strong class="d-block mb-1">Cálculo de Cuenta:</strong>
                                Total: ${{ number_format($renta->total, 2) }}<br>
                                - Depósito: ${{ number_format($renta->deposito ?? 0, 2) }}<br>
                                - Pagado: ${{ number_format($renta->total_pagado, 2) }}<br>
                                <hr class="my-1">
                                <strong class="text-danger fs-6">Saldo Pendiente: ${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="alert alert-warning py-2 px-3 small h-100 mb-0">
                                <strong class="d-block mb-1"><i class="bi bi-box-arrow-in-down me-1"></i> Equipos a devolver:</strong>
                                @foreach($renta->detalles as $detalle)
                                    <div>• {{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}</div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-body fs-6 border-bottom pb-2">Registrar Pago Final</h6>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Monto a pagar <span class="text-danger">*</span></label>
                            <input type="number" name="monto_pago" id="montoPagoFinal" class="form-control form-control-sm bg-body" step="0.01" 
                                   value="{{ $renta->saldo_pendiente > 0 ? $renta->saldo_pendiente : 0 }}" required>
                            <small class="text-secondary d-block" id="montoMaximoLabel" style="font-size: 11px;">Máximo: ${{ number_format($renta->saldo_pendiente, 2) }}</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Método de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago_final" class="form-select form-select-sm bg-body" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body">Referencia (opcional)</label>
                            <input type="text" name="referencia_final" class="form-control form-control-sm bg-body" placeholder="Folio de transferencia o boucher">
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Finalizar Renta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validaciones para el modal de finalización
    const inputMonto = document.getElementById('montoPagoFinal');
    const saldoPendiente = parseFloat("{{ $renta->saldo_pendiente }}") || 0;
    
    if (inputMonto) {
        inputMonto.max = saldoPendiente;
        inputMonto.value = saldoPendiente > 0 ? saldoPendiente : 0;
        
        const labelMax = document.getElementById('montoMaximoLabel');
        if (labelMax) {
            labelMax.textContent = 'Máximo: $' + saldoPendiente.toFixed(2);
        }
        
        inputMonto.addEventListener('change', function() {
            const val = parseFloat(this.value) || 0;
            if (val > saldoPendiente) {
                alert('El monto ingresado ($' + val.toFixed(2) + ') excede el saldo pendiente ($' + saldoPendiente.toFixed(2) + ')');
                this.value = saldoPendiente;
            }
        });
    }
});
</script>
@endsection