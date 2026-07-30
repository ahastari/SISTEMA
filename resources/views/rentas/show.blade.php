@extends('layouts.admin')

@section('content')
<style>
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
            <button class="btn btn-secondary btn-sm rounded-3 text-white" data-bs-toggle="modal" data-bs-target="#modalDevParcial">
                <i class="bi bi-box-arrow-in-down me-1"></i> Dev. Parcial
            </button>
            <button class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalAmpliar">
                <i class="bi bi-plus-circle me-1"></i> Ampliar Días
            </button>
            <button class="btn btn-info btn-sm rounded-3 text-white" data-bs-toggle="modal" data-bs-target="#modalPago">
                <i class="bi bi-cash-coin me-1"></i> Registrar Pago
            </button>
            @if(empty($renta->contrato_firmado_path) || empty($renta->pagare_firmado_path))
                <button class="btn btn-success btn-sm rounded-3 opacity-50" onclick="alert('Faltan documentos por subir. Ve a la sección de Documentos Firmados en la parte inferior para subirlos antes de finalizar.');">
                    <i class="bi bi-check-lg me-1"></i> Finalizar
                </button>
            @else
                <button class="btn btn-success btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                    <i class="bi bi-check-lg me-1"></i> Finalizar
                </button>
            @endif
            <a href="{{ route('rentas.cancelar', $renta) }}" class="btn btn-outline-danger btn-sm rounded-3" onclick="return confirm('¿Estás seguro de cancelar esta renta? El stock regresará inmediatamente.');">
                <i class="bi bi-x-octagon me-1"></i> Cancelar
            </a>
        @endif
    </div>
</div>

@if($renta->estado == 'activa' && (empty($renta->contrato_firmado_path) || empty($renta->pagare_firmado_path)))
<div class="alert alert-warning alert-dismissible fade show rounded-3 mb-4" role="alert" style="border-left: 4px solid #ffc107;">
    <i class="bi bi-exclamation-triangle-fill me-2 text-warning"></i>
    <strong>¡Documentos Pendientes!</strong> No podrás finalizar la renta hasta que subas el 
    @if(empty($renta->contrato_firmado_path) && empty($renta->pagare_firmado_path))
        <strong>Contrato y el Pagaré</strong>
    @elseif(empty($renta->contrato_firmado_path))
        <strong>Contrato</strong>
    @else
        <strong>Pagaré</strong>
    @endif
    firmados en la sección de abajo.
</div>
@endif

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
                @elseif($renta->estado == 'cancelada')
                    <span class="badge bg-dark rounded-pill px-2">CANCELADA</span>
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
                            <th>Cant. Inicial</th>
                            <th>Entregados</th>
                            <th>Pendientes</th>
                            <th>Equipo</th>
                            <th>Código</th>
                            <th>Precio/día</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renta->detalles as $detalle)
                        <tr>
                            <td class="fw-bold text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-success fw-bold text-center">{{ $detalle->cantidad_devuelta }}</td>
                            <td class="text-danger fw-bold text-center">{{ $detalle->cantidad - $detalle->cantidad_devuelta }}</td>
                            <td>{{ $detalle->equipo->nombre }}</td>
                            <td><code>{{ $detalle->equipo->codigo }}</code></td>
                            <td>${{ number_format($detalle->precio_dia, 2) }}</td>
                            <td class="fw-semibold">${{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LADO IZQUIERDO: REGISTRO DE MOVIMIENTOS -->
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-clock-history text-warning me-2"></i>REGISTRO DE MOVIMIENTOS</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="bg-body-tertiary">
                        <tr>
                            <th>Fecha</th>
                            <th>Movimiento</th>
                            <th>Detalle / Ref.</th>
                            <th class="text-end">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(($renta->deposito ?? 0) > 0)
                        <tr>
                            <td class="text-secondary">{{ $renta->fecha_inicio->format('d/m/Y') }}</td>
                            <td><span class="badge bg-secondary">Depósito</span></td>
                            <td class="text-secondary small">Garantía inicial</td>
                            <td class="text-end fw-bold text-primary">-${{ number_format($renta->deposito, 2) }}</td>
                        </tr>
                        @endif

                        @if(($renta->dias_ampliados ?? 0) > 0)
                        @php
                        $costoDiarioRenta = 0;
                        foreach($renta->detalles as $detalle) {
                        $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
                        if ($pendiente > 0) {
                        $costoDiarioRenta += ($detalle->precio_dia * $pendiente);
                        }
                        }
                        $costoAmpliacion = $costoDiarioRenta * $renta->dias_ampliados;
                        @endphp
                        <tr>
                            <td class="text-secondary">{{ $renta->fecha_ampliacion ? \Carbon\Carbon::parse($renta->fecha_ampliacion)->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="badge bg-primary">Ampliación</span></td>
                            <td class="text-secondary small">+{{ $renta->dias_ampliados }} días al contrato</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($costoAmpliacion, 2) }}</td>
                        </tr>
                        @endif

                        @foreach($renta->pagos as $pago)
                        <tr>
                            <td class="text-secondary">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge 
                                    @if($pago->metodo_pago == 'efectivo') bg-success
                                    @elseif($pago->metodo_pago == 'transferencia') bg-info
                                    @else bg-warning text-dark
                                    @endif">
                                    Pago {{ ucfirst($pago->metodo_pago) }}
                                </span>
                            </td>
                            <td class="text-secondary small text-truncate" style="max-width: 150px;" title="{{ $pago->referencia ?? $pago->observaciones }}">
                                {{ $pago->referencia ?? $pago->observaciones ?? 'Abono a cuenta' }}
                            </td>
                            <td class="text-end fw-bold text-success">-${{ number_format($pago->monto, 2) }}</td>
                        </tr>
                        @endforeach

                        @if(isset($renta->cargos_extra) && $renta->cargos_extra > 0)
                        <tr>
                            <td class="text-secondary">{{ $renta->fecha_devolucion ? $renta->fecha_devolucion->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="badge bg-danger">Cargos Extra</span></td>
                            <td class="text-secondary small">Multas o Daños</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($renta->cargos_extra, 2) }}</td>
                        </tr>
                        @endif

                        @if($renta->estado == 'activa' && $multaCalculada > 0)
                        <tr class="bg-danger bg-opacity-10">
                            <td class="text-danger fw-bold">Actual</td>
                            <td><span class="badge bg-danger">Retraso</span></td>
                            <td class="text-danger small">{{ $diasRetraso }} día(s) vencido(s)</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($multaCalculada, 2) }}</td>
                        </tr>
                        @endif

                        @if(($renta->deposito ?? 0) <= 0 && ($renta->dias_ampliados ?? 0) <= 0 && $renta->pagos->count() == 0 && (!isset($renta->cargos_extra) || $renta->cargos_extra <= 0) && $multaCalculada <=0)
                                    <tr>
                                    <td colspan="4" class="text-center text-secondary py-4">No hay movimientos financieros registrados.</td>
                                    </tr>
                                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- LADO DERECHO: RESUMEN FINANCIERO -->
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 p-3 h-100" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3">
                <i class="bi bi-calculator text-success me-2"></i>RESUMEN FINANCIERO
                @if($renta->estado == 'cancelada')
                    <span class="badge bg-secondary ms-2 fs-6">Anulado</span>
                @endif
            </h5>

            <table class="table table-borderless align-middle mb-0" style="font-size: 14px;">
                <tbody>
                    @php
                    // Separamos el costo puro de los equipos del subtotal guardado
                    $subtotalEquipos = $renta->subtotal - ($renta->flete ?? 0) - ($renta->mano_obra ?? 0);
                    @endphp

                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">Equipos:</td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($subtotalEquipos, 2) }}</td>
                    </tr>

                    @if(($renta->flete ?? 0) > 0)
                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">Flete:</td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($renta->flete, 2) }}</td>
                    </tr>
                    @endif

                    @if(($renta->mano_obra ?? 0) > 0)
                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">Mano de Obra:</td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($renta->mano_obra, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="border-bottom bg-body-tertiary">
                        <td class="text-secondary fw-bold">Subtotal General:</td>
                        <td class="text-end fw-bold text-secondary">${{ number_format($renta->subtotal, 2) }}</td>
                    </tr>

                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">IVA (16%):</td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($renta->iva, 2) }}</td>
                    </tr>

                    @php
                    $cargosAdicionales = (isset($renta->cargos_extra) ? $renta->cargos_extra : 0) + ($renta->estado == 'activa' ? $multaCalculada : 0);
                    @endphp
                    @if($cargosAdicionales > 0)
                    <tr class="border-bottom">
                        <td class="text-danger fw-bold">Cargos por Retraso/Daños:</td>
                        <td class="text-end fw-bold text-danger">+${{ number_format($cargosAdicionales, 2) }}</td>
                    </tr>
                    @endif

                    <tr class="bg-body-tertiary border-bottom">
                        <td class="text-body fw-bold py-3">TOTAL DEL CONTRATO:</td>
                        <td class="text-end fw-bold text-success fs-5 py-3">
                            @if($renta->estado == 'cancelada')
                                <span class="text-decoration-line-through text-secondary fs-6">${{ number_format($renta->total, 2) }}</span><br>
                                <span class="text-dark">$0.00</span>
                            @else
                                ${{ number_format($renta->total + ($renta->estado == 'activa' ? $multaCalculada : 0), 2) }}
                            @endif
                        </td>
                    </tr>

                    @php
                    $totalAbonado = $renta->pagos->sum('monto') + ($renta->deposito ?? 0);
                    @endphp
                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">Total Abonado (Inc. Depósito):</td>
                        <td class="text-end fw-bold text-primary">-${{ number_format($totalAbonado, 2) }}</td>
                    </tr>

                    @php
                    // Forzamos el saldo a 0 visualmente si está cancelada por mayor seguridad
                    $saldoPendienteReal = $renta->estado == 'cancelada' ? 0 : ($renta->saldo_pendiente + ($renta->estado == 'activa' ? $multaCalculada : 0));
                    @endphp
                    <tr>
                        <td class="text-body fw-bold py-3">SALDO PENDIENTE:</td>
                        <td class="text-end fw-bold fs-4 {{ $saldoPendienteReal > 0 ? 'text-danger' : 'text-success' }} py-3">
                            ${{ number_format($saldoPendienteReal, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- DOCUMENTOS FIRMADOS -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 p-3 mt-2" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
            <h5 class="fw-bold text-body mb-3"><i class="bi bi-folder-check text-warning me-2"></i>DOCUMENTOS FIRMADOS</h5>
            <div class="row g-3">
                
                <!-- CONTRATO -->
                <div class="col-12 col-md-6">
                    <div class="border rounded p-3 h-100 bg-body-tertiary">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-pdf text-danger"></i> Contrato de Renta</h6>
                        @if($renta->contrato_firmado_path)
                        <div class="d-flex align-items-center justify-content-between bg-body border p-2 rounded">
                            <span class="small text-success fw-bold"><i class="bi bi-check-circle"></i> Archivo subido</span>
                            <div>
                                <a href="{{ Storage::url($renta->contrato_firmado_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Ver</a>
                                @if($renta->estado == 'activa')
                                <form action="{{ route('rentas.deleteDocumento', [$renta, 'contrato']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este contrato?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @else
                            @if($renta->estado == 'activa')
                            <form action="{{ route('rentas.uploadContrato', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="contrato_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button type="submit" class="btn btn-primary fw-bold"><i class="bi bi-upload"></i> Subir</button>
                                </div>
                            </form>
                            @else
                            <div class="alert alert-secondary py-2 px-3 small mb-0 text-center">No se subió ningún documento</div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- PAGARÉ -->
                <div class="col-12 col-md-6">
                    <div class="border rounded p-3 h-100 bg-body-tertiary">
                        <h6 class="fw-bold mb-3"><i class="bi bi-file-earmark-text text-warning"></i> Pagaré</h6>
                        @if($renta->pagare_firmado_path)
                        <div class="d-flex align-items-center justify-content-between bg-body border p-2 rounded">
                            <span class="small text-success fw-bold"><i class="bi bi-check-circle"></i> Archivo subido</span>
                            <div>
                                <a href="{{ Storage::url($renta->pagare_firmado_path) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Ver</a>
                                @if($renta->estado == 'activa')
                                <form action="{{ route('rentas.deleteDocumento', [$renta, 'pagare']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este pagaré?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                @endif
                            </div>
                        </div>
                        @else
                            @if($renta->estado == 'activa')
                            <form action="{{ route('rentas.uploadPagare', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="input-group input-group-sm">
                                    <input type="file" name="pagare_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <button type="submit" class="btn btn-warning fw-bold text-dark"><i class="bi bi-upload"></i> Subir</button>
                                </div>
                            </form>
                            @else
                            <div class="alert alert-secondary py-2 px-3 small mb-0 text-center">No se subió ningún documento</div>
                            @endif
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

@if($renta->estado == 'activa')
<!-- MODAL: Ampliar Días -->
<div class="modal fade" id="modalAmpliar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-1"></i> Ampliar Días de Renta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">Días adicionales <span class="text-danger">*</span></label>
                                <input type="number" name="dias_extra" id="dias_extra" class="form-control form-control-sm bg-body" min="1" required oninput="calcularAmpliacion()" placeholder="Ej: 3">
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
                    <button type="submit" class="btn btn-sm btn-primary fw-bold"><i class="bi bi-check-lg me-1"></i> Procesar</button>
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

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Monto Recibido <span class="text-danger">*</span></label>
                        <input type="number" id="inputMontoRecibidoPago" class="form-control form-control-sm bg-body fw-bold" step="0.01" required oninput="calcularCambioPago()" placeholder="Ej: 500">
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

<!-- MODAL: Finalizar Renta -->
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
                        <div class="col-12 col-md-5">
                            <div class="alert alert-info py-2 px-3 small h-100 mb-0">
                                <strong class="d-block mb-2"><i class="bi bi-calculator me-1"></i> Cálculo de Cuenta Inicial:</strong>
                                Total original: ${{ number_format($renta->total, 2) }}<br>
                                - Depósito: ${{ number_format($renta->deposito ?? 0, 2) }}<br>
                                - Pagado: ${{ number_format($renta->pagos->sum('monto'), 2) }}<br>
                                <hr class="my-2">
                                <strong class="text-danger fs-6">Saldo Base a Pagar: $<span id="saldo_base_txt">{{ number_format($renta->saldo_pendiente, 2) }}</span></strong>
                            </div>
                        </div>
                        <div class="col-12 col-md-7">
                            <div class="card border-warning h-100 mb-0">
                                <div class="card-header bg-warning bg-opacity-25 text-dark py-1 fw-bold" style="font-size: 13px;">
                                    <i class="bi bi-box-arrow-in-down me-1"></i> Confirmar Entrega de Equipo
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 150px;">
                                        <table class="table table-sm mb-0 align-middle" style="font-size: 12px;">
                                            <thead class="bg-body-tertiary">
                                                <tr>
                                                    <th>Equipo</th>
                                                    <th class="text-center">Pend.</th>
                                                    <th style="width: 80px;" class="text-center">Devueltos</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($renta->detalles as $detalle)
                                                @php $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta; @endphp
                                                @if($pendiente > 0)
                                                <tr>
                                                    <td class="text-truncate" style="max-width: 140px;">{{ $detalle->equipo->nombre }}</td>
                                                    <td class="text-center fw-bold text-danger">{{ $pendiente }}</td>
                                                    <td class="text-center">
                                                        <input type="number" name="devolver_final[{{ $detalle->id }}]" class="form-control form-control-sm text-center px-1" min="0" max="{{ $pendiente }}" value="{{ $pendiente }}" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                                                    </td>
                                                </tr>
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="px-2 py-1 bg-body-tertiary text-secondary border-top" style="font-size: 10px;">
                                        <i class="bi bi-info-circle me-1"></i> Si falta algún artículo, disminuye el número en "Devueltos".
                                    </div>
                                </div>
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
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="multa_retraso" id="multa_retraso" value="0">
                    <input type="hidden" name="motivo_multa" id="motivo_multa" value="">
                    @endif

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="check_cargo_manual" onchange="toggleCargoManual()">
                        <label class="form-check-label fw-bold text-danger small" for="check_cargo_manual">
                            ¿Generar otro cargo extra? (Daños, piezas perdidas)
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
                            <label class="form-label small fw-semibold text-body">Monto Recibido <span class="text-danger">*</span></label>
                            <input type="number" id="montoRecibidoFinal" class="form-control form-control-sm bg-body fw-bold text-primary" step="0.01" value="" required oninput="recalcularFinalizacion()">
                            <input type="hidden" name="monto_pago" id="montoPagoFinal" value="0">
                            <small class="text-secondary d-block" id="montoMaximoLabel" style="font-size: 11px;">Deuda Total: ${{ number_format($renta->saldo_pendiente, 2) }}</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Método de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago_final" id="metodoPagoFinalRegistro" class="form-select form-select-sm bg-body" required onchange="toggleReferenciaFinal()">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                            </select>
                        </div>
                        <div class="col-12" id="campoReferenciaFinal" style="display: none;">
                            <label class="form-label small fw-semibold text-body">Referencia</label>
                            <input type="text" name="referencia_final" id="inputReferenciaFinal" class="form-control form-control-sm bg-body">
                        </div>
                        <div class="col-12 mt-2">
                            <div class="alert alert-secondary py-2 px-3 small mb-0">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Monto a cobrar:</span>
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
                    <button type="submit" class="btn btn-sm btn-success fw-bold" onclick="return validarCargo()"><i class="bi bi-check-lg me-1"></i> Finalizar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--Devolución Parcial -->
<div class="modal fade" id="modalDevParcial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-secondary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-box-arrow-in-down me-1"></i> Registrar Devolución Parcial</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.devolucionParcial', $renta) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="small text-secondary mb-3">Indica la cantidad de artículos que el cliente está devolviendo en este momento. El stock regresará automáticamente a la sucursal.</p>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle" style="font-size: 13px;">
                            <thead class="bg-body-tertiary">
                                <tr>
                                    <th>Equipo</th>
                                    <th class="text-center">Pendientes</th>
                                    <th style="width: 120px;">A devolver hoy</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($renta->detalles as $detalle)
                                @php $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta; @endphp
                                <tr>
                                    <td>{{ $detalle->equipo->nombre }}</td>
                                    <td class="text-center fw-bold {{ $pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ $pendiente }}
                                    </td>
                                    <td>
                                        @if($pendiente > 0)
                                        <input type="number" name="devolver[{{ $detalle->id }}]" class="form-control form-control-sm" min="0" max="{{ $pendiente }}" value="0" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                                        @else
                                        <span class="badge bg-success w-100">Devuelto</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-secondary fw-bold">Registrar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

    @php
        $costoDiarioPendiente = 0;
        foreach($renta->detalles as $detalle) {
            $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta;
            if ($pendiente > 0) {
                $costoDiarioPendiente += ($detalle->precio_dia * $pendiente);
            }
        }
    @endphp

<script>
    // Lógica de Modal Pago Regular
    function calcularCambioPago() {
        const saldoPendienteOriginal = parseFloat("{{ $renta->saldo_pendiente }}") || 0;
        const montoRecibido = parseFloat(document.getElementById('inputMontoRecibidoPago').value) || 0;

        let montoARegistrar = 0,
            cambio = 0,
            faltante = 0;

        if (montoRecibido >= saldoPendienteOriginal) {
            montoARegistrar = saldoPendienteOriginal;
            cambio = montoRecibido - saldoPendienteOriginal;
        } else {
            montoARegistrar = montoRecibido;
            faltante = saldoPendienteOriginal - montoRecibido;
        }

        document.getElementById('inputMontoRegistrarPago').value = montoARegistrar.toFixed(2);
        document.getElementById('montoRegistrarText').textContent = '$' + montoARegistrar.toFixed(2);
        document.getElementById('cambioText').textContent = '$' + cambio.toFixed(2);
        document.getElementById('faltanteText').textContent = '$' + faltante.toFixed(2);

        const elNuevoSaldo = document.getElementById('nuevoSaldo');
        elNuevoSaldo.textContent = '$' + faltante.toFixed(2);
        elNuevoSaldo.className = (faltante === 0 && montoRecibido > 0) ? 'text-success fw-bold' : 'text-primary fw-bold';
    }

    function toggleReferencia() {
        const metodo = document.getElementById('metodoPagoRegistro').value;
        const inputReferencia = document.getElementById('inputReferencia');
        if (metodo === 'transferencia' || metodo === 'tarjeta') {
            document.getElementById('campoReferencia').style.display = 'block';
            inputReferencia.setAttribute('required', 'required');
        } else {
            document.getElementById('campoReferencia').style.display = 'none';
            inputReferencia.removeAttribute('required');
            inputReferencia.value = '';
        }
    }

    // Lógica de Modal Finalizar
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

    function toggleReferenciaFinal() {
        const metodo = document.getElementById('metodoPagoFinalRegistro').value;
        const inputReferencia = document.getElementById('inputReferenciaFinal');
        if (metodo === 'transferencia' || metodo === 'tarjeta') {
            document.getElementById('campoReferenciaFinal').style.display = 'block';
            inputReferencia.setAttribute('required', 'required');
        } else {
            document.getElementById('campoReferenciaFinal').style.display = 'none';
            inputReferencia.removeAttribute('required');
            inputReferencia.value = '';
        }
    }

    function recalcularFinalizacion() {
        let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
        let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
        let deudaReal = saldoBaseOriginal + multa + manual;

        if (labelMaximo) labelMaximo.textContent = 'Deuda Total: $' + deudaReal.toFixed(2);

        let montoRecibido = parseFloat(document.getElementById('montoRecibidoFinal').value) || 0;
        let montoACobrar = 0,
            cambio = 0,
            faltante = 0;

        if (montoRecibido >= deudaReal) {
            montoACobrar = deudaReal;
            cambio = montoRecibido - deudaReal;
        } else {
            montoACobrar = montoRecibido;
            faltante = deudaReal - montoRecibido;
        }

        inputMontoFinalHidden.value = montoACobrar.toFixed(2);
        document.getElementById('montoCobrarFinalText').textContent = '$' + montoACobrar.toFixed(2);
        document.getElementById('cambioFinalText').textContent = '$' + cambio.toFixed(2);
        document.getElementById('faltanteFinalText').textContent = '$' + faltante.toFixed(2);
    }

    function validarCargo() {
        let manual = parseFloat(inputCargoManual ? inputCargoManual.value : 0) || 0;
        let motivo = motivoCargoManual ? motivoCargoManual.value.trim() : '';

        if (checkCargoManual && checkCargoManual.checked && manual > 0 && motivo === '') {
            alert('Ha agregado un cargo extra manual. Especifique el motivo.');
            motivoCargoManual.focus();
            return false;
        }

        let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
        let deudaReal = saldoBaseOriginal + multa + manual;
        let montoRecibido = parseFloat(document.getElementById('montoRecibidoFinal').value) || 0;

        if (montoRecibido < deudaReal) {
            alert('El monto recibido ($' + montoRecibido.toFixed(2) + ') NO cubre la deuda total ($' + deudaReal.toFixed(2) + ').');
            return false;
        }
        return true;
    }

    const costoDiarioRenta = {{ $costoDiarioPendiente }};

    function calcularAmpliacion() {
        const dias = parseInt(document.getElementById('dias_extra').value) || 0;
        const checkFacturar = document.getElementById('facturar');

        const costoExtra = dias * costoDiarioRenta;
        const ivaExtra = (checkFacturar && checkFacturar.checked) ? (costoExtra * 0.16) : 0;

        document.getElementById('res_costo').textContent = '$' + costoExtra.toFixed(2);
        document.getElementById('res_iva_ext').textContent = '$' + ivaExtra.toFixed(2);
        document.getElementById('res_total_ext').textContent = '$' + (costoExtra + ivaExtra).toFixed(2);

        const elNuevaFecha = document.getElementById('nueva_fecha');
        let partesFecha = fechaFinOriginal.split('-');

        if (dias > 0) {
            let fecha = new Date(partesFecha[0], partesFecha[1] - 1, partesFecha[2]);
            fecha.setDate(fecha.getDate() + dias);
            elNuevaFecha.textContent = String(fecha.getDate()).padStart(2, '0') + '/' + String(fecha.getMonth() + 1).padStart(2, '0') + '/' + fecha.getFullYear();
        } else {
            elNuevaFecha.textContent = String(partesFecha[2]).padStart(2, '0') + '/' + String(partesFecha[1]).padStart(2, '0') + '/' + partesFecha[0];
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['modalPago', 'modalFinalizar', 'modalAmpliar'].forEach(id => {
            let modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('show.bs.modal', function() {
                    if (id === 'modalPago') toggleReferencia();
                    if (id === 'modalFinalizar') {
                        toggleReferenciaFinal();
                        document.getElementById('montoRecibidoFinal').value = '';
                        recalcularFinalizacion();
                    }
                    if (id === 'modalAmpliar') {
                        document.getElementById('dias_extra').value = '';
                        document.getElementById('facturar').checked = false;
                        calcularAmpliacion();
                    }
                });
            }
        });
        recalcularFinalizacion();
    });
</script>
@endsection