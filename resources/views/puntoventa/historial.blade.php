@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-clock-history text-primary me-2"></i>Historial de Ventas Realizadas
        </h3>
        <p class="text-secondary small mb-0">Audita transacciones comerciales, realiza devoluciones de stock o reimprime comprobantes.</p>
    </div>
    
    <form action="{{ route('puntoventa.historial') }}" method="GET" class="d-flex gap-2 w-100 w-md-auto">
        <div class="input-group input-group-sm border rounded-3 overflow-hidden shadow-sm">
            <span class="input-group-text bg-body-tertiary border-0 text-secondary"><i class="bi bi-calendar-event"></i></span>
            <input type="date" name="fecha" class="form-control bg-body border-0 text-body p-2 fw-semibold" 
                   value="{{ $fechaFiltro }}" onchange="this.form.submit()">
        </div>
        <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm fw-bold px-3 d-inline-flex align-items-center">
            <i class="bi bi-arrow-left-short fs-5 me-1"></i> POS
        </a>
    </form>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3 rounded-3">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-body-tertiary text-body border-bottom">
                    <tr>
                        <th class="ps-4 py-2">Folio</th>
                        <th class="py-2">Hora</th>
                        <th class="py-2">Cliente Destinatario</th>
                        <th class="py-2 text-center">Método de Pago</th>
                        <th class="py-2 text-end">Subtotal</th>
                        <th class="py-2 text-end">IVA (16%)</th>
                        <th class="py-2 text-end">Total Liquidado</th>
                        <th class="py-2 text-center">Estado</th>
                        <th class="pe-4 py-2 text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                    <tr style="{{ $venta->estado == 'cancelada' ? 'opacity: 0.55; text-decoration: line-through;' : '' }}">
                        <td class="ps-4 font-monospace fw-bold text-body">{{ $venta->folio }}</td>
                        <td class="text-secondary">{{ $venta->created_at->format('H:i') }} hrs</td>
                        <td>
                            <div class="fw-semibold text-body">{{ $venta->cliente_nombre ?? ($venta->cliente->nombre_completo ?? 'Público General') }}</div>
                            @if($venta->requiere_factura && $venta->rfc_cliente)
                                <small class="text-primary" style="font-size: 11px;"><i class="bi bi-file-earmark-spreadsheet me-1"></i>RFC: {{ $venta->rfc_cliente }}</small>
                            @endif
                        </td>
                        <td class="text-center text-uppercase">
                            <span class="badge bg-body border text-body px-2.5 py-1.5" style="font-size: 11px; font-weight: 600;">
                                @if($venta->metodo_pago == 'efectivo')
                                    <i class="bi bi-cash text-success me-1"></i>
                                @elseif($venta->metodo_pago == 'transferencia')
                                    <i class="bi bi-arrow-right-short text-info me-1"></i>
                                @else
                                    <i class="bi bi-credit-card text-primary me-1"></i>
                                @endif
                                {{ $venta->metodo_pago }}
                            </span>
                        </td>
                        <td class="text-end text-secondary">${{ number_format($venta->subtotal, 2) }}</td>
                        <td class="text-end text-secondary">${{ number_format($venta->iva, 2) }}</td>
                        <td class="text-end fw-bold text-success fs-6">${{ number_format($venta->total, 2) }}</td>
                        <td class="text-center">
                            @if($venta->estado == 'completada')
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1 border border-success-subtle">
                                    Aprobada
                                </span>
                            @else
                                <span class="badge rounded-pill bg-danger-subtle text-danger px-3 py-1 border border-danger-subtle">
                                    Cancelada
                                </span>
                            @endif
                        </td>
                        <td class="pe-4 text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-outline-secondary border p-1 px-2 text-body" title="Reimprimir Ticket" onclick="reimprimirTicket({{ $venta->id }})">
                                    <i class="bi bi-printer"></i>
                                </button>
                                
                                @if($venta->estado == 'completada')
                                <form action="{{ route('puntoventa.cancelar', $venta->id) }}" method="POST" class="m-0 d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger border p-1 px-2" title="Cancelar Venta" 
                                            onclick="return confirm('¿Seguro que deseas cancelar la venta {{ $venta->folio }}? El dinero se descontará de caja y los productos volverán al almacén.')">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-sm btn-outline-secondary border p-1 px-2" disabled>
                                    <i class="bi bi-dash-circle"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-secondary">
                            <i class="bi bi-receipt-cutoff fs-2 d-block mb-2 opacity-50"></i>
                            No se encontraron transacciones registradas para el día {{ date('d/m/Y', strtotime($fechaFiltro)) }}.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalReimpresionTicket" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 380px;">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Reimpresión de Ticket</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeReimpresion" src="" style="width: 100%; height: 450px; border: none; display: block; background: #fff;"></iframe>
            </div>
            <div class="modal-footer py-2 bg-body d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" onclick="ejecutarImpresionIframe()">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function reimprimirTicket(ventaId) {
    const iframe = document.getElementById('iframeReimpresion');
    if (iframe) {
        iframe.src = `/puntoventa/ticket/${ventaId}`;
        const modalEl = document.getElementById('modalReimpresionTicket');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }
}

function ejecutarImpresionIframe() {
    const iframe = document.getElementById('iframeReimpresion');
    if (iframe) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}
</script>
@endsection