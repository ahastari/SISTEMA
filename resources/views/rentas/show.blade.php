@extends('layouts.admin')

@section('content')
<style>
    .contrato-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 20px;
        border-radius: 10px 10px 0 0;
    }
    .contrato-body {
        background: white;
        padding: 30px;
        border-radius: 0 0 10px 10px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .info-card {
        background: #f8f9fa;
        border-left: 4px solid #2a5298;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 8px;
    }
    .info-card h6 {
        color: #2a5298;
        font-weight: bold;
        margin-bottom: 10px;
    }
    .table-renta {
        background: white;
    }
    .table-renta thead {
        background: #2a5298;
        color: white;
    }
    .badge-estado {
        font-size: 14px;
        padding: 8px 15px;
        border-radius: 20px;
    }
    .total-card {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
    }
    .clausula {
        background: #fff3cd;
        border-left: 4px solid #ffc107;
        padding: 10px 15px;
        margin-bottom: 8px;
        border-radius: 5px;
        font-size: 13px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Contrato de Renta</h2>
    <div>
        <a href="{{ route('rentas.index') }}" class="btn btn-secondary">Regresar</a>
        <a href="{{ route('rentas.contrato', $renta) }}" class="btn btn-danger">Descargar Contrato PDF</a>
        <a href="{{ route('rentas.pagare', $renta) }}" class="btn btn-warning">Descargar Pagaré PDF</a>  <!-- ← Agrega esta línea -->
        @if($renta->estado == 'activa')
            <a href="{{ route('rentas.finalizar', $renta) }}" class="btn btn-success">Finalizar Renta</a>
        @endif
    </div>
</div>

<div class="contrato-header">
    <div class="row">
        <div class="col-md-8">
            <h3>ANDAMIOS Y MADERA VIRAMONTES</h3>
            <p class="mb-0">Ing. Godofredo Viramontes Medina</p>
            <small>AVE. DEL CIPRES #314 COL. MASIE DURANGO, DGO. C.P. 34217</small><br>
            <small>TEL. 618 455 36 71 CEL. 618 159 70 19</small>
        </div>
        <div class="col-md-4 text-end">
            <h4>CONTRATO N°</h4>
            <h2 class="fw-bold">{{ $renta->folio }}</h2>
        </div>
    </div>
</div>

<div class="contrato-body">
    <!-- Datos del Cliente -->
    <div class="row">
        <div class="col-md-12">
            <div class="info-card">
                <h6><i class="bi bi-person"></i> DATOS DEL CLIENTE</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre:</strong> {{ $renta->cliente->nombre_completo }}<br>
                        <strong>Teléfono:</strong> {{ $renta->cliente->telefono }}<br>
                        <strong>Email:</strong> {{ $renta->cliente->email ?? 'No especificado' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Dirección:</strong> {{ $renta->cliente->direccion ?? 'No especificada' }}<br>
                        <strong>RFC:</strong> {{ $renta->cliente->rfc ?? 'No especificado' }}<br>
                        <strong>CURP:</strong> {{ $renta->cliente->curp ?? 'No especificado' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Equipos Rentados -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5><i class="bi bi-box-seam"></i> EQUIPO RENTADO</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-renta">
                    <thead>
                        <tr>
                            <th>Cantidad</th>
                            <th>Equipo</th>
                            <th>Código</th>
                            <th>Precio/día</th>
                            <th>Días</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renta->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->cantidad }} x {{ $detalle->equipo->nombre }}</td>
                            <td>{{ $detalle->equipo->nombre }}</td>
                            <td>{{ $detalle->equipo->codigo }}</td>
                            <td>${{ number_format($detalle->precio_dia, 2) }}</td>
                            <td>{{ $detalle->dias }}</td>
                            <td>${{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr><td colspan="5" class="text-end fw-bold">Subtotal:</td><td>${{ number_format($renta->subtotal, 2) }}</td></tr>
                        <tr><td colspan="5" class="text-end fw-bold">IVA (16%):</td><td>${{ number_format($renta->iva, 2) }}</td></tr>
                        <tr class="fw-bold"><td colspan="5" class="text-end fs-5">TOTAL:</td><td class="fs-5 text-success">${{ number_format($renta->total, 2) }}</td></tr>
                        @if($renta->deposito > 0)
                        <tr><td colspan="5" class="text-end">Depósito:</td><td>${{ number_format($renta->deposito, 2) }}</td></tr>
                        <tr class="fw-bold"><td colspan="5" class="text-end">Saldo a pagar:</td><td class="text-primary">${{ number_format($renta->total - $renta->deposito, 2) }}</td></tr>
                        @endif
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalles de la Renta -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="info-card">
                <h6><i class="bi bi-calendar"></i> PERIODO DE RENTA</h6>
                <strong>Fecha inicio:</strong> {{ $renta->fecha_inicio->format('d/m/Y') }}<br>
                <strong>Fecha fin:</strong> {{ $renta->fecha_fin->format('d/m/Y') }}<br>
                <strong>Días totales:</strong> {{ $renta->dias_totales }} días<br>
                <strong>Estado:</strong> 
                    @if($renta->estado == 'activa')
                        <span class="badge bg-success">ACTIVA</span>
                    @elseif($renta->estado == 'finalizada')
                        <span class="badge bg-info">FINALIZADA</span>
                    @endif
            </div>
        </div>
        <div class="col-md-6">
            <div class="total-card">
                <h6 class="text-white">MONTO TOTAL</h6>
                <h2 class="fw-bold">${{ number_format($renta->total, 2) }}</h2>
                @if($renta->deposito > 0)
                    <small>Depósito: ${{ number_format($renta->deposito, 2) }}</small>
                @endif
            </div>
        </div>
    </div>

    <!-- CLAUSULAS -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h5><i class="bi bi-file-text"></i> CLAUSULAS</h5>
            <div class="clausula">1. El prestador de servicios se compromete a entregar en perfectas condiciones de trabajo el equipo.</div>
            <div class="clausula">2. El cliente tiene la obligación de verificar el buen estado en que recibe el equipo y entregarlo de igual forma.</div>
            <div class="clausula">3. Las piezas faltantes o averiadas se cobrarán en efectivo, no siendo sustituidas por otras.</div>
            <div class="clausula">4. En la renta del equipo NO HAY CREDITO, al devolver el equipo se deberá liquidar la renta.</div>
            <div class="clausula">5. Se cobrará el día de salida y el día de entrada.</div>
        </div>
    </div>

    <!-- Firmas -->
    <div class="row mt-5 pt-3">
        <div class="col-md-6 text-center">
            <hr style="width: 80%; margin: auto;">
            <p class="mt-2 fw-bold">{{ $renta->cliente->nombre_completo }}</p>
            <small>Nombre y firma del cliente</small>
        </div>
        <div class="col-md-6 text-center">
            <hr style="width: 80%; margin: auto;">
            <p class="mt-2 fw-bold">Ing. Godofredo Viramontes Medina</p>
            <small>Nombre y firma del prestador</small>
        </div>
    </div>

    <div class="text-center mt-4">
        <small class="text-muted">
            Durango, Dgo. a {{ $renta->created_at->format('d') }} de 
            {{ $renta->created_at->locale('es')->monthName }} de 
            {{ $renta->created_at->format('Y') }}
        </small>
    </div>
</div>

<style>
@media print {
    .btn, .navbar, .sidebar-link, nav, .d-flex.justify-content-between.align-items-center {
        display: none !important;
    }
    .contrato-header, .contrato-body {
        margin: 0;
        padding: 0;
    }
    body {
        background: white;
    }
}
</style>
@endsection