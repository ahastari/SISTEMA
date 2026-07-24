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
    .table-renta thead th {
        background-color: var(--bs-tertiary-bg) !important;
        color: var(--bs-body-color) !important;
        border-bottom: 1px solid var(--bs-border-color) !important;
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

    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-box-seam text-primary me-2"></i>EQUIPO RENTADO</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0 table-renta" style="font-size: 13px;">
                    <thead>
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
                        <!-- 1. Cargos Base -->
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-secondary">Subtotal:</td>
                            <td class="fw-semibold text-secondary">${{ number_format($renta->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-secondary">IVA (16%):</td>
                            <td class="fw-semibold text-secondary">${{ number_format($renta->iva, 2) }}</td>
                        </tr>
                        
                        <!-- 2. Cargos Extra (En caso de que tenga multas/daños) -->
                        @if(isset($renta->cargos_extra) && $renta->cargos_extra > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-danger">Cargos Extra (Multas/Daños):</td>
                            <td class="fw-semibold text-danger">+${{ number_format($renta->cargos_extra, 2) }}</td>
                        </tr>
                        @endif

                        <!-- 3. Total Real del Contrato (CORREGIDO PARA MODO OSCURO) -->
                        <tr class="fw-bold bg-body-tertiary">
                            <td colspan="5" class="text-end fs-6 text-body">TOTAL DEL CONTRATO:</td>
                            <td class="fs-6 text-success">${{ number_format($renta->total, 2) }}</td>
                        </tr>
                        
                        <!-- 4. Depósito (Abono inicial) -->
                        @if($renta->deposito > 0)
                        <tr>
                            <td colspan="5" class="text-end fw-bold text-secondary">Depósito en Garantía:</td>
                            <td class="text-primary fw-bold">-${{ number_format($renta->deposito, 2) }}</td>
                        </tr>
                        @endif
                        
                        <!-- 5. Desglose de cada pago registrado -->
                        @if($renta->pagos->count() > 0)
                            <tr>
                                <td colspan="6" class="p-0 border-0"></td>
                            </tr>
                            
                            @foreach($renta->pagos as $pago)
                            <tr>
                                <td colspan="5" class="text-end fw-bold text-secondary">
                                    <i class="bi bi-arrow-down-right text-success"></i> Pago ({{ $pago->fecha_pago->format('d/m/Y') }}):
                                </td>
                                <td class="text-info fw-bold">-${{ number_format($pago->monto, 2) }}</td>
                            </tr>
                            @endforeach
                            
                            <!-- Suma total de los abonos -->
                            <tr class="bg-body-secondary">
                                <td colspan="5" class="text-end fw-bold text-body">Total Abonado:</td>
                                <td class="fw-bold text-info">-${{ number_format($renta->pagos->sum('monto'), 2) }}</td>
                            </tr>
                        @endif
                        
                        <!-- 6. Saldo Pendiente Final -->
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end fs-6 text-body">SALDO PENDIENTE:</td>
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
                    <table class="table table-bordered table-hover align-middle mb-0 table-renta" style="font-size: 13px;">
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

    <!-- 🔥 SECCIÓN DE DOCUMENTOS FIRMADOS CORREGIDA PARA MODO OSCURO -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3 mt-2" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-folder-check text-warning me-2"></i>DOCUMENTOS FIRMADOS</h5>
            <div class="row g-3">
                <!-- Contrato -->
                <div class="col-12 col-md-6">
                    <div class="border rounded p-3 h-100 bg-body-tertiary">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf text-danger"></i> Contrato de Renta</h6>
                        @if($renta->contrato_firmado_path)
                            <!-- 👇 Aquí se cambió bg-white por bg-body -->
                            <div class="d-flex align-items-center justify-content-between bg-body border p-2 rounded">
                                <span class="small text-success fw-bold"><i class="bi bi-check-circle"></i> Archivo subido</span>
                                <div>
                                    <a href="{{ Storage::url($renta->contrato_firmado_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Ver</a>
                                    <form action="{{ route('rentas.deleteDocumento', [$renta, 'contrato']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este contrato?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('rentas.uploadContrato', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="contrato_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-upload"></i> Subir</button>
                                </div>
                                <small class="text-secondary d-block mt-1" style="font-size: 11px;">Formatos permitidos: PDF, JPG, PNG (Max. 5MB)</small>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Pagaré -->
                <div class="col-12 col-md-6">
                    <div class="border rounded p-3 h-100 bg-body-tertiary">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-warning"></i> Pagaré</h6>
                        @if($renta->pagare_firmado_path)
                            <!-- 👇 Aquí se cambió bg-white por bg-body -->
                            <div class="d-flex align-items-center justify-content-between bg-body border p-2 rounded">
                                <span class="small text-success fw-bold"><i class="bi bi-check-circle"></i> Archivo subido</span>
                                <div>
                                    <a href="{{ Storage::url($renta->pagare_firmado_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Ver</a>
                                    <form action="{{ route('rentas.deleteDocumento', [$renta, 'pagare']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este pagaré?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        @else
                            <form action="{{ route('rentas.uploadPagare', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="pagare_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="bi bi-upload"></i> Subir</button>
                                </div>
                                <small class="text-secondary d-block mt-1" style="font-size: 11px;">Formatos permitidos: PDF, JPG, PNG (Max. 5MB)</small>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
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
        <!-- Contenido original del modal ampliar (sin cambios) -->
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Ampliar Días de Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST" id="formAmpliar">
                @csrf
                <div class="modal-body">
                    <div class="row">
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
                    <div class="alert alert-light border mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <small class="text-secondary">Saldo Pendiente Actual:</small>
                            <strong class="text-body">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <small class="text-secondary">Saldo tras este pago:</small>
                            <strong id="nuevoSaldo" class="text-primary">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                        </div>
                    </div>

                    <!-- 🔥 CÁLCULO DE CAMBIO/FALTANTE -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">
                            Monto Recibido (por el cliente) <span class="text-danger">*</span>
                        </label>
                        <input type="number" id="inputMontoRecibidoPago" class="form-control form-control-sm bg-body fw-bold" step="0.01" required oninput="calcularCambioPago()" placeholder="Ej: 500">
                        <!-- Input oculto que envía la cantidad real a registrar al backend -->
                        <input type="hidden" name="monto" id="inputMontoRegistrarPago">
                    </div>

                    <div class="alert alert-secondary py-2 px-3 small mb-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Monto a registrar:</span>
                            <strong id="montoRegistrarText" class="text-primary">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Cambio a regresar:</span>
                            <strong id="cambioText" class="text-success">$0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Faltaría por liquidar:</span>
                            <strong id="faltanteText" class="text-danger">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Método de Pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago" id="metodoPagoRegistro" class="form-select form-select-sm bg-body" required onchange="toggleReferencia()">
                            <option value="efectivo">Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>

                    <div class="mb-3" id="campoReferencia" style="display: none;">
                        <label class="form-label small fw-semibold text-body">Referencia</label>
                        <input type="text" name="referencia" id="inputReferencia" class="form-control form-control-sm bg-body" placeholder="Folio de transferencia o boucher">
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

<script>
function calcularCambioPago() {
    const saldoPendienteOriginal = parseFloat("{{ $renta->saldo_pendiente }}") || 0;
    const montoRecibido = parseFloat(document.getElementById('inputMontoRecibidoPago').value) || 0;
    
    let montoARegistrar = 0;
    let cambio = 0;
    let faltante = 0;

    // Si pagan más o igual a la deuda
    if (montoRecibido >= saldoPendienteOriginal) {
        montoARegistrar = saldoPendienteOriginal;
        cambio = montoRecibido - saldoPendienteOriginal;
        faltante = 0;
    } else {
        // Si pagan menos de la deuda
        montoARegistrar = montoRecibido;
        cambio = 0;
        faltante = saldoPendienteOriginal - montoRecibido;
    }

    // Actualizamos el input oculto que viaja al backend
    document.getElementById('inputMontoRegistrarPago').value = montoARegistrar.toFixed(2);
    
    // Textos informativos
    document.getElementById('montoRegistrarText').textContent = '$' + montoARegistrar.toFixed(2);
    document.getElementById('cambioText').textContent = '$' + cambio.toFixed(2);
    document.getElementById('faltanteText').textContent = '$' + faltante.toFixed(2);
    
    // Feedback de "Saldo tras este pago"
    const elNuevoSaldo = document.getElementById('nuevoSaldo');
    elNuevoSaldo.textContent = '$' + faltante.toFixed(2);
    
    if (faltante === 0 && montoRecibido > 0) {
        elNuevoSaldo.className = 'text-success fw-bold';
    } else {
        elNuevoSaldo.className = 'text-primary fw-bold';
    }
}

function toggleReferencia() {
    const metodo = document.getElementById('metodoPagoRegistro').value;
    const campoReferencia = document.getElementById('campoReferencia');
    const inputReferencia = document.getElementById('inputReferencia');

    if (metodo === 'transferencia' || metodo === 'tarjeta') {
        campoReferencia.style.display = 'block';
        inputReferencia.setAttribute('required', 'required');
    } else {
        campoReferencia.style.display = 'none';
        inputReferencia.removeAttribute('required');
        inputReferencia.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalPago = document.getElementById('modalPago');
    if (modalPago) {
        modalPago.addEventListener('show.bs.modal', function () {
            toggleReferencia();
        });
    }
});
</script>

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

                    @if($diasRetraso > 0)
                        <div class="card border-warning mb-3">
                            <div class="card-header bg-warning bg-opacity-25 text-dark py-2 fw-bold" style="font-size: 14px;">
                                <i class="bi bi-clock-history me-1"></i> Multa por Retraso Generada ({{ $diasRetraso }} días)
                            </div>
                            <div class="card-body py-2">
                                <div class="row g-2 align-items-center">
                                    <div class="col-12 col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body text-danger fw-bold">$</span>
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
                        <input type="hidden" name="multa_retraso" id="multa_retraso" value="0">
                        <input type="hidden" name="motivo_multa" id="motivo_multa" value="">
                    @endif

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
                        <!-- 🔥 CAMPO MODIFICADO: VALUE 0 O VACÍO -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Monto Recibido <span class="text-danger">*</span></label>
                            <input type="number" id="montoRecibidoFinal" class="form-control form-control-sm bg-body fw-bold text-primary" step="0.01" 
                                   value="" placeholder="0.00" required oninput="recalcularFinalizacion()">
                            
                            <input type="hidden" name="monto_pago" id="montoPagoFinal" value="0">
                            
                            <small class="text-secondary d-block" id="montoMaximoLabel" style="font-size: 11px;">Deuda Total: ${{ number_format($renta->saldo_pendiente, 2) }}</small>
                        </div>

                        <!-- 🔥 SELECTOR CON ID PARA EJECUTAR LA FUNCIÓN -->
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Método de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago_final" id="metodoPagoFinalRegistro" class="form-select form-select-sm bg-body" required onchange="toggleReferenciaFinal()">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>

                        <!-- 🔥 CAMPO DE REFERENCIA OCULTO POR DEFECTO -->
                        <div class="col-12" id="campoReferenciaFinal" style="display: none;">
                            <label class="form-label small fw-semibold text-body">Referencia (Obligatoria si es Tarjeta/Transferencia)</label>
                            <input type="text" name="referencia_final" id="inputReferenciaFinal" class="form-control form-control-sm bg-body" placeholder="Folio de transferencia o boucher">
                        </div>

                        <div class="col-12 mt-2">
                            <div class="alert alert-secondary py-2 px-3 small mb-0">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Monto a cobrar (Deuda real):</span>
                                    <strong id="montoCobrarFinalText" class="text-primary">${{ number_format($renta->saldo_pendiente, 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cambio a regresar:</span>
                                    <strong id="cambioFinalText" class="text-success">$0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Faltaría por liquidar:</span>
                                    <strong id="faltanteFinalText" class="text-danger">$0.00</strong>
                                </div>
                            </div>
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
const inputMontoFinalHidden = document.getElementById('montoPagoFinal');
const labelMaximo = document.getElementById('montoMaximoLabel');
const fechaFinOriginal = "{{ $renta->fecha_fin->format('Y-m-d') }}";

function toggleCargoManual() {
    if (checkCargoManual.checked) {
        seccionCargoManual.classList.remove('d-none');
    } else {
        seccionCargoManual.classList.add('d-none');
        inputCargoManual.value = 0;
        motivoCargoManual.value = '';
    }
    recalcularFinalizacion();
}

// Ocultar o Mostrar el campo de Referencia Final
function toggleReferenciaFinal() {
    const metodo = document.getElementById('metodoPagoFinalRegistro').value;
    const campoReferencia = document.getElementById('campoReferenciaFinal');
    const inputReferencia = document.getElementById('inputReferenciaFinal');

    if (metodo === 'transferencia' || metodo === 'tarjeta') {
        campoReferencia.style.display = 'block';
        inputReferencia.setAttribute('required', 'required'); // Se vuelve obligatorio
    } else {
        campoReferencia.style.display = 'none';
        inputReferencia.removeAttribute('required'); // Se quita lo obligatorio
        inputReferencia.value = ''; // Se limpia el campo
    }
}

// Recalcula el total de la deuda y el cambio/faltante
function recalcularFinalizacion() {
    let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
    let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
    let deudaReal = saldoBaseOriginal + multa + manual;
    
    if (labelMaximo) {
        labelMaximo.textContent = 'Deuda Total: $' + deudaReal.toFixed(2);
    }
    
    let montoRecibido = parseFloat(document.getElementById('montoRecibidoFinal').value) || 0;
    let montoACobrar = 0;
    let cambio = 0;
    let faltante = 0;

    if (montoRecibido >= deudaReal) {
        montoACobrar = deudaReal;
        cambio = montoRecibido - deudaReal;
        faltante = 0;
    } else {
        montoACobrar = montoRecibido;
        cambio = 0;
        faltante = deudaReal - montoRecibido;
    }

    // Input hidden que se envía al backend
    inputMontoFinalHidden.value = montoACobrar.toFixed(2);
    
    // UI Feedback
    document.getElementById('montoCobrarFinalText').textContent = '$' + montoACobrar.toFixed(2);
    document.getElementById('cambioFinalText').textContent = '$' + cambio.toFixed(2);
    document.getElementById('faltanteFinalText').textContent = '$' + faltante.toFixed(2);
}

// Validación final al presionar el botón verde
function validarCargo() {
    let manual = parseFloat(inputCargoManual ? inputCargoManual.value : 0) || 0;
    let motivo = motivoCargoManual ? motivoCargoManual.value.trim() : '';
    
    if (checkCargoManual && checkCargoManual.checked && manual > 0 && motivo === '') {
        alert('Ha agregado un cargo extra manual de $' + manual + '. Por favor, especifique el motivo (ej: Llanta dañada).');
        motivoCargoManual.focus();
        return false;
    }
    
    let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
    let deudaReal = saldoBaseOriginal + multa + manual;
    let montoRecibido = parseFloat(document.getElementById('montoRecibidoFinal').value) || 0;
    
    // Validar que no falte dinero
    if (montoRecibido < deudaReal) {
        alert('El monto recibido ($' + montoRecibido.toFixed(2) + ') NO cubre la deuda total ($' + deudaReal.toFixed(2) + '). Para FINALIZAR la renta la deuda debe quedar en $0.00.');
        return false;
    }
    
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    recalcularFinalizacion();

    // Inicializar el estado de la referencia al cargar el modal (por si se abriera con otro método)
    const modalFinalizar = document.getElementById('modalFinalizar');
    if (modalFinalizar) {
        modalFinalizar.addEventListener('show.bs.modal', function () {
            toggleReferenciaFinal();
            // Asegurarse de que el input inicie vacío al abrir el modal (opcional, pero da mejor UX)
            document.getElementById('montoRecibidoFinal').value = '';
            recalcularFinalizacion();
        });
    }
});

const costoDiarioRenta = {{ $renta->detalles->sum(function($detalle) { 
    return $detalle->precio_dia * $detalle->cantidad; 
}) }};

function calcularAmpliacion() {
    const inputDias = document.getElementById('dias_extra');
    const dias = parseInt(inputDias.value) || 0;
    const checkFacturar = document.getElementById('facturar');
    
    // --- CÁLCULO DE COSTOS ---
    const costoExtra = dias * costoDiarioRenta;
    const ivaExtra = (checkFacturar && checkFacturar.checked) ? (costoExtra * 0.16) : 0;
    const totalExtra = costoExtra + ivaExtra;
    
    // Actualizamos los textos en el DOM
    document.getElementById('res_costo').textContent = '$' + costoExtra.toFixed(2);
    document.getElementById('res_iva_ext').textContent = '$' + ivaExtra.toFixed(2);
    document.getElementById('res_total_ext').textContent = '$' + totalExtra.toFixed(2);
    
    // --- CÁLCULO DE LA NUEVA FECHA TENTATIVA ---
    const elNuevaFecha = document.getElementById('nueva_fecha');
    
    // Evitamos problemas de Zona Horaria dividiendo la cadena manualmente
    let partesFecha = fechaFinOriginal.split('-');
    let anioOriginal = parseInt(partesFecha[0]);
    let mesOriginal = parseInt(partesFecha[1]) - 1; // En JS los meses van de 0 a 11
    let diaOriginal = parseInt(partesFecha[2]);
    
    if (dias > 0) {
        // Creamos la fecha original y le sumamos los días extra
        let fecha = new Date(anioOriginal, mesOriginal, diaOriginal);
        fecha.setDate(fecha.getDate() + dias);
        
        // Formateamos la nueva fecha a DD/MM/YYYY
        let diaStr = String(fecha.getDate()).padStart(2, '0');
        let mesStr = String(fecha.getMonth() + 1).padStart(2, '0');
        let anioStr = fecha.getFullYear();
        
        elNuevaFecha.textContent = diaStr + '/' + mesStr + '/' + anioStr;
    } else {
        // Si el input está en 0 o vacío, mostramos la fecha original
        let diaStr = String(diaOriginal).padStart(2, '0');
        let mesStr = String(mesOriginal + 1).padStart(2, '0');
        elNuevaFecha.textContent = diaStr + '/' + mesStr + '/' + anioOriginal;
    }
}

// Escuchar cuando se abre el modal para reiniciar los valores por defecto
document.addEventListener('DOMContentLoaded', function() {
    const modalAmpliar = document.getElementById('modalAmpliar');
    if (modalAmpliar) {
        modalAmpliar.addEventListener('show.bs.modal', function () {
            // Opcional: Limpiar el input al abrir el modal
            document.getElementById('dias_extra').value = '';
            document.getElementById('facturar').checked = false;
            calcularAmpliacion();
        });
    }
});
</script>
@endsection