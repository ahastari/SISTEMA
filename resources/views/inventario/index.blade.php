@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between mb-4">
    <h2>Inventario de Equipos</h2>
    <div>
        <a href="{{ route('inventario.kanban') }}" class="btn btn-info me-2">
            <i class="bi bi-grid-3x3-gap-fill"></i> Vista Kanban
        </a>
        <a href="{{ route('inventario.create') }}" class="btn btn-success">
            + Nuevo Equipo
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card">
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Buscar equipo..." value="{{ request('search') }}">
                        <button class="btn btn-primary" type="submit">Buscar</button>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <form method="GET" action="{{ route('inventario.index') }}">
                    <select name="categoria" class="form-select" onchange="this.form.submit()">
                        <option value="">Todas las categorías</option>
                        <option value="Andamios" {{ request('categoria') == 'Andamios' ? 'selected' : '' }}>Andamios</option>
                        <option value="Ruedas" {{ request('categoria') == 'Ruedas' ? 'selected' : '' }}>Ruedas</option>
                        <option value="Flete" {{ request('categoria') == 'Flete' ? 'selected' : '' }}>Flete</option>
                        <option value="Madera" {{ request('categoria') == 'Madera' ? 'selected' : '' }}>Madera</option>
                        <option value="Herramientas" {{ request('categoria') == 'Herramientas' ? 'selected' : '' }}>Herramientas</option>
                    </select>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Imagen</th>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th>Precio día</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($equipos as $equipo)
                    <tr>
                        <td class="text-center">
                            @if($equipo->imagen && Storage::disk('public')->exists($equipo->imagen))
                                <img src="{{ asset('storage/' . $equipo->imagen) }}" alt="{{ $equipo->nombre }}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            @else
                                <i class="bi bi-image" style="font-size: 30px; color: #999;"></i>
                            @endif
                        </td>
                        <td>{{ $equipo->codigo }}</td>
                        <td>{{ $equipo->nombre }}</td>
                        <td>{{ $equipo->categoria }}</td>
                        <td>${{ number_format($equipo->precio_dia, 2) }}</td>
                        <td>
                            @if($equipo->stock <= 5 && $equipo->stock > 0)
                                <span class="badge bg-warning">{{ $equipo->stock }}</span>
                            @elseif($equipo->stock == 0)
                                <span class="badge bg-danger">{{ $equipo->stock }}</span>
                            @else
                                <span class="badge bg-success">{{ $equipo->stock }}</span>
                            @endif
                        </td>
                        <td>
                            @if($equipo->activo)
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('inventario.show', $equipo) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('inventario.edit', $equipo) }}" class="btn btn-warning btn-sm">Editar</a>
                            <form action="{{ route('inventario.destroy', $equipo) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este equipo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay equipos registrados</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $equipos->appends(request()->query())->links() }}
    </div>
</div>

<script>
// Mantener la categoría al buscar
document.querySelector('form .input-group button').addEventListener('click', function(e) {
    let categoriaSelect = document.querySelector('select[name="categoria"]');
    if (categoriaSelect && categoriaSelect.value) {
        let form = this.closest('form');
        let hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'categoria';
        hiddenInput.value = categoriaSelect.value;
        form.appendChild(hiddenInput);
    }
});
</script>
@endsection