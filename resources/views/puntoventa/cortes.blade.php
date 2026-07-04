@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 fw-bold text-dark">
        <i class="bi bi-cash-stack text-primary"></i> Historial de Cortes y Turnos
    </h4>
    <a href="{{ route('puntoventa.index') }}" class="btn btn-outline-primary px-3 shadow-sm fw-bold">
        <i class="bi bi-arrow-left-short fs-5 align-middle"></i> Regresar al POS
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm mb-3">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger border-0 shadow-sm mb-3">{{ session('error') }}</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase tracking-wider">
                    <tr>
                        <th class="ps-4 py-3">#</th>
                        <th class="py-3">Usuario / Turno</th>
                        <th class="py-3">Apertura / Cierre</th>
                        <th class="py-3 text-end">Fondo Inicial</th>
                        <th class="py-3 text-end">Ventas Brutas</th>
                        <th class="py-3 text-center">Flujo Efectivo</th>
                        <th class="py-3 text-end bg-dark-subtle text-dark">Efectivo Esperado</th>
                        <th class="py-3 text-end">Monto Real Físico</th>
                        <th class="py-3 text-end">Diferencia</th>
                        <th class="pe-4 py-3 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    @forelse($cortes as $corte)
                    @php
                        // Cálculos matemáticos de flujos para este corte específico
                        $ingresosEfe = $corte->movimientos->where('tipo', 'ingreso')->where('metodo', 'efectivo')->sum('monto');
                        $egresosEfe = $corte->movimientos->where('tipo', 'egreso')->where('metodo', 'efectivo')->sum('monto');
                        
                        // Dinero que DEBÍA haber en caja física
                        $efectivoEsperado = $corte->monto_inicial + $corte->total_efectivo + $ingresosEfe - $egresosEfe;
                    @endphp
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $corte->id }}</td>
                        <td>
                            <div class="fw-bold text-dark">{{ $corte->user->name }}</div>
                            <span class="badge bg-light text-dark border text-capitalize px-2 py-1" style="font-size: 10px;">
                                <i class="bi bi-clock-history me-1"></i>{{ $corte->turno ?? 'Mañana' }}
                            </span>
                        </td>
                        <td>
                            <div class="small text-dark"><i class="bi bi-box-arrow-in-right text-success me-1"></i>{{ $corte->fecha_apertura->format('d/m/Y H:i') }}</div>
                            @if($corte->fecha_cierre)
                                <div class="small text-muted mt-1"><i class="bi bi-box-arrow-left text-danger me-1"></i>{{ $corte->fecha_cierre->format('d/m/Y H:i') }}</div>
                            @else
                                <div class="small text-muted mt-1"><i class="bi bi-dash-lg me-1"></i>En curso...</div>
                            @endif
                        </td>
                        <td class="text-end fw-semibold text-secondary">${{ number_format($corte->monto_inicial, 2) }}</td>
                        <td class="text-end fw-semibold text-primary">
                            ${{ number_format($corte->total_ventas, 2) }}
                            <br>
                            <small class="text-muted" style="font-size: 10px;" title="Ventas hechas únicamente en efectivo">
                                (Efe: ${{ number_format($corte->total_efectivo, 2) }})
                            </small>
                        </td>
                        
                        <td class="text-center">
                            @if($ingresosEfe > 0 || $egresosEfe > 0)
                                <div class="d-inline-block text-start" style="font-size: 11px;">
                                    <span class="text-success d-block" title="Ingresos adicionales de efectivo">
                                        <i class="bi bi-arrow-up-circle-fill"></i> +${{ number_format($ingresosEfe, 2) }}
                                    </span>
                                    <span class="text-danger d-block mt-1" title="Egresos adicionales de efectivo">
                                        <i class="bi bi-arrow-down-circle-fill"></i> -${{ number_format($egresosEfe, 2) }}
                                    </span>
                                </div>
                            @else
                                <small class="text-muted" style="font-size: 11px;">Sin movimientos</small>
                            @endif
                        </td>
                        
                        <td class="text-end fw-bold bg-light-subtle text-dark border-start border-end">
                            ${{ number_format($efectivoEsperado, 2) }}
                        </td>
                        
                        <td class="text-end fw-bold text-dark">
                            {{ $corte->monto_final ? '$' . number_format($corte->monto_final, 2) : '-' }}
                        </td>
                        
                        <td class="text-end fw-bold">
                            @if($corte->estado == 'abierto')
                                <span class="text-muted small fw-normal">Calculando...</span>
                            @else
                                @if(($corte->diferencia ?? 0) < 0)
                                    <span class="text-danger" title="Faltante en caja">
                                        <i class="bi bi-dash-circle"></i> ${{ number_format(abs($corte->diferencia), 2) }}
                                    </span>
                                @elseif(($corte->diferencia ?? 0) > 0)
                                    <span class="text-success" title="Sobrante en caja">
                                        <i class="bi bi-plus-circle"></i> ${{ number_format($corte->diferencia, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted"><i class="bi bi-check-all text-success"></i> $0.00</span>
                                @endif
                            @endif
                        </td>
                        
                        <td class="pe-4 text-center">
                            @if($corte->estado == 'abierto')
                                <span class="badge rounded-pill bg-success-subtle text-success px-3 py-1 border border-success-subtle">
                                    <span class="spinner-grow spinner-grow-sm align-middle me-1" style="width: 8px; height: 8px;" role="status"></span> Abierta
                                </span>
                            @else
                                <span class="badge rounded-pill bg-secondary-subtle text-secondary px-3 py-1 border border-secondary-subtle">
                                    Cerrada
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-black-50"></i>
                            No hay cortes de caja registrados en el sistema.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $cortes->links() }}
</div>
@endsection