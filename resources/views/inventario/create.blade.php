@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Nuevo Equipo</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('inventario.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                El código se generará automáticamente al guardar según la categoría seleccionada.
                <br>
                <strong>Formato:</strong> AND-001, RUE-001, MAD-001, etc.
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Código</label>
                    <input type="text" class="form-control" value="Se generará automáticamente" disabled>
                    <small class="text-muted">El código se asignará al guardar</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre') }}" placeholder="Ej: Andamio Tablón" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Categoría *</label>
                    <select name="categoria" class="form-control @error('categoria') is-invalid @enderror" required>
                        <option value="">Seleccionar...</option>
                        <option value="Andamios" {{ old('categoria') == 'Andamios' ? 'selected' : '' }}>Andamios (AND)</option>
                        <option value="Ruedas" {{ old('categoria') == 'Ruedas' ? 'selected' : '' }}>Ruedas (RUE)</option>
                        <option value="Flete" {{ old('categoria') == 'Flete' ? 'selected' : '' }}>Flete (FLE)</option>
                        <option value="Madera" {{ old('categoria') == 'Madera' ? 'selected' : '' }}>Madera (MAD)</option>
                        <option value="Herramientas" {{ old('categoria') == 'Herramientas' ? 'selected' : '' }}>Herramientas (HER)</option>
                    </select>
                    @error('categoria')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Stock *</label>
                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" value="{{ old('stock', 0) }}" min="0" required>
                    @error('stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Precio por día *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="precio_dia" step="0.01" class="form-control @error('precio_dia') is-invalid @enderror" value="{{ old('precio_dia', 0) }}" required>
                    </div>
                    @error('precio_dia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label>Imagen del equipo</label>
                    <input type="file" name="imagen" class="form-control @error('imagen') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Formatos: JPG, JPEG, PNG (Máx. 2MB)</small>
                    @error('imagen')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion') }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ old('activo', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Equipo activo (disponible para renta/venta)</label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Equipo</button>
            </div>
        </div>
    </div>
</form>
@endsection