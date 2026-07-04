@extends('layouts.admin')

@section('content')
<style>
    /* Estructura Global Premium y Corrección de Contenedores */
    body {
        background-color: #f8f9fa;
        overflow-x: hidden;
    }
    .pos-layout {
        display: flex;
        gap: 24px;
        height: calc(100vh - 170px); /* Ajuste de altura balanceado */
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
    
    /* Panel Lateral de Cobro Inteligente Antidesbordamiento */
    .pos-cart-panel {
        width: 400px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
        display: flex;
        flex-direction: column; 
        border: 1px solid #eef0f2;
        height: 100%; 
        overflow: hidden;
    }
    
    /* Zona superior de artículos con un tamaño máximo flexible */
    #carritoItems {
        max-height: 35%; /* Evita que crezca demasiado y robe espacio abajo */
        overflow-y: auto; 
        padding: 12px 16px;
        border-bottom: 1px dashed #eef0f2;
    }
    
    /* SOLUCIÓN AL CORTE: Contenedor de cobro con scroll interno si la pantalla es chica */
    .cart-total {
        background: #f8f9fa;
        border-top: 1px solid #eef0f2;
        padding: 16px 20px;
        flex: 1;
        overflow-y: auto; /* Si los inputs no caben en pantallas chicas, esta zona tendrá scroll propio */
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    /* Tarjetas de Producto Minimalistas */
    .product-card {
        background: white;
        border: 1px solid #eef0f2;
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
        box-shadow: 0 10px 25px rgba(13, 110, 253, 0.08);
        border-color: #0d6efd;
    }
    .product-card .price {
        font-size: 20px;
        font-weight: 700;
        color: #198754;
        margin-top: 8px;
    }
    .product-card .stock {
        font-size: 11px;
        font-weight: 600;
        color: #6c757d;
        background: #f1f3f5;
        padding: 2px 8px;
        border-radius: 20px;
        display: inline-block;
        width: fit-content;
        margin-top: 6px;
    }

    /* Elementos del Carrito */
    .cart-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 10px;
        margin-bottom: 6px;
        background: #f8f9fa;
        border-radius: 10px;
        border: 1px solid #f1f3f5;
    }
    .cart-item-qty-actions {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cart-item-qty-actions span {
        font-weight: 600;
        min-width: 20px;
        text-align: center;
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
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }
    .caja-badge.abierta {
        background: #e8f5e9;
        color: #1b5e20;
        border: 1px solid #c8e6c9;
    }
    .caja-badge.cerrada {
        background: #ffebee;
        color: #c62828;
        border: 1px solid #ffcdd2;
    }

    /* Responsive Ajustes */
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
    
    /* Scrollbars Estilizadas */
    ::-webkit-scrollbar {
        width: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: #ced4da;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #adb5bd;
    }
    /* Contenedor y Renderizado Fijo de Imágenes de Producto */
    .product-image-wrapper {
        width: 100%;
        height: 120px; /* Altura estándar controlada */
        background-color: #f8f9fa;
        border-radius: 10px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #f1f3f5;
    }
    .product-img-render {
        width: 100%;
        height: 100%;
        object-fit: cover; /* Recorta y rellena el espacio de forma uniforme sin estirar */
        transition: transform 0.3s ease;
    }
    .product-card:hover .product-img-render {
        transform: scale(1.05); /* Efecto sutil de zoom al pasar el cursor */
    }
    .product-img-placeholder {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #f9fafb 0%, #f1f3f5 100%);
    }
</style>

<!-- Barra Superior -->
<div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom flex-wrap gap-3">
    <div>
        <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-cpu text-primary me-2"></i>Punto de Venta</h4>
        <!-- <p class="text-muted small mb-0">Andamios y Madera Viramontes</p> -->
    </div>
    
    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if($corteActivo)
            <div class="caja-badge abierta">
                <!-- <span class="spinner-grow spinner-grow-sm text-success" role="status"></span> -->
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
            <button class="btn btn-light btn-sm rounded-circle p-2 shadow-sm border" type="button" id="configDropdown" data-bs-toggle="dropdown">
                <i class="bi bi-three-dots-vertical fs-6"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                <li><a class="dropdown-item py-2" href="{{ route('puntoventa.cortes') }}"><i class="bi bi-cash-stack me-2 text-muted"></i> Historial de Cortes</a></li>
                <li><a class="dropdown-item py-2" href="{{ route('puntoventa.reportes') }}"><i class="bi bi-graph-up-arrow me-2 text-muted"></i> Dashboard e Informes</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item py-2" href="#" data-bs-toggle="modal" data-bs-target="#modalMovimiento"><i class="bi bi-arrow-left-right me-2 text-muted"></i> Entrada / Salida Efectivo</a></li>
            </ul>
        </div>
    </div>
</div>

@if(!$corteActivo)
    <div class="text-center py-5 my-4 bg-white rounded-4 shadow-sm border p-5">
        <div class="display-1 text-muted mb-4"><i class="bi bi-cash-register text-black-50"></i></div>
        <h3 class="fw-bold text-dark">La estación de cobro se encuentra bloqueada</h3>
        <p class="text-muted max-w-md mx-auto mb-4">Para comenzar a pasar artículos, registrar clientes y emitir comprobantes térmicos, es obligatorio iniciar el fondo de caja del turno correspondiente.</p>
        <button class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow" data-bs-toggle="modal" data-bs-target="#modalAbrirCaja">
            <i class="bi bi-unlock-fill me-2"></i> Aperturar Turno Ahora
        </button>
    </div>
@else
    <div class="pos-layout">
        <!-- Catálogo -->
        <div class="pos-catalog">
            <div class="row g-3 bg-white p-3 rounded-4 shadow-sm border-0">
                <div class="col-md-8">
                    <div class="input-group border rounded-3 overflow-hidden bg-light">
                        <span class="input-group-text bg-transparent border-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" id="buscarProducto" class="form-control bg-transparent border-0 p-2" placeholder="Escanea código de barras o busca el nombre del producto...">
                    </div>
                </div>
                <div class="col-md-4">
                    <select id="filtroCategoria" class="form-select border rounded-3 p-2 bg-light">
                        <option value="">Todas las líneas de producto</option>
                        @foreach($productos->pluck('categoria.nombre')->unique() as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="pos-products-scroll">
                <div class="row g-3" id="listaProductos">
                    @foreach($productos as $producto)
                        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                            <div class="product-card" onclick="agregarProducto({{ $producto->id }})">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-light text-secondary border rounded-1 px-2 py-1" style="font-size: 10px;">{{ $producto->codigo }}</span>
                                        <i class="bi bi-box-seam text-muted small"></i>
                                    </div>

                                    <div class="product-image-wrapper mb-3">
                                        @if($producto->imagen && file_exists(public_path('storage/' . $producto->imagen)))
                                            <img src="{{ asset('storage/' . $producto->imagen) }}" alt="{{ $producto->nombre }}" class="product-img-render">
                                        @elseif($producto->imagen && (str_starts_with($producto->imagen, 'http') || str_starts_with($producto->imagen, 'https')))
                                            <img src="{{ $producto->imagen }}" alt="{{ $producto->nombre }}" class="product-img-render">
                                        @else
                                            <div class="product-img-placeholder">
                                                <i class="bi bi-image text-black-50 fs-4"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <h6 class="fw-bold text-dark mb-1" style="font-size: 13px; line-height: 1.4; height: 38px; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
                                        {{ \Illuminate\Support\Str::limit($producto->nombre, 35) }}
                                    </h6>
                                </div>
                                <div class="mt-2">
                                    <div class="price">${{ number_format($producto->precio_venta ?? $producto->precio_dia, 2) }}</div>
                                    <div class="stock">Disponibles: <span id="stock-val-{{ $producto->id }}">{{ $producto->stock }}</span></div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Carrito con corrección de empalme -->
        <div class="pos-cart-panel">
            <div class="p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-bag-check-fill text-primary me-2"></i>Artículos a Vender</h6>
                <span class="badge bg-dark rounded-pill px-2" id="contadorItems">0 items</span>
            </div>
            
            <div id="carritoItems">
                <div class="text-center text-muted py-5 my-4">
                    <i class="bi bi-cart3 text-black-50 mb-3" style="font-size: 44px;"></i>
                    <p class="small fw-semibold">El carrito está vacío.<br>Haz clic en los productos para agregarlos.</p>
                </div>
            </div>
            
            <div class="cart-total">
                <div class="bg-white p-3 rounded-3 shadow-sm border mb-3">
                    <div class="d-flex justify-content-between text-muted small mb-1">
                        <span>Subtotal Bruto:</span>
                        <span id="subtotalCarrito">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-2">
                        <span>Impuesto IVA (16%):</span>
                        <span id="ivaCarrito">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center fw-bold border-top pt-2">
                        <span class="text-dark fs-6">Gran Total:</span>
                        <span id="totalCarrito" class="text-success fs-4">$0.00</span>
                    </div>
                </div>
                
                <div class="mb-2">
                    <select id="metodoPago" class="form-select border rounded-3 p-2">
                        <option value="" selected disabled>Selecciona el método...</option>
                        <option value="efectivo">Efectivo Físico</option>
                        <option value="transferencia">Transferencia Bancaria</option>
                        <option value="tarjeta">Terminal de Tarjeta</option>
                        <option value="mixto">Pago Mixto</option>
                    </select>
                </div>

                <div class="mb-2" id="seccionCambio" style="display: none; background: #eef1f6; padding: 12px; border-radius: 10px;">
                    <div class="mb-1">
                        <label class="form-label text-dark fw-bold mb-1" style="font-size: 11px;">Efectivo Recibido</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 fw-bold">$</span>
                            <input type="number" id="montoRecibido" class="form-control border-start-0 fw-bold" step="0.01" min="0" oninput="calcularCambio()">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary-subtle">
                        <span class="text-muted fw-bold small">Cambio:</span>
                        <span id="cambioCliente" class="fs-5 fw-bold text-primary">$0.00</span>
                    </div>
                </div>

                <div class="mb-2">
                    <div class="form-check form-switch">
                        <input type="checkbox" class="form-check-input" id="requiereFactura">
                        <label class="form-check-label small fw-semibold text-dark" for="requiereFactura">Desglosar Factura Fiscal</label>
                    </div>
                </div>
                
                <div class="mb-2" id="campoRFC" style="display: none;">
                    <input type="text" id="rfcCliente" class="form-control p-2 border rounded-3" placeholder="RFC del contribuyente">
                </div>
                
                <div class="mb-3">
                    <select id="clienteVenta" class="form-select border rounded-3 p-2">
                        <option value="">Cliente de Mostrador (Público General)</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <button class="btn btn-success btn-lg w-100 rounded-3 shadow fw-bold py-2 fs-6" id="btnRealizarVenta" onclick="realizarVenta()">
                        <i class="bi bi-shield-check me-2"></i> Registrar y Emitir Ticket
                    </button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- ====== MODALES ====== -->

<!-- Modal Abrir Caja -->
<div class="modal fade" id="modalAbrirCaja" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-success text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-unlock-fill me-2"></i> Apertura de Turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('puntoventa.abrirCaja') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info border-0 shadow-sm small d-flex gap-2">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span>Establece el dinero base en caja metálica para iniciar la jornada comercial.</span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asignación de Turno <span class="text-danger">*</span></label>
                        <select name="turno" class="form-select p-2" required>
                            <option value="mañana">Matutino (Mañana)</option>
                            <option value="tarde">Vespertino (Tarde)</option>
                            <option value="noche">Nocturno (Noche)</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Efectivo Inicial de Fondo <span class="text-danger">*</span></label>
                        <div class="input-group input-group-lg shadow-sm">
                            <span class="input-group-text bg-white border-end-0 fw-bold text-muted">$</span>
                            <input type="number" name="monto_inicial" class="form-control border-start-0 fw-bold text-success" step="0.01" min="0" placeholder="0.00" required autofocus>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">Confirmar Apertura</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Cerrar Caja -->
<div class="modal fade" id="modalCerrarCaja" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-lock-fill"></i> Arqueo y Cierre de Turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('puntoventa.cerrarCaja') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    @if($corteActivo)
                        <div class="card border-0 shadow-sm mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="text-muted small uppercase fw-bold">Fondo de Apertura:</span>
                                    <span class="fw-bold text-dark">$<span id="m-inicial">{{ number_format($corteActivo->monto_inicial, 2) }}</span></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center border-top pt-2">
                                    <span class="text-muted small uppercase fw-bold">Total Ventas Brutas:</span>
                                    <span class="fw-bold text-primary fs-5">$<span id="m-ventas-total">{{ number_format($corteActivo->total_ventas, 2) }}</span></span>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-muted small fw-bold mb-2 text-uppercase tracking-wider">Ventas por Método de Pago</h6>
                        <div class="list-group shadow-sm mb-3">
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0">
                                <div><i class="bi bi-cash text-success me-2"></i> Efectivo</div>
                                <span class="fw-bold" id="m-v-efectivo">${{ number_format($corteActivo->total_efectivo, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-top">
                                <div><i class="bi bi-arrow-right-short text-info me-2"></i> Transferencias</div>
                                <span class="fw-bold" id="m-v-transferencia">${{ number_format($corteActivo->total_transferencias, 2) }}</span>
                            </div>
                            <div class="list-group-item d-flex justify-content-between align-items-center border-0 border-top">
                                <div><i class="bi bi-credit-card text-primary me-2"></i> Tarjetas</div>
                                <span class="fw-bold" id="m-v-tarjeta">${{ number_format($corteActivo->total_tarjetas, 2) }}</span>
                            </div>
                        </div>

                        <h6 class="text-muted small fw-bold mb-2 text-uppercase tracking-wider">Movimientos de Efectivo</h6>
                        <div class="row g-2 mb-4">
                            <div class="col-6">
                                <div class="p-2 bg-white rounded shadow-sm text-center">
                                    <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Ingresos (+)</small>
                                    <span class="fw-bold text-success" id="m-mov-ingresos">${{ number_format($corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto'), 2) }}</span>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-white rounded shadow-sm text-center">
                                    <small class="text-muted d-block text-uppercase" style="font-size: 10px;">Egresos (-)</small>
                                    <span class="fw-bold text-danger" id="m-mov-egresos">${{ number_format($corteActivo->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto'), 2) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 rounded-3 bg-dark text-white text-center mb-4 shadow-sm">
                            <span class="text-white-50 small text-uppercase d-block mb-1">Efectivo Esperado en Caja Físico</span>
                            @php
                                $ingresosEfe = $corteActivo->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
                                $egresosEfe = $corteActivo->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
                                $efeEsperado = $corteActivo->monto_inicial + $corteActivo->total_efectivo + $ingresosEfe - $egresosEfe;
                            @endphp
                            <h3 class="fw-bold mb-0 text-warning">$<span id="m-total-esperado">{{ number_format($efeEsperado, 2) }}</span></h3>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold text-dark">Efectivo Real Contado por Cajero <span class="text-danger">*</span></label>
                            <div class="input-group input-group-lg shadow-sm">
                                <span class="input-group-text bg-white border-end-0 fw-bold text-muted">$</span>
                                <input type="number" name="monto_final" class="form-control border-start-0 fw-bold text-success" step="0.01" min="0" placeholder="0.00" required>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger px-4 fw-bold">Confirmar y Cerrar Caja</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Movimiento de Caja -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-arrow-left-right"></i> Ajuste Extra de Efectivo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('puntoventa.movimiento') }}" method="POST">
                @csrf
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tipo de Ajuste <span class="text-danger">*</span></label>
                        <select name="tipo" class="form-select" required>
                            <option value="ingreso">Ingreso (+ En efectivo)</option>
                            <option value="egreso">Egreso (- Retiro de efectivo)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Concepto / Motivo <span class="text-danger">*</span></label>
                        <input type="text" name="concepto" class="form-control" required placeholder="Ej: Compra de papelería">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Monto del Movimiento <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold text-muted">$</span>
                            <input type="number" name="monto" class="form-control fw-bold" step="0.01" min="0.01" required placeholder="0.00">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Canal de Caja</label>
                        <select name="metodo" class="form-select">
                            <option value="efectivo" selected>Cajón de Efectivo</option>
                            <option value="transferencia">Transferencia Bancaria</option>
                            <option value="tarjeta">Tarjeta</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 p-3 bg-white">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Registrar Operación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Comprobante Térmico Limpio (Eliminado Doble Botón Interno) -->
<div class="modal fade" id="modalTicket" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="width: 380px;">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h6 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Comprobante de Venta</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="limpiarYEnfocarPOS()"></button>
            </div>
            <div class="modal-body p-0 bg-secondary">
                <iframe id="iframeTicket" src="" style="width: 100%; height: 480px; border: none; display: block; background: #fff;"></iframe>
            </div>
            <div class="modal-footer border-0 p-3 bg-white d-flex gap-2">
                <button type="button" id="btnFinalizarTicket" class="btn btn-light flex-grow-1 fw-semibold" data-bs-dismiss="modal" onclick="limpiarYEnfocarPOS()">Finalizar</button>
                <button type="button" class="btn btn-primary px-4 fw-bold shadow" onclick="imprimirTicketModal()">
                    <i class="bi bi-printer-fill me-1"></i> Imprimir Ticket
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let carrito = [];
let productos = @json($productos);

// 1. FUNCIONES DEL CARRITO
function agregarProducto(id) {
    const producto = productos.find(p => p.id === id);
    if (!producto) return;

    const item = carrito.find(i => i.id === id);
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
            stock: producto.stock
        });
    }
    renderizarCarrito();
}

function eliminarItem(id) {
    carrito = carrito.filter(i => i.id !== id);
    renderizarCarrito();
}

function actualizarCantidad(id, nuevaCantidad) {
    const item = carrito.find(i => i.id === id);
    if (!item) return;
    if (nuevaCantidad < 1) {
        eliminarItem(id);
        return;
    }
    if (nuevaCantidad > item.stock) {
        alert('Stock insuficiente');
        return;
    }
    item.cantidad = nuevaCantidad;
    renderizarCarrito();
}

function renderizarCarrito() {
    const container = document.getElementById('carritoItems');
    const contador = document.getElementById('contadorItems');
    let subtotal = 0;

    if (carrito.length === 0) {
        container.innerHTML = `
            <div class="text-center text-muted py-5 my-4">
                <i class="bi bi-cart3 text-black-50 mb-3" style="font-size: 44px;"></i>
                <p class="small fw-semibold">El carrito está vacío.<br>Haz clic en los productos para agregarlos.</p>
            </div>
        `;
        contador.textContent = '0 items';
        document.getElementById('subtotalCarrito').textContent = '$0.00';
        document.getElementById('ivaCarrito').textContent = '$0.00';
        document.getElementById('totalCarrito').textContent = '$0.00';
        calcularCambio();
        return;
    }

    let html = '';
    carrito.forEach((item) => {
        const totalItem = item.precio * item.cantidad;
        subtotal += totalItem;
        html += `
            <div class="cart-item">
                <div style="max-width: 60%;">
                    <div class="fw-bold text-dark small text-truncate">${item.nombre}</div>
                    <small class="text-muted">$${parseFloat(item.precio).toFixed(2)} c/u</small>
                </div>
                <div class="cart-item-qty-actions">
                    <button class="btn btn-xs btn-light border p-1 py-0 rounded" onclick="actualizarCantidad(${item.id}, ${item.cantidad - 1})">-</button>
                    <span>${item.cantidad}</span>
                    <button class="btn btn-xs btn-light border p-1 py-0 rounded" onclick="actualizarCantidad(${item.id}, ${item.cantidad + 1})">+</button>
                    <button class="btn btn-sm btn-link text-danger ms-2 p-0" onclick="eliminarItem(${item.id})">
                        <i class="bi bi-trash3-fill"></i>
                    </button>
                </div>
            </div>
        `;
    });

    container.innerHTML = html;
    contador.textContent = carrito.reduce((sum, i) => sum + i.cantidad, 0) + ' items';

    const requiereFactura = document.getElementById('requiereFactura').checked;
    const iva = requiereFactura ? subtotal * 0.16 : 0;
    const total = subtotal + iva;
    
    document.getElementById('subtotalCarrito').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('ivaCarrito').textContent = requiereFactura ? '$' + iva.toFixed(2) : '$0.00';
    document.getElementById('totalCarrito').textContent = '$' + total.toFixed(2);
    
    calcularCambio();
}

// 2. INTERFAZ Y CÁLCULO DE CAMBIO
const reqFacturaCheck = document.getElementById('requiereFactura');
if (reqFacturaCheck) {
    reqFacturaCheck.addEventListener('change', function() {
        document.getElementById('campoRFC').style.display = this.checked ? 'block' : 'none';
        renderizarCarrito();
    });
}

const selectMetodoPago = document.getElementById('metodoPago');
if (selectMetodoPago) {
    selectMetodoPago.addEventListener('change', function() {
        const seccionCambio = document.getElementById('seccionCambio');
        const inputRecibido = document.getElementById('montoRecibido');
        
        if (this.value === 'efectivo' || this.value === 'mixto') {
            seccionCambio.style.display = 'block';
            if (inputRecibido) inputRecibido.focus();
        } else {
            seccionCambio.style.display = 'none';
            if (inputRecibido) inputRecibido.value = '';
            document.getElementById('cambioCliente').textContent = '$0.00';
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
        elCambio.className = 'fs-5 fw-bold text-success';
    } else {
        elCambio.textContent = 'Faltan $' + Math.abs(cambio).toFixed(2);
        elCambio.className = 'fs-5 fw-bold text-danger';
    }
}

// 3. PROCESAMIENTO DE LA VENTA Y REACTIVIDAD
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

    if (metodoPago === 'efectivo' || metodoPago === 'mixto') {
        const totalRaw = document.getElementById('totalCarrito').textContent.replace('$', '').replace(',', '');
        const total = parseFloat(totalRaw) || 0;
        const recibido = parseFloat(document.getElementById('montoRecibido').value) || 0;
        
        if (recibido < total) {
            alert('El monto recibido es menor al total de la venta.');
            document.getElementById('montoRecibido').focus();
            return;
        }
    }

    const data = {
        items: carrito.map(i => ({ id: i.id, cantidad: i.cantidad })),
        metodo_pago: metodoPago,
        cliente_id: clienteId || null,
        requiere_factura: requiereFactura,
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
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            carrito.forEach(item => {
                const prodLocal = productos.find(p => p.id === item.id);
                if (prodLocal) {
                    prodLocal.stock -= item.cantidad;
                    const elStockHTML = document.getElementById(`stock-val-${item.id}`);
                    if (elStockHTML) elStockHTML.textContent = prodLocal.stock;
                }
            });

            const elTotalVentasCaja = document.getElementById('caja-total-ventas');
            if (elTotalVentasCaja && data.total) {
                let ventasActuales = parseFloat(elTotalVentasCaja.textContent.replace(/[^0-9.-]+/g,"")) || 0;
                let nuevaVentaTotal = parseFloat(data.total) || 0;
                let nuevoAcumuladoVentas = ventasActuales + nuevaVentaTotal;
                elTotalVentasCaja.textContent = nuevoAcumuladoVentas.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            const iframe = document.getElementById('iframeTicket');
            if (iframe) {
                iframe.src = `/puntoventa/ticket/${data.venta_id}`; 
            }
            
            const elModal = document.getElementById('modalTicket');
            if (elModal) {
                if (typeof bootstrap !== 'undefined') {
                    const modalTicket = new bootstrap.Modal(elModal);
                    modalTicket.show();
                } else {
                    elModal.classList.add('show');
                    elModal.style.display = 'block';
                    document.body.classList.add('modal-open');
                }
            }

            carrito = [];
            renderizarCarrito();
            if (selectMetodoPago) selectMetodoPago.value = "";
            const inputRecibido = document.getElementById('montoRecibido');
            if (inputRecibido) inputRecibido.value = "";
            document.getElementById('seccionCambio').style.display = "none";
            document.getElementById('cambioCliente').textContent = "$0.00";
        } else {
            alert(data.message || 'Error al procesar la venta');
        }
    })
    .catch(error => {
        alert('Error al procesar la venta.');
        console.error('Error:', error);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-lg"></i> Realizar Venta';
    });
}

function imprimirTicketModal() {
    const iframe = document.getElementById('iframeTicket');
    if (iframe) {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }
}

// 4. BÚSQUEDA Y FILTROS
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
// Función para limpiar la pantalla y regresar el foco al buscador de productos
function limpiarYEnfocarPOS() {
    // Si se usó el modal por clases CSS alternativas de respaldo, limpiamos los estilos manuales
    const elModal = document.getElementById('modalTicket');
    if (elModal) {
        elModal.classList.remove('show');
        elModal.style.display = 'none';
        document.body.classList.remove('modal-open');
        
        const bDrop = document.getElementById('backdrop-temporal');
        if (bDrop) bDrop.remove();
    }
    
    // Regresar el foco de inmediato para la siguiente venta rápida
    const inputBusqueda = document.getElementById('buscarProducto');
    if (inputBusqueda) {
        inputBusqueda.value = '';
        inputBusqueda.focus();
    }
}
</script>
@endsection