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
            <button class="btn btn-info btn-sm rounded-3 text-white" data-bs-toggle="modal" data-bs-target="#modalPago">
                <i class="bi bi-cash-coin me-1"></i> Registrar Pago
            </button>
            <!-- 🔥 Botón siempre activo -->
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

            <!-- 🔒 Alerta visual de multa (fuera del modal) -->
                @if($diasRetraso > 0)
                    <div class="alert alert-danger mt-3 mb-0 py-2 small" style="border-left: 4px solid #dc3545;">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                        <strong>¡Contrato Vencido!</strong><br>
                        {{ $diasRetraso }} día(s) de retraso. 
                        @if($multaCalculada > 0)
                            Multa generada: <strong>${{ number_format($multaCalculada, 2) }}</strong>
                        @endif
                    </div>
                @endif
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

    <!-- Equipos Rentados -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-box-seam text-primary me-2"></i>EQUIPO RENTADO</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 table-renta" style="font-size: 13px;">
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

<!-- MODAL: Ampliar Días -->
<div class="modal fade" id="modalAmpliar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Ampliar Días de Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST" id="formAmpliar">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Inputs -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">Días adicionales <span class="text-danger">*</span></label>
                                <input type="number" name="dias_extra" id="dias_extra" class="form-control form-control-sm bg-body" min="1" required oninput="calcularAmpliacion()" placeholder="Ej: 3">
                                <small class="text-secondary" style="font-size: 11px;">Se recalculará el total automáticamente</small>
                            </div>
                            <div class="mb-3 form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="facturar" id="facturar" value="1" onchange="calcularAmpliacion()">
                                <label class="form-check-label small fw-semibold text-body" for="facturar">Aplicar Factura (IVA 16%)</label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">Abono a cuenta</label>
                                <input type="number" name="abono" class="form-control form-control-sm bg-body" step="0.01" placeholder="0.00">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">Motivo de la ampliación</label>
                                <textarea name="motivo" class="form-control form-control-sm bg-body" rows="2" placeholder="Ej: Cliente solicita extensión por lluvias"></textarea>
                            </div>
                        </div>
                        
                        <!-- Desglose tipo POS -->
                        <div class="col-md-6">
                            <div class="card bg-body-tertiary border p-3 h-100">
                                <h6 class="text-primary border-bottom pb-2 fw-bold">Resumen de Ampliación</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-body small">Costo Extra:</span> 
                                    <strong id="res_costo" class="text-body">$0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-body small">IVA:</span> 
                                    <strong id="res_iva_ext" class="text-body">$0.00</strong>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between fs-6 fw-bold">
                                    <span>Total a Sumar:</span> 
                                    <strong id="res_total_ext" class="text-primary">$0.00</strong>
                                </div>
                                <div class="alert alert-info py-2 px-3 small mt-3 mb-0">
                                    <i class="bi bi-info-circle me-1"></i> 
                                    <strong>Nueva fecha tentativa:</strong> 
                                    <span id="nueva_fecha">{{ $renta->fecha_fin->addDays(1)->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">
                        <i class="bi bi-check-lg me-1"></i> Procesar Ampliación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Registro de Pago -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-info text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-cash-coin me-1"></i> Registrar Nuevo Pago</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.registrarPago', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">
                            Monto a pagar 
                            <small class="text-secondary">(Saldo pendiente: ${{ number_format($renta->saldo_pendiente, 2) }})</small>
                        </label>
                        <input type="number" name="monto" class="form-control form-control-sm bg-body" step="0.01" max="{{ $renta->saldo_pendiente }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Método de Pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago" class="form-select form-select-sm bg-body" required>
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Referencia (opcional)</label>
                        <input type="text" name="referencia" class="form-control form-control-sm bg-body" placeholder="Folio de transferencia o boucher">
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-info text-white fw-bold">Confirmar Pago</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Finalizar Renta con Pago -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-check-circle me-1"></i> Finalizar Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.finalizarConPago', $renta) }}" method="POST" id="formFinalizar">
                @csrf
                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <div class="alert alert-info py-2 px-3 small h-100 mb-0">
                                <strong class="d-block mb-1">Cálculo de Cuenta Inicial:</strong>
                                Total original: ${{ number_format($renta->total, 2) }}<br>
                                - Depósito: ${{ number_format($renta->deposito ?? 0, 2) }}<br>
                                - Pagado: ${{ number_format($renta->pagos->sum('monto'), 2) }}<br>
                                <hr class="my-1">
                                <strong class="text-danger fs-6">Saldo Base a Pagar: $<span id="saldo_base_txt">{{ number_format($renta->saldo_pendiente, 2) }}</span></strong>
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

                    <!-- 🔥 SECCIÓN 1: Multa por retraso (Solo aparece si hay retraso) -->
                    @if($diasRetraso > 0)
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning bg-opacity-25 text-dark py-2 fw-bold" style="font-size: 14px;">
                                <i class="bi bi-clock-history me-1"></i> Multa por Retraso Generada ({{ $diasRetraso }} días)
                            </div>
                            <div class="card-body py-2">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white text-danger fw-bold">$</span>
                                            <input type="number" name="multa_retraso" id="multa_retraso" class="form-control text-danger fw-bold" step="0.01" min="0" value="{{ $multaCalculada }}" oninput="recalcularFinalizacion()" {{ !$puedeEditarMulta ? 'readonly' : '' }}>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <input type="text" name="motivo_multa" id="motivo_multa" class="form-control form-control-sm" value="{{ $motivoMulta }}" {{ !$puedeEditarMulta ? 'readonly' : '' }}>
                                    </div>
                                </div>
                                @if(!$puedeEditarMulta)
                                    <small class="text-danger mt-1 d-block" style="font-size:11px;"><i class="bi bi-lock-fill"></i> Solo el Gerente/Admin puede condonar la multa.</small>
                                @endif
                            </div>
                        </div>
                    @else
                        <!-- Si no hay retraso, enviamos los valores en cero ocultos -->
                        <input type="hidden" name="multa_retraso" id="multa_retraso" value="0">
                        <input type="hidden" name="motivo_multa" id="motivo_multa" value="">
                    @endif

                    <!-- 🔥 SECCIÓN 2: Checkbox opcional para cargos manuales (Daños/Perdidas) -->
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="check_cargo_manual" onchange="toggleCargoManual()">
                        <label class="form-check-label fw-bold text-danger small" for="check_cargo_manual">
                            ¿Generar otro cargo extra? (Daños, limpieza, piezas perdidas)
                        </label>
                    </div>

                    <div class="card border-danger mb-3 d-none" id="seccion_cargo_manual">
                        <div class="card-body py-2 bg-danger bg-opacity-10">
                            <div class="row g-2">
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-danger mb-1">Monto del cargo ($)</label>
                                    <input type="number" name="cargo_manual" id="cargo_manual" class="form-control form-control-sm border-danger" step="0.01" min="0" value="0" oninput="recalcularFinalizacion()">
                                </div>
                                <div class="col-12 col-md-8">
                                    <label class="form-label small fw-semibold text-danger mb-1">Motivo (Obligatorio)</label>
                                    <input type="text" name="motivo_cargo_manual" id="motivo_cargo_manual" class="form-control form-control-sm border-danger" placeholder="Ej: Llanta averiada">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-body fs-6 border-bottom pb-2">Registrar Pago Final</h6>
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Monto a cobrar <span class="text-danger">*</span></label>
                            <input type="number" name="monto_pago" id="montoPagoFinal" class="form-control form-control-sm bg-body fw-bold text-primary" step="0.01" 
                                   value="{{ $renta->saldo_pendiente > 0 ? $renta->saldo_pendiente : 0 }}" required>
                            <small class="text-secondary d-block" id="montoMaximoLabel" style="font-size: 11px;">Máximo a cobrar: ${{ number_format($renta->saldo_pendiente, 2) }}</small>
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
                    <button type="submit" class="btn btn-sm btn-success fw-bold" onclick="return validarCargo()">
                        <i class="bi bi-check-lg me-1"></i> Finalizar Renta
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const saldoBaseOriginal = parseFloat("{{ $renta->saldo_pendiente }}") || 0;
const inputMulta = document.getElementById('multa_retraso');
const checkCargoManual = document.getElementById('check_cargo_manual');
const seccionCargoManual = document.getElementById('seccion_cargo_manual');
const inputCargoManual = document.getElementById('cargo_manual');
const motivoCargoManual = document.getElementById('motivo_cargo_manual');
const inputMontoFinal = document.getElementById('montoPagoFinal');
const labelMaximo = document.getElementById('montoMaximoLabel');

// Muestra u oculta la caja de cargos extra manuales
function toggleCargoManual() {
    if (checkCargoManual.checked) {
        seccionCargoManual.classList.remove('d-none');
    } else {
        seccionCargoManual.classList.add('d-none');
        inputCargoManual.value = 0;
        motivoCargoManual.value = '';
        recalcularFinalizacion();
    }
}

// Recalcula el total de la deuda (Saldo Base + Multa + Daños)
function recalcularFinalizacion() {
    let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
    let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
    let nuevoSaldoTotal = saldoBaseOriginal + multa + manual;
    
    if (inputMontoFinal) {
        inputMontoFinal.max = nuevoSaldoTotal;
        inputMontoFinal.value = nuevoSaldoTotal.toFixed(2);
    }
    if (labelMaximo) {
        labelMaximo.textContent = 'Máximo a cobrar: $' + nuevoSaldoTotal.toFixed(2);
    }
}

// Validación final al presionar el botón verde
function validarCargo() {
    let manual = parseFloat(inputCargoManual ? inputCargoManual.value : 0) || 0;
    let motivo = motivoCargoManual ? motivoCargoManual.value.trim() : '';
    
    // Si prendió el switch y puso cantidad, el motivo es obligatorio
    if (checkCargoManual && checkCargoManual.checked && manual > 0 && motivo === '') {
        alert('Ha agregado un cargo extra manual de $' + manual + '. Por favor, especifique el motivo (ej: Llanta dañada).');
        motivoCargoManual.focus();
        return false;
    }
    
    // Validar que no cobre más de la deuda real
    let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
    let deudaReal = saldoBaseOriginal + multa + manual;
    let cobro = parseFloat(inputMontoFinal.value) || 0;
    
    if (cobro > deudaReal) {
        alert('El monto ingresado ($' + cobro.toFixed(2) + ') excede el saldo pendiente total ($' + deudaReal.toFixed(2) + ')');
        inputMontoFinal.value = deudaReal.toFixed(2);
        return false;
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    // Escuchar si el usuario cambia el monto a cobrar a mano
    if (inputMontoFinal) {
        inputMontoFinal.addEventListener('change', function() {
            let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
            let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
            let deudaReal = saldoBaseOriginal + multa + manual;
            let cobro = parseFloat(this.value) || 0;
            
            if (cobro > deudaReal) {
                alert('El monto ingresado ($' + cobro.toFixed(2) + ') excede el saldo pendiente total ($' + deudaReal.toFixed(2) + ')');
                this.value = deudaReal.toFixed(2);
            }
        });
    }

    // Inicializar cálculo si el sistema detectó multa automática
    if (inputMulta && parseFloat(inputMulta.value) > 0) {
        recalcularFinalizacion();
    }
});
</script>
@endsection