@extends('layouts.admin')

@section('content')
<h2>Detalle del Equipo</h2>

<div class="card mt-3">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 text-center">
                @if($equipo->imagen)
                    <img src="{{ Storage::url($equipo->imagen) }}" alt="{{ $equipo->nombre }}" class="img-fluid rounded" style="max-width: 200px;">
                @else
                    <i class="bi bi-box-seam" style="font-size: 150px;"></i>
                @endif
            </div>
            <div class="col-md-9">
                <table class="table table-borderless">
                    <tr>
                        <th width="150">Código:</th>
                        <td>{{ $equipo->codigo }}</td>
                    </tr>
                    <tr>
                        <th>Nombre:</th>
                        <td>{{ $equipo->nombre }}</td>
                    </tr>
                    <tr>
                        <th>Categoría:</th>
                        <td>
                            @if($equipo->categoria)
                                <span class="badge" style="background-color: {{ $equipo->categoria->color ?? '#6c757d' }}; color: white;">
                                    {{ $equipo->categoria->nombre }}
                                </span>
                            @else
                                <span class="text-muted">Sin categoría</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Unidad de Medida:</th>
                        <td>
                            @if($equipo->unidadMedida)
                                {{ $equipo->unidadMedida->nombre }} ({{ $equipo->unidadMedida->abreviatura }})
                            @else
                                <span class="text-muted">Sin unidad</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Stock:</th>
                        <td>
                            @if($equipo->stock <= 5 && $equipo->stock > 0)
                                <span class="badge bg-warning">{{ $equipo->stock }} unidades</span>
                            @elseif($equipo->stock == 0)
                                <span class="badge bg-danger">{{ $equipo->stock }} unidades (Agotado)</span>
                            @else
                                <span class="badge bg-success">{{ $equipo->stock }} unidades</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Precio por día:</th>
                        <td><span class="badge bg-primary">${{ number_format($equipo->precio_dia, 2) }}</span></td>
                    </tr>
                    <tr>
                        <th>Estado:</th>
                        <td>
                            @if($equipo->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Descripción:</th>
                        <td>{{ $equipo->descripcion ?? 'Sin descripción' }}</td>
                    </tr>
                    <tr>
                        <th>Fecha registro:</th>
                        <td>
                            @if($equipo->created_at)
                                {{ $equipo->created_at->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">Fecha no disponible</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="{{ route('inventario.index') }}" class="btn btn-primary">Regresar</a>
    <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-warning">Editar</a>
    <form action="{{ route('inventario.destroy', $equipo) }}" method="POST" class="d-inline">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Eliminar</button>
    </form>
</div>
@endsection