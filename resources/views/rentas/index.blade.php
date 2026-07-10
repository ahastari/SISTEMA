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
</style>

<!-- Header Responsive -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h3 class="mb-0 fw-bold text-body">
            <i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Rentas
        </h3>
        <p class="text-secondary small mb-0">Gestión e historial de contratos de renta</p>
    </div>
    <a href="{{ route('rentas.create') }}" class="btn btn-primary rounded-3 px-3 py-2 fw-semibold">
        <i class="bi bi-plus-circle me-1"></i> Nueva Renta
    </a>
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

<!-- Tarjetas de estadísticas Responsive -->
<div class="row g-2 mb-3">
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #0d6efd;">
            <h3>{{ $rentas->total() }}</h3>
            <p>Total de Rentas</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #198754;">
            <h3>{{ $rentas->where('estado', 'activa')->count() }}</h3>
            <p>Rentas Activas</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #0dcaf0;">
            <h3>{{ $rentas->where('estado', 'finalizada')->count() }}</h3>
            <p>Rentas Finalizadas</p>
        </div>
    </div>
    <div class="col-6 col-sm-6 col-lg-3">
        <div class="stats-card" style="border-left-color: #ffc107;">
            <h3>${{ number_format($rentas->sum('total'), 0) }}</h3>
            <p>Total Facturado</p>
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
    <div class="renta-card" data-estado="{{ $renta->estado }}" data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}">
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

            <!-- Equipos -->
            <div class="col-6 col-sm-4 col-md-2 text-start text-sm-center">
                <small class="text-secondary d-block" style="font-size: 11px;">Equipos</small>
                <strong class="text-body small">{{ $renta->detalles->count() }} tipos</strong>
                <small class="text-secondary d-block" style="font-size: 11px;">{{ $renta->detalles->sum('cantidad') }} unds</small>
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

            <!-- Total -->
            <div class="col-6 col-md-2 col-lg-1 text-end text-md-center">
                <small class="text-secondary d-block" style="font-size: 11px;">Total</small>
                <div class="total-amount">${{ number_format($renta->total, 0) }}</div>
            </div>

            <!-- Estado y Acciones -->
            <div class="col-12 col-md-2 col-lg-2">
                <div class="d-flex justify-content-between justify-content-md-end align-items-center gap-2">
                    <span class="{{ $renta->estado == 'activa' ? 'estado-activa' : 'estado-finalizada' }}">
                        {{ $renta->estado == 'activa' ? 'ACTIVA' : 'FINALIZADA' }}
                    </span>
                    
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary border-0 rounded-circle p-2" data-bs-toggle="dropdown" aria-expanded="false">
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
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="#" onclick="event.preventDefault(); if(confirm('¿Eliminar esta renta?')) document.getElementById('delete-form-{{ $renta->id }}').submit();">
                                    <i class="bi bi-trash me-2"></i> Eliminar
                                </a>
                                <form id="delete-form-{{ $renta->id }}" action="{{ route('rentas.destroy', $renta) }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </li>
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
        <!-- <a href="{{ route('rentas.create') }}" class="btn btn-primary btn-sm rounded-3 mt-1">
            <i class="bi bi-plus-circle me-1"></i> Nueva Renta
        </a> -->
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
            if (renta.dataset.estado !== estado) {
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