@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Editar Equipo</h2>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('inventario.update', $equipo) }}" method="POST" enctype="multipart/form-data">
    @method('PUT')
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Código</label>
                    <input type="text" class="form-control" value="{{ $equipo->codigo }}" readonly disabled>
                    <input type="hidden" name="codigo" value="{{ $equipo->codigo }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                           value="{{ old('nombre', $equipo->nombre) }}" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Categoría *</label>
                    <select name="categoria_id" class="form-control @error('categoria_id') is-invalid @enderror" required>
                        <option value="">Seleccionar categoría...</option>
                        @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ old('categoria_id', $equipo->categoria_id) == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                    @error('categoria_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Unidad de Medida *</label>
                    <select name="unidad_medida_id" class="form-control @error('unidad_medida_id') is-invalid @enderror" required>
                        <option value="">Seleccionar unidad...</option>
                        @foreach($unidades as $unidad)
                            <option value="{{ $unidad->id }}" {{ old('unidad_medida_id', $equipo->unidad_medida_id) == $unidad->id ? 'selected' : '' }}>
                                {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                            </option>
                        @endforeach
                    </select>
                    @error('unidad_medida_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Stock *</label>
                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                           value="{{ old('stock', $equipo->stock) }}" min="0" required>
                    @error('stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Precio por día *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="precio_dia" step="0.01" class="form-control @error('precio_dia') is-invalid @enderror" 
                               value="{{ old('precio_dia', $equipo->precio_dia) }}" required>
                    </div>
                    @error('precio_dia')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label>Imagen actual</label>
                    @if($equipo->imagen)
                        <div class="mb-2">
                            <img src="{{ Storage::url($equipo->imagen) }}" style="max-width: 150px;">
                            <br>
                            <small><a href="{{ Storage::url($equipo->imagen) }}" target="_blank">Ver imagen actual</a></small>
                        </div>
                    @endif
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                    <small class="text-muted">Dejar vacío para mantener la imagen actual (Máx. 2MB)</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $equipo->descripcion) }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ old('activo', $equipo->activo) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Equipo activo (disponible para renta/venta)</label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Actualizar Equipo</button>
            </div>
        </div>
    </div>
</form>
@endsection