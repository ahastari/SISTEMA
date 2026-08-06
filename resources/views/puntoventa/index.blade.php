@extends('layouts.admin')

@section('content')
<style>
    /* Estructura Global Adaptativa al Tema */
    .pos-layout {
        display: flex;
        gap: 20px;
        height: calc(100vh - 170px); 
        min-height: 500px;
        overflow: hidden; 
    }
    .pos-catalog {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 16px;
        height: 100%;
        overflow: hidden;
    }
    .pos-products-scroll {
        flex: 1;
        overflow-y: auto;
        padding-right: 4px;
    }
    
    /* Panel Lateral de Cobro Inteligente */
    .pos-cart-panel {
        width: 420px;
        background: var(--bs-body-bg);
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column; 
        border: 1px solid var(--bs-border-color);
        height: 100%; 
        overflow: hidden;
    }
    
    #carritoItems {
        max-height: 35%; 
        overflow-y: auto; 
        padding: 12px 16px;
        border-bottom: 1px dashed var(--bs-border-color);
    }
    
    .cart-total {
        background: var(--bs-tertiary-bg);
        border-top: 1px solid var(--bs-border-color);
        padding: 16px 20px;
        flex: 1;
        overflow-y: auto; 
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    /* Tarjetas de Producto */
    .product-card {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        padding: 16px;
        cursor: pointer;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
    }
    .product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.12);
        border-color: #0d6efd;
    }
    .product-card .price {
        font-size: 18px;
        font-weight: 700;
        color: #198754;
        margin-top: 8px;
    }
    .product-card .stock {
        font-size: 11px;
        font-weight: 600;
        color: var(--bs-secondary-color);
        background: var(--bs-secondary-bg);
        padding: 2px 8px;
        border-radius: 20px;
        display: inline-block;
        width: fit-content;
        margin-top: 6px;
    }

    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        margin-bottom: 6px;
        background: var(--bs-tertiary-bg);
        border-radius: 10px;
        border: 1px solid var(--bs-border-color);
    }
    .cart-item-qty-actions {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .cart-qty-input {
        width: 50px;
        text-align: center;
        font-weight: bold;
        font-size: 13px;
        padding: 2px 4px;
    }

    /* Indicadores de Caja */
    .caja-badge {
        padding: 8px 16px;
        border-radius: 30px;
        font-weight: 600;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    .caja-badge.abierta {
        background: rgba(25, 135, 84, 0.15);
        color: #198754;
        border: 1px solid rgba(25, 135, 84, 0.25);
    }
    .caja-badge.cerrada {
        background: rgba(220, 53, 69, 0.15);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.25);
    }

    #configDropdown::after {
        display: none !important;
    }
    #configDropdown * {
        pointer-events: none;
    }

    @media (max-width: 991.98px) {
        .pos-layout {
            flex-direction: column;
            height: auto;
            overflow: visible;
        }
        .pos-cart-panel {
            width: 100%;
            height: auto; 
            max-height: 600px;
        }
        .pos-catalog {
            overflow: visible;
            height: auto;
        }
        .pos-products-scroll {
            overflow-y: visible;
        }
    }
    
    .product-image-wrapper {
        width: 100%;
        height: 120px; 
        background-color: var(--bs-tertiary-bg);
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--bs-border-color);
    }
    .product-img-render {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    .product-card:hover .product-img-render {
        transform: scale(1.05);
    }
    .product-img-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-3">
    <div>
        <h4 class="mb-0 fw-bold text-body"><i class="bi bi-cpu text-primary me-2"></i>Punto de Venta</h4>
    </div>
    
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if($corteActivo)
            <div class="caja-badge abierta">
                <span>Caja Abierta | Inicial: <strong>${{ number_format($corteActivo->monto_inicial, 2) }}</strong></span>
                <span class="mx-1 text-muted d-none d-sm-inline">|</span>
                <span>Ventas: <strong class="text-primary">$<span id="caja-total-ventas">{{ number_format($corteActivo->total_ventas, 2) }}</span></strong></span>
            </div>
            <button class="btn btn-danger btn-sm rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCerrarCaja">
                <i class="bi bi-lock-fill me-1"></i> Cerrar Caja / Turno
            </button>
        @else
            <div class="caja-badge cerrada">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Operaciones Suspendidas (Caja Cerrada)</span>
            </div>
            <button class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAbrirCaja">
                <i class="bi bi-unlock-fill me-1"></i> Abrir Turno de Caja
            </button>
        @endif

        <div class="dropdown">
            <button class="btn btn-outline-secondary btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center dropdown-toggle" 
                    type="button" 
                    id="configDropdown" 
                    data-bs-toggle="dropdown" 
                    aria-expanded="false"
                    style="width: 34px; height: 34px;">
                <i class="bi bi-three-dots-vertical fs-6"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border border-translucent mt-2" aria-labelledby="configDropdown">
                <li><a class="dropdown-item py-2" href="{{ route('puntoventa.historial') }}"><i class="bi bi-clock-history me-2 text-secondary"></i> Historial de Ventas (Día)</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('puntoventa.cortes') }}"><i class="bi bi-cash-stack me-2 text-secondary"></i> Historial de Cortes</a></li>
                
                {{-- 🔒 OCULTAR REPORTES FINANCIEROS AL CAJERO --}}
                @if(Auth::user()->isAdmin() || Auth::user()->isGerente())
                <li><a class="dropdown-item py-2" href="{{ route('puntoventa.reportes') }}"><i class="bi bi-graph-up-arrow me-2 text-secondary"></i> Dashboard e Informes</a></li>
                @endif
                
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalMovimiento"><i class="bi bi-arrow-left-right me-2 text-secondary"></i> Entrada / Salida Efectivo</a></li>
            </ul>
        </div>
    </div>
</div>

@if(!$corteActivo)
    <div class="text-center py-5 my-4 bg-body border rounded-4 shadow-sm p-5">
        <div class="display-1 text-secondary mb-4"><i class="bi bi-cash-register text-secondary opacity-50"></i></div>
        <h3 class="fw-bold text-body">La estación de cobro se encuentra bloqueada</h3>
        <p class="text-secondary small max-w-md mx-auto mb-4">Para comenzar a pasar artículos, registrar clientes y emitir comprobantes térmicos, es obligatorio iniciar el fondo de caja del turno correspondiente.</p>
        <button class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow" data-bs-toggle="modal" data-bs-target="#modalAbrirCaja">
            <i class="bi bi-unlock-fill me-2"></i> Aperturar Turno Ahora
        </button>
    </div>
@else
    <div class="pos-layout">
        <div class="pos-catalog">
            <div class="row g-2 bg-body p-3 rounded-4 shadow-sm border" style="border-color: var(--bs-border-color) !important;">
                <div class="col-md-8">
                    <div class="input-group input-group-sm border rounded-3 overflow-hidden bg-body-tertiary">
                        <span class="input-group-text bg-transparent border-0 text-secondary"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscarProducto" class="form-control bg-transparent border-0 p-2 text-body" placeholder="Escanea código de barras o busca por nombre...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="filtroCategoria" class="form-select form-select-sm border rounded-3 p-2 bg-body-tertiary text-body">
                        <option value="">Todas las líneas de producto</option>
                        @foreach($productos->pluck('categoria.nombre')->unique() as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pos-products-scroll">
                <div class="row g-2" id="listaProductos">
                    @foreach($productos as $producto)
                        @if(in_array($producto->tipo_operacion, ['venta', 'ambas']))
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="product-card" onclick="agregarProducto({{ $producto->id }})">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-secondary font-monospace" style="font-size: 10px;">{{ $producto->codigo }}</span>
                                        <i class="bi bi-box-seam text-secondary small"></i>
                                    </div>

                                    <div class="product-image-wrapper mb-2">
                                        @if($producto->imagen && file_exists(public_path('storage/' . $producto->imagen)))
                                            <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="product-img-render">
                                        @elseif($producto->imagen && (str_starts_with($producto->imagen, 'http') || str_starts_with($producto->imagen, 'https')))
                                            <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}" class="product-img-render">
                                        @else
                                            <div class="product-img-placeholder text-secondary">
                                                <i class="bi bi-image fs-4 shadow-none"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <h6 class="fw-bold text-body mb-1" style="font-size: 13px; line-height: 1.4; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        {{ \Illuminate\Support\Str::limit($producto->nombre, 35) }}
                                    </h6>
                                </div>
                                <div class="mt-2">
                                    <div class="price">${{ number_format($producto->precio_venta ?? $producto->precio_dia, 2) }}</div>
                                    <div class="stock">Disponibles: <span id="stock-val-{{ $producto->id }}">{{ $producto->stock }}</span></div>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div class="pos-cart-panel">
            <div class="p-3 border-bottom bg-body-tertiary d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-body"><i class="bi bi-bag-check-fill text-primary me-2"></i>Artículos a Vender</h6>
                <span class="badge bg-secondary rounded-pill px-2" id="contadorItems">0 items</span>
            </div>

            <!-- Botones de Cargos Especiales (Flete / Mano de Obra) -->
            <div class="p-2 bg-body border-bottom d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary flex-fill fw-bold" onclick="agregarServicioEspecial('flete')">
                    <i class="bi bi-truck me-1"></i> + Flete
                </button>
                <button class="btn btn-sm btn-outline-warning flex-fill fw-bold" onclick="agregarServicioEspecial('mano_obra')">
                    <i class="bi bi-tools me-1"></i> + Mano de Obra
                </button>
            </div>
            
            <div id="carritoItems">
                <div class="text-center text-secondary py-5 my-2">
                    <i class="bi bi-cart3 text-secondary mb-2 opacity-50" style="font-size: 40px;"></i>
                    <p class="small fw-semibold mb-0">El carrito está vacío.<br>Haz clic en los productos para agregarlos.</p>
                </div>
            </div>
            
            <div class="cart-total">

                <div class="mb-1">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="requiereFactura">
                        <label class="form-check-label small fw-semibold text-body" for="requiereFactura">¿Requiere Factura?</label>
                    </div>
                </div>

                <div class="mb-2">
                    <select id="clienteVenta" class="form-select form-select-sm bg-body text-body">
                        <option value="" data-rfc="">Cliente de Mostrador (Público General)</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" data-rfc="{{ $cliente->rfc ?? '' }}">
                                {{ $cliente->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="mb-1" id="campoRFC" style="display: none;">
                    <input type="text" id="rfcCliente" class="form-control form-control-sm bg-body text-body" placeholder="RFC del contribuyente">
                </div>

                <div class="bg-body p-3 rounded-3 shadow-sm border mb-2" style="border-color: var(--bs-border-color) !important;">
                    <div class="d-flex justify-content-between text-secondary small mb-1">
                        <span>Subtotal Bruto:</span>
                        <span id="subtotalCarrito" class="text-body fw-bold">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary small mb-2">
                        <span>Impuesto IVA (16%):</span>
                        <span id="ivaCarrito" class="text-body fw-bold">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center fw-bold border-top pt-2">
                        <span class="text-body fs-6">Gran Total:</span>
                        <span id="totalCarrito" class="text-success fs-5">$0.00</span>
                    </div>
                </div>
                
                <div class="mb-1">
                    <select id="metodoPago" class="form-select form-select-sm bg-body text-body">
                        <option value="" selected disabled>Selecciona el método...</option>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Terminal</option>
                        <option value="mixto">Pago Mixto</option>
                    </select>
                </div>

                <!-- DESGLOSE DINÁMICO DE PAGO MIXTO -->
                <div id="seccionPagoMixto" class="p-2 border rounded-3 mb-2 bg-body-tertiary" style="display: none;">
                    <small class="fw-bold text-primary d-block mb-2" style="font-size: 11px;">
                        <i class="bi bi-diagram-3-fill me-1"></i> Configurar Combinación de Pago
                    </small>
                    
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <select id="mixtoMetodo1" class="form-select form-select-sm bg-body text-body">
                                <option value="efectivo" selected>Efectivo</option>
                                <option value="tarjeta">Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body">$</span>
                                <input type="number" id="mixtoMonto1" class="form-control bg-body fw-bold" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6">
                            <select id="mixtoMetodo2" class="form-select form-select-sm bg-body text-body">
                                <option value="tarjeta" selected>Tarjeta</option>
                                <option value="transferencia">Transferencia</option>
                                <option value="efectivo">Efectivo</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-body">$</span>
                                <input type="number" id="mixtoMonto2" class="form-control bg-body fw-bold" step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-1" id="seccionCambio" style="display: none; background: var(--bs-secondary-bg); padding: 10px; border-radius: 10px;">
                    <div class="mb-1">
                        <label class="form-label text-body fw-bold mb-1" style="font-size: 11px;">Efectivo Recibido</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body text-secondary fw-bold">$</span>
                            <input type="number" id="montoRecibido" class="form-control bg-body text-body fw-bold" step="0.01" min="0" oninput="calcularCambio()">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <span class="text-secondary small fw-bold">Cambio:</span>
                        <span id="cambioCliente" class="fs-5 fw-bold text-primary">$0.00</span>
                    </div>
                </div>
                
                <div>
                    <button class="btn btn-success btn-sm w-100 rounded-3 shadow fw-bold py-2" id="btnRealizarVenta" onclick="realizarVenta()">
                        <i class="bi bi-shield-check me-2"></i> Registrar y Emitir Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- MODALES MANTENIDOS -->
<div class="modal fade" id="modalAbrirCaja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-success text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-unlock-fill me-2"></i> Apertura de Turno</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('puntoventa.abrirCaja') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Asignación de Turno *</label>
                        <select name="turno" class="form-select form-select-sm bg-body text-body" required>
                            <option value="mañana">Matutino (Mañana)</option>
                            <option value="tarde">Vespertino (Tarde)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-body">Efectivo Inicial de Fondo *</label>
                        <div class="input-group input-group-sm shadow-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary fw-bold">$</span>
                            <input type="number" name="monto_inicial" class="form-control bg-body text-success fw-bold fs-5" step="0.01" min="0" placeholder="0.00" required autofocus>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success fw-bold">Confirmar Apertura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCerrarCaja" tabindex="-1" aria-labelledby="modalCerrarCajaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-danger text-white border-0 py-2.5">
                <h6 class="modal-title fw-bold" id="modalCerrarCajaLabel"><i class="bi bi-lock-fill me-2"></i>Cierre de Turno</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('puntoventa.cerrarCaja') }}" method="POST">
                @csrf
                <div class="modal-body p-3 p-sm-4 bg-body-tertiary">
                    @if($corteActivo)
                        @php
                            // Calcular acumulados de Flete y Mano de Obra para las ventas de este turno
                            $montoFleteModal = 0;
                            $montoManoObraModal = 0;

                            if($corteActivo->ventas) {
                                foreach($corteActivo->ventas as $v) {
                                    foreach($v->detalles as $d) {
                                        if(str_contains(strtolower($d->concepto_especial ?? ''), 'flete')) {
                                            $montoFleteModal += $d->subtotal;
                                        } elseif(str_contains(strtolower($d->concepto_especial ?? ''), 'mano de obra')) {
                                            $montoManoObraModal += $d->subtotal;
                                        }
                                    }
                                }
                            }
                        @endphp

                        <div class="card border shadow-sm mb-3 rounded-3" style="background: var(--bs-body-bg); border-color: var(--bs-border-color) !important;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Fondo de Apertura:</span>
                                    <span class="fw-bold text-body">$<span id="m-inicial">{{ number_format($corteActivo->monto_inicial, 2) }}</span></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <span class="text-secondary small fw-bold text-uppercase" style="font-size: 11px;">Total Ventas Brutas:</span>
                                    <span class="fw-bold text-primary fs-5">$<span id="m-ventas-total">{{ number_format($corteActivo->total_ventas, 2) }}</span></span>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-secondary small fw-bold mb-2 text-uppercase tracking-wider" style="font-size: 11px;">Ventas y Servicios Cobrados</h6>
                        <div class="list-group shadow-sm mb-3 rounded-3" style="border: 1px solid var(--bs-border-color);">
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0">
                                <div><i class="bi bi-cash text-success me-2"></i> Efectivo</div>
                                <span class="fw-bold" id="m-v-efectivo">${{ number_format($corteActivo->total_efectivo, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-arrow-right-short text-info me-2"></i> Transferencias</div>
                                <span class="fw-bold" id="m-v-transferencia">${{ number_format($corteActivo->total_transferencias, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-credit-card text-primary me-2"></i> Tarjetas</div>
                                <span class="fw-bold" id="m-v-tarjeta">${{ number_format($corteActivo->total_tarjetas, 2) }}</span>
                            </div>

                            <!-- DESGLOSE DE FLETE Y MANO DE OBRA -->
                            @if($montoFleteModal > 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div class="text-info-emphasis"><i class="bi bi-truck text-info me-2"></i> Cobrado por Fletes</div>
                                <span class="fw-bold text-info-emphasis">${{ number_format($montoFleteModal, 2) }}</span>
                            </div>
                            @endif

                            @if($montoManoObraModal > 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div class="text-warning-emphasis"><i class="bi bi-tools text-warning me-2"></i> Cobrado por Mano de Obra</div>
                                <span class="fw-bold text-warning-emphasis">${{ number_format($montoManoObraModal, 2) }}</span>
                            </div>
                            @endif
                        </div>

                        <h6 class="text-secondary small fw-bold mb-2 text-uppercase tracking-wider" style="font-size: 11px;">Movimientos de Efectivo</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 border rounded shadow-sm text-center bg-body" style="border-color: var(--bs-border-color) !important;">
                                    <small class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 10px;">Ingresos (+)</small>
                                    <span class="fw-bold text-success" id="m-mov-ingresos" style="font-size: 13px;">${{ number_format($corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto'), 2) }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 border rounded shadow-sm text-center bg-body" style="border-color: var(--bs-border-color) !important;">
                                    <small class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 10px;">Egresos (-)</small>
                                    <span class="fw-bold text-danger" id="m-mov-egresos" style="font-size: 13px;">${{ number_format($corteActivo->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto'), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 text-center mb-3 shadow-sm bg-dark">
                            <span class="text-white-50 small text-uppercase d-block mb-1" style="font-size: 10px; letter-spacing: 0.3px;">Efectivo Esperado en Caja</span>
                            @php
                                $ingresosEfe = $corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
                                $egresosEfe = $corteActivo->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
                                $efeEsperado = $corteActivo->monto_inicial + $corteActivo->total_efectivo + $ingresosEfe - $egresosEfe;
                            @endphp
                            <h3 class="fw-bold mb-0 text-warning font-monospace">$<span id="m-total-esperado">{{ number_format($efeEsperado, 2) }}</span></h3>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-body mb-1">Efectivo Real Contado por Cajero <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm shadow-sm">
                                <span class="input-group-text bg-body-tertiary border-end-0 fw-bold text-secondary">$</span>
                                <input type="number" name="monto_final" class="form-control bg-body text-success fw-bold fs-6 border-start-0" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-3 bg-body">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-3 fw-semibold" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger px-4 fw-bold rounded-3">Confirmar y Cerrar Caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalMovimiento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-primary text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-arrow-left-right me-1"></i> Ajuste Extra de Efectivo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('puntoventa.movimiento') }}" method="POST">
                @csrf
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Tipo de Ajuste *</label>
                        <select name="tipo" class="form-select form-select-sm bg-body text-body" required>
                            <option value="ingreso">Ingreso (+ En efectivo)</option>
                            <option value="egreso">Egreso (- Retiro de efectivo)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Concepto / Motivo *</label>
                        <input type="text" name="concepto" class="form-control form-control-sm bg-body text-body" required placeholder="Ej: Compra de insumos rápidos">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-body">Monto del Movimiento *</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-body-tertiary text-secondary fw-bold">$</span>
                            <input type="number" name="monto" class="form-control bg-body text-body fw-bold" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-body">Metodo</label>
                        <select name="metodo" class="form-select form-select-sm bg-body text-body">
                            <option value="efectivo" selected>Efectivo</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="tarjeta">Terminal</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary fw-bold">Registrar Operación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTicket" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="width: 380px;">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-2">
                <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de Venta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="limpiarYEnfocarPOS()"></button>
            </div>
            <div class="modal-body p-0">
                <iframe id="iframeTicket" src="" style="width: 100%; height: 450px; border: none; display: block; background: #fff;"></iframe>
            </div>
            <div class="modal-footer py-2 d-flex gap-2">
                <button type="button" class="btn btn-sm btn-light flex-grow-1" data-bs-dismiss="modal" onclick="limpiarYEnfocarPOS()">Finalizar</button>
                <button type="button" class="btn btn-sm btn-primary px-3 fw-bold" onclick="imprimirTicketModal()">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let carrito = [];
let productos = @json($productos);

function agregarProducto(id) {
    const producto = productos.find(p => p.id === id);
    if (!producto) return;

    const item = carrito.find(i => i.id === id && !i.esEspecial);
    if (item) {
        if (item.cantidad < producto.stock) {
            item.cantidad++;
        } else {
            alert('Stock insuficiente en catálogo para ' + producto.nombre);
            return;
        }
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            codigo: producto.codigo,
            precio: producto.precio_venta || producto.precio_dia,
            cantidad: 1, 
            stock: producto.stock,
            esEspecial: false
        });
    }
    renderizarCarrito();
}

function agregarServicioEspecial(tipo) {
    let titulo = tipo === 'flete' ? 'Servicio de Flete / Envío' : 'Servicio de Mano de Obra / Instalación';
    let codigo = tipo === 'flete' ? 'FLE-000' : 'MOB-000';
    
    let monto = prompt(`Ingrese el costo total para ${titulo}:`, "100.00");
    if (monto === null) return;
    
    monto = parseFloat(monto);
    if (isNaN(monto) || monto <= 0) {
        alert('Por favor ingrese un monto válido.');
        return;
    }

    // ID negativo único para evitar colisión con IDs de BD
    let idEspecial = tipo === 'flete' ? -1 : -2;
    let itemExistente = carrito.find(i => i.id === idEspecial);

    if (itemExistente) {
        itemExistente.precio = monto;
    } else {
        carrito.push({
            id: idEspecial,
            nombre: titulo,
            codigo: codigo,
            precio: monto,
            cantidad: 1,
            stock: 999,
            esEspecial: true
        });
    }
    renderizarCarrito();
}

function eliminarItem(id) {
    carrito = carrito.filter(i => i.id !== id);
    renderizarCarrito();
}

function cambiarCantidadTeclado(id, input) {
    let nuevaCantidad = parseInt(input.value) || 1;
    const item = carrito.find(i => i.id === id);
    if (!item) return;

    if (nuevaCantidad < 1) {
        nuevaCantidad = 1;
        input.value = 1;
    }

    if (!item.esEspecial && nuevaCantidad > item.stock) {
        alert('Stock insuficiente (Máximo: ' + item.stock + ')');
        nuevaCantidad = item.stock;
        input.value = item.stock;
    }

    item.cantidad = nuevaCantidad;
    renderizarCarrito(false); // Renderizar sin perder el foco del input activo
}

function renderizarCarrito(redibujarHTML = true) {
    const container = document.getElementById('carritoItems');
    const contador = document.getElementById('contadorItems');
    let subtotal = 0;

    if (carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center text-secondary py-5 my-2">
                <i class="bi bi-cart3 text-secondary mb-2 opacity-50" style="font-size: 40px;"></i>
                <p class="small fw-semibold mb-0">El carrito está vacío.<br>Haz clic en los productos para agregarlos.</p>
            </div>
        `;
        contador.textContent = '0 items';
        document.getElementById('subtotalCarrito').textContent = '$0.00';
        document.getElementById('ivaCarrito').textContent = '$0.00';
        document.getElementById('totalCarrito').textContent = '$0.00';
        calcularCambio();
        return;
    }

    if (redibujarHTML) {
        let html = '';
        carrito.forEach((item) => {
            html += `
                <div class="cart-item">
                    <div style="max-width: 55%;" class="text-truncate">
                        <div class="fw-bold text-body small text-truncate">
                            <span class="badge ${item.esEspecial ? 'bg-warning text-dark' : 'bg-secondary'} font-monospace p-1 me-1" style="font-size: 9px;">${item.codigo}</span> ${item.nombre}
                        </div>
                        <small class="text-secondary">$${parseFloat(item.precio).toFixed(2)} c/u</small>
                    </div>
                    <div class="cart-item-qty-actions">
                        <input type="number" class="form-control form-control-sm cart-qty-input bg-body text-body border" 
                               value="${item.cantidad}" min="1" ${item.esEspecial ? 'disabled' : ''} 
                               oninput="cambiarCantidadTeclado(${item.id}, this)">
                        <button class="btn btn-sm btn-link text-danger ms-1 p-0" onclick="eliminarItem(${item.id})">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    carrito.forEach((item) => {
        subtotal += item.precio * item.cantidad;
    });

    contador.textContent = carrito.reduce((sum, i) => sum + i.cantidad, 0) + ' items';

    const requiereFactura = document.getElementById('requiereFactura').checked;
    const iva = requiereFactura ? subtotal * 0.16 : 0;
    const total = subtotal + iva;
    
    document.getElementById('subtotalCarrito').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('ivaCarrito').textContent = requiereFactura ? '$' + iva.toFixed(2) : '$0.00';
    document.getElementById('totalCarrito').textContent = '$' + total.toFixed(2);
    
    calcularCambio();
}

const reqFacturaCheck = document.getElementById('requiereFactura');
if (reqFacturaCheck) {
    reqFacturaCheck.addEventListener('change', function() {
        document.getElementById('campoRFC').style.display = this.checked ? 'block' : 'none';
        renderizarCarrito(false);
    });
}

// Listener para autocompletar el RFC al seleccionar un cliente
const selectClienteVenta = document.getElementById('clienteVenta');
if (selectClienteVenta) {
    selectClienteVenta.addEventListener('change', function() {
        const optionSeleccionada = this.options[this.selectedIndex];
        const rfcGuardado = optionSeleccionada.getAttribute('data-rfc') || '';
        
        const checkFactura = document.getElementById('requiereFactura');
        const campoRFC = document.getElementById('campoRFC');
        const inputRFC = document.getElementById('rfcCliente');

        if (rfcGuardado.trim() !== '') {
            // Si el cliente tiene RFC registrado, autocompletar y activar switch de factura
            inputRFC.value = rfcGuardado;
            checkFactura.checked = true;
            campoRFC.style.display = 'block';
        } else {
            // Si es Cliente General o no tiene RFC, limpiar y ocultar si no lo había activado manualmente
            inputRFC.value = '';
            if (!this.value) { // Si regresó a Público General
                checkFactura.checked = false;
                campoRFC.style.display = 'none';
            }
        }
        renderizarCarrito(false);
    });
}

const selectMetodoPago = document.getElementById('metodoPago');
if (selectMetodoPago) {
    selectMetodoPago.addEventListener('change', function() {
        const seccionCambio = document.getElementById('seccionCambio');
        const seccionMixto = document.getElementById('seccionPagoMixto');
        const inputRecibido = document.getElementById('montoRecibido');
        
        if (this.value === 'efectivo') {
            seccionCambio.style.display = 'block';
            seccionMixto.style.display = 'none';
            if (inputRecibido) inputRecibido.focus();
        } else if (this.value === 'mixto') {
            seccionCambio.style.display = 'none';
            seccionMixto.style.display = 'block';
        } else {
            seccionCambio.style.display = 'none';
            seccionMixto.style.display = 'none';
        }
    });
}

function calcularCambio() {
    const totalRaw = document.getElementById('totalCarrito').textContent.replace('$', '').replace(',', '');
    const total = parseFloat(totalRaw) || 0;
    
    const inputRecibido = document.getElementById('montoRecibido');
    if (!inputRecibido) return;
    
    const recibido = parseFloat(inputRecibido.value) || 0;
    const cambio = recibido - total;
    const elCambio = document.getElementById('cambioCliente');

    if (!inputRecibido.value) {
        elCambio.textContent = '$0.00';
        elCambio.className = 'fs-5 fw-bold text-primary';
        return;
    }

    if (cambio >= 0) {
        elCambio.textContent = '$' + cambio.toFixed(2);
        elCambio.className = 'fw-bold text-success';
    } else {
        elCambio.textContent = 'Faltan $' + Math.abs(cambio).toFixed(2);
        elCambio.className = 'fw-bold text-danger';
    }
}

function realizarVenta() {
    if (carrito.length === 0) {
        alert('Agrega productos al carrito');
        return;
    }

    const metodoPago = document.getElementById('metodoPago').value;
    const clienteId = document.getElementById('clienteVenta').value;
    const requiereFactura = document.getElementById('requiereFactura').checked;
    const rfcCliente = document.getElementById('rfcCliente').value;

    if (!metodoPago) {
        alert('Selecciona un método de pago');
        return;
    }

    const totalRaw = document.getElementById('totalCarrito').textContent.replace('$', '').replace(',', '');
    const totalVenta = parseFloat(totalRaw) || 0;

    if (metodoPago === 'efectivo') {
        const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        if (recibido < totalVenta) {
            alert('El monto recibido es menor al total de la venta.');
            document.getElementById('montoRecibido').focus();
            return;
        }
    }

    let detalleMixto = [];
    let sumaPagoMixto = 0;

    if (metodoPago === 'mixto') {
        const m1 = document.getElementById('mixtoMetodo1').value;
        const monto1 = parseFloat(document.getElementById('mixtoMonto1').value) || 0;
        const m2 = document.getElementById('mixtoMetodo2').value;
        const monto2 = parseFloat(document.getElementById('mixtoMonto2').value) || 0;

        if (m1 === m2) {
            alert('En pago mixto debes elegir dos métodos de pago diferentes.');
            return;
        }

        sumaPagoMixto = monto1 + monto2;
        if (sumaPagoMixto < totalVenta) {
            alert('La suma de los dos pagos ($' + sumaPagoMixto.toFixed(2) + ') no cubre el total ($' + totalVenta.toFixed(2) + ').');
            return;
        }

        detalleMixto = [{ metodo: m1, monto: monto1 }, { metodo: m2, monto: monto2 }];
    }

    const inputEfectivo = document.getElementById('montoRecibido');
    const valorIngresado = inputEfectivo ? parseFloat(inputEfectivo.value) : 0;

    let finalMontoRecibido = totalVenta;
    if (metodoPago === 'efectivo') {
        finalMontoRecibido = (!isNaN(valorIngresado) && valorIngresado > 0) ? valorIngresado : totalVenta;
    } else if (metodoPago === 'mixto') {
        finalMontoRecibido = sumaPagoMixto;
    }

    const data = {
        items: carrito.map(i => ({ 
            id: i.id, 
            cantidad: i.cantidad,
            precio: i.precio,
            nombre: i.nombre,
            esEspecial: i.esEspecial 
        })),
        metodo_pago: metodoPago,
        pagos_mixtos: detalleMixto,
        monto_recibido: finalMontoRecibido,
        cliente_id: clienteId || null,
        requiere_factura: requiereFactura ? 1 : 0, 
        rfc_cliente: requiereFactura ? rfcCliente : null
    };

    const btn = document.getElementById('btnRealizarVenta');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

    fetch('{{ route("puntoventa.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const responseData = await response.json();
        if (!response.ok) {
            throw new Error(responseData.message || 'Error detectado al validar la venta.');
        }
        return responseData;
    })
    .then(data => {
        if (data.success) {
            carrito.forEach(item => {
                if (!item.esEspecial) {
                    const prodLocal = productos.find(p => p.id === item.id);
                    if (prodLocal) {
                        prodLocal.stock -= item.cantidad;
                        const elStockHTML = document.getElementById(`stock-val-${item.id}`);
                        if (elStockHTML) elStockHTML.textContent = prodLocal.stock;
                    }
                }
            });

            // Actualizar montos en vivo en el modal de cierre
            if (data.modal_data) {
                const m = data.modal_data;
                if (document.getElementById('m-ventas-total')) document.getElementById('m-ventas-total').textContent = m.total_ventas;
                if (document.getElementById('m-v-efectivo')) document.getElementById('m-v-efectivo').textContent = '$' + m.total_efectivo;
                if (document.getElementById('m-v-transferencia')) document.getElementById('m-v-transferencia').textContent = '$' + m.total_transferencias;
                if (document.getElementById('m-v-tarjeta')) document.getElementById('m-v-tarjeta').textContent = '$' + m.total_tarjetas;
                if (document.getElementById('m-v-flete')) document.getElementById('m-v-flete').textContent = '$' + m.total_flete;
                if (document.getElementById('m-v-mano-obra')) document.getElementById('m-v-mano-obra').textContent = '$' + m.total_mano_obra;
                if (document.getElementById('m-total-esperado')) document.getElementById('m-total-esperado').textContent = m.efectivo_esperado;
            }
            
            const elTotalVentasCaja = document.getElementById('caja-total-ventas');
            if (elTotalVentasCaja && data.total) {
                let ventasActuales = parseFloat(elTotalVentasCaja.textContent.replace(/[^0-9.-]+/g,"")) || 0;
                let nuevaVentaTotal = parseFloat(data.total) || 0;
                let nuevoAcumuladoVentas = ventasActuales + nuevaVentaTotal;
                elTotalVentasCaja.textContent = nuevoAcumuladoVentas.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const iframe = document.getElementById('iframeTicket');
            if (iframe) iframe.src = `/puntoventa/ticket/${data.venta_id}`; 
            
            const elModal = document.getElementById('modalTicket');
            if (elModal) {
                let modalTicket = bootstrap.Modal.getInstance(elModal);
                if (!modalTicket) {
                    modalTicket = new bootstrap.Modal(elModal);
                }
                modalTicket.show();
            }

            carrito = [];
            renderizarCarrito();
            if (selectMetodoPago) selectMetodoPago.value = "";
            const inputRecibido = document.getElementById('montoRecibido');
            if (inputRecibido) inputRecibido.value = "";

            if (document.getElementById('mixtoMonto1')) document.getElementById('mixtoMonto1').value = "";
            if (document.getElementById('mixtoMonto2')) document.getElementById('mixtoMonto2').value = "";

            if (document.getElementById('seccionCambio')) document.getElementById('seccionCambio').style.display = "none";
            if (document.getElementById('seccionPagoMixto')) document.getElementById('seccionPagoMixto').style.display = "none";

            if (document.getElementById('cambioCliente')) document.getElementById('cambioCliente').textContent = "$0.00";
        } else {
            alert(data.message || 'Error al procesar la venta');
        }
    })
    .catch(error => {
        alert('Fallo en la operación: ' + error.message);
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-shield-check me-2"></i> Registrar y Emitir Ticket';
    });
}

function imprimirTicketModal() {
    const iframe = document.getElementById('iframeTicket');
    if (iframe) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

const inputBusqueda = document.getElementById('buscarProducto');
const selectCategoria = document.getElementById('filtroCategoria');

window.onload = function() {
    if(inputBusqueda) inputBusqueda.focus();
};

if(inputBusqueda) {
    inputBusqueda.addEventListener('keyup', function(e) {
        filtrarProductos();
        if (e.key === 'Enter') {
            e.preventDefault();
            const busqueda = this.value.toLowerCase().trim();
            const productoExacto = productos.find(p => p.codigo && p.codigo.toLowerCase() === busqueda);
            if (productoExacto) {
                agregarProducto(productoExacto.id);
                this.value = ''; 
                filtrarProductos(); 
            }
        }
    });
}

if(selectCategoria) {
    selectCategoria.addEventListener('change', filtrarProductos);
}

function filtrarProductos() {
    if(!inputBusqueda) return;
    const busqueda = inputBusqueda.value.toLowerCase();
    const categoria = selectCategoria ? selectCategoria.value : '';
    const cards = document.querySelectorAll('.product-card');

    cards.forEach((card, index) => {
        const producto = productos[index];
        if (!producto) return;

        let mostrar = true;
        if (busqueda) {
            const texto = (producto.nombre + ' ' + (producto.codigo || '')).toLowerCase();
            if (!texto.includes(busqueda)) mostrar = false;
        }
        if (categoria && producto.categoria?.nombre !== categoria) {
            mostrar = false;
        }
        card.closest('.col-xl-3, .col-lg-4, .col-md-6').style.display = mostrar ? '' : 'none';
    });
}

function limpiarYEnfocarPOS() {
    // 1. Limpiar carrito de compras
    carrito = [];
    renderizarCarrito();

    // 2. Resetear selección de cliente a Público General
    const selectCliente = document.getElementById('clienteVenta');
    if (selectCliente) {
        selectCliente.value = '';
    }

    // 3. Resetear switch de factura y campo RFC
    const checkFactura = document.getElementById('requiereFactura');
    const campoRFC = document.getElementById('campoRFC');
    const inputRFC = document.getElementById('rfcCliente');

    if (checkFactura) checkFactura.checked = false;
    if (inputRFC) inputRFC.value = '';
    if (campoRFC) campoRFC.style.display = 'none';

    // 4. Resetear método de pago y secciones dinámicas
    const selectMetodo = document.getElementById('metodoPago');
    const inputRecibido = document.getElementById('montoRecibido');
    const seccionCambio = document.getElementById('seccionCambio');
    const seccionMixto = document.getElementById('seccionPagoMixto');

    if (selectMetodo) selectMetodo.value = '';
    if (inputRecibido) inputRecibido.value = '';
    if (seccionCambio) seccionCambio.style.display = 'none';
    if (seccionMixto) seccionMixto.style.display = 'none';

    if (document.getElementById('mixtoMonto1')) document.getElementById('mixtoMonto1').value = '';
    if (document.getElementById('mixtoMonto2')) document.getElementById('mixtoMonto2').value = '';
    if (document.getElementById('cambioCliente')) document.getElementById('cambioCliente').textContent = '$0.00';

    // 5. Limpiar buscador de productos y devolver el foco para la siguiente venta
    const inputBusqueda = document.getElementById('buscarProducto');
    if (inputBusqueda) {
        inputBusqueda.value = '';
        filtrarProductos(); // Restablece el catálogo visual de productos
        inputBusqueda.focus();
    }

    // Garantiza la limpieza al cerrar el modal por cualquier vía de Bootstrap
    const modalTicketEl = document.getElementById('modalTicket');
    if (modalTicketEl) {
        modalTicketEl.addEventListener('hidden.bs.modal', function () {
            limpiarYEnfocarPOS();
        });
    }
}
</script>
@endsection