@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Editar Cliente</h2>

<form action="{{ route('clientes.update', $cliente) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row">
                <!-- Nombre completo -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">Nombre completo</label>
                    <input type="text" name="nombre_completo" class="form-control @error('nombre_completo') is-invalid @enderror" 
                           value="{{ old('nombre_completo', $cliente->nombre_completo) }}" required>
                    @error('nombre_completo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" 
                           value="{{ old('telefono', $cliente->telefono) }}" required>
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Correo electrónico -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                           value="{{ old('email', $cliente->email) }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- RFC -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">RFC</label>
                    <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" 
                           value="{{ old('rfc', $cliente->rfc) }}">
                    @error('rfc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- CURP -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">CURP</label>
                    <input type="text" name="curp" class="form-control @error('curp') is-invalid @enderror" 
                           value="{{ old('curp', $cliente->curp) }}">
                    @error('curp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Número de INE 
                <div class="col-md-6 mb-3">
                    <label>Número de INE</label>
                    <input type="text" name="ine_numero" class="form-control @error('ine_numero') is-invalid @enderror" 
                           value="{{ old('ine_numero', $cliente->ine_numero) }}">
                    @error('ine_numero')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>-->

                <!-- Documento INE -->
                <div class="col-md-6 mb-3">
                    <label>Documento INE (Imagen o PDF)</label>
                    @if($cliente->ine_documento)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->ine_documento) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="ine_documento" class="form-control @error('ine_documento') is-invalid @enderror" 
                           accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('ine_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Contrato Firmado 
                <div class="col-md-6 mb-3">
                    <label>Contrato Firmado (Opcional)</label>
                    @if($cliente->contrato_firmado)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->contrato_firmado) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="contrato_firmado" class="form-control @error('contrato_firmado') is-invalid @enderror" 
                           accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('contrato_firmado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>-->

                <!-- Comprobante de Depósito 
                <div class="col-md-6 mb-3">
                    <label>Comprobante de Depósito (Opcional)</label>
                    @if($cliente->comprobante_deposito)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->comprobante_deposito) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="comprobante_deposito" class="form-control @error('comprobante_deposito') is-invalid @enderror" 
                           accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('comprobante_deposito')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>-->

                <!-- Teléfono alternativo -->
                <div class="col-md-6 mb-3">
                    <label>Teléfono alternativo</label>
                    <input type="text" name="telefono_alternativo" class="form-control" 
                           value="{{ old('telefono_alternativo', $cliente->telefono_alternativo) }}">
                </div>

                <!-- Empresa -->
                <div class="col-md-6 mb-3">
                    <label class="required-label">Empresa</label>
                    <input type="text" name="empresa" class="form-control" 
                           value="{{ old('empresa', $cliente->empresa) }}">
                </div>

                <!-- Dirección -->
                <div class="col-md-12 mb-3">
                    <label class="required-label">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="2">{{ old('direccion', $cliente->direccion) }}</textarea>
                </div>

                <!-- Ciudad -->
                <div class="col-md-4 mb-3">
                    <label class="required-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" 
                           value="{{ old('ciudad', $cliente->ciudad) }}">
                </div>

                <!-- Estado -->
                <div class="col-md-4 mb-3">
                    <label class="required-label">Estado</label>
                    <input type="text" name="estado" class="form-control" 
                           value="{{ old('estado', $cliente->estado) }}">
                </div>

                <!-- Código Postal -->
                <div class="col-md-4 mb-3">
                    <label class="required-label">Código Postal</label>
                    <input type="text" name="codigo_postal" class="form-control" 
                           value="{{ old('codigo_postal', $cliente->codigo_postal) }}">
                </div>

                <!-- Observaciones -->
                <div class="col-md-12 mb-3">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones', $cliente->observaciones) }}</textarea>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">
                    <i class="bi bi-save"></i> Actualizar Cliente
                </button>
            </div>
        </div>
    </div>
</form>
@endsection