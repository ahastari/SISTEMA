@extends('layouts.admin')

@section('content')
<style>
    .equipo-item {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    .remove-equipo {
        cursor: pointer;
        color: #dc3545;
    }
    .remove-equipo:hover {
        color: #bb2d3b;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-plus-circle me-2"></i>Nueva Renta
    </h2>
    <a href="{{ route('rentas.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Cancelar
    </a>
</div>

@if(session('error'))
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
    </div>
@endif

<form action="{{ route('rentas.store') }}" method="POST" id="formRenta">
    @csrf
    
    <div class="row">
        <!-- Datos principales -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Datos de la Renta</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Folio</label>
                        <input type="text" class="form-control" value="{{ $folio }}" disabled>
                        <small class="text-muted">Se generará automáticamente</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Cliente *</label>
                        <select name="cliente_id" class="form-select @error('cliente_id') is-invalid @enderror" required>
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
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Fecha Inicio *</label>
                            <input type="date" name="fecha_inicio" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   value="{{ old('fecha_inicio', date('Y-m-d')) }}" required id="fecha_inicio">
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label>Fecha Fin *</label>
                            <input type="date" name="fecha_fin" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                   value="{{ old('fecha_fin', date('Y-m-d', strtotime('+1 day'))) }}" required id="fecha_fin">
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label>Días Totales</label>
                        <input type="text" class="form-control" id="dias_totales" readonly>
                        <small class="text-muted">Se cobra día de salida y día de entrada</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Depósito (opcional)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" name="deposito" class="form-control" step="0.01" value="{{ old('deposito', 0) }}" id="deposito">
                        </div>
                        <small class="text-muted">Monto que el cliente deja en garantía</small>
                    </div>
                    
                    <div class="mb-3">
                        <label>Observaciones</label>
                        <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Equipos -->
        <div class="col-md-6">
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-box-seam"></i> Equipos a Rentar</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Agregar equipo</label>
                        <div class="input-group">
                            <select class="form-select" id="selectEquipo">
                                <option value="">Seleccionar equipo...</option>
                                @foreach($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" data-precio="{{ $equipo->precio_dia }}" data-nombre="{{ $equipo->nombre }}" data-stock="{{ $equipo->stock }}">
                                        {{ $equipo->codigo }} - {{ $equipo->nombre }} (${{ number_format($equipo->precio_dia, 2) }}/día) - Stock: {{ $equipo->stock }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" id="cantidadEquipo" class="form-control" placeholder="Cantidad" style="width: 100px;" min="1">
                            <button type="button" class="btn btn-primary" onclick="agregarEquipo()">
                                <i class="bi bi-plus"></i> Agregar
                            </button>
                        </div>
                    </div>
                    
                    <div id="equiposLista">
                        <div class="alert alert-info" id="listaVacia">
                            <i class="bi bi-info-circle"></i> No hay equipos agregados
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen -->
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-calculator"></i> Resumen</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tr>
                            <th>Subtotal:</th>
                            <td class="text-end"><strong id="res_subtotal">$0.00</strong></td>
                        </tr>
                        <tr>
                            <th>IVA (16%):</th>
                            <td class="text-end"><strong id="res_iva">$0.00</strong></td>
                        </tr>
                        <tr>
                            <th>Total:</th>
                            <td class="text-end"><strong id="res_total" class="text-success fs-4">$0.00</strong></td>
                        </tr>
                        <tr>
                            <th>Depósito:</th>
                            <td class="text-end"><strong id="res_deposito">$0.00</strong></td>
                        </tr>
                        <tr>
                            <th>Saldo a pagar:</th>
                            <td class="text-end"><strong id="res_saldo" class="text-primary fs-4">$0.00</strong></td>
                        </tr>
                    </table>
                    
                    <button type="submit" class="btn btn-success w-100" id="btnGuardar" disabled>
                        <i class="bi bi-save"></i> Guardar Renta
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
let equipos = [];

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

// Actualizar resumen
function actualizarResumen() {
    const dias = calcularDias();
    let subtotal = 0;
    
    equipos.forEach(eq => {
        subtotal += eq.precio * eq.cantidad * dias;
    });
    
    const iva = subtotal * 0.16;
    const total = subtotal + iva;
    const deposito = parseFloat(document.getElementById('deposito').value) || 0;
    const saldo = total - deposito;
    
    document.getElementById('res_subtotal').innerHTML = '$' + subtotal.toFixed(2);
    document.getElementById('res_iva').innerHTML = '$' + iva.toFixed(2);
    document.getElementById('res_total').innerHTML = '$' + total.toFixed(2);
    document.getElementById('res_deposito').innerHTML = '$' + deposito.toFixed(2);
    document.getElementById('res_saldo').innerHTML = '$' + saldo.toFixed(2);
    
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
    
    // Verificar si ya existe
    const existe = equipos.find(e => e.id == equipoId);
    if (existe) {
        alert('Este equipo ya fue agregado. Elimina el actual y vuelve a agregar si necesitas más cantidad');
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

// Eliminar equipo
function eliminarEquipo(index) {
    equipos.splice(index, 1);
    renderizarEquipos();
    actualizarResumen();
}

// Renderizar lista de equipos
function renderizarEquipos() {
    const container = document.getElementById('equiposLista');
    const listaVacia = document.getElementById('listaVacia');
    
    if (equipos.length === 0) {
        container.innerHTML = '<div class="alert alert-info" id="listaVacia"><i class="bi bi-info-circle"></i> No hay equipos agregados</div>';
        return;
    }
    
    let html = '';
    equipos.forEach((eq, index) => {
        html += `
            <div class="equipo-item">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${eq.nombre}</strong><br>
                        <small>Cantidad: ${eq.cantidad} | Precio: $${eq.precio}/día</small>
                    </div>
                    <div>
                        <span class="remove-equipo" onclick="eliminarEquipo(${index})">
                            <i class="bi bi-trash"></i>
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

// Event listeners
document.getElementById('fecha_inicio').addEventListener('change', actualizarResumen);
document.getElementById('fecha_fin').addEventListener('change', actualizarResumen);
document.getElementById('deposito').addEventListener('input', actualizarResumen);
</script>
@endsection
