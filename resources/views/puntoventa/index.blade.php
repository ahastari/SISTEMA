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
                                        
                                        @if($producto->codigo_barras)
                                            <span class="text-secondary font-monospace d-flex align-items-center" style="font-size: 10px;" title="Código de barras">
                                                <i class="bi bi-upc-scan me-1"></i> {{ $producto->codigo_barras }}
                                            </span>
                                        @else
                                            <i class="bi bi-box-seam text-secondary small" title="Sin código de barras"></i>
                                        @endif
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
                <div class="d-flex align-items-center gap-2">
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-warning fw-bold" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" title="Carritos en espera">
                            <i class="bi bi-pause-circle me-1"></i>En espera <span id="contadorEspera" class="badge bg-warning text-dark ms-1">0</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end p-2 shadow" id="listaEspera" style="min-width: 330px; max-height: 60vh; overflow-y: auto;"></div>
                    </div>
                    <span class="badge bg-secondary rounded-pill px-2" id="contadorItems">0 items</span>
                </div>
            </div>

            <!-- Botones de Cargos Especiales (Flete / Mano de Obra) -->
            <div class="p-2 bg-body border-bottom d-flex gap-2">
                <button class="btn btn-sm btn-outline-primary flex-fill fw-bold" onclick="agregarServicioEspecial('flete')">
                    <i class="bi bi-truck me-1"></i> + Flete
                </button>
                <button class="btn btn-sm btn-outline-warning flex-fill fw-bold" onclick="agregarServicioEspecial('mano_obra')">
                    <i class="bi bi-tools me-1"></i> + Mano de Obra
                </button>
                <button class="btn btn-sm btn-warning fw-bold" onclick="ponerEnEspera()" title="Deja este carrito en espera y atiende a otro cliente">
                    <i class="bi bi-pause-fill me-1"></i> Espera
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
                
                <!-- Campo Descuento en Monto Directo ($) -->
                <div class="mb-2">
                    <label class="form-label text-body fw-bold mb-1" style="font-size: 11px;">Descuento ($ MXN)
                        <span id="badgeDescuentoEstado" class="badge ms-1" style="display: none; font-size: 9px;"></span>
                    </label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body text-secondary fw-bold">$</span>
                        <input type="number" id="descuentoMonto" class="form-control bg-body text-body fw-bold" step="0.01" min="0" value="0.00" oninput="alCambiarDescuento()">
                    </div>
                </div>

                <div class="mb-1" id="campoRFC" style="display: none;">
                    <input type="text" id="rfcCliente" class="form-control form-control-sm bg-body text-body" placeholder="RFC del contribuyente">
                </div>

                <!-- desglose Totales -->
                <div class="bg-body p-3 rounded-3 shadow-sm border mb-2" style="border-color: var(--bs-border-color) !important;">
                    <div class="d-flex justify-content-between text-secondary small mb-1">
                        <span>Subtotal Bruto:</span>
                        <span id="subtotalCarrito" class="text-body fw-bold">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-danger small mb-1" id="filaDescuento" style="display: none;">
                        <span>Descuento Aplicado:</span>
                        <span id="descuentoCarrito" class="fw-bold">-$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-secondary small mb-2 d-none" id="filaIva">
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
                        <option value="credito">A Crédito</option>
                        <option value="mixto">Pago Mixto</option>
                    </select>
                </div>
                
                <!-- Campo Días de Crédito (Oculto dinámicamente) -->
                <div class="mb-2" id="seccionCredito" style="display: none;">
                    <label class="form-label text-body fw-bold mb-1" style="font-size: 11px;">Días de Crédito Otorgados <span id="badgeCreditoEstado" class="badge ms-1" style="display: none; font-size: 9px;"></span></label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-body text-secondary fw-bold"><i class="bi bi-calendar-range"></i></span>
                        <input type="number" id="diasCredito" class="form-control bg-body text-body fw-bold" min="1" value="15" placeholder="Ej: 15, 30 días">
                        <span class="input-group-text bg-body text-secondary">días</span>
                    </div>
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

                            // Abonos de crédito cobrados en este turno (cobros de ventas a crédito anteriores)
                            $abonosModal = $corteActivo->movimientos
                                ->where('tipo', 'ingreso')
                                ->filter(fn ($m) => \Illuminate\Support\Str::startsWith($m->concepto ?? '', 'Abono crédito'));
                            $abonosEfeModal   = $abonosModal->where('metodo', 'efectivo')->sum('monto');
                            $abonosTransModal = $abonosModal->where('metodo', 'transferencia')->sum('monto');
                            $abonosTarjModal  = $abonosModal->where('metodo', 'tarjeta')->sum('monto');
                            $ingresosEfeModal = $corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');

                            // Abonos de renta cobrados en este turno (pagos, ampliaciones y liquidaciones)
                            $rentasModal = $corteActivo->movimientos
                                ->where('tipo', 'ingreso')
                                ->filter(fn ($m) => \Illuminate\Support\Str::startsWith($m->concepto ?? '', 'Abono renta'));
                            $rentasEfeModal   = $rentasModal->where('metodo', 'efectivo')->sum('monto');
                            $rentasTransModal = $rentasModal->where('metodo', 'transferencia')->sum('monto');
                            $rentasTarjModal  = $rentasModal->where('metodo', 'tarjeta')->sum('monto');
                            $otrosIngresosEfeModal = max(0, $ingresosEfeModal - $abonosEfeModal - $rentasEfeModal);
                            $ventasCreditoModal = 0;

                            if($corteActivo->ventas) {
                                foreach($corteActivo->ventas as $v) {
                                    if ($v->estado !== 'completada') continue;

                                    if (($v->metodo_pago ?? '') === 'credito') {
                                        $ventasCreditoModal += $v->total;
                                    }
                                    
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

                            @if($ventasCreditoModal > 0)
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div class="text-warning-emphasis"><i class="bi bi-credit-card-2-front text-warning me-2"></i> A Crédito <small class="text-secondary">(por cobrar, no entra a caja)</small></div>
                                <span class="fw-bold text-warning-emphasis">${{ number_format($ventasCreditoModal, 2) }}</span>
                            </div>
                            @endif

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

                        <h6 class="text-secondary small fw-bold mb-2 text-uppercase tracking-wider" style="font-size: 11px;">Abonos de Crédito Cobrados</h6>
                        <div class="list-group shadow-sm mb-1 rounded-3" style="border: 1px solid var(--bs-border-color);">
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0">
                                <div><i class="bi bi-cash text-success me-2"></i> Efectivo <small class="text-secondary">(suma al efectivo esperado)</small></div>
                                <span class="fw-bold" id="m-abonos-efectivo">${{ number_format($abonosEfeModal, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-arrow-right-short text-info me-2"></i> Transferencias</div>
                                <span class="fw-bold" id="m-abonos-transferencia">${{ number_format($abonosTransModal, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-credit-card text-primary me-2"></i> Tarjetas</div>
                                <span class="fw-bold" id="m-abonos-tarjeta">${{ number_format($abonosTarjModal, 2) }}</span>
                            </div>
                        </div>
                        <div class="text-secondary mb-3" style="font-size: 10px;"><i class="bi bi-info-circle me-1"></i>Son cobros de ventas a crédito anteriores; no son ventas nuevas de este turno.</div>

                        <h6 class="text-secondary small fw-bold mb-2 text-uppercase tracking-wider" style="font-size: 11px;">Abonos de Renta Cobrados</h6>
                        <div class="list-group shadow-sm mb-1 rounded-3" style="border: 1px solid var(--bs-border-color);">
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0">
                                <div><i class="bi bi-cash text-success me-2"></i> Efectivo <small class="text-secondary">(suma al efectivo esperado)</small></div>
                                <span class="fw-bold" id="m-rentas-efectivo">${{ number_format($rentasEfeModal, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-arrow-right-short text-info me-2"></i> Transferencias</div>
                                <span class="fw-bold" id="m-rentas-transferencia">${{ number_format($rentasTransModal, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center bg-body text-body border-0 border-top" style="border-color: var(--bs-border-color) !important;">
                                <div><i class="bi bi-credit-card text-primary me-2"></i> Tarjetas</div>
                                <span class="fw-bold" id="m-rentas-tarjeta">${{ number_format($rentasTarjModal, 2) }}</span>
                            </div>
                        </div>
                        <div class="text-secondary mb-3" style="font-size: 10px;"><i class="bi bi-info-circle me-1"></i>Son pagos de rentas (abonos, ampliaciones y liquidaciones); no son ventas nuevas de este turno.</div>

                        <h6 class="text-secondary small fw-bold mb-2 text-uppercase tracking-wider" style="font-size: 11px;">Movimientos de Efectivo</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-2 border rounded shadow-sm text-center bg-body" style="border-color: var(--bs-border-color) !important;">
                                    <small class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 10px;">Otros Ingresos (+)</small>
                                    <span class="fw-bold text-success" id="m-mov-ingresos" style="font-size: 13px;">${{ number_format($otrosIngresosEfeModal, 2) }}</span>
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
                            <small class="text-white-50 d-block mt-1" style="font-size: 10px;">Fondo + ventas en efectivo{{ $abonosEfeModal > 0 ? ' + abonos de crédito ($' . number_format($abonosEfeModal, 2) . ')' : '' }}{{ $rentasEfeModal > 0 ? ' + pagos de renta ($' . number_format($rentasEfeModal, 2) . ')' : '' }} + otros ingresos − egresos</small>
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

<!-- MODAL: SOLICITUD DE AUTORIZACIÓN DE DESCUENTO -->
<div class="modal fade" id="modalAutorizarDescuento" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg" style="background: var(--bs-body-bg);">
            <div class="modal-header bg-warning-subtle py-2">
                <h6 class="modal-title fw-bold text-body"><i class="bi bi-percent me-2" id="iconoModalAutorizacion"></i><span id="tituloModalAutorizacion">Autorización de Descuento</span></h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <!-- Paso 1: enviar solicitud -->
            <div id="authFormSection">
                <div class="modal-body">
                    <p class="small text-secondary mb-3">
                        <span id="txtTipoAuth">El descuento</span> de <strong id="montoDescuentoAutorizar" class="text-body">$0.00</strong> debe ser autorizado por un gerente o administrador.<br>
                        Cliente: <strong id="clienteDescuentoAutorizar" class="text-body">Público General</strong><br>
                        <span id="lineaPlazoAuth" style="display: none;">Plazo solicitado: <strong id="diasCreditoAutorizar" class="text-body">0</strong> días<br></span>
                        <span id="infoLimiteAuth" class="text-warning-emphasis fw-semibold"></span>
                    </p>
                    <label id="lblMotivoAuth" class="form-label small fw-bold text-body mb-1">Motivo del descuento</label>
                    <textarea id="motivoDescuento" class="form-control form-control-sm bg-body text-body" rows="3" maxlength="255"
                              placeholder="Ej. Cliente frecuente, compra por volumen..."></textarea>
                    <div id="authError" class="text-danger small fw-semibold mt-2" style="display: none;"></div>
                </div>
                <div class="modal-footer py-2 bg-body d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold px-3" id="btnEnviarSolicitud" onclick="enviarSolicitudDescuento()">
                        <i class="bi bi-send-fill me-1"></i>Enviar solicitud
                    </button>
                </div>
            </div>

            <!-- Paso 2: esperando respuesta del gerente -->
            <div id="authEsperaSection" style="display: none;">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-warning mb-3" role="status"></div>
                    <p class="fw-bold text-body mb-1">Solicitud enviada al gerente</p>
                    <p class="small text-secondary mb-0">Esperando autorización del <span id="txtTipoEspera">descuento</span> de <strong id="montoDescuentoEspera" class="text-body">$0.00</strong>.<br>Te avisaremos cuando el gerente responda; mientras tanto puedes atender a otro cliente.</p>
                </div>
                <div class="modal-footer py-2 bg-body">
                    <div class="d-flex gap-2 w-100">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-grow-1" data-bs-dismiss="modal">Seguir aquí</button>
                        <button type="button" class="btn btn-sm btn-warning fw-bold flex-grow-1" onclick="ponerEnEsperaDesdeModal()">
                            <i class="bi bi-pause-fill me-1"></i>Atender otro cliente
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AVISOS (toasts) DE DESCUENTOS: no bloquean el POS -->
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastDescuentos" style="z-index: 2000;"></div>

<script>
let carrito = [];
let productos = @json($productos);
// Admin/Gerente no necesitan autorización; los cajeros solicitan al gerente
const PUEDE_AUTORIZAR_DESCUENTO = @json($puedeAutorizarDescuento ?? false);
let solicitudDescuento = null; // { id, monto, estado: 'pendiente' | 'aprobada' }
let pollDescuento = null;
let solicitudReemplazable = null; // id de la solicitud anterior de ESTE carrito (si cambió el monto o el cliente)
let carritosEspera = [];          // carritos guardados mientras se atiende a otro cliente
// Límite de crédito sin autorización (null = sin límite). Admin/Gerente no lo necesitan.
const LIMITE_CREDITO = @json($limiteCredito ?? null);
let solicitudCredito = null;      // { id, monto, estado, tipo:'credito', cliente_id, cliente_nombre }
let tipoModalAuth = 'descuento';  // qué tipo de solicitud está mostrando el modal
let montoModalAuth = 0;

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
        if (document.getElementById('subtotalCarrito')) document.getElementById('subtotalCarrito').textContent = '$0.00';
        if (document.getElementById('descuentoCarrito')) document.getElementById('descuentoCarrito').textContent = '-$0.00';
        if (document.getElementById('ivaCarrito')) document.getElementById('ivaCarrito').textContent = '$0.00';
        if (document.getElementById('totalCarrito')) document.getElementById('totalCarrito').textContent = '$0.00';
        if (typeof calcularCambio === 'function') calcularCambio();
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
        subtotal += parseFloat(item.precio) * parseInt(item.cantidad);
    });

    contador.textContent = carrito.reduce((sum, i) => sum + i.cantidad, 0) + ' items';

    // 1. Obtener el valor del descuento
    const inputDescuento = document.getElementById('descuentoMonto');
    const descuento = inputDescuento ? (parseFloat(inputDescuento.value) || 0) : 0;

    // 2. Aplicar descuento al subtotal bruto
    const subtotalConDescuento = Math.max(0, subtotal - descuento);

    // 3. Calcular IVA sobre la base con descuento
    const requiereFactura = document.getElementById('requiereFactura')?.checked || false;
    const iva = requiereFactura ? (subtotalConDescuento * 0.16) : 0;
    
    // 4. Calcular el Gran Total final
    const total = subtotalConDescuento + iva;

    // 5. Renderizar en la interfaz
    if (document.getElementById('subtotalCarrito')) {
        document.getElementById('subtotalCarrito').textContent = '$' + subtotal.toFixed(2);
    }
    if (document.getElementById('descuentoCarrito')) {
        document.getElementById('descuentoCarrito').textContent = '-$' + descuento.toFixed(2);
    }
    if (document.getElementById('ivaCarrito')) {
        document.getElementById('ivaCarrito').textContent = '$' + iva.toFixed(2);
    }
    // La fila de IVA solo se muestra cuando se requiere factura
    const filaIva = document.getElementById('filaIva');
    if (filaIva) filaIva.classList.toggle('d-none', !requiereFactura);
    if (document.getElementById('totalCarrito')) {
        document.getElementById('totalCarrito').textContent = '$' + total.toFixed(2);
    }

    if (typeof calcularCambio === 'function') {
        calcularCambio();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const inputDescuento = document.getElementById('descuentoMonto');
        if (inputDescuento) {
            inputDescuento.addEventListener('input', function () {
                renderizarCarrito(false);
            });
        }
    });

}

const reqFacturaCheck = document.getElementById('requiereFactura');
if (reqFacturaCheck) {
    reqFacturaCheck.addEventListener('change', function() {
        document.getElementById('campoRFC').style.display = this.checked ? 'block' : 'none';
        if (this.checked && !document.getElementById('rfcCliente')?.value) autocompletarRFC();
        renderizarCarrito(false);
    });
}

// Autocompletar el RFC con el del cliente seleccionado (#clienteVenta)
function autocompletarRFC() {
    const sel = document.getElementById('clienteVenta');
    const inp = document.getElementById('rfcCliente');
    if (!sel || !inp) return;
    const opt = sel.options[sel.selectedIndex];
    inp.value = (sel.value && opt) ? (opt.dataset.rfc || '') : '';
}

document.getElementById('clienteVenta')?.addEventListener('change', autocompletarRFC);

const selectMetodoPago = document.getElementById('metodoPago');
if (selectMetodoPago) {
    selectMetodoPago.addEventListener('change', function() {
        const seccionCambio = document.getElementById('seccionCambio');
        const seccionMixto = document.getElementById('seccionPagoMixto');
        const seccionCredito = document.getElementById('seccionCredito');
        const inputRecibido = document.getElementById('montoRecibido');
        
        // Ocultar todas las secciones dinámicas por defecto
        if (seccionCambio) seccionCambio.style.display = 'none';
        if (seccionMixto) seccionMixto.style.display = 'none';
        if (seccionCredito) seccionCredito.style.display = 'none';

        if (this.value === 'efectivo') {
            if (seccionCambio) seccionCambio.style.display = 'block';
            if (inputRecibido) inputRecibido.focus();
        } else if (this.value === 'mixto') {
            if (seccionMixto) seccionMixto.style.display = 'block';
        } else if (this.value === 'credito') {
            if (seccionCredito) seccionCredito.style.display = 'block';
        }
    });
}

// ===== SOLICITUD DE AUTORIZACIÓN DE DESCUENTOS (mismo esquema que la cancelación) =====
function descuentoAutorizado(descuento) {
    if (descuento <= 0 || PUEDE_AUTORIZAR_DESCUENTO) return true;
    return !!solicitudDescuento
        && solicitudDescuento.estado === 'aprobada'
        && Math.abs(solicitudDescuento.monto - descuento) < 0.01;
}

function actualizarBadgeDescuento() {
    const badge = document.getElementById('badgeDescuentoEstado');
    if (!badge) return;
    const descuento = parseFloat(document.getElementById('descuentoMonto')?.value) || 0;

    if (PUEDE_AUTORIZAR_DESCUENTO || descuento <= 0 || !solicitudDescuento
        || Math.abs(solicitudDescuento.monto - descuento) >= 0.01) {
        badge.style.display = 'none';
        return;
    }

    if (solicitudDescuento.estado === 'aprobada') {
        badge.className = 'badge ms-1 bg-success-subtle text-success border border-success-subtle';
        badge.innerHTML = '<i class="bi bi-shield-check me-1"></i>Autorizado';
    } else {
        badge.className = 'badge ms-1 bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Pendiente de autorización';
    }
    badge.style.display = 'inline-block';
}

function detenerPollingDescuento() {
    if (pollDescuento) { clearInterval(pollDescuento); pollDescuento = null; }
}

function reiniciarSolicitudDescuento() {
    // El polling es global (también vigila los carritos en espera), por eso ya no se detiene aquí
    solicitudDescuento = null;
    actualizarBadgeDescuento();
}

function alCambiarDescuento() {
    // Si el monto cambia respecto al solicitado, la solicitud deja de aplicar
    const descuento = parseFloat(document.getElementById('descuentoMonto')?.value) || 0;
    if (solicitudDescuento && Math.abs(solicitudDescuento.monto - descuento) >= 0.01) {
        solicitudReemplazable = solicitudDescuento.id;
        reiniciarSolicitudDescuento();
    }
    actualizarBadgeDescuento();
    renderizarCarrito(false);
}

function mostrarPasoModalDescuento(paso) {
    document.getElementById('authFormSection').style.display = paso === 'form' ? 'block' : 'none';
    document.getElementById('authEsperaSection').style.display = paso === 'espera' ? 'block' : 'none';
}

function abrirModalDescuento() {
    const modalEl = document.getElementById('modalAutorizarDescuento');
    (bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl)).show();
}

function totalActualCarrito() {
    return parseFloat((document.getElementById('totalCarrito')?.textContent || '0').replace('$', '').replace(/,/g, '')) || 0;
}

function creditoRequiereAutorizacion(total) {
    return !PUEDE_AUTORIZAR_DESCUENTO && LIMITE_CREDITO !== null && total > LIMITE_CREDITO + 0.0001;
}

function diasCreditoActual() {
    return parseInt(document.getElementById('diasCredito')?.value) || 0;
}

function creditoCoincide(total) {
    const cli = String(document.getElementById('clienteVenta')?.value || '');
    return !!solicitudCredito
        && Math.abs(solicitudCredito.monto - total) < 0.01
        && String(solicitudCredito.cliente_id || '') === cli
        && Number(solicitudCredito.dias || 0) === diasCreditoActual();
}

function creditoAutorizado(total) {
    if (!creditoRequiereAutorizacion(total)) return true;
    return creditoCoincide(total) && solicitudCredito.estado === 'aprobada';
}

function actualizarBadgeCredito() {
    const badge = document.getElementById('badgeCreditoEstado');
    if (!badge) return;
    const metodo = document.getElementById('metodoPago')?.value;
    const total = totalActualCarrito();

    if (metodo !== 'credito' || !creditoRequiereAutorizacion(total)) { badge.style.display = 'none'; return; }

    if (creditoCoincide(total) && solicitudCredito.estado === 'aprobada') {
        badge.className = 'badge ms-1 bg-success-subtle text-success border border-success-subtle';
        badge.innerHTML = '<i class="bi bi-shield-check me-1"></i>Autorizado';
    } else if (creditoCoincide(total)) {
        badge.className = 'badge ms-1 bg-warning-subtle text-warning-emphasis border border-warning-subtle';
        badge.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Pendiente de autorización';
    } else {
        badge.className = 'badge ms-1 bg-danger-subtle text-danger border border-danger-subtle';
        badge.innerHTML = '<i class="bi bi-exclamation-triangle me-1"></i>Requiere autorización (límite $' + LIMITE_CREDITO.toFixed(2) + ')';
    }
    badge.style.display = 'inline-block';
}

function configurarModalAuth(tipo, monto) {
    tipoModalAuth = tipo;
    montoModalAuth = monto;
    const esCredito = tipo === 'credito';
    document.getElementById('tituloModalAutorizacion').textContent = esCredito ? 'Autorización de Crédito' : 'Autorización de Descuento';
    document.getElementById('iconoModalAutorizacion').className = 'bi me-2 ' + (esCredito ? 'bi-credit-card-2-front' : 'bi-percent');
    document.getElementById('txtTipoAuth').textContent = esCredito ? 'El crédito' : 'El descuento';
    document.getElementById('txtTipoEspera').textContent = esCredito ? 'crédito' : 'descuento';
    document.getElementById('lblMotivoAuth').textContent = esCredito ? 'Motivo / observaciones del crédito' : 'Motivo del descuento';
    document.getElementById('motivoDescuento').placeholder = esCredito
        ? 'Ej. Cliente con historial de pagos, obra en curso...'
        : 'Ej. Cliente frecuente, compra por volumen...';
    document.getElementById('montoDescuentoAutorizar').textContent = '$' + monto.toFixed(2);
    document.getElementById('montoDescuentoEspera').textContent = '$' + monto.toFixed(2);
    document.getElementById('clienteDescuentoAutorizar').textContent = nombreClienteActual();
    const lineaPlazo = document.getElementById('lineaPlazoAuth');
    if (lineaPlazo) {
        lineaPlazo.style.display = esCredito ? 'inline' : 'none';
        document.getElementById('diasCreditoAutorizar').textContent = diasCreditoActual();
    }
    document.getElementById('infoLimiteAuth').textContent = (esCredito && LIMITE_CREDITO !== null)
        ? 'Excede el límite de crédito sin autorización ($' + LIMITE_CREDITO.toFixed(2) + ').' : '';
}

function solicitarAutorizacionCredito(total) {
    configurarModalAuth('credito', total);

    // Ya hay una solicitud pendiente por este monto y cliente: solo mostrar la espera
    if (creditoCoincide(total) && solicitudCredito.estado === 'pendiente') {
        mostrarPasoModalDescuento('espera');
        abrirModalDescuento();
        return;
    }

    document.getElementById('motivoDescuento').value = '';
    const err = document.getElementById('authError');
    err.style.display = 'none';
    err.textContent = '';
    mostrarPasoModalDescuento('form');
    abrirModalDescuento();
    setTimeout(() => document.getElementById('motivoDescuento').focus(), 300);
}

function solicitarAutorizacionDescuento(descuento, subtotal) {
    configurarModalAuth('descuento', descuento);
    // Ya hay una solicitud pendiente por este monto: solo mostrar el estado de espera
    if (solicitudDescuento && solicitudDescuento.estado === 'pendiente'
        && Math.abs(solicitudDescuento.monto - descuento) < 0.01) {
        document.getElementById('montoDescuentoEspera').textContent = '$' + descuento.toFixed(2);
        mostrarPasoModalDescuento('espera');
        abrirModalDescuento();
        return;
    }

    document.getElementById('montoDescuentoAutorizar').textContent = '$' + descuento.toFixed(2);
    document.getElementById('clienteDescuentoAutorizar').textContent = nombreClienteActual();
    document.getElementById('motivoDescuento').value = '';
    const err = document.getElementById('authError');
    err.style.display = 'none';
    err.textContent = '';
    mostrarPasoModalDescuento('form');
    abrirModalDescuento();
    setTimeout(() => document.getElementById('motivoDescuento').focus(), 300);
}

function enviarSolicitudDescuento() {
    const esCredito = tipoModalAuth === 'credito';
    if (esCredito && diasCreditoActual() <= 0) {
        const e = document.getElementById('authError');
        e.textContent = 'Indica los días de crédito antes de enviar la solicitud.';
        e.style.display = 'block';
        return;
    }
    // Para crédito, "descuento"/"subtotal" llevan el total a crédito (mismo canal de solicitud)
    const descuento = esCredito ? montoModalAuth : (parseFloat(document.getElementById('descuentoMonto')?.value) || 0);
    const subtotal = esCredito ? montoModalAuth : carrito.reduce((sum, i) => sum + parseFloat(i.precio) * parseInt(i.cantidad), 0);
    const motivo = document.getElementById('motivoDescuento').value.trim();
    const err = document.getElementById('authError');
    const btn = document.getElementById('btnEnviarSolicitud');

    if (!motivo) {
        err.textContent = 'Debes ingresar el motivo del descuento.';
        err.style.display = 'block';
        return;
    }

    btn.disabled = true;

    fetch('{{ route("puntoventa.descuento.solicitar") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            monto: descuento,
            subtotal: subtotal,
            motivo: motivo,
            cliente_id: document.getElementById('clienteVenta')?.value || null,
            tipo: tipoModalAuth,
            dias_credito: esCredito ? diasCreditoActual() : null,
            reemplaza_id: esCredito ? (solicitudCredito ? solicitudCredito.id : null) : solicitudReemplazable
        })
    })
    .then(async response => {
        const data = await response.json().catch(() => ({}));
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'No se pudo enviar la solicitud.');
        }
        return data;
    })
    .then(data => {
        const nueva = {
            id: data.id,
            monto: descuento,
            estado: 'pendiente',
            tipo: tipoModalAuth,
            dias: esCredito ? diasCreditoActual() : null,
            cliente_id: document.getElementById('clienteVenta')?.value || '',
            cliente_nombre: nombreClienteActual()
        };
        if (tipoModalAuth === 'credito') {
            solicitudCredito = nueva;
            actualizarBadgeCredito();
        } else {
            solicitudReemplazable = null;
            solicitudDescuento = nueva;
            actualizarBadgeDescuento();
        }
        document.getElementById('montoDescuentoEspera').textContent = '$' + descuento.toFixed(2);
        mostrarPasoModalDescuento('espera');
        asegurarPolling();
    })
    .catch(error => {
        err.textContent = error.message;
        err.style.display = 'block';
    })
    .finally(() => { btn.disabled = false; });
}

// ===== CARRITOS EN ESPERA + AVISOS DE DESCUENTO (no bloquean el POS) =====
const CLAVE_ESPERA = 'pos_espera_{{ auth()->id() }}';

function escapeHtml(t) {
    return String(t ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

function nombreClienteActual() {
    const sel = document.getElementById('clienteVenta');
    if (!sel || !sel.value) return 'Público General';
    return sel.options[sel.selectedIndex].text.trim();
}

function persistirEspera() {
    try { localStorage.setItem(CLAVE_ESPERA, JSON.stringify(carritosEspera)); } catch (e) {}
}

function cargarEsperaGuardada() {
    try {
        const raw = localStorage.getItem(CLAVE_ESPERA);
        if (raw) {
            const limite = Date.now() - 12 * 3600 * 1000; // descarta carritos de más de 12 h
            carritosEspera = JSON.parse(raw).filter(c => c.creado > limite);
        }
    } catch (e) { carritosEspera = []; }
    renderizarEspera();
    if (solicitudesPendientesRastreadas().length) asegurarPolling();
}

function guardarActualEnEspera() {
    const sel = document.getElementById('clienteVenta');
    carritosEspera.push({
        uid: 'c' + Date.now() + Math.random().toString(36).slice(2, 6),
        creado: Date.now(),
        carrito: JSON.parse(JSON.stringify(carrito)),
        cliente_id: sel ? sel.value : '',
        cliente_nombre: nombreClienteActual(),
        descuento: parseFloat(document.getElementById('descuentoMonto')?.value) || 0,
        requiere_factura: document.getElementById('requiereFactura')?.checked || false,
        rfc: document.getElementById('rfcCliente')?.value || '',
        pago: {
            metodo: document.getElementById('metodoPago')?.value || '',
            dias: document.getElementById('diasCredito')?.value || '15',
            recibido: document.getElementById('montoRecibido')?.value || '',
            m1: document.getElementById('mixtoMetodo1')?.value || '',
            monto1: document.getElementById('mixtoMonto1')?.value || '',
            m2: document.getElementById('mixtoMetodo2')?.value || '',
            monto2: document.getElementById('mixtoMonto2')?.value || ''
        },
        solicitud: solicitudDescuento,
        solicitudCredito: solicitudCredito
    });
    persistirEspera();
    renderizarEspera();
}

function ponerEnEspera() {
    if (carrito.length === 0) { alert('El carrito está vacío.'); return; }
    guardarActualEnEspera();
    // La solicitud de descuento (si existe) sigue viva dentro del carrito en espera
    solicitudDescuento = null;
    solicitudReemplazable = null;
    const sel = document.getElementById('clienteVenta');
    if (sel) sel.value = '';
    limpiarYEnfocarPOS();
}

function ponerEnEsperaDesdeModal() {
    bootstrap.Modal.getInstance(document.getElementById('modalAutorizarDescuento'))?.hide();
    ponerEnEspera();
}

function cargarCarrito(snap) {
    carrito = snap.carrito;
    // Refrescar stock local por si cambió mientras estaba en espera
    carrito.forEach(i => {
        if (i.esEspecial) return;
        const p = productos.find(p => p.id === i.id);
        if (p) { i.stock = p.stock; if (i.cantidad > p.stock) i.cantidad = Math.max(1, p.stock); }
    });

    const sel = document.getElementById('clienteVenta');
    if (sel) sel.value = snap.cliente_id || '';
    const chk = document.getElementById('requiereFactura');
    if (chk) chk.checked = !!snap.requiere_factura;
    const campoRFC = document.getElementById('campoRFC');
    if (campoRFC) campoRFC.style.display = snap.requiere_factura ? 'block' : 'none';
    const rfc = document.getElementById('rfcCliente');
    if (rfc) rfc.value = snap.rfc || '';
    if (snap.requiere_factura && rfc && !rfc.value) autocompletarRFC();
    const desc = document.getElementById('descuentoMonto');
    if (desc) desc.value = (parseFloat(snap.descuento) || 0).toFixed(2);

    // Forma de pago: se restaura y se dispara 'change' para mostrar su sección (cambio / mixto / crédito)
    const pg = snap.pago || {};
    const setVal = (id, v) => { const el = document.getElementById(id); if (el && v !== undefined && v !== null && v !== '') el.value = v; };
    const selMetodo = document.getElementById('metodoPago');
    if (selMetodo) {
        selMetodo.value = pg.metodo || '';
        selMetodo.dispatchEvent(new Event('change'));
    }
    setVal('diasCredito', pg.dias);
    setVal('montoRecibido', pg.recibido);
    setVal('mixtoMetodo1', pg.m1);
    setVal('mixtoMonto1', pg.monto1);
    setVal('mixtoMetodo2', pg.m2);
    setVal('mixtoMonto2', pg.monto2);

    solicitudDescuento = snap.solicitud || null;
    solicitudCredito = snap.solicitudCredito || null;
    renderizarCarrito();
    actualizarBadgeDescuento();
    actualizarBadgeCredito();
}

function retomarCarrito(uid) {
    const idx = carritosEspera.findIndex(c => c.uid === uid);
    if (idx < 0) return;
    const destino = carritosEspera.splice(idx, 1)[0];

    // Si hay un carrito en pantalla, se intercambia (pasa a espera)
    if (carrito.length > 0) guardarActualEnEspera();

    cargarCarrito(destino);
    persistirEspera();
    renderizarEspera();
}

function descartarCarritoEspera(uid) {
    const c = carritosEspera.find(c => c.uid === uid);
    if (!c) return;
    if (!confirm('¿Descartar el carrito de ' + c.cliente_nombre + '?')) return;
    if (c.solicitud && c.solicitud.estado === 'pendiente') {
        // La solicitud queda pendiente en la bandeja del gerente; se ignora al resolverse
    }
    carritosEspera = carritosEspera.filter(x => x.uid !== uid);
    persistirEspera();
    renderizarEspera();
}

function renderizarEspera() {
    const lista = document.getElementById('listaEspera');
    const cnt = document.getElementById('contadorEspera');
    if (cnt) cnt.textContent = carritosEspera.length;
    if (!lista) return;

    if (carritosEspera.length === 0) {
        lista.innerHTML = '<div class="text-center text-secondary small py-3">No hay carritos en espera.</div>';
        return;
    }

    lista.innerHTML = carritosEspera.map(c => {
        const sub = c.carrito.reduce((s, i) => s + parseFloat(i.precio) * parseInt(i.cantidad), 0);
        const desc = parseFloat(c.descuento) || 0;
        const items = c.carrito.reduce((s, i) => s + parseInt(i.cantidad), 0);
        let badge = '';
        if (desc > 0 && c.solicitud) {
            badge = c.solicitud.estado === 'aprobada'
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i>Descuento autorizado</span>'
                : '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-hourglass-split me-1"></i>Descuento pendiente</span>';
        }
        if (c.solicitudCredito) {
            badge += ' ' + (c.solicitudCredito.estado === 'aprobada'
                ? '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-shield-check me-1"></i>Crédito autorizado</span>'
                : '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"><i class="bi bi-hourglass-split me-1"></i>Crédito pendiente</span>');
        }
        return `
            <div class="border rounded-3 p-2 mb-2 bg-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="text-truncate" style="max-width: 200px;">
                        <div class="fw-bold small text-body text-truncate"><i class="bi bi-person me-1"></i>${escapeHtml(c.cliente_nombre)}</div>
                        <div class="text-secondary" style="font-size: 11px;">${items} items · $${(sub - desc).toFixed(2)}${desc > 0 ? ' (desc. $' + desc.toFixed(2) + ')' : ''}</div>
                        <div class="text-secondary" style="font-size: 10px;">${new Date(c.creado).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</div>
                    </div>
                    <div class="d-flex gap-1">
                        <button class="btn btn-sm btn-primary fw-bold py-0 px-2" onclick="retomarCarrito('${c.uid}')">Retomar</button>
                        <button class="btn btn-sm btn-outline-danger py-0 px-2" onclick="descartarCarritoEspera('${c.uid}')" title="Descartar"><i class="bi bi-trash3"></i></button>
                    </div>
                </div>
                ${badge ? '<div class="mt-1">' + badge + '</div>' : ''}
            </div>`;
    }).join('');
}

// ---- Polling único (carrito actual + carritos en espera) ----
function solicitudesPendientesRastreadas() {
    const lista = [];
    [solicitudDescuento, solicitudCredito].forEach(s => { if (s && s.estado === 'pendiente') lista.push(s); });
    carritosEspera.forEach(c => {
        [c.solicitud, c.solicitudCredito].forEach(s => { if (s && s.estado === 'pendiente') lista.push(s); });
    });
    return lista;
}

function asegurarPolling() {
    if (!pollDescuento) pollDescuento = setInterval(consultarEstadoDescuento, 4000);
}

function consultarEstadoDescuento() {
    const pendientes = solicitudesPendientesRastreadas();
    if (pendientes.length === 0) { detenerPollingDescuento(); return; }

    fetch('{{ route("puntoventa.descuento.estados") }}?ids=' + pendientes.map(s => s.id).join(','), {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.ok ? r.json() : null)
    .then(data => {
        if (!data) return;
        pendientes.forEach(sol => {
            const info = data[sol.id];
            if (!info || info.estado === 'pendiente') return;
            aplicarResolucionDescuento(sol, info);
        });
    })
    .catch(error => console.error('Error al consultar solicitudes de descuento:', error));
}

function aplicarResolucionDescuento(sol, info) {
    const esCredito = sol.tipo === 'credito';
    const enActual = esCredito ? (sol === solicitudCredito) : (sol === solicitudDescuento);
    const enEspera = carritosEspera.find(c => (esCredito ? c.solicitudCredito : c.solicitud) === sol);
    if (!enActual && !enEspera) return; // ya no pertenece a ningún carrito (venta cerrada o descartado)

    const heldUid = enEspera ? enEspera.uid : null;

    if (info.estado === 'aprobada') {
        sol.estado = 'aprobada';
        sol.autorizador = info.autorizador;
    } else if (esCredito) {
        // Crédito rechazado/cancelado: se descarta la solicitud; el cajero debe cambiar la forma de pago
        if (enActual) solicitudCredito = null; else enEspera.solicitudCredito = null;
    } else if (enActual) {
        // Descuento rechazado/cancelado: se quita el descuento del carrito en pantalla
        reiniciarSolicitudDescuento();
        const inputDescuento = document.getElementById('descuentoMonto');
        if (inputDescuento) inputDescuento.value = '0.00';
        renderizarCarrito(false);
    } else {
        // Descuento rechazado/cancelado en un carrito en espera
        enEspera.solicitud = null;
        enEspera.descuento = 0;
    }

    if (enActual) {
        bootstrap.Modal.getInstance(document.getElementById('modalAutorizarDescuento'))?.hide();
        actualizarBadgeDescuento();
        actualizarBadgeCredito();
    }
    persistirEspera();
    renderizarEspera();
    mostrarToastDescuento(sol, info.estado, info.autorizador, heldUid, info.sucursal);
}

function beepAviso() {
    try {
        const ctx = new (window.AudioContext || window.webkitAudioContext)();
        const o = ctx.createOscillator(), g = ctx.createGain();
        o.connect(g); g.connect(ctx.destination);
        o.frequency.value = 880; g.gain.value = 0.08;
        o.start(); o.stop(ctx.currentTime + 0.25);
    } catch (e) {}
}

function mostrarToastDescuento(sol, estado, autorizador, heldUid, sucursal) {
    const cont = document.getElementById('toastDescuentos');
    const aprobada = estado === 'aprobada';
    const rechazada = estado === 'rechazada';
    const cliente = sol.cliente_nombre || 'Público General';
    const monto = (parseFloat(sol.monto) || 0).toFixed(2);

    if (!cont) {
        alert((aprobada ? 'Descuento aprobado' : 'Descuento rechazado') + ' — ' + cliente + ' ($' + monto + ')');
        return;
    }

    const clase = aprobada ? 'text-bg-success' : (rechazada ? 'text-bg-danger' : 'text-bg-secondary');
    const icono = aprobada ? 'bi-check-circle-fill' : (rechazada ? 'bi-x-circle-fill' : 'bi-info-circle-fill');
    const esCredito = sol.tipo === 'credito';
    const nombreTipo = esCredito ? 'Crédito' : 'Descuento';
    const titulo = aprobada ? nombreTipo + ' APROBADO' : (rechazada ? nombreTipo + ' RECHAZADO' : 'Solicitud de ' + nombreTipo.toLowerCase() + ' cancelada');

    const el = document.createElement('div');
    el.className = 'toast border-0 shadow-lg ' + clase;
    el.setAttribute('role', 'alert');
    el.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <div class="fw-bold"><i class="bi ${icono} me-1"></i>${titulo}</div>
                <div class="small">Cliente: <strong>${escapeHtml(cliente)}</strong></div>
                ${esCredito && sol.dias ? '<div class="small">Plazo: <strong>' + sol.dias + ' días</strong></div>' : ''}
                ${sucursal ? '<div class="small">Sucursal: <strong>' + escapeHtml(sucursal) + '</strong></div>' : ''}
                <div class="small">${nombreTipo}: $${monto}${autorizador ? ' · ' + (aprobada ? 'Autorizó: ' : 'Resolvió: ') + escapeHtml(autorizador) : ''}</div>
                ${rechazada ? '<div class="small mt-1">' + (esCredito ? 'El crédito no fue autorizado: cambia la forma de pago o ajusta el monto.' : 'Se quitó el descuento: cobra la venta sin descuento.') + '</div>' : ''}
                ${heldUid ? '<button type="button" class="btn btn-sm btn-light fw-bold mt-2" data-retomar>Retomar carrito</button>' : ''}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>`;
    cont.appendChild(el);

    el.querySelector('[data-retomar]')?.addEventListener('click', () => {
        retomarCarrito(heldUid);
        bootstrap.Toast.getOrCreateInstance(el).hide();
    });
    el.addEventListener('hidden.bs.toast', () => el.remove());

    // No se oculta solo: el cajero debe enterarse de la respuesta
    new bootstrap.Toast(el, { autohide: false }).show();
    beepAviso();
}

// Si cambia el cliente después de pedir la autorización, esa autorización ya no aplica
document.getElementById('clienteVenta')?.addEventListener('change', function () {
    if (solicitudDescuento && String(solicitudDescuento.cliente_id || '') !== String(this.value || '')) {
        solicitudReemplazable = solicitudDescuento.id;
        reiniciarSolicitudDescuento();
        if ((parseFloat(document.getElementById('descuentoMonto')?.value) || 0) > 0) {
            alert('La autorización de descuento era para otro cliente. Solicítala de nuevo para este cliente.');
        }
    }
});

// Si cambian los días después de pedir la autorización, esa autorización ya no aplica
document.getElementById('diasCredito')?.addEventListener('input', function () {
    if (typeof actualizarBadgeCredito === 'function') actualizarBadgeCredito();
});

function calcularCambio() {
    if (typeof actualizarBadgeCredito === 'function') actualizarBadgeCredito();
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
    const selectCliente = document.getElementById('clienteVenta');
    const clienteId = selectCliente ? selectCliente.value : null;

    // Capturar el nombre del cliente desde el option
    let clienteNombre = 'Cliente general';
    if (selectCliente && selectCliente.selectedIndex >= 0 && clienteId) {
        clienteNombre = selectCliente.options[selectCliente.selectedIndex].text.trim();
    }

    // CAPTURAR DESCUENTO Y DÍAS DE CRÉDITO
    const descuento = parseFloat(document.getElementById('descuentoMonto')?.value) || 0;
    const diasCredito = parseInt(document.getElementById('diasCredito')?.value) || 0;

    if (!metodoPago) {
        alert('Selecciona un método de pago');
        return;
    }

    // VALIDACIÓN DE DESCUENTO: no puede exceder el subtotal y requiere autorización
    const subtotalBrutoVenta = carrito.reduce((sum, i) => sum + parseFloat(i.precio) * parseInt(i.cantidad), 0);
    if (descuento > subtotalBrutoVenta) {
        alert('El descuento no puede ser mayor al subtotal de la venta.');
        return;
    }
    if (!descuentoAutorizado(descuento)) {
        solicitarAutorizacionDescuento(descuento, subtotalBrutoVenta);
        return;
    }

    // VALIDACIÓN SI EL PAGO ES A CRÉDITO
    if (metodoPago === 'credito') {
        if (!clienteId) {
            alert('Debes seleccionar un cliente de la lista para otorgar crédito.');
            return;
        }
        if (diasCredito <= 0) {
            alert('Ingresa una cantidad válida de días de crédito.');
            return;
        }
    }

    const totalRaw = document.getElementById('totalCarrito').textContent.replace('$', '').replace(',', '');
    const totalVenta = parseFloat(totalRaw) || 0;

    // CRÉDITO: si el total excede el límite configurado, requiere autorización del gerente
    if (metodoPago === 'credito' && !creditoAutorizado(totalVenta)) {
        solicitarAutorizacionCredito(totalVenta);
        return;
    }

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

    // Estado real del check (usar .checked; el id del input crea una variable global que siempre es "truthy")
    const requiereFactura = document.getElementById('requiereFactura')?.checked || false;
    const rfcInput = document.getElementById('rfcCliente');
    const rfcValue = rfcInput ? rfcInput.value : '';

    const data = {
        items: carrito.map(i => ({ 
            id: i.id, 
            cantidad: i.cantidad,
            precio: i.precio,
            nombre: i.nombre,
            esEspecial: i.esEspecial 
        })),
        metodo_pago: metodoPago,
        descuento: descuento,
        solicitud_descuento_id: (descuento > 0 && solicitudDescuento) ? solicitudDescuento.id : null,
        solicitud_credito_id: (metodoPago === 'credito' && solicitudCredito) ? solicitudCredito.id : null,
        dias_credito: metodoPago === 'credito' ? diasCredito : null,
        pagos_mixtos: detalleMixto,
        monto_recibido: finalMontoRecibido,
        cliente_nombre: clienteId ? clienteNombre : 'Cliente general',
        cliente_id: clienteId || null,
        requiere_factura: requiereFactura ? 1 : 0, 
        rfc_cliente: requiereFactura ? rfcValue : null
    };

    const btn = document.getElementById('btnRealizarVenta');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Procesando...';

    fetch('{{ route("puntoventa.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(async response => {
        const isJson = response.headers.get('content-type')?.includes('application/json');
        const responseData = isJson ? await response.json() : null;

        if (!response.ok || !isJson) {
            const errorMsg = responseData?.message || await response.text();
            console.error('Detalle del error devuelto por Laravel:', errorMsg);
            throw new Error(responseData?.message || `Error en el servidor (${response.status}). Revisa la consola (F12).`);
        }

        return responseData;
    })
    .then(data => {
        if (data.success) {
            // Actualizar stock local
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

            // Actualizar datos del modal de cierre en tiempo real
            if (data.modal_data) {
                const m = data.modal_data;
                if (document.getElementById('m-ventas-total')) document.getElementById('m-ventas-total').textContent = m.total_ventas;
                if (document.getElementById('caja-total-ventas')) document.getElementById('caja-total-ventas').textContent = m.total_ventas;
                if (document.getElementById('m-v-efectivo')) document.getElementById('m-v-efectivo').textContent = '$' + m.total_efectivo;
                if (document.getElementById('m-v-transferencia')) document.getElementById('m-v-transferencia').textContent = '$' + m.total_transferencias;
                if (document.getElementById('m-v-tarjeta')) document.getElementById('m-v-tarjeta').textContent = '$' + m.total_tarjetas;
                if (document.getElementById('m-v-flete')) document.getElementById('m-v-flete').textContent = '$' + m.total_flete;
                if (document.getElementById('m-v-mano-obra')) document.getElementById('m-v-mano-obra').textContent = '$' + m.total_mano_obra;
                if (document.getElementById('m-total-esperado')) document.getElementById('m-total-esperado').textContent = m.efectivo_esperado;
            }

            // Mostrar ticket de venta
            const iframe = document.getElementById('iframeTicket');
            if (iframe) iframe.src = `/puntoventa/ticket/${data.venta_id}`; 
            
            const elModal = document.getElementById('modalTicket');
            if (elModal) {
                let modalTicket = bootstrap.Modal.getInstance(elModal) || new bootstrap.Modal(elModal);
                modalTicket.show();
            }

            limpiarYEnfocarPOS();
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
            
            const productoExacto = productos.find(p => 
                (p.codigo && p.codigo.toLowerCase() === busqueda) || 
                (p.codigo_barras && p.codigo_barras.toLowerCase() === busqueda)
            );
            
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
            const texto = (producto.nombre + ' ' + (producto.codigo || '') + ' ' + (producto.codigo_barras || '')).toLowerCase();
            
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
    if (selectCliente) selectCliente.value = '';
    const clienteId = selectCliente ? selectCliente.value : null;

    let clienteNombre = null;
    if (selectCliente && selectCliente.selectedIndex >= 0) {
        clienteNombre = selectCliente.options[selectCliente.selectedIndex].text;
    }

    // 3. Resetear switch de factura y campo RFC
    const checkFactura = document.getElementById('requiereFactura');
    const campoRFC = document.getElementById('campoRFC');
    const inputRFC = document.getElementById('rfcCliente');

    if (checkFactura) checkFactura.checked = false;
    document.getElementById('filaIva')?.classList.add('d-none');
    if (inputRFC) inputRFC.value = '';
    if (campoRFC) campoRFC.style.display = 'none';

    // 4. Resetear descuento, días de crédito, método de pago y secciones dinámicas
    const inputDescuento = document.getElementById('descuentoMonto');
    const inputDiasCredito = document.getElementById('diasCredito');
    const selectMetodo = document.getElementById('metodoPago');
    const inputRecibido = document.getElementById('montoRecibido');
    const seccionCambio = document.getElementById('seccionCambio');
    const seccionMixto = document.getElementById('seccionPagoMixto');
    const seccionCredito = document.getElementById('seccionCredito');

    if (inputDescuento) inputDescuento.value = '0.00';
    reiniciarSolicitudDescuento();
    solicitudCredito = null;
    actualizarBadgeCredito();
    if (inputDiasCredito) inputDiasCredito.value = '15';
    if (selectMetodo) selectMetodo.value = '';
    if (inputRecibido) inputRecibido.value = '';

    if (seccionCambio) seccionCambio.style.display = 'none';
    if (seccionMixto) seccionMixto.style.display = 'none';
    if (seccionCredito) seccionCredito.style.display = 'none';

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

document.getElementById('metodoPago')?.addEventListener('change', actualizarBadgeCredito);
document.getElementById('clienteVenta')?.addEventListener('change', actualizarBadgeCredito);

// Restaurar carritos en espera y reanudar vigilancia de sus descuentos
cargarEsperaGuardada();

// Si el cajero sale de la pantalla (otro módulo, F5, cerrar pestaña) con un carrito sin cobrar,
// se guarda automáticamente en "En espera" en vez de perderse.
let carritoGuardadoAlSalir = false;
function guardarAlSalir() {
    if (carritoGuardadoAlSalir || carrito.length === 0) return;
    carritoGuardadoAlSalir = true;
    guardarActualEnEspera();
}
window.addEventListener('pagehide', guardarAlSalir);
window.addEventListener('beforeunload', guardarAlSalir);
window.addEventListener('pageshow', () => { carritoGuardadoAlSalir = false; });
</script>
@include('puntoventa._avisos_cancelacion')
@endsection