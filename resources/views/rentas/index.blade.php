@extends('layouts.admin')

@section('content')
<style>
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        transition: transform 0.2s;
        border-left: 4px solid;
    }
    .stats-card:hover {
        transform: translateY(-3px);
    }
    .stats-card h3 {
        font-size: 28px;
        font-weight: bold;
        margin-bottom: 5px;
    }
    .stats-card p {
        color: #6c757d;
        margin-bottom: 0;
        font-size: 14px;
    }
    .renta-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.2s;
        border: 1px solid #e9ecef;
    }
    .renta-card:hover {
        box-shadow: 0 5px 20px rgba(0,0,0,0.12);
        transform: translateY(-2px);
    }
    .folio-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .cliente-info {
        border-left: 3px solid #667eea;
        padding-left: 12px;
    }
    .total-amount {
        font-size: 24px;
        font-weight: bold;
        color: #28a745;
    }
    .estado-activa {
        background: #d4edda;
        color: #155724;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .estado-finalizada {
        background: #cfe2ff;
        color: #084298;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
    }
    .dias-restantes {
        font-size: 14px;
        font-weight: bold;
    }
    .filtro-input {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 15px;
    }
    .btn-renta {
        border-radius: 10px;
        padding: 10px 20px;
    }
</style>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-0 fw-bold">
            <i class="bi bi-journal-bookmark-fill me-2 text-primary"></i>Rentas
        </h2>
        <p class="text-muted mt-1">Gestión de contratos de renta</p>
    </div>
    <a href="{{ route('rentas.create') }}" class="btn btn-primary btn-renta">
        <i class="bi bi-plus-circle me-2"></i>Nueva Renta
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

<!-- Tarjetas de estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stats-card" style="border-left-color: #667eea;">
            <h3>{{ $rentas->total() }}</h3>
            <p>Total de Rentas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="border-left-color: #28a745;">
            <h3>{{ $rentas->where('estado', 'activa')->count() }}</h3>
            <p>Rentas Activas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="border-left-color: #17a2b8;">
            <h3>{{ $rentas->where('estado', 'finalizada')->count() }}</h3>
            <p>Rentas Finalizadas</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card" style="border-left-color: #ffc107;">
            <h3>${{ number_format($rentas->sum('total'), 0) }}</h3>
            <p>Total Facturado</p>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card border-0 shadow-sm mb-4 rounded-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" id="buscarInput" class="form-control border-start-0 filtro-input" 
                           placeholder="Buscar por folio o cliente...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="estadoSelect" class="form-select filtro-input">
                    <option value="">Todos los estados</option>
                    <option value="activa">Activas</option>
                    <option value="finalizada">Finalizadas</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" id="fechaFilter" class="form-control filtro-input" placeholder="Filtrar por fecha">
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100" id="limpiarFiltros">
                    <i class="bi bi-x-circle"></i> Limpiar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Rentas -->
<div id="rentasLista">
    @forelse($rentas as $renta)
    <div class="renta-card" data-estado="{{ $renta->estado }}" data-fecha="{{ $renta->fecha_inicio->format('Y-m-d') }}">
        <div class="row align-items-center">
            <div class="col-lg-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="folio-badge">
                        <i class="bi bi-file-text me-1"></i> {{ $renta->folio }}
                    </div>
                    <div class="cliente-info">
                        <h6 class="mb-0 fw-bold">{{ $renta->cliente->nombre_completo }}</h6>
                        <small class="text-muted">
                            <i class="bi bi-telephone"></i> {{ $renta->cliente->telefono }}
                        </small>
                    </div>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="text-center">
                    <small class="text-muted d-block">Período</small>
                    <strong>{{ $renta->fecha_inicio->format('d/m') }} - {{ $renta->fecha_fin->format('d/m/Y') }}</strong>
                    <small class="text-muted d-block">{{ $renta->dias_totales }} días</small>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="text-center">
                    <small class="text-muted d-block">Equipos</small>
                    <strong>{{ $renta->detalles->count() }} tipos</strong>
                    <small class="text-muted d-block">{{ $renta->detalles->sum('cantidad') }} unidades</small>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="text-center">
                    <small class="text-muted d-block">Días restantes</small>
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
                            <span class="text-danger fw-bold">¡Vencida!</span>
                        @endif
                    @else
                        <span class="text-muted">—</span>
                    @endif
                </div>
            </div>
            <div class="col-lg-1">
                <div class="text-end">
                    <small class="text-muted d-block">Total</small>
                    <div class="total-amount">${{ number_format($renta->total, 0) }}</div>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="d-flex justify-content-end gap-2">
                    <span class="{{ $renta->estado == 'activa' ? 'estado-activa' : 'estado-finalizada' }}">
                        {{ $renta->estado == 'activa' ? 'ACTIVA' : 'FINALIZADA' }}
                    </span>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-light rounded-circle" data-bs-toggle="dropdown">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('rentas.show', $renta) }}">
                                    <i class="bi bi-eye me-2"></i> Ver contrato
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="{{ route('rentas.contrato', $renta) }}" target="_blank">
                                    <i class="bi bi-file-pdf me-2 text-danger"></i> Ver PDF
                                </a>
                            </li>
                            @if($renta->estado == 'activa')
                            <li>
                                <a class="dropdown-item" href="{{ route('rentas.finalizar', $renta) }}" onclick="return confirm('¿Finalizar esta renta?')">
                                    <i class="bi bi-check-lg me-2 text-success"></i> Finalizar
                                </a>
                            </li>
                            @endif
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="#" onclick="event.preventDefault(); if(confirm('¿Eliminar esta renta?')) document.getElementById('delete-form-{{ $renta->id }}').submit();">
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
    <div class="text-center py-5">
        <i class="bi bi-inbox" style="font-size: 64px; color: #ccc;"></i>
        <h4 class="mt-3 text-muted">No hay rentas registradas</h4>
        <p class="text-muted">Comienza creando tu primera renta</p>
        <a href="{{ route('rentas.create') }}" class="btn btn-primary mt-2">
            <i class="bi bi-plus-circle"></i> Nueva Renta
        </a>
    </div>
    @endforelse
</div>

<!-- Paginación -->
<div class="mt-4">
    {{ $rentas->links() }}
</div>

<script>
// Filtros en tiempo real
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