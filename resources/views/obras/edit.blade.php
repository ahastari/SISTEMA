@extends('layouts.admin')

@section('content')
<h2>Editar Obra</h2>

<form action="{{ route('obras.update', $obra) }}" method="POST">
    @csrf
    @method('PUT')
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre de la Obra *</label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $obra->nombre) }}" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Cliente *</label>
                    <select name="cliente_id" class="form-control" required>
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ old('cliente_id', $obra->cliente_id) == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre_completo }} - {{ $cliente->telefono }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Dirección *</label>
                    <textarea name="direccion" class="form-control" rows="2" required>{{ old('direccion', $obra->direccion) }}</textarea>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Colonia</label>
                    <input type="text" name="colonia" class="form-control" value="{{ old('colonia', $obra->colonia) }}">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="{{ old('ciudad', $obra->ciudad ?? 'Durango') }}">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Estado</label>
                    <input type="text" name="estado" class="form-control" value="{{ old('estado', $obra->estado ?? 'Dgo.') }}">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Código Postal</label>
                    <input type="text" name="codigo_postal" class="form-control" value="{{ old('codigo_postal', $obra->codigo_postal) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Teléfono de la obra</label>
                    <input type="text" name="telefono_obra" class="form-control" value="{{ old('telefono_obra', $obra->telefono_obra) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Contacto en obra</label>
                    <input type="text" name="contacto_obra" class="form-control" value="{{ old('contacto_obra', $obra->contacto_obra) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="activa" class="form-check-input" id="activa" {{ $obra->activa ? 'checked' : '' }}>
                        <label class="form-check-label" for="activa">Obra activa</label>
                    </div>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2">{{ old('observaciones', $obra->observaciones) }}</textarea>
                </div>
            </div>
            
            <hr>
            
            <div class="text-end">
                <a href="{{ route('obras.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-warning">Actualizar Obra</button>
            </div>
        </div>
    </div>
</form>
@endsection