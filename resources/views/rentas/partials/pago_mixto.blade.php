{{-- Uso: @include('rentas.partials.pago_mixto', ['id' => 'pago']) --}}
<div class="border rounded-3 p-2 bg-body-tertiary mt-2" id="mixto_{{ $id }}" style="display:none;">
    <div class="small fw-bold mb-2 text-body"><i class="bi bi-shuffle me-1 text-primary"></i>Desglose del pago mixto</div>
    @foreach([1, 2] as $n)
    <div class="row g-2 mb-2 mixto-parte">
        <div class="col-6">
            <label class="form-label small mb-0 text-body">Método {{ $n }}</label>
            <select name="mixto_metodo_{{ $n }}" class="form-select form-select-sm bg-body mixto-metodo" onchange="mixtoCambio('{{ $id }}')" disabled>
                <option value="efectivo">Efectivo</option>
                <option value="transferencia" {{ $n == 2 ? 'selected' : '' }}>Transferencia</option>
                <option value="tarjeta">Tarjeta</option>
            </select>
        </div>
        <div class="col-6">
            <label class="form-label small mb-0 text-body">Monto {{ $n }}</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-body text-secondary">$</span>
                <input type="number" step="0.01" min="0" name="mixto_monto_{{ $n }}" class="form-control bg-body mixto-monto" placeholder="0.00" oninput="mixtoCambio('{{ $id }}')" disabled>
            </div>
        </div>
        <div class="col-12 mixto-ref-wrap" style="display:none;">
            <input type="text" name="mixto_ref_{{ $n }}" class="form-control form-control-sm bg-body" placeholder="Referencia / folio del método {{ $n }}" disabled>
        </div>
    </div>
    @endforeach
    <div class="d-flex justify-content-between small border-top pt-1">
        <span class="text-secondary">Suma capturada:</span>
        <strong id="mixto_suma_{{ $id }}" class="text-primary">$0.00</strong>
    </div>
</div>