@extends('layouts.admin')

@section('content')
<style>
    /* Estilos adaptables a Tema Oscuro y Claro */
    .stats-card {
        background: var(--bs-body-bg);
        border-radius: 12px;
        padding: 16px 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid var(--bs-border-color);
        border-left: 4px solid;
        height: 100%;
    }
    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .stats-card h3 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 2px;
        color: var(--bs-heading-color);
    }
    .stats-card p {
        color: var(--bs-secondary-color);
        margin-bottom: 0;
        font-size: 13px;
    }

    .renta-card {
        background: var(--bs-body-bg);
        border-radius: 12px;
        padding: 16px;
        margin-bottom: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.2s;
        border: 1px solid var(--bs-border-color);
    }
    .renta-card:hover {
        box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }

    .folio-badge {
        background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        white-space: nowrap;
    }
    
    .cliente-info {
        border-left: 3px solid #0d6efd;
        padding-left: 10px;
    }

    .total-amount {
        font-size: 20px;
        font-weight: bold;
        color: #198754;
    }

    /* Badges de Estado */
    .estado-activa {
        background: rgba(25, 135, 84, 0.15);
        color: #198754;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        display: inline-block;
    }
    .estado-finalizada {
        background: rgba(13, 110, 253, 0.15);
        color: #0d6efd;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        display: inline-block;
    }

    .dias-restantes {
        font-size: 13px;
        font-weight: bold;
    }

    .filtro-input {
        border-radius: 8px;
    }

    .estado-cancelada {
        background: rgba(108, 117, 125, 0.15);
        color: var(--bs-secondary-color);
        border: 1px solid var(--bs-border-color);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: bold;
        display: inline-block;
    }
</style>

<!-- Header Responsive -->
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
    <h2><i class="bi bi-card-list me-2"></i>Historial de Rentas</h2>
    
    <div class="d-flex gap-2">
        <a href="{{ route('rentas.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Nueva Renta
        </a>
    </div>
</div>

<!-- Alertas -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Tarjetas de estadísticas Financieras -->
<div class="row g-2 mb-3">
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #0d6efd;">
            <h3>${{ number_format($totalFacturado, 2) }}</h3>
            <p>Total Histórico</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #198754;">
            <h3>${{ number_format($totalPagado, 2) }}</h3>
            <p>Dinero Recibido</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #dc3545;">
            <h3>${{ number_format($totalPendiente, 2) }}</h3>
            <p>Resta por Cobrar</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #ffc107;">
            <h3>{{ $rentas->total() }}</h3>
            <p>Rentas Registradas</p>
        </div>
    </div>
</div>

<!-- Filtros Adaptativos -->
<div class="card border-0 shadow-sm mb-3 rounded-3" style="background: var(--bs-body-bg); border: 1px solid var(--bs-border-color) !important;">
    <div class="card-body p-3">
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-body text-secondary border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="buscarInput" class="form-control bg-body border-start-0 filtro-input" 
                           placeholder="Buscar folio o cliente...">
                </div>
            </div>
            <div class="col-6 col-md-3">
                <select id="estadoSelect" class="form-select bg-body filtro-input">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="finalizada">Finalizadas</option>
                    <option value="cancelada">Canceladas</option>
                    <option value="adeudo">Con Adeudo (Deben dinero)</option>
                </select>
            </div>
            <div class="col-6 col-md-3">
                <input type="date" id="fechaFilter" class="form-control bg-body filtro-input">
            </div>
            <div class="col-12 col-md-2">
                <button class="btn btn-outline-secondary w-100 filtro-input" id="limpiarFiltros">
                    <i class="bi bi-x-circle me-1"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Rentas Responsive -->
<div id="rentasLista">
    @forelse($rentas as $renta)
    <!-- Agregamos el atributo data-adeudo para que funcione el filtro -->
    <div class="renta-card" data-estado="{{ $renta->estado }}" data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}" data-adeudo="{{ $renta->saldo_pendiente > 0 ? 'si' : 'no' }}">
        <div class="row align-items-center g-3">
            
            <!-- Folio y Cliente -->
            <div class="col-12 col-md-4 col-lg-3">
                <div class="d-flex align-items-center gap-2">
                    <div class="folio-badge">
                        <i class="bi bi-file-text me-1"></i> {{ $renta->folio }}
                    </div>
                    <div class="cliente-info text-truncate">
                        <h6 class="mb-0 fw-bold text-body text-truncate" style="max-width: 200px;">{{ $renta->cliente->nombre_completo ?? 'Cliente general' }}</h6>
                        <small class="text-secondary d-block">
                            <i class="bi bi-telephone me-1"></i>{{ $renta->cliente->telefono ?? 'S/N' }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Período -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <small class="text-secondary d-block" style="font-size: 11px;">Período</small>
                <strong class="text-body small">{{ $renta->fecha_inicio->format('d/m') }} - {{ $renta->fecha_fin->format('d/m/Y') }}</strong>
                <small class="text-secondary d-block" style="font-size: 11px;">{{ $renta->dias_totales }} días</small>
            </div>

            <!-- Días Restantes -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <small class="text-secondary d-block" style="font-size: 11px;">Restante</small>
                @if($renta->estado == 'activa')
                    @if($renta->dias_restantes > 0)
                        <span class="dias-restantes 
                            @if($renta->dias_restantes <= 3) text-danger 
                            @elseif($renta->dias_restantes <= 7) text-warning 
                            @else text-success 
                            @endif">
                            {{ $renta->dias_restantes }} días
                        </span>
                    @else
                        <span class="text-danger fw-bold small">¡Vencida!</span>
                    @endif
                @else
                    <span class="text-secondary small">—</span>
                @endif
            </div>

            <!-- Total y Adeudo -->
            <div class="col-6 col-md-2 col-lg-3 text-end text-md-center">
                @if($renta->estado == 'cancelada')
                    <small class="text-secondary d-block text-decoration-line-through" style="font-size: 11px;">Original: ${{ number_format($renta->total_real, 2) }}</small>
                    <span class="badge bg-secondary mt-1 fs-6 px-2 py-1"><i class="bi bi-slash-circle me-1"></i>Deuda Anulada</span>
                @else
                    <small class="text-secondary d-block" style="font-size: 11px;">Total: ${{ number_format($renta->total_real, 2) }}</small>
                    
                    @if($renta->saldo_pendiente > 0)
                        <span class="badge bg-danger mt-1 fs-6 px-2 py-1"><i class="bi bi-exclamation-circle me-1"></i>Debe: ${{ number_format($renta->saldo_pendiente, 2) }}</span>
                    @else
                        <span class="badge bg-success mt-1"><i class="bi bi-check2-circle me-1"></i>Pagado</span>
                    @endif
                @endif
            </div>

            <!-- Estado y Acciones -->
            <div class="col-12 col-md-2 col-lg-2">
                <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    <span class="{{ $renta->estado == 'activa' ? 'estado-activa' : ($renta->estado == 'finalizada' ? 'estado-finalizada' : 'estado-cancelada') }}">
                        {{ strtoupper($renta->estado) }}
                    </span>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical fs-6"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('rentas.show', $renta) }}">
                                    <i class="bi bi-eye me-2 text-primary"></i> Ver contrato
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('rentas.contrato', $renta) }}" target="_blank">
                                    <i class="bi bi-file-pdf me-2 text-danger"></i> Imprimir PDF
                                </a>
                            </li>
                            @if($renta->estado == 'activa')
                            <li>
                                <a class="dropdown-item py-2" href="{{ route('rentas.finalizar', $renta) }}" onclick="return confirm('¿Finalizar esta renta?')">
                                    <i class="bi bi-check-lg me-2 text-success"></i> Finalizar
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="{{ route('rentas.cancelar', $renta) }}" onclick="return confirm('¿Seguro que deseas cancelar esta renta? El stock se devolverá automáticamente.')">
                                    <i class="bi bi-x-octagon me-2"></i> Cancelar Renta
                                </a>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @empty
    <div class="text-center py-5 rounded-3 bg-body border border-dashed">
        <i class="bi bi-inbox text-secondary" style="font-size: 48px;"></i>
        <h5 class="mt-3 text-body">No hay rentas registradas</h5>
        <p class="text-secondary small">Comienza creando tu primera renta en el sistema.</p>
    </div>
    @endforelse
</div>

<!-- Paginación -->
<div class="mt-3 d-flex justify-content-center">
    {{ $rentas->links() }}
</div>

<!-- JavaScript de Filtros -->
<script>
function filtrarRentas() {
    const busqueda = document.getElementById('buscarInput').value.toLowerCase();
    const estado = document.getElementById('estadoSelect').value;
    const fecha = document.getElementById('fechaFilter').value;
    
    const rentas = document.querySelectorAll('.renta-card');
    
    rentas.forEach(renta => {
        let mostrar = true;
        
        if (busqueda) {
            const texto = renta.innerText.toLowerCase();
            if (!texto.includes(busqueda)) {
                mostrar = false;
            }
        }
        
        if (mostrar && estado) {
            // Evaluamos si pidió filtrar los que deben dinero
            if (estado === 'adeudo') {
                if (renta.dataset.adeudo !== 'si') {
                    mostrar = false;
                }
            } else if (renta.dataset.estado !== estado) {
                // Filtro normal activa/finalizada
                mostrar = false;
            }
        }
        
        if (mostrar && fecha) {
            if (renta.dataset.fecha !== fecha) {
                mostrar = false;
            }
        }
        
        renta.style.display = mostrar ? '' : 'none';
    });
}

document.getElementById('limpiarFiltros').addEventListener('click', () => {
    document.getElementById('buscarInput').value = '';
    document.getElementById('estadoSelect').value = '';
    document.getElementById('fechaFilter').value = '';
    filtrarRentas();
});

document.getElementById('buscarInput').addEventListener('keyup', filtrarRentas);
document.getElementById('estadoSelect').addEventListener('change', filtrarRentas);
document.getElementById('fechaFilter').addEventListener('change', filtrarRentas);
</script>
@endsection