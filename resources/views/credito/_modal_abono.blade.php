{{-- Modal de abono + modal del comprobante. Se incluye en la cartera y en el detalle del crédito.
     Requiere: $cajaAbierta. Los botones con la clase .btn-abonar abren el modal.
     Sin caja abierta NO se puede abonar (ningún método): el botón muestra un aviso en su lugar. --}}

<div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <form method="POST" id="formAbono" action="">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold"><i class="bi bi-cash-coin me-2"></i>Registrar abono</h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="small mb-3">
                        <div class="text-secondary">Venta <strong id="abonoFolio" class="text-body"></strong></div>
                        <div class="text-secondary">Cliente: <strong id="abonoCliente" class="text-body"></strong></div>
                        <div class="text-secondary">Saldo pendiente: <strong id="abonoSaldo" class="text-warning-emphasis"></strong></div>
                    </div>
                    <label class="form-label small fw-bold text-body mb-1">Monto del abono</label>
                    <div class="input-group input-group-sm mb-1">
                        <span class="input-group-text">$</span>
                        <input type="number" name="monto" id="abonoMonto" class="form-control bg-body text-body fw-bold" step="0.01" min="0.01" required>
                        <button type="button" class="btn btn-outline-success" id="btnLiquidar" title="Poner el saldo completo">Liquidar</button>
                    </div>
                    <label class="form-label small fw-bold text-body mt-2 mb-1">Método de pago</label>
                    <select name="metodo" id="abonoMetodo" class="form-select form-select-sm bg-body text-body" required>
                        <option value="efectivo" selected>Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                        <option value="mixto">Mixto (2 métodos)</option>
                    </select>

                    {{-- Desglose del pago mixto: dos métodos distintos; el monto total es la suma --}}
                    <div id="seccionAbonoMixto" class="border rounded-3 p-2 mt-2 bg-body-tertiary" style="display: none;">
                        @foreach([1, 2] as $i)
                            <div class="row g-1 {{ $i === 1 ? 'mb-1' : '' }}">
                                <div class="col-6">
                                    <select id="mixtoMetodo{{ $i }}" name="pagos_mixtos[{{ $i - 1 }}][metodo]" class="form-select form-select-sm bg-body text-body" disabled>
                                        <option value="efectivo">Efectivo</option>
                                        <option value="transferencia">Transferencia</option>
                                        <option value="tarjeta">Tarjeta</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">$</span>
                                        <input type="number" id="mixtoMonto{{ $i }}" name="pagos_mixtos[{{ $i - 1 }}][monto]" class="form-control bg-body text-body" step="0.01" min="0.01" placeholder="0.00" disabled>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <div class="form-text" style="font-size: 11px;">El monto del abono se calcula sumando los dos pagos.</div>
                    </div>
                    <label class="form-label small fw-bold text-body mt-2 mb-1">Referencia <span class="text-secondary fw-normal">(opcional)</span></label>
                    <input type="text" name="referencia" maxlength="100" class="form-control form-control-sm bg-body text-body" placeholder="No. de transferencia, autorización...">
                    <label class="form-label small fw-bold text-body mt-2 mb-1">Observaciones <span class="text-secondary fw-normal">(opcional)</span></label>
                    <input type="text" name="observaciones" maxlength="255" class="form-control form-control-sm bg-body text-body">
                    <div class="form-text" style="font-size: 11px;">Los abonos en efectivo entran a tu caja abierta como ingreso.</div>
                </div>
                <div class="modal-footer py-2 bg-body d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold px-3" id="btnGuardarAbono"><i class="bi bi-check-lg me-1"></i>Registrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Aviso cuando no hay caja abierta: impide registrar abonos --}}
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
                <h6 class="fw-bold text-body mt-2 mb-1">No puedes registrar abonos</h6>
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
@endunless

{{-- Comprobante del abono (se abre solo después de registrar) --}}
<div class="modal fade" id="modalTicketAbono" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de abono</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeTicketAbono" src="" style="width: 100%; height: 470px; border: none; display: block; background: #fff;"></iframe>
            </div>
            <div class="modal-footer py-2 bg-body d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" id="btnImprimirTicketAbono"><i class="bi bi-printer-fill me-1"></i>Imprimir</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Bootstrap (Vite) carga diferido: se usa cuando el DOM está listo y se instancia al hacer clic
    const abrirModal = id => bootstrap.Modal.getOrCreateInstance(document.getElementById(id)).show();
    const money = n => '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    let saldoActual = 0;
    const cajaAbierta = @json($cajaAbierta);

    const $ = id => document.getElementById(id);
    const selMetodo = $('abonoMetodo'), seccionMixto = $('seccionAbonoMixto'), inputMonto = $('abonoMonto');
    const mixtoEls = ['mixtoMetodo1', 'mixtoMonto1', 'mixtoMetodo2', 'mixtoMonto2'].map($);
    const esMixto = () => selMetodo.value === 'mixto';

    // En mixto el monto total es solo lectura: suma de los dos pagos
    function sumarMixto() {
        if (!esMixto()) return;
        const t = (parseFloat($('mixtoMonto1').value) || 0) + (parseFloat($('mixtoMonto2').value) || 0);
        inputMonto.value = t > 0 ? t.toFixed(2) : '';
    }
    function alternarMixto() {
        const m = esMixto();
        seccionMixto.style.display = m ? 'block' : 'none';
        mixtoEls.forEach(el => el.disabled = !m);   // deshabilitado = no se envía
        inputMonto.readOnly = m;
        inputMonto.required = !m;
        if (m) {
            $('mixtoMetodo1').value = 'efectivo';
            $('mixtoMetodo2').value = 'transferencia';
            ['mixtoMonto1', 'mixtoMonto2'].forEach(id => $(id).value = '');
            inputMonto.value = '';
        }
    }
    selMetodo.addEventListener('change', alternarMixto);
    ['mixtoMonto1', 'mixtoMonto2'].forEach(id => $(id).addEventListener('input', sumarMixto));

    document.querySelectorAll('.btn-abonar').forEach(btn => btn.addEventListener('click', () => {
        // Sin caja abierta no se puede abonar: se muestra el aviso en lugar del formulario
        if (!cajaAbierta) { abrirModal('modalCajaCerrada'); return; }

        saldoActual = parseFloat(btn.dataset.saldo) || 0;
        $('formAbono').action = btn.dataset.action;
        $('abonoFolio').textContent = btn.dataset.folio;
        $('abonoCliente').textContent = btn.dataset.cliente;
        $('abonoSaldo').textContent = money(saldoActual);
        selMetodo.value = 'efectivo';
        alternarMixto();
        inputMonto.max = saldoActual.toFixed(2);
        inputMonto.value = '';
        const b = $('btnGuardarAbono');
        b.disabled = false;
        b.innerHTML = '<i class="bi bi-check-lg me-1"></i>Registrar';
        abrirModal('modalAbono');
        setTimeout(() => (esMixto() ? $('mixtoMonto1') : inputMonto).focus(), 300);
    }));

    $('btnLiquidar').addEventListener('click', () => {
        if (esMixto()) {
            // Completa el segundo pago con lo que falte para liquidar
            const m1 = parseFloat($('mixtoMonto1').value) || 0;
            $('mixtoMonto2').value = Math.max(0, saldoActual - m1).toFixed(2);
            sumarMixto();
        } else {
            inputMonto.value = saldoActual.toFixed(2);
        }
    });

    $('formAbono').addEventListener('submit', function (e) {
        // Segunda barrera en el navegador (el servidor también debe validarlo)
        if (!cajaAbierta) {
            e.preventDefault();
            abrirModal('modalCajaCerrada');
            return;
        }
        if (esMixto()) {
            const m1 = $('mixtoMetodo1').value, m2 = $('mixtoMetodo2').value;
            const a1 = parseFloat($('mixtoMonto1').value) || 0, a2 = parseFloat($('mixtoMonto2').value) || 0;
            if (m1 === m2) { e.preventDefault(); alert('En pago mixto debes elegir dos métodos de pago diferentes.'); return; }
            if (a1 <= 0 || a2 <= 0) { e.preventDefault(); alert('Captura el monto de los dos pagos (mayor a $0 cada uno).'); return; }
            sumarMixto();
        }
        const monto = parseFloat(inputMonto.value) || 0;
        if (monto <= 0 || monto > saldoActual + 0.009) {
            e.preventDefault();
            alert('El abono debe ser mayor a $0 y no puede exceder el saldo (' + money(saldoActual) + ').');
            return;
        }
        const b = $('btnGuardarAbono');
        b.disabled = true;
        b.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Guardando...';
    });

    // Comprobante
    window.verTicketAbono = function (url) {
        document.getElementById('iframeTicketAbono').src = url;
        abrirModal('modalTicketAbono');
    };
    document.getElementById('btnImprimirTicketAbono').addEventListener('click', () => {
        const f = document.getElementById('iframeTicketAbono');
        f.contentWindow.focus();
        f.contentWindow.print();
    });
    document.getElementById('modalTicketAbono').addEventListener('hidden.bs.modal', () => {
        document.getElementById('iframeTicketAbono').src = '';
    });

    @if(session('abono_id'))
        verTicketAbono(@json(route('puntoventa.creditos.ticket', session('abono_id'))));
    @endif
});
</script>