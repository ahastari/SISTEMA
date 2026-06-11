@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Editar Equipo</h2>

<form action="{{ route('inventario.update', $inventario) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Código</label>
                    <input type="text" class="form-control" value="{{ $inventario->codigo }}" readonly disabled>
                    <input type="hidden" name="codigo" value="{{ $inventario->codigo }}">
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $inventario->nombre) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Categoría *</label>
                    <select name="categoria" class="form-control" required>
                        <option value="Andamios" {{ $inventario->categoria == 'Andamios' ? 'selected' : '' }}>Andamios</option>
                        <option value="Ruedas" {{ $inventario->categoria == 'Ruedas' ? 'selected' : '' }}>Ruedas</option>
                        <option value="Flete" {{ $inventario->categoria == 'Flete' ? 'selected' : '' }}>Flete</option>
                        <option value="Madera" {{ $inventario->categoria == 'Madera' ? 'selected' : '' }}>Madera</option>
                        <option value="Herramientas" {{ $inventario->categoria == 'Herramientas' ? 'selected' : '' }}>Herramientas</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Stock *</label>
                    <input type="number" name="stock" class="form-control" value="{{ old('stock', $inventario->stock) }}" min="0" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Precio por día *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="precio_dia" step="0.01" class="form-control" value="{{ old('precio_dia', $inventario->precio_dia) }}" required>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Imagen actual</label>
                    @if($inventario->imagen)
                        <div class="mb-2">
                            <img src="{{ Storage::url($inventario->imagen) }}" style="max-width: 150px;">
                        </div>
                    @endif
                    <input type="file" name="imagen" class="form-control" accept="image/*">
                    <small class="text-muted">Dejar vacío para mantener la imagen actual</small>
                </div>

                <div class="col-md-12 mb-3">
                    <label>Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $inventario->descripcion) }}</textarea>
                </div>

                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input type="checkbox" name="activo" class="form-check-input" id="activo" {{ $inventario->activo ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Equipo activo</label>
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-end">
                <a href="{{ route('inventario.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Actualizar</button>
            </div>
        </div>
    </div>
</form>
@endsection