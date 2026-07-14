@extends('layouts.admin')

@section('content')
<style>
    /* Soporte para modo oscuro/claro y respuesta fluida */
    .equipo-item {
        background: var(--bs-tertiary-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 8px;
    }
    .remove-equipo {
        cursor: pointer;
        color: #dc3545;
        transition: color 0.15s ease-in-out;
    }
    .remove-equipo:hover {
        color: #a71d2a;
    }
    .card-custom-header {
        border-bottom: 1px solid var(--bs-border-color);
    }
    .factura-option {
        border: 2px solid var(--bs-border-color);
        border-radius: 10px;
        padding: 12px 16px;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        height: 100%;
    }
    .factura-option:hover {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.03);
    }
    .factura-option.selected {
        border-color: #0d6efd;
        background: rgba(13, 110, 253, 0.08);
        box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    }
    .factura-option.selected .factura-icon {
        color: #0d6efd;
    }
    .factura-option.selected .factura-label {
        color: #0d6efd;
        font-weight: 700;
    }
    .factura-icon {
        font-size: 28px;
        margin-bottom: 8px;
        color: var(--bs-secondary-color);
        transition: color 0.2s;
    }
    .factura-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--bs-body-color);
        margin-bottom: 4px;
    }
    .factura-desc {
        font-size: 11px;
        color: var(--bs-secondary-color);
    }
</style>

<!-- Header Responsive -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-plus-circle me-2 text-primary"></i>Nueva Renta
        </h3>
        <p class="text-secondary small mb-0">Registrar un nuevo contrato de arrendamiento</p>
    </div>
    <a href="{{ route('rentas.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
        <i class="bi bi-arrow-left me-1"></i> Cancelar
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form action="{{ route('rentas.store') }}" method="POST" id="formRenta">
    @csrf
    
    <div class="row g-3">
        
        <!-- COLUMNA IZQUIERDA: Datos principales -->
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-primary text-white py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-info-circle me-1"></i> Datos de la Renta</h6>
                </div>
                <div class="card-body p-3">
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Folio</label>
                        <input type="text" class="form-control form-control-sm bg-body-tertiary" value="{{ $folio }}" readonly>
                        <small class="text-secondary" style="font-size: 11px;">Identificador autogenerado</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Cliente <span class="text-danger">*</span></label>
                        <select name="cliente_id" id="clienteSelect" class="form-select form-select-sm bg-body @error('cliente_id') is-invalid @enderror" required>
                            <option value="">Seleccionar cliente...</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre_completo }} - {{ $cliente->telefono }}
                                </option>
                            @endforeach
                        </select>
                        @error('cliente_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Obra / Proyecto</label>
                        <select name="obra_id" class="form-select form-select-sm bg-body" id="obraSelect">
                            <option value="">Seleccionar obra (opcional)...</option>
                        </select>
                        <small class="text-secondary" style="font-size: 11px;">¿No existe la obra? <a href="{{ route('obras.create') }}" target="_blank" class="text-primary">Regístrala aquí</a></small>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-body">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-sm bg-body @error('fecha_inicio') is-invalid @enderror" 
                                   value="{{ old('fecha_inicio', date('Y-m-d')) }}" required id="fecha_inicio">
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-semibold text-body">Fecha Fin <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" class="form-control form-control-sm bg-body @error('fecha_fin') is-invalid @enderror" 
                                   value="{{ old('fecha_fin', date('Y-m-d', strtotime('+1 day'))) }}" required id="fecha_fin">
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Días Totales</label>
                        <input type="text" class="form-control form-control-sm bg-body-tertiary fw-bold text-primary" id="dias_totales" readonly>
                        <small class="text-secondary" style="font-size: 11px;">Se calcula día de salida y día de entrega</small>
                    </div>
                    
                    <!-- 🔥 NUEVO: Selector de Facturación -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">¿Requiere Factura? <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-6">
                                <div class="factura-option selected" id="conFactura" onclick="seleccionarFacturacion(true)">
                                    <div class="factura-icon">
                                        <i class="bi bi-receipt"></i>
                                    </div>
                                    <div class="factura-label">Con Factura</div>
                                    <div class="factura-desc">Se aplica IVA (16%)</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="factura-option" id="sinFactura" onclick="seleccionarFacturacion(false)">
                                    <div class="factura-icon">
                                        <i class="bi bi-cash-stack"></i>
                                    </div>
                                    <div class="factura-label">Sin Factura</div>
                                    <div class="factura-desc">Sin IVA (Público general)</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="requiere_factura" id="requiere_factura" value="1">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Depósito en Garantía</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body text-secondary">$</span>
                            <input type="number" name="deposito" class="form-control bg-body" step="0.01" value="{{ old('deposito', 0) }}" id="deposito">
                        </div>
                        <small class="text-secondary" style="font-size: 11px;">Monto dejado en garantía (reembolsable o acreditable)</small>
                    </div>
                    
                    <div>
                        <label class="form-label small fw-semibold text-body">Observaciones</label>
                        <textarea name="observaciones" class="form-control form-control-sm bg-body" rows="2" placeholder="Detalles o condiciones del contrato...">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- COLUMNA DERECHA: Equipos y Resumen -->
        <div class="col-12 col-lg-6">
            
            <!-- Selección de Equipos -->
            <div class="card border-0 shadow-sm rounded-3 mb-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-success text-white py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-box-seam me-1"></i> Productos a Rentar</h6>
                </div>
                <div class="card-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Agregar producto</label>
                        <div class="row g-2">
                            <div class="col-12 col-sm-7">
                                <select class="form-select form-select-sm bg-body" id="selectEquipo">
                                    <option value="">Seleccionar producto...</option>
                                    @foreach($equipos as $equipo)
                                        <option value="{{ $equipo->id }}" data-precio="{{ $equipo->precio_dia }}" data-nombre="{{ $equipo->nombre }}" data-stock="{{ $equipo->stock }}">
                                            {{ $equipo->codigo }} - {{ $equipo->nombre }} (${{ number_format($equipo->precio_dia, 2) }}/día) - Stock: {{ $equipo->stock }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-7 col-sm-3">
                                <input type="number" id="cantidadEquipo" class="form-control form-control-sm bg-body" placeholder="Cant." min="1">
                            </div>
                            <div class="col-5 col-sm-2">
                                <button type="button" class="btn btn-primary btn-sm w-100" onclick="agregarEquipo()">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div id="equiposLista" class="mt-2">
                        <div class="alert alert-info py-2 px-3 mb-0 small" id="listaVacia">
                            <i class="bi bi-info-circle me-1"></i> No hay productos agregados al contrato
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen Financiero -->
            <div class="card border-0 shadow-sm rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
                <div class="card-header bg-info text-white py-2 px-3 rounded-top-3">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-calculator me-1"></i> Resumen de Pago</h6>
                </div>
                <div class="card-body p-3">
                    <table class="table table-borderless align-middle mb-2" style="font-size: 13px;">
                        <tbody>
                            <tr>
                                <th class="p-1 text-secondary">Subtotal:</th>
                                <td class="p-1 text-end text-body"><strong id="res_subtotal">$0.00</strong></td>
                            </tr>
                            <tr id="fila_iva">
                                <th class="p-1 text-secondary">IVA (16%):</th>
                                <td class="p-1 text-end text-body"><strong id="res_iva">$0.00</strong></td>
                            </tr>
                            <tr class="border-top">
                                <th class="p-1 text-body">Total:</th>
                                <td class="p-1 text-end"><strong id="res_total" class="text-success fs-5">$0.00</strong></td>
                            </tr>
                            <tr>
                                <th class="p-1 text-secondary">Depósito:</th>
                                <td class="p-1 text-end text-body"><strong id="res_deposito">$0.00</strong></td>
                            </tr>
                            <tr class="border-top">
                                <th class="p-1 text-body">Saldo Final:</th>
                                <td class="p-1 text-end"><strong id="res_saldo" class="text-primary fs-5">$0.00</strong></td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 mt-2" id="btnGuardar" disabled>
                        <i class="bi bi-save me-1"></i> Guardar Renta
                    </button>
                </div>
            </div>

        </div>
    </div>
</form>

<script>
let equipos = [];
let requiereFactura = true; // 🔥 Por defecto: Con Factura

// 🔥 NUEVO: Función para seleccionar tipo de facturación
function seleccionarFacturacion(conFactura) {
    requiereFactura = conFactura;
    
    // Actualizar UI
    document.getElementById('conFactura').classList.toggle('selected', conFactura);
    document.getElementById('sinFactura').classList.toggle('selected', !conFactura);
    
    // Actualizar campo hidden
    document.getElementById('requiere_factura').value = conFactura ? '1' : '0';
    
    // Mostrar/ocultar fila de IVA
    document.getElementById('fila_iva').style.display = conFactura ? '' : 'none';
    
    // Recalcular totales
    actualizarResumen();
}

// Calcular días
function calcularDias() {
    const inicio = document.getElementById('fecha_inicio').value;
    const fin = document.getElementById('fecha_fin').value;
    
    if (inicio && fin) {
        const fechaInicio = new Date(inicio);
        const fechaFin = new Date(fin);
        const diffTime = Math.abs(fechaFin - fechaInicio);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('dias_totales').value = diffDays;
        return diffDays;
    }
    return 0;
}

// 🔥 MODIFICADO: Actualizar resumen con soporte de facturación
function actualizarResumen() {
    const dias = calcularDias();
    let subtotal = 0;
    
    equipos.forEach(eq => {
        subtotal += eq.precio * eq.cantidad * dias;
    });
    
    // Calcular IVA según si requiere factura
    const iva = requiereFactura ? (subtotal * 0.16) : 0;
    const total = subtotal + iva;
    const deposito = parseFloat(document.getElementById('deposito').value) || 0;
    const saldo = total - deposito;
    
    document.getElementById('res_subtotal').innerHTML = '$' + subtotal.toFixed(2);
    document.getElementById('res_iva').innerHTML = '$' + iva.toFixed(2);
    document.getElementById('res_total').innerHTML = '$' + total.toFixed(2);
    document.getElementById('res_deposito').innerHTML = '$' + deposito.toFixed(2);
    document.getElementById('res_saldo').innerHTML = '$' + saldo.toFixed(2);
    
    // Mostrar/ocultar fila de IVA
    document.getElementById('fila_iva').style.display = requiereFactura ? '' : 'none';
    
    document.getElementById('btnGuardar').disabled = equipos.length === 0;
}

// Agregar equipo
function agregarEquipo() {
    const select = document.getElementById('selectEquipo');
    const cantidad = parseInt(document.getElementById('cantidadEquipo').value);
    const equipoId = select.value;
    
    if (!equipoId || !cantidad || cantidad < 1) {
        alert('Selecciona un equipo y una cantidad válida');
        return;
    }
    
    const option = select.options[select.selectedIndex];
    const nombre = option.dataset.nombre;
    const precio = parseFloat(option.dataset.precio);
    const stock = parseInt(option.dataset.stock);
    
    if (cantidad > stock) {
        alert(`Stock insuficiente. Solo hay ${stock} unidades disponibles`);
        return;
    }
    
    const existe = equipos.find(e => e.id == equipoId);
    if (existe) {
        alert('Este equipo ya fue agregado');
        return;
    }
    
    equipos.push({
        id: equipoId,
        nombre: nombre,
        cantidad: cantidad,
        precio: precio
    });
    
    renderizarEquipos();
    actualizarResumen();
    
    select.value = '';
    document.getElementById('cantidadEquipo').value = '';
}

function eliminarEquipo(index) {
    equipos.splice(index, 1);
    renderizarEquipos();
    actualizarResumen();
}

function renderizarEquipos() {
    const container = document.getElementById('equiposLista');
    
    if (equipos.length === 0) {
        container.innerHTML = '<div class="alert alert-info py-2 px-3 mb-0 small"><i class="bi bi-info-circle me-1"></i> No hay equipos agregados al contrato</div>';
        return;
    }
    
    let html = '';
    equipos.forEach((eq, index) => {
        html += `
            <div class="equipo-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-body d-block small">${eq.nombre}</strong>
                        <small class="text-secondary" style="font-size: 11px;">Cant: ${eq.cantidad} | Precio: $${eq.precio}/día</small>
                    </div>
                    <div>
                        <span class="remove-equipo" onclick="eliminarEquipo(${index})" title="Eliminar">
                            <i class="bi bi-trash fs-6"></i>
                        </span>
                    </div>
                </div>
            </div>
            <input type="hidden" name="equipos[${index}][id]" value="${eq.id}">
            <input type="hidden" name="equipos[${index}][cantidad]" value="${eq.cantidad}">
        `;
    });
    
    container.innerHTML = html;
}

// Cargar obras AJAX
document.getElementById('clienteSelect').addEventListener('change', function() {
    const clienteId = this.value;
    const obraSelect = document.getElementById('obraSelect');
    
    if (clienteId) {
        obraSelect.innerHTML = '<option value="">Cargando obras...</option>';
        
        fetch(`/get-obras/${clienteId}`)
            .then(response => response.json())
            .then(data => {
                obraSelect.innerHTML = '<option value="">Seleccionar obra (opcional)...</option>';
                if (data.length === 0) {
                    obraSelect.innerHTML += '<option value="" disabled>No hay obras registradas para este cliente</option>';
                } else {
                    data.forEach(obra => {
                        obraSelect.innerHTML += `<option value="${obra.id}">${obra.nombre} - ${obra.direccion}</option>`;
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                obraSelect.innerHTML = '<option value="">Error al cargar obras</option>';
            });
    } else {
        obraSelect.innerHTML = '<option value="">Seleccionar obra (opcional)...</option>';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const clienteSelect = document.getElementById('clienteSelect');
    if (clienteSelect && clienteSelect.value) {
        clienteSelect.dispatchEvent(new Event('change'));
    }
    actualizarResumen();
});

document.getElementById('fecha_inicio').addEventListener('change', actualizarResumen);
document.getElementById('fecha_fin').addEventListener('change', actualizarResumen);
document.getElementById('deposito').addEventListener('input', actualizarResumen);
</script>
@endsection