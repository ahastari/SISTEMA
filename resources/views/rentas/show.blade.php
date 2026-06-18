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
    .document-card {
        border: 2px dashed #6c757d;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        height: 100%;
        transition: all 0.3s;
    }
    .document-card:hover {
        border-color: #0d6efd;
        background: #f8f9fa;
    }
    .document-card .icon {
        font-size: 48px;
        color: #6c757d;
    }
    .document-card .file-name {
        font-size: 12px;
        color: #6c757d;
        word-break: break-all;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">
        <i class="bi bi-file-text me-2"></i>Contrato de Renta
        <small class="text-muted">Folio: {{ $renta->folio }}</small>
    </h2>
    <div>
        <a href="{{ route('rentas.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Regresar
        </a>
        <a href="{{ route('rentas.contrato', $renta) }}" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-pdf"></i> Ver Contrato
        </a>
        <a href="{{ route('rentas.pagare', $renta) }}" class="btn btn-warning" target="_blank">
            <i class="bi bi-file-text"></i> Ver Pagaré
        </a>
        @if($renta->estado == 'activa')
            <a href="{{ route('rentas.finalizar', $renta) }}" class="btn btn-success" onclick="return confirm('¿Finalizar esta renta? Esto devolverá los equipos al inventario.')">
                <i class="bi bi-check-lg"></i> Finalizar Renta
            </a>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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
            <span class="badge bg-light text-dark">
                {{ $renta->estado == 'activa' ? 'ACTIVA' : 'FINALIZADA' }}
            </span>
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

    <!-- DATOS DE LA OBRA -->
    @if($renta->obra_id && $renta->obra)
    <div class="row">
        <div class="col-md-12">
            <div class="info-card" style="border-left-color: #28a745;">
                <h6><i class="bi bi-building"></i> DATOS DE LA OBRA / PROYECTO</h6>
                <div class="row">
                    <div class="col-md-6">
                        <strong>Nombre de la obra:</strong> {{ $renta->obra->nombre }}<br>
                        <strong>Dirección:</strong> {{ $renta->obra->direccion }}<br>
                        <strong>Colonia:</strong> {{ $renta->obra->colonia ?? 'No especificada' }}
                    </div>
                    <div class="col-md-6">
                        <strong>Ciudad:</strong> {{ $renta->obra->ciudad ?? 'Durango' }}<br>
                        <strong>Contacto:</strong> {{ $renta->obra->contacto_obra ?? 'No especificado' }}<br>
                        <strong>Teléfono obra:</strong> {{ $renta->obra->telefono_obra ?? 'No especificado' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detalles de la Renta -->
    <div class="info-card">
        <h6><i class="bi bi-calendar"></i> PERIODO DE RENTA</h6>
        <strong>Fecha inicio:</strong> {{ $renta->fecha_inicio->format('d/m/Y') }}<br>
        <strong>Fecha fin:</strong> {{ $renta->fecha_fin->format('d/m/Y') }}<br>
        <strong>Días totales:</strong> {{ $renta->dias_totales }} días<br>
        
        <!-- Días restantes -->
        @if($renta->estado == 'activa')
            <strong>Días restantes:</strong>
            @if($diasRestantes > 0)
                <span class="badge 
                    @if($diasRestantes <= 3) bg-danger 
                    @elseif($diasRestantes <= 7) bg-warning text-dark 
                    @else bg-success 
                    @endif">
                    {{ $diasRestantes }} días
                </span>
            @else
                <span class="badge bg-danger">¡Vencida!</span>
            @endif
            <br>
        @endif
        
        <strong>Estado:</strong> 
            @if($renta->estado == 'activa')
                <span class="badge bg-success">ACTIVA</span>
            @elseif($renta->estado == 'finalizada')
                <span class="badge bg-info">FINALIZADA</span>
            @endif
        @if($renta->obra_id && $renta->obra)
        @endif
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
                            <td>{{ $detalle->cantidad }} x</td>
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


    <!-- DOCUMENTOS FIRMADOS -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h5><i class="bi bi-files"></i> DOCUMENTOS FIRMADOS</h5>
            <div class="row">
                <!-- Contrato Firmado -->
                <div class="col-md-6 mb-3">
                    <div class="document-card">
                        <div class="icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <h6>Contrato Firmado</h6>
                        @if($renta->contrato_firmado_path)
                            <div class="file-name">
                                <a href="{{ Storage::url($renta->contrato_firmado_path) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <form action="{{ route('rentas.deleteDocumento', ['renta' => $renta, 'tipo' => 'contrato']) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este documento?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <small class="text-muted">Subido: {{ $renta->updated_at->format('d/m/Y H:i') }}</small>
                        @else
                            <form action="{{ route('rentas.uploadContrato', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <input type="file" name="contrato_firmado" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">PDF, JPG, PNG (Máx. 5MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Guardar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Pagaré Firmado -->
                <div class="col-md-6 mb-3">
                    <div class="document-card">
                        <div class="icon">
                            <i class="bi bi-file-earmark-text"></i>
                        </div>
                        <h6>Pagaré Firmado</h6>
                        @if($renta->pagare_firmado_path)
                            <div class="file-name">
                                <a href="{{ Storage::url($renta->pagare_firmado_path) }}" target="_blank" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> Ver
                                </a>
                                <form action="{{ route('rentas.deleteDocumento', ['renta' => $renta, 'tipo' => 'pagare']) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este documento?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <small class="text-muted">Subido: {{ $renta->updated_at->format('d/m/Y H:i') }}</small>
                        @else
                            <form action="{{ route('rentas.uploadPagare', $renta) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-2">
                                    <input type="file" name="pagare_firmado" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted">PDF, JPG, PNG (Máx. 5MB)</small>
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="bi bi-upload"></i> Guardar
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CLAUSULAS 
    <div class="row mt-4">
        <div class="col-md-12">
            <h5><i class="bi bi-file-text"></i> CLAUSULAS</h5>
            <div class="clausula">1. El prestador de servicios se compromete a entregar en perfectas condiciones de trabajo el equipo.</div>
            <div class="clausula">2. El cliente tiene la obligación de verificar el buen estado en que recibe el equipo y entregarlo de igual forma.</div>
            <div class="clausula">3. Las piezas faltantes o averiadas se cobrarán en efectivo, no siendo sustituidas por otras.</div>
            <div class="clausula">4. En la renta del equipo NO HAY CREDITO, al devolver el equipo se deberá liquidar la renta.</div>
            <div class="clausula">5. Se cobrará el día de salida y el día de entrada.</div>
        </div>
    </div>-->

    <!-- Firmas 
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
    </div>-->
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