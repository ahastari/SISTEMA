@extends('layouts.admin')

@section('content')
@php
    $badges = [
        'pendiente' => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'bi-hourglass', 'Pendiente'],
        'parcial'   => ['bg-info-subtle text-info-emphasis border-info-subtle', 'bi-pie-chart', 'Con abonos'],
        'vencido'   => ['bg-danger-subtle text-danger border-danger-subtle', 'bi-exclamation-octagon', 'Vencido'],
        'liquidado' => ['bg-success-subtle text-success border-success-subtle', 'bi-check-circle', 'Liquidado'],
        'cancelado' => ['bg-dark-subtle text-dark border-dark-subtle', 'bi-x-circle', 'Venta cancelada'],
    ];
    [$clase, $icono, $etq] = $badges[$credito->estado];
    $nombreEquipo = fn ($d) => $d->concepto_especial ?: ($d->equipo->nombre ?? 'Producto');
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="mb-1 fw-bold text-body">
            <i class="bi bi-credit-card-2-front text-warning me-2"></i>Crédito {{ $credito->folio }}
            <span class="badge rounded-pill border px-3 py-1 fs-6 align-middle ms-2 {{ $clase }}"><i class="bi {{ $icono }} me-1"></i>{{ $etq }}</span>
        </h3>
        <p class="text-secondary small mb-0">Detalle de lo vendido a crédito, plazo y todos los abonos recibidos.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if($credito->saldo > 0)
            <button type="button" class="btn btn-success btn-sm fw-bold rounded-3 px-3 shadow-sm btn-abonar"
                    data-action="{{ route('puntoventa.creditos.abonar', $venta->id) }}"
                    data-folio="{{ $credito->folio }}" data-cliente="{{ $credito->cliente }}" data-saldo="{{ $credito->saldo }}">
                <i class="bi bi-cash-coin me-1"></i>Registrar abono
            </button>
        @endif
        <a href="{{ route('puntoventa.ticket', $venta->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-3 px-3 shadow-sm">
            <i class="bi bi-receipt me-1"></i>Ticket de la venta
        </a>
        <a href="{{ route('puntoventa.creditos') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 shadow-sm fw-bold">
            <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Cartera
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3 rounded-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3">{{ $errors->first() }}</div>
@endif

<div class="row g-3 mb-3">
    {{-- Datos de la venta --}}
    <div class="col-12 col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body-tertiary fw-bold py-2"><i class="bi bi-info-circle me-1"></i>Datos del crédito</div>
            <div class="card-body">
                <div class="row g-3 small">
                    <div class="col-6">
                        <div class="text-secondary">Cliente</div>
                        <div class="fw-bold text-body">{{ $credito->cliente }}</div>
                        @if($venta->requiere_factura && $venta->rfc_cliente)
                            <div class="text-primary" style="font-size: 11px;">RFC: {{ $venta->rfc_cliente }}</div>
                        @endif
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Sucursal</div>
                        <div class="fw-bold text-body"><i class="bi bi-shop me-1"></i>{{ $credito->sucursal }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Fecha de la venta</div>
                        <div class="fw-bold text-body">{{ $credito->fecha->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Vendió</div>
                        <div class="fw-bold text-body">{{ $vendedor->name ?? '—' }}</div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Plazo otorgado</div>
                        <div class="fw-bold text-body"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $credito->dias_credito }} días</span></div>
                    </div>
                    <div class="col-6">
                        <div class="text-secondary">Vencimiento</div>
                        <div class="fw-bold {{ $credito->estado === 'vencido' ? 'text-danger' : 'text-body' }}">
                            {{ $credito->vence->format('d/m/Y') }}
                            @if(!in_array($credito->estado, ['liquidado', 'cancelado']))
                                <span class="fw-normal" style="font-size: 11px;">
                                    · {{ $credito->dias_restantes >= 0 ? $credito->dias_restantes . ' días restantes' : abs($credito->dias_restantes) . ' días de atraso' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    @if($venta->observaciones)
                    <div class="col-12">
                        <div class="text-secondary">Observaciones</div>
                        <div class="text-body">{{ $venta->observaciones }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen financiero --}}
    <div class="col-12 col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border: 1px solid var(--bs-border-color) !important;">
            <div class="card-header bg-body-tertiary fw-bold py-2"><i class="bi bi-calculator me-1"></i>Resumen</div>
            <div class="card-body">
                <table class="table table-borderless table-sm mb-2" style="font-size: 13px;">
                    <tr><td class="text-secondary">Subtotal</td><td class="text-end text-body">${{ number_format($venta->subtotal, 2) }}</td></tr>
                    @if($venta->descuento > 0)
                    <tr><td class="text-success">Descuento</td><td class="text-end text-success">-${{ number_format($venta->descuento, 2) }}</td></tr>
                    @endif
                    @if($venta->iva > 0)
                    <tr><td class="text-secondary">IVA (16%)</td><td class="text-end text-body">${{ number_format($venta->iva, 2) }}</td></tr>
                    @endif
                    <tr class="border-top"><td class="fw-bold text-body">Total a crédito</td><td class="text-end fw-bold text-body">${{ number_format($credito->total, 2) }}</td></tr>
                    <tr><td class="text-success">Abonado</td><td class="text-end fw-bold text-success">${{ number_format($credito->abonado, 2) }}</td></tr>
                    <tr class="border-top">
                        <td class="fw-bold text-body fs-6">Saldo pendiente</td>
                        <td class="text-end fw-bold fs-5 {{ $credito->saldo > 0 ? 'text-warning-emphasis' : 'text-success' }}">${{ number_format($credito->saldo, 2) }}</td>
                    </tr>
                </table>
                <div class="progress" style="height: 8px;"><div class="progress-bar bg-success" style="width: {{ $credito->porcentaje }}%"></div></div>
                <div class="text-secondary text-end mt-1" style="font-size: 11px;">{{ $credito->porcentaje }}% pagado</div>
            </div>
        </div>
    </div>
</div>

{{-- Productos que se fueron a crédito --}}
<div class="card border-0 shadow-sm rounded-3 mb-3" style="border: 1px solid var(--bs-border-color) !important;">
    <div class="card-header bg-body-tertiary fw-bold py-2"><i class="bi bi-box-seam me-1"></i>Lo que se vendió a crédito</div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" style="font-size: 13px;">
            <thead class="bg-body-tertiary">
                <tr><th class="ps-3">Producto / Concepto</th><th class="text-center">Cantidad</th><th class="text-end">Precio unit.</th><th class="text-end pe-3">Importe</th></tr>
            </thead>
            <tbody>
                @forelse($venta->detalles as $d)
                    <tr>
                        <td class="ps-3 fw-semibold text-body">{{ $nombreEquipo($d) }}</td>
                        <td class="text-center">{{ rtrim(rtrim(number_format($d->cantidad, 2), '0'), '.') }}</td>
                        <td class="text-end text-secondary">${{ number_format($d->precio_unitario ?? ($d->cantidad > 0 ? $d->subtotal / $d->cantidad : 0), 2) }}</td>
                        <td class="text-end fw-bold pe-3">${{ number_format($d->subtotal, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-secondary py-4">Sin detalle de productos.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Abonos --}}
<div class="card border-0 shadow-sm rounded-3" style="border: 1px solid var(--bs-border-color) !important;">
    <div class="card-header bg-body-tertiary fw-bold py-2 d-flex justify-content-between align-items-center">
        <span><i class="bi bi-cash-coin me-1"></i>Abonos recibidos ({{ count($credito->abonos) }})</span>
        @if($credito->saldo > 0)
            <button type="button" class="btn btn-success btn-sm fw-bold py-0 px-2 btn-abonar"
                    data-action="{{ route('puntoventa.creditos.abonar', $venta->id) }}"
                    data-folio="{{ $credito->folio }}" data-cliente="{{ $credito->cliente }}" data-saldo="{{ $credito->saldo }}">
                <i class="bi bi-plus-lg"></i> Abonar
            </button>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0" style="font-size: 13px;">
            <thead class="bg-body-tertiary">
                <tr>
                    <th class="ps-3">#</th><th>Fecha</th><th>Método</th><th>Referencia</th><th>Recibió</th>
                    <th class="text-end">Monto</th><th class="text-end">Saldo después</th><th class="text-center pe-3">Comprobante</th>
                </tr>
            </thead>
            <tbody>
                @forelse($credito->abonos as $a)
                    <tr>
                        <td class="ps-3 text-secondary">{{ $a['n'] }}</td>
                        <td>{{ $a['fecha'] }}</td>
                        <td class="text-capitalize">{{ $a['metodo'] }}@if($a['desglose'])<div class="text-secondary" style="font-size: 11px;">{{ $a['desglose'] }}</div>@endif</td>
                        <td class="text-secondary">
                            {{ $a['ref'] ?: '—' }}
                            @if($a['obs'])<div style="font-size: 11px;">{{ $a['obs'] }}</div>@endif
                        </td>
                        <td>{{ $a['usuario'] }}</td>
                        <td class="text-end fw-bold text-success">${{ number_format($a['monto'], 2) }}</td>
                        <td class="text-end text-secondary">${{ number_format($a['saldo_despues'], 2) }}</td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-outline-secondary border py-0 px-2" onclick="verTicketAbono('{{ $a['ticket'] }}')" title="Ver / reimprimir comprobante">
                                <i class="bi bi-printer"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-secondary py-4"><i class="bi bi-inbox d-block fs-3 opacity-50 mb-1"></i>Aún no hay abonos registrados.</td></tr>
                @endforelse
            </tbody>
            @if(count($credito->abonos))
            <tfoot class="bg-body-tertiary">
                <tr>
                    <td colspan="5" class="text-end fw-bold ps-3">Total abonado</td>
                    <td class="text-end fw-bold text-success">${{ number_format($credito->abonado, 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@include($vista . '._modal_abono')
@endsection