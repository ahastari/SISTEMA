@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-building"></i> Obras / Proyectos</h2>
    <a href="{{ route('obras.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Nueva Obra
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre de la Obra</th>
                        <th>Cliente</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($obras as $obra)
                    <tr>
                        <td>{{ $obra->id }}</td>
                        <td>{{ $obra->nombre }}</td>
                        <td>{{ $obra->cliente->nombre_completo }}</td>
                        <td>{{ $obra->direccion }}</td>
                        <td>{{ $obra->ciudad ?? 'N/A' }}</td>
                        <td>
                            @if($obra->activa)
                                <span class="badge bg-success">Activa</span>
                            @else
                                <span class="badge bg-danger">Inactiva</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('obras.show', $obra) }}" class="btn btn-info btn-sm">Ver</a>
                            <a href="{{ route('obras.edit', $obra) }}" class="btn btn-warning btn-sm">Editar</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center">No hay obras registradas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $obras->links() }}
    </div>
</div>
@endsection