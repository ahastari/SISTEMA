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

    .doc-card {
        border: 1px solid var(--bs-border-color);
        border-radius: 12px;
        transition: all 0.25s ease;
        background: var(--bs-body-bg);
    }

    .doc-card.is-uploaded {
        border-color: rgba(25, 135, 84, 0.35);
        background: rgba(25, 135, 84, 0.02);
    }

    .doc-card.is-pending {
        border: 2px dashed rgba(255, 193, 7, 0.6);
        background: rgba(255, 193, 7, 0.02);
    }

    .doc-card.is-pending:hover {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.03);
    }

    .doc-icon-wrapper {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }

</style>

<!-- ENCABEZADO Y BARRA DE ACCIONES DE RENTA -->
<div class="card border-0 shadow-sm rounded-3 mb-4" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        
        <!-- Lado Izquierdo: Título, Estado y Folio -->
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('rentas.index') }}" class="btn btn-outline-secondary btn-sm rounded-3" title="Regresar al listado">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="mb-0 fw-bold text-body">Contrato de Renta</h4>
                    @if($renta->estado == 'activa')
                        <span class="badge bg-success rounded-pill px-2">ACTIVA</span>
                    @elseif($renta->estado == 'cancelada')
                        <span class="badge bg-dark rounded-pill px-2">CANCELADA</span>
                    @else
                        <span class="badge bg-info rounded-pill px-2">FINALIZADA</span>
                    @endif
                </div>
                <small class="text-secondary">Folio: <strong class="text-body">{{ $renta->folio }}</strong></small>
            </div>
        </div>

        <!-- Lado Derecho: Botones con el Estilo y Colores Originales -->
        <div class="d-flex flex-wrap align-items-center gap-2">

            <!-- PDF Contrato y Pagaré -->
            <button type="button" class="btn btn-danger btn-sm rounded-3" onclick="verDocumento('{{ route('rentas.contrato', $renta) }}', 'Contrato de Renta PDF')">
                <i class="bi bi-file-pdf me-1"></i> <span class="d-none d-sm-inline">Contrato PDF</span>
            </button>

            <button type="button" class="btn btn-warning btn-sm rounded-3 text-dark" onclick="verDocumento('{{ route('rentas.pagare', $renta) }}', 'Pagaré PDF')">
                <i class="bi bi-file-text me-1"></i> <span class="d-none d-sm-inline">Pagaré</span>
            </button>
            
            <!-- Acciones Operativas -->
            @if($renta->estado == 'activa' && !$renta->autorizacion_solicitada && !$renta->autorizacion_aprobada)
                <button class="btn btn-secondary btn-sm rounded-3 text-white" data-bs-toggle="modal" data-bs-target="#modalDevParcial">
                    <i class="bi bi-box-arrow-in-down me-1"></i> Dev. Parcial
                </button>
                <button class="btn btn-primary btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalAmpliar">
                    <i class="bi bi-plus-circle me-1"></i> Ampliar Días
                </button>
            @endif

            <!-- Registrar Pago / Liquidar -->
            @if($renta->saldo_pendiente > 0 || $renta->autorizacion_aprobada || ($renta->estado == 'activa' && !$renta->autorizacion_solicitada))
                <button class="btn {{ $renta->autorizacion_aprobada ? 'btn-success' : 'btn-info' }} btn-sm rounded-3 text-white" data-bs-toggle="modal" data-bs-target="#modalPago">
                    <i class="bi bi-cash-coin me-1"></i> {{ $renta->autorizacion_aprobada ? 'Liquidar / Registrar Productos' : 'Registrar Pago' }}
                </button>
            @endif

            <!-- Finalizar Renta & Cancelar -->
            @if($renta->estado == 'activa' && !$renta->autorizacion_solicitada && !$renta->autorizacion_aprobada)
                @if(empty($renta->contrato_firmado_path) || empty($renta->pagare_firmado_path))
                    <button class="btn btn-success btn-sm rounded-3 opacity-50" onclick="alert('Faltan documentos por subir.');">
                        <i class="bi bi-check-lg me-1"></i> Finalizar
                    </button>
                @else
                    <button class="btn btn-success btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalFinalizar">
                        <i class="bi bi-check-lg me-1"></i> Finalizar
                    </button>
                @endif

                <a href="{{ route('rentas.cancelar', $renta) }}" class="btn btn-outline-danger btn-sm rounded-3" onclick="return confirm('¿Cancelar?');">
                    <i class="bi bi-x-octagon me-1"></i> Cancelar
                </a>
            @endif

        </div>
    </div>
</div>

@if($renta->autorizacion_solicitada)
<div class="alert alert-warning alert-dismissible fade show rounded-3 mb-4 border-warning shadow-sm" role="alert">
    <i class="bi bi-shield-lock-fill me-2 text-warning fs-5"></i>
    <strong>Cuenta en Revisión:</strong> Se ha solicitado autorización al gerente para finalizar esta renta con un adeudo. Las acciones de cuenta están bloqueadas temporalmente hasta su respuesta.
</div>
@endif

@if($renta->autorizacion_aprobada)
<div class="alert alert-success alert-dismissible fade show rounded-3 mb-4 border-success shadow-sm" role="alert">
    <i class="bi bi-check-circle-fill me-2 text-success fs-5"></i>
    <strong>¡Autorización Aprobada por el Gerente!</strong> Esta renta ya cuenta con permiso para finalizar con adeudo. Puedes proceder a registrar la devolución de productos, agregar cargos extra por faltantes y liquidar/abonar la cuenta.
</div>
@endif

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
                            $costoDiarioPendienteMov = $renta->detalles->sum(function($d) {
                                $pendiente = $d->cantidad - $d->cantidad_devuelta;
                                return $pendiente > 0 ? ($pendiente * $d->precio_dia) : 0;
                            });
                            $subtotalAmpliacionMov = $costoDiarioPendienteMov * $renta->dias_ampliados;
                            $ivaAmpliacionMov = $renta->facturar ? ($subtotalAmpliacionMov * 0.16) : 0;
                            $montoTotalAmpliacionMov = $subtotalAmpliacionMov + $ivaAmpliacionMov;
                        @endphp
                        <tr>
                            <td class="text-secondary">{{ $renta->fecha_ampliacion ? \Carbon\Carbon::parse($renta->fecha_ampliacion)->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="badge bg-primary">Ampliación</span></td>
                            <td class="text-secondary small">+{{ $renta->dias_ampliados }} días al contrato</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($montoTotalAmpliacionMov, 2) }}</td>
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
                            <td class="text-secondary small">Multas, Daños o Faltantes</td>
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
                        <td class="text-danger fw-bold">Cargos por Retraso/Daños/Faltantes:</td>
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
            
            {{-- Encabezado con Contador de Estado --}}
            @php
                $docsSubidos = ($renta->contrato_firmado_path ? 1 : 0) + ($renta->pagare_firmado_path ? 1 : 0);
            @endphp
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h5 class="fw-bold text-body mb-0">
                    <i class="bi bi-folder-check text-warning me-2"></i>DOCUMENTOS FIRMADOS
                </h5>
                @if($docsSubidos == 2)
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> 2/2 Documentos Completos
                    </span>
                @elseif($docsSubidos == 1)
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> 1/2 Documento Pendiente
                    </span>
                @else
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                        <i class="bi bi-x-circle-fill me-1"></i> 0/2 Documentos Subidos
                    </span>
                @endif
            </div>

            <div class="row g-3">
                <!-- CONTRATO DE RENTA -->
                <div class="col-12 col-md-6">
                    @if($renta->contrato_firmado_path)
                        <div class="doc-card is-uploaded p-3 h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="doc-icon-wrapper bg-danger bg-opacity-10 text-danger">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-body">Contrato de Renta</h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="bi bi-check-circle-fill me-1"></i> Archivo subido
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3" onclick="verDocumento('{{ Storage::url($renta->contrato_firmado_path) }}', 'Contrato Firmado Subido')">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </button>
                                @if($renta->estado == 'activa')
                                <form action="{{ route('rentas.deleteDocumento', [$renta, 'contrato']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este contrato?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar documento">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @else
                        @if($renta->estado == 'activa')
                            <div class="doc-card is-pending p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="doc-icon-wrapper bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-cloud-arrow-up-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-body">Contrato de Renta</h6>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small">
                                                <i class="bi bi-clock-history me-1"></i> Pendiente de subir
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('rentas.uploadContrato', $renta) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="file" name="contrato_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <button type="submit" class="btn btn-primary fw-bold px-3">
                                                <i class="bi bi-upload me-1"></i> Subir
                                            </button>
                                        </div>
                                        <small class="text-secondary d-block" style="font-size: 11px;">PDF, JPG o PNG</small>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="doc-card p-3 h-100 bg-body-tertiary d-flex align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="doc-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-file-earmark-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-body">Contrato de Renta</h6>
                                        <span class="text-secondary small">Sin documento adjunto</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                <!-- PAGARÉ -->
                <div class="col-12 col-md-6">
                    @if($renta->pagare_firmado_path)
                        <div class="doc-card is-uploaded p-3 h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="doc-icon-wrapper bg-warning bg-opacity-15 text-warning-emphasis">
                                    <i class="bi bi-file-earmark-text-fill"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1 text-body">Pagaré</h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                        <i class="bi bi-check-circle-fill me-1"></i> Archivo subido
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2 pt-2 border-top">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3" onclick="verDocumento('{{ Storage::url($renta->pagare_firmado_path) }}', 'Pagaré Firmado Subido')">
                                    <i class="bi bi-eye me-1"></i> Ver documento
                                </button>
                                @if($renta->estado == 'activa')
                                <form action="{{ route('rentas.deleteDocumento', [$renta, 'pagare']) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Seguro que deseas eliminar este pagaré?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar documento">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    @else
                        @if($renta->estado == 'activa')
                            <div class="doc-card is-pending p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center gap-3 mb-3">
                                        <div class="doc-icon-wrapper bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-cloud-arrow-up-fill"></i>
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-1 text-body">Pagaré</h6>
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small">
                                                <i class="bi bi-clock-history me-1"></i> Pendiente de subir
                                            </span>
                                        </div>
                                    </div>
                                    <form action="{{ route('rentas.uploadPagare', $renta) }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="input-group input-group-sm mb-1">
                                            <input type="file" name="pagare_firmado" class="form-control bg-body" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <button type="submit" class="btn btn-warning fw-bold text-dark px-3">
                                                <i class="bi bi-upload me-1"></i> Subir
                                            </button>
                                        </div>
                                        <small class="text-secondary d-block" style="font-size: 11px;">PDF, JPG o PNG</small>
                                    </form>
                                </div>
                            </div>
                        @else
                            <div class="doc-card p-3 h-100 bg-body-tertiary d-flex align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="doc-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                                        <i class="bi bi-file-earmark-x"></i>
                                    </div>
                                    <div>
                                        <h6 class="fw-bold mb-1 text-body">Pagaré</h6>
                                        <span class="text-secondary small">Sin documento adjunto</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

        </div>
    </div>

    @if($renta->observaciones)
    <div class="col-12">
        <div class="info-card" style="border-left-color: #6c757d;">
            <h6 class="text-secondary"><i class="bi bi-chat-left-text me-1"></i> OBSERVACIONES E HISTORIAL</h6>
            <p class="mb-0 text-body small" style="line-height: 1.6;">
                @php
                    $obsFormateadas = e($renta->observaciones);
                    $obsFormateadas = str_replace('[AUTORIZACIÓN APROBADA', '<br><strong class="text-success"><i class="bi bi-check-circle-fill"></i> AUTORIZACIÓN APROBADA</strong>', $obsFormateadas);
                    $obsFormateadas = str_replace('[AUTORIZACIÓN RECHAZADA', '<br><strong class="text-danger"><i class="bi bi-x-circle-fill"></i> AUTORIZACIÓN RECHAZADA</strong>', $obsFormateadas);
                @endphp
                {!! nl2br($obsFormateadas) !!}
            </p>
        </div>
    </div>
    @endif
</div>

@if($renta->saldo_pendiente > 0 || $renta->estado == 'activa')

<!-- MODAL: Registro de Pago / Liquidación -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog {{ $renta->autorizacion_aprobada ? 'modal-lg' : '' }} modal-dialog-centered">
        <div class="modal-content border shadow-lg rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            
            <!-- Encabezado del Modal -->
            <div class="modal-header {{ $renta->autorizacion_aprobada ? 'bg-success' : 'bg-info' }} text-white py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-cash-coin fs-4 text-white"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white">
                            {{ $renta->autorizacion_aprobada ? 'Liquidar Renta Aprobada y Registrar Productos' : 'Registrar Nuevo Pago' }}
                        </h6>
                        <small class="text-white-50" style="font-size: 11px;">
                            {{ $renta->autorizacion_aprobada ? 'Confirma la recepción de equipos y procesa el cobro final' : 'Registra abonos directos al saldo pendiente de la cuenta' }}
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('rentas.registrarPago', $renta) }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-md-4">

                    {{-- TABLA DE DEVOLUCIÓN Y FALTANTES: SOLO SI LA RENTA FUE APROBADA --}}
                    @if($renta->autorizacion_aprobada)
                    <div class="card border mb-4 shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                        <div class="card-header bg-warning-subtle text-warning-emphasis border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold small">
                                <i class="bi bi-box-arrow-in-down me-1"></i> Confirmar Entrega de Equipo y Faltantes
                            </span>
                            <span class="badge bg-warning text-dark font-monospace" style="font-size: 10px;">Revisión de Inventario</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height: 220px;">
                                <table class="table table-hover table-sm mb-0 align-middle text-body" style="font-size: 12px;">
                                    <thead class="bg-body-tertiary text-body-secondary border-bottom">
                                        <tr>
                                            <th class="ps-3 py-2">Equipo</th>
                                            <th class="text-center py-2">Pendiente</th>
                                            <th style="width: 90px;" class="text-center py-2">Devueltos</th>
                                            <th style="width: 80px;" class="text-center py-2">Faltantes</th>
                                            <th style="width: 135px;" class="text-center pe-3 py-2">Costo Faltante ($)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($renta->detalles as $detalle)
                                        @php $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta; @endphp
                                        @if($pendiente > 0)
                                        <tr class="fila-detalle-pago border-bottom" data-detalle-id="{{ $detalle->id }}" data-pendiente="{{ $pendiente }}">
                                            <td class="ps-3 fw-semibold text-truncate text-body" style="max-width: 150px;" title="{{ $detalle->equipo->nombre }}">
                                                {{ $detalle->equipo->nombre }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">
                                                    {{ $pendiente }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <input type="number" name="devolver_final[{{ $detalle->id }}]" class="form-control form-control-sm text-center px-1 fw-bold bg-body text-body input-devuelto-pago" min="0" max="{{ $pendiente }}" value="{{ $pendiente }}" oninput="calcularFaltantesPagoModal()">
                                            </td>
                                            <td class="text-center fw-bold text-body-secondary col-faltante-pago">0</td>
                                            <td class="pe-3">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-body-tertiary text-body border-end-0">$</span>
                                                    <input type="number" name="costo_faltante[{{ $detalle->id }}]" class="form-control form-control-sm text-end bg-body text-body input-costo-faltante-pago fw-semibold" step="0.01" min="0" value="0" placeholder="0.00" oninput="calcularCambioPago()" disabled>
                                                </div>
                                            </td>
                                        </tr>
                                        @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div id="alerta-faltantes-equipo-pago" class="alert alert-warning border-0 rounded-0 m-0 py-2 px-3 small d-none" style="font-size: 11px;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                Hay equipos sin devolver. Ingresa el <strong>Costo Faltante ($)</strong> para sumarlo al cobro final.
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- CAPTURA DE PAGO Y RESUMEN FINANCIERO --}}
                    <div class="row g-3">
                        
                        <!-- Columna Izquierda: Entradas de datos -->
                        <div class="col-12 {{ $renta->autorizacion_aprobada ? 'col-md-6' : '' }}">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">
                                    Monto Recibido <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-body-tertiary text-success fw-bold">$</span>
                                    <input type="number" id="inputMontoRecibidoPago" class="form-control form-control-sm bg-body text-body fw-bold fs-6" step="0.01" required oninput="calcularCambioPago()" placeholder="0.00">
                                </div>
                                <input type="hidden" name="monto" id="inputMontoRegistrarPago">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">
                                    Método de Pago <span class="text-danger">*</span>
                                </label>
                                <select name="metodo_pago" id="metodoPagoRegistro" class="form-select form-select-sm bg-body text-body" required onchange="toggleReferencia()">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="transferencia">Transferencia</option>
                                    <option value="tarjeta">Tarjeta</option>
                                </select>
                            </div>

                            <div class="mb-0" id="campoReferencia" style="display: none;">
                                <label class="form-label small fw-semibold text-body">Referencia / Folio</label>
                                <input type="text" name="referencia" id="inputReferencia" class="form-control form-control-sm bg-body text-body" placeholder="N° Transferencia o Voucher">
                            </div>
                        </div>

                        <!-- Columna Derecha: Tarjeta Resumen -->
                        <div class="col-12 {{ $renta->autorizacion_aprobada ? 'col-md-6' : '' }}">
                            <div class="card bg-body-tertiary border rounded-3 p-3 h-100 d-flex flex-column justify-content-between" style="border-color: var(--bs-border-color) !important;">
                                <h6 class="fw-bold text-body border-bottom pb-2 mb-2 small">
                                    <i class="bi bi-calculator me-1 text-primary"></i> RESUMEN DE TRANSACCIÓN
                                </h6>
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-body-secondary small">Saldo Base Pendiente:</span>
                                    <span class="fw-bold text-body">${{ number_format($renta->saldo_pendiente, 2) }}</span>
                                </div>
                                
                                @if($renta->estado == 'activa' && $multaCalculada > 0)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-danger small">Cargos por Retraso:</span>
                                    <span class="fw-bold text-danger">+${{ number_format($multaCalculada, 2) }}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-1">
                                    <span class="text-body-secondary small">Deuda Total Actual:</span>
                                    <span class="fw-bold text-body">${{ number_format($renta->saldo_pendiente + $multaCalculada, 2) }}</span>
                                </div>
                                @else
                                <div class="mb-2"></div>
                                @endif

                                <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-2">
                                    <span class="text-body-secondary small">Monto a Registrar:</span>
                                    <strong id="montoRegistrarText" class="text-primary fs-6">$0.00</strong>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-body-secondary small">Cambio a Regresar:</span>
                                    <strong id="cambioText" class="text-success fs-6">$0.00</strong>
                                </div>

                                <hr class="my-2 border-secondary-subtle">

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-body small">Faltaría por liquidar:</span>
                                    <strong id="faltanteText" class="text-danger fs-5">${{ number_format($renta->saldo_pendiente + ($renta->estado == 'activa' ? $multaCalculada : 0), 2) }}</strong>
                                </div>

                                {{-- Elemento oculto mantenido para compatibilidad estricta con el JS --}}
                                <span id="nuevoSaldo" class="d-none"></span>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Botones de Acción -->
                <div class="modal-footer bg-body-tertiary py-2 px-3 border-top" style="border-color: var(--bs-border-color) !important;">
                    <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm {{ $renta->autorizacion_aprobada ? 'btn-success' : 'btn-info text-white' }} fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $renta->autorizacion_aprobada ? 'Liquidar y Finalizar' : 'Confirmar Pago' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

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
                                    <span id="nueva_fecha">{{ $renta->fecha_fin->copy()->addDay()->format('d/m/Y') }}</span>
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
                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <strong class="d-block mb-1"><i class="bi bi-calculator me-1"></i> Resumen de la Cuenta:</strong>
                        Total original: ${{ number_format($renta->total, 2) }} | 
                        Depósito: ${{ number_format($renta->deposito ?? 0, 2) }} | 
                        Pagado: ${{ number_format($renta->pagos->sum('monto'), 2) }}<br>
                        <strong class="text-danger fs-6">Saldo Base a Pagar: $<span id="saldo_base_txt">{{ number_format($renta->saldo_pendiente, 2) }}</span></strong>
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
                            ¿Generar otro cargo extra? (Daños, reparaciones)
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

<!-- Devolución Parcial -->
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

<!-- MODAL: Visualizador de Documentos -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border shadow-lg rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            
            <!-- Encabezado del Modal -->
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="modalVerDocumentoTitulo">Visualizador de Documento</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Modal (Iframe Visor) -->
            <div class="modal-body p-0 bg-secondary bg-opacity-10 position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-body-secondary mt-2">Cargando documento...</div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 78vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>

            <!-- Pie del Modal -->
            <div class="modal-footer bg-body-tertiary py-2 px-3 border-top d-flex justify-content-between" style="border-color: var(--bs-border-color) !important;">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
@endif

@php
    $costoDiarioPendiente = $renta->detalles->sum(function($d) {
        $pendiente = $d->cantidad - $d->cantidad_devuelta;
        return $pendiente > 0 ? ($pendiente * $d->precio_dia) : 0;
    });
@endphp

<!-- Contenedor seguro para variables PHP hacia JavaScript -->
<div id="renta-js-data"
     class="d-none"
     data-saldo-pendiente="{{ $renta->saldo_pendiente }}"
     data-multa-calculada="{{ $renta->estado == 'activa' ? $multaCalculada : 0 }}"
     data-fecha-fin="{{ $renta->fecha_fin->format('Y-m-d') }}"
     data-costo-diario-pendiente="{{ $costoDiarioPendiente }}"
     data-facturar="{{ $renta->facturar ? '1' : '0' }}"
     data-es-gerente="{{ (auth()->user()->isAdmin() || auth()->user()->isGerente()) ? '1' : '0' }}"
     data-autorizacion-aprobada="{{ $renta->autorizacion_aprobada ? '1' : '0' }}">
</div>

<script>
    const rentaData = document.getElementById('renta-js-data') ? document.getElementById('renta-js-data').dataset : {};
    const saldoBaseOriginal = parseFloat(rentaData.saldoPendiente) || 0;
    const fechaFinOriginal = rentaData.fechaFin || '';
    const costoDiarioPendiente = parseFloat(rentaData.costoDiarioPendiente) || 0;

    // Elementos de Modal Finalizar
    const inputMulta = document.getElementById('multa_retraso');
    const checkCargoManual = document.getElementById('check_cargo_manual');
    const seccionCargoManual = document.getElementById('seccion_cargo_manual');
    const inputCargoManual = document.getElementById('cargo_manual');
    const motivoCargoManual = document.getElementById('motivo_cargo_manual');
    const inputMontoFinalHidden = document.getElementById('montoPagoFinal');
    const labelMaximo = document.getElementById('montoMaximoLabel');

    // ==========================================
    // LÓGICA DE MODAL REGISTRAR PAGO / LIQUIDACIÓN
    // ==========================================
    function calcularFaltantesPagoModal() {
        let hayFaltantes = false;
        const filas = document.querySelectorAll('.fila-detalle-pago');

        filas.forEach(fila => {
            const pendiente = parseInt(fila.dataset.pendiente) || 0;
            const inputDevuelto = fila.querySelector('.input-devuelto-pago');
            const colFaltante = fila.querySelector('.col-faltante-pago');
            const inputCosto = fila.querySelector('.input-costo-faltante-pago');

            if (!inputDevuelto) return;

            let devuelto = parseInt(inputDevuelto.value);
            if (isNaN(devuelto) || devuelto < 0) devuelto = 0;
            if (devuelto > pendiente) {
                devuelto = pendiente;
                inputDevuelto.value = pendiente;
            }

            const faltante = pendiente - devuelto;
            if (colFaltante) colFaltante.textContent = faltante;

            if (faltante > 0) {
                hayFaltantes = true;
                if (colFaltante) colFaltante.className = 'text-center fw-bold text-danger col-faltante-pago';
                if (inputCosto) inputCosto.removeAttribute('disabled');
            } else {
                if (colFaltante) colFaltante.className = 'text-center fw-bold text-secondary col-faltante-pago';
                if (inputCosto) {
                    inputCosto.value = 0;
                    inputCosto.setAttribute('disabled', 'disabled');
                }
            }
        });

        const alerta = document.getElementById('alerta-faltantes-equipo-pago');
        if (alerta) {
            if (hayFaltantes) {
                alerta.classList.remove('d-none');
            } else {
                alerta.classList.add('d-none');
            }
        }

        calcularCambioPago();
    }

    function calcularCambioPago() {
        const elMontoRecibido = document.getElementById('inputMontoRecibidoPago');
        if (!elMontoRecibido) return;

        const saldoPendienteOriginal = parseFloat(rentaData.saldoPendiente) || 0;
        const multaCalculada = parseFloat(rentaData.multaCalculada) || 0; // NUEVO: Extraemos la multa del data-set

        // Sumar costos por faltantes si la renta fue autorizada para devolución/faltantes
        let totalFaltantes = 0;
        document.querySelectorAll('.input-costo-faltante-pago').forEach(inp => {
            if (!inp.disabled) {
                totalFaltantes += parseFloat(inp.value) || 0;
            }
        });

        // NUEVO: Sumar la multa calculada al saldo total
        const saldoTotal = saldoPendienteOriginal + multaCalculada + totalFaltantes;
        const montoRecibido = parseFloat(elMontoRecibido.value) || 0;

        let montoARegistrar = 0,
            cambio = 0,
            faltante = 0;

        if (montoRecibido >= saldoTotal) {
            montoARegistrar = saldoTotal;
            cambio = montoRecibido - saldoTotal;
        } else {
            montoARegistrar = montoRecibido;
            faltante = saldoTotal - montoRecibido;
        }

        const inputRegistrar = document.getElementById('inputMontoRegistrarPago');
        const txtRegistrar = document.getElementById('montoRegistrarText');
        const txtCambio = document.getElementById('cambioText');
        const txtFaltante = document.getElementById('faltanteText');
        const elNuevoSaldo = document.getElementById('nuevoSaldo');

        if (inputRegistrar) inputRegistrar.value = montoARegistrar.toFixed(2);
        if (txtRegistrar) txtRegistrar.textContent = '$' + montoARegistrar.toFixed(2);
        if (txtCambio) txtCambio.textContent = '$' + cambio.toFixed(2);
        if (txtFaltante) txtFaltante.textContent = '$' + faltante.toFixed(2);

        if (elNuevoSaldo) {
            elNuevoSaldo.textContent = '$' + faltante.toFixed(2);
            elNuevoSaldo.className = (faltante === 0 && montoRecibido > 0) ? 'text-success fw-bold' : 'text-primary fw-bold';
        }
    }

    function toggleReferencia() {
        const selectMetodo = document.getElementById('metodoPagoRegistro');
        const inputReferencia = document.getElementById('inputReferencia');
        const campoRef = document.getElementById('campoReferencia');
        if (!selectMetodo || !campoRef) return;

        const metodo = selectMetodo.value;
        if (metodo === 'transferencia' || metodo === 'tarjeta') {
            campoRef.style.display = 'block';
            if (inputReferencia) inputReferencia.setAttribute('required', 'required');
        } else {
            campoRef.style.display = 'none';
            if (inputReferencia) {
                inputReferencia.removeAttribute('required');
                inputReferencia.value = '';
            }
        }
    }

    // ==========================================
    // LÓGICA DE MODAL FINALIZAR RENTA
    // ==========================================
    function toggleCargoManual() {
        if (checkCargoManual && checkCargoManual.checked) {
            if (seccionCargoManual) seccionCargoManual.classList.remove('d-none');
        } else {
            if (seccionCargoManual) seccionCargoManual.classList.add('d-none');
            if (inputCargoManual) inputCargoManual.value = 0;
            if (motivoCargoManual) motivoCargoManual.value = '';
        }
        recalcularFinalizacion();
    }

    function toggleReferenciaFinal() {
        const selectMetodo = document.getElementById('metodoPagoFinalRegistro');
        const inputReferencia = document.getElementById('inputReferenciaFinal');
        const campoRef = document.getElementById('campoReferenciaFinal');
        if (!selectMetodo || !campoRef) return;

        const metodo = selectMetodo.value;
        if (metodo === 'transferencia' || metodo === 'tarjeta') {
            campoRef.style.display = 'block';
            if (inputReferencia) inputReferencia.setAttribute('required', 'required');
        } else {
            campoRef.style.display = 'none';
            if (inputReferencia) {
                inputReferencia.removeAttribute('required');
                inputReferencia.value = '';
            }
        }
    }

    function recalcularFinalizacion() {
        let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
        let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
        let deudaReal = saldoBaseOriginal + multa + manual;

        if (labelMaximo) labelMaximo.textContent = 'Deuda Total: $' + deudaReal.toFixed(2);

        const elRecibido = document.getElementById('montoRecibidoFinal');
        let montoRecibido = parseFloat(elRecibido ? elRecibido.value : 0) || 0;
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

        if (inputMontoFinalHidden) inputMontoFinalHidden.value = montoACobrar.toFixed(2);
        
        const txtCobrar = document.getElementById('montoCobrarFinalText');
        const txtCambio = document.getElementById('cambioFinalText');
        const txtFaltante = document.getElementById('faltanteFinalText');

        if (txtCobrar) txtCobrar.textContent = '$' + montoACobrar.toFixed(2);
        if (txtCambio) txtCambio.textContent = '$' + cambio.toFixed(2);
        if (txtFaltante) txtFaltante.textContent = '$' + faltante.toFixed(2);
    }

    function validarCargo() {
        let manual = parseFloat(inputCargoManual ? inputCargoManual.value : 0) || 0;
        let motivo = motivoCargoManual ? motivoCargoManual.value.trim() : '';

        if (checkCargoManual && checkCargoManual.checked && manual > 0 && motivo === '') {
            alert('Ha agregado un cargo extra manual. Especifique el motivo.');
            if (motivoCargoManual) motivoCargoManual.focus();
            return false;
        }

        let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
        let deudaReal = saldoBaseOriginal + multa + manual;

        const elRecibido = document.getElementById('montoRecibidoFinal');
        let montoRecibido = parseFloat(elRecibido ? elRecibido.value : 0) || 0;

        if (montoRecibido < deudaReal) {
            const esGerenteAdmin = rentaData.esGerente === '1';
            const estaAutorizado = rentaData.autorizacionAprobada === '1';
            
            if (!esGerenteAdmin && !estaAutorizado) {
                return confirm('El monto recibido ($' + montoRecibido.toFixed(2) + ') NO cubre la deuda total ($' + deudaReal.toFixed(2) + ').\n\n¿Deseas enviar una SOLICITUD DE AUTORIZACIÓN al gerente para poder finalizar la renta con adeudo?');
            } else {
                return confirm('El monto recibido ($' + montoRecibido.toFixed(2) + ') NO cubre la deuda total ($' + deudaReal.toFixed(2) + ').\n\n¿Deseas FINALIZAR la renta dejando este adeudo pendiente en la cuenta?');
            }
        }
        
        return true;
    }

    // ==========================================
    // LÓGICA DE MODAL AMPLIAR DÍAS
    // ==========================================
    function calcularAmpliacion() {
        const diasInput = document.getElementById('dias_extra');
        const dias = parseInt(diasInput ? diasInput.value : 0) || 0;
        
        const aplicaIva = rentaData.facturar === '1';

        const costoExtra = dias * costoDiarioPendiente;
        const ivaExtra = aplicaIva ? (costoExtra * 0.16) : 0;
        const totalExtra = costoExtra + ivaExtra;

        if (document.getElementById('res_costo')) {
            document.getElementById('res_costo').textContent = '$' + costoExtra.toFixed(2);
        }
        if (document.getElementById('res_iva_ext')) {
            document.getElementById('res_iva_ext').textContent = '$' + ivaExtra.toFixed(2);
        }
        if (document.getElementById('res_total_ext')) {
            document.getElementById('res_total_ext').textContent = '$' + totalExtra.toFixed(2);
        }

        const elNuevaFecha = document.getElementById('nueva_fecha');
        if (elNuevaFecha && fechaFinOriginal) {
            let partes = fechaFinOriginal.split('-');
            let fecha = new Date(partes[0], partes[1] - 1, partes[2]);

            if (dias > 0) {
                fecha.setDate(fecha.getDate() + dias);
            }

            let dia = String(fecha.getDate()).padStart(2, '0');
            let mes = String(fecha.getMonth() + 1).padStart(2, '0');
            let anio = fecha.getFullYear();

            elNuevaFecha.textContent = `${dia}/${mes}/${anio}`;
        }
    }

    // ==========================================
    // INICIALIZACIÓN Y EVENT LISTENERS DE MODALES
    // ==========================================
    document.addEventListener('DOMContentLoaded', function() {
        ['modalPago', 'modalFinalizar', 'modalAmpliar'].forEach(id => {
            let modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('show.bs.modal', function() {
                    if (id === 'modalPago') {
                        toggleReferencia();
                        const elRecibido = document.getElementById('inputMontoRecibidoPago');
                        if (elRecibido) elRecibido.value = '';
                        calcularFaltantesPagoModal();
                    }
                    if (id === 'modalFinalizar') {
                        toggleReferenciaFinal();
                        const elRecibidoFinal = document.getElementById('montoRecibidoFinal');
                        if (elRecibidoFinal) elRecibidoFinal.value = '';
                        recalcularFinalizacion();
                    }
                    if (id === 'modalAmpliar') {
                        const diasInput = document.getElementById('dias_extra');
                        if (diasInput) diasInput.value = '';
                        calcularAmpliacion();
                    }
                });
            }
        });

        recalcularFinalizacion();
    });

    // Función para abrir cualquier documento en el modal visor
function verDocumento(url, titulo) {
    const tituloEl = document.getElementById('modalVerDocumentoTitulo');
    const iframeEl = document.getElementById('iframeDocumento');
    const loaderEl = document.getElementById('loaderDocumento');
    const btnDescargar = document.getElementById('btnDescargarDocumento');

    if (tituloEl) tituloEl.textContent = titulo;
    if (btnDescargar) btnDescargar.href = url;
    
    if (loaderEl) loaderEl.classList.remove('d-none');
    if (iframeEl) iframeEl.src = url;

    const modalEl = document.getElementById('modalVerDocumento');
    if (modalEl) {
        const modalInstance = new bootstrap.Modal(modalEl);
        modalInstance.show();
    }
}

    // Limpiar el iframe al cerrar el modal para no consumir memoria en segundo plano
    document.addEventListener('DOMContentLoaded', function() {
        const modalVerDoc = document.getElementById('modalVerDocumento');
        if (modalVerDoc) {
            modalVerDoc.addEventListener('hidden.bs.modal', function () {
                const iframeEl = document.getElementById('iframeDocumento');
                if (iframeEl) iframeEl.src = '';
            });
        }
    });
</script>
@endsection