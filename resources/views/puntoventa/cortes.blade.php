@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-cash-stack text-primary me-2"></i>Historial de Cortes
        </h3>
    </div>
    <div class="d-flex gap-2">
        <button onclick="recargarTablaCortes()" class="btn btn-outline-secondary btn-sm rounded-3 shadow-sm fw-bold">
            <i class="bi bi-arrow-clockwise me-1"></i> Actualizar Ahora
        </button>
        <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary btn-sm rounded-3 shadow-sm fw-bold">
            <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Regresar 
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3 rounded-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3 rounded-3">{{ session('error') }}</div>
@endif

<div class="alert alert-warning border-0 shadow-sm rounded-3 py-2 small d-flex align-items-start gap-2 mb-3">
    <i class="bi bi-info-circle-fill mt-1"></i>
    <div>
        <strong>Abonos de crédito:</strong> son cobros de ventas a crédito hechas antes. Suman al <em>Efectivo Esperado</em> (si se pagaron en efectivo) pero
        <strong>no son ventas nuevas</strong>. La etiqueta <em>"A crédito"</em> en Ventas indica lo que quedó por cobrar y no entró a caja.
        Lo mismo aplica a los <strong>abonos de renta</strong> (pagos, ampliaciones y liquidaciones de rentas).
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="bg-body-tertiary text-body border-bottom">
                    <tr>
                        <th class="ps-4 py-2">#</th>
                        <th class="py-2">Usuario / Turno</th>
                        <th class="py-2">Apertura / Cierre</th>
                        <th class="py-2 text-end">Fondo Inicial</th>
                        <th class="py-2 text-end">Ventas + Servicios</th>
                        <th class="py-2 text-center">Flujo Efectivo</th>
                        <th class="py-2 text-end bg-body-secondary text-body">Efectivo Esperado</th>
                        <th class="py-2 text-end">Monto Real Físico</th>
                        <th class="py-2 text-end">Diferencia</th>
                        <!-- <th class="pe-4 py-2 text-center">Estado</th> -->
                    </tr>
                </thead>
                <tbody id="contenedorCortesTabla">
                    @forelse($cortes as $corte)
                    @php
                        $ingresosEfe = $corte->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
                        $egresosEfe = $corte->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
                        $efectivoEsperado = $corte->monto_inicial + $corte->total_efectivo + $ingresosEfe - $egresosEfe;

                        // Abonos de crédito: cobros de ventas a crédito ANTERIORES (no son ventas nuevas)
                        $abonosMov = $corte->movimientos
                            ->where('tipo', 'ingreso')
                            ->filter(fn ($m) => \Illuminate\Support\Str::startsWith($m->concepto ?? '', 'Abono crédito'));
                        $abonosEfe   = $abonosMov->where('metodo', 'efectivo')->sum('monto');
                        $abonosOtros = $abonosMov->where('metodo', '!=', 'efectivo')->sum('monto');
                        $abonosMixtosN = $abonosMov->filter(fn ($m) => str_contains($m->concepto ?? '', '(mixto)'))->pluck('concepto')->unique()->count();

                        // Abonos de renta: cobros de rentas (abonos, ampliaciones y liquidaciones)
                        $rentasMov = $corte->movimientos
                            ->where('tipo', 'ingreso')
                            ->filter(fn ($m) => \Illuminate\Support\Str::startsWith($m->concepto ?? '', 'Abono renta'));
                        $rentasEfe   = $rentasMov->where('metodo', 'efectivo')->sum('monto');
                        $rentasOtros = $rentasMov->where('metodo', '!=', 'efectivo')->sum('monto');
                        $rentasDetalle = $rentasMov->map(fn ($m) => [
                            'concepto' => $m->concepto,
                            'metodo'   => $m->metodo,
                            'monto'    => (float) $m->monto,
                            'hora'     => optional($m->created_at)->format('d/m/Y H:i'),
                        ])->values();
                        $otrosIngresosEfe = max(0, $ingresosEfe - $abonosEfe - $rentasEfe);
                        $abonosDetalle = $abonosMov->map(fn ($m) => [
                            'concepto' => $m->concepto,
                            'metodo'   => $m->metodo,
                            'monto'    => (float) $m->monto,
                            'hora'     => optional($m->created_at)->format('d/m/Y H:i'),
                        ])->values();
                        $ventasCredito = 0;

                        // Obtener montos de Flete y Mano de Obra sumando desde las ventas del corte
                        $montoFlete = 0;
                        $montoManoObra = 0;

                        if($corte->ventas) {
                            foreach($corte->ventas as $v) {
                                if ($v->estado !== 'completada') continue;

                                if (($v->metodo_pago ?? '') === 'credito') {
                                    $ventasCredito += $v->total;
                                }
                                
                                foreach($v->detalles as $d) {
                                    if(str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                                        $montoFlete += $d->subtotal;
                                    } elseif(str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                                        $montoManoObra += $d->subtotal;
                                    }
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-secondary">#{{ $corte->id }}</td>
                        <td>
                            <div class="fw-bold text-body">{{ $corte->user->name }}</div>
                            <span class="badge bg-body-tertiary text-body border text-capitalize px-2 py-1 mt-1" style="font-size: 10px;">
                                <i class="bi bi-clock-history me-1"></i>{{ $corte->turno ?? 'Mañana' }}
                            </span>
                        </td>
                        <td>
                            <div class="small text-body"><i class="bi bi-box-arrow-in-right text-success me-1"></i>{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</div>
                            @if($corte->fecha_cierre)
                                <div class="small text-secondary mt-1"><i class="bi bi-box-arrow-left text-danger me-1"></i>{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</div>
                            @else
                                <div class="small text-muted mt-1"><i class="bi bi-dash-lg me-1"></i>En curso...</div>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($corte->monto_inicial, 2) }}</td>
                        
                        <!-- VENTAS + DESGLOSE DE FLETE Y MANO DE OBRA -->
                        <td class="text-end fw-semibold text-primary">
                            ${{ number_format($corte->total_ventas, 2) }}
                            <div class="d-flex flex-column align-items-end mt-1 gap-1" style="font-size: 10px;">
                                @if($montoFlete > 0)
                                    <span class="badge bg-info-subtle text-info border border-info-subtle" title="Monto cobrado por flete">
                                        <i class="bi bi-truck me-1"></i>Flete: ${{ number_format($montoFlete, 2) }}
                                    </span>
                                @endif
                                @if($montoManoObra > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Monto cobrado por mano de obra">
                                        <i class="bi bi-tools me-1"></i>M. Obra: ${{ number_format($montoManoObra, 2) }}
                                    </span>
                                @endif
                                @if($ventasCredito > 0)
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Parte de las ventas del turno que quedó a crédito: NO entró dinero a caja, queda por cobrar">
                                        <i class="bi bi-credit-card-2-front me-1"></i>A crédito: ${{ number_format($ventasCredito, 2) }}
                                    </span>
                                @endif
                                <!-- <span class="text-secondary" style="font-size: 10px;" title="Ventas hechas únicamente en efectivo">
                                    (Efe: ${{ number_format($corte->total_efectivo, 2) }})
                                </span> -->
                            </div>
                        </td>
                        
                        <td class="text-center">
                            @if($ingresosEfe > 0 || $egresosEfe > 0 || $abonosOtros > 0 || $rentasOtros > 0)
                                <div class="d-inline-block text-start" style="font-size: 11px;">
                                    @if($abonosEfe > 0 || $abonosOtros > 0)
                                        <div class="mb-1">
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" title="Cobros de ventas a crédito anteriores. NO son ventas nuevas.">
                                                <i class="bi bi-credit-card-2-front me-1"></i>Abonos crédito
                                            </span>
                                            @if($abonosEfe > 0)
                                                <span class="text-success d-block" title="Abonos de crédito recibidos en efectivo (suman al efectivo esperado)">
                                                    <i class="bi bi-arrow-up-circle-fill"></i> +${{ number_format($abonosEfe, 2) }} <span class="text-secondary">efectivo</span>
                                                </span>
                                            @endif
                                            @if($abonosOtros > 0)
                                                <span class="text-secondary d-block" title="Abonos por transferencia/tarjeta: no afectan el efectivo de caja">
                                                    <i class="bi bi-bank"></i> ${{ number_format($abonosOtros, 2) }} transf./tarj.
                                                </span>
                                            @endif
                                            @if($abonosMixtosN > 0)
                                                <span class="text-secondary d-block" title="Abonos pagados con 2 métodos; cada parte ya está sumada en su método">
                                                    <i class="bi bi-diagram-3"></i> {{ $abonosMixtosN }} en pago mixto
                                                </span>
                                            @endif
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 10px;"
                                                    data-corte="#{{ $corte->id }} · {{ $corte->user->name }}"
                                                    data-abonos="{{ json_encode($abonosDetalle) }}" onclick="verAbonosCorte(this)">
                                                <i class="bi bi-list-ul"></i> ver detalle
                                            </button>
                                        </div>
                                    @endif
                                    @if($rentasEfe > 0 || $rentasOtros > 0)
                                        <div class="mb-1">
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" title="Cobros de rentas (abonos, ampliaciones y liquidaciones). NO son ventas nuevas.">
                                                <i class="bi bi-building me-1"></i>Abonos renta
                                            </span>
                                            @if($rentasEfe > 0)
                                                <span class="text-success d-block" title="Pagos de renta recibidos en efectivo (suman al efectivo esperado)">
                                                    <i class="bi bi-arrow-up-circle-fill"></i> +${{ number_format($rentasEfe, 2) }} <span class="text-secondary">efectivo</span>
                                                </span>
                                            @endif
                                            @if($rentasOtros > 0)
                                                <span class="text-secondary d-block" title="Pagos de renta por transferencia/tarjeta: no afectan el efectivo de caja">
                                                    <i class="bi bi-bank"></i> ${{ number_format($rentasOtros, 2) }} transf./tarj.
                                                </span>
                                            @endif
                                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" style="font-size: 10px;"
                                                    data-corte="#{{ $corte->id }} · {{ $corte->user->name }}"
                                                    data-abonos="{{ json_encode($rentasDetalle) }}" onclick="verAbonosCorte(this)">
                                                <i class="bi bi-list-ul"></i> ver detalle
                                            </button>
                                        </div>
                                    @endif
                                    @if($otrosIngresosEfe > 0)
                                        <span class="text-success d-block" title="Otros ingresos de efectivo registrados en caja">
                                            <i class="bi bi-arrow-up-circle-fill"></i> +${{ number_format($otrosIngresosEfe, 2) }} <span class="text-secondary">otros ingresos</span>
                                        </span>
                                    @endif
                                    @if($egresosEfe > 0)
                                        <span class="text-danger d-block mt-1" title="Egresos adicionales de efectivo">
                                            <i class="bi bi-arrow-down-circle-fill"></i> -${{ number_format($egresosEfe, 2) }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <small class="text-secondary small" style="font-size: 11px;">Sin movimientos</small>
                            @endif
                        </td>
                        
                        <td class="text-end fw-bold bg-body-tertiary text-body font-monospace border-start border-end">
                            ${{ number_format($efectivoEsperado, 2) }}
                            @if(($abonosEfe + $rentasEfe) > 0)
                                <div class="fw-normal text-secondary font-monospace" style="font-size: 10px;" title="Dentro del efectivo esperado hay dinero de abonos de crédito y/o pagos de renta, no solo de ventas del turno">
                                    incl. ${{ number_format($abonosEfe + $rentasEfe, 2) }} de abonos
                                </div>
                            @endif
                        </td>
                        
                        <td class="text-end fw-bold text-body font-monospace">
                            {{ $corte->monto_final ? '$' . number_format($corte->monto_final, 2) : '-' }}
                        </td>
                        
                        <td class="text-end fw-bold font-monospace">
                            @if($corte->estado == 'abierto')
                                <!-- <span class="text-muted small fw-normal">Calculando...</span> -->
                            @else
                                @if(($corte->diferencia ?? 0) < 0)
                                    <span class="text-danger" title="Faltante en caja">
                                        <i class="bi bi-dash-circle"></i> -${{ number_format(abs($corte->diferencia), 2) }}
                                    </span>
                                @elseif(($corte->diferencia ?? 0) > 0)
                                    <span class="text-success" title="Sobrante en caja">
                                        <i class="bi bi-plus-circle"></i> +${{ number_format($corte->diferencia, 2) }}
                                    </span>
                                @else
                                    <span class="text-secondary"><i class="bi bi-check-all text-success"></i> $0.00</span>
                                @endif
                            @endif
                        </td>
                        
                        <!-- <td class="pe-4 text-center">
                            @if($corte->estado == 'abierto')
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1 border border-success-subtle">
                                    <span class="spinner-grow spinner-grow-sm align-middle me-1" style="width: 8px; height: 8px;" role="status"></span> Abierta
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1 border border-secondary-subtle">
                                    Cerrada
                                </span>
                            @endif
                        </td> -->
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-secondary">
                            <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                            No hay cortes de caja registrados en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $cortes->links() }}
</div>

{{-- Detalle de los abonos de crédito recibidos en un corte --}}
<div class="modal fade" id="modalAbonosCorte" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-credit-card-2-front me-2"></i>Abonos recibidos · corte <span id="abonosCorteTitulo"></span></h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0" style="font-size: 13px;">
                        <thead class="bg-body-tertiary"><tr><th>Fecha</th><th>Concepto</th><th>Método</th><th class="text-end">Monto</th></tr></thead>
                        <tbody id="abonosCorteCuerpo"></tbody>
                        <tfoot class="bg-body-tertiary"><tr><td colspan="3" class="text-end fw-bold">Total</td><td class="text-end fw-bold" id="abonosCorteTotal"></td></tr></tfoot>
                    </table>
                </div>
                <div class="form-text mt-2">Solo los abonos en <strong>efectivo</strong> suman al efectivo esperado del corte.</div>
            </div>
        </div>
    </div>
</div>

<!-- SCRIPT DE AUTO-RECARGA DINÁMICA -->
<script>
function verAbonosCorte(btn) {
    const abonos = JSON.parse(btn.dataset.abonos || '[]');
    const money = n => '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    document.getElementById('abonosCorteTitulo').textContent = btn.dataset.corte;
    document.getElementById('abonosCorteCuerpo').innerHTML = abonos.map(a => `
        <tr>
            <td>${esc(a.hora)}</td>
            <td>${esc(a.concepto)}</td>
            <td class="text-capitalize">${esc(a.metodo)}</td>
            <td class="text-end fw-bold text-success">${money(a.monto)}</td>
        </tr>`).join('');
    document.getElementById('abonosCorteTotal').textContent = money(abonos.reduce((t, a) => t + a.monto, 0));
    bootstrap.Modal.getOrCreateInstance(document.getElementById('modalAbonosCorte')).show();
}

function recargarTablaCortes() {
    fetch(window.location.href, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const nuevoContenido = doc.getElementById('contenedorCortesTabla');
        if (nuevoContenido) {
            document.getElementById('contenedorCortesTabla').innerHTML = nuevoContenido.innerHTML;
        }
    })
    .catch(error => console.error('Error al actualizar historial:', error));
}

// Auto-refrescar cada 10 segundos
setInterval(recargarTablaCortes, 10000);
</script>
@endsection