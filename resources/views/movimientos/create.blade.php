@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <h2 class="mb-0">Registrar Movimiento entre Sucursales</h2>
    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="{{ route('movimientos.store') }}" method="POST" id="formMovimiento">
            @csrf
            
            <div class="row">
                <div class="col-12 col-lg-6">
                    <!-- Información del Producto -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Producto *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-box-seam"></i>
                            </span>
                            <select name="equipo_id" id="equipo_id" class="form-select" required>
                                <option value="">Seleccione un producto...</option>
                                @foreach($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" 
                                            data-nombre="{{ $equipo->nombre }}"
                                            {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                        {{ $equipo->codigo }} - {{ $equipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="info_producto" class="mt-2 text-muted small" style="display: none;">
                            <span id="producto_nombre"></span>
                        </div>
                    </div>

                    <!-- Sucursal Origen -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Sucursal Origen *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-building"></i>
                            </span>
                            <select name="sucursal_origen_id" id="sucursal_origen_id" class="form-select" required>
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
                        <div id="stock_origen" class="mt-2 text-muted small" style="display: none;">
                            Stock disponible: <strong id="stock_origen_cantidad">0</strong> unidades
                        </div>
                    </div>

                    <!-- Sucursal Destino -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Sucursal Destino *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-building"></i>
                            </span>
                            <select name="sucursal_destino_id" id="sucursal_destino_id" class="form-select" required>
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
                    <!-- Cantidad -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Cantidad *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="number" name="cantidad" id="cantidad" 
                                   class="form-control form-control-lg" 
                                   value="{{ old('cantidad', 1) }}" 
                                   min="1" required>
                        </div>
                        <div id="stock_destino" class="mt-2 text-muted small" style="display: none;">
                            Stock en destino actual: <strong id="stock_destino_cantidad">0</strong> unidades
                        </div>
                    </div>

                    <!-- Tipo de Movimiento -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Tipo de Movimiento *</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_transferencia" 
                                       value="transferencia" checked>
                                <label class="form-check-label" for="tipo_transferencia">
                                    <i class="bi bi-arrow-left-right text-info"></i> Transferencia
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" 
                                       value="entrada">
                                <label class="form-check-label" for="tipo_entrada">
                                    <i class="bi bi-arrow-down-circle text-success"></i> Entrada
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_salida" 
                                       value="salida">
                                <label class="form-check-label" for="tipo_salida">
                                    <i class="bi bi-arrow-up-circle text-danger"></i> Salida
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_ajuste" 
                                       value="ajuste">
                                <label class="form-check-label" for="tipo_ajuste">
                                    <i class="bi bi-sliders text-warning"></i> Ajuste
                                </label>
                            </div>
                        </div>
                        <small class="text-muted d-block mt-1">
                            Transferencia: mueve stock entre sucursales | Entrada/Salida: ajuste de inventario
                        </small>
                    </div>

                    <!-- Motivo -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Motivo *</label>
                        <input type="text" name="motivo" class="form-control" 
                               value="{{ old('motivo') }}" 
                               placeholder="Ej: Reabastecimiento, Traslado, Venta, etc." required>
                    </div>

                    <!-- Descripción -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">Descripción (opcional)</label>
                        <textarea name="descripcion" class="form-control" rows="2" 
                                  placeholder="Detalles adicionales del movimiento...">{{ old('descripcion') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                        <i class="bi bi-save me-2"></i> Registrar Movimiento
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal de Confirmación Rápida -->
<div class="modal fade" id="modalConfirmacion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check-circle text-success"></i> Confirmar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="resumen_movimiento"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnConfirmarMovimiento">
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

    // Función para consultar stock
    function consultarStock(equipoId, sucursalId, callback) {
        if (!equipoId || !sucursalId) {
            callback(null);
            return;
        }

        fetch(`{{ route('movimientos.stock') }}?equipo_id=${equipoId}&sucursal_id=${sucursalId}`)
            .then(response => response.json())
            .then(data => {
                callback(data.success ? data.stock : null);
            })
            .catch(() => callback(null));
    }

    // Actualizar stock origen
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
                
                // Validar cantidad
                const cantidad = parseInt(cantidadInput.value) || 0;
                if (cantidad > stock) {
                    cantidadInput.classList.add('is-invalid');
                    document.querySelector('#cantidad-error')?.remove();
                    const error = document.createElement('div');
                    error.id = 'cantidad-error';
                    error.className = 'invalid-feedback d-block';
                    error.textContent = `No hay suficiente stock. Disponible: ${stock}`;
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

    // Actualizar stock destino
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

    // Eventos
    equipoSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            productoNombre.textContent = selected.textContent;
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

    // Validar que origen y destino sean diferentes
    sucursalOrigen.addEventListener('change', function() {
        if (this.value && this.value === sucursalDestino.value) {
            this.classList.add('is-invalid');
            document.querySelector('#origen-error')?.remove();
            const error = document.createElement('div');
            error.id = 'origen-error';
            error.className = 'invalid-feedback d-block';
            error.textContent = 'Origen y destino deben ser diferentes';
            this.parentNode.after(error);
        } else {
            this.classList.remove('is-invalid');
            document.querySelector('#origen-error')?.remove();
        }
    });

    sucursalDestino.addEventListener('change', function() {
        if (this.value && this.value === sucursalOrigen.value) {
            this.classList.add('is-invalid');
            document.querySelector('#destino-error')?.remove();
            const error = document.createElement('div');
            error.id = 'destino-error';
            error.className = 'invalid-feedback d-block';
            error.textContent = 'Origen y destino deben ser diferentes';
            this.parentNode.after(error);
        } else {
            this.classList.remove('is-invalid');
            document.querySelector('#destino-error')?.remove();
        }
    });

    // Cargar datos iniciales
    if (equipoSelect.value && sucursalOrigen.value) {
        productoNombre.textContent = equipoSelect.options[equipoSelect.selectedIndex].textContent;
        productoInfo.style.display = 'block';
        actualizarStockOrigen();
        actualizarStockDestino();
    }
});
</script>
@endpush
@endsection