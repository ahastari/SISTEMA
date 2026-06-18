@extends('layouts.admin')

@section('content')
<style>
    .required-asterisk {
        color: red;
        margin-left: 2px;
        font-weight: bold;
    }
</style>
<h2 class="mb-4">Nuevo Cliente</h2>

<form action="{{ route('clientes.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="required-label">Nombre completo</label>
                    <input type="text" name="nombre_completo" class="form-control @error('nombre_completo') is-invalid @enderror" value="{{ old('nombre_completo') }}" required>
                    @error('nombre_completo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="required-label">Teléfono</label>
                    <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono') }}" required>
                    @error('telefono')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="required-label">Correo electrónico</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="required-label">RFC</label>
                    <input type="text" name="rfc" class="form-control @error('rfc') is-invalid @enderror" value="{{ old('rfc') }}">
                    @error('rfc')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label class="required-label">CURP</label>
                    <input type="text" name="curp" class="form-control @error('curp') is-invalid @enderror" value="{{ old('curp') }}">
                    @error('curp')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
<!-- 
                <div class="col-md-6 mb-3">
                    <label>Número de INE</label>
                    <input type="text" name="ine_numero" class="form-control @error('ine_numero') is-invalid @enderror" value="{{ old('ine_numero') }}">
                    @error('ine_numero')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> -->

                <div class="col-md-6 mb-3">
                    <label class="required-label">Documento INE (Imagen o PDF)</label>
                    <input type="file" name="ine_documento" class="form-control @error('ine_documento') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>
                    @error('ine_documento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- <div class="col-md-6 mb-3">
                    <label>Contrato Firmado (Opcional)</label>
                    <input type="file" name="contrato_firmado" class="form-control @error('contrato_firmado') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>
                    @error('contrato_firmado')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Comprobante de Depósito (Opcional)</label>
                    <input type="file" name="comprobante_deposito" class="form-control @error('comprobante_deposito') is-invalid @enderror" accept="image/*,application/pdf">
                    <small class="text-muted">Formatos: JPG, PNG, PDF (Máx. 5MB)</small>
                    @error('comprobante_deposito')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div> -->

                <div class="col-md-6 mb-3">
                    <label>Teléfono alternativo</label>
                    <input type="text" name="telefono_alternativo" class="form-control" value="{{ old('telefono_alternativo') }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="required-label">Empresa</label>
                    <input type="text" name="empresa" class="form-control" value="{{ old('empresa') }}">
                </div>

                <div class="col-md-12 mb-3">
                    <label class="required-label">Dirección</label>
                    <textarea name="direccion" class="form-control" rows="2">{{ old('direccion') }}</textarea>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="required-label">Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="required-label">Estado</label>
                    <input type="text" name="estado" class="form-control" value="{{ old('estado') }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label class="required-label">Código Postal</label>
                    <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal') }}">
                </div>

                <div class="col-md-12 mb-3">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="3">{{ old('observaciones') }}</textarea>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Cliente</button>
            </div>
        </div>
    </div>
</form>
@endsection