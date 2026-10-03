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

@php
    // Cálculo centralizado del Saldo Real tomando en cuenta multas dinámicas
    $saldoPendienteReal = $renta->estado == 'cancelada' ? 0 : ($renta->saldo_pendiente + ($renta->estado == 'activa' ? $multaCalculada : 0));
    // Redondear para evitar decimales residuales negativos por cálculo
    if ($saldoPendienteReal < 0.01) {
        $saldoPendienteReal = 0;
    }
@endphp

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
            @if($saldoPendienteReal > 0 || $renta->autorizacion_aprobada || ($renta->estado == 'activa' && !$renta->autorizacion_solicitada))
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

                <button type="button" class="btn btn-outline-danger btn-sm rounded-3" data-bs-toggle="modal" data-bs-target="#modalCancelarRenta">
                    <i class="bi bi-x-octagon me-1"></i> Cancelar
                </button>
            @endif

        </div>
    </div>
</div>

@if($renta->autorizacion_solicitada)
    @if(str_starts_with($renta->motivo_autorizacion, '[CANCELACION]'))
        <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4 shadow-sm" role="alert">
            <i class="bi bi-shield-lock-fill me-2 fs-5"></i>
            <strong>Cancelación en Revisión:</strong> Se ha solicitado autorización al gerente para cancelar totalmente este contrato. Acciones bloqueadas.
        </div>
    @else
        <div class="alert alert-warning alert-dismissible fade show rounded-3 mb-4 border-warning shadow-sm" role="alert">
            <i class="bi bi-shield-lock-fill me-2 text-warning fs-5"></i>
            <strong>Cuenta en Revisión:</strong> Se ha solicitado autorización al gerente para finalizar esta renta con un adeudo. Acciones bloqueadas.
        </div>
    @endif
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
                @if($saldoPendienteReal > 0)
                    <!-- ALERTA DE COBRO PENDIENTE -->
                    <div class="alert alert-danger mt-3 mb-0 py-2 px-3 small shadow-sm" style="border-left: 4px solid #dc3545; background-color: #f8d7da; color: #842029;">
                        <i class="bi bi-exclamation-triangle-fill text-danger me-1"></i>
                        <strong>¡Contrato Vencido!</strong><br>
                        {{ $diasRetraso }} día(s) de retraso con deuda activa.
                        @if($multaCalculada > 0)
                            Multa pendiente: <strong>${{ number_format($multaCalculada, 2) }}</strong>
                        @endif
                    </div>
                @else
                    <!-- ALERTA VERDE DE LIQUIDACIÓN EXITOSA -->
                    <div class="alert alert-success mt-3 mb-0 py-2 px-3 small shadow-sm" style="border-left: 4px solid #198754; background-color: #d1e7dd; color: #0f5132;">
                        <i class="bi bi-check-circle-fill text-success me-1 fs-6 align-middle"></i>
                        <strong class="align-middle">¡Deuda Liquidada!</strong><br>
                        Los costos por los {{ $diasRetraso }} día(s) de retraso han sido pagados en su totalidad. Por favor, solicita la devolución del equipo o finaliza la cuenta.
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($renta->obra_id && $renta->obra)
    <div class="col-12">
        <div class="info-card" style="border-left-color: #6f42c1;">
            <h6 style="color: #6f42c1;"><i class="bi bi-building me-1"></i> OBRA / PROYECTO</h6>
            <div class="text-body small">
                <strong>Nombre:</strong> {{ $renta->obra->nombre }}<br>
                <strong>Dirección:</strong> {{ $renta->obra->direccion }}@if($renta->obra->colonia), {{ $renta->obra->colonia }}@endif<br>
                <strong>Ciudad:</strong> {{ $renta->obra->ciudad ?? 'N/A' }}, {{ $renta->obra->estado ?? 'N/A' }}@if($renta->obra->codigo_postal) (C.P. {{ $renta->obra->codigo_postal }})@endif<br>
                <strong>Contacto:</strong> {{ $renta->obra->contacto_obra ?? 'No especificado' }}<br>
                <strong>Teléfono:</strong> {{ $renta->obra->telefono_obra ?? 'No especificado' }}
                @if($renta->obra->observaciones)
                    <br><strong>Notas:</strong> {{ $renta->obra->observaciones }}
                @endif
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
                            <th>Tarifa</th>
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
                            <td>${{ number_format($detalle->precio_dia, 2) }} <small class="text-secondary">/ {{ $detalle->etiqueta_tarifa }}</small></td>
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
                            <th class="text-center" style="width: 45px;"><i class="bi bi-printer"></i></th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(($renta->deposito ?? 0) > 0)
                        @php
                            // Buscamos el registro formal del pago del depósito
                            $pagoDeposito = $renta->pagos->where('tipo', 'deposito')->first();
                        @endphp
                        <tr>
                            <td class="text-secondary">
                                {{ $pagoDeposito ? $pagoDeposito->fecha_pago->format('d/m/Y') : $renta->fecha_inicio->format('d/m/Y') }}
                            </td>
                            <td><span class="badge bg-secondary">Depósito</span></td>
                            <td class="text-secondary small {{ $pagoDeposito && $pagoDeposito->desglose_mixto ? '' : 'text-truncate' }}"
                                style="max-width: 220px;"
                                title="{{ $pagoDeposito->referencia ?? 'Depósito Inicial' }}">
                            
                                @if($pagoDeposito)
                                {{ $pagoDeposito->referencia ?? 'Depósito Inicial' }}
                                @else
                                Garantía inicial
                                @endif
                            
                            </td>
                            <td class="text-end fw-bold text-primary">-${{ number_format($renta->deposito, 2) }}</td>
                            <td class="text-center">
                                @if($pagoDeposito)
                                <button type="button" class="btn btn-sm btn-outline-secondary border rounded-3 shadow-sm px-2 py-1" onclick="reimprimirTicket('{{ route('rentas.ticketPago', $pagoDeposito->id) }}')" title="Reimprimir Ticket de Depósito">
                                    <i class="bi bi-printer"></i>
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endif

                        @if(($renta->dias_ampliados ?? 0) > 0)
                        @php
                            $costoDiarioPendienteMov = $renta->detalles->sum(function($d) {
                                return $d->costoDiarioPendiente();
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
                            <td></td>
                        </tr>
                        @endif

                        @foreach($renta->pagos->where('tipo', '!=', 'deposito') as $pago)
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
                            <td class="text-secondary small {{ $pago->desglose_mixto ? '' : 'text-truncate' }}" style="max-width: 220px;" title="{{ $pago->referencia ?? $pago->observaciones }}">
                                {{ $pago->referencia ?? $pago->observaciones ?? 'Abono a cuenta' }}
                            </td>
                            <td class="text-end fw-bold text-success">-${{ number_format($pago->monto, 2) }}</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary border rounded-3 shadow-sm px-2 py-1" onclick="reimprimirTicket('{{ route('rentas.ticketPago', $pago->id) }}')" title="Reimprimir Ticket">
                                    <i class="bi bi-printer"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach

                        @if(isset($renta->cargos_extra) && $renta->cargos_extra > 0)
                        <tr>
                            <td class="text-secondary">{{ $renta->fecha_devolucion ? $renta->fecha_devolucion->format('d/m/Y') : 'N/A' }}</td>
                            <td><span class="badge bg-danger">Cargos Extra</span></td>
                            <td class="text-secondary small">Multas, Daños o Faltantes</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($renta->cargos_extra, 2) }}</td>
                            <td></td>
                        </tr>
                        @endif

                        @if($renta->estado == 'activa' && $multaCalculada > 0)
                        <tr class="bg-danger bg-opacity-10">
                            <td class="text-danger fw-bold">Actual</td>
                            <td><span class="badge bg-danger">Retraso</span></td>
                            <td class="text-danger small">{{ $diasRetraso }} día(s) vencido(s)</td>
                            <td class="text-end fw-bold text-danger">+${{ number_format($multaCalculada, 2) }}</td>
                            <td></td>
                        </tr>
                        @endif

                        @if(($renta->deposito ?? 0) <= 0 && ($renta->dias_ampliados ?? 0) <= 0 && $renta->pagos->count() == 0 && (!isset($renta->cargos_extra) || $renta->cargos_extra <= 0) && $multaCalculada <=0)
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-4">No hay movimientos financieros registrados.</td>
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

                    @if(($renta->descuento ?? 0) > 0)
                    <tr class="border-bottom">
                        <td class="text-success fw-bold">
                            Descuento:
                            @if($renta->motivo_descuento)
                                <div class="fw-normal text-secondary" style="font-size: 11px;">{{ $renta->motivo_descuento }}</div>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-success">-${{ number_format($renta->descuento, 2) }}</td>
                    </tr>
                    @endif

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
                    $totalAbonado = $renta->pagos->where('tipo', '!=', 'deposito')->sum('monto') + ($renta->deposito ?? 0);
                    @endphp
                    <tr class="border-bottom">
                        <td class="text-secondary fw-bold">Total Abonado (Inc. Depósito):</td>
                        <td class="text-end fw-bold text-primary">-${{ number_format($totalAbonado, 2) }}</td>
                    </tr>

                    <tr>
                        <td class="text-body fw-bold py-3">SALDO PENDIENTE ACTUAL:</td>
                        <td class="text-end fw-bold fs-4 {{ $saldoPendienteReal > 0 ? 'text-danger' : 'text-success' }} py-3">
                            @if($saldoPendienteReal <= 0)
                                <span class="badge bg-success text-white fs-6 py-1 px-2 rounded-pill align-middle me-2"><i class="bi bi-check2-all"></i> Liquidado</span>$0.00
                            @else
                                ${{ number_format($saldoPendienteReal, 2) }}
                            @endif
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

<!-- ======================= INICIO SECCIÓN MODALES ======================= -->

@if($renta->saldo_pendiente > 0 || $renta->estado == 'activa')
<!-- MODAL: Registro de Pago / Liquidación -->
<div class="modal fade" id="modalPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            
            <div class="modal-header {{ $renta->autorizacion_aprobada ? 'bg-success' : 'bg-info' }} text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; background: rgba(255, 255, 255, 0.2);">
                        <i class="bi bi-cash-coin fs-4 text-white"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white">
                            {{ $renta->autorizacion_aprobada ? 'Liquidar Renta Aprobada y Registrar Productos' : 'Registrar Nuevo Pago' }}
                        </h5>
                        <small class="text-white-50" style="font-size: 12px;">
                            {{ $renta->autorizacion_aprobada ? 'Confirma la recepción de equipos y procesa el cobro final' : 'Registra abonos directos al saldo pendiente de la cuenta' }}
                        </small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- EVENTO ONSUBMIT PARA PROTEGER EL CÁLCULO DE EXTENSIONES DEL NAVEGADOR -->
            <form action="{{ route('rentas.registrarPago', $renta) }}" method="POST" onsubmit="return prepararEnvioPago();">
                @csrf
                <div class="modal-body p-4">
                    @unless($cajaAbierta)
                        <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-lock-fill mt-1"></i>
                            <div>
                                <strong>Caja cerrada.</strong> No puedes registrar cobros hasta abrir tu caja en el Punto de Venta.
                                Solo podrás procesar movimientos con monto $0.
                                <a href="{{ route('puntoventa.index') }}" class="alert-link ms-1">Abrir caja <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    @endunless

                    <div class="row g-4">
                        
                        <!-- ================= COLUMNA IZQUIERDA ================= -->
                        <div class="col-12 col-lg-7">
                            
                            <!-- 1. Retorno de Equipo -->
                            <div class="card border mb-4 shadow-sm rounded-3 overflow-hidden" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                                <div class="card-header bg-secondary bg-opacity-10 border-bottom py-2 px-3 d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small text-body">
                                        <i class="bi bi-box-arrow-in-down me-1"></i> Retorno de Equipo (Opcional)
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 220px;">
                                        <table class="table table-hover table-sm mb-0 align-middle text-body" style="font-size: 12px;">
                                            <thead class="bg-body-tertiary text-body-secondary border-bottom">
                                                <tr>
                                                    <th class="ps-3 py-2">Equipo</th>
                                                    <th class="text-center py-2">Pendiente</th>
                                                    <th style="width: 100px;" class="text-center py-2">A Devolver</th>
                                                    <th style="width: 90px;" class="text-center pe-3 py-2">Faltantes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($renta->detalles as $detalle)
                                                @php $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta; @endphp
                                                @if($pendiente > 0)
                                                <tr class="fila-detalle-pago border-bottom" data-detalle-id="{{ $detalle->id }}" data-pendiente="{{ $pendiente }}">
                                                    <td class="ps-3 fw-semibold text-truncate text-body" style="max-width: 130px;" title="{{ $detalle->equipo->nombre }}">
                                                        {{ $detalle->equipo->nombre }}
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1">{{ $pendiente }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" name="devolver_final[{{ $detalle->id }}]" class="form-control form-control-sm text-center px-1 fw-bold bg-body text-body input-devuelto-pago" min="0" max="{{ $pendiente }}" value="" placeholder="0" oninput="calcularFaltantesPagoModal()">
                                                    </td>
                                                    <td class="text-center pe-3 fw-bold text-body-secondary col-faltante-pago">0</td>
                                                </tr>
                                                @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Monto y Método de Pago (Anidados en una fila) -->
                            <div class="row g-3">
                                @include('rentas.partials.pago_mixto', ['id' => 'pago'])
                                <div class="col-12 col-md-6">
                                    <div class="mb-3 mb-md-0">
                                        <label class="form-label small fw-semibold text-body">Monto Recibido <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body-tertiary text-success fw-bold">$</span>
                                            <input type="number" id="inputMontoRecibidoPago" class="form-control form-control-sm bg-body text-body fw-bold fs-6" step="0.01" required oninput="calcularCambioPago()" placeholder="0.00">
                                        </div>
                                        <input type="hidden" name="monto" id="inputMontoRegistrarPago">
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-body">Método de Pago <span class="text-danger">*</span></label>
                                        <select name="metodo_pago" id="metodoPagoRegistro" class="form-select form-select-sm bg-body text-body" required onchange="toggleReferencia()">
                                            <option value="efectivo">Efectivo</option>
                                            <option value="transferencia">Transferencia</option>
                                            <option value="tarjeta">Tarjeta</option>
                                            <option value="mixto">Pago Mixto</option>
                                        </select>
                                    </div>

                                    <div class="mb-0" id="campoReferencia" style="display: none;">
                                        <label class="form-label small fw-semibold text-body">Referencia / Folio</label>
                                        <input type="text" name="referencia" id="inputReferencia" class="form-control form-control-sm bg-body text-body" placeholder="N° Transferencia o Voucher">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- ================= COLUMNA DERECHA ================= -->
                        <div class="col-12 col-lg-5">
                            <div class="card bg-body-tertiary border rounded-3 p-3 h-100 d-flex flex-column justify-content-between" style="border-color: var(--bs-border-color) !important;">
                                <div>
                                    <h6 class="fw-bold text-body border-bottom pb-2 mb-3 small">
                                        <i class="bi bi-calculator me-1 text-primary"></i> RESUMEN
                                    </h6>
                                    
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-body-secondary small">Saldo Base:</span>
                                        <span class="fw-bold text-body">${{ number_format($renta->saldo_pendiente, 2) }}</span>
                                    </div>
                                    
                                    @if($renta->estado == 'activa' && $multaCalculada > 0)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-danger small">Cargos por Retraso:</span>
                                        <span class="fw-bold text-danger">+${{ number_format($multaCalculada, 2) }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-3 border-top pt-2">
                                        <span class="text-body-secondary small">Deuda Actual:</span>
                                        <span class="fw-bold text-body">${{ number_format($renta->saldo_pendiente + $multaCalculada, 2) }}</span>
                                    </div>
                                    @else
                                    <div class="mb-3"></div>
                                    @endif

                                    <div class="d-flex justify-content-between align-items-center mb-2 border-top pt-3">
                                        <span class="text-body-secondary small">A Registrar:</span>
                                        <strong id="montoRegistrarText" class="text-primary fs-6">$0.00</strong>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-body-secondary small">Cambio:</span>
                                        <strong id="cambioText" class="text-success fs-6">$0.00</strong>
                                    </div>
                                </div>

                                <div>
                                    <hr class="my-3 border-secondary-subtle">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-body small">Falta liquidar:</span>
                                        <strong id="faltanteText" class="text-danger fs-5">${{ number_format(max(0, $saldoPendienteReal), 2) }}</strong>
                                    </div>
                                    <span id="nuevoSaldo" class="d-none"></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn {{ $renta->autorizacion_aprobada ? 'btn-success' : 'btn-info text-white' }} fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check-circle me-1"></i>
                        {{ $renta->autorizacion_aprobada ? 'Liquidar' : 'Confirmar Pago' }}
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
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-plus-circle me-2"></i>Ampliar Días de Renta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.ampliarDias', $renta) }}" method="POST" onsubmit="return validarMixtoAmpliar();">
                @csrf
                <div class="modal-body p-4">
                    @unless($cajaAbierta)
                        <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-lock-fill mt-1"></i>
                            <div>
                                <strong>Caja cerrada.</strong> No puedes registrar cobros hasta abrir tu caja en el Punto de Venta.
                                Solo podrás procesar movimientos con monto $0.
                                <a href="{{ route('puntoventa.index') }}" class="alert-link ms-1">Abrir caja <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    @endunless

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">Días adicionales <span class="text-danger">*</span></label>
                                <input type="number" name="dias_extra" id="dias_extra" class="form-control form-control-sm bg-body" min="1" required oninput="calcularAmpliacion()" placeholder="Ej: 3">
                            </div>
                            <div class="row g-2 mb-3">
                                @include('rentas.partials.pago_mixto', ['id' => 'ampliar'])
                                <div class="col-12 col-md-4">
                                    <label class="form-label small fw-semibold text-body">Abono a cuenta</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body text-secondary">$</span>
                                        <input type="number" name="abono" id="abonoAmpliar" class="form-control form-control-sm bg-body" step="0.01" placeholder="0.00">
                                    </div>
                                </div>
                                <div class="col-12 col-md-4" id="divMetodoAmpliar" style="display: none;">
                                    <label class="form-label small fw-semibold text-body">Método <span class="text-danger">*</span></label>
                                    <select name="metodo_pago" id="metodoPagoAmpliar" class="form-select form-select-sm bg-body" onchange="toggleReferenciaAmpliar()">
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="tarjeta">Tarjeta</option>
                                        <option value="mixto">Pago Mixto</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4" id="divReferenciaAmpliar" style="display: none;">
                                    <label class="form-label small fw-semibold text-body">Ref. / Folio <span class="text-danger">*</span></label>
                                    <input type="text" name="referencia" id="inputReferenciaAmpliar" class="form-control form-control-sm bg-body" placeholder="Ref. pago">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-body">¿Se devuelve algún equipo hoy?</label>
                                <div class="table-responsive border rounded bg-body-tertiary">
                                    <table class="table table-sm mb-0 text-body" style="font-size:12px;">
                                        <thead>
                                            <tr>
                                                <th class="ps-2">Equipo</th>
                                                <th class="text-center">Pendiente</th>
                                                <th class="text-center pe-2">A devolver</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($renta->detalles as $detalle)
                                            @php $pendiente = $detalle->cantidad - $detalle->cantidad_devuelta; @endphp
                                            @if($pendiente > 0)
                                            <tr>
                                                <td class="ps-2 align-middle">{{ $detalle->equipo->nombre }}</td>
                                                <td class="text-center align-middle">{{ $pendiente }}</td>
                                                <td class="text-center pe-2">
                                                    <input type="number" name="devolver_final[{{ $detalle->id }}]" class="form-control form-control-sm text-center input-devolver-ampliar" data-precio="{{ $detalle->esPorM2() ? 0 : $detalle->precio_dia }}" min="0" max="{{ $pendiente }}" value="0" oninput="calcularAmpliacion()">
                                                </td>
                                            </tr>
                                            @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
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
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold rounded-3 px-4"><i class="bi bi-check-lg me-1"></i> Procesar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Finalizar Renta -->
<div class="modal fade" id="modalFinalizar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-success text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-check-circle-fill me-2"></i>Finalizar Renta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.finalizarConPago', $renta) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    @unless($cajaAbierta)
                        <div class="alert alert-danger py-2 px-3 small mb-3 d-flex align-items-start gap-2">
                            <i class="bi bi-lock-fill mt-1"></i>
                            <div>
                                <strong>Caja cerrada.</strong> No puedes registrar cobros hasta abrir tu caja en el Punto de Venta.
                                Solo podrás procesar movimientos con monto $0.
                                <a href="{{ route('puntoventa.index') }}" class="alert-link ms-1">Abrir caja <i class="bi bi-arrow-right"></i></a>
                            </div>
                        </div>
                    @endunless

                    <div class="alert alert-info py-2 px-3 small mb-3">
                        <strong class="d-block mb-1"><i class="bi bi-calculator me-1"></i> Resumen de la Cuenta:</strong>
                        Total original: ${{ number_format($renta->total, 2) }} | 
                        Depósito: ${{ number_format($renta->deposito ?? 0, 2) }} | 
                        Pagado: ${{ number_format($renta->pagos->sum('monto'), 2) }}<br>
                        <strong class="text-danger fs-6">Saldo Base Original: $<span id="saldo_base_txt">{{ number_format($renta->saldo_pendiente, 2) }}</span></strong>
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
                            <small class="text-secondary d-block" id="montoMaximoLabel" style="font-size: 11px;">Deuda Total: ${{ number_format(max(0, $saldoPendienteReal), 2) }}</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Método de pago <span class="text-danger">*</span></label>
                            <select name="metodo_pago_final" id="metodoPagoFinalRegistro" class="form-select form-select-sm bg-body" required onchange="toggleReferenciaFinal()">
                                <option value="efectivo">Efectivo</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="mixto">Pago Mixto</option>
                            </select>
                        </div>
                        <div class="col-12" id="campoReferenciaFinal" style="display: none;">
                            <label class="form-label small fw-semibold text-body">Referencia</label>
                            <input type="text" name="referencia_final" id="inputReferenciaFinal" class="form-control form-control-sm bg-body">
                        </div>
                        <div class="col-12">
                            @include('rentas.partials.pago_mixto', ['id' => 'final'])
                        </div>
                        <div class="col-12 mt-2">
                            <div class="alert alert-secondary py-2 px-3 small mb-0">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Deuda total a liquidar:</span>
                                    <strong id="montoCobrarFinalText" class="text-primary">${{ number_format(max(0, $saldoPendienteReal), 2) }}</strong>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cambio a regresar:</span>
                                    <strong id="cambioFinalText" class="text-success">$0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Faltaría por liquidar:</span>
                                    <strong id="faltanteFinalText" class="text-danger">${{ number_format(max(0, $saldoPendienteReal), 2) }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success fw-bold rounded-3 px-4" onclick="return validarCargo()"><i class="bi bi-check-lg me-1"></i> Finalizar Contrato</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Cancelar Renta -->
<div class="modal fade" id="modalCancelarRenta" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-danger text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-x-octagon-fill me-2"></i>Cancelar Renta</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.cancelar', $renta) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-danger py-2 px-3 small mb-3 border-danger shadow-sm bg-danger bg-opacity-10 text-danger">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Atención:</strong> Esta acción anulará el contrato y devolverá los artículos al inventario. Si no eres gerente, se enviará una solicitud.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Motivo de la cancelación <span class="text-danger">*</span></label>
                        <textarea name="motivo_cancelacion" class="form-control bg-body" rows="3" required placeholder="Explica por qué se está cancelando este contrato..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-send-fill me-1"></i> {{ (auth()->user()->isAdmin() || auth()->user()->isGerente()) ? 'Cancelar Renta' : 'Enviar Solicitud' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Devolución Parcial -->
<div class="modal fade" id="modalDevParcial" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-secondary text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-box-arrow-in-down me-2"></i>Registrar Devolución Parcial</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('rentas.devolucionParcial', $renta) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
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
                                @if($pendiente > 0)
                                <tr>
                                    <td>{{ $detalle->equipo->nombre }}</td>
                                    <td class="text-center fw-bold text-danger">
                                        {{ $pendiente }}
                                    </td>
                                    <td>
                                        <input type="number" name="devolver[{{ $detalle->id }}]" class="form-control form-control-sm" min="0" max="{{ $pendiente }}" value="0" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                                    </td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-light rounded-3 px-4 border" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary fw-bold rounded-3 px-4">Registrar Entrega</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
<!-- ======================= FIN SECCIÓN MODALES ======================= -->

<!-- MODAL PARA VER E IMPRIMIR EL TICKET DE PAGO -->
<div class="modal fade" id="modalTicketPago" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 380px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-receipt me-2"></i>Comprobante de Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeTicketPago" src="" style="width: 100%; height: 450px; border: none; display: block; background: #fff;"></iframe>
            </div>
            <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0 d-flex gap-2">
                <button type="button" class="btn btn-secondary rounded-3 flex-grow-1" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold" onclick="document.getElementById('iframeTicketPago').contentWindow.print()">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL: Visualizador de Documentos Generales -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-4"></i>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="modalVerDocumentoTitulo">Visualizador de Documento</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10 position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-body-secondary mt-2">Cargando documento...</div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 78vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>
            <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0 d-flex justify-content-between">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-outline-secondary rounded-3 px-4">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-secondary rounded-3 px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="renta-js-data"
     class="d-none"
     data-saldo-pendiente="{{ $renta->saldo_pendiente }}"
     data-multa-calculada="{{ $renta->estado == 'activa' ? $multaCalculada : 0 }}"
     data-fecha-fin="{{ $renta->fecha_fin->format('Y-m-d') }}"
     data-costo-diario-pendiente="{{ $costoDiarioPendiente ?? 0 }}"
     data-facturar="{{ $renta->facturar ? '1' : '0' }}"
     data-es-gerente="{{ (auth()->user()->isAdmin() || auth()->user()->isGerente()) ? '1' : '0' }}"
     data-autorizacion-aprobada="{{ $renta->autorizacion_aprobada ? '1' : '0' }}">
</div>

{{-- Aviso y bloqueo cuando no hay caja abierta: impide registrar cobros de la renta --}}
@unless($cajaAbierta)
<div class="modal fade" id="modalCajaCerrada" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-danger text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-lock-fill me-2"></i>Caja cerrada</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4">
                <i class="bi bi-cash-stack text-danger" style="font-size: 2.5rem;"></i>
                <h6 class="fw-bold text-body mt-2 mb-1">No puedes registrar cobros</h6>
                <p class="small text-secondary mb-0">Necesitas tener tu caja abierta. Ábrela en el Punto de Venta y vuelve a esta pantalla.</p>
            </div>
            <div class="modal-footer py-2 bg-body d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" onclick="window.location.reload()" title="Si ya abriste tu caja, recarga la pantalla">
                    <i class="bi bi-arrow-clockwise me-1"></i>Ya la abrí
                </button>
                <a href="{{ route('puntoventa.index') }}" class="btn btn-sm btn-primary fw-bold flex-grow-1">
                    <i class="bi bi-cart-check-fill me-1"></i>Ir al POS
                </a>
            </div>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const reglas = [
        { form: '#modalPago form',      monto: () => document.getElementById('inputMontoRecibidoPago') },
        { form: '#modalAmpliar form',   monto: () => document.querySelector('#modalAmpliar input[name="abono"]') },
        { form: '#modalFinalizar form', monto: () => document.getElementById('montoRecibidoFinal') },
    ];
    reglas.forEach(function (r) {
        const f = document.querySelector(r.form);
        if (!f) return;
        f.addEventListener('submit', function (e) {
            const campo = r.monto();
            if ((parseFloat(campo ? campo.value : 0) || 0) <= 0) return;   // sin cobro: se permite
            e.preventDefault();
            e.stopImmediatePropagation();
            const origen = f.closest('.modal');
            const aviso = document.getElementById('modalCajaCerrada');
            origen.addEventListener('hidden.bs.modal', function () {
                bootstrap.Modal.getOrCreateInstance(aviso).show();
            }, { once: true });
            bootstrap.Modal.getOrCreateInstance(origen).hide();
        });
    });
});
</script>
@endunless

@include('rentas.partials.pago_mixto_js')
<script>
    const rentaData = document.getElementById('renta-js-data') ? document.getElementById('renta-js-data').dataset : {};
    const saldoBaseOriginal = parseFloat(rentaData.saldoPendiente) || 0;
    const fechaFinOriginal = rentaData.fechaFin || '';
    const costoDiarioPendiente = parseFloat(rentaData.costoDiarioPendiente) || 0;

    const inputMulta = document.getElementById('multa_retraso');
    const checkCargoManual = document.getElementById('check_cargo_manual');
    const seccionCargoManual = document.getElementById('seccion_cargo_manual');
    const inputCargoManual = document.getElementById('cargo_manual');
    const motivoCargoManual = document.getElementById('motivo_cargo_manual');
    const inputMontoFinalHidden = document.getElementById('montoPagoFinal');
    const labelMaximo = document.getElementById('montoMaximoLabel');

    function calcularFaltantesPagoModal() {
        const filas = document.querySelectorAll('.fila-detalle-pago');

        filas.forEach(fila => {
            const pendiente = parseInt(fila.dataset.pendiente) || 0;
            const inputDevuelto = fila.querySelector('.input-devuelto-pago');
            const colFaltante = fila.querySelector('.col-faltante-pago');

            if (!inputDevuelto) return;

            let devueltoStr = inputDevuelto.value;
            let devuelto = parseInt(devueltoStr);
            
            if (isNaN(devuelto) || devuelto < 0) {
                devuelto = 0; 
            }
            
            if (devuelto > pendiente) {
                devuelto = pendiente;
                inputDevuelto.value = pendiente;
            }

            const faltante = pendiente - devuelto;
            if (colFaltante) colFaltante.textContent = faltante;

            if (faltante > 0) {
                if (colFaltante) colFaltante.className = 'text-center fw-bold text-danger col-faltante-pago';
            } else {
                if (colFaltante) colFaltante.className = 'text-center fw-bold text-secondary col-faltante-pago';
            }
        });

        calcularCambioPago();
    }

    function calcularCambioPago() {
        const elMontoRecibido = document.getElementById('inputMontoRecibidoPago');
        if (!elMontoRecibido) return;

        const saldoPendienteOriginal = parseFloat(rentaData.saldoPendiente) || 0;
        const multaCalculada = parseFloat(rentaData.multaCalculada) || 0;

        const saldoTotal = saldoPendienteOriginal + multaCalculada;
        const montoRecibido = parseFloat(elMontoRecibido.value) || 0;

        let montoARegistrar = 0, cambio = 0, faltante = 0;

        if (montoRecibido >= saldoTotal) {
            montoARegistrar = Math.max(0, saldoTotal);
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
        if (txtFaltante) txtFaltante.textContent = '$' + Math.max(0, faltante).toFixed(2); 

        if (elNuevoSaldo) {
            elNuevoSaldo.textContent = '$' + Math.max(0, faltante).toFixed(2);
            elNuevoSaldo.className = (faltante <= 0 && montoRecibido > 0) ? 'text-success fw-bold' : 'text-primary fw-bold';
        }
    }

    function prepararEnvioPago() {
        calcularCambioPago();
        
        document.querySelectorAll('.input-devuelto-pago').forEach(inp => {
            if (inp.value === '') {
                inp.value = '0';
            }
        });
        
        const metodo = document.getElementById('metodoPagoRegistro').value;
        const aRegistrar = parseFloat(document.getElementById('inputMontoRegistrarPago').value) || 0;
        if (metodo === 'mixto' && aRegistrar > 0 && !mixtoValido('pago', aRegistrar)) return false;
        return true;
    }

    function reimprimirTicket(url) {
        // 1. Cargamos el ticket en el iframe
        let iframe = document.getElementById('iframeTicketPago');
        if (iframe) {
            iframe.src = url;
        }
        
        // 2. Mostramos el modal usando Bootstrap de forma segura
        let modalElement = document.getElementById('modalTicketPago');
        if (modalElement && typeof bootstrap !== 'undefined') {
            let modalTicket = bootstrap.Modal.getInstance(modalElement);
            if(!modalTicket) {
                modalTicket = new bootstrap.Modal(modalElement);
            }
            modalTicket.show();
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
            if (inputReferencia) { inputReferencia.removeAttribute('required'); inputReferencia.value = ''; }
        }
        mixtoToggle('pago', metodo);
    }

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
            if (inputReferencia) { inputReferencia.removeAttribute('required'); inputReferencia.value = ''; }
        }
        mixtoToggle('final', metodo);
    }

    function recalcularFinalizacion() {
        let multa = parseFloat(inputMulta ? inputMulta.value : 0) || 0;
        let manual = (checkCargoManual && checkCargoManual.checked) ? (parseFloat(inputCargoManual.value) || 0) : 0;
        
        let deudaReal = saldoBaseOriginal + multa + manual;
        if (deudaReal < 0) deudaReal = 0;

        deudaReal = parseFloat(deudaReal.toFixed(2));

        const formatter = new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: 'USD'
        });

        if (labelMaximo) labelMaximo.textContent = 'Deuda Total: ' + formatter.format(deudaReal);

        const elRecibido = document.getElementById('montoRecibidoFinal');
        let montoRecibido = parseFloat(elRecibido ? elRecibido.value : 0) || 0;
        
        let montoACobrar = 0, cambio = 0, faltante = 0;

        if (montoRecibido >= deudaReal) {
            montoACobrar = deudaReal; 
            cambio = montoRecibido - deudaReal;
            faltante = 0;
        } else {
            montoACobrar = montoRecibido; 
            cambio = 0;
            faltante = deudaReal - montoRecibido;
        }

        if (inputMontoFinalHidden) inputMontoFinalHidden.value = montoACobrar.toFixed(2);
        
        const txtDeudaTotal = document.getElementById('montoCobrarFinalText');
        const txtCambio = document.getElementById('cambioFinalText');
        const txtFaltante = document.getElementById('faltanteFinalText');

        if (txtDeudaTotal) txtDeudaTotal.textContent = formatter.format(deudaReal);
        if (txtCambio) txtCambio.textContent = formatter.format(cambio);
        if (txtFaltante) txtFaltante.textContent = formatter.format(faltante);
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
        if (deudaReal < 0) deudaReal = 0;

        deudaReal = parseFloat(deudaReal.toFixed(2));

        const elRecibido = document.getElementById('montoRecibidoFinal');
        let montoRecibido = parseFloat(elRecibido ? elRecibido.value : 0) || 0;

        montoRecibido = parseFloat(montoRecibido.toFixed(2));

        const metodoFinal = document.getElementById('metodoPagoFinalRegistro').value;
        const aCobrar = Math.min(montoRecibido, deudaReal);
        if (metodoFinal === 'mixto' && aCobrar > 0 && !mixtoValido('final', aCobrar)) return false;

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

    function calcularAmpliacion() {
        const diasInput = document.getElementById('dias_extra');
        const dias = parseInt(diasInput ? diasInput.value : 0) || 0;
        const aplicaIva = rentaData.facturar === '1';

        let nuevoCostoDiario = 0;
        const inputsDevolver = document.querySelectorAll('.input-devolver-ampliar');
        
        if (inputsDevolver.length > 0) {
            inputsDevolver.forEach(inp => {
                let devuelto = parseInt(inp.value) || 0;
                let max = parseInt(inp.getAttribute('max')) || 0;
                let precio = parseFloat(inp.getAttribute('data-precio')) || 0;
                
                if (devuelto > max) { devuelto = max; inp.value = max; }
                if (devuelto < 0) { devuelto = 0; inp.value = 0; }
                
                const pendienteReal = max - devuelto;
                nuevoCostoDiario += (pendienteReal * precio);
            });
        } else {
            nuevoCostoDiario = parseFloat(rentaData.costoDiarioPendiente) || 0;
        }

        const costoExtra = dias * nuevoCostoDiario;
        const ivaExtra = aplicaIva ? (costoExtra * 0.16) : 0;
        const totalExtra = costoExtra + ivaExtra;

        if (document.getElementById('res_costo')) document.getElementById('res_costo').textContent = '$' + costoExtra.toFixed(2);
        if (document.getElementById('res_iva_ext')) document.getElementById('res_iva_ext').textContent = '$' + ivaExtra.toFixed(2);
        if (document.getElementById('res_total_ext')) document.getElementById('res_total_ext').textContent = '$' + totalExtra.toFixed(2);

        const elNuevaFecha = document.getElementById('nueva_fecha');
        if (elNuevaFecha && fechaFinOriginal) {
            let partes = fechaFinOriginal.split('-');
            let fecha = new Date(partes[0], partes[1] - 1, partes[2]);
            if (dias > 0) fecha.setDate(fecha.getDate() + dias);

            let dia = String(fecha.getDate()).padStart(2, '0');
            let mes = String(fecha.getMonth() + 1).padStart(2, '0');
            let anio = fecha.getFullYear();

            elNuevaFecha.textContent = `${dia}/${mes}/${anio}`;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        ['modalPago', 'modalFinalizar', 'modalAmpliar'].forEach(id => {
            let modal = document.getElementById(id);
            if (modal) {
                modal.addEventListener('show.bs.modal', function() {
                    if (id === 'modalPago') {
                        toggleReferencia();
                        const elRecibido = document.getElementById('inputMontoRecibidoPago');
                        if (elRecibido) elRecibido.value = '';
                        
                        // Limpiar campos de "A devolver"
                        const inputsDevolver = document.querySelectorAll('.input-devuelto-pago');
                        inputsDevolver.forEach(inp => {
                            inp.value = '';
                        });
                        
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

    document.addEventListener('DOMContentLoaded', function() {
        const modalVerDoc = document.getElementById('modalVerDocumento');
        if (modalVerDoc) {
            modalVerDoc.addEventListener('hidden.bs.modal', function () {
                const iframeEl = document.getElementById('iframeDocumento');
                if (iframeEl) iframeEl.src = '';
            });
        }
    });

    const inputAbonoAmpliar = document.getElementById('abonoAmpliar');
    if (inputAbonoAmpliar) {
        inputAbonoAmpliar.addEventListener('input', function() {
            const val = parseFloat(this.value) || 0;
            document.getElementById('divMetodoAmpliar').style.display = val > 0 ? 'block' : 'none';
            toggleReferenciaAmpliar();
        });
    }

    function toggleReferenciaAmpliar() {
        const val = parseFloat(document.getElementById('abonoAmpliar').value) || 0;
        const metodo = document.getElementById('metodoPagoAmpliar').value;
        const campoRef = document.getElementById('divReferenciaAmpliar');
        const inputRef = document.getElementById('inputReferenciaAmpliar');

        if (val > 0 && (metodo === 'transferencia' || metodo === 'tarjeta')) {
            campoRef.style.display = 'block';
            inputRef.setAttribute('required', 'required');
        } else {
            campoRef.style.display = 'none';
            inputRef.removeAttribute('required');
            inputRef.value = '';
        }
        mixtoToggle('ampliar', val > 0 ? metodo : '');
    }

    function validarMixtoAmpliar() {
        const abono = parseFloat(document.getElementById('abonoAmpliar').value) || 0;
        const metodo = document.getElementById('metodoPagoAmpliar').value;
        if (abono > 0 && metodo === 'mixto') return mixtoValido('ampliar', abono);
        return true;
    }

    // En mixto, el "Monto recibido" se calcula solo con la suma de las dos partes
    window.mixtoCallbacks.pago = function (suma) {
        const el = document.getElementById('inputMontoRecibidoPago');
        const mix = document.getElementById('metodoPagoRegistro')?.value === 'mixto';
        if (!el) return;
        el.readOnly = mix;
        if (mix) { el.value = suma > 0 ? suma.toFixed(2) : ''; calcularCambioPago(); }
    };
    window.mixtoCallbacks.final = function (suma) {
        const el = document.getElementById('montoRecibidoFinal');
        const mix = document.getElementById('metodoPagoFinalRegistro')?.value === 'mixto';
        if (!el) return;
        el.readOnly = mix;
        if (mix) { el.value = suma > 0 ? suma.toFixed(2) : ''; recalcularFinalizacion(); }
    };
</script>

<!-- AUTO-INICIAR EL TICKET TÉRMICO SI ESTÁ DISPONIBLE EN LA SESIÓN -->
@if(session('imprimir_ticket_pago'))
<script>
    setTimeout(function() {
        // 1. Asignamos la URL al iframe
        let urlTicket = "{{ route('rentas.ticketPago', session('imprimir_ticket_pago')) }}";
        let iframe = document.getElementById('iframeTicketPago');
        if (iframe) {
            iframe.src = urlTicket;
        }
        
        // 2. Abrimos el modal de manera segura
        let modalElement = document.getElementById('modalTicketPago');
        if (modalElement && typeof bootstrap !== 'undefined') {
            let modalTicket = new bootstrap.Modal(modalElement);
            modalTicket.show();
        } else {
            console.error("No se pudo cargar Bootstrap o el Modal no existe.");
        }
    }, 400); // 400 milisegundos de espera para evitar conflictos de carga
</script>
@endif
@endsection