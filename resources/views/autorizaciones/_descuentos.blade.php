{{-- 5. SOLICITUDES DE DESCUENTO Y CRÉDITO (POS) --}}
@if($totalDescuentos > 0)
<div id="seccion-descuentos" class="mb-5 seccion-ancla">
    <h6 class="fw-bold text-body mb-3 text-uppercase" style="letter-spacing: 0.5px;">
        <i class="bi bi-circle-fill text-success me-2" style="font-size: 8px; vertical-align: middle;"></i>Descuentos y Créditos en Punto de Venta
    </h6>
    <div class="row g-3">
        @foreach($descuentosPendientes as $desc)
        @php
            $esCredito = ($desc->tipo ?? 'descuento') === 'credito';
            $nombreSucursal = ($sucursalesNombres[$desc->sucursal_id] ?? null) ?: 'Sin sucursal';
        @endphp
        <div class="col-12 col-md-6 col-xl-4">
            <div class="auth-card type-descuento">
                <div class="auth-header">
                    @if($esCredito)
                        <span class="badge bg-warning text-dark font-monospace px-2 py-1"><i class="bi bi-credit-card-2-front me-1"></i>Crédito</span>
                    @else
                        <span class="badge bg-success text-white font-monospace px-2 py-1"><i class="bi bi-percent me-1"></i>Descuento</span>
                    @endif
                    <span class="text-secondary" style="font-size: 11px;"><i class="bi bi-clock me-1"></i>{{ $desc->created_at->diffForHumans() }}</span>
                </div>
                <div class="auth-body">
                    <h6 class="fw-bold text-body mb-1 text-truncate">
                        <i class="bi bi-person text-success me-1"></i> {{ $desc->user->name ?? 'Cajero' }}
                        <small class="text-secondary fw-normal">(cajero)</small>
                    </h6>
                    <div class="d-flex align-items-center mb-2 small">
                        <i class="bi bi-person-badge text-success me-1"></i>
                        <span class="text-secondary me-1">Cliente:</span>
                        <strong class="text-body text-truncate">{{ $desc->cliente_nombre ?: 'Público General' }}</strong>
                    </div>
                    <div class="d-flex align-items-center mb-2 small">
                        <i class="bi bi-shop text-success me-1"></i>
                        <span class="text-secondary me-1">Sucursal:</span>
                        <strong class="text-body text-truncate">{{ $nombreSucursal }}</strong>
                    </div>
                    @if($esCredito)
                    <div class="d-flex align-items-center mb-2 small">
                        <i class="bi bi-calendar-event text-warning me-1"></i>
                        <span class="text-secondary me-1">Plazo solicitado:</span>
                        <strong class="text-body">{{ $desc->dias_credito ? $desc->dias_credito . ' días' : 'No especificado' }}</strong>
                    </div>
                    @endif
                    <div class="p-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 small text-body mb-3">
                        <strong class="text-success-emphasis d-block mb-1"><i class="bi bi-info-circle me-1"></i>Justificación Cajero:</strong>
                        {{ $desc->motivo }}
                    </div>
                    @if($esCredito)
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <span class="d-block text-secondary" style="font-size: 11px;">Límite sin autorización:</span>
                                <span class="fw-semibold text-body small">${{ number_format($desc->limite_credito ?? 0, 2) }}</span>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-secondary" style="font-size: 11px;">Total a crédito:</span>
                                <strong class="text-warning-emphasis fs-6">${{ number_format($desc->monto, 2) }}</strong>
                                @if($desc->limite_credito !== null)
                                    <small class="d-block text-secondary" style="font-size: 10px;">(excede por ${{ number_format($desc->monto - $desc->limite_credito, 2) }})</small>
                                @endif
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <span class="d-block text-secondary" style="font-size: 11px;">Subtotal de la venta:</span>
                                <span class="fw-semibold text-body small">${{ number_format($desc->subtotal, 2) }}</span>
                            </div>
                            <div class="text-end">
                                <span class="d-block text-secondary" style="font-size: 11px;">Descuento solicitado:</span>
                                <strong class="text-success fs-6">-${{ number_format($desc->monto, 2) }}</strong>
                                @if($desc->subtotal > 0)
                                    <small class="d-block text-secondary" style="font-size: 10px;">({{ number_format(($desc->monto / $desc->subtotal) * 100, 1) }}% del subtotal)</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
                <div class="auth-footer d-flex gap-2">
                    <form action="{{ route('autorizaciones.aprobarDescuento', $desc) }}" method="POST" class="flex-fill form-autorizacion">
                        @csrf
                        <button type="button" class="btn btn-success btn-sm w-100 fw-bold rounded-3 btn-submit-auth"
                                data-confirm="{{ $esCredito
                                    ? '¿Autorizar el crédito de $' . number_format($desc->monto, 2) . ' para el cliente ' . ($desc->cliente_nombre ?: 'Público General') . ' (sucursal ' . $nombreSucursal . ', a ' . ($desc->dias_credito ?: '?') . ' días)?'
                                    : '¿Aprobar el descuento de $' . number_format($desc->monto, 2) . ' para el cliente ' . ($desc->cliente_nombre ?: 'Público General') . ' (sucursal ' . $nombreSucursal . ')?' }}">
                            <i class="bi bi-check-lg me-1"></i> Aprobar
                        </button>
                    </form>
                    <form action="{{ route('autorizaciones.rechazarDescuento', $desc) }}" method="POST" class="flex-fill form-autorizacion">
                        @csrf
                        <button type="button" class="btn btn-outline-danger btn-sm w-100 fw-bold rounded-3 btn-submit-auth"
                                data-confirm="{{ $esCredito
                                    ? '¿Rechazar el crédito de ' . ($desc->cliente_nombre ?: 'Público General') . '? El cajero deberá cambiar la forma de pago.'
                                    : '¿Rechazar el descuento de ' . ($desc->cliente_nombre ?: 'Público General') . '? El cajero deberá cobrar sin descuento.' }}">
                            <i class="bi bi-x-lg me-1"></i> Rechazar
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif