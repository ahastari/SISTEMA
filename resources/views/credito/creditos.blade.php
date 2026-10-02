@extends('layouts.admin')

@section('content')
@php
    $badges = [
        'pendiente' => ['bg-secondary-subtle text-secondary border-secondary-subtle', 'bi-hourglass', 'Pendiente'],
        'parcial'   => ['bg-info-subtle text-info-emphasis border-info-subtle', 'bi-pie-chart', 'Abonado'],
        'vencido'   => ['bg-danger-subtle text-danger border-danger-subtle', 'bi-exclamation-octagon', 'Vencido'],
        'liquidado' => ['bg-success-subtle text-success border-success-subtle', 'bi-check-circle', 'Liquidado'],
    ];
@endphp

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="mb-0 fw-bold text-body"><i class="bi bi-credit-card-2-front text-warning me-2"></i>Cartera de Créditos</h3>
        <p class="text-secondary small mb-0">Ventas a crédito por sucursal: saldos, vencimientos y registro de abonos.</p>
    </div>
    <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm fw-bold px-3">
        <i class="bi bi-arrow-left-short fs-5 align-middle"></i> POS
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3 rounded-3"><i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}</div>
@endif
@unless($cajaAbierta)
    <div class="alert alert-warning border-0 shadow-sm mb-3 rounded-3 py-2 small"><i class="bi bi-exclamation-triangle-fill me-2"></i>No tienes caja abierta: puedes registrar abonos por transferencia o tarjeta; para cobrar en efectivo abre tu caja en el Punto de Venta.</div>
@endunless
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
@endif
@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3">{{ $errors->first() }}</div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body py-3">
                <div class="text-secondary small fw-semibold text-uppercase">Total otorgado</div>
                <div class="fs-4 fw-bold text-body">${{ number_format($kpi['total'], 2) }}</div>
                <div class="text-secondary" style="font-size: 11px;">{{ $kpi['creditos'] }} créditos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body py-3">
                <div class="text-secondary small fw-semibold text-uppercase">Abonado</div>
                <div class="fs-4 fw-bold text-success">${{ number_format($kpi['abonado'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body py-3">
                <div class="text-secondary small fw-semibold text-uppercase">Por cobrar</div>
                <div class="fs-4 fw-bold text-warning-emphasis">${{ number_format($kpi['por_cobrar'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border-0 shadow-sm rounded-3 h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body py-3">
                <div class="text-secondary small fw-semibold text-uppercase">Vencido</div>
                <div class="fs-4 fw-bold text-danger">${{ number_format($kpi['vencido'], 2) }}</div>
                <div class="text-secondary" style="font-size: 11px;">{{ $kpi['n_vencidos'] }} créditos vencidos</div>
            </div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<form method="GET" action="{{ route('puntoventa.creditos') }}" class="card border-0 shadow-sm rounded-3 mb-3">
    <div class="card-body py-3">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-body mb-1">Buscar</label>
                <input type="text" name="buscar" value="{{ request('buscar') }}" class="form-control form-control-sm bg-body text-body" placeholder="Folio o cliente">
            </div>
            @if($esGlobal)
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-body mb-1">Sucursal</label>
                <select name="sucursal" class="form-select form-select-sm bg-body text-body">
                    <option value="">Todas</option>
                    @foreach($sucursales as $s)
                        <option value="{{ $s->id }}" {{ request('sucursal') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-body mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm bg-body text-body">
                    @foreach(['con_saldo' => 'Con saldo', 'pendiente' => 'Pendientes', 'parcial' => 'Con abonos', 'vencido' => 'Vencidos', 'liquidado' => 'Liquidados', 'todos' => 'Todos'] as $k => $l)
                        <option value="{{ $k }}" {{ $estadoFiltro === $k ? 'selected' : '' }}>{{ $l }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-body mb-1">Desde</label>
                <input type="date" name="desde" value="{{ request('desde') }}" class="form-control form-control-sm bg-body text-body">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-body mb-1">Hasta</label>
                <input type="date" name="hasta" value="{{ request('hasta') }}" class="form-control form-control-sm bg-body text-body">
            </div>
            <div class="col-12 col-md-auto d-flex gap-2">
                <button class="btn btn-sm btn-primary fw-bold px-3"><i class="bi bi-funnel me-1"></i>Filtrar</button>
                <a href="{{ route('puntoventa.creditos') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
            </div>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-body-tertiary text-body border-bottom">
                    <tr>
                        <th class="ps-4 py-2">Folio</th>
                        <th class="py-2">Cliente</th>
                        <th class="py-2">Sucursal</th>
                        <th class="py-2">Otorgado / Vence</th>
                        <th class="py-2 text-center">Plazo</th>
                        <th class="py-2 text-end">Monto</th>
                        <th class="py-2 text-end">Abonado</th>
                        <th class="py-2 text-end">Saldo</th>
                        <th class="py-2 text-center">Estado</th>
                        <th class="pe-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($creditos as $c)
                    @php [$clase, $icono, $etq] = $badges[$c->estado]; @endphp
                    <tr>
                        <td class="ps-4 font-monospace fw-bold"><a href="{{ route('puntoventa.creditos.show', $c->id) }}" class="text-decoration-none text-primary">{{ $c->folio }}</a></td>
                        <td class="fw-semibold text-body">{{ $c->cliente }}</td>
                        <td class="text-secondary"><i class="bi bi-shop me-1"></i>{{ $c->sucursal }}</td>
                        <td>
                            <div class="text-body small">{{ $c->fecha->format('d/m/Y') }}</div>
                            <div class="small {{ $c->estado === 'vencido' ? 'text-danger fw-semibold' : 'text-secondary' }}">
                                Vence {{ $c->vence->format('d/m/Y') }}
                                @if($c->estado !== 'liquidado')
                                    · {{ $c->dias_restantes >= 0 ? ($c->dias_restantes . ' d restantes') : (abs($c->dias_restantes) . ' d de atraso') }}
                                @endif
                            </div>
                        </td>
                        <td class="text-center"><span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">{{ $c->dias_credito }} días</span></td>
                        <td class="text-end text-body">${{ number_format($c->total, 2) }}</td>
                        <td class="text-end text-success">
                            ${{ number_format($c->abonado, 2) }}
                            <div class="progress mt-1" style="height: 4px;"><div class="progress-bar bg-success" style="width: {{ $c->porcentaje }}%"></div></div>
                        </td>
                        <td class="text-end fw-bold {{ $c->saldo > 0 ? 'text-warning-emphasis' : 'text-success' }}">${{ number_format($c->saldo, 2) }}</td>
                        <td class="text-center">
                            <span class="badge rounded-pill border px-3 py-1 {{ $clase }}"><i class="bi {{ $icono }} me-1"></i>{{ $etq }}</span>
                        </td>
                        <td class="pe-4 text-center">
                            <div class="d-flex justify-content-center gap-1">
                                @if($c->saldo > 0)
                                <button type="button" class="btn btn-sm btn-success fw-bold px-2 btn-abonar"
                                        data-action="{{ route('puntoventa.creditos.abonar', $c->id) }}"
                                        data-folio="{{ $c->folio }}" data-cliente="{{ $c->cliente }}"
                                        data-saldo="{{ $c->saldo }}" title="Registrar abono">
                                    <i class="bi bi-cash-coin"></i> Abonar
                                </button>
                                @endif
                                <a href="{{ route('puntoventa.creditos.show', $c->id) }}" class="btn btn-sm btn-outline-primary px-2" title="Ver detalle del crédito">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary border px-2 text-body btn-historial"
                                        data-folio="{{ $c->folio }}" data-cliente="{{ $c->cliente }}"
                                        data-total="{{ $c->total }}" data-saldo="{{ $c->saldo }}"
                                        data-abonos="{{ json_encode($c->abonos) }}" title="Historial de abonos">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>No hay créditos con esos filtros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">{{ $creditos->links() }}</div>

@include($vista . '._modal_abono')

{{-- MODAL: historial de abonos --}}
<div class="modal fade" id="modalHistorialAbonos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-list-ul me-2"></i>Abonos · <span id="histFolio"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex flex-wrap gap-3 small mb-3">
                    <span class="text-secondary">Cliente: <strong id="histCliente" class="text-body"></strong></span>
                    <span class="text-secondary">Monto: <strong id="histTotal" class="text-body"></strong></span>
                    <span class="text-secondary">Saldo: <strong id="histSaldo" class="text-warning-emphasis"></strong></span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-body-tertiary">
                            <tr><th>Fecha</th><th>Método</th><th>Referencia</th><th>Recibió</th><th class="text-end">Monto</th><th></th></tr>
                        </thead>
                        <tbody id="histCuerpo"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const money = n => '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    document.querySelectorAll('.btn-historial').forEach(btn => btn.addEventListener('click', () => {
        const abonos = JSON.parse(btn.dataset.abonos || '[]');
        document.getElementById('histFolio').textContent = btn.dataset.folio;
        document.getElementById('histCliente').textContent = btn.dataset.cliente;
        document.getElementById('histTotal').textContent = money(btn.dataset.total);
        document.getElementById('histSaldo').textContent = money(btn.dataset.saldo);
        document.getElementById('histCuerpo').innerHTML = abonos.length ? abonos.map(a => `
            <tr>
                <td>${esc(a.fecha)}</td>
                <td class="text-capitalize">${esc(a.metodo)}</td>
                <td class="text-secondary">${esc(a.ref || '—')}${a.obs ? '<div style="font-size:11px">' + esc(a.obs) + '</div>' : ''}</td>
                <td>${esc(a.usuario)}</td>
                <td class="text-end fw-bold text-success">${money(a.monto)}</td>
                <td class="text-end"><button type="button" class="btn btn-sm btn-outline-secondary border py-0 px-2" onclick="verTicketAbono('${esc(a.ticket)}')" title="Reimprimir comprobante"><i class="bi bi-printer"></i></button></td>
            </tr>`).join('')
            : '<tr><td colspan="6" class="text-center text-secondary py-4">Aún no hay abonos registrados.</td></tr>';
        bootstrap.Modal.getOrCreateInstance(document.getElementById('modalHistorialAbonos')).show();
    }));
});
</script>
@endsection