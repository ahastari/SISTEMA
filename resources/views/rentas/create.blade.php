@extends('layouts.admin')

@section('content')
<style>
    /* ===== Estructura por pasos ===== */
    .card-paso { border: 1px solid var(--bs-border-color) !important; background: var(--bs-body-bg); }
    .card-paso > .card-header { background: var(--bs-tertiary-bg); border-bottom: 1px solid var(--bs-border-color); }
    .paso-num {
        width: 26px; height: 26px; border-radius: 50%; flex: 0 0 auto;
        background: var(--bs-primary); color: #fff; font-weight: 700; font-size: 13px;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .form-label-sm { font-size: 12px; font-weight: 600; color: var(--bs-body-color); margin-bottom: 4px; }
    .remove-equipo { cursor: pointer; color: #dc3545; transition: color .15s; }
    .remove-equipo:hover { color: #a71d2a; }
    .tabla-equipos th { font-size: 11px; text-transform: uppercase; letter-spacing: .03em; color: var(--bs-secondary-color); font-weight: 600; }

    /* Facturación: dos opciones lado a lado */
    .factura-option {
        display: flex; align-items: center; gap: 12px; height: 100%;
        border: 2px solid var(--bs-border-color); border-radius: 10px;
        padding: 10px 14px; cursor: pointer; transition: all .2s ease;
    }
    .factura-option:hover { border-color: #0d6efd; background: rgba(13,110,253,.03); }
    .factura-option.selected { border-color: #0d6efd; background: rgba(13,110,253,.08); box-shadow: 0 0 0 3px rgba(13,110,253,.15); }
    .factura-icon { font-size: 24px; color: var(--bs-secondary-color); transition: color .2s; }
    .factura-option.selected .factura-icon, .factura-option.selected .factura-label { color: #0d6efd; }
    .factura-label { font-size: 13px; font-weight: 700; color: var(--bs-body-color); line-height: 1.2; }
    .factura-desc { font-size: 11px; color: var(--bs-secondary-color); }

    /* Resumen fijo al desplazar (solo pantallas grandes) */
    @media (min-width: 1200px) { .resumen-sticky { position: sticky; top: 1rem; } }
    .total-box { background: var(--bs-success-bg-subtle); border: 1px solid var(--bs-success-border-subtle); border-radius: 10px; }

    /* Rentas en espera */
    .espera-card { border: 1px solid var(--bs-border-color); border-left: 4px solid var(--bs-warning); border-radius: 10px; background: var(--bs-body-bg); padding: 10px 12px; height: 100%; }
    .espera-card.lista { border-left-color: var(--bs-success); border-color: var(--bs-success); animation: pulsoEspera 1.6s ease-in-out 3; }
    .espera-card.rechazada { border-left-color: var(--bs-danger); }
    @keyframes pulsoEspera { 0%,100% { box-shadow: 0 0 0 0 rgba(25,135,84,.45); } 50% { box-shadow: 0 0 0 7px rgba(25,135,84,0); } }
</style>

<!-- Encabezado -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0 fw-bold text-body"><i class="bi bi-plus-circle me-2 text-primary"></i>Nueva Renta</h3>
        <p class="text-secondary small mb-0">Folio sugerido <strong class="text-body">{{ $folio }}</strong> · Registrar un nuevo contrato de arrendamiento</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('rentas.index') }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 fw-semibold">
            <i class="bi bi-journal-bookmark-fill me-1"></i> Historial de Rentas
        </a>
        <a href="{{ route('rentas.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 px-3">
            <i class="bi bi-arrow-left me-1"></i> Cancelar
        </a>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- ===== RENTAS EN ESPERA (solo aparece si hay alguna) ===== -->
<div id="barraEspera" class="card card-paso border-0 shadow-sm rounded-3 mb-3 d-none">
    <div class="card-header py-2 px-3 d-flex align-items-center gap-2">
        <i class="bi bi-pause-circle-fill text-warning fs-5"></i>
        <h6 class="mb-0 fw-bold">Rentas en espera</h6>
        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle" id="contEspera">0</span>
        <small class="text-secondary ms-auto d-none d-md-inline" style="font-size: 11px;">El inventario no se reserva mientras esperan; se valida al guardar.</small>
    </div>
    <div class="card-body p-3">
        <div class="row g-2" id="listaEspera"></div>
    </div>
</div>

<form action="{{ route('rentas.store') }}" method="POST" id="formRenta">
    @csrf

    <div class="row g-3">

        <!-- ================= FLUJO PRINCIPAL ================= -->
        <div class="col-12 col-xl-8">

            <!-- PASO 1: Cliente, obra y fechas -->
            <div class="card card-paso border-0 shadow-sm rounded-3 mb-3">
                <div class="card-header py-2 px-3 d-flex align-items-center gap-2">
                    <span class="paso-num">1</span>
                    <h6 class="mb-0 fw-bold">Cliente y fechas</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label-sm">Cliente <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="clienteSelect" class="form-select form-select-sm bg-body @error('cliente_id') is-invalid @enderror" required>
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                        {{ $cliente->nombre_completo }} - {{ $cliente->telefono }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cliente_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label-sm">Obra / Proyecto</label>
                            <select name="obra_id" class="form-select form-select-sm bg-body" id="obraSelect">
                                <option value="">Seleccionar obra (opcional)...</option>
                            </select>
                            <small class="text-secondary" style="font-size: 11px;">¿No existe? <a href="#" data-bs-toggle="modal" data-bs-target="#modalNuevaObra" class="text-primary">Regístrala aquí</a></small>
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label-sm">Fecha inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm bg-body @error('fecha_inicio') is-invalid @enderror"
                                   value="{{ old('fecha_inicio', date('Y-m-d')) }}" required>
                            @error('fecha_inicio')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-6 col-md-4">
                            <label class="form-label-sm">Fecha fin <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm bg-body @error('fecha_fin') is-invalid @enderror"
                                   value="{{ old('fecha_fin', date('Y-m-d', strtotime('+1 day'))) }}" required>
                            @error('fecha_fin')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label-sm">Duración</label>
                            <div class="form-control form-control-sm bg-body-tertiary fw-bold text-primary" id="dias_totales">—</div>
                            <small class="text-secondary" style="font-size: 11px;">Cuenta día de salida y de entrega</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASO 2: Productos -->
            <div class="card card-paso border-0 shadow-sm rounded-3 mb-3">
                <div class="card-header py-2 px-3 d-flex align-items-center gap-2">
                    <span class="paso-num">2</span>
                    <h6 class="mb-0 fw-bold">Productos a rentar</h6>
                    <span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle ms-auto" id="contProductos">0</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-2 align-items-end mb-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label-sm">Producto</label>
                            <select class="form-select form-select-sm bg-body" id="selectEquipo">
                                <option value="">Seleccionar producto...</option>
                                @foreach($equipos as $equipo)
                                    <option value="{{ $equipo->id }}" data-precio="{{ $equipo->precio_dia }}" data-tarifa="{{ $equipo->tipo_tarifa ?? 'dia' }}" data-nombre="{{ $equipo->nombre }}" data-stock="{{ $equipo->stock }}">
                                        {{ $equipo->codigo }} - {{ $equipo->nombre }} (${{ number_format($equipo->precio_dia, 2) }}/{{ ($equipo->tipo_tarifa ?? 'dia') === 'm2' ? 'm²' : 'día' }}) - Stock: {{ $equipo->stock }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-3">
                            <label class="form-label-sm">Cantidad</label>
                            <input type="number" id="cantidadEquipo" class="form-control form-control-sm bg-body" placeholder="Cant." min="1">
                        </div>
                        <div class="col-6 col-md-2">
                            <button type="button" class="btn btn-primary btn-sm w-100 fw-semibold" onclick="agregarEquipo()">
                                <i class="bi bi-plus-lg me-1"></i>Agregar
                            </button>
                        </div>
                    </div>
                    <div id="equiposLista"></div>
                </div>
            </div>

            <!-- PASO 3: Condiciones (aparece al elegir productos) -->
            <div class="card card-paso border-0 shadow-sm rounded-3 mb-3 d-none" id="bloqueDetalles">
                <div class="card-header py-2 px-3 d-flex align-items-center gap-2">
                    <span class="paso-num">3</span>
                    <h6 class="mb-0 fw-bold">Condiciones del contrato</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label-sm">¿Requiere factura? <span class="text-danger">*</span></label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <div class="factura-option selected" id="conFactura" onclick="seleccionarFacturacion(true)">
                                        <i class="bi bi-receipt factura-icon"></i>
                                        <div><div class="factura-label">Con factura</div><div class="factura-desc">Se aplica IVA (16%)</div></div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="factura-option" id="sinFactura" onclick="seleccionarFacturacion(false)">
                                        <i class="bi bi-cash-stack factura-icon"></i>
                                        <div><div class="factura-label">Sin factura</div><div class="factura-desc">Sin IVA (Público general)</div></div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="requiere_factura" id="requiere_factura" value="1">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label-sm">Flete</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body text-secondary">$</span>
                                <input type="number" name="flete" id="flete" class="form-control bg-body" step="0.01" min="0" value="{{ old('flete', 0) }}" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label-sm">Mano de obra</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body text-secondary">$</span>
                                <input type="number" name="mano_obra" id="mano_obra" class="form-control bg-body" step="0.01" min="0" value="{{ old('mano_obra', 0) }}" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label-sm">Depósito inicial</label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body text-secondary">$</span>
                                <input type="number" name="deposito" id="deposito" class="form-control bg-body" step="0.01" min="0" value="{{ old('deposito', 0) }}" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                            </div>
                            <small class="text-secondary" style="font-size: 11px;">Garantía reembolsable o acreditable</small>
                        </div>

                        <!-- Descuento (el cajero debe pedir autorización al gerente) -->
                        <div class="col-12">
                            <div class="p-2 p-md-3 border rounded-3 bg-body-tertiary" id="bloqueDescuento">
                                <label class="form-label-sm"><i class="bi bi-tag-fill text-success me-1"></i>Descuento</label>
                                <div class="row g-2 align-items-start">
                                    <div class="col-12 col-md-4">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-body text-secondary">$</span>
                                            <input type="number" name="descuento" id="descuento" class="form-control bg-body" step="0.01" min="0" value="{{ old('descuento', 0) }}" onfocus="if(this.value == 0) this.value = '';" onblur="if(this.value == '') this.value = 0;">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8">
                                        <small class="text-secondary d-block" style="font-size: 11px; line-height: 1.3;">
                                            @if(!$puedeAutorizarDescuento)
                                                Todo descuento debe ser autorizado por un gerente o administrador.
                                            @else
                                                Como gerente/administrador el descuento se aplica directamente.
                                            @endif
                                        </small>
                                    </div>
                                </div>
                                <input type="hidden" name="solicitud_descuento_id" id="solicitud_descuento_id" value="{{ old('solicitud_descuento_id') }}">

                                @if(!$puedeAutorizarDescuento)
                                <div id="zonaAutorizacion" style="display: none;" class="mt-2">
                                    <input type="text" id="descuentoMotivo" maxlength="255" class="form-control form-control-sm bg-body mb-2" placeholder="Motivo del descuento (obligatorio)">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btnSolicitarDescuento">
                                            <i class="bi bi-shield-check me-1"></i>Solicitar autorización
                                        </button>
                                        <span id="estadoDescuento" class="small"></span>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label-sm">Observaciones</label>
                            <textarea name="observaciones" id="observaciones" class="form-control form-control-sm bg-body" rows="2" placeholder="Detalles o condiciones del contrato...">{{ old('observaciones') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= RESUMEN (fijo) ================= -->
        <div class="col-12 col-xl-4">
            <div class="resumen-sticky">
                <div class="card card-paso border-0 shadow-sm rounded-3">
                    <div class="card-header py-2 px-3 d-flex align-items-center gap-2">
                        <i class="bi bi-calculator text-primary"></i>
                        <h6 class="mb-0 fw-bold">Resumen de pago</h6>
                    </div>
                    <div class="card-body p-3">
                        <table class="table table-borderless table-sm align-middle mb-2" style="font-size: 13px;">
                            <tbody>
                                <tr><th class="text-secondary fw-normal">Equipos</th><td class="text-end"><strong id="res_equipos">$0.00</strong></td></tr>
                                <tr id="fila_flete" style="display: none;"><th class="text-secondary fw-normal">Flete</th><td class="text-end"><strong id="res_flete">$0.00</strong></td></tr>
                                <tr id="fila_mano_obra" style="display: none;"><th class="text-secondary fw-normal">Mano de obra</th><td class="text-end"><strong id="res_mano_obra">$0.00</strong></td></tr>
                                <tr class="border-top"><th class="text-secondary fw-normal">Subtotal</th><td class="text-end"><strong id="res_subtotal">$0.00</strong></td></tr>
                                <tr id="fila_descuento" style="display: none;"><th class="text-success fw-normal">Descuento</th><td class="text-end text-success"><strong id="res_descuento">-$0.00</strong></td></tr>
                                <tr id="fila_iva"><th class="text-secondary fw-normal">IVA (16%)</th><td class="text-end"><strong id="res_iva">$0.00</strong></td></tr>
                            </tbody>
                        </table>

                        <div class="total-box d-flex justify-content-between align-items-center px-3 py-2 mb-2">
                            <span class="fw-bold text-body">Total</span>
                            <strong id="res_total" class="text-success fs-4">$0.00</strong>
                        </div>
                        <table class="table table-borderless table-sm align-middle mb-2" style="font-size: 13px;">
                            <tbody>
                                <tr><th class="text-secondary fw-normal">Depósito</th><td class="text-end"><strong id="res_deposito">$0.00</strong></td></tr>
                                <tr class="border-top"><th class="text-body">Saldo final</th><td class="text-end"><strong id="res_saldo" class="text-primary fs-6">$0.00</strong></td></tr>
                            </tbody>
                        </table>

                        <div id="faltante" class="text-secondary mb-2" style="font-size: 11px;"></div>

                        <button type="submit" class="btn btn-success w-100 fw-bold py-2" id="btnGuardar" disabled>
                            <i class="bi bi-save me-1"></i> Guardar renta
                        </button>
                        <button type="button" class="btn btn-outline-warning w-100 fw-semibold btn-sm mt-2" id="btnEspera" onclick="ponerEnEspera()" disabled>
                            <i class="bi bi-pause-circle me-1"></i> Poner en espera
                        </button>
                        <small class="text-secondary d-block mt-2" style="font-size: 11px; line-height: 1.3;">
                            Guarda este borrador para atender a otro cliente mientras autorizan el descuento; luego lo retomas tal cual.
                        </small>
                    </div>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- ======================================================= -->
<!-- MODAL PARA REGISTRAR NUEVA OBRA (AHORA SOLO HAY UNO) -->
<!-- ======================================================= -->
<div class="modal fade" id="modalNuevaObra" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-3 px-4 border-0">
                <h5 class="modal-title fw-bold mb-0"><i class="bi bi-building-add me-2"></i>Registrar Nueva Obra</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formNuevaObraAjax">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Nombre de la Obra / Proyecto <span class="text-danger">*</span></label>
                            <input type="text" name="nombre" class="form-control form-control-sm bg-body" placeholder="Ej: Residencial Los Arboles" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-semibold text-body">Cliente Asociado <span class="text-danger">*</span></label>
                            <select name="cliente_id" id="modal_cliente_id" class="form-select form-select-sm bg-body" required>
                                <option value="">Seleccionar cliente...</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body">Calle y Número <span class="text-danger">*</span></label>
                            <textarea name="direccion" class="form-control form-control-sm bg-body" rows="2" required></textarea>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-body">Colonia</label>
                            <input type="text" name="colonia" class="form-control form-control-sm bg-body">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-body">Ciudad / Municipio</label>
                            <input type="text" name="ciudad" class="form-control form-control-sm bg-body" value="Durango">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-body">Estado</label>
                            <input type="text" name="estado" class="form-control form-control-sm bg-body" value="Dgo.">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-semibold text-body">Código Postal</label>
                            <input type="text" name="codigo_postal" class="form-control form-control-sm bg-body">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-body">Teléfono de la Obra</label>
                            <input type="text" name="telefono_obra" class="form-control form-control-sm bg-body" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-semibold text-body">Contacto / Encargado</label>
                            <input type="text" name="contacto_obra" class="form-control form-control-sm bg-body">
                        </div>
                        <div class="col-12 col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" name="activa" id="modal_activa" value="1" checked>
                                <label class="form-check-label fw-semibold text-body small" for="modal_activa">Obra activa</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-body">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm bg-body" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-body-tertiary py-3 px-4 border-top-0">
                    <button type="button" class="btn btn-secondary btn-sm rounded-3 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btnGuardarObraAjax" class="btn btn-success btn-sm fw-bold rounded-3 px-4 shadow-sm">
                        <i class="bi bi-check-lg me-1"></i> Guardar Obra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


@php
    $equiposPrevios = collect(old('equipos', []))->map(function ($e) use ($equipos) {
        $eq = $equipos->firstWhere('id', $e['id'] ?? null);
        return $eq ? ['id' => (string) $eq->id, 'nombre' => $eq->nombre, 'cantidad' => (int) ($e['cantidad'] ?? 1), 'precio' => (float) $eq->precio_dia, 'tarifa' => $eq->tipo_tarifa ?? 'dia', 'stock' => (int) $eq->stock] : null;
    })->filter()->values();
@endphp
<script>
// ======================= DATOS INICIALES ======================= //
// Si el servidor regresó el formulario con un error, se recuperan productos, factura y obra
const EQUIPOS_PREVIOS = @json($equiposPrevios);
const FACTURA_PREVIA = @json(old('requiere_factura', '1')) === '1';
let OBRA_PREVIA = @json(old('obra_id'));

const PUEDE_AUTORIZAR_DESCUENTO = @json($puedeAutorizarDescuento);
const URL_SOLICITAR_DESCUENTO = '{{ route("puntoventa.descuento.solicitar") }}';
const URL_ESTADOS_DESCUENTO = '{{ route("puntoventa.descuento.estados") }}';
const CSRF = '{{ csrf_token() }}';
// Rentas en espera: se guardan en este navegador, separadas por usuario y sucursal
const KEY_ESPERA = 'rentas_espera_u{{ auth()->id() }}_s{{ session('activo_sucursal_id') }}';

let equipos = EQUIPOS_PREVIOS.slice();
let requiereFactura = true;
let solDescuento = null;      // { id, monto, cliente_id, cliente_nombre, estado, autorizador }
let subtotalVigente = 0;
let totalVigente = 0;
let enviando = false;
let timerVigilancia = null;

// ======================= UTILIDADES ======================= //
const $ = id => document.getElementById(id);
const money = n => '$' + (parseFloat(n) || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const esc = t => String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
const pad = n => String(n).padStart(2, '0');
const fechaLocal = d => d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
const nombreCliente = () => { const c = $('clienteSelect'); return c.value ? c.options[c.selectedIndex].text.split(' - ')[0] : ''; };
function hace(ts) {
    const m = Math.floor((Date.now() - ts) / 60000);
    if (m < 1) return 'hace un momento';
    if (m < 60) return 'hace ' + m + ' min';
    const h = Math.floor(m / 60);
    return 'hace ' + h + ' h';
}

// ======================= FACTURACIÓN Y FECHAS ======================= //
function seleccionarFacturacion(conFactura) {
    requiereFactura = conFactura;
    $('conFactura').classList.toggle('selected', conFactura);
    $('sinFactura').classList.toggle('selected', !conFactura);
    $('requiere_factura').value = conFactura ? '1' : '0';
    actualizarResumen();
}

function calcularDias() {
    const inicio = $('fecha_inicio').value, fin = $('fecha_fin').value;
    if (inicio && fin) {
        const dias = Math.ceil(Math.abs(new Date(fin) - new Date(inicio)) / 86400000) + 1;
        $('dias_totales').textContent = dias + (dias === 1 ? ' día' : ' días');
        return dias;
    }
    $('dias_totales').textContent = '—';
    return 0;
}

// ======================= RESUMEN ======================= //
function actualizarResumen() {
    const dias = calcularDias();
    let subtotalEquipos = 0;
    equipos.forEach((eq, i) => {
        // Por m²: precio × cantidad (sin días). Por día: precio × cantidad × días
        const imp = eq.tarifa === 'm2' ? eq.precio * eq.cantidad : eq.precio * eq.cantidad * dias;
        subtotalEquipos += imp;
        const el = $('imp_' + i); if (el) el.textContent = money(imp);
    });

    const flete = parseFloat($('flete').value) || 0;
    const manoObra = parseFloat($('mano_obra').value) || 0;
    const subtotal = subtotalEquipos + flete + manoObra;
    const descuento = descuentoActual(subtotal);
    const baseImponible = Math.max(0, subtotal - descuento);
    const iva = requiereFactura ? baseImponible * 0.16 : 0;
    const total = baseImponible + iva;
    const deposito = parseFloat($('deposito').value) || 0;
    totalVigente = total;

    $('res_equipos').textContent = money(subtotalEquipos);
    $('fila_flete').style.display = flete > 0 ? '' : 'none';
    $('res_flete').textContent = money(flete);
    $('fila_mano_obra').style.display = manoObra > 0 ? '' : 'none';
    $('res_mano_obra').textContent = money(manoObra);
    $('res_subtotal').textContent = money(subtotal);
    $('fila_descuento').style.display = descuento > 0 ? '' : 'none';
    $('res_descuento').textContent = '-' + money(descuento);
    $('fila_iva').style.display = requiereFactura ? '' : 'none';
    $('res_iva').textContent = money(iva);
    $('res_total').textContent = money(total);
    $('res_deposito').textContent = money(deposito);
    $('res_saldo').textContent = money(total - deposito);

    actualizarEstadoDescuento(subtotal);

    // Las condiciones del contrato aparecen hasta que ya hay productos
    $('bloqueDetalles').classList.toggle('d-none', equipos.length === 0);
    $('contProductos').textContent = equipos.length;

    const faltan = [];
    if (!$('clienteSelect').value) faltan.push('Selecciona el cliente');
    if (equipos.length === 0) faltan.push('Agrega al menos un producto');
    if (!descuentoListo(subtotal)) faltan.push('El descuento necesita autorización');
    $('faltante').innerHTML = faltan.length ? '<i class="bi bi-info-circle me-1"></i>' + faltan.join(' · ') : '';

    $('btnGuardar').disabled = equipos.length === 0 || !descuentoListo(subtotal);
    $('btnEspera').disabled = equipos.length === 0;
}

// ======================= PRODUCTOS ======================= //
function agregarEquipo() {
    const select = $('selectEquipo');
    const cantidad = parseInt($('cantidadEquipo').value);
    if (!select.value || !cantidad || cantidad < 1) { alert('Selecciona un producto y una cantidad válida'); return; }

    const op = select.options[select.selectedIndex];
    const stock = parseInt(op.dataset.stock);
    const existe = equipos.find(e => e.id == select.value);
    const total = cantidad + (existe ? existe.cantidad : 0);
    if (total > stock) {
        alert('Stock insuficiente. Solo hay ' + stock + ' unidades disponibles' + (existe ? ' (ya agregaste ' + existe.cantidad + ')' : ''));
        return;
    }
    if (existe) existe.cantidad = total;
    else equipos.push({ id: select.value, nombre: op.dataset.nombre, cantidad: cantidad, precio: parseFloat(op.dataset.precio), tarifa: op.dataset.tarifa || 'dia', stock: stock });

    renderizarEquipos();
    actualizarResumen();
    select.value = '';
    $('cantidadEquipo').value = '';
    select.focus();
}

function cambiarCantidad(i, valor) {
    let v = parseInt(valor) || 1;
    const eq = equipos[i];
    if (v < 1) v = 1;
    if (eq.stock && v > eq.stock) { alert('Stock insuficiente. Solo hay ' + eq.stock + ' unidades disponibles'); v = eq.stock; }
    eq.cantidad = v;
    renderizarEquipos();
    actualizarResumen();
}

function eliminarEquipo(i) {
    equipos.splice(i, 1);
    renderizarEquipos();
    actualizarResumen();
}

function renderizarEquipos() {
    const c = $('equiposLista');
    if (!equipos.length) {
        c.innerHTML = `<div class="border border-2 rounded-3 text-center text-secondary py-4 px-3" style="border-style: dashed !important;">
            <i class="bi bi-box-seam fs-3 d-block mb-1 opacity-50"></i>
            <div class="small fw-semibold">Aún no hay productos en el contrato</div>
            <div style="font-size: 11px;">Agrega el primero para ver factura, descuento y demás condiciones.</div></div>`;
        return;
    }
    const filas = equipos.map((eq, i) => `
        <tr>
            <td>
                <div class="fw-semibold small">${esc(eq.nombre)}</div>
                ${eq.stock ? `<div class="text-secondary" style="font-size: 11px;">Disponible: ${eq.stock}</div>` : ''}
                ${eq.tarifa === 'm2' ? `<div class="text-info" style="font-size: 11px;"><i class="bi bi-rulers me-1"></i>Tarifa por m² (no se multiplica por días)</div>` : ''}
                <input type="hidden" name="equipos[${i}][id]" value="${esc(eq.id)}">
            </td>
            <td class="text-center">
                <input type="number" name="equipos[${i}][cantidad]" min="1" ${eq.stock ? 'max="' + eq.stock + '"' : ''} value="${eq.cantidad}"
                       class="form-control form-control-sm bg-body text-center mx-auto" style="width: 80px;" onchange="cambiarCantidad(${i}, this.value)">
            </td>
            <td class="text-end text-secondary small">${money(eq.precio)} <small class="text-secondary">${eq.tarifa === 'm2' ? '/m²' : '/día'}</small></td>
            <td class="text-end fw-bold small" id="imp_${i}">$0.00</td>
            <td class="text-end"><span class="remove-equipo" onclick="eliminarEquipo(${i})" title="Quitar"><i class="bi bi-trash fs-6"></i></span></td>
        </tr>`).join('');
    c.innerHTML = `<div class="table-responsive"><table class="table table-sm align-middle mb-0 tabla-equipos">
        <thead><tr><th>Producto</th><th class="text-center">Cant.</th><th class="text-end">Tarifa</th><th class="text-end">Importe</th><th></th></tr></thead>
        <tbody>${filas}</tbody></table></div>`;
}

// ======================= CLIENTE / OBRAS ======================= //
$('clienteSelect').addEventListener('change', function () {
    const obraSelect = $('obraSelect');
    if (this.value) {
        obraSelect.innerHTML = '<option value="">Cargando obras...</option>';
        fetch(`/get-obras/${this.value}`)
            .then(r => r.json())
            .then(data => {
                obraSelect.innerHTML = '<option value="">Seleccionar obra (opcional)...</option>';
                if (data.length === 0) {
                    obraSelect.innerHTML += '<option value="" disabled>No hay obras registradas para este cliente</option>';
                } else {
                    data.forEach(obra => { obraSelect.innerHTML += `<option value="${obra.id}">${esc(obra.nombre)} - ${esc(obra.direccion)}</option>`; });
                    if (OBRA_PREVIA) { obraSelect.value = OBRA_PREVIA; OBRA_PREVIA = null; }
                }
            })
            .catch(err => { console.error('Error:', err); obraSelect.innerHTML = '<option value="">Error al cargar obras</option>'; });
    } else {
        obraSelect.innerHTML = '<option value="">Seleccionar obra (opcional)...</option>';
    }
    actualizarResumen();
});

['flete', 'mano_obra', 'deposito', 'descuento'].forEach(id => $(id).addEventListener('input', actualizarResumen));
$('fecha_inicio').addEventListener('change', actualizarResumen);
$('fecha_fin').addEventListener('change', actualizarResumen);

// ======================= DESCUENTO CON AUTORIZACIÓN ======================= //
function descuentoActual(subtotal) {
    const d = parseFloat($('descuento').value) || 0;
    return Math.min(Math.max(d, 0), subtotal);
}

function coincideSolicitud(sol, subtotal) {
    return !!sol
        && Math.abs(sol.monto - descuentoActual(subtotal)) < 0.01
        && String(sol.cliente_id || '') === String($('clienteSelect').value || '');
}

function autorizacionVigente(subtotal) {
    return !!solDescuento && solDescuento.estado === 'aprobada' && coincideSolicitud(solDescuento, subtotal);
}

function descuentoListo(subtotal) {
    if (descuentoActual(subtotal) <= 0) return true;
    if (PUEDE_AUTORIZAR_DESCUENTO) return true;
    return autorizacionVigente(subtotal);
}

function actualizarEstadoDescuento(subtotal) {
    subtotalVigente = subtotal;
    const hidden = $('solicitud_descuento_id');
    const zona = $('zonaAutorizacion');
    if (PUEDE_AUTORIZAR_DESCUENTO || !zona) return;

    const d = descuentoActual(subtotal);
    zona.style.display = d > 0 ? '' : 'none';
    hidden.value = autorizacionVigente(subtotal) ? solDescuento.id : '';

    const est = $('estadoDescuento'), btn = $('btnSolicitarDescuento');
    if (d <= 0) { est.innerHTML = ''; return; }

    const coincide = coincideSolicitud(solDescuento, subtotal);
    if (coincide && solDescuento.estado === 'pendiente') {
        est.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span>Esperando al gerente...</span>'
            + '<button type="button" class="btn btn-link btn-sm p-0 ms-2 fw-semibold" onclick="ponerEnEspera()"><i class="bi bi-pause-circle me-1"></i>Poner en espera y atender a otro cliente</button>';
        btn.disabled = true;
    } else if (coincide && solDescuento.estado === 'aprobada') {
        est.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-check-circle-fill me-1"></i>Autorizado' + (solDescuento.autorizador ? ' por ' + esc(solDescuento.autorizador) : '') + '</span>';
        btn.disabled = true;
    } else if (coincide && solDescuento.estado === 'rechazada') {
        est.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-x-circle-fill me-1"></i>Rechazado: guarda la renta sin descuento o pide otro monto</span>';
        btn.disabled = false;
    } else {
        est.innerHTML = '<span class="badge bg-secondary-subtle text-secondary border"><i class="bi bi-shield-exclamation me-1"></i>Requiere autorización</span>';
        btn.disabled = false;
    }
}

function mostrarToast(clase, html) {
    let c = $('toastAvisosTop');
    if (!c) {
        c = document.createElement('div');
        c.id = 'toastAvisosTop';
        c.className = 'toast-container position-fixed top-0 end-0 p-3';
        c.style.zIndex = 2100;
        document.body.appendChild(c);
    }
    const el = document.createElement('div');
    el.className = 'toast border-0 shadow-lg ' + clase;
    el.setAttribute('role', 'alert');
    el.innerHTML = '<div class="d-flex"><div class="toast-body">' + html + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div>';
    c.appendChild(el);
    el.addEventListener('hidden.bs.toast', () => el.remove());
    new bootstrap.Toast(el, { autohide: false }).show();
    return el;
}

function avisoDescuento(aprobada, monto, autorizador, cliente) {
    try {
        mostrarToast(aprobada ? 'text-bg-success' : 'text-bg-danger', `
            <div class="fw-bold"><i class="bi ${aprobada ? 'bi-check-circle-fill' : 'bi-x-circle-fill'} me-1"></i>Descuento de renta ${aprobada ? 'AUTORIZADO' : 'RECHAZADO'}</div>
            <div class="small">Cliente: <strong>${esc(cliente)}</strong></div>
            <div class="small">Monto: <strong>${money(monto)}</strong></div>
            ${autorizador ? '<div class="small">' + (aprobada ? 'Autorizó: ' : 'Resolvió: ') + esc(autorizador) + '</div>' : ''}`);
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator(), g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination); o.frequency.value = 880; g.gain.value = 0.08;
        o.start(); o.stop(ctx.currentTime + 0.25);
    } catch (e) { /* el aviso nunca debe romper el flujo */ }
}

$('btnSolicitarDescuento')?.addEventListener('click', function () {
    const cliente = $('clienteSelect');
    const motivo = $('descuentoMotivo').value.trim();
    const monto = descuentoActual(subtotalVigente);
    if (!cliente.value) { alert('Selecciona primero al cliente: el descuento se autoriza para un cliente específico.'); return; }
    if (monto <= 0) return;
    if (!motivo) { alert('Escribe el motivo del descuento.'); $('descuentoMotivo').focus(); return; }

    const btn = this; btn.disabled = true;
    fetch(URL_SOLICITAR_DESCUENTO, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            monto: monto, subtotal: subtotalVigente, motivo: motivo, cliente_id: cliente.value,
            tipo: 'descuento', origen: 'renta', reemplaza_id: solDescuento ? solDescuento.id : null
        })
    })
    .then(async r => { const d = await r.json().catch(() => ({})); if (!r.ok || !d.success) throw new Error(d.message || 'No se pudo enviar la solicitud.'); return d; })
    .then(d => {
        solDescuento = { id: d.id, monto: monto, estado: 'pendiente', cliente_id: cliente.value, cliente_nombre: nombreCliente() };
        actualizarResumen();
    })
    .catch(e => { alert(e.message); btn.disabled = false; });
});

// Si el formulario regresó con error (p. ej. stock), recupera la autorización que ya se tenía
function recuperarAutorizacionPrevia() {
    const previo = $('solicitud_descuento_id').value;
    if (!previo || PUEDE_AUTORIZAR_DESCUENTO) return;
    fetch(URL_ESTADOS_DESCUENTO + '?ids=' + previo, { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            const info = data && data[previo];
            if (info && (info.estado === 'aprobada' || info.estado === 'pendiente')) {
                solDescuento = { id: parseInt(previo), monto: info.monto, estado: info.estado, autorizador: info.autorizador,
                                 cliente_id: $('clienteSelect').value, cliente_nombre: nombreCliente() };
                actualizarResumen();
            }
        }).catch(() => {});
}

// ======================= VIGILANCIA ÚNICA (renta actual + rentas en espera) ======================= //
function idsPendientes() {
    const ids = new Set();
    if (solDescuento && solDescuento.estado === 'pendiente') ids.add(solDescuento.id);
    leerEspera().forEach(b => { if (b.sol && b.sol.estado === 'pendiente') ids.add(b.sol.id); });
    return [...ids];
}

function revisarSolicitudes() {
    const ids = idsPendientes();
    if (!ids.length) return;
    fetch(URL_ESTADOS_DESCUENTO + '?ids=' + ids.join(','), { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data) return;
            let cambioActual = false, cambioEspera = false;

            if (solDescuento && solDescuento.estado === 'pendiente') {
                const info = data[solDescuento.id];
                if (info && info.estado !== 'pendiente') {
                    if (info.estado === 'aprobada' || info.estado === 'rechazada') {
                        solDescuento.estado = info.estado;
                        solDescuento.autorizador = info.autorizador || null;
                        avisoDescuento(info.estado === 'aprobada', solDescuento.monto, info.autorizador, solDescuento.cliente_nombre);
                    } else {
                        solDescuento = null; // cancelada / usada
                    }
                    cambioActual = true;
                }
            }

            const lista = leerEspera();
            lista.forEach(b => {
                if (!b.sol || b.sol.estado !== 'pendiente') return;
                const info = data[b.sol.id];
                if (!info || info.estado === 'pendiente') return;
                if (info.estado === 'aprobada' || info.estado === 'rechazada') {
                    b.sol.estado = info.estado;
                    b.sol.autorizador = info.autorizador || null;
                    avisoDescuento(info.estado === 'aprobada', b.sol.monto, info.autorizador, (b.cliente_nombre || 'Cliente') + ' (en espera)');
                } else {
                    b.sol = null;
                }
                cambioEspera = true;
            });

            if (cambioEspera) { guardarEspera(lista); renderEspera(); }
            if (cambioActual) actualizarResumen();
        })
        .catch(() => {});
}

// ======================= RENTAS EN ESPERA ======================= //
function leerEspera() {
    try { return JSON.parse(localStorage.getItem(KEY_ESPERA) || '[]') || []; } catch (e) { return []; }
}
function guardarEspera(lista) {
    try { localStorage.setItem(KEY_ESPERA, JSON.stringify(lista)); return true; }
    catch (e) { alert('No se pudo guardar la renta en espera en este navegador.'); return false; }
}

function capturarFormulario() {
    return {
        id: 'b' + Date.now() + Math.floor(Math.random() * 1000),
        ts: Date.now(),
        cliente_id: $('clienteSelect').value,
        cliente_nombre: nombreCliente() || 'Sin cliente',
        obra_id: $('obraSelect').value,
        fecha_inicio: $('fecha_inicio').value,
        fecha_fin: $('fecha_fin').value,
        requiere_factura: requiereFactura ? '1' : '0',
        flete: $('flete').value, mano_obra: $('mano_obra').value,
        descuento: $('descuento').value, deposito: $('deposito').value,
        motivo: $('descuentoMotivo') ? $('descuentoMotivo').value : '',
        observaciones: $('observaciones').value,
        equipos: equipos.map(e => Object.assign({}, e)),
        total: totalVigente,
        sol: (!PUEDE_AUTORIZAR_DESCUENTO && solDescuento) ? Object.assign({}, solDescuento) : null
    };
}

function limpiarFormulario() {
    equipos = []; solDescuento = null;
    $('clienteSelect').value = '';
    $('obraSelect').innerHTML = '<option value="">Seleccionar obra (opcional)...</option>';
    const hoy = new Date(), manana = new Date(); manana.setDate(manana.getDate() + 1);
    $('fecha_inicio').value = fechaLocal(hoy);
    $('fecha_fin').value = fechaLocal(manana);
    ['flete', 'mano_obra', 'descuento', 'deposito'].forEach(id => $(id).value = 0);
    $('observaciones').value = '';
    if ($('descuentoMotivo')) $('descuentoMotivo').value = '';
    $('solicitud_descuento_id').value = '';
    renderizarEquipos();
    seleccionarFacturacion(true);   // también refresca el resumen
}

function cargarBorrador(b) {
    equipos = (b.equipos || []).map(e => Object.assign({}, e));
    equipos.forEach(e => {
        if (!e.tarifa) {
            const o = document.querySelector('#selectEquipo option[value="' + e.id + '"]');
            e.tarifa = o ? (o.dataset.tarifa || 'dia') : 'dia';
        }
    });
    solDescuento = b.sol ? Object.assign({}, b.sol) : null;
    $('clienteSelect').value = b.cliente_id || '';
    OBRA_PREVIA = b.obra_id || null;
    $('clienteSelect').dispatchEvent(new Event('change'));   // carga obras y refresca
    $('fecha_inicio').value = b.fecha_inicio;
    $('fecha_fin').value = b.fecha_fin;
    $('flete').value = b.flete; $('mano_obra').value = b.mano_obra;
    $('descuento').value = b.descuento; $('deposito').value = b.deposito;
    $('observaciones').value = b.observaciones || '';
    if ($('descuentoMotivo')) $('descuentoMotivo').value = b.motivo || '';
    renderizarEquipos();
    seleccionarFacturacion(b.requiere_factura === '1');
}

function ponerEnEspera() {
    if (equipos.length === 0) { alert('Agrega al menos un producto para poder ponerla en espera.'); return; }
    actualizarResumen();
    const lista = leerEspera();
    const borrador = capturarFormulario();
    lista.unshift(borrador);
    if (!guardarEspera(lista)) return;
    limpiarFormulario();
    renderEspera();
    mostrarToast('text-bg-secondary', '<div class="fw-bold"><i class="bi bi-pause-circle-fill me-1"></i>Renta en espera</div><div class="small">' + esc(borrador.cliente_nombre) + ' · ' + money(borrador.total) + '</div><div class="small">Ya puedes atender a otro cliente.</div>');
    setTimeout(() => document.querySelectorAll('#toastAvisosTop .text-bg-secondary').forEach(t => bootstrap.Toast.getOrCreateInstance(t).hide()), 4000);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function retomar(id) {
    let lista = leerEspera();
    const b = lista.find(x => x.id === id);
    if (!b) return;
    lista = lista.filter(x => x.id !== id);
    if (equipos.length > 0) {      // lo que hay en pantalla pasa a espera (intercambio)
        actualizarResumen();
        lista.unshift(capturarFormulario());
    }
    if (!guardarEspera(lista)) return;
    cargarBorrador(b);
    renderEspera();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function eliminarEspera(id) {
    if (!confirm('¿Eliminar esta renta en espera? Si tenía un descuento autorizado, se perderá.')) return;
    guardarEspera(leerEspera().filter(x => x.id !== id));
    renderEspera();
}

function estadoEspera(b) {
    const d = parseFloat(b.descuento) || 0;
    const base = { clase: '', badge: 'bg-secondary-subtle text-secondary border', icono: 'bi-pause-circle', texto: 'En espera' };
    if (d <= 0 || PUEDE_AUTORIZAR_DESCUENTO) return base;
    const s = b.sol;
    if (!s || Math.abs(s.monto - d) >= 0.01) return Object.assign(base, { badge: 'bg-secondary-subtle text-secondary border', icono: 'bi-shield-exclamation', texto: 'Descuento sin solicitar' });
    if (s.estado === 'pendiente') return { clase: '', badge: 'bg-warning-subtle text-warning-emphasis border border-warning-subtle', icono: 'bi-hourglass-split', texto: 'Esperando al gerente' };
    if (s.estado === 'aprobada') return { clase: 'lista', badge: 'bg-success-subtle text-success border border-success-subtle', icono: 'bi-check-circle-fill', texto: 'Descuento autorizado' };
    return { clase: 'rechazada', badge: 'bg-danger-subtle text-danger border border-danger-subtle', icono: 'bi-x-circle-fill', texto: 'Descuento rechazado' };
}

function renderEspera() {
    const lista = leerEspera();
    $('barraEspera').classList.toggle('d-none', lista.length === 0);
    $('contEspera').textContent = lista.length;
    $('listaEspera').innerHTML = lista.map(b => {
        const e = estadoEspera(b);
        const n = (b.equipos || []).length;
        return `<div class="col-12 col-md-6 col-xl-4"><div class="espera-card ${e.clase}">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div style="min-width: 0;">
                    <div class="fw-bold small text-truncate">${esc(b.cliente_nombre)}</div>
                    <div class="text-secondary" style="font-size: 11px;">${n} producto${n === 1 ? '' : 's'} · ${money(b.total)} · ${hace(b.ts)}</div>
                </div>
                <button type="button" class="btn btn-link text-danger p-0" title="Eliminar" onclick="eliminarEspera('${b.id}')"><i class="bi bi-trash"></i></button>
            </div>
            <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
                <span class="badge ${e.badge}">${e.icono === 'bi-hourglass-split' ? '<span class="spinner-border spinner-border-sm me-1" style="width:10px;height:10px;"></span>' : '<i class="bi ' + e.icono + ' me-1"></i>'}${e.texto}</span>
                <button type="button" class="btn btn-sm btn-primary py-0 px-3 fw-semibold" onclick="retomar('${b.id}')">Retomar</button>
            </div>
        </div></div>`;
    }).join('');
}

// ======================= ENVÍO ======================= //
$('formRenta').addEventListener('submit', function (e) {
    actualizarResumen();
    if (equipos.length === 0) { e.preventDefault(); alert('Agrega al menos un producto al contrato.'); return; }
    if (!descuentoListo(subtotalVigente)) {
        e.preventDefault();
        alert('El descuento todavía no está autorizado para este monto y cliente. Espera la autorización o ajusta el monto.');
        return;
    }
    enviando = true;
});

// Enter en un campo no debe enviar el formulario (en "Cantidad" agrega el producto)
$('formRenta').addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
        e.preventDefault();
        if (e.target.id === 'cantidadEquipo') agregarEquipo();
    }
});

// Evita perder una renta a medias por cerrar o salirse de la pantalla
window.addEventListener('beforeunload', function (e) {
    if (!enviando && equipos.length > 0) { e.preventDefault(); e.returnValue = ''; }
});

document.addEventListener('DOMContentLoaded', function () {
    if ($('clienteSelect').value) $('clienteSelect').dispatchEvent(new Event('change'));
    renderizarEquipos();
    seleccionarFacturacion(FACTURA_PREVIA);   // también refresca el resumen
    renderEspera();
    recuperarAutorizacionPrevia();
    // Retomar una renta en espera desde el Historial de Rentas (?retomar=ID)
    const idRetomar = new URLSearchParams(window.location.search).get('retomar');
    if (idRetomar) {
        retomar(idRetomar);
        history.replaceState(null, '', window.location.pathname);
    }
    revisarSolicitudes();
    timerVigilancia = setInterval(revisarSolicitudes, 5000);
    setInterval(renderEspera, 60000);          // refresca el "hace X min"
});

// ====== LÓGICA DEL MODAL DE NUEVA OBRA ====== //

document.getElementById('modalNuevaObra').addEventListener('show.bs.modal', function () {
    const clienteSelectRenta = document.getElementById('clienteSelect');
    const modalClienteId = document.getElementById('modal_cliente_id');
    if(clienteSelectRenta.value) {
        modalClienteId.value = clienteSelectRenta.value;
    }
});

document.getElementById('formNuevaObraAjax').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const btnGuardar = document.getElementById('btnGuardarObraAjax');
    const originalBtnHtml = btnGuardar.innerHTML;
    
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

    fetch("{{ route('obras.store') }}", {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData 
    })
    .then(async response => {
        if (!response.ok) {
            if (response.status === 422) { 
                const data = await response.json();
                let errors = '';
                for (let field in data.errors) {
                    errors += data.errors[field].join('\n') + '\n';
                }
                throw new Error(errors);
            }
            throw new Error('Ocurrió un error en el servidor al guardar la obra.');
        }
        return response.json();
    })
    .then(data => {
        if(data.success) {
            const clienteActual = document.getElementById('clienteSelect').value;
            if(clienteActual == data.obra.cliente_id) {
                const obraSelect = document.getElementById('obraSelect');
                const option = new Option(data.obra.nombre + ' - ' + data.obra.direccion, data.obra.id, true, true);
                obraSelect.add(option);
            }
            form.reset();
            const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalNuevaObra'));
            modalInstance.hide();
            alert('¡La obra fue registrada y seleccionada exitosamente!');
        }
    })
    .catch(error => {
        alert("Errores al guardar:\n\n" + error.message);
    })
    .finally(() => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = originalBtnHtml;
    });
});
</script>
@endsection