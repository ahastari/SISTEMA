@extends('layouts.admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-arrow-left-right me-2 text-primary"></i>Registrar Movimiento de Inventario
        </h3>
        <p class="text-secondary small mb-0">Gestión de ingresos autorizados y envíos a otras sucursales</p>
    </div>
    <a href="{{ route('movimientos.index') }}" class="btn btn-outline-secondary btn-sm rounded-3">
        <i class="bi bi-arrow-left me-1"></i> Regresar
    </a>
</div>

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

        <div id="banner_info_movimiento" class="alert alert-info border-info-subtle bg-info-subtle text-info-emphasis rounded-3 p-3 mb-4 small">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill fs-5 me-2 mt-1 text-info"></i>
                <div id="banner_texto">
                    <strong>Modo Ingreso:</strong> Seleccione el envío <strong>autorizado por el gerente</strong> de la sucursal de origen. Solo aparecen envíos que ya fueron aprobados.
                </div>
            </div>
        </div>

        <form action="{{ route('movimientos.store') }}" method="POST" id="formMovimiento">
            @csrf
            
            <div class="row g-3">
                <div class="col-12 col-lg-6">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body d-block mb-2">Tipo de Operación <span class="text-danger">*</span></label>
                        <div class="d-flex flex-wrap gap-4 py-1">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_entrada" value="entrada" 
                                    {{ old('tipo', 'entrada') === 'entrada' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-success" for="tipo_entrada">
                                    <i class="bi bi-arrow-down-circle fs-6 me-1"></i> Ingreso de Productos (Entrada)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo" id="tipo_salida" value="salida" 
                                    {{ old('tipo') === 'salida' ? 'checked' : '' }}>
                                <label class="form-check-label small fw-bold text-danger" for="tipo_salida">
                                    <i class="bi bi-arrow-up-circle fs-6 me-1"></i> Envío / Transferencia a Otra Sucursal
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN INGRESO (ENTRADA) --}}
                    <div id="seccion_entrada">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-body">
                                Sucursal de donde se recibe (Origen) - Envíos Autorizados <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary">
                                    <i class="bi bi-building-check"></i>
                                </span>
                                <select name="movimiento_autorizado_id" id="movimiento_autorizado_id" class="form-select bg-body text-body">
                                    <option value="">Seleccione el envío a recibir...</option>
                                    @forelse($transferenciasAprobadas as $transf)
                                        <option value="{{ $transf->id }}" 
                                                data-equipo-id="{{ $transf->equipo_id }}" 
                                                data-cantidad="{{ $transf->cantidad }}"
                                                data-motivo="{{ $transf->motivo }}">
                                            Desde: {{ $transf->sucursalOrigen->nombre }} | Prod: {{ $transf->equipo->nombre }} (Cant: {{ $transf->cantidad }})
                                        </option>
                                    @empty
                                        <option value="" disabled>No hay envíos autorizados pendientes de recibir</option>
                                    @endforelse
                                </select>
                            </div>
                            @if($transferenciasAprobadas->isEmpty())
                                <small class="text-warning d-block mt-1" style="font-size: 11px;">
                                    ⚠️ No hay envíos autorizados pendientes. El gerente de la sucursal de origen debe aprobar la transferencia primero.
                                </small>
                            @endif
                        </div>
                    </div>

                    {{-- SECCIÓN ENVÍO (SALIDA) --}}
                    <div id="seccion_salida">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-body">Producto <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary">
                                    <i class="bi bi-box-seam"></i>
                                </span>
                                <select name="equipo_id" id="equipo_id" class="form-select bg-body text-body">
                                    <option value="">Seleccione un producto...</option>
                                    @foreach($equipos as $equipo)
                                        <option value="{{ $equipo->id }}" {{ old('equipo_id') == $equipo->id ? 'selected' : '' }}>
                                            {{ $equipo->codigo }} - {{ $equipo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div id="stock_origen" class="mt-2 text-secondary small fw-medium" style="display: none;">
                                Stock disponible en tu sucursal: <strong id="stock_origen_cantidad" class="text-primary">0</strong> unidades
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-body">Sucursal Destino (A dónde se envía) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body-tertiary text-secondary">
                                    <i class="bi bi-building"></i>
                                </span>
                                <select name="sucursal_destino_id" id="sucursal_destino_id" class="form-select bg-body text-body">
                                    <option value="">Seleccione la sucursal destino...</option>
                                    @foreach($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id }}" {{ old('sucursal_destino_id') == $sucursal->id ? 'selected' : '' }}>
                                            {{ $sucursal->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="col-12 col-lg-6">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body" id="label_cantidad">Cantidad Autorizada a Recibir <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary">
                                <i class="bi bi-hash"></i>
                            </span>
                            <input type="number" name="cantidad" id="cantidad" 
                                   class="form-control bg-body text-body fw-bold text-primary" 
                                   value="{{ old('cantidad', 1) }}" 
                                   min="1" required readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Motivo <span class="text-danger">*</span></label>
                        <input type="text" name="motivo" id="motivo" class="form-control form-control-sm bg-body text-body" 
                               value="{{ old('motivo') }}" 
                               placeholder="Ej: Recepción por traspaso de tienda" required>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-body">Observaciones (opcional)</label>
                        <textarea name="descripcion" class="form-control form-control-sm bg-body text-body" rows="3" 
                                  placeholder="Detalles adicionales sobre este movimiento..."></textarea>
                    </div>

                    <button type="submit" id="btn_submit" class="btn btn-primary fw-bold w-100 py-2 shadow-sm rounded-3">
                        <i class="bi bi-box-arrow-in-down me-1"></i> Confirmar Ingreso de Mercancía
                    </button>

                </div>
            </div>
        </form>
    </div>
</div>

{{-- Datos de Laravel para JavaScript --}}
<script>
    window.movimientosData = {
        sucursalActivaId: "{{ session('activo_sucursal_id') }}",
        stockRoute: "{{ route('movimientos.stock') }}"
    };
</script>

{{-- Script principal --}}
<script>
(function() {
    var movimientosData = window.movimientosData;
    
    function mostrarSeccion(tipo) {
        var seccionEntrada = document.getElementById('seccion_entrada');
        var seccionSalida = document.getElementById('seccion_salida');
        var movimientoSelect = document.getElementById('movimiento_autorizado_id');
        var equipoSelect = document.getElementById('equipo_id');
        var sucursalSelect = document.getElementById('sucursal_destino_id');
        var cantidadInput = document.getElementById('cantidad');
        var stockDiv = document.getElementById('stock_origen');
        var labelCantidad = document.getElementById('label_cantidad');
        var btnSubmit = document.getElementById('btn_submit');
        var bannerTexto = document.getElementById('banner_texto');
        var motivoInput = document.getElementById('motivo');
        
        if (!seccionEntrada || !seccionSalida) return;
        
        if (tipo === 'entrada') {
            seccionSalida.style.setProperty('display', 'none', 'important');
            seccionSalida.classList.add('d-none');
            seccionSalida.setAttribute('hidden', '');
            
            seccionEntrada.style.setProperty('display', 'block', 'important');
            seccionEntrada.classList.remove('d-none');
            seccionEntrada.removeAttribute('hidden');
            
            if (movimientoSelect) {
                movimientoSelect.setAttribute('required', 'required');
                movimientoSelect.disabled = false;
            }
            if (equipoSelect) {
                equipoSelect.removeAttribute('required');
                equipoSelect.disabled = true;
                equipoSelect.value = '';
            }
            if (sucursalSelect) {
                sucursalSelect.removeAttribute('required');
                sucursalSelect.disabled = true;
                sucursalSelect.value = '';
            }
            if (cantidadInput) {
                cantidadInput.setAttribute('readonly', 'readonly');
                cantidadInput.classList.remove('is-invalid', 'is-valid');
                var errorAnterior = document.querySelector('#cantidad-error');
                if (errorAnterior) errorAnterior.remove();
            }
            if (stockDiv) {
                stockDiv.style.display = 'none';
            }
            if (labelCantidad) {
                labelCantidad.innerHTML = 'Cantidad Autorizada a Recibir <span class="text-danger">*</span>';
            }
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="bi bi-box-arrow-in-down me-1"></i> Confirmar Ingreso de Mercancía';
            }
            if (motivoInput) {
                motivoInput.placeholder = 'Ej: Recepción por traspaso de tienda';
            }
            if (bannerTexto) {
                bannerTexto.innerHTML = '<strong>Modo Ingreso:</strong> Seleccione el envío <strong>autorizado por el gerente</strong> de la sucursal de origen. Solo aparecen envíos que ya fueron aprobados y están listos para recibir.';
            }
        } else {
            seccionEntrada.style.setProperty('display', 'none', 'important');
            seccionEntrada.classList.add('d-none');
            seccionEntrada.setAttribute('hidden', '');
            
            seccionSalida.style.setProperty('display', 'block', 'important');
            seccionSalida.classList.remove('d-none');
            seccionSalida.removeAttribute('hidden');
            
            if (movimientoSelect) {
                movimientoSelect.removeAttribute('required');
                movimientoSelect.disabled = true;
                movimientoSelect.value = '';
            }
            if (equipoSelect) {
                equipoSelect.setAttribute('required', 'required');
                equipoSelect.disabled = false;
            }
            if (sucursalSelect) {
                sucursalSelect.setAttribute('required', 'required');
                sucursalSelect.disabled = false;
            }
            if (cantidadInput) {
                cantidadInput.removeAttribute('readonly');
                cantidadInput.value = 1;
                cantidadInput.classList.remove('is-invalid', 'is-valid');
                var errorAnterior = document.querySelector('#cantidad-error');
                if (errorAnterior) errorAnterior.remove();
            }
            if (labelCantidad) {
                labelCantidad.innerHTML = 'Cantidad a Enviar <span class="text-danger">*</span>';
            }
            if (btnSubmit) {
                btnSubmit.innerHTML = '<i class="bi bi-send me-1"></i> Solicitar Envío / Transferencia';
            }
            if (motivoInput) {
                motivoInput.placeholder = 'Ej: Reabastecimiento de tienda, Traslado por pedido';
            }
            if (bannerTexto) {
                bannerTexto.innerHTML = '<strong>Modo Envío:</strong> Selecciona el producto, la cantidad y la <strong>Sucursal Destino</strong>. La solicitud quedará <strong>pendiente</strong> hasta que el gerente de tu sucursal la autorice. El stock se descontará al momento de la autorización.';
            }
            
            actualizarStock();
        }
    }
    
    function consultarStock(equipoId, callback) {
        if (!equipoId) {
            callback(null);
            return;
        }
        
        var url = movimientosData.stockRoute + '?equipo_id=' + equipoId + '&sucursal_id=' + movimientosData.sucursalActivaId;
        
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    callback(data);
                } else {
                    callback(null);
                }
            })
            .catch(function() {
                callback(null);
            });
    }
    
    function actualizarStock() {
        var tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
        var stockDiv = document.getElementById('stock_origen');
        var stockCantidad = document.getElementById('stock_origen_cantidad');
        var equipoSelect = document.getElementById('equipo_id');
        var cantidadInput = document.getElementById('cantidad');
        
        if (!tipoSeleccionado || tipoSeleccionado.value !== 'salida') {
            if (stockDiv) stockDiv.style.display = 'none';
            return;
        }
        
        if (!equipoSelect || !equipoSelect.value) {
            if (stockDiv) stockDiv.style.display = 'none';
            if (cantidadInput) {
                cantidadInput.classList.remove('is-invalid', 'is-valid');
                var errorAnterior = document.querySelector('#cantidad-error');
                if (errorAnterior) errorAnterior.remove();
            }
            return;
        }
        
        consultarStock(equipoSelect.value, function(data) {
            if (data !== null && stockCantidad && stockDiv) {
                var stockDisponible = parseInt(data.stock) || 0;
                stockCantidad.textContent = stockDisponible;
                stockDiv.style.display = 'block';
                
                var cantidadSolicitada = parseInt(cantidadInput.value) || 0;
                
                cantidadInput.classList.remove('is-invalid', 'is-valid');
                var errorAnterior = document.querySelector('#cantidad-error');
                if (errorAnterior) errorAnterior.remove();
                
                if (cantidadSolicitada > 0) {
                    if (cantidadSolicitada > stockDisponible) {
                        cantidadInput.classList.add('is-invalid');
                        var error = document.createElement('div');
                        error.id = 'cantidad-error';
                        error.className = 'invalid-feedback d-block';
                        error.textContent = '⚠️ No hay suficiente stock disponible. Disponible: ' + stockDisponible + ' unidades';
                        cantidadInput.parentNode.after(error);
                    } else {
                        cantidadInput.classList.add('is-valid');
                    }
                }
            } else {
                if (stockDiv) stockDiv.style.display = 'none';
            }
        });
    }
    
    function initForm() {
        var radioEntrada = document.getElementById('tipo_entrada');
        var radioSalida = document.getElementById('tipo_salida');
        var movimientoSelect = document.getElementById('movimiento_autorizado_id');
        var equipoSelect = document.getElementById('equipo_id');
        var cantidadInput = document.getElementById('cantidad');
        var motivoInput = document.getElementById('motivo');
        var formMovimiento = document.getElementById('formMovimiento');
        var stockCantidad = document.getElementById('stock_origen_cantidad');
        var sucursalSelect = document.getElementById('sucursal_destino_id');
        
        if (!radioEntrada || !radioSalida) return;
        
        radioEntrada.onclick = function() { mostrarSeccion('entrada'); };
        radioSalida.onclick = function() { mostrarSeccion('salida'); };
        
        if (movimientoSelect) {
            movimientoSelect.onchange = function() {
                var selectedOption = this.options[this.selectedIndex];
                if (selectedOption && selectedOption.value) {
                    var cantidad = selectedOption.getAttribute('data-cantidad');
                    var motivo = selectedOption.getAttribute('data-motivo');
                    if (cantidadInput) cantidadInput.value = cantidad || 1;
                    if (motivoInput) motivoInput.value = 'Recepción: ' + (motivo || 'Reabastecimiento de sucursal');
                } else {
                    if (cantidadInput) cantidadInput.value = 1;
                    if (motivoInput) motivoInput.value = '';
                }
            };
        }
        
        if (equipoSelect) {
            equipoSelect.onchange = function() {
                if (cantidadInput) {
                    cantidadInput.value = 1;
                    cantidadInput.classList.remove('is-invalid', 'is-valid');
                    var errorAnterior = document.querySelector('#cantidad-error');
                    if (errorAnterior) errorAnterior.remove();
                }
                actualizarStock();
            };
        }
        
        if (cantidadInput) {
            cantidadInput.oninput = function() {
                actualizarStock();
            };
        }
        
        if (formMovimiento) {
            formMovimiento.onsubmit = function(e) {
                var tipoSeleccionado = document.querySelector('input[name="tipo"]:checked');
                
                if (!tipoSeleccionado) {
                    e.preventDefault();
                    alert('⚠️ Seleccione un tipo de operación.');
                    return false;
                }
                
                var tipo = tipoSeleccionado.value;
                
                if (tipo === 'entrada') {
                    if (!movimientoSelect || !movimientoSelect.value) {
                        e.preventDefault();
                        alert('⚠️ Debe seleccionar un envío autorizado para confirmar el ingreso.');
                        return false;
                    }
                }
                
                if (tipo === 'salida') {
                    if (!equipoSelect || !equipoSelect.value) {
                        e.preventDefault();
                        alert('⚠️ Debe seleccionar un producto para el envío.');
                        return false;
                    }
                    
                    if (!sucursalSelect || !sucursalSelect.value) {
                        e.preventDefault();
                        alert('⚠️ Debe seleccionar una sucursal destino.');
                        return false;
                    }
                    
                    if (stockCantidad && stockCantidad.textContent) {
                        var cantidad = parseInt(cantidadInput.value) || 0;
                        var stock = parseInt(stockCantidad.textContent) || 0;
                        
                        if (cantidad <= 0) {
                            e.preventDefault();
                            alert('⚠️ La cantidad debe ser mayor a 0.');
                            return false;
                        }
                        
                        if (cantidad > stock) {
                            e.preventDefault();
                            alert('⚠️ La cantidad a enviar (' + cantidad + ') excede el stock disponible (' + stock + ') en tu sucursal.');
                            return false;
                        }
                    }
                }
            };
        }
        
        if (radioEntrada.checked) {
            mostrarSeccion('entrada');
        } else if (radioSalida.checked) {
            mostrarSeccion('salida');
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initForm);
    } else {
        initForm();
    }
})();
</script>
@endsection