@extends('layouts.admin')

@section('content')
<h2 class="mb-4">Nuevo Equipo</h2>

<form action="{{ route('inventario.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Categoría *</label>
                    <div class="input-group">
                        <select name="categoria_id" class="form-control @error('categoria_id') is-invalid @enderror" required>
                            <option value="">Seleccionar categoría...</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCategoria">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                    @error('categoria_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Unidad de Medida *</label>
                    <div class="input-group">
                        <select name="unidad_medida_id" class="form-control @error('unidad_medida_id') is-invalid @enderror" required>
                            <option value="">Seleccionar unidad...</option>
                            @foreach($unidades as $unidad)
                                <option value="{{ $unidad->id }}" {{ old('unidad_medida_id') == $unidad->id ? 'selected' : '' }}>
                                    {{ $unidad->nombre }} ({{ $unidad->abreviatura }})
                                </option>
                            @endforeach
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalUnidad">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                    @error('unidad_medida_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Resto de campos... -->
                <div class="col-md-6 mb-3">
                    <label>Código</label>
                    <input type="text" class="form-control" value="Se generará automáticamente" disabled>
                    <small class="text-muted">El código se asignará al guardar</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label>Nombre *</label>
                    <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" 
                           value="{{ old('nombre') }}" placeholder="Ej: Andamio Tablón" required>
                    @error('nombre')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Stock *</label>
                    <input type="number" name="stock" class="form-control @error('stock') is-invalid @enderror" 
                           value="{{ old('stock', 0) }}" min="0" required>
                    @error('stock')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label>Precio por día *</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="number" name="precio_dia" step="0.01" class="form-control @error('precio_dia') is-invalid @enderror" 
                               value="{{ old('precio_dia', 0) }}" required>
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

<!-- Modal para crear categoría -->
<div class="modal fade" id="modalCategoria" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Categoría</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formCategoria" action="{{ route('categorias.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre de la categoría *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control form-control-color" value="#0d6efd">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para crear unidad de medida -->
<div class="modal fade" id="modalUnidad" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Unidad de Medida</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="formUnidad" action="{{ route('unidades.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nombre de la unidad *</label>
                        <input type="text" name="nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Abreviatura *</label>
                        <input type="text" name="abreviatura" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Unidad</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection