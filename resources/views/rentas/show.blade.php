@extends('layouts.admin')

@section('content')
<style>
    .info-card {
        background: #f8f9fa;
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
    .total-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .badge-estado {
        font-size: 14px;
        padding: 8px 15px;
        border-radius: 20px;
    }
    .table-renta thead {
        background: #2a5298;
        color: white;
    }
    .clausula {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 10px 15px;
        margin-bottom: 8px;
        border-radius: 5px;
        font-size: 13px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0">Contrato de Renta</h2>
        <small class="text-muted">Folio: {{ $renta->folio }}</small>
    </div>
    <div>
        <a href="{{ route('rentas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('rentas.contrato', $renta) }}" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-pdf"></i> Ver Contrato
        </a>
        <a href="{{ route('rentas.pagare', $renta) }}" class="btn btn-warning" target="_blank">
            <i class="bi bi-file-text"></i> Ver Pagaré
        </a>
        @if($renta->estado == 'activa')
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAmpliar">
                <i class="bi bi-plus-circle"></i> Ampliar Días
            </button>
            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                <i class="bi bi-check-lg"></i> Finalizar Renta
            </button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row">
    <!-- Datos del Cliente -->
    <div class="col-md-6">
        <div class="info-card">
            <h6><i class="bi bi-person"></i> DATOS DEL CLIENTE</h6>
            <strong>Nombre:</strong> {{ $renta->cliente->nombre_completo }}<br>
            <strong>Teléfono:</strong> {{ $renta->cliente->telefono }}<br>
            <strong>Email:</strong> {{ $renta->cliente->email ?? 'No especificado' }}<br>
            <strong>RFC:</strong> {{ $renta->cliente->rfc ?? 'No especificado' }}
        </div>
    </div>

    <!-- Periodo y Monto -->
    <div class="col-md-6">
        <div class="info-card" style="border-left-color: #28a745;">
            <h6><i class="bi bi-calendar"></i> PERIODO DE RENTA</h6>
            <strong>Inicio:</strong> {{ $renta->fecha_inicio->format('d/m/Y') }}<br>
            <strong>Fin:</strong> {{ $renta->fecha_fin->format('d/m/Y') }}<br>
            <strong>Días totales:</strong> {{ $renta->dias_totales }} días<br>
            @if($renta->dias_ampliados > 0)
                <strong>Días ampliados:</strong> {{ $renta->dias_ampliados }} días<br>
                <strong>Fecha ampliación:</strong> {{ $renta->fecha_ampliacion ? \Carbon\Carbon::parse($renta->fecha_ampliacion)->format('d/m/Y') : 'N/A' }}<br>
            @endif
            <strong>Estado:</strong>
            @if($renta->estado == 'activa')
                <span class="badge bg-success">ACTIVA</span>
            @else
                <span class="badge bg-info">FINALIZADA</span>
            @endif
        </div>
    </div>

    <!-- Obra -->
    @if($renta->obra_id && $renta->obra)
    <div class="col-md-12">
        <div class="info-card" style="border-left-color: #6f42c1;">
            <h6><i class="bi bi-building"></i> OBRA / PROYECTO</h6>
            <strong>Nombre:</strong> {{ $renta->obra->nombre }}<br>
            <strong>Dirección:</strong> {{ $renta->obra->direccion }}<br>
            <strong>Contacto:</strong> {{ $renta->obra->contacto_obra ?? 'No especificado' }}
        </div>
    </div>
    @endif

    <!-- Equipos Rentados -->
    <div class="col-md-12">
        <h5><i class="bi bi-box-seam"></i> EQUIPO RENTADO</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-renta">
                <thead>
                    <tr>
                        <th>Cantidad</th>
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
                        <td>{{ $detalle->equipo->codigo }}</td>
                        <td>${{ number_format($detalle->precio_dia, 2) }}</td>
                        <td>{{ $detalle->dias }}</td>
                        <td>${{ number_format($detalle->subtotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr><td colspan="5" class="text-end fw-bold">Subtotal:</td><td>${{ number_format($renta->subtotal, 2) }}</td></tr>
                    <tr><td colspan="5" class="text-end fw-bold">IVA (16%):</td><td>${{ number_format($renta->iva, 2) }}</td></tr>
                    <tr class="fw-bold"><td colspan="5" class="text-end fs-5">TOTAL:</td><td class="fs-5 text-success">${{ number_format($renta->total, 2) }}</td></tr>
                    
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
                        <td colspan="5" class="text-end fs-5">SALDO PENDIENTE:</td>
                        <td class="fs-5 {{ $renta->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                            ${{ number_format($renta->saldo_pendiente, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Historial de Pagos -->
    <div class="col-md-12">
        <h5><i class="bi bi-credit-card"></i> HISTORIAL DE PAGOS</h5>
        @if($renta->pagos->count() > 0)
            <table class="table table-bordered table-hover">
                <thead>
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
                        <td>${{ number_format($pago->monto, 2) }}</td>
                        <td>
                            <span class="badge 
                                @if($pago->metodo_pago == 'efectivo') bg-success
                                @elseif($pago->metodo_pago == 'transferencia') bg-info
                                @else bg-warning
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
        @else
            <p class="text-muted">No hay pagos registrados</p>
        @endif
    </div>

    <!-- Observaciones -->
    @if($renta->observaciones)
    <div class="col-md-12">
        <div class="info-card" style="border-left-color: #6c757d;">
            <h6><i class="bi bi-chat"></i> OBSERVACIONES</h6>
            <p class="mb-0">{!! nl2br(e($renta->observaciones)) !!}</p>
        </div>
    </div>
    @endif
</div>

<!-- MODAL: Ampliar Días -->
<div class="modal fade" id="modalAmpliar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Ampliar Días de Renta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Días a ampliar <span class="text-danger">*</span></label>
                        <input type="number" name="dias_extra" class="form-control" min="1" required>
                        <small class="text-muted">Se recalculará el total automáticamente</small>
                    </div>
                    <div class="mb-3">
                        <label>Motivo de la ampliación</label>
                        <textarea name="motivo" class="form-control" rows="2" placeholder="Ej: Cliente solicita más tiempo"></textarea>
                    </div>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> 
                        <strong>Nueva fecha de fin:</strong> 
                        {{ $renta->fecha_fin->addDays(1)->format('d/m/Y') }}
                        <br>
                        <small>Se agregará al saldo pendiente</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Ampliar Días</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Finalizar Renta con Pago -->
<div class="modal fade" id="modalFinalizar" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Finalizar Renta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('rentas.finalizarConPago', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="alert alert-info">
                                <strong>Total de la renta:</strong> ${{ number_format($renta->total, 2) }}<br>
                                <strong>Depósito:</strong> ${{ number_format($renta->deposito ?? 0, 2) }}<br>
                                <strong>Total pagado:</strong> ${{ number_format($renta->total_pagado, 2) }}<br>
                                <strong class="text-danger" style="font-size: 18px;">Saldo pendiente:</strong> 
                                <strong class="text-danger" style="font-size: 18px;">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                            </div>
                            <div class="alert alert-info mt-2">
                                <strong>Fórmula:</strong><br>
                                Total: ${{ number_format($renta->total, 2) }}<br>
                                - Depósito: ${{ number_format($renta->deposito ?? 0, 2) }}<br>
                                - Pagado: ${{ number_format($renta->total_pagado, 2) }}<br>
                                <strong>= Saldo: ${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i>
                                <strong>Equipos a devolver:</strong>
                                @foreach($renta->detalles as $detalle)
                                    <br>{{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h6>Registrar Pago Final</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Monto a pagar <span class="text-danger">*</span></label>
                            <input type="number" name="monto_pago" id="montoPagoFinal" class="form-control" step="0.01" 
                                   value="{{ $renta->saldo_pendiente > 0 ? $renta->saldo_pendiente : 0 }}" required>
                            <small class="text-muted" id="montoMaximoLabel">Máximo: ${{ number_format($renta->saldo_pendiente, 2) }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Método de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago_final" class="form-control" required>
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label>Referencia (opcional)</label>
                            <input type="text" name="referencia_final" class="form-control" placeholder="Número de transferencia, etc.">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg"></i> Finalizar Renta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validar monto en modal de finalización
    const modalFinalizar = document.getElementById('modalFinalizar');
    const inputMonto = document.getElementById('montoPagoFinal');
    const saldoPendiente = {{ $renta->saldo_pendiente }};
    
    if (inputMonto) {
        inputMonto.max = saldoPendiente;
        inputMonto.value = saldoPendiente > 0 ? saldoPendiente : 0;
        
        // Actualizar mensaje de máximo
        const labelMax = document.getElementById('montoMaximoLabel');
        if (labelMax) {
            labelMax.textContent = 'Máximo: $' + saldoPendiente.toFixed(2);
        }
        
        // Validar al cambiar el valor
        inputMonto.addEventListener('change', function() {
            if (parseFloat(this.value) > saldoPendiente) {
                alert('El monto no puede ser mayor al saldo pendiente ($' + saldoPendiente.toFixed(2) + ')');
                this.value = saldoPendiente;
            }
        });
    }
});
</script>

<script>
// Actualizar el monto máximo en el modal de finalización
document.addEventListener('DOMContentLoaded', function() {
    const modalFinalizar = document.getElementById('modalFinalizar');
    const inputMonto = modalFinalizar.querySelector('input[name="monto_pago"]');
    const saldoPendiente = {{ $renta->saldo_pendiente }};
    
    if (inputMonto) {
        inputMonto.max = saldoPendiente;
        inputMonto.value = saldoPendiente > 0 ? saldoPendiente : 0;
    }
});
</script>
@endsection