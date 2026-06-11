@extends('layouts.admin')

@section('content')
<h2>Detalle del Equipo</h2>

<div class="card mt-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center">
                @if($inventario->imagen)
                    <img src="{{ Storage::url($inventario->imagen) }}" class="img-fluid rounded" style="max-width: 200px;">
                @else
                    <i class="bi bi-box-seam" style="font-size: 150px;"></i>
                @endif
            </div>
            <div class="col-md-9">
                <table class="table table-borderless">
                    <tr><th>Código:</th><td>{{ $inventario->codigo }}</td></tr>
                    <tr><th>Nombre:</th><td>{{ $inventario->nombre }}</td></tr>
                    <tr><th>Categoría:</th><td>{{ $inventario->categoria }}</td></tr>
                    <tr><th>Stock:</th><td>{{ $inventario->stock }} unidades</td></tr>
                    <tr><th>Precio día:</th><td>${{ number_format($inventario->precio_dia, 2) }}</td></tr>
                    <tr><th>Estado:</th><td>{{ $inventario->activo ? 'Activo' : 'Inactivo' }}</td></tr>
                    <tr><th>Descripción:</th><td>{{ $inventario->descripcion ?? 'Sin descripción' }}</td></tr>
                    <tr><th>Fecha registro:</th><td>{{ $inventario->created_at ? $inventario->created_at->format('d/m/Y H:i') : 'N/A' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('inventario.index') }}" class="btn btn-primary">Regresar</a>
    <a href="{{ route('inventario.edit', $inventario) }}" class="btn btn-warning">Editar</a>
    <form action="{{ route('inventario.destroy', $inventario) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Eliminar?')">Eliminar</button>
    </form>
</div>
@endsection