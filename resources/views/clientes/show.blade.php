@extends('layouts.admin')

@section('content')
<h2>Detalle del Cliente</h2>

<div class="card mt-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Nombre:</th>
                        <td>{{ $cliente->nombre_completo }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $cliente->telefono }}</td>
                    </tr>
                    <tr>
                        <th>Teléfono alternativo:</th>
                        <td>{{ $cliente->telefono_alternativo ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Email:</th>
                        <td>{{ $cliente->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>RFC:</th>
                        <td>{{ $cliente->rfc ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>CURP:</th>
                        <td>{{ $cliente->curp ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>INE Número:</th>
                        <td>{{ $cliente->ine_numero ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table table-borderless">
                    <tr>
                        <th width="200">Empresa:</th>
                        <td>{{ $cliente->empresa ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Dirección:</th>
                        <td>{{ $cliente->direccion ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Ciudad:</th>
                        <td>{{ $cliente->ciudad ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>{{ $cliente->estado ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Código Postal:</th>
                        <td>{{ $cliente->codigo_postal ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Observaciones:</th>
                        <td>{{ $cliente->observaciones ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <hr>

        <div class="row mt-3">
            <div class="col-md-4">
                <h6>Documento INE</h6>
                @if($cliente->ine_documento)
                    <a href="{{ Storage::url($cliente->ine_documento) }}" target="_blank" class="btn btn-sm btn-info">
                        Ver documento
                    </a>
                @else
                    <p class="text-muted">No disponible</p>
                @endif
            </div>

            <div class="col-md-4">
                <h6>Contrato Firmado</h6>
                @if($cliente->contrato_firmado)
                    <a href="{{ Storage::url($cliente->contrato_firmado) }}" target="_blank" class="btn btn-sm btn-info">
                        Ver contrato
                    </a>
                @else
                    <p class="text-muted">No disponible</p>
                @endif
            </div>

            <div class="col-md-4">
                <h6>Comprobante de Depósito</h6>
                @if($cliente->comprobante_deposito)
                    <a href="{{ Storage::url($cliente->comprobante_deposito) }}" target="_blank" class="btn btn-sm btn-info">
                        Ver comprobante
                    </a>
                @else
                    <p class="text-muted">No disponible</p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('clientes.index') }}" class="btn btn-primary">Regresar</a>
    <a href="{{ route('clientes.edit', $cliente) }}" class="btn btn-warning">Editar</a>
</div>
@endsection