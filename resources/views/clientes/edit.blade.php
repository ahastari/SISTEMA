@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Editar Cliente</h2>

<form action="{{ route('clientes.update', $cliente) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre completo *</label>
                    <input type="text" name="nombre_completo" class="form-control @error('nombre_completo') is-invalid @enderror" value="{{ old('nombre_completo', $cliente->nombre_completo) }}" required>
                    @error('nombre_completo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Teléfono *</label>
                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $cliente->telefono) }}" required>
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Resto de campos igual que en create.blade.php pero con value="{{ old('campo', $cliente->campo) }}" -->

                <div class="col-md-6 mb-3">
                    <label>Documento INE</label>
                    @if($cliente->ine_documento)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->ine_documento) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="ine_documento" class="form-control @error('ine_documento') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('ine_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Contrato Firmado</label>
                    @if($cliente->contrato_firmado)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->contrato_firmado) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="contrato_firmado" class="form-control @error('contrato_firmado') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('contrato_firmado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Comprobante de Depósito</label>
                    @if($cliente->comprobante_deposito)
                        <div class="mb-2">
                            <small>Archivo actual: <a href="{{ Storage::url($cliente->comprobante_deposito) }}" target="_blank">Ver actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="comprobante_deposito" class="form-control @error('comprobante_deposito') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Dejar vacío para mantener el archivo actual</small>
                    @error('comprobante_deposito')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Copia el resto de campos del create.blade.php aquí -->
                
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Actualizar Cliente</button>
            </div>
        </div>
    </div>
</form>
@endsection