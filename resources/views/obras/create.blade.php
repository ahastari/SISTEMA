@extends('layouts.admin')

@section('content')
<h2>Nueva Obra</h2>

<form action="{{ route('obras.store') }}" method="POST">
    @csrf
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Nombre de la Obra *</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label>Cliente *</label>
                    <select name="cliente_id" class="form-control" required>
                        <option value="">Seleccionar cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{ $cliente->id }}">{{ $cliente->nombre_completo }} - {{ $cliente->telefono }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Dirección *</label>
                    <textarea name="direccion" class="form-control" rows="2" required></textarea>
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Colonia</label>
                    <input type="text" name="colonia" class="form-control">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Ciudad</label>
                    <input type="text" name="ciudad" class="form-control" value="Durango">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Estado</label>
                    <input type="text" name="estado" class="form-control" value="Dgo.">
                </div>
                
                <div class="col-md-3 mb-3">
                    <label>Código Postal</label>
                    <input type="text" name="codigo_postal" class="form-control">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Teléfono de la obra</label>
                    <input type="text" name="telefono_obra" class="form-control">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label>Contacto en obra</label>
                    <input type="text" name="contacto_obra" class="form-control">
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="activa" class="form-check-input" id="activa" checked>
                        <label class="form-check-label" for="activa">Obra activa</label>
                    </div>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label>Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"></textarea>
                </div>
            </div>
            
            <hr>
            
            <div class="text-end">
                <a href="{{ route('obras.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Obra</button>
            </div>
        </div>
    </div>
</form>
@endsection