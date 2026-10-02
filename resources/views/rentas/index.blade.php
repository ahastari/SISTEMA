@extends('layouts.admin')

@section('content')
<style>
    /* ==========================================================================
       ESTILOS VISUALES ADAPTABLES A MODO OSCURO / CLARO
       ========================================================================== */
    .page-title {
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
    }

    /* Tarjetas de Estadísticas Principales */
    .stats-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 20px;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    }
    .metric-icon-avatar {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        flex-shrink: 0;
    }
    .stats-card p {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 700;
        color: var(--bs-secondary-color);
        margin-bottom: 2px;
    }
    .stats-card h3 {
        font-size: 22px;
        font-weight: 800;
        letter-spacing: -0.5px;
        color: var(--bs-heading-color);
        margin-bottom: 0;
    }

    /* Tarjetas de Lista de Rentas */
    .renta-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        padding: 18px 20px;
        margin-bottom: 12px;
        transition: all 0.25s ease;
        position: relative;
        z-index: 1;
        cursor: pointer;
    }
    .renta-card:hover {
        border-color: var(--bs-primary);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
        transform: translateY(-2px);
    }

    .renta-card:has(.show),
    .renta-card.dropdown-abierto {
        z-index: 1050 !important;
    }

    .folio-badge {
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        color: #ffffff;
        padding: 4px 12px;
        border-radius: 50rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 6px rgba(13, 110, 253, 0.2);
    }

    .cliente-avatar-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background-color: rgba(13, 110, 253, 0.12);
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 15px;
        font-weight: 800;
        flex-shrink: 0;
    }

    .status-pill {
        padding: 5px 12px;
        border-radius: 50rem;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
    }

    .filter-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
    }
    .filtro-input {
        border-radius: 10px !important;
        font-size: 13px;
        transition: all 0.2s ease;
    }

    .pagination { gap: 6px; margin-bottom: 0; }
    .pagination .page-item .page-link {
        color: var(--bs-body-color);
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 10px !important;
        padding: 6px 14px;
        font-size: 13px;
        font-weight: 600;
    }
    .pagination .page-item.active .page-link {
        background: #0d6efd;
        border-color: #0d6efd;
        color: #ffffff;
        font-weight: 700;
    }

    /* Tarjeta: Rentas en espera */
    .espera-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-left: 4px solid #ffc107;
        border-radius: 16px;
        overflow: hidden;
    }
    .espera-head {
        padding: 16px 20px;
        border-bottom: 1px solid var(--bs-border-color);
    }
    .espera-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px 18px;
        padding: 14px 20px;
        border-bottom: 1px solid var(--bs-border-color);
        transition: background 0.2s ease;
    }
    .espera-row:last-child { border-bottom: 0; }
    .espera-row:hover { background: var(--bs-tertiary-bg); }
    .espera-row .col-fixed { min-width: 120px; }
    .espera-label {
        font-size: 10.5px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        color: var(--bs-secondary-color);
        display: block;
    }
    .espera-scroll { max-height: 440px; overflow-y: auto; }
</style>

<!-- Header de la sección -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
    <div>
        <h2 class="page-title mb-1">
            <i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Historial de Rentas
        </h2>
        <p class="text-body-secondary small mb-0">Gestión de contratos, control de cobros y estados de facturación.</p>
    </div>
    
    <div>
        <a href="{{ route('rentas.create') }}" class="btn btn-primary px-3 py-2 rounded-3 fw-bold shadow-sm">
            <i class="bi bi-plus-circle-fill me-1"></i> Nueva Renta
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-check-circle-fill fs-5 me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tarjetas de Estadísticas Financieras -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #0d6efd;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Total Histórico</p>
                    <h3>${{ number_format($totalFacturado, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-primary-subtle text-primary">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #198754;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Dinero Recibido</p>
                    <h3 class="text-success">${{ number_format($totalPagado, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-success-subtle text-success">
                    <i class="bi bi-wallet2"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #dc3545;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Rentas por Cobrar</p>
                    <h3 class="text-danger">${{ number_format($totalPendiente, 2) }}</h3>
                </div>
                <div class="metric-icon-avatar bg-danger-subtle text-danger">
                    <i class="bi bi-exclamation-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="stats-card" style="border-left: 4px solid #ffc107;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p>Rentas Registradas</p>
                    <h3>{{ $rentas->total() }}</h3>
                </div>
                <div class="metric-icon-avatar bg-warning-subtle text-warning-emphasis">
                    <i class="bi bi-card-checklist"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tarjeta: Rentas en Espera (seguimiento) -->
@php
    $esperaPend = $rentasEspera->where('autorizada', false)->values();
    $esperaAut  = $rentasEspera->where('autorizada', true)->values();
    $esperaGrupos = [
        'pend' => ['lista' => $esperaPend, 'vacio' => 'No hay rentas esperando autorización del gerente.'],
        'aut'  => ['lista' => $esperaAut,  'vacio' => 'No hay rentas autorizadas pendientes de liquidar.'],
    ];
    $tabEsperaInicial = 'borr'; // los borradores viven en el navegador; el JS cambia de pestaña si no hay ninguno
@endphp
<div class="espera-card shadow-sm mb-4">
    <div class="espera-head d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div class="d-flex align-items-center gap-3">
            <div class="metric-icon-avatar bg-warning-subtle text-warning-emphasis"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <h5 class="fw-bold mb-0 text-body">Rentas en Espera</h5>
            </div>
        </div>
        <ul class="nav nav-pills gap-2" id="esperaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm fw-bold rounded-pill px-3 py-1 active" data-bs-toggle="pill" data-bs-target="#esperaBorr" type="button" role="tab">
                    <i class="bi bi-pause-circle me-1"></i> En espera
                    <span class="badge rounded-pill bg-warning text-dark ms-1" id="cntEsperaBorr">0</span>
                </button>
            </li>
            <!-- <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm fw-bold rounded-pill px-3 py-1 " data-bs-toggle="pill" data-bs-target="#esperaPend" data-count="{{ $esperaPend->count() }}" type="button" role="tab">
                    <i class="bi bi-shield-lock me-1"></i> En revisión
                    <span class="badge rounded-pill bg-warning text-dark ms-1">{{ $esperaPend->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link btn-sm fw-bold rounded-pill px-3 py-1 " data-bs-toggle="pill" data-bs-target="#esperaAut" data-count="{{ $esperaAut->count() }}" type="button" role="tab">
                    <i class="bi bi-check-circle me-1"></i> Autorizadas por liquidar
                    <span class="badge rounded-pill bg-success ms-1">{{ $esperaAut->count() }}</span>
                </button>
            </li> -->
        </ul>
    </div>

    <div class="tab-content">
        <!-- Borradores "Poner en espera" de Nueva Renta (guardados en este navegador) -->
        <div class="tab-pane fade show active" id="esperaBorr" role="tabpanel">
            <div id="listaBorradores" class="espera-scroll"></div>
            <div id="pieBorradores" class="px-3 py-2 bg-body-tertiary border-top d-flex justify-content-between small d-none">
                <span class="text-body-secondary" id="pieBorradoresN"></span>
                <span class="fw-bold text-body">Total en espera: <span class="text-primary" id="pieBorradoresTotal"></span></span>
            </div>
        </div>

        @foreach($esperaGrupos as $clave => $grupo)
            @php $lista = $grupo['lista']; @endphp
            <div class="tab-pane fade {{ $tabEsperaInicial === $clave ? 'show active' : '' }}" id="{{ $clave === 'pend' ? 'esperaPend' : 'esperaAut' }}" role="tabpanel">
                @if($lista->isEmpty())
                    <div class="text-center text-body-secondary py-4 small">
                        <i class="bi bi-check2-all fs-4 d-block mb-1 text-success"></i>{{ $grupo['vacio'] }}
                    </div>
                @else
                    <div class="espera-scroll">
                        @foreach($lista as $r)
                            @php
                                $urgencia = $r['horas'] >= 24 ? 'danger' : ($r['horas'] >= 4 ? 'warning' : 'secondary');
                                $wa = strlen($r['tel_digitos']) === 10 ? '52' . $r['tel_digitos'] : (strlen($r['tel_digitos']) >= 11 ? $r['tel_digitos'] : null);
                            @endphp
                            <div class="espera-row">
                                <!-- Folio y cliente -->
                                <div class="col-fixed" style="min-width: 190px;">
                                    <span class="folio-badge"><i class="bi bi-file-text me-1"></i>{{ $r['folio'] }}</span>
                                    <div class="fw-bold text-body mt-1 text-truncate" style="max-width: 210px;">{{ $r['cliente'] }}</div>
                                    <small class="text-body-secondary"><i class="bi bi-telephone me-1"></i>{{ $r['telefono'] ?: 'S/N' }}</small>
                                </div>

                                <!-- Tipo de solicitud y motivo -->
                                <div style="min-width: 190px; flex: 1;">
                                    <span class="espera-label">Situación</span>
                                    @if($r['autorizada'])
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $r['tipo'] }}</span>
                                    @elseif($r['es_cancel'])
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">{{ $r['tipo'] }}</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $r['tipo'] }}</span>
                                    @endif
                                    @if(count($r['docs_faltan']))
                                        <span class="badge bg-body-tertiary text-body-secondary border" title="Documentos firmados pendientes de subir">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Falta {{ implode(' y ', $r['docs_faltan']) }}
                                        </span>
                                    @endif
                                    <small class="d-block text-body-secondary mt-1 text-truncate" style="max-width: 280px;" title="{{ $r['motivo'] }}">
                                        {{ $r['motivo'] ?: 'Sin motivo registrado' }}
                                    </small>
                                </div>

                                <!-- Solicitante y tiempo -->
                                <div class="col-fixed">
                                    <span class="espera-label">Solicitó</span>
                                    <span class="small fw-semibold text-body">{{ $r['solicitante'] }}</span>
                                    <small class="d-block text-{{ $urgencia === 'secondary' ? 'body-secondary' : $urgencia }}" title="{{ $r['actualizada'] ? $r['actualizada']->format('d/m/Y H:i') : '' }}">
                                        <i class="bi bi-clock me-1"></i>{{ $r['actualizada'] ? $r['actualizada']->diffForHumans() : '—' }}
                                    </small>
                                </div>

                                <!-- Vencimiento -->
                                <div class="col-fixed">
                                    <span class="espera-label">Fin de renta</span>
                                    <span class="small fw-semibold text-body">{{ $r['fecha_fin'] ? $r['fecha_fin']->format('d/m/Y') : '—' }}</span>
                                    @if($r['dias_retraso'] > 0)
                                        <small class="d-block text-danger fw-bold">{{ $r['dias_retraso'] }} día(s) de retraso</small>
                                    @endif
                                </div>

                                <!-- Saldo -->
                                <div class="col-fixed text-md-end">
                                    <span class="espera-label">Saldo</span>
                                    @if($r['saldo'] > 0)
                                        <span class="fw-bold text-danger">${{ number_format($r['saldo'], 2) }}</span>
                                    @else
                                        <span class="fw-bold text-success">Liquidada</span>
                                    @endif
                                    <small class="d-block text-body-secondary">Contrato ${{ number_format($r['total'], 2) }}</small>
                                </div>

                                <!-- Acciones de seguimiento -->
                                <div class="d-flex flex-wrap gap-1 ms-auto">
                                    <a href="{{ $r['url'] }}" class="btn btn-sm btn-primary rounded-3 fw-bold">
                                        <i class="bi bi-eye me-1"></i> {{ $r['autorizada'] ? 'Liquidar' : 'Ver' }}
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-3" title="Ver contrato"
                                            onclick="verDocumento('{{ $r['contrato_url'] }}', 'Contrato de Renta - Folio {{ $r['folio'] }}')">
                                        <i class="bi bi-file-earmark-pdf text-danger"></i>
                                    </button>
                                    @if($r['tel_digitos'])
                                        <a href="tel:{{ $r['tel_digitos'] }}" class="btn btn-sm btn-outline-secondary rounded-3" title="Llamar al cliente">
                                            <i class="bi bi-telephone"></i>
                                        </a>
                                    @endif
                                    @if($wa)
                                        <a href="https://wa.me/{{ $wa }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success rounded-3" title="WhatsApp">
                                            <i class="bi bi-whatsapp"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="px-3 py-2 bg-body-tertiary border-top d-flex justify-content-between small">
                        <span class="text-body-secondary">{{ $lista->count() }} renta(s)</span>
                        <span class="fw-bold text-body">Saldo en espera: <span class="text-danger">${{ number_format($lista->sum('saldo'), 2) }}</span></span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

<!-- Panel de Filtros -->
<div class="filter-card p-3 mb-4">
    <div class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-body-tertiary text-body-secondary border-end-0 rounded-start-3">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" id="buscarInput" class="form-control bg-body text-body border-start-0 filtro-input rounded-end-3" 
                       placeholder="Buscar folio o cliente...">
            </div>
        </div>

        <div class="col-6 col-md-3">
            <select id="estadoSelect" class="form-select form-select-sm bg-body text-body filtro-input">
                <option value="">Estado: Todos</option>
                <option value="activa">Activas</option>
                <option value="aprobada_adeudo">Finalizadas c/ Adeudo</option>
                <option value="finalizada">Finalizadas</option>
                <option value="cancelada">Canceladas</option>
                <option value="adeudo">Con Adeudo General</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <select id="facturaSelect" class="form-select form-select-sm bg-body text-body filtro-input">
                <option value="">Factura: Todas</option>
                <option value="si">Requiere Factura</option>
                <option value="no">Sin Factura</option>
            </select>
        </div>

        <div class="col-6 col-md-2">
            <input type="date" id="fechaFilter" class="form-control form-control-sm bg-body text-body filtro-input">
        </div>

        <div class="col-6 col-md-2">
            <button class="btn btn-sm btn-outline-secondary w-100 filtro-input fw-semibold" id="limpiarFiltros">
                <i class="bi bi-x-circle me-1"></i> Limpiar
            </button>
        </div>
    </div>
</div>

<!-- Lista de Rentas -->
<div id="rentasLista">
    @forelse($rentas as $renta)
    @php
        $esAprobadaConAdeudo = ($renta->estado == 'activa' && $renta->autorizacion_aprobada);
        $estadoData = $esAprobadaConAdeudo ? 'aprobada_adeudo' : $renta->estado;
        $inicialCliente = strtoupper(substr($renta->cliente->nombre_completo ?? 'C', 0, 1));

        // 🔥 CÁLCULO DE MULTA Y DEUDA REAL
        $multaGenerada = ($renta->estado == 'activa' && isset($renta->total_real)) ? max(0, $renta->total_real - $renta->total) : 0;
        $saldoPendienteReal = $renta->estado == 'cancelada' ? 0 : ($renta->saldo_pendiente + $multaGenerada);
    @endphp
    
    <div class="renta-card" 
         data-estado="{{ $estadoData }}" 
         data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}" 
         data-adeudo="{{ $saldoPendienteReal > 0 ? 'si' : 'no' }}"
         data-factura="{{ $renta->facturar ? 'si' : 'no' }}"
         data-url="{{ route('rentas.show', $renta) }}">
        
        <div class="row align-items-center g-3">
            
            <!-- Folio, Factura y Cliente -->
            <div class="col-12 col-md-4 col-lg-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="folio-badge">
                        <i class="bi bi-file-text me-1"></i>{{ $renta->folio }}
                    </span>
                    @if($renta->facturar)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small px-2">
                            <i class="bi bi-receipt me-1"></i> Factura
                        </span>
                    @endif
                </div>

                <div class="d-flex align-items-center gap-2">
                    <div class="cliente-avatar-icon">{{ $inicialCliente }}</div>
                    <div class="text-truncate">
                        <h6 class="mb-0 fw-bold text-body text-truncate" style="max-width: 180px;">
                            {{ $renta->cliente->nombre_completo ?? 'Cliente general' }}
                        </h6>
                        <small class="text-body-secondary d-block" style="font-size: 11px;">
                            <i class="bi bi-telephone me-1"></i>{{ $renta->cliente->telefono ?? 'S/N' }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Período de Renta -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <span class="text-body-secondary d-block fw-semibold" style="font-size: 11px;">Período</span>
                <strong class="text-body small d-block">
                    {{ $renta->fecha_inicio->format('d/m') }} - {{ $renta->fecha_fin->format('d/m/Y') }}
                </strong>
                <small class="text-body-secondary d-block" style="font-size: 11px;">{{ $renta->dias_totales }} días</small>
            </div>

            <!-- Días Restantes / Retraso -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <span class="text-body-secondary d-block fw-semibold" style="font-size: 11px;">Restante</span>
                @if($renta->estado == 'activa' && !$renta->autorizacion_aprobada)
                    @if($renta->dias_restantes > 0)
                        <span class="fw-bold small 
                            @if($renta->dias_restantes <= 3) text-danger 
                            @elseif($renta->dias_restantes <= 7) text-warning 
                            @else text-success 
                            @endif">
                            <i class="bi bi-hourglass-split me-1"></i>{{ $renta->dias_restantes }} días
                        </span>
                    @else
                        <span class="text-danger fw-bold small"><i class="bi bi-exclamation-triangle-fill me-1"></i>¡Vencida!</span>
                    @endif
                @elseif($esAprobadaConAdeudo)
                    <span class="text-warning-emphasis fw-bold small"><i class="bi bi-clock-history me-1"></i>Por Liquidar</span>
                @else
                    <span class="text-body-secondary small">—</span>
                @endif
            </div>

            <!-- DESGLOSE DE MONTOS Y SALDO (CORREGIDO) -->
            <div class="col-6 col-md-2 col-lg-3 text-end text-md-center">
                @if($renta->estado == 'cancelada')
                    <small class="text-body-secondary d-block text-decoration-line-through" style="font-size: 11px;">Original: ${{ number_format($renta->total, 2) }}</small>
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1" style="font-size: 11px;">
                        <i class="bi bi-slash-circle me-1"></i> Deuda Anulada
                    </span>
                @else
                    {{-- Total Base del Contrato --}}
                    <small class="text-body-secondary d-block" style="font-size: 11px;">
                        Contrato: <strong class="text-body">${{ number_format($renta->total, 2) }}</strong>
                    </small>
                    
                    {{-- Multa desglosada si el contrato está vencido --}}
                    @if($multaGenerada > 0)
                        <small class="text-danger d-block fw-bold mt-1" style="font-size: 10.5px;">
                            + Retraso: ${{ number_format($multaGenerada, 2) }}
                        </small>
                    @endif
                    
                    {{-- Gran Total a Deber contemplando la multa --}}
                    @if($saldoPendienteReal > 0)
                        <div class="mt-1">
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 12px; font-weight: 800;">
                                <i class="bi bi-exclamation-circle-fill me-1"></i> Debe Total: ${{ number_format($saldoPendienteReal, 2) }}
                            </span>
                        </div>
                    @else
                        <div class="mt-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 11px;">
                                <i class="bi bi-check-circle-fill me-1"></i> Liquidada
                            </span>
                        </div>
                    @endif
                @endif
            </div>

            <!-- Estado de Renta y Menú de Acciones -->
            <div class="col-12 col-md-2 col-lg-2">
                <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    
                    @if($esAprobadaConAdeudo)
                        <span class="status-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            <span class="status-dot bg-warning"></span> FINALIZADA
                        </span>
                    @elseif($renta->estado == 'activa')
                        <span class="status-pill bg-success-subtle text-success border border-success-subtle">
                            <span class="status-dot bg-success"></span> ACTIVA
                        </span>
                    @elseif($renta->estado == 'finalizada')
                        <span class="status-pill bg-primary-subtle text-primary border border-primary-subtle">
                            <span class="status-dot bg-primary"></span> FINALIZADA
                        </span>
                    @else
                        <span class="status-pill bg-secondary-subtle text-secondary border border-secondary-subtle">
                            <span class="status-dot bg-secondary"></span> CANCELADA
                        </span>
                    @endif
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-three-dots-vertical fs-6"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                            <li>
                                <a class="dropdown-item py-2 text-body" href="{{ route('rentas.show', $renta) }}">
                                    <i class="bi bi-eye me-2 text-primary"></i> 
                                    Ver detalle y cobrar
                                </a>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item py-2 text-body" onclick="verDocumento('{{ route('rentas.contrato', $renta) }}', 'Contrato de Renta - Folio {{ $renta->folio }}')">
                                    <i class="bi bi-file-earmark-pdf me-2 text-danger"></i> Ver PDF
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
    @empty
    <div class="text-center py-5 rounded-4 bg-body border border-dashed">
        <i class="bi bi-inbox text-body-secondary" style="font-size: 48px;"></i>
        <h5 class="mt-3 text-body fw-bold">No hay rentas registradas</h5>
        <p class="text-body-secondary small mb-0">Comienza creando tu primera renta en el sistema.</p>
    </div>
    @endforelse
</div>

<!-- Paginación con Estilos Bootstrap 5 -->
<div class="mt-4 d-flex justify-content-center">
    {{ $rentas->links('pagination::bootstrap-5') }}
</div>

<!-- MODAL: VISUALIZADOR DINÁMICO DE DOCUMENTOS -->
<div class="modal fade" id="modalVerDocumento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border shadow-lg rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0" id="modalVerDocumentoTitulo">Visualizador de Documento</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 bg-secondary bg-opacity-10 position-relative">
                <div id="loaderDocumento" class="position-absolute top-50 start-50 translate-middle text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
                <iframe id="iframeDocumento" src="" style="width: 100%; height: 78vh; border: none;" onload="document.getElementById('loaderDocumento').classList.add('d-none')"></iframe>
            </div>
            <div class="modal-footer bg-body-tertiary py-2 px-3 border-top">
                <a id="btnDescargarDocumento" href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-3">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Abrir en ventana nueva
                </a>
                <button type="button" class="btn btn-sm btn-secondary rounded-3 px-3" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
function filtrarRentas() {
    const busqueda = document.getElementById('buscarInput').value.toLowerCase();
    const estado = document.getElementById('estadoSelect').value;
    const factura = document.getElementById('facturaSelect').value;
    const fecha = document.getElementById('fechaFilter').value;
    
    document.querySelectorAll('.renta-card').forEach(renta => {
        let mostrar = true;
        
        if (busqueda && !renta.innerText.toLowerCase().includes(busqueda)) mostrar = false;
        
        if (mostrar && estado) {
            if (estado === 'adeudo' && renta.dataset.adeudo !== 'si') mostrar = false;
            else if (estado !== 'adeudo' && renta.dataset.estado !== estado) mostrar = false;
        }

        if (mostrar && factura && renta.dataset.factura !== factura) mostrar = false;
        if (mostrar && fecha && renta.dataset.fecha !== fecha) mostrar = false;
        
        renta.style.display = mostrar ? '' : 'none';
    });
}

document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('buscarInput').value = '';
    document.getElementById('estadoSelect').value = '';
    document.getElementById('facturaSelect').value = '';
    document.getElementById('fechaFilter').value = '';
    filtrarRentas();
});

document.getElementById('buscarInput').addEventListener('keyup', filtrarRentas);
document.getElementById('estadoSelect').addEventListener('change', filtrarRentas);
document.getElementById('facturaSelect').addEventListener('change', filtrarRentas);
document.getElementById('fechaFilter').addEventListener('change', filtrarRentas);

function verDocumento(url, titulo) {
    document.getElementById('modalVerDocumentoTitulo').textContent = titulo;
    document.getElementById('btnDescargarDocumento').href = url;
    document.getElementById('loaderDocumento').classList.remove('d-none');
    document.getElementById('iframeDocumento').src = url;
    new bootstrap.Modal(document.getElementById('modalVerDocumento')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.renta-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.dropdown') && !e.target.closest('a') && !e.target.closest('button')) {
                window.location.href = this.dataset.url;
            }
        });
        
        const dropdown = card.querySelector('.dropdown');
        if (dropdown) {
            dropdown.addEventListener('show.bs.dropdown', () => card.classList.add('dropdown-abierto'));
            dropdown.addEventListener('hide.bs.dropdown', () => card.classList.remove('dropdown-abierto'));
        }
    });

    document.getElementById('modalVerDocumento').addEventListener('hidden.bs.modal', function () {
        document.getElementById('iframeDocumento').src = '';
    });
});
</script>

<script>
// ===== Rentas en espera (borradores de "Nueva Renta"): se guardan en este navegador por usuario y sucursal =====
(function () {
    const KEY = 'rentas_espera_u{{ auth()->id() }}_s{{ session('activo_sucursal_id') }}';
    const PUEDE_AUTORIZAR = @json($puedeAutorizarDescuento);
    const URL_ESTADOS = '{{ route("puntoventa.descuento.estados") }}';
    const URL_CREAR = '{{ route("rentas.create") }}';

    const $ = id => document.getElementById(id);
    const money = n => '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const fechaCorta = s => { const p = String(s || '').split('-'); return p.length === 3 ? p[2] + '/' + p[1] + '/' + p[0] : '—'; };
    function hace(ts) {
        const m = Math.floor((Date.now() - ts) / 60000);
        if (m < 1) return 'hace un momento';
        if (m < 60) return 'hace ' + m + ' min';
        const h = Math.floor(m / 60);
        return h < 24 ? 'hace ' + h + ' h' : 'hace ' + Math.floor(h / 24) + ' d';
    }

    function leer() { try { return JSON.parse(localStorage.getItem(KEY) || '[]') || []; } catch (e) { return []; } }
    function guardar(lista) { try { localStorage.setItem(KEY, JSON.stringify(lista)); return true; } catch (e) { return false; } }

    // Mismo criterio que en Nueva Renta
    function estado(b) {
        const d = parseFloat(b.descuento) || 0;
        const base = { badge: 'bg-secondary-subtle text-secondary border', icono: 'bi-pause-circle', texto: 'En espera', clase: '' };
        if (d <= 0 || PUEDE_AUTORIZAR) return base;
        const s = b.sol;
        if (!s || Math.abs(s.monto - d) >= 0.01) return { badge: 'bg-secondary-subtle text-secondary border', icono: 'bi-shield-exclamation', texto: 'Descuento sin solicitar', clase: '' };
        if (s.estado === 'pendiente') return { badge: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', icono: 'spin', texto: 'Esperando al gerente', clase: '' };
        if (s.estado === 'aprobada')  return { badge: 'bg-success-subtle text-success border border-success-subtle', icono: 'bi-check-circle-fill', texto: 'Descuento autorizado', clase: 'text-success' };
        return { badge: 'bg-danger-subtle text-danger border border-danger-subtle', icono: 'bi-x-circle-fill', texto: 'Descuento rechazado', clase: 'text-danger' };
    }

    function render() {
        const lista = leer();
        const cont = $('listaBorradores');
        if (!cont) return;
        $('cntEsperaBorr').textContent = lista.length;

        if (!lista.length) {
            cont.innerHTML = '<div class="text-center text-body-secondary py-4 small"><i class="bi bi-check2-all fs-4 d-block mb-1 text-success"></i>'
                + 'Se crean con <strong>Poner en espera</strong> en Nueva Renta.</div>';
            $('pieBorradores').classList.add('d-none');
            return;
        }

        cont.innerHTML = lista.map(b => {
            const e = estado(b);
            const eq = b.equipos || [];
            const n = eq.length;
            const nombres = eq.map(x => x.cantidad + ' × ' + x.nombre).join(', ');
            const desc = parseFloat(b.descuento) || 0;
            const dep = parseFloat(b.deposito) || 0;
            const icono = e.icono === 'spin'
                ? '<span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span>'
                : '<i class="bi ' + e.icono + ' me-1"></i>';
            return `<div class="espera-row">
                <div class="col-fixed" style="min-width: 200px;">
                    <span class="espera-label">Cliente</span>
                    <div class="fw-bold text-body text-truncate" style="max-width: 230px;">${esc(b.cliente_nombre)}</div>
                    <small class="text-body-secondary"><i class="bi bi-clock me-1"></i>${hace(b.ts)}</small>
                </div>
                <div style="min-width: 190px; flex: 1;">
                    <span class="espera-label">Productos</span>
                    <span class="small fw-semibold text-body">${n} producto${n === 1 ? '' : 's'}</span>
                    <small class="d-block text-body-secondary text-truncate" style="max-width: 300px;" title="${esc(nombres)}">${esc(nombres) || '—'}</small>
                </div>
                <div class="col-fixed">
                    <span class="espera-label">Período</span>
                    <span class="small fw-semibold text-body">${fechaCorta(b.fecha_inicio)} – ${fechaCorta(b.fecha_fin)}</span>
                </div>
                <div class="col-fixed text-md-end">
                    <span class="espera-label">Total</span>
                    <span class="fw-bold text-body">${money(b.total)}</span>
                    ${desc > 0 ? `<small class="d-block ${e.clase || 'text-body-secondary'}">Desc. ${money(desc)}</small>` : ''}
                    ${dep > 0 ? `<small class="d-block text-body-secondary">Depósito ${money(dep)}</small>` : ''}
                </div>
                <div class="col-fixed">
                    <span class="badge ${e.badge}">${icono}${e.texto}</span>
                </div>
                <div class="d-flex gap-1 ms-auto">
                    <a href="${URL_CREAR}?retomar=${encodeURIComponent(b.id)}" class="btn btn-sm btn-primary rounded-3 fw-bold"><i class="bi bi-play-fill me-1"></i> Retomar</a>
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-3" title="Eliminar" onclick="eliminarRentaEnEspera('${esc(b.id)}')"><i class="bi bi-trash"></i></button>
                </div>
            </div>`;
        }).join('');

        $('pieBorradoresN').textContent = lista.length + ' renta(s) en espera';
        $('pieBorradoresTotal').textContent = money(lista.reduce((a, b) => a + (parseFloat(b.total) || 0), 0));
        $('pieBorradores').classList.remove('d-none');
    }

    window.eliminarRentaEnEspera = function (id) {
        if (!confirm('¿Eliminar esta renta en espera? Si tenía un descuento autorizado, se perderá.')) return;
        guardar(leer().filter(x => x.id !== id));
        render();
    };

    // Seguimiento: revisa si el gerente ya autorizó/rechazó los descuentos pendientes
    function revisarSolicitudes() {
        if (PUEDE_AUTORIZAR) return;
        const ids = [...new Set(leer().filter(b => b.sol && b.sol.estado === 'pendiente').map(b => b.sol.id))];
        if (!ids.length) return;
        fetch(URL_ESTADOS + '?ids=' + ids.join(','), { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : null)
            .then(data => {
                if (!data) return;
                const lista = leer();            // se relee para no pisar cambios hechos en otra pestaña
                let cambio = false;
                lista.forEach(b => {
                    if (!b.sol || b.sol.estado !== 'pendiente') return;
                    const info = data[b.sol.id];
                    if (!info || info.estado === 'pendiente') return;
                    if (info.estado === 'aprobada' || info.estado === 'rechazada') {
                        b.sol.estado = info.estado;
                        b.sol.autorizador = info.autorizador || null;
                    } else {
                        b.sol = null;            // cancelada / usada
                    }
                    cambio = true;
                });
                if (cambio && guardar(lista)) render();
            })
            .catch(() => {});
    }

    function pestanaInicial() {
        if (leer().length > 0) return;
        const t = [...document.querySelectorAll('#esperaTabs button')].find(b => b.dataset.bsTarget !== '#esperaBorr' && Number(b.dataset.count || 0) > 0);
        if (t) new bootstrap.Tab(t).show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        render();
        pestanaInicial();
        revisarSolicitudes();
        setInterval(revisarSolicitudes, 8000);
        setInterval(render, 60000);                                   // refresca el "hace X min"
        window.addEventListener('storage', e => { if (e.key === KEY) render(); });   // cambios desde Nueva Renta en otra pestaña
    });
})();
</script>
@endsection