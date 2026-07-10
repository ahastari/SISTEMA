@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Registrar Movimiento entre Sucursales
        </h3>
        <p class="text-secondary small mb-0">Trasladar stock de equipos, registrar entradas, salidas o ajustes</p>
    </div>
    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> Por favor corrige los siguientes errores:
        <ul class="mb-0 mt-1 small">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3 p-md-4">
        <form action="{{ route('movimientos.store') }}" method="POST" id="formMovimiento">
            @csrf
            
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Producto <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-box-seam"></i>
                            </span>
                            <select name="equipo_id" id="equipo_id" class="form-select bg-body text-body" required>
                                <option value="">Seleccione un producto...</option>
                                @foreach($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" 
                                            data-nombre="{{ $equipo->nombre }}"
                                            {{ old('equipo_id', request('equipo_id')) == $equipo->id ? 'selected' : '' }}>
                                        {{ $equipo->codigo }} - {{ $equipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="info_producto" class="mt-2 text-secondary small fw-medium" style="display: none;">
                            <i class="bi bi-info-circle-fill text-primary me-1"></i> <span id="producto_nombre"></span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Sucursal Origen <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-building"></i>
                            </span>
                            <select name="sucursal_origen_id" id="sucursal_origen_id" class="form-select bg-body text-body" required>
                                @if($sucursalOrigen)
                                    <option value="{{ $sucursalOrigen->id }}" selected>
                                        {{ $sucursalOrigen->nombre }}
                                    </option>
                                @else
                                    <option value="">Seleccione sucursal de origen...</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal_origen_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div id="stock_origen" class="mt-2 text-secondary small fw-medium" style="display: none;">
                            Stock disponible: <strong id="stock_origen_cantidad" class="text-primary">0</strong> unidades
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Sucursal Destino <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-building"></i>
                            </span>
                            <select name="sucursal_destino_id" id="sucursal_destino_id" class="form-select bg-body text-body" required>
                                <option value="">Seleccione sucursal de destino...</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" {{ old('sucursal_destino_id') == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-6">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Cantidad a Mover <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="number" name="cantidad" id="cantidad" 
                                   class="form-control bg-body text-body fw-bold text-primary" 
                                   value="{{ old('cantidad', 1) }}" 
                                   min="1" required>
                        </div>
                        <div id="stock_destino" class="mt-2 text-secondary small fw-medium" style="display: none;">
                            Stock actual en destino: <strong id="stock_destino_cantidad" class="text-success">0</strong> unidades
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body d-block mb-2">Tipo de Movimiento <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-3 py-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_transferencia" value="transferencia" checked>
                                <label class="form-check-label small fw-medium text-body" for="tipo_transferencia">
                                    <i class="bi bi-arrow-left-right text-info me-1"></i> Transferencia
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="entrada">
                                <label class="form-check-label small fw-medium text-body" for="tipo_entrada">
                                    <i class="bi bi-arrow-down-circle text-success me-1"></i> Entrada
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_salida" value="salida">
                                <label class="form-check-label small fw-medium text-body" for="tipo_salida">
                                    <i class="bi bi-arrow-up-circle text-danger me-1"></i> Salida
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_ajuste" value="ajuste">
                                <label class="form-check-label small fw-medium text-body" for="tipo_ajuste">
                                    <i class="bi bi-sliders text-warning me-1"></i> Ajuste
                                </label>
                            </div>
                        </div>
                        <small class="text-secondary d-block mt-1" style="font-size: 11px;">
                            <strong>Transferencia:</strong> entre tiendas | <strong>Entrada/Salida/Ajuste:</strong> auditoría interna externa.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Motivo <span class="text-danger">*</span></label>
                        <input type="text" name="motivo" class="form-control form-control-sm bg-body text-body" 
                               value="{{ old('motivo') }}" 
                               placeholder="Ej: Reabastecimiento de tienda, Traslado por obra, ajuste físico" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-body">Descripción u Observaciones (opcional)</label>
                        <textarea name="descripcion" class="form-control form-control-sm bg-body text-body" rows="2" 
                                  placeholder="Detalles adicionales o notas sobre este movimiento técnico..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm rounded-3">
                        <i class="bi bi-save me-1"></i> Registrar Movimiento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="modal fade" id="modalConfirmacion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-check-circle me-1"></i> Confirmar Movimiento</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div id="resumen_movimiento" class="text-body small"></div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="btnConfirmarMovimiento">
                    <i class="bi bi-check-lg me-1"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formMovimiento');
    const equipoSelect = document.getElementById('equipo_id');
    const sucursalOrigen = document.getElementById('sucursal_origen_id');
    const sucursalDestino = document.getElementById('sucursal_destino_id');
    const cantidadInput = document.getElementById('cantidad');
    const stockOrigenDiv = document.getElementById('stock_origen');
    const stockOrigenCantidad = document.getElementById('stock_origen_cantidad');
    const stockDestinoDiv = document.getElementById('stock_destino');
    const stockDestinoCantidad = document.getElementById('stock_destino_cantidad');
    const productoInfo = document.getElementById('info_producto');
    const productoNombre = document.getElementById('producto_nombre');

    // Función Ajax para consultar stock
    function consultarStock(equipoId, sucursalId, callback) {
        if (!equipoId || !sucursalId) {
            callback(null);
            return;
        }
        fetch(`{{ route('movimientos.stock') }}?equipo_id=${equipoId}&sucursal_id=${sucursalId}`)
            .then(response => response.json())
            .then(data => { callback(data.success ? data.stock : null); })
            .catch(() => callback(null));
    }

    function actualizarStockOrigen() {
        const equipoId = equipoSelect.value;
        const sucursalId = sucursalOrigen.value;
        if (!equipoId || !sucursalId) {
            stockOrigenDiv.style.display = 'none';
            return;
        }

        consultarStock(equipoId, sucursalId, function(stock) {
            if (stock !== null) {
                stockOrigenCantidad.textContent = stock;
                stockOrigenDiv.style.display = 'block';
                
                const cantidad = parseInt(cantidadInput.value) || 0;
                const tipoSelected = document.querySelector('input[name="tipo"]:checked')?.value;

                // Solo validar insuficiencia de stock físico si es Salida o Transferencia
                if (cantidad > stock && (tipoSelected === 'transferencia' || tipoSelected === 'salida')) {
                    cantidadInput.classList.add('is-invalid');
                    document.querySelector('#cantidad-error')?.remove();
                    const error = document.createElement('div');
                    error.id = 'cantidad-error';
                    error.className = 'invalid-feedback d-block';
                    error.textContent = `No hay suficiente stock en almacén origen. Disponible: ${stock}`;
                    cantidadInput.parentNode.after(error);
                } else {
                    cantidadInput.classList.remove('is-invalid');
                    document.querySelector('#cantidad-error')?.remove();
                }
            } else {
                stockOrigenDiv.style.display = 'none';
            }
        });
    }

    function actualizarStockDestino() {
        const equipoId = equipoSelect.value;
        const sucursalId = sucursalDestino.value;
        if (!equipoId || !sucursalId) {
            stockDestinoDiv.style.display = 'none';
            return;
        }
        consultarStock(equipoId, sucursalId, function(stock) {
            if (stock !== null) {
                stockDestinoCantidad.textContent = stock;
                stockDestinoDiv.style.display = 'block';
            } else {
                stockDestinoDiv.style.display = 'none';
            }
        });
    }

    // Escuchadores
    equipoSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            productoNombre.textContent = selected.textContent.trim();
            productoInfo.style.display = 'block';
        } else {
            productoInfo.style.display = 'none';
        }
        actualizarStockOrigen();
        actualizarStockDestino();
    });

    sucursalOrigen.addEventListener('change', actualizarStockOrigen);
    sucursalDestino.addEventListener('change', actualizarStockDestino);
    cantidadInput.addEventListener('input', actualizarStockOrigen);
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', actualizarStockOrigen);
    });

    // Validar Cruce de Sucursales
    function verificarSucursales() {
        const tipoSelected = document.querySelector('input[name="tipo"]:checked')?.value;
        if (tipoSelected === 'transferencia' && sucursalOrigen.value && sucursalOrigen.value === sucursalDestino.value) {
            sucursalOrigen.classList.add('is-invalid');
            sucursalDestino.classList.add('is-invalid');
            return false;
        }
        sucursalOrigen.classList.remove('is-invalid');
        sucursalDestino.classList.remove('is-invalid');
        return true;
    }

    sucursalOrigen.addEventListener('change', verificarSucursales);
    sucursalDestino.addEventListener('change', verificarSucursales);

    // Cargar estados si ya existen datos viejos en recarga
    if (equipoSelect.value) {
        productoNombre.textContent = equipoSelect.options[equipoSelect.selectedIndex].textContent.trim();
        productoInfo.style.display = 'block';
        actualizarStockOrigen();
        actualizarStockDestino();
    }
});
</script>
@endpush
@endsection