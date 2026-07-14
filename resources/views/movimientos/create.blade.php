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

<!-- 🔥 MENSAJES DE NOTIFICACIÓN DE ÉXITO O ERROR (Faltaban en tu código original) -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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

                    <!-- 🔥 SE AGREGÓ ID container_origen PARA OCULTAR/MOSTRAR CON JS -->
                    <div class="mb-3" id="container_origen">
                        <label class="form-label small fw-semibold text-body">Sucursal Origen (De donde sale) <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-building"></i>
                            </span>
                            <select name="sucursal_origen_id" id="sucursal_origen_id" class="form-select bg-body text-body" required>
                                <option value="">Seleccione sucursal de origen...</option>
                                @foreach($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id }}" 
                                        {{ (old('sucursal_origen_id') ?? $sucursalActivaId) == $sucursal->id ? 'selected' : '' }}>
                                        {{ $sucursal->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div id="stock_origen" class="mt-2 text-secondary small fw-medium" style="display: none;">
                            Stock disponible: <strong id="stock_origen_cantidad" class="text-primary">0</strong> unidades
                        </div>
                    </div>

                    <!-- 🔥 SE AGREGÓ ID container_destino PARA OCULTAR/MOSTRAR CON JS -->
                    <div class="mb-3" id="container_destino">
                        <label class="form-label small fw-semibold text-body">Sucursal Destino (Donde entra) <span class="text-danger">*</span></label>
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
                            <strong>Transferencia:</strong> entre tiendas | <strong>Entrada/Ajuste:</strong> auditoría interna externa.
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
@endsection

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
    
    const containerOrigen = document.getElementById('container_origen');
    const containerDestino = document.getElementById('container_destino');

    if (!document.getElementById('stock_total_info')) {
        const stockTotalInfo = document.createElement('div');
        stockTotalInfo.id = 'stock_total_info';
        stockTotalInfo.className = 'mt-2 text-secondary small';
        stockTotalInfo.style.display = 'none';
        stockTotalInfo.innerHTML = 'Stock total del producto: <strong id="stock_total_cantidad">0</strong> unidades';
        cantidadInput.parentNode.after(stockTotalInfo);
    }

    const stockTotalDiv = document.getElementById('stock_total_info');
    const stockTotalCantidad = document.getElementById('stock_total_cantidad');

    // 🔥 NUEVA FUNCIÓN: Exclusión mutua de sucursales
    function regularSucursalesDisponibles() {
        const origenSeleccionado = sucursalOrigen.value;
        const destinoSeleccionado = sucursalDestino.value;
        const tipoSelected = document.querySelector('input[name="tipo"]:checked').value;

        // Solo procesamos la exclusión si es una transferencia
        if (tipoSelected === 'transferencia') {
            
            // 1. Limpiar sucursal seleccionada en Origen dentro del selector de Destino
            Array.from(sucursalDestino.options).forEach(option => {
                if (option.value !== "" && option.value === origenSeleccionado) {
                    option.style.display = 'none'; // Ocultar en navegadores modernos
                    option.disabled = true;        // Respaldo de seguridad
                    if (destinoSeleccionado === origenSeleccionado) {
                        sucursalDestino.value = ""; // Reset si coincide
                    }
                } else {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            });

            // 2. Limpiar sucursal seleccionada en Destino dentro del selector de Origen
            Array.from(sucursalOrigen.options).forEach(option => {
                if (option.value !== "" && option.value === destinoSeleccionado) {
                    option.style.display = 'none';
                    option.disabled = true;
                } else {
                    option.style.display = 'block';
                    option.disabled = false;
                }
            });
        }
    }

    function toggleSucursalesVisibility() {
        const tipoSelected = document.querySelector('input[name="tipo"]:checked').value;

        if (tipoSelected === 'transferencia') {
            containerOrigen.style.display = 'block';
            sucursalOrigen.setAttribute('required', 'required');
            containerDestino.style.display = 'block';
            sucursalDestino.setAttribute('required', 'required');
            
            // Al cambiar a transferencia, regulamos las opciones
            regularSucursalesDisponibles();
            
        } else if (tipoSelected === 'entrada' || tipoSelected === 'ajuste') {
            containerOrigen.style.display = 'none';
            sucursalOrigen.removeAttribute('required');
            sucursalOrigen.value = '';
            
            containerDestino.style.display = 'block';
            sucursalDestino.setAttribute('required', 'required');
            
            // Habilitar todo en destino ya que no hay origen con el cual chocar
            Array.from(sucursalDestino.options).forEach(o => { o.style.display = 'block'; o.disabled = false; });
            
        } else if (tipoSelected === 'salida') {
            containerOrigen.style.display = 'block';
            sucursalOrigen.setAttribute('required', 'required');
            containerDestino.style.display = 'none';
            sucursalDestino.removeAttribute('required');
            sucursalDestino.value = '';
            
            // Habilitar todo en origen ya que no hay destino con el cual chocar
            Array.from(sucursalOrigen.options).forEach(o => { o.style.display = 'block'; o.disabled = false; });
        }
        
        actualizarStockOrigen();
        actualizarStockDestino();
    }

    function consultarStock(equipoId, sucursalId, callback) {
        if (!equipoId || !sucursalId) {
            callback(null);
            return;
        }
        fetch(`{{ route('movimientos.stock') }}?equipo_id=${equipoId}&sucursal_id=${sucursalId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    callback(data);
                } else {
                    callback(null);
                }
            })
            .catch(() => callback(null));
    }

    function actualizarStockOrigen() {
        const equipoId = equipoSelect.value;
        const sucursalId = sucursalOrigen.value;
        const tipoSelected = document.querySelector('input[name="tipo"]:checked')?.value;
        
        if (!equipoId || !sucursalId || tipoSelected === 'entrada' || tipoSelected === 'ajuste') {
            stockOrigenDiv.style.display = 'none';
            stockTotalDiv.style.display = 'none';
            return;
        }

        consultarStock(equipoId, sucursalId, function(data) {
            if (data !== null) {
                stockOrigenCantidad.textContent = data.stock;
                stockOrigenDiv.style.display = 'block';
                
                stockTotalCantidad.textContent = data.stock_total || data.stock;
                stockTotalDiv.style.display = 'block';
                
                const cantidad = parseInt(cantidadInput.value) || 0;

                if (cantidad > data.stock && (tipoSelected === 'transferencia' || tipoSelected === 'salida')) {
                    cantidadInput.classList.add('is-invalid');
                    document.querySelector('#cantidad-error')?.remove();
                    const error = document.createElement('div');
                    error.id = 'cantidad-error';
                    error.className = 'invalid-feedback d-block';
                    error.textContent = `⚠️ No hay suficiente stock. Disponible: ${data.stock} ${data.unidad || 'unidades'}`;
                    cantidadInput.parentNode.after(error);
                } else if (cantidad > 0) {
                    cantidadInput.classList.remove('is-invalid');
                    cantidadInput.classList.add('is-valid');
                    document.querySelector('#cantidad-error')?.remove();
                }
            } else {
                stockOrigenDiv.style.display = 'none';
                stockTotalDiv.style.display = 'none';
            }
        });
    }

    function actualizarStockDestino() {
        const equipoId = equipoSelect.value;
        const sucursalId = sucursalDestino.value;
        const tipoSelected = document.querySelector('input[name="tipo"]:checked')?.value;
        
        if (!equipoId || !sucursalId || tipoSelected === 'salida') {
            stockDestinoDiv.style.display = 'none';
            return;
        }

        consultarStock(equipoId, sucursalId, function(data) {
            if (data !== null) {
                stockDestinoDiv.style.display = 'block';
                const cantidad = parseInt(cantidadInput.value) || 0;
                const stockActual = data.stock;
                const stockFuturo = stockActual + cantidad;
                stockDestinoCantidad.textContent = `${stockActual} → ${stockFuturo} (después del movimiento)`;
            } else {
                stockDestinoDiv.style.display = 'none';
            }
        });
    }

    equipoSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected.value) {
            productoNombre.textContent = selected.getAttribute('data-nombre') || selected.textContent;
            productoInfo.style.display = 'block';
        } else {
            productoInfo.style.display = 'none';
        }
        actualizarStockOrigen();
        actualizarStockDestino();
    });

    // 🔥 ESCUCHADORES MODIFICADOS: Ejecutan la regulación dinámica
    sucursalOrigen.addEventListener('change', function() {
        regularSucursalesDisponibles();
        actualizarStockOrigen();
    });
    
    sucursalDestino.addEventListener('change', function() {
        regularSucursalesDisponibles();
        actualizarStockDestino();
    });
    
    cantidadInput.addEventListener('input', function() {
        actualizarStockOrigen();
        actualizarStockDestino();
    });
    
    document.querySelectorAll('input[name="tipo"]').forEach(radio => {
        radio.addEventListener('change', function() {
            toggleSucursalesVisibility();
        });
    });

    form.addEventListener('submit', function(e) {
        const tipoSelected = document.querySelector('input[name="tipo"]:checked')?.value;
        const cantidad = parseInt(cantidadInput.value) || 0;
        const stock = parseInt(stockOrigenCantidad.textContent) || 0;
        const origen = sucursalOrigen.value;
        const destino = sucursalDestino.value;
        
        if (tipoSelected === 'transferencia' && origen === destino) {
            e.preventDefault();
            alert('⚠️ La sucursal de origen y destino no pueden ser iguales.');
            return;
        }

        if ((tipoSelected === 'transferencia' || tipoSelected === 'salida') && cantidad > stock) {
            e.preventDefault();
            alert('⚠️ La cantidad excede el stock disponible en la sucursal origen.');
        }
    });

    // Estado inicial
    toggleSucursalesVisibility();

    if (equipoSelect.value) {
        const selectedOption = equipoSelect.options[equipoSelect.selectedIndex];
        productoNombre.textContent = selectedOption.getAttribute('data-nombre') || selectedOption.textContent;
        productoInfo.style.display = 'block';
        actualizarStockOrigen();
        actualizarStockDestino();
    }
});
</script>
@endpush